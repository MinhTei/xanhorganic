<?php
/**
 * INCLUDES/FUNCTIONS.PHP - Hàm tiện ích cho toàn hệ thống
 * 
 * Đã sửa tất cả các vấn đề:
 * - Null safety với ?? operator
 * - Xử lý lỗi htmlspecialchars với null
 * - Kiểm tra tồn tại trước khi truy xuất
 */

/**
 * HÀM AN TOÀN ĐỂ ESCAPE HTML (Tránh lỗi Deprecated với NULL)
 * 
 * @param mixed $value - Giá trị cần escape
 * @return string - Chuỗi đã escape hoặc rỗng
 */
function safe_html($value) {
    // Nếu null hoặc rỗng, trả về chuỗi rỗng
    if ($value === null || $value === '') {
        return '';
    }
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// ===== QUẢN LÝ USER =====

/**
 * Lấy thông tin user hiện tại từ session
 * 
 * @return array|null - Thông tin user hoặc null nếu chưa đăng nhập
 */
function getCurrentUser() {
    global $conn;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

// ===== QUẢN LÝ CATEGORIES =====

/**
 * Lấy danh sách tất cả categories đang hoạt động
 * 
 * @return array - Mảng các categories
 */
function getCategories() {
    global $conn;
    
    $sql = "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC";
    $result = $conn->query($sql);
    
    $categories = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    
    return $categories;
}

/**
 * Lấy thông tin category theo ID
 * 
 * @param int $id - ID của category
 * @return array|null - Thông tin category hoặc null
 */
function getCategoryById($id) {
    global $conn;
    
    $sql = "SELECT * FROM categories WHERE id = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

// ===== QUẢN LÝ PRODUCTS =====

/**
 * Lấy sản phẩm theo ID (với thông tin category)
 * 
 * @param int $id - ID sản phẩm
 * @return array|null - Thông tin sản phẩm hoặc null
 */
function getProductById($id) {
    global $conn;
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ? AND p.status = 'active'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Lấy sản phẩm theo category
 * 
 * @param int $category_id - ID category
 * @param int|null $limit - Giới hạn số lượng (null = không giới hạn)
 * @return array - Mảng sản phẩm
 */
function getProductsByCategory($category_id, $limit = null) {
    global $conn;
    
    $sql = "SELECT * FROM products WHERE category_id = ? AND status = 'active' ORDER BY created_at DESC";
    
    if ($limit !== null) {
        $sql .= " LIMIT " . (int)$limit;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    return $products;
}

/**
 * Lấy sản phẩm nổi bật
 * 
 * @param int $limit - Số lượng sản phẩm
 * @return array - Mảng sản phẩm nổi bật
 */
function getFeaturedProducts($limit = 8) {
    global $conn;
    
    $sql = "SELECT * FROM products WHERE featured = 1 AND status = 'active' ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    return $products;
}

/**
 * Lấy sản phẩm mới nhất
 * 
 * @param int $limit - Số lượng sản phẩm
 * @return array - Mảng sản phẩm mới
 */
function getLatestProducts($limit = 8) {
    global $conn;
    
    $sql = "SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    return $products;
}

// ===== QUẢN LÝ GIỎ HÀNG =====

/**
 * Đếm số sản phẩm trong giỏ hàng
 * 
 * @return int - Số lượng sản phẩm
 */
function getCartCount() {
    global $conn;
    
    if (!isLoggedIn()) {
        // Chưa đăng nhập -> Đếm từ session
        return isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
    }
    
    // Đã đăng nhập -> Đếm từ database
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT COUNT(*) as count FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return (int)($row['count'] ?? 0);
}

/**
 * Lấy toàn bộ giỏ hàng
 * 
 * @return array - Mảng các item trong giỏ hàng
 */
function getCart() {
    global $conn;
    
    if (!isLoggedIn()) {
        // Chưa đăng nhập -> Lấy từ session
        $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
        
        // Chuyển đổi format từ session sang giống database
        $formatted_cart = [];
        foreach ($cart as $product_id => $quantity) {
            $formatted_cart[] = [
                'product_id' => $product_id,
                'quantity' => $quantity
            ];
        }
        return $formatted_cart;
    }
    
    // Đã đăng nhập -> Lấy từ database
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT c.product_id, c.quantity 
            FROM cart c 
            WHERE c.user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $cart = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $cart[] = $row;
        }
    }
    
    return $cart;
}

/**
 * Thêm sản phẩm vào giỏ hàng
 * 
 * @param int $product_id - ID sản phẩm
 * @param int $quantity - Số lượng (mặc định 1)
 * @return bool - True nếu thành công
 */
function addToCart($product_id, $quantity = 1) {
    global $conn;
    
    // Đảm bảo số lượng là số dương
    $quantity = max(1, (int)$quantity);
    
    if (!isLoggedIn()) {
        // Chưa đăng nhập -> Lưu vào session
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
        
        return true;
    }
    
    // Đã đăng nhập -> Lưu vào database
    $user_id = $_SESSION['user_id'];
    
    // Kiểm tra sản phẩm đã có trong giỏ chưa
    $sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Đã có -> Cập nhật số lượng
        $sql = "UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $quantity, $user_id, $product_id);
    } else {
        // Chưa có -> Thêm mới
        $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
    }
    
    return $stmt->execute();
}

/**
 * Cập nhật số lượng sản phẩm trong giỏ hàng
 * 
 * @param int $product_id - ID sản phẩm
 * @param int $quantity - Số lượng mới (0 = xóa khỏi giỏ)
 * @return bool - True nếu thành công
 */
function updateCartQuantity($product_id, $quantity) {
    global $conn;
    
    $quantity = (int)$quantity;
    
    if (!isLoggedIn()) {
        // Chưa đăng nhập -> Cập nhật session
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
        return true;
    }
    
    // Đã đăng nhập -> Cập nhật database
    $user_id = $_SESSION['user_id'];
    
    if ($quantity <= 0) {
        // Xóa sản phẩm khỏi giỏ
        $sql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $product_id);
    } else {
        // Cập nhật số lượng
        $sql = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $quantity, $user_id, $product_id);
    }
    
    return $stmt->execute();
}

/**
 * Xóa sản phẩm khỏi giỏ hàng
 * 
 * @param int $product_id - ID sản phẩm cần xóa
 * @return bool - True nếu thành công
 */
function removeFromCart($product_id) {
    global $conn;
    
    if (!isLoggedIn()) {
        unset($_SESSION['cart'][$product_id]);
        return true;
    }
    
    $user_id = $_SESSION['user_id'];
    $sql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    
    return $stmt->execute();
}

/**
 * Tính tổng tiền giỏ hàng
 * 
 * @return float - Tổng tiền
 */
function getCartTotal() {
    $cart = getCart();
    $total = 0;
    
    foreach ($cart as $item) {
        $product = getProductById($item['product_id']);
        if ($product) {
            // Ưu tiên giá sale nếu có
            $price = $product['sale_price'] ?? $product['price'];
            $total += $price * $item['quantity'];
        }
    }
    
    return $total;
}

/**
 * Xóa toàn bộ giỏ hàng
 * 
 * @return bool - True nếu thành công
 */
function clearCart() {
    global $conn;
    
    if (!isLoggedIn()) {
        unset($_SESSION['cart']);
        return true;
    }
    
    $user_id = $_SESSION['user_id'];
    $sql = "DELETE FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    
    return $stmt->execute();
}

// ===== QUẢN LÝ ĐƠN HÀNG =====

/**
 * Tạo mã đơn hàng duy nhất
 * 
 * @return string - Mã đơn hàng (VD: XO20241109153045123)
 */
function generateOrderNumber() {
    return 'XO' . date('YmdHis') . rand(100, 999);
}

// ===== VALIDATE =====

/**
 * Kiểm tra email hợp lệ
 * 
 * @param string $email - Email cần kiểm tra
 * @return bool - True nếu hợp lệ
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Kiểm tra số điện thoại hợp lệ (Việt Nam)
 * 
 * @param string $phone - Số điện thoại
 * @return bool - True nếu hợp lệ
 */
function isValidPhone($phone) {
    // Loại bỏ ký tự không phải số
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Kiểm tra độ dài (10-11 số)
    return strlen($phone) >= 10 && strlen($phone) <= 11;
}