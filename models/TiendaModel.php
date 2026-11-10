<?php
// models/TiendaModel.php

class TiendaModel {
    private $db;

    public function __construct() {
        // Al crear el modelo, conectamos a la BD usando el archivo que ya creamos
        $this->db = (new Database())->connect();
    }

    // --- LÓGICA DE PRODUCTOS ---
    public function obtenerProductos() {
        try {
            // Seleccionamos todos los productos activos, uniendo con su categoría
            $sql = "SELECT p.*, c.nombre as categoria_nombre 
                    FROM productos p 
                    LEFT JOIN categorias c ON p.categoria_id = c.id 
                    WHERE p.estado = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Devuelve un array con los datos
        } catch (PDOException $e) {
            return []; // Si hay error, devuelve un array vacío
        }
    }

    // --- LÓGICA DE USUARIOS (REGISTRO) ---
    public function registrarUsuario($nombre, $email, $password, $telefono, $direccion) {
        try {
            // 1. Encriptar la contraseña (MUY IMPORTANTE por seguridad)
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            
            // 2. Insertar en la tabla clientes
            $sql = "INSERT INTO clientes (nombre, email, password, telefono, direccion) 
                    VALUES (:nombre, :email, :password, :telefono, :direccion)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $passwordHash);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':direccion', $direccion);
            
            return $stmt->execute(); // Devuelve true si se guardó bien
        } catch (PDOException $e) {
            // Error común: el email ya existe
            return false; 
        }
    }
}
?>