
<?php
define('BASE_URL', 'http://localhost:8080/Satori-Spa/');
require_once 'config/database.php';
// ... resto del código
?>

<?php
require_once 'config/database.php';


// Basic router
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'home';
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Determine controller and method
$controllerName = isset($url[0]) ? $url[0] : 'home';
$method = isset($url[1]) ? $url[1] : 'index';

// Controller file path
$controllerFile = "controllers/" . strtolower($controllerName) . "controller.php";

// Check if controller exists
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    
    // Create controller class name
    $controllerClass = ucfirst($controllerName) . 'Controller';
    
    // Instantiate controller
    if (class_exists($controllerClass)) {
        $controller = new $controllerClass();
        
        // Check if method exists
        if (method_exists($controller, $method)) {
            // Call controller method
            $controller->$method();
        } else {
            // Method not found
            echo "Error 404 - Método no encontrado: $method";
        }
    } else {
        echo "Error 404 - Clase no encontrada: $controllerClass";
    }
} else {
    // Controller not found - try to load view directly
    $viewFile = "views/{$controllerName}.php";
    $viewFileHtml = "views/{$controllerName}.html";
    $viewFileHtml2 = "views/{$controllerName}_view.html";
    
    if (file_exists($viewFile)) {
        require_once $viewFile;
    } else if (file_exists($viewFileHtml)) {
        require_once $viewFileHtml;
    } else if (file_exists($viewFileHtml2)) {
        require_once $viewFileHtml2;
    } else {
        // Show default home
        echo "<h1>Bienvenido a Satori Spa</h1>";
        echo "<p>Si ves este mensaje, el router funciona pero no encontró controlador ni vista.</p>";
        echo "<a href='http://localhost:8080/Satori-Spa/login/'>Ir al Login</a>";
    }
}
?>