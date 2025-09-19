<?php
include_once '../config/database.php';

class Contacto extends Conexion {
    public function login($correo, $contrasena) {
        $correo = pg_escape_string($correo);
        $contrasena = pg_escape_string($contrasena);
        $this->sentencia = "SELECT * FROM usuarios WHERE correo = '$correo' AND contrasena = '$contrasena'";
        $resultado = $this->obtener_sentencia();
        if ($resultado && pg_num_rows($resultado) == 1) {
            return pg_fetch_assoc($resultado);
        }
        return false;
    }
}