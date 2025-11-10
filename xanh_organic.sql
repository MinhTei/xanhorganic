-- =====================================
-- DATABASE: xanh_organic (CẬP NHẬT)
-- =====================================

CREATE DATABASE IF NOT EXISTS xanh_organic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE xanh_organic;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS voucher_usage, order_items, cart, wishlist, orders, products, categories, vouchers, policies, contacts, payment_qrcodes, users;
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================
-- 1. USERS TABLE
-- =====================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- 2. CATEGORIES TABLE
-- =====================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- 3. PRODUCTS TABLE
-- =====================================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2),
    unit VARCHAR(20) DEFAULT 'Kg',
    stock INT DEFAULT 0,
    image VARCHAR(255),
    certification VARCHAR(100),
    origin VARCHAR(100),
    featured TINYINT(1) DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- 4. WISHLIST TABLE
-- =====================================
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- 5. CART TABLE
-- =====================================
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- 6. ORDERS TABLE
-- =====================================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    note TEXT,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cod', 'bank_transfer', 'momo') DEFAULT 'cod',
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    order_status ENUM('pending', 'processing', 'shipping', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- 7. ORDER_ITEMS TABLE
-- =====================================
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(200) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- 8. CONTACTS TABLE
-- =====================================
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- INSERT SAMPLE DATA
-- =====================================

-- Tạo admin và customer mẫu
INSERT INTO users (full_name, email, password, phone, address, role) VALUES
('Admin', 'admin@xanhorganic.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901234567', '123 Đường ABC, Quận 1, TP.HCM', 'admin'),
('Nguyễn Văn A', 'customer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0909876543', '456 Đường XYZ, Quận 3, TP.HCM', 'customer');
-- Password cho cả 2: password

-- Thêm danh mục
INSERT INTO categories (name, description, image, status) VALUES
('Rau Củ Quả Hữu Cơ', 'Rau củ quả tươi ngon, không hóa chất', 'images/categories/rau-cu-qua.jpg', 'active'),
('Trái Cây Hữu Cơ', 'Trái cây tươi ngon, ngọt tự nhiên', 'images/categories/trai-cay.jpg', 'active'),
('Thịt & Hải Sản Sạch', 'Thịt và hải sản sạch, an toàn', 'images/categories/thit-hai-san.jpg', 'active'),
('Sữa & Trứng Organic', 'Sữa và trứng từ trang trại hữu cơ', 'images/categories/sua-trung.jpg', 'active'),
('Gạo & Ngũ Cốc', 'Gạo và ngũ cốc nguyên chất', 'images/categories/gao-ngu-coc.jpg', 'active');

-- Thêm sản phẩm Rau Củ Quả (category_id = 1)
INSERT INTO products (category_id, name, description, price, sale_price, unit, stock, image, certification, origin, featured, status) VALUES
(1, 'Rau Cải Xanh Hữu Cơ', 'Rau cải xanh tươi ngon, không hóa chất, giàu vitamin và khoáng chất', 25000, 20000, '500g', 100, 'images/products/rau-cai-xanh.jpg', 'USDA Organic', 'Đà Lạt, Việt Nam', 1, 'active'),
(1, 'Cà Chua Bi Organic', 'Cà chua bi ngọt tự nhiên, màu đỏ tươi, giàu lycopene', 45000, 38000, 'Kg', 80, 'images/products/ca-chua-bi.jpg', 'EU Organic', 'Lâm Đồng, Việt Nam', 1, 'active'),
(1, 'Xà Lách Lolo Đỏ', 'Xà lách lolo đỏ giòn, ngọt, dùng làm salad tuyệt vời', 35000, NULL, '300g', 60, 'images/products/xa-lach-lolo-do.jpg', 'USDA Organic', 'Đà Lạt, Việt Nam', 0, 'active'),
(1, 'Cải Thảo Hữu Cơ', 'Cải thảo tươi ngon, lá dày, ngọt nước', 28000, 25000, 'Kg', 90, 'images/products/cai-thao.jpg', 'USDA Organic', 'Đà Lạt, Việt Nam', 0, 'active'),
(1, 'Bông Cải Xanh Organic', 'Bông cải xanh tươi, giàu chất xơ và vitamin C', 48000, 42000, '500g', 70, 'images/products/bong-cai-xanh.jpg', 'EU Organic', 'Đà Lạt, Việt Nam', 1, 'active');

-- Thêm sản phẩm Trái Cây (category_id = 2)
INSERT INTO products (category_id, name, description, price, sale_price, unit, stock, image, certification, origin, featured, status) VALUES
(2, 'Dâu Tây Hữu Cơ Đà Lạt', 'Dâu tây đỏ mọng, ngọt tự nhiên, không thuốc trừ sâu', 120000, 99000, 'Hộp 500g', 50, 'images/products/dau-tay.jpg', 'USDA Organic', 'Đà Lạt, Việt Nam', 1, 'active'),
(2, 'Cam Canh Organic', 'Cam canh ngọt thanh, nhiều nước, giàu vitamin C', 55000, 48000, 'Kg', 100, 'images/products/cam-canh.jpg', 'EU Organic', 'Vĩnh Long, Việt Nam', 1, 'active'),
(2, 'Chuối Già Hữu Cơ', 'Chuối già chín tự nhiên, ngọt, giàu kali', 32000, NULL, 'Nải', 120, 'images/products/chuoi-gia.jpg', 'USDA Organic', 'Đồng Tháp, Việt Nam', 0, 'active'),
(2, 'Táo Fuji Organic', 'Táo Fuji giòn ngọt, màu đỏ đẹp mắt', 85000, 75000, 'Kg', 60, 'images/products/tao-fuji.jpg', 'EU Organic', 'Nhật Bản', 1, 'active'),
(2, 'Xoài Cát Hòa Lộc', 'Xoài cát Hòa Lộc thơm ngon, ngọt đậm', 95000, 88000, 'Kg', 45, 'images/products/xoai-hoa-loc.jpg', 'USDA Organic', 'Tiền Giang, Việt Nam', 0, 'active');

-- Thêm sản phẩm Thịt & Hải Sản (category_id = 3)
INSERT INTO products (category_id, name, description, price, sale_price, unit, stock, image, certification, origin, featured, status) VALUES
(3, 'Thịt Gà Hữu Cơ', 'Thịt gà chăn thả, không kích thích tăng trưởng, thịt chắc ngọt', 180000, 165000, 'Kg', 40, 'images/products/thit-ga.jpg', 'USDA Organic', 'Long An, Việt Nam', 1, 'active'),
(3, 'Thịt Heo Hữu Cơ', 'Thịt heo sạch, nuôi theo tiêu chuẩn hữu cơ', 220000, 198000, 'Kg', 35, 'images/products/thit-heo.jpg', 'EU Organic', 'Đồng Nai, Việt Nam', 0, 'active'),
(3, 'Tôm Sú Hữu Cơ', 'Tôm sú nuôi theo phương pháp tự nhiên, không thuốc', 350000, 320000, 'Kg', 25, 'images/products/tom-su.jpg', 'USDA Organic', 'Cà Mau, Việt Nam', 1, 'active'),
(3, 'Cá Diêu Hồng Sạch', 'Cá diêu hồng nuôi bằng thức ăn tự nhiên', 145000, 135000, 'Kg', 30, 'images/products/ca-dieu-hong.jpg', 'EU Organic', 'Tiền Giang, Việt Nam', 0, 'active');

-- Thêm sản phẩm Sữa & Trứng (category_id = 4)
INSERT INTO products (category_id, name, description, price, sale_price, unit, stock, image, certification, origin, featured, status) VALUES
(4, 'Trứng Gà Hữu Cơ', 'Trứng gà từ trang trại hữu cơ, gà chăn thả tự nhiên', 65000, 58000, 'Hộp 10 quả', 80, 'images/products/trung-ga.jpg', 'USDA Organic', 'Đà Lạt, Việt Nam', 1, 'active'),
(4, 'Sữa Tươi Organic Vinamilk', 'Sữa tươi từ bò ăn cỏ hữu cơ, không chất bảo quản', 85000, NULL, 'Hộp 1L', 60, 'images/products/sua-tuoi.jpg', 'EU Organic', 'Việt Nam', 1, 'active'),
(4, 'Phô Mai Hữu Cơ', 'Phô mai làm từ sữa bò hữu cơ, giàu canxi', 125000, 115000, 'Hộp 200g', 40, 'images/products/pho-mai.jpg', 'USDA Organic', 'Pháp', 0, 'active'),
(4, 'Sữa Chua Hy Lạp Organic', 'Sữa chua Hy Lạp đặc, giàu protein', 95000, 88000, 'Hộp 500g', 50, 'images/products/sua-chua.jpg', 'EU Organic', 'Việt Nam', 0, 'active');

-- Thêm sản phẩm Gạo & Ngũ Cốc (category_id = 5)
INSERT INTO products (category_id, name, description, price, sale_price, unit, stock, image, certification, origin, featured, status) VALUES
(5, 'Gạo Lứt Hữu Cơ ST25', 'Gạo lứt ST25 thơm dẻo, giàu chất xơ', 85000, 78000, 'Kg', 100, 'images/products/gao-lut-st25.jpg', 'USDA Organic', 'Sóc Trăng, Việt Nam', 1, 'active'),
(5, 'Gạo Huyết Rồng Organic', 'Gạo huyết rồng màu tím tự nhiên, giàu anthocyanin', 95000, 88000, 'Kg', 70, 'images/products/gao-huyet-rong.jpg', 'EU Organic', 'Thái Bình, Việt Nam', 1, 'active'),
(5, 'Yến Mạch Organic Úc', 'Yến mạch nguyên hạt, giàu chất xơ hòa tan', 120000, 108000, 'Hộp 500g', 80, 'images/products/yen-mach.jpg', 'USDA Organic', 'Úc', 0, 'active'),
(5, 'Hạt Quinoa Hữu Cơ', 'Hạt quinoa trắng, siêu thực phẩm giàu protein', 135000, 125000, 'Hộp 500g', 60, 'images/products/quinoa.jpg', 'EU Organic', 'Peru', 1, 'active'),
(5, 'Hạt Chia Organic', 'Hạt chia đen, giàu omega-3 và chất xơ', 98000, 89000, 'Hộp 300g', 50, 'images/products/hat-chia.jpg', 'USDA Organic', 'Mexico', 0, 'active');