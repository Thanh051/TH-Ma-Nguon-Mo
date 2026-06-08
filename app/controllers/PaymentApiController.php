<?php
require_once 'app/controllers/BaseApiController.php';
require_once 'app/config/database.php';

class PaymentApiController extends BaseApiController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // POST /api/payment/create
    public function create() {
        if (!$this->requireMethod('POST')) return;
        $data = $this->body();
        $orderId = (int)($data['order_id'] ?? 0);
        $method = $data['method'] ?? 'cod';
        $allowed = ['cod', 'bank_transfer', 'wallet'];

        if ($orderId <= 0) {
            $this->json(['status' => false, 'message' => 'Thiếu order_id'], 400);
            return;
        }
        if (!in_array($method, $allowed, true)) {
            $this->json(['status' => false, 'message' => 'Phương thức thanh toán không hợp lệ'], 400);
            return;
        }

        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $orderId]);
        $order = $stmt->fetch();
        if (!$order) {
            $this->json(['status' => false, 'message' => 'Đơn hàng không tồn tại'], 404);
            return;
        }
        if (($order['payment_status'] ?? '') === 'paid') {
            $this->json(['status' => false, 'message' => 'Đơn hàng này đã thanh toán, không thể thanh toán lại'], 400);
            return;
        }

        $paymentStatus = $method === 'cod' ? 'cod_pending' : 'paid';
        $paymentStmt = $this->conn->prepare("INSERT INTO payments (order_id, method, amount, status)
                                             VALUES (:order_id, :method, :amount, :status)");
        $paymentStmt->execute([
            ':order_id' => $orderId,
            ':method' => $method,
            ':amount' => $order['total_amount'],
            ':status' => $paymentStatus
        ]);

        $update = $this->conn->prepare("UPDATE orders SET payment_status = :status WHERE id = :id");
        $update->execute([':status' => $paymentStatus, ':id' => $orderId]);

        $this->json([
            'status' => true,
            'message' => 'Tạo thanh toán bằng API thành công',
            'payment_id' => (int)$this->conn->lastInsertId(),
            'payment_status' => $paymentStatus
        ], 201);
    }
}
