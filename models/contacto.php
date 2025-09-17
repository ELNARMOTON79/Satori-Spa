<?php
    include_once __DIR__ . '/../config/database.php';

    class Contacto extends Conexion {
        private $sentencia; // Declare the property to avoid dynamic property creation

        public function login($correo, $contrasena) {
            if (!function_exists('pg_escape_string')) {
                die("La extensión de PostgreSQL no está habilitada en PHP.");
            }

            // Escapar los parámetros para evitar inyección SQL
            $correo = pg_escape_string($this->conexion, $correo);
            $contrasena = pg_escape_string($this->conexion, $contrasena);

            // Consulta segura
            $this->sentencia = "SELECT * FROM usuarios WHERE correo = '$correo' AND contrasena = '$contrasena'";
            $resultado = pg_query($this->conexion, $this->sentencia);

            // Depuración: Verificar resultado
            if (!$resultado) {
                echo "Error en la consulta: " . pg_last_error($this->conexion);
                return false;
            }

            return pg_fetch_all($resultado);
        }
    }