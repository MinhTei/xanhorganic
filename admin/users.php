<?php
/**
 * ADMIN/USERS.PHP - Quản lý người dùng (Cập nhật)
 * 
 * Chức năng:
 * - Hiển thị danh sách người dùng
 * - Xem thông tin chi tiết
 * - Xóa người dùng (có kiểm tra đơn hàng)
 * - Thống kê tổng quan
 */

require_once '../includes/config.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    redirect('login.php');
}

// Lấy tham số tìm kiếm và lọc
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';

// Xây dựng query
$where = [];
$params = [];
$types = "";

if ($search) {
    $where[] = "(full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

if ($role_filter) {
    $where[] = "role = ?";
    $params[] = $role_filter;
    $types .= "s";
}

$where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";

// Phân trang
$per_page = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// Đếm tổng
$count_sql = "SELECT COUNT(*) as total FROM users $where_clause";
$count_stmt = $conn->prepare($count_sql);
if ($params) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);

// Lấy danh sách users
$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,
        (SELECT SUM(total_amount) FROM orders WHERE user_id = u.id AND order_status = 'completed') as total_spent
        FROM users u 
        $where_clause
        ORDER BY u.created_at DESC 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$params[] = $per_page;
$params[] = $offset;
$types .= "ii";
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Thống kê
$total_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$total_customers = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'")->fetch_assoc()['total'];
$total_admins = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'")->fetch_assoc()['total'];

require_once '../includes/header.php';
?>

<div class="container">
    <h1><i class="fas fa-users"></i> Quản lý Người Dùng</h1>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>
        
        <div style="color: #666;">
            Tìm thấy <strong><?php echo number_format($total); ?></strong> người dùng
        </div>
    </div>
</div>

<!-- Thống kê -->
<div class="container">
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 10px; color: white; text-align: center;">
            <i class="fas fa-users" style="font-size: 40px; margin-bottom: 10px;"></i>
            <h3 style="font-size: 32px; margin-bottom: 5px;"><?php echo $total_users; ?></h3>
            <p style="margin: 0;">Tổng người dùng</p>
        </div>
        
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 25px; border-radius: 10px; color: white; text-align: center;">
            <i class="fas fa-user" style="font-size: 40px; margin-bottom: 10px;"></i>
            <h3 style="font-size: 32px; margin-bottom: 5px;"><?php echo $total_customers; ?></h3>
            <p style="margin: 0;">Khách hàng</p>
        </div>
        
        <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 25px; border-radius: 10px; color: white; text-align: center;">
            <i class="fas fa-user-shield" style="font-size: 40px; margin-bottom: 10px;"></i>
            <h3 style="font-size: 32px; margin-bottom: 5px;"><?php echo $total_admins; ?></h3>
            <p style="margin: 0;">Quản trị viên</p>
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
                       placeholder="Tên, email, số điện thoại..." 
                       value="<?php echo safe_html($search); ?>"
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            
            <!-- Lọc theo vai trò -->
            <div>
                <label style="display: block; margin-bottom: 5px; color: #666;">Vai trò</label>
                <select name="role" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="">Tất cả</option>
                    <option value="customer" <?php echo $role_filter == 'customer' ? 'selected' : ''; ?>>Khách hàng</option>
                    <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Quản trị viên</option>
                </select>
            </div>
            
            <!-- Nút lọc -->
            <div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-filter"></i> Lọc
                </button>
            </div>
        </div>
        
        <?php if ($search || $role_filter): ?>
        <div style="margin-top: 15px;">
            <a href="users.php" style="color: #e74c3c; font-size: 14px;">
                <i class="fas fa-times"></i> Xóa bộ lọc
            </a>
        </div>
        <?php endif; ?>
    </form>
</div>

<!-- Bảng người dùng -->
<div class="container">
    <?php if ($result->num_rows > 0): ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th style="width: 60px;">ID</th>
                <th>Thông tin</th>
                <th style="width: 120px;">Vai trò</th>
                <th style="width: 100px;">Đơn hàng</th>
                <th style="width: 150px;">Tổng chi tiêu</th>
                <th style="width: 150px;">Ngày tạo</th>
                <th style="width: 180px;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td>
                    <div>
                        <strong style="color: #2d5016;"><?php echo safe_html($user['full_name']); ?></strong><br>
                        <small style="color: #666;">
                            <i class="fas fa-envelope"></i> <?php echo safe_html($user['email']); ?><br>
                            <?php if ($user['phone']): ?>
                            <i class="fas fa-phone"></i> <?php echo safe_html($user['phone']); ?>
                            <?php endif; ?>
                        </small>
                    </div>
                </td>
                <td>
                    <?php if ($user['role'] == 'admin'): ?>
                        <span style="background: #e74c3c; color: white; padding: 5px 10px; border-radius: 10px; font-size: 12px;">
                            <i class="fas fa-user-shield"></i> Admin
                        </span>
                    <?php else: ?>
                        <span style="background: #28a745; color: white; padding: 5px 10px; border-radius: 10px; font-size: 12px;">
                            <i class="fas fa-user"></i> Khách hàng
                        </span>
                    <?php endif; ?>
                </td>
                <td style="text-align: center;">
                    <?php if ($user['order_count'] > 0): ?>
                        <a href="<?php echo SITE_URL; ?>/admin/orders.php?user_id=<?php echo $user['id']; ?>" 
                           style="color: #2d5016; font-weight: 600;">
                            <?php echo $user['order_count']; ?> đơn
                        </a>
                    <?php else: ?>
                        <span style="color: #999;">0 đơn</span>
                    <?php endif; ?>
                </td>
                <td style="text-align: right;">
                    <strong style="color: #e74c3c;">
                        <?php echo formatMoney($user['total_spent'] ?? 0); ?>
                    </strong>
                </td>
                <td>
                    <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                </td>
                <td>
                    <a href="users_edit.php?id=<?php echo $user['id']; ?>" 
                       class="btn btn-sm btn-edit"
                       title="Chỉnh sửa">
                        <i class="fas fa-edit"></i> Sửa
                    </a>
                    
                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                        <a href="users_delete.php?id=<?php echo $user['id']; ?>" 
                           class="btn btn-sm btn-delete"
                           title="Xóa">
                            <i class="fas fa-trash-alt"></i> Xóa
                        </a>
                    <?php else: ?>
                        <button class="btn btn-sm btn-delete" 
                                disabled
                                title="Không thể xóa chính mình"
                                style="opacity: 0.5; cursor: not-allowed;">
                            <i class="fas fa-trash-alt"></i> Xóa
                        </button>
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
    <!-- Không có người dùng -->
    <div style="text-align: center; padding: 50px; background: white; border-radius: 10px;">
        <i class="fas fa-user-slash" style="font-size: 80px; color: #ddd; margin-bottom: 20px;"></i>
        <h3 style="color: #666; margin-bottom: 10px;">Không tìm thấy người dùng</h3>
        <?php if ($search || $role_filter): ?>
        <p style="color: #999;">Thử điều chỉnh bộ lọc hoặc <a href="users.php">xem tất cả người dùng</a></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>