<?php
$host = '127.0.0.1';
$user = 'root';
$pass = ''; // XAMPP mặc định trống

try {
    // Kết nối đến MySQL Server
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Kết nối MySQL thành công!<br>";

    // Tạo CSDL
    $pdo->exec("CREATE DATABASE IF NOT EXISTS bookstore_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Tạo CSDL 'bookstore_db' thành công!<br>";

    // Chọn CSDL
    $pdo->exec("USE bookstore_db");

    // Tạo các bảng
    $sql_create_tables = "
    CREATE TABLE IF NOT EXISTS `users` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `fullname` VARCHAR(100) NOT NULL,
      `email` VARCHAR(100) NOT NULL UNIQUE,
      `password` VARCHAR(255) NOT NULL,
      `role` VARCHAR(10) NOT NULL DEFAULT 'user'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `books` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `title` VARCHAR(255) NOT NULL,
      `author` VARCHAR(150) NOT NULL,
      `category` VARCHAR(100) NOT NULL,
      `price` DECIMAL(10, 2) NOT NULL CHECK (`price` >= 0),
      `image` VARCHAR(255) DEFAULT NULL,
      `description` TEXT DEFAULT NULL,
      `featured` TINYINT(1) DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `orders` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `user_id` INT DEFAULT NULL,
      `total_price` DECIMAL(10, 2) NOT NULL,
      `customer_name` VARCHAR(100) NOT NULL,
      `customer_phone` VARCHAR(15) NOT NULL,
      `customer_address` TEXT NOT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `order_items` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `order_id` INT NOT NULL,
      `book_id` INT DEFAULT NULL,
      `quantity` INT NOT NULL CHECK (`quantity` > 0),
      `price` DECIMAL(10, 2) NOT NULL,
      FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
      FOREIGN KEY (`book_id`) REFERENCES `books`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $pdo->exec($sql_create_tables);
    echo "Tạo cấu trúc các bảng thành công!<br>";

    // Kiểm tra và chèn dữ liệu mẫu nếu chưa có
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        $sql_seed = "
        INSERT INTO `users` (`fullname`, `email`, `password`, `role`) VALUES
        ('Quản trị viên', 'admin@bookstore.com', '$2y$10\$l1VnfR43H1h/jsRmi9jxr.5kJIf4PTvyF3LoPhewTGm/lquiew/ma', 'admin'),
        ('Nguyễn Văn Khách', 'customer@example.com', '$2y$10\$dktO2PmjAVu5aRCkIB.DEeJWxPCNTbRq8H0AmA4qnsgYhKTeYVe/K', 'user');

        INSERT INTO `books` (`title`, `author`, `category`, `price`, `image`, `description`, `featured`) VALUES
        ('Đắc Nhân Tâm', 'Dale Carnegie', 'Kỹ năng sống', 86000.00, 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&q=80&w=400', 'Cuốn sách đưa ra các lời khuyên về cách thức cư xử, ứng xử và giao tiếp với mọi người để đạt được thành công trong cuộc sống.', 1),
        ('Nhà Giả Kim', 'Paulo Coelho', 'Tiểu thuyết', 79000.00, 'https://images.unsplash.com/photo-1589829085413-56de8ae18c73?auto=format&fit=crop&q=80&w=400', 'Câu chuyện kể về hành trình đầy sóng gió và chiêm nghiệm của cậu bé chăn cừu Santiago đi tìm kho báu của đời mình.', 1),
        ('Tôi Thấy Hoa Vàng Trên Cỏ Xanh', 'Nguyễn Nhật Ánh', 'Văn học Việt Nam', 125000.00, 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?auto=format&fit=crop&q=80&w=400', 'Tác phẩm truyện dài đầy xúc động về tuổi thơ nghèo khó của những đứa trẻ nơi vùng quê miền Trung thanh bình.', 0),
        ('Clean Code (Mã Sạch)', 'Robert C. Martin', 'Công nghệ thông tin', 320000.00, 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&q=80&w=400', 'Cuốn cẩm nang kinh điển dành cho lập trình viên.', 1),
        ('Lược Sử Thời Gian', 'Stephen Hawking', 'Khoa học vũ trụ', 150000.00, 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&q=80&w=400', 'Khám phá những bí ẩn lớn nhất của vũ trụ.', 0),
        ('Cha Giàu Cha Nghèo', 'Robert T. Kiyosaki', 'Tài chính cá nhân', 110000.00, 'https://images.unsplash.com/photo-1579621970588-a3f5ce599fac?auto=format&fit=crop&q=80&w=400', 'Tư duy tài chính và cách tạo nguồn thu nhập thụ động.', 0);

        INSERT INTO `orders` (`id`, `user_id`, `total_price`, `customer_name`, `customer_phone`, `customer_address`) VALUES
        (1, 2, 165000.00, 'Nguyễn Văn Khách', '0912345678', '123 Đường Nguyễn Trãi, Quận 1, TP. Hồ Chí Minh');

        INSERT INTO `order_items` (`order_id`, `book_id`, `quantity`, `price`) VALUES
        (1, 1, 1, 86000.00),
        (1, 2, 1, 79000.00);
        ";
        $pdo->exec($sql_seed);
        echo "Chèn dữ liệu mẫu thành công!<br>";
    }

    echo "<br><b>Hoàn tất!</b> Bạn có thể truy cập trang web ngay bây giờ.";

} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}
?>