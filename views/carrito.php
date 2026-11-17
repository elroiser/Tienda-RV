<?php require_once 'views/includes/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 border-b pb-4 mt-2">
        <h2 class="text-3xl font-bold text-slate-900">Tu Carrito de Compras</h2>
        <p class="text-slate-600 mt-1">Revisa tus productos seleccionados antes de confirmar el pedido contra entrega.</p>
    </div>

    <!-- Alertas de control de inventario -->
    <?php if (isset($_GET['error'])): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3.5 rounded-xl mb-6 flex items-center gap-3 text-sm font-medium">
            <i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i>
            <span>
                <?php
                if ($_GET['error'] === 'stock_maximo') {
                    $max = intval($_GET['max'] ?? 0);
                    echo "Has alcanzado el límite máximo disponible en inventario ({$max} unidades).";
                } elseif ($_GET['error'] === 'sin_stock') {
                    $prodNombre = htmlspecialchars($_GET['prod'] ?? 'uno de los productos');
                    $disp = intval($_GET['disp'] ?? 0);
                    echo $disp > 0
                        ? "Stock insuficiente para <b>{$prodNombre}</b>. Actualmente solo quedan <b>{$disp}</b> unidades disponibles; ajusta la cantidad para continuar."
                        : "Lo sentimos, <b>{$prodNombre}</b> acaba de agotarse. Elimínalo del carrito para continuar con tu compra.";
                }
                ?>
            </span>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['aviso_carrito_repetir'])): ?>
        <div class="bg-amber-50 border border-amber-200 text-amber-900 px-4 py-3.5 rounded-xl mb-6 flex items-start gap-3 text-sm font-medium">
            <i class="fa-solid fa-circle-info text-amber-600 text-lg mt-0.5"></i>
            <span><?php echo $_SESSION['aviso_carrito_repetir']; ?></span>
        </div>
        <?php unset($_SESSION['aviso_carrito_repetir']); ?>
    <?php endif; ?>

    <?php if (empty($carrito)): ?>
        <div class="text-center py-16 bg-white rounded-2xl border border-slate-200 shadow-sm">
            <i class="fa-solid fa-cart-shopping text-5xl text-slate-300 mb-4"></i>
            <h3 class="text-xl font-bold text-slate-700 mb-2">Tu carrito está vacío</h3>
            <p class="text-slate-500 mb-6">Aún no has agregado ningún producto de limpieza.</p>
            <a href="index.php?action=productos" class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-3 rounded-xl font-bold shadow-md transition-colors">
                Ir al Catálogo
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Lista de productos -->
            <div class="lg:col-span-2 space-y-4">
                <?php foreach ($carrito as $item): ?>
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-slate-100 rounded-xl flex items-center justify-center border border-slate-100 shrink-0">
                                <i class="fa-solid fa-bottle-droplet text-2xl text-teal-600"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-lg"><?php echo htmlspecialchars($item['nombre_base']); ?></h4>
                                <p class="text-xs text-slate-500 font-medium">
                                    Presentación: <span class="text-teal-700 font-semibold"><?php echo htmlspecialchars($item['presentacion']); ?></span>
                                    <?php if (!empty($item['aroma']) && $item['aroma'] !== 'Estándar'): ?>
                                        | Aroma: <span class="text-teal-700 font-semibold"><?php echo htmlspecialchars($item['aroma']); ?></span>
                                    <?php endif; ?>
                                </p>
                                <p class="text-sm font-bold text-slate-700 mt-1">$<?php echo number_format($item['precio'], 2); ?> c/u</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between w-full sm:w-auto gap-4 border-t sm:border-t-0 pt-3 sm:pt-0">
                            <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl p-1">
                                <a href="index.php?action=disminuirCarrito&id=<?php echo $item['id']; ?>" class="bg-white hover:bg-slate-200 text-slate-700 w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shadow-xs transition-colors" title="Restar uno">
                                    <i class="fa-solid fa-minus"></i>
                                </a>

                                <span class="text-sm font-bold text-slate-800 px-2"><?php echo $item['cantidad']; ?></span>

                                <a href="index.php?action=aumentarCarrito&id=<?php echo $item['id']; ?>" class="bg-white hover:bg-slate-200 text-slate-700 w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shadow-xs transition-colors" title="Sumar uno">
                                    <i class="fa-solid fa-plus"></i>
                                </a>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="text-right min-w-[75px]">
                                    <p class="text-lg font-extrabold text-teal-700">$<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></p>
                                </div>

                                <a href="index.php?action=eliminarCarrito&id=<?php echo $item['id']; ?>" class="text-slate-400 hover:text-rose-500 transition-colors p-2" title="Eliminar producto">
                                    <i class="fa-solid fa-trash-can text-base"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Resumen de Compra -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm h-fit">
                <h3 class="text-xl font-bold text-slate-800 mb-4 border-b pb-3">Resumen del Pedido</h3>

                <div class="space-y-3 text-sm text-slate-600 mb-6">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span class="font-semibold text-slate-800">$<?php echo number_format($totalGeneral, 2); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Envío (Pasaje / El Oro)</span>
                        <span class="font-semibold text-teal-600">¡Gratis por lanzamiento!</span>
                    </div>
                    <div class="border-t pt-3 flex justify-between text-base font-bold text-slate-900">
                        <span>Total a Pagar</span>
                        <span class="text-2xl text-teal-700">$<?php echo number_format($totalGeneral, 2); ?></span>
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mb-6 text-xs text-amber-800 flex items-start gap-2">
                    <i class="fa-solid fa-truck-fast text-amber-600 text-sm mt-0.5"></i>
                    <span>Método de pago seleccionado: <b>Efectivo Contra Entrega</b> al recibir tu pedido en la puerta de tu casa u negocio.</span>
                </div>

                <a href="index.php?action=checkout" class="w-full bg-teal-600 hover:bg-teal-700 text-white py-3.5 rounded-xl font-bold shadow-lg transition-colors flex items-center justify-center text-center">
                    Proceder al Checkout <i class="fa-solid fa-arrow-right ml-2"></i>
                </a>
            </div>

        </div>
    <?php endif; ?>
</div>

<?php require_once 'views/includes/footer.php'; ?>