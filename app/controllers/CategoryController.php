<?php
require_once 'app/models/CategoryModel.php';

class CategoryController {
    private $categoryModel;

    public function __construct() {
        $this->categoryModel = new CategoryModel();
    }

    // Hiển thị danh sách danh mục trong Admin
    public function admin() {
        $categories = $this->categoryModel->getAllCategories();
        require_once 'app/views/category/show.php';
    }

    // Thêm danh mục mới
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $this->categoryModel->create($name);
            header('Location: /index.php?url=category/admin');
            exit;
        }
        require_once 'app/views/category/add.php';
    }

    // Sửa danh mục
    public function edit($id) {
        $category = $this->categoryModel->getCategoryById($id);
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $this->categoryModel->update($id, $name);
            header('Location: /index.php?url=category/admin');
            exit;
        }
        require_once 'app/views/category/edit.php';
    }

    // Xóa danh mục
    public function delete($id) {
        $this->categoryModel->delete($id);
        header('Location: /index.php?url=category/admin');
        exit;
    }
}