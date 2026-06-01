<?php
require_once 'app/models/ProductModel.php';
require_once 'app/models/CategoryModel.php';

class ProductController {
    private $productModel;
    private $categoryModel;

    public function __construct() {
        // Đảm bảo Session luôn được bật để kiểm tra quyền đăng nhập
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
    }

    // --- HÀM BẢO MẬT: CHẶN TÀI KHOẢN THƯỜNG TRUY CẬP VÀO CRUD ---
    private function checkAdmin() {
        // Nếu chưa đăng nhập HOẶC tài khoản đăng nhập không có vai trò 'admin'
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            die("<div style='text-align:center; margin-top:80px; font-family:sans-serif;'>
                    <h1 style='color:#d9534f; font-size: 50px; margin-bottom:10px;'>🚫 TRUY CẬP BỊ TỪ CHỐI</h1>
                    <h3 style='color:#333;'>Bạn không có quyền quản trị để thực hiện chức năng này!</h3>
                    <p style='color:#666;'>Vui lòng đăng nhập bằng tài khoản Admin.</p>
                    <a href='/index.php' style='display:inline-block; margin-top:15px; padding:10px 20px; background:#0275d8; color:#fff; text-decoration:none; border-radius:5px; font-weight:bold;'>Quay lại Trang Chủ</a>
                 </div>");
        }
    }

    // 1. TRANG CHỦ BÁN HÀNG (Hiển thị sản phẩm cho khách mua)
    public function index() {
        // Lấy category_id từ URL xuống (nếu có)
        $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;

        // Truyền biến $category_id vào hàm để Model biết đường mà lọc dữ liệu
        $products = $this->productModel->getAllProducts($category_id);
        
        // Lấy danh sách danh mục để hiển thị thanh menu bên trái
        $categories = $this->categoryModel->getAllCategories();
        
        // Nạp file giao diện trang chủ
        require_once 'app/views/product/list.php';
    }

    // 2. TRANG QUẢN TRỊ ADMIN (Hiển thị bảng danh sách CRUD)
    public function admin() {
        $this->checkAdmin(); // CHẶN QUYỀN USER
        $products = $this->productModel->getAllProducts();
        require_once 'app/views/product/show.php';
    }
    
    // 3. XỬ LÝ THÊM SẢN PHẨM MỚI (CÓ UPLOAD ẢNH)
    public function add() {
        $this->checkAdmin(); // CHẶN QUYỀN USER
        $categories = $this->categoryModel->getAllCategories();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $price = $_POST['price'] ?? 0;
            $description = $_POST['description'] ?? '';
            $category_id = $_POST['category_id'] ?? null;
            
            $imageName = 'default.jpg'; 

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $targetDir = "public/images/";
                
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $fileName = time() . '_' . basename($_FILES["image"]["name"]);
                $targetFilePath = $targetDir . $fileName;
                $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

                $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp');
                if (in_array(strtolower($fileType), $allowTypes)) {
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                        $imageName = $fileName; 
                    }
                }
            }
            
            $this->productModel->create($name, $price, $imageName, $description, $category_id);
            header('Location: /index.php?url=product/admin');
            exit;
        }
        require_once 'app/views/product/add.php';
    }

    // 4. XỬ LÝ SỬA SẢN PHẨM (CÓ UPLOAD ẢNH)
    public function edit($id) {
        $this->checkAdmin(); // CHẶN QUYỀN USER
        $product = $this->productModel->getProductById($id);
        $categories = $this->categoryModel->getAllCategories();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $price = $_POST['price'] ?? 0;
            $description = $_POST['description'] ?? '';
            $category_id = $_POST['category_id'] ?? null;
            
            $imageName = $_POST['existing_image'] ?? 'default.jpg'; 

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $targetDir = "public/images/";
                $fileName = time() . '_' . basename($_FILES["image"]["name"]);
                $targetFilePath = $targetDir . $fileName;
                $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

                $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp');
                if (in_array(strtolower($fileType), $allowTypes)) {
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                        $imageName = $fileName; 
                    }
                }
            }

            $this->productModel->update($id, $name, $price, $imageName, $description, $category_id);
            header('Location: /index.php?url=product/admin');
            exit;
        }
        require_once 'app/views/product/edit.php';
    }

    // 5. XỬ LÝ XÓA SẢN PHẨM
    public function delete($id) {
        $this->checkAdmin(); // CHẶN QUYỀN USER
        $this->productModel->delete($id);
        header('Location: /index.php?url=product/admin');
        exit;
    }

    // KHÁCH HÀNG: XEM CHI TIẾT SẢN PHẨM
    public function detail($id) {
        $product = $this->productModel->getProductById($id);
        if (!$product) {
            header('Location: /index.php');
            exit;
        }
        require_once 'app/views/product/detail.php';
    }

    // KHÁCH HÀNG: TRANG GIỎ HÀNG 
    public function cart() {
        if (isset($_SESSION['user'])) {
            // Đã đăng nhập: Lấy giỏ hàng trực tiếp từ DATABASE
            $current_cart = $this->productModel->getCartByUserId($_SESSION['user']['id']);
        } else {
            // Chưa đăng nhập: Trả về mảng rỗng (hoặc có thể đá sang trang login tùy bạn)
            $current_cart = [];
        }
        require_once 'app/views/product/cart.php';
    }

    // KHÁCH HÀNG: THÊM VÀO GIỎ HÀNG
    public function addToCart($id) {
        // Chặn nếu chưa đăng nhập
        if (!isset($_SESSION['user'])) {
            header('Location: /index.php?url=account/login');
            exit;
        }

        // Đã đăng nhập -> Lưu vào DATABASE
        $this->productModel->addToCartDB($_SESSION['user']['id'], $id, 1);
        header('Location: /index.php?url=product/cart');
        exit;
    }

    // KHÁCH HÀNG: CẬP NHẬT SỐ LƯỢNG TRONG GIỎ HÀNG
    public function updateCart() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'] ?? null;
            $action = $_POST['action'] ?? '';

            // Chỉ xử lý khi đã đăng nhập tài khoản
            if ($id && isset($_SESSION['user'])) {
                $this->productModel->updateCartDB($_SESSION['user']['id'], $id, $action);
            }
        }
        header('Location: /index.php?url=product/cart');
        exit;
    }

    // KHÁCH HÀNG: THANH TOÁN GIỎ HÀNG
    public function checkout() {
        if (!isset($_SESSION['user'])) {
            header('Location: /index.php?url=account/login');
            exit;
        }

        $current_cart = $this->productModel->getCartByUserId($_SESSION['user']['id']);

        if (empty($current_cart)) {
            header('Location: /index.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $total = 0;
            foreach ($current_cart as $item) { 
                $total += $item['price'] * $item['qty']; 
            }
            
            $success = $this->productModel->saveOrder(
                $_POST['name'], 
                $_POST['email'], 
                $_POST['phone'], 
                $_POST['address'], 
                $total, 
                $current_cart 
            );
            
            if ($success) {
                // Xóa sạch giỏ hàng trong DATABASE sau khi mua xong
                $this->productModel->clearCartDB($_SESSION['user']['id']);
                header('Location: /index.php?url=product/success');
                exit;
            }
        }
        require_once 'app/views/product/checkout.php';
    }
    
    // THÔNG BÁO ĐẶT HÀNG THÀNH CÔNG
    public function success() {
        require_once 'app/views/product/success.php';
    }
}