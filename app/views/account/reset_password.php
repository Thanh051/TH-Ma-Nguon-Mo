<?php require_once 'app/views/shares/header.php'; ?>
<div class="container mt-5" style="max-width: 450px;">
    <div class="card shadow border-0 rounded-3">
        <div class="card-body p-4">
            <h4 class="fw-bold text-center mb-3">🔑 ĐẶT LẠI MẬT KHẨU</h4>
            <p class="text-muted text-center small">
                Vui lòng nhập mã OTP đã được gửi đến email <strong class="text-primary"><?= htmlspecialchars($email ?? '') ?></strong> và điền mật khẩu mới.
            </p>
            
            <?= $msg ?? '' ?>
            
            <form action="index.php?url=account/resetPassword&email=<?= urlencode($email ?? '') ?>" method="POST" onsubmit="return validatePasswords()">
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Mã xác thực OTP</label>
                    <input type="text" name="otp" class="form-control py-2 text-center fw-bold text-danger fs-5" placeholder="• • • • • •" maxlength="6" pattern="\d{6}" title="Mã OTP phải bao gồm 6 chữ số" required>
                    <div class="form-text small text-muted">Kiểm tra hộp thư đến hoặc thư rác (Spam) để lấy mã.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Mật khẩu mới</label>
                    <input type="password" id="password" name="password" class="form-control py-2" placeholder="Nhập mật khẩu mới (ít nhất 6 ký tự)..." minlength="6" required>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-secondary">Xác nhận mật khẩu mới</label>
                    <input type="password" id="confirm_password" class="form-control py-2" placeholder="Nhập lại mật khẩu mới..." required>
                    <div id="password-error" class="text-danger small mt-1" style="display: none;">❌ Mật khẩu nhập lại không trùng khớp!</div>
                </div>

                <button type="submit" class="btn btn-blue-tgdd w-100 fw-bold py-2">XÁC NHẬN ĐỔI MẬT KHẨU</button>
            </form>
            
            <div class="text-center mt-3">
                <a href="index.php?url=account/login" class="text-decoration-none small text-secondary">← Quay lại đăng nhập</a>
            </div>
        </div>
    </div>
</div>

<script>
function validatePasswords() {
    var password = document.getElementById("password").value;
    var confirmPassword = document.getElementById("confirm_password").value;
    var errorDiv = document.getElementById("password-error");

    if (password !== confirmPassword) {
        errorDiv.style.display = "block";
        return false; // Ngăn chặn form gửi đi
    }
    errorDiv.style.display = "none";
    return true; // Cho phép gửi form
}
</script>
<?php require_once 'app/views/shares/footer.php'; ?>