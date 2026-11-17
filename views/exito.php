<?php require_once 'views/includes/header.php'; 
$idPedido = intval($_GET['pedido'] ?? 0);
?>

<div class="max-w-3xl mx-auto px-4 py-16 text-center">
    <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm space-y-6">
        <div class="w-20 h-20 bg-teal-50 text-teal-600 rounded-full flex items-center justify-center mx-auto text-3xl">
            <i class="fa-solid fa-check"></i>
        </div>
        
        <h2 class="text-3xl font-extrabold text-slate-900">¡Pedido #<?php echo htmlspecialchars($idPedido); ?> Registrado!</h2>
        <p class="text-slate-600 max-w-lg mx-auto">
            Tu compra se ha guardado correctamente en nuestro sistema. Nuestro equipo ha recibido tu orden y se comunicará contigo pronto para coordinar la entrega y el pago contra entrega.
        </p>

        <div class="pt-4 flex flex-col sm:flex-row justify-center items-center gap-3 flex-wrap">
            <?php if ($idPedido > 0): ?>
                <a href="index.php?action=notaVenta&id=<?php echo $idPedido; ?>" target="_blank"
                    class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-3.5 rounded-xl font-bold shadow-md transition-colors w-full sm:w-auto flex items-center justify-center gap-2">
                    <i class="fa-solid fa-file-pdf"></i> Nota de Venta (PDF)
                </a>
            <?php endif; ?>

            <a href="index.php?action=misPedidos" class="bg-slate-800 hover:bg-slate-900 text-white px-6 py-3.5 rounded-xl font-bold transition-colors w-full sm:w-auto">
                Ver Mis Pedidos
            </a>

            <a href="index.php?action=productos" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-6 py-3.5 rounded-xl font-bold transition-colors w-full sm:w-auto">
                Volver al catálogo
            </a>
        </div>
    </div>
</div>

<?php require_once 'views/includes/footer.php'; ?>