<?php require_once 'app/views/shares/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-success text-white fw-bold py-3 fs-5">
                ➕ THÊM SẢN PHẨM MỚI
            </div>
            <div class="card-body p-4">
                <form action="/index.php?url=product/add" method="POST" enctype="multipart/form-data">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Tên Sản Phẩm</label>
                        <input type="text" name="name" class="form-control" placeholder="Ví dụ: iPhone 15 Pro Max" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Giá Sản Phẩm (đ)</label>
                        <input type="number" name="price" class="form-control" placeholder="Nhập số tiền bán lẻ" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Danh Mục Phân Loại</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Chọn danh mục sản phẩm --</option>
                            <?php if (!empty($categories)): ?>
                                <?php foreach($categories as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Hình ảnh sản phẩm</label>
                        <input type="file" name="image" class="form-control" accept="image/*" required>
                        <small class="text-muted">Hỗ trợ các định dạng file: .jpg, .jpeg, .png, .webp</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary">Mô tả thông số kỹ thuật</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Nhập thông số chi tiết..."></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success w-100 fw-bold py-2 shadow-sm">LƯU SẢN PHẨM</button>
                        <a href="/index.php?url=product/admin" class="btn btn-secondary w-50 fw-bold py-2">HỦY BỎ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/shares/footer.php'; ?>