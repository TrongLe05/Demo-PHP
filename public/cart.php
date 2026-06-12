<?php
require_once __DIR__ . '/../includes/db_helper.php';

// Yêu cầu đăng nhập trước khi xem giỏ hàng
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?status=login_required");
    exit;
}

// Xử lý cập nhật số lượng sách trong giỏ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    if (isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $book_id => $qty) {
            $book_id = (int)$book_id;
            $qty = (int)$qty;
            if ($qty <= 0) {
                unset($_SESSION['cart'][$book_id]);
            } else {
                $_SESSION['cart'][$book_id] = $qty;
            }
        }
    }
    // Đồng bộ giỏ hàng vào CSDL nếu đã đăng nhập
    if (isset($_SESSION['user_id'])) {
        sync_session_to_db_cart($_SESSION['user_id']);
    }
    header("Location: cart.php?status=updated");
    exit;
}

// Xử lý xóa sách khỏi giỏ hàng
if (isset($_GET['remove'])) {
    $book_id = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$book_id])) {
        unset($_SESSION['cart'][$book_id]);
    }
    // Đồng bộ giỏ hàng vào CSDL nếu đã đăng nhập
    if (isset($_SESSION['user_id'])) {
        sync_session_to_db_cart($_SESSION['user_id']);
    }
    header("Location: cart.php?status=removed");
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <h2 class="section-title mb-4">Giỏ Hàng Của Bạn</h2>

    <!-- Thanh tiến trình mua hàng -->
    <div class="steps-indicator">
        <div class="step-node active">
            <div class="step-circle">1</div>
            <div class="step-label">Giỏ hàng</div>
        </div>
        <div class="step-line"></div>
        <div class="step-node">
            <div class="step-circle">2</div>
            <div class="step-label">Thanh toán</div>
        </div>
        <div class="step-line"></div>
        <div class="step-node">
            <div class="step-circle">3</div>
            <div class="step-label">Hoàn tất</div>
        </div>
    </div>

    <!-- Thông báo kết quả thao tác bằng Toast -->
    <?php if (isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('Cập nhật số lượng giỏ hàng thành công!', 'success');
        });
        </script>
    <?php elseif (isset($_GET['status']) && $_GET['status'] == 'removed'): ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('Đã xóa sách khỏi giỏ hàng.', 'danger');
        });
        </script>
    <?php endif; ?>

    <?php if (empty($_SESSION['cart'])): ?>
        <div class="glass-panel text-center p-5">
            <i class="fas fa-shopping-cart text-muted fa-4x mb-3"></i>
            <h4 class="text-white">Giỏ hàng của bạn đang trống</h4>
            <p class="text-muted">Hãy lấp đầy nó bằng những cuốn sách hay từ cửa hàng của chúng tôi.</p>
            <a href="index.php" class="btn btn-primary-custom mt-3"><i class="fas fa-arrow-left me-2"></i>Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <form action="cart.php" method="POST">
            <input type="hidden" name="update_cart" value="1">
            <div class="row">
                <!-- Danh sách mặt hàng -->
                <div class="col-lg-8">
                    <div class="glass-panel cart-card table-responsive">
                        <table class="table cart-table align-middle">
                            <thead>
                                <tr>
                                    <th scope="col" style="width: 80px;"></th>
                                    <th scope="col">Tên sách / Tác giả</th>
                                    <th scope="col" style="width: 120px;">Giá bán</th>
                                    <th scope="col" style="width: 120px;">Số lượng</th>
                                    <th scope="col" style="width: 150px;">Tổng tiền</th>
                                    <th scope="col" style="width: 50px;">Xóa</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_price = 0;
                                foreach ($_SESSION['cart'] as $book_id => $qty): 
                                    $book = get_book_by_id($book_id);
                                    if (!$book) continue;
                                    
                                    $subtotal = $book['price'] * $qty;
                                    $total_price += $subtotal;
                                ?>
                                    <tr class="cart-row">
                                        <td>
                                            <a href="book-detail.php?id=<?php echo $book['id']; ?>">
                                                <img src="<?php echo htmlspecialchars($book['image']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="cart-book-img">
                                            </a>
                                        </td>
                                        <td>
                                            <h6 class="mb-1 text-white">
                                                <a href="book-detail.php?id=<?php echo $book['id']; ?>" class="text-decoration-none text-white hover-accent">
                                                    <?php echo htmlspecialchars($book['title']); ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted">Tác giả: <?php echo htmlspecialchars($book['author']); ?></small>
                                        </td>
                                        <td>
                                            <span><?php echo number_format($book['price'], 0, ',', '.'); ?> đ</span>
                                        </td>
                                        <td>
                                            <input type="number" id="qtyInput-<?php echo $book['id']; ?>" name="quantities[<?php echo $book['id']; ?>]" value="<?php echo $qty; ?>" min="1" max="100" class="cart-qty-input" onchange="this.form.submit()">
                                        </td>
                                        <td>
                                            <strong class="text-white"><?php echo number_format($subtotal, 0, ',', '.'); ?> đ</strong>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);" class="btn-remove btn-remove-cart" data-book-id="<?php echo $book['id']; ?>" title="Xóa khỏi giỏ hàng">
                                                <i class="far fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
                            <a href="index.php" class="btn btn-secondary-custom"><i class="fas fa-arrow-left me-2"></i>Tiếp tục mua sắm</a>
                        </div>
                    </div>
                </div>
                
                <!-- Hóa đơn tóm tắt -->
                <div class="col-lg-4">
                    <div class="glass-panel p-4">
                        <h4 class="text-white mb-4" style="font-family: var(--font-heading);">Tóm tắt đơn hàng</h4>
                        <div class="d-flex justify-content-between mb-3 text-muted">
                            <span>Tổng số lượng sách:</span>
                            <span class="cart-total-qty-display"><?php echo get_cart_count(); ?> cuốn</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 text-muted">
                            <span>Phí vận chuyển:</span>
                            <span class="text-success">Miễn phí</span>
                        </div>
                        <hr style="border-color: var(--glass-border);" class="my-4">
                        <div class="d-flex justify-content-between mb-4 align-items-center">
                            <span class="text-white fs-5">Thành tiền:</span>
                            <span class="text-warning fs-3 font-weight-700 cart-grand-total-display"><?php echo number_format($total_price, 0, ',', '.'); ?> đ</span>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-primary-custom w-100 py-3 text-center d-block">
                            Tiến hành thanh toán <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const removeButtons = document.querySelectorAll('.btn-remove-cart');
    removeButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const bookId = this.getAttribute('data-book-id');
            const row = this.closest('.cart-row');
            
            if (confirm('Bạn có chắc chắn muốn xóa cuốn sách này khỏi giỏ hàng không?')) {
                const formData = new FormData();
                formData.append('book_id', bookId);
                formData.append('quantity', 0); // 0 để xóa sản phẩm
                
                fetch('api/update_cart_ajax.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Hiệu ứng xóa mượt mà
                        row.style.transition = 'all 0.4s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            row.remove();
                            
                            // Cập nhật lại tổng tiền trên giao diện
                            const grandTotalEl = document.querySelector('.cart-grand-total-display');
                            if (grandTotalEl) {
                                grandTotalEl.innerText = data.total_price;
                            }
                            
                            // Cập nhật lại tổng số lượng sách trên giao diện
                            const totalQtyEl = document.querySelector('.cart-total-qty-display');
                            if (totalQtyEl) {
                                totalQtyEl.innerText = data.cart_count + ' cuốn';
                            }
                            
                            // Cập nhật giỏ hàng trên header nếu có class .cart-badge
                            const headerBadge = document.querySelector('.cart-badge');
                            if (headerBadge) {
                                headerBadge.innerText = data.cart_count;
                            }
                            
                            // Nếu giỏ hàng trống, hiển thị màn hình trống
                            if (data.cart_count === 0) {
                                const container = document.querySelector('.container.my-5');
                                if (container) {
                                    container.innerHTML = `
                                        <h2 class="section-title mb-4">Giỏ Hàng Của Bạn</h2>
                                        <div class="glass-panel text-center p-5">
                                            <i class="fas fa-shopping-cart text-muted fa-4x mb-3"></i>
                                            <h4 class="text-white">Giỏ hàng của bạn đang trống</h4>
                                            <p class="text-muted">Hãy lấp đầy nó bằng những cuốn sách hay từ cửa hàng của chúng tôi.</p>
                                            <a href="index.php" class="btn btn-primary-custom mt-3"><i class="fas fa-arrow-left me-2"></i>Tiếp tục mua sắm</a>
                                        </div>
                                    `;
                                }
                            }
                        }, 400);
                    } else {
                        showToast(data.message, 'danger');
                    }
                })
                .catch(err => {
                    console.error('Lỗi khi xóa sản phẩm:', err);
                    showToast('Có lỗi xảy ra trong quá trình kết nối máy chủ.', 'danger');
                });
            }
        });
    });
});
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
