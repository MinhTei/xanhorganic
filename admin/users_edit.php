<?php
require_once '../includes/config.php';

if (!isAdmin()) {
    redirect('login.php');
}

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id <= 0) {
    redirect('admin/users.php');
}

// Lấy thông tin user
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    redirect('admin/users.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $role = $_POST['role'];
    $new_password = trim($_POST['new_password']);

    if (empty($full_name) || empty($email)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
    } elseif (!isValidEmail($email)) {
        $error = 'Email không hợp lệ!';
    } else {
        // Kiểm tra email trùng (trừ user hiện tại)
        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_email->bind_param("si", $email, $user_id);
        $check_email->execute();
        
        if ($check_email->get_result()->num_rows > 0) {
            $error = 'Email đã được sử dụng bởi người dùng khác!';
        } else {
            // Update thông tin
            if (!empty($new_password)) {
                // Có đổi mật khẩu
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, address = ?, role = ?, password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssi", $full_name, $email, $phone, $address, $role, $hashed_password, $user_id);
            } else {
                // Không đổi mật khẩu
                $sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, address = ?, role = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssi", $full_name, $email, $phone, $address, $role, $user_id);
            }

            if ($stmt->execute()) {
                $success = 'Cập nhật thông tin thành công!';
                // Reload user data
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
            } else {
                $error = 'Có lỗi xảy ra: ' . $conn->error;
            }
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <h1 style="color: #2d5016; margin-bottom: 20px;">
        <i class="fas fa-user-edit"></i> Chỉnh Sửa Người Dùng
    </h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <form method="POST">
            <div class="form-group">
                <label for="full_name">Họ và tên <span style="color: red;">*</span></label>
                <input type="text" id="full_name" name="full_name" required value="<?php echo safe_html($user['full_name']); ?>">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="email">Email <span style="color: red;">*</span></label>
                    <input type="email" id="email" name="email" required value="<?php echo safe_html($user['email']); ?>">
                </div>

                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo safe_html($user['phone']); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="address">Địa chỉ</label>
                <textarea id="address" name="address" rows="3"><?php echo safe_html($user['address']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="role">Vai trò</label>
                <select id="role" name="role">
                    <option value="customer" <?php echo $user['role'] == 'customer' ? 'selected' : ''; ?>>Khách hàng</option>
                    <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Quản trị viên</option>
                </select>
            </div>

            <div class="form-group">
                <label for="new_password">Mật khẩu mới (để trống nếu không đổi)</label>
                <input type="password" id="new_password" name="new_password" minlength="6" placeholder="Tối thiểu 6 ký tự">
                <small style="color: #666;">Chỉ điền nếu muốn thay đổi mật khẩu</small>
            </div>

            <!-- Thông tin bổ sung -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <h3 style="margin-bottom: 15px; color: #2d5016;">Thông tin tài khoản</h3>
                <div style="display: grid; gap: 10px;">
                    <div>
                        <strong>Ngày tạo:</strong> <?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?>
                    </div>
                    <div>
                        <strong>Cập nhật lần cuối:</strong> <?php echo date('d/m/Y H:i', strtotime($user['updated_at'])); ?>
                    </div>
                </div>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 15px;">
                <button type="submit" class="btn-submit" style="background-color: #28a745;">
                    <i class="fas fa-save"></i> Cập Nhật
                </button>
                <a href="<?php echo SITE_URL; ?>/admin/users.php" 
                   class="btn" 
                   style="background-color: #6c757d; color: white; padding: 15px;">
                    <i class="fas fa-arrow-left"></i> Quay Lại
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>