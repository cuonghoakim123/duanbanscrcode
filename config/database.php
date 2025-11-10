<?php
require_once __DIR__ . '/env_loader.php';

class Database {
    private $host;
    private $database_name;
    private $username;
    private $password;
    public $conn;
    
    public function __construct() {
        $this->host = EnvLoader::get('DB_HOST', 'localhost');
        $this->database_name = EnvLoader::get('DB_NAME', 'ecommerce_db');
        $this->username = EnvLoader::get('DB_USER', 'root');
        $this->password = EnvLoader::get('DB_PASS', '');
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->database_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8mb4");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Lỗi kết nối: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
