<?php
// categories.php - Quản lý danh mục admin

// Include config trước để kết nối database và session
require_once __DIR__ . '/../includes/config.php';

// Kiểm tra đăng nhập admin trước khi output bất kỳ HTML nào
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Lấy danh sách danh mục từ database
$result = $conn->query("SELECT * FROM categories ORDER BY id DESC");

// Include header HTML
require_once __DIR__ . '/../includes/header.php';
?>

<h1><i class="fas fa-tags"></i> Quản lý Danh mục</h1>

<!-- Nút thêm danh mục mới -->
<a href="category_add.php" class="btn btn-primary">
    <i class="fas fa-plus"></i> Thêm danh mục
</a>

<!-- Bảng danh mục -->
<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Tên danh mục</th>
            <th>Mô tả</th>
            <th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($cat = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $cat['id']; ?></td>
                <td><?php echo htmlspecialchars($cat['name']); ?></td>
                <td><?php echo htmlspecialchars($cat['description']); ?></td>
                <td>
                    <!-- Nút sửa danh mục -->
                    <a href="category_edit.php?id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-edit">
                        <i class="fas fa-edit"></i> Sửa
                    </a>

                    <!-- Nút xóa danh mục -->
                    <a href="category_delete.php?id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-delete"
                       onclick="return confirm('Bạn có chắc muốn xóa danh mục này?');">
                        <i class="fas fa-trash-alt"></i> Xóa
                    </a>

                    <!-- Nút quay về dashboard -->
                    <a href="index.php" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Dashboard
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" style="text-align:center;">Chưa có danh mục nào.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
// Include footer HTML
require_once __DIR__ . '/../includes/footer.php';
?>
