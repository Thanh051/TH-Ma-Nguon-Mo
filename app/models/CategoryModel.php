<?php
require_once 'app/config/database.php';

class CategoryModel {
    private $conn;
    private $table = "categories";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllCategories() {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getCategoryById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch();
    }

    public function create($name, $description = '') {
        $stmt = $this->conn->prepare("INSERT INTO " . $this->table . " (name, description) VALUES (:name, :description)");
        return $stmt->execute([':name' => $name, ':description' => $description]);
    }

    public function update($id, $name, $description = '') {
        $stmt = $this->conn->prepare("UPDATE " . $this->table . " SET name = :name, description = :description WHERE id = :id");
        return $stmt->execute([':id' => (int)$id, ':name' => $name, ':description' => $description]);
    }

    public function countProducts($id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM products WHERE category_id = :id");
        $stmt->execute([':id' => (int)$id]);
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table . " WHERE id = :id");
        return $stmt->execute([':id' => (int)$id]);
    }
}
