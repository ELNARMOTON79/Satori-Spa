<?php

session_start();

require_once 'config/database.php';
require_once 'models/contacto.php';

// Si no está logueado, mostrar login
if (!isset($_SESSION['user'])) {
    include 'views/login_view.php';
    exit;
}

// Basic router
$page = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'dashboard';
$page = filter_var($page, FILTER_SANITIZE_URL);
$url = explode('/', $page);

$content_view = 'views/dashboard_content.php'; // Vista por defecto

switch ($url[0]) {
    case 'dashboard':
        $content_view = 'views/dashboard_content.php';
        break;
    case 'usuarios':
        $content_view = 'views/user_view.php';
        break;
    case 'servicios':
        $content_view = 'views/service_view.php';
        break;
}

// Permite usar las variables PHP dentro de la vista HTML
ob_start();
include 'views/admin_layout.php';
echo ob_get_clean();