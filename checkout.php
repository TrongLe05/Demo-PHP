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
    $payment_method = trim($_POST['payment_method'] ?? 'COD');
    
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
        if (save_order($fullname, $phone, $address, $cart_items, $total_price, $payment_method)) {
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
                    
                    <form action="checkout.php" method="POST" id="checkout-form">
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
                                    <div class="payment-method-card glass-panel p-3 d-flex align-items-center gap-3 cursor-pointer" data-value="Ví điện tử">
                                        <input type="radio" name="payment_method" value="Ví điện tử" class="d-none">
                                        <div class="payment-icon bg-info bg-opacity-25 rounded-circle p-2 text-info" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-wallet"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-white mb-0" style="font-size: 0.95rem;">Ví điện tử</h6>
                                            <small class="text-muted" style="font-size: 0.8rem;">MoMo / ZaloPay</small>
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

                        <div id="payment-details-Ví điện tử" class="payment-details-panel glass-panel p-3 mt-3 d-none text-center">
                            <p class="text-white mb-2" style="font-size: 0.9rem;">Quét mã QR sau để thanh toán bằng MoMo hoặc ZaloPay</p>
                            <div class="qr-mockup-wrapper bg-white p-2 rounded d-inline-block mb-2">
                                <svg width="120" height="120" viewBox="0 0 29 29" fill="black">
                                    <path d="M0 0h7v7H0zm1 1v5h5V1zm21-1h7v7h-7zm1 1v5h5V1zM0 22h7v7H0zm1 1v5h5V23zm10-22h7v7h-7zm1 1v5h5V1zm-2 9h3v3h-3zm5 0h3v3h-3zm-5 5h3v3h-3zm5 0h3v3h-3zm-10-5h3v3H0zm10 5h3v3h-3zm12-5h3v3h-3zm0 5h3v3h-3zm-7 7h3v3h-3zm5 0h3v3h-3zm5 0h3v3h-3zm-15-2h2v2h-2zm4 0h2v2h-2zm6 0h2v2h-2zm4 0h2v2h-2zm-12 4h2v2h-2zm4 0h2v2h-2zm6 0h2v2h-2z" />
                                </svg>
                            </div>
                            <p class="text-warning mb-0" style="font-size: 0.85rem;">Nội dung chuyển khoản: <strong class="text-white">BHAVEN MOMO <?php echo time(); ?></strong></p>
                        </div>

                        <div id="payment-details-QR" class="payment-details-panel glass-panel p-3 mt-3 d-none text-center">
                            <p class="text-white mb-2" style="font-size: 0.9rem;">Quét mã VietQR bằng ứng dụng Ngân hàng (Mobile Banking) để thanh toán</p>
                            <div class="qr-mockup-wrapper bg-white p-2 rounded d-inline-block mb-2">
                                <svg width="120" height="120" viewBox="0 0 29 29" fill="black">
                                    <path d="M0 0h7v7H0zm1 1v5h5V1zm21-1h7v7h-7zm1 1v5h5V1zM0 22h7v7H0zm1 1v5h5V23zm10-22h7v7h-7zm1 1v5h5V1zm-2 9h3v3h-3zm5 0h3v3h-3zm-5 5h3v3h-3zm5 0h3v3h-3zm-10-5h3v3H0zm10 5h3v3h-3zm12-5h3v3h-3zm0 5h3v3h-3zm-7 7h3v3h-3zm5 0h3v3h-3zm5 0h3v3h-3zm-15-2h2v2h-2zm4 0h2v2h-2zm6 0h2v2h-2zm4 0h2v2h-2zm-12 4h2v2h-2zm4 0h2v2h-2zm6 0h2v2h-2z" />
                                </svg>
                            </div>
                            <p class="text-warning mb-0" style="font-size: 0.85rem;">Nội dung chuyển khoản: <strong class="text-white">BHAVEN QR <?php echo time(); ?></strong></p>
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
        
        const updateQty = function(newQty) {
            if (newQty < 1) {
                if (confirm('Bạn có muốn xóa sách này khỏi đơn hàng không?')) {
                    newQty = 0;
                } else {
                    return;
                }
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

    // 2. Chuyển đổi qua lại giữa các phương thức thanh toán
    const paymentCards = document.querySelectorAll('.payment-method-card');
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
        });
    });

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
});
</script>

<?php
require_once __DIR__ . '/footer.php';
?>
