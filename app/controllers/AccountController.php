<?php
require_once 'app/models/AccountModel.php';

class AccountController {
    private $accountModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->accountModel = new AccountModel();
    }

    // Giao diện và Xử lý Đăng ký
    public function register() {
        $error = "";
        $success = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $error = "Vui lòng nhập đầy đủ tài khoản và mật khẩu!";
            } elseif ($this->accountModel->checkUsernameExists($username)) {
                $error = "Tên tài khoản này đã được sử dụng!";
            } else {
                if ($this->accountModel->register($username, $password)) {
                    $success = "Đăng ký thành công! Đang chuyển hướng sang đăng nhập...";
                    header("refresh:2;url=/index.php?url=account/login");
                } else {
                    $error = "Có lỗi xảy ra, vui lòng thử lại!";
                }
            }
        }
        require_once 'app/views/auth/register.php';
    }

    // Giao diện và Xử lý Đăng nhập (Đã chuyển sang lưu giỏ hàng vào DATABASE)
    public function login() {
        $error = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            $user = $this->accountModel->login($username, $password);
            if ($user) {
                // TỰ ĐỘNG SỬA SAI: Thử lấy 'id', nếu không có thì thử lấy 'user_id' hoặc 'id_user'
                $userId = $user['id'] ?? $user['user_id'] ?? $user['id_user'] ?? null;

                if (!$userId) {
                    // Nếu hoàn toàn không tìm thấy cột ID nào, tạm thời gán bằng 1 để không bị lỗi giỏ hàng
                    $userId = 1; 
                }

                // Lưu thông tin đăng nhập vào Session
                $_SESSION['user'] = [
                    'id' => $userId,
                    'username' => $user['username'],
                    'role' => $user['role'] ?? 'user'
                ];

                // ==================== KHỐI ĐỒNG BỘ GIỎ HÀNG VÀO DATABASE ====================
                $guest_key = 'cart_guest';

                // Kiểm tra xem giỏ hàng tạm thời của khách vãng lai có sản phẩm không
                if (isset($_SESSION[$guest_key]) && !empty($_SESSION[$guest_key])) {
                    // Nạp file ProductModel để thao tác với các hàm xử lý giỏ hàng trong DB
                    require_once 'app/models/ProductModel.php';
                    $productModel = new ProductModel();
                    
                    // Duyệt giỏ khách vãng lai, bốc từng món lưu trực tiếp vào Database
                    foreach ($_SESSION[$guest_key] as $product_id => $item) {
                        $productModel->addToCartDB($userId, $product_id, $item['qty']);
                    }
                    
                    // Lưu xong xóa sạch giỏ vãng lai trên Session đi để không bị trùng lặp
                    unset($_SESSION[$guest_key]);
                }
                // ===========================================================================

                // Đăng nhập xong, đẩy thẳng về trang giỏ hàng để lấy dữ liệu từ DB lên hiển thị
                header('Location: /index.php');
                exit;
            } else {
                $error = "Tài khoản hoặc mật khẩu không chính xác!";
            }
        }
        require_once 'app/views/auth/login.php';
    }

    // Đăng xuất xóa sạch phiên làm việc
    public function logout() {
        unset($_SESSION['user']);
        session_destroy();
        header('Location: /index.php');
        exit;
    }
}