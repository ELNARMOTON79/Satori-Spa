<?php

include_once __DIR__ . '/../config/database.php';

class Service extends Conexion {

    public function getServices() {
        $sql = "SELECT * FROM servicios ORDER BY id ASC";
        $conexion = $this->getConexion();
        if (!$conexion) {
            return [];
        }
        
        $resultado = pg_query($conexion, $sql);
        
        if ($resultado) {
            $services = pg_fetch_all($resultado);
            return $services ? $services : [];
        }
        
        return [];
    }

    public function createService($nombre, $descripcion, $precio) {
        $sql = "INSERT INTO servicios (nombre_servicio, descripcion, precio, fecha_creacion) VALUES ($1, $2, $3, NOW())";
        $conexion = $this->getConexion();
        if (!$conexion) {
            return false;
        }

        // Usar un nombre único para la sentencia preparada para evitar conflictos
        $stmt_name = "create_service_" . uniqid();
        $stmt = pg_prepare($conexion, $stmt_name, $sql);
        if (!$stmt) {
            return false;
        }

        $resultado = pg_execute($conexion, $stmt_name, array($nombre, $descripcion, $precio));

        return $resultado !== false;
    }

    public function updateService($id, $nombre, $descripcion, $precio) {
        $sql = 'UPDATE servicios SET nombre_servicio=$1, descripcion=$2, precio=$3 WHERE id=$4';
        $conexion = $this->getConexion();
        $stmt_name = "update_service_" . uniqid();
        $stmt = pg_prepare($conexion, $stmt_name, $sql);
        if (!$stmt) {
            return false;
        }
        $resultado = pg_execute($conexion, $stmt_name, array($nombre, $descripcion, $precio, $id));
        return $resultado !== false;
    }

    public function deleteService($id) {
        $sql = "DELETE FROM servicios WHERE id = $1";
        $conexion = $this->getConexion();
        $stmt_name = "delete_service_" . uniqid();
        $stmt = pg_prepare($conexion, $stmt_name, $sql);
        if (!$stmt) {
            return false;
        }
        $resultado = pg_execute($conexion, $stmt_name, array($id));
        return $resultado !== false;
    }
    public function getRecentServices() {
    $conexion = $this->getConexion();
    if (!$conexion) {
        return [];
    }

    $sql = "SELECT nombre_servicio AS descripcion, 
                   'servicio' AS tipo, 
                   NOW() AS fecha 
            FROM servicios 
            ORDER BY id DESC 
            LIMIT 20";

    $resultado = pg_query($conexion, $sql);
    if (!$resultado) {
        return [];
    }

    $data = [];
    while ($row = pg_fetch_assoc($resultado)) {
        $data[] = $row;
    }

    // No cerramos la conexión aquí
    return $data;
}


}
?>