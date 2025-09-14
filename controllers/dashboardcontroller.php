<?php
class DashboardController {
    public function index() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "login/");
            exit();
        }
        
        require_once 'views/dashboard.php';
    }
}
?>