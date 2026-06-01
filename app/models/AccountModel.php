<?php
require_once 'app/config/database.php';

class AccountModel {
    private $conn;
    private $table = "users";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Kiểm tra tên đăng nhập đã tồn tại chưa
    public function checkUsernameExists($username) {
        $query = "SELECT id FROM " . $this->table . " WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }

    // Đăng ký tài khoản mới (Mặc định quyền là 'user')
    public function register($username, $password) {
        // Mã hóa bảo mật mật khẩu bằng thuật toán BCRYPT trước khi lưu vào DB
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO " . $this->table . " (username, password, role) VALUES (:username, :password, 'user')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed_password);

        return $stmt->execute();
    }

    // Kiểm tra đăng nhập
    public function login($username, $password) {
        // Phải SELECT * hoặc SELECT ít nhất là id, username, password, role
        $query = "SELECT * FROM users WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Kiểm tra mật khẩu mã hóa
        if ($user && password_verify($password, $user['password'])) {
            return $user; // Trả về toàn bộ mảng chứa ID, Role...
        }
        return false;
    }
}