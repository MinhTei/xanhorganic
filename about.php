<?php
// ===== about.php (Giới thiệu) =====
require_once 'includes/header.php';
?>

<div class="container">
    <div style="padding: 20px 0; color: #666;">
        <a href="<?php echo SITE_URL; ?>" style="color: #90c33c;">Trang chủ</a>
        <i class="fas fa-chevron-right" style="margin: 0 10px; font-size: 12px;"></i>
        <span>Giới thiệu</span>
    </div>
    
    <div style="background: white; padding: 50px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h1 style="color: #2d5016; text-align: center; margin-bottom: 30px;">
            Về Xanh Organic
        </h1>
        
        <div style="max-width: 800px; margin: 0 auto;">
            <p style="font-size: 16px; line-height: 1.8; color: #666; margin-bottom: 20px;">
                <strong>Xanh Organic</strong> là hệ thống phát triển và phân phối thực phẩm hữu cơ uy tín tại Việt Nam. 
                Chúng tôi cam kết mang đến cho khách hàng những sản phẩm hữu cơ chất lượng cao nhất, 
                được chứng nhận bởi các tổ chức quốc tế như USDA và EU Organic.
            </p>
            
            <h2 style="color: #2d5016; margin: 30px 0 20px;">Sứ Mệnh</h2>
            <p style="font-size: 16px; line-height: 1.8; color: #666; margin-bottom: 20px;">
                Chúng tôi hướng tới mục tiêu phát triển nền nông nghiệp bền vững, 
                bảo vệ môi trường và nâng cao sức khỏe cộng đồng thông qua việc cung cấp 
                thực phẩm hữu cơ an toàn, không chứa hóa chất độc hại.
            </p>
            
            <h2 style="color: #2d5016; margin: 30px 0 20px;">Giá Trị Cốt Lõi</h2>
            <ul style="font-size: 16px; line-height: 2; color: #666; margin-bottom: 30px;">
                <li><strong>100% Hữu Cơ:</strong> Tất cả sản phẩm đều có chứng nhận hữu cơ quốc tế</li>
                <li><strong>Minh Bạch:</strong> Nguồn gốc xuất xứ rõ ràng, truy xuất được</li>
                <li><strong>Chất Lượng:</strong> Kiểm soát nghiêm ngặt từ trang trại đến bàn ăn</li>
                <li><strong>Bền Vững:</strong> Đồng hành cùng nông dân, bảo vệ môi trường</li>
            </ul>
            
            <div style="text-align: center; margin-top: 40px;">
                <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary">
                    Khám Phá Sản Phẩm
                </a>
            </div>
        </div>
    </div>
</div>

<?php 
require_once 'includes/footer.php';

// ===== contact.php (Liên hệ) =====
// Tạo file mới contact.php và paste code dưới đây:
?>
