<?php
require_once '../includes/config.php';

if (!isAdmin()) {
    redirect('login.php');
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    redirect('admin/orders.php');
}

// Lấy thông tin đơn hàng
$sql = "SELECT o.*, u.full_name as customer_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    redirect('admin/orders.php');
}

// Lấy chi tiết sản phẩm
$sql = "SELECT * FROM order_items WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['order_status'];
    $payment_status = $_POST['payment_status'];
    
    $update_sql = "UPDATE orders SET order_status = ?, payment_status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssi", $new_status, $payment_status, $order_id);
    
    if ($update_stmt->execute()) {
        $order['order_status'] = $new_status;
        $order['payment_status'] = $payment_status;
        $success = 'Cập nhật trạng thái thành công!';
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <h1><i class="fas fa-file-invoice"></i> Chi Tiết Đơn Hàng #<?php echo safe_html($order['order_number']); ?></h1>
    
    <div style="margin-bottom: 20px;">
        <a href="<?php echo SITE_URL; ?>/admin/orders.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> In đơn hàng
        </button>
    </div>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
</div>

<div class="container">
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
        <!-- Thông tin đơn hàng -->
        <div>
            <!-- Thông tin khách hàng -->
            <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px;">
                <h2 style="color: #2d5016; margin-bottom: 20px;">
                    <i class="fas fa-user"></i> Thông Tin Khách Hàng
                </h2>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <p style="color: #666; margin-bottom: 5px;">Họ tên:</p>
                        <p style="font-weight: 600; margin: 0;"><?php echo safe_html($order['full_name']); ?></p>
                    </div>
                    
                    <div>
                        <p style="color: #666; margin-bottom: 5px;">Email:</p>
                        <p style="font-weight: 600; margin: 0;"><?php echo safe_html($order['email']); ?></p>
                    </div>
                    
                    <div>
                        <p style="color: #666; margin-bottom: 5px;">Số điện thoại:</p>
                        <p style="font-weight: 600; margin: 0;"><?php echo safe_html($order['phone']); ?></p>
                    </div>
                    
                    <div>
                        <p style="color: #666; margin-bottom: 5px;">Ngày đặt:</p>
                        <p style="font-weight: 600; margin: 0;"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                    </div>
                </div>
                
                <div style="margin-top: 20px;">
                    <p style="color: #666; margin-bottom: 5px;">Địa chỉ giao hàng:</p>
                    <p style="font-weight: 600; margin: 0;"><?php echo safe_html($order['address']); ?></p>
                </div>
                
                <?php if ($order['note']): ?>
                <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                    <p style="color: #666; margin-bottom: 5px;">Ghi chú:</p>
                    <p style="margin: 0;"><?php echo safe_html($order['note']); ?></p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Chi tiết sản phẩm -->
            <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h2 style="color: #2d5016; margin-bottom: 20px;">
                    <i class="fas fa-box"></i> Chi Tiết Sản Phẩm
                </h2>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th style="text-align: center;">Số lượng</th>
                            <th style="text-align: right;">Đơn giá</th>
                            <th style="text-align: right;">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo safe_html($item['product_name']); ?></td>
                            <td style="text-align: center;"><?php echo $item['quantity']; ?></td>
                            <td style="text-align: right;"><?php echo formatMoney($item['price']); ?></td>
                            <td style="text-align: right; font-weight: 600;"><?php echo formatMoney($item['subtotal']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr style="background: #f8f9fa; font-weight: 700;">
                            <td colspan="3" style="text-align: right; font-size: 18px;">Tổng cộng:</td>
                            <td style="text-align: right; font-size: 20px; color: #e74c3c;">
                                <?php echo formatMoney($order['total_amount']); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Sidebar - Trạng thái & Actions -->
        <div>
            <!-- Cập nhật trạng thái -->
            <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px;">
                <h3 style="color: #2d5016; margin-bottom: 20px;">
                    <i class="fas fa-cog"></i> Cập Nhật Trạng Thái
                </h3>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Trạng thái đơn hàng:</label>
                        <select name="order_status" class="form-control">
                            <option value="pending" <?php echo $order['order_status'] == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                            <option value="processing" <?php echo $order['order_status'] == 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                            <option value="shipping" <?php echo $order['order_status'] == 'shipping' ? 'selected' : ''; ?>>Đang giao</option>
                            <option value="completed" <?php echo $order['order_status'] == 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                            <option value="cancelled" <?php echo $order['order_status'] == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Trạng thái thanh toán:</label>
                        <select name="payment_status" class="form-control">
                            <option value="pending" <?php echo $order['payment_status'] == 'pending' ? 'selected' : ''; ?>>Chưa thanh toán</option>
                            <option value="paid" <?php echo $order['payment_status'] == 'paid' ? 'selected' : ''; ?>>Đã thanh toán</option>
                            <option value="failed" <?php echo $order['payment_status'] == 'failed' ? 'selected' : ''; ?>>Thất bại</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="update_status" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-save"></i> Cập Nhật
                    </button>
                </form>
            </div>
            
            <!-- Thông tin thanh toán -->
            <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h3 style="color: #2d5016; margin-bottom: 20px;">
                    <i class="fas fa-money-bill-wave"></i> Thanh Toán
                </h3>
                
                <div style="margin-bottom: 15px;">
                    <p style="color: #666; margin-bottom: 5px;">Phương thức:</p>
                    <p style="font-weight: 600; margin: 0;">
                        <?php
                        $payment_methods = [
                            'cod' => 'Thanh toán khi nhận hàng',
                            'bank_transfer' => 'Chuyển khoản ngân hàng',
                            'momo' => 'Ví MoMo'
                        ];
                        echo $payment_methods[$order['payment_method']];
                        ?>
                    </p>
                </div>
                
                <div>
                    <p style="color: #666; margin-bottom: 5px;">Trạng thái:</p>
                    <?php
                    $payment_badges = [
                        'pending' => '<span style="background: #ffc107; color: white; padding: 5px 10px; border-radius: 10px; font-size: 12px;">Chưa thanh toán</span>',
                        'paid' => '<span style="background: #28a745; color: white; padding: 5px 10px; border-radius: 10px; font-size: 12px;">Đã thanh toán</span>',
                        'failed' => '<span style="background: #dc3545; color: white; padding: 5px 10px; border-radius: 10px; font-size: 12px;">Thất bại</span>'
                    ];
                    echo $payment_badges[$order['payment_status']];
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>