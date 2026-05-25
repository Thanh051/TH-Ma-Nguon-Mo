<?php require_once 'app/views/shares/header.php'; ?>

<div class="card border-0 shadow-sm p-4 rounded-3 bg-white mt-3">
    <div class="row">
        <div class="col-md-5 text-center mb-4 mb-md-0 bg-light rounded-3 p-4 d-flex align-items-center justify-content-center" style="min-height: 350px;">
            <img src="public/images/<?= !empty($product['image']) ? $product['image'] : 'default.jpg' ?>" 
                 class="img-fluid rounded" style="max-height: 350px; object-fit: contain;" alt="<?= htmlspecialchars($product['name']) ?>"
                 onerror="this.src='public/images/default.jpg';">
        </div>
        
        <div class="col-md-7 ps-md-4 d-flex flex-column">
            <h2 class="fw-bold text-dark mb-2"><?= htmlspecialchars($product['name']) ?></h2>
            <hr class="text-muted">
            <h3 class="text-danger fw-bold mb-3"><?= number_format($product['price']) ?> đ</h3>
            
            <div class="mb-4 bg-light p-3 rounded">
                <h6 class="fw-bold text-secondary mb-2">⚙️ Thông số kỹ thuật / Mô tả sản phẩm:</h6>
                <p class="text-dark mb-0" style="white-space: pre-line; line-height: 1.6;">
                    <?= htmlspecialchars($product['description']) ?>
                </p>
            </div>
            
            <div class="mt-auto d-flex gap-2">
                <a href="/index.php" class="btn btn-secondary px-4 py-2.5 fw-bold">⬅ Quay lại trang chủ</a>
                <a href="/index.php?url=product/addToCart/<?= $product['id'] ?>" class="btn btn-blue-tgdd px-5 py-2.5 fw-bold fs-5 shadow-sm">Thêm Vào Giỏ Hàng</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/shares/footer.php'; ?>