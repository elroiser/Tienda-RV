<?php
// api_productos.php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once 'config/db.php';

try {
    $db = new Database();
    $conn = $db->connect();

    // SI ES POST: Actualizar stock y precio
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $productoId = $_POST['producto_id'] ?? null;
        $nuevoStock = $_POST['stock'] ?? null;
        $nuevoPrecio = $_POST['precio'] ?? null;

        if ($productoId !== null && $nuevoStock !== null && $nuevoPrecio !== null) {
            $stmtUpdate = $conn->prepare("UPDATE productos SET stock = ?, precio = ? WHERE id = ?");
            $stmtUpdate->execute([$nuevoStock, $nuevoPrecio, $productoId]);

            echo json_encode([
                "status" => "success",
                "message" => "Producto actualizado correctamente"
            ]);
            exit;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Faltan datos obligatorios"
            ]);
            exit;
        }
    }

    // SI ES GET: Consultar productos
    $stmt = $conn->prepare("SELECT id, nombre, precio, stock, imagen FROM productos ORDER BY nombre ASC");
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => $productos
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>