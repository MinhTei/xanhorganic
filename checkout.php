<?php
require_once 'includes/config.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirect('login.php?redirect=checkout.php');
}

require_once 'includes/header.php';

// Lấy giỏ hàng
$cart_items = getCart();
$total = getCartTotal();

// Kiểm tra giỏ hàng trống
if (empty($cart_items)) {
    redirect('cart.php');
}

// Lấy thông tin user
$user = getCurrentUser();

$error = '';
$success = '';

// Xử lý đặt hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $note = trim($_POST['note']);
    $payment_method = $_POST['payment_method'];
    
    // Validate
    if (empty($full_name) || empty($email) || empty($phone) || empty($address)) {
        $error = 'Vui lòng điền đầy đủ thông tin giao hàng!';
    } elseif (!isValidEmail($email)) {
        $error = 'Email không hợp lệ!';
    } elseif (!isValidPhone($phone)) {
        $error = 'Số điện thoại không hợp lệ!';
    } else {
        // Bắt đầu transaction
        $conn->begin_transaction();
        
        try {
            // Tạo đơn hàng
            $order_number = generateOrderNumber();
            $user_id = $_SESSION['user_id'];
            
            $sql = "INSERT INTO orders (user_id, order_number, full_name, email, phone, address, total_amount, payment_method, note) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssssdss", $user_id, $order_number, $full_name, $email, $phone, $address, $total, $payment_method, $note);
            $stmt->execute();
            
            $order_id = $conn->insert_id;
            
            // Thêm chi tiết đơn hàng
            $sql = "INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            
            foreach ($cart_items as $item) {
                $product = getProductById($item['product_id']);
                $price = $product['sale_price'] ?? $product['price'];
                $subtotal = $price * $item['quantity'];
                
                $stmt->bind_param("iisdid", $order_id, $product['id'], $product['name'], $price, $item['quantity'], $subtotal);
                $stmt->execute();
                
                // Cập nhật tồn kho
                $new_stock = $product['stock'] - $item['quantity'];
                $update_sql = "UPDATE products SET stock = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ii", $new_stock, $product['id']);
                $update_stmt->execute();
            }
            
            // Xóa giỏ hàng
            clearCart();
            
            // Commit transaction
            $conn->commit();
            
            // Redirect đến trang thành công
            redirect('order-success.php?order=' . $order_number);
            
        } catch (Exception $e) {
            // Rollback nếu có lỗi
            $conn->rollback();
            $error = 'Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại!';
        }
    }
}
?>

<div class="container">
    <div style="padding: 20px 0; color: #666;">
        <a href="<?php echo SITE_URL; ?>" style="color: #90c33c;">Trang chủ</a>
        <i class="fas fa-chevron-right" style="margin: 0 10px; font-size: 12px;"></i>
        <a href="<?php echo SITE_URL; ?>/cart.php" style="color: #90c33c;">Giỏ hàng</a>
        <i class="fas fa-chevron-right" style="margin: 0 10px; font-size: 12px;"></i>
        <span>Thanh toán</span>
    </div>
    
    <h1 style="color: #2d5016; margin-bottom: 30px;">
        <i class="fas fa-credit-card"></i> Thanh Toán
    </h1>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" style="display: grid; grid-template-columns: 1fr 400px; gap: 30px;">
        <!-- Thông tin giao hàng -->
        <div>
            <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px;">
                <h2 style="color: #2d5016; margin-bottom: 25px;">
                    <i class="fas fa-shipping-fast"></i> Thông Tin Giao Hàng
                </h2>
                
                <div class="form-group">
                    <label for="full_name">Họ và tên <span style="color: red;">*</span></label>
                    <input type="text" id="full_name" name="full_name" required
                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : htmlspecialchars($user['full_name']); ?>"
                           placeholder="Nhập họ tên người nhận">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="email">Email <span style="color: red;">*</span></label>
                        <input type="email" id="email" name="email" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($user['email']); ?>"
                               placeholder="email@example.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Số điện thoại <span style="color: red;">*</span></label>
                        <input type="tel" id="phone" name="phone" required
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : htmlspecialchars($user['phone']); ?>"
                               placeholder="0901234567">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address">Địa chỉ giao hàng <span style="color: red;">*</span></label>
                    <textarea id="address" name="address" required rows="3" placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : htmlspecialchars($user['address']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="note">Ghi chú đơn hàng (tùy chọn)</label>
                    <textarea id="note" name="note" rows="3" placeholder="Ví dụ: Giao hàng giờ hành chính, gọi trước khi giao..."><?php echo isset($_POST['note']) ? htmlspecialchars($_POST['note']) : ''; ?></textarea>
                </div>
            </div>

            <!-- Phương thức thanh toán -->
            <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h2 style="color: #2d5016; margin-bottom: 25px;">
                    <i class="fas fa-money-bill-wave"></i> Phương Thức Thanh Toán
                </h2>
                
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <label style="display: flex; align-items: center; padding: 20px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; transition: all 0.3s;"
                           onmouseover="this.style.borderColor='#90c33c'" 
                           onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='#ddd'">
                        <input type="radio" name="payment_method" value="cod" checked 
                               style="width: 20px; height: 20px; margin-right: 15px;"
                               onchange="document.querySelectorAll('label').forEach(l => l.style.borderColor='#ddd'); this.parentElement.style.borderColor='#90c33c';">
                        <div>
                            <strong style="color: #2d5016; font-size: 16px; display: block; margin-bottom: 5px;">
                                <i class="fas fa-money-bill-wave"></i> Thanh toán khi nhận hàng (COD)
                            </strong>
                            <span style="color: #666; font-size: 14px;">Thanh toán bằng tiền mặt khi nhận hàng</span>
                        </div>
                    </label>
                    
                    <label style="display: flex; align-items: center; padding: 20px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; transition: all 0.3s;"
                           onmouseover="this.style.borderColor='#90c33c'" 
                           onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='#ddd'">
                        <input type="radio" name="payment_method" value="bank_transfer" 
                               style="width: 20px; height: 20px; margin-right: 15px;"
                               onchange="document.querySelectorAll('label').forEach(l => l.style.borderColor='#ddd'); this.parentElement.style.borderColor='#90c33c';">
                        <div>
                            <strong style="color: #2d5016; font-size: 16px; display: block; margin-bottom: 5px;">
                                <i class="fas fa-university"></i> Chuyển khoản ngân hàng
                            </strong>
                            <span style="color: #666; font-size: 14px;">Chuyển khoản qua ngân hàng</span>
                        </div>
                    </label>
                    
                    <label style="display: flex; align-items: center; padding: 20px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; transition: all 0.3s;"
                           onmouseover="this.style.borderColor='#90c33c'" 
                           onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='#ddd'">
                        <input type="radio" name="payment_method" value="momo" 
                               style="width: 20px; height: 20px; margin-right: 15px;"
                               onchange="document.querySelectorAll('label').forEach(l => l.style.borderColor='#ddd'); this.parentElement.style.borderColor='#90c33c';">
                        <div>
                            <strong style="color: #2d5016; font-size: 16px; display: block; margin-bottom: 5px;">
                                <i class="fas fa-mobile-alt"></i> Ví MoMo
                            </strong>
                            <span style="color: #666; font-size: 14px;">Thanh toán qua ví điện tử MoMo</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Tóm tắt đơn hàng -->
        <div>
            <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: sticky; top: 20px;">
                <h2 style="color: #2d5016; margin-bottom: 25px;">
                    <i class="fas fa-receipt"></i> Đơn Hàng Của Bạn
                </h2>
                
                <!-- Danh sách sản phẩm -->
                <div style="max-height: 300px; overflow-y: auto; margin-bottom: 20px; padding-right: 10px;">
                    <?php foreach ($cart_items as $item): ?>
                        <?php 
                        $product = getProductById($item['product_id']);
                        $price = $product['sale_price'] ?? $product['price'];
                        $subtotal = $price * $item['quantity'];
                        ?>
                        <div style="display: flex; gap: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; margin-bottom: 10px;">
                            <img src="https://via.placeholder.com/60?text=<?php echo urlencode($product['name']); ?>" 
                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                            <div style="flex: 1;">
                                <h4 style="font-size: 14px; margin-bottom: 5px; color: #333;">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </h4>
                                <p style="font-size: 13px; color: #666; margin-bottom: 5px;">
                                    <?php echo formatMoney($price); ?> x <?php echo $item['quantity']; ?>
                                </p>
                                <p style="font-size: 14px; font-weight: 600; color: #2d5016;">
                                    <?php echo formatMoney($subtotal); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Tổng tiền -->
                <div style="border-top: 1px solid #eee; padding-top: 20px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px; color: #666;">
                        <span>Tạm tính:</span>
                        <strong><?php echo formatMoney($total); ?></strong>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px; color: #666;">
                        <span>Phí vận chuyển:</span>
                        <strong style="color: #90c33c;">Miễn phí</strong>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px; color: #666;">
                        <span>Giảm giá:</span>
                        <strong>0đ</strong>
                    </div>
                    
                    <div style="border-top: 2px solid #90c33c; padding-top: 15px; display: flex; justify-content: space-between; font-size: 20px; font-weight: 700; color: #e74c3c;">
                        <span>Tổng cộng:</span>
                        <strong><?php echo formatMoney($total); ?></strong>
                    </div>
                </div>

                <!-- Nút đặt hàng -->
                <button type="submit" style="width: 100%; background: #90c33c; color: white; padding: 18px; border: none; border-radius: 8px; font-size: 18px; font-weight: 600; margin-top: 25px; cursor: pointer; transition: all 0.3s;"
                        onmouseover="this.style.background='#7ab02c'" 
                        onmouseout="this.style.background='#90c33c'">
                    <i class="fas fa-check-circle"></i> Đặt Hàng
                </button>

                <div style="margin-top: 20px; padding: 15px; background: #e8f5e9; border-radius: 5px; font-size: 13px; color: #2d5016;">
                    <i class="fas fa-shield-alt"></i> Thông tin của bạn được bảo mật
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>