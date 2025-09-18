<?php

class UserController {

    private $db;

    public function __construct() {
        $this->db = new Conexion();
    }

    public function getAllUsers() {
        // Es mejor ser explícito con las columnas que necesitas.
        // Asegúrate de que los nombres (id, nombre, etc.) coincidan con tu tabla en la BD.
        $this->db->sentencia = "SELECT id, nombre, apellido, correo, id_rol FROM usuarios ORDER BY id ASC";
        // Ahora obtener_sentencia() devuelve directamente el array de usuarios.
        $users = $this->db->obtener_sentencia();
        return $users;
    }
}