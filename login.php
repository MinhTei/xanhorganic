<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Nếu đã đăng nhập, chuyển về trang chủ
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validate
    if (empty($email) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    } elseif (!isValidEmail($email)) {
        $error = 'Email không hợp lệ!';
    } else {
        // Kiểm tra user
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Kiểm tra password
            if (password_verify($password, $user['password'])) {
                // Đăng nhập thành công
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Chuyển giỏ hàng từ session vào database
                if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $product_id => $quantity) {
                        addToCart($product_id, $quantity);
                    }
                    unset($_SESSION['cart']);
                }
                
                // Redirect
                if (isset($_GET['redirect'])) {
                    redirect($_GET['redirect']);
                } else {
                    redirect('index.php');
                }
            } else {
                $error = 'Mật khẩu không đúng!';
            }
        } else {
            $error = 'Email không tồn tại trong hệ thống!';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h2 style="text-align: center; color: #2d5016; margin-bottom: 30px;">
            <i class="fas fa-sign-in-alt"></i> Đăng Nhập
        </h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Đăng ký thành công! Vui lòng đăng nhập.
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email
                </label>
                <input type="email" id="email" name="email" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                       placeholder="Nhập email của bạn">
            </div>
            
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Mật khẩu
                </label>
                <input type="password" id="password" name="password" required
                       placeholder="Nhập mật khẩu">
            </div>
            
            <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                <label style="margin: 0;">
                    <input type="checkbox" name="remember" style="width: auto; margin-right: 5px;">
                    Ghi nhớ đăng nhập
                </label>
                <a href="#" style="color: #90c33c;">Quên mật khẩu?</a>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-sign-in-alt"></i> Đăng Nhập
            </button>
        </form>
        
        <div class="form-footer">
            <p>Chưa có tài khoản? 
                <a href="<?php echo SITE_URL; ?>/register.php">
                    Đăng ký ngay <i class="fas fa-arrow-right"></i>
                </a>
            </p>
        </div>
        
        <div style="text-align: center; margin-top: 30px; padding-top: 30px; border-top: 1px solid #eee;">
            <p style="color: #666; margin-bottom: 15px;">Hoặc đăng nhập với</p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button style="flex: 1; padding: 10px; background: #4267B2; color: white; border-radius: 5px;">
                    <i class="fab fa-facebook"></i> Facebook
                </button>
                <button style="flex: 1; padding: 10px; background: #DB4437; color: white; border-radius: 5px;">
                    <i class="fab fa-google"></i> Google
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>