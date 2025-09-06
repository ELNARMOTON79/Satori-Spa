// BD/Conexion.js
import { db } from '../FIREBASE/firebase.js';
import { collection, getDocs } from "https://www.gstatic.com/firebasejs/12.2.1/firebase-firestore.js";

// Prueba de conexión: intenta leer una colección (puedes cambiar 'test' por una colección existente)
try {
        const querySnapshot = await getDocs(collection(db, "test"));
        console.log("Conexión exitosa a Firestore");
    } catch (error) {
        console.error("Error al conectar a Firestore:", error);
    }
    