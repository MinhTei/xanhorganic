<?php
require_once '../includes/config.php';

if (!isAdmin()) {
    redirect('login.php');
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    redirect('admin/products.php');
}

// Lấy thông tin sản phẩm
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    redirect('admin/products.php');
}

// Kiểm tra xem sản phẩm có trong đơn hàng nào không
$check_orders = $conn->prepare("SELECT COUNT(*) as count FROM order_items WHERE product_id = ?");
$check_orders->bind_param("i", $product_id);
$check_orders->execute();
$order_count = $check_orders->get_result()->fetch_assoc()['count'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    // Xóa ảnh nếu có
    if (!empty($product['image'])) {
        $image_path = __DIR__ . '/../assets/images/products/' . $product['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    // Xóa sản phẩm khỏi giỏ hàng
    $conn->query("DELETE FROM cart WHERE product_id = $product_id");
    
    // Xóa sản phẩm
    $delete_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $delete_stmt->bind_param("i", $product_id);
    
    if ($delete_stmt->execute()) {
        $_SESSION['success_message'] = 'Đã xóa sản phẩm thành công!';
        redirect('admin/products.php');
    } else {
        $error = 'Có lỗi xảy ra khi xóa sản phẩm!';
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <h1 style="color: #dc3545; margin-bottom: 30px;">
        <i class="fas fa-trash-alt"></i> Xóa Sản Phẩm
    </h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div style="background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto;">
        
        <?php if ($order_count > 0): ?>
            <!-- Cảnh báo: Sản phẩm đã có trong đơn hàng -->
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin-bottom: 30px; border-radius: 5px;">
                <h3 style="color: #856404; margin-bottom: 10px;">
                    <i class="fas fa-exclamation-triangle"></i> Cảnh báo!
                </h3>
                <p style="color: #856404; margin: 0;">
                    Sản phẩm này đã có trong <strong><?php echo $order_count; ?> đơn hàng</strong>. 
                    Việc xóa sẽ không ảnh hưởng đến các đơn hàng đã tạo, nhưng sản phẩm sẽ không còn hiển thị trên website.
                </p>
            </div>
        <?php endif; ?>

        <div style="display: flex; gap: 30px; align-items: start;">
            <!-- Ảnh sản phẩm -->
            <div style="flex-shrink: 0;">
                <?php
                $image_url = SITE_URL . '/assets/images/products/' . safe_html($product['image'] ?? '');
                $image_path = __DIR__ . '/../assets/images/products/' . ($product['image'] ?? '');
                
                if (empty($product['image']) || !file_exists($image_path)) {
                    $image_url = 'https://via.placeholder.com/200x200?text=' . urlencode($product['name']);
                }
                ?>
                <img src="<?php echo $image_url; ?>" 
                     alt="<?php echo safe_html($product['name']); ?>"
                     style="width: 200px; height: 200px; object-fit: cover; border-radius: 10px; border: 2px solid #ddd;">
            </div>

            <!-- Thông tin sản phẩm -->
            <div style="flex: 1;">
                <h2 style="color: #2d5016; margin-bottom: 20px;">
                    <?php echo safe_html($product['name']); ?>
                </h2>
                
                <div style="display: grid; gap: 15px;">
                    <div>
                        <strong>Giá:</strong> 
                        <span style="color: #e74c3c; font-size: 18px;">
                            <?php echo formatMoney($product['price']); ?>
                        </span>
                    </div>
                    
                    <?php if ($product['sale_price']): ?>
                    <div>
                        <strong>Giá khuyến mãi:</strong> 
                        <span style="color: #28a745; font-size: 18px;">
                            <?php echo formatMoney($product['sale_price']); ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <div>
                        <strong>Tồn kho:</strong> <?php echo $product['stock']; ?> <?php echo safe_html($product['unit']); ?>
                    </div>
                    
                    <div>
                        <strong>Danh mục:</strong> 
                        <?php
                        $cat = getCategoryById($product['category_id']);
                        echo safe_html($cat['name'] ?? 'N/A');
                        ?>
                    </div>
                    
                    <?php if ($product['certification']): ?>
                    <div>
                        <strong>Chứng nhận:</strong> <?php echo safe_html($product['certification']); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Xác nhận xóa -->
        <div style="margin-top: 40px; padding-top: 30px; border-top: 2px solid #eee;">
            <h3 style="color: #dc3545; margin-bottom: 20px;">
                <i class="fas fa-question-circle"></i> Bạn có chắc chắn muốn xóa sản phẩm này?
            </h3>
            
            <p style="color: #666; margin-bottom: 30px;">
                Hành động này không thể hoàn tác. Sản phẩm sẽ bị xóa vĩnh viễn khỏi hệ thống.
            </p>

            <form method="POST" style="display: flex; gap: 15px;">
                <button type="submit" name="confirm_delete" 
                        class="btn btn-delete" 
                        style="background: #dc3545; padding: 15px 30px;">
                    <i class="fas fa-trash-alt"></i> Xác Nhận Xóa
                </button>
                
                <a href="<?php echo SITE_URL; ?>/admin/products.php" 
                   class="btn btn-secondary" 
                   style="padding: 15px 30px;">
                    <i class="fas fa-times"></i> Hủy Bỏ
                </a>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>