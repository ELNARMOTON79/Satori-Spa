<!-- filepath: c:\xampp\htdocs\Satori-Spa\views\service_view.php -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<?php
if (isset($_GET['created']) && $_GET['created'] == 1) {
    echo '<div class="auto-hide-alert bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">¡Éxito!</strong>
            <span class="block sm:inline">El servicio ha sido creado correctamente.</span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display=\'none\';"><svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg></span>
          </div>';
}
if (isset($_GET['updated']) && $_GET['updated'] == 1) {
    echo '<div class="auto-hide-alert bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">¡Éxito!</strong>
            <span class="block sm:inline">El servicio ha sido actualizado correctamente.</span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display=\'none\';"><svg class="fill-current h-6 w-6 text-blue-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg></span>
          </div>';
}
if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    echo '<div class="auto-hide-alert bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">¡Éxito!</strong>
            <span class="block sm:inline">El servicio ha sido eliminado correctamente.</span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display=\'none\';"><svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg></span>
          </div>';
}
if (isset($_GET['error'])) {
    echo '<div class="auto-hide-alert bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">¡Error!</strong>
            <span class="block sm:inline">Ha ocurrido un error. Por favor, inténtelo de nuevo.</span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display=\'none\';"><svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg></span>
          </div>';
}
?>

<div class="bg-white rounded-xl shadow p-6">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-[#5C4633]">Gestión de Servicios</h2>
        <button onclick="openModal('serviceModal')" class="px-6 py-2 bg-[#5C4633] text-white rounded-lg hover:bg-[#4A3829] focus:outline-none flex items-center">
            <i class="fa-solid fa-plus mr-2"></i>
            Añadir Servicio
        </button>
    </div>

    <!-- Tabla de servicios -->
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-[#F5EBDD] text-[#5C4633]">
                <tr>
                    <th class="p-3">Nombre</th>
                    <th class="p-3">Descripción</th>
                    <th class="p-3">Precio</th>
                    <th class="p-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['services'])): ?>
                    <?php foreach ($data['services'] as $service): ?>
                        <tr class="border-b border-[#EAE3D9]">
                            <td class="p-3"><?= htmlspecialchars($service['nombre_servicio']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($service['descripcion']) ?></td>
                            <td class="p-3">$<?= htmlspecialchars(number_format($service['precio'], 2)) ?></td>
                            <td class="p-3 flex gap-2">
                                <button type="button" class="text-amber-700" title="Editar"
                                    onclick="openEditServiceModal(<?= htmlspecialchars(json_encode($service), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="fa-solid fa-pencil"></i>
                                </button>
                                <form method="POST" action="controllers/service_controller.php" style="display:inline;" 
                                    onsubmit="return confirm('¿Seguro que deseas eliminar este servicio?');">
                                    <input type="hidden" name="deleteService" value="1">
                                    <input type="hidden" name="id" value="<?= $service['id'] ?>">
                                    <button type="submit" class="text-red-600" title="Eliminar" style="background:none;border:none;padding:0;">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="p-3 text-center text-gray-500">No se encontraron servicios.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para añadir servicio -->
<div id="serviceModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center hidden">
    <div class="relative mx-auto p-8 border w-full max-w-lg shadow-lg rounded-xl bg-white">
        <div class="text-left">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-[#5C4633]">Nuevo Servicio</h3>
                <button onclick="closeModal('serviceModal')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-times fa-lg"></i></button>
            </div>
            <form action="controllers/service_controller.php" method="POST" class="space-y-4 mt-6">
                <input type="hidden" name="addService" value="1">
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" id="nombre" name="nombre" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                </div>
                <div>
                    <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required></textarea>
                </div>
                <div>
                    <label for="precio" class="block text-sm font-medium text-gray-700 mb-1">Precio</label>
                    <input type="number" id="precio" name="precio" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                </div>
                <div class="flex items-center justify-end gap-4 pt-4">
                    <button type="button" onclick="closeModal('serviceModal')" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg">Cancelar</button>
                    <button type="submit" class="px-6 py-2 bg-[#5C4633] text-white rounded-lg hover:bg-[#4A3829]">Guardar Servicio</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de edición de servicio -->
<div id="editServiceModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-xl p-8 w-full max-w-lg relative">
        <button onclick="closeEditServiceModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">&times;</button>
        <h3 class="text-2xl font-bold text-[#5C4633] mb-4">Editar Servicio</h3>
        <form method="POST" action="controllers/service_controller.php" class="space-y-4" onsubmit="return confirm('¿Estás seguro de que quieres guardar estos cambios?');">
            <input type="hidden" name="editService" value="1">
            <input type="hidden" name="id" id="edit_service_id">
            <div>
                <label for="edit_nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                <input type="text" id="edit_nombre" name="nombre" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
            </div>
            <div>
                <label for="edit_descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <textarea id="edit_descripcion" name="descripcion" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required></textarea>
            </div>
            <div>
                <label for="edit_precio" class="block text-sm font-medium text-gray-700 mb-1">Precio</label>
                <input type="number" id="edit_precio" name="precio" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
            </div>
            <div class="flex items-center justify-end gap-4 pt-4">
                <button type="button" onclick="closeEditServiceModal()" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg">Cancelar</button>
                <button type="submit" class="px-6 py-2 bg-[#5C4633] text-white rounded-lg hover:bg-[#4A3829]">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}
function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}
function openEditServiceModal(service) {
    document.getElementById('edit_service_id').value = service.id;
    document.getElementById('edit_nombre').value = service.nombre_servicio;
    document.getElementById('edit_descripcion').value = service.descripcion;
    document.getElementById('edit_precio').value = service.precio;
    document.getElementById('editServiceModal').classList.remove('hidden');
}
function closeEditServiceModal() {
    document.getElementById('editServiceModal').classList.add('hidden');
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