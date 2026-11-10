<?php
// controllers/AdminController.php

class AdminController {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Mostrar formulario de login de administrador
    public function login() {
        require_once 'views/admin/login.php';
    }

    // Procesar las credenciales del admin
    public function procesarLogin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = trim($_POST['usuario'] ?? '');
            $password = $_POST['password'] ?? '';

            require_once 'config/db.php';
            $db = new Database();
            $conn = $db->connect();

            $stmt = $conn->prepare("SELECT * FROM administradores WHERE usuario = ?");
            $stmt->execute([$usuario]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_logueado'] = true;
                $_SESSION['admin_user'] = $admin['usuario'];
                header("Location: index.php?action=adminDashboard");
                exit();
            } else {
                header("Location: index.php?action=adminLogin&error=1");
                exit();
            }
        }
    }

    // Mostrar el panel de pedidos protegido
    public function dashboard() {
        // BARRERA DE SEGURIDAD: Si no es admin, fuera
        if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
            header("Location: index.php?action=adminLogin");
            exit();
        }

        require_once 'config/db.php';
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("
            SELECT p.*, c.nombre as cliente_nombre, c.telefono as cliente_telefono 
            FROM pedidos p 
            JOIN clientes c ON p.cliente_id = c.id 
            ORDER BY p.fecha_pedido DESC
        ");
        $stmt->execute();
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($pedidos as &$pedido) {
            $stmtDetalle = $conn->prepare("
                SELECT dp.*, pr.nombre 
                FROM detalle_pedidos dp 
                JOIN productos pr ON dp.producto_id = pr.id 
                WHERE dp.pedido_id = ?
            ");
            $stmtDetalle->execute([$pedido['id']]);
            $pedido['detalles'] = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($pedido);

        require_once 'views/admin/dashboard.php';
    }

    // Cerrar sesión de administrador
    public function logout() {
        unset($_SESSION['admin_logueado']);
        unset($_SESSION['admin_user']);
        header("Location: index.php?action=adminLogin");
        exit();
    }
}