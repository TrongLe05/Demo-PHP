<?php
require_once __DIR__ . '/db_helper.php';

// Đã đăng nhập rồi thì chuyển về trang chủ
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $errors['global'] = 'Vui lòng điền đầy đủ email và mật khẩu.';
    } else {
        $user = get_user_by_email($email);
        
        if ($user && (password_verify($password, $user['password']) || $password === $user['password'])) {
            // Đăng nhập thành công, thiết lập session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['fullname'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // Chuyển hướng
            if ($user['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $errors['global'] = 'Email hoặc mật khẩu không chính xác.';
        }
    }
}

require_once __DIR__ . '/header.php';
?>

<div class="container">
    <div class="glass-panel auth-container">
        <h2 class="auth-title text-white">Đăng Nhập</h2>
        
        <!-- Thông báo đăng ký thành công hoặc quyền truy cập -->
        <?php if (isset($_GET['status']) && $_GET['status'] == 'registered'): ?>
            <div class="alert alert-custom alert-success-custom d-flex align-items-center gap-2 mb-3">
                <i class="fas fa-check-circle"></i>
                <span>Đăng ký tài khoản thành công! Hãy đăng nhập.</span>
            </div>
        <?php elseif (isset($_GET['status']) && $_GET['status'] == 'unauthorized'): ?>
            <div class="alert alert-custom alert-danger-custom d-flex align-items-center gap-2 mb-3">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Bạn cần đăng nhập bằng quyền Admin để truy cập trang đó.</span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($errors['global'])): ?>
            <div class="alert alert-custom alert-danger-custom d-flex align-items-center gap-2 mb-3">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $errors['global']; ?></span>
            </div>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
            <div class="form-group-custom">
                <label for="email">Địa chỉ Email</label>
                <input type="email" id="email" name="email" class="form-control-custom" 
                       value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            
            <div class="form-group-custom">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" class="form-control-custom" required>
            </div>
            
            <button type="submit" class="btn btn-primary-custom w-100 py-3 mt-3">
                <i class="fas fa-sign-in-alt me-2"></i> Đăng nhập
            </button>
        </form>
        
        <div class="text-center mt-4">
            <span class="text-muted">Chưa có tài khoản?</span>
            <a href="register.php" class="text-warning text-decoration-none ms-1">Đăng ký ngay</a>
        </div>
        
        <hr style="border-color: var(--glass-border);" class="my-4">
        
        <div class="text-center text-muted">
            <small>Tài khoản Quản trị mẫu:<br><strong>admin@bookstore.com</strong> / mật khẩu: <strong>admin123</strong></small>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/footer.php';
?>
