<?php
require_once 'includes/config.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirect('login.php');
}

require_once 'includes/header.php';

// Lấy mã đơn hàng
$order_number = isset($_GET['order']) ? trim($_GET['order']) : '';

if (empty($order_number)) {
    redirect('index.php');
}

// Lấy thông tin đơn hàng
$sql = "SELECT * FROM orders WHERE order_number = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $order_number, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    redirect('index.php');
}

// Lấy chi tiết đơn hàng
$sql = "SELECT * FROM order_items WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order['id']);
$stmt->execute();
$order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="container">
    <div style="max-width: 800px; margin: 50px auto; text-align: center;">
        <!-- Icon thành công -->
        <div style="width: 120px; height: 120px; margin: 0 auto 30px; background: linear-gradient(135deg, #28a745, #90c33c); border-radius: 50%; display: flex; align-items: center; justify-content: center; animation: scaleIn 0.5s ease;">
            <i class="fas fa-check" style="font-size: 60px; color: white;"></i>
        </div>

        <h1 style="color: #28a745; font-size: 36px; margin-bottom: 15px;">
            Đặt Hàng Thành Công!
        </h1>
        
        <p style="font-size: 18px; color: #666; margin-bottom: 30px;">
            Cảm ơn bạn đã tin tưởng và mua sắm tại Xanh Organic
        </p>

        <!-- Thông tin đơn hàng -->
        <div style="background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: left; margin-bottom: 30px;">
            <h2 style="color: #2d5016; margin-bottom: 25px; text-align: center;">
                <i class="fas fa-receipt"></i> Thông Tin Đơn Hàng
            </h2>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <div>
                    <p style="color: #666; margin-bottom: 5px;">Mã đơn hàng:</p>
                    <p style="font-size: 20px; font-weight: 700; color: #2d5016; margin: 0;">
                        <?php echo htmlspecialchars($order['order_number']); ?>
                    </p>
                </div>
                
                <div>
                    <p style="color: #666; margin-bottom: 5px;">Ngày đặt hàng:</p>
                    <p style="font-size: 18px; font-weight: 600; color: #333; margin: 0;">
                        <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                    </p>
                </div>
                
                <div>
                    <p style="color: #666; margin-bottom: 5px;">Tổng tiền:</p>
                    <p style="font-size: 24px; font-weight: 700; color: #e74c3c; margin: 0;">
                        <?php echo formatMoney($order['total_amount']); ?>
                    </p>
                </div>
                
                <div>
                    <p style="color: #666; margin-bottom: 5px;">Phương thức thanh toán:</p>
                    <p style="font-size: 16px; font-weight: 600; color: #333; margin: 0;">
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
            </div>

            <!-- Thông tin nhận hàng -->
            <div style="border-top: 2px solid #eee; padding-top: 25px; margin-bottom: 25px;">
                <h3 style="color: #2d5016; margin-bottom: 15px; font-size: 18px;">
                    <i class="fas fa-shipping-fast"></i> Thông Tin Nhận Hàng
                </h3>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                    <p style="margin-bottom: 10px;"><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                    <p style="margin-bottom: 10px;"><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                    <p style="margin-bottom: 10px;"><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                    <p style="margin-bottom: 0;"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                    <?php if ($order['note']): ?>
                    <p style="margin-top: 10px; margin-bottom: 0;"><strong>Ghi chú:</strong> <?php echo htmlspecialchars($order['note']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Chi tiết sản phẩm -->
            <div style="border-top: 2px solid #eee; padding-top: 25px;">
                <h3 style="color: #2d5016; margin-bottom: 15px; font-size: 18px;">
                    <i class="fas fa-box"></i> Chi Tiết Sản Phẩm
                </h3>
                
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa; border-bottom: 2px solid #ddd;">
                            <th style="padding: 12px; text-align: left;">Sản phẩm</th>
                            <th style="padding: 12px; text-align: center;">Số lượng</th>
                            <th style="padding: 12px; text-align: right;">Đơn giá</th>
                            <th style="padding: 12px; text-align: right;">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 12px;"><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td style="padding: 12px; text-align: center;"><?php echo $item['quantity']; ?></td>
                            <td style="padding: 12px; text-align: right;"><?php echo formatMoney($item['price']); ?></td>
                            <td style="padding: 12px; text-align: right; font-weight: 600;"><?php echo formatMoney($item['subtotal']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr style="background: #f8f9fa; font-weight: 700;">
                            <td colspan="3" style="padding: 15px; text-align: right; font-size: 18px;">Tổng cộng:</td>
                            <td style="padding: 15px; text-align: right; font-size: 20px; color: #e74c3c;">
                                <?php echo formatMoney($order['total_amount']); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Thông báo quan trọng -->
        <?php if ($order['payment_method'] == 'bank_transfer'): ?>
        <div style="background: #fff3cd; border: 2px solid #ffc107; padding: 25px; border-radius: 10px; margin-bottom: 30px; text-align: left;">
            <h3 style="color: #856404; margin-bottom: 15px;">
                <i class="fas fa-exclamation-triangle"></i> Thông Tin Chuyển Khoản
            </h3>
            <p style="color: #856404; margin-bottom: 15px;">
                Vui lòng chuyển khoản theo thông tin dưới đây:
            </p>
            <div style="background: white; padding: 20px; border-radius: 8px;">
                <p style="margin-bottom: 10px;"><strong>Ngân hàng:</strong> Vietcombank</p>
                <p style="margin-bottom: 10px;"><strong>Số tài khoản:</strong> 1234567890</p>
                <p style="margin-bottom: 10px;"><strong>Chủ tài khoản:</strong> CONG TY XANH ORGANIC</p>
                <p style="margin-bottom: 10px;"><strong>Số tiền:</strong> <span style="color: #e74c3c; font-size: 20px; font-weight: 700;"><?php echo formatMoney($order['total_amount']); ?></span></p>
                <p style="margin-bottom: 0;"><strong>Nội dung:</strong> <span style="color: #2d5016; font-weight: 600;"><?php echo $order['order_number']; ?></span></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Các bước tiếp theo -->
        <div style="background: #e8f5e9; padding: 25px; border-radius: 10px; margin-bottom: 30px; text-align: left;">
            <h3 style="color: #2d5016; margin-bottom: 15px;">
                <i class="fas fa-info-circle"></i> Các Bước Tiếp Theo
            </h3>
            <ol style="color: #2d5016; line-height: 2; padding-left: 25px; margin: 0;">
                <li>Chúng tôi sẽ xác nhận đơn hàng của bạn trong vòng 30 phút</li>
                <li>Đơn hàng sẽ được chuẩn bị và đóng gói cẩn thận</li>
                <li>Giao hàng nhanh trong 2-4 giờ tại TP.HCM</li>
                <li>Bạn sẽ nhận được thông báo qua SMS/Email khi đơn hàng được giao</li>
            </ol>
        </div>

        <!-- Các nút hành động -->
        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <a href="<?php echo SITE_URL; ?>/profile.php#orders" 
               style="display: inline-block; padding: 15px 30px; background: #90c33c; color: white; border-radius: 8px; font-weight: 600; transition: all 0.3s;"
               onmouseover="this.style.background='#7ab02c'" 
               onmouseout="this.style.background='#90c33c'">
                <i class="fas fa-list"></i> Xem Đơn Hàng
            </a>
            
            <a href="<?php echo SITE_URL; ?>/products.php" 
               style="display: inline-block; padding: 15px 30px; background: white; color: #2d5016; border: 2px solid #2d5016; border-radius: 8px; font-weight: 600; transition: all 0.3s;"
               onmouseover="this.style.background='#2d5016'; this.style.color='white'" 
               onmouseout="this.style.background='white'; this.style.color='#2d5016'">
                <i class="fas fa-shopping-bag"></i> Tiếp Tục Mua Sắm
            </a>
            
            <a href="<?php echo SITE_URL; ?>" 
               style="display: inline-block; padding: 15px 30px; background: #f8f9fa; color: #666; border-radius: 8px; font-weight: 600; transition: all 0.3s;"
               onmouseover="this.style.background='#e9ecef'" 
               onmouseout="this.style.background='#f8f9fa'">
                <i class="fas fa-home"></i> Về Trang Chủ
            </a>
        </div>

        <!-- Hỗ trợ -->
        <div style="margin-top: 50px; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h3 style="color: #2d5016; margin-bottom: 20px;">
                <i class="fas fa-headset"></i> Cần Hỗ Trợ?
            </h3>
            <p style="color: #666; margin-bottom: 20px;">
                Nếu bạn có bất kỳ thắc mắc nào về đơn hàng, vui lòng liên hệ với chúng tôi:
            </p>
            <div style="display: flex; gap: 30px; justify-content: center; flex-wrap: wrap;">
                <div>
                    <i class="fas fa-phone" style="color: #90c33c; font-size: 20px;"></i>
                    <strong style="margin-left: 10px;">Hotline: 1900 1234</strong>
                </div>
                <div>
                    <i class="fas fa-envelope" style="color: #90c33c; font-size: 20px;"></i>
                    <strong style="margin-left: 10px;">Email: info@xanhorganic.com</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes scaleIn {
    from {
        transform: scale(0);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>