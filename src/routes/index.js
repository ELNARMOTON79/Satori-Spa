const { db, auth, storage } = require("../firebase");
const { Router } = require("express");
const router = Router();
const fetch = require('node-fetch');
const multer = require('multer');
const path = require('path');
const fs = require('fs');
const PDFDocument = require('pdfkit');

// Helper: normalize a service document so templates can rely on `servicio` as the
// human-readable label. This prefers several common field names and falls back
// to the document id when no name is present.
function normalizeService(svc, docId) {
  if (!svc) svc = {};
  const id = docId || svc.id || '';
  svc.servicio = svc.servicio || svc.nombre || svc.name || svc.servicio_nombre || svc.nombre_servicio || svc.serviceName || svc.servicioName || String(id);
  return svc;
}

// Multer setup: use memory storage so we can pipe the buffer to Firebase Storage
const upload = multer({
  storage: multer.memoryStorage(),
  limits: { fileSize: 5 * 1024 * 1024 } // 5 MB file size limit
});

// IMPORTANT: Replace with your Firebase Web API Key
const FIREBASE_API_KEY = 'AIzaSyDZm61e0yhp49aYEtnsrxE9X9LiBcZJSas';

router.get("/", (req, res) => {
  if (req.session.user) {
    return res.redirect("/dashboard");
  }
  res.render("index");
});

router.get("/dashboard", (req, res) => {
  console.log('GET /dashboard - sessionID:', req.sessionID, 'session.user:', req.session && req.session.user);
  if (!req.session.user) {
    return res.redirect("/");
  }
  // Only allow admin to access this view
  // Require explicit admin role; otherwise redirect appropriately
  if (req.session.user.role !== 'admin') {
    if (req.session.user.role === 'secretario') return res.redirect('/dashboard_secretario');
    return res.redirect('/');
  }
  // Keep old route but redirect to the canonical admin dashboard route
  return res.redirect('/admin/dashboard');
});

// Canonical admin dashboard route that renders the view inside views/admin/
router.get('/admin/dashboard', async (req, res) => {
  console.log('GET /admin/dashboard - sessionID:', req.sessionID, 'session.user:', req.session && req.session.user);
  if (!req.session.user) return res.redirect('/');
  if (req.session.user.role !== 'admin') {
    if (req.session.user.role === 'secretario') return res.redirect('/dashboard_secretario');
    return res.redirect('/');
  }

  // Define sidebar sections (id, title, href, icon html)
  const sections = [
    { id: 'dashboard', title: 'Dashboard', href: '/admin/dashboard', iconClass: 'fa-solid fa-chart-simple' },
    { id: 'usuarios', title: 'Usuarios', href: '/admin/usuarios', iconClass: 'fa-solid fa-users' },
    { id: 'servicios', title: 'Servicios', href: '/admin/servicios', iconClass: 'fa-solid fa-concierge-bell' },
    { id: 'categorias', title: 'Categorías', href: '/admin/categorias', iconClass: 'fa-solid fa-tags' },
    { id: 'citas', title: 'Citas', href: '/admin/citas', iconClass: 'fa-solid fa-calendar-check' },
    { id: 'configuracion', title: 'Configuración', href: '/admin/configuracion', iconClass: 'fa-solid fa-cog' }
  ];

  // Fetch usuarios and servicios to show lists in the dashboard
  try {
    const usuarios = [];
    try {
      const usersSnap = await db.collection('usuarios').get();
      const fetchEmailPromises = [];
      usersSnap.forEach(doc => {
        const d = doc.data() || {};
        const u = {
          id: doc.id,
          nombre: d.nombre || null,
          apellido: d.apellido || null,
          correo: d.correo || d.email || null,
          email: d.email || d.correo || null,
          rol: d.role || d.rol || null
        };
        usuarios.push(u);

        // Try to enrich from Auth: email and possible customClaims.role
        if (auth && typeof auth.getUser === 'function') {
          fetchEmailPromises.push(
            auth.getUser(doc.id)
              .then(authUser => {
                if (authUser && authUser.email) {
                  u.email = authUser.email;
                  u.correo = authUser.email;
                }
                // If role is stored in customClaims, prefer that
                if (authUser && authUser.customClaims && authUser.customClaims.role) {
                  try {
                    u.rol = String(authUser.customClaims.role).toLowerCase();
                  } catch (e) {
                    // ignore
                  }
                }
              })
              .catch(() => {})
          );
        }
      });

      if (fetchEmailPromises.length) await Promise.all(fetchEmailPromises);
    } catch (e) {
      console.debug('No se pudo leer colección usuarios (puede no existir aún):', e.message || e);
    }

    // Filter only users with role 'cliente' (or 'client')
    const clientes = usuarios.filter(u => {
      const r = (u.rol || '').toString().toLowerCase();
      return r === 'cliente' || r === 'client';
    });

    const servicios = [];
    try {
      const snap = await db.collection('servicios').get();
      snap.forEach(doc => {
        const d = doc.data() || {};
        const svc = Object.assign({ id: doc.id }, d);
        normalizeService(svc, doc.id);
        servicios.push(svc);
      });
    } catch (e) {
      console.debug('No se pudo leer colección servicios (puede no existir aún):', e.message || e);
    }

    // Render the admin dashboard view located at views/admin/dashboard.hbs
  // Pass only clientes in the `usuarios` variable to keep template unchanged
  res.render('admin/dashboard', { user: req.session.user, sections, active: 'dashboard', usuarios: clientes, servicios });
  } catch (err) {
    console.error('Error fetching data for admin dashboard:', err.message || err);
    res.render('admin/dashboard', { user: req.session.user, sections, active: 'dashboard', usuarios: [], servicios: [], error: 'No se pudieron cargar los datos' });
  }
});

// Admin users list
router.get('/admin/usuarios', async (req, res) => {
  if (!req.session.user) return res.redirect('/');
  if (req.session.user.role !== 'admin') {
    if (req.session.user.role === 'secretario') return res.redirect('/dashboard_secretario');
    return res.redirect('/');
  }

  // Define same sidebar sections so partial can render links
  const sections = [
      { id: 'dashboard', title: 'Dashboard', href: '/admin/dashboard', iconClass: 'fa-solid fa-chart-simple' },
      { id: 'usuarios', title: 'Usuarios', href: '/admin/usuarios', iconClass: 'fa-solid fa-users' },
      { id: 'servicios', title: 'Servicios', href: '/admin/servicios', iconClass: 'fa-solid fa-concierge-bell' },
      { id: 'categorias', title: 'Categorías', href: '/admin/categorias', iconClass: 'fa-solid fa-tags' },
      { id: 'citas', title: 'Citas', href: '/admin/citas', iconClass: 'fa-solid fa-calendar-check' },
      { id: 'configuracion', title: 'Configuración', href: '/admin/configuracion', iconClass: 'fa-solid fa-cog' }
  ];

  try {
    // Read users from Firestore collection 'usuarios'
    const usersSnap = await db.collection('usuarios').get();
    const users = [];
    const fetchEmailPromises = [];
    usersSnap.forEach(doc => {
      const d = doc.data() || {};
      // Prepare user object and expose both `correo` and `email` to match different naming conventions in the templates/code
      const u = {
        id: doc.id,
        nombre: d.nombre || null,
        apellido: d.apellido || null,
        correo: d.correo || d.email || null,
        email: d.email || d.correo || null,
        rol: d.role || d.rol || null,
        // include assigned servicio if present so template can render data-servicio
        terapeuta_servicio: d.terapeuta_servicio || d.servicioAsignado || null
      };

      users.push(u);

      // If we don't have an email in the Firestore doc, try to fetch it from Firebase Authentication
      if (!u.email && auth && typeof auth.getUser === 'function') {
        // Assume document id may be the uid used by Authentication; try to fetch and fill the email fields
        fetchEmailPromises.push(
          auth.getUser(doc.id)
            .then(authUser => {
              if (authUser && authUser.email) {
                u.email = authUser.email;
                u.correo = authUser.email;
              }
            })
            .catch(err => {
              // ignore - the doc id might not be the auth uid or user might not exist in Auth
              // keep u.email/u.correo as null
              // console.debug(`Could not fetch auth user for uid=${doc.id}:`, err.message || err);
            })
        );
      }
    });

    // Wait for any pending auth fetches
    if (fetchEmailPromises.length) await Promise.all(fetchEmailPromises);

    // If there are still users without email, do a paginated listUsers() to build a uid->email map
    const usersMissingEmail = users.filter(u => !u.email);
    if (usersMissingEmail.length && auth && typeof auth.listUsers === 'function') {
      try {
        const uidToEmail = new Map();
        // Paginate through auth users; stop early when we've found all missing emails
        let nextPageToken = undefined;
        const missingUids = new Set(usersMissingEmail.map(u => u.id));

        while (missingUids.size > 0) {
          const listResult = await auth.listUsers(1000, nextPageToken);
          (listResult.users || []).forEach(aUserRecord => {
            if (aUserRecord && aUserRecord.uid && aUserRecord.email) {
              if (missingUids.has(aUserRecord.uid)) {
                uidToEmail.set(aUserRecord.uid, aUserRecord.email);
                missingUids.delete(aUserRecord.uid);
              }
            }
          });

          nextPageToken = listResult.pageToken;
          if (!nextPageToken) break; // no more pages
        }

        // Fill users from the map
        users.forEach(u => {
          if (!u.email && uidToEmail.has(u.id)) {
            u.email = uidToEmail.get(u.id);
            u.correo = uidToEmail.get(u.id);
          }
        });
      } catch (e) {
        console.error('Error paginating auth.listUsers to fill emails:', e.message || e);
      }
    }

    res.render('admin/usuarios', { user: req.session.user, sections, active: 'usuarios', users });
  } catch (err) {
    console.error('Error fetching usuarios from Firestore:', err.message || err);
    res.render('admin/usuarios', { user: req.session.user, sections, active: 'usuarios', users: [], error: 'No se pudieron cargar los usuarios' });
  }
});

// Admin servicios list (renders views/admin/servicios.hbs)
router.get('/admin/servicios', async (req, res) => {
  if (!req.session.user) return res.redirect('/');
  if (req.session.user.role !== 'admin') {
    if (req.session.user.role === 'secretario') return res.redirect('/dashboard_secretario');
    return res.redirect('/');
  }

  const sections = [
    { id: 'dashboard', title: 'Dashboard', href: '/admin/dashboard', iconClass: 'fa-solid fa-chart-simple' },
    { id: 'usuarios', title: 'Usuarios', href: '/admin/usuarios', iconClass: 'fa-solid fa-users' },
    { id: 'servicios', title: 'Servicios', href: '/admin/servicios', iconClass: 'fa-solid fa-concierge-bell' },
    { id: 'categorias', title: 'Categorías', href: '/admin/categorias', iconClass: 'fa-solid fa-tags' },
    { id: 'citas', title: 'Citas', href: '/admin/citas', iconClass: 'fa-solid fa-calendar-check' },
    { id: 'configuracion', title: 'Configuración', href: '/admin/configuracion', iconClass: 'fa-solid fa-cog' }
  ];

  try {
    // Try to fetch servicios from Firestore if the collection exists. If not, render with an empty list.
    let servicios = [];
    try {
      const snap = await db.collection('servicios').get();
      snap.forEach(doc => {
        const d = doc.data() || {};
        const svc = Object.assign({ id: doc.id }, d);
        normalizeService(svc, doc.id);
        // compute a CSS class for estado to be used by the template for conditional colors
        const estadoVal = (svc.estado || svc.state || '').toString().toLowerCase();
        if (estadoVal === 'agotado' || estadoVal === 'no disponible') {
          svc.estadoClass = 'absolute top-2 left-2 text-xs font-medium px-2 py-1 rounded-full bg-red-100 text-red-800 border border-red-200';
        } else if (estadoVal === 'disponible' || estadoVal === 'activo' || estadoVal === 'available') {
          svc.estadoClass = 'absolute top-2 left-2 text-xs font-medium px-2 py-1 rounded-full bg-green-100 text-green-800 border border-green-200';
        } else {
          svc.estadoClass = 'absolute top-2 left-2 text-xs font-medium px-2 py-1 rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200';
        }

        servicios.push(svc);
      });
    } catch (e) {
      // Non-fatal: collection might not exist yet. We'll render the page regardless.
      console.debug('No se pudo leer colección servicios (puede no existir aún):', e.message || e);
    }

    // Load categories so we can show category names and populate selects in the template
    let categories = [];
    const categoriesMap = new Map();
    try {
      const catSnap = await db.collection('categorias').get();
      catSnap.forEach(cdoc => {
        const cd = cdoc.data() || {};
        const catObj = Object.assign({ id: cdoc.id }, cd);
        categories.push(catObj);
        categoriesMap.set(String(cdoc.id), catObj);
      });
    } catch (e) {
      // ignore: categories may not exist yet
    }

    // Normalize servicio objects to carry category id and resolved name
    servicios = servicios.map(svc => {
      // prefer an explicit categoriaId field, fall back to legacy `categoria` which may contain an id or a name
      const raw = svc.categoriaId || svc.categoria || svc.categoria_id || null;
      svc.categoria = raw || null; // this will be used in data-categoria (holds id when available)
      // resolve human-readable name
      let catName = null;
      if (svc.categoria && categoriesMap.has(String(svc.categoria))) {
        catName = categoriesMap.get(String(svc.categoria)).name || categoriesMap.get(String(svc.categoria)).title || null;
      }
      // fallback to any stored categoriaNombre or plain categoria string
      svc.categoriaNombre = catName || svc.categoriaNombre || (typeof svc.categoria === 'string' ? svc.categoria : null) || 'Sin categoría';
      return svc;
    });

    // Try to resolve assigned therapists for each service (optional enhancement)
    try {
      const therapistByService = new Map();
      const usersSnap = await db.collection('usuarios').get();
      usersSnap.forEach(uDoc => {
        const ud = uDoc.data() || {};
        const role = (ud.role || ud.rol || '').toString().toLowerCase();
        const assigned = ud.terapeuta_servicio || ud.servicioAsignado || null;
        if (role === 'terapeuta' && assigned) {
          const nameParts = [];
          if (ud.nombre) nameParts.push(ud.nombre);
          if (ud.apellido) nameParts.push(ud.apellido);
          const display = nameParts.length ? nameParts.join(' ') : (ud.correo || ud.email || uDoc.id);
          therapistByService.set(String(assigned), display);
        }
      });

      // attach therapist display name to corresponding servicio objects when available
      servicios.forEach(svc => {
        try { svc.therapist = therapistByService.get(String(svc.id)) || null; } catch (e) { svc.therapist = null; }
      });
    } catch (e) {
      // non-fatal; ignore
    }

    res.render('admin/servicios', { user: req.session.user, sections, active: 'servicios', servicios, categories });
  } catch (err) {
    console.error('Error rendering servicios page:', err.message || err);
    res.render('admin/servicios', { user: req.session.user, sections, active: 'servicios', servicios: [], categories: [], error: 'No se pudieron cargar los servicios' });
  }
});

// Admin categorias list (renders views/admin/categorias.hbs)
router.get('/admin/categorias', async (req, res) => {
  if (!req.session.user) return res.redirect('/');
  if (req.session.user.role !== 'admin') {
    if (req.session.user.role === 'secretario') return res.redirect('/dashboard_secretario');
    return res.redirect('/');
  }

  const sections = [
    { id: 'dashboard', title: 'Dashboard', href: '/admin/dashboard', iconClass: 'fa-solid fa-chart-simple' },
    { id: 'usuarios', title: 'Usuarios', href: '/admin/usuarios', iconClass: 'fa-solid fa-users' },
    { id: 'servicios', title: 'Servicios', href: '/admin/servicios', iconClass: 'fa-solid fa-concierge-bell' },
    { id: 'categorias', title: 'Categorías', href: '/admin/categorias', iconClass: 'fa-solid fa-tags' },
    { id: 'citas', title: 'Citas', href: '/admin/citas', iconClass: 'fa-solid fa-calendar-check' },
    { id: 'configuracion', title: 'Configuración', href: '/admin/configuracion', iconClass: 'fa-solid fa-cog' }
  ];

  try {
    let categories = [];
    try {
      const snap = await db.collection('categorias').get();
      snap.forEach(doc => {
        const d = doc.data() || {};
        categories.push(Object.assign({ id: doc.id }, d));
      });
    } catch (e) {
      console.debug('No se pudo leer colección categorias (puede no existir aún):', e && e.message ? e.message : e);
    }

    const totalCategories = Array.isArray(categories) ? categories.length : 0;
    return res.render('admin/categorias', { user: req.session.user, sections, active: 'categorias', categories, totalCategories });
  } catch (err) {
    console.error('Error rendering categorias page:', err && err.message ? err.message : err);
    return res.render('admin/categorias', { user: req.session.user, sections, active: 'categorias', categories: [], totalCategories: 0, error: 'No se pudo cargar la página de categorías' });
  }
});

// API: create categoria (admin)
router.post('/api/categorias', async (req, res) => {
  if (!req.session || !req.session.user) return res.status(401).json({ ok: false, error: 'No autorizado' });
  if (req.session.user.role !== 'admin') return res.status(403).json({ ok: false, error: 'Forbidden' });

  try {
    const { name, description, active } = req.body || {};
    const nombre = (name || '').toString().trim();
    if (!nombre) return res.status(400).json({ ok: false, error: 'El nombre de la categoría es requerido' });

    const dataToSave = {
      name: nombre,
      description: (typeof description !== 'undefined') ? description : null,
      active: (active === true || active === 'true' || active === 1 || active === '1') ? true : false,
      createdBy: req.session.user && (req.session.user.uid || req.session.user.id) || null,
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString()
    };

    const docRef = await db.collection('categorias').add(dataToSave);
    const savedSnap = await docRef.get();
    const saved = savedSnap.exists ? Object.assign({ id: docRef.id }, savedSnap.data()) : Object.assign({ id: docRef.id }, dataToSave);
    return res.status(201).json({ ok: true, id: docRef.id, data: saved });
  } catch (e) {
    console.error('Error creating categoria via /api/categorias:', e && e.message ? e.message : e);
    return res.status(500).json({ ok: false, error: 'Error creando categoría' });
  }
});


// Admin citas route (renders views/admin/citas.hbs)
router.get('/admin/citas', async (req, res) => {
  if (!req.session.user) return res.redirect('/');
  if (req.session.user.role !== 'admin') {
    if (req.session.user.role === 'secretario') return res.redirect('/dashboard_secretario');
    return res.redirect('/');
  }

  const sections = [
    { id: 'dashboard', title: 'Dashboard', href: '/admin/dashboard', iconClass: 'fa-solid fa-chart-simple' },
    { id: 'usuarios', title: 'Usuarios', href: '/admin/usuarios', iconClass: 'fa-solid fa-users' },
    { id: 'servicios', title: 'Servicios', href: '/admin/servicios', iconClass: 'fa-solid fa-concierge-bell' },
    { id: 'categorias', title: 'Categorías', href: '/admin/categorias', iconClass: 'fa-solid fa-tags' },
    { id: 'citas', title: 'Citas', href: '/admin/citas', iconClass: 'fa-solid fa-calendar-check' },
    { id: 'configuracion', title: 'Configuración', href: '/admin/configuracion', iconClass: 'fa-solid fa-cog' }
    
  ];

  try {
    // Optionally, you can fetch citas data here and pass to the template.
    // Build appointments in the exact shape the template expects: date, time, clientName, service, duration, phone, status, statusColor, therapist
    const appointments = [];
    try {
      const snap = await db.collection('citas').get();

      // collect service ids found in citas so we can resolve service labels/duration
      const serviceIds = new Set();
      const clientIds = new Set();
      const raw = [];
      snap.forEach(doc => {
        const d = doc.data() || {};
        raw.push({ id: doc.id, data: d });
        const svc = d.servicio || d.service || d.servicio_id || d.service_id || null;
        if (svc) serviceIds.add(svc);
        const cid = d.cliente_id || d.clienteId || d.cliente || null;
        if (cid) clientIds.add(cid);
      });

      // Fetch services for those ids
      const servicesMap = new Map();
      try {
        const svcPromises = [];
        for (const sid of serviceIds) {
          svcPromises.push(
            db.collection('servicios').doc(sid).get().then(snap => {
              if (snap && snap.exists) servicesMap.set(sid, snap.data());
            }).catch(() => {})
          );
        }
        await Promise.all(svcPromises);
      } catch (e) {
        // ignore
      }

      // Fetch therapists (users who have terapeuta_servicio) to map service -> therapist
      const therapistByService = new Map();
      try {
        const usersSnap = await db.collection('usuarios').get();
        usersSnap.forEach(uDoc => {
          const ud = uDoc.data() || {};
          const role = (ud.role || ud.rol || '').toString().toLowerCase();
          const assigned = ud.terapeuta_servicio || ud.servicioAsignado || null;
          if (role === 'terapeuta' && assigned) {
            const nameParts = [];
            if (ud.nombre) nameParts.push(ud.nombre);
            if (ud.apellido) nameParts.push(ud.apellido);
            const display = nameParts.length ? nameParts.join(' ') : (ud.correo || ud.email || uDoc.id);
            therapistByService.set(String(assigned), display);
          }
        });
      } catch (e) {
        // ignore
      }

      // Fetch clients by id (only those referenced) to show client names
      const clientsMap = new Map();
      try {
        const promises = [];
        for (const cid of clientIds) {
          promises.push(db.collection('usuarios').doc(cid).get().then(s => { if (s && s.exists) clientsMap.set(cid, s.data()); }).catch(() => {}));
        }
        await Promise.all(promises);
      } catch (e) {
        // ignore
      }

      // Build appointments
      raw.forEach(item => {
        const d = item.data || {};
        const date = d.fecha || d.date || null;
        const time = d.hora || d.time || null;

        // client name resolution
        let clientName = null;
        if (d.cliente) clientName = d.cliente;
        else if (d.cliente_id) {
          const stored = clientsMap.get(d.cliente_id) || null;
          if (stored) {
            const p = [];
            if (stored.nombre) p.push(stored.nombre);
            if (stored.apellido) p.push(stored.apellido);
            clientName = p.length ? p.join(' ') : (stored.correo || stored.email || d.cliente_id);
          } else {
            clientName = d.cliente_id;
          }
        }

        const svcId = d.servicio || d.service || d.servicio_id || d.service_id || null;
        let serviceLabel = svcId || null;
        let duration = d.duracion || d.duration || d.duracion_min || null;
        if (svcId && servicesMap.has(svcId)) {
          const s = servicesMap.get(svcId) || {};
          serviceLabel = s.servicio || s.nombre || serviceLabel;
          duration = duration || s.duracion || s.duration || 60;
        }

        // status color
        const estado = (d.estado || d.status || 'Pendiente').toString().toLowerCase();
        let statusColor = '#E0D5C5';
        if (estado === 'Pendiente' || estado === 'Pendiente') statusColor = '#F59E0B';
        else if (estado === 'confirmed' || estado === 'confirmada' || estado === 'confirmado') statusColor = '#10B981';
        else if (estado === 'cancelled' || estado === 'cancelada' || estado === 'cancelado') statusColor = '#EF4444';

        // phone fallback
        let phone = d.telefono || d.phone || null;

        // therapist: prefer explicit field on cita, otherwise map by service
        let therapist = d.terapeuta || d.terapeuta_servicio || null;
        if (!therapist && svcId && therapistByService.has(String(svcId))) therapist = therapistByService.get(String(svcId));

        appointments.push({
          id: item.id,
          date,
          time,
          clientName: clientName || null,
          service: serviceLabel || null,
          duration: duration || 60,
          phone: phone || null,
          status: estado,
          statusColor,
          therapist: therapist || null,
          raw: d
        });
      });
    } catch (e) {
      console.debug('No se pudo leer colección citas (puede no existir aún):', e.message || e);
    }

    res.render('admin/citas', { user: req.session.user, sections, active: 'citas', appointments });
  } catch (err) {
    console.error('Error rendering citas page:', err.message || err);
    res.render('admin/citas', { user: req.session.user, sections, active: 'citas', error: 'No se pudo cargar la página de citas' });
  }
});

// Extend /admin/citas rendering: pass full clients and services lists so the form can use them
// (backwards-compatible: this simply augments the existing route by reusing its logic)
router.get('/admin/citas', async (req, res, next) => {
  // This handler will not replace the previous one; if code reaches here it will run after the above.
  // To avoid double-rendering, delegate to next() so only the first handler renders. Keep for completeness.
  return next();
});

// POST /api/citas - crear cita (admin)
router.post('/api/citas', async (req, res) => {
  if (!req.session || !req.session.user) return res.status(401).json({ ok: false, error: 'No autorizado' });
  if (req.session.user.role !== 'admin' && req.session.user.role !== 'secretario') return res.status(403).json({ ok: false, error: 'Forbidden' });

  try {
  const body = req.body || {};
  // Support both JSON and form posts
  const fecha = body.date || body.fecha || null;
  const hora = body.time || body.hora || null;
  const clienteId = body.clienteId || body.cliente_id || null;
  const clienteName = body.clienteName || body.cliente || null; // guest name
  // Accept both english and spanish keys for service id/name
  const serviceId = body.serviceId || body.servicio || body.servicio_id || null;
  const serviceName = body.serviceName || body.service || body.servicio_nombre || null;
  const telefono = body.phone || body.telefono || null;
  const terapeuta = body.therapist || body.therapistName || body.terapeuta || null;
  const estado = body.status || body.estado || 'Pendiente';

    // minimal validation
    if (!fecha || !hora || !(clienteId || clienteName) || !(serviceId || serviceName)) {
      return res.status(400).json({ ok: false, error: 'Campos requeridos: date, time, cliente (id o nombre) y service (id o nombre)' });
    }

    const dataToSave = {
      fecha,
      hora,
      estado,
      creadoPor: req.session.user && (req.session.user.uid || req.session.user.id) || null,
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString()
    };

    if (clienteId) dataToSave.cliente_id = clienteId;
    if (clienteName && !clienteId) dataToSave.cliente = clienteName;
    if (serviceId) dataToSave.servicio = serviceId;
    if (serviceName && !serviceId) dataToSave.servicio_nombre = serviceName;
    if (telefono) dataToSave.telefono = telefono;
    if (terapeuta) dataToSave.terapeuta = terapeuta;

    const docRef = await db.collection('citas').add(dataToSave);
    const saved = await docRef.get();
    return res.status(201).json({ ok: true, id: docRef.id, data: saved.exists ? saved.data() : dataToSave });
  } catch (e) {
    console.error('Error creating cita via /api/citas:', e && e.message ? e.message : e);
    return res.status(500).json({ ok: false, error: 'Error creando cita' });
  }
});

//Configuracion
router.get('/admin/configuracion', async (req, res) => {
  if (!req.session.user) return res.redirect('/');
  if (req.session.user.role !== 'admin') {
    if (req.session.user.role === 'secretario') return res.redirect('/dashboard_secretario');
    return res.redirect('/');
  }
  const sections = [
    { id: 'dashboard', title: 'Dashboard', href: '/admin/dashboard', iconClass: 'fa-solid fa-chart-simple' },
    { id: 'usuarios', title: 'Usuarios', href: '/admin/usuarios', iconClass: 'fa-solid fa-users' },
    { id: 'servicios', title: 'Servicios', href: '/admin/servicios', iconClass: 'fa-solid fa-concierge-bell' },
    { id: 'categorias', title: 'Categorías', href: '/admin/categorias', iconClass: 'fa-solid fa-tags' },
    { id: 'citas', title: 'Citas', href: '/admin/citas', iconClass: 'fa-solid fa-calendar-check' },
    { id: 'configuracion', title: 'Configuración', href: '/admin/configuracion', iconClass: 'fa-solid fa-cog' }
  ];
  res.render('admin/configuracion', { user: req.session.user, sections, active: 'configuracion' });
});

// API: update current user's profile (nombre, apellido)
router.patch('/api/profile', async (req, res) => {
  if (!req.session || !req.session.user) return res.status(401).json({ ok: false, error: 'No autorizado' });

  const uid = req.session.user.uid || req.session.user.id || null;
  if (!uid) return res.status(400).json({ ok: false, error: 'UID de sesión no disponible' });

  const { firstName, lastName, nombre, apellido } = req.body || {};
  // support both english and spanish keys
  const nombreVal = (typeof firstName !== 'undefined') ? String(firstName).trim() : (typeof nombre !== 'undefined' ? String(nombre).trim() : undefined);
  const apellidoVal = (typeof lastName !== 'undefined') ? String(lastName).trim() : (typeof apellido !== 'undefined' ? String(apellido).trim() : undefined);

  if (typeof nombreVal === 'undefined' && typeof apellidoVal === 'undefined') {
    return res.status(400).json({ ok: false, error: 'No hay campos para actualizar' });
  }

  try {
    const docRef = db.collection('usuarios').doc(uid);
    const updateData = {};
    if (typeof nombreVal !== 'undefined') updateData.nombre = nombreVal || null;
    if (typeof apellidoVal !== 'undefined') updateData.apellido = apellidoVal || null;
    if (Object.keys(updateData).length) {
      updateData.updatedAt = new Date().toISOString();
      await docRef.set(updateData, { merge: true });
    }

    // update Firebase Auth displayName if admin SDK available
    try {
      if (auth && typeof auth.updateUser === 'function') {
        const displayName = `${updateData.nombre || req.session.user.nombre || ''} ${updateData.apellido || req.session.user.apellido || ''}`.trim();
        if (displayName) {
          await auth.updateUser(uid, { displayName });
        }
      }
    } catch (e) {
      console.warn('No se pudo actualizar displayName en Auth (non-fatal):', e && e.message ? e.message : e);
    }

    // Refresh session fields so templates see new name without requiring logout/login
    req.session.user = Object.assign({}, req.session.user, {
      nombre: (typeof updateData.nombre !== 'undefined') ? updateData.nombre : req.session.user.nombre,
      apellido: (typeof updateData.apellido !== 'undefined') ? updateData.apellido : req.session.user.apellido,
      displayName: `${(typeof updateData.nombre !== 'undefined' ? updateData.nombre : req.session.user.nombre) || ''} ${(typeof updateData.apellido !== 'undefined' ? updateData.apellido : req.session.user.apellido) || ''}`.trim()
    });

    const updatedSnap = await docRef.get();
    const updated = updatedSnap.exists ? Object.assign({ id: updatedSnap.id }, updatedSnap.data()) : null;
    return res.json({ ok: true, data: updated });
  } catch (e) {
    console.error('Error actualizando perfil usuario uid=' + uid + ':', e && e.message ? e.message : e);
    return res.status(500).json({ ok: false, error: 'Error actualizando perfil' });
  }
});

// Create a new user: create in Firebase Authentication and create a Firestore document using the auth uid
router.post('/admin/usuarios', async (req, res) => {
  if (!req.session.user) return res.status(401).json({ ok: false, error: 'No autorizado' });
  if (req.session.user.role !== 'admin') return res.status(403).json({ ok: false, error: 'Forbidden' });

  const { nombre, apellido, correo, contrasena, rol } = req.body || {};
  if (!correo || !contrasena) return res.status(400).json({ ok: false, error: 'Correo y contraseña son requeridos' });

  try {
    // Create user in Firebase Authentication (admin SDK)
    const userRecord = await auth.createUser({
      email: String(correo).toLowerCase(),
      password: String(contrasena),
      displayName: `${nombre || ''} ${apellido || ''}`.trim() || undefined,
    });

    const uid = userRecord.uid;

    // Optionally set a custom claim for role so auth-based checks can use it (non-blocking)
    if (rol) {
      try {
        await auth.setCustomUserClaims(uid, { role: String(rol).toLowerCase() });
      } catch (e) {
        // Non-fatal: log and continue
        console.error('Could not set custom user claims for uid=' + uid + ':', e.message || e);
      }
    }

    // Create Firestore document for the user using the auth uid as the doc id
    await db.collection('usuarios').doc(uid).set({
      nombre: nombre || null,
      apellido: apellido || null,
      correo: correo || null,
      rol: rol || null,
      createdAt: new Date().toISOString()
    });

    // Try to send the Firebase Authentication "verify email" template email.
    // Approach: sign in the newly created user via the REST API (using the provided password)
    // to obtain an idToken, then call accounts:sendOobCode with requestType=VERIFY_EMAIL.
    // This causes Firebase to send the verification email using the template configured
    // in the Firebase Console (instead of requiring us to send a custom email).
    try {
      if (FIREBASE_API_KEY && contrasena) {
        const signInResp = await fetch(`https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=${FIREBASE_API_KEY}`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ email: correo, password: contrasena, returnSecureToken: true })
        });

        const signInData = await signInResp.json();
        if (signInResp.ok && signInData && signInData.idToken) {
          // Request Firebase to send the verification email using the idToken
          const sendResp = await fetch(`https://identitytoolkit.googleapis.com/v1/accounts:sendOobCode?key=${FIREBASE_API_KEY}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ requestType: 'VERIFY_EMAIL', idToken: signInData.idToken })
          });
          const sendData = await sendResp.json();
          if (!sendResp.ok) {
            // Non-fatal: warn but don't fail user creation
            console.warn('Could not send verification email via Firebase template:', sendData);
          } else {
            console.log('Verification email sent (Firebase template) for:', correo);
          }
        } else {
          // sign-in failed unexpectedly; warn
          console.warn('Could not sign in newly created user to request verification email:', signInData);
        }
      }
    } catch (err) {
      // Do not block user creation on email send errors. Log for diagnostics.
      console.warn('Error while attempting to send Firebase verification email:', err && err.message ? err.message : err);
    }

    return res.json({ ok: true, uid });
  } catch (e) {
    console.error('Error creating new user:', e.message || e);
    return res.status(500).json({ ok: false, error: e.message || 'Error al crear usuario' });
  }
});

// Delete a user (Auth + Firestore)
router.delete('/admin/usuarios/:uid', async (req, res) => {
  if (!req.session.user) return res.status(401).json({ ok: false, error: 'No autorizado' });
  if (req.session.user.role !== 'admin') return res.status(403).json({ ok: false, error: 'Forbidden' });

  const uid = req.params.uid;
  if (!uid) return res.status(400).json({ ok: false, error: 'UID requerido' });

  try {
    // Try to delete from Firebase Auth (if admin SDK available)
    if (auth && typeof auth.deleteUser === 'function') {
      try {
        await auth.deleteUser(uid);
      } catch (e) {
        // If the uid is not a real auth user, log and continue to delete Firestore doc
        console.error('Error deleting user from Auth for uid=' + uid + ':', e.message || e);
      }
    }

    // Delete Firestore document under 'usuarios' collection
    try {
      await db.collection('usuarios').doc(uid).delete();
    } catch (e) {
      console.error('Error deleting Firestore doc for uid=' + uid + ':', e.message || e);
      // still return success if auth delete succeeded? We'll treat as error
      return res.status(500).json({ ok: false, error: 'Error al eliminar documento de usuario' });
    }

    return res.json({ ok: true });
  } catch (e) {
    console.error('Unexpected error deleting user uid=' + uid + ':', e.message || e);
    return res.status(500).json({ ok: false, error: e.message || 'Error al eliminar usuario' });
  }
});

// Update a user (Auth + Firestore)
router.put('/admin/usuarios/:uid', async (req, res) => {
  if (!req.session.user) return res.status(401).json({ ok: false, error: 'No autorizado' });
  if (req.session.user.role !== 'admin') return res.status(403).json({ ok: false, error: 'Forbidden' });

  const uid = req.params.uid;
  if (!uid) return res.status(400).json({ ok: false, error: 'UID requerido' });

  const { nombre, apellido, correo, rol, terapeuta_servicio } = req.body || {};

  try {
    // Update Auth user if auth available and correo or displayName changed
    if (auth && typeof auth.updateUser === 'function') {
      const updatePayload = {};
      if (correo) updatePayload.email = String(correo).toLowerCase();
      const displayName = `${nombre || ''} ${apellido || ''}`.trim();
      if (displayName) updatePayload.displayName = displayName;
      if (Object.keys(updatePayload).length) {
        try {
          await auth.updateUser(uid, updatePayload);
        } catch (e) {
          // If updateUser fails (for example email already in use), return error
          console.error('Error updating auth user for uid=' + uid + ':', e.message || e);
          return res.status(500).json({ ok: false, error: e.message || 'Error updating auth user' });
        }
      }

      // Optionally set custom claims when role provided
      if (rol) {
        try {
          await auth.setCustomUserClaims(uid, { role: String(rol).toLowerCase() });
        } catch (e) {
          // non-fatal: log and continue
          console.error('Could not set custom claims for uid=' + uid + ':', e.message || e);
        }
      }
    }

    // Update Firestore document (merge)
    const docRef = db.collection('usuarios').doc(uid);
    const docData = {};
  if (typeof nombre !== 'undefined') docData.nombre = nombre || null;
  if (typeof apellido !== 'undefined') docData.apellido = apellido || null;
  if (typeof correo !== 'undefined') docData.correo = correo || null;
  if (typeof rol !== 'undefined') docData.rol = rol || null;
  // Optional: store assigned service for therapists (design field)
  if (typeof terapeuta_servicio !== 'undefined') docData.terapeuta_servicio = terapeuta_servicio || null;

    if (Object.keys(docData).length) {
      await docRef.set(docData, { merge: true });
    }

    return res.json({ ok: true });
  } catch (e) {
    console.error('Error updating user uid=' + uid + ':', e.message || e);
    return res.status(500).json({ ok: false, error: e.message || 'Error al modificar usuario' });
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

    // Fallback: check possible Firestore collections/fields for a role and capture additional user fields
    let firestoreUserData = null;
    if (!role) {
      const collectionsToTry = ['users', 'usuarios'];
      const roleFields = ['role', 'rol'];
      for (const col of collectionsToTry) {
        try {
          const userDoc = await db.collection(col).doc(user.uid).get();
          if (userDoc.exists) {
            const data = userDoc.data();
            if (data) {
              // Save the firestore document data so we can pull name fields later
              firestoreUserData = data;
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
    } else {
      // If role was found earlier (e.g. custom claims), try to read Firestore doc for name fields as an extra convenience
      try {
        const userDoc = await db.collection('usuarios').doc(user.uid).get();
        if (userDoc.exists) firestoreUserData = userDoc.data();
      } catch (e) {
        // Non-fatal
      }
    }

    // Normalize role but DO NOT create the session yet. We'll determine finalRole
    // after fallback lookups and only create a session for allowed roles.
    const normalizedRole = role ? String(role).trim().toLowerCase() : null;
    const userInfo = {
      uid: user.uid,
      email: user.email,
      displayName: user.displayName,
      // Prefer Firestore 'nombre'/'apellido' fields if available
      nombre: firestoreUserData && firestoreUserData.nombre ? firestoreUserData.nombre : null,
      apellido: firestoreUserData && firestoreUserData.apellido ? firestoreUserData.apellido : null
    };

  //console.log('After initial login set - sessionID:', req.sessionID, 'initial role:', normalizedRole, 'session.user:', req.session.user);


    // Redirect based on explicit role. Deny-by-default when role is unknown.
    // If we don't have a role yet, try to find a Firestore document by email as a fallback.
    if (!normalizedRole) {
      try {
        const collectionsToTry2 = ['users', 'usuarios'];
        for (const col of collectionsToTry2) {
          try {
            // First try where queries on common fields
            let q = await db.collection(col).where('correo', '==', user.email).limit(1).get();
            if (q.empty) q = await db.collection(col).where('email', '==', user.email).limit(1).get();
            if (!q.empty) {
              const doc = q.docs[0];
              const data = doc.data();
              if (data) {
                firestoreUserData = data;
                for (const f of ['role', 'rol']) {
                  if (data[f]) {
                    role = String(data[f]).trim().toLowerCase();
                    //console.log('Fallback: found role from document', doc.id, 'in collection', col, 'role=', role);
                    break;
                  }
                }
              }
            }

            // Also try a doc id equal to email (some projects use email as doc id)
            if (!role) {
              try {
                const docById = await db.collection(col).doc(user.email).get();
                if (docById && docById.exists) {
                  const data = docById.data();
                  firestoreUserData = data;
                  for (const f of ['role', 'rol']) {
                    if (data && data[f]) {
                      role = String(data[f]).trim().toLowerCase();
                      //console.log('Fallback: found role by doc id in', col, 'doc=', user.email, 'role=', role);
                      break;
                    }
                  }
                }
              } catch (e) {
                // ignore doc by id lookup errors
              }
            }

            if (role) break;
          } catch (e) {
            //console.error(`Error querying ${col} by email during fallback role lookup:`, e.message || e);
          }
        }
      } catch (e) {
        // non-fatal
      }
    }

    // Recompute finalRole after fallback attempt
    const finalRole = role ? String(role).trim().toLowerCase() : null;

    // Only allow admin or secretario to log in. Others should see 'Usuario no encontrado'
    if (finalRole !== 'admin' && finalRole !== 'secretario') {
      // Do not create a session for unauthorized roles
      return res.render('index', { error: 'Usuario no encontrado' });
    }

    // Create session now that role is approved
    req.session.user = Object.assign({ role: finalRole }, userInfo);

    // Save session explicitly to ensure it persists before redirecting (some session stores are async)
    if (req.session && typeof req.session.save === 'function') {
      req.session.save((saveErr) => {
        if (saveErr) console.error('Session save error after login:', saveErr);
        if (finalRole === 'secretario') return res.redirect('/dashboard_secretario');
        if (finalRole === 'admin') return res.redirect('/admin/dashboard');
        return res.redirect('/');
      });
    } else {
      if (finalRole === 'secretario') return res.redirect('/dashboard_secretario');
      if (finalRole === 'admin') return res.redirect('/admin/dashboard');
      return res.redirect('/');
    }
  } catch (error) {
    res.render("index", { error: error.message });
  }
});

// Forgot password: render form
router.get('/forgot-password', (req, res) => {
  if (req.session && req.session.user) return res.redirect('/');
  return res.render('forgotpassword');
});

// Forgot password: handle POST and send password reset email via Firebase REST API
router.post('/forgot-password', async (req, res) => {
  const { correo, email } = req.body || {};
  const userEmail = (correo || email || '').toString().trim();
  if (!userEmail) return res.render('forgotpassword', { error: 'Por favor ingresa tu correo.' });

  try {
    const response = await fetch(`https://identitytoolkit.googleapis.com/v1/accounts:sendOobCode?key=${FIREBASE_API_KEY}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ requestType: 'PASSWORD_RESET', email: userEmail })
    });

    const data = await response.json();

    if (!response.ok) {
      // Pass back friendly messages when possible
      const errMessage = (data && data.error && data.error.message) ? data.error.message : 'No se pudo enviar el correo. Intenta de nuevo.';
      return res.render('forgotpassword', { error: errMessage });
    }

    return res.render('forgotpassword', { success: 'Se ha enviado un correo para restablecer la contraseña. Revisa tu bandeja de entrada.' });
  } catch (e) {
    console.error('Error sending password reset email:', e && e.message ? e.message : e);
    return res.render('forgotpassword', { error: 'Ocurrió un error inesperado. Intenta más tarde.' });
  }
});

// Secretary dashboard route
router.get('/dashboard_secretario', async (req, res) => {
  if (!req.session.user) return res.redirect('/');
  if (req.session.user.role !== 'secretario') return res.redirect('/dashboard');

  // Sidebar sections specifically for secretario (smaller set than admin)
  const sections = [
    { id: 'dashboard', title: 'Dashboard', href: '/dashboard_secretario', iconClass: 'fa-solid fa-chart-simple' },
    { id: 'citas', title: 'Citas', href: '/secretaria/citas', iconClass: 'fa-solid fa-calendar-check' },
    { id: 'servicios', title: 'Servicios', href: '/secretaria/servicios', iconClass: 'fa-solid fa-concierge-bell' },
    { id: 'configuracion', title: 'Configuración', href: '/secretaria/configuracion', iconClass: 'fa-solid fa-cog' }
  ];

  // Fetch servicios so the dashboard can show counts/initial data (non-fatal)
  let servicios = [];
  try {
    const snap = await db.collection('servicios').get();
    snap.forEach(doc => {
      const d = doc.data() || {};
      const svc = Object.assign({ id: doc.id }, d);
      normalizeService(svc, doc.id);
      servicios.push(svc);
    });
  } catch (e) {
    console.debug('No se pudo leer colección servicios para dashboard_secretario (puede no existir aún):', e && e.message ? e.message : e);
  }

  // Also fetch clientes (usuarios con rol 'cliente') so the dashboard can show the count
  let usuarios = [];
  try {
    const usersSnap = await db.collection('usuarios').get();
    usersSnap.forEach(doc => {
      const d = doc.data() || {};
      const r = (d.role || d.rol || '').toString().toLowerCase();
      if (r === 'cliente' || r === 'client') {
        usuarios.push({ id: doc.id, nombre: d.nombre || null, apellido: d.apellido || null, correo: d.correo || d.email || null, email: d.email || d.correo || null });
      }
    });
  } catch (e) {
    console.debug('No se pudo leer colección usuarios para dashboard_secretario (puede no existir aún):', e && e.message ? e.message : e);
  }

  // Render the secretaria dashboard view inside views/secretaria/
  res.render('secretaria/dashboard_secretario', { user: req.session.user, sections, active: 'dashboard', servicios, usuarios });
});

// Secretaria: citas page
router.get('/secretaria/citas', async (req, res) => {
  if (!req.session.user) return res.redirect('/');
  if (req.session.user.role !== 'secretario') return res.redirect('/dashboard');

  const sections = [
    { id: 'dashboard', title: 'Dashboard', href: '/dashboard_secretario', iconClass: 'fa-solid fa-chart-simple' },
    { id: 'citas', title: 'Citas', href: '/secretaria/citas', iconClass: 'fa-solid fa-calendar-check' },
    { id: 'servicios', title: 'Servicios', href: '/secretaria/servicios', iconClass: 'fa-solid fa-concierge-bell' },
    { id: 'configuracion', title: 'Configuración', href: '/secretaria/configuracion', iconClass: 'fa-solid fa-cog' }
  ];

  // Try to load servicios and clients for the secretaria UI (non-fatal)
  let servicios = [];
  let clients = [];
  try {
    const snap = await db.collection('servicios').get();
    snap.forEach(doc => {
      const d = doc.data() || {};
      const svc = Object.assign({ id: doc.id }, d);
      normalizeService(svc, doc.id);
      servicios.push(svc);
    });
  } catch (e) {
    console.debug('No se pudo leer servicios para /secretaria/citas:', e && e.message ? e.message : e);
  }

  try {
    const usersSnap = await db.collection('usuarios').get();
    usersSnap.forEach(doc => {
      const d = doc.data() || {};
      const r = (d.role || d.rol || '').toString().toLowerCase();
      if (r === 'cliente' || r === 'client') {
        const nameParts = [];
        if (d.nombre) nameParts.push(d.nombre);
        if (d.apellido) nameParts.push(d.apellido);
        const displayName = nameParts.length ? nameParts.join(' ') : (d.displayName || d.email || null);
        clients.push({ id: doc.id, name: displayName, phone: d.telefono || d.phone || null });
      }
    });
  } catch (e) {
    console.debug('No se pudo leer usuarios para /secretaria/citas:', e && e.message ? e.message : e);
  }

  // Render secretaria citas view
  // Build appointments for the secretaria view (same shape expected by template)
  const appointments = [];
  try {
    const snap = await db.collection('citas').get();
    const raw = [];
    const serviceIds = new Set();
    const clientIds = new Set();
    snap.forEach(doc => {
      const d = doc.data() || {};
      raw.push({ id: doc.id, data: d });
      const sid = d.servicio || d.service || d.servicio_id || d.service_id || null;
      if (sid) serviceIds.add(sid);
      const cid = d.cliente_id || d.clienteId || d.cliente || null;
      if (cid) clientIds.add(cid);
    });

    // fetch services
    const servicesMap = new Map();
    try {
      const promises = Array.from(serviceIds).map(sid => db.collection('servicios').doc(sid).get().then(s => { if (s && s.exists) servicesMap.set(sid, s.data()); }).catch(() => {}));
      await Promise.all(promises);
    } catch (e) {}

    // fetch therapists to map service->therapist
    const therapistByService = new Map();
    try {
      const usersSnap = await db.collection('usuarios').get();
      usersSnap.forEach(uDoc => {
        const ud = uDoc.data() || {};
        const role = (ud.role || ud.rol || '').toString().toLowerCase();
        const assigned = ud.terapeuta_servicio || ud.servicioAsignado || null;
        if (role === 'terapeuta' && assigned) {
          const nameParts = [];
          if (ud.nombre) nameParts.push(ud.nombre);
          if (ud.apellido) nameParts.push(ud.apellido);
          const display = nameParts.length ? nameParts.join(' ') : (ud.correo || ud.email || uDoc.id);
          therapistByService.set(String(assigned), display);
        }
      });
    } catch (e) {}

    // fetch clients referenced by id
    const clientsMap = new Map();
    try {
      const clientPromises = Array.from(clientIds).map(cid => db.collection('usuarios').doc(cid).get().then(s => { if (s && s.exists) clientsMap.set(cid, s.data()); }).catch(() => {}));
      await Promise.all(clientPromises);
    } catch (e) {}

    raw.forEach(item => {
      const d = item.data || {};
      const date = d.fecha || d.date || null;
      const time = d.hora || d.time || null;

      // client name resolution
      let clientName = null;
      if (d.cliente) clientName = d.cliente;
      else if (d.cliente_id) {
        const stored = clientsMap.get(d.cliente_id) || null;
        if (stored) {
          const p = [];
          if (stored.nombre) p.push(stored.nombre);
          if (stored.apellido) p.push(stored.apellido);
          clientName = p.length ? p.join(' ') : (stored.correo || stored.email || d.cliente_id);
        } else {
          clientName = d.cliente_id;
        }
      }

      const svcId = d.servicio || d.service || d.servicio_id || d.service_id || null;
      let serviceLabel = svcId || null;
      let duration = d.duracion || d.duration || d.duracion_min || null;
      if (svcId && servicesMap.has(svcId)) {
        const s = servicesMap.get(svcId) || {};
        serviceLabel = s.servicio || s.nombre || serviceLabel;
        duration = duration || s.duracion || s.duration || 60;
      }

      // status color
      const estadoRaw = d.estado || d.status || 'Pendiente';
      const estado = String(estadoRaw);
      const estLower = estado.toString().toLowerCase();
      let statusColor = '#E0D5C5';
      if (estLower.indexOf('pend') !== -1) statusColor = '#F59E0B';
      else if (estLower.indexOf('conf') !== -1 || estLower.indexOf('acept') !== -1) statusColor = '#10B981';
      else if (estLower.indexOf('cancel') !== -1 || estLower.indexOf('anul') !== -1 || estLower.indexOf('rech') !== -1) statusColor = '#EF4444';

      const phone = d.telefono || d.phone || null;
      let therapist = d.terapeuta || d.terapeuta_servicio || null;
      if (!therapist && svcId && therapistByService.has(String(svcId))) therapist = therapistByService.get(String(svcId));

      appointments.push({
        id: item.id,
        date,
        time,
        clientName: clientName || null,
        service: serviceLabel || null,
        duration: duration || 60,
        phone: phone || null,
        status: estado,
        statusColor,
        therapist: therapist || null,
        raw: d
      });
    });
  } catch (e) {
    console.debug('No se pudo leer colección citas para /secretaria/citas (puede no existir aún):', e && e.message ? e.message : e);
  }

  return res.render('secretaria/citas', { user: req.session.user, sections, active: 'citas', appointments, servicios, clients });
});

// Secretaria: servicios page
router.get('/secretaria/servicios', async (req, res) => {
  if (!req.session.user) return res.redirect('/');
  if (req.session.user.role !== 'secretario') return res.redirect('/dashboard');

  const sections = [
    { id: 'dashboard', title: 'Dashboard', href: '/dashboard_secretario', iconClass: 'fa-solid fa-chart-simple' },
    { id: 'citas', title: 'Citas', href: '/secretaria/citas', iconClass: 'fa-solid fa-calendar-check' },
    { id: 'servicios', title: 'Servicios', href: '/secretaria/servicios', iconClass: 'fa-solid fa-concierge-bell' },
    { id: 'configuracion', title: 'Configuración', href: '/secretaria/configuracion', iconClass: 'fa-solid fa-cog' }
  ];

  let servicios = [];
  try {
    const snap = await db.collection('servicios').get();
    snap.forEach(doc => {
      const d = doc.data() || {};
      const svc = Object.assign({ id: doc.id }, d);
      normalizeService(svc, doc.id);
      servicios.push(svc);
    });
  } catch (e) {
    console.debug('No se pudo leer servicios para /secretaria/servicios:', e && e.message ? e.message : e);
  }

  // Attempt to resolve therapist assigned to each service (optional)
  try {
    const therapistByService = new Map();
    const usersSnap = await db.collection('usuarios').get();
    usersSnap.forEach(uDoc => {
      const ud = uDoc.data() || {};
      const role = (ud.role || ud.rol || '').toString().toLowerCase();
      const assigned = ud.terapeuta_servicio || ud.servicioAsignado || null;
      if (role === 'terapeuta' && assigned) {
        const nameParts = [];
        if (ud.nombre) nameParts.push(ud.nombre);
        if (ud.apellido) nameParts.push(ud.apellido);
        const display = nameParts.length ? nameParts.join(' ') : (ud.correo || ud.email || uDoc.id);
        therapistByService.set(String(assigned), display);
      }
    });

    servicios.forEach(svc => { try { svc.therapist = therapistByService.get(String(svc.id)) || null; } catch(e){ svc.therapist = null; } });
  } catch (e) { /* ignore non-fatal */ }

  return res.render('secretaria/servicios', { user: req.session.user, sections, active: 'servicios', servicios });
});

// Configuración - secretaria (top-level route)
router.get('/secretaria/configuracion', async (req, res) => {
  if (!req.session.user) return res.redirect('/');
  if (req.session.user.role !== 'secretario') {
    if (req.session.user.role === 'admin') return res.redirect('/admin/dashboard');
    return res.redirect('/');
  }

  const sections = [
    { id: 'dashboard', title: 'Dashboard', href: '/dashboard_secretario', iconClass: 'fa-solid fa-chart-simple' },
    { id: 'citas', title: 'Citas', href: '/secretaria/citas', iconClass: 'fa-solid fa-calendar-check' },
    { id: 'servicios', title: 'Servicios', href: '/secretaria/servicios', iconClass: 'fa-solid fa-concierge-bell' },
    { id: 'configuracion', title: 'Configuración', href: '/secretaria/configuracion', iconClass: 'fa-solid fa-cog' }
  ];

  return res.render('secretaria/configuracion', { user: req.session.user, sections, active: 'configuracion' });
});
router.get("/logout", (req, res) => {
  // If there is no session, just redirect to home
  if (!req.session) return res.redirect('/');

  req.session.destroy((err) => {
    if (err) {
      console.error('Error destroying session during logout:', err);
      res.clearCookie && res.clearCookie('connect.sid');
      return res.status(500).render('index', { error: 'Error al cerrar sesión. Intenta de nuevo.' });
    }
    res.set('Cache-Control', 'no-store');
    res.clearCookie && res.clearCookie('connect.sid');
    return res.redirect('/');
  });
});

// GET /api/servicios
router.get('/api/servicios', async (req, res) => {
  try {
    let servicios = [];
    const snap = await db.collection('servicios').get();
    snap.forEach(doc => {
      const d = doc.data() || {};
      const svc = Object.assign({ id: doc.id }, d);
      normalizeService(svc, doc.id);
      // compute simple estadoClass value (same logic as admin route)
      const estadoVal = (svc.estado || svc.state || '').toString().toLowerCase();
      if (estadoVal === 'agotado' || estadoVal === 'no disponible') svc.estadoClass = 'agotado';
      else if (estadoVal === 'disponible' || estadoVal === 'activo' || estadoVal === 'available') svc.estadoClass = 'disponible';
      else svc.estadoClass = 'otro';
      servicios.push(svc);
    });
    return res.json({ ok: true, servicios });
  } catch (e) {
    console.error('Error fetching servicios for API:', e.message || e);
    return res.status(500).json({ ok: false, error: e.message || 'Error leyendo servicios' });
  }
});

// GET /api/servicios/activos
// Devuelve sólo los servicios cuyo estado se considera "activo".
router.get('/api/servicios/activos', async (req, res) => {
  try {
    const servicios = [];
    const snap = await db.collection('servicios').get();
    snap.forEach(doc => {
      const d = doc.data() || {};
      const svc = Object.assign({ id: doc.id }, d);
      normalizeService(svc, doc.id);
      const estadoVal = (svc.estado || svc.state || '').toString().toLowerCase();
      // Consider these values as active
      const isActive = estadoVal === 'activo' || estadoVal === 'disponible' || estadoVal === 'available';
      if (!isActive) return; // skip non-actives

      // compute a lightweight estadoClass similar to other endpoints
      if (estadoVal === 'agotado' || estadoVal === 'no disponible') svc.estadoClass = 'agotado';
      else if (estadoVal === 'disponible' || estadoVal === 'activo' || estadoVal === 'available') svc.estadoClass = 'disponible';
      else svc.estadoClass = 'otro';

      servicios.push(svc);
    });

    return res.json({ ok: true, servicios });
  } catch (e) {
    console.error('Error fetching active servicios for API:', e.message || e);
    return res.status(500).json({ ok: false, error: e.message || 'Error leyendo servicios activos' });
  }
});

// GET /api/clientes - devuelve usuarios con rol 'cliente'
router.get('/api/clientes', async (req, res) => {
  try {
    const clientes = [];
    const snap = await db.collection('usuarios').get();
    snap.forEach(doc => {
      const d = doc.data() || {};
      const rolVal = (d.role || d.rol || '').toString().toLowerCase();
      if (rolVal === 'cliente' || rolVal === 'client') {
        const nameParts = [];
        if (d.nombre) nameParts.push(d.nombre);
        if (d.apellido) nameParts.push(d.apellido);
        const displayName = nameParts.length ? nameParts.join(' ') : (d.nombre || d.displayName || d.email || null);
        clientes.push({ id: doc.id, name: displayName, phone: d.telefono || d.phone || null, email: d.correo || d.email || null });
      }
    });

    return res.json({ ok: true, clientes });
  } catch (e) {
    console.error('Error fetching clientes for API:', e && e.message ? e.message : e);
    return res.status(500).json({ ok: false, error: 'Error leyendo clientes' });
  }
});

// Create a new service: upload image to Storage, save data to Firestore
router.post('/api/servicios', upload.single('imagen'), async (req, res) => {
  try {
    // Extract form data (support both categoriaId and legacy categoria)
    const { servicio, descripcion, precio, estado } = req.body;
    const categoriaId = (req.body && (req.body.categoriaId || req.body.categoria)) || null;

    if (!servicio || !precio) {
      return res.status(400).json({ ok: false, error: 'El nombre del servicio y el precio son requeridos.' });
    }

    if (!req.file) {
      return res.status(400).json({ ok: false, error: 'La imagen del servicio es requerida.' });
    }

    // Get a reference to the default storage bucket
    const bucket = storage.bucket();
    
    // Create a unique filename
    const fileName = `servicios/${Date.now()}-${req.file.originalname}`;
    const file = bucket.file(fileName);

    // Create a writable stream and pipe the file buffer to it
    const stream = file.createWriteStream({
      metadata: {
        contentType: req.file.mimetype,
      },
      resumable: false
    });

    stream.on('error', (err) => {
      console.error('Error subiendo a Firebase Storage:', err);
      res.status(500).json({ ok: false, error: 'Error al subir la imagen.' });
    });

    stream.on('finish', async () => {
      try {
        // Make the file public
        await file.makePublic();

        // Get the public URL
        const imageUrl = file.publicUrl();

        // Resolve category name (if categoriaId provided)
        let categoriaNombre = null;
        if (categoriaId) {
          try {
            const catDoc = await db.collection('categorias').doc(String(categoriaId)).get();
            if (catDoc.exists) {
              const cd = catDoc.data() || {};
              categoriaNombre = cd.name || cd.title || null;
            }
          } catch (e) {
            // ignore lookup errors
          }
        }

        // Save service data to Firestore
        const servicioData = {
          servicio,
          descripcion: descripcion || null,
          precio: parseFloat(precio) || 0,
          estado: estado || 'disponible',
          categoriaId: categoriaId || null,
          categoriaNombre: categoriaNombre || null,
          imagen: imageUrl,
          createdAt: new Date().toISOString(),
          duracion: 60
        };

        const docRef = await db.collection('servicios').add(servicioData);

        res.status(201).json({ ok: true, id: docRef.id, data: servicioData });
      } catch (err) {
        console.error('Error guardando en Firestore o haciendo pública la imagen:', err);
        res.status(500).json({ ok: false, error: 'Error al guardar el servicio.' });
      }
    });

    stream.end(req.file.buffer);

  } catch (err) {
    console.error('Error en el endpoint /api/servicios:', err);
    res.status(500).json({ ok: false, error: 'Ocurrió un error inesperado.' });
  }
});

// Update a service: optionally upload new image and merge fields into Firestore doc
router.put('/api/servicios/:id', upload.single('imagen'), async (req, res) => {
  try {
    const id = req.params.id;
    if (!id) return res.status(400).json({ ok: false, error: 'ID de servicio requerido' });

    const { servicio, descripcion, precio, estado } = req.body || {};
    const categoriaId = (req.body && (req.body.categoriaId || req.body.categoria)) || null;

    const docRef = db.collection('servicios').doc(id);
    const docSnap = await docRef.get();
    if (!docSnap.exists) return res.status(404).json({ ok: false, error: 'Servicio no encontrado' });

    const updateData = {};
    if (typeof servicio !== 'undefined') updateData.servicio = servicio || null;
    if (typeof descripcion !== 'undefined') updateData.descripcion = descripcion || null;
    if (typeof precio !== 'undefined') updateData.precio = (precio === '' || precio === null) ? 0 : parseFloat(precio) || 0;
    if (typeof estado !== 'undefined') updateData.estado = estado || null;
    if (typeof categoriaId !== 'undefined') {
      updateData.categoriaId = categoriaId || null;
      // try to resolve name
      if (categoriaId) {
        try {
          const catDoc = await db.collection('categorias').doc(String(categoriaId)).get();
          if (catDoc.exists) {
            const cd = catDoc.data() || {};
            updateData.categoriaNombre = cd.name || cd.title || null;
          }
        } catch (e) {
          // ignore
        }
      } else {
        updateData.categoriaNombre = null;
      }
    }

    // handle image if provided
    if (req.file) {
      const bucket = storage.bucket();
      const fileName = `servicios/${Date.now()}-${req.file.originalname}`;
      const file = bucket.file(fileName);
      const stream = file.createWriteStream({ metadata: { contentType: req.file.mimetype }, resumable: false });
      await new Promise((resolve, reject) => {
        stream.on('error', (err) => reject(err));
        stream.on('finish', async () => {
          try {
            await file.makePublic();
            const imageUrl = file.publicUrl();
            updateData.imagen = imageUrl;
            resolve();
          } catch (err) { reject(err); }
        });
        stream.end(req.file.buffer);
      });
    }

    // merge update
    if (Object.keys(updateData).length) {
      await docRef.set(updateData, { merge: true });
    }

    // return updated doc data
    const updatedSnap = await docRef.get();
    const updated = updatedSnap.exists ? Object.assign({ id: updatedSnap.id }, updatedSnap.data()) : null;
    return res.json({ ok: true, id: id, data: updated });
  } catch (err) {
    console.error('Error updating servicio:', err.message || err);
    return res.status(500).json({ ok: false, error: err.message || 'Error actualizando servicio' });
  }
});

//eliminar /api/servicios/:id
router.delete('/api/servicios/:id', async (req, res) => {

  // require admin session for delete operations coming from admin UI
  if (!req.session || !req.session.user) return res.status(401).json({ ok: false, error: 'No autorizado' });
  if (req.session.user.role !== 'admin') return res.status(403).json({ ok: false, error: 'Forbidden' });

  const id = req.params.id;
  if (!id) return res.status(400).json({ ok: false, error: 'ID requerido' });

  try {
    const docRef = db.collection('servicios').doc(id);
    const snap = await docRef.get();
    if (!snap.exists) return res.status(404).json({ ok: false, error: 'Servicio no encontrado' });

    const data = snap.data() || {};
    const imageUrl = data.imagen || null;

    // Try to delete the image from Storage if present
    if (imageUrl && storage && typeof storage.bucket === 'function') {
      try {
        const bucket = storage.bucket();
        let filePath = null;
        try {
          const u = new URL(String(imageUrl));
          // storage.googleapis.com/<bucket>/<path>
          if (u.hostname === 'storage.googleapis.com') {
            filePath = u.pathname.replace('/' + bucket.name + '/', '');
          } else if (u.hostname === 'firebasestorage.googleapis.com') {
            // urls like /v0/b/<bucket>/o/<encodedPath>
            const parts = u.pathname.split('/');
            const oIdx = parts.indexOf('o');
            if (oIdx !== -1 && parts.length > oIdx + 1) {
              filePath = decodeURIComponent(parts[oIdx + 1]);
            }
          }
        } catch (e) {
          // fallback: try to extract after bucket name if present
          const bn = bucket && bucket.name ? bucket.name : null;
          if (bn && imageUrl.indexOf('/' + bn + '/') !== -1) {
            filePath = imageUrl.split('/' + bn + '/').pop().split('?')[0];
          }
        }

        if (filePath) {
          try {
            await bucket.file(filePath).delete();
          } catch (err) {
            // non-fatal - file may already be deleted or path may not match
            console.warn('Could not delete storage file for servicio id=' + id + ':', err && err.message ? err.message : err);
          }
        }
      } catch (err) {
        console.warn('Storage delete attempt failed for servicio id=' + id + ':', err && err.message ? err.message : err);
      }
    }

    // Delete Firestore document
    await docRef.delete();

    return res.json({ ok: true });
  } catch (e) {
    console.error('Error deleting servicio id=' + id + ':', e.message || e);
    return res.status(500).json({ ok: false, error: e.message || 'Error al eliminar servicio' });
  }
});

module.exports = router;

router.get('/admin/reports/pdf', async (req, res) => {
  if (!req.session || !req.session.user) return res.status(401).send('No autorizado');
  if (req.session.user.role !== 'admin') return res.status(403).send('Forbidden');

  try {
    // ----------------------
    // 1) GATHER DATA (igual lógica que ya tenías)
    // ----------------------
    let theDb = (req && req.app && typeof req.app.get === 'function') ? req.app.get('firestoreDb') : undefined;
    theDb = theDb || req.firestoreDB || db || (global && global.db);
    if (!theDb) {
      console.error('Firestore DB not found when generating PDF: req.app.get or module-scoped db missing');
      return res.status(500).send('Firestore DB no inicializada');
    }
    const usuariosSnap = await theDb.collection('usuarios').get();
    let clientesCount = 0;
    usuariosSnap.forEach(doc => {
      const d = doc.data() || {};
      const r = (d.role || d.rol || '').toString().toLowerCase();
      if (r === 'cliente' || r === 'client') clientesCount++;
    });

    const serviciosSnap = await theDb.collection('servicios').get();
    const servicios = [];
    serviciosSnap.forEach(doc => {
      const svc = Object.assign({ id: doc.id }, doc.data() || {});
      // normalizeService optional — si no la tienes, simple fallback
      if (typeof normalizeService === 'function') normalizeService(svc, doc.id);
      servicios.push(svc);
    });
    const serviciosCount = servicios.length;

    const citasSnap = await theDb.collection('citas').get();
    let pending = 0, confirmed = 0, canceled = 0, total = 0;
    const servicesMap = Object.create(null);
    const citasArray = []; // keep for charts grouping
    citasSnap.forEach(doc => {
      const d = doc.data() || {};
      total++;
      const estado = (d.estado || d.status || '').toString().toLowerCase();
      if (estado.indexOf('pend') !== -1) pending++;
      else if (estado.indexOf('conf') !== -1 || estado.indexOf('acept') !== -1) confirmed++;
      else if (estado.indexOf('cancel') !== -1 || estado.indexOf('anul') !== -1 || estado.indexOf('rech') !== -1) canceled++;

      const rawSvc = d.servicio || d.service || d.servicio_id || d.service_id || null;
      const altName = d.servicio_nombre || d.nombre_servicio || d.serviceName || d.servicioName || d.treatment || null;
      let key = 'Sin servicio';
      if (rawSvc) {
        const found = servicios.find(s => String(s.id) === String(rawSvc));
        if (found && (found.servicio || found.nombre)) key = found.servicio || found.nombre;
        else key = String(rawSvc);
      } else if (altName) {
        key = altName;
      }
      servicesMap[key] = (servicesMap[key] || 0) + 1;

      // keep original doc for chart grouping
      citasArray.push({ id: doc.id, data: d });
    });

    const topServices = Object.keys(servicesMap)
      .map(k => ({ name: k, count: servicesMap[k] }))
      .sort((a, b) => b.count - a.count)
      .slice(0, 20);

    // ----------------------
    // 2) PDF SETUP
    // ----------------------
    res.setHeader('Content-Type', 'application/pdf');
    res.setHeader('Content-Disposition', 'attachment; filename="informes_satori.pdf"');

    const doc = new PDFDocument({ size: 'A4', margin: 48, bufferPages: true });
    doc.pipe(res);

    // Colors (corporate derived palette)
    const PALETTE = {
      primary: '#1F3A2F',    // deep green
      accent: '#BFA07A',     // dorado suave
      cream: '#FBF7F2',      // fondo claro
      border: '#E7DCCB',
      text: '#1f2937',
      greenOK: '#10B981',
      orangeWarn: '#F59E0B',
      redBad: '#EF4444'
    };

    const pageInnerWidth = doc.page.width - doc.page.margins.left - doc.page.margins.right;
    const pageBottom = doc.page.height - doc.page.margins.bottom;

    // Logo path — intenta la ubicación normal, si no existe usa la ruta local del archivo subido.
    // Nota: el sistema transformará la ruta si es necesario (usamos la ruta vista en tu sesión).
    const logoPathPrimary = path.join(__dirname, '..', 'public', 'images', 'logo.png');
    // fallback: path del archivo que subiste (developer instruction). Usar exactamente la ruta que está en tu historial.
    const logoFallback = '/mnt/data/informes_satori (5).pdf'; // el sistema transformará a url si hace falta

    const logoPath = fs.existsSync(logoPathPrimary) ? logoPathPrimary : logoFallback;

    // Helper: safe header redraw on new page
    function ensureSpace(neededHeight) {
      if (doc.y + neededHeight > pageBottom - 40) {
        doc.addPage();
        drawHeader();
      }
    }

    // ----------------------
    // 3) HEADER & FOOTER
    // ----------------------
    function drawHeader() {
      const left = doc.page.margins.left;
      const top = doc.page.margins.top - 10;
      const headerH = 60;

      // background band
      doc.save();
      doc.rect(left - 10, top, pageInnerWidth + 20, headerH).fillAndStroke(PALETTE.cream, PALETTE.border);

      // logo (try/catch for safety)
      try {
        if (fs.existsSync(logoPathPrimary)) {
          // logo image available
          doc.image(logoPathPrimary, left + 8, top + 10, { width: 56, height: 40 });
        } else {
          // fallback: try to place whatever asset (if it's an image). If it's not an image it will be ignored by try/catch.
          doc.image(logoPath, left + 8, top + 10, { width: 56, height: 40 });
        }
      } catch (e) {
        // draw circular placeholder if logo fails
        doc.circle(left + 36, top + 30, 20).fill(PALETTE.primary);
      }

      // Title & subtitle
      const titleX = left + 80;
      doc.fillColor(PALETTE.primary).fontSize(16).font('Helvetica-Bold').text('Satori SPA', titleX, top + 12, { continued: false });
      doc.fontSize(9).font('Helvetica').fillColor(PALETTE.text).text('Informe ejecutivo — Resumen rápido', titleX, top + 32);

      // generated date (right aligned)
      const dateText = 'Generado: ' + new Date().toLocaleString('es-ES', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
      const dateW = doc.widthOfString(dateText, { size: 9 });
      doc.fontSize(9).fillColor('#666').text(dateText, left + pageInnerWidth - dateW, top + 34);

      doc.restore();
      doc.moveDown(2.2); // push cursor under header
    }

    // Footer draw (we will later fill page numbers using buffered pages)
    function drawFooter(pageIndex, pageCount) {
      const footerY = doc.page.height - doc.page.margins.bottom + 6;
      const textLeft = 'Satori SPA • Informe ejecutivo';
      const pageText = `Página ${pageIndex + 1} de ${pageCount}`;
      doc.fontSize(8).fillColor('#666').text(textLeft, doc.page.margins.left, footerY, { lineBreak: false });
      const pw = doc.widthOfString(pageText, { size: 8 });
      doc.text(pageText, doc.page.margins.left + pageInnerWidth - pw, footerY, { lineBreak: false });
    }

    // draw header on first page and future pages
    drawHeader();
    doc.on('pageAdded', () => {
      // ensure top margin & header repeated
      drawHeader();
    });

    // ----------------------
    // 4) SUMMARY CARDS (3 columns)
    // ----------------------
    const cardH = 74;
    const gap = 12;
    const cardW = Math.floor((pageInnerWidth - gap * 2) / 3);
    ensureSpace(cardH + 10);
    const startX = doc.page.margins.left;
    const startY = doc.y;

    function drawCard(x, y, w, h, title, value, accent) {
      // dynamic rounded rect (safe)
      try {
        doc.roundedRect(x, y, w, h, 8).fillAndStroke(PALETTE.cream, PALETTE.border);
      } catch (e) {
        doc.rect(x, y, w, h).fillAndStroke(PALETTE.cream, PALETTE.border);
      }
      doc.fillColor('#666').font('Helvetica').fontSize(9).text(title, x + 12, y + 10);
      doc.fillColor(accent || PALETTE.primary).font('Helvetica-Bold').fontSize(22).text(String(value), x + 12, y + 28);
    }

    drawCard(startX, startY, cardW, cardH, 'Clientes registrados', clientesCount, PALETTE.accent);
    drawCard(startX + cardW + gap, startY, cardW, cardH, 'Servicios', serviciosCount, PALETTE.primary);
    drawCard(startX + (cardW + gap) * 2, startY, cardW, cardH, 'Citas (total)', total, PALETTE.greenOK);

    // move cursor under cards
    doc.y = startY + cardH + 16;

    // ----------------------
    // 5) BADGES (Citas por estado)
    // ----------------------
    ensureSpace(48);
    // Center the section title horizontally on the page and split into two lines
    {
      const lines = ['Citas por estado', ''];
      doc.font('Helvetica-Bold').fontSize(12).fillColor(PALETTE.text);
      // find widest line to center
      const lineWidths = lines.map(l => doc.widthOfString(l, { size: 12, font: 'Helvetica-Bold' }));
      const maxW = Math.max.apply(null, lineWidths);
      const titleX = doc.page.margins.left + Math.floor((pageInnerWidth - maxW) / 2);
      const startY = doc.y;
      const lineH = 25; // approx line height for font size 12
      for (let i = 0; i < lines.length; i++) {
        doc.text(lines[i], titleX, startY + i * lineH, { lineBreak: false });
      }
      doc.moveDown(0.6);
    }
    const badgeY = doc.y;
    const badgeW = 150;
    const badgeH = 28;
    const badgesTotalW = badgeW * 3 + 16 * 2;
    let badgesX = doc.page.margins.left + Math.floor((pageInnerWidth - badgesTotalW) / 2);

    function drawBadge(x, y, w, h, label, count, bgColor) {
      try {
        doc.roundedRect(x, y, w, h, 6).fillAndStroke(bgColor, PALETTE.border);
      } catch (e) {
        doc.rect(x, y, w, h).fillAndStroke(bgColor, PALETTE.border);
      }
      doc.fillColor('#fff').font('Helvetica-Bold').fontSize(10).text(`${label}`, x + 12, y + 6, { continued: true });
      const cnt = String(count);
      const cntW = doc.widthOfString(cnt, { size: 10, font: 'Helvetica-Bold' });
      doc.text(cnt, x + w - 12 - cntW, y + 6);
    }

    drawBadge(badgesX, badgeY, badgeW, badgeH, 'Confirmadas', confirmed, PALETTE.greenOK);
    drawBadge(badgesX + badgeW + 16, badgeY, badgeW, badgeH, 'Pendientes', pending, PALETTE.orangeWarn);
    drawBadge(badgesX + (badgeW + 16) * 2, badgeY, badgeW, badgeH, 'Canceladas', canceled, PALETTE.redBad);

    // Add extra vertical spacing after the badges so subsequent chart cards
    // (and their titles/legends) do not overlap with the status boxes.
    // Increase the gap further to ensure the badges do not sit over the
    // top-right legend area of the second chart. If necessary we can
    // compute this dynamically based on chart header height, but a fixed
    // comfortable gap works for common page sizes.
    doc.y = badgeY + badgeH + 60;

    // ----------------------
    // 6) CHARTS (8 últimas semanas) - simplificado a barras internas sin librería
    // ----------------------
    // Prepare date buckets (last 8 Mondays)
    function getMonday(d) {
      const c = new Date(d);
      c.setHours(0, 0, 0, 0);
      const day = c.getDay();
      const diff = (day + 6) % 7;
      c.setDate(c.getDate() - diff);
      return c;
    }

    const now = new Date();
    const monday = getMonday(now);
    const msDay = 24 * 60 * 60 * 1000;
    const weeksStart = [];
    for (let i = 7; i >= 0; i--) {
      const s = new Date(monday);
      s.setDate(monday.getDate() - (i * 7));
      s.setHours(0, 0, 0, 0);
      weeksStart.push(s);
    }
    const labels = weeksStart.map(d => d.toISOString().slice(0, 10));
    const createdCounts = new Array(8).fill(0);
    const confirmedCounts = new Array(8).fill(0);
    const canceledCounts = new Array(8).fill(0);

    // aggregate using citasArray
    citasArray.forEach(item => {
      const d = item.data || {};
      const createdRaw = d.createdAt || d.created_at || d.created || d.updatedAt || null;
      let ts = null;
      if (createdRaw) {
        const parsed = Date.parse(createdRaw);
        if (!isNaN(parsed)) ts = parsed;
      }
      if (!ts) return;
      for (let i = 0; i < weeksStart.length; i++) {
        const start = weeksStart[i].getTime();
        const end = start + 7 * msDay;
        if (ts >= start && ts < end) {
          createdCounts[i] = (createdCounts[i] || 0) + 1;
          const estado = (d.estado || d.status || '').toString().toLowerCase();
          if (estado.indexOf('conf') !== -1 || estado.indexOf('acept') !== -1) confirmedCounts[i] = (confirmedCounts[i] || 0) + 1;
          if (estado.indexOf('cancel') !== -1 || estado.indexOf('anul') !== -1 || estado.indexOf('rech') !== -1) canceledCounts[i] = (canceledCounts[i] || 0) + 1;
          break;
        }
      }
    });

    // Draw two side-by-side small charts
    ensureSpace(160);
    // Start the charts a little lower than the current cursor to ensure
    // the top-right legend of the right chart doesn't get overlapped by
    // the badges drawn above. Increase if needed for very crowded layouts.
    const chartsY = doc.y + 20;
    const chartGap = 14;
    const chartWidth = Math.floor((pageInnerWidth - chartGap) / 2);
    const chartHeight = 130;

    function drawBarChartSimple(x, y, w, h, labels, seriesArray, title) {
      // container card
      try {
        doc.roundedRect(x, y, w, h, 6).fillAndStroke(PALETTE.cream, PALETTE.border);
      } catch (e) {
        doc.rect(x, y, w, h).fillAndStroke(PALETTE.cream, PALETTE.border);
      }
      doc.fillColor(PALETTE.primary).font('Helvetica-Bold').fontSize(11).text(title, x + 10, y + 8);

      const innerX = x + 10;
      const innerY = y + 30;
      const innerW = w - 20;
      const innerH = h - 46;

      // find max
      let maxVal = 1;
      seriesArray.forEach(s => s.data.forEach(v => { if (v > maxVal) maxVal = v; }));

      // draw grid lines
      doc.strokeColor('#eee').lineWidth(0.5);
      for (let gi = 0; gi <= 3; gi++) {
        const gy = innerY + Math.round(innerH * (gi / 3));
        doc.moveTo(innerX, gy).lineTo(innerX + innerW, gy).stroke();
      }

      const slotW = innerW / labels.length;
      const padding = 3;
      for (let i = 0; i < labels.length; i++) {
        const lx = innerX + i * slotW;
        const seriesCount = seriesArray.length;
        const bw = Math.max(4, Math.floor((slotW - padding * 2) / Math.max(1, seriesCount)) - 2);
        for (let sIdx = 0; sIdx < seriesCount; sIdx++) {
          const val = seriesArray[sIdx].data[i] || 0;
          const hPerc = val / maxVal;
          const barH = Math.round(hPerc * (innerH - 6));
          const barX = lx + padding + sIdx * (bw + 2);
          const barY = innerY + (innerH - barH);
          if (barH > 0) {
            doc.fillColor(seriesArray[sIdx].color || PALETTE.accent).rect(barX, barY, bw, barH).fill();
          }
        }
        // label small (show every other to reduce clutter)
        if (i % Math.ceil(labels.length / 6) === 0) {
          doc.fillColor('#666').font('Helvetica').fontSize(7).text(labels[i].slice(5), lx - (slotW/2 - 6), innerY + innerH + 4, {
            width: slotW,
            align: 'center'
          });
        }
      }

      // legend (right-top inside card)
      let lx = x + w - 10;
      let ly = y + 12;
      for (let s = seriesArray.length - 1; s >= 0; s--) {
        const sItem = seriesArray[s];
        try { doc.rect(lx - 10, ly - 3, 8, 8).fill(sItem.color || PALETTE.accent); } catch (e) {}
        doc.fillColor('#333').font('Helvetica').fontSize(9).text(sItem.label, lx - 2 - 70, ly - 6, { width: 70, align: 'right' });
        ly += 12;
      }
    }

    drawBarChartSimple(doc.page.margins.left, chartsY, chartWidth, chartHeight, labels, [{ label: 'Creadas', data: createdCounts, color: PALETTE.accent }], 'Citas creadas por semana');
    drawBarChartSimple(doc.page.margins.left + chartWidth + chartGap, chartsY, chartWidth, chartHeight, labels, [
      { label: 'Confirmadas', data: confirmedCounts, color: PALETTE.greenOK },
      { label: 'Canceladas', data: canceledCounts, color: PALETTE.redBad }
    ], 'Confirmadas vs Canceladas');

    doc.y = chartsY + chartHeight + 18;

    // ----------------------
    // 7) TOP SERVICES TABLE (dinámica con altura por fila)
    // ----------------------
    ensureSpace(100);
    // Center the table section title horizontally on the page and split into two lines
    {
      const lines = ['Servicios más solicitados', ''];
      doc.font('Helvetica-Bold').fontSize(12).fillColor(PALETTE.text);
      const lineWidths = lines.map(l => doc.widthOfString(l, { size: 12, font: 'Helvetica-Bold' }));
      const maxW = Math.max.apply(null, lineWidths);
      const titleX = doc.page.margins.left + Math.floor((pageInnerWidth - maxW) / 2);
      const startY = doc.y;
      const lineH = 16;
      for (let i = 0; i < lines.length; i++) {
        doc.text(lines[i], titleX, startY + i * lineH, { lineBreak: false });
      }
      doc.moveDown(0.6);
    }

    const tableX = doc.page.margins.left;
    let tableY = doc.y;
    // left column (table) and right column (privacy text)
    // Make left column narrower so the right column has more space for the privacy text
    const leftColW = Math.floor(pageInnerWidth * 0.5);
    const rightColGap = 12;
    const headerH = 22;

    // Privacy/footer text that will always be drawn below the table
    const footerText = `Este informe presenta un resumen ejecutivo. Para información detallada consulte el panel de administración o exporte datos desde las vistas correspondientes. ` +
      `Tratamos los datos personales contenidos en este documento conforme a nuestra Política de Privacidad: los datos de clientes y citas se almacenan y procesan únicamente para la prestación del servicio y la gestión administrativa. ` +
      `Para ejercer derechos de acceso, rectificación, supresión, oposición o portabilidad, o para obtener información adicional sobre el tratamiento, consulta la Política de Privacidad en el panel de administración o contacta con el responsable del tratamiento.`;

    // header row for table (inside left column)
    try {
      doc.roundedRect(tableX, tableY, leftColW, headerH, 6).fillAndStroke('#F3EFE8', PALETTE.border);
    } catch (e) {
      doc.rect(tableX, tableY, leftColW, headerH).fillAndStroke('#F3EFE8', PALETTE.border);
    }
    doc.fillColor('#222').font('Helvetica-Bold').fontSize(10).text('Servicio', tableX + 10, tableY + 6, { width: leftColW - 12 });
    doc.text('Veces', tableX + leftColW - 44, tableY + 6, { width: 34, align: 'right' });

    

    let ry = tableY + headerH;
    const paddingY = 8;
    for (let i = 0; i < topServices.length; i++) {
      const s = topServices[i];
      // compute dynamic row height based on service name length
      const textH = doc.heightOfString(s.name, { width: leftColW - 24, align: 'left', lineGap: 2 });
      const rowH = Math.max(22, textH + paddingY);

      // page break handling
      if (ry + rowH + 60 > pageBottom) {
        doc.addPage();
        // redraw header row on new page
        doc.fillColor(PALETTE.text).font('Helvetica-Bold').fontSize(12).text('Servicios más solicitados (cont.)');
        doc.moveDown(0.6);
        ry = doc.y;
        // draw table header on new page (left column)
        try {
          doc.roundedRect(tableX, ry, leftColW, headerH, 6).fillAndStroke('#F3EFE8', PALETTE.border);
        } catch (e) {
          doc.rect(tableX, ry, leftColW, headerH).fillAndStroke('#F3EFE8', PALETTE.border);
        }
        doc.fillColor('#222').font('Helvetica-Bold').fontSize(10).text('Servicio', tableX + 10, ry + 6, { width: leftColW - 12 });
        doc.text('Veces', tableX + leftColW - 44, ry + 6, { width: 34, align: 'right' });
        ry += headerH;

        // no inline privacy drawing here; footer will be drawn after the table
      }

      const bg = (i % 2 === 0) ? '#FFFFFF' : '#FBF9F6';
      try {
        doc.rect(tableX, ry, leftColW, rowH).fillAndStroke(bg, PALETTE.border);
      } catch (e) {
        doc.rect(tableX, ry, leftColW, rowH).fillAndStroke(bg, PALETTE.border);
      }

      doc.fillColor('#222').font('Helvetica').fontSize(10).text(s.name, tableX + 10, ry + 6, {
        width: leftColW - 20,
        align: 'left',
        lineGap: 2
      });
      doc.text(String(s.count), tableX + leftColW - 44, ry + 6, { width: 34, align: 'right' });

      ry += rowH;
    }

    // set doc.y after table end
    doc.y = ry + 12;

    // Draw privacy/footer text below the table (full width) but slightly shifted left
    ensureSpace(100);
    // Force ragged-right left alignment by using the page left margin and slightly smaller width
    const pageLeft = doc.page.margins.left;
    const footerX = pageLeft; // align to normal left page margin
    const footerW = Math.max(100, pageInnerWidth - 12); // keep a small right margin to avoid forced justification
    doc.fillColor('#666').font('Helvetica').fontSize(9).text(footerText, footerX, doc.y, {
      width: footerW,
      align: 'left',
      lineGap: 2
    });
    doc.moveDown(0.6);
    const policyText = 'Política de Privacidad disponible en: Panel de administración → Configuración. Contacto: privacidad@satori.example';
    doc.fillColor('#666').font('Helvetica').fontSize(8).text(policyText, footerX, doc.y, {
      width: footerW,
      align: 'left'
    });
    doc.moveDown(0.6);

    // ----------------------
    // 8) FOOTER NOTE & PRIVACY (handled above: either right-column or fallback below)
    // ----------------------

    // ----------------------
    // 9) ADD PAGE NUMBERS (buffered pages)
    // ----------------------
    const range = doc.bufferedPageRange(); // { start: 0, count: N }
    for (let i = range.start; i < range.start + range.count; i++) {
      doc.switchToPage(i);
      drawFooter(i, range.count);
    }

    // finalize
    doc.end();
  } catch (e) {
    console.error('Error generando PDF profesional:', e && e.message ? e.message : e);
    return res.status(500).send('Error generando PDF');
  }
});


// API: change password for current session user using Firebase Auth
router.patch('/api/change-password', async (req, res) => {
  if (!req.session || !req.session.user) return res.status(401).json({ ok: false, error: 'No autorizado' });

  const uid = req.session.user.uid || req.session.user.id || null;
  if (!uid) return res.status(400).json({ ok: false, error: 'UID de sesión no disponible' });

  const { currentPassword, newPassword } = req.body || {};
  if (!currentPassword || !newPassword) return res.status(400).json({ ok: false, error: 'Se requieren currentPassword y newPassword' });

  try {
    // Resolve email from session or auth
    let email = (req.session.user && req.session.user.email) ? req.session.user.email : null;
    if (!email) {
      try {
        const userRecord = await auth.getUser(uid);
        email = userRecord && userRecord.email ? userRecord.email : null;
      } catch (e) {
        // ignore - we'll fail later if email not available
      }
    }

    if (!email) return res.status(400).json({ ok: false, error: 'Email del usuario no disponible para verificar credenciales' });

    // Verify current password via Firebase REST signInWithPassword
    const verifyResp = await fetch(`https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=${FIREBASE_API_KEY}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: String(email), password: String(currentPassword), returnSecureToken: true })
    });

    const verifyData = await verifyResp.json();
    if (!verifyResp.ok) {
      // map common Firebase REST error codes/messages to Spanish
      const code = (verifyData && verifyData.error && verifyData.error.message) ? String(verifyData.error.message) : '';
      let userMsg = 'Credenciales inválidas.';
      switch (code) {
        case 'INVALID_PASSWORD':
          userMsg = 'Contraseña inválida.';
          break;
        case 'EMAIL_NOT_FOUND':
          userMsg = 'Correo no encontrado.';
          break;
        case 'USER_DISABLED':
          userMsg = 'Cuenta deshabilitada. Contacta al administrador.';
          break;
        case 'TOO_MANY_ATTEMPTS_TRY_LATER':
          userMsg = 'Demasiados intentos. Intenta más tarde.';
          break;
        default:
          // Some Firebase responses include more verbose messages; try to humanize common fragments
          if (code.indexOf('INVALID') !== -1) userMsg = 'Datos inválidos.';
          else if (code.indexOf('EXPIRED') !== -1) userMsg = 'Credencial expirada.';
          else userMsg = 'Credenciales inválidas.';
      }
      return res.status(400).json({ ok: false, error: userMsg });
    }

    // At this point current password is valid for that email. Proceed to update password using admin SDK
    try {
      await auth.updateUser(uid, { password: String(newPassword) });
      // Revoke refresh tokens so existing sessions require re-auth
      try { await auth.revokeRefreshTokens(uid); } catch (e) { /* non-fatal */ }
      return res.json({ ok: true });
    } catch (e) {
      console.error('Error updating user password for uid=' + uid + ':', e && e.message ? e.message : e);
      // Try to translate common admin SDK errors
      let errMsg = 'Error actualizando contraseña.';
      const code = e && e.code ? String(e.code) : '';
      const msg = e && e.message ? String(e.message) : '';
      if (code.indexOf('INVALID') !== -1 || msg.toLowerCase().indexOf('password') !== -1) {
        // e.g. invalid-password or message about length
        if (msg.match(/at least \d+/i)) {
          // extract number
          const m = msg.match(/at least (\d+)/i);
          if (m && m[1]) errMsg = 'La contraseña debe tener al menos ' + m[1] + ' caracteres.';
        } else {
          errMsg = 'Contraseña inválida o no cumple la política.';
        }
      } else if (code.indexOf('NOT_FOUND') !== -1) {
        errMsg = 'Usuario no encontrado.';
      }
      return res.status(500).json({ ok: false, error: errMsg });
    }
  } catch (e) {
    console.error('Error in /api/change-password:', e && e.message ? e.message : e);
    return res.status(500).json({ ok: false, error: 'Error verificando credenciales' });
  }
});

// GET /api/citas - devuelve las citas en formato JSON (appointments)
router.get('/api/citas', async (req, res) => {
  try {
    const appointments = [];
    try {
      const snap = await db.collection('citas').get();

      const serviceIds = new Set();
      const clientIds = new Set();
      const raw = [];
      snap.forEach(doc => {
        const d = doc.data() || {};
        raw.push({ id: doc.id, data: d });
        const svc = d.servicio || d.service || d.servicio_id || d.service_id || null;
        if (svc) serviceIds.add(svc);
        const cid = d.cliente_id || d.clienteId || d.cliente || null;
        if (cid) clientIds.add(cid);
      });

      // fetch services referenced
      const servicesMap = new Map();
      try {
        const svcPromises = Array.from(serviceIds).map(sid => db.collection('servicios').doc(sid).get().then(s => { if (s && s.exists) servicesMap.set(sid, s.data()); }).catch(() => {}));
        await Promise.all(svcPromises);
      } catch (e) {}

      // fetch therapists to map service -> therapist
      const therapistByService = new Map();
      try {
        const usersSnap = await db.collection('usuarios').get();
        usersSnap.forEach(uDoc => {
          const ud = uDoc.data() || {};
          const role = (ud.role || ud.rol || '').toString().toLowerCase();
          const assigned = ud.terapeuta_servicio || ud.servicioAsignado || null;
          if (role === 'terapeuta' && assigned) {
            const nameParts = [];
            if (ud.nombre) nameParts.push(ud.nombre);
            if (ud.apellido) nameParts.push(ud.apellido);
            const display = nameParts.length ? nameParts.join(' ') : (ud.correo || ud.email || uDoc.id);
            therapistByService.set(String(assigned), display);
          }
        });
      } catch (e) {}

      // fetch clients referenced
      const clientsMap = new Map();
      try {
        const clientPromises = Array.from(clientIds).map(cid => db.collection('usuarios').doc(cid).get().then(s => { if (s && s.exists) clientsMap.set(cid, s.data()); }).catch(() => {}));
        await Promise.all(clientPromises);
      } catch (e) {}

      // build appointments
      raw.forEach(item => {
        const d = item.data || {};
        const date = d.fecha || d.date || null;
        const time = d.hora || d.time || null;

        // client name resolution
        let clientName = null;
        if (d.cliente) clientName = d.cliente;
        else if (d.cliente_id) {
          const stored = clientsMap.get(d.cliente_id) || null;
          if (stored) {
            const p = [];
            if (stored.nombre) p.push(stored.nombre);
            if (stored.apellido) p.push(stored.apellido);
            clientName = p.length ? p.join(' ') : (stored.correo || stored.email || d.cliente_id);
          } else {
            clientName = d.cliente_id;
          }
        }

        const svcId = d.servicio || d.service || d.servicio_id || d.service_id || null;
        let serviceLabel = svcId || null;
        let duration = d.duracion || d.duration || d.duracion_min || null;
        if (svcId && servicesMap.has(svcId)) {
          const s = servicesMap.get(svcId) || {};
          serviceLabel = s.servicio || s.nombre || serviceLabel;
          duration = duration || s.duracion || s.duration || 60;
        }

        const estadoRaw = d.estado || d.status || 'Pendiente';
        const estado = String(estadoRaw);
        const estLower = estado.toString().toLowerCase();
        let statusColor = '#E0D5C5';
        if (estLower.indexOf('pend') !== -1) statusColor = '#F59E0B';
        else if (estLower.indexOf('conf') !== -1 || estLower.indexOf('acept') !== -1) statusColor = '#10B981';
        else if (estLower.indexOf('cancel') !== -1 || estLower.indexOf('anul') !== -1 || estLower.indexOf('rech') !== -1) statusColor = '#EF4444';

        const phone = d.telefono || d.phone || null;
        let therapist = d.terapeuta || d.terapeuta_servicio || null;
        if (!therapist && svcId && therapistByService.has(String(svcId))) therapist = therapistByService.get(String(svcId));

        appointments.push({
          id: item.id,
          date,
          time,
          clientName: clientName || null,
          service: serviceLabel || null,
          duration: duration || 60,
          phone: phone || null,
          status: estado,
          statusColor,
          therapist: therapist || null,
          raw: d
        });
      });
    } catch (e) {
      console.debug('No se pudo leer colección citas para API (puede no existir aún):', e && e.message ? e.message : e);
    }

    return res.json({ ok: true, appointments });
  } catch (err) {
    console.error('Error en /api/citas:', err && err.message ? err.message : err);
    return res.status(500).json({ ok: false, error: 'Error leyendo citas' });
  }
});

// GET /api/citas/:id - devuelve una cita por id
router.get('/api/citas/:id', async (req, res) => {
  try {
    const id = req.params.id;
    if (!id) return res.status(400).json({ ok: false, error: 'ID requerido' });
    const docRef = db.collection('citas').doc(id);
    const snap = await docRef.get();
    if (!snap.exists) return res.status(404).json({ ok: false, error: 'Cita no encontrada' });
    const d = snap.data() || {};

    // Normalize common fields so frontend can consume them easily
    const appointment = {
      id: snap.id,
      date: d.fecha || d.date || null,
      time: d.hora || d.time || null,
      telefono: d.telefono || d.phone || null,
      status: d.estado || d.status || null,
      clienteId: d.cliente_id || d.clienteId || null,
      clienteName: d.cliente || d.clienteName || null,
      serviceId: d.servicio || d.service || d.servicio_id || d.service_id || null,
      serviceName: d.servicio_nombre || d.serviceName || null,
      therapist: d.terapeuta || d.therapist || null,
      raw: d
    };

    return res.json({ ok: true, appointment });
  } catch (e) {
    console.error('Error en GET /api/citas/:id', e && e.message ? e.message : e);
    return res.status(500).json({ ok: false, error: 'Error leyendo la cita' });
  }
});

// PUT /api/citas/:id - actualizar una cita
router.put('/api/citas/:id', async (req, res) => {
  if (!req.session || !req.session.user) return res.status(401).json({ ok: false, error: 'No autorizado' });
  if (req.session.user.role !== 'admin' && req.session.user.role !== 'secretario') return res.status(403).json({ ok: false, error: 'Forbidden' });

  try {
    const id = req.params.id;
    if (!id) return res.status(400).json({ ok: false, error: 'ID requerido' });
    const body = req.body || {};

    const fecha = body.date || body.fecha || null;
    const hora = body.time || body.hora || null;
    const clienteId = body.clienteId || body.cliente_id || null;
    const clienteName = body.clienteName || body.cliente || null;
    const serviceId = body.serviceId || body.servicio || body.servicio_id || null;
    const serviceName = body.serviceName || body.servicio_nombre || null;
    const telefono = body.phone || body.telefono || null;
    const estado = body.status || body.estado || null;
    const terapeuta = body.therapist || body.terapeuta || null;

    const docRef = db.collection('citas').doc(id);
    const snap = await docRef.get();
    if (!snap.exists) return res.status(404).json({ ok: false, error: 'Cita no encontrada' });

    const updateData = {};
    if (fecha !== undefined && fecha !== null) updateData.fecha = fecha;
    if (hora !== undefined && hora !== null) updateData.hora = hora;
    if (telefono !== undefined) updateData.telefono = telefono || null;
    if (estado !== undefined && estado !== null) updateData.estado = estado;
    if (terapeuta !== undefined) updateData.terapeuta = terapeuta || null;
    if (clienteId) updateData.cliente_id = clienteId;
    else if (clienteName) updateData.cliente = clienteName;
    if (serviceId) updateData.servicio = serviceId;
    else if (serviceName) updateData.servicio_nombre = serviceName;

    if (Object.keys(updateData).length === 0) return res.status(400).json({ ok: false, error: 'No hay campos para actualizar' });
    updateData.updatedAt = new Date().toISOString();

    await docRef.set(updateData, { merge: true });
    const updatedSnap = await docRef.get();
    return res.json({ ok: true, id: id, data: updatedSnap.exists ? updatedSnap.data() : updateData });
  } catch (e) {
    console.error('Error en PUT /api/citas/:id', e && e.message ? e.message : e);
    return res.status(500).json({ ok: false, error: 'Error actualizando la cita' });
  }
});

// API: change email for current session user and send Firebase verification template
router.patch('/api/change-email', async (req, res) => {
  if (!req.session || !req.session.user) return res.status(401).json({ ok: false, error: 'No autorizado' });

  const uid = req.session.user.uid || req.session.user.id || null;
  if (!uid) return res.status(400).json({ ok: false, error: 'UID de sesión no disponible' });

  const { currentPassword, newEmail } = req.body || {};
  if (!currentPassword || !newEmail) return res.status(400).json({ ok: false, error: 'Se requieren contraseña actual y nuevo correo' });

  try {
    // Resolve current email from session or auth
    let email = (req.session.user && req.session.user.email) ? req.session.user.email : null;
    if (!email) {
      try { const userRecord = await auth.getUser(uid); email = userRecord && userRecord.email ? userRecord.email : null; } catch (e) {}
    }
    if (!email) return res.status(400).json({ ok: false, error: 'Email del usuario no disponible' });

    // Verify current password by signing in via REST to obtain idToken
    const signInResp = await fetch(`https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=${FIREBASE_API_KEY}`, {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: String(email), password: String(currentPassword), returnSecureToken: true })
    });
    const signInData = await signInResp.json();
    if (!signInResp.ok || !signInData || !signInData.idToken) {
      const code = (signInData && signInData.error && signInData.error.message) ? signInData.error.message : '';
      let userMsg = 'Credenciales inválidas.';
      if (code === 'INVALID_PASSWORD') userMsg = 'Contraseña inválida.';
      else if (code === 'EMAIL_NOT_FOUND') userMsg = 'Correo no encontrado.';
      return res.status(400).json({ ok: false, error: userMsg });
    }

    const idToken = signInData.idToken;

    // Use REST accounts:update to change the user's email using their idToken (works like client-side updateEmail)
    const updateResp = await fetch(`https://identitytoolkit.googleapis.com/v1/accounts:update?key=${FIREBASE_API_KEY}`, {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ idToken: idToken, email: String(newEmail), returnSecureToken: true })
    });
    const updateData = await updateResp.json();
    if (!updateResp.ok) {
      const code = (updateData && updateData.error && updateData.error.message) ? updateData.error.message : '';
      let userMsg = 'No se pudo actualizar el correo.';
      if (code.indexOf('EMAIL_EXISTS') !== -1) userMsg = 'El correo ya está en uso.';
      return res.status(400).json({ ok: false, error: userMsg });
    }

    // After updating email, request Firebase to send the verification email using the same idToken (accounts:sendOobCode)
    try {
      const sendResp = await fetch(`https://identitytoolkit.googleapis.com/v1/accounts:sendOobCode?key=${FIREBASE_API_KEY}`, {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ requestType: 'VERIFY_EMAIL', idToken: updateData.idToken || idToken })
      });
      const sendData = await sendResp.json();
      if (!sendResp.ok) {
        console.warn('Could not send verification email via Firebase template on change-email:', sendData);
      }
    } catch (err) {
      console.warn('Error sending verification email after email change:', err && err.message ? err.message : err);
    }

    // Update Firestore user document correo/email and session
    try {
      await db.collection('usuarios').doc(uid).set({ correo: String(newEmail), email: String(newEmail), updatedAt: new Date().toISOString() }, { merge: true });
    } catch (e) {
      console.warn('Could not update Firestore usuarios doc correo field (non-fatal):', e && e.message ? e.message : e);
    }

    // Update session so templates display new email
    req.session.user = Object.assign({}, req.session.user, { email: String(newEmail), correo: String(newEmail) });

    return res.json({ ok: true, email: String(newEmail) });
  } catch (e) {
    console.error('Error in /api/change-email:', e && e.message ? e.message : e);
    return res.status(500).json({ ok: false, error: 'Error cambiando el correo' });
  }
});

// API: delete categoria (admin)
router.delete('/api/categorias/:id', async (req, res) => {
  if (!req.session || !req.session.user) return res.status(401).json({ ok: false, error: 'No autorizado' });
  if (req.session.user.role !== 'admin') return res.status(403).json({ ok: false, error: 'Forbidden' });

  const id = req.params.id;
  if (!id) return res.status(400).json({ ok: false, error: 'ID requerido' });

  try {
    const docRef = db.collection('categorias').doc(id);
    const snap = await docRef.get();
    if (!snap.exists) return res.status(404).json({ ok: false, error: 'Categoría no encontrada' });
    await docRef.delete();
    return res.json({ ok: true });
  } catch (e) {
    console.error('Error deleting categoria via /api/categorias/:id:', e && e.message ? e.message : e);
    return res.status(500).json({ ok: false, error: 'Error eliminando categoría' });
  }
});

// API: update categoria (admin)
router.put('/api/categorias/:id', async (req, res) => {
  if (!req.session || !req.session.user) return res.status(401).json({ ok: false, error: 'No autorizado' });
  if (req.session.user.role !== 'admin') return res.status(403).json({ ok: false, error: 'Forbidden' });

  const id = req.params.id;
  if (!id) return res.status(400).json({ ok: false, error: 'ID requerido' });

  try {
    const { name, description, active } = req.body || {};
    const nombre = (name || '').toString().trim();
    if (!nombre) return res.status(400).json({ ok: false, error: 'El nombre es requerido' });

    const docRef = db.collection('categorias').doc(id);
    const snap = await docRef.get();
    if (!snap.exists) return res.status(404).json({ ok: false, error: 'Categoría no encontrada' });

    const updateData = {
      name: nombre,
      description: (typeof description !== 'undefined') ? description : null,
      active: (active === true || active === 'true' || active === 1 || active === '1') ? true : false,
      updatedAt: new Date().toISOString()
    };

    await docRef.update(updateData);
    const updatedSnap = await docRef.get();
    const updated = updatedSnap.exists ? Object.assign({ id: updatedSnap.id }, updatedSnap.data()) : null;
    return res.json({ ok: true, id: id, data: updated });
  } catch (e) {
    console.error('Error updating categoria via /api/categorias/:id:', e && e.message ? e.message : e);
    return res.status(500).json({ ok: false, error: 'Error actualizando categoría' });
  }
});