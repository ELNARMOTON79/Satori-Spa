<!-- filepath: c:\xampp\htdocs\Satori-Spa\views\user_view.php -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                                    onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?');">
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
                    <span class="absolute inset-y-0 right-3 top-7 flex items-center text-gray-400 cursor-pointer" onclick="togglePassword()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm-9 0a9 9 0 0118 0c-1.5 2.5-4.5 6-9 6s-7.5-3.5-9-6z" />
                        </svg>
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
            <div class="relative">
                <label for="edit_password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                <input type="password" id="edit_password" name="password" placeholder="Dejar en blanco para no cambiar" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
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
    const type = passwordInput.type === 'password' ? 'text' : 'password';
    passwordInput.type = type;
}
</script>