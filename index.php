<?php

session_start();

// Headers para prevenir el caché del navegador
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

require_once 'config/database.php';
require_once 'models/contacto.php';
require_once 'models/user.php';

// Si no está logueado, mostrar login
if (!isset($_SESSION['user'])) {
    include 'views/login_view.php';
    exit;
}

// Basic router
$page = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'dashboard';
$page = filter_var($page, FILTER_SANITIZE_URL);
$url = explode('/', $page);

$data = []; // Inicializar el array de datos para las vistas
$content_view = 'views/dashboard_content.php'; // Vista por defecto

switch ($url[0]) {
    case 'dashboard':
        $content_view = 'views/dashboard_content.php';
        break;
    case 'usuarios':
        $userModel = new User();
        $data['users'] = $userModel->getUsers();
        $content_view = 'views/user_view.php';
        break;
    case 'servicios':
        require_once 'models/service.php';
        $serviceModel = new Service();
        $data['services'] = $serviceModel->getServices();
        $content_view = 'views/service_view.php';
        break;
    case 'nfc':
        require_once 'controllers/nfc_controller.php';
        $controller = new NfcController($conexion);
        $controller->index(); // Llamar al método para mostrar la vista
        exit; // Detener la ejecución para no cargar el layout principal

}

// Permite usar las variables PHP dentro de la vista HTML
ob_start();
include 'views/admin_layout.php';
echo ob_get_clean();
?>