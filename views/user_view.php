<!-- filepath: c:\xampp\htdocs\Satori-Spa\views\user_view.php -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<?php
$messages = [
    'created' => '<div class="auto-hide-alert bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                     <strong class="font-bold">¡Éxito!</strong>
                     <span class="block sm:inline">El usuario ha sido creado correctamente.</span>
                     <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display=\'none\';">
                         <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                     </span>
                   </div>',
    'updated' => '<div class="auto-hide-alert bg-green-200 border border-green-500 text-green-800 px-4 py-3 rounded relative mb-4" role="alert">
                     <strong class="font-bold">¡Éxito!</strong>
                     <span class="block sm:inline">El usuario ha sido actualizado correctamente.</span>
                     <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display=\'none\';">
                         <svg class="fill-current h-6 w-6 text-green-600" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                     </span>
                   </div>',
    'deleted' => '<div class="auto-hide-alert bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                     <strong class="font-bold">¡Éxito!</strong>
                     <span class="block sm:inline">El usuario ha sido eliminado correctamente.</span>
                     <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display=\'none\';">
                         <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                     </span>
                   </div>',
    'error'   => '<div class="auto-hide-alert bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                     <strong class="font-bold">¡Error!</strong>
                     <span class="block sm:inline">Ha ocurrido un error. Por favor, inténtelo de nuevo.</span>
                     <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display=\'none\';">
                         <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                     </span>
                   </div>'
];

foreach ($messages as $key => $message) {
    if (isset($_GET[$key])) {
        echo $message;
    }
}
?>

<div class="bg-white rounded-xl shadow p-6">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-[#5C4633]">Gestión de Usuarios</h2>
        <button onclick="openModal('userModal')" class="px-6 py-2 bg-[#5C4633] text-white rounded-lg hover:bg-[#4A3829] focus:outline-none flex items-center">
            <i class="fa-solid fa-plus mr-2"></i>
            Añadir Usuario
        </button>
    </div>

    <!-- Tabla de usuarios -->
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-[#F5EBDD] text-[#5C4633]">
                <tr>
                    <th class="p-3">Nombre</th>
                    <th class="p-3">Apellido</th>
                    <th class="p-3">Correo Electrónico</th>
                    <th class="p-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['users'])): ?>
                    <?php foreach ($data['users'] as $user): ?>
                        <tr class="border-b border-[#EAE3D9]">
                            <td class="p-3"><?= htmlspecialchars($user['nombre']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($user['apellido']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($user['correo']) ?></td>
                            <td class="p-3 flex gap-2">
                                <!-- Botón Editar -->
                                <button type="button" class="text-amber-700" title="Editar"
                                    onclick="openEditModal(<?= htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="fa-solid fa-pencil"></i>
                                </button>
                                <!-- Botón Eliminar -->
                                <form method="POST" action="controllers/user_controller.php" style="display:inline;" 
                                    onsubmit="event.preventDefault(); openConfirmationModal('¿Seguro que deseas eliminar este usuario?', () => this.submit());">
                                    <input type="hidden" name="deleteUser" value="1">
                                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                    <button type="submit" class="text-red-600" title="Eliminar" style="background:none;border:none;padding:0;">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="p-3 text-center text-gray-500">No se encontraron usuarios.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para añadir usuario -->
<div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center hidden">
    <div class="relative mx-auto p-8 border w-full max-w-lg shadow-lg rounded-xl bg-white">
        <div class="text-left">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-[#5C4633]">Nuevo Usuario</h3>
                <button onclick="closeModal('userModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-times fa-lg"></i>
                </button>
            </div>
            <form action="controllers/user_controller.php" method="POST" class="space-y-4 mt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Gerardo" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-300" required>
                    </div>
                    <div>
                        <label for="apellido" class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                        <input type="text" id="apellido" name="apellido" placeholder="Gutierrez" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-300" required>
                    </div>
                </div>
                <div>
                    <label for="correo" class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                    <input type="email" id="correo" name="correo" placeholder="GerardoGutierrez@ejemplo.com" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-300" required>
                </div>
                <div class="relative">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-300" required>
                    <span id="togglePasswordIcon" class="absolute inset-y-0 right-3 top-7 flex items-center text-gray-400 cursor-pointer" onclick="togglePassword()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a9.97 9.97 0 01-1.563 3.029m-5.858-.908l-4.243-4.243" /></svg>
                    </span>
                </div>
                <div>
                    <label for="id_rol" class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                    <select id="id_rol" name="id_rol" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-300" required>
                        <option value="" disabled selected>Seleccionar Rol</option>
                        <option value="1">Administrador</option>
                        <option value="2">Usuario</option>
                    </select>
                </div>
                <div class="flex items-center justify-end gap-4 pt-4">
                    <button type="button" onclick="closeModal('userModal')" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:outline-none">Cancelar</button>
                    <button type="submit" name="addUser" class="px-6 py-2 bg-[#5C4633] text-white rounded-lg hover:bg-[#4A3829] focus:outline-none">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de edición de usuario -->
<div id="editUserModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-xl p-8 w-full max-w-lg relative">
        <button onclick="closeEditModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            &times;
        </button>
        <h3 class="text-2xl font-bold text-[#5C4633] mb-4">Editar Usuario</h3>
        <form method="POST" action="controllers/user_controller.php" class="space-y-4" onsubmit="event.preventDefault(); openConfirmationModal('¿Estás seguro de que quieres guardar estos cambios?', () => this.submit());">
            <input type="hidden" name="editUser" value="1">
            <input type="hidden" name="id" id="edit_id">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="edit_nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" id="edit_nombre" name="nombre" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                </div>
                <div>
                    <label for="edit_apellido" class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                    <input type="text" id="edit_apellido" name="apellido" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                </div>
            </div>
            <div>
                <label for="edit_correo" class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                <input type="email" id="edit_correo" name="correo" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
            </div>
            <div class="relative">
                <label for="edit_password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                <input type="password" id="edit_password" name="password" placeholder="Dejar en blanco para no cambiar" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <span id="toggleEditPasswordIcon" class="absolute inset-y-0 right-3 top-7 flex items-center text-gray-400 cursor-pointer" onclick="toggleEditPassword()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a9.97 9.97 0 01-1.563 3.029m-5.858-.908l-4.243-4.243" /></svg>
                </span>
            </div>
            <div>
                <label for="edit_id_rol" class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                <select id="edit_id_rol" name="id_rol" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                    <option value="1">Administrador</option>
                    <option value="2">Usuario</option>
                </select>
            </div>
            <div class="flex items-center justify-end gap-4 pt-4">
                <button type="button" onclick="closeEditModal()" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Cancelar</button>
                <button type="submit" class="px-6 py-2 bg-[#5C4633] text-white rounded-lg hover:bg-[#4A3829]">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center hidden z-50">
    <div class="relative mx-auto p-8 border w-full max-w-md shadow-lg rounded-xl bg-white">
        <div class="text-center">
            <h3 id="confirmationMessage" class="text-lg font-medium text-gray-900"></h3>
            <div class="mt-4 flex justify-center gap-4">
                <button id="confirmButton" class="px-6 py-2 bg-red-600 text-white rounded-lg">Confirmar</button>
                <button id="cancelButton" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<script>
function openEditModal(user) {
    document.getElementById('edit_id').value = user.id;
    document.getElementById('edit_nombre').value = user.nombre;
    document.getElementById('edit_apellido').value = user.apellido;
    document.getElementById('edit_correo').value = user.correo;
    document.getElementById('edit_id_rol').value = user.id_rol;
    document.getElementById('edit_password').value = ''; // Clear password field
    document.getElementById('editUserModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editUserModal').classList.add('hidden');
}
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}
function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('togglePasswordIcon');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm-9 0a9 9 0 0118 0c-1.5 2.5-4.5 6-9 6s-7.5-3.5-9-6z" /></svg>`;
    } else {
        passwordInput.type = 'password';
        eyeIcon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a9.97 9.97 0 01-1.563 3.029m-5.858-.908l-4.243-4.243" /></svg>`;
    }
}

function toggleEditPassword() {
    const passwordInput = document.getElementById('edit_password');
    const eyeIcon = document.getElementById('toggleEditPasswordIcon');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm-9 0a9 9 0 0118 0c-1.5 2.5-4.5 6-9 6s-7.5-3.5-9-6z" /></svg>`;
    } else {
        passwordInput.type = 'password';
        eyeIcon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a9.97 9.97 0 01-1.563 3.029m-5.858-.908l-4.243-4.243" /></svg>`;
    }
}

function openConfirmationModal(message, onConfirm) {
    document.getElementById('confirmationMessage').textContent = message;
    document.getElementById('confirmationModal').classList.remove('hidden');

    document.getElementById('confirmButton').onclick = function() {
        onConfirm();
        closeConfirmationModal();
    };

    document.getElementById('cancelButton').onclick = function() {
        closeConfirmationModal();
    };
}

function closeConfirmationModal() {
    document.getElementById('confirmationModal').classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.auto-hide-alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.display = 'none';
        }, 5000);
    });
});
</script>