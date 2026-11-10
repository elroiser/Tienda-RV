<?php
// config/db.php
class Database {
    private $host = 'localhost';
    private $db_name = 'rv_limpieza_db'; // El nombre de la BD que vi en tu foto
    private $username = 'root';
    private $password = ''; // Pon tu clave si tienes una en XAMPP

    public function connect() {
        $conn = null;
        try {
            $conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->exec("set names utf8");
        } catch(PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }
        return $conn;
    }
}
?>