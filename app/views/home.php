<?php $pageTitle = 'Tổng quan'; require __DIR__ . '/shares/header.php'; ?>

<?php
$successMessages = [
    '1' => '✓ Thêm thành công!',
    '2' => '✓ Cập nhật thành công!',
    '3' => '✓ Đã xóa.',
];
if (isset($_GET['success']) && isset($successMessages[$_GET['success']])): ?>
    <div class="alert alert-success"><?= $successMessages[$_GET['success']] ?></div>
<?php endif; ?>

<div class="hero-banner">
    <div class="banner-content">
        <h1>Chào mừng đến với cửa hàng của chúng tôi!</h1>
        <p>Khám phá những sản phẩm tuyệt vời nhất với giá ưu đãi lớn trong ngày hôm nay.</p>
        <a href="/products" class="btn-shop-now">Mua sắm ngay</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Tổng sản phẩm</div>
        <div class="stat-value"><?= number_format($stats['total_products']) ?></div>
        <div class="stat-icon">◉</div>
    </div>
    <div class="stat-card accent">
        <div class="stat-label">Danh mục</div>
        <div class="stat-value"><?= number_format($stats['total_categories']) ?></div>
        <div class="stat-icon">◧</div>
    </div>
    <div class="stat-card dark">
        <div class="stat-label">Tổng giá trị kho</div>
        <div class="stat-value"><?= number_format($stats['total_value'], 0, ',', '.') ?>₫</div>
        <div class="stat-icon">◈</div>
    </div>
    <div class="stat-card warn">
        <div class="stat-label">Sắp hết hàng (&lt;5)</div>
        <div class="stat-value"><?= number_format($stats['low_stock']) ?></div>
        <div class="stat-icon">⚠</div>
    </div>
</div>

<div class="section-header">
    <h2>Sản phẩm mới nhất</h2>
    <a href="?controller=product&action=add" class="btn btn-primary">+ Thêm sản phẩm</a>
</div>

<div class="products-grid">
<?php foreach ($latest as $p): ?>
    <div class="product-card">
        <div class="product-img">
            <?php if ($p['image']): ?>
                <img src="public/uploads/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
            <?php else: ?>
                <div class="img-placeholder">◉</div>
            <?php endif; ?>
        </div>
        <div class="product-info">
            <span class="product-cat"><?= htmlspecialchars($p['category_name'] ?? 'Chưa phân loại') ?></span>
            <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>
            <div class="product-meta">
                <span class="product-price"><?= number_format($p['price'], 0, ',', '.') ?>₫</span>
                <span class="product-stock <?= $p['stock'] < 5 ? 'low' : '' ?>">
                    <?= $p['stock'] ?> còn
                </span>
            </div>
        </div>
        <div class="product-actions">
            <a href="?controller=product&action=show&id=<?= $p['id'] ?>" class="btn-sm">Xem</a>
            <a href="?controller=product&action=edit&id=<?= $p['id'] ?>" class="btn-sm">Sửa</a>
        </div>
    </div>
<?php endforeach; ?>
<?php if (empty($latest)): ?>
    <div class="empty-state">
        <div class="empty-icon">◎</div>
        <p>Chưa có sản phẩm nào. <a href="?controller=product&action=add">Thêm ngay</a></p>
    </div>
<?php endif; ?>
</div>

<?php require __DIR__ . '/shares/footer.php'; ?>
