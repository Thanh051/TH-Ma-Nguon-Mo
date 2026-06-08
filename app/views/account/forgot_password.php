<?php require_once 'app/views/shares/header.php'; ?>
<div class="container mt-5" style="max-width: 450px;">
    <div class="card shadow border-0 rounded-3">
        <div class="card-body p-4">
            <h4 class="fw-bold text-center mb-3">🔒 QUÊN MẬT KHẨU</h4>
            <p class="text-muted text-center small">Nhập email đăng ký của bạn để hệ thống gửi mã xác thực OTP.</p>
            
            <?= $msg ?? '' ?>
            
            <form action="index.php?url=account/forgot_password" method="POST">
                <div class="mb-3">
                    <input type="email" name="email" class="form-control py-2" placeholder="Nhập email của bạn..." required>
                </div>
                <button type="submit" class="btn btn-blue-tgdd w-100 fw-bold py-2">GỬI MÃ XÁC NHẬN</button>
            </form>
            
            <div class="text-center mt-3">
                <a href="index.php?url=account/login" class="text-decoration-none small text-secondary">← Quay lại đăng nhập</a>
            </div>
        </div>
    </div>
</div>
<?php require_once 'app/views/shares/footer.php'; ?>