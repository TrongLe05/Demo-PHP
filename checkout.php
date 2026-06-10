<?php
require_once __DIR__ . '/db_helper.php';

// Không cho phép truy cập nếu giỏ hàng trống
if (empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit;
}

$errors = [];
$success = false;

// Đọc thông tin từ giỏ hàng
$cart_items = [];
$total_price = 0;
foreach ($_SESSION['cart'] as $book_id => $qty) {
    $book = get_book_by_id($book_id);
    if ($book) {
        $cart_items[] = [
            'book_id' => $book['id'],
            'title' => $book['title'],
            'price' => $book['price'],
            'quantity' => $qty
        ];
        $total_price += $book['price'] * $qty;
    }
}

// Xử lý gửi đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    if (empty($fullname)) {
        $errors['fullname'] = 'Họ tên người nhận không được để trống.';
    }
    if (empty($phone)) {
        $errors['phone'] = 'Số điện thoại nhận hàng không được để trống.';
    } elseif (!preg_match('/^[0-9]{9,11}$/', $phone)) {
        $errors['phone'] = 'Số điện thoại không hợp lệ (9 đến 11 chữ số).';
    }
    if (empty($address)) {
        $errors['address'] = 'Địa chỉ giao hàng không được để trống.';
    }
    
    if (empty($errors)) {
        // Lưu đơn hàng vào CSDL MySQL thông qua helper
        if (save_order($fullname, $phone, $address, $cart_items, $total_price)) {
            // Xóa giỏ hàng sau khi đặt hàng thành công
            unset($_SESSION['cart']);
            $success = true;
        } else {
            $errors['global'] = 'Lỗi hệ thống! Không thể đặt hàng vào lúc này. Vui lòng liên hệ quản trị viên.';
        }
    }
}

require_once __DIR__ . '/header.php';
?>

<div class="container my-5">
    <?php if ($success): ?>
        <div class="glass-panel text-center p-5 max-width-600 mx-auto">
            <i class="fas fa-heart text-warning fa-4x mb-3"></i>
            <h2 class="text-white mb-3" style="font-family: var(--font-heading);">Đặt Hàng Thành Công!</h2>
            <p class="text-muted fs-5">Cảm ơn bạn đã mua hàng tại <strong>BookHaven</strong>. Đơn hàng của bạn đang được xử lý và chuẩn bị vận chuyển.</p>
            <div class="mt-4">
                <a href="index.php" class="btn btn-primary-custom"><i class="fas fa-home me-2"></i>Về trang chủ mua thêm</a>
            </div>
        </div>
    <?php else: ?>
        <h2 class="section-title mb-4">Thanh Toán Đơn Hàng</h2>
        
        <?php if (isset($errors['global'])): ?>
            <div class="alert alert-custom alert-danger-custom d-flex align-items-center gap-2 mb-4">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $errors['global']; ?></span>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Form thông tin giao hàng -->
            <div class="col-lg-7 mb-4 mb-lg-0">
                <div class="glass-panel p-4">
                    <h4 class="text-white mb-4" style="font-family: var(--font-heading);"><i class="fas fa-shipping-fast text-warning me-2"></i>Thông tin nhận hàng</h4>
                    
                    <form action="checkout.php" method="POST">
                        <div class="form-group-custom">
                            <label for="fullname">Họ và tên người nhận *</label>
                            <input type="text" id="fullname" name="fullname" class="form-control-custom" 
                                   value="<?php echo htmlspecialchars($fullname ?? $_SESSION['user_name'] ?? ''); ?>" required>
                            <?php if (isset($errors['fullname'])): ?>
                                <span class="text-danger fs-7 d-block mt-1"><?php echo $errors['fullname']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group-custom">
                            <label for="phone">Số điện thoại *</label>
                            <input type="text" id="phone" name="phone" class="form-control-custom" 
                                   placeholder="Ví dụ: 0912345678" value="<?php echo htmlspecialchars($phone ?? ''); ?>" required>
                            <?php if (isset($errors['phone'])): ?>
                                <span class="text-danger fs-7 d-block mt-1"><?php echo $errors['phone']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group-custom">
                            <label for="address">Địa chỉ nhận hàng cụ thể *</label>
                            <textarea id="address" name="address" class="form-control-custom" rows="4" 
                                      placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố..." required><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                            <?php if (isset($errors['address'])): ?>
                                <span class="text-danger fs-7 d-block mt-1"><?php echo $errors['address']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <button type="submit" class="btn btn-primary-custom w-100 py-3 mt-3">
                            <i class="fas fa-check-double me-2"></i> Xác nhận đặt hàng
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Tóm tắt sản phẩm -->
            <div class="col-lg-5">
                <div class="glass-panel p-4">
                    <h4 class="text-white mb-4" style="font-family: var(--font-heading);"><i class="fas fa-receipt text-warning me-2"></i>Chi tiết đơn hàng</h4>
                    
                    <div class="checkout-items mb-4" style="max-height: 300px; overflow-y: auto;">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div style="max-width: 70%;">
                                    <h6 class="text-white mb-0"><?php echo htmlspecialchars($item['title']); ?></h6>
                                    <small class="text-muted">Số lượng: <?php echo $item['quantity']; ?> x <?php echo number_format($item['price'], 0, ',', '.'); ?> đ</small>
                                </div>
                                <span class="text-white"><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> đ</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <hr style="border-color: var(--glass-border);" class="my-4">
                    
                    <div class="d-flex justify-content-between mb-3 text-muted">
                        <span>Phí giao hàng:</span>
                        <span class="text-success">Miễn phí</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-white fs-5 font-weight-500">Tổng thanh toán:</span>
                        <span class="text-warning fs-3 font-weight-700"><?php echo number_format($total_price, 0, ',', '.'); ?> đ</span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/footer.php';
?>
