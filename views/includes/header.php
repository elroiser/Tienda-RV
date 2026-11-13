<?php
// Asegurarnos de que la sesión esté activa para leer los datos
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Calcular total de items del carrito de forma segura
$totalItems = 0;
if (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $totalItems += $item['cantidad'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RV Limpieza | Distribuidor Chemlook</title>

    <!-- Favicon para la pestaña del navegador -->
    <link rel="icon" type="image/png" href="public/img/logo.png">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- FontAwesome para Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tu CSS Personalizado -->
    <link rel="stylesheet" href="public/css/style.css">
</head>

<body class="bg-slate-50 font-sans text-slate-800 relative">

    <!-- Navegación Principal Única -->
    <nav class="bg-teal-700 text-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">

            <!-- Logo a la izquierda -->
            <a href="index.php?action=home" class="flex items-center font-bold text-xl">
                <img src="public/img/logo.png" alt="RV Logo" class="h-10 w-10 rounded-full mr-3 border-2 border-teal-300 bg-white object-cover">
                <span class="italic tracking-tighter">RV Limpieza</span>
            </a>

            <!-- Contenedores derechos: Carrito y Botón Hamburguesa para Móvil -->
            <div class="flex items-center gap-3 md:hidden">

                <!-- Botón del carrito en móvil con ID propio -->
                <a href="index.php?action=carrito" class="bg-teal-600 hover:bg-teal-500 p-2 rounded-full transition-colors relative flex items-center justify-center h-10 w-10">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span id="contadorCarritoMobile" class="absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] font-bold h-5 w-5 flex items-center justify-center rounded-full border-2 border-teal-700 <?php echo ($totalItems > 0) ? '' : 'hidden'; ?>">
                        <?php echo $totalItems > 0 ? $totalItems : ''; ?>
                    </span>
                </a>

                <!-- Botón Menú Hamburguesa Móvil -->
                <button onclick="toggleMenuMobile()" id="btnMenuMobile" class="text-white focus:outline-none p-2">
                    <i class="fa-solid fa-bars text-2xl"></i>
                </button>
            </div>

            <!-- Enlaces Centrales / Menú adaptable (Móvil y PC) -->
            <div id="menuMovil" class="hidden md:flex flex-col md:flex-row md:items-center space-y-3 md:space-y-0 md:space-x-6 font-medium absolute md:relative top-full left-0 w-full md:w-auto bg-teal-700 md:bg-transparent p-4 md:p-0 shadow-lg md:shadow-none border-t border-teal-600 md:border-0 z-40">
                <a href="index.php?action=home" class="hover:text-teal-200 transition-colors block"><i class="fa-solid fa-house mr-1"></i> Inicio</a>
                <a href="index.php?action=productos" class="hover:text-teal-200 transition-colors block"><i class="fa-solid fa-box-open mr-1"></i> Catálogo</a>

                <?php if (isset($_SESSION['cliente_nombre'])): ?>
                    <!-- Contenedor del Perfil con Menú Desplegable -->
                    <div class="relative md:border-l md:border-teal-500 md:pl-4 pt-2 md:pt-0 border-t border-teal-600 md:border-t-0">
                        <button onclick="toggleMenuUsuario()" id="btnMenuUsuario" class="text-white font-bold flex items-center justify-between w-full md:w-auto gap-2 focus:outline-none py-1">
                            <span class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-user text-xl"></i>
                                <span><?php echo htmlspecialchars(explode(' ', trim($_SESSION['cliente_nombre']))[0]); ?></span>
                            </span>
                            <i class="fa-solid fa-chevron-down text-xs ml-1"></i>
                        </button>

                        <!-- Menú desplegable oculto por defecto -->
                        <div id="menuDesplegableUsuario" class="hidden md:absolute right-0 mt-2 w-full md:w-48 bg-white rounded-xl shadow-xl py-2 border border-slate-100 z-50 text-slate-800">
                            <a href="index.php?action=miPerfil" class="block px-4 py-2.5 text-sm hover:bg-slate-50 font-medium text-slate-700">
                                <i class="fa-solid fa-user-gear mr-2 text-teal-600"></i> Mi Perfil
                            </a>
                            <a href="index.php?action=misPedidos" class="block px-4 py-2.5 text-sm hover:bg-slate-50 font-medium text-slate-700">
                                <i class="fa-solid fa-box mr-2 text-teal-600"></i> Mis Pedidos
                            </a>
                            <div class="border-t border-slate-100 my-1"></div>
                            <a href="index.php?action=logout" class="block px-4 py-2.5 text-sm hover:bg-rose-50 font-medium text-rose-600">
                                <i class="fa-solid fa-right-from-bracket mr-2"></i> Salir
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Vista cuando el usuario NO está logueado -->
                    <div class="flex flex-col md:flex-row items-stretch md:items-center gap-3 md:border-l md:border-teal-500 md:pl-4 md:ml-2 pt-2 md:pt-0 border-t border-teal-600 md:border-t-0">
                        <a href="index.php?action=login" class="hover:text-teal-200 transition-colors py-1">Ingresar</a>
                        <a href="index.php?action=registro" class="bg-teal-600 hover:bg-teal-500 px-4 py-2 rounded-lg font-bold transition-colors shadow-sm text-center">
                            <i class="fa-solid fa-user-plus mr-1"></i> Registrarse
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Botón del carrito a la derecha en PC con ID propio -->
            <a href="index.php?action=carrito" class="bg-teal-600 hover:bg-teal-500 p-2 rounded-full transition-colors relative hidden md:flex items-center justify-center h-10 w-10">
                <i class="fa-solid fa-cart-shopping"></i>
                <span id="contadorCarritoDesktop" class="absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] font-bold h-5 w-5 flex items-center justify-center rounded-full border-2 border-teal-700 <?php echo ($totalItems > 0) ? '' : 'hidden'; ?>">
                    <?php echo $totalItems > 0 ? $totalItems : ''; ?>
                </span>
            </a>

        </div>
    </nav>

    <!-- Scripts de Control Desplegable -->
    <script>
        // Abrir/Cerrar menú hamburguesa móvil
        function toggleMenuMobile() {
            const menu = document.getElementById('menuMovil');
            menu.classList.toggle('hidden');
        }

        // Abrir/Cerrar menú desplegable de usuario
        function toggleMenuUsuario() {
            const menu = document.getElementById('menuDesplegableUsuario');
            menu.classList.toggle('hidden');
        }

        // Cerrar menús al hacer clic fuera
        window.addEventListener('click', function(e) {
            const btnUser = document.getElementById('btnMenuUsuario');
            const menuUser = document.getElementById('menuDesplegableUsuario');
            const btnMobile = document.getElementById('btnMenuMobile');
            const menuMobile = document.getElementById('menuMovil');

            if (btnUser && menuUser && !btnUser.contains(e.target) && !menuUser.contains(e.target)) {
                menuUser.classList.add('hidden');
            }

            if (window.innerWidth < 768 && btnMobile && menuMobile && !btnMobile.contains(e.target) && !menuMobile.contains(e.target)) {
                menuMobile.classList.add('hidden');
            }
        });
    </script>

    <!-- Contenedor Principal de la Página -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 min-h-screen">