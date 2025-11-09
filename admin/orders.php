<?php
require_once '../includes/config.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    redirect('login.php');
}

require_once '../includes/header.php';

$orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
?>
<h1><i class="fas fa-shopping-cart"></i> Quản lý Đơn hàng</h1>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Khách hàng</th>
            <th>Ngày đặt</th>
            <th>Tổng tiền</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($order = $orders->fetch_assoc()): ?>
        <tr>
            <td>#<?php echo $order['id']; ?></td>
            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
            <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
            <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> ₫</td>
            <td><?php echo htmlspecialchars($order['status']); ?></td>
            <td>
                <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-view">Xem</a>
                <a href="order_delete.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Xóa đơn hàng này?');">Xóa</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php require_once '../includes/footer.php'; ?>
