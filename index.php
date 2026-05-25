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
$url = isset($_GET['url']) ? explode('/', rtrim($_GET['url'], '/')) : ['default'];

$controllerName = ucfirst($url[0]) . 'Controller';
$action = isset($url[1]) ? $url[1] : 'index';

// Đường dẫn tới file Controller
$controllerFile = "app/controllers/" . $controllerName . ".php";

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
        echo "<h3>404 - Hành động '$action' không tồn tại trong $controllerName!</h3>";
    }
} else {
    // Nếu không tìm thấy controller, chạy mặc định (Trang chủ bán hàng)
    require_once "app/controllers/ProductController.php";
    $controller = new ProductController();
    $controller->index();
}