<?php
require_once __DIR__ . '/../includes/db_helper.php';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <div class="text-center mb-5">
        <h2 class="section-title mb-2">Ưu Đãi & Khuyến Mãi</h2>
        <p class="text-muted fs-5">"Đọc nhiều hơn, tiết kiệm nhiều hơn với 3FC Bookstore"</p>
    </div>

    <!-- Danh sách mã giảm giá dạng thẻ premium -->
    <div class="row g-4 mb-5">
        <!-- Voucher 1 -->
        <div class="col-lg-6">
            <div class="glass-panel p-4 text-white h-100 position-relative overflow-hidden hover-lift transition-smooth" style="border-left: 4px solid var(--accent-color);">
                <div class="row align-items-center">
                    <div class="col-8">
                        <span class="badge bg-danger mb-2">HOT SUMMER</span>
                        <h4 class="mb-2" style="font-family: var(--font-heading); font-weight: 700; color: var(--text-main);">Giảm 20% sách KH-CN</h4>
                        <p class="text-muted small mb-0">Áp dụng cho mọi đầu sách thuộc thể loại Khoa học & Công nghệ. Hạn dùng đến 31/08/2026.</p>
                    </div>
                    <div class="col-4 text-end">
                        <div class="p-2 rounded text-center" style="background: rgba(255,255,255,0.05); border: 1px dashed var(--glass-border);">
                            <span class="d-block small text-muted mb-1">Mã code:</span>
                            <strong class="text-warning d-block mb-2">SUMMER20</strong>
                            <button class="btn btn-sm btn-warning w-100 py-1" onclick="copyCode('SUMMER20')" style="font-size:0.75rem; font-weight:600; border-radius: 6px;">Copy</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Voucher 2 -->
        <div class="col-lg-6">
            <div class="glass-panel p-4 text-white h-100 position-relative overflow-hidden hover-lift transition-smooth" style="border-left: 4px solid #4f7a52;">
                <div class="row align-items-center">
                    <div class="col-8">
                        <span class="badge bg-success mb-2">NEW MEMBER</span>
                        <h4 class="mb-2" style="font-family: var(--font-heading); font-weight: 700; color: var(--text-main);">Tặng ngay 10.000đ</h4>
                        <p class="text-muted small mb-0">Dành riêng cho khách hàng mới đăng ký tài khoản tại 3FC. Hạn dùng đến 31/12/2026.</p>
                    </div>
                    <div class="col-4 text-end">
                        <div class="p-2 rounded text-center" style="background: rgba(255,255,255,0.05); border: 1px dashed var(--glass-border);">
                            <span class="d-block small text-muted mb-1">Mã code:</span>
                            <strong class="text-success d-block mb-2">WELCOME3FC</strong>
                            <button class="btn btn-sm btn-success w-100 py-1 text-white" onclick="copyCode('WELCOME3FC')" style="font-size:0.75rem; font-weight:600; border-radius: 6px;">Copy</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Voucher 3 -->
        <div class="col-lg-6">
            <div class="glass-panel p-4 text-white h-100 position-relative overflow-hidden hover-lift transition-smooth" style="border-left: 4px solid #0052cc;">
                <div class="row align-items-center">
                    <div class="col-8">
                        <span class="badge bg-primary mb-2">FREE SHIP</span>
                        <h4 class="mb-2" style="font-family: var(--font-heading); font-weight: 700; color: var(--text-main);">Miễn phí vận chuyển</h4>
                        <p class="text-muted small mb-0">Miễn phí ship toàn quốc cho mọi đơn hàng giá trị từ 250.000đ trở lên.</p>
                    </div>
                    <div class="col-4 text-end">
                        <div class="p-2 rounded text-center" style="background: rgba(255,255,255,0.05); border: 1px dashed var(--glass-border);">
                            <span class="d-block small text-muted mb-1">Mã code:</span>
                            <strong class="text-primary d-block mb-2">FREESHIP</strong>
                            <button class="btn btn-sm btn-primary w-100 py-1" onclick="copyCode('FREESHIP')" style="font-size:0.75rem; font-weight:600; border-radius: 6px;">Copy</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Voucher 4 -->
        <div class="col-lg-6">
            <div class="glass-panel p-4 text-white h-100 position-relative overflow-hidden hover-lift transition-smooth" style="border-left: 4px solid #e0c568;">
                <div class="row align-items-center">
                    <div class="col-8">
                        <span class="badge bg-warning text-dark mb-2">CASHBACK</span>
                        <h4 class="mb-2" style="font-family: var(--font-heading); font-weight: 700; color: var(--text-main);">Hoàn tiền 5% qua PayOS</h4>
                        <p class="text-muted small mb-0">Chọn thanh toán Online bằng cổng PayOS/VietQR để được hoàn ngay 5% (tối đa 20k).</p>
                    </div>
                    <div class="col-4 text-end">
                        <div class="p-2 rounded text-center" style="background: rgba(255,255,255,0.05); border: 1px dashed var(--glass-border);">
                            <span class="d-block small text-muted mb-1">Mã code:</span>
                            <strong class="text-warning d-block mb-2">QRONLINE</strong>
                            <button class="btn btn-sm btn-warning w-100 py-1 text-dark" onclick="copyCode('QRONLINE')" style="font-size:0.75rem; font-weight:600; border-radius: 6px;">Copy</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Banner kêu gọi đăng ký -->
    <div class="glass-panel p-5 text-center text-white">
        <i class="fas fa-gift text-warning fa-3x mb-3"></i>
        <h3 class="mb-3" style="font-family: var(--font-heading); font-weight: 700; color: var(--text-main);">Bạn chưa đăng ký thành viên?</h3>
        <p class="text-muted mb-4 max-width-600 mx-auto">Đăng ký tài khoản ngay hôm nay để nhận được các mã giảm giá cá nhân đặc biệt và nhận tin tức khuyến mãi sớm nhất từ hệ thống của chúng tôi.</p>
        <a href="register.php" class="btn btn-primary-custom px-4 py-2"><i class="fas fa-user-plus me-2"></i>Đăng ký tài khoản ngay</a>
    </div>
</div>

<script>
function copyCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        showToast('Đã copy mã giảm giá "' + code + '" thành công!', 'success');
    }).catch(err => {
        console.error('Không thể copy mã: ', err);
        showToast('Không thể copy mã tự động, vui lòng thử lại.', 'danger');
    });
}
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
