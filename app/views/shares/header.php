<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TTG Store - Điện thoại, Laptop chính hãng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
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
                <li class="nav-item">
                    <a class="nav-link text-white fw-bold px-3" href="/index.php?url=product/admin">Trang Quản Trị (CRUD)</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white-50 fw-bold px-3" href="/index.php?url=category/admin">📂 Quản lý Danh mục</a>
                </li>
                <li class="nav-item">
    <a class="nav-link" href="/index.php?url=product/history">
        <i class="bi bi-clock-history"></i> Lịch sử đơn hàng
    </a>
</li>
            </ul>
            
            <div class="d-flex align-items-center mt-2 mt-lg-0">
                <a href="/index.php?url=product/cart" class="btn btn-light text-primary fw-bold rounded-pill px-4 shadow-sm d-flex align-items-center gap-2">
                    <span>🛒 Giỏ hàng</span>
                    <span class="badge bg-danger rounded-pill">
                        <?= isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'qty')) : 0 ?>
                    </span>
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="container">