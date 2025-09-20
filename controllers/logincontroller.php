<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once '../models/contacto.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $correo = $_POST['email'] ?? '';
    $contrasena = $_POST['password'] ?? '';

    $Contacto = new Contacto();
    $usuario = $Contacto->login($correo, $contrasena);

    if ($usuario) {
        $_SESSION['user'] = $usuario['correo'];
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