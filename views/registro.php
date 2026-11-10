<?php require_once 'views/includes/header.php'; ?>

<div class="max-w-md mx-auto bg-white rounded-2xl shadow-sm border border-slate-100 p-8 my-10">
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-slate-900">Crear Cuenta</h2>
        <p class="text-slate-500 text-sm mt-2">Para realizar y gestionar tus pedidos fácilmente.</p>
    </div>


    <!-- Mensajes de alerta (si los hay) -->
    <?php if (isset($_GET['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">
                <?php
                if ($_GET['error'] == 'correo_existente') echo "Ese correo ya está registrado.";
                if ($_GET['error'] == 'campos_vacios') echo "Por favor, llena todos los campos obligatorios.";
                ?>
            </span>
        </div>
    <?php endif; ?>

    <!-- El action apuntará a un futuro método en el controlador para guardar en la BD -->
    <form action="index.php?action=procesar_registro" method="POST" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Nombre Completo</label>
            <input required type="text" name="nombre" class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none" placeholder="Ej: Juan Pérez">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Correo Electrónico</label>
            <input required type="email" name="email" class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none" placeholder="correo@ejemplo.com">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Contraseña</label>
            <input required type="password" name="password" class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none" placeholder="••••••••">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono (WhatsApp)</label>
            <input required type="tel" name="telefono" class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none" placeholder="0987654321">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Dirección de entrega (Pasaje)</label>
            <textarea required name="direccion" class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none h-20" placeholder="Calle principal y secundaria..."></textarea>
        </div>

        <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-4 rounded-lg transition-colors mt-6">
            Registrarse
        </button>
    </form>
</div>

<?php require_once 'views/includes/footer.php'; ?>