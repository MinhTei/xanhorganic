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

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    if (empty($full_name)) {
        $error = 'Vui lòng nhập họ tên!';
    } elseif ($phone && !isValidPhone($phone)) {
        $error = 'Số điện thoại không hợp lệ!';
    } else {
        $sql = "UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $full_name, $phone, $address, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $_SESSION['full_name'] = $full_name;
            $success = 'Cập nhật thông tin thành công!';
            $user = getCurrentUser(); // Refresh user data
        } else {
            $error = 'Có lỗi xảy ra, vui lòng thử lại!';
        }
    }
}



// Lấy danh sách đơn hàng
$orders_sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
$orders_stmt = $conn->prepare($orders_sql);
$orders_stmt->bind_param("i", $_SESSION['user_id']);
$orders_stmt->execute();
$orders = $orders_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="container">
    <div style="padding: 20px 0; color: #666;">
        <a href="<?php echo SITE_URL; ?>" style="color: #90c33c;">Trang chủ</a>
        <i class="fas fa-chevron-right" style="margin: 0 10px; font-size: 12px;"></i>
        <span>Tài khoản của tôi</span>
    </div>
    
    <h1 style="color: #2d5016; margin-bottom: 30px;">
        <i class="fas fa-user-circle"></i> Tài Khoản Của Tôi
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
                    <a href="#profile-info" style="display: block; padding: 15px; color: #333; border-left: 3px solid #90c33c; background: #f8f9fa; margin-bottom: 5px; border-radius: 5px;">
                        <i class="fas fa-user" style="width: 25px;"></i> Thông tin cá nhân
                    </a>
                    <a href="#orders" style="display: block; padding: 15px; color: #666; border-left: 3px solid transparent; margin-bottom: 5px; border-radius: 5px; transition: all 0.3s;"
                       onmouseover="this.style.background='#f8f9fa'; this.style.borderLeftColor='#90c33c'"
                       onmouseout="this.style.background='transparent'; this.style.borderLeftColor='transparent'">
                        <i class="fas fa-shopping-bag" style="width: 25px;"></i> Đơn hàng của tôi
                    </a>
                    <a href="editpassword.php" style="display: block; padding: 15px; color: #666; border-left: 3px solid transparent; margin-bottom: 5px; border-radius: 5px; transition: all 0.3s;"
                       onmouseover="this.style.background='#f8f9fa'; this.style.borderLeftColor='#90c33c'"
                       onmouseout="this.style.background='transparent'; this.style.borderLeftColor='transparent'">
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
        <div>
            <!-- Thông tin cá nhân -->
            <div id="profile-info" style="background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px;">
                <h2 style="color: #2d5016; margin-bottom: 25px;">
                    <i class="fas fa-user"></i> Thông Tin Cá Nhân
                </h2>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="full_name">Họ và tên</label>
                        <input type="text" id="full_name" name="full_name" required
                               value="<?php echo htmlspecialchars($user['full_name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled
                               style="background: #f8f9fa; cursor: not-allowed;">
                        <small style="color: #666; font-size: 13px;">Email không thể thay đổi</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Số điện thoại</label>
                        <input type="tel" id="phone" name="phone"
                               value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Địa chỉ</label>
                        <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn-submit">
                        <i class="fas fa-save"></i> Cập Nhật Thông Tin
                    </button>
                </form>
            </div>

            <!-- Đơn hàng -->
            <div id="orders" style="background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px;">
                <h2 style="color: #2d5016; margin-bottom: 25px;">
                    <i class="fas fa-shopping-bag"></i> Đơn Hàng Của Tôi
                </h2>
                
                <?php if (empty($orders)): ?>
                    <div style="text-align: center; padding: 50px; color: #666;">
                        <i class="fas fa-shopping-bag" style="font-size: 60px; color: #ddd; margin-bottom: 20px;"></i>
                        <p>Bạn chưa có đơn hàng nào</p>
                        <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary" style="margin-top: 20px;">
                            Mua sắm ngay
                        </a>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f8f9fa;">
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #ddd;">Mã đơn hàng</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #ddd;">Ngày đặt</th>
                                    <th style="padding: 15px; text-align: right; border-bottom: 2px solid #ddd;">Tổng tiền</th>
                                    <th style="padding: 15px; text-align: center; border-bottom: 2px solid #ddd;">Trạng thái</th>
                                    <th style="padding: 15px; text-align: center; border-bottom: 2px solid #ddd;">Thanh toán</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 15px;">
                                        <strong style="color: #2d5016;"><?php echo $order['order_number']; ?></strong>
                                    </td>
                                    <td style="padding: 15px; color: #666;">
                                        <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                    </td>
                                    <td style="padding: 15px; text-align: right;">
                                        <strong style="color: #e74c3c;"><?php echo formatMoney($order['total_amount']); ?></strong>
                                    </td>
                                    <td style="padding: 15px; text-align: center;">
                                        <?php
                                        $status_colors = [
                                            'pending' => '#ffc107',
                                            'processing' => '#17a2b8',
                                            'shipping' => '#007bff',
                                            'completed' => '#28a745',
                                            'cancelled' => '#dc3545'
                                        ];
                                        
                                        $status_labels = [
                                            'pending' => 'Chờ xử lý',
                                            'processing' => 'Đang xử lý',
                                            'shipping' => 'Đang giao',
                                            'completed' => 'Hoàn thành',
                                            'cancelled' => 'Đã hủy'
                                        ];
                                        
                                        $color = $status_colors[$order['order_status']];
                                        $label = $status_labels[$order['order_status']];
                                        ?>
                                        <span style="background: <?php echo $color; ?>; color: white; padding: 5px 12px; border-radius: 15px; font-size: 13px; font-weight: 500;">
                                            <?php echo $label; ?>
                                        </span>
                                    </td>
                                    <td style="padding: 15px; text-align: center;">
                                        <?php
                                        $payment_labels = [
                                            'pending' => 'Chưa thanh toán',
                                            'paid' => 'Đã thanh toán',
                                            'failed' => 'Thất bại'
                                        ];
                                        echo $payment_labels[$order['payment_status']];
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>


        </div>
    </div>
</div>

<script>
// Smooth scroll to section
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            
            // Update active state
            document.querySelectorAll('nav a').forEach(link => {
                link.style.borderLeftColor = 'transparent';
                link.style.background = 'transparent';
            });
            this.style.borderLeftColor = '#90c33c';
            this.style.background = '#f8f9fa';
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>