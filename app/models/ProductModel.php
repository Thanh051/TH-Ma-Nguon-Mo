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
    public function getAllProducts($category_id = null) {
        if ($category_id !== null && $category_id > 0) {
            // Thêm LEFT JOIN để lấy được c.name đặt tên thành category_name khi lọc theo danh mục
            $query = "SELECT p.*, c.name AS category_name 
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      WHERE p.category_id = :category_id 
                      ORDER BY p.id DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        } else {
            // Thêm LEFT JOIN để lấy được c.name đặt tên thành category_name cho tất cả sản phẩm
            $query = "SELECT p.*, c.name AS category_name 
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      ORDER BY p.id DESC";
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. LẤY CHI TIẾT 1 SẢN PHẨM THEO ID [ĐÃ FIX LỖI BIẾN $query THÀNH $sql]
    public function getProductById($id) {
        $sql = "SELECT p.*, c.name AS category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = :id";
        $stmt = $this->conn->prepare($sql); // ĐÃ FIX: Đổi từ $query thành $sql cho đồng bộ
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
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
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    // 4. CẬP NHẬT SẢN PHẨM (Update)
    public function update($id, $name, $price, $image, $description, $category_id) {
        $query = "UPDATE " . $this->table . " 
                  SET name = :name, price = :price, image = :image, description = :description, category_id = :category_id 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    // 5. XÓA SẢN PHẨM (Delete)
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // 6. LƯU ĐƠN HÀNG & CHI TIẾT ĐƠN HÀNG (Thanh toán với Transaction)
    // ĐÃ UPDATE: Tự động nhận diện lưu kèm `user_id` để phục vụ tính năng Lịch sử đơn hàng
    public function saveOrder($name, $email, $phone, $address, $total, $cart) {
        try {
            // Bắt đầu tiến trình Transaction an toàn dữ liệu
            $this->conn->beginTransaction();

            // Lấy ID người dùng đăng nhập hiện tại nếu có (phục vụ lưu lịch sử)
            $user_id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;

            // Chèn dữ liệu vào bảng hóa đơn chính (orders) có thêm cột user_id
            $query = "INSERT INTO orders (user_id, customer_name, email, phone, address, total_price) 
                      VALUES (:user_id, :name, :email, :phone, :address, :total)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':user_id' => $user_id,
                ':name'    => $name, 
                ':email'   => $email, 
                ':phone'   => $phone, 
                ':address' => $address, 
                ':total'   => $total
            ]);
            
            // Lấy ID tự động tăng của đơn hàng vừa chèn
            $order_id = $this->conn->lastInsertId();

            // Duyệt qua giỏ hàng để chèn vào bảng chi tiết hóa đơn (order_details)
            foreach ($cart as $product_id => $item) {
                $queryDetail = "INSERT INTO order_details (order_id, product_id, quantity, price) 
                                VALUES (:order_id, :product_id, :quantity, :price)";
                $stmtDetail = $this->conn->prepare($queryDetail);
                $stmtDetail->execute([
                    ':order_id'   => $order_id,
                    ':product_id' => $product_id,
                    ':quantity'   => $item['qty'],
                    ':price'      => $item['price']
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

    // 8. LẤY TOÀN BỘ ĐƠN HÀNG ĐỂ QUẢN LÝ (Dành cho ADMIN)
    public function getAllOrders() {
        $query = "SELECT * FROM orders ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 9. LẤY CHI TIẾT SẢN PHẨM TRONG 1 ĐƠN HÀNG CỤ THỂ (Dùng chung cho cả Admin và Lịch sử đơn hàng của User)
    public function getOrderDetails($order_id) {
        $query = "SELECT od.*, p.name as product_name, p.image 
                  FROM order_details od
                  JOIN products p ON od.product_id = p.id
                  WHERE od.order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':order_id' => $order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== BỔ SUNG: HÀM LẤY LỊCH SỬ ĐƠN HÀNG CHO TỪNG USER ====================
    
    // 9.5 Lấy danh sách hóa đơn theo ID của tài khoản đang đăng nhập
    public function getOrdersByUserId($user_id) {
        $query = "SELECT id, customer_name as name, email, phone, address, total_price as total, created_at 
                  FROM orders 
                  WHERE user_id = :user_id 
                  ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== NHÓM HÀM QUẢN LÝ GIỎ HÀNG TRONG DATABASE ====================

    // 10. Lấy toàn bộ sản phẩm trong giỏ hàng DB của 1 User (Định dạng chuẩn mảng giống Session)
    public function getCartByUserId($user_id) {
        $query = "SELECT c.qty, p.id, p.name, p.price, p.image 
                  FROM cart c 
                  JOIN products p ON c.product_id = p.id 
                  WHERE c.user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $cart = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cart[$row['id']] = [
                'name'  => $row['name'],
                'price' => $row['price'],
                'image' => $row['image'],
                'qty'   => $row['qty']
            ];
        }
        return $cart;
    }

    // 11. Thêm sản phẩm hoặc cộng dồn số lượng vào bảng giỏ hàng DB
    public function addToCartDB($user_id, $product_id, $qty = 1) {
        $query = "SELECT id, qty FROM cart WHERE user_id = :user_id AND product_id = :product_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            $new_qty = $item['qty'] + $qty;
            $updateQuery = "UPDATE cart SET qty = :qty WHERE id = :id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':qty', $new_qty, PDO::PARAM_INT);
            $updateStmt->bindParam(':id', $item['id'], PDO::PARAM_INT);
            return $updateStmt->execute();
        } else {
            $insertQuery = "INSERT INTO cart (user_id, product_id, qty) VALUES (:user_id, :product_id, :qty)";
            $insertStmt = $this->conn->prepare($insertQuery);
            $insertStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $insertStmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $insertStmt->bindParam(':qty', $qty, PDO::PARAM_INT);
            return $insertStmt->execute();
        }
    }

    // 12. Xử lý tăng / giảm / xóa từng sản phẩm cụ thể của giỏ hàng DB
    public function updateCartDB($user_id, $product_id, $action) {
        $query = "SELECT id, qty FROM cart WHERE user_id = :user_id AND product_id = :product_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            if ($action === 'increase') {
                $new_qty = $item['qty'] + 1;
                $sql = "UPDATE cart SET qty = :qty WHERE id = :id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':qty', $new_qty, PDO::PARAM_INT);
                $stmt->bindParam(':id', $item['id'], PDO::PARAM_INT);
                $stmt->execute();
            } elseif ($action === 'decrease') {
                $new_qty = $item['qty'] - 1;
                if ($new_qty <= 0) {
                    $sql = "DELETE FROM cart WHERE id = :id";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindParam(':id', $item['id'], PDO::PARAM_INT);
                    $stmt->execute();
                } else {
                    $sql = "UPDATE cart SET qty = :qty WHERE id = :id";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindParam(':qty', $new_qty, PDO::PARAM_INT);
                    $stmt->bindParam(':id', $item['id'], PDO::PARAM_INT);
                    $stmt->execute();
                }
            } elseif ($action === 'remove') {
                $sql = "DELETE FROM cart WHERE id = :id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':id', $item['id'], PDO::PARAM_INT);
                $stmt->execute();
            }
        }
    }

    // 13. Dọn dẹp sạch giỏ hàng trong DATABASE của User sau khi hoàn tất mua hàng
    public function clearCartDB($user_id) {
        $query = "DELETE FROM cart WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}