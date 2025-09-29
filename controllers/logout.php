<?php
// 1. Inicia la sesión para poder acceder a ella.
session_start();

// 2. Elimina todas las variables de la sesión.
$_SESSION = array();

// 3. Si se está usando una cookie de sesión, se elimina.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Destruye la sesión por completo.
session_destroy();

// 5. Redirige al usuario a la página de login.
header('Location: ../index.php');
exit();