<?php
/**
 * ORDER-HISTORY.PHP - Trang lịch sử mua hàng
 */

require_once 'includes/config.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirect('login.php?redirect=order-history.php');
}

require_once 'includes/header.php';

$user_id = $_SESSION['user_id'];

// Lấy tham số lọc
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Phân trang
$per_page = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// Xây dựng query
$where = "user_id = $user_id";
if ($status_filter) {
    $where .= " AND order_status = '" . $conn->real_escape_string($status_filter) . "'";
}

// Đếm tổng
$count_sql = "SELECT COUNT(*) as total FROM orders WHERE $where";
$total = $conn->query($count_sql)->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);

// Lấy đơn hàng
$orders_sql = "SELECT * FROM orders WHERE $where ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$orders = $conn->query($orders_sql)->fetch_all(MYSQLI_ASSOC);

// Thống kê
$total_orders = getTotalOrders($user_id);
$total_spent = getTotalSpent($user_id);
?>

<div class="container">
    <!-- Breadcrumb -->
    <div style="padding: 20px 0; color: #666;">
        <a href="<?php echo SITE_URL; ?>" style="color: #90c33c;">Trang chủ</a>
        <i class="fas fa-chevron-right" style="margin: 0 10px; font-size: 12px;"></i>
        <span>Lịch sử mua hàng</span>
    </div>
    
    <h1 style="color: #2d5016; margin-bottom: 30px;">
        <i class="fas fa-history"></i> Lịch Sử Mua Hàng
    </h1>
    
    <!-- Thống kê tổng quan -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 10px; color: white; text-align: center;">
            <i class="fas fa-shopping-bag" style="font-size: 40px; margin-bottom: 15px;"></i>
            <h3 style="font-size: 32px; margin-bottom: 10px;"><?php echo $total_orders; ?></h3>
            <p style="margin: 0;">Tổng đơn hàng</p>
        </div>
        
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 30px; border-radius: 10px; color: white; text-align: center;">
            <i class="fas fa-dollar-sign" style="font-size: 40px; margin-bottom: 15px;"></i>
            <h3 style="font-size: 28px; margin-bottom: 10px;"><?php echo formatMoney($total_spent); ?></h3>
            <p style="margin: 0;">Tổng chi tiêu</p>
        </div>
        
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 30px; border-radius: 10px; color: white; text-align: center;">
            <i class="fas fa-award" style="font-size: 40px; margin-bottom: 15px;"></i>
            <h3 style="font-size: 24px; margin-bottom: 10px;">
                <?php echo $total_orders >= 10 ? 'VIP' : ($total_orders >= 5 ? 'Thân thiết' : 'Mới'); ?>
            </h3>
            <p style="margin: 0;">Hạng thành viên</p>
        </div>
    </div>
    
    <!-- Bộ lọc -->
    <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <form method="GET" style="display: flex; gap: 15px; align-items: end;">
            <div style="flex: 1;">
                <label style="display: block; margin-bottom: 5px; color: #666;">Lọc theo trạng thái</label>
                <select name="status" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="">Tất cả đơn hàng</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                    <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                    <option value="shipping" <?php echo $status_filter == 'shipping' ? 'selected' : ''; ?>>Đang giao</option>
                    <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                    <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Lọc
            </button>
            
            <?php if ($status_filter): ?>
                <a href="order-history.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Xóa lọc
                </a>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- Danh sách đơn hàng -->
    <?php if (empty($orders)): ?>
        <div style="text-align: center; padding: 50px; background: white; border-radius: 10px;">
            <i class="fas fa-box-open" style="font-size: 80px; color: #ddd; margin-bottom: 20px;"></i>
            <h3 style="color: #666; margin-bottom: 10px;">Chưa có đơn hàng nào</h3>
            <p style="color: #999; margin-bottom: 30px;">Hãy bắt đầu mua sắm ngay!</p>
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i> Khám phá sản phẩm
            </a>
        </div>
    <?php else: ?>
        <div style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <?php foreach ($orders as $order): ?>
                <div style="border-bottom: 1px solid #eee; padding: 20px; transition: all 0.3s;"
                     onmouseover="this.style.background='#f8f9fa'"
                     onmouseout="this.style.background='white'">
                    
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                        <div>
                            <h3 style="color: #2d5016; margin-bottom: 5px;">
                                Đơn hàng #<?php echo safe_html($order['order_number']); ?>
                            </h3>
                            <p style="color: #666; font-size: 14px; margin: 0;">
                                <i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                            </p>
                        </div>
                        
                        <div style="text-align: right;">
                            <?php
                            $status_colors = [
                                'pending' => '#ffc107',
                                'processing' => '#17a2b8',
                                'shipping' => '#007bff',
                                'completed' => '#28a745',
                                'cancelled' => '#dc3545'
                            ];
                            
                            $status_labels = [
                                'pending' => 'Chờ xử lý',
                                'processing' => 'Đang xử lý',
                                'shipping' => 'Đang giao',
                                'completed' => 'Hoàn thành',
                                'cancelled' => 'Đã hủy'
                            ];
                            
                            $color = $status_colors[$order['order_status']];
                            $label = $status_labels[$order['order_status']];
                            ?>
                            <span style="background: <?php echo $color; ?>; color: white; padding: 6px 15px; border-radius: 20px; font-size: 13px; font-weight: 500;">
                                <?php echo $label; ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php
                    // Lấy sản phẩm trong đơn hàng
                    $items_sql = "SELECT * FROM order_items WHERE order_id = {$order['id']} LIMIT 3";
                    $items = $conn->query($items_sql)->fetch_all(MYSQLI_ASSOC);
                    ?>
                    
                    <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                        <?php foreach ($items as $item): ?>
                            <div style="color: #666; font-size: 14px;">
                                <i class="fas fa-box" style="color: #90c33c;"></i>
                                <?php echo safe_html($item['product_name']); ?> x<?php echo $item['quantity']; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 15px; border-top: 1px solid #eee;">
                        <div>
                            <span style="color: #666;">Tổng tiền:</span>
                            <strong style="color: #e74c3c; font-size: 20px; margin-left: 10px;">
                                <?php echo formatMoney($order['total_amount']); ?>
                            </strong>
                        </div>
                        
                        <div style="display: flex; gap: 10px;">
                            <a href="<?php echo SITE_URL; ?>/order-detail-user.php?id=<?php echo $order['id']; ?>" 
                               class="btn btn-primary" style="padding: 10px 20px;">
                                <i class="fas fa-eye"></i> Xem chi tiết
                            </a>
                            
                            <?php if ($order['order_status'] == 'completed'): ?>
                                <button class="btn btn-secondary" style="padding: 10px 20px;"
                                        onclick="alert('Tính năng mua lại đang phát triển')">
                                    <i class="fas fa-redo"></i> Mua lại
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
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
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>