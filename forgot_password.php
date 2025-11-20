<?php
/**
 * forgot_password.php - Form yêu cầu reset mật khẩu (simple mode)
 *
 * Behavior: tạo token selector+validator, lưu vào bảng `password_resets`,
 * rồi HIỂN THỊ trực tiếp liên kết reset trên trang để bạn có thể nhấn thử.
 * Không cố gắng gửi email (undo composer/PHPMailer changes).
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
        // Tìm user
        $sql = "SELECT id, email, full_name FROM users WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Thông báo chung cho UX (không tiết lộ existence)
        $success = 'Nếu email tồn tại trong hệ thống, chúng tôi đã gửi hướng dẫn đặt lại mật khẩu.';

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Tạo bảng password_resets nếu chưa có (an toàn)
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

            // Tạo selector + validator và lưu hash của validator
            $selector = bin2hex(random_bytes(6));
            $validator = bin2hex(random_bytes(32));
            $token_hash = hash('sha256', $validator);
            $expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 giờ
            $created_at = date('Y-m-d H:i:s');

            $ins = "INSERT INTO password_resets (user_id, selector, token_hash, expires_at, created_at) VALUES (?, ?, ?, ?, ?)";
            $s2 = $conn->prepare($ins);
            $s2->bind_param('issss', $user['id'], $selector, $token_hash, $expires_at, $created_at);
            $s2->execute();

            // Tạo link reset và hiển thị trực tiếp trên trang
            $reset_link = rtrim(SITE_URL, '/') . '/reset_password.php?selector=' . $selector . '&validator=' . $validator;
            $debug_link = $reset_link; // always show link for simple testing
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
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php';
/**
 * forgot_password.php - Form yêu cầu reset mật khẩu
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';
$success = '';
 // ĐƠN GIẢN: không gửi email, hiển thị trực tiếp link trên trang để tiện test (undo composer/PHPMailer)
 $debug_link = $reset_link;
 $mail_sent = false;
$debug_link = '';
$debug_info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Vui lòng nhập email hợp lệ.';
    } else {
        // Tìm user
        $sql = "SELECT id, email, full_name FROM users WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Thông báo trung tính cho UX
        $success = 'Nếu email tồn tại trong hệ thống, chúng tôi đã gửi hướng dẫn đặt lại mật khẩu.';

        if ($result->num_rows === 0) {
            // Không tiết lộ user không tồn tại
        } else {
            $user = $result->fetch_assoc();

            // Tạo bảng password_resets (an toàn, không DROP dữ liệu hiện có)
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
            // Tạo selector + validator
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
            // ĐƠN GIẢN: hiển thị trực tiếp link trên trang để tiện test (undo composer/PHPMailer)
            $debug_link = $reset_link;  
        }
    }
}
require_once 'includes/header.php';
?>
<div class="container">
    