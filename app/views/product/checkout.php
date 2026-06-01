<?php require_once 'app/views/shares/header.php'; ?>

<?php
// ĐỒNG BỘ VỚI CONTROLLER:
// Biến $current_cart đã được ProductController lấy từ Database (hoặc session khách) và truyền sẵn sang đây.
if (!isset($current_cart)) {
    $current_cart = [];
}
?>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-success text-white fw-bold py-3 fs-5">
                📋 THÔNG TIN GIAO HÀNG
            </div>
            <div class="card-body p-4">
                <form action="/index.php?url=product/checkout" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Họ và tên người nhận</label>
                        <input type="text" name="name" class="form-control py-2" placeholder="Nhập đầy đủ họ tên" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Địa chỉ Email</label>
                        <input type="email" name="email" class="form-control py-2" placeholder="Ví dụ: nguyenvan@gmail.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control py-2" placeholder="Nhập số điện thoại nhận hàng" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary">Địa chỉ nhận hàng thực tế</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Số nhà, tên đường, xã/phường, quận/huyện..." required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100 fw-bold py-3 fs-5 shadow-sm">XÁC NHẬN ĐẶT HÀNG</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-light text-dark fw-bold py-3 fs-5 border-bottom">
                📦 TÓM TẮT ĐƠN HÀNG
            </div>
            <div class="card-body p-4">
                <ul class="list-group list-group-flush mb-3">
                    <?php 
                    $total = 0; 
                    foreach($current_cart as $id => $item): 
                        $total += $item['price'] * $item['qty']; 
                    ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                            <div>
                                <span class="fw-bold text-dark"><?= htmlspecialchars($item['name']) ?></span>
                                <small class="text-muted d-block">Số lượng: <?= $item['qty'] ?></small>
                            </div>
                            <span class="text-secondary fw-bold"><?= number_format($item['price'] * $item['qty']) ?> đ</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="border-top pt-3 d-flex justify-content-between align-items-center">
                    <span class="fs-5 fw-bold">Thành tiền:</span>
                    <strong class="text-danger fs-3"><?= number_format($total) ?> đ</strong>
                </div>
                <div class="mt-4">
                    <a href="/index.php?url=product/cart" class="btn btn-outline-secondary btn-sm w-100">Sửa đổi giỏ hàng</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/shares/footer.php'; ?>