<?php
// index.php
require_once 'config/db.php';
require_once 'controllers/PageController.php';
require_once 'controllers/CarritoController.php'; // <-- 1. Requerimos el controlador del carrito

// Obtenemos la acción de la URL
$action = isset($_GET['action']) ? $_GET['action'] : 'home';

$controller = new PageController();
$carritoController = new CarritoController(); // <-- 2. Instanciamos el controlador del carrito

switch ($action) {
    case 'home':
        $controller->home();
        break;
    case 'productos':
        $controller->productos();
        break;
    case 'registro':
        $controller->registro();
        break;
    case 'procesar_registro':
        $controller->procesarRegistro();
        break;

    // --- 3. NUEVAS RUTas DEL CARRITO QUE FALTABAN ---
    case 'carrito':
        $carritoController->index();
        break;
    case 'misPedidos':
        $carritoController->misPedidos();
        break;
    case 'repetirPedido':
        $carritoController->repetirPedido();
        break;
    case 'agregarCarrito':
        $carritoController->agregar();
        break;
    case 'miPerfil':
        $carritoController->miPerfil();
        break;
    case 'actualizarPerfil':
        $carritoController->actualizarPerfil();
        break;
    case 'cambiarPassword':
        $carritoController->cambiarPassword();
        break;
    case 'eliminarCarrito':
        $carritoController->eliminar();
        break;
    case 'checkout':
        $carritoController->checkout();
        break;
    case 'procesarPedido':
        $carritoController->procesarPedido();
        break;
    case 'exito':
        // Vista rápida de pedido exitoso
        require_once 'views/exito.php';
        break;
    case 'login':
        $controller->login();
        break;
    case 'procesarLogin':
        $controller->procesarLogin();
        break;
    case 'logout':
        $controller->logout();
        break;

    case 'adminlogin':
    case 'adminLogin':
        require_once 'controllers/AdminController.php';
        $adminCtrl = new AdminController();
        $adminCtrl->login();
        break;

    case 'procesarAdminLogin':
        require_once 'controllers/AdminController.php';
        $adminCtrl = new AdminController();
        $adminCtrl->procesarLogin();
        break;

    case 'adminDashboard':
        require_once 'controllers/AdminController.php';
        $adminCtrl = new AdminController();
        $adminCtrl->dashboard();
        break;

    case 'adminLogout':
        require_once 'controllers/AdminController.php';
        $adminCtrl = new AdminController();
        $adminCtrl->logout();
        break;

    case 'disminuirCarrito':
        $controller = new CarritoController();
        $controller->disminuirCantidad();
        break;
    case 'aumentarCarrito':
        $controller = new CarritoController();
        $controller->aumentarCantidad();
        break;
        

    default:
        $controller->home();
        break;
}
