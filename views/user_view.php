<div class="bg-white rounded-xl shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="font-semibold text-lg text-[#5C4633]">Lista de Usuarios</h2>
        <button class="px-4 py-2 bg-[#80684B] text-white rounded-lg hover:bg-[#6D573F] focus:outline-none">
            <i class="fa-solid fa-plus mr-2"></i>Añadir Usuario
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-[#F5EBDD] text-[#5C4633]">
                <tr>
                    <th class="p-3">ID</th>
                    <th class="p-3">Nombre</th>
                    <th class="p-3">Apellido</th>
                    <th class="p-3">Email</th>
                    <th class="p-3">Rol</th>
                    <th class="p-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['users'])): ?>
                    <?php foreach ($data['users'] as $user): ?>
                        <tr class="border-b border-[#EAE3D9]">
                            <td class="p-3"><?= htmlspecialchars($user['id']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($user['nombre']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($user['apellido']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($user['correo']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($user['id_rol']) ?></td>
                            <td class="p-3 text-[#80684B]">
                                <button class="mr-2"><i class="fa-solid fa-pencil"></i></button>
                                <button><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="p-3 text-center text-gray-500">No se encontraron usuarios.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>