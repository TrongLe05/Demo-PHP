<?php
require_once __DIR__ . '/../includes/db_helper.php';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <div class="text-center mb-5">
        <h2 class="section-title mb-2">Về Chúng Tôi - 3FC Bookstore</h2>
        <p class="text-muted fs-5">"Nơi tri thức hội tụ, trải nghiệm mua sắm đỉnh cao"</p>
    </div>

    <!-- Hộp giới thiệu chung -->
    <div class="glass-panel p-5 mb-5 text-white">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0 text-center">
                <i class="fas fa-book-open text-warning" style="font-size: 8rem; filter: drop-shadow(0 0 20px rgba(244, 211, 94, 0.4));"></i>
            </div>
            <div class="col-lg-6">
                <h3 class="mb-3" style="font-family: var(--font-heading); font-weight: 700; color: var(--text-main);">Câu chuyện thương hiệu 3FC</h3>
                <p class="fs-6" style="line-height: 1.8; color: var(--text-muted);">
                    Được thành lập vào năm 2026 bởi nhóm sinh viên khoa Công nghệ và kỹ thuật trường <strong>Đại học Đồng Tháp</strong>, <strong>3FC Bookstore</strong> ra đời với sứ mệnh xây dựng một cổng kết nối tri thức cao cấp, hiện đại và thân thiện cho tất cả mọi người.
                </p>
                <p class="fs-6 mb-0" style="line-height: 1.8; color: var(--text-muted);">
                    Từ một dự án môn học Thiết kế Web với PHP, chúng tôi đã không ngừng nỗ lực, tích hợp các công nghệ thanh toán thời gian thực như VietQR, cổng thanh toán tự động PayOS và hệ thống giỏ hàng đồng bộ AJAX để mang lại trải nghiệm mua sắm tiện lợi nhất cho độc giả.
                </p>
            </div>
        </div>
    </div>

    <!-- Grid Sứ mệnh & Giá trị cốt lõi -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="glass-panel p-4 h-100 text-center text-white transition-smooth hover-lift">
                <div class="mb-3">
                    <i class="fas fa-bullseye text-warning fa-3x"></i>
                </div>
                <h4 class="mb-3" style="font-family: var(--font-heading); font-weight: 600; color: var(--text-main);">Sứ mệnh</h4>
                <p class="text-muted mb-0" style="font-size: 0.95rem; line-height: 1.6;">
                    Phổ cập tri thức chất lượng cao đến mọi nẻo đường đất nước thông qua nền tảng mua sách trực tuyến thông minh, minh bạch và an toàn tối đa.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-panel p-4 h-100 text-center text-white transition-smooth hover-lift">
                <div class="mb-3">
                    <i class="fas fa-heart text-danger fa-3x"></i>
                </div>
                <h4 class="mb-3" style="font-family: var(--font-heading); font-weight: 600; color: var(--text-main);">Giá trị cốt lõi</h4>
                <p class="text-muted mb-0" style="font-size: 0.95rem; line-height: 1.6;">
                    Đặt khách hàng và chất lượng dịch vụ làm trung tâm. Mọi cuốn sách đều được chọn lọc kỹ càng, đóng gói cẩn thận trước khi gửi tới tay bạn đọc.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-panel p-4 h-100 text-center text-white transition-smooth hover-lift">
                <div class="mb-3">
                    <i class="fas fa-magic text-primary fa-3x"></i>
                </div>
                <h4 class="mb-3" style="font-family: var(--font-heading); font-weight: 600; color: var(--text-main);">Công nghệ</h4>
                <p class="text-muted mb-0" style="font-size: 0.95rem; line-height: 1.6;">
                    Ứng dụng AJAX và các cổng API thanh toán tự động thời gian thực (VietQR, PayOS) để loại bỏ mọi thời gian chờ đợi phiền toái cho khách hàng.
                </p>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
