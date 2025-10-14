const { db, auth } = require("../firebase");
const { Router } = require("express");
const router = Router();
const fetch = require('node-fetch');

// IMPORTANT: Replace with your Firebase Web API Key
const FIREBASE_API_KEY = 'AIzaSyAInkCQtGoouJ8Yn5eWQ70NnLXU-FvX-Jw';

router.get("/", (req, res) => {
  if (req.session.user) {
    return res.redirect("/dashboard");
  }
  res.render("index");
});

router.get("/dashboard", (req, res) => {
  if (!req.session.user) {
    return res.redirect("/");
  }
  // Only allow admin to access this view
  // Require explicit admin role; otherwise redirect appropriately
  if (req.session.user.role !== 'admin') {
    if (req.session.user.role === 'secretario') return res.redirect('/dashboard_secretario');
    return res.redirect('/');
  }
  res.render("dashboard", { user: req.session.user });
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
    req.session.user = {
      uid: user.uid,
      email: user.email,
      displayName: user.displayName,
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

// Secretary dashboard route
router.get('/dashboard_secretario', (req, res) => {
  if (!req.session.user) return res.redirect('/');
  if (req.session.user.role !== 'secretario') return res.redirect('/dashboard');
  res.render('dashboard_secretario', { user: req.session.user });
});

router.get("/logout", (req, res) => {
  req.session.destroy(() => {
    res.redirect("/");
  });
});

module.exports = router;