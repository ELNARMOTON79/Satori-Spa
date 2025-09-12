<!-- Dashboard Admin - Tailwind CSS -->
<div class="flex h-screen bg-[#fdf8ee]">
  <!-- Sidebar -->
  <aside class="w-64 bg-white shadow-lg flex flex-col">
    <div class="px-8 py-6 border-b">
      <div class="flex items-center gap-2">
        <span class="bg-amber-700 text-white rounded-full p-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10" /></svg>
        </span>
        <div>
          <h1 class="font-bold text-lg text-amber-900">SATORI SPA</h1>
          <span class="text-xs text-gray-500">Panel de Administración</span>
        </div>
      </div>
    </div>
    <nav class="flex-1 px-4 py-6 space-y-2">
      <button class="w-full flex items-center gap-3 px-4 py-2 rounded-lg bg-amber-200 text-amber-900 font-semibold focus:outline-none focus:ring-2 focus:ring-amber-400 transition">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6" /></svg>
        Dashboard
      </button>
      <button class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-amber-100 text-gray-700 font-semibold focus:outline-none focus:ring-2 focus:ring-amber-400 transition">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" /></svg>
        Usuarios
      </button>
      <button class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-amber-100 text-gray-700 font-semibold focus:outline-none focus:ring-2 focus:ring-amber-400 transition">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 17v-6h6v6" /></svg>
        Servicios
      </button>
    </nav>
  </aside>
  <!-- Main Content -->
  <main class="flex-1 p-8">
    <div class="flex justify-between items-center mb-8">
      <div></div>
      <div class="flex items-center gap-4">
        <span class="text-gray-700">Bienvenido, Administrador</span>
        <button class="bg-white border px-4 py-2 rounded-lg font-semibold hover:bg-amber-100 transition flex items-center gap-2">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7" /></svg>
          Cerrar Sesión
        </button>
      </div>
    </div>
    <!-- Cards -->
    <div class="grid grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-xl shadow p-6 flex flex-col">
        <span class="text-gray-500 font-semibold">Total Usuarios</span>
        <span class="text-3xl font-bold mt-2">156</span>
        <span class="text-green-600 text-sm mt-1">+12% desde el mes pasado</span>
      </div>
      <div class="bg-white rounded-xl shadow p-6 flex flex-col">
        <span class="text-gray-500 font-semibold">Servicios Activos</span>
        <span class="text-3xl font-bold mt-2">12</span>
        <span class="text-blue-600 text-sm mt-1">+2 nuevos servicios</span>
      </div>
      <div class="bg-white rounded-xl shadow p-6 flex flex-col">
        <span class="text-gray-500 font-semibold">Citas Hoy</span>
        <span class="text-3xl font-bold mt-2">8</span>
        <span class="text-gray-600 text-sm mt-1">4 completadas, 4 pendientes</span>
      </div>
      <div class="bg-white rounded-xl shadow p-6 flex flex-col">
        <span class="text-gray-500 font-semibold">Ingresos del Mes</span>
        <span class="text-3xl font-bold mt-2">$45.600</span>
        <span class="text-green-600 text-sm mt-1">+18% vs mes anterior</span>
      </div>
    </div>
    <!-- Actividad Reciente -->
    <div class="bg-white rounded-xl shadow p-6">
      <h2 class="font-bold text-lg mb-4">Actividad Reciente</h2>
      <ul class="space-y-4">
        <li class="flex items-center gap-3">
          <span class="h-3 w-3 rounded-full bg-green-500 inline-block"></span>
          <div>
            <span class="font-semibold text-gray-800">Nueva cita reservada por María García</span>
            <div class="text-xs text-gray-500">Hace 5 minutos</div>
          </div>
        </li>
        <li class="flex items-center gap-3">
          <span class="h-3 w-3 rounded-full bg-yellow-500 inline-block"></span>
          <div>
            <span class="font-semibold text-gray-800">Servicio "Masaje Relajante" actualizado</span>
            <div class="text-xs text-gray-500">Hace 1 hora</div>
          </div>
        </li>
        <li class="flex items-center gap-3">
          <span class="h-3 w-3 rounded-full bg-orange-500 inline-block"></span>
          <div>
            <span class="font-semibold text-gray-800">Nuevo usuario registrado: Carlos López</span>
            <div class="text-xs text-gray-500">Hace 2 horas</div>
          </div>
        </li>
      </ul>
    </div>
  </main>
</div>