<?php
require_once 'app/config/database.php';

class ProductModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Real schema: id, name, price, image, description, category_id (no stock)
    public function getAllProducts($filters = []) {
        $sql    = "SELECT p.*, c.name AS category_name
                   FROM products p
                   LEFT JOIN categories c ON c.id = p.category_id
                   WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND p.name LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['category_id']) && is_numeric($filters['category_id'])) {
            $sql .= " AND p.category_id = :category_id";
            $params[':category_id'] = (int)$filters['category_id'];
        }
        if (!empty($filters['sort_price'])) {
            $dir  = strtolower($filters['sort_price']) === 'asc' ? 'ASC' : 'DESC';
            $sql .= " ORDER BY p.price $dir";
        } else {
            $sql .= " ORDER BY p.id DESC";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getProductById($id) {
        $stmt = $this->conn->prepare(
            "SELECT p.*, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch() ?: null;
    }

    public function categoryExists($categoryId) {
        if (!$categoryId || !is_numeric($categoryId)) return false;
        $stmt = $this->conn->prepare("SELECT id FROM categories WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => (int)$categoryId]);
        return (bool)$stmt->fetch();
    }

    // Create: no stock column
    public function create($name, $price, $image, $description, $categoryId) {
        $stmt = $this->conn->prepare(
            "INSERT INTO products (name, price, image, description, category_id)
             VALUES (:name, :price, :image, :description, :category_id)"
        );
        return $stmt->execute([
            ':name'        => $name,
            ':price'       => (float)$price,
            ':image'       => $image ?: null,
            ':description' => $description,
            ':category_id' => (int)$categoryId
        ]);
    }

    public function update($id, $name, $price, $image, $description, $categoryId) {
        $stmt = $this->conn->prepare(
            "UPDATE products SET name = :name, price = :price, image = :image,
             description = :description, category_id = :category_id WHERE id = :id"
        );
        return $stmt->execute([
            ':name'        => $name,
            ':price'       => (float)$price,
            ':image'       => $image ?: null,
            ':description' => $description,
            ':category_id' => (int)$categoryId,
            ':id'          => (int)$id
        ]);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute([':id' => (int)$id]);
    }
}
