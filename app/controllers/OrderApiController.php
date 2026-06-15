<?php
require_once 'app/controllers/BaseApiController.php';
require_once 'app/config/database.php';

/**
 * OrderApiController – adapted for real DB schema:
 * orders: id, customer_name, customer_phone, customer_address, total_amount,
 *         order_status, payment_status, created_at
 * order_details: id, order_id, product_id, quantity, price
 * payments: id, order_id, method, amount, status, created_at
 */
class OrderApiController extends BaseApiController {
    private $conn;
    private $cartKey;
    private $userId;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
        // Giỏ hàng riêng biệt theo user
        $this->userId = !empty($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
        $this->cartKey = $this->userId ? 'cart_' . $this->userId : 'cart_guest';
        if (!isset($_SESSION[$this->cartKey])) {
            $_SESSION[$this->cartKey] = [];
        }
    }

    private function cartTotal() {
        $total = 0;
        foreach ($_SESSION[$this->cartKey] as $item) {
            $total += (float)$item['price'] * (int)$item['quantity'];
        }
        return $total;
    }

    // GET /api/order  – danh sách tất cả đơn hàng (chỉ admin)
    public function index() {
        if (!$this->requireMethod('GET')) return;
        $user = $this->requireRole('admin');
        $stmt = $this->conn->prepare("SELECT * FROM orders ORDER BY id DESC");
        $stmt->execute();
        $orders = $stmt->fetchAll();
        $this->json(['status' => true, 'data' => $orders]);
    }

    // GET /api/order/mine – danh sách đơn hàng (không có cột user_id nên trả về tất cả)
    public function mine() {
        if (!$this->requireMethod('GET')) return;
        $this->requireAuth();
        $stmt = $this->conn->prepare("SELECT * FROM orders ORDER BY id DESC");
        $stmt->execute();
        $orders = $stmt->fetchAll();
        $this->json(['status' => true, 'data' => $orders]);
    }

    // GET /api/order/detail/{id}
    public function detail($id) {
        if (!$this->requireMethod('GET')) return;
        $this->requireAuth();
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => (int)$id]);
        $order = $stmt->fetch();
        if (!$order) {
            $this->json(['status' => false, 'message' => 'Đơn hàng không tồn tại'], 404);
            return;
        }
        $detailStmt = $this->conn->prepare(
            "SELECT od.*, p.name AS product_name
             FROM order_details od
             LEFT JOIN products p ON p.id = od.product_id
             WHERE od.order_id = :id"
        );
        $detailStmt->execute([':id' => (int)$id]);
        $order['details'] = $detailStmt->fetchAll();
        $this->json(['status' => true, 'data' => $order]);
    }

    // POST /api/order/create  – tạo đơn hàng từ giỏ hàng session
    public function create() {
        if (!$this->requireMethod('POST')) return;
        if (empty($_SESSION[$this->cartKey])) {
            $this->json(['status' => false, 'message' => 'Giỏ hàng đang trống, không thể đặt hàng'], 400);
            return;
        }

        $data    = $this->body();
        // Support both field naming conventions
        $name    = trim($data['customer_name'] ?? $data['name'] ?? '');
        $email   = trim($data['email'] ?? '');
        $phone   = trim($data['customer_phone'] ?? $data['phone'] ?? '');
        $address = trim($data['customer_address'] ?? $data['address'] ?? '');

        if ($name === '' || $phone === '' || $address === '') {
            $this->json(['status' => false, 'message' => 'Vui lòng nhập đủ họ tên, số điện thoại và địa chỉ'], 400);
            return;
        }

        $total = $this->cartTotal();
        // $this->userId đã có sẵn từ constructor

        try {
            $this->conn->beginTransaction();

            // Chèn đơn hàng – dùng đúng tên cột trong DB:
            // customer_name, customer_phone, customer_address, total_amount
            $stmt = $this->conn->prepare(
                "INSERT INTO orders (customer_name, customer_phone, customer_address, total_amount)
                 VALUES (:name, :phone, :address, :total)"
            );
            $stmt->execute([
                ':name'    => $name,
                ':phone'   => $phone,
                ':address' => $address,
                ':total'   => $total,
            ]);
            $orderId = (int)$this->conn->lastInsertId();

            $detailStmt = $this->conn->prepare(
                "INSERT INTO order_details (order_id, product_id, quantity, price)
                 VALUES (:order_id, :product_id, :qty, :price)"
            );
            foreach ($_SESSION[$this->cartKey] as $productId => $item) {
                $detailStmt->execute([
                    ':order_id'   => $orderId,
                    ':product_id' => (int)$productId,
                    ':qty'        => (int)$item['quantity'],
                    ':price'      => (float)$item['price'],
                ]);
            }

            $_SESSION[$this->cartKey] = [];
            if ($this->userId) {
                $this->conn->prepare("DELETE FROM cart WHERE user_id = :uid")
                           ->execute([':uid' => $this->userId]);
            }
            $this->conn->commit();

            $this->json([
                'status'       => true,
                'message'      => 'Đặt hàng thành công! Giỏ hàng đã được làm trống.',
                'order_id'     => $orderId,
                'total_amount' => $total,
            ], 201);
        } catch (Exception $e) {
            $this->conn->rollBack();
            $this->json(['status' => false, 'message' => 'Lỗi tạo đơn hàng: ' . $e->getMessage()], 500);
        }
    }

    // DELETE /api/order/cancel/{id}
    public function cancel($id) {
        if (!$this->requireMethod('DELETE')) return;
        $this->requireAuth();
        $stmt = $this->conn->prepare("SELECT id FROM orders WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => (int)$id]);
        $order = $stmt->fetch();
        if (!$order) {
            $this->json(['status' => false, 'message' => 'Đơn hàng không tồn tại'], 404);
            return;
        }
        $del = $this->conn->prepare("DELETE FROM orders WHERE id = :id");
        $ok  = $del->execute([':id' => (int)$id]);
        $this->json([
            'status'  => $ok,
            'message' => $ok ? 'Đã hủy và xóa đơn hàng' : 'Không thể hủy đơn hàng'
        ]);
    }

    // PUT /api/order/updateStatus/{id} – không có cột status nên không dùng
    // Giữ lại endpoint để không báo lỗi 404
    public function updateStatus($id) {
        if (!$this->requireMethod('PUT')) return;
        $this->json([
            'status'  => false,
            'message' => 'Schema hiện tại không hỗ trợ cập nhật trạng thái'
        ], 400);
    }
}
