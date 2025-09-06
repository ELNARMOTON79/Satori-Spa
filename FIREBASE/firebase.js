// Import the functions you need from the SDKs you need
import { initializeApp } from "https://www.gstatic.com/firebasejs/12.2.1/firebase-app.js";
import { getAnalytics } from "https://www.gstatic.com/firebasejs/12.2.1/firebase-analytics.js";
import { getFirestore } from "https://www.gstatic.com/firebasejs/12.2.1/firebase-firestore.js";
// TODO: Add SDKs for Firebase products that you want to use
// https://firebase.google.com/docs/web/setup#available-libraries

// Your web app's Firebase configuration
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
    apiKey: "AIzaSyCcL9DbMR1zGalRUkRg-J2wqxFtyQKY1x8",
    authDomain: "satori-spa.firebaseapp.com",
    projectId: "satori-spa",
    storageBucket: "satori-spa.firebasestorage.app",
    messagingSenderId: "177418537448",
    appId: "1:177418537448:web:6291974585d3a95722bc5d",
    measurementId: "G-40EK37NE8Q"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);
const db = getFirestore(app);

export { db };