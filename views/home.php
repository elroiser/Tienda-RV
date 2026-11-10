<?php require_once 'views/includes/header.php'; ?>
<?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'registro_exitoso'): ?>
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative flex items-center shadow-sm">
            <i class="fa-solid fa-circle-check mr-2 text-xl"></i>
            <span class="block sm:inline font-semibold">¡Cuenta creada con éxito! Ya puedes realizar pedidos.</span>
        </div>
    </div>
<?php endif; ?>

<div class="space-y-12">
    <!-- Hero Section (Sección Principal) -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden flex flex-col md:flex-row mt-4">
        
        <!-- Columna de Texto -->
        <div class="p-8 md:p-12 flex-1 flex flex-col justify-center">
            <div class="inline-block bg-teal-100 text-teal-800 font-semibold px-4 py-1.5 rounded-full text-sm mb-6 w-max border border-teal-200">
                <i class="fa-solid fa-location-dot mr-1"></i> Pasaje, El Oro
            </div>
            
            <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 mb-6 leading-tight">
                Limpieza Profesional <br/>
                <span class="text-teal-600">a tu Alcance</span>
            </h1>
            
            <p class="text-slate-600 mb-8 text-lg leading-relaxed">
                En <strong>RV Venta de Productos de Limpieza</strong> te ofrecemos la línea completa de productos para el hogar e industria. Somos distribuidores oficiales de la marca <strong>Chemlook</strong>.
            </p>
            
            <div class="flex flex-wrap gap-4">
                <a href="index.php?action=productos" class="btn-primary text-white font-bold py-3 px-8 rounded-xl flex items-center shadow-md">
                    Ver Catálogo <i class="fa-solid fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- Columna de Imagen/Logo Destacado -->
        <div class="hidden md:flex flex-1 bg-gradient-to-br from-teal-50 to-teal-100 items-center justify-center p-12 relative overflow-hidden">
            <!-- Gotas decorativas de fondo -->
            <i class="fa-solid fa-droplet text-teal-200 absolute top-10 left-10 text-8xl opacity-40"></i>
            <i class="fa-solid fa-spray-can text-teal-200 absolute bottom-10 right-10 text-7xl opacity-40"></i>
            
            <!-- Logo Central Grande -->
            <div class="relative z-10 w-72 h-72 rounded-full bg-white border-4 border-teal-700 shadow-2xl flex flex-col items-center justify-center p-4 overflow-hidden">
                <!-- Asegúrate de que la ruta a logo.png sea correcta -->
                <img src="public/img/logo.png" alt="Logo RV Limpieza Grande" class="w-full h-full object-cover">
            </div>
        </div>
    </div>

    <!-- Sección de Tarjetas de Información (Info Cards) -->
    <div class="grid md:grid-cols-3 gap-6">
        
        <!-- Tarjeta 1 -->
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 flex flex-col items-center text-center hover:shadow-md transition-shadow">
            <div class="w-16 h-16 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mb-5 border border-rose-100">
                <i class="fa-solid fa-truck-fast text-2xl"></i>
            </div>
            <h3 class="font-bold text-xl text-slate-800 mb-3">Entregas en Pasaje</h3>
            <p class="text-slate-600 text-sm">Realizamos entregas rápidas dentro de la ciudad de Pasaje. <br/><span class="text-teal-600 font-semibold mt-2 inline-block bg-teal-50 px-2 py-1 rounded">¡Pronto a todo Ecuador!</span></p>
        </div>
        
        <!-- Tarjeta 2 -->
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 flex flex-col items-center text-center hover:shadow-md transition-shadow">
            <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mb-5 border border-blue-100">
                <i class="fa-solid fa-hand-holding-dollar text-2xl"></i>
            </div>
            <h3 class="font-bold text-xl text-slate-800 mb-3">Pago Contra Entrega</h3>
            <p class="text-slate-600 text-sm">Tu seguridad es primero. Paga tus productos únicamente al momento de recibirlos en tus manos en tu domicilio.</p>
        </div>
        
        <!-- Tarjeta 3 -->
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 flex flex-col items-center text-center hover:shadow-md transition-shadow">
            <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mb-5 border border-amber-100">
                <i class="fa-solid fa-award text-2xl"></i>
            </div>
            <h3 class="font-bold text-xl text-slate-800 mb-3">Calidad Chemlook</h3>
            <p class="text-slate-600 text-sm">Distribuimos productos concentrados e industriales garantizados para una limpieza profunda y duradera.</p>
        </div>
    </div>

    <!-- NUEVA SECCIÓN: Información sobre Chemlook -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-10 flex flex-col md:flex-row-reverse">
        
        <!-- Imagen representativa -->
        <div class="md:w-1/2 h-64 md:h-auto relative">
            <!-- Usamos una imagen de Unsplash de alta calidad orientada a limpieza. Puedes cambiar el 'src' por la ruta de una foto tuya luego -->
            <img src="https://images.unsplash.com/photo-1585421514738-01798e348b17?auto=format&fit=crop&w=800&q=80" alt="Productos de limpieza de alta calidad" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-teal-900 opacity-10"></div> <!-- Filtro ligero para que encaje con tus colores -->
        </div>

        <!-- Contenido de texto -->
        <div class="md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
            <h2 class="text-3xl font-bold text-slate-900 mb-4">La Potencia de <span class="text-teal-600">Chemlook</span></h2>
            <p class="text-slate-600 mb-6 leading-relaxed">
                Como distribuidores oficiales, te traemos una línea de productos químicos de alto rendimiento. Las fórmulas de Chemlook están diseñadas para ofrecer resultados superiores tanto en el cuidado del hogar como en la industria.
            </p>
            
            <ul class="space-y-5">
                <li class="flex items-start">
                    <i class="fa-solid fa-circle-check text-teal-500 mt-1 mr-4 text-xl"></i>
                    <div>
                        <strong class="block text-slate-800 text-lg">Fórmulas Concentradas</strong>
                        <span class="text-sm text-slate-600">Mayor rendimiento y ahorro por cada aplicación. Un galón rinde mucho más que los productos convencionales.</span>
                    </div>
                </li>
                <li class="flex items-start">
                    <i class="fa-solid fa-leaf text-teal-500 mt-1 mr-4 text-xl"></i>
                    <div>
                        <strong class="block text-slate-800 text-lg">Opciones Biodegradables</strong>
                        <span class="text-sm text-slate-600">Contamos con líneas amigables con el medio ambiente, como nuestro poderoso desengrasante D-CITRUS CHEM.</span>
                    </div>
                </li>
                <li class="flex items-start">
                    <i class="fa-solid fa-shield-virus text-teal-500 mt-1 mr-4 text-xl"></i>
                    <div>
                        <strong class="block text-slate-800 text-lg">Desinfección Segura</strong>
                        <span class="text-sm text-slate-600">Eliminación de bacterias y virus para proteger tu hogar, baños y espacios de trabajo.</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>

<?php require_once 'views/includes/footer.php'; ?>