<?php

require_once 'config/database.php';
require_once 'models/contacto.php';
require_once 'controllers/UserController.php';

// Basic router
$page = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'dashboard';
$page = filter_var($page, FILTER_SANITIZE_URL);
$url = explode('/', $page);

$content_view = 'views/dashboard_content.php'; // Vista por defecto
$data = []; // Datos para la vista

switch ($url[0]) {
    case 'dashboard':
        // Aquí podrías cargar datos para el dashboard si fuera necesario
        $content_view = 'views/dashboard_content.php';
        break;
    case 'usuarios':
        $userController = new UserController();
        $data['users'] = $userController->getAllUsers();
        $content_view = 'views/user_view.php';
        break;
    case 'servicios':
        // Aún no hay lógica, solo cargamos una vista vacía.
        $content_view = 'views/service_view.php';
        break;
}

// Permite usar las variables PHP dentro de la vista HTML
ob_start();
include 'views/dashboard_layout.php';
echo ob_get_clean();
