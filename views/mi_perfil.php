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
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl mb-6 flex items-center gap-3 text-sm font-medium">
            <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg"></i>
            <span>
                <?php
                if ($_GET['error'] == 'campos_vacios') echo "Por favor, completa todos los campos de contraseña.";
                elseif ($_GET['error'] == 'no_coinciden') echo "La nueva contraseña y la confirmación no coinciden.";
                elseif ($_GET['error'] == 'password_incorrecta') echo "La contraseña actual es incorrecta.";
                ?>
            </span>
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

        <form action="index.php?action=actualizarPerfil" method="POST" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre Completo</label>
                <input type="text" name="nombre" required value="<?php echo htmlspecialchars($cliente['nombre']); ?>" class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-medium">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Teléfono / WhatsApp</label>
                <input type="text" name="telefono" required value="<?php echo htmlspecialchars($cliente['telefono']); ?>" class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-medium">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Dirección Exacta</label>
                <textarea name="direccion" required class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-medium h-24"><?php echo htmlspecialchars($cliente['direccion']); ?></textarea>
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

        <form action="index.php?action=cambiarPassword" method="POST" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Contraseña Actual</label>
                <input type="password" name="password_actual" required placeholder="••••••••" class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-medium">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Nueva Contraseña</label>
                <input type="password" name="password_nueva" required placeholder="••••••••" class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-medium">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Confirmar Nueva Contraseña</label>
                <input type="password" name="password_confirmar" required placeholder="••••••••" class="w-full p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-medium">
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
        passCard.classList.add('hidden'); // Ocultar password si está abierto
    }

    function togglePasswordModal() {
        const vista = document.getElementById('cardVista');
        const passCard = document.getElementById('cardPassword');
        const edicion = document.getElementById('cardEdicion');

        vista.classList.toggle('hidden');
        passCard.classList.toggle('hidden');
        edicion.classList.add('hidden'); // Ocultar edición si está abierta
    }
</script>

<?php require_once 'views/includes/footer.php'; ?>