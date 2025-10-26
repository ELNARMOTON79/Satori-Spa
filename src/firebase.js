require('dotenv').config();

const {initializeApp, applicationDefault} = require('firebase-admin/app');
const {getFirestore} = require('firebase-admin/firestore');
const {getAuth} = require('firebase-admin/auth');
const {getStorage} = require('firebase-admin/storage'); // Added

initializeApp({
  credential: applicationDefault(),
  storageBucket: process.env.FIREBASE_STORAGE_BUCKET // Added for Storage
});

const db = getFirestore();
const auth = getAuth();
const storage = getStorage(); // Added

module.exports = {
    db,
    auth,
    storage // Added
};