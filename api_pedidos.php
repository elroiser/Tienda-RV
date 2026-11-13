<?php
// 1. Configuramos las cabeceras para que escupa JSON y permita conexiones externas
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config/db.php';

try {
    $db = new Database();
    $conn = $db->connect();

    // 2. SI ES UNA PETICIÓN POST: Actualizamos el estado del pedido desde la app móvil
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $pedidoId = $_POST['pedido_id'] ?? null;
        $nuevoEstado = $_POST['estado'] ?? null;

        if ($pedidoId && $nuevoEstado) {
            $stmtUpdate = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
            $stmtUpdate->execute([$nuevoEstado, $pedidoId]);

            echo json_encode([
                "status" => "success", 
                "message" => "Estado del pedido actualizado correctamente"
            ]);
            exit;
        } else {
            echo json_encode([
                "status" => "error", 
                "message" => "Faltan datos obligatorios (pedido_id o estado)"
            ]);
            exit;
        }
    }

    // 3. SI ES UNA PETICIÓN GET: Consultamos y listamos todos los pedidos (tu código actual)
    $stmt = $conn->prepare("
        SELECT p.id, p.total, p.estado, p.metodo_pago, p.direccion_envio, p.referencia_envio, p.fecha_pedido, 
               c.nombre as cliente_nombre, c.telefono as cliente_telefono 
        FROM pedidos p 
        JOIN clientes c ON p.cliente_id = c.id 
        ORDER BY p.fecha_pedido DESC
    ");
    $stmt->execute();
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Buscamos los productos detallados para cada pedido
    foreach ($pedidos as &$pedido) {
        $stmtDetalle = $conn->prepare("
            SELECT dp.cantidad, dp.precio_unitario, pr.nombre as producto_nombre 
            FROM detalle_pedidos dp 
            JOIN productos pr ON dp.producto_id = pr.id 
            WHERE dp.pedido_id = ?
        ");
        $stmtDetalle->execute([$pedido['id']]);
        $pedido['detalles'] = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($pedido);

    // 5. Imprimimos el resultado en formato JSON puro
    echo json_encode([
        "status" => "success", 
        "data" => $pedidos
    ]);

} catch (Exception $e) {
    // Si hay error, también lo mandamos en formato JSON
    echo json_encode([
        "status" => "error", 
        "message" => $e->getMessage()
    ]);
}
?>