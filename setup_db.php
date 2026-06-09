<?php
$host = '127.0.0.1';
$user = 'root';
$pass = ''; // XAMPP mặc định trống

try {
    // Kết nối đến MySQL Server
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Kết nối MySQL thành công!<br>";

    // Đọc file CSDL.txt
    $sql_file = __DIR__ . '/CSDL.txt';
    if (!file_exists($sql_file)) {
        die("Không tìm thấy file CSDL.txt tại đường dẫn: " . $sql_file);
    }
    
    $sql = file_get_contents($sql_file);
    
    // Thực thi các câu lệnh SQL
    $pdo->exec($sql);
    
    echo "Khởi tạo cơ sở dữ liệu 'ban_sach_online' từ file CSDL.txt thành công!<br>";
    echo "<br><b>Hoàn tất!</b> Bạn có thể truy cập trang web ngay bây giờ.";

} catch (PDOException $e) {
    die("Lỗi khởi tạo CSDL: " . $e->getMessage());
}
?>