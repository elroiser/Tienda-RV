<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin | RV Limpieza</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-3xl shadow-xl w-full max-w-md">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-teal-50 text-teal-600 rounded-full flex items-center justify-center mx-auto text-2xl mb-3">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-900">Acceso Administrador</h2>
            <p class="text-slate-500 text-sm">Panel de control exclusivo para el negocio</p>
        </div>

        <?php if(isset($_GET['error'])): ?>
            <div class="bg-rose-50 text-rose-700 p-3 rounded-xl text-sm mb-4 text-center font-medium">
                Usuario o contraseña incorrectos.
            </div>
        <?php endif; ?>

        <form action="index.php?action=procesarAdminLogin" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Usuario</label>
                <input type="text" name="usuario" required class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Contraseña</label>
                <input type="password" name="password" required class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none">
            </div>
            <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white py-3.5 rounded-xl font-bold shadow-lg transition-colors">
                Ingresar al Panel
            </button>
        </form>
        <div class="mt-6 text-center">
            <a href="index.php" class="text-xs text-slate-400 hover:text-slate-600">← Volver a la tienda pública</a>
        </div>
    </div>
</body>
</html>
