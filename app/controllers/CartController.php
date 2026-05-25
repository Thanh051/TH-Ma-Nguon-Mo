<?php
// website/app/controllers/CartController.php

class CartController {
    
    // Phương thức thêm sản phẩm vào giỏ
    public function add() {
        // Lấy thông tin từ form ẩn khi người dùng bấm "Thêm vào giỏ"
        $productId = $_POST['product_id'];
        $productName = $_POST['product_name'];
        $price = $_POST['price'];
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

        // Khởi tạo giỏ hàng nếu chưa có
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Nếu sản phẩm đã có trong giỏ, tăng số lượng
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] += $quantity;
        } else {
            // Nếu chưa có, thêm mới vào Session
            $_SESSION['cart'][$productId] = [
                'name' => $productName,
                'price' => $price,
                'quantity' => $quantity
            ];
        }

        // Chuyển hướng người dùng về trang giỏ hàng
        header('Location: /cart');
        exit;
    }

    // Phương thức hiển thị trang giỏ hàng
    public function index() {
        $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
        
        // Gọi view hiển thị giỏ hàng và truyền biến $cart ra view
        require_once 'app/views/cart.php';
    }
}
?>