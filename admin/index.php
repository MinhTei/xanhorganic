<?php
/**
 * ADMIN/INDEX.PHP - Trang Dashboard Admin
 * 
 * Hiển thị:
 * - Thống kê tổng quan (sản phẩm, đơn hàng, users, doanh thu)
 * - Quản lý nhanh (quick actions)
 * - Đơn hàng gần đây
 */

require_once '../includes/config.php';

// ===== KIỂM TRA QUYỀN ADMIN =====
// Phải đăng nhập và có role = 'admin'
if (!isAdmin()) {
    redirect('login.php'); // Redirect về trang login (không phải admin/login.php)
}

require_once '../includes/header.php';

// ===== LẤY THỐNG KÊ TỔNG QUAN =====

// Tổng số sản phẩm
$total_products = $conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()['total'];

// Tổng số đơn hàng
$total_orders = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()['total'];

// Tổng số người dùng
$total_users = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];

// Tổng số danh mục
$total_categories = $conn->query("SELECT COUNT(*) AS total FROM categories")->fetch_assoc()['total'];

// Tổng doanh thu (chỉ tính đơn hàng đã hoàn thành)
$revenue_result = $conn->query("SELECT SUM(total_amount) AS total FROM orders WHERE order_status='completed'");
$total_revenue = $revenue_result->fetch_assoc()['total'] ?? 0;

// Thống kê đơn hàng theo trạng thái
$pending_orders = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE order_status='pending'")->fetch_assoc()['total'];
$processing_orders = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE order_status='processing'")->fetch_assoc()['total'];
$shipping_orders = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE order_status='shipping'")->fetch_assoc()['total'];
?>

<!-- Dashboard Container -->
<section class="admin-dashboard">
    <div class="container">
        <!-- Header -->
        <h1 class="admin-title">
            <i class="fas fa-tachometer-alt"></i> Bảng Điều Khiển Quản Trị
        </h1>
        <p>Chào mừng, <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong> 👋</p>

        <!-- ===== CARDS THỐNG KÊ TỔNG QUAN ===== -->
        <div class="dashboard-cards">
            <!-- Card: Sản phẩm -->
            <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="fas fa-box"></i>
                <h3><?php echo number_format($total_products); ?></h3>
                <p>Sản phẩm</p>
                <a href="products.php" style="color: white; font-size: 12px; text-decoration: underline;">
                    Xem chi tiết →
                </a>
            </div>
            
            <!-- Card: Danh mục -->
            <div class="card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <i class="fas fa-list"></i>
                <h3><?php echo number_format($total_categories); ?></h3>
                <p>Danh mục</p>
                <a href="categories.php" style="color: white; font-size: 12px; text-decoration: underline;">
                    Xem chi tiết →
                </a>
            </div>
            
            <!-- Card: Đơn hàng -->
            <div class="card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <i class="fas fa-shopping-cart"></i>
                <h3><?php echo number_format($total_orders); ?></h3>
                <p>Đơn hàng</p>
                <a href="orders.php" style="color: white; font-size: 12px; text-decoration: underline;">
                    Xem chi tiết →
                </a>
            </div>
            
            <!-- Card: Người dùng -->
            <div class="card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <i class="fas fa-users"></i>
                <h3><?php echo number_format($total_users); ?></h3>
                <p>Người dùng</p>
                <a href="users.php" style="color: white; font-size: 12px; text-decoration: underline;">
                    Xem chi tiết →
                </a>
            </div>
            
            <!-- Card: Doanh thu -->
            <div class="card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <i class="fas fa-dollar-sign"></i>
                <h3><?php echo number_format($total_revenue, 0, ',', '.'); ?> ₫</h3>
                <p>Doanh thu</p>
                <small style="color: white; font-size: 11px;">(Đơn hoàn thành)</small>
            </div>
        </div>

        <!-- ===== THỐNG KÊ ĐƠN HÀNG THEO TRẠNG THÁI ===== -->
        <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px;">
            <h2 style="color: #2d5016; margin-bottom: 20px;">
                <i class="fas fa-chart-bar"></i> Thống Kê Đơn Hàng
            </h2>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
                <div style="text-align: center; padding: 20px; background: #fff3cd; border-radius: 10px;">
                    <h3 style="color: #856404; font-size: 32px; margin-bottom: 10px;"><?php echo $pending_orders; ?></h3>
                    <p style="color: #856404; margin: 0;">Chờ xử lý</p>
                </div>
                <div style="text-align: center; padding: 20px; background: #d1ecf1; border-radius: 10px;">
                    <h3 style="color: #0c5460; font-size: 32px; margin-bottom: 10px;"><?php echo $processing_orders; ?></h3>
                    <p style="color: #0c5460; margin: 0;">Đang xử lý</p>
                </div>
                <div style="text-align: center; padding: 20px; background: #cce5ff; border-radius: 10px;">
                    <h3 style="color: #004085; font-size: 32px; margin-bottom: 10px;"><?php echo $shipping_orders; ?></h3>
                    <p style="color: #004085; margin: 0;">Đang giao</p>
                </div>
                <div style="text-align: center; padding: 20px; background: #d4edda; border-radius: 10px;">
                    <h3 style="color: #155724; font-size: 32px; margin-bottom: 10px;">
                        <?php 
                        $completed = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE order_status='completed'")->fetch_assoc()['total'];
                        echo $completed; 
                        ?>
                    </h3>
                    <p style="color: #155724; margin: 0;">Hoàn thành</p>
                </div>
            </div>
        </div>

        <!-- ===== QUẢN LÝ NHANH (QUICK ACTIONS) ===== -->
        <div class="quick-actions">
            <h2><i class="fas fa-cogs"></i> Quản Lý Nhanh</h2>
            <div class="actions-grid">
                <a href="products.php" class="btn-admin">
                    <i class="fas fa-boxes"></i> Quản lý sản phẩm
                </a>
                <a href="product_add.php" class="btn-admin">
                    <i class="fas fa-plus-circle"></i> Thêm sản phẩm mới
                </a>
                <a href="orders.php" class="btn-admin">
                    <i class="fas fa-shopping-bag"></i> Quản lý đơn hàng
                </a>
                <a href="categories.php" class="btn-admin">
                    <i class="fas fa-tags"></i> Quản lý danh mục
                </a>
                <a href="users.php" class="btn-admin">
                    <i class="fas fa-user-cog"></i> Quản lý người dùng
                </a>
                <a href="#" class="btn-admin disabled" onclick="alert('Chức năng đang phát triển'); return false;">
                    <i class="fas fa-sliders-h"></i> Cấu hình hệ thống
                </a>
            </div>
        </div>

        <!-- ===== ĐỢN HÀNG GẦN ĐÂY ===== -->
        <div class="recent-section">
            <h2><i class="fas fa-clock"></i> Đơn Hàng Gần Đây</h2>
            <?php
            // Lấy 10 đơn hàng mới nhất
            $recent_orders = $conn->query("SELECT o.*, u.full_name as customer_name 
                                           FROM orders o 
                                           LEFT JOIN users u ON o.user_id = u.id 
                                           ORDER BY o.created_at DESC 
                                           LIMIT 10");
            ?>
            
            <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
            <table class="recent-table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Thanh toán</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $recent_orders->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong style="color: #2d5016;"><?php echo htmlspecialchars($order['order_number']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($order['customer_name'] ?? $order['full_name']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        <td>
                            <strong style="color: #e74c3c;">
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
                            echo $payment_badges[$order['payment_status']] ?? $order['payment_status'];
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
                            echo $status_badges[$order['order_status']] ?? $order['order_status'];
                            ?>
                        </td>
                        <td>
                            <a href="order_detail.php?id=<?php echo $order['id']; ?>" 
                               class="btn btn-sm btn-view"
                               title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="orders.php" class="btn btn-primary">
                    Xem Tất Cả Đơn Hàng <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <?php else: ?>
            <div style="text-align: center; padding: 50px; color: #666;">
                <i class="fas fa-inbox" style="font-size: 60px; color: #ddd; margin-bottom: 20px;"></i>
                <p>Chưa có đơn hàng nào</p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- ===== LIÊN KẾT NHANH ===== -->
        <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-top: 30px;">
            <h2 style="color: #2d5016; margin-bottom: 20px;">
                <i class="fas fa-link"></i> Liên Kết Nhanh
            </h2>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                <a href="<?php echo SITE_URL; ?>" target="_blank" 
                   style="padding: 15px; background: #f8f9fa; border-radius: 8px; text-align: center; color: #333; text-decoration: none; transition: all 0.3s;">
                    <i class="fas fa-home" style="font-size: 24px; color: #90c33c; margin-bottom: 10px;"></i>
                    <p style="margin: 0;">Xem Website</p>
                </a>
                <a href="<?php echo SITE_URL; ?>/logout.php" 
                   style="padding: 15px; background: #f8f9fa; border-radius: 8px; text-align: center; color: #333; text-decoration: none; transition: all 0.3s;"
                   onclick="return confirm('Bạn có chắc muốn đăng xuất?')">
                    <i class="fas fa-sign-out-alt" style="font-size: 24px; color: #e74c3c; margin-bottom: 10px;"></i>
                    <p style="margin: 0;">Đăng Xuất</p>
                </a>
                <a href="<?php echo SITE_URL; ?>/profile.php" 
                   style="padding: 15px; background: #f8f9fa; border-radius: 8px; text-align: center; color: #333; text-decoration: none; transition: all 0.3s;">
                    <i class="fas fa-user-circle" style="font-size: 24px; color: #007bff; margin-bottom: 10px;"></i>
                    <p style="margin: 0;">Tài Khoản</p>
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>