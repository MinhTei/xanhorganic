<?php
require_once 'includes/header.php';

$featured_products = getFeaturedProducts(8);
$latest_products = getLatestProducts(8);
$categories = getCategories();
?>

<!-- Hero Slideshow với nội dung động -->
<section class="hero-slideshow">
    <div class="slideshow-container">
        <!-- Slide 1: Chào mừng -->
        <div class="slide fade">
            <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?w=1600&h=600&fit=crop&q=80" alt="Thực phẩm hữu cơ">
            <div class="slide-text">
                <h2>🌱 Chào Mừng Đến Với Xanh Organic</h2>
                <p>Thực phẩm hữu cơ 100% - An toàn cho sức khỏe gia đình bạn</p>
                <div style="display: flex; gap: 15px; justify-content: center; margin-top: 20px;">
                    <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Mua Sắm Ngay
                    </a>
                    <a href="<?php echo SITE_URL; ?>/about.php" class="btn" style="background: rgba(255,255,255,0.3); color: white; border: 2px solid white;">
                        <i class="fas fa-info-circle"></i> Tìm Hiểu Thêm
                    </a>
                </div>
            </div>
        </div>

        <!-- Slide 2: Chứng nhận -->
        <div class="slide fade">
            <img src="https://images.unsplash.com/photo-1488459716781-31db52582fe9?w=1600&h=600&fit=crop&q=80" alt="Chứng nhận USDA">
            <div class="slide-text">
                <h2>🏆 Chứng Nhận USDA & EU Organic</h2>
                <p>Cam kết chất lượng theo tiêu chuẩn quốc tế nghiêm ngặt nhất</p>
                <a href="<?php echo SITE_URL; ?>/about.php" class="btn btn-primary">
                    <i class="fas fa-certificate"></i> Xem Chứng Nhận
                </a>
            </div>
        </div>

        <!-- Slide 3: Giao hàng -->
        <div class="slide fade">
            <img src="https://images.unsplash.com/photo-1540420773420-3366772f4999?w=1600&h=600&fit=crop&q=80" alt="Giao hàng nhanh">
            <div class="slide-text">
                <h2>🚚 Giao Hàng Siêu Tốc 2-4 Giờ</h2>
                <p>Tươi ngon từ trang trại đến bàn ăn của bạn</p>
                <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary">
                    <i class="fas fa-shipping-fast"></i> Đặt Hàng Ngay
                </a>
            </div>
        </div>

        <!-- Slide 4: Ưu đãi -->
        <div class="slide fade">
            <img src="https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=1600&h=600&fit=crop&q=80" alt="Ưu đãi đặc biệt">
            <div class="slide-text">
                <h2>🎁 Giảm Giá Đến 30%</h2>
                <p>Cho đơn hàng đầu tiên - Đăng ký thành viên ngay hôm nay!</p>
                <div style="display: flex; gap: 15px; justify-content: center; margin-top: 20px;">
                    <?php if (isLoggedIn()): ?>
                        <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary">
                            <i class="fas fa-tags"></i> Xem Ưu Đãi
                        </a>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Đăng Ký Ngay
                        </a>
                        <a href="<?php echo SITE_URL; ?>/login.php" class="btn" style="background: rgba(255,255,255,0.3); color: white; border: 2px solid white;">
                            <i class="fas fa-sign-in-alt"></i> Đăng Nhập
                        </a>
                    <?php endif; ?>
                </div>
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

<!-- Quick Stats Bar -->
<section style="background: linear-gradient(135deg, #2d5016, #90c33c); padding: 30px 0; margin-bottom: 50px;">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 30px; text-align: center; color: white;">
            <div>
                <div style="font-size: 40px; font-weight: 700; margin-bottom: 5px;">100%</div>
                <div style="font-size: 14px; opacity: 0.9;">Hữu Cơ</div>
            </div>
            <div>
                <div style="font-size: 40px; font-weight: 700; margin-bottom: 5px;">2-4h</div>
                <div style="font-size: 14px; opacity: 0.9;">Giao Hàng</div>
            </div>
            <div>
                <div style="font-size: 40px; font-weight: 700; margin-bottom: 5px;">24/7</div>
                <div style="font-size: 14px; opacity: 0.9;">Hỗ Trợ</div>
            </div>
            <div>
                <div style="font-size: 40px; font-weight: 700; margin-bottom: 5px;">
                    <?php
                    $total_products = $conn->query("SELECT COUNT(*) as total FROM products WHERE status='active'")->fetch_assoc()['total'];
                    echo $total_products;
                    ?>+
                </div>
                <div style="font-size: 14px; opacity: 0.9;">Sản Phẩm</div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section với link -->
<section class="categories-section">
    <div class="container">
        <div class="section-title">
            <h2>🏪 Danh Mục Sản Phẩm</h2>
            <p>Khám phá đa dạng sản phẩm hữu cơ chất lượng cao</p>
        </div>
        
        <div class="products-grid">
            <?php foreach ($categories as $category): ?>
            <div class="product-card" style="cursor: pointer; transition: all 0.3s;" 
                 onclick="window.location.href='<?php echo SITE_URL; ?>/products.php?category=<?php echo $category['id']; ?>'">
                <div class="product-image">
                    <?php $image_src = getCategoryImageUrl($category); ?>
                    <img src="<?php echo $image_src; ?>" alt="<?php echo safe_html($category['name']); ?>">
                    
                    <!-- Badge số lượng sản phẩm -->
                    <?php
                    $count = $conn->query("SELECT COUNT(*) as total FROM products WHERE category_id={$category['id']} AND status='active'")->fetch_assoc()['total'];
                    ?>
                    <span style="position: absolute; top: 15px; right: 15px; background: rgba(144,195,60,0.95); color: white; padding: 8px 15px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                        <?php echo $count; ?> sản phẩm
                    </span>
                </div>
                <div class="product-info">
                    <h3 class="product-name" style="text-align: center; font-size: 20px; margin-bottom: 10px;">
                        <?php echo safe_html($category['name']); ?>
                    </h3>
                    <p style="text-align: center; color: #666; font-size: 14px; margin-bottom: 15px;">
                        <?php echo safe_html($category['description']); ?>
                    </p>
                    <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $category['id']; ?>" 
                       class="btn btn-primary" 
                       style="width: 100%; text-align: center;"
                       onclick="event.stopPropagation()">
                        Xem Sản Phẩm <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary" style="padding: 15px 40px; font-size: 16px;">
                <i class="fas fa-th"></i> Xem Tất Cả Danh Mục
            </a>
        </div>
    </div>
</section>

<!-- Featured Products với wishlist -->
<section class="featured-products">
    <div class="container">
        <div class="section-title">
            <h2>⭐ Sản Phẩm Nổi Bật</h2>
            <p>Những sản phẩm được yêu thích và đánh giá cao nhất</p>
        </div>
        
        <div class="products-grid">
            <?php foreach ($featured_products as $product): ?>
            <div class="product-card" data-product-id="<?php echo $product['id']; ?>">
                <div class="product-image">
                    <?php if ($product['sale_price']): ?>
                        <span class="product-badge">
                            -<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%
                        </span>
                    <?php endif; ?>
                    
                    <!-- Nút wishlist -->
                    <?php if (isLoggedIn()): ?>
                    <button class="btn-wishlist <?php echo isInWishlist($product['id']) ? 'active' : ''; ?>" 
                            onclick="toggleWishlist(<?php echo $product['id']; ?>, this)"
                            title="Thêm vào yêu thích">
                        <i class="fas fa-heart"></i>
                    </button>
                    <?php endif; ?>
                    
                    <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $product['id']; ?>">
                        <?php $image_url = getProductImageUrl($product); ?>
                        <img src="<?php echo $image_url; ?>" alt="<?php echo safe_html($product['name']); ?>">
                    </a>
                </div>
                <div class="product-info">
                    <div class="product-category">
                        <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $product['category_id']; ?>" style="color: #90c33c;">
                            <?php
                            $cat = getCategoryById($product['category_id']);
                            echo safe_html($cat['name'] ?? 'Chưa phân loại');
                            ?>
                        </a>
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
            <a href="<?php echo SITE_URL; ?>/products.php?featured=1" class="btn btn-primary" style="padding: 15px 40px; font-size: 16px;">
                Xem Tất Cả Sản Phẩm Nổi Bật <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Latest Products -->
<!-- <section class="latest-products">
    <div class="container">
        <div class="section-title">
            <h2>🆕 Sản Phẩm Mới Nhất</h2>
            <p>Cập nhật liên tục các sản phẩm tươi ngon mỗi ngày</p>
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
                    
                    <?php if (isLoggedIn()): ?>
                    <button class="btn-wishlist <?php echo isInWishlist($product['id']) ? 'active' : ''; ?>" 
                            onclick="toggleWishlist(<?php echo $product['id']; ?>, this)">
                        <i class="fas fa-heart"></i>
                    </button>
                    <?php endif; ?>
                    
                    <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $product['id']; ?>">
                        <?php
                        $image_url = 'https://via.placeholder.com/300x250?text=' . urlencode($product['name']);
                        if (!empty($product['image'])) {
                            $image_path = __DIR__ . '/assets/' . $product['image'];
                            if (file_exists($image_path)) {
                                $image_url = SITE_URL . '/assets/' . safe_html($product['image']);
                            }
                        }
                        ?>
                        <img src="<?php echo $image_url; ?>" alt="<?php echo safe_html($product['name']); ?>">
                    </a>
                </div>
                <div class="product-info">
                    <div class="product-category">
                        <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $product['category_id']; ?>">
                            <?php
                            $cat = getCategoryById($product['category_id']);
                            echo safe_html($cat['name'] ?? '');
                            ?>
                        </a>
                    </div>
                    <h3 class="product-name">
                        <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $product['id']; ?>">
                            <?php echo safe_html($product['name']); ?>
                        </a>
                    </h3>
                    <div class="product-price">
                        <span class="price-current">
                            <?php echo formatMoney($product['sale_price'] ?? $product['price']); ?>
                        </span>
                        <?php if ($product['sale_price']): ?>
                            <span class="price-old"><?php echo formatMoney($product['price']); ?></span>
                        <?php endif; ?>
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
            <a href="<?php echo SITE_URL; ?>/products.php?sort=newest" class="btn btn-primary" style="padding: 15px 40px; font-size: 16px;">
                Xem Tất Cả Sản Phẩm Mới <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section> -->

<!-- News Section với liên kết -->
<section class="news-section">
    <div class="container">
        <div class="section-title">
            <h2>📰 Tin Tức & Kiến Thức</h2>
            <p>Cập nhật thông tin về sức khỏe và dinh dưỡng hữu cơ</p>
        </div>
        
        <div class="news-grid">
            <article class="news-card">
                <div class="news-image">
                    <img src="https://images.unsplash.com/photo-1498837167922-ddd27525d352?w=400&h=250&fit=crop" alt="Lợi ích thực phẩm hữu cơ">
                    <span class="news-date"><?php echo date('d/m/Y'); ?></span>
                </div>
                <div class="news-content">
                    <h3><a href="<?php echo SITE_URL; ?>/news.php">Lợi ích của thực phẩm hữu cơ với sức khỏe</a></h3>
                    <p>Thực phẩm hữu cơ không chỉ tốt cho sức khỏe mà còn góp phần bảo vệ môi trường...</p>
                    <a href="<?php echo SITE_URL; ?>/news.php" class="read-more">Đọc thêm <i class="fas fa-arrow-right"></i></a>
                </div>
            </article>

            <article class="news-card">
                <div class="news-image">
                    <img src="https://images.unsplash.com/photo-1490818387583-1baba5e638af?w=400&h=250&fit=crop" alt="Bảo quản rau củ">
                    <span class="news-date"><?php echo date('d/m/Y', strtotime('-1 day')); ?></span>
                </div>
                <div class="news-content">
                    <h3><a href="<?php echo SITE_URL; ?>/news.php">5 Cách bảo quản rau củ hữu cơ tươi lâu</a></h3>
                    <p>Hướng dẫn chi tiết cách bảo quản rau củ hữu cơ để giữ được độ tươi ngon...</p>
                    <a href="<?php echo SITE_URL; ?>/news.php" class="read-more">Đọc thêm <i class="fas fa-arrow-right"></i></a>
                </div>
            </article>

            <article class="news-card">
                <div class="news-image">
                    <img src="https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=400&h=250&fit=crop" alt="Chứng nhận USDA">
                    <span class="news-date"><?php echo date('d/m/Y', strtotime('-2 days')); ?></span>
                </div>
                <div class="news-content">
                    <h3><a href="<?php echo SITE_URL; ?>/news.php">Chứng nhận hữu cơ USDA là gì?</a></h3>
                    <p>Tìm hiểu về tiêu chuẩn chứng nhận hữu cơ USDA - một trong những tiêu chuẩn khắt khe nhất...</p>
                    <a href="<?php echo SITE_URL; ?>/news.php" class="read-more">Đọc thêm <i class="fas fa-arrow-right"></i></a>
                </div>
            </article>

            <article class="news-card">
                <div class="news-image">
                    <img src="https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&h=250&fit=crop" alt="Thực đơn healthy">
                    <span class="news-date"><?php echo date('d/m/Y', strtotime('-3 days')); ?></span>
                </div>
                <div class="news-content">
                    <h3><a href="<?php echo SITE_URL; ?>/news.php">Thực đơn healthy với thực phẩm hữu cơ</a></h3>
                    <p>Gợi ý thực đơn 7 ngày với các món ăn healthy từ thực phẩm hữu cơ...</p>
                    <a href="<?php echo SITE_URL; ?>/news.php" class="read-more">Đọc thêm <i class="fas fa-arrow-right"></i></a>
                </div>
            </article>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="<?php echo SITE_URL; ?>/news.php" class="btn btn-primary" style="padding: 15px 40px; font-size: 16px;">
                Xem Tất Cả Tin Tức <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="why-choose-us">
    <div class="container">
        <div class="section-title">
            <h2>💚 Tại Sao Chọn Xanh Organic?</h2>
            <p>Cam kết chất lượng và dịch vụ tốt nhất cho khách hàng</p>
        </div>
        
        <div class="products-grid">
            <div style="text-align: center; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <i class="fas fa-certificate" style="font-size: 50px; color: #90c33c; margin-bottom: 20px;"></i>
                <h3 style="color: #2d5016; margin-bottom: 15px;">Chứng Nhận Quốc Tế</h3>
                <p style="color: #666; line-height: 1.6;">100% sản phẩm có chứng nhận USDA, EU Organic đảm bảo chất lượng</p>
                <a href="<?php echo SITE_URL; ?>/about.php" style="color: #90c33c; font-weight: 500; margin-top: 10px; display: inline-block;">
                    Tìm hiểu thêm <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div style="text-align: center; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <i class="fas fa-leaf" style="font-size: 50px; color: #90c33c; margin-bottom: 20px;"></i>
                <h3 style="color: #2d5016; margin-bottom: 15px;">100% Hữu Cơ</h3>
                <p style="color: #666; line-height: 1.6;">Không sử dụng hóa chất, thuốc trừ sâu, an toàn tuyệt đối cho sức khỏe</p>
                <a href="<?php echo SITE_URL; ?>/products.php" style="color: #90c33c; font-weight: 500; margin-top: 10px; display: inline-block;">
                    Xem sản phẩm <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div style="text-align: center; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <i class="fas fa-truck" style="font-size: 50px; color: #90c33c; margin-bottom: 20px;"></i>
                <h3 style="color: #2d5016; margin-bottom: 15px;">Giao Hàng Nhanh</h3>
                <p style="color: #666; line-height: 1.6;">Giao hàng trong 2-4 giờ tại TP.HCM, 24h toàn quốc</p>
                <a href="<?php echo SITE_URL; ?>/contact.php" style="color: #90c33c; font-weight: 500; margin-top: 10px; display: inline-block;">
                    Liên hệ ngay <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div style="text-align: center; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <i class="fas fa-shield-alt" style="font-size: 50px; color: #90c33c; margin-bottom: 20px;"></i>
                <h3 style="color: #2d5016; margin-bottom: 15px;">Đảm Bảo Chất Lượng</h3>
                <p style="color: #666; line-height: 1.6;">Hoàn tiền 100% nếu không hài lòng về chất lượng sản phẩm</p>
                <a href="<?php echo SITE_URL; ?>/about.php" style="color: #90c33c; font-weight: 500; margin-top: 10px; display: inline-block;">
                    Chính sách đổi trả <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section style="background: linear-gradient(135deg, #2d5016, #90c33c); padding: 80px 0; color: white; margin-top: 50px;">
    <div class="container">
        <div style="max-width: 800px; margin: 0 auto; text-align: center;">
            <h2 style="font-size: 42px; margin-bottom: 20px; font-weight: 700;">
                Sẵn Sàng Bắt Đầu Cuộc Sống Khỏe Mạnh? 🌿
            </h2>
            <p style="font-size: 18px; margin-bottom: 40px; opacity: 0.95; line-height: 1.8;">
                Tham gia cộng đồng hơn 10,000+ khách hàng đã tin tưởng và lựa chọn Xanh Organic 
                cho gia đình của họ. Đăng ký ngay để nhận ưu đãi đặc biệt!
            </p>
            
            <div style="display: flex; gap: 20px; justify-content: center; margin-bottom: 30px;">
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo SITE_URL; ?>/products.php" 
                       class="btn" 
                       style="background: white; color: #2d5016; padding: 18px 40px; font-size: 18px; font-weight: 600; border-radius: 50px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                        <i class="fas fa-shopping-bag"></i> Mua Sắm Ngay
                    </a>
                    <a href="<?php echo SITE_URL; ?>/about.php" 
                       class="btn" 
                       style="background: rgba(255,255,255,0.2); color: white; padding: 18px 40px; font-size: 18px; font-weight: 600; border-radius: 50px; border: 2px solid white;">
                        <i class="fas fa-info-circle"></i> Tìm Hiểu Thêm
                    </a>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/register.php" 
                       class="btn" 
                       style="background: white; color: #2d5016; padding: 18px 40px; font-size: 18px; font-weight: 600; border-radius: 50px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                        <i class="fas fa-user-plus"></i> Đăng Ký Miễn Phí
                    </a>
                    <a href="<?php echo SITE_URL; ?>/products.php" 
                       class="btn" 
                       style="background: rgba(255,255,255,0.2); color: white; padding: 18px 40px; font-size: 18px; font-weight: 600; border-radius: 50px; border: 2px solid white;">
                        <i class="fas fa-eye"></i> Xem Sản Phẩm
                    </a>
                <?php endif; ?>
            </div>
            
            <div style="display: flex; gap: 40px; justify-content: center; font-size: 14px; opacity: 0.9;">
                <div>
                    <i class="fas fa-shipping-fast"></i> Miễn phí vận chuyển
                </div>
                <div>
                    <i class="fas fa-shield-alt"></i> Đảm bảo chất lượng
                </div>
                <div>
                    <i class="fas fa-headset"></i> Hỗ trợ 24/7
                </div>
            </div>
        </div>
    </div>
</section>

<script src="<?php echo SITE_URL; ?>/assets/js/wishlist.js"></script>
<script>
// Slideshow Script
let slideIndex = 1;
showSlides(slideIndex);

// Auto slide every 5 seconds
setInterval(() => {
    plusSlides(1);
}, 5000);

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