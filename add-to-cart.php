<?php
/**
 * ADD-TO-CART.PHP - AJAX Handler thêm sản phẩm vào giỏ hàng
 * 
 * Xử lý thêm sản phẩm vào giỏ hàng không reload trang
 * Trả về JSON response
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Set header JSON
header('Content-Type: application/json');

// Chỉ chấp nhận POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Lấy thông tin từ POST
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

// Validate product_id
if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không hợp lệ']);
    exit;
}

// Kiểm tra sản phẩm tồn tại
$product = getProductById($product_id);

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
    exit;
}

// Kiểm tra tồn kho
if ($product['stock'] < $quantity) {
    echo json_encode([
        'success' => false, 
        'message' => 'Sản phẩm chỉ còn ' . $product['stock'] . ' trong kho'
    ]);
    exit;
}

// Thêm vào giỏ hàng
if (addToCart($product_id, $quantity)) {
    echo json_encode([
        'success' => true,
        'message' => 'Đã thêm sản phẩm vào giỏ hàng',
        'cart_count' => getCartCount(), // Số lượng sản phẩm trong giỏ
        'product_name' => $product['name']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi thêm vào giỏ hàng']);
}