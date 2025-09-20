<?php

include_once __DIR__ . '/../config/database.php';

class Contacto extends Conexion {
    public function login($correo, $contrasena) {
        // 1. Definir la consulta SQL para permitir solo el login de administradores (id_rol = 1)
        $sql = "SELECT * FROM usuarios WHERE correo = $1 AND id_rol = 1";

        // 2. Obtener la conexión a la base de datos
        $conexion = $this->getConexion();
        if (!$conexion) {
            // No se pudo obtener la conexión
            return false;
        }

        // 3. Preparar y ejecutar la consulta de forma segura
        // Usamos un nombre único para la consulta preparada para evitar conflictos
        $stmt = pg_prepare($conexion, "admin_login_query", $sql);
        $resultado = pg_execute($conexion, "admin_login_query", array($correo));

        // 4. Procesar el resultado
        if ($resultado && pg_num_rows($resultado) === 1) {
            $usuario = pg_fetch_assoc($resultado);
            
            // 5. Verificar que la contraseña proporcionada coincida con el hash en la BD
            if (password_verify($contrasena, $usuario['contrasena'])) {
                // ¡La contraseña es correcta!
                return $usuario;
            }
        }

        // Si el correo no existe o la contraseña es incorrecta
        return false;
    }
}