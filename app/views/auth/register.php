<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Ký Tài Khoản</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card shadow border-0 p-4" style="width: 100%; max-width: 400px; border-radius: 12px;">
        <h3 class="text-center fw-bold text-success mb-3">ĐĂNG KÝ</h3>
        
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger py-2 small"><?= $error ?></div>
        <?php endif; ?>
        <?php if(!empty($success)): ?>
            <div class="alert alert-success py-2 small"><?= $success ?></div>
        <?php endif; ?>

        <form action="/index.php?url=account/register" method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold small">Tên đăng nhập mẫu</label>
                <input type="text" name="username" class="form-control" placeholder="Tạo tài khoản mới..." required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-bold small">Mật khẩu</label>
                <input type="password" name="password" class="form-control" placeholder="Tạo mật khẩu..." required>
            </div>
            <button type="submit" class="btn btn-success w-100 fw-bold py-2 mb-3">Đăng Ký</button>
            <div class="text-center small">
                Đã có tài khoản? <a href="/index.php?url=account/login" class="text-decoration-none text-success">Đăng nhập</a>
            </div>
        </form>
    </div>
</body>
</html>