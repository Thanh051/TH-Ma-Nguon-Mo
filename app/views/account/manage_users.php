<?php require_once 'app/views/shares/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark m-0"><i class="bi bi-people-fill text-primary"></i> QUẢN LÝ THÀNH VIÊN</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb m-0 small">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">Thành viên</li>
            </ol>
        </nav>
    </div>

    <?= $msg ?? '' ?>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 80px;">ID</th>
                            <th>Thành viên</th>
                            <th>Email</th>
                            <th>Quyền hạn</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-4" style="width: 150px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-secondary">#<?= $user['id'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php $userAvatar = !empty($user['avatar']) ? $user['avatar'] : 'default_avatar.png'; ?>
                                            <img src="public/uploads/avatars/<?= htmlspecialchars($userAvatar) ?>" 
                                                 alt="Avatar" 
                                                 class="rounded-circle border" 
                                                 style="width: 38px; height: 38px; object-fit: cover;">
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($user['username']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle px-2 py-1" style="font-size: 11px;">ADMIN</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle px-2 py-1" style="font-size: 11px;">USER</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['status'] == 1): ?>
                                            <span class="badge bg-success d-inline-flex align-items-center gap-1 px-2 py-1"><i class="bi bi-check-circle-fill"></i> Hoạt động</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger d-inline-flex align-items-center gap-1 px-2 py-1"><i class="bi bi-dash-circle-fill"></i> Đang bị khóa</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <form action="index.php?url=account/toggleUserStatus" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn thực hiện hành động này đối với tài khoản <?= htmlspecialchars($user['username']) ?>?')">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <input type="hidden" name="current_status" value="<?= $user['status'] ?>">
                                            
                                            <?php if ($user['status'] == 1): ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger fw-semibold d-inline-flex align-items-center gap-1" <?= ($user['id'] == $_SESSION['user']['id']) ? 'disabled' : '' ?>>
                                                    <i class="bi bi-lock-fill"></i> Khóa tài khoản
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-sm btn-success fw-semibold d-inline-flex align-items-center gap-1">
                                                    <i class="bi bi-unlock-fill"></i> Mở khóa
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Chưa có thành viên nào đăng ký trong hệ thống.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/shares/footer.php'; ?>