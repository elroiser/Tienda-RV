// public/js/productos.js

const modal = document.getElementById('productoModal');
const modalBackdrop = document.getElementById('modalBackdrop');
const modalContent = document.getElementById('modalContent');
let productoActual = null; // Guardará todos los datos del producto abierto

if (modal) {
    document.body.appendChild(modal);
}

function abrirModalProducto(producto) {
    productoActual = producto;
    document.body.style.overflow = 'hidden';

    // Rellenar datos base
    document.getElementById('modalNombreBase').innerText = producto.nombre_base;
    document.getElementById('modalCategoria').innerText = producto.categoria;
    
    let rutaImg = 'public/img/productos/' + (producto.imagen ? producto.imagen : 'default.png');
    document.getElementById('modalImagen').src = rutaImg;

    // Configurar Selector de Aromas
    const contenedorAroma = document.getElementById('contenedorAroma');
    const selectAroma = document.getElementById('modalSelectAroma');
    selectAroma.innerHTML = '';

    // Si solo hay un aroma y es "Estándar", ocultamos la caja de aroma
    if (producto.variantes.length === 1 && producto.variantes[0].aroma === 'Estándar') {
        contenedorAroma.classList.add('hidden');
    } else {
        contenedorAroma.classList.remove('hidden');
    }

    // Llenar los aromas disponibles
    producto.variantes.forEach((variante, index) => {
        let option = document.createElement('option');
        option.value = index; // Guardamos el número de posición
        option.text = variante.aroma;
        selectAroma.appendChild(option);
    });

    // Cargar las presentaciones del primer aroma por defecto
    cambiarAroma();

    // Mostrar Modal
    modal.classList.remove('hidden');
    setTimeout(() => {
        modalBackdrop.classList.remove('opacity-0');
        modalContent.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
    }, 10);
}

// Se ejecuta cuando el usuario cambia de aroma o al abrir el modal
function cambiarAroma() {
    const selectAroma = document.getElementById('modalSelectAroma');
    const selectPresentacion = document.getElementById('modalSelectPresentacion');
    
    // Obtener la variante seleccionada basada en su posición
    const indexAroma = selectAroma.value;
    const varianteSeleccionada = productoActual.variantes[indexAroma];
    
    selectPresentacion.innerHTML = '';

    // Ordenar presentaciones de mayor a menor precio (Galón primero)
    let presentacionesOrdenadas = [...varianteSeleccionada.presentaciones].sort((a, b) => b.precio - a.precio);

    presentacionesOrdenadas.forEach(pres => {
        let option = document.createElement('option');
        option.value = pres.precio; 
        option.text = `${pres.tipo} - $${parseFloat(pres.precio).toFixed(2)}`;
        option.dataset.id = pres.id; 
        selectPresentacion.appendChild(option);
    });

    actualizarPrecioModal();
}

function actualizarPrecioModal() {
    const selectPresentacion = document.getElementById('modalSelectPresentacion');
    if(selectPresentacion.value) {
        const precioSeleccionado = parseFloat(selectPresentacion.value).toFixed(2);
        document.getElementById('modalPrecioFinal').innerText = '$' + precioSeleccionado;
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

    const idSeleccionado = selectPresentacion.options[selectPresentacion.selectedIndex].dataset.id;
    const nombreBase = document.getElementById('modalNombreBase').innerText;
    const presentacion = selectPresentacion.options[selectPresentacion.selectedIndex].text.split(' - ')[0]; 
    const precio = selectPresentacion.value;
    
    let textoAroma = "Estándar";
    if (!contenedorAroma.classList.contains('hidden')) {
        textoAroma = selectAroma.options[selectAroma.selectedIndex].text;
    }

    const formData = new URLSearchParams();
    formData.append('id_producto', idSeleccionado);
    formData.append('nombre_base', nombreBase);
    formData.append('aroma', textoAroma);
    formData.append('presentacion', presentacion);
    formData.append('precio', precio);
    formData.append('cantidad', 1);

    // Petición AJAX sin recargar la página
    fetch('index.php?action=agregarCarrito', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            cerrarModal(); // Cierra el modal y te deja en el catálogo de productos

            // Actualizar el número del carrito en la esquina superior derecha en tiempo real
            actualizarContadorCarrito(data.total_items);

            // Opcional: Mostrar un aviso flotante elegante de éxito si lo deseas
        } else {
            alert('Hubo un error al agregar el producto.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Función auxiliar para actualizar dinámicamente el círculo rojo del icono del carrito
function actualizarContadorCarrito(cantidadTotal) {
    // Actualizamos tanto el badge móvil como el de escritorio
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

    // Evento al escribir en el buscador
    if (buscadorInput) {
        buscadorInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') e.preventDefault();
        });
        buscadorInput.addEventListener('input', filtrarCatalogo);
    }

    // Evento al hacer clic en los botones de categoría
    botonesCategoria.forEach(boton => {
        boton.addEventListener('click', function() {
            categoriaActiva = this.getAttribute('data-categoria');

            // Actualizar estilos visuales de botón activo vs inactivo
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