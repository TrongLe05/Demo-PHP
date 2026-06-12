<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/db_helper.php';

// Yêu cầu đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn chưa đăng nhập.']);
    exit;
}

$temp_order_code = isset($_GET['order_code']) ? (int)$_GET['order_code'] : 0;
if ($temp_order_code <= 0) {
    echo json_encode(['success' => false, 'message' => 'Mã thanh toán không hợp lệ.']);
    exit;
}

$payOS = new PayOSService(PAYOS_CLIENT_ID, PAYOS_API_KEY, PAYOS_CHECKSUM_KEY);
if (!$payOS->isConfigured()) {
    echo json_encode([
        'success' => false,
        'message' => 'Cổng thanh toán PayOS chưa được cấu hình.'
    ]);
    exit;
}

$paymentInfo = $payOS->getPaymentLinkInformation($temp_order_code);
if ($paymentInfo && isset($paymentInfo['status'])) {
    echo json_encode([
        'success' => true,
        'status' => $paymentInfo['status'],
        'is_mock' => false
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Không thể lấy thông tin trạng thái từ PayOS.'
    ]);
}
exit;
