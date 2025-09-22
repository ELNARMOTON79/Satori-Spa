<?php
    // Usamos __DIR__ para construir una ruta absoluta y robusta.
    // Esto asegura que el archivo de base de datos se encuentre sin importar desde dónde se incluya este modelo.
    include_once __DIR__ . '/../config/database.php';

    class Contacto extends Conexion{
        public function getAllUsers() {
            $this->sentencia = "SELECT nombre, apellido, correo, id_rol FROM usuarios";
            // obtener_sentencia() viene de la clase padre Conexion
            $users = $this->obtener_sentencia();
            return $users;
        }

       public function validateUser($email, $password) {
            // Usamos sentencias preparadas para prevenir inyección SQL.
            // La función se aplica a la columna, no al marcador de posición ($1).
            $this->sentencia = "SELECT * FROM usuarios WHERE LOWER(TRIM(correo)) = $1";
             
            // Pasamos el email ya procesado (minúsculas y sin espacios) como parámetro.
            $result = $this->obtener_sentencia_preparada([strtolower(trim($email))]);

            if (!empty($result)) {
                $user = $result[0]; 
                if (password_verify($password, $user['contrasena'])) {
                    return $user; 
                }
            }
            return false; // El usuario no existe o la contraseña es incorrecta.
        }
    }