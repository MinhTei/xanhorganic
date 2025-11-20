<?php
/**
 * PRODUCT-DETAIL.PHP - Trang chi tiết sản phẩm
 * 
 * Hiển thị:
 * - Thông tin chi tiết sản phẩm
 * - Hình ảnh sản phẩm
 * - Form thêm vào giỏ hàng
 * - Sản phẩm liên quan
 */

require_once 'includes/header.php';

// Lấy ID sản phẩm từ URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    redirect('products.php');
}

// Lấy thông tin sản phẩm
$product = getProductById($product_id);

if (!$product) {
    redirect('products.php');
}

// Lấy sản phẩm liên quan (cùng category)
$related_products = getProductsByCategory($product['category_id'], 4);

$error = '';
$success = '';

// Xử lý thêm vào giỏ hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
    
    if ($product['stock'] < $quantity) {
        $error = 'Sản phẩm không đủ số lượng trong kho!';
    } else {
        if (addToCart($product_id, $quantity)) {
            $success = 'Đã thêm sản phẩm vào giỏ hàng!';
        } else {
            $error = 'Có lỗi xảy ra, vui lòng thử lại!';
        }
    }
}

$price = $product['sale_price'] ?? $product['price'];
$discount = 0;
if ($product['sale_price']) {
    $discount = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
}

// Xử lý hình ảnh sản phẩm (dùng helper để chuẩn hóa đường dẫn và fallback)
$image_url = getProductImageUrl($product);
?>

<div class="container">
    <!-- Breadcrumb -->
    <div style="padding: 20px 0; color: #666;">
        <a href="<?php echo SITE_URL; ?>" style="color: #90c33c;">Trang chủ</a>
        <i class="fas fa-chevron-right" style="margin: 0 10px; font-size: 12px;"></i>
        <a href="<?php echo SITE_URL; ?>/products.php" style="color: #90c33c;">Sản phẩm</a>
        <i class="fas fa-chevron-right" style="margin: 0 10px; font-size: 12px;"></i>
        <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $product['category_id']; ?>" style="color: #90c33c;">
            <?php echo safe_html($product['category_name']); ?>
        </a>
        <i class="fas fa-chevron-right" style="margin: 0 10px; font-size: 12px;"></i>
        <span><?php echo safe_html($product['name']); ?></span>
    </div>

    <!-- Thông báo -->
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            <a href="<?php echo SITE_URL; ?>/cart.php" style="color: #155724; text-decoration: underline; margin-left: 10px;">
                Xem giỏ hàng
            </a>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- Chi tiết sản phẩm -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 50px;">
        
        <!-- Hình ảnh sản phẩm -->
        <div>
            <div style="position: relative; border-radius: 10px; overflow: hidden; border: 1px solid #eee;">
                <?php if ($discount > 0): ?>
                    <span style="position: absolute; top: 20px; left: 20px; background: #e74c3c; color: white; padding: 8px 15px; border-radius: 20px; font-weight: 500; font-size: 14px; z-index: 10;">
                        -<?php echo $discount; ?>%
                    </span>
                <?php endif; ?>
                
                <img src="<?php echo $image_url; ?>" 
                     alt="<?php echo safe_html($product['name']); ?>"
                     style="width: 100%; height: 500px; object-fit: cover;">
            </div>
        </div>

        <!-- Thông tin sản phẩm -->
        <div>
            <div style="display: inline-block; background: #e8f5e9; color: #2d5016; padding: 5px 15px; border-radius: 15px; font-size: 13px; font-weight: 500; margin-bottom: 15px;">
                <?php echo safe_html($product['category_name']); ?>
            </div>
            
            <h1 style="color: #2d5016; font-size: 32px; margin-bottom: 15px; line-height: 1.3;">
                <?php echo safe_html($product['name']); ?>
            </h1>

            <!-- Chứng nhận -->
            <?php if ($product['certification']): ?>
            <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                <span style="background: #90c33c; color: white; padding: 6px 15px; border-radius: 20px; font-size: 13px; font-weight: 500;">
                    <i class="fas fa-certificate"></i> <?php echo safe_html($product['certification']); ?>
                </span>
                <?php if ($product['origin']): ?>
                <span style="background: #f8f9fa; color: #666; padding: 6px 15px; border-radius: 20px; font-size: 13px;">
                    <i class="fas fa-map-marker-alt"></i> <?php echo safe_html($product['origin']); ?>
                </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Giá -->
            <div style="background: #f8f9fa; padding: 25px; border-radius: 10px; margin-bottom: 25px;">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
                    <span style="font-size: 36px; font-weight: 700; color: #e74c3c;">
                        <?php echo formatMoney($price); ?>
                    </span>
                    <?php if ($product['sale_price']): ?>
                        <span style="font-size: 20px; color: #999; text-decoration: line-through;">
                            <?php echo formatMoney($product['price']); ?>
                        </span>
                    <?php endif; ?>
                    <span style="color: #666; font-size: 16px;">
                        /<?php echo safe_html($product['unit']); ?>
                    </span>
                </div>
                
                <div style="display: flex; gap: 20px; color: #666; font-size: 14px;">
                    <div>
                        <i class="fas fa-box" style="color: #90c33c;"></i>
                        Tình trạng: 
                        <?php if ($product['stock'] > 0): ?>
                            <strong style="color: #28a745;">Còn hàng (<?php echo $product['stock']; ?>)</strong>
                        <?php else: ?>
                            <strong style="color: #e74c3c;">Hết hàng</strong>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Form thêm giỏ hàng -->
            <?php if ($product['stock'] > 0): ?>
            <form method="POST" style="margin-bottom: 25px;">
                <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 20px;">
                    <label style="font-weight: 500; color: #333;">Số lượng:</label>
                    <div class="quantity-input">
                        <button type="button" onclick="this.nextElementSibling.stepDown();">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" style="width: 80px;">
                        <button type="button" onclick="this.previousElementSibling.stepUp();">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div style="display: flex; gap: 15px;">
                    <button type="submit" name="add_to_cart" class="btn-add-cart" style="flex: 2; font-size: 16px; padding: 15px;">
                        <i class="fas fa-cart-plus"></i> Thêm Vào Giỏ Hàng
                    </button>
                    <a href="<?php echo SITE_URL; ?>/cart.php" 
                       style="flex: 1; background: #e74c3c; color: white; padding: 15px; border-radius: 5px; font-weight: 500; font-size: 16px; text-align: center; text-decoration: none;">
                        Xem Giỏ Hàng
                    </a>
                </div>
            </form>
            <?php else: ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 25px;">
                <i class="fas fa-exclamation-circle"></i> Sản phẩm hiện đang hết hàng
            </div>
            <?php endif; ?>

            <!-- Thông tin thêm -->
            <div style="border-top: 1px solid #eee; padding-top: 25px;">
                <h3 style="color: #2d5016; margin-bottom: 15px; font-size: 18px;">Đặc điểm nổi bật:</h3>
                <ul style="color: #666; line-height: 2; padding-left: 20px;">
                    <li>100% hữu cơ, không hóa chất bảo vệ thực vật</li>
                    <li>Chứng nhận <?php echo safe_html($product['certification']); ?></li>
                    <li>Nguồn gốc: <?php echo safe_html($product['origin']); ?></li>
                    <li>Tươi ngon, thu hoạch gần nhất</li>
                    <li>Giao hàng nhanh trong 2-4 giờ</li>
                </ul>
            </div>

            <!-- Chính sách -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 25px;">
                <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <i class="fas fa-truck" style="font-size: 24px; color: #90c33c; margin-bottom: 10px;"></i>
                    <p style="font-size: 12px; color: #666; margin: 0;">Giao hàng nhanh</p>
                </div>
                <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <i class="fas fa-undo" style="font-size: 24px; color: #90c33c; margin-bottom: 10px;"></i>
                    <p style="font-size: 12px; color: #666; margin: 0;">Đổi trả 7 ngày</p>
                </div>
                <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <i class="fas fa-shield-alt" style="font-size: 24px; color: #90c33c; margin-bottom: 10px;"></i>
                    <p style="font-size: 12px; color: #666; margin: 0;">Cam kết chất lượng</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Mô tả chi tiết -->
    <div style="background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 50px;">
        <h2 style="color: #2d5016; margin-bottom: 20px;">Mô Tả Sản Phẩm</h2>
        <div style="color: #666; line-height: 1.8;">
            <?php echo nl2br(safe_html($product['description'])); ?>
            
            <h3 style="color: #2d5016; margin-top: 30px; margin-bottom: 15px;">Lợi ích sức khỏe:</h3>
            <p>Sản phẩm hữu cơ này mang lại nhiều lợi ích cho sức khỏe của bạn và gia đình, 
            không chứa hóa chất độc hại, giàu dinh dưỡng tự nhiên.</p>
            
            <h3 style="color: #2d5016; margin-top: 30px; margin-bottom: 15px;">Hướng dẫn bảo quản:</h3>
            <p>Bảo quản trong ngăn mát tủ lạnh, sử dụng trong vòng 3-5 ngày để đảm bảo độ tươi ngon.</p>
        </div>
    </div>

    <!-- Sản phẩm liên quan -->
    <?php if (!empty($related_products)): ?>
    <div style="margin-bottom: 50px;">
        <h2 style="color: #2d5016; margin-bottom: 30px; text-align: center;">Sản Phẩm Liên Quan</h2>
        <div class="products-grid">
            <?php foreach ($related_products as $related): ?>
                <?php if ($related['id'] != $product_id): ?>
                    <?php
                           $related_image_url = getProductImageUrl($related);
                    ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($related['sale_price']): ?>
                            <span class="product-badge">
                                -<?php echo round((($related['price'] - $related['sale_price']) / $related['price']) * 100); ?>%
                            </span>
                        <?php endif; ?>
                        <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $related['id']; ?>">
                            <img src="<?php echo $related_image_url; ?>" 
                                 alt="<?php echo safe_html($related['name']); ?>">
                        </a>
                    </div>
                    <div class="product-info">
                        <div class="product-category"><?php echo safe_html($product['category_name']); ?></div>
                        <h3 class="product-name">
                            <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $related['id']; ?>">
                                <?php echo safe_html($related['name']); ?>
                            </a>
                        </h3>
                        <div class="product-price">
                            <span class="price-current">
                                <?php echo formatMoney($related['sale_price'] ?? $related['price']); ?>
                            </span>
                            <?php if ($related['sale_price']): ?>
                                <span class="price-old"><?php echo formatMoney($related['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="product-actions">
                            <button class="btn-add-cart" onclick="addToCart(<?php echo $related['id']; ?>)">
                                <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                            </button>
                            <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $related['id']; ?>" class="btn-view-detail">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>