<?php
// api_productos.php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once 'config/db.php';

try {
    $db = new Database();
    $conn = $db->connect();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $accion = $_POST['accion'] ?? 'actualizar';

        // --- ACCIÓN 1: CREAR NUEVO PRODUCTO ---
        if ($accion === 'crear') {
            $nombreBase    = trim($_POST['nombre_base'] ?? '');
            $aroma         = trim($_POST['aroma'] ?? '');
            $presentacion  = trim($_POST['presentacion'] ?? '');
            $categoriaId   = intval($_POST['categoria_id'] ?? 1);
            $precio        = floatval($_POST['precio'] ?? 0);
            $stock         = intval($_POST['stock'] ?? 0);
            $codigoBarras  = trim($_POST['codigo_barras'] ?? '');

            if ($nombreBase === '' || $presentacion === '' || $precio <= 0) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Nombre, presentación y precio mayor a 0 son obligatorios"
                ]);
                exit;
            }

            // Construir el nombre estandarizado para el catálogo web: "Nombre Aroma (Presentación)"
            $nombreCompleto = $nombreBase;
            if ($aroma !== '') {
                $nombreCompleto .= " " . $aroma;
            }
            $nombreCompleto .= " (" . $presentacion . ")";

            // Generar código de barras automático si viene vacío
            if ($codigoBarras === '') {
                $codigoBarras = '786' . time();
            }

            // Procesar imagen subida desde Android (si existe)
            $nombreArchivoImagen = 'default.png';
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $directorioDestino = __DIR__ . '/public/img/productos/';
                if (!is_dir($directorioDestino)) {
                    mkdir($directorioDestino, 0775, true);
                }

                $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $extension = 'jpg';
                }

                $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($nombreBase));
                $nombreArchivoImagen = $nombreLimpio . '_' . time() . '.' . $extension;

                if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $directorioDestino . $nombreArchivoImagen)) {
                    $nombreArchivoImagen = 'default.png';
                }
            }

            $stmtInsert = $conn->prepare("
                INSERT INTO productos (categoria_id, codigo_barras, nombre, precio, stock, imagen, estado)
                VALUES (?, ?, ?, ?, ?, ?, 1)
            ");
            $stmtInsert->execute([
                $categoriaId,
                $codigoBarras,
                $nombreCompleto,
                $precio,
                $stock,
                $nombreArchivoImagen
            ]);

            echo json_encode([
                "status" => "success",
                "message" => "Producto '$nombreCompleto' agregado correctamente"
            ]);
            exit;
        }

        // --- ACCIÓN 2: ACTUALIZAR STOCK Y PRECIO EXISTENTE ---
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

    // --- GET: CONSULTAR PRODUCTOS Y CATEGORÍAS ---
    $stmt = $conn->prepare("SELECT id, nombre, precio, stock, imagen FROM productos ORDER BY id DESC");
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtCat = $conn->prepare("SELECT id, nombre FROM categorias ORDER BY id ASC");
    $stmtCat->execute();
    $categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => $productos,
        "categorias" => $categorias
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>