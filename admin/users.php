<?php
require_once __DIR__ . '/../includes/config.php';

// Kiểm tra quyền admin trước khi include header
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../includes/header.php';

// Lấy danh sách người dùng
$result = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>

<h1><i class="fas fa-users"></i> Quản lý Người dùng</h1>

<!-- Nút quay về dashboard -->
<a href="index.php" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i> Dashboard
</a>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Tên</th>
            <th>Email</th>
            <th>Vai trò</th>
            <th>Ngày tạo</th>
            <th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($user = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $user['id']; ?></td>
            <td><?php echo htmlspecialchars($user['name']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><?php echo $user['role'] == 'admin' ? 'Quản trị' : 'Khách hàng'; ?></td>
            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
            <td>
                <a href="user_edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-edit">
                    <i class="fas fa-edit"></i> Sửa
                </a>
                <a href="user_delete.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-delete"
                   onclick="return confirm('Xóa tài khoản này?');">
                   <i class="fas fa-trash-alt"></i> Xóa
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
