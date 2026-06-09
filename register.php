<?php
require_once __DIR__ . '/db_helper.php';

// Đã đăng nhập rồi thì chuyển về trang chủ
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$errors = [];
$fullname = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate
    if (empty($fullname)) {
        $errors['fullname'] = 'Họ tên không được để trống.';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email không được để trống.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email không đúng định dạng.';
    } elseif (get_user_by_email($email) !== null) {
        $errors['email'] = 'Email này đã được đăng ký sử dụng.';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Mật khẩu không được để trống.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Mật khẩu phải chứa ít nhất 6 ký tự.';
    }
    
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Xác nhận mật khẩu không trùng khớp.';
    }
    
    if (empty($errors)) {
        $new_user = register_user($fullname, $email, $password);
        if ($new_user) {
            header("Location: login.php?status=registered");
            exit;
        } else {
            $errors['global'] = 'Có lỗi xảy ra trong quá trình ghi dữ liệu. Vui lòng thử lại.';
        }
    }
}

require_once __DIR__ . '/header.php';
?>

<div class="container">
    <div class="glass-panel auth-container">
        <h2 class="auth-title text-white">Đăng Ký Tài Khoản</h2>
        
        <?php if (isset($errors['global'])): ?>
            <div class="alert alert-custom alert-danger-custom d-flex align-items-center gap-2 mb-3">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $errors['global']; ?></span>
            </div>
        <?php endif; ?>
        
        <form action="register.php" method="POST">
            <div class="form-group-custom">
                <label for="fullname">Họ tên của bạn *</label>
                <input type="text" id="fullname" name="fullname" class="form-control-custom" 
                       value="<?php echo htmlspecialchars($fullname); ?>" required>
                <?php if (isset($errors['fullname'])): ?>
                    <span class="text-danger fs-7 d-block mt-1"><?php echo $errors['fullname']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group-custom">
                <label for="email">Địa chỉ Email *</label>
                <input type="email" id="email" name="email" class="form-control-custom" 
                       value="<?php echo htmlspecialchars($email); ?>" required>
                <?php if (isset($errors['email'])): ?>
                    <span class="text-danger fs-7 d-block mt-1"><?php echo $errors['email']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group-custom">
                <label for="password">Mật khẩu *</label>
                <input type="password" id="password" name="password" class="form-control-custom" required>
                <?php if (isset($errors['password'])): ?>
                    <span class="text-danger fs-7 d-block mt-1"><?php echo $errors['password']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group-custom">
                <label for="confirm_password">Nhập lại mật khẩu *</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control-custom" required>
                <?php if (isset($errors['confirm_password'])): ?>
                    <span class="text-danger fs-7 d-block mt-1"><?php echo $errors['confirm_password']; ?></span>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn btn-primary-custom w-100 py-3 mt-3">
                <i class="fas fa-user-plus me-2"></i> Đăng ký ngay
            </button>
        </form>
        
        <div class="text-center mt-4">
            <span class="text-muted">Đã có tài khoản?</span>
            <a href="login.php" class="text-warning text-decoration-none ms-1">Đăng nhập</a>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/footer.php';
?>
