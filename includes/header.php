<?php
require_once __DIR__ . '/db_helper.php';

// Xác định base URL của dự án một cách động để tránh mất CSS/JS khi đường dẫn URL thay đổi hoặc thiếu dấu gạch chéo cuối
$base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';

// Xác định trang hiện tại để gán class active cho navbar (sử dụng SCRIPT_NAME để luôn có tên file chính xác)
$current_page = basename($_SERVER['SCRIPT_NAME']);

// Lấy danh sách thể loại từ cơ sở dữ liệu
$categories_list = get_categories();
$categories = [];
foreach ($categories_list as $cat_row) {
    $categories[] = $cat_row['name'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3FC - Hệ Thống Bán Sách Trực Tuyến</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS (with Cache Busting to ensure instant browser updates) -->
    <?php
    $style_css = "assets/css/style.css";
    $style_time = file_exists(__DIR__ . "/../public/" . $style_css) ? filemtime(__DIR__ . "/../public/" . $style_css) : time();
    ?>
    <link rel="stylesheet" href="<?php echo $base_url . $style_css; ?>?v=<?php echo $style_time; ?>">
    <?php
    $page_name = pathinfo($current_page, PATHINFO_FILENAME);
    $css_file = "assets/css/{$page_name}.css";
    if (file_exists(__DIR__ . "/../public/" . $css_file)) {
        $file_time = filemtime(__DIR__ . "/../public/" . $css_file);
        echo '    <link rel="stylesheet" href="' . $base_url . $css_file . '?v=' . $file_time . '">' . PHP_EOL;
    }
    ?>
    <script>
        // Khởi chạy màu giao diện lập tức trước khi tải CSS để tránh bị nhấp nháy màn hình trắng
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        })();
    </script>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="<?php echo $base_url; ?>index.php">
                <i class="fas fa-book-open"></i> 3<span>FC</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'index.php' && !isset($_GET['category'])) ? 'active' : ''; ?>" href="<?php echo $base_url; ?>index.php">Trang chủ</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo isset($_GET['category']) ? 'active' : ''; ?>" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Thể loại
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-custom" aria-labelledby="navbarDropdown" style="background: var(--bg-secondary); border: 1px solid var(--glass-border);">
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>index.php">Tất cả sách</a></li>
                            <li><hr class="dropdown-divider" style="border-color: var(--glass-border);"></li>
                            <?php foreach ($categories as $cat): ?>
                                <li>
                                    <a class="dropdown-item <?php echo (isset($_GET['category']) && $_GET['category'] == $cat) ? 'active' : ''; ?>" href="<?php echo $base_url; ?>index.php?category=<?php echo urlencode($cat); ?>">
                                        <?php echo htmlspecialchars($cat); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'about.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>about.php">Giới thiệu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'promotions.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>promotions.php">Khuyến mãi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>contact.php">Liên hệ</a>
                    </li>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'admin.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>admin.php">
                                <i class="fas fa-user-shield text-warning me-1"></i>Quản trị viên
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <div class="d-flex align-items-center gap-3">
                    <!-- Giỏ hàng (chỉ hiển thị cho khách hàng, ẩn đối với admin) -->
                    <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'): ?>
                        <a href="<?php echo $base_url; ?>cart.php" class="btn btn-cart">
                            <i class="fas fa-shopping-basket"></i> Giỏ hàng
                            <span class="cart-badge"><?php echo get_cart_count(); ?></span>
                        </a>
                    <?php endif; ?>

                    <!-- Tài khoản -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="dropdown">
                            <a class="btn btn-secondary-custom dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="far fa-user-circle me-1"></i> Chào, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark dropdown-menu-custom" aria-labelledby="userDropdown" style="background: var(--bg-secondary); border: 1px solid var(--glass-border);">
                                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                    <li><a class="dropdown-item" href="<?php echo $base_url; ?>admin.php"><i class="fas fa-cog me-2"></i>Quản lý cửa hàng</a></li>
                                    <li><hr class="dropdown-divider" style="border-color: var(--glass-border);"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?php echo $base_url; ?>orders.php"><i class="fas fa-receipt me-2"></i>Đơn hàng của tôi</a></li>
                                <li><hr class="dropdown-divider" style="border-color: var(--glass-border);"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo $base_url; ?>logout.php"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo $base_url; ?>login.php" class="btn-login">
                            <i class="fas fa-sign-in-alt me-1"></i> Đăng nhập
                        </a>
                    <?php endif; ?>

                    <!-- Nút chuyển đổi giao diện Sáng/Tối -->
                    <button id="theme-toggle" class="btn-theme-toggle" title="Đổi giao diện Sáng/Tối">
                        <i class="fas fa-sun icon-sun"></i>
                        <i class="fas fa-moon icon-moon"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Container chứa thông báo Toast -->
    <div id="toast-container" class="toast-container"></div>

    <script>
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;
        
        // Tạo phần tử Toast mới
        const toast = document.createElement('div');
        toast.className = `toast-item toast-${type} d-flex align-items-center p-3 mb-2`;
        
        // Xác định icon tương ứng với loại thông báo
        let icon = 'fa-check-circle text-success';
        if (type === 'danger' || type === 'error') {
            icon = 'fa-exclamation-circle text-danger';
        } else if (type === 'warning') {
            icon = 'fa-exclamation-triangle text-warning';
        } else if (type === 'info') {
            icon = 'fa-info-circle text-info';
        }
        
        toast.innerHTML = `
            <div class="toast-icon me-3"><i class="fas ${icon}"></i></div>
            <div class="toast-message flex-grow-1 text-white">${message}</div>
            <button type="button" class="btn-close btn-close-white ms-2" aria-label="Close" onclick="this.parentElement.remove()" style="font-size: 0.75rem; filter: invert(0);"></button>
        `;
        
        // Thêm vào container
        container.appendChild(toast);
        
        // Tự động xóa sau 4 giây
        setTimeout(() => {
            toast.style.animation = 'toastSlideOut 0.4s ease forwards';
            setTimeout(() => {
                toast.remove();
            }, 400);
        }, 4000);
    }

    // JavaScript xử lý chuyển đổi giao diện Sáng/Tối (Dark/Light mode)
    document.addEventListener('DOMContentLoaded', function() {
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', function() {
                const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                document.documentElement.setAttribute('data-theme', newTheme);
                document.documentElement.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                
                // Kích hoạt event custom nếu có component khác lắng nghe thay đổi giao diện
                document.dispatchEvent(new CustomEvent('themeChanged', { detail: newTheme }));
            });
        }
    });
    </script>

    <!-- Content Container -->
    <div class="main-content flex-grow-1" style="margin-bottom: 30px;">
