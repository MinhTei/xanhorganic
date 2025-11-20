<?php
/**
 * ADMIN/PRODUCTS.PHP - Trang quản lý danh sách sản phẩm
 * 
 * Chức năng:
 * - Hiển thị danh sách sản phẩm
 * - Tìm kiếm và lọc sản phẩm
 * - Thêm, sửa, xóa sản phẩm
 * - Phân trang
 */

require_once '../includes/config.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    redirect('login.php');
}

require_once '../includes/header.php';

// ===== XỬ LÝ TÌM KIẾM VÀ LỌC =====
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Xây dựng query
$where = [];
$params = [];
$types = "";

if ($search) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

if ($category_filter > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $category_filter;
    $types .= "i";
}

if ($status_filter) {
    $where[] = "p.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";

// Phân trang
$per_page = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// Đếm tổng sản phẩm
$count_sql = "SELECT COUNT(*) as total FROM products p $where_clause";
$count_stmt = $conn->prepare($count_sql);
if ($params) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);

// Lấy danh sách sản phẩm
$sql = "SELECT p.*, c.name AS category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        $where_clause
        ORDER BY p.id DESC 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$params[] = $per_page;
$params[] = $offset;
$types .= "ii";
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Lấy danh sách categories cho filter
$categories = getCategories();
?>

<!-- Page Header -->
<div class="container">
    <h1><i class="fas fa-box"></i> Quản lý Sản phẩm</h1>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <a href="product_add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm sản phẩm mới
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>
        
        <div style="color: #666;">
            Tìm thấy <strong><?php echo number_format($total); ?></strong> sản phẩm
        </div>
    </div>
</div>

<!-- Bộ lọc và tìm kiếm -->
<div class="container">
    <form method="GET" action="" style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 15px; align-items: end;">
            <!-- Tìm kiếm -->
            <div>
                <label style="display: block; margin-bottom: 5px; color: #666;">Tìm kiếm</label>
                <input type="text" 
                       name="search" 
                       placeholder="Tên sản phẩm, mô tả..." 
                       value="<?php echo htmlspecialchars($search); ?>"
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            
            <!-- Lọc theo danh mục -->
            <div>
                <label style="display: block; margin-bottom: 5px; color: #666;">Danh mục</label>
                <select name="category" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="">Tất cả</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Lọc theo trạng thái -->
            <div>
                <label style="display: block; margin-bottom: 5px; color: #666;">Trạng thái</label>
                <select name="status" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="">Tất cả</option>
                    <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                    <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Không hoạt động</option>
                </select>
            </div>
            
            <!-- Nút lọc -->
            <div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-filter"></i> Lọc
                </button>
            </div>
        </div>
        
        <?php if ($search || $category_filter || $status_filter): ?>
        <div style="margin-top: 15px;">
            <a href="products.php" style="color: #e74c3c; font-size: 14px;">
                <i class="fas fa-times"></i> Xóa bộ lọc
            </a>
        </div>
        <?php endif; ?>
    </form>
</div>

<!-- Bảng sản phẩm -->
<div class="container">
    <?php if ($result->num_rows > 0): ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th style="width: 60px;">ID</th>
                <th style="width: 80px;">Ảnh</th>
                <th>Tên sản phẩm</th>
                <th style="width: 150px;">Danh mục</th>
                <th style="width: 120px;">Giá</th>
                <th style="width: 120px;">Giảm giá</th>
                <th style="width: 80px;">Tồn kho</th>
                <th style="width: 100px;">Trạng thái</th>
                <th style="width: 180px;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td>
                    <?php
                    // Use helper to resolve product image URL (tolerant to DB format)
                    $image_url = getProductImageUrl($row);
                    ?>
                    <img src="<?php echo $image_url; ?>" 
                         alt="<?php echo htmlspecialchars($row['name']); ?>"
                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                </td>
                <td>
                    <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                    <?php if ($row['featured']): ?>
                        <span style="background: #ffc107; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; margin-left: 5px;">
                            <i class="fas fa-star"></i> Nổi bật
                        </span>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                <td><?php echo number_format($row['price'], 0, ',', '.'); ?> ₫</td>
                <td>
                    <?php if ($row['sale_price']): ?>
                        <strong style="color: #e74c3c;">
                            <?php echo number_format($row['sale_price'], 0, ',', '.'); ?> ₫
                        </strong>
                    <?php else: ?>
                        <span style="color: #999;">-</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($row['stock'] > 10): ?>
                        <span style="color: #28a745;"><?php echo $row['stock']; ?></span>
                    <?php elseif ($row['stock'] > 0): ?>
                        <span style="color: #ffc107;"><?php echo $row['stock']; ?></span>
                    <?php else: ?>
                        <span style="color: #dc3545;">Hết hàng</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($row['status'] == 'active'): ?>
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
                    <a href="product_edit.php?id=<?php echo $row['id']; ?>" 
                       class="btn btn-sm btn-edit"
                       title="Chỉnh sửa">
                        <i class="fas fa-edit"></i> Sửa
                    </a>
                    <a href="product_delete.php?id=<?php echo $row['id']; ?>" 
                       class="btn btn-sm btn-delete" 
                       onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?');"
                       title="Xóa">
                        <i class="fas fa-trash-alt"></i> Xóa
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <!-- Phân trang -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                <i class="fas fa-chevron-left"></i> Trước
            </a>
        <?php endif; ?>

        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <?php if ($i == $page): ?>
                <span class="active"><?php echo $i; ?></span>
            <?php else: ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                    <?php echo $i; ?>
                </a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                Sau <i class="fas fa-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <?php else: ?>
    <!-- Không có sản phẩm -->
    <div style="text-align: center; padding: 50px; background: white; border-radius: 10px;">
        <i class="fas fa-box-open" style="font-size: 80px; color: #ddd; margin-bottom: 20px;"></i>
        <h3 style="color: #666; margin-bottom: 10px;">Không tìm thấy sản phẩm</h3>
        <?php if ($search || $category_filter || $status_filter): ?>
        <p style="color: #999;">Thử điều chỉnh bộ lọc hoặc <a href="products.php">xem tất cả sản phẩm</a></p>
        <?php else: ?>
        <p style="color: #999;">Hãy thêm sản phẩm đầu tiên</p>
        <a href="product_add.php" class="btn btn-primary" style="margin-top: 20px;">
            <i class="fas fa-plus"></i> Thêm sản phẩm mới
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>