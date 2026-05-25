<?php require_once 'app/views/shares/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6 text-center">
            <div class="card border-0 shadow rounded-3 p-5 bg-white">
                
                <div class="mb-4">
                    <div class="display-1 text-success animate-bounce">🎉</div>
                    <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16">
                            <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                        </svg>
                    </div>
                </div>

                <h2 class="fw-bold text-dark mb-2">ĐẶT HÀNG THÀNH CÔNG!</h2>
                <p class="text-secondary mb-4">Cảm ơn bạn đã tin tưởng và mua sắm tại <strong>TTG Store</strong>.</p>
                
                <div class="bg-light p-3 rounded-3 text-start mb-4 border border-dashed">
                    <h6 class="fw-bold text-primary mb-2">📦 Trạng thái đơn hàng của bạn:</h6>
                    <ul class="list-unstyled small text-secondary mb-0" style="line-height: 1.8;">
                        <li>• Hệ thống đã ghi nhận thông tin đặt hàng tự động.</li>
                        <li>• Nhân viên tổng đài sẽ gọi điện xác nhận trong vòng <strong class="text-dark">15 - 30 phút</strong>.</li>
                        <li>• Thời gian giao hàng dự kiến: <strong class="text-dark">2 - 4 ngày làm việc</strong>.</li>
                    </ul>
                </div>

                <p class="small text-muted mb-4">Mọi thắc mắc vui lòng liên hệ hotline miễn phí: <strong class="text-danger">1800.1060</strong></p>

                <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                    <a href="/index.php" class="btn btn-blue-tgdd fw-bold px-4 py-2.5 rounded-pill shadow-sm">
                        🏠 Quay lại trang chủ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .border-dashed {
        border-style: dashed !important;
    }
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    .animate-bounce {
        animation: bounce 2s infinite;
    }
</style>

<?php require_once 'app/views/shares/footer.php'; ?>