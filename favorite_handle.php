<?php
/**
 * WISHLIST-HANDLER.PHP - AJAX Handler cho Wishlist
 * 
 * Actions:
 * - add: Thêm sản phẩm vào wishlist
 * - remove: Xóa sản phẩm khỏi wishlist
 * - count: Đếm số sản phẩm trong wishlist
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Set header JSON
header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false, 
        'message' => 'Vui lòng đăng nhập để sử dụng tính năng này!'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Xử lý GET request (count)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'count') {
        $count = getWishlistCount();
        echo json_encode([
            'success' => true,
            'count' => $count
        ]);
        exit;
    }
}

// Xử lý POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? '';
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

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

switch ($action) {
    case 'add':
        // Thêm vào wishlist
        if (addToWishlist($product_id)) {
            echo json_encode([
                'success' => true,
                'message' => 'Đã thêm "' . $product['name'] . '" vào danh sách yêu thích!',
                'count' => getWishlistCount()
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Sản phẩm đã có trong danh sách yêu thích hoặc có lỗi xảy ra!'
            ]);
        }
        break;
        
    case 'remove':
        // Xóa khỏi wishlist
        if (removeFromWishlist($product_id)) {
            echo json_encode([
                'success' => true,
                'message' => 'Đã xóa "' . $product['name'] . '" khỏi danh sách yêu thích!',
                'count' => getWishlistCount()
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa sản phẩm!'
            ]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
        break;
}