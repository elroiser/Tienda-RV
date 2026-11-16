<?php
require_once 'views/includes/header.php';

// Recuperar datos previos si hubo algún error de validación
$prev = $_SESSION['form_registro'] ?? [];
unset($_SESSION['form_registro']);
$redirect = $_GET['redirect'] ?? ($_POST['redirect'] ?? 'home');
?>

<div class="max-w-md mx-auto bg-white rounded-2xl shadow-sm border border-slate-100 p-8 my-10">
    <div class="text-center mb-7">
        <h2 class="text-2xl font-bold text-slate-900">Crear Cuenta</h2>
        <p class="text-slate-500 text-sm mt-1">Para realizar y gestionar tus pedidos fácilmente.</p>
    </div>

    <!-- Mensajes de alerta del servidor -->
    <?php if (isset($_GET['error'])): ?>
        <?php
        $mensajesError = [
            'email_existe'      => 'Este correo electrónico ya está registrado. Intenta iniciar sesión.',
            'correo_existente'  => 'Este correo electrónico ya está registrado. Intenta iniciar sesión.',
            'campos_vacios'     => 'Por favor, completa todos los campos obligatorios.',
            'nombre_invalido'   => 'Ingresa tu nombre y apellido real (solo letras).',
            'email_invalido'    => 'El formato del correo electrónico no es válido.',
            'password_corta'    => 'La contraseña debe tener al menos 6 caracteres.',
            'telefono_invalido' => 'Ingresa un número celular válido de Ecuador (10 dígitos, ej. 0987654321).',
            'direccion_corta'   => 'Por favor detalla un poco más tu dirección de entrega.'
        ];
        $textoError = $mensajesError[$_GET['error']] ?? 'Verifica los datos ingresados e intenta nuevamente.';
        ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-medium mb-5 flex items-start gap-2.5" role="alert">
            <i class="fa-solid fa-circle-exclamation mt-0.5 text-rose-500"></i>
            <span><?php echo htmlspecialchars($textoError); ?></span>
        </div>
    <?php endif; ?>

    <form id="formRegistro" action="index.php?action=procesar_registro" method="POST" class="space-y-4" novalidate>
        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

        <!-- Nombre Completo -->
        <div>
            <label for="regNombre" class="block text-sm font-semibold text-slate-700 mb-1">Nombre y Apellido *</label>
            <input required type="text" id="regNombre" name="nombre"
                value="<?php echo htmlspecialchars($prev['nombre'] ?? ''); ?>"
                class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-colors"
                placeholder="Ej: Juan Pérez">
            <p id="errNombre" class="text-xs text-rose-600 mt-1 hidden">Ingresa tu nombre y apellido (solo letras).</p>
        </div>

        <!-- Correo Electrónico -->
        <div>
            <label for="regEmail" class="block text-sm font-semibold text-slate-700 mb-1">Correo Electrónico *</label>
            <input required type="email" id="regEmail" name="email"
                value="<?php echo htmlspecialchars($prev['email'] ?? ''); ?>"
                class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-colors"
                placeholder="correo@ejemplo.com">
            <p id="errEmail" class="text-xs text-rose-600 mt-1 hidden">Ingresa un correo electrónico válido (ej. usuario@gmail.com).</p>
        </div>

        <!-- Contraseña con botón mostrar/ocultar y barra de seguridad -->
        <div>
            <label for="regPassword" class="block text-sm font-semibold text-slate-700 mb-1">Contraseña *</label>
            <div class="relative">
                <input required type="password" id="regPassword" name="password" minlength="6"
                    class="w-full p-3 pr-11 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-colors"
                    placeholder="Mínimo 6 caracteres">
                <button type="button" id="btnTogglePass" tabindex="-1"
                    class="absolute inset-y-0 right-0 px-3.5 flex items-center text-slate-400 hover:text-slate-600">
                    <i id="iconoOjo" class="fa-regular fa-eye"></i>
                </button>
            </div>
            <!-- Barra de fortaleza -->
            <div class="mt-2 flex items-center gap-2">
                <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div id="barraFuerza" class="h-full w-0 transition-all duration-300 rounded-full"></div>
                </div>
                <span id="textoFuerza" class="text-[11px] font-semibold text-slate-400">Mín. 6 caracteres</span>
            </div>
            <p id="errPassword" class="text-xs text-rose-600 mt-1 hidden">La contraseña debe tener al menos 6 caracteres.</p>
        </div>

        <!-- Teléfono Celular Ecuador -->
        <div>
            <label for="regTelefono" class="block text-sm font-semibold text-slate-700 mb-1">Teléfono Celular (WhatsApp) *</label>
            <div class="relative">
                <input required type="tel" id="regTelefono" name="telefono" maxlength="10" inputmode="numeric"
                    value="<?php echo htmlspecialchars($prev['telefono'] ?? ''); ?>"
                    class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-colors"
                    placeholder="0987654321">
            </div>
            <p id="errTelefono" class="text-xs text-rose-600 mt-1 hidden">Debe tener 10 dígitos y empezar con 09 (ej. 0987654321).</p>
        </div>

        <!-- Dirección de Entrega -->
        <div>
            <label for="regDireccion" class="block text-sm font-semibold text-slate-700 mb-1">Dirección de entrega (Pasaje) *</label>
            <textarea required id="regDireccion" name="direccion"
                class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none h-20 transition-colors"
                placeholder="Calle principal, secundaria y referencia de casa/local..."><?php echo htmlspecialchars($prev['direccion'] ?? ''); ?></textarea>
            <p id="errDireccion" class="text-xs text-rose-600 mt-1 hidden">Detalla tu dirección (mínimo 10 caracteres).</p>
        </div>

        <button type="submit" id="btnSubmitRegistro"
            class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-sm transition-all mt-4 cursor-pointer">
            Crear Cuenta
        </button>

        <p class="text-center text-sm text-slate-500 pt-2">
            ¿Ya tienes una cuenta?
            <a href="index.php?action=login" class="text-teal-700 font-bold hover:underline">Inicia sesión aquí</a>
        </p>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formRegistro');
    const inpNombre = document.getElementById('regNombre');
    const inpEmail = document.getElementById('regEmail');
    const inpPass = document.getElementById('regPassword');
    const inpTel = document.getElementById('regTelefono');
    const inpDir = document.getElementById('regDireccion');

    const btnTogglePass = document.getElementById('btnTogglePass');
    const iconoOjo = document.getElementById('iconoOjo');
    const barraFuerza = document.getElementById('barraFuerza');
    const textoFuerza = document.getElementById('textoFuerza');

    // Mostrar / Ocultar contraseña
    btnTogglePass.addEventListener('click', function() {
        const esPassword = inpPass.type === 'password';
        inpPass.type = esPassword ? 'text' : 'password';
        iconoOjo.classList.toggle('fa-eye', !esPassword);
        iconoOjo.classList.toggle('fa-eye-slash', esPassword);
    });

    // Solo permitir números en el teléfono
    inpTel.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
        validarCampo(inpTel, 'errTelefono', /^09[0-9]{8}$/.test(this.value));
    });

    // Helper visual de estado válido / inválido
    function validarCampo(input, errorId, esValido) {
        const errorEl = document.getElementById(errorId);
        if (esValido) {
            input.classList.remove('border-rose-500', 'focus:ring-rose-500');
            input.classList.add('border-emerald-500', 'focus:ring-emerald-500');
            errorEl.classList.add('hidden');
        } else {
            input.classList.remove('border-emerald-500', 'focus:ring-emerald-500');
            input.classList.add('border-rose-500', 'focus:ring-rose-500');
            errorEl.classList.remove('hidden');
        }
        return esValido;
    }

    function validarNombre() {
        const val = inpNombre.value.trim().replace(/\s+/g, ' ');
        const partes = val.split(' ');
        const valido = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]{5,80}$/.test(val) && partes.length >= 2 && partes[1].length >= 2;
        return validarCampo(inpNombre, 'errNombre', valido);
    }

    function validarEmail() {
        const val = inpEmail.value.trim();
        const valido = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(val);
        return validarCampo(inpEmail, 'errEmail', valido);
    }

    function validarPassword() {
        const val = inpPass.value;
        // Actualizar barra visual de fortaleza
        let fuerza = 0;
        if (val.length >= 6) fuerza++;
        if (val.length >= 8 && /[A-Z]/.test(val)) fuerza++;
        if (/[0-9]/.test(val) && /[^A-Za-z0-9]/.test(val)) fuerza++;

        if (val.length === 0) {
            barraFuerza.style.width = '0%';
            textoFuerza.textContent = 'Mín. 6 caracteres';
            textoFuerza.className = 'text-[11px] font-semibold text-slate-400';
        } else if (val.length < 6) {
            barraFuerza.style.width = '30%';
            barraFuerza.className = 'h-full transition-all duration-300 rounded-full bg-rose-500';
            textoFuerza.textContent = 'Muy corta';
            textoFuerza.className = 'text-[11px] font-semibold text-rose-600';
        } else if (fuerza === 1) {
            barraFuerza.style.width = '60%';
            barraFuerza.className = 'h-full transition-all duration-300 rounded-full bg-amber-500';
            textoFuerza.textContent = 'Aceptable';
            textoFuerza.className = 'text-[11px] font-semibold text-amber-600';
        } else {
            barraFuerza.style.width = '100%';
            barraFuerza.className = 'h-full transition-all duration-300 rounded-full bg-emerald-500';
            textoFuerza.textContent = 'Segura';
            textoFuerza.className = 'text-[11px] font-semibold text-emerald-600';
        }

        return validarCampo(inpPass, 'errPassword', val.length >= 6);
    }

    function validarDireccion() {
        const val = inpDir.value.trim();
        return validarCampo(inpDir, 'errDireccion', val.length >= 10);
    }

    inpNombre.addEventListener('blur', validarNombre);
    inpEmail.addEventListener('blur', validarEmail);
    inpPass.addEventListener('input', validarPassword);
    inpDir.addEventListener('blur', validarDireccion);

    form.addEventListener('submit', function(e) {
        const okNombre = validarNombre();
        const okEmail = validarEmail();
        const okPass = validarPassword();
        const okTel = validarCampo(inpTel, 'errTelefono', /^09[0-9]{8}$/.test(inpTel.value));
        const okDir = validarDireccion();

        if (!okNombre || !okEmail || !okPass || !okTel || !okDir) {
            e.preventDefault();
        }
    });
});
</script>

<?php require_once 'views/includes/footer.php'; ?>