<?php
require_once 'app/models/ProductModel.php';
require_once 'app/models/CategoryModel.php';

class ProductController {
    private $productModel;
    private $categoryModel;

    public function __construct() {
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
    }

    // 1. TRANG CHỦ BÁN HÀNG (Nhận diện chính xác category_id đi kèm Router)
    public function index() {
        // Lấy tất cả danh mục từ Database thông qua CategoryModel
        $categories = $this->categoryModel->getAllCategories();

        // Đọc mã category_id từ thanh địa chỉ URL (?category_id=...)
        $categoryId = $_GET['category_id'] ?? null;

        if (!empty($categoryId)) {
            // Nếu có bấm chọn danh mục -> Tiến hành lọc theo câu lệnh WHERE SQL
            $products = $this->productModel->getProductsByCategory($categoryId);
        } else {
            // Không bấm chọn hoặc bấm "Tất cả sản phẩm" -> Gọi hàm lấy tất cả như cũ
            $products = $this->productModel->getAllProducts();
        }

        require_once 'app/views/product/list.php';
    }

    // 2. TRANG QUẢN TRỊ ADMIN (Hiển thị bảng danh sách CRUD)
    public function admin() {
        $products = $this->productModel->getAllProducts();
        require_once 'app/views/product/show.php';
    }
    
    // 3. XỬ LÝ THÊM SẢN PHẨM MỚI (CÓ UPLOAD ẢNH)
    public function add() {
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
        $this->productModel->delete($id);
        header('Location: /index.php?url=product/admin');
        exit;
    } // ĐÃ SỬA LỖI: Gom các hàm dưới đây vào lại bên trong Class một cách chính xác

    // KHÁCH HÀNG: XEM CHI TIẾT SẢN PHẨM
    public function detail($id) {
        $product = $this->productModel->getProductById($id);
        if (!$product) {
            header('Location: /index.php');
            exit;
        }
        require_once 'app/views/product/detail.php';
    }

    // KHÁCH HÀNG: TRANG GIỎ HÀNG RIÊNG
    public function cart() {
        require_once 'app/views/product/cart.php';
    }

    // KHÁCH HÀNG: CẬP NHẬT SỐ LƯỢNG TRONG GIỎ HÀNG (Tăng / Giảm / Xóa)
    public function updateCart() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'] ?? null;
            $action = $_POST['action'] ?? '';

            if ($id && isset($_SESSION['cart'][$id])) {
                if ($action === 'increase') {
                    $_SESSION['cart'][$id]['qty']++;
                } elseif ($action === 'decrease') {
                    $_SESSION['cart'][$id]['qty']--;
                    if ($_SESSION['cart'][$id]['qty'] <= 0) {
                        unset($_SESSION['cart'][$id]);
                    }
                } elseif ($action === 'remove') {
                    unset($_SESSION['cart'][$id]);
                }
            }
        }
        header('Location: /index.php?url=product/cart');
        exit;
    }

    // KHÁCH HÀNG: THÊM VÀO GIỎ HÀNG
    public function addToCart($id) {
        $product = $this->productModel->getProductById($id);
        if ($product) {
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]['qty']++;
            } else {
                $_SESSION['cart'][$id] = [
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'image' => $product['image'],
                    'qty' => 1
                ];
            }
        }
        header('Location: /index.php?url=product/cart');
        exit;
    }

    // KHÁCH HÀNG: TRANG THANH TOÁN RIÊNG
    public function checkout() {
        if (empty($_SESSION['cart'])) {
            header('Location: /index.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $total = 0;
            foreach($_SESSION['cart'] as $item) { 
                $total += $item['price'] * $item['qty']; 
            }
            
            $success = $this->productModel->saveOrder(
                $_POST['name'], 
                $_POST['email'], 
                $_POST['phone'], 
                $_POST['address'], 
                $total, 
                $_SESSION['cart']
            );
            
            if ($success) {
                $_SESSION['cart'] = []; 
                header('Location: /index.php?url=product/success');
                exit;
            }
        }
        require_once 'app/views/product/checkout.php';
    }

    // KHÁCH HÀNG: TRANG THÀNH CÔNG
    public function success() {
        require_once 'app/views/product/success.php';
    }

    public function history() {
        $orders = $this->productModel->getAllOrders();
        require_once 'app/views/product/history.php';
    }
}