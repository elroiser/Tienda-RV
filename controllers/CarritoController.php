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
    public function agregar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idProducto = $_POST['id_producto'] ?? null;
            $nombreBase = $_POST['nombre_base'] ?? '';
            $aroma = $_POST['aroma'] ?? '';
            $presentacion = $_POST['presentacion'] ?? '';
            $precio = floatval($_POST['precio'] ?? 0);
            $cantidad = intval($_POST['cantidad'] ?? 1);

            if ($idProducto && $precio > 0) {
                if (!isset($_SESSION['carrito'])) {
                    $_SESSION['carrito'] = [];
                }

                // Creamos una clave única basada en el ID real del producto en la base de datos
                $claveUnica = $idProducto;

                if (isset($_SESSION['carrito'][$claveUnica])) {
                    // Si ya existe, sumamos la cantidad
                    $_SESSION['carrito'][$claveUnica]['cantidad'] += $cantidad;
                } else {
                    // Si no existe, lo creamos nuevo
                    $_SESSION['carrito'][$claveUnica] = [
                        'id' => $idProducto,
                        'nombre_base' => $nombreBase,
                        'aroma' => $aroma,
                        'presentacion' => $presentacion,
                        'precio' => $precio,
                        'cantidad' => $cantidad
                    ];
                }

                echo json_encode(['status' => 'success', 'mensaje' => 'Producto añadido correctamente']);
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

            // Datos del formulario de envío
            $nombre = trim($_POST['nombre'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            $referencia = trim($_POST['referencia'] ?? '');
            $ciudad = "Pasaje, El Oro";

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

                // ----------------------------------------------------
                // AQUÍ ES EXACTAMENTE DÓNDE VA EL BLOQUE QUE ME PEDISTE:
                // ----------------------------------------------------
                if (isset($_SESSION['cliente_id'])) {
                    // Si ya está logueado, usamos su ID directamente
                    $clienteId = $_SESSION['cliente_id'];
                } else {
                    // Si no ha iniciado sesión, verificamos si el correo ya existe o creamos uno nuevo
                    $stmtCliente = $conn->prepare("SELECT id FROM clientes WHERE email = ?");
                    $stmtCliente->execute([$email !== '' ? $email : 'cliente_' . time() . '@rvlimpieza.com']);
                    $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

                    if ($cliente) {
                        $clienteId = $cliente['id'];
                    } else {
                        $stmtInsertCliente = $conn->prepare("INSERT INTO clientes (nombre, email, password, telefono, direccion, ciudad) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmtInsertCliente->execute([
                            $nombre,
                            $email !== '' ? $email : 'cliente_' . time() . '@rvlimpieza.com',
                            password_hash('123456', PASSWORD_DEFAULT),
                            $telefono,
                            $direccion,
                            $ciudad
                        ]);
                        $clienteId = $conn->lastInsertId();
                    }
                }
                // ----------------------------------------------------

                // 2. Insertar el pedido en la tabla pedidos
                $stmtPedido = $conn->prepare("INSERT INTO pedidos (cliente_id, total, estado, metodo_pago, direccion_envio, referencia_envio) VALUES (?, ?, 'Pendiente', 'Efectivo Contra Entrega', ?, ?)");
                $stmtPedido->execute([$clienteId, $totalGeneral, $direccion, $referencia]);
                $pedidoId = $conn->lastInsertId();

                // 3. Insertar los detalles de los productos del carrito
                $stmtDetalle = $conn->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");

                foreach ($carrito as $item) {
                    $stmtDetalle->execute([
                        $pedidoId,
                        $item['id'],
                        $item['cantidad'],
                        $item['precio']
                    ]);
                }



                /// 🔥 CREAR MENSAJE PARA WHATSAPP
                $telefonoPapa = "593968865893"; // El número de tu papá

                $textoWa = "*¡NUEVO PEDIDO - RV LIMPIEZA!* 🧼\n\n";
                $textoWa .= "*Orden N°:* " . $pedidoId . "\n";
                $textoWa .= "*Cliente:* " . $nombre . "\n";
                $textoWa .= "*Teléfono:* " . $telefono . "\n";
                $textoWa .= "*Dirección:* " . $direccion . "\n";

                if (!empty($referencia)) {
                    $textoWa .= "*Ref:* " . $referencia . "\n";
                }

                $textoWa .= "\n*🛒 DETALLE DEL PEDIDO:*\n";
                foreach ($carrito as $item) {
                    $nombreAroma = !empty($item['aroma']) ? " (" . $item['aroma'] . ")" : "";
                    $textoWa .= "- " . $item['cantidad'] . "x " . $item['nombre_base'] . $nombreAroma . " [" . $item['presentacion'] . "] -> $" . number_format($item['precio'] * $item['cantidad'], 2) . "\n";
                }

                $textoWa .= "\n*💰 TOTAL A PAGAR:* $" . number_format($totalGeneral, 2) . "\n";
                $textoWa .= "*💳 Método:* Efectivo Contra Entrega\n\n";
                $textoWa .= "_Hola, confirmo mi pedido para que me lo envíen._";

                // Guardar la URL de WhatsApp en una sesión temporal
                $_SESSION['whatsapp_url'] = "https://wa.me/" . $telefonoPapa . "?text=" . urlencode($textoWa);

                // --- INICIO DE NOTIFICACIÓN FIREBASE ---
                try {
                    $factory = (new Factory)->withServiceAccount(__DIR__ . '/../tienda-rv-b288dc407207.json');
                    $messaging = $factory->createMessaging();

                    // Consultamos todos los tokens de los administradores guardados en MySQL
                    $stmtTokens = $conn->prepare("SELECT token FROM admin_tokens");
                    $stmtTokens->execute();
                    $tokensAdmins = $stmtTokens->fetchAll(PDO::FETCH_COLUMN);

                    if (!empty($tokensAdmins)) {
                        foreach ($tokensAdmins as $deviceToken) {
                            $mensaje = CloudMessage::withTarget('token', $deviceToken)
                                ->withNotification(Notification::create(
                                    "¡Nuevo Pedido #" . $pedidoId . "!",
                                    $nombre . " acaba de hacer una compra por $" . number_format($totalGeneral, 2)
                                ));

                            $messaging->send($mensaje);
                        }
                    }
                } catch (Exception $e) {
                    error_log("Error de Firebase: " . $e->getMessage());
                }
                // --- FIN DE NOTIFICACIÓN FIREBASE ---

                // Confirmar transacción en la base de datos
                $conn->commit();

                // Vaciar el carrito
                unset($_SESSION['carrito']);

                // Redirigir a la vista de éxito
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
}
