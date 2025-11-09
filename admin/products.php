<?php
require_once '../includes/config.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    redirect('login.php');
}

require_once '../includes/header.php';

// Lấy danh sách sản phẩm
$result = $conn->query("SELECT p.*, c.name AS category_name 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        ORDER BY p.id DESC");
?>
<h1><i class="fas fa-box"></i> Quản lý Sản phẩm</h1>

<a href="product_add.php" class="btn btn-primary"><i class="fas fa-plus"></i> Thêm sản phẩm mới</a>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Tên sản phẩm</th>
            <th>Danh mục</th>
            <th>Giá</th>
            <th>Giảm giá</th>
            <th>Đơn vị</th>
            <th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
            <td><?php echo number_format($row['price'], 0, ',', '.'); ?> ₫</td>
            <td><?php echo $row['sale_price'] ? number_format($row['sale_price'], 0, ',', '.') . " ₫" : '-'; ?></td>
            <td><?php echo $row['unit']; ?></td>
            <td>
                <a href="product_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-edit">Sửa</a>
                <a href="product_delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Xóa sản phẩm này?');">Xóa</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php require_once '../includes/footer.php'; ?>
