<?php
require_once __DIR__ . '/db_helper.php';

// Yêu cầu đăng nhập trước khi thực hiện mua hàng hoặc thêm vào giỏ hàng
if (isset($_GET['buy_now']) || isset($_GET['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?status=login_required");
        exit;
    }
}

// Xử lý Mua ngay (Buy Now)
if (isset($_GET['buy_now'])) {
    $book_id = (int)$_GET['buy_now'];
    $book = get_book_by_id($book_id);
    
    if ($book) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$book_id])) {
            $_SESSION['cart'][$book_id]++;
        } else {
            $_SESSION['cart'][$book_id] = 1;
        }
        
        // Đồng bộ giỏ hàng vào CSDL nếu đã đăng nhập
        if (isset($_SESSION['user_id'])) {
            sync_session_to_db_cart($_SESSION['user_id']);
        }
        
        header("Location: checkout.php");
        exit;
    }
}

// Xử lý thêm vào giỏ hàng (Add to Cart)
if (isset($_GET['add_to_cart'])) {
    $book_id = (int)$_GET['add_to_cart'];
    $book = get_book_by_id($book_id);
    
    if ($book) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$book_id])) {
            $_SESSION['cart'][$book_id]++;
        } else {
            $_SESSION['cart'][$book_id] = 1;
        }
        
        // Đồng bộ giỏ hàng vào CSDL nếu đã đăng nhập
        if (isset($_SESSION['user_id'])) {
            sync_session_to_db_cart($_SESSION['user_id']);
        }
        
        // Chuyển hướng để tránh lặp hành động F5
        $redirect_url = 'index.php?status=added';
        if (isset($_GET['category'])) {
            $redirect_url .= '&category=' . urlencode($_GET['category']);
        }
        if (isset($_GET['search'])) {
            $redirect_url .= '&search=' . urlencode($_GET['search']);
        }
        header("Location: " . $redirect_url);
        exit;
    }
}

// Lấy danh sách sách
$books = get_books();

// Bộ lọc Tìm kiếm và Thể loại
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';

// Tiến hành lọc sách
$filtered_books = [];
foreach ($books as $book) {
    $matches_search = true;
    $matches_category = true;
    
    if ($search_query !== '') {
        $search_lower = mb_strtolower($search_query);
        $title_lower = mb_strtolower($book['title']);
        $author_lower = mb_strtolower($book['author']);
        if (strpos($title_lower, $search_lower) === false && strpos($author_lower, $search_lower) === false) {
            $matches_search = false;
        }
    }
    
    if ($category_filter !== '') {
        if ($book['category'] !== $category_filter) {
            $matches_category = false;
        }
    }
    
    if ($matches_search && $matches_category) {
        $filtered_books[] = $book;
    }
}

// Lọc sách nổi bật (Featured Books) để hiển thị banner riêng
$featured_books = [];
foreach ($books as $book) {
    if (isset($book['featured']) && $book['featured'] == 1) {
        $featured_books[] = $book;
    }
}

require_once __DIR__ . '/header.php';
?>

<!-- Hero Banner (Chỉ hiển thị khi không có tìm kiếm/lọc cụ thể) -->
<?php if ($search_query === '' && $category_filter === ''): ?>
<div class="container mt-4">
    <div class="hero-banner">
        <h1>Thế Giới Sách Của Tri Thức</h1>
        <p>Tìm kiếm và khám phá hàng ngàn cuốn sách hấp dẫn thuộc nhiều thể loại khác nhau. Đọc sách mỗi ngày để mở rộng thế giới quan của bạn.</p>
        <div class="d-flex justify-content-center">
            <form action="index.php" method="GET" class="w-50 d-flex gap-2">
                <input type="text" name="search" class="form-control search-input" placeholder="Tìm kiếm sách, tác giả...">
                <button type="submit" class="btn btn-primary-custom px-4"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Danh mục thể loại & Tìm kiếm -->
<div class="container my-4">
    <div class="glass-panel filter-bar">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-3 mb-lg-0">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="text-muted me-2"><i class="fas fa-filter text-warning"></i> Lọc:</span>
                    <a href="index.php" class="category-badge <?php echo ($category_filter === '') ? 'active' : ''; ?>">Tất cả</a>
                    <?php 
                    // Tạo danh mục duy nhất
                    $temp_cats = [];
                    foreach ($books as $b) {
                        if (!in_array($b['category'], $temp_cats)) {
                            $temp_cats[] = $b['category'];
                        }
                    }
                    foreach ($temp_cats as $cat): 
                    ?>
                        <a href="index.php?category=<?php echo urlencode($cat); ?>&search=<?php echo urlencode($search_query); ?>" 
                           class="category-badge <?php echo ($category_filter === $cat) ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-5">
                <form action="index.php" method="GET" class="d-flex gap-2">
                    <?php if ($category_filter !== ''): ?>
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_filter); ?>">
                    <?php endif; ?>
                    <input type="text" name="search" class="form-control search-input" placeholder="Tìm tên sách hoặc tác giả..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="btn btn-primary-custom"><i class="fas fa-search"></i></button>
                    <?php if ($search_query !== '' || $category_filter !== ''): ?>
                        <a href="index.php" class="btn btn-secondary-custom" title="Xóa bộ lọc"><i class="fas fa-undo"></i></a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Thông báo thêm giỏ hàng thành công -->
    <?php if (isset($_GET['status']) && $_GET['status'] == 'added'): ?>
        <div class="alert alert-custom alert-success-custom d-flex align-items-center gap-2" role="alert">
            <i class="fas fa-check-circle"></i>
            <span>Đã thêm sách vào giỏ hàng thành công! <a href="cart.php" class="alert-link text-white text-decoration-underline">Xem giỏ hàng</a></span>
        </div>
    <?php endif; ?>

    <!-- PHẦN 1: SÁCH NỔI BẬT (Chỉ hiển thị ở trang chủ mặc định) -->
    <?php if ($search_query === '' && $category_filter === '' && !empty($featured_books)): ?>
        <div class="mb-5" id="featured-section">
            <h2 class="section-title">Sách Nổi Bật</h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($featured_books as $book): ?>
                    <div class="col book-item-column featured-item" data-title="<?php echo htmlspecialchars($book['title']); ?>" data-author="<?php echo htmlspecialchars($book['author']); ?>">
                        <div class="glass-panel glass-panel-hover book-card">
                            <div class="book-featured-tag">Nổi bật</div>
                            <div class="book-img-wrapper">
                                <img src="<?php echo htmlspecialchars($book['image']); ?>" class="book-img" alt="<?php echo htmlspecialchars($book['title']); ?>">
                            </div>
                            <div class="book-card-body">
                                <span class="book-category"><?php echo htmlspecialchars($book['category']); ?></span>
                                <h4 class="book-title">
                                    <a href="book-detail.php?id=<?php echo $book['id']; ?>"><?php echo htmlspecialchars($book['title']); ?></a>
                                </h4>
                                <p class="book-author">Tác giả: <?php echo htmlspecialchars($book['author']); ?></p>
                                <div class="book-price-row align-items-center">
                                    <span class="book-price"><?php echo number_format($book['price'], 0, ',', '.'); ?> đ</span>
                                    <div class="d-flex gap-2 align-items-center">
                                        <a href="index.php?buy_now=<?php echo $book['id']; ?>" class="btn btn-sm btn-warning py-1 px-3 text-dark font-weight-600" style="border-radius: 20px; font-size: 0.75rem; transition: var(--transition-smooth);" title="Mua ngay">Mua ngay</a>
                                        <a href="index.php?add_to_cart=<?php echo $book['id']; ?>" class="btn-add-cart" title="Thêm vào giỏ hàng">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- PHẦN 2: DANH SÁCH SÁCH -->
    <div>
        <h2 class="section-title">
            <?php 
            if ($search_query !== '' || $category_filter !== '') {
                echo 'Kết Quả Tìm Kiếm (' . count($filtered_books) . ')';
            } else {
                echo 'Tất Cả Sách';
            }
            ?>
        </h2>

        <!-- Trình giữ chỗ khi tìm kiếm real-time không ra kết quả -->
        <div id="no-results-placeholder" class="glass-panel text-center p-5 my-4 d-none">
            <i class="fas fa-book-dead text-muted fa-3x mb-3"></i>
            <p class="text-muted mb-0">Không tìm thấy cuốn sách nào phù hợp với tìm kiếm của bạn.</p>
        </div>

        <?php if (empty($filtered_books)): ?>
            <div class="glass-panel text-center p-5 my-4">
                <i class="fas fa-book-dead text-muted fa-3x mb-3"></i>
                <p class="text-muted mb-0">Không tìm thấy cuốn sách nào phù hợp với yêu cầu của bạn.</p>
                <a href="index.php" class="btn btn-primary-custom mt-3">Quay lại trang chủ</a>
            </div>
        <?php else: ?>
            <div id="all-books-grid" class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
                <?php foreach ($filtered_books as $book): ?>
                    <div class="col book-item-column" data-title="<?php echo htmlspecialchars($book['title']); ?>" data-author="<?php echo htmlspecialchars($book['author']); ?>">
                        <div class="glass-panel glass-panel-hover book-card">
                            <div class="book-img-wrapper">
                                <img src="<?php echo htmlspecialchars($book['image']); ?>" class="book-img" alt="<?php echo htmlspecialchars($book['title']); ?>">
                            </div>
                            <div class="book-card-body">
                                <span class="book-category"><?php echo htmlspecialchars($book['category']); ?></span>
                                <h4 class="book-title">
                                    <a href="book-detail.php?id=<?php echo $book['id']; ?>"><?php echo htmlspecialchars($book['title']); ?></a>
                                </h4>
                                <p class="book-author">Tác giả: <?php echo htmlspecialchars($book['author']); ?></p>
                                <div class="book-price-row align-items-center">
                                    <span class="book-price"><?php echo number_format($book['price'], 0, ',', '.'); ?> đ</span>
                                    <div class="d-flex gap-2 align-items-center">
                                        <a href="index.php?buy_now=<?php echo $book['id']; ?>" class="btn btn-sm btn-warning py-1 px-3 text-dark font-weight-600" style="border-radius: 20px; font-size: 0.75rem; transition: var(--transition-smooth);" title="Mua ngay">Mua ngay</a>
                                        <a href="index.php?add_to_cart=<?php echo $book['id']; ?><?php 
                                            if ($category_filter) echo '&category=' . urlencode($category_filter);
                                            if ($search_query) echo '&search=' . urlencode($search_query);
                                        ?>" class="btn-add-cart" title="Thêm vào giỏ hàng">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInputs = document.querySelectorAll('.search-input');
    const bookColumns = document.querySelectorAll('.book-item-column');
    const noResultsPlaceholder = document.getElementById('no-results-placeholder');
    const featuredSection = document.getElementById('featured-section');
    const allBooksGrid = document.getElementById('all-books-grid');
    
    searchInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            const query = e.target.value.trim().toLowerCase();
            
            // Đồng bộ giá trị giữa các ô tìm kiếm
            searchInputs.forEach(otherInput => {
                if (otherInput !== input) {
                    otherInput.value = e.target.value;
                }
            });
            
            let visibleCount = 0;
            let featuredVisibleCount = 0;
            
            bookColumns.forEach(col => {
                const title = col.getAttribute('data-title').toLowerCase();
                const author = col.getAttribute('data-author').toLowerCase();
                const isFeatured = col.classList.contains('featured-item');
                
                if (title.includes(query) || author.includes(query)) {
                    col.style.display = '';
                    if (isFeatured) {
                        featuredVisibleCount++;
                    } else {
                        visibleCount++;
                    }
                } else {
                    col.style.display = 'none';
                }
            });
            
            // Ẩn tiêu đề/section sách nổi bật nếu không có sản phẩm nổi bật nào khớp
            if (featuredSection) {
                if (query !== '' && featuredVisibleCount === 0) {
                    featuredSection.style.display = 'none';
                } else {
                    featuredSection.style.display = '';
                }
            }
            
            // Xử lý thông báo trống cho danh sách tất cả sách
            if (visibleCount === 0 && (featuredSection ? featuredVisibleCount === 0 : true)) {
                if (noResultsPlaceholder) noResultsPlaceholder.classList.remove('d-none');
                if (allBooksGrid) allBooksGrid.classList.add('d-none');
            } else {
                if (noResultsPlaceholder) noResultsPlaceholder.classList.add('d-none');
                if (allBooksGrid) allBooksGrid.classList.remove('d-none');
            }
        });
        
        // Chặn submit form khi bấm Enter để không bị load lại trang
        const form = input.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
            });
        }
    });
});
</script>

<?php
require_once __DIR__ . '/footer.php';
?>
