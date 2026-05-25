<?php
// website/app/controllers/CheckoutController.php

require_once 'app/models/OrderModel.php';

class CheckoutController {
    
    public function process() {
        // Kiểm tra xem giỏ hàng có trống không
        if (empty($_SESSION['cart'])) {
            header('Location: /cart');
            exit;
        }

        // Nhận dữ liệu từ form thanh toán
        $name = $_POST['customer_name'];
        $phone = $_POST['customer_phone'];
        $address = $_POST['customer_address'];
        
        // Tính tổng tiền
        $totalAmount = 0;
        foreach ($_SESSION['cart'] as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
        }

        // Gọi Model để lưu vào Database
        $orderModel = new OrderModel();
        $orderId = $orderModel->createOrder($name, $phone, $address, $totalAmount);

        if ($orderId) {
            // Lưu chi tiết đơn hàng
            foreach ($_SESSION['cart'] as $productId => $item) {
                $orderModel->createOrderDetail($orderId, $productId, $item['quantity'], $item['price']);
            }

            // Xóa giỏ hàng sau khi đặt hàng thành công
            unset($_SESSION['cart']);

            // Chuyển hướng đến trang thành công
            echo "Đặt hàng thành công! Mã đơn hàng của bạn là: " . $orderId;
            // header('Location: /success');
        } else {
            echo "Có lỗi xảy ra trong quá trình đặt hàng.";
        }
    }
}
?>