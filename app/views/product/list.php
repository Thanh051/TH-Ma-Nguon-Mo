<?php require_once 'app/views/shares/header.php'; ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
    /* Giữ nguyên toàn bộ khối CSS custom cao cấp cũ của bạn */
    .category-sidebar { border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); background: #fff; padding: 8px 0; border: 1px solid #eaeaea; }
    .category-title-box { padding: 14px 20px; font-weight: 700; color: #333; border-bottom: 1px solid #f5f5f5; font-size: 15px; text-transform: uppercase; }
    .category-item { display: flex; align-items: center; justify-content: space-between; padding: 12px 20px; color: #333; text-decoration: none; font-weight: 500; font-size: 14px; transition: all 0.2s ease-in-out; border-left: 3px solid transparent; }
    .category-item:hover { background-color: #f9f9f9; padding-left: 24px; }
    .category-item.active { background-color: #ffebee; color: #d70018 !important; font-weight: 700; border-left-color: #d70018; }
    .category-left-group { display: flex; align-items: center; gap: 12px; }
    .category-icon { font-size: 20px; display: flex; align-items: center; color: #e63946; }
    .category-arrow { font-size: 14px; color: #bbb; transition: transform 0.2s; }
    .category-item:hover .category-arrow { color: #666; transform: translateX(3px); }
    .product-card { border: 1px solid #f0f0f0; border-radius: 8px; transition: all 0.3s; }
    .product-card:hover { box-shadow: 0 6px 18px rgba(0,0,0,0.08); transform: translateY(-3px); }
</style>

<div id="carouselExampleIndicators" class="carousel slide shadow rounded-3 overflow-hidden mb-4" data-bs-ride="carousel">
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
  </div>
  
  <div class="carousel-inner">
    <div class="carousel-item active" data-bs-interval="2000">
      <img src="https://images.unsplash.com/photo-1531297484001-80022131f5a1?q=80&w=1200&auto=format&fit=crop" class="d-block w-100" alt="Banner Laptop" style="height: 400px; object-fit: cover;">
    </div>
    <div class="carousel-item" data-bs-interval="2000">
      <img src="https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?q=80&w=1200&auto=format&fit=crop" class="d-block w-100" alt="Banner Điện thoại" style="height: 400px; object-fit: cover;">
    </div>
    <div class="carousel-item" data-bs-interval="2000">
      <img src="https://images.unsplash.com/photo-1542751371-adc38448a05e?q=80&w=1200&auto=format&fit=crop" class="d-block w-100" alt="Banner Linh kiện PC" style="height: 400px; object-fit: cover;">
    </div>
  </div>
  
  <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  
  <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
  </button>
</div>

<div class="row">
    <div class="col-lg-3 mb-4">
        <div class="category-sidebar">
            <div class="category-title-box">
                <i class="bi bi-list-ul me-2 text-primary"></i> Danh mục sản phẩm
            </div>
            
            <?php 
                // Nhận ID danh mục đang chọn để Active đổi màu
                $current_cat = $_GET['category_id'] ?? ''; 
            ?>
            
            <a href="/index.php?url=product/index" class="category-item <?= empty($current_cat) ? 'active' : '' ?>">
                <div class="category-left-group">
                    <div class="category-icon"><i class="bi bi-grid-fill text-primary"></i></div>
                    <span>Tất cả sản phẩm</span>
                </div>
                <i class="bi bi-chevron-right category-arrow"></i>
            </a>

            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $cat): 
                    $catName = mb_strtolower($cat['name'], 'UTF-8');
                    $iconClass = "bi bi-box-seam"; 
                    
                    if (str_contains($catName, 'điện thoại') || str_contains($catName, 'phone')) { $iconClass = "bi bi-phone"; }
                    elseif (str_contains($catName, 'laptop') || str_contains($catName, 'máy tính')) { $iconClass = "bi bi-laptop"; }
                    elseif (str_contains($catName, 'âm thanh') || str_contains($catName, 'tai nghe')) { $iconClass = "bi bi-headphones"; }
                    elseif (str_contains($catName, 'linh kiện') || str_contains($catName, 'phụ kiện')) { $iconClass = "bi bi-usb-plug"; }
                ?>
                    <a href="/index.php?url=product/index&category_id=<?= $cat['id'] ?>" class="category-item <?= $current_cat == $cat['id'] ? 'active' : '' ?>">
                        <div class="category-left-group">
                            <div class="category-icon"><i class="<?= $iconClass ?>"></i></div>
                            <span><?= htmlspecialchars($cat['name']) ?></span>
                        </div>
                        <i class="bi bi-chevron-right category-arrow"></i>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-9">
        <h3 class="mb-4 text-primary fw-bold text-center text-md-start">🚀 SẢN PHẨM NỔI BẬT</h3>
        
        <?php if (empty($products)): ?>
            <div class="alert alert-warning text-center py-5 rounded-3 shadow-sm" role="alert">
                <p class="mb-0 fw-bold text-secondary fs-6">Chưa có sản phẩm nào thuộc phân loại này!</p>
                <a href="/index.php?url=product/index" class="btn btn-sm btn-blue-tgdd mt-3 fw-bold px-3 py-2">Quay lại xem tất cả</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($products as $p): ?>
                    <div class="col-sm-6 col-md-6 col-lg-4 mb-4">
                        <div class="card product-card h-100 d-flex flex-column shadow-sm border-0 rounded-3 overflow-hidden">
                            
                            <div class="p-3 text-center bg-light" style="height: 200px; display: flex; align-items: center; justify-content: center;">
                                <?php $imagePath = !empty($p['image']) ? $p['image'] : 'default.jpg'; ?>
                                <img src="public/images/<?= $imagePath ?>" class="img-fluid" style="max-height: 100%; object-fit: contain;" alt="<?= htmlspecialchars($p['name']) ?>" onerror="this.src='public/images/default.jpg';">
                            </div>
                            
                            <div class="card-body d-flex flex-column p-3">
                                <h5 class="card-title fw-bold text-dark fs-6 text-truncate-2" style="min-height: 40px;">
                                    <?= htmlspecialchars($p['name']) ?>
                                </h5>
                                <p class="text-danger fw-bold fs-5 mb-1"><?= number_format($p['price']) ?> đ</p>
                                <p class="card-text text-muted small mb-3 text-truncate"><?= htmlspecialchars($p['description']) ?></p>
                                
                                <div class="d-flex gap-2 mt-auto">
                                    <a href="/index.php?url=product/detail/<?= $p['id'] ?>" class="btn btn-outline-primary btn-sm w-50 fw-bold py-2 rounded-2">Chi Tiết</a>
                                    <a href="/index.php?url=product/addToCart/<?= $p['id'] ?>" class="btn btn-blue-tgdd btn-sm w-50 fw-bold py-2 rounded-2">Mua Ngay</a>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'app/views/shares/footer.php'; ?>