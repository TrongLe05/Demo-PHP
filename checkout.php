<?php
require_once __DIR__ . '/db_helper.php';

// Yêu cầu đăng nhập trước khi thanh toán
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?status=login_required");
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$order_id_param = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($action === 'payos_success' && $order_id_param > 0) {
    // Cập nhật trạng thái đơn hàng sang Đã xác nhận
    update_order_status($order_id_param, 'Đã xác nhận');
    $success = true;
} elseif ($action === 'payos_cancel' && $order_id_param > 0) {
    // Hủy đơn hàng và hoàn kho
    cancel_order($order_id_param);
    $payos_cancelled = true;
} else {
    // Không cho phép truy cập nếu giỏ hàng trống và không phải là callback thanh toán
    if (empty($_SESSION['cart'])) {
        header("Location: index.php");
        exit;
    }
}

$errors = [];
if (!isset($success)) {
    $success = false;
}

// Đọc thông tin từ giỏ hàng
$cart_items = [];
$total_price = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
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
}

// Xử lý gửi đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? 'COD');
    $payos_paid_verified = trim($_POST['payos_paid_verified'] ?? '0');
    
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
        $payment_valid = true;
        if ($payment_method === 'PayOS') {
            if ($payos_paid_verified !== '1') {
                $payment_valid = false;
                $errors['global'] = 'Vui lòng hoàn tất thanh toán chuyển khoản PayOS trước khi đặt hàng.';
            } else {
                // Kiểm tra lại trên server cho an toàn
                $temp_order_code = isset($_POST['temp_order_code']) ? (int)$_POST['temp_order_code'] : 0;
                $payOS = new PayOSService(PAYOS_CLIENT_ID, PAYOS_API_KEY, PAYOS_CHECKSUM_KEY);
                if ($payOS->isConfigured() && $temp_order_code > 0) {
                    $paymentInfo = $payOS->getPaymentLinkInformation($temp_order_code);
                    if (!$paymentInfo || !isset($paymentInfo['status']) || $paymentInfo['status'] !== 'PAID') {
                        $payment_valid = false;
                        $errors['global'] = 'Hệ thống chưa ghi nhận thanh toán PayOS. Vui lòng thử lại.';
                    }
                }
            }
        }
        
        if ($payment_valid) {
            // Lưu đơn hàng vào CSDL MySQL thông qua helper
            $order_id = save_order($fullname, $phone, $address, $cart_items, $total_price, $payment_method);
            if ($order_id) {
                // Nếu là PayOS, cập nhật trạng thái đơn hàng thành Đã xác nhận vì đã thanh toán thành công
                if ($payment_method === 'PayOS') {
                    update_order_status($order_id, 'Đã xác nhận');
                }
                // Xóa giỏ hàng sau khi đặt hàng thành công
                unset($_SESSION['cart']);
                $success = true;
            } else {
                $errors['global'] = 'Lỗi hệ thống! Không thể đặt hàng vào lúc này. Vui lòng liên hệ quản trị viên.';
            }
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
            <p class="text-muted fs-5">Cảm ơn bạn đã mua hàng tại <strong>BookHaven</strong>. Đơn hàng của bạn đã được thanh toán và đang được chuẩn bị vận chuyển.</p>
            <div class="mt-4">
                <a href="index.php" class="btn btn-primary-custom"><i class="fas fa-shopping-basket me-2"></i>Tiếp tục mua sắm</a>
            </div>
        </div>
    <?php elseif (isset($payos_cancelled) && $payos_cancelled): ?>
        <div class="glass-panel text-center p-5 max-width-600 mx-auto">
            <i class="fas fa-times-circle text-danger fa-4x mb-3"></i>
            <h2 class="text-white mb-3" style="font-family: var(--font-heading);">Thanh Toán Bị Hủy</h2>
            <p class="text-muted fs-5">Bạn đã hủy thanh toán qua cổng PayOS. Đơn hàng #<?php echo $order_id_param; ?> đã được hủy tự động.</p>
            <div class="mt-4">
                <a href="index.php" class="btn btn-primary-custom"><i class="fas fa-shopping-basket me-2"></i>Tiếp tục mua sắm</a>
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
                    
                    <form action="checkout.php" method="POST" id="checkout-form">
                        <!-- Cấu hình tài khoản ngân hàng của cửa hàng từ .env -->
                        <input type="hidden" id="merchant-bank-id" value="<?php echo htmlspecialchars(getenv('MERCHANT_BANK_ID') ?: 'vietinbank'); ?>">
                        <input type="hidden" id="merchant-account-no" value="<?php echo htmlspecialchars(getenv('MERCHANT_ACCOUNT_NO') ?: '113366668888'); ?>">
                        <input type="hidden" id="merchant-account-name" value="<?php echo htmlspecialchars(getenv('MERCHANT_ACCOUNT_NAME') ?: 'BOOKHAVEN SHOP'); ?>">
                        <input type="hidden" id="temp-order-code" name="temp_order_code" value="">
                        <input type="hidden" id="payos-paid-verified" name="payos_paid_verified" value="0">

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
                            <textarea id="address" name="address" class="form-control-custom" rows="3" 
                                      placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố..." required><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                            <?php if (isset($errors['address'])): ?>
                                <span class="text-danger fs-7 d-block mt-1"><?php echo $errors['address']; ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Phương thức thanh toán -->
                        <div class="form-group-custom mt-4">
                            <label class="mb-3 d-block text-white" style="font-weight: 500;"><i class="fas fa-credit-card text-warning me-2"></i>Phương thức thanh toán *</label>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="payment-method-card glass-panel p-3 d-flex align-items-center gap-3 cursor-pointer active" data-value="COD">
                                        <input type="radio" name="payment_method" value="COD" class="d-none" checked>
                                        <div class="payment-icon bg-success bg-opacity-25 rounded-circle p-2 text-success" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-white mb-0" style="font-size: 0.95rem;">Thanh toán khi nhận</h6>
                                            <small class="text-muted" style="font-size: 0.8rem;">COD (Tiền mặt)</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="payment-method-card glass-panel p-3 d-flex align-items-center gap-3 cursor-pointer" data-value="PayOS">
                                        <input type="radio" name="payment_method" value="PayOS" class="d-none">
                                        <div class="payment-icon bg-danger bg-opacity-25 rounded-circle p-2 text-danger" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-wallet"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-white mb-0" style="font-size: 0.95rem;">Cổng PayOS</h6>
                                            <small class="text-muted" style="font-size: 0.8rem;">Thanh toán Online (VietQR)</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="payment-method-card glass-panel p-3 d-flex align-items-center gap-3 cursor-pointer" data-value="Thẻ tín dụng">
                                        <input type="radio" name="payment_method" value="Thẻ tín dụng" class="d-none">
                                        <div class="payment-icon bg-primary bg-opacity-25 rounded-circle p-2 text-primary" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                            <i class="far fa-credit-card"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-white mb-0" style="font-size: 0.95rem;">Thẻ tín dụng</h6>
                                            <small class="text-muted" style="font-size: 0.8rem;">Visa / Mastercard</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="payment-method-card glass-panel p-3 d-flex align-items-center gap-3 cursor-pointer" data-value="QR">
                                        <input type="radio" name="payment_method" value="QR" class="d-none">
                                        <div class="payment-icon bg-warning bg-opacity-25 rounded-circle p-2 text-warning" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-qrcode"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-white mb-0" style="font-size: 0.95rem;">Mã QR Ngân hàng</h6>
                                            <small class="text-muted" style="font-size: 0.8rem;">VietQR / Napas</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Panel Chi tiết Thanh toán Động -->
                        <div id="payment-details-COD" class="payment-details-panel glass-panel p-3 mt-3">
                            <p class="text-muted mb-0" style="font-size: 0.9rem;"><i class="fas fa-info-circle text-success me-2"></i>Bạn sẽ thanh toán bằng tiền mặt trực tiếp cho nhân viên giao hàng khi nhận được sách.</p>
                        </div>

                        <div id="payment-details-PayOS" class="payment-details-panel glass-panel p-3 mt-3 d-none text-center">
                            <p class="text-white mb-2" style="font-size: 0.9rem; font-weight: 600;"><i class="fas fa-wallet text-danger me-2"></i>Thanh toán qua Cổng PayOS</p>
                            <p class="text-muted mb-3" style="font-size: 0.85rem;">Quét mã VietQR dưới đây để thanh toán qua cổng PayOS</p>
                            
                            <div class="qr-mockup-wrapper bg-white p-3 rounded d-inline-block mb-2 position-relative" style="box-shadow: 0 4px 15px rgba(0,0,0,0.2); border: 2px solid #0052cc; min-width: 200px; min-height: 200px;">
                                <span class="badge bg-danger position-absolute" style="top: 8px; right: 8px; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; border-radius: 4px; z-index: 10;">payOS</span>
                                <div id="payos-qr-loading" class="position-absolute top-50 start-50 translate-middle d-none" style="z-index: 5;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Đang tải...</span>
                                    </div>
                                </div>
                                <img id="payos-qr-image" 
                                     src="" 
                                     alt="PayOS QR Payment" 
                                     style="max-width: 200px; height: auto; display: block; min-height: 150px;">
                            </div>
                            <p class="text-warning mb-2" style="font-size: 0.85rem;">Nội dung chuyển khoản: <strong id="payos-desc-text" class="text-white">BHAVEN PAYOS</strong></p>
                            <div class="mt-2 text-muted" style="font-size: 0.8rem; margin-bottom: 0.5rem;">
                                <i class="fas fa-shield-alt text-success me-1"></i> Giao dịch được bảo mật và xử lý tự động bởi PayOS
                            </div>
                            <div id="payos-payment-status-alert" class="alert alert-success mt-2 d-none" style="font-size: 0.9rem; border-radius: 8px;">
                                <i class="fas fa-check-circle me-1"></i> Thanh toán thành công qua PayOS! Đang hoàn tất đơn hàng...
                            </div>
                        </div>

                        <div id="payment-details-QR" class="payment-details-panel glass-panel p-3 mt-3 d-none text-center">
                            <p class="text-white mb-2" style="font-size: 0.9rem;">Quét mã VietQR bằng ứng dụng Ngân hàng (Mobile Banking) để thanh toán</p>
                            <div class="qr-mockup-wrapper bg-white p-3 rounded d-inline-block mb-2" style="box-shadow: 0 4px 15px rgba(0,0,0,0.15);">
                                <img id="vietqr-image" 
                                     src="https://img.vietqr.io/image/<?php echo htmlspecialchars(getenv('MERCHANT_BANK_ID') ?: 'vietinbank'); ?>-<?php echo htmlspecialchars(getenv('MERCHANT_ACCOUNT_NO') ?: '113366668888'); ?>-compact2.png?amount=<?php echo $total_price; ?>&addInfo=BHAVEN%20QR%20<?php echo time(); ?>&accountName=<?php echo urlencode(getenv('MERCHANT_ACCOUNT_NAME') ?: 'BOOKHAVEN SHOP'); ?>" 
                                     alt="VietQR Payment" 
                                     style="max-width: 200px; height: auto; display: block;"
                                     data-order-time="<?php echo time(); ?>">
                            </div>
                            <p class="text-warning mb-0" style="font-size: 0.85rem;">Nội dung chuyển khoản: <strong id="vietqr-desc-text" class="text-white">BHAVEN QR <?php echo time(); ?></strong></p>
                        </div>

                        <div id="payment-details-Thẻ tín dụng" class="payment-details-panel glass-panel p-3 mt-3 d-none">
                            <div class="row align-items-center">
                                <div class="col-md-7">
                                    <div class="form-group-custom mb-2">
                                        <label for="card_number" style="font-size: 0.8rem;">Số thẻ</label>
                                        <input type="text" id="card_number" class="form-control-custom py-1 px-2" placeholder="4111 2222 3333 4444" maxlength="19">
                                    </div>
                                    <div class="form-group-custom mb-2">
                                        <label for="card_name" style="font-size: 0.8rem;">Tên chủ thẻ</label>
                                        <input type="text" id="card_name" class="form-control-custom py-1 px-2" placeholder="NGUYEN VAN A" oninput="this.value = this.value.toUpperCase()">
                                    </div>
                                    <div class="row">
                                        <div class="col-6 form-group-custom mb-0">
                                            <label for="card_expiry" style="font-size: 0.8rem;">Hết hạn</label>
                                            <input type="text" id="card_expiry" class="form-control-custom py-1 px-2" placeholder="MM/YY" maxlength="5">
                                        </div>
                                        <div class="col-6 form-group-custom mb-0">
                                            <label for="card_cvv" style="font-size: 0.8rem;">Mã CVV</label>
                                            <input type="password" id="card_cvv" class="form-control-custom py-1 px-2" placeholder="***" maxlength="3">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5 mt-3 mt-md-0 d-flex justify-content-center">
                                    <div class="credit-card-mockup p-3 rounded text-white position-relative overflow-hidden w-100" style="height: 140px; max-width: 240px; background: linear-gradient(135deg, #6366f1 0%, #1e1b4b 100%); border: 1px solid rgba(255,255,255,0.15); box-shadow: 0 8px 20px rgba(0,0,0,0.5);">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <i class="fas fa-sim-card fa-lg text-warning"></i>
                                            <i class="fab fa-cc-visa fa-lg text-white"></i>
                                        </div>
                                        <div class="card-number-display mb-3" style="font-family: monospace; letter-spacing: 1px; font-size: 0.85rem;">•••• •••• •••• ••••</div>
                                        <div class="d-flex justify-content-between align-items-end">
                                            <div>
                                                <small class="text-muted d-block uppercase" style="font-size: 0.55rem; line-height: 1;">Chủ thẻ</small>
                                                <span class="card-name-display text-white" style="font-size: 0.75rem; font-family: monospace;">NGUYEN VAN A</span>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted d-block uppercase" style="font-size: 0.55rem; line-height: 1;">Hết hạn</small>
                                                <span class="card-expiry-display text-white" style="font-size: 0.75rem; font-family: monospace;">MM/YY</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary-custom w-100 py-3 mt-4">
                            <i class="fas fa-check-double me-2"></i> Xác nhận đặt hàng
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Tóm tắt sản phẩm -->
            <div class="col-lg-5">
                <div class="glass-panel p-4">
                    <h4 class="text-white mb-4" style="font-family: var(--font-heading);"><i class="fas fa-receipt text-warning me-2"></i>Chi tiết đơn hàng</h4>
                    
                    <div class="checkout-items mb-4" style="max-height: 350px; overflow-y: auto;">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom cart-item-row" data-book-id="<?php echo $item['book_id']; ?>" style="border-color: var(--glass-border) !important;">
                                <div style="max-width: 65%;">
                                    <h6 class="text-white mb-1" style="font-size: 0.95rem; font-weight: 500;"><?php echo htmlspecialchars($item['title']); ?></h6>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <!-- Interactive Quantity Adjustment -->
                                        <button type="button" class="btn btn-sm btn-outline-light py-0 px-2 qty-btn minus-btn" style="border-radius: 4px; line-height: 1; font-weight: bold; background: rgba(255,255,255,0.05); border-color: var(--glass-border);">-</button>
                                        <input type="text" class="qty-input text-center text-white" value="<?php echo $item['quantity']; ?>" data-price="<?php echo $item['price']; ?>" readonly style="width: 32px; height: 24px; background: rgba(255,255,255,0.08); border: 1px solid var(--glass-border); border-radius: 4px; font-size: 0.8rem; font-weight: 600;">
                                        <button type="button" class="btn btn-sm btn-outline-light py-0 px-2 qty-btn plus-btn" style="border-radius: 4px; line-height: 1; font-weight: bold; background: rgba(255,255,255,0.05); border-color: var(--glass-border);">+</button>
                                        <small class="text-muted ms-1">x <?php echo number_format($item['price'], 0, ',', '.'); ?>đ</small>
                                    </div>
                                </div>
                                <span class="text-white item-total-display" style="font-weight: 500; font-size: 0.95rem;"><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> đ</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <hr style="border-color: var(--glass-border);" class="my-4">
                    
                    <div class="d-flex justify-content-between mb-3 text-muted">
                        <span>Phí giao hàng:</span>
                        <span class="text-success" style="font-weight: 500;">Miễn phí</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-white fs-5 font-weight-500">Tổng thanh toán:</span>
                        <span id="checkout-grand-total" class="text-warning fs-3 font-weight-700"><?php echo number_format($total_price, 0, ',', '.'); ?> đ</span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Tương tác số lượng bằng AJAX
    const cartItemRows = document.querySelectorAll('.cart-item-row');
    cartItemRows.forEach(row => {
        const bookId = row.getAttribute('data-book-id');
        const minusBtn = row.querySelector('.minus-btn');
        const plusBtn = row.querySelector('.plus-btn');
        const qtyInput = row.querySelector('.qty-input');
        const itemTotalDisplay = row.querySelector('.item-total-display');
        
        const submitBtn = document.querySelector('button[type="submit"]');
        const updateQty = function(newQty) {
            if (newQty < 1) {
                if (confirm('Bạn có muốn xóa sách này khỏi đơn hàng không?')) {
                    newQty = 0;
                } else {
                    return;
                }
            }
            
            // Khóa nút submit và đổi trạng thái chờ
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Đang cập nhật giỏ hàng...';
            }
            
            const formData = new FormData();
            formData.append('book_id', bookId);
            formData.append('quantity', newQty);
            
            fetch('update_cart_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (newQty === 0) {
                        row.style.transition = 'all 0.3s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(20px)';
                        setTimeout(() => {
                            row.remove();
                            if (document.querySelectorAll('.cart-item-row').length === 0) {
                                window.location.href = 'index.php';
                            }
                        }, 300);
                    } else {
                        qtyInput.value = newQty;
                        itemTotalDisplay.innerText = data.item_subtotal;
                    }
                    
                    document.getElementById('checkout-grand-total').innerText = data.total_price;
                    
                    // Cập nhật VietQR image nếu có
                    const qrImg = document.getElementById('vietqr-image');
                    if (qrImg && data.total_price_raw) {
                        const amount = data.total_price_raw;
                        const orderTime = qrImg.getAttribute('data-order-time');
                        const merchantBank = document.getElementById('merchant-bank-id')?.value || 'vietinbank';
                        const merchantAccount = document.getElementById('merchant-account-no')?.value || '113366668888';
                        const merchantName = document.getElementById('merchant-account-name')?.value || 'BOOKHAVEN SHOP';
                        
                        qrImg.src = `https://img.vietqr.io/image/${merchantBank}-${merchantAccount}-compact2.png?amount=${amount}&addInfo=BHAVEN%20QR%20${orderTime}&accountName=${encodeURIComponent(merchantName)}`;
                    }
                    
                    // Nếu đang ở phương thức PayOS, tải lại QR để cập nhật số tiền mới
                    const selectedPayment = document.querySelector('input[name="payment_method"]:checked')?.value;
                    if (selectedPayment === 'PayOS') {
                        loadPayOSQr();
                    }
                    
                    // Cập nhật giỏ hàng trên header
                    const badge = document.querySelector('.cart-badge');
                    if (badge) {
                        badge.innerText = data.cart_count;
                    }
                } else {
                    alert(data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Có lỗi xảy ra khi cập nhật số lượng.');
            })
            .finally(() => {
                // Mở khóa nút submit
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-check-double me-2"></i> Xác nhận đặt hàng';
                }
            });
        };
        
        minusBtn.addEventListener('click', function() {
            const currentQty = parseInt(qtyInput.value);
            updateQty(currentQty - 1);
        });
        
        plusBtn.addEventListener('click', function() {
            const currentQty = parseInt(qtyInput.value);
            updateQty(currentQty + 1);
        });
    });

    // 2. Xử lý thanh toán PayOS (Hiển thị QR phía dưới & Polling ngầm)
    const paymentCards = document.querySelectorAll('.payment-method-card');
    let payosPollInterval = null;
    let isQrLoading = false;

    function startPayOSPolling(orderCode) {
        if (payosPollInterval) clearInterval(payosPollInterval);
        
        payosPollInterval = setInterval(function() {
            fetch(`check_payos_status.php?order_code=${orderCode}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.status === 'PAID') {
                    clearInterval(payosPollInterval);
                    
                    // Hiển thị thông báo thanh toán thành công
                    const alertBox = document.getElementById('payos-payment-status-alert');
                    if (alertBox) {
                        alertBox.classList.remove('d-none');
                    }
                    
                    // Tự động gửi form sau 1.5 giây
                    setTimeout(function() {
                        const checkoutForm = document.getElementById('checkout-form');
                        if (checkoutForm) {
                            // Ghi nhận thanh toán thành công vào input ẩn
                            const paidInput = document.getElementById('payos-paid-verified');
                            if (paidInput) paidInput.value = '1';
                            checkoutForm.submit();
                        }
                    }, 1500);
                }
            })
            .catch(err => console.error('Lỗi check status:', err));
        }, 3000);
    }

    function stopPayOSPolling() {
        if (payosPollInterval) {
            clearInterval(payosPollInterval);
            payosPollInterval = null;
        }
    }

    function loadPayOSQr() {
        const payosQrImg = document.getElementById('payos-qr-image');
        const payosDescText = document.getElementById('payos-desc-text');
        const spinner = document.getElementById('payos-qr-loading');
        const tempOrderInput = document.getElementById('temp-order-code');
        const alertBox = document.getElementById('payos-payment-status-alert');
        
        if (!payosQrImg) return;
        
        isQrLoading = true;
        if (spinner) spinner.classList.remove('d-none');
        if (alertBox) {
            alertBox.classList.add('d-none');
            alertBox.className = 'alert alert-success mt-2'; // Reset to default success class
        }
        payosQrImg.style.opacity = '0.3';
        payosQrImg.src = '';
        
        const grandTotalText = document.getElementById('checkout-grand-total').innerText;
        const amount = grandTotalText.replace(/[^0-9]/g, '');
        
        const formData = new FormData();
        formData.append('amount', amount);
        
        fetch('get_payos_payment_info.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.qrCode) {
                    // Sử dụng chuỗi EMVCo của PayOS để tạo QR thật chính xác
                    payosQrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(data.qrCode)}`;
                } else {
                    // Fallback nếu có
                    payosQrImg.src = `https://img.vietqr.io/image/${data.bin}-${data.accountNumber}-compact2.png?amount=${data.amount}&addInfo=${encodeURIComponent(data.description)}&accountName=${encodeURIComponent(data.accountName)}`;
                }
                
                if (payosDescText) {
                    payosDescText.innerText = data.description;
                }
                if (tempOrderInput) {
                    tempOrderInput.value = data.tempOrderCode;
                }
                
                // Bắt đầu polling trong nền
                startPayOSPolling(data.tempOrderCode);
            } else {
                // Hiển thị lỗi cấu hình hoặc tạo link từ backend
                if (alertBox) {
                    alertBox.className = 'alert alert-danger mt-2';
                    alertBox.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i> ${data.message || 'Lỗi tạo liên kết thanh toán từ PayOS.'}`;
                    alertBox.classList.remove('d-none');
                }
                payosQrImg.alt = data.message || 'Lỗi tải mã QR';
            }
        })
        .catch(err => {
            console.error('Lỗi khi fetch PayOS QR:', err);
            if (alertBox) {
                alertBox.className = 'alert alert-danger mt-2';
                alertBox.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i> Không thể kết nối với cổng thanh toán PayOS. Vui lòng thử lại sau.`;
                alertBox.classList.remove('d-none');
            }
            payosQrImg.alt = 'Lỗi kết nối';
        })
        .finally(() => {
            isQrLoading = false;
            if (spinner) spinner.classList.add('d-none');
            payosQrImg.style.opacity = '1';
        });
    }

    paymentCards.forEach(card => {
        card.addEventListener('click', function() {
            paymentCards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
            
            document.querySelectorAll('.payment-details-panel').forEach(panel => {
                panel.classList.add('d-none');
            });
            
            const selectedVal = radio.value;
            const panel = document.getElementById('payment-details-' + selectedVal);
            if (panel) {
                panel.classList.remove('d-none');
            }

            if (selectedVal === 'PayOS') {
                loadPayOSQr();
            } else {
                stopPayOSPolling();
            }
        });
    });

    // Tự động load QR và polling nếu PayOS được chọn mặc định lúc khởi chạy
    const initialPayment = document.querySelector('input[name="payment_method"]:checked')?.value;
    if (initialPayment === 'PayOS') {
        loadPayOSQr();
    }

    // 3. Hiển thị thông tin Thẻ tín dụng mô phỏng thời gian thực
    const cardNumberInput = document.getElementById('card_number');
    const cardNameInput = document.getElementById('card_name');
    const cardExpiryInput = document.getElementById('card_expiry');
    
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = '';
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }
            e.target.value = formattedValue;
            
            const display = document.querySelector('.card-number-display');
            display.innerText = formattedValue || '•••• •••• •••• ••••';
        });
    }

    if (cardNameInput) {
        cardNameInput.addEventListener('input', function(e) {
            const display = document.querySelector('.card-name-display');
            display.innerText = e.target.value.toUpperCase() || 'NGUYEN VAN A';
        });
    }

    if (cardExpiryInput) {
        cardExpiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            if (value.length > 2) {
                value = value.substr(0, 2) + '/' + value.substr(2, 2);
            }
            e.target.value = value;
            
            const display = document.querySelector('.card-expiry-display');
            display.innerText = value || 'MM/YY';
        });
    }

    // 4. Kiểm thử submit form: Nếu chọn PayOS nhưng chưa thanh toán -> chặn submit và báo lỗi
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const selectedPayment = document.querySelector('input[name="payment_method"]:checked').value;
            if (selectedPayment === 'PayOS') {
                const paidVerified = document.getElementById('payos-paid-verified')?.value;
                if (paidVerified !== '1') {
                    alert('Vui lòng quét mã QR PayOS ở phía dưới, thực hiện chuyển tiền và đợi hệ thống báo "Thanh toán thành công" trước khi xác nhận đơn hàng.');
                    e.preventDefault();
                }
            }
        });
    }
});
</script>

<?php
require_once __DIR__ . '/footer.php';
?>
