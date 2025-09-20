<?php
// 1. Inicia la sesión para poder acceder a ella.
session_start();

// 2. Elimina todas las variables de la sesión.
session_unset();

// 3. Destruye la sesión por completo.
session_destroy();

// 4. Redirige al usuario a la página de login.
header('Location: ../index.php');
exit();