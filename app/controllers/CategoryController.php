<?php
require_once 'app/models/CategoryModel.php';

class CategoryController {
    private $categoryModel;

    public function __construct() {
        // Đảm bảo Session luôn được bật để kiểm tra quyền
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->categoryModel = new CategoryModel();
    }

    // --- HÀM BẢO MẬT: CHẶN USER THƯỜNG TRUY CẬP VÀO QUẢN LÝ DANH MỤC ---
    private function checkAdmin() {
        // Nếu chưa đăng nhập HOẶC không phải admin -> Bẻ gãy tiến trình ngay lập tức
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            die("<div style='text-align:center; margin-top:80px; font-family:sans-serif;'>
                    <h1 style='color:#d9534f; font-size: 50px; margin-bottom:10px;'>🚫 TRUY CẬP BỊ TỪ CHỐI</h1>
                    <h3 style='color:#333;'>Bạn không có quyền quản trị để cấu hình Danh Mục!</h3>
                    <p style='color:#666;'>Vui lòng đăng nhập bằng tài khoản Admin.</p>
                    <a href='/index.php' style='display:inline-block; margin-top:15px; padding:10px 20px; background:#0275d8; color:#fff; text-decoration:none; border-radius:5px; font-weight:bold;'>Quay lại Trang Chủ</a>
                 </div>");
        }
    }

    // Trang hiển thị danh sách danh mục (Admin)
    public function index() {
        $this->checkAdmin(); // CHẶN QUYỀN USER
        $categories = $this->categoryModel->getAllCategories();
        require_once 'app/views/category/show.php'; 
    }

    // Xử lý thêm danh mục mới
    public function create() {
        $this->checkAdmin(); // CHẶN QUYỀN USER
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            if (!empty($name)) {
                $this->categoryModel->create($name);
                header('Location: /index.php?url=category/index');
                exit;
            }
        }
        require_once 'app/views/category/create.php';
    }

    // Xử lý sửa danh mục
    public function edit($id) {
        $this->checkAdmin(); // CHẶN QUYỀN USER
        $category = $this->categoryModel->getCategoryById($id);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            if (!empty($name)) {
                $this->categoryModel->update($id, $name);
                header('Location: /index.php?url=category/index');
                exit;
            }
        }
        require_once 'app/views/category/edit.php';
    }

    // Xử lý xóa danh mục
    public function delete($id) {
        $this->checkAdmin(); // CHẶN QUYỀN USER
        $this->categoryModel->delete($id);
        header('Location: /index.php?url=category/index');
        exit;
    }
}