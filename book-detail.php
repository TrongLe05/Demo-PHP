<?php
require_once __DIR__ . '/db_helper.php';

$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$book = get_book_by_id($book_id);

if (!$book) {
    header("Location: index.php");
    exit;
}

// Xử lý thêm vào giỏ hàng hoặc mua ngay tại trang chi tiết
if (isset($_POST['add_to_cart_btn']) || isset($_POST['buy_now_btn'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?status=login_required");
        exit;
    }
    $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    if ($qty <= 0) $qty = 1;
    
    // Giới hạn số lượng mua không vượt quá số lượng tồn kho
    if (isset($book['quantity']) && $qty > $book['quantity']) {
        $qty = $book['quantity'];
    }
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$book_id])) {
        $_SESSION['cart'][$book_id] += $qty;
    } else {
        $_SESSION['cart'][$book_id] = $qty;
    }
    
    // Đồng bộ giỏ hàng vào CSDL nếu đã đăng nhập
    if (isset($_SESSION['user_id'])) {
        sync_session_to_db_cart($_SESSION['user_id']);
    }
    
    if (isset($_POST['buy_now_btn'])) {
        header("Location: checkout.php");
    } else {
        header("Location: book-detail.php?id=" . $book_id . "&status=added");
    }
    exit;
}

require_once __DIR__ . '/header.php';
?>

<div class="container book-detail-container">
    <!-- Nút quay lại -->
    <div class="mb-4">
        <a href="index.php" class="btn btn-secondary-custom"><i class="fas fa-arrow-left me-2"></i>Quay lại trang chủ</a>
    </div>

    <!-- Thông báo thêm giỏ hàng thành công -->
    <?php if (isset($_GET['status']) && $_GET['status'] == 'added'): ?>
        <div class="alert alert-custom alert-success-custom d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="fas fa-check-circle"></i>
            <span>Đã thêm sách vào giỏ hàng thành công! <a href="cart.php" class="alert-link text-white text-decoration-underline">Xem giỏ hàng và thanh toán</a></span>
        </div>
    <?php endif; ?>

    <div class="glass-panel p-4">
        <div class="row">
            <!-- Ảnh bìa sách -->
            <div class="col-md-5 col-lg-4 mb-4 mb-md-0">
                <div class="book-detail-img-wrapper">
                    <img src="<?php echo htmlspecialchars($book['image']); ?>" class="w-100" style="object-fit: cover;" alt="<?php echo htmlspecialchars($book['title']); ?>">
                </div>
            </div>
            
            <!-- Thông tin sách -->
            <div class="col-md-7 col-lg-8 book-detail-info">
                <span class="badge bg-warning text-dark mb-2 px-3 py-2 fs-6"><?php echo htmlspecialchars($book['category']); ?></span>
                <h1 style="font-family: var(--font-heading); font-size: 2.8rem; font-weight: 700; margin-bottom: 0.5rem; text-shadow: 0 2px 10px rgba(255,255,255,0.05);">
                    <?php echo htmlspecialchars($book['title']); ?>
                </h1>
                <h5 class="text-muted mb-4">Tác giả: <strong class="text-white"><?php echo htmlspecialchars($book['author']); ?></strong></h5>
                
                <hr style="border-color: var(--glass-border);">
                
                <div class="book-detail-price">
                    <?php echo number_format($book['price'], 0, ',', '.'); ?> đ
                </div>
                
                <div class="mb-4 text-white">
                    Tình trạng: 
                    <?php if (isset($book['quantity']) && $book['quantity'] > 0): ?>
                        <span class="badge bg-success px-3 py-2 fs-6">Còn hàng (Tồn kho: <?php echo $book['quantity']; ?> cuốn)</span>
                    <?php else: ?>
                        <span class="badge bg-danger px-3 py-2 fs-6">Tạm hết hàng</span>
                    <?php endif; ?>
                </div>
                
                <p class="text-muted mb-4 fs-5" style="line-height: 1.8;">
                    <?php echo nl2br(htmlspecialchars($book['description'])); ?>
                </p>
                
                <hr style="border-color: var(--glass-border);" class="my-4">
                
                <!-- Form thêm vào giỏ hàng -->
                <?php if (isset($book['quantity']) && $book['quantity'] > 0): ?>
                    <form action="book-detail.php?id=<?php echo $book['id']; ?>" method="POST" class="d-flex align-items-center gap-3 flex-wrap">
                        <div class="d-flex align-items-center gap-2">
                            <label for="quantity" class="text-muted font-weight-500">Số lượng:</label>
                            <input type="number" id="quantity" name="quantity" class="form-control-custom text-center" style="width: 80px;" value="1" min="1" max="<?php echo $book['quantity']; ?>">
                        </div>
                        
                        <button type="submit" name="add_to_cart_btn" class="btn btn-secondary-custom px-4 py-3">
                            <i class="fas fa-cart-plus me-1"></i> Thêm vào giỏ hàng
                        </button>
                        <button type="submit" name="buy_now_btn" class="btn btn-primary-custom px-4 py-3">
                            <i class="fas fa-bolt me-1"></i> Mua ngay
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-custom alert-danger-custom d-flex align-items-center gap-2" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Sản phẩm này hiện đang hết hàng. Vui lòng quay lại sau!</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/footer.php';
?>
