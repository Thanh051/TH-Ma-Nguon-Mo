<?php
require_once 'app/config/database.php';

class CategoryModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllCategories() {
        $stmt = $this->conn->query("SELECT * FROM categories ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    public function getCategoryById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM categories WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch() ?: null;
    }

    public function create($name) {
        $stmt = $this->conn->prepare("INSERT INTO categories (name) VALUES (:name)");
        return $stmt->execute([':name' => $name]);
    }

    public function update($id, $name) {
        $stmt = $this->conn->prepare("UPDATE categories SET name = :name WHERE id = :id");
        return $stmt->execute([':name' => $name, ':id' => (int)$id]);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM categories WHERE id = :id");
        return $stmt->execute([':id' => (int)$id]);
    }

    public function countProducts($id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = :id");
        $stmt->execute([':id' => (int)$id]);
        return (int)$stmt->fetchColumn();
    }
}
