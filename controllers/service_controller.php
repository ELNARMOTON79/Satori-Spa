<?php
session_start();

require_once __DIR__ . '/../models/service.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceModel = new Service();

    // Add service
    if (isset($_POST['addService'])) {
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        $precio = trim($_POST['precio']);

        $errors = [];
        if (empty($nombre)) {
            $errors['nombre'] = 'El nombre es obligatorio.';
        } elseif (!preg_match('/^[a-zA-Z0-9\s.,áéíóúÁÉÍÓÚñÑ]+$/u', $nombre)) {
            $errors['nombre'] = 'El nombre solo puede contener letras, números y los siguientes caracteres: . ,';
        }

        if (empty($descripcion)) {
            $errors['descripcion'] = 'La descripción es obligatoria.';
        } elseif (!preg_match('/^[a-zA-Z\s_áéíóúÁÉÍÓÚñÑ]+$/u', $descripcion)) {
            $errors['descripcion'] = 'La descripción solo puede contener letras y espacios.';
        }

        if (empty($precio)) {
            $errors['precio'] = 'El precio es obligatorio.';
        } elseif (!filter_var($precio, FILTER_VALIDATE_FLOAT) || $precio <= 0) {
            $errors['precio'] = 'El precio debe ser un número positivo.';
        } elseif ($precio > 10000) {
            $errors['precio'] = 'El precio no puede exceder los $10,000.';
        }

        if (!empty($errors)) {
            $_SESSION['form_data'] = $_POST;
            $_SESSION['form_errors'] = $errors;
            $_SESSION['open_add_modal'] = true;
            header('Location: ../index.php?url=servicios');
            exit;
        }

        $success = $serviceModel->createService($nombre, $descripcion, $precio);
        $param = $success ? 'created=1' : 'error=1';
        header('Location: ../index.php?url=servicios&' . $param);
        exit;
    }

    // Edit service
    if (isset($_POST['editService']) && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        $precio = trim($_POST['precio']);

        $errors = [];
        if (empty($nombre)) {
            $errors['nombre'] = 'El nombre es obligatorio.';
        } elseif (!preg_match('/^[a-zA-Z0-9\s.,áéíóúÁÉÍÓÚñÑ]+$/u', $nombre)) {
            $errors['nombre'] = 'El nombre solo puede contener letras, números y los siguientes caracteres: . ,';
        }

        if (empty($descripcion)) {
            $errors['descripcion'] = 'La descripción es obligatoria.';
        } elseif (!preg_match('/^[a-zA-Z\s_áéíóúÁÉÍÓÚñÑ]+$/u', $descripcion)) {
            $errors['descripcion'] = 'La descripción solo puede contener letras y espacios.';
        }

        if (empty($precio)) {
            $errors['precio'] = 'El precio es obligatorio.';
        } elseif (!filter_var($precio, FILTER_VALIDATE_FLOAT) || $precio <= 0) {
            $errors['precio'] = 'El precio debe ser un número positivo.';
        } elseif ($precio > 10000) {
            $errors['precio'] = 'El precio no puede exceder los $10,000.';
        }

        if (!empty($errors)) {
            $_SESSION['edit_form_data'] = $_POST;
            $_SESSION['edit_form_errors'] = $errors;
            $_SESSION['open_edit_modal'] = $id;
            header('Location: ../index.php?url=servicios');
            exit;
        }

        $success = $serviceModel->updateService($id, $nombre, $descripcion, $precio);
        $param = $success ? 'updated=1' : 'error=1';
        header('Location: ../index.php?url=servicios&' . $param);
        exit;
    }

    // Delete service
    if (isset($_POST['deleteService']) && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $success = $serviceModel->deleteService($id);
        header('Location: ../index.php?url=servicios' . ($success ? '&deleted=1' : '&error=1'));
        exit();
    }
}

// Redirect if accessed directly
header('Location: ../index.php?url=servicios');
exit();
?>