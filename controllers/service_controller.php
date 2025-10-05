<?php

require_once __DIR__ . '/../models/service.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceModel = new Service();

    // Add service
    if (isset($_POST['addService'])) {
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        $precio = trim($_POST['precio']);

        if (empty($nombre) || empty($descripcion) || empty($precio)) {
            header('Location: ../index.php?url=servicios&error=campos_vacios');
            exit();
        }

        // Validation for special characters
        if (!preg_match('/^[a-zA-Z0-9\s.,]+$/', $nombre) || !preg_match('/^[a-zA-Z0-9\s.,]+$/', $descripcion)) {
            header('Location: ../index.php?url=servicios&error=caracteres_especiales');
            exit();
        }

        if (!is_numeric($precio) || $precio <= 0 || $precio > 99999.99) {
            header('Location: ../index.php?url=servicios&error=precio_invalido');
            exit();
        }

        $success = $serviceModel->createService($nombre, $descripcion, $precio);
        header('Location: ../index.php?url=servicios&created=' . ($success ? '1' : '0'));
        exit();
    }

    // Edit service
    if (isset($_POST['editService']) && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        $precio = trim($_POST['precio']);

        if (empty($nombre) || empty($descripcion) || empty($precio)) {
            header('Location: ../index.php?url=servicios&error=campos_vacios');
            exit();
        }

        // Validation for special characters
        if (!preg_match('/^[a-zA-Z0-9\s.,]+$/', $nombre) || !preg_match('/^[a-zA-Z0-9\s.,]+$/', $descripcion)) {
            header('Location: ../index.php?url=servicios&error=caracteres_especiales');
            exit();
        }

        if (!is_numeric($precio)) {
            header('Location: ../index.php?url=servicios&error=precio_invalido');
            exit();
        }

        $success = $serviceModel->updateService($id, $nombre, $descripcion, $precio);
        header('Location: ../index.php?url=servicios&updated=' . ($success ? '1' : '0'));
        exit();
    }

    // Delete service
    if (isset($_POST['deleteService']) && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $success = $serviceModel->deleteService($id);
        header('Location: ../index.php?url=servicios&deleted=' . ($success ? '1' : '0'));
        exit();
    }
}

// Redirect if accessed directly
header('Location: ../index.php?url=servicios');
exit();
?>