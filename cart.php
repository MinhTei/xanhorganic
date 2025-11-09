<?php
// --- ĐỊNH NGHĨA HÀM JSON RESPONSE (Đảm bảo nó không bị gọi hai lần) ---
if (!function_exists('sendJsonResponse')) {
    function sendJsonResponse($data) {
        // Xóa bộ đệm đầu ra nếu có output bị bắt
        if (ob_get_level() > 0) {
            ob_end_clean(); 
        }
        header('Content-Type: application/json'); 
        echo json_encode($data);
        exit();
    }
}
// ------------------------------------------

// Kiểm tra xem đây có phải là yêu cầu AJAX để cập nhật số lượng không
if (isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1') {
    // 1. KHÔNG TẢI header.php! Chỉ tải các file PHP cốt lõi.
    require_once 'includes/config.php';
    require_once 'includes/functions.php'; 

    $action = $_POST['action'] ?? null;
    $product_id = (int)($_POST['product_id'] ?? 0); 

    if ($action === 'update') {
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        
        updateCartQuantity($product_id, $quantity);
        
        $new_total = getCartTotal();
        $product = getProductById($product_id);
        
        $price = $product['sale_price'] ?? $product['price'];
        $subtotal = $price * $quantity;
        
        sendJsonResponse([
            'success' => true,
            'total_formatted' => formatMoney($new_total),
            'subtotal_formatted' => formatMoney($subtotal)
        ]);
    }
    
    sendJsonResponse(['success' => false, 'message' => 'Invalid AJAX request.']);
}
// -----------------------------------------------------

// --- BẮT ĐẦU HIỂN THỊ HTML ---
// Bắt đầu bộ đệm đầu ra cho request tải trang bình thường
ob_start(); 
require_once 'includes/header.php'; 
ob_end_flush(); // Gửi HTML sau khi header.php đã được nạp

// Lấy giỏ hàng cho việc hiển thị HTML
$cart_items = getCart();
$total = getCartTotal();

// LƯU Ý: Nếu hàm safe_html() không có, bạn phải dùng htmlspecialchars($value ?? '')
$safe_html = function($value) { return htmlspecialchars($value ?? ''); };

// Xử lý các action POST thông thường (chỉ còn Remove/Clear)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $product_id = (int)($_POST['product_id'] ?? 0);
    
    if ($action !== 'update') {
        switch ($action) {
            case 'remove':
                removeFromCart($product_id);
                break;
            case 'clear':
                clearCart();
                break;
        }
        // Redirect để tránh resubmit
        redirect('cart.php');
    }
}
?>

<div class="container">
    <div style="padding: 20px 0; color: #666;">
        <a href="<?php echo SITE_URL; ?>" style="color: #90c33c;">Trang chủ</a>
        <i class="fas fa-chevron-right" style="margin: 0 10px; font-size: 12px;"></i>
        <span>Giỏ hàng</span>
    </div>
    
    <h1 style="color: #2d5016; margin-bottom: 30px;">
        <i class="fas fa-shopping-cart"></i> Giỏ Hàng Của Bạn
    </h1>
    
    <?php if (empty($cart_items)): ?>
        <div style="text-align: center; padding: 50px; background: white; border-radius: 10px;">
            <i class="fas fa-shopping-cart" style="font-size: 80px; color: #ddd; margin-bottom: 20px;"></i>
            <h3 style="color: #666; margin-bottom: 10px;">Giỏ hàng trống</h3>
            <p style="color: #999; margin-bottom: 30px;">Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm</p>
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
            </a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 1fr 400px; gap: 30px;">
            <div class="cart-table">
                <table>
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): 
                        $product = getProductById($item['product_id']);
                        if (!$product) continue; 
                        
                        $price = $product['sale_price'] ?? $product['price'];
                        $subtotal = $price * $item['quantity'];
                        
                        $image_url = SITE_URL . '/assets/images/products/' . $safe_html($product['image']);
                        if (empty($product['image'])) {
                            $image_url = 'https://via.placeholder.com/80x80?text=' . urlencode($safe_html($product['name']));
                        }
                        ?>
                        <tr>
                            <td>
                                <div class="cart-item-info">
                                    <div class="cart-item-image">
                                        <img src="<?php echo $image_url; ?>" 
                                            alt="<?php echo $safe_html($product['name']); ?>">
                                    </div>
                                    <div>
                                        <h4><?php echo $safe_html($product['name']); ?></h4>
                                        <p style="color: #666; font-size: 14px;">
                                            <?php echo $safe_html($product['unit']); ?>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong style="color: #e74c3c;"><?php echo formatMoney($price); ?></strong>
                                <?php if ($product['sale_price']): ?>
                                    <br>
                                    <small style="text-decoration: line-through; color: #999;">
                                        <?php echo formatMoney($product['price']); ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" class="quantity-input">
                                    <input type="hidden" name="is_ajax" value="1"> 
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="button" onclick="decreaseQty(this)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                            min="1" max="99" onchange="updateCartItem(this)">
                                    <button type="button" onclick="increaseQty(this)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <strong style="color: #2d5016; font-size: 18px;" class="item-subtotal">
                                    <?php echo formatMoney($subtotal); ?>
                                </strong>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" class="btn-remove" 
                                            onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div style="padding: 20px; display: flex; justify-content: space-between; align-items: center;">
                    <a href="<?php echo SITE_URL; ?>/products.php" style="color: #90c33c;">
                        <i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
                    </a>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" style="color: #e74c3c; background: none; padding: 10px 20px;"
                                onclick="return confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng?')">
                            <i class="fas fa-trash"></i> Xóa giỏ hàng
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="cart-summary">
                <h3>Tổng Đơn Hàng</h3>
                
                <div class="summary-row">
                    <span>Tạm tính:</span>
                    <strong class="cart-temp-total"><?php echo formatMoney($total); ?></strong>
                </div>
                
                <div class="summary-row">
                    <span>Phí vận chuyển:</span>
                    <strong style="color: #90c33c;">Miễn phí</strong>
                </div>
                
                <div class="summary-row">
                    <span>Giảm giá:</span>
                    <strong>0đ</strong>
                </div>
                
                <div class="summary-row total">
                    <span>Tổng cộng:</span>
                    <strong class="cart-final-total"><?php echo formatMoney($total); ?></strong>
                </div>
                
                <div style="margin: 20px 0;">
                    <input type="text" placeholder="Nhập mã giảm giá" 
                            style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 10px;">
                    <button style="width: 100%; padding: 10px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 5px;">
                        Áp dụng
                    </button>
                </div>
                
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo SITE_URL; ?>/checkout.php" class="btn-checkout">
                        <i class="fas fa-credit-card"></i> Thanh Toán
                    </a>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/login.php?redirect=checkout.php" class="btn-checkout">
                        <i class="fas fa-sign-in-alt"></i> Đăng nhập để thanh toán
                    </a>
                <?php endif; ?>
                
                <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                    <p style="font-size: 13px; color: #666; margin-bottom: 10px;">
                        <i class="fas fa-shield-alt" style="color: #90c33c;"></i>
                        Thanh toán an toàn & bảo mật
                    </p>
                    <p style="font-size: 13px; color: #666; margin-bottom: 10px;">
                        <i class="fas fa-truck" style="color: #90c33c;"></i>
                        Giao hàng nhanh 2-4 giờ
                    </p>
                    <p style="font-size: 13px; color: #666;">
                        <i class="fas fa-undo" style="color: #90c33c;"></i>
                        Đổi trả miễn phí trong 7 ngày
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function updateCartItem(inputElement) {
        const form = inputElement.closest('form');
        const formData = new FormData(form);
        
        // Cần tìm cell Subtotal và Total
        const row = inputElement.closest('tr');
        const itemSubtotalElement = row.querySelector('.item-subtotal');
        const tempTotalElement = document.querySelector('.cart-temp-total');
        const finalTotalElement = document.querySelector('.cart-final-total');
        
        // Kiểm tra và hiệu chỉnh số lượng (để tránh lỗi)
        let newQty = parseInt(inputElement.value);
        if (newQty < 1 || isNaN(newQty)) {
            newQty = 1;
            inputElement.value = 1;
        }

        formData.set('quantity', newQty); 
        
        // Hiệu ứng chờ
        itemSubtotalElement.textContent = '...'; 

        // Gửi yêu cầu AJAX
        fetch('cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 1. Cập nhật Thành tiền của sản phẩm này
                itemSubtotalElement.textContent = data.subtotal_formatted;

                // 2. Cập nhật Tổng cộng giỏ hàng
                if (tempTotalElement) tempTotalElement.textContent = data.total_formatted;
                if (finalTotalElement) finalTotalElement.textContent = data.total_formatted;
                
            } else {
                alert('Lỗi cập nhật giỏ hàng: ' + data.message);
            }
        })
        .catch(error => console.error('Lỗi AJAX:', error));
    }
    
    // Hàm cho nút + và -
    function changeQty(button, delta) {
        const form = button.closest('form');
        const input = form.querySelector('input[name="quantity"]');
        let currentQty = parseInt(input.value);
        let newQty = currentQty + delta;

        if (newQty >= 1 && newQty <= 99) {
            input.value = newQty;
            updateCartItem(input); // Gọi AJAX để cập nhật
        }
    }
    
    function decreaseQty(button) {
        changeQty(button, -1);
    }
    
    function increaseQty(button) {
        changeQty(button, 1);
    }
</script>

<?php require_once 'includes/footer.php'; ?>