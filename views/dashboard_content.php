<?php
include_once __DIR__ . '/../controllers/dashboard_controller.php';
$controller = new DashboardController();
$recentActivities = $controller->getRecentActivities();
?>
<!-- Welcome Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-[#5C4633]">Satori SPA te da la Bienvenida <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Administrador'; ?></h1>
    <p class="text-[#8C837B]">Aquí tienes un resumen de la actividad de tu spa.</p>
</div>

<div class="grid grid-cols-3 gap-2 mb-10 w-full">
    <div class="bg-white rounded-xl shadow p-3 border border-[#EAE3D9] flex flex-col items-center justify-between h-28">
        <div class="text-[#8C837B] text-xs text-center"><i class="fa-regular fa-circle-user"></i> Total de Usuarios</div>
        <div id="totalUsuarios" class="text-xl font-bold text-[#5C4633]"><?= $data['totalUsuarios'] ?></div>
        <div class="text-xs text-[#8C837B] text-center">+12% desde el mes pasado</div>
    </div>
    <div class="bg-white rounded-xl shadow p-3 border border-[#EAE3D9] flex flex-col items-center justify-between h-28">
        <div class="text-[#8C837B] text-xs text-center"><i class="fa-solid fa-list"></i> Total de Servicios</div>
        <div id="serviciosActivos" class="text-xl font-bold text-[#5C4633]"><?= $data['totalServicios'] ?></div>
        <div class="text-xs text-[#8C837B] text-center">+2 nuevos servicios</div>
    </div>
    <div class="bg-white rounded-xl shadow p-3 border border-[#EAE3D9] flex flex-col items-center justify-between h-28">
        <div class="text-[#8C837B] text-xs text-center"><i class="fa-regular fa-calendar-days"></i> Total de Citas</div>
        <div id="citasHoy" class="text-xl font-bold text-[#5C4633]"><?= $data['totalCitas'] ?></div>
        <div class="text-xs text-[#8C837B] text-center">4 pendientes</div>
    </div>
</div>

<!-- Actividad Reciente -->
<div class="p-4 bg-white rounded-xl shadow">
 <h2 class="font-semibold text-lg text-[#5C4633] mb-4">Actividades Recientes</h2>
 <div style="max-height: 300px; overflow-y: auto;">
  <ul class="space-y-3">
   <?php foreach ($recentActivities as $activity): ?>
   <?php
    $dt = new DateTime($activity['fecha']);
    $dt->setTimezone(new DateTimeZone('America/Mexico_City'));
    $fecha_formateada = $dt->format('d/m/Y H:i');
    ?>
   <li class="border-b pb-2">
    <span class="font-semibold text-[#5C4633] capitalize">
     <?= ucfirst($activity['tipo']); ?>:
    </span>
    <span class="text-[#5C4633]"><?= htmlspecialchars($activity['descripcion']); ?></span><br>
    <span class="text-sm text-[#8C837B]"><?= $fecha_formateada; ?></span>
   </li>
   <?php endforeach; ?>
  </ul>
 </div>
</div>
