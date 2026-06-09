<?php
require_once __DIR__ . '/db_helper.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php?status=unauthorized");
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$errors = [];
$success_message = '';

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
        
        // Xử lý upload file ảnh nếu có tệp được chọn tải lên
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['image_file']['tmp_name'];
            $fileName = $_FILES['image_file']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $dest_path = $uploadDir . $newFileName;
                
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $image = 'uploads/' . $newFileName;
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
        
        if (empty($errors)) {
            if ($id > 0) {
                // Sửa sách
                if (update_book($id, $title, $author, $category, $price, $image, $description, $featured)) {
                    header("Location: admin.php?status=updated");
                    exit;
                } else {
                    $errors['global'] = 'Lỗi hệ thống! Không thể cập nhật thông tin sách.';
                }
            } else {
                // Thêm sách mới
                if (add_book($title, $author, $category, $price, $image, $description, $featured)) {
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
}

// Đọc thông tin nếu ở chế độ sửa (edit)
$edit_book = null;
if ($action === 'edit') {
    $edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $edit_book = get_book_by_id($edit_id);
    if (!$edit_book) {
        $errors['global'] = 'Không tìm thấy cuốn sách yêu cầu.';
        $action = 'list';
    }
}

// Lấy danh sách sách và đơn hàng
$books = get_books();
$orders = get_orders();

require_once __DIR__ . '/header.php';
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

    <!-- Thông báo kết quả -->
    <?php if ($success_message): ?>
        <div class="alert alert-custom alert-success-custom d-flex align-items-center gap-2 mb-4">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $success_message; ?></span>
        </div>
    <?php endif; ?>
    <?php if (isset($errors['global'])): ?>
        <div class="alert alert-custom alert-danger-custom d-flex align-items-center gap-2 mb-4">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $errors['global']; ?></span>
        </div>
    <?php endif; ?>

    <!-- CHẾ ĐỘ 1: DANH SÁCH SÁCH -->
    <?php if ($action === 'list'): ?>
        <div class="glass-panel admin-card table-responsive">
            <h4 class="text-white mb-4">Danh sách sản phẩm sách</h4>
            <table class="table admin-table align-middle">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th style="width: 70px;">Bìa</th>
                        <th>Tên sách / Tác giả</th>
                        <th>Thể loại</th>
                        <th>Giá bán</th>
                        <th>Nổi bật</th>
                        <th style="width: 150px; text-align: center;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($books)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Chưa có cuốn sách nào trong cửa hàng.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($books as $b): ?>
                            <tr>
                                <td><?php echo $b['id']; ?></td>
                                <td>
                                    <img src="<?php echo htmlspecialchars($b['image']); ?>" style="width: 45px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid var(--glass-border);" alt="">
                                </td>
                                <td>
                                    <strong class="text-white"><?php echo htmlspecialchars($b['title']); ?></strong><br>
                                    <small class="text-muted">Tác giả: <?php echo htmlspecialchars($b['author']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($b['category']); ?></td>
                                <td><strong class="text-warning"><?php echo number_format($b['price'], 0, ',', '.'); ?> đ</strong></td>
                                <td>
                                    <?php echo (isset($b['featured']) && $b['featured'] == 1) ? '<span class="badge bg-success">Có</span>' : '<span class="badge bg-secondary">Không</span>'; ?>
                                </td>
                                <td class="text-center">
                                    <a href="admin.php?action=edit&id=<?php echo $b['id']; ?>" class="btn btn-sm btn-secondary-custom me-1" title="Sửa"><i class="fas fa-edit text-info"></i></a>
                                    <a href="admin.php?action=delete&id=<?php echo $b['id']; ?>" class="btn btn-sm btn-secondary-custom" onclick="return confirm('Bạn có chắc chắn muốn xóa cuốn sách này không?');" title="Xóa"><i class="fas fa-trash-alt text-danger"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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
                        <input type="text" id="category" name="category" class="form-control-custom" 
                               placeholder="Ví dụ: Kỹ năng sống, Công nghệ, Tiểu thuyết..."
                               value="<?php echo htmlspecialchars($_POST['category'] ?? $edit_book['category'] ?? ''); ?>" required>
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

                <div class="form-group-custom">
                    <label for="image_file">Tải ảnh bìa lên (Tệp từ máy tính)</label>
                    <input type="file" id="image_file" name="image_file" class="form-control-custom" accept="image/*">
                    <?php if (isset($errors['image_file'])): ?>
                        <span class="text-danger fs-7"><?php echo $errors['image_file']; ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group-custom">
                    <label for="image">Hoặc nhập đường dẫn ảnh trực tuyến (URL)</label>
                    <input type="url" id="image" name="image" class="form-control-custom" 
                           placeholder="https://example.com/image.jpg"
                           value="<?php echo htmlspecialchars($_POST['image'] ?? $edit_book['image'] ?? ''); ?>">
                    <?php if ($action === 'edit' && !empty($edit_book['image'])): ?>
                        <small class="text-muted d-block mt-1">Ảnh hiện tại: <code><?php echo htmlspecialchars($edit_book['image']); ?></code></small>
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
                <?php foreach (array_reverse($orders) as $order): ?>
                    <div class="order-box p-3 mb-4 rounded border" style="background: rgba(255, 255, 255, 0.01); border-color: var(--glass-border) !important;">
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
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/footer.php';
?>
