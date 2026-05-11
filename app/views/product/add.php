<!DOCTYPE html>
<html>
<head>
    <title>Thêm sản phẩm</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h1 class="mb-4">Thêm sản phẩm mới</h1>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/project1/Product/add" enctype="multipart/form-data" class="card p-4 shadow">
        <div class="mb-3">
            <label for="name" class="form-label">Tên sản phẩm</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Mô tả</label>
            <textarea id="description" name="description" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label for="price" class="form-label">Giá</label>
            <input type="number" id="price" name="price" step="0.01" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Hình ảnh sản phẩm</label>
            <input type="file" id="image" name="image" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">➕ Thêm sản phẩm</button>
        <a href="/project1/Product/list" class="btn btn-secondary">Quay lại</a>
    </form>
</body>
</html>
