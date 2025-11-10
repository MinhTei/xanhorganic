<?php
require_once 'includes/header.php';

$featured_products = getFeaturedProducts(8);
$latest_products = getLatestProducts(8);
$categories = getCategories();
?>

<!-- Hero Slideshow -->
<section class="hero-slideshow">
    <div class="slideshow-container">
        <div class="slide fade">
            <img src="https://images.unsplash.com/photo-1540420773420-3366772f4999?w=1200&h=500&fit=crop" alt="Banner 1">
            <div class="slide-text">
                <h2>Thực Phẩm Hữu Cơ 100%</h2>
                <p>An toàn - Tươi ngon - Chất lượng cao</p>
                <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary">Mua Ngay</a>
            </div>
        </div>

        <div class="slide fade">
            <img src="https://images.unsplash.com/photo-1488459716781-31db52582fe9?w=1200&h=500&fit=crop" alt="Banner 2">
            <div class="slide-text">
                <h2>Chứng Nhận USDA & EU Organic</h2>
                <p>Sản phẩm đạt tiêu chuẩn quốc tế</p>
                <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary">Khám Phá</a>
            </div>
        </div>

        <div class="slide fade">
            <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?w=1200&h=500&fit=crop" alt="Banner 3">
            <div class="slide-text">
                <h2>Giao Hàng Nhanh 2-4 Giờ</h2>
                <p>Tươi ngon đến tận nhà bạn</p>
                <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary">Đặt Hàng</a>
            </div>
        </div>

        <div class="slide fade">
            <img src="https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=1200&h=500&fit=crop" alt="Banner 4">
            <div class="slide-text">
                <h2>Ưu Đãi Đặc Biệt</h2>
                <p>Giảm giá đến 30% cho đơn hàng đầu tiên</p>
                <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary">Xem Ngay</a>
            </div>
        </div>

        <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
        <a class="next" onclick="plusSlides(1)">&#10095;</a>
    </div>

    <div class="dots-container">
        <span class="dot" onclick="currentSlide(1)"></span>
        <span class="dot" onclick="currentSlide(2)"></span>
        <span class="dot" onclick="currentSlide(3)"></span>
        <span class="dot" onclick="currentSlide(4)"></span>
    </div>
</section>

<!-- Categories Section -->
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
                    $image_src = 'https://via.placeholder.com/300x250?text=' . urlencode($category['name']);
                    
                    // Nếu có hình ảnh thực tế
                    if (!empty($category['image'])) {
                        $image_path = __DIR__ . '/assets/' . $category['image'];
                        if (file_exists($image_path)) {
                            $image_src = SITE_URL . '/assets/' . safe_html($category['image']);
                        }
                    }
                    ?>
                    <img src="<?php echo $image_src; ?>" alt="<?php echo safe_html($category['name']); ?>">
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

<!-- Featured Products -->
<section class="featured-products">
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
                        $image_url = 'https://via.placeholder.com/300x250?text=' . urlencode($product['name']);
                        
                        if (!empty($product['image'])) {
                            $image_path = __DIR__ . '/assets/images/products/' . $product['image'];
                            if (file_exists($image_path)) {
                                $image_url = SITE_URL . '/assets/images/products/' . safe_html($product['image']);
                            }
                        }
                        ?>
                        <img src="<?php echo $image_url; ?>" alt="<?php echo safe_html($product['name']); ?>">
                    </a>
                </div>
                <div class="product-info">
                    <div class="product-category">
                        <?php
                        $cat = getCategoryById($product['category_id']);
                        echo safe_html($cat['name'] ?? 'Chưa phân loại');
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
                        <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $product['id']; ?>" class="btn-view-detail">
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

<!-- News Section -->
<section class="news-section">
    <div class="container">
        <div class="section-title">
            <h2>Tin Tức Mới Nhất</h2>
            <p>Cập nhật thông tin về sức khỏe và dinh dưỡng</p>
        </div>
        
        <div class="news-grid">
            <article class="news-card">
                <div class="news-image">
                    <img src="https://images.unsplash.com/photo-1498837167922-ddd27525d352?w=400&h=250&fit=crop" alt="Tin tức 1">
                    <span class="news-date">09/11/2025</span>
                </div>
                <div class="news-content">
                    <h3><a href="<?php echo SITE_URL; ?>/news-detail.php?id=1">Lợi ích của thực phẩm hữu cơ với sức khỏe</a></h3>
                    <p>Thực phẩm hữu cơ không chỉ tốt cho sức khỏe mà còn góp phần bảo vệ môi trường...</p>
                    <a href="<?php echo SITE_URL; ?>/news-detail.php?id=1" class="read-more">Đọc thêm <i class="fas fa-arrow-right"></i></a>
                </div>
            </article>

            <article class="news-card">
                <div class="news-image">
                    <img src="https://images.unsplash.com/photo-1490818387583-1baba5e638af?w=400&h=250&fit=crop" alt="Tin tức 2">
                    <span class="news-date">08/11/2025</span>
                </div>
                <div class="news-content">
                    <h3><a href="<?php echo SITE_URL; ?>/news-detail.php?id=2">5 Cách bảo quản rau củ hữu cơ tươi lâu</a></h3>
                    <p>Hướng dẫn chi tiết cách bảo quản rau củ hữu cơ để giữ được độ tươi ngon...</p>
                    <a href="<?php echo SITE_URL; ?>/news-detail.php?id=2" class="read-more">Đọc thêm <i class="fas fa-arrow-right"></i></a>
                </div>
            </article>

            <article class="news-card">
                <div class="news-image">
                    <img src="https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=400&h=250&fit=crop" alt="Tin tức 3">
                    <span class="news-date">07/11/2025</span>
                </div>
                <div class="news-content">
                    <h3><a href="<?php echo SITE_URL; ?>/news-detail.php?id=3">Chứng nhận hữu cơ USDA là gì?</a></h3>
                    <p>Tìm hiểu về tiêu chuẩn chứng nhận hữu cơ USDA - một trong những tiêu chuẩn khắt khe nhất...</p>
                    <a href="<?php echo SITE_URL; ?>/news-detail.php?id=3" class="read-more">Đọc thêm <i class="fas fa-arrow-right"></i></a>
                </div>
            </article>

            <article class="news-card">
                <div class="news-image">
                    <img src="https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&h=250&fit=crop" alt="Tin tức 4">
                    <span class="news-date">06/11/2025</span>
                </div>
                <div class="news-content">
                    <h3><a href="<?php echo SITE_URL; ?>/news-detail.php?id=4">Thực đơn healthy với thực phẩm hữu cơ</a></h3>
                    <p>Gợi ý thực đơn 7 ngày với các món ăn healthy từ thực phẩm hữu cơ...</p>
                    <a href="<?php echo SITE_URL; ?>/news-detail.php?id=4" class="read-more">Đọc thêm <i class="fas fa-arrow-right"></i></a>
                </div>
            </article>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="<?php echo SITE_URL; ?>/news.php" class="btn btn-primary">
                Xem Tất Cả Tin Tức <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Latest Products -->
<section class="latest-products">
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
                        $image_url = 'https://via.placeholder.com/300x250?text=' . urlencode($product['name']);
                        
                        if (!empty($product['image'])) {
                            $image_path = __DIR__ . '/assets/images/products/' . $product['image'];
                            if (file_exists($image_path)) {
                                $image_url = SITE_URL . '/assets/images/products/' . safe_html($product['image']);
                            }
                        }
                        ?>
                        <img src="<?php echo $image_url; ?>" alt="<?php echo safe_html($product['name']); ?>">
                    </a>
                </div>
                <div class="product-info">
                    <div class="product-category">
                        <?php
                        $cat = getCategoryById($product['category_id']);
                        echo safe_html($cat['name'] ?? 'Chưa phân loại');
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
                        <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $product['id']; ?>" class="btn-view-detail">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="why-choose-us">
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

<script>
// Slideshow Script
let slideIndex = 1;
showSlides(slideIndex);

// Auto slide every 3 seconds
setInterval(() => {
    plusSlides(1);
}, 3000);

function plusSlides(n) {
    showSlides(slideIndex += n);
}

function currentSlide(n) {
    showSlides(slideIndex = n);
}

function showSlides(n) {
    let slides = document.getElementsByClassName("slide");
    let dots = document.getElementsByClassName("dot");
    
    if (n > slides.length) { slideIndex = 1 }
    if (n < 1) { slideIndex = slides.length }
    
    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }
    
    for (let i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", "");
    }
    
    if (slides[slideIndex - 1]) {
        slides[slideIndex - 1].style.display = "block";
    }
    if (dots[slideIndex - 1]) {
        dots[slideIndex - 1].className += " active";
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>