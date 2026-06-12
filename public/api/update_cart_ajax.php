<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/db_helper.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Bạn cần đăng nhập trước khi thực hiện thao tác này.'
    ]);
    exit;
}

// Chỉ nhận yêu cầu POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Phương thức yêu cầu không hợp lệ.'
    ]);
    exit;
}

// Lấy tham số gửi lên
$book_id = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

if ($book_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Sản phẩm không hợp lệ.'
    ]);
    exit;
}

$book = get_book_by_id($book_id);
if (!$book) {
    echo json_encode([
        'success' => false,
        'message' => 'Sách không tồn tại.'
    ]);
    exit;
}

// Kiểm tra số lượng tồn kho
if ($quantity > $book['quantity']) {
    echo json_encode([
        'success' => false,
        'message' => "Chỉ còn {$book['quantity']} cuốn trong kho."
    ]);
    exit;
}

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Cập nhật số lượng
if ($quantity <= 0) {
    unset($_SESSION['cart'][$book_id]);
} else {
    $_SESSION['cart'][$book_id] = $quantity;
}

// Đồng bộ vào cơ sở dữ liệu nếu đã đăng nhập
if (isset($_SESSION['user_id'])) {
    sync_session_to_db_cart($_SESSION['user_id']);
}

// Tính toán lại tổng giỏ hàng
$total_price = 0;
$cart_count = 0;
$item_subtotal = 0;

foreach ($_SESSION['cart'] as $id => $qty) {
    $b = get_book_by_id($id);
    if ($b) {
        $sub = $b['price'] * $qty;
        $total_price += $sub;
        $cart_count += $qty;
        if ($id === $book_id) {
            $item_subtotal = $sub;
        }
    }
}

echo json_encode([
    'success' => true,
    'message' => 'Cập nhật giỏ hàng thành công.',
    'item_subtotal' => number_format($item_subtotal, 0, ',', '.') . ' đ',
    'total_price' => number_format($total_price, 0, ',', '.') . ' đ',
    'total_price_raw' => $total_price,
    'cart_count' => $cart_count
]);
exit;
