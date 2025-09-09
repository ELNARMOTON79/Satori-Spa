<?php

require_once 'config/database.php';

// Basic router
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'home';
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// You can expand this to load controllers and methods

echo "<pre>";
print_r($url);
echo "</pre>";

