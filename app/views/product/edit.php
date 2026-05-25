<?php require_once 'app/views/shares/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-warning text-dark fw-bold py-3 fs-5">
                📝 CẬP NHẬT SẢN PHẨM (ID: #<?= $product['id'] ?>)
            </div>
            <div class="card-body p-4">
                <form action="/index.php?url=product/edit/<?= $product['id'] ?>" method="POST" enctype="multipart/form-data">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Tên Sản Phẩm</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Giá Sản Phẩm (đ)</label>
                        <input type="number" name="price" class="form-control" value="<?= $product['price'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Danh Mục Phân Loại</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach($categories as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $c['id'] == $product['category_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Thay đổi hình ảnh sản phẩm</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($product['image']) ?>">
                        <div class="mt-2">
                            <span class="small text-muted">Ảnh hiện tại: <strong><?= $product['image'] ?></strong></span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary">Mô tả thông số kỹ thuật</label>
                        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning text-dark w-100 fw-bold py-2 shadow-sm">CẬP NHẬT NGAY</button>
                        <a href="/index.php?url=product/admin" class="btn btn-secondary w-50 fw-bold py-2">QUAY LẠI</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/shares/footer.php'; ?>