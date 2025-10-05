<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satori Spa - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-amber-50 to-amber-100 min-h-screen flex items-center justify-center p-4">
    <?php if (isset($_GET['error'])): ?>
    <script>
        alert("Credenciales incorrectas. Por favor, inténtalo de nuevo.");
    </script>
    <?php endif; ?>
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md pt-0 px-8 pb-8 flex flex-col items-center">
        <img src="views/images/logo.png" alt="Satori Spa Logo" class="w-full object-contain mb-0 transform scale-125">
        <h1 class="text-2xl font-semibold text-amber-900 mb-0 tracking-wide">SATORI SPA</h1>
        <p class="text-gray-500 mb-0">Panel de Administración</p>
        <form method="POST" action="controllers/logincontroller.php" class="w-full mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1" for="email">Email</label>
            <input name="email" id="email" type="email" class="mb-4 w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-300 focus:border-transparent" placeholder="admin@satori.spa" required>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="password">Contraseña</label>
            <div class="relative mb-6">
                <input name="password" id="password" type="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-300 focus:border-transparent" placeholder="••••••••" required>
                <span class="absolute inset-y-0 right-3 flex items-center text-gray-400 cursor-pointer" onclick="togglePassword()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm-9 0a9 9 0 0118 0c-1.5 2.5-4.5 6-9 6s-7.5-3.5-9-6z" />
                    </svg>
                </span>
            </div>
            <button type="submit" name="login" class="w-full bg-[#5C4633] text-white py-3 rounded-lg font-semibold hover:bg-[#4A3829] transition-colors duration-200">
                Iniciar Sesión
            </button>   
        </form>
        <div class="mt-6 w-full bg-amber-50 rounded-lg px-4 py-3 text-sm text-gray-600 border border-amber-200">
            <span class="font-semibold text-amber-900">Credenciales demo:</span><br>
            Email: admin@satori.spa<br>
            Contraseña: admin123
        </div>
    </div>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
        }
    </script>
</body>
</html>