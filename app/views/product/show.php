<?php require_once 'app/views/shares/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="text-primary fw-bold">Hệ Thống Quản Lý Sản Phẩm (CRUD)</h3>
    <a href="/index.php?url=product/add" class="btn btn-success fw-bold">+ Thêm Mới</a>
</div>

<table class="table table-bordered table-striped align-middle">
    <thead class="table-primary text-center">
        <tr>
            <th>ID</th>
            <th>Tên Sản Phẩm</th>
            <th>Giá Cả</th>
            <th>Danh Mục</th>
            <th>Hành Động</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $p): ?>
        <tr>
            <td class="text-center"><?= $p['id'] ?></td>
            <td><strong><?= $p['name'] ?></strong></td>
            <td class="text-danger fw-bold text-end"><?= number_format($p['price']) ?> đ</td>
            <td class="text-center"><?= $p['category_name'] ?></td>
            <td class="text-center">
                <a href="/index.php?url=product/edit/<?= $p['id'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                <a href="/index.php?url=product/delete/<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa sản phẩm này?')">Xóa</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once 'app/views/shares/footer.php'; ?>