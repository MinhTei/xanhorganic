-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3306
-- Thời gian đã tạo: Th10 22, 2025 lúc 01:50 PM
-- Phiên bản máy phục vụ: 9.1.0
-- Phiên bản PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `xanh_organic`
--

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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`) VALUES
(13, 3, 1, 1, '2025-11-22 02:50:31');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `image`, `status`, `created_at`) VALUES
(1, 'Rau Củ Quả Hữu Cơ', 'Rau củ quả tươi ngon, không hóa chất', 'rau-cu-qua-huu-co-1763771752.jpg', 'active', '2025-11-18 05:04:02'),
(2, 'Trái Cây Hữu Cơ', 'Trái cây tươi ngon, ngọt tự nhiên', 'trai-cay-huu-co-1763771742.jpg', 'active', '2025-11-18 05:04:02'),
(3, 'Thịt & Hải Sản Sạch', 'Thịt và hải sản sạch, an toàn', 'thit-hai-san-sach-1763771733.jpg', 'active', '2025-11-18 05:04:02'),
(4, 'Sữa & Trứng Organic', 'Sữa và trứng từ trang trại hữu cơ', 'sua-trung-organic-1763771724.jpg', 'active', '2025-11-18 05:04:02'),
(5, 'Gạo & Ngũ Cốc', 'Gạo và ngũ cốc nguyên chất', 'gao-ngu-coc-1763771712.png', 'active', '2025-11-18 05:04:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contacts`
--

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cod','bank_transfer','momo') COLLATE utf8mb4_unicode_ci DEFAULT 'cod',
  `payment_status` enum('pending','paid','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `order_status` enum('pending','processing','shipping','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `full_name`, `email`, `phone`, `address`, `note`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `created_at`, `updated_at`) VALUES
(2, 3, 'XO20251119090047601', 'Tài Minh', 'buiminhtai97@gmail.com', '0966330634', '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', '', 78000.00, 'cod', 'paid', 'completed', '2025-11-19 02:00:47', '2025-11-19 02:04:13'),
(3, 4, 'XO20251122075520587', 'admin', 'admin@xanhorganic.com', '0966330634', 'TTN\r\nTTN', '', 459000.00, 'momo', 'paid', 'cancelled', '2025-11-22 00:55:20', '2025-11-22 00:59:15'),
(4, 3, 'XO20251122082106524', 'Tài Minh', 'buiminhtai97@gmail.com', '0966330634', '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', '', 45000.00, 'cod', 'pending', 'pending', '2025-11-22 01:21:06', '2025-11-22 01:21:06'),
(5, 4, 'XO20251122094139870', 'admin', 'admin@xanhorganic.com', '0966330634', '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', '', 116000.00, 'cod', 'pending', 'pending', '2025-11-22 02:41:39', '2025-11-22 02:41:39');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `product_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`, `subtotal`) VALUES
(4, 2, 19, 'Gạo Lứt Hữu Cơ ST25', 78000.00, 1, 78000.00),
(5, 3, 6, 'Dâu Tây Hữu Cơ Đà Lạt', 99000.00, 2, 198000.00),
(6, 3, 7, 'Cam Canh Organic', 48000.00, 2, 96000.00),
(7, 3, 11, 'Thịt Gà Hữu Cơ', 165000.00, 1, 165000.00),
(8, 4, 1, 'Rau Cải Xanh Hữu Cơ', 20000.00, 1, 20000.00),
(9, 4, 4, 'Cải Thảo Hữu Cơ', 25000.00, 1, 25000.00),
(10, 5, 1, 'Rau Cải Xanh Hữu Cơ', 20000.00, 2, 40000.00),
(11, 5, 2, 'Cà Chua Bi Organic', 38000.00, 2, 76000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `selector` char(12) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `selector` (`selector`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `selector`, `token_hash`, `expires_at`, `created_at`) VALUES
(1, 3, '6963c91ec0d4', '8904ef8c4a24e5d5329f737390385e6a01df6f6e9701933b94111f5e016fed4d', '2025-11-20 18:28:12', '2025-11-20 17:28:12'),
(2, 3, 'd0aa5bf31bc6', '21c7782d2e7742d7312a9ee105acbb88746a83c09d0cb2d7945b7761944feeba', '2025-11-20 18:32:58', '2025-11-20 17:32:58'),
(3, 3, '5f3f028b10a5', '51e5ad5f73b0de1bdfcb480899b8218431fc18ae948bf72b3759092e9ecb8292', '2025-11-20 18:49:29', '2025-11-20 17:49:29'),
(4, 3, 'ff39e4c47b77', '2654cadbd5dda5869cca76c2a5ef3721818a3c4023f4d9ed00f3994a8297c3bf', '2025-11-20 18:58:42', '2025-11-20 17:58:42'),
(5, 3, '5fece02d8d54', '02218051c5f5d375856749d7059cbd0f60b1ac87b6e2ce7e46f7cf74e2e86074', '2025-11-20 19:00:49', '2025-11-20 18:00:49'),
(6, 3, '3af79d791072', '323f8321d284bc059edfc97876a3a92cd6ab7180f4073274fecb7dc4cc55a5aa', '2025-11-20 19:00:49', '2025-11-20 18:00:49'),
(7, 3, '1f0a7ea3c7e3', '0888e3082d0b369dc06b12d710e4d91f380ac4dd0781221b8bd7c79eba78f1a9', '2025-11-20 19:03:18', '2025-11-20 18:03:18'),
(8, 3, '433bad3af263', '173231bb2a080fd35c006d84359cc415052602bf1b8774fcff9a30d0ca136044', '2025-11-20 19:03:18', '2025-11-20 18:03:18'),
(9, 3, '511667493e3a', '48f5f9e4319ff5b5731570eab539152877384fa29b096c0bb1854927d7fa0fc8', '2025-11-20 19:09:59', '2025-11-20 18:09:59'),
(10, 3, '5cd4855158be', '65e313c392d05fcb2a1e825744d4a55835b939f324301b233f45d350389832d7', '2025-11-20 19:09:59', '2025-11-20 18:09:59');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'Kg',
  `stock` int DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certification` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `origin` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `featured` tinyint(1) DEFAULT '0',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `sale_price`, `unit`, `stock`, `image`, `certification`, `origin`, `featured`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Rau Cải Xanh Hữu Cơ', 'Rau cải xanh tươi ngon, không hóa chất, giàu vitamin và khoáng chất', 25000.00, 20000.00, '500g', 95, 'rau-cai-xanh-huu-co-1763772027-477111d82c.png', 'USDA Organic', 'Đà Lạt, Việt Nam', 1, 'inactive', '2025-11-18 05:04:02', '2025-11-22 04:04:05'),
(2, 1, 'Cà Chua Bi Organic', 'Cà chua bi ngọt tự nhiên, màu đỏ tươi, giàu lycopene', 45000.00, 38000.00, 'Kg', 76, 'ca-chua-bi-organic-1763772009-3acfe533b4.png', 'EU Organic', 'Lâm Đồng, Việt Nam', 1, 'inactive', '2025-11-18 05:04:02', '2025-11-22 04:04:05'),
(3, 1, 'Xà Lách Lolo Đỏ', 'Xà lách lolo đỏ giòn, ngọt, dùng làm salad tuyệt vời', 35000.00, NULL, '300g', 60, 'xa-lach-lolo-do-1763771998-0fae5aaa9b.png', 'USDA Organic', 'Đà Lạt, Việt Nam', 1, 'active', '2025-11-18 05:04:02', '2025-11-22 04:04:33'),
(4, 1, 'Cải Thảo Hữu Cơ', 'Cải thảo tươi ngon, lá dày, ngọt nước', 28000.00, 25000.00, 'Kg', 89, 'cai-thao-huu-co-1763771978-c0fb170c75.png', 'USDA Organic', 'Đà Lạt, Việt Nam', 1, 'active', '2025-11-18 05:04:02', '2025-11-22 04:04:33'),
(5, 1, 'Bông Cải Xanh Organic', 'Bông cải xanh tươi, giàu chất xơ và vitamin C', 48000.00, 42000.00, '500g', 70, 'bong-cai-xanh-organic-1763783111-87a73afeaf.png', 'EU Organic', 'Đà Lạt, Việt Nam', 1, 'inactive', '2025-11-18 05:04:02', '2025-11-22 04:00:09'),
(6, 2, 'Dâu Tây Hữu Cơ Đà Lạt', 'Dâu tây đỏ mọng, ngọt tự nhiên, không thuốc trừ sâu', 120000.00, 99000.00, 'Hộp 500g', 47, 'dau-tay-huu-co-da-lat-1763771952-cadf193de8.png', 'USDA Organic', 'Đà Lạt, Việt Nam', 1, 'inactive', '2025-11-18 05:04:02', '2025-11-22 04:01:54'),
(7, 2, 'Cam Canh Organic', 'Cam canh ngọt thanh, nhiều nước, giàu vitamin C', 55000.00, 48000.00, 'Kg', 98, 'cam-canh-organic-1763771942-c435074284.png', 'EU Organic', 'Vĩnh Long, Việt Nam', 1, 'active', '2025-11-18 05:04:02', '2025-11-22 00:55:20'),
(8, 2, 'Chuối Già Hữu Cơ', 'Chuối già chín tự nhiên, ngọt, giàu kali', 32000.00, NULL, 'Nải', 120, 'chuoi-gia-huu-co-1763771932-d313ce420d.png', 'USDA Organic', 'Đồng Tháp, Việt Nam', 1, 'active', '2025-11-18 05:04:02', '2025-11-22 04:00:34'),
(9, 2, 'Táo Fuji Organic', 'Táo Fuji giòn ngọt, màu đỏ đẹp mắt', 85000.00, 75000.00, 'Kg', 60, 'tao-fuji-organic-1763771911-ddf5bd12b6.png', 'EU Organic', 'Nhật Bản', 1, 'active', '2025-11-18 05:04:02', '2025-11-22 04:00:34'),
(10, 2, 'Xoài Cát Hòa Lộc', 'Xoài cát Hòa Lộc thơm ngon, ngọt đậm', 95000.00, 88000.00, 'Kg', 45, 'xoai-cat-hoa-loc-1763771899-0c6dc0ea79.png', 'USDA Organic', 'Tiền Giang, Việt Nam', 0, 'active', '2025-11-18 05:04:02', '2025-11-22 00:38:19'),
(11, 3, 'Thịt Gà Hữu Cơ', 'Thịt gà chăn thả, không kích thích tăng trưởng, thịt chắc ngọt', 180000.00, 165000.00, 'Kg', 39, 'thit-ga-huu-co-1763771872-483c724478.png', 'USDA Organic', 'Long An, Việt Nam', 1, 'active', '2025-11-18 05:04:02', '2025-11-22 00:55:20'),
(12, 3, 'Thịt Heo Hữu Cơ', 'Thịt heo sạch, nuôi theo tiêu chuẩn hữu cơ', 220000.00, 198000.00, 'Kg', 35, 'thit-heo-huu-co-1763771887-30ad315cd8.png', 'EU Organic', 'Đồng Nai, Việt Nam', 0, 'active', '2025-11-18 05:04:02', '2025-11-22 00:38:07'),
(13, 3, 'Tôm Sú Hữu Cơ', 'Tôm sú nuôi theo phương pháp tự nhiên, không thuốc', 350000.00, 320000.00, 'Kg', 25, 'tom-su-huu-co-1763771833-2093e56476.jpg', 'USDA Organic', 'Cà Mau, Việt Nam', 1, 'active', '2025-11-18 05:04:02', '2025-11-22 03:49:55'),
(14, 3, 'Cá Diêu Hồng Sạch', 'Cá diêu hồng nuôi bằng thức ăn tự nhiên', 145000.00, 135000.00, 'Kg', 30, '', 'EU Organic', 'Tiền Giang, Việt Nam', 1, 'active', '2025-11-18 05:04:02', '2025-11-22 03:49:55'),
(15, 4, 'Trứng Gà Hữu Cơ', 'Trứng gà từ trang trại hữu cơ, gà chăn thả tự nhiên', 65000.00, 58000.00, 'Hộp 10 quả', 80, 'trung-ga-huu-co-1763771854-c6cf9de503.jpg', 'USDA Organic', 'Đà Lạt, Việt Nam', 0, 'active', '2025-11-18 05:04:02', '2025-11-22 00:37:34'),
(16, 4, 'Sữa Tươi Organic Vinamilk', 'Sữa tươi từ bò ăn cỏ hữu cơ, không chất bảo quản', 85000.00, NULL, 'Hộp 1L', 60, 'sua-tuoi-organic-vinamilk-1763772484-65a08e0432.jpg', 'EU Organic', 'Việt Nam', 0, 'inactive', '2025-11-18 05:04:02', '2025-11-22 00:48:04'),
(17, 4, 'Phô Mai Hữu Cơ', 'Phô mai làm từ sữa bò hữu cơ, giàu canxi', 125000.00, 115000.00, 'Hộp 200g', 40, 'pho-mai-huu-co-1763771687-a954f9120c.jpg', 'USDA Organic', 'Pháp', 0, 'active', '2025-11-18 05:04:02', '2025-11-22 00:34:47'),
(18, 4, 'Sữa Chua Hy Lạp Organic', 'Sữa chua Hy Lạp đặc, giàu protein', 95000.00, 88000.00, 'Hộp 500g', 50, 'sua-chua-hy-lap-organic-1763771676-6306cf4f56.jpg', 'EU Organic', 'Việt Nam', 0, 'active', '2025-11-18 05:04:02', '2025-11-22 00:34:36'),
(19, 5, 'Gạo Lứt Hữu Cơ ST25', 'Gạo lứt ST25 thơm dẻo, giàu chất xơ', 85000.00, 78000.00, 'Kg', 99, 'gao-lut-huu-co-st25-1763771664-c5962beea6.jpg', 'USDA Organic', 'Sóc Trăng, Việt Nam', 0, 'inactive', '2025-11-18 05:04:02', '2025-11-22 00:34:24'),
(20, 5, 'Gạo Huyết Rồng Organic', 'Gạo huyết rồng màu tím tự nhiên, giàu anthocyanin', 95000.00, 88000.00, 'Kg', 70, 'gao-huyet-rong-organic-1763771651-7ac7fb0dd0.webp', 'EU Organic', 'Thái Bình, Việt Nam', 0, 'inactive', '2025-11-18 05:04:02', '2025-11-22 00:34:11'),
(21, 5, 'Yến Mạch Organic Úc', 'Yến mạch nguyên hạt, giàu chất xơ hòa tan', 120000.00, 108000.00, 'Hộp 500g', 80, 'yen-mach-organic-uc-1763771635-60c8e1dd7b.png', 'USDA Organic', 'Úc', 0, 'active', '2025-11-18 05:04:02', '2025-11-22 00:33:55'),
(22, 5, 'Hạt Quinoa Hữu Cơ', 'Hạt quinoa trắng, siêu thực phẩm giàu protein', 135000.00, 125000.00, 'Hộp 500g', 60, 'hat-quinoa-huu-co-1763771617-3bee845be6.jpg', 'EU Organic', 'Peru', 0, 'inactive', '2025-11-18 05:04:02', '2025-11-22 00:33:37'),
(23, 5, 'Hạt Chia Organic', 'Hạt chia đen, giàu omega-3 và chất xơ', 98000.00, 89000.00, 'Hộp 300g', 50, 'hat-chia-organic-1763771608-5b13867d55.png', 'USDA Organic', 'Mexico', 0, 'active', '2025-11-18 05:04:02', '2025-11-22 00:33:28');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `role` enum('customer','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'customer',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `address`, `role`, `created_at`, `updated_at`) VALUES
(3, 'Tài Minh', 'buiminhtai97@gmail.com', '$2y$10$36UYS.4YtpgzpyeBCksCZO/9.kHBJzUKFUwA214svBs7fOdu2DxKK', '0966330634', '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', 'customer', '2025-11-18 05:10:22', '2025-11-18 05:31:18'),
(4, 'admin', 'admin@xanhorganic.com', '$2y$10$rO007/ZcBMXEIwHkmIV2zemW.lMU5wtJeT/zT695al4uNaE5Gz0mi', '0966330634', '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', 'admin', '2025-11-19 02:03:27', '2025-11-22 00:57:18');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `wishlist`
--

DROP TABLE IF EXISTS `wishlist`;
CREATE TABLE IF NOT EXISTS `wishlist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
