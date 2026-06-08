<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Hệ Thống - TTG STORE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card-login { max-width: 420px; margin: 80px auto; border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .btn-custom { background-color: #28a745; border: none; }
        .btn-custom:hover { background-color: #218838; }
    </style>
</head>
<body>

<div class="container">
    <div class="card card-login p-4 bg-white">
        <h3 class="text-center text-success mb-4 fw-bold">ĐĂNG NHẬP</h3>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'verified'): ?>
            <div class="alert alert-success py-2">Kích hoạt Email thành công! Bạn có thể đăng nhập ngay.</div>
        <?php endif; ?>

        <form action="index.php?url=account/login" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label fw-semibold">Tên đăng nhập</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Nhập username của bạn..." value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Mật khẩu</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Nhập mật khẩu..." required>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label text-muted" for="remember">Ghi nhớ đăng nhập</label>
                </div>
                <a href="index.php?url=account/forgot_password" class="text-decoration-none text-sm text-danger">Quên mật khẩu?</a>
            </div>

            <button type="submit" class="btn btn-custom btn-success w-100 fw-bold py-2 text-white">ĐĂNG NHẬP</button>
        </form>

        <div class="text-center mt-4">
            <span class="text-muted">Chưa có tài khoản?</span> 
            <a href="index.php?url=account/register" class="text-decoration-none fw-semibold text-success">Đăng ký mới</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>