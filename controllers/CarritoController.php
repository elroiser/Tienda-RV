<?php
// controllers/CarritoController.php

require_once __DIR__ . '/../vendor/autoload.php'; // Nota el /../ porque el controlador está dentro de una carpeta
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class CarritoController
{

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Mostrar la vista del carrito
    public function index()
    {
        $carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];
        $totalGeneral = 0;

        foreach ($carrito as $item) {
            $totalGeneral += $item['precio'] * $item['cantidad'];
        }

        require_once 'views/carrito.php';
    }

    // Agregar producto al carrito (vía AJAX o petición POST)
    // Agregar producto al carrito validando stock real
    public function agregar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idProducto = intval($_POST['id_producto'] ?? 0);
            $nombreBase = $_POST['nombre_base'] ?? '';
            $aroma = $_POST['aroma'] ?? '';
            $presentacion = $_POST['presentacion'] ?? '';
            $precio = floatval($_POST['precio'] ?? 0);
            $cantidad = intval($_POST['cantidad'] ?? 1);

            if ($idProducto > 0 && $precio > 0 && $cantidad > 0) {
                require_once 'config/db.php';
                $db = new Database();
                $conn = $db->connect();

                $stmtStock = $conn->prepare("SELECT stock FROM productos WHERE id = ? AND estado = 1");
                $stmtStock->execute([$idProducto]);
                $prod = $stmtStock->fetch(PDO::FETCH_ASSOC);

                $stockDisponible = $prod ? intval($prod['stock']) : 0;

                if ($stockDisponible <= 0) {
                    echo json_encode([
                        'status' => 'error',
                        'mensaje' => 'Lo sentimos, este producto está agotado.'
                    ]);
                    exit;
                }

                if (!isset($_SESSION['carrito'])) {
                    $_SESSION['carrito'] = [];
                }

                $cantidadActualEnCarrito = isset($_SESSION['carrito'][$idProducto])
                    ? intval($_SESSION['carrito'][$idProducto]['cantidad'])
                    : 0;

                if (($cantidadActualEnCarrito + $cantidad) > $stockDisponible) {
                    echo json_encode([
                        'status' => 'error',
                        'mensaje' => "Solo quedan {$stockDisponible} unidades disponibles en inventario."
                    ]);
                    exit;
                }

                if (isset($_SESSION['carrito'][$idProducto])) {
                    $_SESSION['carrito'][$idProducto]['cantidad'] += $cantidad;
                } else {
                    $_SESSION['carrito'][$idProducto] = [
                        'id' => $idProducto,
                        'nombre_base' => $nombreBase,
                        'aroma' => $aroma,
                        'presentacion' => $presentacion,
                        'precio' => $precio,
                        'cantidad' => $cantidad
                    ];
                }

                $totalItems = 0;
                foreach ($_SESSION['carrito'] as $item) {
                    $totalItems += $item['cantidad'];
                }

                echo json_encode([
                    'status' => 'success',
                    'mensaje' => 'Producto añadido correctamente',
                    'total_items' => $totalItems
                ]);
                exit;
            }
        }
        echo json_encode(['status' => 'error', 'mensaje' => 'Datos inválidos']);
        exit;
    }

    // Eliminar un producto del carrito
    public function eliminar()
    {
        $id = $_GET['id'] ?? null;
        if ($id && isset($_SESSION['carrito'][$id])) {
            unset($_SESSION['carrito'][$id]);
        }
        header("Location: index.php?action=carrito");
        exit();
    }

    // Mostrar la vista del formulario de pago (Checkout)
    public function checkout()
    {
        $carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];

        if (empty($carrito)) {
            header("Location: index.php?action=productos");
            exit();
        }

        // BARRERA DE LOGIN: Si no hay sesión, lo mandamos a login y le decimos a dónde volver
        if (!isset($_SESSION['cliente_id'])) {
            header("Location: index.php?action=login&redirect=checkout");
            exit();
        }

        $totalGeneral = 0;
        foreach ($carrito as $item) {
            $totalGeneral += $item['precio'] * $item['cantidad'];
        }

        // Como ya pasó la barrera, obtenemos sus datos
        require_once 'config/db.php';
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$_SESSION['cliente_id']]);
        $clienteLogueado = $stmt->fetch(PDO::FETCH_ASSOC);

        require_once 'views/checkout.php';
    }

    // Procesar y guardar el pedido en la base de datos
    public function procesarPedido()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];
            if (empty($carrito)) {
                header("Location: index.php?action=productos");
                exit();
            }

            // Datos del formulario de envío limpios
            $nombre     = trim(preg_replace('/\s+/', ' ', $_POST['nombre'] ?? ''));
            $telefono   = preg_replace('/[^0-9]/', '', $_POST['telefono'] ?? '');
            $email      = trim(strtolower($_POST['email'] ?? ''));
            $direccion  = trim($_POST['direccion'] ?? '');
            $referencia = trim($_POST['referencia'] ?? '');
            $ciudad     = "Pasaje, El Oro";

            // Validación estricta en servidor antes de procesar la orden
            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]{5,80}$/', $nombre) || count(explode(' ', $nombre)) < 2) {
                header("Location: index.php?action=checkout&error=nombre_invalido");
                exit();
            }

            if (!preg_match('/^09[0-9]{8}$/', $telefono)) {
                header("Location: index.php?action=checkout&error=telefono_invalido");
                exit();
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                header("Location: index.php?action=checkout&error=email_invalido");
                exit();
            }

            if (strlen($direccion) < 10) {
                header("Location: index.php?action=checkout&error=direccion_corta");
                exit();
            }

            // Calcular total general
            $totalGeneral = 0;
            foreach ($carrito as $item) {
                $totalGeneral += $item['precio'] * $item['cantidad'];
            }

            require_once 'config/db.php';
            $db = new Database();
            $conn = $db->connect();

            try {
                $conn->beginTransaction();

                if (isset($_SESSION['cliente_id'])) {
                    $clienteId = $_SESSION['cliente_id'];
                } else {
                    $stmtCliente = $conn->prepare("SELECT id FROM clientes WHERE email = ?");
                    $stmtCliente->execute([$email]);
                    $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

                    if ($cliente) {
                        $clienteId = $cliente['id'];
                    } else {
                        $stmtInsertCliente = $conn->prepare("INSERT INTO clientes (nombre, email, password, telefono, direccion, ciudad) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmtInsertCliente->execute([
                            $nombre,
                            $email,
                            password_hash('123456', PASSWORD_DEFAULT),
                            $telefono,
                            $direccion,
                            $ciudad
                        ]);
                        $clienteId = $conn->lastInsertId();
                    }
                }

                // 2. Insertar el pedido en la tabla pedidos
                $stmtPedido = $conn->prepare("INSERT INTO pedidos (cliente_id, total, estado, metodo_pago, direccion_envio, referencia_envio) VALUES (?, ?, 'Pendiente', 'Efectivo Contra Entrega', ?, ?)");
                $stmtPedido->execute([$clienteId, $totalGeneral, $direccion, $referencia]);
                $pedidoId = $conn->lastInsertId();

                // 3. Validar stock real con bloqueo de fila, guardar detalles y descontar inventario
                $stmtDetalle = $conn->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
                $stmtStock = $conn->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
                $stmtCheckStock = $conn->prepare("SELECT nombre, stock FROM productos WHERE id = ? FOR UPDATE");
                $productosStockBajo = [];

                foreach ($carrito as $item) {
                    $stmtCheckStock->execute([$item['id']]);
                    $prodActual = $stmtCheckStock->fetch(PDO::FETCH_ASSOC);

                    if (!$prodActual || intval($prodActual['stock']) < intval($item['cantidad'])) {
                        $conn->rollBack();
                        $nombreAgotado = $prodActual ? $prodActual['nombre'] : $item['nombre_base'];
                        $disp = $prodActual ? max(0, intval($prodActual['stock'])) : 0;
                        header("Location: index.php?action=carrito&error=sin_stock&prod=" . urlencode($nombreAgotado) . "&disp=" . $disp);
                        exit();
                    }

                    $stmtDetalle->execute([
                        $pedidoId,
                        $item['id'],
                        $item['cantidad'],
                        $item['precio']
                    ]);

                    $stmtStock->execute([
                        $item['cantidad'],
                        $item['id']
                    ]);

                    $stockRestante = intval($prodActual['stock']) - intval($item['cantidad']);
                    if ($stockRestante <= 5) {
                        $productosStockBajo[] = [
                            'nombre' => $prodActual['nombre'],
                            'stock'  => $stockRestante
                        ];
                    }
                }

                // Guardar ID del pedido recién creado para permitir ver su Nota de Venta
                $_SESSION['ultimo_pedido_id'] = $pedidoId;

                // --- INICIO DE NOTIFICACIÓN FIREBASE BLINDADA ---
                try {
                    $factory = (new Factory)->withServiceAccount(__DIR__ . '/../tienda-rv-b288dc407207.json');
                    $messaging = $factory->createMessaging();

                    $stmtTokens = $conn->prepare("SELECT id, token FROM admin_tokens");
                    $stmtTokens->execute();
                    $adminsTokens = $stmtTokens->fetchAll(PDO::FETCH_ASSOC);

                    if (!empty($adminsTokens)) {
                        foreach ($adminsTokens as $adminToken) {
                            try {
                                $mensaje = CloudMessage::withTarget('token', $adminToken['token'])
                                    ->withNotification(Notification::create(
                                        "¡Nuevo Pedido #" . $pedidoId . "!",
                                        $nombre . " acaba de hacer una compra por $" . number_format($totalGeneral, 2)
                                    ));
                                $messaging->send($mensaje);
                            } catch (\Exception $exToken) {
                                error_log("Fallo al enviar notificación de pedido al token ID " . $adminToken['id'] . ": " . $exToken->getMessage());
                            }

                            foreach ($productosStockBajo as $pBajo) {
                                try {
                                    $mensajeStock = CloudMessage::withTarget('token', $adminToken['token'])
                                        ->withNotification(Notification::create(
                                            "⚠️ ¡Alerta de Stock Bajo!",
                                            "El producto \"" . $pBajo['nombre'] . "\" tiene menos de 6 unidades (Quedan: " . $pBajo['stock'] . ")."
                                        ));
                                    $messaging->send($mensajeStock);
                                } catch (\Exception $exStock) {
                                    error_log("Fallo al enviar alerta de stock al token ID " . $adminToken['id'] . ": " . $exStock->getMessage());
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log("Error general de Firebase: " . $e->getMessage());
                }

                $conn->commit();
                unset($_SESSION['carrito']);

                header("Location: index.php?action=exito&pedido=" . $pedidoId);
                exit();
            } catch (Exception $e) {
                $conn->rollBack();
                die("Error al procesar el pedido: " . $e->getMessage());
            }
        }
    }
    // Disminuir en 1 la cantidad de un producto del carrito
    public function disminuirCantidad()
    {
        $id = $_GET['id'] ?? null;
        if ($id && isset($_SESSION['carrito'][$id])) {
            if ($_SESSION['carrito'][$id]['cantidad'] > 1) {
                // Si hay más de 1, le restamos uno
                $_SESSION['carrito'][$id]['cantidad']--;
            } else {
                // Si solo queda 1, lo eliminamos por completo del carrito
                unset($_SESSION['carrito'][$id]);
            }
        }
        header("Location: index.php?action=carrito");
        exit();
    }

    // Mostrar los pedidos del cliente logueado
    // Mostrar los pedidos del cliente logueado con su desglose detallado
    public function misPedidos()
    {
        if (!isset($_SESSION['cliente_id'])) {
            header("Location: index.php?action=login&redirect=misPedidos");
            exit();
        }

        require_once 'config/db.php';
        $db = new Database();
        $conn = $db->connect();

        // Consultar los pedidos del cliente
        $stmt = $conn->prepare("SELECT * FROM pedidos WHERE cliente_id = ? ORDER BY id DESC");
        $stmt->execute([$_SESSION['cliente_id']]);
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener el detalle exacto de productos para cada pedido
        $stmtDetalle = $conn->prepare("
            SELECT dp.*, pr.nombre AS producto_nombre, pr.imagen, pr.estado AS producto_activo
            FROM detalle_pedidos dp
            JOIN productos pr ON dp.producto_id = pr.id
            WHERE dp.pedido_id = ?
        ");

        foreach ($pedidos as &$pedido) {
            $stmtDetalle->execute([$pedido['id']]);
            $pedido['detalles'] = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($pedido);

        require_once 'views/mis_pedidos.php';
    }

    // Generar vista de Nota de Venta imprimible / descargable como PDF
    public function notaVenta()
    {
        $pedidoId = intval($_GET['id'] ?? 0);
        if ($pedidoId <= 0) {
            header("Location: index.php?action=home");
            exit();
        }

        require_once 'config/db.php';
        $db = new Database();
        $conn = $db->connect();

        // Obtener cabecera del pedido y datos del cliente
        $stmt = $conn->prepare("
            SELECT p.*, c.nombre AS cliente_nombre, c.telefono AS cliente_telefono, c.email AS cliente_email
            FROM pedidos p
            JOIN clientes c ON p.cliente_id = c.id
            WHERE p.id = ?
        ");
        $stmt->execute([$pedidoId]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pedido) {
            header("Location: index.php?action=home");
            exit();
        }

        // Seguridad: permitir acceso al dueño del pedido o al administrador logueado
        $esDueno = isset($_SESSION['cliente_id']) && intval($_SESSION['cliente_id']) === intval($pedido['cliente_id']);
        $esAdmin = isset($_SESSION['admin_id']);
        $esReciente = isset($_SESSION['ultimo_pedido_id']) && intval($_SESSION['ultimo_pedido_id']) === $pedidoId;

        if (!$esDueno && !$esAdmin && !$esReciente) {
            header("Location: index.php?action=login");
            exit();
        }

        // Obtener detalle de productos (galones/litros/unidades)
        $stmtDet = $conn->prepare("
            SELECT dp.*, pr.nombre AS producto_nombre
            FROM detalle_pedidos dp
            JOIN productos pr ON dp.producto_id = pr.id
            WHERE dp.pedido_id = ?
        ");
        $stmtDet->execute([$pedidoId]);
        $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

        require_once 'views/nota_venta.php';
    }

    // Volver a pedir: cargar todos los productos de una orden pasada al carrito actual
    // Volver a pedir: cargar productos disponibles de una orden pasada respetando el stock real
    public function repetirPedido()
    {
        if (!isset($_SESSION['cliente_id'])) {
            header("Location: index.php?action=login&redirect=misPedidos");
            exit();
        }

        $pedidoId = intval($_GET['id'] ?? 0);
        if ($pedidoId <= 0) {
            header("Location: index.php?action=misPedidos");
            exit();
        }

        require_once 'config/db.php';
        $db = new Database();
        $conn = $db->connect();

        // Verificar que el pedido realmente pertenezca al cliente logueado
        $stmtCheck = $conn->prepare("SELECT id FROM pedidos WHERE id = ? AND cliente_id = ?");
        $stmtCheck->execute([$pedidoId, $_SESSION['cliente_id']]);
        if (!$stmtCheck->fetch()) {
            header("Location: index.php?action=misPedidos");
            exit();
        }

        // Obtener los productos del pedido junto con su stock y precio actual
        $stmtItems = $conn->prepare("
            SELECT dp.producto_id, dp.cantidad, pr.nombre, pr.precio, pr.stock, pr.estado
            FROM detalle_pedidos dp
            JOIN productos pr ON dp.producto_id = pr.id
            WHERE dp.pedido_id = ?
        ");
        $stmtItems->execute([$pedidoId]);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        if (empty($items)) {
            header("Location: index.php?action=misPedidos&aviso=orden_vacia");
            exit();
        }

        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }

        $agregadosCount = 0;
        $omitidos = [];
        $ajustados = [];

        foreach ($items as $item) {
            $idProd = intval($item['producto_id']);
            $nombreCompleto = $item['nombre'];
            $stockDisp = (intval($item['estado']) === 1) ? max(0, intval($item['stock'])) : 0;
            $cantDeseada = intval($item['cantidad']);

            // Extraer presentación entre paréntesis
            $pos = strrpos($nombreCompleto, '(');
            if ($pos !== false) {
                $nombreBase = trim(substr($nombreCompleto, 0, $pos));
                $presentacion = trim(substr($nombreCompleto, $pos + 1, -1));
            } else {
                $nombreBase = trim($nombreCompleto);
                $presentacion = "Unidad";
            }

            if ($stockDisp <= 0) {
                $omitidos[] = $nombreCompleto;
                continue;
            }

            $enCarritoActual = isset($_SESSION['carrito'][$idProd]) ? intval($_SESSION['carrito'][$idProd]['cantidad']) : 0;
            $espacioDisponible = $stockDisp - $enCarritoActual;

            if ($espacioDisponible <= 0) {
                $ajustados[] = "{$nombreCompleto} (límite máximo de {$stockDisp} uds en carrito)";
                continue;
            }

            $cantFinalAgregar = min($cantDeseada, $espacioDisponible);

            if ($cantFinalAgregar < $cantDeseada) {
                $ajustados[] = "{$nombreCompleto} (ajustado a {$cantFinalAgregar} de {$cantDeseada} uds por stock)";
            }

            if (isset($_SESSION['carrito'][$idProd])) {
                $_SESSION['carrito'][$idProd]['cantidad'] += $cantFinalAgregar;
            } else {
                $_SESSION['carrito'][$idProd] = [
                    'id' => $idProd,
                    'nombre_base' => $nombreBase,
                    'aroma' => '',
                    'presentacion' => $presentacion,
                    'precio' => floatval($item['precio']),
                    'cantidad' => $cantFinalAgregar
                ];
            }
            $agregadosCount++;
        }

        // Si ningún producto pudo añadirse porque todos estaban agotados
        if ($agregadosCount === 0) {
            $_SESSION['aviso_pedidos'] = !empty($omitidos)
                ? "No se pudo repetir la orden #{$pedidoId} porque los siguientes productos están agotados temporalmente: " . implode(', ', $omitidos) . "."
                : "Ya tienes en tu carrito el máximo de unidades disponibles para los productos de la orden #{$pedidoId}.";
            header("Location: index.php?action=misPedidos");
            exit();
        }

        // Si se agregaron algunos pero otros se omitieron o ajustaron por stock
        if (!empty($omitidos) || !empty($ajustados)) {
            $notas = [];
            if (!empty($omitidos)) {
                $notas[] = "Productos agotados omitidos: <b>" . htmlspecialchars(implode(', ', $omitidos)) . "</b>";
            }
            if (!empty($ajustados)) {
                $notas[] = "Cantidades ajustadas al inventario: <b>" . htmlspecialchars(implode(', ', $ajustados)) . "</b>";
            }
            $_SESSION['aviso_carrito_repetir'] = implode('. ', $notas) . ".";
        }

        header("Location: index.php?action=carrito");
        exit();
    }

    // Mostrar la vista de Mi Perfil con los datos del cliente
    public function miPerfil()
    {
        if (!isset($_SESSION['cliente_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        require_once 'config/db.php';
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$_SESSION['cliente_id']]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        require_once 'views/mi_perfil.php';
    }

    // Actualizar los datos del perfil en la base de datos
    // Actualizar los datos del perfil en la base de datos
    public function actualizarPerfil()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['cliente_id'])) {
            $nombre    = trim(preg_replace('/\s+/', ' ', $_POST['nombre'] ?? ''));
            $telefono  = preg_replace('/[^0-9]/', '', $_POST['telefono'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');

            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]{5,80}$/', $nombre) || count(explode(' ', $nombre)) < 2) {
                header("Location: index.php?action=miPerfil&error=nombre_invalido");
                exit();
            }

            if (!preg_match('/^09[0-9]{8}$/', $telefono)) {
                header("Location: index.php?action=miPerfil&error=telefono_invalido");
                exit();
            }

            if (strlen($direccion) < 10) {
                header("Location: index.php?action=miPerfil&error=direccion_corta");
                exit();
            }

            require_once 'config/db.php';
            $db = new Database();
            $conn = $db->connect();

            $stmt = $conn->prepare("UPDATE clientes SET nombre = ?, telefono = ?, direccion = ? WHERE id = ?");
            $stmt->execute([$nombre, $telefono, $direccion, $_SESSION['cliente_id']]);

            $_SESSION['cliente_nombre'] = $nombre;

            header("Location: index.php?action=miPerfil&actualizado=1");
            exit();
        }
    }

    // Procesar el cambio de contraseña del cliente
    public function cambiarPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['cliente_id'])) {
            $passwordActual = $_POST['password_actual'] ?? '';
            $passwordNueva = $_POST['password_nueva'] ?? '';
            $passwordConfirmar = $_POST['password_confirmar'] ?? '';

            if (empty($passwordActual) || empty($passwordNueva) || empty($passwordConfirmar)) {
                header("Location: index.php?action=miPerfil&error=campos_vacios");
                exit();
            }

            if (strlen($passwordNueva) < 6) {
                header("Location: index.php?action=miPerfil&error=password_corta");
                exit();
            }

            if ($passwordNueva !== $passwordConfirmar) {
                header("Location: index.php?action=miPerfil&error=no_coinciden");
                exit();
            }

            require_once 'config/db.php';
            $db = new Database();
            $conn = $db->connect();

            $stmt = $conn->prepare("SELECT password FROM clientes WHERE id = ?");
            $stmt->execute([$_SESSION['cliente_id']]);
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cliente && password_verify($passwordActual, $cliente['password'])) {
                $nuevaHash = password_hash($passwordNueva, PASSWORD_DEFAULT);

                $stmtUpdate = $conn->prepare("UPDATE clientes SET password = ? WHERE id = ?");
                $stmtUpdate->execute([$nuevaHash, $_SESSION['cliente_id']]);

                header("Location: index.php?action=miPerfil&password_cambiada=1");
                exit();
            } else {
                header("Location: index.php?action=miPerfil&error=password_incorrecta");
                exit();
            }
        }
    }
    // Aumentar en 1 la cantidad de un producto del carrito
    public function aumentarCantidad()
    {
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0 && isset($_SESSION['carrito'][$id])) {
            require_once 'config/db.php';
            $db = new Database();
            $conn = $db->connect();

            $stmt = $conn->prepare("SELECT stock FROM productos WHERE id = ? AND estado = 1");
            $stmt->execute([$id]);
            $prod = $stmt->fetch(PDO::FETCH_ASSOC);
            $stockDisponible = $prod ? intval($prod['stock']) : 0;

            if ($_SESSION['carrito'][$id]['cantidad'] < $stockDisponible) {
                $_SESSION['carrito'][$id]['cantidad']++;
            } else {
                header("Location: index.php?action=carrito&error=stock_maximo&max=" . $stockDisponible);
                exit();
            }
        }
        header("Location: index.php?action=carrito");
        exit();
    }
}
