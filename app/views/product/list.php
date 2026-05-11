<!DOCTYPE html>
<html>
<head>
    <title>Danh sách sản phẩm</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h1 class="mb-4">Danh sách sản phẩm</h1>
    <a href="/project1/Product/add" class="btn btn-success mb-3">➕ Thêm sản phẩm mới</a>
    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-4">
                <div class="card mb-4 shadow-sm">
                    <img src="<?php echo $product->getImage() ?: 'https://via.placeholder.com/300x200'; ?>" class="card-img-top" alt="Product Image">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($product->getName(), ENT_QUOTES, 'UTF-8'); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($product->getDescription(), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-danger fw-bold"><?php echo number_format($product->getPrice(), 0, ',', '.'); ?> VND</p>
                        <a href="/project1/Product/edit/<?php echo $product->getID(); ?>" class="btn btn-primary">✏️ Sửa</a>
                        <a href="/project1/Product/delete/<?php echo $product->getID(); ?>" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">🗑️ Xóa</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
