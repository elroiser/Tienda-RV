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
                <p class="text-teal-600 font-medium mt-2 text-sm bg-teal-50 py-2 rounded-lg">
                    Inicia sesión o regístrate para finalizar tu pedido. ¡Tu carrito está a salvo!
                </p>
            <?php else: ?>
                <p class="text-slate-500 mt-1 text-sm">Bienvenido de nuevo a RV Limpieza</p>
            <?php endif; ?>
        </div>

        <?php if($error): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-700 p-3 rounded-xl text-sm mb-6 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i>
                Correo o contraseña incorrectos.
            </div>
        <?php endif; ?>

        <form action="index.php?action=procesarLogin" method="POST" class="space-y-5">
            <!-- Campo oculto para recordar a dónde debe ir tras iniciar sesión -->
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Correo Electrónico</label>
                <div class="relative">
                    <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="email" name="email" required placeholder="tu@correo.com" class="w-full pl-11 p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-medium text-slate-800">
                </div>
            </div>
            

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Contraseña</label>
                <div class="relative">
                    <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full pl-11 p-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none font-medium text-slate-800">
                </div>
            </div>

            <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white py-3.5 rounded-xl font-bold shadow-lg transition-colors mt-2">
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

<?php require_once 'views/includes/footer.php'; ?>