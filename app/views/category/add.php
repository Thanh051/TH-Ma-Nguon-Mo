<?php require_once 'app/views/shares/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-success text-white fw-bold py-3">
                ➕ THÊM DANH MỤC MỚI
            </div>
            <div class="card-body p-4">
                <form action="/index.php?url=category/add" method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary">Tên Danh Mục</label>
                        <input type="text" name="name" class="form-control" placeholder="Ví dụ: Đồng hồ thông minh, Máy cũ giá rẻ" required>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success w-100 fw-bold">LƯU DANH MỤC</button>
                        <a href="/index.php?url=category/admin" class="btn btn-secondary w-50">HỦY</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/shares/footer.php'; ?>