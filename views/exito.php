<?php require_once 'views/includes/header.php'; 
$idPedido = $_GET['pedido'] ?? '000';
$whatsappUrl = $_SESSION['whatsapp_url'] ?? '#';
?>

<div class="max-w-3xl mx-auto px-4 py-16 text-center">
    <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm space-y-6">
        <div class="w-20 h-20 bg-teal-50 text-teal-600 rounded-full flex items-center justify-center mx-auto text-3xl">
            <i class="fa-solid fa-check"></i>
        </div>
        
        <h2 class="text-3xl font-extrabold text-slate-900">¡Pedido #<?php echo htmlspecialchars($idPedido); ?> Registrado!</h2>
        <p class="text-slate-600 max-w-lg mx-auto">
            Tu compra se ha guardado correctamente en nuestro sistema. Para coordinar la entrega inmediata y el pago contra entrega, <b>por favor envíanos el detalle del pedido por WhatsApp con un solo clic:</b>
        </p>

        <div class="pt-4 flex flex-col sm:flex-row justify-center items-center gap-4">
            

            <a href="index.php?action=productos" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-6 py-3.5 rounded-xl font-bold transition-colors w-full sm:w-auto">
                Volver al catálogo
            </a>
        </div>
    </div>
</div>

<?php 
// Limpiamos la URL de WhatsApp de la sesión para que no se quede guardada
if(isset($_SESSION['whatsapp_url'])) {
    unset($_SESSION['whatsapp_url']);
}
require_once 'views/includes/footer.php'; 
?>