<?php
// Iniciar la sesión es el primer paso y es crucial.
// Sin esto, el script no puede acceder a $_SESSION y no sabrá que el usuario ha iniciado sesión.
session_start();
require_once 'config/database.php';
require_once 'models/contacto.php';
require_once 'controllers/UserController.php';

// Basic router
$page = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'login';
$page = filter_var($page, FILTER_SANITIZE_URL);
$url = explode('/', $page);

// Si la URL es 'login', mostramos la vista de login y detenemos la ejecución.
// Esto evita que se cargue el layout del dashboard.
if ($url[0] == 'login') {
    include 'views/login_view.php';
    exit();
}

// A partir de aquí, todas las páginas requieren que el usuario haya iniciado sesión.
// Si la variable de sesión 'loggedin' no existe o no es verdadera, lo redirigimos al login.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php?url=login');
    exit();
}

// El resto del código solo se ejecutará si la URL NO es 'login'.
// Aquí es donde se manejan las vistas del panel de administración.

$content_view = 'views/dashboard_content.php'; // Vista por defecto para el dashboard
$data = []; // Datos para la vista

switch ($url[0]) {
    case 'dashboard':
        $content_view = 'views/dashboard_content.php';
        break;
    case 'usuarios':
        $userController = new UserController();
        $data['users'] = $userController->getAllUsers();
        $content_view = 'views/user_view.php';
        break;
    case 'servicios':
        $content_view = 'views/service_view.php';
        break;
}

// Permite usar las variables PHP dentro de la vista HTML
ob_start();
include 'views/dashboard_layout.php';
echo ob_get_clean();
