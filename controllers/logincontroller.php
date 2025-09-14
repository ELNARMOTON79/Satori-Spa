<?php
require_once 'models/UserModel.php';

class LoginController {
    private $userModel;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->userModel = new UserModel($db);
    }

    public function index() {
        // Mostrar formulario de login
        require_once 'views/login_view.php';
    }

    public function auth() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);

            // Validar campos
            if (empty($email) || empty($password)) {
                $this->showError("Todos los campos son obligatorios");
                return;
            }

            // Buscar usuario
            $user = $this->userModel->getUserByEmail($email);
            
            if ($user && $this->userModel->verifyPassword($password, $user['password'])) {
                // Login exitoso
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['nombre'];
                
                header("Location: " . BASE_URL . "dashboard");
                exit();
            } else {
                $this->showError("Credenciales incorrectas");
            }
        }
    }

    private function showError($message) {
        echo "<script>alert('Error: $message'); window.location.href = '" . BASE_URL . "login/';</script>";
    }

    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        header("Location: " . BASE_URL . "login/");
        exit();
    }
}
?>