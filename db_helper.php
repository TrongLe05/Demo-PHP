<?php
// =========================================================================
// CẤU HÌNH KẾT NỐI DATABASE MYSQL (TẠM THỜI ẨN - DÙNG MOCK DATA JSON)
// =========================================================================
/*
define('DB_HOST', 'localhost');
define('DB_NAME', 'bookstore_db');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Để ẩn kết nối và tránh báo lỗi khi chưa cài đặt MySQL, khối này tạm thời đóng lại.
    // die("Kết nối CSDL MySQL thất bại: " . $e->getMessage());
}
*/

// Định nghĩa đường dẫn các tệp tin cơ sở dữ liệu giả lập (Mock Data JSON)
define('BOOKS_FILE', __DIR__ . '/books.json');
define('USERS_FILE', __DIR__ . '/users.json');
define('ORDERS_FILE', __DIR__ . '/orders.json');

// Khởi động session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Đọc danh sách sách từ file JSON
 */
function get_books() {
    if (!file_exists(BOOKS_FILE)) {
        return [];
    }
    $content = file_get_contents(BOOKS_FILE);
    return json_decode($content, true) ?: [];
}

/**
 * Lấy thông tin một cuốn sách theo ID
 */
function get_book_by_id($id) {
    $books = get_books();
    foreach ($books as $book) {
        if ($book['id'] == $id) {
            return $book;
        }
    }
    return null;
}

/**
 * Lưu danh sách sách vào file JSON (Dành cho Admin CRUD)
 */
function save_books($books) {
    return file_put_contents(BOOKS_FILE, json_encode($books, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

/**
 * Thêm một cuốn sách mới (Admin)
 */
function add_book($title, $author, $category, $price, $image, $description, $featured = 0) {
    $books = get_books();
    $max_id = 0;
    foreach ($books as $b) {
        if ($b['id'] > $max_id) {
            $max_id = $b['id'];
        }
    }
    
    $new_book = [
        'id' => $max_id + 1,
        'title' => $title,
        'author' => $author,
        'category' => $category,
        'price' => (float)$price,
        'image' => $image ? $image : 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?auto=format&fit=crop&q=80&w=400',
        'description' => $description,
        'featured' => (int)$featured
    ];
    
    $books[] = $new_book;
    return save_books($books);
}

/**
 * Cập nhật thông tin sách (Admin)
 */
function update_book($id, $title, $author, $category, $price, $image, $description, $featured = 0) {
    $books = get_books();
    $updated = false;
    foreach ($books as &$b) {
        if ($b['id'] == $id) {
            $b['title'] = $title;
            $b['author'] = $author;
            $b['category'] = $category;
            $b['price'] = (float)$price;
            if ($image) {
                $b['image'] = $image;
            }
            $b['description'] = $description;
            $b['featured'] = (int)$featured;
            $updated = true;
            break;
        }
    }
    if ($updated) {
        return save_books($books);
    }
    return false;
}

/**
 * Xóa một cuốn sách (Admin)
 */
function delete_book($id) {
    $books = get_books();
    $new_books = [];
    $found = false;
    foreach ($books as $b) {
        if ($b['id'] == $id) {
            $found = true;
            continue;
        }
        $new_books[] = $b;
    }
    if ($found) {
        return save_books($new_books);
    }
    return false;
}

/**
 * Đọc danh sách người dùng từ file JSON
 */
function get_users() {
    if (!file_exists(USERS_FILE)) {
        return [];
    }
    $content = file_get_contents(USERS_FILE);
    return json_decode($content, true) ?: [];
}

/**
 * Lấy thông tin người dùng theo email
 */
function get_user_by_email($email) {
    $users = get_users();
    foreach ($users as $user) {
        if (strcasecmp($user['email'], trim($email)) === 0) {
            return $user;
        }
    }
    return null;
}

/**
 * Đăng ký người dùng mới
 */
function register_user($fullname, $email, $password) {
    $users = get_users();
    
    // Kiểm tra trùng email
    if (get_user_by_email($email) !== null) {
        return false;
    }
    
    $max_id = 0;
    foreach ($users as $u) {
        if ($u['id'] > $max_id) {
            $max_id = $u['id'];
        }
    }
    
    $new_user = [
        'id' => $max_id + 1,
        'fullname' => $fullname,
        'email' => trim($email),
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'role' => 'user' // Mặc định đăng ký mới luôn là user thường
    ];
    
    $users[] = $new_user;
    if (file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false) {
        return $new_user;
    }
    return false;
}

/**
 * Đọc danh sách đơn hàng từ file JSON
 */
function get_orders() {
    if (!file_exists(ORDERS_FILE)) {
        return [];
    }
    $content = file_get_contents(ORDERS_FILE);
    return json_decode($content, true) ?: [];
}

/**
 * Lưu đơn hàng mới
 */
function save_order($customer_name, $customer_phone, $customer_address, $cart_items, $total_price) {
    $orders = get_orders();
    
    $max_id = 0;
    foreach ($orders as $o) {
        if ($o['id'] > $max_id) {
            $max_id = $o['id'];
        }
    }
    
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    $new_order = [
        'id' => $max_id + 1,
        'user_id' => $user_id,
        'customer_name' => $customer_name,
        'customer_phone' => $customer_phone,
        'customer_address' => $customer_address,
        'total_price' => $total_price,
        'items' => $cart_items,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $orders[] = $new_order;
    return file_put_contents(ORDERS_FILE, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

/**
 * Lấy giỏ hàng hiện tại (số lượng sản phẩm)
 */
function get_cart_count() {
    $count = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $qty) {
            $count += $qty;
        }
    }
    return $count;
}
?>
