<?php
require_once '../includes/config.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    redirect('login.php');
}

require_once '../includes/header.php';

// Lấy thống kê
$total_products = $conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()['total'];
$total_orders = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()['total'];
$total_users = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$total_categories = $conn->query("SELECT COUNT(*) AS total FROM categories")->fetch_assoc()['total'];
$total_revenue = $conn->query("SELECT SUM(total_amount) AS total FROM orders WHERE order_status='completed'")->fetch_assoc()['total'] ?? 0;
?>

<!-- Dashboard -->
<section class="admin-dashboard">
    <div class="container">
        <h1 class="admin-title"><i class="fas fa-tachometer-alt"></i> Bảng Điều Khiển Quản Trị</h1>
        <p>Chào mừng, <strong><?php echo $_SESSION['full_name']; ?></strong> 👋</p>

        <!-- Cards thống kê -->
        <div class="dashboard-cards">
            <div class="card">
                <i class="fas fa-box"></i>
                <h3><?php echo $total_products; ?></h3>
                <p>Sản phẩm</p>
            </div>
            <div class="card">
                <i class="fas fa-list"></i>
                <h3><?php echo $total_categories; ?></h3>
                <p>Danh mục</p>
            </div>
            <div class="card">
                <i class="fas fa-shopping-cart"></i>
                <h3><?php echo $total_orders; ?></h3>
                <p>Đơn hàng</p>
            </div>
            <div class="card">
                <i class="fas fa-users"></i>
                <h3><?php echo $total_users; ?></h3>
                <p>Người dùng</p>
            </div>
            <div class="card">
                <i class="fas fa-dollar-sign"></i>
                <h3><?php echo number_format($total_revenue, 0, ',', '.'); ?> ₫</h3>
                <p>Doanh thu</p>
            </div>
        </div>

        <!-- Khu vực quản lý nhanh -->
        <div class="quick-actions">
            <h2><i class="fas fa-cogs"></i> Quản Lý Nhanh</h2>
            <div class="actions-grid">
                <a href="products.php" class="btn-admin"><i class="fas fa-boxes"></i> Quản lý sản phẩm</a>
                <a href="orders.php" class="btn-admin"><i class="fas fa-shopping-bag"></i> Quản lý đơn hàng</a>
                <a href="#" class="btn-admin disabled" onclick="alert('Chức năng đang phát triển');"><i class="fas fa-user-cog"></i> Quản lý người dùng</a>
                <a href="#" class="btn-admin disabled" onclick="alert('Chức năng đang phát triển');"><i class="fas fa-tags"></i> Quản lý danh mục</a>
                <a href="#" class="btn-admin disabled" onclick="alert('Chức năng đang phát triển');"><i class="fas fa-sliders-h"></i> Cấu hình hệ thống</a>
            </div>
        </div>

        <!-- Gần đây -->
        <div class="recent-section">
            <h2><i class="fas fa-clock"></i> Hoạt động gần đây</h2>
            <?php
            $recent_orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
            ?>
            <table class="recent-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $recent_orders->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> ₫</td>
                        <td><span class="status <?php echo strtolower($order['status']); ?>">
                            <?php echo htmlspecialchars($order['status']); ?>
                        </span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
