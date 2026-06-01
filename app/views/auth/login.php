<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Nhập Hệ Thống</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card shadow border-0 p-4" style="width: 100%; max-width: 400px; border-radius: 12px;">
        <h3 class="text-center fw-bold text-primary mb-3">ĐĂNG NHẬP</h3>
        
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger py-2 small"><?= $error ?></div>
        <?php endif; ?>

        <form action="/index.php?url=account/login" method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold small">Tên đăng nhập</label>
                <input type="text" name="username" class="form-control" placeholder="Nhập tài khoản..." required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-bold small">Mật khẩu</label>
                <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu..." required>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mb-3">Đăng Nhập</button>
            <div class="text-center small">
                Chưa có tài khoản? <a href="/index.php?url=account/register" class="text-decoration-none">Đăng ký ngay</a>
            </div>
        </form>
    </div>
</body>
</html>