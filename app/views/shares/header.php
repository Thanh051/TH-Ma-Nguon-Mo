<?php
// Đảm bảo Session luôn được bật để nhận diện trạng thái đăng nhập tài khoản
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TTG Store - Điện thoại, Laptop chính hãng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        /* Tông màu xanh biển chủ đạo phong cách Thế Giới Di Động cải tiến */
        .bg-blue-tgdd { background-color: #0056b3 !important; }
        .btn-blue-tgdd { background-color: #007bff; color: white; transition: all 0.2s ease-in-out; }
        .btn-blue-tgdd:hover { background-color: #0056b3; color: white; transform: translateY(-1px); }
        
        /* Hiệu ứng hover nhẹ cho các mục menu */
        .navbar-nav .nav-link {
            transition: color 0.2s ease-in-out, opacity 0.2s ease-in-out;
        }
        .navbar-nav .nav-link:hover {
            color: #ffc107 !important; /* Đổi màu vàng nhẹ khi hover giống TGDD */
            opacity: 1 !important;
        }
        
        .product-card { border: 1px solid #e0e0e0; transition: all 0.3s ease-in-out; }
        .product-card:hover { box-shadow: 0px 8px 20px rgba(0,0,0,0.12); transform: translateY(-3px); }
        
        /* Style cho nút tài khoản user */
        .btn-auth-nav { font-weight: 600; border-radius: 20px; padding: 5px 15px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-blue-tgdd mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4 d-flex align-items-center gap-1 text-white" href="/index.php">
            📱 TTG STORE
        </a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link text-white fw-bold px-3" href="/index.php">Trang chủ</a>
                </li>

                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link text-warning fw-bold px-3" href="/index.php?url=product/admin"><i class="bi bi-shield-lock"></i> SP (CRUD)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-warning fw-bold px-3" href="/index.php?url=category/index">📂 DM (CRUD)</a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <div class="d-flex align-items-center mt-2 mt-lg-0 gap-3 flex-wrap">
                <a href="/index.php?url=product/cart" class="text-white position-relative fs-4 px-2 me-2" title="Giỏ hàng của bạn">
                    <i class="bi bi-cart3"></i>
                    <?php 
                        $cart_count = 0;
                        // ĐÃ FIX: Chỉ đếm số lượng khi người dùng đã đăng nhập và tồn tại giỏ hàng trong Database
                        if (isset($_SESSION['user'])) {
                            require_once 'app/models/ProductModel.php';
                            $pm = new ProductModel();
                            $dbCart = $pm->getCartByUserId($_SESSION['user']['id']);
                            
                            // Đếm tổng số lượng (qty) của toàn bộ sản phẩm đang có trong cơ sở dữ liệu
                            $cart_count = array_sum(array_column($dbCart, 'qty'));
                        } 
                        
                        if ($cart_count > 0): 
                    ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px; padding: 4px 6px;">
                            <?= $cart_count ?>
                        </span>
                    <?php endif; ?>
                </a>

                <?php if (isset($_SESSION['user'])): ?>
                    <div class="dropdown">
                        <button class="btn btn-warning dropdown-toggle fw-bold text-dark rounded-pill px-3" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['user']['username']) ?>
                            <span class="badge bg-dark text-white ms-1 small" style="font-size: 9px;"><?= strtoupper($_SESSION['user']['role']) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="userMenu">
                            <li><h6 class="dropdown-header">Xin chào, <?= htmlspecialchars($_SESSION['user']['username']) ?>!</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger fw-bold" href="/index.php?url=account/logout">
                                    <i class="bi bi-box-arrow-right"></i> Đăng xuất
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="/index.php?url=account/login" class="btn btn-outline-light btn-auth-nav text-white border-white-50">
                        <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                    </a>
                    <a href="/index.php?url=account/register" class="btn btn-warning btn-auth-nav text-dark border-0">
                        Đăng ký
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div class="container">