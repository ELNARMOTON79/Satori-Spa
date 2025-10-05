<?php
session_start();

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

    // 2. Validación de los datos
    $errors = [];
    if (empty($nombre)) {
        $errors['nombre'] = 'El nombre es obligatorio.';
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $nombre)) {
        $errors['nombre'] = 'El nombre solo puede contener letras y espacios.';
    }

    if (empty($apellido)) {
        $errors['apellido'] = 'El apellido es obligatorio.';
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $apellido)) {
        $errors['apellido'] = 'El apellido solo puede contener letras y espacios.';
    }

    if (empty($correo)) {
        $errors['correo'] = 'El correo electrónico es obligatorio.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errors['correo'] = 'El formato del correo electrónico no es válido.';
    }

    if (empty($password)) {
        $errors['password'] = 'La contraseña es obligatoria.';
    } elseif (!preg_match('/^[a-zA-Z0-9]{8,16}$/', $password)) {
        $errors['password'] = 'La contraseña debe tener entre 8 y 16 caracteres alfanuméricos.';
    }

    if (empty($id_rol)) {
        $errors['id_rol'] = 'El rol es obligatorio.';
    } elseif (!filter_var($id_rol, FILTER_VALIDATE_INT)) {
        $errors['id_rol'] = 'El rol seleccionado no es válido.';
    }

    // Si hay errores, guardar los datos y errores en la sesión y redirigir
    if (!empty($errors)) {
        $_SESSION['form_data'] = $_POST;
        $_SESSION['form_errors'] = $errors;
        $_SESSION['open_add_modal'] = true; // Reabrir el modal en error de validación
        header('Location: ../index.php?url=usuarios');
        exit();
    }

    // 3. Crear una instancia del modelo User
    $userModel = new User();

    // 4. Intentar crear el usuario en la base de datos
    $success = $userModel->createUser($nombre, $apellido, $correo, $password, $id_rol);

    // 5. Redirigir al usuario con un mensaje de éxito o error
    if ($success) {
        unset($_SESSION['form_data']); // Limpiar datos del formulario en éxito
        header('Location: ../index.php?url=usuarios&created=1');
        exit();
    } else {
        // Si la creación falla (p.ej. correo duplicado), guardamos los datos
        // y errores en la sesión para repoblar el formulario.
        $_SESSION['form_data'] = $_POST;
        $_SESSION['form_errors']['general'] = 'No se pudo crear el usuario. El correo electrónico ya podría estar en uso.';
        $_SESSION['open_add_modal'] = true; // Reabrir el modal
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
        $errors = [];
        if (empty($nombre)) {
            $errors['nombre'] = 'El nombre es obligatorio.';
        } elseif (!preg_match('/^[a-zA-Z\s]+$/', $nombre)) {
            $errors['nombre'] = 'El nombre solo puede contener letras y espacios.';
        }

        if (empty($apellido)) {
            $errors['apellido'] = 'El apellido es obligatorio.';
        } elseif (!preg_match('/^[a-zA-Z\s]+$/', $apellido)) {
            $errors['apellido'] = 'El apellido solo puede contener letras y espacios.';
        }

        if (empty($correo)) {
            $errors['correo'] = 'El correo electrónico es obligatorio.';
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errors['correo'] = 'El formato del correo electrónico no es válido.';
        }

        if (!empty($password) && !preg_match('/^[a-zA-Z0-9]{8,16}$/', $password)) {
            $errors['password'] = 'La contraseña debe tener entre 8 y 16 caracteres alfanuméricos.';
        }

        if (empty($id_rol)) {
            $errors['id_rol'] = 'El rol es obligatorio.';
        } elseif (!filter_var($id_rol, FILTER_VALIDATE_INT)) {
            $errors['id_rol'] = 'El rol seleccionado no es válido.';
        }

        if (!empty($errors)) {
            $_SESSION['edit_form_data'] = $_POST;
            $_SESSION['edit_form_errors'] = $errors;
            $_SESSION['open_edit_modal'] = $id;
            header('Location: ../index.php?url=usuarios');
            exit();
        }

        $success = $userModel->updateUser($id, $nombre, $apellido, $correo, $password, $id_rol);
        if ($success && isset($_SESSION['user']) && $_SESSION['user'] === $correo) {
            $_SESSION['user_name'] = $nombre;
        }
        // Si la actualización falla (ej. correo duplicado), también mostramos un error.
        if ($success) {
            header('Location: ../index.php?url=usuarios&updated=1');
        } else {
            // Si la actualización falla (ej. correo duplicado), guardar datos y errores para reabrir el modal de edición
            $_SESSION['edit_form_data'] = $_POST;
            $_SESSION['edit_form_errors']['general'] = 'No se pudo actualizar el usuario. Es posible que el correo ya esté en uso.';
            $_SESSION['open_edit_modal'] = $id;
            header('Location: ../index.php?url=usuarios&error=1'); // Añadir &error=1 para la alerta global
        }
        exit();
    }

} else {
    // Si alguien intenta acceder a este archivo directamente, redirigirlo
    header('Location: ../index.php?url=usuarios');
    exit();
}
