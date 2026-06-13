<?php
require_once __DIR__ . '/../../includes/db_helper.php';

header('Content-Type: application/json');

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện hành động này.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if ($category_id > 0) {
        if (is_category_used($category_id)) {
            echo json_encode(['success' => false, 'message' => 'Không thể xóa thể loại này vì đang có sách thuộc thể loại này.']);
        } else {
            if (delete_category($category_id)) {
                echo json_encode(['success' => true, 'message' => 'Đã xóa thể loại thành công.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống! Không thể xóa thể loại này.']);
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID thể loại không hợp lệ.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
}
?>
