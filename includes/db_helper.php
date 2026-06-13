<?php
// =========================================================================
// ENVIRONMENT CONFIGURATION LOADER (SOLID: Single Responsibility)
// =========================================================================
function load_env_file(string $filePath): void {
    if (!file_exists($filePath)) {
        return;
    }
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            $value = trim($value, '"\'');
            if (getenv($key) === false) {
                putenv("$key=$value");
            }
            $_ENV[$key] = $value;
        }
    }
}

// Tải tệp cấu hình .env tại thư mục gốc của dự án
load_env_file(dirname(__DIR__) . '/.env');

// =========================================================================
// CONFIGURATION & DATABASE CONNECTIVITY (SOLID: Single Responsibility)
// =========================================================================
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'ban_sach_online');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

define('PAYOS_CLIENT_ID', getenv('PAYOS_CLIENT_ID') ?: 'your_client_id');
define('PAYOS_API_KEY', getenv('PAYOS_API_KEY') ?: 'your_api_key');
define('PAYOS_CHECKSUM_KEY', getenv('PAYOS_CHECKSUM_KEY') ?: 'your_checksum_key');

function env_bool(string $key, bool $default = false): bool {
    $value = getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }
    return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
}

function env_string(string $key, string $default = ''): string {
    $value = getenv($key);
    return $value !== false && $value !== '' ? trim($value) : $default;
}

function get_enabled_payment_methods(): array {
    $methods = [];

    if (env_bool('PAYMENT_METHOD_COD_ENABLED', true)) {
        $methods[] = 'COD';
    }
    if (env_bool('PAYMENT_METHOD_PAYOS_ENABLED', true)) {
        $methods[] = 'PayOS';
    }
    if (env_bool('PAYMENT_METHOD_CARD_ENABLED', true)) {
        $methods[] = 'Thẻ tín dụng';
    }
    if (env_bool('PAYMENT_METHOD_QR_ENABLED', true)) {
        $methods[] = 'QR';
    }

    return !empty($methods) ? $methods : ['COD'];
}

function get_default_payment_method(): string {
    $default = env_string('DEFAULT_PAYMENT_METHOD', 'COD');
    $enabled = get_enabled_payment_methods();
    return in_array($default, $enabled, true) ? $default : ($enabled[0] ?? 'COD');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Interface DatabaseConnectionInterface
 * Tuân thủ Dependency Inversion Principle (DIP)
 */
interface DatabaseConnectionInterface {
    public function getConnection(): PDO;
}

/**
 * Class DatabaseConnection
 * Tuân thủ Single Responsibility Principle (SRP) - Chỉ chịu trách nhiệm kết nối và kiểm tra CSDL
 */
class DatabaseConnection implements DatabaseConnectionInterface {
    private ?PDO $pdo = null;

    public function getConnection(): PDO {
        if ($this->pdo === null) {
            try {
                $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Tự động kiểm tra di trú cấu trúc bảng
                $this->runMigrations();
            } catch (PDOException $e) {
                die("Kết nối CSDL MySQL thất bại: " . $e->getMessage());
            }
        }
        return $this->pdo;
    }

    private function runMigrations(): void {
        try {
            $stmt_status = $this->pdo->query("SHOW COLUMNS FROM orders LIKE 'status'");
            if (!$stmt_status->fetch()) {
                $this->pdo->exec("ALTER TABLE orders ADD COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Chờ xác nhận'");
            }
            $stmt_pm = $this->pdo->query("SHOW COLUMNS FROM orders LIKE 'payment_method'");
            if (!$stmt_pm->fetch()) {
                $this->pdo->exec("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) NOT NULL DEFAULT 'COD'");
            }
        } catch (PDOException $e) {
            // Bỏ qua nếu bảng chưa được tạo
        }
    }
}

// Khởi tạo instance kết nối dùng chung
$dbConnectionManager = new DatabaseConnection();
$pdo = $dbConnectionManager->getConnection(); // Khai báo biến toàn cục tương thích ngược

/**
 * Class BookRepository
 * Tuân thủ SRP - Chỉ quản lý các thao tác liên quan đến Sách
 */
class BookRepository {
    private PDO $db;

    public function __construct(DatabaseConnectionInterface $connectionManager) {
        $this->db = $connectionManager->getConnection();
    }

    public function getAllBooks(): array {
        try {
            $stmt = $this->db->query("SELECT * FROM books ORDER BY id DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getBookById(int $id): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM books WHERE id = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function add(string $title, string $author, string $category, float $price, ?string $image, string $description, int $featured = 0, int $quantity = 10): bool {
        try {
            $stmt = $this->db->prepare("INSERT INTO books (title, author, category, price, image, description, featured, quantity) VALUES (:title, :author, :category, :price, :image, :description, :featured, :quantity)");
            return $stmt->execute([
                'title' => $title,
                'author' => $author,
                'category' => $category,
                'price' => $price,
                'image' => $image ?: 'dac_nhan_tam.jpg',
                'description' => $description,
                'featured' => $featured,
                'quantity' => $quantity
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update(int $id, string $title, string $author, string $category, float $price, ?string $image, string $description, int $featured = 0, int $quantity = 10): bool {
        try {
            $sql = "UPDATE books SET title = :title, author = :author, category = :category, price = :price, description = :description, featured = :featured, quantity = :quantity";
            $params = [
                'title' => $title,
                'author' => $author,
                'category' => $category,
                'price' => $price,
                'description' => $description,
                'featured' => $featured,
                'quantity' => $quantity,
                'id' => $id
            ];
            if ($image) {
                $sql .= ", image = :image";
                $params['image'] = $image;
            }
            $sql .= " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM books WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}

/**
 * Class UserRepository
 * Tuân thủ SRP - Chỉ quản lý người dùng và xác thực
 */
class UserRepository {
    private PDO $db;

    public function __construct(DatabaseConnectionInterface $connectionManager) {
        $this->db = $connectionManager->getConnection();
    }

    public function getAllUsers(): array {
        try {
            $stmt = $this->db->query("SELECT * FROM users ORDER BY id DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getByEmail(string $email): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => trim($email)]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function getByUsername(string $username): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute(['username' => trim($username)]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function register(string $username, string $fullname, string $email, string $password): array|bool {
        try {
            if ($this->getByEmail($email) !== null || $this->getByUsername($username) !== null) {
                return false;
            }
            
            $stmt = $this->db->prepare("INSERT INTO users (username, fullname, email, password, role) VALUES (:username, :fullname, :email, :password, 'user')");
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $success = $stmt->execute([
                'username' => trim($username),
                'fullname' => $fullname,
                'email' => trim($email),
                'password' => $hashedPassword
            ]);
            
            if ($success) {
                return [
                    'id' => $this->db->lastInsertId(),
                    'username' => trim($username),
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
}

/**
 * Class OrderRepository
 * Tuân thủ SRP - Chỉ chịu trách nhiệm quản lý đơn hàng
 */
class OrderRepository {
    private PDO $db;

    public function __construct(DatabaseConnectionInterface $connectionManager) {
        $this->db = $connectionManager->getConnection();
    }

    public function getOrders(): array {
        try {
            $stmt = $this->db->query("SELECT * FROM orders ORDER BY id ASC");
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($orders as &$order) {
                $stmt_details = $this->db->prepare("
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

    public function getOrdersByUser(int $user_id): array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY id DESC");
            $stmt->execute(['user_id' => $user_id]);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($orders as &$order) {
                $stmt_details = $this->db->prepare("
                    SELECT od.book_id, od.quantity, od.price, b.title, b.image
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

    public function saveOrder(string $customer_name, string $customer_phone, string $customer_address, array $cart_items, float $total_price, string $payment_method = 'COD', bool $clear_cart = true): int|bool {
        try {
            // Yêu cầu đăng nhập bắt buộc (SOLID: Business Rule Enforcement)
            if (!isset($_SESSION['user_id'])) {
                return false;
            }
            $user_id = $_SESSION['user_id'];
            
            $this->db->beginTransaction();
            
            $stmt = $this->db->prepare("INSERT INTO orders (user_id, total_price, customer_name, customer_phone, customer_address, payment_method) VALUES (:user_id, :total_price, :customer_name, :customer_phone, :customer_address, :payment_method)");
            $stmt->execute([
                'user_id' => $user_id,
                'total_price' => $total_price,
                'customer_name' => $customer_name,
                'customer_phone' => $customer_phone,
                'customer_address' => $customer_address,
                'payment_method' => $payment_method
            ]);
            
            $order_id = (int)$this->db->lastInsertId();
            
            $stmt_detail = $this->db->prepare("INSERT INTO order_details (order_id, book_id, quantity, price) VALUES (:order_id, :book_id, :quantity, :price)");
            $stmt_update_qty = $this->db->prepare("UPDATE books SET quantity = quantity - :qty WHERE id = :book_id");
            
            foreach ($cart_items as $item) {
                $stmt_detail->execute([
                    'order_id' => $order_id,
                    'book_id' => $item['book_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);
                
                $stmt_update_qty->execute([
                    'qty' => $item['quantity'],
                    'book_id' => $item['book_id']
                ]);
            }
            
            // Xóa giỏ hàng của user trong DB nếu được yêu cầu
            if ($clear_cart) {
                $stmt_clear_cart = $this->db->prepare("DELETE FROM cart WHERE user_id = :user_id");
                $stmt_clear_cart->execute(['user_id' => $user_id]);
            }
            
            $this->db->commit();
            return $order_id;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function updateStatus(int $order_id, string $status): bool {
        try {
            $stmt = $this->db->prepare("UPDATE orders SET status = :status WHERE id = :id");
            return $stmt->execute([
                'status' => $status,
                'id' => $order_id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function cancel(int $order_id): bool {
        try {
            $this->db->beginTransaction();
            
            $stmt = $this->db->prepare("SELECT status FROM orders WHERE id = :id");
            $stmt->execute(['id' => $order_id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$order || $order['status'] === 'Đã hủy') {
                $this->db->rollBack();
                return false;
            }
            
            $stmt_update = $this->db->prepare("UPDATE orders SET status = 'Đã hủy' WHERE id = :id");
            $stmt_update->execute(['id' => $order_id]);
            
            $stmt_items = $this->db->prepare("SELECT book_id, quantity FROM order_details WHERE order_id = :order_id");
            $stmt_items->execute(['order_id' => $order_id]);
            $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt_restore_stock = $this->db->prepare("UPDATE books SET quantity = quantity + :qty WHERE id = :book_id");
            foreach ($items as $item) {
                $stmt_restore_stock->execute([
                    'qty' => $item['quantity'],
                    'book_id' => $item['book_id']
                ]);
            }
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }
}

/**
 * Class CartManager
 * Tuân thủ SRP - Chỉ chịu trách nhiệm quản lý Giỏ hàng của người dùng
 */
class CartManager {
    private PDO $db;

    public function __construct(DatabaseConnectionInterface $connectionManager) {
        $this->db = $connectionManager->getConnection();
    }

    public function getCartCount(): int {
        $count = 0;
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $qty) {
                $count += $qty;
            }
        }
        return $count;
    }

    public function syncDbToSession(int $user_id): void {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        try {
            $stmt = $this->db->prepare("SELECT book_id, quantity FROM cart WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $user_id]);
            $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($cart_items as $item) {
                $_SESSION['cart'][$item['book_id']] = (int)$item['quantity'];
            }
        } catch (PDOException $e) {
            // Bỏ qua lỗi
        }
    }

    public function syncSessionToDb(int $user_id): void {
        try {
            $this->db->beginTransaction();
            
            $stmt_delete = $this->db->prepare("DELETE FROM cart WHERE user_id = :user_id");
            $stmt_delete->execute(['user_id' => $user_id]);
            
            if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                $stmt_insert = $this->db->prepare("INSERT INTO cart (user_id, book_id, quantity) VALUES (:user_id, :book_id, :quantity)");
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
            
            $this->db->commit();
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
        }
    }
}

/**
 * Class PayOSService
 * Tuân thủ SRP - Chỉ phụ trách giao tiếp với cổng thanh toán PayOS
 */
class PayOSService {
    private string $clientId;
    private string $apiKey;
    private string $checksumKey;

    public function __construct(string $clientId, string $apiKey, string $checksumKey) {
        $this->clientId = $clientId;
        $this->apiKey = $apiKey;
        $this->checksumKey = $checksumKey;
    }

    public function isConfigured(): bool {
        return !empty($this->clientId) && !empty($this->apiKey) && !empty($this->checksumKey) &&
               $this->clientId !== 'your_client_id' && $this->apiKey !== 'your_api_key' && $this->checksumKey !== 'your_checksum_key';
    }

    public function createPaymentLink(int $orderCode, float $amount, string $description, string $returnUrl, string $cancelUrl): ?string {
        if (!$this->isConfigured()) {
            return null;
        }

        $data = [
            'amount' => (int)$amount,
            'cancelUrl' => $cancelUrl,
            'description' => substr(preg_replace('/[^a-zA-Z0-9 ]/', '', $description), 0, 25), // PayOS description max 25 chars, alphanumeric
            'orderCode' => $orderCode,
            'returnUrl' => $returnUrl
        ];

        // Sắp xếp khóa bảng chữ cái để tạo chữ ký
        ksort($data);

        // Chuỗi dữ liệu ký
        $signParts = [];
        foreach ($data as $key => $value) {
            $signParts[] = "$key=$value";
        }
        $signString = implode('&', $signParts);

        // Tạo chữ ký HMAC_SHA256
        $signature = hash_hmac('sha256', $signString, $this->checksumKey);
        $data['signature'] = $signature;

        // Gửi cURL POST đến PayOS
        $ch = curl_init('https://api-merchant.payos.vn/v2/payment-requests');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-client-id: ' . $this->clientId,
            'x-api-key: ' . $this->apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $resData = json_decode($response, true);
            if (isset($resData['code']) && ($resData['code'] === '00' || $resData['code'] === '0' || $resData['code'] === 0) && isset($resData['data']['checkoutUrl'])) {
                return $resData['data']['checkoutUrl'];
            }
        }
        return null;
    }

    public function createPaymentLinkDetails(int $orderCode, float $amount, string $description, string $returnUrl, string $cancelUrl): ?array {
        if (!$this->isConfigured()) {
            return null;
        }

        $data = [
            'amount' => (int)$amount,
            'cancelUrl' => $cancelUrl,
            'description' => substr(preg_replace('/[^a-zA-Z0-9 ]/', '', $description), 0, 25),
            'orderCode' => $orderCode,
            'returnUrl' => $returnUrl
        ];

        ksort($data);

        $signParts = [];
        foreach ($data as $key => $value) {
            $signParts[] = "$key=$value";
        }
        $signString = implode('&', $signParts);

        $signature = hash_hmac('sha256', $signString, $this->checksumKey);
        $data['signature'] = $signature;

        $ch = curl_init('https://api-merchant.payos.vn/v2/payment-requests');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-client-id: ' . $this->clientId,
            'x-api-key: ' . $this->apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $resData = json_decode($response, true);
            if (isset($resData['code']) && ($resData['code'] === '00' || $resData['code'] === '0' || $resData['code'] === 0) && isset($resData['data'])) {
                return $resData['data'];
            }
        }
        return null;
    }

    public function getPaymentLinkInformation(int $orderCode): ?array {
        if (!$this->isConfigured()) {
            return null;
        }

        $ch = curl_init('https://api-merchant.payos.vn/v2/payment-requests/' . $orderCode);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-client-id: ' . $this->clientId,
            'x-api-key: ' . $this->apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $resData = json_decode($response, true);
            if (isset($resData['code']) && ($resData['code'] === '00' || $resData['code'] === '0' || $resData['code'] === 0) && isset($resData['data'])) {
                return $resData['data'];
            }
        }
        return null;
    }
}

// =========================================================================
// FACADE / BACKWARD COMPATIBILITY WRAPPERS
// Các hàm bao này giữ nguyên giao tiếp cũ để không phá vỡ code ở các trang khác
// =========================================================================
$bookRepo = new BookRepository($dbConnectionManager);
$userRepo = new UserRepository($dbConnectionManager);
$orderRepo = new OrderRepository($dbConnectionManager);
$cartManager = new CartManager($dbConnectionManager);

function get_books() {
    global $bookRepo;
    return $bookRepo->getAllBooks();
}

function get_book_by_id($id) {
    global $bookRepo;
    return $bookRepo->getBookById((int)$id);
}

function add_book($title, $author, $category, $price, $image, $description, $featured = 0, $quantity = 10) {
    global $bookRepo;
    return $bookRepo->add($title, $author, $category, (float)$price, $image, $description, (int)$featured, (int)$quantity);
}

function update_book($id, $title, $author, $category, $price, $image, $description, $featured = 0, $quantity = 10) {
    global $bookRepo;
    return $bookRepo->update((int)$id, $title, $author, $category, (float)$price, $image, $description, (int)$featured, (int)$quantity);
}

function delete_book($id) {
    global $bookRepo;
    return $bookRepo->delete((int)$id);
}

function get_user_by_email($email) {
    global $userRepo;
    return $userRepo->getByEmail($email);
}

function get_user_by_username($username) {
    global $userRepo;
    return $userRepo->getByUsername($username);
}

function register_user($username, $fullname, $email, $password) {
    global $userRepo;
    return $userRepo->register($username, $fullname, $email, $password);
}

function get_orders() {
    global $orderRepo;
    return $orderRepo->getOrders();
}

function get_orders_by_user($user_id) {
    global $orderRepo;
    return $orderRepo->getOrdersByUser((int)$user_id);
}

function save_order($customer_name, $customer_phone, $customer_address, $cart_items, $total_price, $payment_method = 'COD', $clear_cart = true) {
    global $orderRepo;
    return $orderRepo->saveOrder($customer_name, $customer_phone, $customer_address, $cart_items, (float)$total_price, $payment_method, $clear_cart);
}

function update_order_status($order_id, $status) {
    global $orderRepo;
    return $orderRepo->updateStatus((int)$order_id, $status);
}

function cancel_order($order_id) {
    global $orderRepo;
    return $orderRepo->cancel((int)$order_id);
}

function get_cart_count() {
    global $cartManager;
    return $cartManager->getCartCount();
}

function sync_db_cart_to_session($user_id) {
    global $cartManager;
    $cartManager->syncDbToSession((int)$user_id);
}

function sync_session_to_db_cart($user_id) {
    global $cartManager;
    $cartManager->syncSessionToDb((int)$user_id);
}

function get_next_order_id() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT MAX(id) AS max_id FROM orders");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row && $row['max_id']) ? ((int)$row['max_id'] + 1) : 1;
    } catch (PDOException $e) {
        return time();
    }
}
?>
