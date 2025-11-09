<?php
require_once 'includes/header.php';

// Lấy sản phẩm nổi bật
$featured_products = getFeaturedProducts(8);

// Lấy sản phẩm mới nhất
$latest_products = getLatestProducts(8);

// Lấy danh mục
$categories = getCategories();

// Sửa lỗi Deprecated: Tạo hàm bọc để xử lý NULL trước khi gọi htmlspecialchars
function safe_html($value) {
    return htmlspecialchars($value ?? '');
}
?>

<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h2>Thực Phẩm Hữu Cơ Chất Lượng Cao</h2>
            <p>100% hữu cơ - Chứng nhận quốc tế USDA & EU</p>
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i> Mua Sắm Ngay
            </a>
        </div>
    </div>
</section>

---

<section class="categories-section">
    <div class="container">
        <div class="section-title">
            <h2>Danh Mục Sản Phẩm</h2>
            <p>Khám phá đa dạng sản phẩm hữu cơ chất lượng</p>
        </div>
        
        <div class="products-grid">
            <?php foreach ($categories as $category): ?>
            <div class="product-card">
                <div class="product-image">
                    <?php 
                        // Lấy giá trị cột 'image' từ CSDL một cách an toàn
                        $image_file = safe_html($category['image']);
                        
                        // TẠO ĐƯỜNG DẪN HOÀN CHỈNH: Thêm tiền tố 'assets/' (nếu cần)
                        // Giả sử CSDL lưu "images/products/ten_file.jpg" và cần tiền tố "assets/"
                        $image_src = 'assets/' . $image_file;
                        
                        // Nếu trường 'image' trống, dùng ảnh placeholder
                        if (empty($category['image'])) {
                            $image_src = 'https://via.placeholder.com/300x250?text=' . urlencode($category['name']);
                        }
                    ?>
                    <img src="<?php echo $image_src; ?>" 
                         alt="<?php echo safe_html($category['name']); ?>">
                </div>
                <div class="product-info">
                    <h3 class="product-name">
                        <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $category['id']; ?>">
                            <?php echo safe_html($category['name']); ?>
                        </a>
                    </h3>
                    <p><?php echo safe_html($category['description']); ?></p>
                    <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $category['id']; ?>" class="btn btn-primary">
                        Xem sản phẩm
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

---

<section class="featured-products" style="margin-top: 50px;">
    <div class="container">
        <div class="section-title">
            <h2>Sản Phẩm Nổi Bật</h2>
            <p>Những sản phẩm được yêu thích nhất</p>
        </div>
        
        <div class="products-grid">
            <?php foreach ($featured_products as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if ($product['sale_price']): ?>
                        <span class="product-badge">
                            -<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%
                        </span>
                    <?php endif; ?>
                    <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $product['id']; ?>">
                        <?php
                            $image_url = SITE_URL . '/assets/images/products/' . safe_html($product['image']);
                        ?>
                        <img src="<?php echo $image_url; ?>" 
                             alt="<?php echo safe_html($product['name']); ?>">
                    </a>
                </div>
                <div class="product-info">
                    <div class="product-category">
                        <?php
                        // Cần đảm bảo $conn được định nghĩa trước đó
                        $cat_result = $conn->query("SELECT name FROM categories WHERE id = " . $product['category_id']);
                        $cat = $cat_result->fetch_assoc();
                        echo safe_html($cat['name']);
                        ?>
                    </div>
                    <h3 class="product-name">
                        <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $product['id']; ?>">
                            <?php echo safe_html($product['name']); ?>
                        </a>
                    </h3>
                    <?php if ($product['certification']): ?>
                    <div class="product-certification">
                        <span class="cert-badge"><?php echo safe_html($product['certification']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="product-price">
                        <span class="price-current">
                            <?php echo formatMoney($product['sale_price'] ?? $product['price']); ?>
                        </span>
                        <?php if ($product['sale_price']): ?>
                            <span class="price-old"><?php echo formatMoney($product['price']); ?></span>
                        <?php endif; ?>
                        <span style="color: #666; font-size: 14px;">/<?php echo safe_html($product['unit']); ?></span>
                    </div>
                    <div class="product-actions">
                        <button class="btn-add-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                            <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                        </button>
                        <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $product['id']; ?>" 
                            class="btn-view-detail">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary">
                Xem Tất Cả Sản Phẩm <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

---

<section class="latest-products" style="margin-top: 50px; background-color: #f9f9f9; padding: 50px 0;">
    <div class="container">
        <div class="section-title">
            <h2>Sản Phẩm Mới Nhất</h2>
            <p>Khám phá những sản phẩm vừa được cập nhật</p>
        </div>
        
        <div class="products-grid">
            <?php foreach ($latest_products as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if ($product['sale_price']): ?>
                        <span class="product-badge">
                            -<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%
                        </span>
                    <?php endif; ?>
                    <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $product['id']; ?>">
                        <?php
                            $image_url = SITE_URL . '/assets/images/products/' . safe_html($product['image']);
                        ?>
                        <img src="<?php echo $image_url; ?>" 
                             alt="<?php echo safe_html($product['name']); ?>">
                    </a>
                </div>
                <div class="product-info">
                    <div class="product-category">
                        <?php
                        $cat_result = $conn->query("SELECT name FROM categories WHERE id = " . $product['category_id']);
                        $cat = $cat_result->fetch_assoc();
                        echo safe_html($cat['name']);
                        ?>
                    </div>
                    <h3 class="product-name">
                        <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $product['id']; ?>">
                            <?php echo safe_html($product['name']); ?>
                        </a>
                    </h3>
                    <?php if ($product['certification']): ?>
                    <div class="product-certification">
                        <span class="cert-badge"><?php echo safe_html($product['certification']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="product-price">
                        <span class="price-current">
                            <?php echo formatMoney($product['sale_price'] ?? $product['price']); ?>
                        </span>
                        <?php if ($product['sale_price']): ?>
                            <span class="price-old"><?php echo formatMoney($product['price']); ?></span>
                        <?php endif; ?>
                        <span style="color: #666; font-size: 14px;">/<?php echo safe_html($product['unit']); ?></span>
                    </div>
                    <div class="product-actions">
                        <button class="btn-add-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                            <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                        </button>
                        <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $product['id']; ?>" 
                            class="btn-view-detail">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

---

<section class="why-choose-us" style="background-color: white; padding: 50px 0; margin-top: 50px;">
    <div class="container">
        <div class="section-title">
            <h2>Tại Sao Chọn Xanh Organic?</h2>
        </div>
        
        <div class="products-grid">
            <div style="text-align: center; padding: 30px;">
                <i class="fas fa-certificate" style="font-size: 50px; color: #90c33c; margin-bottom: 20px;"></i>
                <h3 style="color: #2d5016; margin-bottom: 15px;">Chứng Nhận Quốc Tế</h3>
                <p>100% sản phẩm có chứng nhận USDA, EU Organic đảm bảo chất lượng</p>
            </div>
            
            <div style="text-align: center; padding: 30px;">
                <i class="fas fa-leaf" style="font-size: 50px; color: #90c33c; margin-bottom: 20px;"></i>
                <h3 style="color: #2d5016; margin-bottom: 15px;">100% Hữu Cơ</h3>
                <p>Không sử dụng hóa chất, thuốc trừ sâu, an toàn tuyệt đối</p>
            </div>
            
            <div style="text-align: center; padding: 30px;">
                <i class="fas fa-truck" style="font-size: 50px; color: #90c33c; margin-bottom: 20px;"></i>
                <h3 style="color: #2d5016; margin-bottom: 15px;">Giao Hàng Nhanh</h3>
                <p>Giao hàng trong 2-4 giờ tại TP.HCM, 24h toàn quốc</p>
            </div>
            
            <div style="text-align: center; padding: 30px;">
                <i class="fas fa-shield-alt" style="font-size: 50px; color: #90c33c; margin-bottom: 20px;"></i>
                <h3 style="color: #2d5016; margin-bottom: 15px;">Đảm Bảo Chất Lượng</h3>
                <p>Hoàn tiền 100% nếu không hài lòng về chất lượng</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>