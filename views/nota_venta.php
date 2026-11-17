<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota de Venta #<?php echo str_pad($pedido['id'], 5, '0', STR_PAD_LEFT); ?> - RV Limpieza</title>
    <link rel="icon" type="image/png" href="public/img/logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .hoja-recibo {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                max-width: 100% !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 py-8 px-4 font-sans">

    <!-- Barra superior de acciones (no se imprime) -->
    <div class="no-print max-w-2xl mx-auto mb-5 flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <a href="index.php?action=misPedidos" class="text-slate-600 hover:text-teal-700 font-bold text-sm flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Volver a Mis Pedidos
        </a>
        <div class="flex items-center gap-2.5">
            <button onclick="window.print()" class="bg-teal-600 hover:bg-teal-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm flex items-center gap-2 cursor-pointer transition-colors">
                <i class="fa-solid fa-file-pdf"></i> Descargar PDF / Imprimir
            </button>
        </div>
    </div>

    <!-- Comprobante / Nota de Venta -->
    <div class="hoja-recibo max-w-2xl mx-auto bg-white p-8 sm:p-10 rounded-2xl border border-slate-200 shadow-md">
        
        <!-- Cabecera Empresa -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b-2 border-teal-600 pb-6 gap-4">
            <div class="flex items-center gap-4">
                <img src="public/img/logo.png" alt="RV Limpieza" class="w-16 h-16 rounded-full border-2 border-teal-600 object-cover bg-white">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">RV LIMPIEZA</h1>
                    <p class="text-xs font-bold text-teal-700 uppercase tracking-wider">Distribuidor Autorizado Chemlook</p>
                    <p class="text-xs text-slate-500 mt-1">Pasaje, El Oro - Ecuador</p>
                    <p class="text-xs text-slate-500">Ing. Roiser Valerio Reyes Perez</p>
                    <p class="text-xs text-slate-500">WhatsApp: 096 965 9950</p>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-200 rounded-xl px-5 py-3 text-left sm:text-right w-full sm:w-auto">
                <span class="text-[11px] font-bold uppercase text-slate-400 block">Nota de Venta</span>
                <span class="text-xl font-extrabold text-teal-700">N° <?php echo str_pad($pedido['id'], 5, '0', STR_PAD_LEFT); ?></span>
                <span class="text-xs text-slate-500 block mt-1">
                    Fecha: <b><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></b>
                </span>
            </div>
        </div>

        <!-- Datos del Cliente -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 my-6 bg-slate-50/80 p-4 rounded-xl border border-slate-200/80 text-sm">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase mb-1">Cliente</p>
                <p class="font-bold text-slate-800"><?php echo htmlspecialchars($pedido['cliente_nombre']); ?></p>
                <p class="text-xs text-slate-600 mt-0.5">Tel: <?php echo htmlspecialchars($pedido['cliente_telefono']); ?></p>
                <p class="text-xs text-slate-600"><?php echo htmlspecialchars($pedido['cliente_email']); ?></p>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase mb-1">Entrega y Pago</p>
                <p class="text-xs text-slate-800 font-medium"><?php echo htmlspecialchars($pedido['direccion_envio']); ?></p>
                <?php if (!empty($pedido['referencia_envio'])): ?>
                    <p class="text-xs text-slate-500 mt-0.5">Ref: <?php echo htmlspecialchars($pedido['referencia_envio']); ?></p>
                <?php endif; ?>
                <p class="text-xs text-teal-700 font-bold mt-1">Pago: <?php echo htmlspecialchars($pedido['metodo_pago']); ?></p>
            </div>
        </div>

        <!-- Detalle de Productos -->
        <div class="overflow-hidden border border-slate-200 rounded-xl mb-6">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-teal-700 text-white text-xs uppercase">
                        <th class="py-3 px-4">Cant.</th>
                        <th class="py-3 px-4">Descripción / Presentación</th>
                        <th class="py-3 px-4 text-right">P. Unit.</th>
                        <th class="py-3 px-4 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php 
                    $totalUnidades = 0;
                    foreach ($detalles as $det): 
                        $totalUnidades += intval($det['cantidad']);
                        $subtotalLinea = floatval($det['precio_unitario']) * intval($det['cantidad']);
                    ?>
                        <tr>
                            <td class="py-3 px-4 font-bold text-slate-800 text-center w-16">
                                <?php echo intval($det['cantidad']); ?>
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-800">
                                <?php echo htmlspecialchars($det['producto_nombre']); ?>
                            </td>
                            <td class="py-3 px-4 text-right text-slate-600">
                                $<?php echo number_format($det['precio_unitario'], 2); ?>
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-slate-900">
                                $<?php echo number_format($subtotalLinea, 2); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Totales -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 pt-2">
            <div class="text-xs text-slate-500 space-y-1">
                <p>Total de artículos: <b><?php echo $totalUnidades; ?></b></p>
                <p>Estado del pedido: <b><?php echo htmlspecialchars($pedido['estado']); ?></b></p>
                <p class="italic text-slate-400 pt-2">Documento de control interno y entrega — RV Limpieza.</p>
            </div>

            <div class="w-full sm:w-64 bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-2 text-sm">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span class="font-semibold">$<?php echo number_format($pedido['total'], 2); ?></span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Envío:</span>
                    <span class="font-semibold text-teal-700">$0.00</span>
                </div>
                <div class="border-t border-slate-300 pt-2 flex justify-between items-center text-base font-extrabold text-slate-900">
                    <span>TOTAL:</span>
                    <span class="text-xl text-teal-700">$<?php echo number_format($pedido['total'], 2); ?></span>
                </div>
            </div>
        </div>

    </div>

</body>
</html>