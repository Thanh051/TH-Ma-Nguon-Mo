<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký Tài Khoản - TTG STORE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card-register { max-width: 450px; margin: 60px auto; border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .btn-custom { background-color: #0d6efd; border: none; }
        .btn-custom:hover { background-color: #0b5ed7; }
    </style>
</head>
<body>

<div class="container">
    <div class="card card-register p-4 bg-white">
        <h3 class="text-center text-primary mb-4 fw-bold">ĐĂNG KÝ TÀI KHOẢN</h3>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success py-2"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form action="index.php?url=account/register" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label fw-semibold">Tên đăng nhập</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Nhập tên tài khoản..." value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Địa chỉ Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Ví dụ: nguyenvana@gmail.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                <div class="form-text text-muted">Hệ thống sẽ gửi link kích hoạt vào email này.</div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Mật khẩu</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Nhập mật khẩu bí mật..." required>
            </div>

            <div class="mb-4">
                <label for="confirm_password" class="form-label fw-semibold">Xác nhận mật khẩu</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu..." required>
            </div>

            <button type="submit" class="btn btn-custom btn-primary w-100 fw-bold py-2 text-white">ĐĂNG KÝ NGAY</button>
        </form>

        <div class="text-center mt-4">
            <span class="text-muted">Bạn đã có tài khoản rồi?</span> 
            <a href="index.php?url=account/login" class="text-decoration-none fw-semibold">Đăng nhập</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>