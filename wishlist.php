<?php
/**
 * WISHLIST.PHP - Trang danh sách sản phẩm yêu thích
 */

require_once 'includes/config.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirect('login.php?redirect=wishlist.php');
}

require_once 'includes/header.php';

// Lấy danh sách wishlist
$wishlist = getWishlist();
?>

<!-- Thêm wishlist.js -->
<script src="<?php echo SITE_URL; ?>/assets/js/wishlist.js"></script>

<div class="container">
    <!-- Breadcrumb -->
    <div style="padding: 20px 0; color: #666;">
        <a href="<?php echo SITE_URL; ?>" style="color: #90c33c;">Trang chủ</a>
        <i class="fas fa-chevron-right" style="margin: 0 10px; font-size: 12px;"></i>
        <span>Sản phẩm yêu thích</span>
    </div>
    
    <h1 style="color: #2d5016; margin-bottom: 30px;">
        <i class="fas fa-heart" style="color: #e74c3c;"></i> Sản Phẩm Yêu Thích
    </h1>
    
    <?php if (empty($wishlist)): ?>
        <!-- Wishlist trống -->
        <div style="text-align: center; padding: 50px; background: white; border-radius: 10px;">
            <i class="fas fa-heart-broken" style="font-size: 80px; color: #ddd; margin-bottom: 20px;"></i>
            <h3 style="color: #666; margin-bottom: 10px;">Chưa có sản phẩm yêu thích</h3>
            <p style="color: #999; margin-bottom: 30px;">Hãy thêm sản phẩm bạn thích vào danh sách này</p>
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i> Khám phá sản phẩm
            </a>
        </div>
    <?php else: ?>
        <!-- Hiển thị số lượng -->
        <div style="background: #e8f5e9; padding: 15px 20px; border-radius: 8px; margin-bottom: 30px;">
            <p style="margin: 0; color: #2d5016;">
                <i class="fas fa-info-circle"></i> 
                Bạn có <strong><?php echo count($wishlist); ?> sản phẩm</strong> trong danh sách yêu thích
            </p>
        </div>

        <!-- Danh sách sản phẩm -->
        <div class="products-grid">
            <?php foreach ($wishlist as $item): ?>
            <div class="product-card" data-product-id="<?php echo $item['product_id']; ?>">
                <div class="product-image">
                    <?php if ($item['sale_price']): ?>
                        <span class="product-badge">
                            -<?php echo round((($item['price'] - $item['sale_price']) / $item['price']) * 100); ?>%
                        </span>
                    <?php endif; ?>
                    
                    <!-- Nút xóa khỏi wishlist -->
                    <button class="btn-wishlist active" 
                            onclick="removeFromWishlist(<?php echo $item['product_id']; ?>, this)"
                            title="Xóa khỏi yêu thích">
                        <i class="fas fa-heart"></i>
                    </button>
                    
                    <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $item['product_id']; ?>">
                        <?php
                            $image_url = getProductImageUrl($item);
                        ?>
                        <img src="<?php echo $image_url; ?>" alt="<?php echo safe_html($item['name']); ?>">
                    </a>
                </div>
                <div class="product-info">
                    <div class="product-category">
                        <?php echo safe_html($item['category_name']); ?>
                    </div>
                    <h3 class="product-name">
                        <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $item['product_id']; ?>">
                            <?php echo safe_html($item['name']); ?>
                        </a>
                    </h3>
                    <div class="product-price">
                        <span class="price-current">
                            <?php echo formatMoney($item['sale_price'] ?? $item['price']); ?>
                        </span>
                        <?php if ($item['sale_price']): ?>
                            <span class="price-old"><?php echo formatMoney($item['price']); ?></span>
                        <?php endif; ?>
                        <span style="color: #666; font-size: 14px;">/<?php echo safe_html($item['unit']); ?></span>
                    </div>
                    
                    <!-- Stock status -->
                    <div style="margin-bottom: 15px; font-size: 14px;">
                        <?php if ($item['stock'] > 0): ?>
                            <span style="color: #28a745;">
                                <i class="fas fa-check-circle"></i> Còn hàng
                            </span>
                        <?php else: ?>
                            <span style="color: #e74c3c;">
                                <i class="fas fa-times-circle"></i> Hết hàng
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-actions">
                        <?php if ($item['stock'] > 0): ?>
                            <button class="btn-add-cart" onclick="addToCart(<?php echo $item['product_id']; ?>)">
                                <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                            </button>
                        <?php else: ?>
                            <button class="btn-add-cart" disabled style="opacity: 0.5; cursor: not-allowed;">
                                <i class="fas fa-ban"></i> Hết hàng
                            </button>
                        <?php endif; ?>
                        <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $item['product_id']; ?>" 
                           class="btn-view-detail">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Nút thêm -->
        <div style="text-align: center; margin-top: 40px;">
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
/* Additional styling for wishlist page */
.product-card {
    transition: all 0.3s ease;
}

.product-card:hover {
    transform: translateY(-5px);
}
</style>

<?php require_once 'includes/footer.php'; ?>