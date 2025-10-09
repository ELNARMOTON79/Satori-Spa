<?php
include_once __DIR__ . '/../models/user.php';
include_once __DIR__ . '/../models/service.php';

class DashboardController {
    private $userModel;
    private $serviceModel;

    public function __construct() {
        $this->userModel = new User();
        $this->serviceModel = new Service();
    }

    public function getRecentActivities() {
        // 🔹 Obtenemos los usuarios registrados recientemente
        $conexion = $this->userModel->getConexion();
        $usuariosArray = [];
        $sqlUsuarios = "
            SELECT 
                CONCAT('El usuario ', nombre, ' ', apellido, ' fue registrado.') AS descripcion,
                NOW() AS fecha,
                'Usuario' AS tipo
            FROM usuarios
            ORDER BY id DESC
            LIMIT 5
        ";
        $usuarios = pg_query($conexion, $sqlUsuarios);
        if ($usuarios) {
            while ($row = pg_fetch_assoc($usuarios)) {
                $usuariosArray[] = $row;
            }
        }

        // 🔹 Obtenemos los servicios creados recientemente
        $conexion2 = $this->serviceModel->getConexion();
        $serviciosArray = [];
        $sqlServicios = "
            SELECT 
                CONCAT('El servicio \"', nombre_servicio, '\" fue registrado.') AS descripcion,
                NOW() AS fecha,
                'Servicio' AS tipo
            FROM servicios
            ORDER BY id DESC
            LIMIT 5
        ";
        $servicios = pg_query($conexion2, $sqlServicios);
        if ($servicios) {
            while ($row = pg_fetch_assoc($servicios)) {
                $serviciosArray[] = $row;
            }
        }

        // 🔹 Unimos ambas listas y las ordenamos
        $actividades = array_merge($usuariosArray, $serviciosArray);
        usort($actividades, function ($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });

        return $actividades;
    }
}
?>
