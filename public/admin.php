<?php
require_once __DIR__ . '/../includes/db_helper.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php?status=unauthorized");
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$errors = [];
$success_message = '';

// Xử lý cập nhật trạng thái đơn hàng (Admin)
if ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $status = trim($_POST['status'] ?? '');
    
    $valid_statuses = ['Chờ thanh toán', 'Chờ xác nhận', 'Đã xác nhận', 'Đang giao', 'Đã giao', 'Đã hủy'];
    if ($order_id > 0 && in_array($status, $valid_statuses)) {
        // Kiểm tra đơn hàng có thanh toán qua PayOS và đã xác nhận không
        global $pdo;
        $stmt = $pdo->prepare("SELECT status, payment_method FROM orders WHERE id = :id");
        $stmt->execute(['id' => $order_id]);
        $order_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($order_data && $order_data['payment_method'] === 'PayOS' && $order_data['status'] === 'Đã xác nhận' && $status === 'Đã hủy') {
            $errors['global'] = 'Đơn hàng thanh toán qua PayOS đã được xác nhận, không thể hủy.';
            $action = 'orders';
        } else {
            $success = false;
            if ($status === 'Đã hủy') {
                $success = cancel_order($order_id);
            } else {
                $success = update_order_status($order_id, $status);
            }
            
            if ($success) {
                header("Location: admin.php?action=orders&status=status_updated");
                exit;
            } else {
                $errors['global'] = 'Lỗi hệ thống! Không thể cập nhật trạng thái đơn hàng.';
                $action = 'orders';
            }
        }
    } else {
        $errors['global'] = 'Dữ liệu không hợp lệ.';
        $action = 'orders';
    }
}

// Xử lý Xóa sách
if ($action === 'delete') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (delete_book($id)) {
        header("Location: admin.php?status=deleted");
        exit;
    } else {
        $errors['global'] = 'Không tìm thấy sách để xóa hoặc lỗi ghi file.';
        $action = 'list';
    }
}

// Xử lý Thêm sách hoặc Sửa sách khi POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_book'])) {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $title = trim($_POST['title'] ?? '');
        $author = trim($_POST['author'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $image = trim($_POST['image'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $featured = isset($_POST['featured']) ? 1 : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 10;
        
        // Xử lý upload file ảnh nếu có tệp được chọn tải lên
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['image_file']['tmp_name'];
            $fileName = $_FILES['image_file']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $uploadDir = __DIR__ . '/uploads/books/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $dest_path = $uploadDir . $newFileName;
                
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $image = 'uploads/books/' . $newFileName;
                } else {
                    $errors['image_file'] = 'Có lỗi xảy ra khi lưu tệp ảnh lên máy chủ.';
                }
            } else {
                $errors['image_file'] = 'Định dạng file không hợp lệ. Chỉ chấp nhận các đuôi: JPG, JPEG, PNG, GIF, WEBP.';
            }
        }
        
        // Validate
        if (empty($title)) $errors['title'] = 'Tên sách không được bỏ trống.';
        if (empty($author)) $errors['author'] = 'Tác giả không được bỏ trống.';
        if (empty($category)) $errors['category'] = 'Thể loại không được bỏ trống.';
        if ($price <= 0) $errors['price'] = 'Giá bán phải lớn hơn 0.';
        if ($quantity < 0) $errors['quantity'] = 'Số lượng tồn kho không được nhỏ hơn 0.';
        
        if (empty($errors)) {
            if ($id > 0) {
                // Sửa sách
                if (update_book($id, $title, $author, $category, $price, $image, $description, $featured, $quantity)) {
                    header("Location: admin.php?status=updated");
                    exit;
                } else {
                    $errors['global'] = 'Lỗi hệ thống! Không thể cập nhật thông tin sách.';
                }
            } else {
                // Thêm sách mới
                if (add_book($title, $author, $category, $price, $image, $description, $featured, $quantity)) {
                    header("Location: admin.php?status=added");
                    exit;
                } else {
                    $errors['global'] = 'Lỗi hệ thống! Không thể thêm sách mới.';
                }
            }
        } else {
            // Khi có lỗi validate, giữ nguyên form add hoặc edit
            $action = ($id > 0) ? 'edit' : 'add';
        }
    }
}

// Thiết lập thông báo status từ GET
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'added') $success_message = 'Đã thêm sách mới thành công!';
    if ($_GET['status'] == 'updated') $success_message = 'Đã cập nhật thông tin sách thành công!';
    if ($_GET['status'] == 'deleted') $success_message = 'Đã xóa sách thành công!';
    if ($_GET['status'] == 'status_updated') $success_message = 'Đã cập nhật trạng thái đơn hàng thành công!';
}

// Đọc thông tin nếu ở chế độ sửa (edit)
$edit_book = null;
$edit_book_image_src = '';
if ($action === 'edit') {
    $edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $edit_book = get_book_by_id($edit_id);
    if (!is_array($edit_book) || !$edit_book) {
        $edit_book = [];
        $errors['global'] = 'Không tìm thấy cuốn sách yêu cầu.';
        $action = 'list';
    } elseif (!empty($edit_book['image'])) {
        $edit_book_image_src = (strpos($edit_book['image'], 'http') === 0 || strpos($edit_book['image'], 'uploads/') === 0)
            ? $edit_book['image']
            : 'uploads/books/' . $edit_book['image'];
    }
}

// Lấy danh sách sách và đơn hàng
$books = get_books();
$orders = get_orders();

if (!is_array($books)) {
    $books = [];
}

if (!is_array($orders)) {
    $orders = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="text-white" style="font-family: var(--font-heading);"><i class="fas fa-tools text-warning me-2"></i>Bảng Quản Trị Cửa Hàng</h2>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="admin.php?action=list" class="btn btn-secondary-custom me-2 <?php echo ($action === 'list') ? 'active' : ''; ?>">Danh sách sách</a>
            <a href="admin.php?action=orders" class="btn btn-secondary-custom me-2 <?php echo ($action === 'orders') ? 'active' : ''; ?>">Quản lý đơn hàng</a>
            <a href="admin.php?action=add" class="btn btn-primary-custom <?php echo ($action === 'add') ? 'active' : ''; ?>"><i class="fas fa-plus"></i> Thêm sách mới</a>
        </div>
    </div>

    <!-- Thông báo kết quả bằng Toast -->
    <?php if ($success_message): ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('<?php echo addslashes($success_message); ?>', 'success');
        });
        </script>
    <?php endif; ?>
    <?php if (isset($errors['global'])): ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('<?php echo addslashes($errors['global']); ?>', 'danger');
        });
        </script>
    <?php endif; ?>

    <!-- CHẾ ĐỘ 1: DANH SÁCH SÁCH -->
    <?php if ($action === 'list'): ?>
        <div class="glass-panel admin-card table-responsive">
            <h4 class="text-white mb-4">Danh sách sản phẩm sách</h4>
            
            <!-- Bộ lọc và tìm kiếm sách -->
            <div class="d-flex flex-wrap gap-3 mb-4 align-items-center">
                <div class="flex-grow-1" style="max-width: 320px;">
                    <div class="position-relative">
                        <input type="text" id="admin-search-book" class="form-control-custom" placeholder="Tìm tên sách hoặc tác giả..." style="padding-left: 2.5rem;">
                        <i class="fas fa-search position-absolute text-muted" style="left: 1rem; top: 50%; transform: translateY(-50%);"></i>
                    </div>
                </div>
                <div>
                    <select id="admin-filter-category" class="form-select form-control-custom" style="min-width: 220px;">
                        <option value="">-- Tất cả thể loại --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <table class="table admin-table align-middle">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th style="width: 70px;">Bìa</th>
                        <th>Tên sách / Tác giả</th>
                        <th>Thể loại</th>
                        <th>Giá bán</th>
                        <th>Tồn kho</th>
                        <th>Nổi bật</th>
                        <th style="width: 150px; text-align: center;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($books)): ?>
                        <tr>
                             <td colspan="8" class="text-center text-muted py-4">Chưa có cuốn sách nào trong cửa hàng.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($books as $b): ?>
                             <tr>
                                <td><?php echo $b['id']; ?></td>
                                <td>
                                    <img src="<?php echo htmlspecialchars(!empty($b['image']) ? ((strpos($b['image'], 'http') === 0 || strpos($b['image'], 'uploads/') === 0) ? $b['image'] : 'uploads/' . $b['image']) : 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=500'); ?>" class="admin-book-img" alt="<?php echo htmlspecialchars($b['title']); ?>">
                                </td>
                                <td>
                                    <strong class="text-white"><?php echo htmlspecialchars($b['title']); ?></strong><br>
                                    <small class="text-muted">Tác giả: <?php echo htmlspecialchars($b['author']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($b['category']); ?></td>
                                <td><strong class="text-warning"><?php echo number_format($b['price'], 0, ',', '.'); ?> đ</strong></td>
                                <td><span class="badge <?php echo ($b['quantity'] > 0) ? 'bg-info' : 'bg-danger'; ?>"><?php echo $b['quantity']; ?></span></td>
                                <td>
                                    <?php echo (isset($b['featured']) && $b['featured'] == 1) ? '<span class="badge bg-success">Có</span>' : '<span class="badge bg-secondary">Không</span>'; ?>
                                </td>
                                <td class="text-center">
                                    <a href="admin.php?action=edit&id=<?php echo $b['id']; ?>" class="btn btn-sm btn-action-edit me-1" title="Sửa"><i class="fas fa-edit"></i></a>
                                    <a href="javascript:void(0);" class="btn btn-sm btn-action-delete btn-delete-book" data-id="<?php echo $b['id']; ?>" title="Xóa"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Javascript thực hiện tìm kiếm và lọc dữ liệu sách mượt mà -->
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('admin-search-book');
                const categorySelect = document.getElementById('admin-filter-category');
                const tableRows = document.querySelectorAll('.admin-table tbody tr');

                if (searchInput && categorySelect) {
                    function filterBooks() {
                        const searchText = searchInput.value.toLowerCase().trim();
                        const selectedCat = categorySelect.value;

                        tableRows.forEach(row => {
                            if (row.querySelector('td[colspan]')) return;

                            const titleStrong = row.cells[2].querySelector('strong');
                            const authorSmall = row.cells[2].querySelector('small');
                            const categoryCell = row.cells[3];

                            if (!titleStrong || !categoryCell) return;

                            const titleText = titleStrong.textContent.toLowerCase();
                            const authorText = authorSmall ? authorSmall.textContent.toLowerCase() : '';
                            const categoryText = categoryCell.textContent.trim();

                            const matchesSearch = titleText.includes(searchText) || authorText.includes(searchText);
                            const matchesCategory = selectedCat === '' || categoryText === selectedCat;

                            if (matchesSearch && matchesCategory) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    }

                    searchInput.addEventListener('input', filterBooks);
                    categorySelect.addEventListener('change', filterBooks);
                }

                // Xử lý xóa sách bằng AJAX không cần reload trang
                const deleteButtons = document.querySelectorAll('.btn-delete-book');
                deleteButtons.forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const bookId = this.getAttribute('data-id');
                        const row = this.closest('tr');
                        
                        if (confirm('Bạn có chắc chắn muốn xóa cuốn sách này không?')) {
                            const formData = new FormData();
                            formData.append('id', bookId);
                            
                            fetch('api/delete_book_ajax.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    // Xóa hàng tương ứng trên bảng với hiệu ứng mượt mà
                                    row.style.transition = 'all 0.4s ease';
                                    row.style.opacity = '0';
                                    row.style.transform = 'scale(0.95)';
                                    setTimeout(() => {
                                        row.remove();
                                        // Hiển thị thông báo nếu bảng trống
                                        const tableRows = document.querySelectorAll('.admin-table tbody tr');
                                        if (tableRows.length === 0 || (tableRows.length === 1 && tableRows[0].querySelector('td[colspan]'))) {
                                            const tbody = document.querySelector('.admin-table tbody');
                                            if (tbody) {
                                                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Chưa có cuốn sách nào trong cửa hàng.</td></tr>';
                                            }
                                        }
                                    }, 400);
                                } else {
                                    showToast(data.message, 'danger');
                                }
                            })
                            .catch(err => {
                                console.error('Lỗi khi xóa sách:', err);
                                showToast('Có lỗi xảy ra trong quá trình kết nối máy chủ.', 'danger');
                            });
                        }
                    });
                });
            });
            </script>
        </div>

    <!-- CHẾ ĐỘ 2: THÊM / SỬA SÁCH -->
    <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <div class="glass-panel p-4 max-width-800 mx-auto">
            <h4 class="text-white mb-4">
                <?php echo ($action === 'edit') ? 'Cập nhật thông tin sách (ID: ' . $edit_book['id'] . ')' : 'Thêm sách mới vào cửa hàng'; ?>
            </h4>
            
            <form action="admin.php?action=<?php echo $action; ?>" method="POST" enctype="multipart/form-data">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_book['id']; ?>">
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6 form-group-custom">
                        <label for="title">Tên sách *</label>
                        <input type="text" id="title" name="title" class="form-control-custom" 
                               value="<?php echo htmlspecialchars($_POST['title'] ?? $edit_book['title'] ?? ''); ?>" required>
                        <?php if (isset($errors['title'])): ?>
                            <span class="text-danger fs-7"><?php echo $errors['title']; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 form-group-custom">
                        <label for="author">Tác giả *</label>
                        <input type="text" id="author" name="author" class="form-control-custom" 
                               value="<?php echo htmlspecialchars($_POST['author'] ?? $edit_book['author'] ?? ''); ?>" required>
                        <?php if (isset($errors['author'])): ?>
                            <span class="text-danger fs-7"><?php echo $errors['author']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group-custom">
                        <label for="category">Thể loại *</label>
                        <?php
                        $categories_list = [
                            'Tâm lý - Kỹ năng sống',
                            'Tiểu thuyết',
                            'Công nghệ thông tin',
                            'Văn học Việt Nam',
                            'Khoa học vũ trụ',
                            'Tài chính cá nhân'
                        ];
                        ?>
                        <select id="category" name="category" class="form-select form-control-custom" required>
                            <option value="">-- Chọn thể loại --</option>
                            <?php foreach ($categories_list as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php 
                                    $current_cat = $_POST['category'] ?? $edit_book['category'] ?? '';
                                    if ($current_cat === $cat) echo 'selected'; 
                                ?>><?php echo htmlspecialchars($cat); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['category'])): ?>
                            <span class="text-danger fs-7"><?php echo $errors['category']; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 form-group-custom">
                        <label for="price">Giá bán (VND) *</label>
                        <input type="number" id="price" name="price" class="form-control-custom" 
                               value="<?php echo htmlspecialchars($_POST['price'] ?? $edit_book['price'] ?? ''); ?>" min="1" required>
                        <?php if (isset($errors['price'])): ?>
                            <span class="text-danger fs-7"><?php echo $errors['price']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group-custom">
                        <label for="quantity">Số lượng tồn kho *</label>
                        <input type="number" id="quantity" name="quantity" class="form-control-custom" 
                               value="<?php echo htmlspecialchars($_POST['quantity'] ?? $edit_book['quantity'] ?? '10'); ?>" min="0" required>
                        <?php if (isset($errors['quantity'])): ?>
                            <span class="text-danger fs-7"><?php echo $errors['quantity']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group-custom">
                    <label for="image_file">Tải ảnh bìa lên (Tệp từ máy tính)</label>
                    <input type="file" id="image_file" name="image_file" class="form-control-custom" accept="image/*">
                    <?php if (isset($errors['image_file'])): ?>
                        <span class="text-danger fs-7"><?php echo $errors['image_file']; ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group-custom">
                    <label for="image">Hoặc nhập đường dẫn ảnh trực tuyến (URL)</label>
                    <input type="text" id="image" name="image" class="form-control-custom" 
                           placeholder="https://example.com/image.jpg"
                           value="<?php echo htmlspecialchars($_POST['image'] ?? $edit_book['image'] ?? ''); ?>">
                    <?php if ($action === 'edit' && !empty($edit_book['image'])): ?>
                        <div class="mt-2">
                            <small class="text-muted d-block mb-2">Ảnh hiện tại:</small>
                            <img src="<?php echo htmlspecialchars($edit_book_image_src); ?>" alt="<?php echo htmlspecialchars($edit_book['title']); ?>" class="admin-book-img mb-2" style="max-width: 120px; height: auto; object-fit: cover;">
                            <small class="text-muted d-block"><code><?php echo htmlspecialchars($edit_book['image']); ?></code></small>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group-custom">
                    <label for="description">Mô tả sách</label>
                    <textarea id="description" name="description" class="form-control-custom" rows="5"><?php echo htmlspecialchars($_POST['description'] ?? $edit_book['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-check mb-4 mt-3">
                    <input type="checkbox" class="form-check-input" id="featured" name="featured" <?php 
                        if (isset($_POST['featured']) || (isset($edit_book['featured']) && $edit_book['featured'] == 1)) echo 'checked'; 
                    ?>>
                    <label class="form-check-label text-white" for="featured">Đánh dấu là sách nổi bật (Hiển thị đầu trang chủ)</label>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" name="save_book" class="btn btn-primary-custom px-4 py-2">
                        <i class="fas fa-save me-2"></i> Lưu lại
                    </button>
                    <a href="admin.php" class="btn btn-secondary-custom px-4 py-2">Hủy bỏ</a>
                </div>
            </form>
        </div>

    <!-- CHẾ ĐỘ 3: QUẢN LÝ ĐƠN HÀNG -->
    <?php elseif ($action === 'orders'): ?>
        <div class="glass-panel admin-card">
            <h4 class="text-white mb-4"><i class="fas fa-receipt text-warning me-2"></i>Danh sách đơn hàng của khách hàng</h4>
            
            <?php if (empty($orders)): ?>
                <div class="text-center text-muted py-4">Chưa có đơn hàng nào được đặt.</div>
            <?php else: ?>
                <!-- Bộ lọc và tìm kiếm đơn hàng -->
                <div class="d-flex flex-wrap gap-3 mb-4 align-items-center">
                    <div class="flex-grow-1" style="max-width: 320px;">
                        <div class="position-relative">
                            <input type="text" id="admin-search-order" class="form-control-custom" placeholder="Mã đơn hoặc tên khách..." style="padding-left: 2.5rem;">
                            <i class="fas fa-search position-absolute text-muted" style="left: 1rem; top: 50%; transform: translateY(-50%);"></i>
                        </div>
                    </div>
                    <div>
                        <select id="admin-filter-order-status" class="form-select form-control-custom" style="min-width: 200px;">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="Chờ thanh toán">Chờ thanh toán</option>
                            <option value="Chờ xác nhận">Chờ xác nhận</option>
                            <option value="Đã xác nhận">Đã xác nhận</option>
                            <option value="Đang giao">Đang giao</option>
                            <option value="Đã giao">Đã giao</option>
                            <option value="Đã hủy">Đã hủy</option>
                        </select>
                    </div>
                    <div>
                        <select id="admin-filter-order-payment" class="form-select form-control-custom" style="min-width: 200px;">
                            <option value="">-- Tất cả thanh toán --</option>
                            <option value="COD">COD</option>
                            <option value="PayOS">PayOS</option>
                            <option value="Thẻ tín dụng">Thẻ tín dụng</option>
                            <option value="QR">QR / VietQR</option>
                        </select>
                    </div>
                </div>

                <?php foreach (array_reverse($orders) as $order): 
                    $status_class = '';
                    if ($order['status'] === 'Chờ thanh toán') $status_class = 'status-pending-payment';
                    elseif ($order['status'] === 'Chờ xác nhận') $status_class = 'status-pending-confirm';
                    elseif ($order['status'] === 'Đã xác nhận') $status_class = 'status-confirmed';
                    elseif ($order['status'] === 'Đang giao') $status_class = 'status-delivering';
                    elseif ($order['status'] === 'Đã giao') $status_class = 'status-delivered';
                    elseif ($order['status'] === 'Đã hủy') $status_class = 'status-cancelled';
                ?>
                    <div class="order-box <?php echo $status_class; ?> mb-4" 
                         data-order-id="<?php echo $order['id']; ?>" 
                         data-customer-name="<?php echo htmlspecialchars(strtolower($order['customer_name'])); ?>"
                         data-status="<?php echo htmlspecialchars($order['status']); ?>"
                         data-payment="<?php echo htmlspecialchars($order['payment_method']); ?>">
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <h5 class="text-warning mb-1">Mã đơn hàng: #<?php echo $order['id']; ?></h5>
                                <small class="text-muted"><i class="far fa-clock me-1"></i> Ngày đặt: <?php echo $order['created_at']; ?></small>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <span class="text-white">Tổng giá trị:</span>
                                <h4 class="text-white d-inline-block ms-2"><?php echo number_format($order['total_price'], 0, ',', '.'); ?> đ</h4>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <span class="text-muted d-block">Họ tên người nhận:</span>
                                <strong class="text-white"><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted d-block">Số điện thoại:</span>
                                <strong class="text-white"><?php echo htmlspecialchars($order['customer_phone']); ?></strong>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted d-block">Địa chỉ nhận hàng:</span>
                                <strong class="text-white"><?php echo htmlspecialchars($order['customer_address']); ?></strong>
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <div class="col-md-3">
                                <span class="text-muted d-block" style="font-size: 0.85rem;">Hình thức thanh toán:</span>
                                <strong class="text-white" style="font-size: 0.95rem;">
                                    <?php 
                                    $pm_icon = 'fa-money-bill-wave text-success';
                                    if ($order['payment_method'] === 'Ví điện tử') $pm_icon = 'fa-wallet text-info';
                                    elseif ($order['payment_method'] === 'Thẻ tín dụng') $pm_icon = 'fa-credit-card text-primary';
                                    elseif ($order['payment_method'] === 'QR') $pm_icon = 'fa-qrcode text-warning';
                                    elseif ($order['payment_method'] === 'PayOS') $pm_icon = 'fa-wallet text-danger';
                                    ?>
                                    <i class="fas <?php echo $pm_icon; ?> me-1"></i>
                                    <?php echo htmlspecialchars($order['payment_method']); ?>
                                </strong>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted d-block" style="font-size: 0.85rem;">Trạng thái hiện tại:</span>
                                <?php 
                                $badge_class = 'bg-secondary';
                                if ($order['status'] === 'Chờ thanh toán') $badge_class = 'bg-dark text-white border border-secondary';
                                elseif ($order['status'] === 'Chờ xác nhận') $badge_class = 'bg-warning text-dark';
                                elseif ($order['status'] === 'Đã xác nhận') $badge_class = 'bg-primary';
                                elseif ($order['status'] === 'Đang giao') $badge_class = 'bg-info text-dark';
                                elseif ($order['status'] === 'Đã giao') $badge_class = 'bg-success';
                                elseif ($order['status'] === 'Đã hủy') $badge_class = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $badge_class; ?> px-2 py-1 mt-1"><?php echo htmlspecialchars($order['status']); ?></span>
                            </div>
                            <div class="col-md-6 mt-3 mt-md-0 d-flex flex-wrap gap-2 align-items-center">
                                <?php if ($order['status'] !== 'Đã giao' && $order['status'] !== 'Đã hủy'): ?>
                                    <?php
                                    $next_status = '';
                                    $next_label = '';
                                    $next_btn_class = 'btn-primary-custom';
                                    $next_icon = 'fa-check';
                                    
                                    if ($order['status'] === 'Chờ thanh toán') {
                                        $next_status = 'Chờ xác nhận';
                                        $next_label = 'Xác nhận đã thanh toán';
                                        $next_btn_class = 'btn-warning text-dark';
                                        $next_icon = 'fa-money-bill-wave';
                                    } elseif ($order['status'] === 'Chờ xác nhận') {
                                        $next_status = 'Đã xác nhận';
                                        $next_label = 'Xác nhận đơn hàng';
                                        $next_btn_class = 'btn-primary-custom';
                                        $next_icon = 'fa-check-circle';
                                    } elseif ($order['status'] === 'Đã xác nhận') {
                                        $next_status = 'Đang giao';
                                        $next_label = 'Bắt đầu giao hàng';
                                        $next_btn_class = 'btn-secondary-custom'; // Style cho giao diện sáng
                                        $next_icon = 'fa-shipping-fast';
                                    } elseif ($order['status'] === 'Đang giao') {
                                        $next_status = 'Đã giao';
                                        $next_label = 'Hoàn thành giao hàng';
                                        $next_btn_class = 'btn-success text-white';
                                        $next_icon = 'fa-check-double';
                                    }
                                    ?>
                                    
                                    <?php if (!empty($next_status)): ?>
                                        <form action="admin.php?action=update_status" method="POST" class="d-inline">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <input type="hidden" name="status" value="<?php echo $next_status; ?>">
                                            <button type="submit" class="btn <?php echo $next_btn_class; ?> btn-sm py-1 px-3" style="border-radius: 8px; font-weight: 500;">
                                                <i class="fas <?php echo $next_icon; ?> me-1"></i> <?php echo $next_label; ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if (!($order['payment_method'] === 'PayOS' && $order['status'] === 'Đã xác nhận')): ?>
                                    <form action="admin.php?action=update_status" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này không? Sách sẽ được hoàn trả vào kho.');" class="d-inline">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <input type="hidden" name="status" value="Đã hủy">
                                        <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-3" style="border-radius: 8px; font-weight: 500;">
                                            <i class="fas fa-times me-1"></i> Hủy đơn hàng
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size: 0.9rem;"><i class="fas fa-info-circle me-1"></i>Đơn hàng đã hoàn thành hoặc đã hủy.</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="bg-black bg-opacity-25 p-2 rounded">
                            <h6 class="text-white border-bottom pb-2 mb-2" style="font-size: 0.9rem;">Sách đã đặt mua:</h6>
                            <table class="table table-sm table-borderless text-white mb-0">
                                <thead>
                                    <tr class="text-muted" style="font-size: 0.85rem;">
                                        <th>Tên sách</th>
                                        <th style="width: 100px; text-align: center;">Số lượng</th>
                                        <th style="width: 150px; text-align: right;">Đơn giá</th>
                                        <th style="width: 150px; text-align: right;">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order['items'] as $item): ?>
                                        <tr style="font-size: 0.9rem;">
                                            <td><?php echo htmlspecialchars($item['title']); ?></td>
                                            <td class="text-center"><?php echo $item['quantity']; ?></td>
                                            <td class="text-right text-muted" style="text-align: right;"><?php echo number_format($item['price'], 0, ',', '.'); ?> đ</td>
                                            <td class="text-right" style="text-align: right;"><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> đ</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Javascript thực hiện tìm kiếm và lọc dữ liệu đơn hàng mượt mà -->
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('admin-search-order');
                const statusSelect = document.getElementById('admin-filter-order-status');
                const paymentSelect = document.getElementById('admin-filter-order-payment');
                const orderCards = document.querySelectorAll('.order-box');

                if (searchInput && statusSelect && paymentSelect) {
                    function filterOrders() {
                        const searchText = searchInput.value.toLowerCase().trim();
                        const selectedStatus = statusSelect.value;
                        const selectedPayment = paymentSelect.value;

                        orderCards.forEach(card => {
                            const orderId = card.getAttribute('data-order-id') || '';
                            const customerName = card.getAttribute('data-customer-name') || '';
                            const status = card.getAttribute('data-status') || '';
                            const payment = card.getAttribute('data-payment') || '';

                            const matchesSearch = searchText === '' || orderId.includes(searchText) || customerName.includes(searchText);
                            const matchesStatus = selectedStatus === '' || status === selectedStatus;
                            
                            // Hỗ trợ lọc theo loại payment (COD, PayOS, Thẻ tín dụng, QR)
                            let matchesPayment = false;
                            if (selectedPayment === '') {
                                matchesPayment = true;
                            } else if (selectedPayment === 'QR') {
                                matchesPayment = payment === 'QR' || payment === 'Ví điện tử';
                            } else {
                                matchesPayment = payment === selectedPayment;
                            }

                            if (matchesSearch && matchesStatus && matchesPayment) {
                                card.style.display = '';
                            } else {
                                card.style.display = 'none';
                            }
                        });
                    }

                    searchInput.addEventListener('input', filterOrders);
                    statusSelect.addEventListener('change', filterOrders);
                    paymentSelect.addEventListener('change', filterOrders);
                }
            });
            </script>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
