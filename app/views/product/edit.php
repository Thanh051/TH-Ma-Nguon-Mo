<!DOCTYPE html>
<html>
<head>
    <title>Sửa sản phẩm</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h1 class="mb-4">Sửa sản phẩm</h1>
    <form method="POST" action="/project1/Product/edit/<?php echo $product->getID(); ?>" enctype="multipart/form-data" class="card p-4 shadow">
        <div class="mb-3">
            <label class="form-label">Tên sản phẩm</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product->getName(), ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Mô tả</label>
            <textarea name="description" class="form-control" required><?php echo htmlspecialchars($product->getDescription(), ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Giá</label>
            <input type="number" name="price" class="form-control" value="<?php echo htmlspecialchars($product->getPrice(), ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Hình ảnh hiện tại</label><br>
            <img src="<?php echo $product->getImage() ?: 'https://via.placeholder.com/150'; ?>" width="150" class="img-thumbnail">
        </div>
        <div class="mb-3">
            <label class="form-label">Chọn ảnh mới</label>
            <input type="file" name="image" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">💾 Lưu thay đổi</button>
        <a href="/project1/Product/list" class="btn btn-secondary">Quay lại</a>
    </form>
</body>
</html>
