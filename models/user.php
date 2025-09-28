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

    public function deleteUser($id) {
        $sql = "DELETE FROM usuarios WHERE id = $1";
        $conexion = $this->getConexion();
        $stmt = pg_prepare($conexion, "delete_user_query", $sql);
        $resultado = pg_execute($conexion, "delete_user_query", array($id));
        return $resultado !== false;
    }

    public function updateUser($id, $nombre, $apellido, $correo, $password, $id_rol) {
        $conexion = $this->getConexion();
        if (!$conexion) {
            return false;
        }

        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET nombre=$1, apellido=$2, correo=$3, id_rol=$4, contrasena=$5 WHERE id=$6";
            $params = [$nombre, $apellido, $correo, $id_rol, $hashed_password, $id];
            $query_name = "update_user_with_pass_query";
        } else {
            $sql = "UPDATE usuarios SET nombre=$1, apellido=$2, correo=$3, id_rol=$4 WHERE id=$5";
            $params = [$nombre, $apellido, $correo, $id_rol, $id];
            $query_name = "update_user_without_pass_query";
        }

        $stmt = pg_prepare($conexion, $query_name, $sql);
        if (!$stmt) {
            // For debugging: error_log("Prepare failed: " . pg_last_error($conexion));
            return false;
        }

        $resultado = pg_execute($conexion, $query_name, $params);
        return $resultado !== false;
    }
}
?>

