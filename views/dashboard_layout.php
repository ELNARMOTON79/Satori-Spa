<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SATORI SPA - Panel de Administración</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />    
</head>
<body class="bg-gradient-to-b from-[#FFFDF9] to-[#FDFBF7] min-h-screen">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow h-screen flex flex-col">
            <div class="flex items-center px-6 py-4">
                <img src="views/images/logo.png" alt="Logotipo Satori Spa" class="h-12 w-12 rounded-full bg-[#F5EBDD] mr-2 object-cover">
                <div>
                    <h1 class="font-bold text-lg text-[#5C4633]">SATORI SPA</h1>
                    <span class="text-xs text-[#8C837B]">Panel de Administración</span>
                </div>
            </div>
            <nav class="mt-8 flex-1">
                <ul>
                    <li>
                        <a href="index.php?url=dashboard" class="w-full flex items-center px-6 py-3 rounded-lg mb-2 focus:outline-none <?= ($url[0] == 'dashboard' || $url[0] == 'home') ? 'bg-[#80684B] text-white' : 'text-[#5C4633] hover:bg-[#F5EBDD]' ?>">
                            <i class="fa-solid fa-chart-simple mr-2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="index.php?url=usuarios" class="w-full flex items-center px-6 py-3 rounded-lg mb-2 focus:outline-none <?= ($url[0] == 'usuarios') ? 'bg-[#80684B] text-white' : 'text-[#5C4633] hover:bg-[#F5EBDD]' ?>">
                            <i class="fa-solid fa-users mr-2"></i>           
                            Usuarios
                        </a>
                    </li>
                    <li>
                        <a href="index.php?url=servicios" class="w-full flex items-center px-6 py-3 rounded-lg focus:outline-none <?= ($url[0] == 'servicios') ? 'bg-[#80684B] text-white' : 'text-[#5C4633] hover:bg-[#F5EBDD]' ?>">
                            <i class="fa-solid fa-list mr-2"></i>
                            Servicios
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-10">
            <div class="flex justify-between items-center mb-8">
                <div></div>
                <div class="text-right">
                    <span class="text-[#5C4633]">Bienvenido, Administrador</span>
                    <button class="ml-4 px-4 py-2 bg-[#F5EBDD] rounded-lg border border-[#E0D5C5] hover:bg-[#E9DCCB] focus:outline-none">
                        <svg class="inline h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7"/></svg>
                        Cerrar Sesión
                    </button>
                </div>
            </div>

            <?php
                // Incluye la vista de contenido específica (dashboard, usuarios, etc.)
                if (isset($content_view)) {
                    include $content_view;
                }
            ?>
        </main>
    </div>
</body>
</html>
