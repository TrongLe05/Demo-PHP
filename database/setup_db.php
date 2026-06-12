<?php
// Ngăn chặn việc vô tình tải lại trang setup_db.php làm mất sạch dữ liệu
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    die("<div style='font-family: sans-serif; padding: 20px; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; border-radius: 8px; max-width: 600px; margin: 50px auto;'>
            <h3 style='margin-top:0;'>Cảnh báo: Khởi tạo lại Cơ sở dữ liệu</h3>
            <p>Hành động này sẽ <strong>XÓA SẠCH</strong> cơ sở dữ liệu hiện tại và nạp lại dữ liệu mẫu ban đầu từ <code>CSDL.txt</code>.</p>
            <p>Để tiếp tục, vui lòng bấm vào liên kết sau: <a href='setup_db.php?confirm=yes' style='background: #dc2626; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 10px; font-weight: bold;'>Đồng ý Khởi tạo lại CSDL</a></p>
         </div>");
}

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