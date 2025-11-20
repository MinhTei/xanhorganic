<?php
/**
 * ADMIN/ORDERS.PHP - Quản lý đơn hàng
 * 
 * Chức năng:
 * - Hiển thị danh sách đơn hàng
 * - Lọc theo trạng thái
 * - Tìm kiếm đơn hàng
 * - Xem chi tiết đơn hàng
 * - Cập nhật trạng thái
 */

require_once '../includes/config.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    redirect('login.php');
}

// Lấy tham số lọc
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Xây dựng query
$where = [];
$params = [];
$types = "";

if ($status_filter) {
    $where[] = "o.order_status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($search) {
    $where[] = "(o.order_number LIKE ? OR o.full_name LIKE ? OR o.phone LIKE ? OR o.email LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ssss";
}

$where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";

// Phân trang
$per_page = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// Đếm tổng đơn hàng
$count_sql = "SELECT COUNT(*) as total FROM orders o $where_clause";
$count_stmt = $conn->prepare($count_sql);
if ($params) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);

// Lấy danh sách đơn hàng
$sql = "SELECT o.*, u.full_name as customer_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        $where_clause
        ORDER BY o.created_at DESC 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$params[] = $per_page;
$params[] = $offset;
$types .= "ii";
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

require_once '../includes/header.php';
?>

<div class="container">
    <h1><i class="fas fa-shopping-cart"></i> Quản lý Đơn hàng</h1>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>
        
        <div style="color: #666;">
            Tìm thấy <strong><?php echo number_format($total); ?></strong> đơn hàng
        </div>
    </div>
</div>

<!-- Bộ lọc -->
<div class="container">
    <form method="GET" action="" style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 15px; align-items: end;">
            <!-- Tìm kiếm -->
            <div>
                <label style="display: block; margin-bottom: 5px; color: #666;">Tìm kiếm</label>
                <input type="text" 
                       name="search" 
                       placeholder="Mã đơn, tên khách hàng, SĐT, email..." 
                       value="<?php echo safe_html($search); ?>"
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            
            <!-- Lọc theo trạng thái -->
            <div>
                <label style="display: block; margin-bottom: 5px; color: #666;">Trạng thái</label>
                <select name="status" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="">Tất cả</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                    <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                    <option value="shipping" <?php echo $status_filter == 'shipping' ? 'selected' : ''; ?>>Đang giao</option>
                    <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                    <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                </select>
            </div>
            
            <!-- Nút lọc -->
            <div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-filter"></i> Lọc
                </button>
            </div>
        </div>
        
        <?php if ($search || $status_filter): ?>
        <div style="margin-top: 15px;">
            <a href="orders.php" style="color: #e74c3c; font-size: 14px;">
                <i class="fas fa-times"></i> Xóa bộ lọc
            </a>
        </div>
        <?php endif; ?>
    </form>
</div>

<!-- Bảng đơn hàng -->
<div class="container">
    <?php if ($result->num_rows > 0): ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th style="width: 150px;">Mã đơn hàng</th>
                <th>Khách hàng</th>
                <th style="width: 120px;">Ngày đặt</th>
                <th style="width: 120px;">Tổng tiền</th>
                <th style="width: 120px;">Thanh toán</th>
                <th style="width: 120px;">Trạng thái</th>
                <th style="width: 180px;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = $result->fetch_assoc()): ?>
            <tr>
                <td>
                    <strong style="color: #2d5016;">
                        <?php echo safe_html($order['order_number']); ?>
                    </strong>
                </td>
                <td>
                    <div>
                        <strong><?php echo safe_html($order['customer_name'] ?? $order['full_name']); ?></strong><br>
                        <small style="color: #666;">
                            <i class="fas fa-phone"></i> <?php echo safe_html($order['phone']); ?>
                        </small>
                    </div>
                </td>
                <td>
                    <?php echo date('d/m/Y', strtotime($order['created_at'])); ?><br>
                    <small style="color: #666;"><?php echo date('H:i', strtotime($order['created_at'])); ?></small>
                </td>
                <td>
                    <strong style="color: #e74c3c; font-size: 16px;">
                        <?php echo number_format($order['total_amount'], 0, ',', '.'); ?> ₫
                    </strong>
                </td>
                <td>
                    <?php
                    $payment_badges = [
                        'pending' => '<span style="background: #ffc107; color: white; padding: 5px 10px; border-radius: 10px; font-size: 12px;">Chưa thanh toán</span>',
                        'paid' => '<span style="background: #28a745; color: white; padding: 5px 10px; border-radius: 10px; font-size: 12px;">Đã thanh toán</span>',
                        'failed' => '<span style="background: #dc3545; color: white; padding: 5px 10px; border-radius: 10px; font-size: 12px;">Thất bại</span>'
                    ];
                    echo $payment_badges[$order['payment_status']] ?? safe_html($order['payment_status']);
                    ?>
                </td>
                <td>
                    <?php
                    $status_badges = [
                        'pending' => '<span style="background: #ffc107; color: white; padding: 5px 10px; border-radius: 10px; font-size: 12px;">Chờ xử lý</span>',
                        'processing' => '<span style="background: #17a2b8; color: white; padding: 5px 10px; border-radius: 10px; font-size: 12px;">Đang xử lý</span>',
                        'shipping' => '<span style="background: #007bff; color: white; padding: 5px 10px; border-radius: 10px; font-size: 12px;">Đang giao</span>',
                        'completed' => '<span style="background: #28a745; color: white; padding: 5px 10px; border-radius: 10px; font-size: 12px;">Hoàn thành</span>',
                        'cancelled' => '<span style="background: #dc3545; color: white; padding: 5px 10px; border-radius: 10px; font-size: 12px;">Đã hủy</span>'
                    ];
                    echo $status_badges[$order['order_status']] ?? safe_html($order['order_status']);
                    ?>
                </td>
                <td>
                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" 
                       class="btn btn-sm btn-view"
                       title="Xem chi tiết">
                        <i class="fas fa-eye"></i> Xem
                    </a>
                    
                    <?php if ($order['order_status'] != 'cancelled' && $order['order_status'] != 'completed'): ?>
                    <a href="order_delete.php?id=<?php echo $order['id']; ?>" 
                       class="btn btn-sm btn-delete"
                       onclick="return confirm('Bạn có chắc muốn xóa đơn hàng này?');"
                       title="Xóa">
                        <i class="fas fa-trash-alt"></i> Xóa
                    </a>
                    <?php endif; ?>
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
    <!-- Không có đơn hàng -->
    <div style="text-align: center; padding: 50px; background: white; border-radius: 10px;">
        <i class="fas fa-inbox" style="font-size: 80px; color: #ddd; margin-bottom: 20px;"></i>
        <h3 style="color: #666; margin-bottom: 10px;">Không tìm thấy đơn hàng</h3>
        <?php if ($search || $status_filter): ?>
        <p style="color: #999;">Thử điều chỉnh bộ lọc hoặc <a href="orders.php">xem tất cả đơn hàng</a></p>
        <?php else: ?>
        <p style="color: #999;">Chưa có đơn hàng nào</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>