<?php
require_once 'includes/header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    } elseif (!isValidEmail($email)) {
        $error = 'Email không hợp lệ!';
    } else {
        $sql = "INSERT INTO contacts (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
        
        if ($stmt->execute()) {
            $success = 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất.';
            $_POST = []; // Clear form
        } else {
            $error = 'Có lỗi xảy ra, vui lòng thử lại!';
        }
    }
}
?>

<div class="container">
    <div style="padding: 20px 0; color: #666;">
        <a href="<?php echo SITE_URL; ?>" style="color: #90c33c;">Trang chủ</a>
        <i class="fas fa-chevron-right" style="margin: 0 10px; font-size: 12px;"></i>
        <span>Liên hệ</span>
    </div>
    
    <h1 style="color: #2d5016; text-align: center; margin-bottom: 50px;">
        Liên Hệ Với Chúng Tôi
    </h1>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
        <!-- Contact Form -->
        <div style="background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h2 style="color: #2d5016; margin-bottom: 20px;">Gửi Tin Nhắn</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Họ và tên *</label>
                    <input type="text" name="name" required
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="tel" name="phone"
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Tiêu đề</label>
                    <input type="text" name="subject"
                           value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Nội dung *</label>
                    <textarea name="message" required rows="5"><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Gửi Tin Nhắn
                </button>
            </form>
        </div>
        
        <!-- Contact Info -->
        <div>
            <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px;">
                <h3 style="color: #2d5016; margin-bottom: 20px;">Thông Tin Liên Hệ</h3>
                
                <div style="margin-bottom: 20px;">
                    <i class="fas fa-map-marker-alt" style="color: #90c33c; width: 30px;"></i>
                    <strong>Địa chỉ:</strong><br>
                    <span style="color: #666; margin-left: 30px;">123 Đường ABC, Quận 1, TP. Hồ Chí Minh</span>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <i class="fas fa-phone" style="color: #90c33c; width: 30px;"></i>
                    <strong>Hotline:</strong><br>
                    <span style="color: #666; margin-left: 30px;">1900 1234 (8:00 - 22:00)</span>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <i class="fas fa-envelope" style="color: #90c33c; width: 30px;"></i>
                    <strong>Email:</strong><br>
                    <span style="color: #666; margin-left: 30px;">info@xanhorganic.com</span>
                </div>
                
                <div>
                    <i class="fas fa-clock" style="color: #90c33c; width: 30px;"></i>
                    <strong>Giờ làm việc:</strong><br>
                    <span style="color: #666; margin-left: 30px;">Thứ 2 - CN: 8:00 - 22:00</span>
                </div>
            </div>
            
            <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h3 style="color: #2d5016; margin-bottom: 20px;">Kết Nối Với Chúng Tôi</h3>
                <div style="display: flex; gap: 15px;">
                    <a href="#" style="width: 50px; height: 50px; background: #4267B2; color: white; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                        <i class="fab fa-facebook fa-lg"></i>
                    </a>
                    <a href="#" style="width: 50px; height: 50px; background: #E4405F; color: white; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                        <i class="fab fa-instagram fa-lg"></i>
                    </a>
                    <a href="#" style="width: 50px; height: 50px; background: #FF0000; color: white; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                        <i class="fab fa-youtube fa-lg"></i>
                    </a>
                    <a href="#" style="width: 50px; height: 50px; background: #000000; color: white; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                        <i class="fab fa-tiktok fa-lg"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>