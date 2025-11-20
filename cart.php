<?php
/**
 * CART.PHP - Trang giỏ hàng với AJAX cập nhật không reload
 * 
 * Xử lý 2 loại request:
 * 1. AJAX request (is_ajax=1): Trả về JSON cho cập nhật số lượng
 * 2. Normal request: Hiển thị HTML trang giỏ hàng
 */

// ===== PHẦN 1: XỬ LÝ AJAX REQUEST (Không load header/footer) =====
if (isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1') {
    // Chỉ load config và functions, KHÔNG load header.php
    require_once 'includes/config.php';
    require_once 'includes/functions.php';

    // Đặt header JSON để browser hiểu response là JSON
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? null;
    $product_id = (int)($_POST['product_id'] ?? 0);

    // Xử lý cập nhật số lượng sản phẩm
    if ($action === 'update' && $product_id > 0) {
        $quantity = max(1, (int)($_POST['quantity'] ?? 1)); // Tối thiểu 1
        
        // Kiểm tra sản phẩm có tồn tại không
        $product = getProductById($product_id);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
            exit;
        }

        // Kiểm tra tồn kho
        if ($product['stock'] < $quantity) {
            echo json_encode([
                'success' => false, 
                'message' => 'Sản phẩm chỉ còn ' . $product['stock'] . ' trong kho'
            ]);
            exit;
        }
        
        // Cập nhật số lượng trong giỏ hàng
        updateCartQuantity($product_id, $quantity);
        
        // Tính toán lại các giá trị
        $new_total = getCartTotal(); // Tổng giỏ hàng mới
        $price = $product['sale_price'] ?? $product['price']; // Giá sản phẩm
        $subtotal = $price * $quantity; // Thành tiền sản phẩm này
        
        // Trả về JSON với thông tin đã cập nhật
        echo json_encode([
            'success' => true,
            'message' => 'Đã cập nhật giỏ hàng',
            'total_formatted' => formatMoney($new_total), // Tổng giỏ hàng đã format
            'subtotal_formatted' => formatMoney($subtotal), // Thành tiền sản phẩm đã format
            'quantity' => $quantity // Số lượng mới
        ]);
        exit;
    }
    
    // Action không hợp lệ
    echo json_encode(['success' => false, 'message' => 'Invalid AJAX request']);
    exit;
}

// ===== PHẦN 2: HIỂN THỊ HTML TRANG GIỎ HÀNG =====

require_once 'includes/header.php'; // Load header với session, functions

// Lấy giỏ hàng và tổng tiền
$cart_items = getCart();
$total = getCartTotal();

// Xử lý các action POST thông thường (Xóa sản phẩm, Xóa giỏ hàng)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $product_id = (int)($_POST['product_id'] ?? 0);
    
    switch ($action) {
        case 'remove': // Xóa 1 sản phẩm
            if ($product_id > 0) {
                removeFromCart($product_id);
            }
            break;
        case 'clear': // Xóa toàn bộ giỏ hàng
            clearCart();
            break;
    }
    // Redirect để tránh resubmit form khi F5
    redirect('cart.php');
}
?>

<div class="container">
    <!-- Breadcrumb -->
    <div style="padding: 20px 0; color: #666;">
        <a href="<?php echo SITE_URL; ?>" style="color: #90c33c;">Trang chủ</a>
        <i class="fas fa-chevron-right" style="margin: 0 10px; font-size: 12px;"></i>
        <span>Giỏ hàng</span>
    </div>
    
    <h1 style="color: #2d5016; margin-bottom: 30px;">
        <i class="fas fa-shopping-cart"></i> Giỏ Hàng Của Bạn
    </h1>
    
    <?php if (empty($cart_items)): ?>
        <!-- Giỏ hàng trống -->
        <div style="text-align: center; padding: 50px; background: white; border-radius: 10px;">
            <i class="fas fa-shopping-cart" style="font-size: 80px; color: #ddd; margin-bottom: 20px;"></i>
            <h3 style="color: #666; margin-bottom: 10px;">Giỏ hàng trống</h3>
            <p style="color: #999; margin-bottom: 30px;">Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm</p>
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
            </a>
        </div>
    <?php else: ?>
        <!-- Giỏ hàng có sản phẩm -->
        <div style="display: grid; grid-template-columns: 1fr 400px; gap: 30px;">
            
            <!-- Bảng sản phẩm -->
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
                        
                        // Xử lý hình ảnh sản phẩm (ưu tiên ảnh sản phẩm, fallback ảnh category)
                        $image_url = getProductImageUrl($product);
                        ?>
                        <tr data-product-id="<?php echo $product['id']; ?>">
                            <td>
                                <div class="cart-item-info">
                                    <div class="cart-item-image">
                                        <img src="<?php echo $image_url; ?>" 
                                            alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    </div>
                                    <div>
                                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                        <p style="color: #666; font-size: 14px;">
                                            <?php echo htmlspecialchars($product['unit']); ?>
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
                                <!-- Form cập nhật số lượng bằng AJAX -->
                                <form class="quantity-input" onsubmit="return false;">
                                    <input type="hidden" name="is_ajax" value="1"> 
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    
                                    <button type="button" onclick="changeQuantity(this, -1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    
                                    <input type="number" 
                                           name="quantity" 
                                           value="<?php echo $item['quantity']; ?>" 
                                           min="1" 
                                           max="<?php echo $product['stock']; ?>"
                                           onchange="updateCartItemAjax(this)">
                                    
                                    <button type="button" onclick="changeQuantity(this, 1)">
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
                                <!-- Form xóa sản phẩm (POST thông thường) -->
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
                
                <!-- Nút điều hướng -->
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
            
            <!-- Tóm tắt đơn hàng -->
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
                
                <!-- Mã giảm giá -->
                <div style="margin: 20px 0;">
                    <input type="text" placeholder="Nhập mã giảm giá" 
                            style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 10px;">
                    <button style="width: 100%; padding: 10px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 5px;">
                        Áp dụng
                    </button>
                </div>
                
                <!-- Nút thanh toán -->
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo SITE_URL; ?>/checkout.php" class="btn-checkout">
                        <i class="fas fa-credit-card"></i> Thanh Toán
                    </a>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/login.php?redirect=checkout.php" class="btn-checkout">
                        <i class="fas fa-sign-in-alt"></i> Đăng nhập để thanh toán
                    </a>
                <?php endif; ?>
                
                <!-- Thông tin bảo hành -->
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
/**
 * HÀM CẬP NHẬT GIỎ HÀNG BẰNG AJAX
 * Không reload trang, chỉ cập nhật giá trị hiển thị
 */
function updateCartItemAjax(inputElement) {
    const form = inputElement.closest('form');
    const formData = new FormData(form);
    
    // Lấy các elements cần cập nhật
    const row = inputElement.closest('tr');
    const itemSubtotalElement = row.querySelector('.item-subtotal');
    const tempTotalElement = document.querySelector('.cart-temp-total');
    const finalTotalElement = document.querySelector('.cart-final-total');
    
    // Kiểm tra và điều chỉnh số lượng hợp lệ
    let newQty = parseInt(inputElement.value);
    const maxQty = parseInt(inputElement.max);
    
    if (isNaN(newQty) || newQty < 1) {
        newQty = 1;
        inputElement.value = 1;
    } else if (newQty > maxQty) {
        alert(`Sản phẩm chỉ còn ${maxQty} trong kho!`);
        newQty = maxQty;
        inputElement.value = maxQty;
    }
    
    formData.set('quantity', newQty);
    
    // Hiển thị loading
    itemSubtotalElement.textContent = '...';
    
    // Gửi AJAX request
    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cập nhật thành tiền sản phẩm
            itemSubtotalElement.textContent = data.subtotal_formatted;
            
            // Cập nhật tổng giỏ hàng
            if (tempTotalElement) tempTotalElement.textContent = data.total_formatted;
            if (finalTotalElement) finalTotalElement.textContent = data.total_formatted;
            
            // Hiển thị thông báo nhẹ (optional)
            console.log('✓ Đã cập nhật giỏ hàng');
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể cập nhật giỏ hàng'));
            // Reload trang nếu lỗi nghiêm trọng
            location.reload();
        }
    })
    .catch(error => {
        console.error('Lỗi AJAX:', error);
        alert('Có lỗi xảy ra. Vui lòng thử lại!');
        itemSubtotalElement.textContent = '---';
    });
}

/**
 * HÀM THAY ĐỔI SỐ LƯỢNG (+1 hoặc -1)
 */
function changeQuantity(button, delta) {
    const form = button.closest('form');
    const input = form.querySelector('input[name="quantity"]');
    let currentQty = parseInt(input.value);
    let newQty = currentQty + delta;
    const maxQty = parseInt(input.max);
    
    // Kiểm tra giới hạn
    if (newQty < 1) {
        return; // Không cho giảm xuống dưới 1
    }
    
    if (newQty > maxQty) {
        alert(`Sản phẩm chỉ còn ${maxQty} trong kho!`);
        return;
    }
    
    // Cập nhật giá trị và gọi AJAX
    input.value = newQty;
    updateCartItemAjax(input);
}
</script>

<?php require_once 'includes/footer.php'; ?>