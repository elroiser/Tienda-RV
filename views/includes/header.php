<?php
// Asegurarnos de que la sesión esté activa para leer los datos
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RV Limpieza | Distribuidor Chemlook</title>

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

            <!-- Enlaces Centrales Dinámicos -->
            <div class="space-x-6 hidden md:flex items-center font-medium">
                <a href="index.php?action=home" class="hover:text-teal-200 transition-colors"><i class="fa-solid fa-house mr-1"></i> Inicio</a>
                <a href="index.php?action=productos" class="hover:text-teal-200 transition-colors"><i class="fa-solid fa-box-open mr-1"></i> Catálogo</a>
                
                <?php if (isset($_SESSION['cliente_nombre'])): ?>
                    <!-- Vista cuando el usuario SÍ está logueado -->
                    <div class="flex items-center gap-4 border-l border-teal-500 pl-4 ml-2">
                        <span class="text-white font-bold flex items-center gap-2">
                            <i class="fa-solid fa-circle-user text-xl"></i>
                            <!-- Mostrar solo el primer nombre -->
                            <?php echo htmlspecialchars(explode(' ', trim($_SESSION['cliente_nombre']))[0]); ?>
                        </span>
                        <a href="index.php?action=logout" class="text-teal-100 hover:text-rose-300 text-sm font-medium transition-colors" title="Cerrar Sesión">
                            <i class="fa-solid fa-right-from-bracket"></i> Salir
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Vista cuando el usuario NO está logueado -->
                    <div class="flex items-center gap-4 border-l border-teal-500 pl-4 ml-2">
                        <a href="index.php?action=login" class="hover:text-teal-200 transition-colors">Ingresar</a>
                        <a href="index.php?action=registro" class="bg-teal-600 hover:bg-teal-500 px-4 py-2 rounded-lg font-bold transition-colors shadow-sm">
                            <i class="fa-solid fa-user-plus mr-1"></i> Registrarse
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Botón del carrito a la derecha (Con indicador de cantidad) -->
            <a href="index.php?action=carrito" class="bg-teal-600 hover:bg-teal-500 p-2 rounded-full transition-colors relative flex items-center justify-center h-10 w-10">
                <i class="fa-solid fa-cart-shopping"></i>
                
                <!-- Pequeño globo rojo que muestra cuántos items hay en el carrito -->
                <?php 
                $totalItems = 0;
                if(isset($_SESSION['carrito'])) {
                    foreach($_SESSION['carrito'] as $item) {
                        $totalItems += $item['cantidad'];
                    }
                }
                if($totalItems > 0): 
                ?>
                    <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] font-bold h-5 w-5 flex items-center justify-center rounded-full border-2 border-teal-700">
                        <?php echo $totalItems; ?>
                    </span>
                <?php endif; ?>
            </a>
            
        </div>
    </nav>

    <!-- Contenedor Principal de la Página -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 min-h-screen">