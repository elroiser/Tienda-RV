<?php require_once 'views/includes/header.php'; 
$redirect = $_GET['redirect'] ?? 'home';
$error = $_GET['error'] ?? null;
?>

<div class="max-w-md mx-auto px-4 py-16">
    <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
        
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-teal-50 text-teal-600 rounded-full flex items-center justify-center mx-auto text-2xl mb-4">
                <i class="fa-solid fa-user-lock"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-900">Iniciar Sesión</h2>
            <?php if($redirect === 'checkout'): ?>
                <p class="text-teal-600 font-medium mt-2 text-sm bg-teal-50 py-2 px-3 rounded-lg">
                    Inicia sesión o regístrate para finalizar tu pedido. ¡Tu carrito está a salvo!
                </p>
            <?php else: ?>
                <p class="text-slate-500 mt-1 text-sm">Bienvenido de nuevo a RV Limpieza</p>
            <?php endif; ?>
        </div>

        <?php if($error): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-700 p-3.5 rounded-xl text-sm font-medium mb-6 flex items-center gap-2.5">
                <i class="fa-solid fa-circle-exclamation text-rose-500 text-base"></i>
                <span>Correo o contraseña incorrectos. Verifica tus datos.</span>
            </div>
        <?php endif; ?>

        <form id="formLogin" action="index.php?action=procesarLogin" method="POST" class="space-y-5" novalidate>
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

            <!-- Correo Electrónico -->
            <div>
                <label for="loginEmail" class="block text-sm font-semibold text-slate-700 mb-1">Correo Electrónico *</label>
                <div class="relative">
                    <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="email" id="loginEmail" name="email" required placeholder="tu@correo.com"
                        class="w-full pl-11 pr-4 p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-medium text-slate-800 transition-colors">
                </div>
                <p id="errLoginEmail" class="text-xs text-rose-600 mt-1 hidden">Ingresa un correo electrónico válido.</p>
            </div>

            <!-- Contraseña con botón mostrar/ocultar -->
            <div>
                <label for="loginPassword" class="block text-sm font-semibold text-slate-700 mb-1">Contraseña *</label>
                <div class="flex items-center border border-slate-300 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-teal-500 bg-white">
                    <span class="pl-4 text-slate-400">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" id="loginPassword" name="password" required placeholder="••••••••"
                        class="w-full p-3 outline-none font-medium text-slate-800 border-0 bg-transparent">
                    <button type="button" id="btnToggleLoginPass" tabindex="-1"
                        class="px-4 py-3 text-slate-500 hover:text-teal-600 cursor-pointer shrink-0">
                        <i id="iconoOjoLogin" class="fa-solid fa-eye"></i>
                    </button>
                </div>
                <p id="errLoginPass" class="text-xs text-rose-600 mt-1 hidden">Ingresa tu contraseña.</p>
            </div>

            <button type="submit" id="btnSubmitLogin"
                class="w-full bg-teal-600 hover:bg-teal-700 text-white py-3.5 rounded-xl font-bold shadow-lg transition-all mt-2 cursor-pointer">
                Ingresar a mi cuenta
            </button>
        </form>

        <div class="mt-8 text-center text-sm text-slate-600 border-t pt-6">
            ¿No tienes una cuenta? <br>
            <a href="index.php?action=registro&redirect=<?php echo urlencode($redirect); ?>" class="text-teal-600 font-bold hover:underline">
                Crea una cuenta nueva
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formLogin');
    const inpEmail = document.getElementById('loginEmail');
    const inpPass = document.getElementById('loginPassword');
    const btnToggle = document.getElementById('btnToggleLoginPass');
    const iconoOjo = document.getElementById('iconoOjoLogin');
    const btnSubmit = document.getElementById('btnSubmitLogin');

    btnToggle.addEventListener('click', function() {
        const esPass = inpPass.type === 'password';
        inpPass.type = esPass ? 'text' : 'password';
        iconoOjo.classList.toggle('fa-eye', !esPass);
        iconoOjo.classList.toggle('fa-eye-slash', esPass);
    });

    function validarEmail() {
        const valido = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(inpEmail.value.trim());
        const err = document.getElementById('errLoginEmail');
        inpEmail.classList.toggle('border-rose-500', !valido);
        inpEmail.classList.toggle('border-emerald-500', valido);
        err.classList.toggle('hidden', valido);
        return valido;
    }

    function validarPass() {
        const valido = inpPass.value.trim().length > 0;
        const err = document.getElementById('errLoginPass');
        inpPass.classList.toggle('border-rose-500', !valido);
        inpPass.classList.toggle('border-emerald-500', valido);
        err.classList.toggle('hidden', valido);
        return valido;
    }

    inpEmail.addEventListener('blur', validarEmail);
    inpPass.addEventListener('input', function() {
        if (!document.getElementById('errLoginPass').classList.contains('hidden')) {
            validarPass();
        }
    });

    form.addEventListener('submit', function(e) {
        const okEmail = validarEmail();
        const okPass = validarPass();

        if (!okEmail || !okPass) {
            e.preventDefault();
            return;
        }

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Ingresando...';
    });
});
</script>

<?php require_once 'views/includes/footer.php'; ?>