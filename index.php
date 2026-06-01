<?php
session_start();

// Bật hiển thị lỗi tối đa để nếu có lỗi cú pháp ở Model/Controller, PHP sẽ báo ngay lập tức
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Router tiếp nhận URL từ query string index.php?url=...
$rawUrl = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'product/index';

// FIX LỖI: Loại bỏ phần query string phía sau dấu & (nếu có) để không làm sai lệch Action
if (strpos($rawUrl, '&') !== false) {
    $rawUrl = explode('&', $rawUrl)[0];
}

$url = explode('/', $rawUrl);

// Xác định tên Controller và Action
$controllerName = ucfirst($url[0]) . 'Controller';
$action = isset($url[1]) && !empty($url[1]) ? $url[1] : 'index';

// Đường dẫn tới file Controller
$controllerFile = "app/controllers/" . $controllerName . ".php";

// KIỂM TRA: Nếu file Controller tồn tại thì mới xử lý tiếp
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $controller = new $controllerName();
    
    // Kiểm tra xem hàm (action) có tồn tại trong Controller không
    if (method_exists($controller, $action)) {
        
        // Lấy tham số ID từ URL (nếu có, ví dụ /product/edit/5 thì ID là 5)
        $id = isset($url[2]) ? $url[2] : null;
        
        if ($id !== null) {
            // Nếu có tham số ID, truyền trực tiếp biến $id vào hàm
            $controller->$action($id);
        } else {
            // Nếu không có tham số (như hàm add, index), gọi hàm bình thường
            $controller->$action();
        }
        
    } else {
        header("HTTP/1.0 404 Not Found");
        echo "<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>";
        echo "<h2 style='color:red;'>🚫 404 - KHÔNG TÌM THẤY HÀNH ĐỘNG</h2>";
        echo "<p>Hành động <strong>'$action'</strong> không tồn tại trong hệ thống quản lý của $controllerName!</p>";
        echo "<a href='/index.php'>Quay lại trang chủ</a>";
        echo "</div>";
    }
} else {
    // SỬA LỖI TẠI ĐÂY: Nếu người dùng không gõ tham số url (Trang chủ thực sự), nạp ProductController
    if (empty($_GET['url']) || $_GET['url'] === 'product/index') {
        require_once "app/controllers/ProductController.php";
        $controller = new ProductController();
        $controller->index();
    } else {
        // Nếu cố tình gõ bậy bạ một Controller không tồn tại (Ví dụ: url=hack_he_thong) -> Trả lỗi 404 ngay lập tức
        header("HTTP/1.0 404 Not Found");
        echo "<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>";
        echo "<h2 style='color:red;'>🚫 404 - TRANG KHÔNG TỒN TẠI</h2>";
        echo "<p>Đường dẫn trang quản trị hoặc chức năng bạn yêu cầu không tồn tại trên hệ thống.</p>";
        echo "<a href='/index.php'>Quay lại trang chủ</a>";
        echo "</div>";
    }
}