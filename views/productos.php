<?php
/** @var array $productos */
require_once 'views/includes/header.php';
?>

<div class="mb-8 border-b pb-4 mt-6">
    <h2 class="text-3xl font-bold text-slate-900">Catálogo de Productos</h2>
    <p class="text-slate-600 mt-1">Línea completa Chemlook. Selecciona un producto para ver sus presentaciones.</p>
</div>

<!-- Barra de Búsqueda y Filtros por Categoría -->
<div class="mb-6 space-y-4">
    <div class="relative max-w-md">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
            <i class="fa-solid fa-magnifying-glass"></i>
        </span>
        <input type="text" id="buscadorProductos" placeholder="Buscar producto por nombre (ej. cloro, suavizante)..."
            class="w-full pl-10 pr-4 py-3 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none text-slate-800 font-medium shadow-sm">
    </div>

    <?php
    $categoriasDisponibles = [];
    if (!empty($productos)) {
        foreach ($productos as $p) {
            if (!empty($p['categoria']) && !in_array($p['categoria'], $categoriasDisponibles)) {
                $categoriasDisponibles[] = $p['categoria'];
            }
        }
        sort($categoriasDisponibles);
    }
    ?>

    <?php if (!empty($categoriasDisponibles)): ?>
        <div id="filtrosCategoria" class="flex flex-wrap items-center gap-2 pt-1">
            <button type="button" data-categoria="todas"
                class="btn-categoria bg-teal-600 text-white px-4 py-2 rounded-full text-sm font-bold shadow-sm transition-all cursor-pointer">
                Todos
            </button>

            <?php foreach ($categoriasDisponibles as $cat): ?>
                <button type="button" data-categoria="<?php echo htmlspecialchars(mb_strtolower($cat, 'UTF-8')); ?>"
                    class="btn-categoria bg-white hover:bg-teal-50 text-slate-700 border border-slate-200 px-4 py-2 rounded-full text-sm font-semibold transition-all cursor-pointer">
                    <?php echo htmlspecialchars($cat); ?>
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Grid de Productos -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 pb-12">

    <?php if (empty($productos)): ?>
        <div class="col-span-full text-center py-12 bg-white rounded-xl border border-slate-200">
            <i class="fa-solid fa-box-open text-4xl text-slate-300 mb-3"></i>
            <p class="text-slate-500 font-medium">No hay productos disponibles en este momento.</p>
        </div>
    <?php else: ?>

        <?php foreach ($productos as $index => $prod): ?>
            <?php
            $catTarjeta = !empty($prod['categoria']) ? mb_strtolower($prod['categoria'], 'UTF-8') : '';
            $agotadoTotal = isset($prod['stock_total']) && $prod['stock_total'] <= 0;
            ?>
            <div class="product-card flex flex-col h-full bg-white <?php echo $agotadoTotal ? 'opacity-85' : ''; ?>" data-categoria="<?php echo htmlspecialchars($catTarjeta); ?>">
                <div class="relative h-56 bg-slate-100 flex items-center justify-center p-4 border-b border-slate-100 overflow-hidden group">
                    <?php if (!empty($prod['categoria'])): ?>
                        <span class="absolute top-3 left-3 bg-white/90 backdrop-blur text-xs font-bold px-2 py-1 rounded text-teal-800 border border-teal-100 shadow-sm z-10">
                            <?php echo htmlspecialchars($prod['categoria']); ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($agotadoTotal): ?>
                        <span class="absolute top-3 right-3 bg-rose-600 text-white text-[11px] font-bold px-2.5 py-1 rounded-full shadow-sm z-10 uppercase tracking-wide">
                            Agotado
                        </span>
                    <?php endif; ?>

                    <?php
                    $nombreImagen = !empty($prod['imagen']) ? $prod['imagen'] : 'default.png';
                    $rutaImagen = 'public/img/productos/' . $nombreImagen;
                    if (!file_exists($rutaImagen)) {
                        $rutaImagen = 'public/img/productos/default.png';
                    }
                    ?>

                    <img
                        src="<?php echo $rutaImagen; ?>"
                        alt="<?php echo htmlspecialchars($prod['nombre_base']); ?>"
                        class="max-h-full max-w-full object-contain transition-transform duration-300 group-hover:scale-105 <?php echo $agotadoTotal ? 'grayscale-[40%]' : ''; ?>"
                        onerror="this.onerror=null; this.src='https://placehold.co/400x400/f1f5f9/94a3b8?text=Sin+Imagen';">
                </div>

                <div class="p-5 flex-1 flex flex-col">
                    <h3 class="font-semibold text-slate-800 leading-tight mb-3 flex-1 line-clamp-2" title="<?php echo htmlspecialchars($prod['nombre_base']); ?>">
                        <?php echo htmlspecialchars($prod['nombre_base']); ?>
                    </h3>

                    <div class="flex items-center justify-end mt-auto gap-2">
                        <button
                            onclick='abrirModalProducto(<?php echo json_encode($prod, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'
                            class="w-full <?php echo $agotadoTotal ? 'bg-slate-600 hover:bg-slate-700' : 'bg-teal-600 hover:bg-teal-700'; ?> text-white px-4 py-2.5 rounded-lg flex items-center justify-center transition-colors shadow-sm text-sm font-bold cursor-pointer">
                            <?php echo $agotadoTotal ? 'Sin stock disponible' : 'Ver opciones'; ?>
                            <i class="fa-solid fa-chevron-right ml-2 text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>
    <?php endif; ?>

</div>

<!-- Notificación Flotante (Toast) -->
<div id="toastCatalogo" class="fixed bottom-5 right-5 z-[110] hidden max-w-sm bg-slate-900 text-white px-4 py-3 rounded-xl shadow-xl border border-slate-700 flex items-center gap-3 text-sm font-medium transition-all">
    <i id="toastIcono" class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>
    <span id="toastMensaje">Producto añadido al carrito</span>
</div>

<!-- VENTANA FLOTANTE (MODAL) -->
<div id="productoModal" class="relative z-[100] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">

    <div id="modalBackdrop" class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity opacity-0"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">

            <div id="modalContent" class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                <div class="flex justify-between items-center p-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="font-bold text-slate-800 text-lg">Selecciona una presentación</h3>
                    <button onclick="cerrarModal()" class="text-slate-400 hover:text-rose-500 transition-colors p-1 rounded-lg hover:bg-rose-50 cursor-pointer">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <div class="p-6">
                    <div class="flex gap-4 mb-6">
                        <div class="w-24 h-24 bg-slate-100 rounded-xl flex items-center justify-center border border-slate-200 shrink-0 overflow-hidden p-2">
                            <img id="modalImagen" src="" alt="Producto" class="max-w-full max-h-full object-contain">
                        </div>
                        <div class="flex flex-col justify-center">
                            <h4 id="modalNombreBase" class="font-bold text-slate-800 text-lg leading-tight mb-2">Nombre Producto</h4>
                            <div>
                                <span id="modalCategoria" class="inline-block bg-teal-50 text-teal-700 text-xs px-2 py-1 rounded font-semibold border border-teal-100">Categoría</span>
                            </div>
                        </div>
                    </div>

                    <!-- Contenedor de Aroma -->
                    <div id="contenedorAroma" class="mb-4 hidden">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Aroma / Fragancia</label>
                        <select id="modalSelectAroma" onchange="cambiarAroma()" class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none bg-white shadow-sm font-medium text-slate-800 cursor-pointer">
                        </select>
                    </div>

                    <!-- Contenedor de Presentación -->
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tamaño / Presentación</label>
                    <select id="modalSelectPresentacion" onchange="actualizarPrecioModal()" class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-all cursor-pointer font-medium text-slate-800 bg-white shadow-sm">
                    </select>

                    <!-- Estado de Stock Dinámico -->
                    <div id="modalEstadoStock" class="mt-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <i class="fa-solid fa-circle-check"></i>
                        <span id="modalTextoStock">Disponible en inventario</span>
                    </div>

                    <div class="mt-6 flex items-end justify-between bg-slate-50 p-5 rounded-xl border border-slate-100">
                        <div>
                            <p class="text-sm text-slate-500 mb-1 font-medium">Precio Total</p>
                            <p class="text-3xl font-extrabold text-teal-700" id="modalPrecioFinal">$0.00</p>
                        </div>
                        <button id="btnAgregarModal" onclick="agregarAlCarrito()" class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-3 rounded-xl font-bold flex items-center shadow-md transition-all cursor-pointer">
                            <i class="fa-solid fa-cart-shopping mr-2"></i> Añadir
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="public/js/productos.js?v=6"></script>
<?php require_once 'views/includes/footer.php'; ?>