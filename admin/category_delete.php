<?php
require_once '../includes/config.php';

if (!isAdmin()) {
    redirect('login.php');
}

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($category_id <= 0) {
    redirect('admin/categories.php');
}

// Lấy thông tin danh mục
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();

if (!$category) {
    redirect('admin/categories.php');
}

// Kiểm tra số lượng sản phẩm
$check_products = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
$check_products->bind_param("i", $category_id);
$check_products->execute();
$product_count = $check_products->get_result()->fetch_assoc()['count'];

// Không cho xóa nếu có sản phẩm
if ($product_count > 0) {
    $_SESSION['error_message'] = 'Không thể xóa danh mục này vì còn ' . $product_count . ' sản phẩm!';
    redirect('admin/categories.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    // Xóa ảnh nếu có
    if (!empty($category['image'])) {
        $image_path = __DIR__ . '/../assets/' . $category['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    // Xóa danh mục
    $delete_stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $delete_stmt->bind_param("i", $category_id);
    
    if ($delete_stmt->execute()) {
        $_SESSION['success_message'] = 'Đã xóa danh mục thành công!';
        redirect('admin/categories.php');
    } else {
        $error = 'Có lỗi xảy ra khi xóa danh mục!';
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <h1 style="color: #dc3545; margin-bottom: 30px;">
        <i class="fas fa-trash-alt"></i> Xóa Danh Mục
    </h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div style="background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto;">
        
        <div style="display: flex; gap: 30px; align-items: start;">
            <!-- Ảnh danh mục -->
            <div style="flex-shrink: 0;">
                <?php
                $image_url = SITE_URL . '/assets/' . safe_html($category['image'] ?? '');
                $image_path = __DIR__ . '/../assets/' . ($category['image'] ?? '');
                
                if (empty($category['image']) || !file_exists($image_path)) {
                    $image_url = 'https://via.placeholder.com/200x200?text=' . urlencode($category['name']);
                }
                ?>
                <img src="<?php echo $image_url; ?>" 
                     alt="<?php echo safe_html($category['name']); ?>"
                     style="width: 200px; height: 200px; object-fit: cover; border-radius: 10px; border: 2px solid #ddd;">
            </div>

            <!-- Thông tin danh mục -->
            <div style="flex: 1;">
                <h2 style="color: #2d5016; margin-bottom: 20px;">
                    <?php echo safe_html($category['name']); ?>
                </h2>
                
                <div style="display: grid; gap: 15px;">
                    <div>
                        <strong>Mô tả:</strong> 
                        <p style="margin: 5px 0 0 0; color: #666;">
                            <?php echo safe_html($category['description']); ?>
                        </p>
                    </div>
                    
                    <div>
                        <strong>Số sản phẩm:</strong> 
                        <span style="color: #28a745; font-size: 18px; font-weight: 600;">
                            <?php echo $product_count; ?> sản phẩm
                        </span>
                    </div>
                    
                    <div>
                        <strong>Trạng thái:</strong> 
                        <?php if ($category['status'] == 'active'): ?>
                            <span style="background: #28a745; color: white; padding: 5px 10px; border-radius: 10px; font-size: 12px;">
                                Hoạt động
                            </span>
                        <?php else: ?>
                            <span style="background: #6c757d; color: white; padding: 5px 10px; border-radius: 10px; font-size: 12px;">
                                Tạm dừng
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Xác nhận xóa -->
        <div style="margin-top: 40px; padding-top: 30px; border-top: 2px solid #eee;">
            <h3 style="color: #dc3545; margin-bottom: 20px;">
                <i class="fas fa-question-circle"></i> Bạn có chắc chắn muốn xóa danh mục này?
            </h3>
            
            <p style="color: #666; margin-bottom: 30px;">
                Hành động này không thể hoàn tác. Danh mục sẽ bị xóa vĩnh viễn khỏi hệ thống.
            </p>

            <form method="POST" style="display: flex; gap: 15px;">
                <button type="submit" name="confirm_delete" 
                        class="btn btn-delete" 
                        style="background: #dc3545; padding: 15px 30px;">
                    <i class="fas fa-trash-alt"></i> Xác Nhận Xóa
                </button>
                
                <a href="<?php echo SITE_URL; ?>/admin/categories.php" 
                   class="btn btn-secondary" 
                   style="padding: 15px 30px;">
                    <i class="fas fa-times"></i> Hủy Bỏ
                </a>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>