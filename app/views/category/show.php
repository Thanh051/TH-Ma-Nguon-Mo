<?php require_once 'app/views/shares/header.php'; ?>

<div class="bg-white p-4 rounded shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-primary fw-bold mb-0">⚙️ QUẢN LÝ DANH MỤC SẢN PHẨM</h4>
        <div>
            <a href="/index.php?url=product/admin" class="btn btn-outline-primary me-2">Quản lý Sản Phẩm</a>
            <a href="/index.php?url=category/add" class="btn btn-success fw-bold">+ Thêm Danh Mục</a>
        </div>
    </div>

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-primary text-center">
            <tr>
                <th style="width: 100px;">ID</th>
                <th>Tên Danh Mục</th>
                <th style="width: 200px;">Hành Động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $c): ?>
            <tr>
                <td class="text-center fw-bold"><?= $c['id'] ?></td>
                <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                <td class="text-center">
                    <a href="/index.php?url=category/edit/<?= $c['id'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                    <a href="/index.php?url=category/delete/<?= $c['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa danh mục này sẽ xóa toàn bộ sản phẩm thuộc danh mục. Bạn chắc chắn muốn xóa?')">Xóa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'app/views/shares/footer.php'; ?>