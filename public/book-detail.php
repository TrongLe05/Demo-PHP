<?php
require_once __DIR__ . '/../includes/db_helper.php';

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

// Lấy sách cùng thể loại (Related Books)
$all_books = get_books();
$related_books = [];
foreach ($all_books as $b) {
    if (trim($b['category']) === trim($book['category']) && (int)$b['id'] !== (int)$book['id']) {
        $related_books[] = $b;
    }
}
$related_books = array_slice($related_books, 0, 4);

// Giả lập thông số kỹ thuật của sách
$publisher = 'NXB Tổng Hợp TP.HCM';
if (trim($book['category']) === 'Công nghệ thông tin') {
    $publisher = 'NXB Khoa Học & Kỹ Thuật';
} elseif (trim($book['category']) === 'Tiểu thuyết') {
    $publisher = 'NXB Trẻ';
}
$pages = (($book['id'] * 47) % 200) + 220;
$dimensions = '14.5 x 20.5 cm';
$original_price = $book['price'] / 0.85; // 15% discount

require_once __DIR__ . '/../includes/header.php';
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
                <div class="book-detail-img-wrapper position-relative">
                    <div class="discount-badge">-15%</div>
                    <img src="<?php echo htmlspecialchars(!empty($book['image']) ? ((strpos($book['image'], 'http') === 0 || strpos($book['image'], 'uploads/') === 0) ? $book['image'] : 'uploads/' . $book['image']) : 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=500'); ?>" class="img-fluid w-100" style="aspect-ratio: 5/7; object-fit: cover;" alt="<?php echo htmlspecialchars($book['title']); ?>">
                </div>
            </div>
            
            <!-- Thông tin sách -->
            <div class="col-md-7 col-lg-8 book-detail-info">
                <span class="badge bg-warning text-dark mb-2 px-3 py-2 fs-6"><?php echo htmlspecialchars($book['category']); ?></span>
                <h1 class="text-white mb-2 book-detail-title">
                    <?php echo htmlspecialchars($book['title']); ?>
                </h1>
                <h5 class="text-muted mb-3">Tác giả: <strong class="text-white"><?php echo htmlspecialchars($book['author']); ?></strong></h5>
                
                <!-- Rating đánh giá -->
                <div class="rating-stars mb-4 fs-5">
                    <?php 
                    $rating = ($book['id'] % 2 === 0) ? 4.5 : 5.0;
                    $full_stars = floor($rating);
                    $half_star = ($rating - $full_stars) >= 0.5;
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $full_stars) {
                            echo '<i class="fas fa-star"></i>';
                        } elseif ($i === $full_stars + 1 && $half_star) {
                            echo '<i class="fas fa-star-half-alt"></i>';
                        } else {
                            echo '<i class="far fa-star"></i>';
                        }
                    }
                    ?>
                    <span class="rating-count fs-6 text-muted">(<?php echo ($book['id'] * 15 + 4) % 60 + 5; ?> đánh giá từ độc giả)</span>
                </div>

                <hr style="border-color: var(--glass-border);">
                
                <div class="d-flex align-items-center gap-3 my-3">
                    <span class="text-muted text-decoration-line-through fs-6"><?php echo number_format($original_price, 0, ',', '.'); ?> đ</span>
                    <div class="book-detail-price my-0">
                        <?php echo number_format($book['price'], 0, ',', '.'); ?> đ
                    </div>
                </div>
                
                <div class="mb-4 text-white">
                    Tình trạng: 
                    <?php if (isset($book['quantity']) && $book['quantity'] > 0): ?>
                        <span class="badge bg-success px-3 py-2 fs-6">Còn hàng (Tồn kho: <?php echo $book['quantity']; ?> cuốn)</span>
                    <?php else: ?>
                        <span class="badge bg-danger px-3 py-2 fs-6">Tạm hết hàng</span>
                    <?php endif; ?>
                </div>
                
                <p class="book-description mb-4">
                    <?php echo nl2br(htmlspecialchars($book['description'])); ?>
                </p>
                
                <!-- Bảng thông số kỹ thuật chi tiết -->
                <div class="mb-4">
                    <h5 class="text-white mb-3" style="font-family: var(--font-heading);"><i class="fas fa-info-circle text-warning me-2"></i>Thông tin chi tiết</h5>
                    <table class="specs-table">
                        <tr>
                            <td>Nhà xuất bản</td>
                            <td><?php echo $publisher; ?></td>
                        </tr>
                        <tr>
                            <td>Hình thức</td>
                            <td>Bìa mềm</td>
                        </tr>
                        <tr>
                            <td>Số trang</td>
                            <td><?php echo $pages; ?> trang</td>
                        </tr>
                        <tr>
                            <td>Kích thước</td>
                            <td><?php echo $dimensions; ?></td>
                        </tr>
                        <tr>
                            <td>Ngôn ngữ</td>
                            <td>Tiếng Việt</td>
                        </tr>
                    </table>
                </div>

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

    <!-- Phần 2: Sách cùng thể loại gợi ý -->
    <?php if (!empty($related_books)): ?>
        <div class="related-books-section">
            <h3 class="section-title mb-4">Sách Cùng Thể Loại</h3>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                <?php foreach ($related_books as $r_book): 
                    $r_original_price = $r_book['price'] / 0.85;
                ?>
                    <div class="col">
                        <div class="glass-panel glass-panel-hover book-card">
                            <div class="discount-badge">-15%</div>
                            <a href="book-detail.php?id=<?php echo $r_book['id']; ?>">
                                <div class="book-img-wrapper">
                                    <img src="<?php echo htmlspecialchars(!empty($r_book['image']) ? ((strpos($r_book['image'], 'http') === 0 || strpos($r_book['image'], 'uploads/') === 0) ? $r_book['image'] : 'uploads/' . $r_book['image']) : 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=500'); ?>" class="book-img" alt="<?php echo htmlspecialchars($r_book['title']); ?>">
                                </div>
                            </a>
                            <div class="book-card-body">
                                <span class="book-category" style="font-size: 0.75rem;"><?php echo htmlspecialchars($r_book['category']); ?></span>
                                <h5 class="book-title" style="font-size: 1.1rem; height: 3rem;">
                                    <a href="book-detail.php?id=<?php echo $r_book['id']; ?>"><?php echo htmlspecialchars($r_book['title']); ?></a>
                                </h5>
                                <p class="book-author mb-2" style="font-size: 0.8rem;">Tác giả: <?php echo htmlspecialchars($r_book['author']); ?></p>
                                
                                <div class="rating-stars mb-2" style="font-size: 0.75rem;">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                </div>

                                <div class="book-price-row align-items-center mt-auto">
                                    <div>
                                        <span class="book-price text-warning d-block" style="font-size: 1.1rem;"><?php echo number_format($r_book['price'], 0, ',', '.'); ?> đ</span>
                                    </div>
                                    <a href="index.php?add_to_cart=<?php echo $r_book['id']; ?>" class="btn-add-cart" style="width: 34px; height: 34px;" title="Thêm vào giỏ hàng">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
