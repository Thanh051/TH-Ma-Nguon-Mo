<?php include 'app/views/shares/header.php'; ?>

<div class="container mt-4">
    <h1 class="mb-4">Danh sách sản phẩm</h1>
    <a href="/Product/add" class="btn btn-success mb-3">➕ Thêm sản phẩm mới</a>
    <div class="row">
        <?php foreach ($products as $product): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <?php if ($product->image): ?>
                <img src="/<?php echo $product->image; ?>" class="card-img-top" alt="Product Image">
                <?php else: ?>
                <img src="https://via.placeholder.com/150" class="card-img-top" alt="No Image">
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title">
                        <a href="/Product/show/<?php echo $product->id; ?>" class="text-decoration-none">
                            <?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </h5>
                    <p class="card-text text-muted">
                        <?php echo htmlspecialchars($product->description, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    <p><strong>Giá:</strong> <?php echo htmlspecialchars($product->price, ENT_QUOTES, 'UTF-8'); ?> VND</p>
                    <p><strong>Danh mục:</strong> <?php echo htmlspecialchars($product->category_name, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="/Product/edit/<?php echo $product->id; ?>" class="btn btn-warning btn-sm">✏️ Sửa</a>
                    <a href="/Product/delete/<?php echo $product->id; ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">🗑️ Xóa</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
