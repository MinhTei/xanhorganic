<?php
// File chứa các hàm tiện ích

// Lấy thông tin user hiện tại
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

// Lấy danh sách categories
function getCategories() {
    global $conn;
    
    $sql = "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC";
    $result = $conn->query($sql);
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    return $categories;
}

// Lấy sản phẩm theo ID
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

// Lấy sản phẩm theo category
function getProductsByCategory($category_id, $limit = null) {
    global $conn;
    
    $sql = "SELECT * FROM products WHERE category_id = ? AND status = 'active' ORDER BY created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

// Lấy sản phẩm nổi bật
function getFeaturedProducts($limit = 8) {
    global $conn;
    
    $sql = "SELECT * FROM products WHERE featured = 1 AND status = 'active' ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

// Lấy sản phẩm mới nhất
function getLatestProducts($limit = 8) {
    global $conn;
    
    $sql = "SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

// Đếm số sản phẩm trong giỏ hàng
function getCartCount() {
    global $conn;
    
    if (!isLoggedIn()) {
        return isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
    }
    
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT COUNT(*) as count FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'];
}

// Lấy giỏ hàng
function getCart() {
    global $conn;
    
    if (!isLoggedIn()) {
        return isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    }
    
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT c.*, p.name, p.price, p.sale_price, p.image, p.unit 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $cart = [];
    while ($row = $result->fetch_assoc()) {
        $cart[] = $row;
    }
    
    return $cart;
}

// Thêm sản phẩm vào giỏ hàng
function addToCart($product_id, $quantity = 1) {
    global $conn;
    
    if (!isLoggedIn()) {
        // Lưu vào session nếu chưa đăng nhập
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
    
    $user_id = $_SESSION['user_id'];
    
    // Kiểm tra sản phẩm đã có trong giỏ chưa
    $sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Cập nhật số lượng
        $sql = "UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $quantity, $user_id, $product_id);
    } else {
        // Thêm mới
        $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
    }
    
    return $stmt->execute();
}

// Cập nhật số lượng sản phẩm trong giỏ
function updateCartQuantity($product_id, $quantity) {
    global $conn;
    
    if (!isLoggedIn()) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
        return true;
    }
    
    $user_id = $_SESSION['user_id'];
    
    if ($quantity <= 0) {
        $sql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $product_id);
    } else {
        $sql = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $quantity, $user_id, $product_id);
    }
    
    return $stmt->execute();
}

// Xóa sản phẩm khỏi giỏ hàng
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

// Tính tổng tiền giỏ hàng
function getCartTotal() {
    $cart = getCart();
    $total = 0;
    
    foreach ($cart as $item) {
        $price = $item['sale_price'] ?? $item['price'];
        $total += $price * $item['quantity'];
    }
    
    return $total;
}

// Xóa toàn bộ giỏ hàng
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

// Tạo mã đơn hàng
function generateOrderNumber() {
    return 'XO' . date('YmdHis') . rand(100, 999);
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate phone
function isValidPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return strlen($phone) >= 10 && strlen($phone) <= 11;
}
?>