<?php
/**
 * reset_password.php - Xử lý thay đổi mật khẩu qua token
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';
$success = '';
// Use selector + validator pattern
$selector = isset($_GET['selector']) ? $_GET['selector'] : (isset($_POST['selector']) ? $_POST['selector'] : '');
$validator = isset($_GET['validator']) ? $_GET['validator'] : (isset($_POST['validator']) ? $_POST['validator'] : '');

if (empty($selector) || empty($validator)) {
    $error = 'Mã hợp lệ không được cung cấp.';
} else {
    // Tìm bản ghi theo selector
    $sql = "SELECT pr.id, pr.user_id, pr.token_hash, pr.expires_at, u.email FROM password_resets pr JOIN users u ON pr.user_id = u.id WHERE pr.selector = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $selector);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        $error = 'Token không hợp lệ hoặc đã được sử dụng.';
    } else {
        $row = $res->fetch_assoc();
        $expires = strtotime($row['expires_at']);
        if ($expires < time()) {
            $error = 'Token đã hết hạn. Vui lòng yêu cầu đặt lại mật khẩu mới.';
        } else {
            // Kiểm tra validator
            $calc_hash = hash('sha256', $validator);
            if (!hash_equals($row['token_hash'], $calc_hash)) {
                $error = 'Token không hợp lệ.';
            } else {
                // Nếu gửi form để cập nhật mật khẩu
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $password = isset($_POST['password']) ? $_POST['password'] : '';
                    $password2 = isset($_POST['password2']) ? $_POST['password2'] : '';

                    if (empty($password) || strlen($password) < 6) {
                        $error = 'Mật khẩu phải ít nhất 6 ký tự.';
                    } elseif ($password !== $password2) {
                        $error = 'Mật khẩu nhập lại không khớp.';
                    } else {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $up = "UPDATE users SET password = ? WHERE id = ?";
                        $s2 = $conn->prepare($up);
                        $s2->bind_param('si', $hash, $row['user_id']);
                        $s2->execute();

                        // Xóa tất cả token cho user (invalidate mọi token)
                        $del = "DELETE FROM password_resets WHERE user_id = ?";
                        $s3 = $conn->prepare($del);
                        $s3->bind_param('i', $row['user_id']);
                        $s3->execute();

                        $success = 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập bằng mật khẩu mới.';
                    }
                }
            }
        }
    }
}

require_once 'includes/header.php';
?>
<div class="container">
    <div class="form-container">
        <h2 style="text-align: center; color: #2d5016; margin-bottom: 30px;"><i class="fas fa-key"></i> Đặt lại mật khẩu</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
            <div style="margin-top:10px;"><a href="login.php">Quay lại đăng nhập</a></div>
        <?php elseif (empty($error)): ?>
            <form method="POST" action="">
                <input type="hidden" name="selector" value="<?php echo htmlspecialchars($selector); ?>">
                <input type="hidden" name="validator" value="<?php echo htmlspecialchars($validator); ?>">
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Mật khẩu mới</label>
                    <input type="password" id="password" name="password" required placeholder="Nhập mật khẩu mới" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label for="password2"><i class="fas fa-lock"></i> Nhập lại mật khẩu</label>
                    <input type="password" id="password2" name="password2" required placeholder="Nhập lại mật khẩu" autocomplete="new-password">
                </div>
                <button type="submit" class="btn-submit"><i class="fas fa-check"></i> Cập nhật mật khẩu</button>
            </form>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'includes/footer.php';
