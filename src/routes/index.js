const { db, auth, storage } = require("../firebase"); // Added storage
const { Router } = require("express");
const router = Router();
const fetch = require('node-fetch');
const multer = require('multer'); // Added multer
const { v4: uuidv4 } = require('uuid'); // For unique filenames

// IMPORTANT: Replace with your Firebase Web API Key
const FIREBASE_API_KEY = 'AIzaSyDZm61e0yhp49aYEtnsrxE9X9LiBcZJSas';

// Configure Multer for memory storage
const upload = multer({
  storage: multer.memoryStorage()
});

// Function to upload file to Firebase Storage
async function uploadFileToFirebaseStorage(file) {
  if (!file) return null;

  const bucket = storage.bucket();
  const filename = `services/${uuidv4()}-${file.originalname}`;
  const blob = bucket.file(filename);

  const blobStream = blob.createWriteStream({
    metadata: {
      contentType: file.mimetype,
    },
  });

  return new Promise((resolve, reject) => {
    blobStream.on('error', (err) => {
      reject(err);
    });

    blobStream.on('finish', async () => {
      // Make the file public
      await blob.makePublic();
      // Get the public URL
      const publicUrl = `https://storage.googleapis.com/${bucket.name}/${blob.name}`;
      resolve(publicUrl);
    });

    blobStream.end(file.buffer);
  });
}


router.get("/", (req, res) => {
  if (req.session.user) {
    return res.redirect("/dashboard");
  }
  res.render("index", { layout: false }); // Render index.hbs without a layout
});

router.get("/dashboard", async (req, res) => { // Added async
  if (!req.session.user) {
    return res.redirect("/");
  }
  // Only allow admin to access this view
  // Require explicit admin role; otherwise redirect appropriately
  if (req.session.user.role !== 'admin') {
    if (req.session.user.role === 'secretario') return res.redirect('/dashboard_secretario');
    return res.redirect('/');
  }

  // Helper function to safely convert various date formats to a JS Date object
  const safeGetDate = (dateValue) => {
    if (!dateValue) return null;
    // If it's a Firestore Timestamp, it will have a toDate method
    if (typeof dateValue.toDate === 'function') {
      return dateValue.toDate();
    }
    // If it's already a JS Date, return it
    if (dateValue instanceof Date) {
      return dateValue;
    }
    // Try to parse it as a string or number
    const date = new Date(dateValue);
    if (!isNaN(date.getTime())) {
      return date;
    }
    return null; // Return null if conversion fails
  };

  try {
    // --- Fetch Data from Firebase ---
    const usersCollection = await db.collection('usuarios').get();
    const servicesCollection = await db.collection('servicios').get();
    const appointmentsCollection = await db.collection('citas').get();

    const totalUsuarios = usersCollection.size;
    const totalServicios = servicesCollection.size;
    const totalCitas = appointmentsCollection.size;

    // --- Fetch Recent Activities ---
    let recentActivities = [];

    // Get last 5 users
    const usersSnapshot = await db.collection('usuarios').orderBy('createdAt', 'desc').limit(5).get();
    usersSnapshot.forEach(doc => {
      const data = doc.data();
      const activityDate = safeGetDate(data.createdAt);
      if (activityDate) {
        recentActivities.push({
          tipo: 'Usuario',
          descripcion: `Nuevo usuario: ${data.nombre || ''} ${data.apellido || ''}`.trim(),
          fecha: activityDate
        });
      }
    });

    // Get last 5 appointments
    const appointmentsSnapshot = await db.collection('citas').orderBy('fecha', 'desc').limit(5).get();
    appointmentsSnapshot.forEach(doc => {
      const data = doc.data();
      const activityDate = safeGetDate(data.fecha);
      if (activityDate) {
        recentActivities.push({
          tipo: 'Cita',
          descripcion: `Nueva cita para: ${data.nombreCliente} (${data.servicio})`,
          fecha: activityDate
        });
      }
    });

    // Sort all activities by date and take the most recent 5
    recentActivities.sort((a, b) => {
      const dateA = a.fecha ? a.fecha.getTime() : 0; // Treat null/invalid dates as very old
      const dateB = b.fecha ? b.fecha.getTime() : 0;
      return dateB - dateA; // Sort descending (most recent first)
    });
    const latestActivities = recentActivities.slice(0, 5);

    // Format dates for Handlebars
    const formattedActivities = latestActivities.map(activity => {
      const options = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false };
      return {
        ...activity,
        fecha_formateada: activity.fecha.toLocaleString('es-MX', options).replace(',', '')
      };
    });

    res.render("dashboard-content", {
      active: { dashboard: true },
      user_name: req.session.user.nombre || req.session.user.email,
      totalUsuarios,
      totalServicios,
      totalCitas,
      recentActivities: formattedActivities
    });

  } catch (error) {
    console.error("Error fetching dashboard data:", error);
    res.render("dashboard-content", {
      active: { dashboard: true },
      error: "No se pudieron cargar los datos del dashboard. Verifique la consola del servidor para más detalles.",
      user_name: req.session.user.nombre || req.session.user.email,
      totalUsuarios: 0, // Provide default values
      totalServicios: 0,
      totalCitas: 0,
      recentActivities: []
    });
  }
});

router.post("/login", async (req, res) => {
  const { correo, contrasena } = req.body;

  try {
    const response = await fetch(`https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=${FIREBASE_API_KEY}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        email: correo,
        password: contrasena,
        returnSecureToken: true
      })
    });

    const data = await response.json();

    if (!response.ok) {
      const error = data.error.message;
      return res.render("index", { error });
    }

    const user = await auth.getUserByEmail(correo);

    // Try to get role from custom claims first
    let role = null;
    if (user.customClaims && user.customClaims.role) {
      role = user.customClaims.role;
    }

    // Fallback: check possible Firestore collections/fields for a role
    if (!role) {
      const collectionsToTry = ['users', 'usuarios'];
      const roleFields = ['role', 'rol'];
      for (const col of collectionsToTry) {
        try {
          const userDoc = await db.collection(col).doc(user.uid).get();
          if (userDoc.exists) {
            const data = userDoc.data();
            if (data) {
              for (const f of roleFields) {
                if (data[f]) {
                  role = String(data[f]).trim().toLowerCase();
                  break;
                }
              }
            }
          }
        } catch (e) {
          console.error(`Error reading user role from Firestore collection ${col}:`, e.message);
        }
        if (role) break;
      }
    }

    // Normalize and save role in session
    const normalizedRole = role ? String(role).trim().toLowerCase() : null;

    const userDoc = await db.collection('usuarios').doc(user.uid).get();
    let nombre = user.displayName; // fallback to displayName
    if (userDoc.exists) {
        const userData = userDoc.data();
        if (userData.nombre) {
            nombre = userData.nombre;
        }
    }

    req.session.user = {
      uid: user.uid,
      email: user.email,
      displayName: user.displayName,
      nombre: nombre,
      role: normalizedRole
    };

    console.log('User logged in:', { uid: user.uid, email: user.email, role: normalizedRole });

    // Redirect based on explicit role. Deny-by-default when role is unknown.
    if (normalizedRole === 'secretario') return res.redirect('/dashboard_secretario');
    if (normalizedRole === 'admin') return res.redirect('/dashboard');
    // No known role -> redirect to home (change this if you prefer default admin)
    return res.redirect('/');
  } catch (error) {
    res.render("index", { error: error.message });
  }
});

router.get("/logout", (req, res) => {
  req.session.destroy(() => {
    res.redirect("/");
  });
});

// Secretary dashboard route
router.get('/dashboard_secretario', (req, res) => {
  if (!req.session.user) return res.redirect('/');
  if (req.session.user.role !== 'secretario') return res.redirect('/dashboard');
  res.render('dashboard_secretario', { user: req.session.user });
});

// --- Service Management Routes ---
router.get("/servicios", async (req, res) => {
    if (!req.session.user) {
        return res.redirect("/");
    }
    try {
        const servicesSnapshot = await db.collection('servicios').orderBy('servicio').get();
        const services = [];
        servicesSnapshot.forEach(doc => {
            services.push({ id: doc.id, ...doc.data() });
        });
        res.render("service-view", { 
            active: { services: true },
            user_name: req.session.user.nombre || req.session.user.email,
            services: services, 
            query: req.query 
        });
    } catch (error) {
        console.error("Error fetching services: ", error);
        res.redirect("/dashboard?error=true");
    }
});

router.post("/servicios/add", upload.single('imagenFile'), async (req, res) => { // Added upload.single
    if (!req.session.user) {
        return res.redirect("/");
    }
    try {
        const { servicio, categoria, descripcion, precio, estado, duracion } = req.body;
        let imageUrl = '';

        if (req.file) {
            imageUrl = await uploadFileToFirebaseStorage(req.file);
        }

        const newService = {
            servicio,
            categoria,
            descripcion,
            precio: parseFloat(precio),
            estado,
            duracion: parseInt(duracion),
            imagen: imageUrl,
            createdAt: new Date() // Added createdAt
        };
        await db.collection('servicios').add(newService);
        res.redirect("/servicios?created=true");
    } catch (error) {
        console.error("Error adding service: ", error);
        res.redirect("/servicios?error=true");
    }
});

router.post("/servicios/edit", upload.single('imagenFile'), async (req, res) => { // Added upload.single
    if (!req.session.user) {
        return res.redirect("/");
    }
    try {
        const { id, servicio, categoria, descripcion, precio, estado, duracion, currentImagen } = req.body; // Added currentImagen
        let imageUrl = currentImagen; // Start with current image URL

        if (req.file) {
            imageUrl = await uploadFileToFirebaseStorage(req.file);
        }

        const updatedService = {
            servicio,
            categoria,
            descripcion,
            precio: parseFloat(precio),
            estado,
            duracion: parseInt(duracion),
            imagen: imageUrl
        };
        await db.collection('servicios').doc(id).update(updatedService);
        res.redirect("/servicios?updated=true");
    } catch (error) {
        console.error("Error updating service: ", error);
        res.redirect("/servicios?error=true");
    }
});

router.post("/servicios/delete", async (req, res) => {
    if (!req.session.user) {
        return res.redirect("/");
    }
    try {
        const { id } = req.body;
        await db.collection('servicios').doc(id).delete();
        res.redirect("/servicios?deleted=true");
    } catch (error) {
        console.error("Error deleting service: ", error);
        res.redirect("/servicios?error=true");
    }
});

// --- User Management Routes ---
router.get("/usuarios", async (req, res) => {
    if (!req.session.user) {
        return res.redirect("/");
    }
    try {
        const usersSnapshot = await db.collection('usuarios').orderBy('nombre').get();
        const allUsers = [];
        usersSnapshot.forEach(doc => {
            // Exclude Admins from the list
            if (doc.data().rol !== 'Admin') {
                allUsers.push({ id: doc.id, ...doc.data() });
            }
        });

        // Exclude the currently logged-in user from the list
        const loggedInUserId = req.session.user.uid;
        const users = allUsers.filter(user => user.id !== loggedInUserId);

        res.render("user-view", { 
            active: { usuarios: true },
            user_name: req.session.user.nombre || req.session.user.email,
            users: users, // Pass the filtered list
            query: req.query
        });
    } catch (error) {
        console.error("Error fetching users: ", error);
        res.redirect("/dashboard?error=true");
    }
});

router.post("/usuarios/add", async (req, res) => {
    if (!req.session.user) {
        return res.redirect("/");
    }
    const { nombre, apellido, correo, password, rol } = req.body;

    if (!['Secretario', 'Terapeuta'].includes(rol)) {
        return res.redirect(`/usuarios?error=${encodeURIComponent('No está permitido crear usuarios con el rol "' + rol + '"')}`);
    }

    try {
        const userRecord = await auth.createUser({
            email: correo,
            password: password,
            displayName: `${nombre} ${apellido}`
        });

        await auth.setCustomUserClaims(userRecord.uid, { role: rol });

        await db.collection('usuarios').doc(userRecord.uid).set({
            nombre,
            apellido,
            correo,
            rol,
            createdAt: new Date()
        });

        res.redirect("/usuarios?created=true");
    } catch (error) {
        console.error("Error adding user: ", error);
        res.redirect(`/usuarios?error=${encodeURIComponent(error.message)}`);
    }
});

router.post("/usuarios/edit", async (req, res) => {
    if (!req.session.user) {
        return res.redirect("/");
    }
    const { id, nombre, apellido, correo, rol, password } = req.body;

    if (!['Secretario', 'Terapeuta'].includes(rol)) {
        return res.redirect(`/usuarios?error=${encodeURIComponent('No está permitido asignar el rol "' + rol + '"')}`);
    }

    try {
        const userDoc = await db.collection('usuarios').doc(id).get();
        if (!userDoc.exists) {
            return res.redirect(`/usuarios?error=Usuario no encontrado.`);
        }

        const existingRol = userDoc.data().rol;
        if (['Admin', 'Cliente'].includes(existingRol)) {
            return res.redirect(`/usuarios?error=${encodeURIComponent('No se pueden modificar usuarios con el rol "' + existingRol + '"')}`);
        }

        const updatePayload = {
            email: correo,
            displayName: `${nombre} ${apellido}`
        };
        if (password) {
            updatePayload.password = password;
        }
        await auth.updateUser(id, updatePayload);

        await auth.setCustomUserClaims(id, { role: rol });

        await db.collection('usuarios').doc(id).update({
            nombre,
            apellido,
            correo,
            rol
        });

        res.redirect("/usuarios?updated=true");
    } catch (error) {
        console.error("Error updating user: ", error);
        res.redirect(`/usuarios?error=${encodeURIComponent(error.message)}`);
    }
});

router.post("/usuarios/delete", async (req, res) => {
    if (!req.session.user) {
        return res.redirect("/");
    }
    const { id } = req.body;
    try {
        const userDoc = await db.collection('usuarios').doc(id).get();
        if (userDoc.exists) {
            const existingRol = userDoc.data().rol;
            if (['Admin', 'Cliente'].includes(existingRol)) {
                return res.redirect(`/usuarios?error=${encodeURIComponent('No se pueden eliminar usuarios con el rol "' + existingRol + '"')}`);
            }
        }

        await db.collection('usuarios').doc(id).delete();
        await auth.deleteUser(id);
        
        res.redirect("/usuarios?deleted=true");
    } catch (error) {
        console.error("Error deleting user: ", error);
        res.redirect(`/usuarios?error=${encodeURIComponent(error.message)}`);
    }
});

module.exports = router;