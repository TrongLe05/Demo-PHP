<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/db_helper.php';

// Yêu cầu đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Bạn cần đăng nhập trước khi thực hiện thao tác này.'
    ]);
    exit;
}

$amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
if ($amount <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Số tiền thanh toán không hợp lệ.'
    ]);
    exit;
}

// Tạo mã đơn hàng tạm thời cho PayOS (phải là số nguyên)
$tempOrderCode = time();

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$dir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$base_url = $protocol . $host . $dir;

$returnUrl = $base_url . "/checkout.php?action=payos_success&order_id=" . $tempOrderCode;
$cancelUrl = $base_url . "/checkout.php?action=payos_cancel&order_id=" . $tempOrderCode;

$payOS = new PayOSService(PAYOS_CLIENT_ID, PAYOS_API_KEY, PAYOS_CHECKSUM_KEY);

if ($payOS->isConfigured()) {
    $next_order_id = get_next_order_id();
    // Gọi API PayOS để lấy thông tin tài khoản thật
    $payosData = $payOS->createPaymentLinkDetails($tempOrderCode, $amount, "Thanh toan don hang " . $next_order_id, $returnUrl, $cancelUrl);
    if ($payosData) {
        echo json_encode([
            'success' => true,
            'is_mock' => false,
            'bin' => $payosData['bin'],
            'accountNumber' => $payosData['accountNumber'],
            'accountName' => $payosData['accountName'],
            'amount' => $payosData['amount'],
            'description' => $payosData['description'],
            'displayDescription' => 'Thanh toan don hang #' . $next_order_id,
            'qrCode' => $payosData['qrCode'] ?? null, // Trả về qrCode để sinh QR chính xác
            'tempOrderCode' => $tempOrderCode
        ]);
        exit;
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Không thể tạo liên kết thanh toán từ PayOS. Vui lòng kiểm tra lại cấu hình.'
        ]);
        exit;
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Cổng thanh toán PayOS chưa được cấu hình.'
    ]);
    exit;
}
?>
