<?php
// =========================================================================
// CẤU HÌNH KẾT NỐI DATABASE MYSQL
// =========================================================================
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'ban_sach_online');
define('DB_USER', 'root');
define('DB_PASS', ''); // Mặc định XAMPP trống

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối CSDL MySQL thất bại: " . $e->getMessage());
}

// Khởi động session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Đọc danh sách sách từ MySQL
 */
function get_books() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM books ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Lấy thông tin một cuốn sách theo ID
 */
function get_book_by_id($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM books WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Thêm một cuốn sách mới (Admin)
 */
function add_book($title, $author, $category, $price, $image, $description, $featured = 0) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO books (title, author, category, price, image, description, featured) VALUES (:title, :author, :category, :price, :image, :description, :featured)");
        return $stmt->execute([
            'title' => $title,
            'author' => $author,
            'category' => $category,
            'price' => (float)$price,
            'image' => $image ? $image : 'dac_nhan_tam.jpg',
            'description' => $description,
            'featured' => (int)$featured
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Cập nhật thông tin sách (Admin)
 */
function update_book($id, $title, $author, $category, $price, $image, $description, $featured = 0) {
    global $pdo;
    try {
        $sql = "UPDATE books SET title = :title, author = :author, category = :category, price = :price, description = :description, featured = :featured";
        $params = [
            'title' => $title,
            'author' => $author,
            'category' => $category,
            'price' => (float)$price,
            'description' => $description,
            'featured' => (int)$featured,
            'id' => (int)$id
        ];
        if ($image) {
            $sql .= ", image = :image";
            $params['image'] = $image;
        }
        $sql .= " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Xóa một cuốn sách (Admin)
 */
function delete_book($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM books WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Đọc danh sách người dùng từ MySQL
 */
function get_users() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Lấy thông tin người dùng theo email
 */
function get_user_by_email($email) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => trim($email)]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Đăng ký người dùng mới
 */
function register_user($fullname, $email, $password) {
    global $pdo;
    try {
        // Kiểm tra trùng email
        if (get_user_by_email($email) !== null) {
            return false;
        }
        
        $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, role) VALUES (:fullname, :email, :password, 'user')");
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $success = $stmt->execute([
            'fullname' => $fullname,
            'email' => trim($email),
            'password' => $hashedPassword
        ]);
        
        if ($success) {
            $userId = $pdo->lastInsertId();
            return [
                'id' => $userId,
                'fullname' => $fullname,
                'email' => trim($email),
                'role' => 'user'
            ];
        }
        return false;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Đọc danh sách đơn hàng từ MySQL
 */
function get_orders() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM orders ORDER BY id ASC");
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($orders as &$order) {
            $stmt_details = $pdo->prepare("
                SELECT od.book_id, od.quantity, od.price, b.title 
                FROM order_details od
                JOIN books b ON od.book_id = b.id
                WHERE od.order_id = :order_id
            ");
            $stmt_details->execute(['order_id' => $order['id']]);
            $order['items'] = $stmt_details->fetchAll(PDO::FETCH_ASSOC);
        }
        return $orders;
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Lưu đơn hàng mới vào CSDL (sử dụng Transaction)
 */
function save_order($customer_name, $customer_phone, $customer_address, $cart_items, $total_price) {
    global $pdo;
    try {
        $pdo->beginTransaction();
        
        // CSDL.txt định nghĩa user_id là NOT NULL. 
        // Nếu không có user đăng nhập, mặc định gán cho user_id = 2 (Nguyễn Văn A - Tài khoản user mẫu)
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 2;
        
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, customer_name, customer_phone, customer_address) VALUES (:user_id, :total_price, :customer_name, :customer_phone, :customer_address)");
        $stmt->execute([
            'user_id' => $user_id,
            'total_price' => $total_price,
            'customer_name' => $customer_name,
            'customer_phone' => $customer_phone,
            'customer_address' => $customer_address
        ]);
        
        $order_id = $pdo->lastInsertId();
        
        // Thêm chi tiết đơn hàng
        $stmt_detail = $pdo->prepare("INSERT INTO order_details (order_id, book_id, quantity, price) VALUES (:order_id, :book_id, :quantity, :price)");
        foreach ($cart_items as $item) {
            $stmt_detail->execute([
                'order_id' => $order_id,
                'book_id' => $item['book_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ]);
        }
        
        // Xóa giỏ hàng trong DB của user này nếu đã đăng nhập
        if (isset($_SESSION['user_id'])) {
            $stmt_clear_cart = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id");
            $stmt_clear_cart->execute(['user_id' => $_SESSION['user_id']]);
        }
        
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return false;
    }
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

/**
 * Đồng bộ giỏ hàng từ MySQL vào Session khi đăng nhập
 */
function sync_db_cart_to_session($user_id) {
    global $pdo;
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    try {
        $stmt = $pdo->prepare("SELECT book_id, quantity FROM cart WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($cart_items as $item) {
            $_SESSION['cart'][$item['book_id']] = (int)$item['quantity'];
        }
    } catch (PDOException $e) {
        // Bỏ qua lỗi
    }
}

/**
 * Đồng bộ giỏ hàng từ Session vào MySQL khi giỏ hàng thay đổi
 */
function sync_session_to_db_cart($user_id) {
    global $pdo;
    try {
        $pdo->beginTransaction();
        
        // Xóa giỏ hàng cũ trong DB
        $stmt_delete = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id");
        $stmt_delete->execute(['user_id' => $user_id]);
        
        // Thêm các mặt hàng hiện tại trong session vào DB
        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            $stmt_insert = $pdo->prepare("INSERT INTO cart (user_id, book_id, quantity) VALUES (:user_id, :book_id, :quantity)");
            foreach ($_SESSION['cart'] as $book_id => $qty) {
                if ($qty > 0) {
                    $stmt_insert->execute([
                        'user_id' => $user_id,
                        'book_id' => $book_id,
                        'quantity' => $qty
                    ]);
                }
            }
        }
        
        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }
}
?>

