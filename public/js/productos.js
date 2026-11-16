// public/js/productos.js

const modal = document.getElementById('productoModal');
const modalBackdrop = document.getElementById('modalBackdrop');
const modalContent = document.getElementById('modalContent');
let productoActual = null;

if (modal) {
    document.body.appendChild(modal);
}

function mostrarToastCatalogo(mensaje, tipo = 'success') {
    const toast = document.getElementById('toastCatalogo');
    const icono = document.getElementById('toastIcono');
    const texto = document.getElementById('toastMensaje');
    if (!toast) return;

    texto.innerText = mensaje;
    if (tipo === 'error') {
        icono.className = 'fa-solid fa-circle-exclamation text-rose-400 text-lg';
    } else {
        icono.className = 'fa-solid fa-circle-check text-emerald-400 text-lg';
    }

    toast.classList.remove('hidden');
    clearTimeout(window._toastTimer);
    window._toastTimer = setTimeout(() => {
        toast.classList.add('hidden');
    }, 3500);
}

function abrirModalProducto(producto) {
    productoActual = producto;
    document.body.style.overflow = 'hidden';

    document.getElementById('modalNombreBase').innerText = producto.nombre_base;
    document.getElementById('modalCategoria').innerText = producto.categoria;
    
    let rutaImg = 'public/img/productos/' + (producto.imagen ? producto.imagen : 'default.png');
    document.getElementById('modalImagen').src = rutaImg;

    const contenedorAroma = document.getElementById('contenedorAroma');
    const selectAroma = document.getElementById('modalSelectAroma');
    selectAroma.innerHTML = '';

    if (producto.variantes.length === 1 && producto.variantes[0].aroma === 'Estándar') {
        contenedorAroma.classList.add('hidden');
    } else {
        contenedorAroma.classList.remove('hidden');
    }

    producto.variantes.forEach((variante, index) => {
        let option = document.createElement('option');
        option.value = index;
        const tieneStock = variante.presentaciones.some(p => parseInt(p.stock || 0) > 0);
        option.text = tieneStock ? variante.aroma : `${variante.aroma} (Agotado)`;
        selectAroma.appendChild(option);
    });

    cambiarAroma();

    modal.classList.remove('hidden');
    setTimeout(() => {
        modalBackdrop.classList.remove('opacity-0');
        modalContent.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
    }, 10);
}

function cambiarAroma() {
    const selectAroma = document.getElementById('modalSelectAroma');
    const selectPresentacion = document.getElementById('modalSelectPresentacion');
    
    const indexAroma = selectAroma.value;
    const varianteSeleccionada = productoActual.variantes[indexAroma];
    
    selectPresentacion.innerHTML = '';

    let presentacionesOrdenadas = [...varianteSeleccionada.presentaciones].sort((a, b) => b.precio - a.precio);

    presentacionesOrdenadas.forEach(pres => {
        let option = document.createElement('option');
        const stockDisp = parseInt(pres.stock || 0);
        option.value = pres.precio;
        option.dataset.id = pres.id;
        option.dataset.stock = stockDisp;
        option.dataset.tipo = pres.tipo;
        option.text = stockDisp > 0
            ? `${pres.tipo} - $${parseFloat(pres.precio).toFixed(2)}`
            : `${pres.tipo} - $${parseFloat(pres.precio).toFixed(2)} (Sin stock)`;
        selectPresentacion.appendChild(option);
    });

    actualizarPrecioModal();
}

function actualizarPrecioModal() {
    const selectPresentacion = document.getElementById('modalSelectPresentacion');
    const opcion = selectPresentacion.options[selectPresentacion.selectedIndex];
    if (!opcion) return;

    const precioSeleccionado = parseFloat(opcion.value).toFixed(2);
    const stock = parseInt(opcion.dataset.stock || 0);

    document.getElementById('modalPrecioFinal').innerText = '$' + precioSeleccionado;

    const boxStock = document.getElementById('modalEstadoStock');
    const txtStock = document.getElementById('modalTextoStock');
    const btnAgregar = document.getElementById('btnAgregarModal');

    if (stock <= 0) {
        boxStock.className = 'mt-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 bg-rose-50 text-rose-700 border border-rose-200';
        boxStock.querySelector('i').className = 'fa-solid fa-circle-xmark';
        txtStock.innerText = 'Producto agotado temporalmente en esta presentación.';

        btnAgregar.disabled = true;
        btnAgregar.className = 'bg-slate-300 text-slate-500 px-6 py-3 rounded-xl font-bold flex items-center cursor-not-allowed';
        btnAgregar.innerHTML = '<i class="fa-solid fa-ban mr-2"></i> Sin stock';
    } else if (stock <= 5) {
        boxStock.className = 'mt-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 bg-amber-50 text-amber-800 border border-amber-200';
        boxStock.querySelector('i').className = 'fa-solid fa-triangle-exclamation';
        txtStock.innerText = `¡Últimas unidades! Solo quedan ${stock} disponibles.`;

        btnAgregar.disabled = false;
        btnAgregar.className = 'bg-teal-600 hover:bg-teal-700 text-white px-6 py-3 rounded-xl font-bold flex items-center shadow-md transition-all cursor-pointer';
        btnAgregar.innerHTML = '<i class="fa-solid fa-cart-shopping mr-2"></i> Añadir';
    } else {
        boxStock.className = 'mt-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 bg-emerald-50 text-emerald-700 border border-emerald-200';
        boxStock.querySelector('i').className = 'fa-solid fa-circle-check';
        txtStock.innerText = `En stock (${stock} unidades disponibles)`;

        btnAgregar.disabled = false;
        btnAgregar.className = 'bg-teal-600 hover:bg-teal-700 text-white px-6 py-3 rounded-xl font-bold flex items-center shadow-md transition-all cursor-pointer';
        btnAgregar.innerHTML = '<i class="fa-solid fa-cart-shopping mr-2"></i> Añadir';
    }
}

function cerrarModal() {
    modalBackdrop.classList.add('opacity-0');
    modalContent.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300); 
}

function agregarAlCarrito() {
    const selectPresentacion = document.getElementById('modalSelectPresentacion');
    const selectAroma = document.getElementById('modalSelectAroma');
    const contenedorAroma = document.getElementById('contenedorAroma');

    const opcionActual = selectPresentacion.options[selectPresentacion.selectedIndex];
    const idSeleccionado = opcionActual.dataset.id;
    const stockDisponible = parseInt(opcionActual.dataset.stock || 0);

    if (stockDisponible <= 0) {
        mostrarToastCatalogo('Este producto no tiene stock disponible.', 'error');
        return;
    }

    const nombreBase = document.getElementById('modalNombreBase').innerText;
    const presentacion = opcionActual.dataset.tipo || opcionActual.text.split(' - ')[0];
    const precio = selectPresentacion.value;
    
    let textoAroma = "Estándar";
    if (!contenedorAroma.classList.contains('hidden')) {
        textoAroma = selectAroma.options[selectAroma.selectedIndex].text.replace(' (Agotado)', '');
    }

    const formData = new URLSearchParams();
    formData.append('id_producto', idSeleccionado);
    formData.append('nombre_base', nombreBase);
    formData.append('aroma', textoAroma);
    formData.append('presentacion', presentacion);
    formData.append('precio', precio);
    formData.append('cantidad', 1);

    fetch('index.php?action=agregarCarrito', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            cerrarModal();
            actualizarContadorCarrito(data.total_items);
            mostrarToastCatalogo('Producto añadido al carrito', 'success');
        } else {
            mostrarToastCatalogo(data.mensaje || 'No hay suficiente stock disponible.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarToastCatalogo('Error de conexión al añadir el producto.', 'error');
    });
}

function actualizarContadorCarrito(cantidadTotal) {
    ['contadorCarritoMobile', 'contadorCarritoDesktop'].forEach(id => {
        let badge = document.getElementById(id);
        if (badge) {
            badge.innerText = cantidadTotal;
            if (cantidadTotal > 0) {
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const buscadorInput = document.getElementById('buscadorProductos');
    const botonesCategoria = document.querySelectorAll('.btn-categoria');
    const tarjetas = document.querySelectorAll('.product-card');
    let categoriaActiva = 'todas';

    function filtrarCatalogo() {
        const textoBusqueda = buscadorInput
            ? buscadorInput.value
                .toLowerCase()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .trim()
            : '';

        tarjetas.forEach(tarjeta => {
            const titulo = tarjeta.querySelector('h3');
            const nombreProducto = (titulo ? titulo.textContent : tarjeta.textContent)
                .toLowerCase()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "");

            const categoriaProducto = (tarjeta.getAttribute('data-categoria') || '').toLowerCase();

            const coincideTexto = nombreProducto.includes(textoBusqueda);
            const coincideCategoria = (categoriaActiva === 'todas') || (categoriaProducto === categoriaActiva);

            if (coincideTexto && coincideCategoria) {
                tarjeta.style.display = 'flex';
            } else {
                tarjeta.style.display = 'none';
            }
        });
    }

    if (buscadorInput) {
        buscadorInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') e.preventDefault();
        });
        buscadorInput.addEventListener('input', filtrarCatalogo);
    }

    botonesCategoria.forEach(boton => {
        boton.addEventListener('click', function() {
            categoriaActiva = this.getAttribute('data-categoria');

            botonesCategoria.forEach(btn => {
                btn.classList.remove('bg-teal-600', 'text-white', 'font-bold', 'shadow-sm');
                btn.classList.add('bg-white', 'hover:bg-teal-50', 'text-slate-700', 'border', 'border-slate-200', 'font-semibold');
            });

            this.classList.remove('bg-white', 'hover:bg-teal-50', 'text-slate-700', 'border', 'border-slate-200', 'font-semibold');
            this.classList.add('bg-teal-600', 'text-white', 'font-bold', 'shadow-sm');

            filtrarCatalogo();
        });
    });
});

if (modalBackdrop) {
    modalBackdrop.addEventListener('click', cerrarModal);
}