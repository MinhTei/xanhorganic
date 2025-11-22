<?php
/**
 * check_images.php - Kiểm tra và báo cáo tình trạng ảnh trong dự án
 * Đặt file này ở thư mục gốc dự án và chạy qua browser
 */

require_once __DIR__ . '/includes/config.php';

// Chỉ cho admin chạy
if (!isAdmin()) {
    die("Chỉ admin mới có quyền truy cập trang này.");
}

$products_dir = __DIR__ . '/assets/images/products/';
$categories_dir = __DIR__ . '/assets/images/categories/';

// Tạo thư mục nếu chưa có
if (!is_dir($products_dir)) mkdir($products_dir, 0755, true);
if (!is_dir($categories_dir)) mkdir($categories_dir, 0755, true);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kiểm tra ảnh - Xanh Organic</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 1200px; margin: 0 auto; }
        h1 { color: #2d5016; }
        h2 { color: #666; border-bottom: 2px solid #90c33c; padding-bottom: 10px; }
        .ok { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #90c33c; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .summary { background: #f0f0f0; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .img-preview { max-width: 80px; max-height: 80px; object-fit: cover; border-radius: 4px; }
        .btn { padding: 10px 20px; background: #90c33c; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #7ab02c; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
    </style>
</head>
<body>
    <h1>🔍 Kiểm Tra Ảnh Dự Án Xanh Organic</h1>
    
    <!-- Thông tin thư mục -->
    <div class="summary">
        <h3>📁 Thông tin thư mục</h3>
        <p><strong>Thư mục sản phẩm:</strong> <?php echo $products_dir; ?></p>
        <p>Tồn tại: <?php echo is_dir($products_dir) ? '<span class="ok">✅ Có</span>' : '<span class="error">❌ Không</span>'; ?></p>
        <p>Quyền ghi: <?php echo is_writable($products_dir) ? '<span class="ok">✅ Có</span>' : '<span class="error">❌ Không</span>'; ?></p>
        
        <p><strong>Thư mục danh mục:</strong> <?php echo $categories_dir; ?></p>
        <p>Tồn tại: <?php echo is_dir($categories_dir) ? '<span class="ok">✅ Có</span>' : '<span class="error">❌ Không</span>'; ?></p>
        <p>Quyền ghi: <?php echo is_writable($categories_dir) ? '<span class="ok">✅ Có</span>' : '<span class="error">❌ Không</span>'; ?></p>
    </div>

    <!-- Kiểm tra sản phẩm -->
    <h2>🛒 Ảnh Sản Phẩm</h2>
    <?php
    $sql = "SELECT id, name, image, category_id FROM products ORDER BY id";
    $result = $conn->query($sql);
    
    $total = 0;
    $found = 0;
    $missing = 0;
    $external = 0;
    $empty = 0;
    
    echo "<table>";
    echo "<tr><th>ID</th><th>Tên SP</th><th>Giá trị trong DB</th><th>Trạng thái</th><th>Preview</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $total++;
        $img = $row['image'];
        $status = '';
        $preview = '';
        
        if (empty($img)) {
            $empty++;
            $status = '<span class="warning">⚠️ Chưa có ảnh</span>';
            $preview = '-';
        } elseif (preg_match('#^https?://#i', $img)) {
            $external++;
            $status = '<span class="ok">✅ URL bên ngoài</span>';
            $preview = "<img src='$img' class='img-preview' onerror=\"this.src='https://via.placeholder.com/80?text=Error'\">";
        } else {
            // Kiểm tra file local
            $localPath = $products_dir . basename($img);
            $altPath = $products_dir . $img;
            $assetsPath = __DIR__ . '/assets/' . $img;
            
            if (file_exists($localPath) || file_exists($altPath) || file_exists($assetsPath)) {
                $found++;
                $status = '<span class="ok">✅ Tìm thấy</span>';
                $imgUrl = SITE_URL . '/assets/images/products/' . basename($img);
                $preview = "<img src='$imgUrl' class='img-preview' onerror=\"this.src='https://via.placeholder.com/80?text=Error'\">";
            } else {
                $missing++;
                $status = '<span class="error">❌ THIẾU FILE</span>';
                $preview = '<span class="error">File không tồn tại</span>';
            }
        }
        
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td style='font-size:12px; word-break:break-all;'>" . htmlspecialchars($img ?: '(trống)') . "</td>";
        echo "<td>$status</td>";
        echo "<td>$preview</td>";
        echo "</tr>";
    }
    echo "</table>";
    ?>
    
    <div class="summary">
        <h3>📊 Tổng kết Sản phẩm</h3>
        <p>Tổng số: <strong><?php echo $total; ?></strong></p>
        <p class="ok">✅ Tìm thấy file: <strong><?php echo $found; ?></strong></p>
        <p class="ok">🌐 URL bên ngoài: <strong><?php echo $external; ?></strong></p>
        <p class="warning">⚠️ Chưa có ảnh: <strong><?php echo $empty; ?></strong></p>
        <p class="error">❌ Thiếu file: <strong><?php echo $missing; ?></strong></p>
    </div>

    <!-- Kiểm tra danh mục -->
    <h2>📁 Ảnh Danh Mục</h2>
    <?php
    $sql = "SELECT id, name, image FROM categories ORDER BY id";
    $result = $conn->query($sql);
    
    $cat_total = 0;
    $cat_found = 0;
    $cat_missing = 0;
    $cat_external = 0;
    $cat_empty = 0;
    
    echo "<table>";
    echo "<tr><th>ID</th><th>Tên DM</th><th>Giá trị trong DB</th><th>Trạng thái</th><th>Preview</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $cat_total++;
        $img = $row['image'];
        $status = '';
        $preview = '';
        
        if (empty($img)) {
            $cat_empty++;
            $status = '<span class="warning">⚠️ Chưa có ảnh</span>';
        } elseif (preg_match('#^https?://#i', $img)) {
            $cat_external++;
            $status = '<span class="ok">✅ URL bên ngoài</span>';
            $preview = "<img src='$img' class='img-preview'>";
        } else {
            $localPath = $categories_dir . basename($img);
            if (file_exists($localPath)) {
                $cat_found++;
                $status = '<span class="ok">✅ Tìm thấy</span>';
                $imgUrl = SITE_URL . '/assets/images/categories/' . basename($img);
                $preview = "<img src='$imgUrl' class='img-preview'>";
            } else {
                $cat_missing++;
                $status = '<span class="error">❌ THIẾU FILE</span>';
            }
        }
        
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td style='font-size:12px;'>" . htmlspecialchars($img ?: '(trống)') . "</td>";
        echo "<td>$status</td>";
        echo "<td>$preview</td>";
        echo "</tr>";
    }
    echo "</table>";
    ?>

    <div class="summary">
        <h3>📊 Tổng kết Danh mục</h3>
        <p>Tổng số: <strong><?php echo $cat_total; ?></strong></p>
        <p class="ok">✅ Tìm thấy file: <strong><?php echo $cat_found; ?></strong></p>
        <p class="ok">🌐 URL bên ngoài: <strong><?php echo $cat_external; ?></strong></p>
        <p class="error">❌ Thiếu file: <strong><?php echo $cat_missing; ?></strong></p>
    </div>

    <!-- Danh sách file thực tế trong thư mục -->
    <h2>📂 File thực tế trong thư mục</h2>
    <h3>assets/images/products/</h3>
    <ul>
    <?php
    $files = glob($products_dir . '*.*');
    if (empty($files)) {
        echo "<li class='warning'>Thư mục trống!</li>";
    } else {
        foreach ($files as $file) {
            echo "<li>" . basename($file) . " (" . round(filesize($file)/1024, 1) . " KB)</li>";
        }
    }
    ?>
    </ul>

    <h3>assets/images/categories/</h3>
    <ul>
    <?php
    $files = glob($categories_dir . '*.*');
    if (empty($files)) {
        echo "<li class='warning'>Thư mục trống!</li>";
    } else {
        foreach ($files as $file) {
            echo "<li>" . basename($file) . " (" . round(filesize($file)/1024, 1) . " KB)</li>";
        }
    }
    ?>
    </ul>

    <hr>
    <p><a href="<?php echo SITE_URL; ?>/admin/index.php" class="btn">← Về Dashboard Admin</a></p>
</body>
</html>