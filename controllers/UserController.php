<?php

class UserController {

    private $userModel;

    public function __construct() {
        // Instanciamos el modelo en lugar de la conexión directa.
        $this->userModel = new Contacto();
    }

    public function getAllUsers() {
        // La lógica de la base de datos ahora está en el modelo.
        // El controlador solo le pide los datos.
        return $this->userModel->getAllUsers();
    }
}