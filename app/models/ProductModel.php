<?php
require_once 'app/config/database.php';

class ProductModel {
    private $conn;
    private $table = "products";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllProducts($filters = []) {
        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(p.name LIKE :search OR p.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['category_id'])) {
            $where[] = "p.category_id = :category_id";
            $params[':category_id'] = (int)$filters['category_id'];
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $where[] = "p.price >= :min_price";
            $params[':min_price'] = (float)$filters['min_price'];
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $where[] = "p.price <= :max_price";
            $params[':max_price'] = (float)$filters['max_price'];
        }

        $orderBy = "p.id DESC";
        if (!empty($filters['sort_price'])) {
            $sort = strtolower($filters['sort_price']);
            if ($sort === 'asc') {
                $orderBy = "p.price ASC";
            } elseif ($sort === 'desc') {
                $orderBy = "p.price DESC";
            }
        }

        $sql = "SELECT p.*, c.name AS category_name
                FROM " . $this->table . " p
                LEFT JOIN categories c ON p.category_id = c.id";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY " . $orderBy;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getProductById($id) {
        $sql = "SELECT p.*, c.name AS category_name
                FROM " . $this->table . " p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch();
    }

    public function categoryExists($category_id) {
        if ($category_id === null || $category_id === '' || (int)$category_id <= 0) {
            return true;
        }
        $stmt = $this->conn->prepare("SELECT id FROM categories WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => (int)$category_id]);
        return (bool)$stmt->fetch();
    }

    public function create($name, $price, $image = null, $description = '', $category_id = null, $stock = 0) {
        $sql = "INSERT INTO " . $this->table . " (name, description, price, stock, category_id, image)
                VALUES (:name, :description, :price, :stock, :category_id, :image)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':price' => (float)$price,
            ':stock' => (int)$stock,
            ':category_id' => $category_id ? (int)$category_id : null,
            ':image' => $image ?: null
        ]);
    }

    public function update($id, $name, $price, $image = null, $description = '', $category_id = null, $stock = 0) {
        $sql = "UPDATE " . $this->table . "
                SET name = :name,
                    description = :description,
                    price = :price,
                    stock = :stock,
                    category_id = :category_id,
                    image = :image
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id' => (int)$id,
            ':name' => $name,
            ':description' => $description,
            ':price' => (float)$price,
            ':stock' => (int)$stock,
            ':category_id' => $category_id ? (int)$category_id : null,
            ':image' => $image ?: null
        ]);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table . " WHERE id = :id");
        return $stmt->execute([':id' => (int)$id]);
    }
}
