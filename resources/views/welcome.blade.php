<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SATORI SPA - Panel de Administración</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-amber-50 to-white">
    <div class="w-full max-w-md mx-auto bg-white rounded-2xl shadow-xl p-8 flex flex-col items-center">
        <!-- Logo -->
        <img src="{{ asset('logo.png') }}" alt="Logo Satori Spa" class="h-25 w-25 object-contain" />
        
        <!-- Título -->
        <h1 class="text-2xl font-semibold text-gray-800 mb-1 text-center">SATORI SPA</h1>
        <p class="text-lg text-amber-900 mb-6 text-center">Panel de Administración</p>
        <!-- Formulario -->
        <form class="w-full flex flex-col gap-4">
            <div>
                <label class="block text-gray-700 font-semibold mb-1" for="email">Email</label>
                <input id="email" type="email" placeholder="admin@gmail.com" class="w-full px-4 py-2 rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-orange-300" required>
            </div>
            <div>
                <label class="block text-gray-700 font-semibold mb-1" for="password">Contraseña</label>
                <div class="relative">
                    <input id="password" type="password" placeholder="••••••••" class="w-full px-4 py-2 rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-orange-300 pr-10" required>
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>
            <button type="submit" name="login" class="w-full py-2 rounded-lg bg-orange-800 hover:bg-orange-700 text-white font-semibold text-lg transition-colors">Iniciar Sesión</button>
        </form>
    </div>
    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            const eye = document.getElementById('eyeIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                eye.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.956 9.956 0 012.293-3.95m3.249-2.383A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.965 9.965 0 01-4.043 5.197M15 12a3 3 0 11-6 0 3 3 0 016 0z" />`;
            } else {
                pwd.type = 'password';
                eye.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
            }
        }
    </script>
</body>
</html>
