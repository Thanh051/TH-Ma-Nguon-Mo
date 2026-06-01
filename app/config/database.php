<?php
class Database {
    private $host = "localhost";
    private $db_name = "My_Store";
    private $username = "root"; // Đổi nếu bạn dùng mật khẩu khác
    private $password = "";     
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $exception) {
            echo "Lỗi kết nối dữ liệu: " . $exception->getMessage();
        }
        return $this->conn;
    }
}