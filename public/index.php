<?php
require_once __DIR__ . '/../includes/db_helper.php';

// Yêu cầu đăng nhập trước khi thực hiện mua hàng hoặc thêm vào giỏ hàng
if (isset($_GET['buy_now']) || isset($_GET['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ./login.php?status=login_required");
        exit;
    }
}

// Xử lý Mua ngay (Buy Now)
if (isset($_GET['buy_now'])) {
    $book_id = (int)$_GET['buy_now'];
    $book = get_book_by_id($book_id);
    
    if ($book) {
        header("Location: ./checkout.php?buy_now=" . $book_id);
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

// Tiến hành lọc sách (loại trừ sách nổi bật khỏi danh sách chính để tránh trùng lặp)
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
    
    // Loại trừ sách nổi bật khỏi danh sách chính (chỉ khi không có tìm kiếm/lọc)
    $is_featured = isset($book['featured']) && $book['featured'] == 1;
    if ($search_query === '' && $category_filter === '' && $is_featured) {
        continue;
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

// Định nghĩa icon cho các thể loại sách
function get_category_icon($category_name) {
    switch (trim($category_name)) {
        case 'Công nghệ thông tin':
            return 'fa-laptop-code text-primary';
        case 'Tâm lý - Kỹ năng sống':
            return 'fa-brain text-warning';
        case 'Tiểu thuyết':
            return 'fa-book text-danger';
        case 'Tâm lý học':
            return 'fa-user-astronaut text-success';
        default:
            return 'fa-book-open text-info';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Hero Carousel Banner (Chỉ hiển thị khi không có tìm kiếm/lọc cụ thể) -->
<?php if ($search_query === '' && $category_filter === ''): ?>
<div class="container mt-4">
    <div id="heroCarousel" class="carousel slide custom-carousel" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <!-- Slide 1 -->
            <div class="carousel-item active">
                <div class="carousel-item-content">
                    <div class="carousel-slide-text">
                        <h2>Thế Giới Sách Của Tri Thức</h2>
                        <p>Khám phá hàng ngàn cuốn sách hấp dẫn thuộc nhiều thể loại khác nhau. Đọc sách mỗi ngày để mở rộng thế giới quan và tích lũy tri thức vô tận của bạn.</p>
                    </div>
                    <div class="carousel-slide-image d-none d-md-flex">
                        <div class="book-cover-placeholder gradient-0 mockup-cover">
                            <div class="placeholder-top"><span class="placeholder-tag">3FC</span></div>
                            <div class="placeholder-middle"><div class="placeholder-title" style="font-size:1.1rem; color: #ffffff !important;">Khám Phá Tri Thức</div></div>
                            <div class="placeholder-bottom"><div class="placeholder-author" style="color: #ffffff !important;"><i class="fas fa-star me-1 text-warning"></i>5.0 Đánh giá</div></div>
                            <div class="book-spine"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Slide 2 -->
            <div class="carousel-item">
                <div class="carousel-item-content">
                    <div class="carousel-slide-text">
                        <h2>Sách Công Nghệ Ưu Đãi 20%</h2>
                        <p>Nâng cao kiến thức lập trình với các tựa sách chuyên sâu như Clean Code, Lập trình PHP Căn Bản, HTML, CSS & JS và hơn thế nữa.</p>
                        <a href="index.php?category=Công+nghệ+thông+tin" class="btn btn-primary-custom"><i class="fas fa-laptop-code"></i> Xem ngay</a>
                    </div>
                    <div class="carousel-slide-image d-none d-md-flex">
                        <div class="book-cover-placeholder gradient-3 mockup-cover" style="transform: rotate(-4deg);">
                            <div class="placeholder-top"><span class="placeholder-tag">3FC Tech</span></div>
                            <div class="placeholder-middle"><div class="placeholder-title" style="font-size:1.1rem; color: #ffffff !important;">Clean Code</div></div>
                            <div class="placeholder-bottom"><div class="placeholder-author" style="color: #ffffff !important;"><i class="fas fa-code me-1"></i>Robert C. Martin</div></div>
                            <div class="book-spine"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Slide 3 -->
            <div class="carousel-item">
                <div class="carousel-item-content">
                    <div class="carousel-slide-text">
                        <h2>Sách Tâm Lý & Kỹ Năng Sống</h2>
                        <p>Trang bị kỹ năng giao tiếp và tư duy nhạy bén cùng các tác phẩm kinh điển "Đắc Nhân Tâm", "Tư Duy Nhanh Và Chậm" với chất lượng in tuyệt hảo.</p>
                        <a href="index.php?category=Tâm+lý+-+Kỹ+năng+sống" class="btn btn-primary-custom"><i class="fas fa-brain"></i> Khám phá ngay</a>
                    </div>
                    <div class="carousel-slide-image d-none d-md-flex">
                        <div class="book-cover-placeholder gradient-1 mockup-cover" style="transform: rotate(5deg);">
                            <div class="placeholder-top"><span class="placeholder-tag">3FC Self</span></div>
                            <div class="placeholder-middle"><div class="placeholder-title" style="font-size:1.1rem; color: #ffffff !important;">Đắc Nhân Tâm</div></div>
                            <div class="placeholder-bottom"><div class="placeholder-author" style="color: #ffffff !important;"><i class="fas fa-feather me-1"></i>Dale Carnegie</div></div>
                            <div class="book-spine"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true" style="filter: invert(1);"></span>
            <span class="visually-hidden">Trước</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true" style="filter: invert(1);"></span>
            <span class="visually-hidden">Sau</span>
        </button>
    </div>
</div>

<!-- Thanh tiện ích dịch vụ khách hàng -->
<div class="container features-bar-container my-4" style="color: var(--text-main);">
    <div class="row g-3">
        <div class="col-6 col-lg-3">
            <div class="feature-item-card d-flex align-items-center gap-3">
                <div class="feature-icon-wrapper flex-shrink-0">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <div>
                    <h6 class="text-white mb-0" style="font-size: 0.95rem; font-weight: 600;">Miễn Phí Vận Chuyển</h6>
                    <small class="text-muted" style="font-size: 0.8rem;">Đơn hàng từ 0đ toàn quốc</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="feature-item-card d-flex align-items-center gap-3">
                <div class="feature-icon-wrapper flex-shrink-0">
                    <i class="fas fa-headset"></i>
                </div>
                <div>
                    <h6 class="text-white mb-0" style="font-size: 0.95rem; font-weight: 600;">Hỗ Trợ 24/7</h6>
                    <small class="text-muted" style="font-size: 0.8rem;">Tư vấn tận tâm, chu đáo</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="feature-item-card d-flex align-items-center gap-3">
                <div class="feature-icon-wrapper flex-shrink-0">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div>
                    <h6 class="text-white mb-0" style="font-size: 0.95rem; font-weight: 600;">Thanh Toán Bảo Mật</h6>
                    <small class="text-muted" style="font-size: 0.8rem;">Cổng PayOS & VietQR</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="feature-item-card d-flex align-items-center gap-3">
                <div class="feature-icon-wrapper flex-shrink-0">
                    <i class="fas fa-undo-alt"></i>
                </div>
                <div>
                    <h6 class="text-white mb-0" style="font-size: 0.95rem; font-weight: 600;">Đổi Trả Dễ Dàng</h6>
                    <small class="text-muted" style="font-size: 0.8rem;">Trong vòng 7 ngày miễn phí</small>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Danh mục thể loại & Tìm kiếm -->
<div class="container my-4">
    <div class="glass-panel filter-bar">
        <form action="index.php" method="GET" class="row g-3 align-items-center">
            <!-- Dropdown chọn thể loại -->
            <div class="col-md-4 col-lg-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted text-nowrap"><i class="fas fa-filter text-warning"></i> Thể loại:</span>
                    <select name="category" class="form-select-custom w-100" onchange="this.form.submit()">
                        <option value="">Tất cả thể loại</option>
                        <?php 
                        // Lấy danh sách thể loại từ tất cả các sách trong CSDL để đảm bảo đầy đủ thể loại
                        $temp_cats = [];
                        $all_books_list = get_books();
                        foreach ($all_books_list as $b) {
                            if (!in_array($b['category'], $temp_cats)) {
                                $temp_cats[] = $b['category'];
                            }
                        }
                        foreach ($temp_cats as $cat): 
                        ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($category_filter === $cat) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <!-- Tìm kiếm văn bản và nút ấn -->
            <div class="col-md-8 col-lg-9">
                <div class="d-flex gap-2">
                    <input type="text" name="search" class="form-control search-input flex-grow-1" placeholder="Tìm tên sách hoặc tác giả..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="btn btn-primary-custom text-nowrap"><i class="fas fa-search me-1"></i> Tìm kiếm</button>
                    <?php if ($search_query !== '' || $category_filter !== ''): ?>
                        <a href="index.php" class="btn btn-secondary-custom text-nowrap" title="Xóa bộ lọc"><i class="fas fa-undo me-1"></i> Xóa lọc</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="container">
    <!-- Thông báo thêm giỏ hàng thành công -->
    <?php if (isset($_GET['status']) && $_GET['status'] == 'added'): ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('Đã thêm sách vào giỏ hàng thành công! <a href="./cart.php" class="text-warning text-decoration-underline ms-1">Xem giỏ hàng</a>', 'success');
        });
        </script>
    <?php endif; ?>

    <!-- PHẦN 1: SÁCH NỔI BẬT (Chỉ hiển thị ở trang chủ mặc định) -->
    <?php if ($search_query === '' && $category_filter === '' && !empty($featured_books)): ?>
        <div class="mb-5" id="featured-section">
            <h2 class="section-title">Sách Nổi Bật</h2>
            <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
                <?php foreach ($featured_books as $book): 
                    $original_price = $book['price'] / 0.8; // Giả lập giảm 20%
                ?>
                    <div class="col book-item-column featured-item" data-title="<?php echo htmlspecialchars($book['title']); ?>" data-author="<?php echo htmlspecialchars($book['author']); ?>">
                        <div class="glass-panel glass-panel-hover book-card">
                            <div class="discount-badge">-20%</div>
                            <div class="book-featured-tag">Nổi bật</div>
                            <a href="book-detail.php?id=<?php echo $book['id']; ?>">
                                <div class="book-img-wrapper">
                                    <img src="<?php echo htmlspecialchars(!empty($book['image']) ? ((strpos($book['image'], 'http') === 0 || strpos($book['image'], 'uploads/books/') === 0) ? $book['image'] : 'uploads/' . $book['image']) : 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=500'); ?>" class="book-img" alt="<?php echo htmlspecialchars($book['title']); ?>">
                                </div>
                            </a>
                            <div class="book-card-body">
                                <span class="book-category"><?php echo htmlspecialchars($book['category']); ?></span>
                                <h4 class="book-title">
                                    <a href="book-detail.php?id=<?php echo $book['id']; ?>"><?php echo htmlspecialchars($book['title']); ?></a>
                                </h4>
                                <p class="book-author">Tác giả: <?php echo htmlspecialchars($book['author']); ?></p>
                                
                                <!-- Rating giả lập động -->
                                <div class="rating-stars">
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
                                    <span class="rating-count">(<?php echo ($book['id'] * 12 + 7) % 50 + 10; ?>)</span>
                                </div>

                                <div class="book-price-row align-items-center">
                                    <div>
                                        <span class="book-price text-warning"><?php echo number_format($book['price'], 0, ',', '.'); ?> đ</span>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 align-items-center mt-2 w-100">
                                    <a href="index.php?buy_now=<?php echo $book['id']; ?>" class="btn btn-sm btn-warning text-dark font-weight-600 flex-grow-1 text-center d-flex align-items-center justify-content-center" style="border-radius: 20px; font-size: 0.75rem; transition: var(--transition-smooth); white-space: nowrap; height: 40px;" title="Mua ngay">Mua ngay</a>
                                    <a href="javascript:void(0);" class="btn-add-cart btn-add-to-cart-ajax" data-book-id="<?php echo $book['id']; ?>" title="Thêm vào giỏ hàng">
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

        <!-- Trình giữ chỗ khi tìm kiếm không ra kết quả (cả server-side và client-side) -->
        <div id="no-results-placeholder" class="glass-panel text-center p-5 my-4 <?php echo empty($filtered_books) ? '' : 'd-none'; ?>">
            <i class="fas fa-book-dead text-muted fa-3x mb-3"></i>
            <p class="text-muted mb-0">Không tìm thấy cuốn sách nào phù hợp với yêu cầu của bạn.</p>
            <?php if (empty($filtered_books)): ?>
                <a href="index.php" class="btn btn-primary-custom mt-3">Quay lại trang chủ</a>
            <?php endif; ?>
        </div>

        <div id="all-books-grid" class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4 <?php echo empty($filtered_books) ? 'd-none' : ''; ?>">
            <?php if (!empty($filtered_books)): ?>
                <?php foreach ($filtered_books as $book): ?>
                    <?php $original_price = $book['price'] / 0.8; ?>
                    <div class="col book-item-column" data-title="<?php echo htmlspecialchars($book['title']); ?>" data-author="<?php echo htmlspecialchars($book['author']); ?>">
                        <div class="glass-panel glass-panel-hover book-card">
                            <div class="discount-badge">-15%</div>
                            <a href="book-detail.php?id=<?php echo $book['id']; ?>">
                                <div class="book-img-wrapper">
                                    <img src="<?php echo htmlspecialchars(!empty($book['image']) ? ((strpos($book['image'], 'http') === 0 || strpos($book['image'], 'uploads/') === 0) ? $book['image'] : 'uploads/' . $book['image']) : 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=500'); ?>" class="book-img" alt="<?php echo htmlspecialchars($book['title']); ?>">
                                </div>
                            </a>
                            <div class="book-card-body">
                                <span class="book-category"><?php echo htmlspecialchars($book['category']); ?></span>
                                <h4 class="book-title">
                                    <a href="book-detail.php?id=<?php echo $book['id']; ?>"><?php echo htmlspecialchars($book['title']); ?></a>
                                </h4>
                                <p class="book-author">Tác giả: <?php echo htmlspecialchars($book['author']); ?> </p>

                                <div class="rating-stars">
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
                                    <span class="rating-count">(<?php echo ($book['id'] * 15 + 4) % 60 + 5; ?>)</span>
                                </div>

                                <div class="book-price-row align-items-center">
                                    <div>
                                        <span class="book-price text-warning"><?php echo number_format($book['price'], 0, ',', '.'); ?> đ</span>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 align-items-center mt-2 w-100">
                                    <a href="index.php?buy_now=<?php echo $book['id']; ?>" class="btn btn-sm btn-warning text-dark font-weight-600 flex-grow-1 text-center d-flex align-items-center justify-content-center" style="border-radius: 20px; font-size: 0.75rem; transition: var(--transition-smooth); white-space: nowrap; height: 40px;" title="Mua ngay">Mua ngay</a>
                                    <a href="javascript:void(0);" class="btn-add-cart btn-add-to-cart-ajax" data-book-id="<?php echo $book['id']; ?>" title="Thêm vào giỏ hàng">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
</div> <!-- Đóng container danh sách sách -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInputs = document.querySelectorAll('.search-input');
    
    searchInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            // Đồng bộ giá trị giữa các ô tìm kiếm
            searchInputs.forEach(otherInput => {
                if (otherInput !== input) {
                    otherInput.value = e.target.value;
                }
            });
        });
    });

    // Xử lý thêm vào giỏ hàng bằng AJAX
    const addCartButtons = document.querySelectorAll('.btn-add-to-cart-ajax');
    addCartButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const bookId = this.getAttribute('data-book-id');
            
            const formData = new FormData();
            formData.append('book_id', bookId);
            
            fetch('api/add_to_cart_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Hiển thị Toast thông báo thành công
                    showToast(`${data.message} <a href="./cart.php" class="text-warning text-decoration-underline ms-1">Xem giỏ hàng</a>`, 'success');
                    
                    // Cập nhật số lượng trên badge giỏ hàng ở header
                    const badge = document.querySelector('.cart-badge');
                    if (badge) {
                        badge.innerText = data.cart_count;
                    }
                } else {
                    if (data.status === 'login_required') {
                        // Chuyển hướng sang trang login nếu chưa đăng nhập
                        window.location.href = 'login.php?status=login_required';
                    } else {
                        showToast(data.message, 'danger');
                    }
                }
            })
            .catch(err => {
                console.error('Lỗi khi thêm giỏ hàng:', err);
                showToast('Có lỗi xảy ra trong quá trình kết nối máy chủ.', 'danger');
            });
        });
    });
});
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';

?>
