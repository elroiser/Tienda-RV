<?php require_once 'views/includes/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 border-b pb-4 mt-2">
        <h2 class="text-3xl font-bold text-slate-900">Finalizar Pedido</h2>
        <p class="text-slate-600 mt-1">Ingresa tus datos para coordinar la entrega y el pago en efectivo contra entrega.</p>
    </div>

    <!-- Alertas de error del servidor -->
    <?php if (isset($_GET['error'])): ?>
        <?php
        $erroresCheckout = [
            'nombre_invalido'   => 'Por favor ingresa tu nombre y apellido real (solo letras).',
            'telefono_invalido' => 'Ingresa un número celular válido de Ecuador (10 dígitos empezando con 09).',
            'email_invalido'    => 'El formato del correo electrónico no es válido.',
            'direccion_corta'   => 'Por favor detalla un poco más tu dirección exacta de entrega (mínimo 10 caracteres).'
        ];
        $msgCheckout = $erroresCheckout[$_GET['error']] ?? 'Verifica los datos de envío antes de continuar.';
        ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-medium mb-6 flex items-center gap-2.5">
            <i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i>
            <span><?php echo htmlspecialchars($msgCheckout); ?></span>
        </div>
    <?php endif; ?>

    <form id="formCheckout" action="index.php?action=procesarPedido" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8" novalidate>

        <!-- Formulario de Datos del Cliente -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">

            <?php if (isset($clienteLogueado) && $clienteLogueado): ?>
                <div class="bg-teal-50 border border-teal-200 rounded-xl p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-user-check text-teal-600 text-xl"></i>
                        <p class="text-sm text-teal-900">Hola, <b><?php echo htmlspecialchars($clienteLogueado['nombre']); ?></b>. Tus datos se han cargado automáticamente.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex items-center justify-between text-sm text-slate-600">
                    <span>¿Ya tienes una cuenta registrada?</span>
                    <a href="index.php?action=login" class="text-teal-600 font-bold hover:underline">Inicia sesión aquí</a>
                </div>
            <?php endif; ?>

            <h3 class="text-xl font-bold text-slate-800 border-b pb-3">Información de Envío</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="chkNombre" class="block text-sm font-semibold text-slate-700 mb-1">Nombre y Apellido *</label>
                    <input type="text" id="chkNombre" name="nombre" required
                        value="<?php echo isset($clienteLogueado) ? htmlspecialchars($clienteLogueado['nombre']) : ''; ?>"
                        placeholder="Ej. Juan Pérez" class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-medium text-slate-800 transition-colors">
                    <p id="errChkNombre" class="text-xs text-rose-600 mt-1 hidden">Ingresa nombre y apellido (solo letras).</p>
                </div>
                <div>
                    <label for="chkTelefono" class="block text-sm font-semibold text-slate-700 mb-1">Teléfono / WhatsApp *</label>
                    <input type="tel" id="chkTelefono" name="telefono" required maxlength="10" inputmode="numeric"
                        value="<?php echo isset($clienteLogueado) ? htmlspecialchars($clienteLogueado['telefono']) : ''; ?>"
                        placeholder="Ej. 0991234567" class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-medium text-slate-800 transition-colors">
                    <p id="errChkTelefono" class="text-xs text-rose-600 mt-1 hidden">Debe tener 10 dígitos y empezar con 09.</p>
                </div>
            </div>

            <div>
                <label for="chkEmail" class="block text-sm font-semibold text-slate-700 mb-1">Correo Electrónico *</label>
                <input type="email" id="chkEmail" name="email" required
                    value="<?php echo isset($clienteLogueado) ? htmlspecialchars($clienteLogueado['email']) : ''; ?>"
                    placeholder="correo@ejemplo.com" class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-medium text-slate-800 transition-colors">
                <p id="errChkEmail" class="text-xs text-rose-600 mt-1 hidden">Ingresa un correo electrónico válido.</p>
            </div>

            <div>
                <label for="chkDireccion" class="block text-sm font-semibold text-slate-700 mb-1">Dirección Exacta de Entrega *</label>
                <input type="text" id="chkDireccion" name="direccion" required
                    value="<?php echo isset($clienteLogueado) ? htmlspecialchars($clienteLogueado['direccion']) : ''; ?>"
                    placeholder="Calle Principal y Secundaria, Barrio" class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-medium text-slate-800 transition-colors">
                <p id="errChkDireccion" class="text-xs text-rose-600 mt-1 hidden">Detalla tu dirección (mínimo 10 caracteres).</p>
            </div>

            <div>
                <label for="chkReferencia" class="block text-sm font-semibold text-slate-700 mb-1">Referencia del Lugar (Opcional)</label>
                <input type="text" id="chkReferencia" name="referencia" placeholder="Ej. Casa de rejas blancas" class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-medium text-slate-800">
            </div>
        </div>

        <!-- Resumen Lateral -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm h-fit space-y-4">
            <h3 class="text-xl font-bold text-slate-800 border-b pb-3">Resumen de tu Compra</h3>

            <div class="space-y-2 max-h-48 overflow-y-auto pr-2">
                <?php foreach ($carrito as $item): ?>
                    <div class="flex justify-between text-sm text-slate-600">
                        <span class="truncate max-w-[180px]"><?php echo $item['cantidad']; ?>x <?php echo htmlspecialchars($item['nombre_base']); ?></span>
                        <span class="font-semibold text-slate-800">$<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="border-t pt-4 space-y-2 text-sm text-slate-600">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span class="font-semibold text-slate-800">$<?php echo number_format($totalGeneral, 2); ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Envío</span>
                    <span class="font-semibold text-teal-600">Gratis</span>
                </div>
                <div class="border-t pt-3 flex justify-between text-base font-bold text-slate-900">
                    <span>Total a Pagar</span>
                    <span class="text-2xl text-teal-700">$<?php echo number_format($totalGeneral, 2); ?></span>
                </div>
            </div>

            <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white py-3.5 rounded-xl font-bold shadow-lg transition-colors flex items-center justify-center cursor-pointer">
                Confirmar y Enviar Pedido <i class="fa-solid fa-check ml-2"></i>
            </button>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formCheckout');
    const inpNombre = document.getElementById('chkNombre');
    const inpTel = document.getElementById('chkTelefono');
    const inpEmail = document.getElementById('chkEmail');
    const inpDir = document.getElementById('chkDireccion');

    function marcarEstado(input, errorId, esValido) {
        const errorEl = document.getElementById(errorId);
        if (esValido) {
            input.classList.remove('border-rose-500');
            input.classList.add('border-emerald-500');
            errorEl.classList.add('hidden');
        } else {
            input.classList.remove('border-emerald-500');
            input.classList.add('border-rose-500');
            errorEl.classList.remove('hidden');
        }
        return esValido;
    }

    function validarNombre() {
        const val = inpNombre.value.trim().replace(/\s+/g, ' ');
        const partes = val.split(' ');
        return marcarEstado(inpNombre, 'errChkNombre', /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]{5,80}$/.test(val) && partes.length >= 2 && partes[1].length >= 2);
    }

    function validarTelefono() {
        inpTel.value = inpTel.value.replace(/[^0-9]/g, '').slice(0, 10);
        return marcarEstado(inpTel, 'errChkTelefono', /^09[0-9]{8}$/.test(inpTel.value));
    }

    function validarEmail() {
        return marcarEstado(inpEmail, 'errChkEmail', /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(inpEmail.value.trim()));
    }

    function validarDireccion() {
        return marcarEstado(inpDir, 'errChkDireccion', inpDir.value.trim().length >= 10);
    }

    inpNombre.addEventListener('blur', validarNombre);
    inpTel.addEventListener('input', validarTelefono);
    inpEmail.addEventListener('blur', validarEmail);
    inpDir.addEventListener('blur', validarDireccion);

    form.addEventListener('submit', function(e) {
        const ok1 = validarNombre();
        const ok2 = validarTelefono();
        const ok3 = validarEmail();
        const ok4 = validarDireccion();
        if (!ok1 || !ok2 || !ok3 || !ok4) {
            e.preventDefault();
        }
    });
});
</script>

<?php require_once 'views/includes/footer.php'; ?>