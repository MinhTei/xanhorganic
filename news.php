<?php
require_once 'includes/header.php';

// Danh sách bài viết mẫu (sau này có thể lấy từ database)
$news_articles = [
    [
        'id' => 1,
        'title' => 'Lợi ích của thực phẩm hữu cơ với sức khỏe',
        'excerpt' => 'Thực phẩm hữu cơ không chỉ tốt cho sức khỏe mà còn góp phần bảo vệ môi trường. Khám phá những lợi ích tuyệt vời...',
        'image' => 'https://via.placeholder.com/400x250?text=Tin+tuc+1',
        'date' => '09/11/2025',
        'author' => 'Admin'
    ],
    [
        'id' => 2,
        'title' => '5 Cách bảo quản rau củ hữu cơ tươi lâu',
        'excerpt' => 'Hướng dẫn chi tiết cách bảo quản rau củ hữu cơ để giữ được độ tươi ngon và giá trị dinh dưỡng cao nhất...',
        'image' => 'https://via.placeholder.com/400x250?text=Tin+tuc+2',
        'date' => '08/11/2025',
        'author' => 'Admin'
    ],
    [
        'id' => 3,
        'title' => 'Chứng nhận hữu cơ USDA là gì?',
        'excerpt' => 'Tìm hiểu về tiêu chuẩn chứng nhận hữu cơ USDA - một trong những tiêu chuẩn khắt khe nhất thế giới...',
        'image' => 'https://via.placeholder.com/400x250?text=Tin+tuc+3',
        'date' => '07/11/2025',
        'author' => 'Admin'
    ],
    [
        'id' => 4,
        'title' => 'Thực đơn healthy với thực phẩm hữu cơ',
        'excerpt' => 'Gợi ý thực đơn 7 ngày với các món ăn healthy từ thực phẩm hữu cơ, giúp bạn khỏe đẹp mỗi ngày...',
        'image' => 'https://via.placeholder.com/400x250?text=Tin+tuc+4',
        'date' => '06/11/2025',
        'author' => 'Admin'
    ],
    [
        'id' => 5,
        'title' => 'Nông nghiệp hữu cơ - Xu hướng tương lai',
        'excerpt' => 'Nông nghiệp hữu cơ đang trở thành xu hướng phát triển bền vững toàn cầu. Tìm hiểu về những lợi ích to lớn...',
        'image' => 'https://via.placeholder.com/400x250?text=Tin+tuc+5',
        'date' => '05/11/2025',
        'author' => 'Admin'
    ],
    [
        'id' => 6,
        'title' => 'So sánh thực phẩm hữu cơ và thực phẩm thông thường',
        'excerpt' => 'Phân tích chi tiết sự khác biệt giữa thực phẩm hữu cơ và thực phẩm thông thường về dinh dưỡng và an toàn...',
        'image' => 'https://via.placeholder.com/400x250?text=Tin+tuc+6',
        'date' => '04/11/2025',
        'author' => 'Admin'
    ],
    [
        'id' => 7,
        'title' => 'Làm thế nào để nhận biết thực phẩm hữu cơ thật?',
        'excerpt' => 'Hướng dẫn cách phân biệt thực phẩm hữu cơ thật và giả qua các dấu hiệu nhận biết quan trọng...',
        'image' => 'https://via.placeholder.com/400x250?text=Tin+tuc+7',
        'date' => '03/11/2025',
        'author' => 'Admin'
    ],
    [
        'id' => 8,
        'title' => 'Top 10 loại rau củ hữu cơ nên ăn hàng ngày',
        'excerpt' => 'Danh sách 10 loại rau củ hữu cơ giàu dinh dưỡng nhất, nên bổ sung vào thực đơn hàng ngày của gia đình...',
        'image' => 'https://via.placeholder.com/400x250?text=Tin+tuc+8',
        'date' => '02/11/2025',
        'author' => 'Admin'
    ]
];
?>

<div class="container">
    <!-- Breadcrumb -->
    <div style="padding: 20px 0; color: #666;">
        <a href="<?php echo SITE_URL; ?>" style="color: #90c33c;">Trang chủ</a>
        <i class="fas fa-chevron-right" style="margin: 0 10px; font-size: 12px;"></i>
        <span>Tin tức</span>
    </div>
    
    <h1 style="color: #2d5016; text-align: center; margin-bottom: 50px;">
        <i class="fas fa-newspaper"></i> Tin Tức & Kiến Thức
    </h1>
</div>

<!-- News Grid -->
<section class="news-section">
    <div class="container">
        <div class="news-grid">
            <?php foreach ($news_articles as $article): ?>
            <article class="news-card">
                <div class="news-image">
                    <img src="<?php echo $article['image']; ?>" alt="<?php echo safe_html($article['title']); ?>">
                    <span class="news-date"><?php echo $article['date']; ?></span>
                </div>
                <div class="news-content">
                    <h3>
                        <a href="<?php echo SITE_URL; ?>/news-detail.php?id=<?php echo $article['id']; ?>">
                            <?php echo safe_html($article['title']); ?>
                        </a>
                    </h3>
                    <p><?php echo safe_html($article['excerpt']); ?></p>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                        <span style="color: #999; font-size: 13px;">
                            <i class="fas fa-user"></i> <?php echo $article['author']; ?>
                        </span>
                        <a href="<?php echo SITE_URL; ?>/news-detail.php?id=<?php echo $article['id']; ?>" class="read-more">
                            Đọc thêm <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Pagination (mẫu) -->
<div class="container">
    <div class="pagination">
        <span class="active">1</span>
        <a href="#">2</a>
        <a href="#">3</a>
        <a href="#">Sau <i class="fas fa-chevron-right"></i></a>
    </div>
</div>

<!-- Newsletter Subscription -->
<section style="background: linear-gradient(135deg, #2d5016, #90c33c); padding: 60px 0; margin-top: 50px; color: white;">
    <div class="container">
        <div style="max-width: 600px; margin: 0 auto; text-align: center;">
            <h2 style="font-size: 32px; margin-bottom: 15px;">Đăng Ký Nhận Tin</h2>
            <p style="font-size: 16px; margin-bottom: 30px; opacity: 0.9;">
                Nhận thông tin mới nhất về sản phẩm và chương trình ưu đãi
            </p>
            <form style="display: flex; gap: 10px;">
                <input type="email" 
                       placeholder="Nhập email của bạn..." 
                       required
                       style="flex: 1; padding: 15px 20px; border: none; border-radius: 25px; font-size: 14px;">
                <button type="submit" 
                        style="background: white; color: #2d5016; padding: 15px 30px; border: none; border-radius: 25px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-paper-plane"></i> Đăng Ký
                </button>
            </form>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>