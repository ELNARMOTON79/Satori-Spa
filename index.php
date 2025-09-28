<?php

session_start();

require_once 'config/database.php';
require_once 'models/contacto.php';
require_once 'models/user.php';

// Si no está logueado, mostrar login
if (!isset($_SESSION['user'])) {
    include 'views/login_view.php';
    exit;
}

// Basic router
$page = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'dashboard';
$page = filter_var($page, FILTER_SANITIZE_URL);
$url = explode('/', $page);

$data = []; // Inicializar el array de datos para las vistas
$content_view = 'views/dashboard_content.php'; // Vista por defecto

switch ($url[0]) {
    case 'dashboard':
        $content_view = 'views/dashboard_content.php';
        break;
    case 'usuarios':
        $userModel = new User();
        $data['users'] = $userModel->getUsers();
        $content_view = 'views/user_view.php';
        break;
    case 'servicios':
        require_once 'models/service.php';
        $serviceModel = new Service();
        $data['services'] = $serviceModel->getServices();
        $content_view = 'views/service_view.php';
        break;
}

// Permite usar las variables PHP dentro de la vista HTML
ob_start();
include 'views/admin_layout.php';
echo ob_get_clean();
?>
<!-- filepath: c:\xampp\htdocs\Satori-Spa\views\user_view.php -->
<div class="bg-white rounded-xl shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-[#5C4633]">Gestión de Usuarios</h2>
        <!-- Botón para abrir el modal de crear usuario (puedes implementar este modal aparte) -->
        <button onclick="openModal('userModal')" class="bg-amber-800 text-white px-4 py-2 rounded-lg hover:bg-amber-900">
            + Nuevo Usuario
        </button>
    </div>
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-amber-100 text-amber-900">
                <th class="p-3">Nombre</th>
                <th class="p-3">Apellido</th>
                <th class="p-3">Correo</th>
                <th class="p-3">Teléfono</th>
                <th class="p-3">Estado</th>
                <th class="p-3">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                <tr class="border-b">
                    <td class="p-3"><?= htmlspecialchars($user['nombre']) ?></td>
                    <td class="p-3"><?= htmlspecialchars($user['apellido']) ?></td>
                    <td class="p-3"><?= htmlspecialchars($user['correo']) ?></td>
                    <td class="p-3"><?= htmlspecialchars($user['telefono'] ?? '') ?></td>
                    <td class="p-3"><?= isset($user['estado']) && $user['estado'] == 1 ? 'Activo' : 'Inactivo' ?></td>
                    <td class="p-3 flex gap-2">
                        <!-- Botón Editar -->
                        <button class="text-amber-700" onclick="openEditModal(<?= htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8') ?>)">
                            Editar
                        </button>
                        <!-- Botón Eliminar -->
                        <form method="POST" action="controllers/user_controller.php" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?');" style="display:inline;">
                            <input type="hidden" name="deleteUser" value="1">
                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                            <button type="submit" class="text-red-600">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="p-3 text-center text-gray-500">No hay usuarios registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal de edición de usuario -->
<div id="editUserModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-xl p-8 w-full max-w-lg relative">
        <button onclick="closeEditModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            &times;
        </button>
        <h3 class="text-2xl font-bold text-[#5C4633] mb-4">Editar Usuario</h3>
        <form method="POST" action="controllers/user_controller.php" class="space-y-4">
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
            <div>
                <label for="edit_telefono" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                <input type="text" id="edit_telefono" name="telefono" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label for="edit_estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select id="edit_estado" name="estado" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            <div class="flex items-center justify-end gap-4 pt-4">
                <button type="button" onclick="closeEditModal()" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Cancelar</button>
                <button type="submit" class="px-6 py-2 bg-amber-800 text-white rounded-lg hover:bg-amber-900">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(user) {
    document.getElementById('edit_id').value = user.id;
    document.getElementById('edit_nombre').value = user.nombre;
    document.getElementById('edit_apellido').value = user.apellido;
    document.getElementById('edit_correo').value = user.correo;
    document.getElementById('edit_telefono').value = user.telefono || '';
    document.getElementById('edit_estado').value = user.estado !== undefined ? user.estado : 1;
    document.getElementById('editUserModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editUserModal').classList.add('hidden');
}
</script>