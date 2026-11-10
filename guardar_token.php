<?php
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $dispositivo = $_POST['dispositivo'] ?? 'Desconocido';

    if (!empty($token)) {
        $db = new Database();
        $conn = $db->connect();

        // Verificamos si el token ya existe para no duplicarlo
        $stmt = $conn->prepare("SELECT id FROM admin_tokens WHERE token = ?");
        $stmt->execute([$token]);
        
        if (!$stmt->fetch()) {
            $stmtInsert = $conn->prepare("INSERT INTO admin_tokens (token, dispositivo) VALUES (?, ?)");
            $stmtInsert->execute([$token, $dispositivo]);
        }

        echo json_encode(["status" => "success"]);
        exit();
    }
}
echo json_encode(["status" => "error"]);