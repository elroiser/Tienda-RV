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

    // Datos que enviaremos por POST al servidor
    const formData = new URLSearchParams();
    formData.append('id_producto', idSeleccionado);
    formData.append('nombre_base', nombreBase);
    formData.append('aroma', textoAroma);
    formData.append('presentacion', presentacion);
    formData.append('precio', precio);
    formData.append('cantidad', 1);

    // Petición AJAX para guardar en sesión
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
            cerrarModal();
            // Redirigir de inmediato al carrito o mostrar notificación bonita
            window.location.href = 'index.php?action=carrito';
        } else {
            alert('Hubo un error al agregar el producto.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

if (modalBackdrop) {
    modalBackdrop.addEventListener('click', cerrarModal);
}