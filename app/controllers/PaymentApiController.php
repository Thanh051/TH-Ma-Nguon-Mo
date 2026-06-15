<?php
require_once 'app/controllers/BaseApiController.php';
require_once 'app/config/database.php';

/**
 * PaymentApiController – No payments table in real DB.
 * Endpoint is kept but returns a success stub so the checkout flow works.
 */
class PaymentApiController extends BaseApiController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // POST /api/payment/create
    public function create() {
        if (!$this->requireMethod('POST')) return;
        $data    = $this->body();
        $orderId = (int)($data['order_id'] ?? 0);
        $method  = $data['method'] ?? 'cod';
        $allowed = ['cod', 'bank_transfer', 'wallet'];

        if ($orderId <= 0) {
            $this->json(['status' => false, 'message' => 'Thiếu order_id'], 400);
            return;
        }
        if (!in_array($method, $allowed, true)) {
            $this->json(['status' => false, 'message' => 'Phương thức thanh toán không hợp lệ'], 400);
            return;
        }

        // Verify order exists
        $stmt = $this->conn->prepare("SELECT id, total_amount FROM orders WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $orderId]);
        $order = $stmt->fetch();
        if (!$order) {
            $this->json(['status' => false, 'message' => 'Đơn hàng không tồn tại'], 404);
            return;
        }

        // Ghi nhận thanh toán vào bảng payments
        $payStmt = $this->conn->prepare(
            "INSERT INTO payments (order_id, method, amount, status)
             VALUES (:order_id, :method, :amount, :status)"
        );
        $payStatus = ($method === 'cod') ? 'cod_pending' : 'paid';
        $payStmt->execute([
            ':order_id' => $orderId,
            ':method'   => $method,
            ':amount'   => $order['total_amount'],
            ':status'   => $payStatus,
        ]);

        $methodLabels = [
            'cod'           => 'Thanh toán khi nhận hàng (COD)',
            'bank_transfer' => 'Chuyển khoản ngân hàng',
            'wallet'        => 'Ví điện tử',
        ];

        $this->json([
            'status'         => true,
            'message'        => 'Xác nhận thanh toán thành công',
            'order_id'       => $orderId,
            'method'         => $method,
            'method_label'   => $methodLabels[$method],
            'amount'         => $order['total_amount'],
            'payment_status' => $payStatus,
        ], 201);
    }
}
