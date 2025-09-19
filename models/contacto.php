<?php
include_once 'config/database.php';

class Contacto extends Conexion{
    public function login($correo, $contraseña){
        $this->sentencia = "SELECT * FROM usuarios WHERE correo =  $1 '$correo' AND contraseña = '$contraseña'";
        $resultado = $this->obtener_sentencia();
        if (pg_num_rows($resultado) > 0) {
            return pg_fetch_all($resultado);
        } else {
            return false;
        }
}
}