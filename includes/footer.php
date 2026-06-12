    </div> <!-- Đóng thẻ main-content -->

    <!-- Chân trang (Footer) -->
    <footer>
        <div class="container">
            <div class="row g-4">
                <!-- Cột 1: Giới thiệu & Thanh toán -->
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="text-white mb-3" style="font-family: var(--font-heading); font-size: 1.6rem; font-weight: 700;">
                        <i class="fas fa-book-open text-warning me-2"></i>3<span class="text-warning">FC</span>
                    </h5>
                    <p class="mb-3" style="font-size: 0.95rem; line-height: 1.6;">Hệ thống bán sách trực tuyến cao cấp, cung cấp tri thức cho mọi người với trải nghiệm mua sắm hiện đại, nhanh chóng và bảo mật tối đa.</p>
                    <div class="social-icons d-flex gap-3 mb-4">
                        <a href="#" class="text-muted hover-accent"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-muted hover-accent"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-muted hover-accent"><i class="fab fa-github fa-lg"></i></a>
                    </div>
                    <!-- Huy hiệu thanh toán -->
                    <div class="payment-badges d-flex gap-2 align-items-center flex-wrap">
                        <span class="badge bg-light text-dark border py-1 px-2" style="font-size:0.7rem; font-weight: 600; border-radius: 4px;"><i class="fas fa-money-bill-wave text-success me-1"></i>COD</span>
                        <span class="badge bg-light text-dark border py-1 px-2" style="font-size:0.7rem; font-weight: 600; border-radius: 4px;"><i class="fas fa-wallet text-danger me-1"></i>PayOS</span>
                        <span class="badge bg-light text-dark border py-1 px-2" style="font-size:0.7rem; font-weight: 600; border-radius: 4px;"><i class="fab fa-cc-visa text-primary me-1"></i>Visa</span>
                        <span class="badge bg-light text-dark border py-1 px-2" style="font-size:0.7rem; font-weight: 600; border-radius: 4px;"><i class="fas fa-qrcode text-warning me-1"></i>VietQR</span>
                    </div>
                </div>

                <!-- Cột 2: Liên kết nhanh -->
                <div class="col-md-4 col-lg-2 mb-4 mb-md-0 ps-lg-4">
                    <h6 class="text-white mb-3" style="font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Liên kết</h6>
                    <ul class="list-unstyled" style="font-size: 0.95rem;">
                        <li class="mb-2"><a href="index.php" class="text-muted text-decoration-none hover-accent"><i class="fas fa-chevron-right me-1" style="font-size:0.75rem;"></i> Trang chủ</a></li>
                        <li class="mb-2"><a href="cart.php" class="text-muted text-decoration-none hover-accent"><i class="fas fa-chevron-right me-1" style="font-size:0.75rem;"></i> Giỏ hàng</a></li>
                        <li class="mb-2"><a href="orders.php" class="text-muted text-decoration-none hover-accent"><i class="fas fa-chevron-right me-1" style="font-size:0.75rem;"></i> Đơn hàng</a></li>
                    </ul>
                </div>

                <!-- Cột 3: Thông tin môn học -->
                <div class="col-md-4 col-lg-3 mb-4 mb-md-0">
                    <h6 class="text-white mb-3" style="font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Thông tin môn học</h6>
                    <ul class="list-unstyled" style="font-size: 0.95rem; line-height: 1.8;">
                        <li><span class="text-muted">Môn học:</span> Thiết kế Web với PHP</li>
                        <li><span class="text-muted">Giảng viên:</span> ThS. Nguyễn Trung Trí</li>
                        <li><span class="text-muted">Đề tài 2:</span> Website bán sách online</li>
                        <li><span class="text-muted">Trường:</span> Đại học Đồng Tháp</li>
                    </ul>
                </div>

                <!-- Cột 4: Đăng ký nhận tin -->
                <div class="col-md-4 col-lg-3">
                    <h6 class="text-white mb-3" style="font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Đăng ký nhận tin</h6>
                    <p class="small text-muted mb-3" style="line-height: 1.5;">Nhập email của bạn để nhận tin khuyến mãi sớm nhất từ 3FC.</p>
                    <form onsubmit="event.preventDefault(); alert('Cảm ơn bạn đã đăng ký nhận tin bản tin 3FC!'); this.reset();" class="d-flex gap-1">
                        <input type="email" class="form-control form-control-sm" placeholder="Email của bạn..." required style="background: rgba(0,0,0,0.03); border: 1px solid var(--glass-border); color: var(--text-main); font-size: 0.85rem; padding: 0.5rem 0.75rem; border-radius: 8px;">
                        <button type="submit" class="btn btn-warning btn-sm px-3 text-dark font-weight-600" style="font-size: 0.85rem; border-radius: 8px; transition: var(--transition-smooth);">Gửi</button>
                    </form>
                </div>
            </div>
            <hr class="my-4" style="border-color: var(--glass-border);">
            <div class="text-center text-muted" style="font-size: 0.9rem;">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> 3FC Bookstore. Thiết kế & phát triển cho mục đích báo cáo môn học.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
