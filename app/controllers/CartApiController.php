<?php
require_once 'app/controllers/BaseApiController.php';
require_once 'app/models/ProductModel.php';

class CartApiController extends BaseApiController {
    private $productModel;

    public function __construct() {
        $this->productModel = new ProductModel();
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    private function cartData() {
        $items = [];
        $total = 0;
        foreach ($_SESSION['cart'] as $id => $item) {
            $quantity = (int)($item['quantity'] ?? 0);
            $price = (float)($item['price'] ?? 0);
            $subtotal = $quantity * $price;
            $total += $subtotal;
            $items[] = [
                'id' => (int)$id,
                'name' => $item['name'] ?? '',
                'price' => $price,
                'image' => $item['image'] ?? null,
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ];
        }
        return ['items' => $items, 'total' => $total, 'count' => count($items)];
    }

    // GET /api/cart
    public function index() {
        if (!$this->requireMethod('GET')) return;
        $this->json(['status' => true, 'data' => $this->cartData()]);
    }

    // GET /api/cart/total
    public function total() {
        if (!$this->requireMethod('GET')) return;
        $data = $this->cartData();
        $this->json(['status' => true, 'total' => $data['total'], 'count' => $data['count']]);
    }

    // POST /api/cart/add
    public function add() {
        if (!$this->requireMethod('POST')) return;
        $data = $this->body();
        $productId = (int)($data['product_id'] ?? 0);
        $quantity = (int)($data['quantity'] ?? 1);

        if ($productId <= 0) {
            $this->json(['status' => false, 'message' => 'Thiếu product_id'], 400);
            return;
        }
        if ($quantity <= 0) {
            $this->json(['status' => false, 'message' => 'Số lượng sản phẩm phải lớn hơn 0'], 400);
            return;
        }

        $product = $this->productModel->getProductById($productId);
        if (!$product) {
            $this->json(['status' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
            return;
        }

        if (!isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] = [
                'name' => $product['name'],
                'price' => (float)$product['price'],
                'image' => $product['image'],
                'quantity' => 0
            ];
        }
        $_SESSION['cart'][$productId]['quantity'] += $quantity;

        $this->json([
            'status' => true,
            'message' => 'Đã thêm sản phẩm vào giỏ hàng bằng API',
            'data' => $this->cartData()
        ], 201);
    }

    // PUT /api/cart/update/{product_id}
    public function update($id) {
        if (!$this->requireMethod('PUT')) return;
        $productId = (int)$id;
        $data = $this->body();
        $quantity = (int)($data['quantity'] ?? 0);

        if (!isset($_SESSION['cart'][$productId])) {
            $this->json(['status' => false, 'message' => 'Sản phẩm chưa có trong giỏ hàng'], 404);
            return;
        }
        if ($quantity <= 0) {
            $this->json(['status' => false, 'message' => 'Số lượng sản phẩm phải lớn hơn 0'], 400);
            return;
        }

        $_SESSION['cart'][$productId]['quantity'] = $quantity;
        $this->json([
            'status' => true,
            'message' => 'Cập nhật giỏ hàng bằng API thành công',
            'data' => $this->cartData()
        ]);
    }

    // DELETE /api/cart/delete/{product_id}
    public function delete($id) {
        if (!$this->requireMethod('DELETE')) return;
        $productId = (int)$id;
        unset($_SESSION['cart'][$productId]);
        $this->json([
            'status' => true,
            'message' => 'Đã xóa sản phẩm khỏi giỏ hàng bằng API',
            'data' => $this->cartData()
        ]);
    }

    // DELETE /api/cart/clear
    public function clear() {
        if (!$this->requireMethod('DELETE')) return;
        $_SESSION['cart'] = [];
        $this->json(['status' => true, 'message' => 'Đã xóa toàn bộ giỏ hàng bằng API', 'data' => $this->cartData()]);
    }
}
