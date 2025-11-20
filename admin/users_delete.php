<?php
require_once '../includes/config.php';

if (!isAdmin()) {
    redirect('login.php');
}

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Không cho xóa chính mình
if ($user_id <= 0 || $user_id == $_SESSION['user_id']) {
    $_SESSION['error_message'] = 'Không thể xóa tài khoản này!';
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

// Đếm số đơn hàng
$check_orders = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
$check_orders->bind_param("i", $user_id);
$check_orders->execute();
$order_count = $check_orders->get_result()->fetch_assoc()['count'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    // Xóa giỏ hàng của user
    $conn->query("DELETE FROM cart WHERE user_id = $user_id");
    
    // Xóa user (đơn hàng vẫn giữ lại)
    $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $delete_stmt->bind_param("i", $user_id);
    
    if ($delete_stmt->execute()) {
        $_SESSION['success_message'] = 'Đã xóa người dùng thành công!';
        redirect('admin/users.php');
    } else {
        $error = 'Có lỗi xảy ra khi xóa người dùng!';
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <h1 style="color: #dc3545; margin-bottom: 30px;">
        <i class="fas fa-user-times"></i> Xóa Người Dùng
    </h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div style="background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto;">
        
        <?php if ($order_count > 0): ?>
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin-bottom: 30px; border-radius: 5px;">
                <h3 style="color: #856404; margin-bottom: 10px;">
                    <i class="fas fa-exclamation-triangle"></i> Lưu ý!
                </h3>
                <p style="color: #856404; margin: 0;">
                    Người dùng này có <strong><?php echo $order_count; ?> đơn hàng</strong>. 
                    Các đơn hàng sẽ được giữ lại sau khi xóa tài khoản.
                </p>
            </div>
        <?php endif; ?>

        <div style="display: flex; gap: 30px; align-items: start;">
            <!-- Avatar -->
            <div style="flex-shrink: 0;">
                <div style="width: 150px; height: 150px; background: linear-gradient(135deg, #2d5016, #90c33c); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 60px; color: white; font-weight: 700;">
                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                </div>
            </div>

            <!-- Thông tin user -->
            <div style="flex: 1;">
                <h2 style="color: #2d5016; margin-bottom: 20px;">
                    <?php echo safe_html($user['full_name']); ?>
                </h2>
                
                <div style="display: grid; gap: 15px;">
                    <div>
                        <strong>Email:</strong> <?php echo safe_html($user['email']); ?>
                    </div>
                    
                    <div>
                        <strong>Số điện thoại:</strong> <?php echo safe_html($user['phone'] ?? 'Chưa cập nhật'); ?>
                    </div>
                    
                    <div>
                        <strong>Vai trò:</strong> 
                        <?php if ($user['role'] == 'admin'): ?>
                            <span style="background: #e74c3c; color: white; padding: 5px 10px; border-radius: 10px; font-size: 12px;">
                                Quản trị viên
                            </span>
                        <?php else: ?>
                            <span style="background: #28a745; color: white; padding: 5px 10px; border-radius: 10px; font-size: 12px;">
                                Khách hàng
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <strong>Số đơn hàng:</strong> 
                        <span style="color: #2d5016; font-weight: 600;"><?php echo $order_count; ?> đơn</span>
                    </div>
                    
                    <div>
                        <strong>Ngày tạo:</strong> <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Xác nhận xóa -->
        <div style="margin-top: 40px; padding-top: 30px; border-top: 2px solid #eee;">
            <h3 style="color: #dc3545; margin-bottom: 20px;">
                <i class="fas fa-question-circle"></i> Bạn có chắc chắn muốn xóa người dùng này?
            </h3>
            
            <p style="color: #666; margin-bottom: 30px;">
                Hành động này không thể hoàn tác. Tài khoản sẽ bị xóa vĩnh viễn khỏi hệ thống.
            </p>

            <form method="POST" style="display: flex; gap: 15px;">
                <button type="submit" name="confirm_delete" 
                        class="btn btn-delete" 
                        style="background: #dc3545; padding: 15px 30px;">
                    <i class="fas fa-user-times"></i> Xác Nhận Xóa
                </button>
                
                <a href="<?php echo SITE_URL; ?>/admin/users.php" 
                   class="btn btn-secondary" 
                   style="padding: 15px 30px;">
                    <i class="fas fa-times"></i> Hủy Bỏ
                </a>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>