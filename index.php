<?php

require_once 'config/database.php';
require_once 'views/admin_view.html';

// Basic router
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'home';
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// You can expand this to load controllers and methods

$data = getDashboardData();

// Permite usar las variables PHP dentro de la vista HTML
ob_start();
include 'views/admin_view.html';
echo ob_get_clean();

echo "<pre>";
print_r($url);
echo "</pre>";

