<?php require_once 'app/views/shares/header.php'; ?>
<div class="container mt-4">
    <h2 class="text-primary fw-bold mb-4">📜 Lịch sử thanh toán</h2>
    <table class="table table-hover shadow-sm">
        <thead class="table-light">
            <tr>
                <th>Mã ĐH</th>
                <th>Ngày đặt</th>
                <th>Người nhận</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
                <td>#<?= $o['id'] ?></td>
                <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                <td><?= htmlspecialchars($o['customer_name']) ?></td>
                <td class="text-danger fw-bold"><?= number_format($o['total_price']) ?> đ</td>
                <td><span class="badge bg-success">Đã thanh toán</span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once 'app/views/shares/footer.php'; ?>