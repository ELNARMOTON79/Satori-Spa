<?php
    // Mostrar errores
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);


    if (isset($_POST['login'])) {
        $correo = $_POST['email'];
        $contrasena = $_POST['password'];

        // Depuración: Verificar datos recibidos
        echo "Datos recibidos: Correo: $correo, Contraseña: $contrasena";

        require_once './models/contacto.php';
        $contacto = new Contacto();
        $resultado = $contacto->login($correo, $contrasena);

        if ($resultado) {
            echo "<script>alert('Login exitoso. Bienvenido, " . $resultado[0]['nombre'] . "!');</script>";
            header('Location: index.php?url=dashboard');
        } else {
            echo "<script>alert('Credenciales inválidas. Por favor, inténtelo de nuevo.');</script>";
        }
    }