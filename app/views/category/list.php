<?php $pageTitle = 'Danh mục'; require __DIR__ . '/../shares/header.php'; ?>

<?php
$msgs = ['1' => '✓ Thêm danh mục thành công!', '2' => '✓ Cập nhật thành công!', '3' => '✓ Đã xóa danh mục.'];
if (isset($_GET['success']) && isset($msgs[$_GET['success']])): ?>
    <div class="alert alert-success"><?= $msgs[$_GET['success']] ?></div>
<?php endif; ?>

<div class="section-header">
    <h2>Danh mục <span class="badge"><?= count($categories) ?></span></h2>
    <a href="?controller=category&action=add" class="btn btn-primary">+ Thêm mới</a>
</div>

<?php if (empty($categories)): ?>
    <div class="empty-state">
        <div class="empty-icon">◧</div>
        <p>Chưa có danh mục nào. <a href="?controller=category&action=add">Thêm ngay</a></p>
    </div>
<?php else: ?>
<div class="table-card">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Tên danh mục</th>
                <th>Mô tả</th>
                <th class="text-right">Thao tác</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($categories as $i => $c): ?>
            <tr>
                <td class="text-muted"><?= $i + 1 ?></td>
                <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                <td class="text-muted"><?= htmlspecialchars($c['description'] ?: '—') ?></td>
                <td class="text-right">
                    <a href="?controller=category&action=edit&id=<?= $c['id'] ?>" class="btn-sm">Sửa</a>
                    <a href="?controller=category&action=delete&id=<?= $c['id'] ?>" class="btn-sm btn-danger"
                       onclick="return confirm('Xóa danh mục này?')">Xóa</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../shares/footer.php'; ?>
