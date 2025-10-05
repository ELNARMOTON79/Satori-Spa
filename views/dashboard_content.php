
<!-- Welcome Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-[#5C4633]">Bienvenido, <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Administrador'; ?></h1>
    <p class="text-[#8C837B]">Aquí tienes un resumen de la actividad de tu spa.</p>
</div>

<div class="grid grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow p-6 border border-[#EAE3D9]">
        <div class="text-[#8C837B] mb-2">Total Usuarios</div>
        <div id="totalUsuarios" class="text-3xl font-bold text-[#5C4633]">156</div>
        <div class="text-xs text-[#8C837B] mt-2">+12% desde el mes pasado</div>
    </div>
    <div class="bg-white rounded-xl shadow p-6 border border-[#EAE3D9]">
        <div class="text-[#8C837B] mb-2">Servicios Activos</div>
        <div id="serviciosActivos" class="text-3xl font-bold text-[#5C4633]">12</div>
        <div class="text-xs text-[#8C837B] mt-2">+2 nuevos servicios</div>
    </div>
    <div class="bg-white rounded-xl shadow p-6 border border-[#EAE3D9]">
        <div class="text-[#8C837B] mb-2">Citas Hoy</div>
        <div id="citasHoy" class="text-3xl font-bold text-[#5C4633]">8</div>
        <div class="text-xs text-[#8C837B] mt-2">4 completadas, 4 pendientes</div>
    </div>
</div>

<!-- Actividad Reciente -->
<div class="bg-white rounded-xl shadow p-6">
    <div class="font-semibold text-lg mb-4 text-[#5C4633]">Actividad Reciente</div>
    <ul id="actividadReciente">
        <li class="flex items-center mb-3">
            <span class="h-3 w-3 rounded-full mr-3" style="background-color: #22C55E;"></span>
            <span class="text-[#5C4633]">Nueva cita reservada por María García</span>
            <span class="ml-2 text-xs text-[#8C837B]">Hace 5 minutos</span>
        </li>
        <li class="flex items-center mb-3">
            <span class="h-3 w-3 rounded-full mr-3" style="background-color: #F59E0B;"></span>
            <span class="text-[#5C4633]">Servicio "Masaje Relajante" actualizado</span>
            <span class="ml-2 text-xs text-[#8C837B]">Hace 1 hora</span>
        </li>
        <li class="flex items-center mb-3">
            <span class="h-3 w-3 rounded-full mr-3" style="background-color: #F97316;"></span>
            <span class="text-[#5C4633]">Nuevo usuario registrado: Carlos López</span>
            <span class="ml-2 text-xs text-[#8C837B]">Hace 2 horas</span>
        </li>
    </ul>
</div>