<?php
/**
 * forgot_password.php
 * Hiển thị link reset nếu email tồn tại, ngược lại thông báo chưa có tài khoản.
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';
$success = '';
$debug_link = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Vui lòng nhập email hợp lệ.';
    } else {
        // Tìm user theo email
        $sql = "SELECT id, email, full_name FROM users WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            $error = 'Đã xảy ra lỗi hệ thống. Vui lòng thử lại sau.';
        } elseif ($result->num_rows === 0) {
            // Nếu không tìm thấy, hiện thông báo rõ theo yêu cầu
            $error = 'Bạn chưa có tài khoản với email này.';
        } else {
            $user = $result->fetch_assoc();

            // Tạo bảng password_resets nếu chưa có
            $create = "CREATE TABLE IF NOT EXISTS password_resets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                selector CHAR(12) NOT NULL,
                token_hash CHAR(64) NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at DATETIME NOT NULL,
                INDEX (selector),
                INDEX (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $conn->query($create);

            // Tạo selector + validator, lưu hash validator
            $selector = bin2hex(random_bytes(6));
            $validator = bin2hex(random_bytes(32));
            $token_hash = hash('sha256', $validator);
            $expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 giờ
            $created_at = date('Y-m-d H:i:s');

            $ins = "INSERT INTO password_resets (user_id, selector, token_hash, expires_at, created_at) VALUES (?, ?, ?, ?, ?)";
            $s2 = $conn->prepare($ins);
            $s2->bind_param('issss', $user['id'], $selector, $token_hash, $expires_at, $created_at);
            $s2->execute();

            // Tạo link reset
            $reset_link = rtrim(SITE_URL, '/') . '/reset_password.php?selector=' . $selector . '&validator=' . $validator;

            // Gửi email (dùng PHP mail() nếu không cấu hình SMTP/PHPMailer)
            $to = $user['email'];
            $subject = 'Đặt lại mật khẩu - ' . SITE_NAME;
            $message = "Xin chào " . ($user['full_name'] ?: $user['email']) . ",\n\n";
            $message .= "Vui lòng bấm vào liên kết dưới đây để đặt lại mật khẩu (liên kết hết hạn sau 1 giờ):\n";
            $message .= $reset_link . "\n\n";
            $message .= "Nếu bạn không yêu cầu đặt lại mật khẩu, hãy bỏ qua email này.";

            $headers = "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM . ">\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/plain; charset=UTF-8\r\n";

            $mail_sent = false;
            // Nếu DEBUG_MODE bật, vẫn hiển thị link trên trang để tiện dev
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                $debug_link = $reset_link;
                $mail_sent = false;
            } else {
                // Thử gửi bằng mail(); trên môi trường Windows/WAMP cần cấu hình php.ini hoặc sendmail wrapper
                $mail_sent = @mail($to, $subject, $message, $headers);
                if (!$mail_sent) {
                    // Nếu gửi thất bại, cho hiện link tạm thời và cảnh báo cấu hình
                    $debug_link = $reset_link;
                }
            }

            if ($mail_sent) {
                $success = 'Chúng tôi đã gửi liên kết đặt lại mật khẩu tới email của bạn. Vui lòng kiểm tra hộp thư (cả thư rác).';
            } else {
                if (empty($debug_link)) {
                    // Nếu không có debug_link, chỉ báo lỗi hệ thống
                    $error = 'Không thể gửi email. Vui lòng thử lại hoặc liên hệ quản trị.';
                } else {
                    $success = 'Không gửi được email tự động — hiển thị liên kết tạm thời để test.';
                }
            }
        }
    }
}

require_once 'includes/header.php';
?>
<div class="container">
    <div class="form-container">
        <h2 style="text-align: center; color: #2d5016; margin-bottom: 30px;"><i class="fas fa-unlock-alt"></i> Quên mật khẩu</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (!empty($debug_link)): ?>
            <div style="margin-top:10px; font-size:13px; background:#f8f8f8; padding:10px; border-radius:4px;">
                <strong>Reset link (test):</strong>
                <div style="margin-top:6px; word-break:break-word;"><a href="<?php echo htmlspecialchars($debug_link); ?>"><?php echo htmlspecialchars($debug_link); ?></a></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" required placeholder="Nhập email của bạn" autocomplete="email">
            </div>
            <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Gửi hướng dẫn</button>
        </form>

        <div class="form-footer" style="margin-top:15px;">
            <a href="login.php">Quay lại Đăng nhập</a>
            <?php if (!empty($error) && strpos($error, 'Bạn chưa có tài khoản') !== false): ?>
                <div style="margin-top:8px;"><a href="register.php" class="btn-link">Đăng ký ngay</a></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php';
