<?php require_once 'views/includes/header.php'; ?>

<!-- Estilos específicos para impresión limpia de una sola nota de pedido -->
<style>
@media print {
    body * {
        visibility: hidden;
    }
    .imprimible-activo, .imprimible-activo * {
        visibility: visible;
    }
    .imprimible-activo {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        border: none !important;
        box-shadow: none !important;
    }
    .no-print {
        display: none !important;
    }
}
</style>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 border-b pb-4 mt-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-3xl font-bold text-slate-900">Mis Pedidos</h2>
            <p class="text-slate-600 mt-1">Consulta el estado, revisa el desglose o vuelve a pedir tus compras anteriores.</p>
        </div>
        <a href="index.php?action=productos" class="bg-teal-600 hover:bg-teal-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm transition-colors inline-flex items-center justify-center">
            <i class="fa-solid fa-plus mr-2"></i> Nuevo Pedido
        </a>
    </div>

    <?php if (empty($pedidos)): ?>
        <div class="text-center py-16 bg-white rounded-2xl border border-slate-200 shadow-sm">
            <i class="fa-solid fa-box-open text-5xl text-slate-300 mb-4"></i>
            <h3 class="text-xl font-bold text-slate-700 mb-2">Aún no tienes pedidos registrados</h3>
            <p class="text-slate-500 mb-6">Explora nuestro catálogo Chemlook y realiza tu primer pedido contra entrega.</p>
            <a href="index.php?action=productos" class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-3 rounded-xl font-bold shadow-md transition-colors">
                Ver Catálogo
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($pedidos as $pedido): ?>
                <?php
                // Colores dinámicos según el estado del pedido
                $coloresEstado = [
                    'Pendiente'      => 'bg-amber-100 text-amber-800 border-amber-200',
                    'En Preparacion' => 'bg-blue-100 text-blue-800 border-blue-200',
                    'En Camino'      => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                    'Entregado'      => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                    'Cancelado'      => 'bg-rose-100 text-rose-800 border-rose-200'
                ];
                $badgeClase = $coloresEstado[$pedido['estado']] ?? 'bg-slate-100 text-slate-800 border-slate-200';
                $fechaFormateada = date('d/m/Y H:i', strtotime($pedido['fecha_pedido']));
                ?>

                <div id="card-pedido-<?php echo $pedido['id']; ?>" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden transition-all">
                    
                    <!-- Cabecera resumida del pedido (clic para desplegar) -->
                    <div onclick="toggleDetallePedido(<?php echo $pedido['id']; ?>)" class="p-5 cursor-pointer hover:bg-slate-50/80 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start sm:items-center gap-4">
                            <div class="w-12 h-12 bg-teal-50 text-teal-700 rounded-xl flex items-center justify-center font-extrabold text-sm border border-teal-100 shrink-0">
                                #<?php echo $pedido['id']; ?>
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-bold text-slate-800 text-lg">Orden #<?php echo $pedido['id']; ?></h3>
                                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full border <?php echo $badgeClase; ?>">
                                        <?php echo htmlspecialchars($pedido['estado']); ?>
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">
                                    <i class="fa-regular fa-calendar mr-1"></i> <?php echo $fechaFormateada; ?>
                                    <span class="mx-2">•</span>
                                    <span><?php echo count($pedido['detalles']); ?> producto(s)</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-4 border-t sm:border-t-0 pt-3 sm:pt-0">
                            <div class="text-left sm:text-right">
                                <span class="text-xs text-slate-400 block">Total</span>
                                <span class="text-xl font-extrabold text-teal-700">$<?php echo number_format($pedido['total'], 2); ?></span>
                            </div>
                            <button type="button" class="no-print text-teal-700 bg-teal-50 hover:bg-teal-100 px-3 py-2 rounded-lg text-xs font-bold flex items-center gap-1.5 transition-colors">
                                <span>Ver detalle</span>
                                <i id="icono-pedido-<?php echo $pedido['id']; ?>" class="fa-solid fa-chevron-down transition-transform duration-200"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Desglose exacto oculto por defecto -->
                    <div id="detalle-pedido-<?php echo $pedido['id']; ?>" class="hidden border-t border-slate-100 bg-slate-50/60 p-5">
                        
                        <!-- Encabezado visible solo al imprimir -->
                        <div class="hidden print:block mb-4 border-b pb-3">
                            <h2 class="text-xl font-bold text-slate-900">RV Limpieza - Nota de Pedido #<?php echo $pedido['id']; ?></h2>
                            <p class="text-sm text-slate-600">Fecha: <?php echo $fechaFormateada; ?> | Estado: <?php echo htmlspecialchars($pedido['estado']); ?></p>
                        </div>

                        <!-- Información de entrega -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-white p-4 rounded-xl border border-slate-200/80 mb-4 text-sm">
                            <div>
                                <span class="text-xs font-semibold text-slate-400 uppercase block">Dirección de Envío</span>
                                <p class="text-slate-800 font-medium mt-0.5"><?php echo htmlspecialchars($pedido['direccion_envio']); ?></p>
                                <?php if (!empty($pedido['referencia_envio'])): ?>
                                    <p class="text-xs text-slate-500 mt-0.5">Ref: <?php echo htmlspecialchars($pedido['referencia_envio']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-slate-400 uppercase block">Método de Pago</span>
                                <p class="text-slate-800 font-medium mt-0.5"><?php echo htmlspecialchars($pedido['metodo_pago']); ?></p>
                            </div>
                        </div>

                        <!-- Tabla de productos comprados -->
                        <div class="bg-white rounded-xl border border-slate-200/80 overflow-hidden mb-5">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-slate-100 text-slate-600 text-xs uppercase border-b border-slate-200">
                                        <th class="py-2.5 px-4">Producto</th>
                                        <th class="py-2.5 px-3 text-center">Cant.</th>
                                        <th class="py-2.5 px-3 text-right">P. Unit.</th>
                                        <th class="py-2.5 px-4 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php foreach ($pedido['detalles'] as $det): ?>
                                        <tr>
                                            <td class="py-3 px-4 font-medium text-slate-800">
                                                <?php echo htmlspecialchars($det['producto_nombre']); ?>
                                            </td>
                                            <td class="py-3 px-3 text-center font-semibold text-slate-700">
                                                <?php echo $det['cantidad']; ?>
                                            </td>
                                            <td class="py-3 px-3 text-right text-slate-600">
                                                $<?php echo number_format($det['precio_unitario'], 2); ?>
                                            </td>
                                            <td class="py-3 px-4 text-right font-bold text-slate-800">
                                                $<?php echo number_format($det['precio_unitario'] * $det['cantidad'], 2); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-slate-50 border-t border-slate-200">
                                        <td colspan="3" class="py-3 px-4 text-right font-bold text-slate-700">Total Pagado:</td>
                                        <td class="py-3 px-4 text-right font-extrabold text-base text-teal-700">
                                            $<?php echo number_format($pedido['total'], 2); ?>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Botones de Acción (Reimprimir y Volver a Pedir) -->
                        <div class="no-print flex flex-col sm:flex-row justify-end gap-3">
                            <button type="button" onclick="imprimirNotaPedido(<?php echo $pedido['id']; ?>)"
                                class="bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 px-4 py-2.5 rounded-xl font-bold text-sm transition-colors flex items-center justify-center gap-2">
                                <i class="fa-solid fa-print text-slate-500"></i> Reimprimir Nota
                            </button>

                            <a href="index.php?action=repetirPedido&id=<?php echo $pedido['id']; ?>"
                                class="bg-teal-600 hover:bg-teal-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm transition-colors flex items-center justify-center gap-2">
                                <i class="fa-solid fa-rotate-right"></i> Volver a pedir
                            </a>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function toggleDetallePedido(id) {
    const detalle = document.getElementById('detalle-pedido-' + id);
    const icono = document.getElementById('icono-pedido-' + id);
    if (detalle) {
        detalle.classList.toggle('hidden');
    }
    if (icono) {
        icono.classList.toggle('rotate-180');
    }
}

function imprimirNotaPedido(id) {
    // Asegurar que el detalle esté desplegado antes de imprimir
    const detalle = document.getElementById('detalle-pedido-' + id);
    if (detalle && detalle.classList.contains('hidden')) {
        detalle.classList.remove('hidden');
    }

    // Marcar únicamente la tarjeta seleccionada como imprimible
    document.querySelectorAll('.imprimible-activo').forEach(el => el.classList.remove('imprimible-activo'));
    const card = document.getElementById('card-pedido-' + id);
    if (card) {
        card.classList.add('imprimible-activo');
        window.print();
        card.classList.remove('imprimible-activo');
    }
}
</script>

<?php require_once 'views/includes/footer.php'; ?>