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
    echo '<div class="auto-hide-alert bg-green-200 border border-green-500 text-green-800 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">¡Éxito!</strong>
            <span class="block sm:inline">El servicio ha sido actualizado correctamente.</span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display=\'none\';"><svg class="fill-current h-6 w-6 text-green-600" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg></span>
          </div>';
}
if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    echo '<div class="auto-hide-alert bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">¡Éxito!</strong>
            <span class="block sm:inline">El servicio ha sido eliminado correctamente.</span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display=\'none\';"><svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg></span>
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
                            <td class="p-3 flex gap-4 items-center">
                                <button type="button" class="text-amber-700 hover:text-amber-900 transition-colors" title="Editar"
                                    onclick="openEditServiceModal(<?= htmlspecialchars(json_encode($service), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="fa-solid fa-pencil fa-lg"></i>
                                </button>
                                <form method="POST" action="controllers/service_controller.php" style="display:inline;" 
                                    onsubmit="event.preventDefault(); openConfirmationModal('¿Seguro que deseas eliminar este servicio?', () => this.submit());">
                                    <input type="hidden" name="deleteService" value="1">
                                    <input type="hidden" name="id" value="<?= $service['id'] ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-800 transition-colors" title="Eliminar" style="background:none;border:none;padding:0;">
                                        <i class="fa-solid fa-trash fa-lg"></i>
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
<?php
$form_errors = $_SESSION['form_errors'] ?? [];
$form_data = $_SESSION['form_data'] ?? [];
$open_add_modal = $_SESSION['open_add_modal'] ?? false;

unset($_SESSION['form_errors'], $_SESSION['form_data'], $_SESSION['open_add_modal']);
?>
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
                    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($form_data['nombre'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                    <?php if (isset($form_errors['nombre'])): ?>
                        <p class="text-red-500 text-xs mt-1"><?= $form_errors['nombre'] ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required maxlength="50"><?= htmlspecialchars($form_data['descripcion'] ?? '') ?></textarea>
                    <?php if (isset($form_errors['descripcion'])): ?>
                        <p class="text-red-500 text-xs mt-1"><?= $form_errors['descripcion'] ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="precio" class="block text-sm font-medium text-gray-700 mb-1">Precio</label>
                    <input type="number" id="precio" name="precio" step="0.01" min="0.01" value="<?= htmlspecialchars($form_data['precio'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                    <?php if (isset($form_errors['precio'])): ?>
                        <p class="text-red-500 text-xs mt-1"><?= $form_errors['precio'] ?></p>
                    <?php endif; ?>
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
<?php
$edit_form_errors = $_SESSION['edit_form_errors'] ?? [];
$edit_form_data = $_SESSION['edit_form_data'] ?? [];
$open_edit_modal_id = $_SESSION['open_edit_modal'] ?? null;
unset($_SESSION['edit_form_errors'], $_SESSION['edit_form_data'], $_SESSION['open_edit_modal']);
?>
<div id="editServiceModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="relative mx-auto p-8 border w-full max-w-lg shadow-lg rounded-xl bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-2xl font-bold text-[#5C4633]">Editar Servicio</h3>
            <button onclick="closeEditServiceModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-times fa-lg"></i></button>
        </div>
        <form method="POST" action="controllers/service_controller.php" class="space-y-4" onsubmit="event.preventDefault(); openConfirmationModal('¿Estás seguro de que quieres guardar estos cambios?', () => this.submit());">
            <input type="hidden" name="editService" value="1">
            <input type="hidden" name="id" id="edit_service_id">
            <div>
                <label for="edit_nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                <input type="text" id="edit_nombre" name="nombre" value="<?= htmlspecialchars($edit_form_data['nombre'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                <?php if (isset($edit_form_errors['nombre'])): ?>
                    <p class="text-red-500 text-xs mt-1"><?= $edit_form_errors['nombre'] ?></p>
                <?php endif; ?>
            </div>
            <div>
                <label for="edit_descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <textarea id="edit_descripcion" name="descripcion" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required maxlength="50"><?= htmlspecialchars($edit_form_data['descripcion'] ?? '') ?></textarea>
                <?php if (isset($edit_form_errors['descripcion'])): ?>
                    <p class="text-red-500 text-xs mt-1"><?= $edit_form_errors['descripcion'] ?></p>
                <?php endif; ?>
            </div>
            <div>
                <label for="edit_precio" class="block text-sm font-medium text-gray-700 mb-1">Precio</label>
                <input type="number" id="edit_precio" name="precio" step="0.01" min="0.01" value="<?= htmlspecialchars($edit_form_data['precio'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                <?php if (isset($edit_form_errors['precio'])): ?>
                    <p class="text-red-500 text-xs mt-1"><?= $edit_form_errors['precio'] ?></p>
                <?php endif; ?>
            </div>
            <div class="flex items-center justify-end gap-4 pt-4">
                <button type="button" onclick="closeEditServiceModal()" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg">Cancelar</button>
                <button type="submit" class="px-6 py-2 bg-[#5C4633] text-white rounded-lg hover:bg-[#4A3829]">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center hidden z-50">
    <div class="relative mx-auto p-8 border w-full max-w-md shadow-lg rounded-xl bg-white">
        <div class="text-center p-5 flex-auto justify-center">
            <i class="fa-solid fa-circle-exclamation text-amber-500 fa-4x mx-auto mb-4"></i>
            <h3 id="confirmationMessage" class="text-xl font-bold text-gray-800 mt-2"></h3>
            <p class="text-sm text-gray-500 px-8">¿Estás seguro? Esta acción no se puede deshacer.</p>
            <div class="mt-6 flex justify-center gap-4">
                <button id="cancelButton" class="flex items-center px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    <i class="fa-solid fa-times mr-2"></i> Cancelar
                </button>
                <button id="confirmButton" class="flex items-center px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fa-solid fa-check mr-2"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}
function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}
function openEditServiceModal(service, old_data = null) {
    document.getElementById('edit_service_id').value = service.id;
    document.getElementById('edit_nombre').value = old_data && old_data.nombre ? old_data.nombre : service.nombre_servicio;
    document.getElementById('edit_descripcion').value = old_data && old_data.descripcion ? old_data.descripcion : service.descripcion;
    document.getElementById('edit_precio').value = old_data && old_data.precio ? old_data.precio : service.precio;
    document.getElementById('editServiceModal').classList.remove('hidden');
}
function closeEditServiceModal() {
    document.getElementById('editServiceModal').classList.add('hidden');

    // Limpiar errores al cerrar
    const errorMessages = document.querySelectorAll('#editServiceModal .text-red-500');
    errorMessages.forEach(error => error.remove());
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

    const open_add_modal = <?= json_encode($open_add_modal) ?>;
    if (open_add_modal) {
        openModal('serviceModal');
    }

    const open_edit_modal_id = <?= json_encode($open_edit_modal_id) ?>;
    const edit_form_data = <?= json_encode($edit_form_data) ?>;

    if (open_edit_modal_id) {
        const services = <?= json_encode($data['services'] ?? []) ?>;
        let service_to_edit = services.find(s => s.id == open_edit_modal_id);

        if (Object.keys(edit_form_data).length > 0) {
            openEditServiceModal(service_to_edit || { id: open_edit_modal_id }, edit_form_data);
        } else if (service_to_edit) {
            openEditServiceModal(service_to_edit);
        }
    }
});
</script>