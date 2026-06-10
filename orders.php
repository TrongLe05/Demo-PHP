<?php
require_once __DIR__ . '/db_helper.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?status=unauthorized");
    exit;
}

$user_id = $_SESSION['user_id'];

// Xử lý Khách hàng tự hủy đơn hàng
if (isset($_POST['cancel_order_id'])) {
    $cancel_id = (int)$_POST['cancel_order_id'];
    
    // Kiểm tra đơn hàng thuộc quyền sở hữu của user này và ở trạng thái hợp lệ
    global $pdo;
    $stmt = $pdo->prepare("SELECT user_id, status FROM orders WHERE id = :id");
    $stmt->execute(['id' => $cancel_id]);
    $order_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order_data && (int)$order_data['user_id'] === (int)$user_id) {
        if ($order_data['status'] === 'Chờ xác nhận' || $order_data['status'] === 'Đã xác nhận') {
            if (cancel_order($cancel_id)) {
                $success_message = "Đã hủy đơn hàng #{$cancel_id} thành công! Số lượng sách trong kho đã được hoàn lại.";
            } else {
                $error_message = "Lỗi hệ thống! Không thể hủy đơn hàng vào lúc này.";
            }
        } else {
            $error_message = "Bạn chỉ có thể hủy đơn hàng khi trạng thái là 'Chờ xác nhận' hoặc 'Đã xác nhận'.";
        }
    } else {
        $error_message = "Đơn hàng không hợp lệ hoặc không thuộc quyền sở hữu của bạn.";
    }
}

$orders = get_orders_by_user($user_id);

require_once __DIR__ . '/header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h2 class="section-title mb-0"><i class="fas fa-box-open text-warning me-2"></i>Đơn hàng của tôi</h2>
        <a href="index.php" class="btn btn-secondary-custom btn-sm"><i class="fas fa-shopping-basket me-2"></i>Tiếp tục mua sách</a>
    </div>

    <!-- Thông báo kết quả hủy -->
    <?php if (isset($success_message)): ?>
        <div class="alert alert-custom alert-success-custom d-flex align-items-center gap-2 mb-4">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $success_message; ?></span>
        </div>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <div class="alert alert-custom alert-danger-custom d-flex align-items-center gap-2 mb-4">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $error_message; ?></span>
        </div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="glass-panel text-center p-5">
            <i class="fas fa-receipt text-muted fa-4x mb-3"></i>
            <h4 class="text-white">Bạn chưa đặt đơn hàng nào</h4>
            <p class="text-muted">Hãy lựa chọn cho mình những cuốn sách ưng ý nhất và tiến hành thanh toán nhé.</p>
            <a href="index.php" class="btn btn-primary-custom mt-3"><i class="fas fa-home me-2"></i>Xem danh sách sách</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-12">
                <?php foreach ($orders as $order): 
                    // Định nghĩa màu sắc cho badge trạng thái
                    $status_style = 'background: rgba(255, 255, 255, 0.05); color: #94a3b8; border: 1px solid rgba(255, 255, 255, 0.15);';
                    if ($order['status'] === 'Chờ xác nhận') {
                        $status_style = 'background: rgba(255, 183, 3, 0.1); color: #ffb703; border: 1px solid rgba(255, 183, 3, 0.25);';
                    } elseif ($order['status'] === 'Đã xác nhận') {
                        $status_style = 'background: rgba(99, 102, 241, 0.15); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.3);';
                    } elseif ($order['status'] === 'Đang giao') {
                        $status_style = 'background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3);';
                    } elseif ($order['status'] === 'Đã giao') {
                        $status_style = 'background: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.25);';
                    } elseif ($order['status'] === 'Đã hủy') {
                        $status_style = 'background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.25);';
                    }

                    // Định nghĩa icon cho phương thức thanh toán
                    $pm_icon = 'fa-money-bill-wave text-success';
                    if ($order['payment_method'] === 'Ví điện tử') {
                        $pm_icon = 'fa-wallet text-info';
                    } elseif ($order['payment_method'] === 'Thẻ tín dụng') {
                        $pm_icon = 'fa-credit-card text-primary';
                    } elseif ($order['payment_method'] === 'QR') {
                        $pm_icon = 'fa-qrcode text-warning';
                    }
                ?>
                    <div class="glass-panel p-4 mb-4" style="transition: var(--transition-smooth); border: 1px solid var(--glass-border);">
                        <!-- Header đơn hàng -->
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom flex-wrap gap-2" style="border-color: var(--glass-border) !important;">
                            <div>
                                <h5 class="text-white mb-1">Mã đơn hàng: <span class="text-warning">#<?php echo $order['id']; ?></span></h5>
                                <span class="text-muted" style="font-size: 0.85rem;"><i class="far fa-clock me-1"></i> Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div>
                                    <span class="text-muted" style="font-size: 0.85rem;">Trạng thái:</span>
                                    <span class="badge px-3 py-2 ms-1" style="<?php echo $status_style; ?>; font-size: 0.85rem; font-weight: 500; border-radius: 50px;">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Thông tin người nhận và Thanh toán -->
                        <div class="row mb-3 g-3">
                            <div class="col-md-4">
                                <span class="text-muted d-block" style="font-size: 0.85rem;">Thông tin nhận hàng:</span>
                                <strong class="text-white d-block mt-1"><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                                <span class="text-muted" style="font-size: 0.9rem;"><?php echo htmlspecialchars($order['customer_phone']); ?></span>
                            </div>
                            <div class="col-md-5">
                                <span class="text-muted d-block" style="font-size: 0.85rem;">Địa chỉ giao hàng:</span>
                                <span class="text-white d-block mt-1" style="font-size: 0.9rem;"><?php echo htmlspecialchars($order['customer_address']); ?></span>
                            </div>
                            <div class="col-md-3 text-md-end">
                                <span class="text-muted d-block" style="font-size: 0.85rem;">Hình thức thanh toán:</span>
                                <span class="text-white d-inline-block mt-1" style="font-size: 0.9rem;">
                                    <i class="fas <?php echo $pm_icon; ?> me-2"></i><?php echo htmlspecialchars($order['payment_method']); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Chi tiết các mặt hàng đã mua -->
                        <div class="bg-black bg-opacity-20 p-3 rounded" style="border: 1px solid rgba(255, 255, 255, 0.03);">
                            <div class="table-responsive">
                                <table class="table table-borderless text-white align-middle mb-0">
                                    <thead>
                                        <tr class="text-muted" style="font-size: 0.8rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                                            <th scope="col" style="width: 70px;">Bìa sách</th>
                                            <th scope="col">Tên sách</th>
                                            <th scope="col" class="text-center" style="width: 100px;">Số lượng</th>
                                            <th scope="col" class="text-end" style="width: 150px;">Đơn giá</th>
                                            <th scope="col" class="text-end" style="width: 150px;">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order['items'] as $item): ?>
                                            <tr style="font-size: 0.9rem;">
                                                <td>
                                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" style="width: 40px; height: 55px; object-fit: cover; border-radius: 4px; border: 1px solid var(--glass-border);" alt="">
                                                </td>
                                                <td>
                                                    <strong class="text-white"><?php echo htmlspecialchars($item['title']); ?></strong>
                                                </td>
                                                <td class="text-center"><?php echo $item['quantity']; ?></td>
                                                <td class="text-end text-muted"><?php echo number_format($item['price'], 0, ',', '.'); ?> đ</td>
                                                <td class="text-end text-white font-weight-500"><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> đ</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tổng cộng đơn hàng và nút Hủy đơn -->
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 flex-wrap gap-2">
                            <div>
                                <?php if ($order['status'] === 'Chờ xác nhận' || $order['status'] === 'Đã xác nhận'): ?>
                                    <form action="orders.php" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này không? Sách sẽ được hoàn trả vào kho.');" class="d-inline">
                                        <input type="hidden" name="cancel_order_id" value="<?php echo $order['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger px-3 py-1" style="border-radius: 8px; font-weight: 500;">
                                            <i class="fas fa-times me-1"></i> Hủy đơn hàng
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted" style="font-size: 0.9rem;">Tổng thanh toán:</span>
                                <h4 class="text-warning font-weight-700 mb-0"><?php echo number_format($order['total_price'], 0, ',', '.'); ?> đ</h4>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/footer.php';
?>
