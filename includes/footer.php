<?php
// QUAN TRỌNG: Footer cần đảm bảo các hằng số (constants) như SITE_URL được định nghĩa
// File footer.php thường được gọi sau header.php. Nếu không, phải nạp config.php ở đây.
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/config.php';
}
// Nếu không cần các hàm trong functions.php, hãy xóa require_once sau:
// require_once __DIR__ . '/functions.php'; 
?>
</main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-col">
                    <h3><i class="fas fa-leaf"></i> Xanh Organic</h3>
                    <p>Cung cấp thực phẩm hữu cơ chứng nhận quốc tế USDA, EU cho người Việt. Cam kết 100% sản phẩm sạch, an toàn.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>

                <div class="footer-col">
                    <h4>Về chúng tôi</h4>
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>/about.php">Giới thiệu</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/products.php">Sản phẩm</a></li>
                        <li><a href="#">Chứng nhận</a></li>
                        <li><a href="#">Tin tức</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Chính sách</h4>
                    <ul>
                        <li><a href="#">Chính sách đổi trả</a></li>
                        <li><a href="#">Chính sách bảo mật</a></li>
                        <li><a href="#">Điều khoản sử dụng</a></li>
                        <li><a href="#">Hướng dẫn mua hàng</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Liên hệ</h4>
                    <ul class="contact-info">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            123 Đường ABC, Quận 1, TP.HCM
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            Hotline: 1900 1234
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            info@xanhorganic.com
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            8:00 - 22:00 (Hàng ngày)
                        </li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="payment-methods">
                    <img src="https://via.placeholder.com/50x30?text=Visa" alt="Visa">
                    <img src="https://via.placeholder.com/50x30?text=Master" alt="Mastercard">
                    <img src="https://via.placeholder.com/50x30?text=Momo" alt="Momo">
                    <img src="https://via.placeholder.com/50x30?text=COD" alt="COD">
                </div>
                <p>&copy; 2024 Xanh Organic. Bản quyền thuộc về Xanh Organic.</p>
            </div>
        </div>
    </footer>

    <button class="back-to-top" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>