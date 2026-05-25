<?php require_once 'app/views/shares/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning text-dark fw-bold py-3">
                📝 SỬA DANH MỤC (ID: #<?= $category['id'] ?>)
            </div>
            <div class="card-body p-4">
                <form action="/index.php?url=category/edit/<?= $category['id'] ?>" method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary">Tên Danh Mục</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning text-dark w-100 fw-bold">CẬP NHẬT</button>
                        <a href="/index.php?url=category/admin" class="btn btn-secondary w-50">QUAY LẠI</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/shares/footer.php'; ?>