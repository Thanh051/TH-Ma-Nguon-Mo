<?php require_once 'app/views/shares/header.php'; ?>

<?php
// Kiểm tra và dự phòng nếu Controller không truyền biến $userInfo, hệ thống sẽ tự lấy từ Session đã đăng nhập
if (!isset($userInfo) && isset($_SESSION['user'])) {
    $userInfo = $_SESSION['user'];
}
?>

<div class="container mt-4" style="max-width: 600px;">
    <div class="card shadow border-0 rounded-3">
        <div class="card-header bg-primary text-white fw-bold">👤 HỒ SƠ CÁ NHÂN</div>
        <div class="card-body p-4">
            
            <?= $msg ?? '' ?>
            
            <form action="index.php?url=account/profile" method="POST" enctype="multipart/form-data">
                
                <div class="text-center mb-4">
                    <?php $avatarName = !empty($userInfo['avatar']) ? $userInfo['avatar'] : 'default_avatar.png'; ?>
                    <img src="public/uploads/avatars/<?= htmlspecialchars($avatarName) ?>" 
                         id="preview-avatar" 
                         class="rounded-circle img-thumbnail shadow-sm" 
                         style="width: 120px; height: 120px; object-fit: cover;">
                         
                    <input type="hidden" name="old_avatar" value="<?= htmlspecialchars($avatarName) ?>">
                    
                    <div class="mt-2">
                        <input type="file" name="avatar" class="form-control form-control-sm" accept="image/*" onchange="previewImage(event)">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Tên đăng nhập</label>
                    <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($userInfo['username'] ?? '') ?>" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Địa chỉ Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($userInfo['email'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Mật khẩu mới (Bỏ trống nếu không đổi)</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••">
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-bold py-2">LƯU THÔNG TIN</button>
            </form>
        </div>
    </div>
</div>

<script>
function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function(){
        var output = document.getElementById('preview-avatar');
        output.src = reader.result;
    };
    if(event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    }
}
</script>

<?php require_once 'app/views/shares/footer.php'; ?>