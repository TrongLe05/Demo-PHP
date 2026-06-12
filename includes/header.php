<?php
require_once __DIR__ . '/db_helper.php';

// Xác định trang hiện tại để gán class active cho navbar
$current_page = basename($_SERVER['PHP_SELF']);

// Lấy danh sách thể loại từ các cuốn sách để hiển thị động trên menu
$all_books = get_books();
$categories = [];
foreach ($all_books as $book) {
    if (!in_array($book['category'], $categories)) {
        $categories[] = $book['category'];
    }
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
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-book-open"></i> 3<span>FC</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'index.php' && !isset($_GET['category'])) ? 'active' : ''; ?>" href="index.php">Trang chủ</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo isset($_GET['category']) ? 'active' : ''; ?>" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Thể loại
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdown" style="background: var(--bg-secondary); border: 1px solid var(--glass-border);">
                            <li><a class="dropdown-item" href="index.php">Tất cả sách</a></li>
                            <li><hr class="dropdown-divider" style="border-color: var(--glass-border);"></li>
                            <?php foreach ($categories as $cat): ?>
                                <li>
                                    <a class="dropdown-item <?php echo (isset($_GET['category']) && $_GET['category'] == $cat) ? 'active' : ''; ?>" href="index.php?category=<?php echo urlencode($cat); ?>">
                                        <?php echo htmlspecialchars($cat); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'admin.php') ? 'active' : ''; ?>" href="admin.php">
                                <i class="fas fa-user-shield text-warning me-1"></i>Quản trị viên
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <div class="d-flex align-items-center gap-3">
                    <!-- Giỏ hàng (chỉ hiển thị cho khách hàng, ẩn đối với admin) -->
                    <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'): ?>
                        <a href="cart.php" class="btn btn-cart">
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
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark" aria-labelledby="userDropdown" style="background: var(--bg-secondary); border: 1px solid var(--glass-border);">
                                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                    <li><a class="dropdown-item" href="admin.php"><i class="fas fa-cog me-2"></i>Quản lý cửa hàng</a></li>
                                    <li><hr class="dropdown-divider" style="border-color: var(--glass-border);"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="orders.php"><i class="fas fa-receipt me-2"></i>Đơn hàng của tôi</a></li>
                                <li><hr class="dropdown-divider" style="border-color: var(--glass-border);"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-secondary-custom">
                            <i class="fas fa-sign-in-alt me-1"></i> Đăng nhập
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content Container -->
    <div class="main-content flex-grow-1">
