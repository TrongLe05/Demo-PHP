<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/db_helper.php';

// Yêu cầu đăng nhập trước khi thêm vào giỏ hàng
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'status' => 'login_required',
        'message' => 'Bạn cần đăng nhập trước khi thêm sách vào giỏ hàng.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
    
    if ($book_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không hợp lệ.']);
        exit;
    }
    
    $book = get_book_by_id($book_id);
    if (!$book) {
        echo json_encode(['success' => false, 'message' => 'Sách không tồn tại.']);
        exit;
    }
    
    // Kiểm tra số lượng tồn kho
    $current_qty_in_cart = isset($_SESSION['cart'][$book_id]) ? $_SESSION['cart'][$book_id] : 0;
    $new_qty = $current_qty_in_cart + 1;
    
    if ($new_qty > $book['quantity']) {
        echo json_encode([
            'success' => false,
            'message' => "Không thể thêm. Chỉ còn {$book['quantity']} cuốn trong kho và bạn đã có {$current_qty_in_cart} cuốn trong giỏ hàng."
        ]);
        exit;
    }
    
    // Thực hiện thêm vào giỏ
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $_SESSION['cart'][$book_id] = $new_qty;
    
    // Đồng bộ vào DB
    sync_session_to_db_cart($_SESSION['user_id']);
    
    // Tính tổng số lượng trong giỏ
    $cart_count = get_cart_count();
    
    echo json_encode([
        'success' => true,
        'message' => 'Đã thêm sách vào giỏ hàng thành công!',
        'cart_count' => $cart_count
    ]);
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Phương thức yêu cầu không hợp lệ.']);
    exit;
}
?>
