    </div> <!-- Đóng thẻ main-content -->

    <!-- Chân trang (Footer) -->
    <footer>
        <div class="container">
            <div class="row g-4">
                <!-- Cột 1: Giới thiệu & Thanh toán -->
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="footer-brand-title mb-3">
                        <i class="fas fa-book-open me-2"></i>3<span>FC</span>
                    </h5>
                    <p class="footer-brand-desc mb-4">Hệ thống bán sách trực tuyến cao cấp, cung cấp tri thức cho mọi người với trải nghiệm mua sắm hiện đại, nhanh chóng và bảo mật tối đa.</p>
                    <div class="footer-social-icons d-flex gap-3 mb-4">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-github"></i></a>
                    </div>
                    <!-- Huy hiệu thanh toán -->
                    <div class="footer-payment-badges d-flex gap-2 align-items-center flex-wrap">
                        <span class="payment-badge-custom"><i class="fas fa-money-bill-wave text-success"></i>COD</span>
                        <span class="payment-badge-custom"><i class="fas fa-wallet text-danger"></i>PayOS</span>
                        <span class="payment-badge-custom"><i class="fab fa-cc-visa text-primary"></i>Visa</span>
                        <span class="payment-badge-custom"><i class="fas fa-qrcode text-warning"></i>VietQR</span>
                    </div>
                </div>

                <!-- Cột 2: Liên kết nhanh -->
                <div class="col-md-4 col-lg-2 mb-4 mb-md-0 ps-lg-4">
                    <h6 class="footer-section-title">Liên kết</h6>
                    <ul class="list-unstyled footer-links-list">
                        <li><a href="<?php echo $base_url; ?>index.php" class="footer-link-item"><i class="fas fa-chevron-right"></i> Trang chủ</a></li>
                        <li><a href="<?php echo $base_url; ?>about.php" class="footer-link-item"><i class="fas fa-chevron-right"></i> Giới thiệu</a></li>
                        <li><a href="<?php echo $base_url; ?>promotions.php" class="footer-link-item"><i class="fas fa-chevron-right"></i> Khuyến mãi</a></li>
                        <li><a href="<?php echo $base_url; ?>contact.php" class="footer-link-item"><i class="fas fa-chevron-right"></i> Liên hệ</a></li>
                    </ul>
                </div>

                <!-- Cột 3: Thông tin môn học -->
                <div class="col-md-4 col-lg-3 mb-4 mb-md-0">
                    <h6 class="footer-section-title">Thông tin môn học</h6>
                    <ul class="list-unstyled footer-info-list">
                        <li><span class="footer-info-label">Môn học:</span> <span class="footer-info-val">Thiết kế Web với PHP</span></li>
                        <li><span class="footer-info-label">Giảng viên:</span> <span class="footer-info-val">ThS. Nguyễn Trung Trí</span></li>
                        <li><span class="footer-info-label">Đề tài 2:</span> <span class="footer-info-val">Website bán sách online</span></li>
                        <li><span class="footer-info-label">Trường:</span> <span class="footer-info-val">Đại học Đồng Tháp</span></li>
                    </ul>
                </div>

                <!-- Cột 4: Đăng ký nhận tin -->
                <div class="col-md-4 col-lg-3">
                    <h6 class="footer-section-title">Đăng ký nhận tin</h6>
                    <p class="small footer-brand-desc mb-3">Nhập email của bạn để nhận tin khuyến mãi sớm nhất từ 3FC.</p>
                    <form onsubmit="event.preventDefault(); showToast('Cảm ơn bạn đã đăng ký nhận bản tin 3FC!', 'success'); this.reset();" class="d-flex gap-2 footer-newsletter-form">
                        <input type="email" class="form-control footer-newsletter-input" placeholder="Email của bạn..." required>
                        <button type="submit" class="btn footer-newsletter-btn">Gửi</button>
                    </form>
                </div>
            </div>
            <hr class="footer-divider my-4">
            <div class="text-center footer-bottom-text">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> 3FC Bookstore. Thiết kế & phát triển cho mục đích báo cáo môn học.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
