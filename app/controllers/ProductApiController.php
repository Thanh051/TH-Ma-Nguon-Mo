<?php
require_once 'app/controllers/BaseApiController.php';
require_once 'app/models/ProductModel.php';

class ProductApiController extends BaseApiController {
    private $productModel;
    private $uploadDir = 'public/images/';

    public function __construct() {
        $this->productModel = new ProductModel();
    }

    // GET /api/product?search=...&category_id=...&sort_price=asc|desc
    public function index() {
        if (!$this->requireMethod('GET')) return;
        $products = $this->productModel->getAllProducts([
            'search'      => $_GET['search']      ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'sort_price'  => $_GET['sort_price']  ?? '',
        ]);
        $this->json(['status' => true, 'message' => 'Lấy danh sách sản phẩm thành công', 'data' => $products]);
    }

    // GET /api/product/{id}
    public function detail($id) {
        if (!$this->requireMethod('GET')) return;
        $product = $this->productModel->getProductById($id);
        if (!$product) {
            $this->json(['status' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
            return;
        }
        $this->json(['status' => true, 'data' => $product]);
    }

    // POST /api/product/create
    public function create() {
        if (!$this->requireMethod('POST')) return;
        $data        = $this->body();
        $name        = trim($data['name'] ?? '');
        $price       = $data['price'] ?? 0;
        $description = trim($data['description'] ?? '');
        $category_id = $data['category_id'] ?? null;
        $image       = trim($data['image'] ?? '');

        if ($name === '') {
            $this->json(['status' => false, 'message' => 'Tên sản phẩm không được rỗng'], 400);
            return;
        }
        if (!is_numeric($price) || (float)$price <= 0) {
            $this->json(['status' => false, 'message' => 'Giá sản phẩm phải là số và lớn hơn 0'], 400);
            return;
        }
        if (!$this->productModel->categoryExists($category_id)) {
            $this->json(['status' => false, 'message' => 'Danh mục sản phẩm không hợp lệ'], 400);
            return;
        }

        $ok = $this->productModel->create($name, $price, $image ?: null, $description, $category_id);
        $this->json([
            'status'  => $ok,
            'message' => $ok ? 'Thêm sản phẩm thành công' : 'Không thể thêm sản phẩm'
        ], $ok ? 201 : 500);
    }

    // PUT /api/product/update/{id}
    public function update($id) {
        if (!$this->requireMethod('PUT')) return;
        $old = $this->productModel->getProductById($id);
        if (!$old) {
            $this->json(['status' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
            return;
        }
        $data        = $this->body();
        $name        = trim($data['name'] ?? '');
        $price       = $data['price'] ?? 0;
        $description = trim($data['description'] ?? '');
        $category_id = $data['category_id'] ?? null;
        $image       = array_key_exists('image', $data) ? trim((string)$data['image']) : ($old['image'] ?? null);

        if ($name === '') {
            $this->json(['status' => false, 'message' => 'Tên sản phẩm không được rỗng'], 400);
            return;
        }
        if (!is_numeric($price) || (float)$price <= 0) {
            $this->json(['status' => false, 'message' => 'Giá sản phẩm phải là số và lớn hơn 0'], 400);
            return;
        }
        if (!$this->productModel->categoryExists($category_id)) {
            $this->json(['status' => false, 'message' => 'Danh mục sản phẩm không hợp lệ'], 400);
            return;
        }

        $ok = $this->productModel->update($id, $name, $price, $image ?: null, $description, $category_id);
        $this->json([
            'status'  => $ok,
            'message' => $ok ? 'Cập nhật sản phẩm thành công' : 'Không thể cập nhật sản phẩm'
        ]);
    }

    // DELETE /api/product/delete/{id}
    public function delete($id) {
        if (!$this->requireMethod('DELETE')) return;
        $old = $this->productModel->getProductById($id);
        if (!$old) {
            $this->json(['status' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
            return;
        }
        $ok = $this->productModel->delete($id);
        $this->json([
            'status'  => $ok,
            'message' => $ok ? 'Xóa sản phẩm thành công' : 'Không thể xóa sản phẩm'
        ]);
    }

    // POST /api/product/uploadImage  – upload ảnh từ máy (multipart/form-data)
    public function uploadImage() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['status' => false, 'message' => 'Method không hợp lệ'], 405); return;
        }
        if (empty($_FILES['image'])) {
            $this->json(['status' => false, 'message' => 'Không có file ảnh được gửi lên'], 400); return;
        }

        $file     = $_FILES['image'];
        $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize  = 5 * 1024 * 1024; // 5MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->json(['status' => false, 'message' => 'Lỗi upload: code ' . $file['error']], 400); return;
        }
        if (!in_array($file['type'], $allowed)) {
            $this->json(['status' => false, 'message' => 'Chỉ chấp nhận file JPG, PNG, GIF, WEBP'], 400); return;
        }
        if ($file['size'] > $maxSize) {
            $this->json(['status' => false, 'message' => 'File quá lớn (tối đa 5MB)'], 400); return;
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = time() . '_' . uniqid() . '.' . strtolower($ext);
        $dest     = $this->uploadDir . $filename;

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $this->json(['status' => false, 'message' => 'Không thể lưu file ảnh'], 500); return;
        }

        $this->json([
            'status'   => true,
            'message'  => 'Upload ảnh thành công',
            'filename' => $filename,
            'url'      => $dest,
        ]);
    }
}
