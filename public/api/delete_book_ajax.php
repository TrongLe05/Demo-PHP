<?php
require_once __DIR__ . '/../../includes/db_helper.php';

header('Content-Type: application/json');

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện hành động này.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if ($book_id > 0) {
        if (delete_book($book_id)) {
            echo json_encode(['success' => true, 'message' => 'Đã xóa sách thành công.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống! Không thể xóa sách này.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID sách không hợp lệ.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
}
?>
