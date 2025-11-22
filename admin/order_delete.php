<?php
require_once '../includes/config.php';

if (!isAdmin()) {
    redirect('login.php');
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    $_SESSION['error_message'] = 'Đơn hàng không hợp lệ!';
    redirect('orders.php');
}

// Kiểm tra đơn hàng tồn tại
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    $_SESSION['error_message'] = 'Đơn hàng không tồn tại!';
    redirect('orders.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    // Cập nhật trạng thái đơn hàng thành "cancelled"
    $update_stmt = $conn->prepare("UPDATE orders SET order_status = 'cancelled' WHERE id = ?");
    $update_stmt->bind_param("i", $order_id);
    if ($update_stmt->execute()) {
        $_SESSION['success_message'] = 'Đơn hàng đã được chuyển sang trạng thái Đã hủy!';
        redirect('orders.php');
    } else {
        $_SESSION['error_message'] = 'Có lỗi xảy ra khi cập nhật trạng thái đơn hàng!';
    }
}

require_once '../includes/header.php';
?>
<div class="container" style="max-width: 600px; margin: 40px auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <h2 style="color: #2d5016; margin-bottom: 20px;">
        <i class="fas fa-trash-alt"></i> Xóa Đơn Hàng
    </h2>
    <form method="POST">
        <p>Bạn có chắc chắn muốn xóa đơn hàng <strong>#<?php echo safe_html($order['order_number']); ?></strong> không?</p>
        <div style="margin-top: 30px;">
            <a href="orders.php" class="btn btn-secondary">Hủy</a>
            <button type="submit" name="confirm_delete" class="btn btn-danger">Xóa</button>
        </div>
    </form>
</div>
<?php require_once '../includes/footer.php'; ?>
