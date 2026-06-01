<?php require_once 'app/views/shares/header.php'; ?>

<?php
if (!isset($current_cart)) {
    $current_cart = [];
}
?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-blue-tgdd text-white fw-bold py-3 fs-5">
        🛒 GIỎ HÀNG CỦA BẠN
    </div>
    <div class="card-body p-4">
        <?php if(empty($current_cart)): ?>
            <div class="text-center py-5">
                <h5 class="text-muted mb-4">Giỏ hàng của bạn đang trống!</h5>
                <a href="/index.php" class="btn btn-blue-tgdd fw-bold px-4 py-2">TIẾP TỤC MUA SẮM</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="10%">Hình ảnh</th>
                            <th>Sản phẩm</th>
                            <th width="15%">Giá bán</th>
                            <th width="20%" class="text-center">Số lượng</th>
                            <th width="15%" class="text-end">Tổng tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total = 0; 
                        // Chạy vòng lặp mượt mà dựa trên dữ liệu tầng Controller cung cấp
                        foreach($current_cart as $id => $item): 
                            $total += $item['price'] * $item['qty']; 
                        ?>
                            <tr>
                                <td>
                                    <img src="public/images/<?= $item['image'] ?>" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: contain;" onerror="this.src='public/images/default.jpg';">
                                </td>
                                <td><h6 class="mb-0 fw-bold text-dark"><?= htmlspecialchars($item['name']) ?></h6></td>
                                <td class="text-danger fw-bold"><?= number_format($item['price']) ?> đ</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        <form action="/index.php?url=product/updateCart" method="POST" class="d-inline">
                                            <input type="hidden" name="id" value="<?= $id ?>">
                                            <input type="hidden" name="action" value="decrease">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary px-2 fw-bold">-</button>
                                        </form>
                                        
                                        <span class="px-3 py-1 bg-light border rounded fw-bold"><?= $item['qty'] ?></span>
                                        
                                        <form action="/index.php?url=product/updateCart" method="POST" class="d-inline">
                                            <input type="hidden" name="id" value="<?= $id ?>">
                                            <input type="hidden" name="action" value="increase">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary px-2 fw-bold">+</button>
                                        </form>

                                        <form action="/index.php?url=product/updateCart" method="POST" class="d-inline ms-2">
                                            <input type="hidden" name="id" value="<?= $id ?>">
                                            <input type="hidden" name="action" value="remove">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">🗑</button>
                                        </form>
                                    </div>
                                </td>
                                <td class="text-end text-primary fw-bold"><?= number_format($item['price'] * $item['qty']) ?> đ</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <hr class="my-4">
            
            <div class="d-flex flex-column align-items-end mb-3">
                <h5 class="fw-bold">Tổng thanh toán: <span class="text-danger fs-3"><?= number_format($total) ?> đ</span></h5>
            </div>

            <div class="d-flex justify-content-between">
                <a href="/index.php" class="btn btn-outline-secondary fw-bold px-4 py-2">⬅ Tiếp tục mua hàng</a>
                <a href="/index.php?url=product/checkout" class="btn btn-success fw-bold px-5 py-2 fs-5 shadow-sm">TIẾN HÀNH THANH TOÁN ➡</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'app/views/shares/footer.php'; ?>