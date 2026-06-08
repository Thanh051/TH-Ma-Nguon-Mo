<?php
require_once 'app/controllers/BaseApiController.php';
require_once 'app/config/database.php';

class OrderApiController extends BaseApiController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    private function cartTotal() {
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += (float)$item['price'] * (int)$item['quantity'];
        }
        return $total;
    }

    // GET /api/order
    public function index() {
        if (!$this->requireMethod('GET')) return;
        $stmt = $this->conn->prepare("SELECT * FROM orders ORDER BY id DESC");
        $stmt->execute();
        $this->json(['status' => true, 'data' => $stmt->fetchAll()]);
    }

    // GET /api/order/detail/{id}
    public function detail($id) {
        if (!$this->requireMethod('GET')) return;
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => (int)$id]);
        $order = $stmt->fetch();
        if (!$order) {
            $this->json(['status' => false, 'message' => 'Đơn hàng không tồn tại'], 404);
            return;
        }

        $detailStmt = $this->conn->prepare("SELECT od.*, p.name AS product_name
                                            FROM order_details od
                                            LEFT JOIN products p ON p.id = od.product_id
                                            WHERE od.order_id = :id");
        $detailStmt->execute([':id' => (int)$id]);
        $order['details'] = $detailStmt->fetchAll();

        $this->json(['status' => true, 'data' => $order]);
    }

    // POST /api/order/create
    public function create() {
        if (!$this->requireMethod('POST')) return;
        if (empty($_SESSION['cart'])) {
            $this->json(['status' => false, 'message' => 'Không thể đặt hàng vì giỏ hàng đang rỗng'], 400);
            return;
        }

        $data = $this->body();
        $name = trim($data['customer_name'] ?? $data['name'] ?? '');
        $phone = trim($data['customer_phone'] ?? $data['phone'] ?? '');
        $address = trim($data['customer_address'] ?? $data['address'] ?? '');

        if ($name === '' || $phone === '' || $address === '') {
            $this->json(['status' => false, 'message' => 'Vui lòng nhập đủ tên, số điện thoại và địa chỉ'], 400);
            return;
        }

        $total = $this->cartTotal();

        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare("INSERT INTO orders (customer_name, customer_phone, customer_address, total_amount, order_status, payment_status)
                                          VALUES (:name, :phone, :address, :total, 'pending', 'unpaid')");
            $stmt->execute([
                ':name' => $name,
                ':phone' => $phone,
                ':address' => $address,
                ':total' => $total
            ]);
            $orderId = (int)$this->conn->lastInsertId();

            $detailStmt = $this->conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, price)
                                                VALUES (:order_id, :product_id, :quantity, :price)");
            foreach ($_SESSION['cart'] as $productId => $item) {
                $detailStmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => (int)$productId,
                    ':quantity' => (int)$item['quantity'],
                    ':price' => (float)$item['price']
                ]);
            }

            $_SESSION['cart'] = [];
            $this->conn->commit();

            $this->json([
                'status' => true,
                'message' => 'Tạo đơn hàng bằng API thành công. Giỏ hàng đã được làm trống',
                'order_id' => $orderId,
                'total_amount' => $total
            ], 201);
        } catch (Exception $e) {
            $this->conn->rollBack();
            $this->json(['status' => false, 'message' => 'Lỗi tạo đơn hàng: ' . $e->getMessage()], 500);
        }
    }

    // DELETE /api/order/cancel/{id}
    public function cancel($id) {
        if (!$this->requireMethod('DELETE')) return;
        $stmt = $this->conn->prepare("UPDATE orders SET order_status = 'cancelled' WHERE id = :id");
        $ok = $stmt->execute([':id' => (int)$id]);
        $this->json(['status' => $ok, 'message' => $ok ? 'Đã hủy đơn hàng bằng API' : 'Không thể hủy đơn hàng']);
    }

    // PUT /api/order/updateStatus/{id}
    public function updateStatus($id) {
        if (!$this->requireMethod('PUT')) return;
        $data = $this->body();
        $status = $data['order_status'] ?? $data['status'] ?? '';
        $allowed = ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            $this->json(['status' => false, 'message' => 'Trạng thái đơn hàng không hợp lệ'], 400);
            return;
        }
        $stmt = $this->conn->prepare("UPDATE orders SET order_status = :status WHERE id = :id");
        $ok = $stmt->execute([':status' => $status, ':id' => (int)$id]);
        $this->json(['status' => $ok, 'message' => $ok ? 'Cập nhật trạng thái đơn hàng bằng API thành công' : 'Không thể cập nhật trạng thái']);
    }
}
