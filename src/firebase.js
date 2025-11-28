require('dotenv').config();

const {initializeApp, applicationDefault} = require('firebase-admin/app');
const {getFirestore} = require('firebase-admin/firestore');
const {getAuth} = require('firebase-admin/auth');
const {getStorage} = require('firebase-admin/storage');

initializeApp({
  credential: applicationDefault(),
  storageBucket: 'satori-spa-ea79d.firebasestorage.app' // Reemplaza esto con el URL de tu bucket
});

const db = getFirestore();
const auth = getAuth();
const storage = getStorage();

module.exports = {
    db,
    auth,
    storage
};