<?php
// Es crucial iniciar la sesión en la primera línea para poder usar $_SESSION.
session_start();

// Incluimos los archivos necesarios. La ruta debe ser relativa a la ubicación de este controlador.
require_once '../models/contacto.php'; // El modelo ya se encarga de incluir la base de datos.

// Verificamos si el formulario fue enviado usando el método POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {

    // Obtenemos y saneamos el email y la contraseña del formulario.
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password']; // La contraseña no se sanea para la comparación directa.

    // Creamos una instancia de nuestro modelo.
    $userModel = new Contacto();

    // Llamamos al nuevo método para validar al usuario.
    $user = $userModel->validateUser($email, $password);

    if ($user) {
        // Si la validación es exitosa, el modelo devuelve los datos del usuario.
        // Guardamos la información del usuario en la sesión.
        $_SESSION['user'] = $user;
        $_SESSION['loggedin'] = true;

        // Redirigimos al usuario al dashboard.
        header('Location: ../index.php?url=dashboard');
        exit();
    } else {
        // Si la validación falla, redirigimos de vuelta al login con un parámetro de error.
        header('Location: ../index.php?url=login&error=1');
        exit();
    }
} else {
    // Si alguien intenta acceder a este archivo directamente, lo redirigimos al login.
    header('Location: ../index.php?url=login');
    exit();
}
