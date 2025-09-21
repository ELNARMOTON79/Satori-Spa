<?php

include_once __DIR__ . '/../config/database.php';

class User extends Conexion {

    /**
     * Obtiene todos los usuarios de la base de datos.
     * @return array Un array de usuarios o un array vacío si hay un error.
     */
    public function getUsers() {
        $sql = "SELECT * FROM usuarios ORDER BY id ASC";
        $conexion = $this->getConexion();
        if (!$conexion) {
            return [];
        }
        
        $resultado = pg_query($conexion, $sql);
        
        if ($resultado) {
            // pg_fetch_all devuelve un array de arrays asociativos
            $users = pg_fetch_all($resultado);
            return $users ? $users : []; // Devuelve los usuarios o un array vacío si no hay filas
        }
        
        return [];
    }

    /**
     * Crea un nuevo usuario en la base de datos.
     * @param string $nombre
     * @param string $apellido
     * @param string $correo
     * @param string $password
     * @param int $id_rol
     * @return bool True si se creó correctamente, False en caso de error.
     */
    public function createUser($nombre, $apellido, $correo, $password, $id_rol) {
        // Hashear la contraseña por seguridad
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nombre, apellido, correo, contrasena, id_rol) VALUES ($1, $2, $3, $4, $5)";
        
        $conexion = $this->getConexion();
        if (!$conexion) {
            return false;
        }

        // Preparar la consulta de forma segura
        $stmt = pg_prepare($conexion, "create_user_query", $sql);
        if (!$stmt) {
            // Opcional: puedes loggear el error para depuración
            // error_log("Error al preparar la consulta: " . pg_last_error($conexion));
            return false;
        }

        // Ejecutar la consulta con los valores
        $resultado = pg_execute($conexion, "create_user_query", array($nombre, $apellido, $correo, $hashed_password, $id_rol));

        return $resultado !== false;
    }
}
