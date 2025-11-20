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
 * Trả về URL ảnh cho sản phẩm: ưu tiên ảnh sản phẩm, nếu không có thì lấy ảnh của category,
 * nếu vẫn không có thì trả về placeholder.
 *
 * @param array|int $productOrId - mảng product (kèm khóa 'image' và 'category_id') hoặc product id
 * @return string - URL tới ảnh
 */
function getProductImageUrl($productOrId) {
    global $conn;

    // Nếu truyền id, lấy product
    if (!is_array($productOrId)) {
        $product = getProductById((int)$productOrId);
    } else {
        $product = $productOrId;
    }

    $placeholder = 'https://via.placeholder.com/300x250?text=' . urlencode($product['name'] ?? 'Product');

    if (empty($product)) {
        return $placeholder;
    }

    // Check product image
    $prodImage = $product['image'] ?? '';
    // If image is present, try multiple resolution strategies to be tolerant
    if (!empty($prodImage)) {
        // If it's a remote URL, return it directly
        if (preg_match('#^https?://#i', $prodImage)) {
            return $prodImage;
        }

        // 1) If DB stored a path relative to assets (e.g. 'images/products/foo.jpg'), try that first
        if (strpos($prodImage, '/') !== false) {
            $rawPath = __DIR__ . '/../assets/' . $prodImage;
            if (file_exists($rawPath)) {
                // Encode each segment separately to preserve slashes
                $parts = explode('/', str_replace('\\', '/', $prodImage));
                $parts = array_map('rawurlencode', $parts);
                return rtrim(SITE_URL, '/') . '/assets/' . implode('/', $parts);
            }
            // If not found, fall back to basename below
            $prodImage = basename($prodImage);
        }

        // 2) Try treating it as a filename stored under assets/images/products/
        $prodPath = __DIR__ . '/../assets/images/products/' . $prodImage;
        if (file_exists($prodPath)) {
            return rtrim(SITE_URL, '/') . '/assets/images/products/' . rawurlencode($prodImage);
        }

        // 3) fallback to Unsplash by product name
        $placeholder_remote = 'https://source.unsplash.com/600x400/?' . urlencode($product['name'] ?? 'product');
        return $placeholder_remote;
    }

    // Fallback: try category image
    $catId = $product['category_id'] ?? null;
    if ($catId) {
        $cat = getCategoryById((int)$catId);
        if ($cat && !empty($cat['image'])) {
            // if category image is a remote URL, return it
            if (preg_match('#^https?://#i', $cat['image'])) {
                return $cat['image'];
            }
            $catPath = __DIR__ . '/../assets/images/categories/' . $cat['image'];
            if (file_exists($catPath)) {
                return rtrim(SITE_URL, '/') . '/assets/images/categories/' . rawurlencode($cat['image']);
            }
            // otherwise try a remote unsplash match using category name
            $cat_remote = 'https://source.unsplash.com/600x400/?' . urlencode($cat['name'] ?? 'category');
            return $cat_remote;
        }
    }

    // Final fallback
    return $placeholder;
}

/**
 * Trả về URL ảnh cho category: nếu có ảnh trả về assets/images/categories/<file>,
 * nếu không có thì placeholder nhỏ.
 *
 * @param array|int $categoryOrId
 * @return string
 */
function getCategoryImageUrl($categoryOrId) {
    if (!is_array($categoryOrId)) {
        $cat = getCategoryById((int)$categoryOrId);
    } else {
        $cat = $categoryOrId;
    }

    $placeholder = 'https://via.placeholder.com/300x250?text=' . urlencode($cat['name'] ?? 'Category');
    if (empty($cat)) return $placeholder;

    $img = $cat['image'] ?? '';
    if (!empty($img)) {
        if (preg_match('#^https?://#i', $img)) {
            return $img;
        }

        // 1) If DB stored a path relative to assets (e.g. 'images/categories/foo.jpg'), try that first
        if (strpos($img, '/') !== false) {
            $rawPath = __DIR__ . '/../assets/' . $img;
            if (file_exists($rawPath)) {
                $parts = explode('/', str_replace('\\', '/', $img));
                $parts = array_map('rawurlencode', $parts);
                return rtrim(SITE_URL, '/') . '/assets/' . implode('/', $parts);
            }
            // If not found, fall back to basename
            $img = basename($img);
        }

        // 2) Try filename under assets/images/categories/
        $path = __DIR__ . '/../assets/images/categories/' . $img;
        if (file_exists($path)) {
            return rtrim(SITE_URL, '/') . '/assets/images/categories/' . rawurlencode($img);
        }

        // 3) fallback to remote Unsplash
        return 'https://source.unsplash.com/600x400/?' . urlencode($cat['name'] ?? 'category');
    }

    // final fallback to unsplash by category name
    return 'https://source.unsplash.com/600x400/?' . urlencode($cat['name'] ?? 'category');
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
/**
 * THÊM VÀO CUỐI FILE includes/functions.php
 */

// ===== QUẢN LÝ WISHLIST =====

/**
 * Kiểm tra sản phẩm có trong wishlist không
 */
function isInWishlist($product_id) {
    global $conn;
    
    if (!isLoggedIn()) {
        return false;
    }
    
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    
    return $stmt->get_result()->num_rows > 0;
}

/**
 * Thêm sản phẩm vào wishlist
 */
function addToWishlist($product_id) {
    global $conn;
    
    if (!isLoggedIn()) {
        return false;
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Kiểm tra đã có chưa
    if (isInWishlist($product_id)) {
        return true;
    }
    
    $sql = "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    
    return $stmt->execute();
}

/**
 * Xóa sản phẩm khỏi wishlist
 */
function removeFromWishlist($product_id) {
    global $conn;
    
    if (!isLoggedIn()) {
        return false;
    }
    
    $user_id = $_SESSION['user_id'];
    $sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    
    return $stmt->execute();
}

/**
 * Lấy danh sách wishlist
 */
function getWishlist() {
    global $conn;
    
    if (!isLoggedIn()) {
        return [];
    }
    
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT w.*, p.name, p.price, p.sale_price, p.image, p.unit, p.stock, c.name as category_name
            FROM wishlist w
            INNER JOIN products p ON w.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $wishlist = [];
    while ($row = $result->fetch_assoc()) {
        $wishlist[] = $row;
    }
    
    return $wishlist;
}

/**
 * Đếm số sản phẩm trong wishlist
 */
function getWishlistCount() {
    global $conn;
    
    if (!isLoggedIn()) {
        return 0;
    }
    
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    return (int)$stmt->get_result()->fetch_assoc()['count'];
}

// ===== QUẢN LÝ VOUCHERS =====

/**
 * Kiểm tra và lấy thông tin voucher
 */
function getVoucherByCode($code) {
    global $conn;
    
    $sql = "SELECT * FROM vouchers 
            WHERE code = ? 
            AND status = 'active' 
            AND valid_from <= NOW() 
            AND valid_to >= NOW()
            AND (usage_limit IS NULL OR used_count < usage_limit)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $code);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Kiểm tra user có thể dùng voucher không
 */
function canUseVoucher($voucher_id, $user_id) {
    global $conn;
    
    $sql = "SELECT v.*, 
            (SELECT COUNT(*) FROM voucher_usage WHERE voucher_id = ? AND user_id = ?) as user_usage
            FROM vouchers v 
            WHERE v.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $voucher_id, $user_id, $voucher_id);
    $stmt->execute();
    $voucher = $stmt->get_result()->fetch_assoc();
    
    if (!$voucher) {
        return false;
    }
    
    // Kiểm tra số lần dùng của user
    if ($voucher['user_usage'] >= $voucher['user_limit']) {
        return false;
    }
    
    // Kiểm tra voucher dành cho khách mới
    if ($voucher['voucher_type'] == 'new_customer') {
        $order_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id = $user_id")->fetch_assoc()['count'];
        if ($order_count > 0) {
            return false;
        }
    }
    
    // Kiểm tra voucher dành cho khách thân thiết
    if ($voucher['voucher_type'] == 'loyal') {
        $order_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id = $user_id")->fetch_assoc()['count'];
        if ($order_count < 3) {
            return false;
        }
    }
    
    return true;
}

/**
 * Tính số tiền giảm từ voucher
 */
function calculateVoucherDiscount($voucher, $order_amount) {
    if ($order_amount < $voucher['min_order_amount']) {
        return 0;
    }
    
    if ($voucher['discount_type'] == 'percent') {
        $discount = $order_amount * ($voucher['discount_value'] / 100);
        
        // Giới hạn số tiền giảm tối đa
        if ($voucher['max_discount'] && $discount > $voucher['max_discount']) {
            $discount = $voucher['max_discount'];
        }
    } else {
        $discount = $voucher['discount_value'];
    }
    
    return min($discount, $order_amount);
}

/**
 * Áp dụng voucher cho đơn hàng
 */
function applyVoucher($voucher_id, $order_id, $discount_amount) {
    global $conn;
    
    if (!isLoggedIn()) {
        return false;
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Lưu lịch sử sử dụng
    $sql = "INSERT INTO voucher_usage (voucher_id, user_id, order_id, discount_amount) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiid", $voucher_id, $user_id, $order_id, $discount_amount);
    
    if ($stmt->execute()) {
        // Tăng used_count
        $conn->query("UPDATE vouchers SET used_count = used_count + 1 WHERE id = $voucher_id");
        return true;
    }
    
    return false;
}

/**
 * Lấy voucher phù hợp cho user
 */
function getAvailableVouchers($user_id) {
    global $conn;
    
    // Đếm số đơn hàng
    $order_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id = $user_id")->fetch_assoc()['count'];
    
    $sql = "SELECT v.*, 
            (SELECT COUNT(*) FROM voucher_usage WHERE voucher_id = v.id AND user_id = ?) as user_usage
            FROM vouchers v 
            WHERE v.status = 'active' 
            AND v.valid_from <= NOW() 
            AND v.valid_to >= NOW()
            AND (v.usage_limit IS NULL OR v.used_count < v.usage_limit)
            HAVING user_usage < v.user_limit";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $vouchers = [];
    while ($row = $result->fetch_assoc()) {
        // Lọc theo điều kiện voucher_type
        if ($row['voucher_type'] == 'new_customer' && $order_count > 0) {
            continue;
        }
        if ($row['voucher_type'] == 'loyal' && $order_count < 3) {
            continue;
        }
        
        $vouchers[] = $row;
    }
    
    return $vouchers;
}

// ===== QUẢN LÝ POLICIES =====

/**
 * Lấy chính sách theo loại
 */
function getPolicyByType($type) {
    global $conn;
    
    $sql = "SELECT * FROM policies WHERE type = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $type);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Lấy tất cả chính sách
 */
function getAllPolicies() {
    global $conn;
    
    $sql = "SELECT * FROM policies WHERE status = 'active' ORDER BY type";
    $result = $conn->query($sql);
    
    $policies = [];
    while ($row = $result->fetch_assoc()) {
        $policies[] = $row;
    }
    
    return $policies;
}

// ===== QUẢN LÝ PAYMENT QR CODES =====

/**
 * Lấy thông tin thanh toán
 */
function getPaymentInfo($method) {
    global $conn;
    
    $sql = "SELECT * FROM payment_qrcodes WHERE payment_method = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $method);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

// ===== QUẢN LÝ LỊCH SỬ MUA HÀNG =====

/**
 * Lấy lịch sử mua hàng của user
 */
function getOrderHistory($user_id, $limit = 10) {
    global $conn;
    
    $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    return $orders;
}

/**
 * Đếm tổng số đơn hàng của user
 */
function getTotalOrders($user_id) {
    global $conn;
    
    $sql = "SELECT COUNT(*) as count FROM orders WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    return (int)$stmt->get_result()->fetch_assoc()['count'];
}

/**
 * Tính tổng chi tiêu của user
 */
function getTotalSpent($user_id) {
    global $conn;
    
    $sql = "SELECT SUM(total_amount) as total FROM orders WHERE user_id = ? AND order_status = 'completed'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    return (float)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
}
?>