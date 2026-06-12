<?php
require_once __DIR__ . '/../includes/db_helper.php';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <div class="text-center mb-5">
        <h2 class="section-title mb-2">Liên Hệ Với Chúng Tôi</h2>
        <p class="text-muted fs-5">"Chúng tôi luôn sẵn sàng lắng nghe và giải đáp mọi ý kiến phản hồi của bạn"</p>
    </div>

    <div class="row g-4">
        <!-- Cột 1: Thông tin liên hệ -->
        <div class="col-lg-5">
            <div class="glass-panel p-4 text-white h-100 d-flex flex-column justify-content-between">
                <div>
                    <h3 class="mb-4" style="font-family: var(--font-heading); font-weight: 700; color: var(--text-main);">Thông tin chi tiết</h3>
                    
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="p-3 rounded-circle text-center" style="background: rgba(244, 211, 94, 0.1); width: 50px; height: 50px; display:flex; align-items:center; justify-content:center;">
                            <i class="fas fa-map-marker-alt text-warning fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 text-white" style="font-weight: 600;">Địa chỉ</h5>
                            <p class="text-muted small mb-0">783 Phạm Hữu Lầu, Phường 6, TP. Cao Lãnh, tỉnh Đồng Tháp.</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="p-3 rounded-circle text-center" style="background: rgba(244, 211, 94, 0.1); width: 50px; height: 50px; display:flex; align-items:center; justify-content:center;">
                            <i class="fas fa-phone-alt text-warning fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 text-white" style="font-weight: 600;">Số điện thoại</h5>
                            <p class="text-muted small mb-0">0123.456.789 / 0987.654.321</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="p-3 rounded-circle text-center" style="background: rgba(244, 211, 94, 0.1); width: 50px; height: 50px; display:flex; align-items:center; justify-content:center;">
                            <i class="fas fa-envelope text-warning fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 text-white" style="font-weight: 600;">Email hỗ trợ</h5>
                            <p class="text-muted small mb-0">support@3fc.com</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="p-3 rounded-circle text-center" style="background: rgba(244, 211, 94, 0.1); width: 50px; height: 50px; display:flex; align-items:center; justify-content:center;">
                            <i class="fas fa-clock text-warning fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 text-white" style="font-weight: 600;">Giờ mở cửa</h5>
                            <p class="text-muted small mb-0">8:00 AM - 21:00 PM</p>
                        </div>
                    </div>
                </div>

                <!-- Bản đồ giả lập / Logo 3FC phát sáng -->
                <div class="p-4 rounded text-center" style="background: rgba(0,0,0,0.03); border: 1px solid var(--glass-border);">
                    <i class="fas fa-university text-warning fa-3x mb-2"></i>
                    <h5 class="mb-1 text-white" style="font-family: var(--font-heading);">Khoa Công nghệ và Kỹ thuật</h5>
                    <p class="text-muted small mb-0">Trường Đại học Đồng Tháp</p>
                </div>
            </div>
        </div>

        <!-- Cột 2: Form gửi phản hồi -->
        <div class="col-lg-7">
            <div class="glass-panel p-4 text-white">
                <h3 class="mb-4" style="font-family: var(--font-heading); font-weight: 700; color: var(--text-main);">Gửi tin nhắn phản hồi</h3>
                
                <form id="contact-form" onsubmit="handleContactSubmit(event)">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label for="contact-name">Họ và tên của bạn *</label>
                                <input type="text" id="contact-name" class="form-control-custom" required placeholder="Ví dụ: Nguyễn Văn A">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-custom">
                                <label for="contact-email">Email liên hệ *</label>
                                <input type="email" id="contact-email" class="form-control-custom" required placeholder="Ví dụ: name@example.com">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group-custom">
                                <label for="contact-phone">Số điện thoại</label>
                                <input type="tel" id="contact-phone" class="form-control-custom" placeholder="Ví dụ: 0123456789">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group-custom">
                                <label for="contact-subject">Chủ đề phản hồi *</label>
                                <input type="text" id="contact-subject" class="form-control-custom" required placeholder="Ví dụ: Hỏi về đơn hàng, Góp ý giao diện...">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group-custom">
                                <label for="contact-message">Nội dung tin nhắn *</label>
                                <textarea id="contact-message" class="form-control-custom" rows="5" required placeholder="Nhập ý kiến đóng góp hoặc nội dung câu hỏi của bạn tại đây..."></textarea>
                            </div>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary-custom px-4 py-2" id="btn-submit-contact">
                                <i class="fas fa-paper-plane me-2"></i>Gửi tin nhắn
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function handleContactSubmit(event) {
    event.preventDefault();
    const btn = document.getElementById('btn-submit-contact');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Đang xử lý gửi phản hồi...';
    }

    setTimeout(function() {
        showToast('Cảm ơn bạn! Ý kiến đóng góp của bạn đã được gửi thành công đến 3FC Bookstore.', 'success');
        document.getElementById('contact-form').reset();
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Gửi tin nhắn';
        }
    }, 1500);
}
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
