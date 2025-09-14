<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Satori Spa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <nav class="bg-amber-800 text-white p-4">
            <div class="container mx-auto flex justify-between items-center">
                <h1 class="text-2xl font-bold">Satori Spa - Dashboard</h1>
                <div class="flex items-center space-x-4">
                    <span>Hola, <?php echo $_SESSION['user_name']; ?></span>
                    <a href="<?php echo BASE_URL; ?>login/logout" class="bg-amber-600 px-4 py-2 rounded hover:bg-amber-700">
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        </nav>

        <!-- Content -->
        <div class="container mx-auto p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Bienvenido al Panel de Administración</h2>
                <p>Has iniciado sesión correctamente como: <strong><?php echo $_SESSION['user_email']; ?></strong></p>
                
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-amber-50 p-4 rounded-lg">
                        <h3 class="font-semibold">Clientes</h3>
                        <p class="text-2xl">25</p>
                    </div>
                    <div class="bg-amber-50 p-4 rounded-lg">
                        <h3 class="font-semibold">Citas</h3>
                        <p class="text-2xl">12</p>
                    </div>
                    <div class="bg-amber-50 p-4 rounded-lg">
                        <h3 class="font-semibold">Ingresos</h3>
                        <p class="text-2xl">$1,250</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>