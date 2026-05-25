<?php
require_once 'app/models/ProductModel.php';

class DefaultController {
    private $productModel;

    public function __construct() {
        $this->productModel = new ProductModel();
    }

    public function index() {
        $products = $this->productModel->getAllProducts();
        require_once 'app/views/product/list.php'; // Hiển thị danh sách sản phẩm ở trang chủ
    }
}