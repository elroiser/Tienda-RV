<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['cliente_id'])) {
    header("Location: index.php?action=login");
    exit();
}
require_once 'views/includes/header.php';
?>

<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="mb-8 border-b pb-4">
        <h2 class="text-3xl font-bold text-slate-900">Mi Perfil</h2>
        <p class="text-slate-600 mt-1">Visualiza y actualiza tu información personal y de seguridad.</p>
    </div>

    <!-- Alertas de éxito o error -->
    <?php if (isset($_GET['actualizado']) && $_GET['actualizado'] == 1): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl mb-6 flex items-center gap-3 text-sm font-medium">
            <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
            Tus datos han sido actualizados correctamente.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['password_cambiada']) && $_GET['password_cambiada'] == 1): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl mb-6 flex items-center gap-3 text-sm font-medium">
            <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
            Tu contraseña ha sido cambiada exitosamente.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <?php
        $mensajesPerfil = [
            'campos_vacios'       => 'Por favor, completa todos los campos obligatorios.',
            'no_coinciden'        => 'La nueva contraseña y la confirmación no coinciden.',
            'password_incorrecta' => 'La contraseña actual es incorrecta.',
            'password_corta'      => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'nombre_invalido'     => 'Ingresa tu nombre y apellido real (solo letras).',
            'telefono_invalido'   => 'El celular debe tener 10 dígitos y empezar con 09.',
            'direccion_corta'     => 'La dirección debe tener al menos 10 caracteres.'
        ];
        $textoErrorPerfil = $mensajesPerfil[$_GET['error']] ?? 'Ocurrió un error al validar los datos.';
        ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl mb-6 flex items-center gap-3 text-sm font-medium">
            <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg"></i>
            <span><?php echo htmlspecialchars($textoErrorPerfil); ?></span>
        </div>
    <?php endif; ?>

    <!-- TARJETA DE VISTA DE DATOS -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6" id="cardVista">
        <div class="p-6 bg-slate-50 border-b border-slate-100 flex flex-wrap justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-teal-100 text-teal-700 rounded-full flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-lg"><?php echo htmlspecialchars($cliente['nombre']); ?></h3>
                    <p class="text-xs text-slate-500"><?php echo htmlspecialchars($cliente['email']); ?></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="togglePasswordModal()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-xl text-sm font-bold transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-key"></i> Cambiar Clave
                </button>
                <button onclick="toggleEdicion()" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-sm transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square"></i> Modificar Datos
                </button>
            </div>
        </div>

        <div class="p-6 space-y-4 text-sm text-slate-600">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <span class="block text-xs font-bold text-slate-400 uppercase">Nombre Completo</span>
                    <p class="font-semibold text-slate-800 mt-1"><?php echo htmlspecialchars($cliente['nombre']); ?></p>
                </div>
                <div>
                    <span class="block text-xs font-bold text-slate-400 uppercase">Correo Electrónico</span>
                    <p class="font-semibold text-slate-800 mt-1"><?php echo htmlspecialchars($cliente['email']); ?></p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t pt-4">
                <div>
                    <span class="block text-xs font-bold text-slate-400 uppercase">Teléfono / WhatsApp</span>
                    <p class="font-semibold text-slate-800 mt-1"><?php echo htmlspecialchars($cliente['telefono']); ?></p>
                </div>
                <div>
                    <span class="block text-xs font-bold text-slate-400 uppercase">Ciudad</span>
                    <p class="font-semibold text-slate-800 mt-1"><?php echo htmlspecialchars($cliente['ciudad'] ?? 'Pasaje, El Oro'); ?></p>
                </div>
            </div>
            <div class="border-t pt-4">
                <span class="block text-xs font-bold text-slate-400 uppercase">Dirección de Entrega</span>
                <p class="font-semibold text-slate-800 mt-1"><?php echo htmlspecialchars($cliente['direccion']); ?></p>
            </div>
        </div>
    </div>

    <!-- FORMULARIO DE EDICIÓN DE DATOS (Oculto por defecto) -->
    <div id="cardEdicion" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden hidden mb-6">
        <div class="p-6 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-bold text-slate-800 text-lg">Editar Mis Datos</h3>
            <button onclick="toggleEdicion()" class="text-slate-400 hover:text-slate-600 font-bold text-sm">
                <i class="fa-solid fa-xmark text-lg"></i> Cancelar
            </button>
        </div>

        <form id="formEditarPerfil" action="index.php?action=actualizarPerfil" method="POST" class="p-6 space-y-4" novalidate>
            <div>
                <label for="perfNombre" class="block text-sm font-semibold text-slate-700 mb-1">Nombre y Apellido *</label>
                <input type="text" id="perfNombre" name="nombre" required value="<?php echo htmlspecialchars($cliente['nombre']); ?>" class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-medium transition-colors">
                <p id="errPerfNombre" class="text-xs text-rose-600 mt-1 hidden">Ingresa nombre y apellido (solo letras).</p>
            </div>

            <div>
                <label for="perfTelefono" class="block text-sm font-semibold text-slate-700 mb-1">Teléfono / WhatsApp *</label>
                <input type="tel" id="perfTelefono" name="telefono" required maxlength="10" inputmode="numeric" value="<?php echo htmlspecialchars($cliente['telefono']); ?>" class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-medium transition-colors">
                <p id="errPerfTelefono" class="text-xs text-rose-600 mt-1 hidden">Debe tener 10 dígitos y empezar con 09.</p>
            </div>

            <div>
                <label for="perfDireccion" class="block text-sm font-semibold text-slate-700 mb-1">Dirección Exacta *</label>
                <textarea id="perfDireccion" name="direccion" required class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-medium h-24 transition-colors"><?php echo htmlspecialchars($cliente['direccion']); ?></textarea>
                <p id="errPerfDireccion" class="text-xs text-rose-600 mt-1 hidden">Detalla tu dirección (mínimo 10 caracteres).</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="toggleEdicion()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-2.5 rounded-xl font-bold text-sm transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-2.5 rounded-xl font-bold text-sm shadow-sm transition-colors">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>

    <!-- FORMULARIO DE CAMBIO DE CONTRASEÑA (Oculto por defecto) -->
    <div id="cardPassword" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden hidden">
        <div class="p-6 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                <i class="fa-solid fa-key text-teal-600"></i> Cambiar Contraseña
            </h3>
            <button onclick="togglePasswordModal()" class="text-slate-400 hover:text-slate-600 font-bold text-sm">
                <i class="fa-solid fa-xmark text-lg"></i> Cancelar
            </button>
        </div>

        <form id="formCambiarPassword" action="index.php?action=cambiarPassword" method="POST" class="p-6 space-y-4" novalidate>
            <div>
                <label for="passActual" class="block text-sm font-semibold text-slate-700 mb-1">Contraseña Actual *</label>
                <div id="boxPassActual" class="flex items-center border border-slate-300 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-teal-500 bg-white">
                    <input type="password" id="passActual" name="password_actual" required placeholder="••••••••" class="w-full p-3 outline-none font-medium border-0 bg-transparent">
                    <button type="button" data-toggle-pass="passActual" tabindex="-1" class="btn-toggle-pass px-4 py-3 text-slate-500 hover:text-teal-600 cursor-pointer shrink-0">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                <p id="errPassActual" class="text-xs text-rose-600 mt-1 hidden">Ingresa tu contraseña actual.</p>
            </div>

            <div>
                <label for="passNueva" class="block text-sm font-semibold text-slate-700 mb-1">Nueva Contraseña *</label>
                <div id="boxPassNueva" class="flex items-center border border-slate-300 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-teal-500 bg-white">
                    <input type="password" id="passNueva" name="password_nueva" required minlength="6" placeholder="Mínimo 6 caracteres" class="w-full p-3 outline-none font-medium border-0 bg-transparent">
                    <button type="button" data-toggle-pass="passNueva" tabindex="-1" class="btn-toggle-pass px-4 py-3 text-slate-500 hover:text-teal-600 cursor-pointer shrink-0">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                <p id="errPassNueva" class="text-xs text-rose-600 mt-1 hidden">La nueva contraseña debe tener al menos 6 caracteres.</p>
            </div>

            <div>
                <label for="passConfirmar" class="block text-sm font-semibold text-slate-700 mb-1">Confirmar Nueva Contraseña *</label>
                <div id="boxPassConfirmar" class="flex items-center border border-slate-300 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-teal-500 bg-white">
                    <input type="password" id="passConfirmar" name="password_confirmar" required placeholder="Repite la nueva contraseña" class="w-full p-3 outline-none font-medium border-0 bg-transparent">
                    <button type="button" data-toggle-pass="passConfirmar" tabindex="-1" class="btn-toggle-pass px-4 py-3 text-slate-500 hover:text-teal-600 cursor-pointer shrink-0">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                <p id="errPassConfirmar" class="text-xs text-rose-600 mt-1 hidden">Las contraseñas no coinciden.</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="togglePasswordModal()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-2.5 rounded-xl font-bold text-sm transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-2.5 rounded-xl font-bold text-sm shadow-sm transition-colors">
                    Actualizar Contraseña
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleEdicion() {
        const vista = document.getElementById('cardVista');
        const edicion = document.getElementById('cardEdicion');
        const passCard = document.getElementById('cardPassword');
        
        vista.classList.toggle('hidden');
        edicion.classList.toggle('hidden');
        passCard.classList.add('hidden');
    }

    function togglePasswordModal() {
        const vista = document.getElementById('cardVista');
        const passCard = document.getElementById('cardPassword');
        const edicion = document.getElementById('cardEdicion');

        vista.classList.toggle('hidden');
        passCard.classList.toggle('hidden');
        edicion.classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Activar los botones del ojito en los 3 campos de contraseña
        document.querySelectorAll('.btn-toggle-pass').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const input = document.getElementById(this.dataset.togglePass);
                const icono = this.querySelector('i');
                if (!input || !icono) return;

                const esPass = input.type === 'password';
                input.type = esPass ? 'text' : 'password';
                icono.classList.toggle('fa-eye', !esPass);
                icono.classList.toggle('fa-eye-slash', esPass);
            });
        });

        function marcarEstado(input, errorId, esValido) {
            const errorEl = document.getElementById(errorId);
            // Si el input está dentro de un contenedor flex con borde (como boxPassNueva), pintamos el contenedor
            const targetBorde = input.parentElement.classList.contains('flex') ? input.parentElement : input;

            if (esValido) {
                targetBorde.classList.remove('border-rose-500');
                targetBorde.classList.add('border-emerald-500');
                errorEl.classList.add('hidden');
            } else {
                targetBorde.classList.remove('border-emerald-500');
                targetBorde.classList.add('border-rose-500');
                errorEl.classList.remove('hidden');
            }
            return esValido;
        }

        // Validación Editar Perfil
        const formPerfil = document.getElementById('formEditarPerfil');
        const inpNombre = document.getElementById('perfNombre');
        const inpTel = document.getElementById('perfTelefono');
        const inpDir = document.getElementById('perfDireccion');

        inpTel.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
            marcarEstado(inpTel, 'errPerfTelefono', /^09[0-9]{8}$/.test(this.value));
        });

        formPerfil.addEventListener('submit', function(e) {
            const nom = inpNombre.value.trim().replace(/\s+/g, ' ');
            const partes = nom.split(' ');
            const okNom = marcarEstado(inpNombre, 'errPerfNombre', /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]{5,80}$/.test(nom) && partes.length >= 2 && partes[1].length >= 2);
            const okTel = marcarEstado(inpTel, 'errPerfTelefono', /^09[0-9]{8}$/.test(inpTel.value));
            const okDir = marcarEstado(inpDir, 'errPerfDireccion', inpDir.value.trim().length >= 10);

            if (!okNom || !okTel || !okDir) e.preventDefault();
        });

        // Validación Cambiar Contraseña
        const formPass = document.getElementById('formCambiarPassword');
        const pActual = document.getElementById('passActual');
        const pNueva = document.getElementById('passNueva');
        const pConf = document.getElementById('passConfirmar');

        pConf.addEventListener('input', function() {
            marcarEstado(pConf, 'errPassConfirmar', pConf.value === pNueva.value && pConf.value.length >= 6);
        });

        formPass.addEventListener('submit', function(e) {
            const okAct = marcarEstado(pActual, 'errPassActual', pActual.value.length > 0);
            const okNue = marcarEstado(pNueva, 'errPassNueva', pNueva.value.length >= 6);
            const okCon = marcarEstado(pConf, 'errPassConfirmar', pConf.value === pNueva.value && pConf.value.length >= 6);

            if (!okAct || !okNue || !okCon) e.preventDefault();
        });
    });
</script>

<?php require_once 'views/includes/footer.php'; ?>