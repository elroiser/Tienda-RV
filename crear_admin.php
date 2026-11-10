<?php
require_once 'config/db.php';
$db = new Database();
$conn = $db->connect();

// Limpiamos la tabla primero para que no haya duplicados
$conn->exec("TRUNCATE TABLE administradores");

// Definimos usuario y contraseña clara
$usuario = 'admin';
$passwordClaro = '123456'; 

// Generamos el hash correcto
$passwordHash = password_hash($passwordClaro, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO administradores (usuario, password) VALUES (?, ?)");
$stmt->execute([$usuario, $passwordHash]);

echo "¡Listo! Administrador creado con éxito. Usuario: <b>admin</b> | Contraseña: <b>123456</b><br>";
echo '<br><a href="index.php?action=adminLogin">Ir al login de administrador</a>';
?>