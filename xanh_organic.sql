-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3306
-- Thời gian đã tạo: Th10 09, 2025 lúc 02:30 PM (Đã cập nhật cấu trúc mới)
-- Phiên bản máy phục vụ: 9.1.0
-- Phiên bản PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `xanh_organic`
--

-- --------------------------------------------------------
-- Cấu trúc bảng hiện có
-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`) VALUES
(5, 4, 8, 4, '2025-11-09 13:47:59'),
(6, 4, 9, 2, '2025-11-09 13:48:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `status`, `created_at`) VALUES
(1, 'Rau Củ Hữu Cơ', 'rau-cu-huu-co', 'Rau củ tươi sạch, canh tác hữu cơ không hóa chất', '/images/products/raucu.png', 'active', '2025-11-09 09:10:45'),
(2, 'Trái Cây Hữu Cơ', 'trai-cay-huu-co', 'Trái cây ngọt tươi, an toàn cho sức khỏe', '/images/products/dualuoi.png', 'active', '2025-11-09 09:10:45'),
(3, 'Thịt & Hải Sản', 'thit-hai-san', 'Thịt sạch, hải sản tươi ngon', 'images/products/cahoi.jpg', 'active', '2025-11-09 09:10:45'),
(4, 'Ngũ Cốc & Hạt', 'ngu-coc-hat', 'Ngũ cốc dinh dưỡng, hạt giàu vitamin', 'images/products/banhquy.jpg', 'active', '2025-11-09 09:10:45'),
(5, 'Sản Phẩm Chế Biến', 'san-pham-che-bien', 'Thực phẩm chế biến hữu cơ', '/images/products/banhquy.jpg', 'active', '2025-11-09 09:10:45');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contacts`
--

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('new','read','replied') COLLATE utf8mb4_unicode_ci DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 'WinhTei', 'buiminhtai97@gmail.com', '0966330634', 'scsc', 'csc', 'new', '2025-11-09 12:53:39');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `order_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  -- CỘT MỚI: Thêm cột voucher_code và discount_amount
  `voucher_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL, 
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `payment_method` enum('cod','bank_transfer','momo') COLLATE utf8mb4_unicode_ci DEFAULT 'cod',
  `payment_status` enum('pending','paid','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `order_status` enum('pending','processing','shipping','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `full_name`, `email`, `phone`, `address`, `total_amount`, `voucher_code`, `discount_amount`, `payment_method`, `payment_status`, `order_status`, `note`, `created_at`, `updated_at`) VALUES
(1, 4, 'XO20251109180149717', 'admin', 'admin@xanhorganic.com', '0966330634', '<br />\r\n<font size=\'1\'><table class=\'xdebug-error xe-deprecated\' dir=\'ltr\' border=\'1\' cellspacing=\'0\' cellpadding=\'1\'>\r\n<tr><th align=\'left\' bgcolor=\'#f57900\' colspan=\"5\"><span style=\'background-color: #cc0000; color: #fce94f; font-size: x-large;\'>( ! )</span> Deprecated: htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated in C:\\wamp64\\www\\xanhorganic\\checkout.php on line <i>152</i></th></tr>\r\n<tr><th align=\'left\' bgcolor=\'#e9b96e\' colspan=\'5\'>Call Stack</th></tr>\r\n<tr><th align=\'center\' bgcolor=\'#eeeeec\'>#</th><th align=\'left\' bgcolor=\'#eeeeec\'>Time</th><th align=\'left\' bgcolor=\'#eeeeec\'>Memory</th><th align=\'left\' bgcolor=\'#eeeeec\'>Function</th><th align=\'left\' bgcolor=\'#eeeeec\'>Location</th></tr>\r\n<tr><td bgcolor=\'#eeeeec\' align=\'center\'>1</td><td bgcolor=\'#eeeeec\' align=\'center\'>0.0005</td><td bgcolor=\'#eeeeec\' align=\'right\'>468760</td><td bgcolor=\'#eeeeec\'>{main}(  )</td><td title=\'C:\\wamp64\\www\\xanhorganic\\checkout.php\' bgcolor=\'#eeeeec\'>...\\checkout.php<b>:</b>0</td></tr>\r\n<tr><td bgcolor=\'#eeeeec\' align=\'center\'>2</td><td bgcolor=\'#eeeeec\' align=\'center\'>0.0190</td><td bgcolor=\'#eeeeec\' align=\'right\'>608512</td><td bgcolor=\'#eeeeec\'><a href=\'http://www.php.net/function.htmlspecialchars\' target=\'_new\'>htmlspecialchars</a>( <span>$string = </span><span>NULL</span> )</td><td title=\'C:\\wamp64\\www\\xanhorganic\\checkout.php\' bgcolor=\'#eeeeec\'>...\\checkout.php<b>:</b>152</td></tr>\r\n</table></font>', 66000.00, NULL, 0.00, 'cod', 'pending', 'pending', '', '2025-11-09 11:01:49', '2025-11-09 11:01:49');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`, `subtotal`) VALUES
(1, 1, 1, 'Rau Cải Xanh Hữu Cơ', 22000.00, 3, 66000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gallery` text COLLATE utf8mb4_unicode_ci,
  `stock` int DEFAULT '0',
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'kg',
  `certification` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `origin` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `featured` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `price`, `sale_price`, `image`, `gallery`, `stock`, `unit`, `certification`, `origin`, `status`, `featured`, `created_at`, `updated_at`) VALUES
(1, 1, 'Rau Cải Xanh Hữu Cơ', 'rau-cai-xanh-huu-co', 'Rau cải xanh tươi ngon, canh tác theo tiêu chuẩn hữu cơ USDA, không hóa chất bảo vệ thực vật', 25000.00, 22000.00, NULL, NULL, 97, '300g', 'USDA Organic', 'Đà Lạt, Lâm Đồng', 'active', 1, '2025-11-09 09:10:45', '2025-11-09 11:01:49'),
(2, 1, 'Cà Chua Bi Hữu Cơ', 'ca-chua-bi-huu-co', 'Cà chua bi ngọt tự nhiên, giàu vitamin C và lycopene', 45000.00, NULL, NULL, NULL, 80, '500g', 'EU Organic', 'Đà Lạt, Lâm Đồng', 'active', 1, '2025-11-09 09:10:45', '2025-11-09 09:10:45'),
(3, 1, 'Xà Lách Xoăn', 'xa-lach-xoan', 'Xà lách xoăn giòn ngọt, hoàn hảo cho salad', 32000.00, 28000.00, NULL, NULL, 60, '250g', 'USDA Organic', 'Lâm Đồng', 'active', 0, '2025-11-09 09:10:45', '2025-11-09 09:10:45'),
(4, 2, 'Dâu Tây Hữu Cơ', 'dau-tay-huu-co', 'Dâu tây đỏ mọng, ngọt tự nhiên, giàu vitamin', 180000.00, 165000.00, NULL, NULL, 45, '500g', 'USDA Organic', 'Đà Lạt', 'active', 1, '2025-11-09 09:10:45', '2025-11-09 09:10:45'),
(5, 2, 'Chuối Già Nam Mỹ', 'chuoi-gia-nam-my', 'Chuối già hữu cơ, thơm ngon, giàu kali', 35000.00, NULL, NULL, NULL, 120, '1kg', 'EU Organic', 'Việt Nam', 'active', 0, '2025-11-09 09:10:45', '2025-11-09 09:10:45'),
(6, 3, 'Gà Ta Thả Vườn', 'ga-ta-tha-vuon', 'Gà ta nuôi thả vườn, ăn thức ăn tự nhiên, thịt chắc ngọt', 585000.00, 550000.00, NULL, NULL, 30, '1.3kg', 'VietGAP', 'Đồng Nai', 'active', 1, '2025-11-09 09:10:45', '2025-11-09 09:10:45'),
(7, 3, 'Trứng Gà Omega-3', 'trung-ga-omega-3', 'Trứng gà thả vườn, giàu omega-3, an toàn cho sức khỏe', 90000.00, NULL, NULL, NULL, 100, '10 quả', 'VietGAP', 'Lâm Đồng', 'active', 0, '2025-11-09 09:10:45', '2025-11-09 09:10:45'),
(8, 4, 'Hạt Điều Hữu Cơ', 'hat-dieu-huu-co', 'Hạt điều rang muối nhẹ, giàu protein và chất béo lành mạnh', 180000.00, 165000.00, NULL, NULL, 70, '250g', 'USDA Organic', 'Bình Phước', 'active', 1, '2025-11-09 09:10:45', '2025-11-09 09:10:45'),
(9, 4, 'Gạo Lứt Đỏ Hữu Cơ', 'gao-lut-do-huu-co', 'Gạo lứt đỏ giàu chất xơ, vitamin và khoáng chất', 95000.00, NULL, NULL, NULL, 150, '1kg', 'EU Organic', 'An Giang', 'active', 0, '2025-11-09 09:10:45', '2025-11-09 09:10:45'),
(10, 5, 'Mì Gạo Lứt Hữu Cơ', 'mi-gao-lut-huu-co', 'Mì gạo lứt nguyên chất, không chất bảo quản', 45000.00, 40000.00, NULL, NULL, 90, '300g', 'USDA Organic', 'Việt Nam', 'active', 0, '2025-11-09 09:10:45', '2025-11-09 09:10:45');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `role` enum('customer','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'customer',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `address`, `role`, `created_at`, `updated_at`) VALUES
(3, 'MinhTai', 'minhtai@gmail.com', '$2y$10$kEMLsmEwYl3fEdUZLKGlhuS3V0ZsYZ6cAAnFKf0l.YR9ggUTcWiGO', '12345678910', 'TPHCM', 'customer', '2025-11-09 10:00:07', '2025-11-09 10:06:24'),
(4, 'admin', 'admin@xanhorganic.com', '$2y$10$0xN6.qpNy6kpLzTV8wcy7e8FT2JF6pHUg3ZShWROAiYgU07CM2wHi', '0966330634', NULL, 'admin', '2025-11-09 10:14:01', '2025-11-09 10:19:31');

-- --------------------------------------------------------
-- CẤU TRÚC BẢNG MỚI (Đã hợp nhất)
-- --------------------------------------------------------

-- Bảng Wishlist (Sản phẩm yêu thích)
CREATE TABLE IF NOT EXISTS `wishlist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product` (`user_id`, `product_id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng Vouchers
CREATE TABLE IF NOT EXISTS `vouchers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `discount_type` enum('percent','fixed') COLLATE utf8mb4_unicode_ci DEFAULT 'percent',
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int DEFAULT NULL,
  `used_count` int DEFAULT 0,
  `user_limit` int DEFAULT 1 COMMENT 'Số lần 1 user có thể dùng',
  `valid_from` datetime NOT NULL,
  `valid_to` datetime NOT NULL,
  `status` enum('active','inactive','expired') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `voucher_type` enum('new_customer','loyal','general') COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng Voucher Usage (Lịch sử sử dụng voucher)
CREATE TABLE IF NOT EXISTS `voucher_usage` (
  `id` int NOT NULL AUTO_INCREMENT,
  `voucher_id` int NOT NULL,
  `user_id` int NOT NULL,
  `order_id` int NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `used_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `voucher_id` (`voucher_id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng Policies (Chính sách)
CREATE TABLE IF NOT EXISTS `policies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('shipping','purchase','return','privacy') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng Payment QR Codes
CREATE TABLE IF NOT EXISTS `payment_qrcodes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `payment_method` enum('momo','bank') COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bank_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_code_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- CẬP NHẬT CẤU TRÚC & RÀNG BUỘC
-- --------------------------------------------------------

-- Thêm cột voucher_code và discount_amount vào bảng orders
-- LƯU Ý: Nếu bảng orders đã tồn tại trong CSDL, chỉ cần chạy ALTER TABLE
ALTER TABLE `orders` 
ADD COLUMN `voucher_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `total_amount`,
ADD COLUMN `discount_amount` decimal(10,2) DEFAULT 0.00 AFTER `voucher_code`;


-- --------------------------------------------------------
-- CHÈN DỮ LIỆU MẪU MỚI
-- --------------------------------------------------------

-- Insert dữ liệu mẫu cho vouchers
INSERT INTO `vouchers` (`code`, `description`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount`, `usage_limit`, `user_limit`, `valid_from`, `valid_to`, `voucher_type`) VALUES
('NEWUSER2024', 'Giảm 15% cho khách hàng mới (tối đa 50k)', 'percent', 15.00, 200000.00, 50000.00, 100, 1, '2024-01-01 00:00:00', '2024-12-31 23:59:59', 'new_customer'),
('LOYAL50K', 'Giảm 50k cho khách hàng thân thiết', 'fixed', 50000.00, 500000.00, NULL, 50, 2, '2024-01-01 00:00:00', '2024-12-31 23:59:59', 'loyal'),
('XANHORGANIC10', 'Giảm 10% cho mọi đơn hàng', 'percent', 10.00, 100000.00, 100000.00, 200, 3, '2024-01-01 00:00:00', '2024-12-31 23:59:59', 'general');

-- Insert dữ liệu mẫu cho policies
INSERT INTO `policies` (`title`, `slug`, `content`, `type`) VALUES
('Chính Sách Giao Hàng', 'chinh-sach-giao-hang', '<h2>Thời gian giao hàng</h2><p>- Nội thành TP.HCM: 2-4 giờ</p><p>- Ngoại thành TP.HCM: 4-6 giờ</p><p>- Các tỉnh thành khác: 1-3 ngày</p><h2>Phí giao hàng</h2><p>- MIỄN PHÍ cho đơn hàng từ 200.000đ</p><p>- Đơn hàng dưới 200.000đ: 30.000đ</p>', 'shipping'),
('Chính Sách Mua Hàng', 'chinh-sach-mua-hang', '<h2>Quy trình đặt hàng</h2><p>1. Chọn sản phẩm và thêm vào giỏ hàng</p><p>2. Điền thông tin giao hàng</p><p>3. Chọn phương thức thanh toán</p><p>4. Xác nhận đơn hàng</p><h2>Phương thức thanh toán</h2><p>- COD (Thanh toán khi nhận hàng)</p><p>- Chuyển khoản ngân hàng</p><p>- Ví MoMo</p>', 'purchase'),
('Chính Sách Đổi Trả', 'chinh-sach-doi-tra', '<h2>Điều kiện đổi trả</h2><p>- Sản phẩm bị lỗi do nhà sản xuất</p><p>- Sản phẩm không đúng như đơn hàng</p><p>- Sản phẩm hư hỏng trong quá trình vận chuyển</p><h2>Thời gian đổi trả</h2><p>Trong vòng 7 ngày kể từ khi nhận hàng</p>', 'return');

-- Insert dữ liệu mẫu cho payment QR codes
INSERT INTO `payment_qrcodes` (`payment_method`, `account_name`, `account_number`, `bank_name`, `qr_code_image`) VALUES
('bank', 'CONG TY XANH ORGANIC', '1234567890', 'Vietcombank', 'qr_vietcombank.png'),
('momo', 'XANH ORGANIC', '0966330634', NULL, 'qr_momo.png');


-- --------------------------------------------------------
-- CẬP NHẬT RÀNG BUỘC KHÓA NGOẠI MỚI (FOREIGN KEYS)
-- --------------------------------------------------------

--
-- Các ràng buộc cho bảng `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `voucher_usage`
--
ALTER TABLE `voucher_usage`
  ADD CONSTRAINT `voucher_usage_ibfk_1` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `voucher_usage_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `voucher_usage_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;