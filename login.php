<?php
/**
 * LOGIN.PHP - Trang đăng nhập với logic redirect theo role
 * 
 * Flow:
 * 1. Kiểm tra đã đăng nhập chưa -> redirect về trang chủ
 * 2. Xác thực thông tin đăng nhập
 * 3. Nếu là admin -> redirect đến admin/index.php
 * 4. Nếu là customer -> redirect đến trang yêu cầu hoặc trang chủ
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Nếu đã đăng nhập rồi, redirect về trang tương ứng
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/index.php'); // Admin -> trang admin
    } else {
        redirect('index.php'); // Customer -> trang chủ
    }
}

$error = '';
$success = '';

// Xử lý form đăng nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validate cơ bản
    if (empty($email) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    } elseif (!isValidEmail($email)) {
        $error = 'Email không hợp lệ!';
    } else {
        // Truy vấn user từ database
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Kiểm tra password bằng password_verify()
            if (password_verify($password, $user['password'])) {
                // ===== ĐĂNG NHẬP THÀNH CÔNG =====
                
                // Lưu thông tin vào session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role']; // 'admin' hoặc 'customer'
                
                // Thiết lập admin flag nếu là admin
                if ($user['role'] === 'admin') {
                    $_SESSION['admin_logged_in'] = true;
                }
                
                // Chuyển giỏ hàng từ session vào database (nếu có)
                if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $product_id => $quantity) {
                        addToCart($product_id, $quantity);
                    }
                    unset($_SESSION['cart']); // Xóa giỏ hàng session sau khi đã lưu vào DB
                }
                
                // ===== LOGIC REDIRECT THEO ROLE =====
                if ($user['role'] === 'admin') {
                
                    redirect('admin/index.php');
                } else {
                    
                    if (isset($_GET['redirect'])) {
                        redirect($_GET['redirect']);
                    } else {
                        redirect('index.php');
                    }
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
        
        <!-- Hiển thị thông báo lỗi -->
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <!-- Thông báo đăng ký thành công (từ register.php) -->
        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Đăng ký thành công! Vui lòng đăng nhập.
            </div>
        <?php endif; ?>
        
        <!-- Form đăng nhập -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                       placeholder="Nhập email của bạn"
                       autocomplete="email">
            </div>
            
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Mật khẩu
                </label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required
                       placeholder="Nhập mật khẩu"
                       autocomplete="current-password">
            </div>
            
            <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                <label style="margin: 0; display: flex; align-items: center;">
                    <input type="checkbox" name="remember" style="width: auto; margin-right: 5px;">
                    Ghi nhớ đăng nhập
                </label>
                <a href="forgot_password.php" style="color: #90c33c;">Quên mật khẩu?</a>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-sign-in-alt"></i> Đăng Nhập
            </button>
        </form>
        
        <!-- Link đăng ký -->
        <div class="form-footer">
            <p>Chưa có tài khoản? 
                <a href="<?php echo SITE_URL; ?>/register.php">
                    Đăng ký ngay <i class="fas fa-arrow-right"></i>
                </a>
            </p>
        </div>
        
        <!-- Đăng nhập với mạng xã hội -->
        <div style="text-align: center; margin-top: 30px; padding-top: 30px; border-top: 1px solid #eee;">
            <p style="color: #666; margin-bottom: 15px;">Hoặc đăng nhập với</p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button type="button" style="flex: 1; padding: 10px; background: #4267B2; color: white; border-radius: 5px; border: none; cursor: pointer;">
                    <i class="fab fa-facebook"></i> Facebook
                </button>
                <button type="button" style="flex: 1; padding: 10px; background: #DB4437; color: white; border-radius: 5px; border: none; cursor: pointer;">
                    <i class="fab fa-google"></i> Google
                </button>
            </div>
        </div>
        
        <!-- Debug info (CHỈ dùng khi development) -->
        <?php if (defined('DEBUG_MODE') && DEBUG_MODE === true): ?>
        <div style="margin-top: 30px; padding: 15px; background: #f0f0f0; border-radius: 5px; font-size: 12px;">
            <strong>Debug Info:</strong><br>
            Admin test: admin@xanhorganic.com / password<br>
            Customer test: customer@example.com / password
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>