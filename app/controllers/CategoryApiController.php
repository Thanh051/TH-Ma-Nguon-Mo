<?php
require_once 'app/controllers/BaseApiController.php';
require_once 'app/models/CategoryModel.php';

class CategoryApiController extends BaseApiController {
    private $categoryModel;

    public function __construct() {
        $this->categoryModel = new CategoryModel();
    }

    // GET /api/category
    public function index() {
        if (!$this->requireMethod('GET')) return;
        $this->json([
            'status'  => true,
            'message' => 'Lấy danh sách danh mục thành công',
            'data'    => $this->categoryModel->getAllCategories()
        ]);
    }

    // GET /api/category/detail/{id}
    public function detail($id) {
        if (!$this->requireMethod('GET')) return;
        $cat = $this->categoryModel->getCategoryById($id);
        if (!$cat) {
            $this->json(['status' => false, 'message' => 'Danh mục không tồn tại'], 404);
            return;
        }
        $this->json(['status' => true, 'data' => $cat]);
    }

    // POST /api/category/create
    public function create() {
        if (!$this->requireMethod('POST')) return;
        $data = $this->body();
        $name = trim($data['name'] ?? '');
        if ($name === '') {
            $this->json(['status' => false, 'message' => 'Tên danh mục không được rỗng'], 400);
            return;
        }
        $ok = $this->categoryModel->create($name);
        $this->json([
            'status'  => $ok,
            'message' => $ok ? 'Thêm danh mục thành công' : 'Không thể thêm danh mục'
        ], $ok ? 201 : 500);
    }

    // PUT /api/category/update/{id}
    public function update($id) {
        if (!$this->requireMethod('PUT')) return;
        if (!$this->categoryModel->getCategoryById($id)) {
            $this->json(['status' => false, 'message' => 'Danh mục không tồn tại'], 404);
            return;
        }
        $data = $this->body();
        $name = trim($data['name'] ?? '');
        if ($name === '') {
            $this->json(['status' => false, 'message' => 'Tên danh mục không được rỗng'], 400);
            return;
        }
        $ok = $this->categoryModel->update($id, $name);
        $this->json([
            'status'  => $ok,
            'message' => $ok ? 'Cập nhật danh mục thành công' : 'Không thể cập nhật danh mục'
        ]);
    }

    // DELETE /api/category/delete/{id}
    public function delete($id) {
        if (!$this->requireMethod('DELETE')) return;
        if (!$this->categoryModel->getCategoryById($id)) {
            $this->json(['status' => false, 'message' => 'Danh mục không tồn tại'], 404);
            return;
        }
        if ($this->categoryModel->countProducts($id) > 0) {
            $this->json(['status' => false, 'message' => 'Không thể xóa danh mục vì vẫn còn sản phẩm'], 400);
            return;
        }
        $ok = $this->categoryModel->delete($id);
        $this->json([
            'status'  => $ok,
            'message' => $ok ? 'Xóa danh mục thành công' : 'Không thể xóa danh mục'
        ]);
    }
}
