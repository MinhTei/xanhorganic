<?php
/**
 * ADMIN/CATEGORIES.PHP - Quản lý danh mục sản phẩm
 * 
 * Chức năng:
 * - Hiển thị danh sách danh mục
 * - Thêm, sửa, xóa danh mục
 * - Hiển thị số lượng sản phẩm trong mỗi danh mục
 */

require_once '../includes/config.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    redirect('login.php');
}

// Lấy danh sách danh mục kèm số lượng sản phẩm
$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count 
        FROM categories c 
        ORDER BY c.id DESC";
$result = $conn->query($sql);

require_once '../includes/header.php';
?>

<div class="container">
    <h1><i class="fas fa-tags"></i> Quản lý Danh mục</h1>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <a href="category_add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm danh mục mới
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>
        
        <div style="color: #666;">
            Tổng: <strong><?php echo $result ? $result->num_rows : 0; ?></strong> danh mục
        </div>
    </div>
</div>

<div class="container">
    <?php if ($result && $result->num_rows > 0): ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th style="width: 60px;">ID</th>
                <th style="width: 100px;">Ảnh</th>
                <th>Tên danh mục</th>
                <th>Mô tả</th>
                <th style="width: 120px;">Số sản phẩm</th>
                <th style="width: 100px;">Trạng thái</th>
                <th style="width: 180px;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($cat = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $cat['id']; ?></td>
                <td>
                    <?php
                    // Use helper to resolve category image (handles both DB formats)
                    $image_url = getCategoryImageUrl($cat);
                    ?>
                    <img src="<?php echo $image_url; ?>" 
                        alt="<?php echo safe_html($cat['name']); ?>"
                        style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;">
                </td>
                <td>
                    <strong><?php echo safe_html($cat['name']); ?></strong>
                </td>
                <td><?php echo safe_html($cat['description']); ?></td>
                <td style="text-align: center;">
                    <?php if ($cat['product_count'] > 0): ?>
                        <a href="<?php echo SITE_URL; ?>/admin/products.php?category=<?php echo $cat['id']; ?>" 
                           style="color: #2d5016; font-weight: 600;">
                            <?php echo $cat['product_count']; ?> sản phẩm
                        </a>
                    <?php else: ?>
                        <span style="color: #999;">0 sản phẩm</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($cat['status'] == 'active'): ?>
                        <span style="background: #28a745; color: white; padding: 5px 10px; border-radius: 10px; font-size: 12px;">
                            Hoạt động
                        </span>
                    <?php else: ?>
                        <span style="background: #6c757d; color: white; padding: 5px 10px; border-radius: 10px; font-size: 12px;">
                            Tạm dừng
                        </span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="category_edit.php?id=<?php echo $cat['id']; ?>" 
                       class="btn btn-sm btn-edit"
                       title="Chỉnh sửa">
                        <i class="fas fa-edit"></i> Sửa
                    </a>
                    
                    <?php if ($cat['product_count'] == 0): ?>
                        <a href="category_delete.php?id=<?php echo $cat['id']; ?>" 
                           class="btn btn-sm btn-delete"
                           onclick="return confirm('Bạn có chắc muốn xóa danh mục này?');"
                           title="Xóa">
                            <i class="fas fa-trash-alt"></i> Xóa
                        </a>
                    <?php else: ?>
                        <button class="btn btn-sm btn-delete" 
                                disabled
                                title="Không thể xóa danh mục có sản phẩm"
                                style="opacity: 0.5; cursor: not-allowed;">
                            <i class="fas fa-trash-alt"></i> Xóa
                        </button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <?php else: ?>
    <!-- Chưa có danh mục -->
    <div style="text-align: center; padding: 50px; background: white; border-radius: 10px;">
        <i class="fas fa-tags" style="font-size: 80px; color: #ddd; margin-bottom: 20px;"></i>
        <h3 style="color: #666; margin-bottom: 10px;">Chưa có danh mục nào</h3>
        <p style="color: #999;">Hãy thêm danh mục đầu tiên cho sản phẩm</p>
        <a href="category_add.php" class="btn btn-primary" style="margin-top: 20px;">
            <i class="fas fa-plus"></i> Thêm danh mục mới
        </a>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>