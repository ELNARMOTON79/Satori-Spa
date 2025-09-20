<?php
    include_once 'config/database.php';

    class Contacto extends Conexion{
        public function getAllUsers() {
            $this->sentencia = "SELECT nombre, apellido, correo, id_rol FROM usuarios";
            // obtener_sentencia() viene de la clase padre Conexion
            $users = $this->obtener_sentencia();
            return $users;
        }
    }