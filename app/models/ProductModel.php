<?php
require_once 'app/config/database.php';

class ProductModel {
    private $conn;
    private $table = "products";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // 1. LẤY TOÀN BỘ SẢN PHẨM (Read)
    public function getAllProducts() {
        $query = "SELECT p.*, c.name as category_name FROM " . $this->table . " p 
                  LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. LẤY CHI TIẾT 1 SẢN PHẨM THEO ID
    public function getProductById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. THÊM MỚI SẢN PHẨM (Create)
    public function create($name, $price, $image, $description, $category_id) {
        $query = "INSERT INTO " . $this->table . " (name, price, image, description, category_id) 
                  VALUES (:name, :price, :image, :description, :category_id)";
                  
        $stmt = $this->conn->prepare($query);
        
        // Ràng buộc dữ liệu an toàn tránh lỗi SQL Injection
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':category_id', $category_id);
        
        return $stmt->execute();
    }

    // 4. CẬP NHẬT SẢN PHẨM (Update) - Đã chuẩn hóa bindParam an toàn hơn
    public function update($id, $name, $price, $image, $description, $category_id) {
        $query = "UPDATE " . $this->table . " 
                  SET name = :name, price = :price, image = :image, description = :description, category_id = :category_id 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':category_id', $category_id);
        
        return $stmt->execute();
    }

    // 5. XÓA SẢN PHẨM (Delete)
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // 6. LƯU ĐƠN HÀNG & CHI TIẾT ĐƠN HÀNG (Thanh toán với Transaction)
    public function saveOrder($name, $email, $phone, $address, $total, $cart) {
        try {
            // Bắt đầu tiến trình Transaction an toàn dữ liệu
            $this->conn->beginTransaction();

            // Chèn dữ liệu vào bảng hóa đơn chính (orders)
            $query = "INSERT INTO orders (customer_name, email, phone, address, total_price) 
                      VALUES (:name, :email, :phone, :address, :total)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':name' => $name, 
                ':email' => $email, 
                ':phone' => $phone, 
                ':address' => $address, 
                ':total' => $total
            ]);
            
            // Lấy ID tự động tăng của đơn hàng vừa chèn
            $order_id = $this->conn->lastInsertId();

            // Duyệt qua giỏ hàng để chèn vào bảng chi tiết hóa đơn (order_details)
            foreach ($cart as $product_id => $item) {
                $queryDetail = "INSERT INTO order_details (order_id, product_id, quantity, price) 
                                VALUES (:order_id, :product_id, :quantity, :price)";
                $stmtDetail = $this->conn->prepare($queryDetail);
                $stmtDetail->execute([
                    ':order_id' => $order_id,
                    ':product_id' => $product_id,
                    ':quantity' => $item['qty'],
                    ':price' => $item['price']
                ]);
            }

            // Nếu mọi câu lệnh trên chạy mượt mà, xác nhận lưu vĩnh viễn vào DB
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Nếu có bất cứ một dòng nào bị lỗi, hoàn tác (hủy bỏ) toàn bộ tiến trình trên
            $this->conn->rollBack();
            return false;
        }
    }
    
    // 7. TRUY VẤN LỌC SẢN PHẨM THEO MÃ DANH MỤC
    public function getProductsByCategory($category_id) {
        $query = "SELECT p.*, c.name as category_name FROM " . $this->table . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.category_id = :category_id 
                  ORDER BY p.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Thêm vào trong Class ProductModel
public function getAllOrders() {
    $query = "SELECT * FROM orders ORDER BY created_at DESC";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Lấy chi tiết các sản phẩm trong 1 đơn hàng cụ thể
public function getOrderDetails($order_id) {
    $query = "SELECT od.*, p.name as product_name 
              FROM order_details od
              JOIN products p ON od.product_id = p.id
              WHERE od.order_id = :order_id";
    $stmt = $this->conn->prepare($query);
    $stmt->execute([':order_id' => $order_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    
}