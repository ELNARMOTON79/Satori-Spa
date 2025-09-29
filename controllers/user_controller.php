<?php

// Incluir el modelo de usuario para poder usarlo
require_once __DIR__ . '/../models/user.php';

// Verificar si la petición es de tipo POST y si se presionó el botón 'addUser'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addUser'])) {
    
    // 1. Recoger y limpiar los datos del formulario
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $correo = trim($_POST['correo']);
    $password = $_POST['password']; // La contraseña no se trimea
    $id_rol = $_POST['id_rol'];

    // 2. Validación básica de los datos
    if (empty($nombre) || empty($apellido) || empty($correo) || empty($password) || empty($id_rol)) {
        // Si algún campo está vacío, redirigir con un mensaje de error
        header('Location: ../index.php?url=usuarios&error=campos_vacios');
        exit();
    }

    // 3. Crear una instancia del modelo User
    $userModel = new User();

    // 4. Intentar crear el usuario en la base de datos
    $success = $userModel->createUser($nombre, $apellido, $correo, $password, $id_rol);

    // 5. Redirigir al usuario con un mensaje de éxito o error
    if ($success) {
        header('Location: ../index.php?url=usuarios&created=1');
        exit();
    } else {
        header('Location: ../index.php?url=usuarios&error=1');
        exit();
    }

} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userModel = new User();

    // Eliminar usuario
    if (isset($_POST['deleteUser']) && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $success = $userModel->deleteUser($id);
        header('Location: ../index.php?url=usuarios' . ($success ? '&deleted=1' : '&error=1'));
        exit();
    }

    // Editar usuario
    if (isset($_POST['editUser']) && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $nombre = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);
        $correo = trim($_POST['correo']);
        $password = $_POST['password']; // No trim, can be empty
        $id_rol = intval($_POST['id_rol']);
        
        // Validation
        if (empty($nombre) || empty($apellido) || empty($correo) || empty($id_rol)) {
            header('Location: ../index.php?url=usuarios&error=campos_vacios');
            exit();
        }

        $success = $userModel->updateUser($id, $nombre, $apellido, $correo, $password, $id_rol);
        header('Location: ../index.php?url=usuarios' . ($success ? '&updated=1' : '&error=1'));
        exit();
    }

} else {
    // Si alguien intenta acceder a este archivo directamente, redirigirlo
    header('Location: ../index.php?url=usuarios');
    exit();
}
