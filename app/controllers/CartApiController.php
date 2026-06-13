<?php
require_once 'app/controllers/BaseApiController.php';
require_once 'app/models/ProductModel.php';
require_once 'app/config/database.php';

class CartApiController extends BaseApiController {
    private $productModel;
    private $cartKey;
    private $conn;
    private $userId; // null nếu chưa đăng nhập

    public function __construct() {
        $this->productModel = new ProductModel();
        $db = new Database();
        $this->conn = $db->getConnection();

        if (!empty($_SESSION['user']['id'])) {
            $this->userId = (int)$_SESSION['user']['id'];
            $this->cartKey = 'cart_' . $this->userId;
        } else {
            $this->userId = null;
            $this->cartKey = null; // chưa đăng nhập, không có cart
        }

        // Khởi tạo session cart nếu đã đăng nhập
        if ($this->cartKey && !isset($_SESSION[$this->cartKey])) {
            $_SESSION[$this->cartKey] = [];
        }
    }

    // Đồng bộ session cart lên DB (chỉ khi đăng nhập)
    private function syncCartToDB() {
        if (!$this->userId) return;
        // Xóa cart cũ trong DB, rồi insert lại từ session
        $this->conn->prepare("DELETE FROM cart WHERE user_id = :uid")
                   ->execute([':uid' => $this->userId]);

        if (empty($_SESSION[$this->cartKey])) return;

        $stmt = $this->conn->prepare(
            "INSERT INTO cart (user_id, product_id, qty) VALUES (:uid, :pid, :qty)
             ON DUPLICATE KEY UPDATE qty = :qty"
        );
        foreach ($_SESSION[$this->cartKey] as $productId => $item) {
            $stmt->execute([
                ':uid' => $this->userId,
                ':pid' => (int)$productId,
                ':qty' => (int)$item['quantity'],
            ]);
        }
    }

    // Load cart từ DB vào session (gọi khi login)
    public static function loadFromDB($conn, $userId, $productModel) {
        $cartKey = 'cart_' . $userId;
        $_SESSION[$cartKey] = [];

        $stmt = $conn->prepare(
            "SELECT c.product_id, c.qty, p.name, p.price, p.image
             FROM cart c
             JOIN products p ON p.id = c.product_id
             WHERE c.user_id = :uid"
        );
        $stmt->execute([':uid' => $userId]);
        $rows = $stmt->fetchAll();

        foreach ($rows as $row) {
            $_SESSION[$cartKey][$row['product_id']] = [
                'name'     => $row['name'],
                'price'    => (float)$row['price'],
                'image'    => $row['image'],
                'quantity' => (int)$row['qty'],
            ];
        }
    }

    // Xóa cart trong DB khi logout
    public static function clearDB($conn, $userId) {
        $conn->prepare("DELETE FROM cart WHERE user_id = :uid")
             ->execute([':uid' => $userId]);
        $cartKey = 'cart_' . $userId;
        $_SESSION[$cartKey] = [];
    }

    private function cartData() {
        if (!$this->cartKey) return ['items' => [], 'total' => 0, 'count' => 0];
        $items = [];
        $total = 0;
        foreach ($_SESSION[$this->cartKey] as $id => $item) {
            $quantity = (int)($item['quantity'] ?? 0);
            $price    = (float)($item['price'] ?? 0);
            $subtotal = $quantity * $price;
            $total   += $subtotal;
            $items[]  = [
                'id'       => (int)$id,
                'name'     => $item['name'] ?? '',
                'price'    => $price,
                'image'    => $item['image'] ?? null,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ];
        }
        return ['items' => $items, 'total' => $total, 'count' => count($items)];
    }

    // GET /api/cart
    public function index() {
        if (!$this->requireMethod('GET')) return;
        if (!$this->userId) {
            $this->json(['status' => false, 'message' => 'Vui lòng đăng nhập để xem giỏ hàng', 'require_login' => true], 401);
            return;
        }
        $this->json(['status' => true, 'data' => $this->cartData()]);
    }

    // GET /api/cart/total
    public function total() {
        if (!$this->requireMethod('GET')) return;
        $data = $this->cartData();
        $this->json(['status' => true, 'total' => $data['total'], 'count' => $data['count']]);
    }

    // POST /api/cart/add  – yêu cầu đăng nhập
    public function add() {
        if (!$this->requireMethod('POST')) return;

        // Yêu cầu đăng nhập
        if (!$this->userId) {
            $this->json(['status' => false, 'message' => 'Vui lòng đăng nhập để thêm vào giỏ hàng', 'require_login' => true], 401);
            return;
        }

        $data      = $this->body();
        $productId = (int)($data['product_id'] ?? 0);
        $quantity  = (int)($data['quantity'] ?? 1);

        if ($productId <= 0) { $this->json(['status' => false, 'message' => 'Thiếu product_id'], 400); return; }
        if ($quantity <= 0)  { $this->json(['status' => false, 'message' => 'Số lượng phải lớn hơn 0'], 400); return; }

        $product = $this->productModel->getProductById($productId);
        if (!$product) { $this->json(['status' => false, 'message' => 'Sản phẩm không tồn tại'], 404); return; }

        if (!isset($_SESSION[$this->cartKey][$productId])) {
            $_SESSION[$this->cartKey][$productId] = [
                'name'     => $product['name'],
                'price'    => (float)$product['price'],
                'image'    => $product['image'] ?? null,
                'quantity' => 0,
            ];
        }
        $_SESSION[$this->cartKey][$productId]['quantity'] += $quantity;

        // Đồng bộ lên DB
        $this->syncCartToDB();

        $this->json(['status' => true, 'message' => 'Đã thêm sản phẩm vào giỏ hàng', 'data' => $this->cartData()], 201);
    }

    // PUT /api/cart/update/{product_id}
    public function update($id) {
        if (!$this->requireMethod('PUT')) return;
        if (!$this->userId) { $this->json(['status' => false, 'message' => 'Chưa đăng nhập', 'require_login' => true], 401); return; }

        $productId = (int)$id;
        $data      = $this->body();
        $quantity  = (int)($data['quantity'] ?? 0);

        if (!isset($_SESSION[$this->cartKey][$productId])) {
            $this->json(['status' => false, 'message' => 'Sản phẩm chưa có trong giỏ hàng'], 404); return;
        }
        if ($quantity <= 0) { $this->json(['status' => false, 'message' => 'Số lượng phải lớn hơn 0'], 400); return; }

        $_SESSION[$this->cartKey][$productId]['quantity'] = $quantity;
        $this->syncCartToDB();

        $this->json(['status' => true, 'message' => 'Cập nhật giỏ hàng thành công', 'data' => $this->cartData()]);
    }

    // DELETE /api/cart/delete/{product_id}
    public function delete($id) {
        if (!$this->requireMethod('DELETE')) return;
        if (!$this->userId) { $this->json(['status' => false, 'message' => 'Chưa đăng nhập'], 401); return; }

        $productId = (int)$id;
        unset($_SESSION[$this->cartKey][$productId]);
        $this->syncCartToDB();

        $this->json(['status' => true, 'message' => 'Đã xóa sản phẩm khỏi giỏ hàng', 'data' => $this->cartData()]);
    }

    // DELETE /api/cart/clear
    public function clear() {
        if (!$this->requireMethod('DELETE')) return;
        if (!$this->userId) { $this->json(['status' => false, 'message' => 'Chưa đăng nhập'], 401); return; }

        $_SESSION[$this->cartKey] = [];
        $this->syncCartToDB();

        $this->json(['status' => true, 'message' => 'Đã xóa toàn bộ giỏ hàng', 'data' => $this->cartData()]);
    }
}
