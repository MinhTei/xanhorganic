<?php
/**
 * ADMIN/INDEX.PHP - Trang Dashboard Admin (Cải tiến)
 * 
 * Cải tiến:
 * - Banner chào mừng động
 * - Liên kết nhanh đến các trang quản lý
 * - Biểu đồ thống kê trực quan
 * - Thông báo nhanh
 */

require_once '../includes/config.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    redirect('login.php');
}

require_once '../includes/header.php';

// ===== LẤY THỐNG KÊ TỔNG QUAN =====
$total_products = $conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()['total'];
$total_orders = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()['total'];
$total_users = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$total_categories = $conn->query("SELECT COUNT(*) AS total FROM categories")->fetch_assoc()['total'];

// Tổng doanh thu
$revenue_result = $conn->query("SELECT SUM(total_amount) AS total FROM orders WHERE order_status='completed'");
$total_revenue = $revenue_result->fetch_assoc()['total'] ?? 0;

// Thống kê đơn hàng theo trạng thái
$pending_orders = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE order_status='pending'")->fetch_assoc()['total'];
$processing_orders = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE order_status='processing'")->fetch_assoc()['total'];
$shipping_orders = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE order_status='shipping'")->fetch_assoc()['total'];
$completed_orders = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE order_status='completed'")->fetch_assoc()['total'];

// Sản phẩm sắp hết hàng
$low_stock_products = $conn->query("SELECT COUNT(*) AS total FROM products WHERE stock < 10 AND stock > 0")->fetch_assoc()['total'];

// Sản phẩm hết hàng
$out_of_stock = $conn->query("SELECT COUNT(*) AS total FROM products WHERE stock = 0")->fetch_assoc()['total'];

// Doanh thu tháng này
$current_month_revenue = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE order_status='completed' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetch_assoc()['total'] ?? 0;

// Khách hàng mới tháng này
$new_customers = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='customer' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetch_assoc()['total'];
?>

<!-- Hero Banner cho Admin -->
<section style="background: linear-gradient(135deg, #2d5016, #90c33c); padding: 40px 0; margin-bottom: 30px; color: white;">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 36px; margin-bottom: 10px; color: white;">
                    <i class="fas fa-tachometer-alt"></i> Chào mừng, <?php echo htmlspecialchars($_SESSION['full_name']); ?>! 👋
                </h1>
                <p style="font-size: 16px; opacity: 0.9; margin: 0;">
                    Hôm nay là <?php echo date('l, d/m/Y'); ?> | 
                    <i class="fas fa-clock"></i> <?php echo date('H:i'); ?>
                </p>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <a href="<?php echo SITE_URL; ?>" target="_blank" 
                   class="btn" 
                   style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white; padding: 12px 25px;">
                    <i class="fas fa-globe"></i> Xem Website
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/orders.php" 
                   class="btn" 
                   style="background: white; color: #2d5016; padding: 12px 25px; font-weight: 600;">
                    <i class="fas fa-shopping-cart"></i> Đơn Hàng Mới
                    <?php if ($pending_orders > 0): ?>
                        <span style="background: #e74c3c; color: white; padding: 2px 8px; border-radius: 10px; margin-left: 5px; font-size: 12px;">
                            <?php echo $pending_orders; ?>
                        </span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Admin Dashboard Container -->
<section class="admin-dashboard">
    <div class="container">
        
        <!-- Thông báo quan trọng -->
        <?php if ($pending_orders > 0 || $low_stock_products > 0 || $out_of_stock > 0): ?>
        <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
            <h3 style="color: #856404; margin-bottom: 15px;">
                <i class="fas fa-exclamation-triangle"></i> Cần Chú Ý
            </h3>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <?php if ($pending_orders > 0): ?>
                <div style="color: #856404;">
                    <i class="fas fa-shopping-cart"></i> 
                    Có <strong><?php echo $pending_orders; ?> đơn hàng mới</strong> chờ xử lý 
                    <a href="<?php echo SITE_URL; ?>/admin/orders.php?status=pending" style="color: #2d5016; text-decoration: underline; margin-left: 10px;">
                        Xem ngay →
                    </a>
                </div>
                <?php endif; ?>
                
                <?php if ($low_stock_products > 0): ?>
                <div style="color: #856404;">
                    <i class="fas fa-box"></i> 
                    Có <strong><?php echo $low_stock_products; ?> sản phẩm</strong> sắp hết hàng (dưới 10 sản phẩm)
                    <a href="<?php echo SITE_URL; ?>/admin/products.php" style="color: #2d5016; text-decoration: underline; margin-left: 10px;">
                        Xem chi tiết →
                    </a>
                </div>
                <?php endif; ?>
                
                <?php if ($out_of_stock > 0): ?>
                <div style="color: #856404;">
                    <i class="fas fa-exclamation-circle"></i> 
                    Có <strong><?php echo $out_of_stock; ?> sản phẩm</strong> đã hết hàng
                    <a href="<?php echo SITE_URL; ?>/admin/products.php" style="color: #2d5016; text-decoration: underline; margin-left: 10px;">
                        Cập nhật kho →
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Cards Thống Kê với liên kết -->
        <div class="dashboard-cards">
            <!-- Card: Sản phẩm -->
            <a href="<?php echo SITE_URL; ?>/admin/products.php" style="text-decoration: none;">
                <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-box"></i>
                    <h3><?php echo number_format($total_products); ?></h3>
                    <p>Sản phẩm</p>
                    <small style="font-size: 11px; opacity: 0.9;">
                        <?php echo $out_of_stock; ?> hết hàng
                    </small>
                </div>
            </a>
            
            <!-- Card: Danh mục -->
            <a href="<?php echo SITE_URL; ?>/admin/categories.php" style="text-decoration: none;">
                <div class="card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-list"></i>
                    <h3><?php echo number_format($total_categories); ?></h3>
                    <p>Danh mục</p>
                    <small style="font-size: 11px; opacity: 0.9;">
                        Quản lý phân loại
                    </small>
                </div>
            </a>
            
            <!-- Card: Đơn hàng -->
            <a href="<?php echo SITE_URL; ?>/admin/orders.php" style="text-decoration: none;">
                <div class="card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-shopping-cart"></i>
                    <h3><?php echo number_format($total_orders); ?></h3>
                    <p>Đơn hàng</p>
                    <small style="font-size: 11px; opacity: 0.9;">
                        <?php echo $pending_orders; ?> chờ xử lý
                    </small>
                </div>
            </a>
            
            <!-- Card: Người dùng -->
            <a href="<?php echo SITE_URL; ?>/admin/users.php" style="text-decoration: none;">
                <div class="card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <i class="fas fa-users"></i>
                    <h3><?php echo number_format($total_users); ?></h3>
                    <p>Người dùng</p>
                    <small style="font-size: 11px; opacity: 0.9;">
                        +<?php echo $new_customers; ?> tháng này
                    </small>
                </div>
            </a>
            
            <!-- Card: Doanh thu -->
            <div class="card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <i class="fas fa-dollar-sign"></i>
                <h3><?php echo number_format($total_revenue, 0, ',', '.'); ?> ₫</h3>
                <p>Tổng Doanh Thu</p>
                <small style="font-size: 11px; opacity: 0.9;">
                    Đơn hoàn thành
                </small>
            </div>
            
            <!-- Card: Doanh thu tháng -->
            <div class="card" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                <i class="fas fa-chart-line"></i>
                <h3><?php echo number_format($current_month_revenue, 0, ',', '.'); ?> ₫</h3>
                <p>Doanh Thu Tháng</p>
                <small style="font-size: 11px; opacity: 0.9;">
                    <?php echo date('m/Y'); ?>
                </small>
            </div>
        </div>

        <!-- Thống Kê Đơn Hàng Theo Trạng Thái -->
        <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px;">
            <h2 style="color: #2d5016; margin-bottom: 20px;">
                <i class="fas fa-chart-bar"></i> Thống Kê Đơn Hàng
            </h2>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
                <a href="<?php echo SITE_URL; ?>/admin/orders.php?status=pending" style="text-decoration: none;">
                    <div style="text-align: center; padding: 20px; background: #fff3cd; border-radius: 10px; transition: all 0.3s; cursor: pointer;">
                        <h3 style="color: #856404; font-size: 32px; margin-bottom: 10px;"><?php echo $pending_orders; ?></h3>
                        <p style="color: #856404; margin: 0;">Chờ xử lý</p>
                    </div>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/admin/orders.php?status=processing" style="text-decoration: none;">
                    <div style="text-align: center; padding: 20px; background: #d1ecf1; border-radius: 10px; transition: all 0.3s; cursor: pointer;">
                        <h3 style="color: #0c5460; font-size: 32px; margin-bottom: 10px;"><?php echo $processing_orders; ?></h3>
                        <p style="color: #0c5460; margin: 0;">Đang xử lý</p>
                    </div>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/admin/orders.php?status=shipping" style="text-decoration: none;">
                    <div style="text-align: center; padding: 20px; background: #cce5ff; border-radius: 10px; transition: all 0.3s; cursor: pointer;">
                        <h3 style="color: #004085; font-size: 32px; margin-bottom: 10px;"><?php echo $shipping_orders; ?></h3>
                        <p style="color: #004085; margin: 0;">Đang giao</p>
                    </div>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/admin/orders.php?status=completed" style="text-decoration: none;">
                    <div style="text-align: center; padding: 20px; background: #d4edda; border-radius: 10px; transition: all 0.3s; cursor: pointer;">
                        <h3 style="color: #155724; font-size: 32px; margin-bottom: 10px;"><?php echo $completed_orders; ?></h3>
                        <p style="color: #155724; margin: 0;">Hoàn thành</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Quản Lý Nhanh với icon và màu sắc -->
        <div class="quick-actions">
            <h2><i class="fas fa-bolt"></i> Thao Tác Nhanh</h2>
            <div class="actions-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                <a href="<?php echo SITE_URL; ?>/admin/products.php" 
                   class="btn-admin" 
                   style="background: linear-gradient(135deg, #667eea, #764ba2); padding: 20px; text-align: center; border-radius: 10px;">
                    <i class="fas fa-boxes" style="font-size: 30px; margin-bottom: 10px; display: block;"></i>
                    <strong>Quản lý sản phẩm</strong>
                    <p style="font-size: 13px; margin: 5px 0 0 0; opacity: 0.9;"><?php echo $total_products; ?> sản phẩm</p>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/admin/product_add.php" 
                   class="btn-admin" 
                   style="background: linear-gradient(135deg, #43e97b, #38f9d7); padding: 20px; text-align: center; border-radius: 10px;">
                    <i class="fas fa-plus-circle" style="font-size: 30px; margin-bottom: 10px; display: block;"></i>
                    <strong>Thêm sản phẩm mới</strong>
                    <p style="font-size: 13px; margin: 5px 0 0 0; opacity: 0.9;">Tạo sản phẩm mới</p>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/admin/orders.php" 
                   class="btn-admin" 
                   style="background: linear-gradient(135deg, #4facfe, #00f2fe); padding: 20px; text-align: center; border-radius: 10px;">
                    <i class="fas fa-shopping-bag" style="font-size: 30px; margin-bottom: 10px; display: block;"></i>
                    <strong>Quản lý đơn hàng</strong>
                    <p style="font-size: 13px; margin: 5px 0 0 0; opacity: 0.9;"><?php echo $total_orders; ?> đơn hàng</p>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/admin/categories.php" 
                   class="btn-admin" 
                   style="background: linear-gradient(135deg, #f093fb, #f5576c); padding: 20px; text-align: center; border-radius: 10px;">
                    <i class="fas fa-tags" style="font-size: 30px; margin-bottom: 10px; display: block;"></i>
                    <strong>Quản lý danh mục</strong>
                    <p style="font-size: 13px; margin: 5px 0 0 0; opacity: 0.9;"><?php echo $total_categories; ?> danh mục</p>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/admin/users.php" 
                   class="btn-admin" 
                   style="background: linear-gradient(135deg, #fa709a, #fee140); padding: 20px; text-align: center; border-radius: 10px;">
                    <i class="fas fa-user-cog" style="font-size: 30px; margin-bottom: 10px; display: block;"></i>
                    <strong>Quản lý người dùng</strong>
                    <p style="font-size: 13px; margin: 5px 0 0 0; opacity: 0.9;"><?php echo $total_users; ?> người dùng</p>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/admin/category_add.php" 
                   class="btn-admin" 
                   style="background: linear-gradient(135deg, #a8edea, #fed6e3); padding: 20px; text-align: center; border-radius: 10px;">
                    <i class="fas fa-folder-plus" style="font-size: 30px; margin-bottom: 10px; display: block;"></i>
                    <strong>Thêm danh mục</strong>
                    <p style="font-size: 13px; margin: 5px 0 0 0; opacity: 0.9;">Tạo danh mục mới</p>
                </a>
            </div>
        </div>

        <!-- Đơn Hàng Gần Đây -->
        <div class="recent-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2><i class="fas fa-clock"></i> Đơn Hàng Gần Đây</h2>
                <a href="<?php echo SITE_URL; ?>/admin/orders.php" style="color: #90c33c; font-weight: 500;">
                    Xem tất cả <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <?php
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
                            <a href="<?php echo SITE_URL; ?>/admin/order_detail.php?id=<?php echo $order['id']; ?>" 
                               style="color: #2d5016; font-weight: 600;">
                                <?php echo htmlspecialchars($order['order_number']); ?>
                            </a>
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
                            <a href="<?php echo SITE_URL; ?>/admin/order_detail.php?id=<?php echo $order['id']; ?>" 
                               class="btn btn-sm btn-view"
                               title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div style="text-align: center; padding: 50px; color: #666; background: white; border-radius: 10px;">
                <i class="fas fa-inbox" style="font-size: 60px; color: #ddd; margin-bottom: 20px;"></i>
                <p>Chưa có đơn hàng nào</p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Liên Kết Nhanh -->
        <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-top: 30px;">
            <h2 style="color: #2d5016; margin-bottom: 20px;">
                <i class="fas fa-link"></i> Liên Kết Hữu Ích
            </h2>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px;">
                <a href="<?php echo SITE_URL; ?>" target="_blank" 
                   style="padding: 15px; background: #f8f9fa; border-radius: 8px; text-align: center; color: #333; text-decoration: none; transition: all 0.3s;"
                   onmouseover="this.style.background='#e9ecef'" onmouseout="this.style.background='#f8f9fa'">
                    <i class="fas fa-home" style="font-size: 24px; color: #90c33c; margin-bottom: 10px; display: block;"></i>
                    <strong>Website</strong>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/admin/products.php" 
                   style="padding: 15px; background: #f8f9fa; border-radius: 8px; text-align: center; color: #333; text-decoration: none; transition: all 0.3s;"
                   onmouseover="this.style.background='#e9ecef'" onmouseout="this.style.background='#f8f9fa'">
                    <i class="fas fa-box" style="font-size: 24px; color: #667eea; margin-bottom: 10px; display: block;"></i>
                    <strong>Sản phẩm</strong>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/admin/orders.php" 
                   style="padding: 15px; background: #f8f9fa; border-radius: 8px; text-align: center; color: #333; text-decoration: none; transition: all 0.3s;"
                   onmouseover="this.style.background='#e9ecef'" onmouseout="this.style.background='#f8f9fa'">
                    <i class="fas fa-shopping-cart" style="font-size: 24px; color: #4facfe; margin-bottom: 10px; display: block;"></i>
                    <strong>Đơn hàng</strong>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/logout.php" 
                   style="padding: 15px; background: #f8f9fa; border-radius: 8px; text-align: center; color: #333; text-decoration: none; transition: all 0.3s;"
                   onclick="return confirm('Bạn có chắc muốn đăng xuất?')"
                   onmouseover="this.style.background='#fee'" onmouseout="this.style.background='#f8f9fa'">
                    <i class="fas fa-sign-out-alt" style="font-size: 24px; color: #e74c3c; margin-bottom: 10px; display: block;"></i>
                    <strong>Đăng xuất</strong>
                </a>
            </div>
        </div>
    </div>
</section>

<style>
/* Hover effects cho cards */
.dashboard-cards a:hover .card {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.actions-grid a:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-cards {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    
    .actions-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>