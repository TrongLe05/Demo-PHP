<?php
require_once __DIR__ . '/../includes/db_helper.php';

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
        $errors['global'] = 'Vui lòng điền đầy đủ tên đăng nhập/email và mật khẩu.';
    } else {
        // Thử tìm theo email trước, nếu không có thì tìm theo username
        $user = get_user_by_email($email);
        if (!$user) {
            $user = get_user_by_username($email);
        }
        
        if ($user && (password_verify($password, $user['password']) || md5($password) === $user['password'] || $password === $user['password'])) {
            // Đăng nhập thành công, thiết lập session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['fullname'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // Đồng bộ giỏ hàng từ DB vào Session
            sync_db_cart_to_session($user['id']);
            
            // Chuyển hướng
            if ($user['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $errors['global'] = 'Tên đăng nhập/email hoặc mật khẩu không chính xác.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="glass-panel auth-container">
        <h2 class="auth-title text-white">Đăng Nhập</h2>
        
        <!-- Thông báo đăng ký thành công hoặc quyền truy cập bằng Toast -->
        <?php if (isset($_GET['status']) && $_GET['status'] == 'registered'): ?>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('Đăng ký tài khoản thành công! Hãy đăng nhập.', 'success');
            });
            </script>
        <?php elseif (isset($_GET['status']) && $_GET['status'] == 'unauthorized'): ?>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('Bạn cần đăng nhập bằng quyền Admin để truy cập trang đó.', 'danger');
            });
            </script>
        <?php elseif (isset($_GET['status']) && $_GET['status'] == 'login_required'): ?>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('Bạn cần đăng nhập tài khoản trước khi thực hiện mua sách hoặc thêm vào giỏ hàng.', 'danger');
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
        
        <form action="login.php" method="POST">
            <div class="form-group-custom">
                <label for="email">Tên đăng nhập hoặc Email</label>
                <input type="text" id="email" name="email" class="form-control-custom" 
                       placeholder="Nhập tên đăng nhập hoặc email..."
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
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
