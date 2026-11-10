<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Pedidos | RV Limpieza</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen text-slate-800">

    <!-- Barra superior del Admin -->
    <header class="bg-slate-900 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="font-bold text-lg flex items-center gap-2">
                <i class="fa-solid fa-gauge-high text-teal-400"></i> RV Limpieza - Panel de Control
            </h1>
            <div class="flex items-center gap-4">
                <span class="text-sm text-slate-300">Hola, <b><?php echo $_SESSION['admin_user']; ?></b></span>
                <a href="index.php?action=adminLogout" class="bg-rose-600 hover:bg-rose-700 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                    <i class="fa-solid fa-right-from-bracket mr-1"></i> Salir
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8 border-b pb-4">
            <h2 class="text-3xl font-bold text-slate-900">Gestión de Pedidos</h2>
            <p class="text-slate-600 mt-1">Monitorea las ventas y los detalles exactos de los productos solicitados.</p>
        </div>

        <?php if(empty($pedidos)): ?>
            <div class="text-center py-16 bg-white rounded-2xl border border-slate-200 shadow-sm">
                <i class="fa-solid fa-clipboard-list text-5xl text-slate-300 mb-4"></i>
                <h3 class="text-xl font-bold text-slate-700 mb-2">No hay pedidos registrados todavía</h3>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach($pedidos as $pedido): ?>
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="bg-slate-50 p-5 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div>
                                <span class="bg-teal-100 text-teal-800 text-xs font-extrabold px-3 py-1 rounded-full uppercase">
                                    Orden #<?php echo $pedido['id']; ?>
                                </span>
                                <h3 class="text-lg font-bold text-slate-800 mt-2">
                                    Cliente: <span class="text-teal-700"><?php echo htmlspecialchars($pedido['cliente_nombre']); ?></span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    <i class="fa-solid fa-calendar mr-1"></i> <?php echo $pedido['fecha_pedido']; ?> | 
                                    <i class="fa-solid fa-phone ml-1 mr-1"></i> <?php echo htmlspecialchars($pedido['cliente_telefono']); ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold text-slate-500 block">Total a Cobrar</span>
                                <span class="text-2xl font-black text-teal-700">$<?php echo number_format($pedido['total'], 2); ?></span>
                                <span class="block text-[11px] text-amber-600 font-bold bg-amber-50 px-2 py-0.5 rounded border border-amber-200 mt-1">
                                    <?php echo htmlspecialchars($pedido['metodo_pago']); ?>
                                </span>
                            </div>
                        </div>

                        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                            <div class="md:col-span-1 bg-slate-50 p-4 rounded-xl border border-slate-100 text-sm space-y-1">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Dirección de Envío</p>
                                <p class="font-semibold text-slate-800"><i class="fa-solid fa-location-dot text-teal-600 mr-1"></i> <?php echo htmlspecialchars($pedido['direccion_envio']); ?></p>
                                <?php if(!empty($pedido['referencia_envio'])): ?>
                                    <p class="text-xs text-slate-500">Ref: <?php echo htmlspecialchars($pedido['referencia_envio']); ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="md:col-span-2 space-y-2">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Productos en este pedido</p>
                                <div class="divide-y divide-slate-100">
                                    <?php foreach($pedido['detalles'] as $item): ?>
                                        <div class="py-2 flex justify-between items-center text-sm">
                                            <div class="flex items-center gap-2">
                                                <span class="bg-teal-50 text-teal-700 font-bold px-2 py-0.5 rounded text-xs"><?php echo $item['cantidad']; ?>x</span>
                                                <span class="font-medium text-slate-800"><?php echo htmlspecialchars($item['nombre']); ?></span>
                                            </div>
                                            <span class="font-semibold text-slate-600">$<?php echo number_format($item['precio_unitario'] * $item['cantidad'], 2); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
</body>
</html>
