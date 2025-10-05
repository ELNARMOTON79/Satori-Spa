<?php
require_once 'models/nfc_model.php';

class NfcController {
    private $model;

    public function __construct($conexion) {
        $this->model = new NfcModel($conexion);
    }

    public function index() {
        $usuarios = $this->model->obtenerUsuarios();
        require_once 'views/nfc_view.php';
    }

    public function vincular() {
        if (isset($_POST['id_usuario'])) {
            $token = $this->model->vincularUsuario($_POST['id_usuario']);
            if ($token) {
                $mensaje = "Token generado correctamente: $token";
            } else {
                $mensaje = "Error al generar el token.";
            }
        }
        $usuarios = $this->model->obtenerUsuarios();
        require_once 'views/nfc_view.php';
    }
}
?>
