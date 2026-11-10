<?php


// 1. Configuramos las cabeceras para que escupa JSON y permita conexiones externas
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once 'config/db.php';

try {
    $db = new Database();
    $conn = $db->connect();

    // 2. Consultamos los pedidos igual que en el panel de administración
    $stmt = $conn->prepare("
        SELECT p.id, p.total, p.estado, p.metodo_pago, p.direccion_envio, p.referencia_envio, p.fecha_pedido, 
               c.nombre as cliente_nombre, c.telefono as cliente_telefono 
        FROM pedidos p 
        JOIN clientes c ON p.cliente_id = c.id 
        ORDER BY p.fecha_pedido DESC
    ");
    $stmt->execute();
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Buscamos los productos detallados para cada pedido
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

    // 4. Imprimimos el resultado en formato JSON puro
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