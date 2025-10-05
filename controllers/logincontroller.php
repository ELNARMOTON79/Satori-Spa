<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once '../models/contacto.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $correo = $_POST['email'] ?? '';
    $contrasena = $_POST['password'] ?? '';

    // Validate email
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        header('Location: ../index.php?error=invalid_email');
        exit;
    }

    // Validate password (e.g., 8-16 alphanumeric characters)
    if (!preg_match('/^[a-zA-Z0-9]{8,16}$/', $contrasena)) {
        header('Location: ../index.php?error=invalid_password');
        exit;
    }

    $Contacto = new Contacto();
    $usuario = $Contacto->login($correo, $contrasena);

    if ($usuario) {
        $_SESSION['user'] = $usuario['correo'];
        $_SESSION['user_name'] = $usuario['nombre']; // Asumiendo que la columna del nombre es 'nombre'
        header('Location: ../index.php?url=dashboard');
        exit;
    } else {
        header('Location: ../index.php?error=1');
        exit;
    }
} else {
    header('Location: ../index.php');
    exit;
}