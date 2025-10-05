<div class="max-w-lg mx-auto mt-10 bg-white p-6 rounded-2xl shadow-md">
  <h1 class="text-2xl font-bold text-center mb-4 text-blue-600">Vincular Token NFC</h1>

  <?php if (!empty($mensaje)): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
      <?= htmlspecialchars($mensaje) ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="?controller=nfc&action=vincular" class="space-y-4">
    <div>
      <label class="block text-gray-700 font-semibold mb-2">Seleccionar Usuario</label>
      <select name="id_usuario" required class="w-full border-gray-300 rounded-lg p-2">
        <option value="">-- Seleccionar --</option>
        <?php foreach ($usuarios as $u): ?>
          <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="flex justify-center">
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">
        Generar Token NFC
      </button>
    </div>
  </form>
</div>
