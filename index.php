<?php
  ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $url = isset($_GET['url']) ? $_GET['url'] : 'login_view';
    $url = filter_var($url, FILTER_SANITIZE_URL);
    $url = explode('/', $url);

    if($url[0] === 'login_view'){
        require_once 'views/login_view.php';
        exit;
    }   else if ($url[0] == 'dashboard') {
        require_once 'views/dashboard.php';
        exit;
    } 