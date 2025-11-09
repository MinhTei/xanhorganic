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
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    } elseif (!isValidEmail($email)) {
        $error = 'Email không hợp lệ!';
    } elseif ($phone && !isValidPhone($phone)) {
        $error = 'Số điện thoại không hợp lệ!';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp!';
    } else {
        // Kiểm tra email đã tồn tại
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = 'Email đã được sử dụng!';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $sql = "INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $full_name, $email, $phone, $hashed_password);
            
            if ($stmt->execute()) {
                // Đăng ký thành công
                redirect('login.php?registered=1');
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại!';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h2 style="text-align: center; color: #2d5016; margin-bottom: 30px;">
            <i class="fas fa-user-plus"></i> Đăng Ký Tài Khoản
        </h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="full_name">
                    <i class="fas fa-user"></i> Họ và tên <span style="color: red;">*</span>
                </label>
                <input type="text" id="full_name" name="full_name" required
                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                       placeholder="Nhập họ và tên đầy đủ">
            </div>
            
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email <span style="color: red;">*</span>
                </label>
                <input type="email" id="email" name="email" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                       placeholder="example@email.com">
            </div>
            
            <div class="form-group">
                <label for="phone">
                    <i class="fas fa-phone"></i> Số điện thoại
                </label>
                <input type="tel" id="phone" name="phone"
                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                       placeholder="0901234567">
            </div>
            
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Mật khẩu <span style="color: red;">*</span>
                </label>
                <input type="password" id="password" name="password" required
                       placeholder="Ít nhất 6 ký tự"
                       minlength="6">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">
                    <i class="fas fa-lock"></i> Xác nhận mật khẩu <span style="color: red;">*</span>
                </label>
                <input type="password" id="confirm_password" name="confirm_password" required
                       placeholder="Nhập lại mật khẩu"
                       minlength="6">
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: flex-start; gap: 10px;">
                    <input type="checkbox" name="agree" required style="width: auto; margin-top: 5px;">
                    <span>Tôi đồng ý với 
                        <a href="#" style="color: #90c33c;">Điều khoản sử dụng</a> và 
                        <a href="#" style="color: #90c33c;">Chính sách bảo mật</a>
                    </span>
                </label>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-user-plus"></i> Đăng Ký
            </button>
        </form>
        
        <div class="form-footer">
            <p>Đã có tài khoản? 
                <a href="<?php echo SITE_URL; ?>/login.php">
                    Đăng nhập ngay <i class="fas fa-arrow-right"></i>
                </a>
            </p>
        </div>
        
        <div style="text-align: center; margin-top: 30px; padding-top: 30px; border-top: 1px solid #eee;">
            <p style="color: #666; margin-bottom: 15px;">Hoặc đăng ký với</p>
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