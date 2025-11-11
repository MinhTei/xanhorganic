-- ===============================
-- HỆ THỐNG CỬA HÀNG RAU SẠCH
-- ===============================

-- Tạo database
CREATE DATABASE IF NOT EXISTS cua_hang_rau_sach 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE cua_hang_rau_sach;

-- ===============================
-- BẢNG USERS - QUẢN LÝ TÀI KHOẢN
-- ===============================
CREATE TABLE USERS (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login DATETIME,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================
-- BẢNG USER_ADDRESS - ĐỊA CHỈ GIAO HÀNG
-- ===============================
CREATE TABLE USER_ADDRESS (
    address_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recipient_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    address_line VARCHAR(255) NOT NULL,
    ward VARCHAR(100),
    district VARCHAR(100),
    city VARCHAR(100),
    is_default BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES USERS(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================
-- BẢNG CATEGORY - DANH MỤC SẢN PHẨM
-- ===============================
CREATE TABLE CATEGORY (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    slug VARCHAR(100) UNIQUE,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================
-- BẢNG PRODUCT - SẢN PHẨM RAU SẠCH
-- ===============================
CREATE TABLE PRODUCT (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    sale_price DECIMAL(10, 2),
    unit VARCHAR(20) DEFAULT 'kg',
    origin VARCHAR(100),
    description TEXT,
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES CATEGORY(category_id) ON DELETE RESTRICT,
    INDEX idx_category (category_id),
    INDEX idx_active (is_active),
    INDEX idx_price (price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================
-- BẢNG PRODUCT_INVENTORY - QUẢN LÝ TỒN KHO
-- ===============================
CREATE TABLE PRODUCT_INVENTORY (
    inventory_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL UNIQUE,
    stock_quantity INT DEFAULT 0,
    min_stock_level INT DEFAULT 10,
    max_stock_level INT DEFAULT 1000,
    last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES PRODUCT(product_id) ON DELETE CASCADE,
    INDEX idx_stock (stock_quantity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================
-- BẢNG SUPPLIER - NHÀ CUNG CẤP
-- ===============================
CREATE TABLE SUPPLIER (
    supplier_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(200) NOT NULL,
    phone VARCHAR(15),
    email VARCHAR(100),
    address VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (supplier_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================
-- BẢNG IMPORT_RECEIPT - PHIẾU NHẬP HÀNG
-- ===============================
CREATE TABLE IMPORT_RECEIPT (
    receipt_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    user_id INT NOT NULL,
    total_amount DECIMAL(15, 2) DEFAULT 0,
    note TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES SUPPLIER(supplier_id) ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES USERS(user_id) ON DELETE RESTRICT,
    INDEX idx_supplier (supplier_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================
-- BẢNG IMPORT_ITEM - CHI TIẾT PHIẾU NHẬP
-- ===============================
CREATE TABLE IMPORT_ITEM (
    import_item_id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    cost_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(15, 2) NOT NULL,
    FOREIGN KEY (receipt_id) REFERENCES IMPORT_RECEIPT(receipt_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES PRODUCT(product_id) ON DELETE RESTRICT,
    INDEX idx_receipt (receipt_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================
-- BẢNG VOUCHER - MÃ GIẢM GIÁ
-- ===============================
CREATE TABLE VOUCHER (
    voucher_id INT AUTO_INCREMENT PRIMARY KEY,
    voucher_code VARCHAR(50) NOT NULL UNIQUE,
    discount_type ENUM('percent', 'fixed') NOT NULL,
    discount_value DECIMAL(10, 2) NOT NULL,
    min_order_value DECIMAL(10, 2) DEFAULT 0,
    usage_limit INT DEFAULT 1,
    used_count INT DEFAULT 0,
    valid_from DATETIME NOT NULL,
    valid_to DATETIME NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_code (voucher_code),
    INDEX idx_active (is_active),
    INDEX idx_valid (valid_from, valid_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================
-- BẢNG ORDER - ĐƠN HÀNG
-- ===============================
CREATE TABLE `ORDER` (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    address_id INT NOT NULL,
    voucher_id INT,
    order_code VARCHAR(50) NOT NULL UNIQUE,
    subtotal DECIMAL(15, 2) NOT NULL,
    discount_amount DECIMAL(10, 2) DEFAULT 0,
    shipping_fee DECIMAL(10, 2) DEFAULT 0,
    total_amount DECIMAL(15, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'shipping', 'delivered', 'cancelled') DEFAULT 'pending',
    shipper_name VARCHAR(100),
    tracking_code VARCHAR(100),
    estimated_delivery DATETIME,
    delivered_at DATETIME,
    note TEXT,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES USERS(user_id) ON DELETE RESTRICT,
    FOREIGN KEY (address_id) REFERENCES USER_ADDRESS(address_id) ON DELETE RESTRICT,
    FOREIGN KEY (voucher_id) REFERENCES VOUCHER(voucher_id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_order_date (order_date),
    INDEX idx_order_code (order_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================
-- BẢNG ORDER_ITEM - CHI TIẾT ĐƠN HÀNG
-- ===============================
CREATE TABLE ORDER_ITEM (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(15, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES `ORDER`(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES PRODUCT(product_id) ON DELETE RESTRICT,
    INDEX idx_order (order_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================
-- BẢNG PAYMENT - THANH TOÁN
-- ===============================
CREATE TABLE PAYMENT (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    payment_method ENUM('COD', 'bank_transfer', 'momo') NOT NULL,
    payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
    transaction_id VARCHAR(100),
    payment_date DATETIME,
    note TEXT,
    FOREIGN KEY (order_id) REFERENCES `ORDER`(order_id) ON DELETE CASCADE,
    INDEX idx_order (order_id),
    INDEX idx_status (payment_status),
    INDEX idx_transaction (transaction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================
-- BẢNG ORDER_STATUS_LOG - LỊCH SỬ TRẠNG THÁI
-- ===============================
CREATE TABLE ORDER_STATUS_LOG (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    updated_by INT NOT NULL,
    old_status VARCHAR(50),
    new_status VARCHAR(50) NOT NULL,
    note TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES `ORDER`(order_id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES USERS(user_id) ON DELETE RESTRICT,
    INDEX idx_order (order_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================
-- BẢNG CART_ITEM - GIỎ HÀNG
-- ===============================
CREATE TABLE CART_ITEM (
    cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES USERS(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES PRODUCT(product_id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart (user_id, product_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================
-- BẢNG FAVORITE - YÊU THÍCH
-- ===============================
CREATE TABLE FAVORITE (
    favorite_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES USERS(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES PRODUCT(product_id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, product_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================
-- TRIGGER TỰ ĐỘNG CẬP NHẬT TỒN KHO
-- ===============================

-- Trigger khi nhập hàng: tăng tồn kho
DELIMITER $$
CREATE TRIGGER after_import_item_insert
AFTER INSERT ON IMPORT_ITEM
FOR EACH ROW
BEGIN
    UPDATE PRODUCT_INVENTORY 
    SET stock_quantity = stock_quantity + NEW.quantity,
        last_updated = CURRENT_TIMESTAMP
    WHERE product_id = NEW.product_id;
END$$
DELIMITER ;

-- Trigger khi đặt hàng: giảm tồn kho
DELIMITER $$
CREATE TRIGGER after_order_item_insert
AFTER INSERT ON ORDER_ITEM
FOR EACH ROW
BEGIN
    UPDATE PRODUCT_INVENTORY 
    SET stock_quantity = stock_quantity - NEW.quantity,
        last_updated = CURRENT_TIMESTAMP
    WHERE product_id = NEW.product_id;
END$$
DELIMITER ;

-- ===============================
-- DỮ LIỆU MẪU CHO TẤT CẢ CÁC BẢNG
-- ===============================

-- ========== USERS - TÀI KHOẢN ==========
INSERT INTO USERS (email, password_hash, full_name, phone, role, is_active) VALUES
('admin@xanhorganic.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Xanh Organic', '0901234567', 'admin', TRUE),
('nguyenvana@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn A', '0909876543', 'user', TRUE),
('tranthib@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị B', '0912345678', 'user', TRUE),
('levanc@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lê Văn C', '0923456789', 'user', TRUE);

-- ========== USER_ADDRESS - ĐỊA CHỈ GIAO HÀNG ==========
INSERT INTO USER_ADDRESS (user_id, recipient_name, phone, address_line, ward, district, city, is_default) VALUES
(2, 'Nguyễn Văn A', '0909876543', '123 Nguyễn Văn Linh', 'Phường Tân Phú', 'Quận 7', 'TP. Hồ Chí Minh', TRUE),
(2, 'Nguyễn Văn A', '0909876543', '456 Lê Lợi', 'Phường Bến Thành', 'Quận 1', 'TP. Hồ Chí Minh', FALSE),
(3, 'Trần Thị B', '0912345678', '789 Trần Hưng Đạo', 'Phường 1', 'Quận 5', 'TP. Hồ Chí Minh', TRUE),
(4, 'Lê Văn C', '0923456789', '321 Lý Tự Trọng', 'Phường Bến Nghé', 'Quận 1', 'TP. Hồ Chí Minh', TRUE);

-- ========== CATEGORY - DANH MỤC SẢN PHẨM ==========
INSERT INTO CATEGORY (category_name, description, slug, display_order, is_active) VALUES
('Rau Củ Quả Hữu Cơ', 'Rau củ quả tươi ngon, không hóa chất, giàu vitamin', 'rau-cu-qua', 1, TRUE),
('Trái Cây Hữu Cơ', 'Trái cây tươi ngon, ngọt tự nhiên, không thuốc bảo quản', 'trai-cay', 2, TRUE),
('Thịt & Hải Sản Sạch', 'Thịt và hải sản sạch, an toàn, nguồn gốc rõ ràng', 'thit-hai-san', 3, TRUE),
('Sữa & Trứng Organic', 'Sữa và trứng từ trang trại hữu cơ, không kháng sinh', 'sua-trung', 4, TRUE),
('Gạo & Ngũ Cốc', 'Gạo và ngũ cốc nguyên chất, giàu chất xơ', 'gao-ngu-coc', 5, TRUE),
('Rau Ăn Lá', 'Rau ăn lá xanh tươi, giàu dinh dưỡng', 'rau-an-la', 6, TRUE);

-- ========== PRODUCT - SẢN PHẨM RAU SẠCH ==========
INSERT INTO PRODUCT (category_id, product_name, price, sale_price, unit, origin, description, image_url, is_active) VALUES
-- Rau củ quả
(1, 'Rau Cải Xanh Hữu Cơ', 25000, 20000, '500g', 'Đà Lạt, Việt Nam', 'Rau cải xanh tươi ngon, không hóa chất, giàu vitamin và khoáng chất', 'images/products/rau-cai-xanh.jpg', TRUE),
(1, 'Cà Chua Bi Organic', 45000, 38000, 'Kg', 'Lâm Đồng, Việt Nam', 'Cà chua bi ngọt tự nhiên, màu đỏ tươi, giàu lycopene', 'images/products/ca-chua-bi.jpg', TRUE),
(1, 'Bí Đỏ Hữu Cơ', 35000, 30000, 'Kg', 'Đà Lạt, Việt Nam', 'Bí đỏ ngọt, giàu beta-carotene, tốt cho mắt', 'images/products/bi-do.jpg', TRUE),
(1, 'Cà Rót Tím', 28000, 25000, '500g', 'Lâm Đồng, Việt Nam', 'Cà rót tím tươi, giàu chất chống oxi hóa', 'images/products/ca-rot-tim.jpg', TRUE),

-- Trái cây
(2, 'Dâu Tây Hữu Cơ Đà Lạt', 120000, 99000, 'Hộp 500g', 'Đà Lạt, Việt Nam', 'Dâu tây đỏ mọng, ngọt tự nhiên, không thuốc trừ sâu', 'images/products/dau-tay.jpg', TRUE),
(2, 'Bơ Sáp Đà Lạt', 80000, 70000, 'Kg', 'Đà Lạt, Việt Nam', 'Bơ sáp béo ngậy, không hạt, giàu dinh dưỡng', 'images/products/bo-sap.jpg', TRUE),
(2, 'Táo Fuji Hữu Cơ', 95000, 85000, 'Kg', 'Nhật Bản', 'Táo Fuji giòn ngọt, giàu chất xơ', 'images/products/tao-fuji.jpg', TRUE),
(2, 'Cam Sành Hữu Cơ', 55000, 48000, 'Kg', 'Hà Giang, Việt Nam', 'Cam sành ngọt thanh, nhiều nước, giàu vitamin C', 'images/products/cam-sanh.jpg', TRUE),

-- Sữa & Trứng
(4, 'Trứng Gà Hữu Cơ', 65000, 58000, 'Hộp 10 quả', 'Đà Lạt, Việt Nam', 'Trứng gà từ trang trại hữu cơ, gà chăn thả tự nhiên', 'images/products/trung-ga.jpg', TRUE),
(4, 'Sữa Tươi Organic', 85000, 78000, 'Lít', 'Đà Lạt, Việt Nam', 'Sữa tươi nguyên chất 100%, không chất bảo quản', 'images/products/sua-tuoi.jpg', TRUE),

-- Gạo & Ngũ cốc
(5, 'Gạo Lứt Hữu Cơ ST25', 85000, 78000, 'Kg', 'Sóc Trăng, Việt Nam', 'Gạo lứt ST25 thơm dẻo, giàu chất xơ', 'images/products/gao-lut-st25.jpg', TRUE),
(5, 'Gạo Huyết Rồng', 120000, 110000, 'Kg', 'Quảng Trị, Việt Nam', 'Gạo huyết rồng đỏ tím, giàu anthocyanin', 'images/products/gao-huyet-rong.jpg', TRUE),

-- Rau ăn lá
(6, 'Xà Lách Tím Hữu Cơ', 30000, 25000, '500g', 'Đà Lạt, Việt Nam', 'Xà lách tím giòn ngọt, làm salad ngon', 'images/products/xa-lach-tim.jpg', TRUE),
(6, 'Rau Muống Hữu Cơ', 18000, 15000, '500g', 'TP.HCM, Việt Nam', 'Rau muống tươi non, xào ăn giòn ngọt', 'images/products/rau-muong.jpg', TRUE);

-- ========== PRODUCT_INVENTORY - TỒN KHO ==========
INSERT INTO PRODUCT_INVENTORY (product_id, stock_quantity, min_stock_level, max_stock_level) VALUES
(1, 100, 10, 200), (2, 80, 10, 150), (3, 60, 10, 100), (4, 70, 10, 120),
(5, 50, 10, 100), (6, 45, 10, 80), (7, 55, 10, 90), (8, 65, 10, 110),
(9, 80, 10, 150), (10, 90, 10, 180), (11, 100, 20, 200), (12, 95, 20, 180),
(13, 75, 10, 120), (14, 85, 10, 150);

-- ========== SUPPLIER - NHÀ CUNG CẤP ==========
INSERT INTO SUPPLIER (supplier_name, phone, email, address) VALUES
('Trang Trại Đà Lạt Organic', '0907123456', 'contact@dalat-organic.vn', '123 Đường Hồ Xuân Hương, Đà Lạt, Lâm Đồng'),
('Vườn Rau Hà Nội Sạch', '0908234567', 'hanoi@rausach.vn', '456 Đường Láng Hạ, Ba Đình, Hà Nội'),
('Nông Trại Xanh Việt', '0909345678', 'info@nongtrai-xanh.vn', '789 Quốc Lộ 1A, Hóc Môn, TP.HCM'),
('Trang Trại Hải Sản Sạch', '0910456789', 'haisan@clean.vn', '321 Đường Biển, Vũng Tàu, Bà Rịa - Vũng Tàu');

-- ========== IMPORT_RECEIPT - PHIẾU NHẬP HÀNG ==========
INSERT INTO IMPORT_RECEIPT (supplier_id, user_id, total_amount, note) VALUES
(1, 1, 15000000, 'Nhập hàng tháng 11/2025 - Rau củ quả Đà Lạt'),
(2, 1, 8500000, 'Nhập hàng tháng 11/2025 - Rau ăn lá Hà Nội'),
(3, 1, 12000000, 'Nhập hàng tháng 11/2025 - Gạo và ngũ cốc');

-- ========== IMPORT_ITEM - CHI TIẾT PHIẾU NHẬP ==========
INSERT INTO IMPORT_ITEM (receipt_id, product_id, quantity, cost_price, total_price) VALUES
-- Phiếu nhập 1
(1, 1, 100, 18000, 1800000),
(1, 2, 80, 32000, 2560000),
(1, 5, 50, 85000, 4250000),
-- Phiếu nhập 2
(2, 13, 75, 20000, 1500000),
(2, 14, 85, 12000, 1020000),
-- Phiếu nhập 3
(3, 11, 100, 68000, 6800000),
(3, 12, 95, 95000, 9025000);

-- ========== VOUCHER - MÃ GIẢM GIÁ ==========
INSERT INTO VOUCHER (voucher_code, discount_type, discount_value, min_order_value, usage_limit, used_count, valid_from, valid_to, is_active) VALUES
('WELCOME10', 'percent', 10.00, 0, 100, 5, '2025-01-01 00:00:00', '2025-12-31 23:59:59', TRUE),
('FLAT50K', 'fixed', 50000.00, 300000, 50, 3, '2025-01-01 00:00:00', '2025-12-31 23:59:59', TRUE),
('NEWYEAR2025', 'percent', 15.00, 500000, 200, 10, '2025-01-01 00:00:00', '2025-01-31 23:59:59', TRUE),
('FREESHIP', 'fixed', 30000.00, 200000, 150, 8, '2025-11-01 00:00:00', '2025-11-30 23:59:59', TRUE),
('VIP20', 'percent', 20.00, 1000000, 30, 2, '2025-01-01 00:00:00', '2025-12-31 23:59:59', TRUE);

-- ========== ORDER - ĐơN HÀNG ==========
INSERT INTO `ORDER` (user_id, address_id, voucher_id, order_code, subtotal, discount_amount, shipping_fee, total_amount, status, order_date) VALUES
(2, 1, 1, 'DH2025110001', 450000, 45000, 30000, 435000, 'delivered', '2025-11-01 10:30:00'),
(3, 3, 2, 'DH2025110002', 850000, 50000, 30000, 830000, 'shipping', '2025-11-05 14:20:00'),
(4, 4, NULL, 'DH2025110003', 280000, 0, 30000, 310000, 'confirmed', '2025-11-08 09:15:00'),
(2, 2, 4, 'DH2025110004', 650000, 30000, 0, 620000, 'pending', '2025-11-10 16:45:00');

-- ========== ORDER_ITEM - CHI TIẾT ĐƠN HÀNG ==========
INSERT INTO ORDER_ITEM (order_id, product_id, quantity, price, subtotal) VALUES
-- Đơn hàng 1
(1, 1, 5, 20000, 100000),
(1, 2, 3, 38000, 114000),
(1, 5, 2, 99000, 198000),
-- Đơn hàng 2
(2, 9, 4, 58000, 232000),
(2, 11, 5, 78000, 390000),
(2, 13, 3, 25000, 75000),
-- Đơn hàng 3
(3, 3, 2, 30000, 60000),
(3, 14, 5, 15000, 75000),
(3, 6, 2, 70000, 140000),
-- Đơn hàng 4
(4, 7, 3, 85000, 255000),
(4, 8, 4, 48000, 192000),
(4, 10, 1, 78000, 78000);

-- ========== PAYMENT - THANH TOÁN ==========
INSERT INTO PAYMENT (order_id, amount, payment_method, payment_status, transaction_id, payment_date) VALUES
(1, 435000, 'momo', 'paid', 'MOMO2025110001', '2025-11-01 10:35:00'),
(2, 830000, 'bank_transfer', 'paid', 'BANK2025110002', '2025-11-05 14:30:00'),
(3, 310000, 'COD', 'unpaid', NULL, NULL),
(4, 620000, 'momo', 'unpaid', NULL, NULL);

-- ========== ORDER_STATUS_LOG - LỊCH SỬ TRẠNG THÁI ==========
INSERT INTO ORDER_STATUS_LOG (order_id, updated_by, old_status, new_status, note) VALUES
(1, 1, 'pending', 'confirmed', 'Đơn hàng đã được xác nhận'),
(1, 1, 'confirmed', 'shipping', 'Đơn hàng đang giao'),
(1, 1, 'shipping', 'delivered', 'Đơn hàng đã giao thành công'),
(2, 1, 'pending', 'confirmed', 'Đơn hàng đã được xác nhận'),
(2, 1, 'confirmed', 'shipping', 'Đơn hàng đang trên đường giao'),
(3, 1, 'pending', 'confirmed', 'Đơn hàng đã được xác nhận');

-- ========== CART_ITEM - GIỎ HÀNG ==========
INSERT INTO CART_ITEM (user_id, product_id, quantity) VALUES
(2, 7, 2),
(2, 12, 1),
(3, 4, 3),
(3, 6, 2),
(4, 1, 5),
(4, 9, 2);

-- ========== FAVORITE - YÊU THÍCH ==========
INSERT INTO FAVORITE (user_id, product_id) VALUES
(2, 1), (2, 5), (2, 9), (2, 11),
(3, 2), (3, 7), (3, 10),
(4, 3), (4, 6), (4, 8), (4, 12);

-- ===============================
-- HOÀN TẤT
-- ===============================
SELECT 'Database với dữ liệu mẫu đã được tạo thành công!' AS Status;