<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    include_once './models/database.php';
    include './views/login_view.html';

    if (isset($_POST['login'])){
        $correo = $_POST['email'];
        $contraseña = $_['password'];

        $contacto = new Contacto();
        $resultadousuario = $contacto->login($correo, $contraseña);

        if ($usuario){
            echo "Login Exitoso perrakito, " . $usuario[0] ['nombre'] . "!";
        } else {
            echo "Credenciales Incorrctas NEGRO.    Por favor, Intentalo de nuevo";
        }
    }