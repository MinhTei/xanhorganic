<?php
require_once 'includes/config.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirect('login.php');
}

require_once 'includes/header.php';

$user = getCurrentUser();
$error = '';
$success = '';

// Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    } elseif (strlen($new_password) < 6) {
        $error = 'Mật khẩu mới phải có ít nhất 6 ký tự!';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp!';
    } else {
        // Kiểm tra mật khẩu hiện tại
        if (password_verify($current_password, $user['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $success = 'Đổi mật khẩu thành công!';
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại!';
            }
        } else {
            $error = 'Mật khẩu hiện tại không đúng!';
        }
    }
}
?>

<div class="container">
    <div style="padding: 20px 0; color: #666;">
        <a href="<?php echo SITE_URL; ?>" style="color: #90c33c;">Trang chủ</a>
        <i class="fas fa-chevron-right" style="margin: 0 10px; font-size: 12px;"></i>
        <a href="<?php echo SITE_URL; ?>/profile.php" style="color: #90c33c;">Tài khoản của tôi</a>
        <i class="fas fa-chevron-right" style="margin: 0 10px; font-size: 12px;"></i>
        <span>Đổi mật khẩu</span>
    </div>
    
    <h1 style="color: #2d5016; margin-bottom: 30px;">
        <i class="fas fa-lock"></i> Đổi Mật Khẩu
    </h1>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 300px 1fr; gap: 30px;">
        <!-- Sidebar -->
        <div>
            <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <div style="text-align: center; margin-bottom: 25px;">
                    <div style="width: 100px; height: 100px; margin: 0 auto 15px; background: linear-gradient(135deg, #2d5016, #90c33c); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; color: white;">
                        <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                    </div>
                    <h3 style="color: #2d5016; margin-bottom: 5px;"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                    <p style="color: #666; font-size: 14px;"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                
                <nav style="list-style: none; padding: 0;">
                    <a href="profile.php#profile-info" style="display: block; padding: 15px; color: #666; border-left: 3px solid transparent; margin-bottom: 5px; border-radius: 5px; transition: all 0.3s;"
                       onmouseover="this.style.background='#f8f9fa'; this.style.borderLeftColor='#90c33c'"
                       onmouseout="this.style.background='transparent'; this.style.borderLeftColor='transparent'">
                        <i class="fas fa-user" style="width: 25px;"></i> Thông tin cá nhân
                    </a>
                    <a href="profile.php#orders" style="display: block; padding: 15px; color: #666; border-left: 3px solid transparent; margin-bottom: 5px; border-radius: 5px; transition: all 0.3s;"
                       onmouseover="this.style.background='#f8f9fa'; this.style.borderLeftColor='#90c33c'"
                       onmouseout="this.style.background='transparent'; this.style.borderLeftColor='transparent'">
                        <i class="fas fa-shopping-bag" style="width: 25px;"></i> Đơn hàng của tôi
                    </a>
                    <a href="editpassword.php" style="display: block; padding: 15px; color: #333; border-left: 3px solid #90c33c; background: #f8f9fa; margin-bottom: 5px; border-radius: 5px;">
                        <i class="fas fa-lock" style="width: 25px;"></i> Đổi mật khẩu
                    </a>
                    <a href="<?php echo SITE_URL; ?>/logout.php" style="display: block; padding: 15px; color: #e74c3c; border-left: 3px solid transparent; border-radius: 5px; transition: all 0.3s;"
                       onmouseover="this.style.background='#f8f9fa'"
                       onmouseout="this.style.background='transparent'"
                       onclick="return confirm('Bạn có chắc muốn đăng xuất?')">
                        <i class="fas fa-sign-out-alt" style="width: 25px;"></i> Đăng xuất
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div style="background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <form method="POST" style="max-width: 500px;">
                <div class="form-group">
                    <label for="current_password">Mật khẩu hiện tại</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">Mật khẩu mới</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6">
                    <small style="color: #666; font-size: 13px;">Tối thiểu 6 ký tự</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Xác nhận mật khẩu mới</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>
                
                <button type="submit" name="change_password" class="btn-submit">
                    <i class="fas fa-key"></i> Đổi Mật Khẩu
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>