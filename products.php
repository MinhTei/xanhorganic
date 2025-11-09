<?php
require_once 'includes/header.php';

// Lấy tham số lọc
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Xây dựng query
$where = ["p.status = 'active'"];
$params = [];
$types = "";

if ($category_id > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}

if ($search) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

$where_clause = implode(" AND ", $where);

// Sắp xếp
$order_by = "p.created_at DESC";
switch ($sort) {
    case 'price_asc':
        $order_by = "COALESCE(p.sale_price, p.price) ASC";
        break;
    case 'price_desc':
        $order_by = "COALESCE(p.sale_price, p.price) DESC";
        break;
    case 'name':
        $order_by = "p.name ASC";
        break;
}

// Phân trang
$per_page = 12;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// Đếm tổng sản phẩm
$count_sql = "SELECT COUNT(*) as total FROM products p WHERE $where_clause";
$count_stmt = $conn->prepare($count_sql);
if ($params) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);

// Lấy sản phẩm
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE $where_clause 
        ORDER BY $order_by 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$params[] = $per_page;
$params[] = $offset;
$types .= "ii";
$stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Lấy category hiện tại
$current_category = null;
if ($category_id > 0) {
    $cat_stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $cat_stmt->bind_param("i", $category_id);
    $cat_stmt->execute();
    $current_category = $cat_stmt->get_result()->fetch_assoc();
}
?>

<!-- Breadcrumb -->
<div class="container">
    <div style="padding: 20px 0; color: #666;">
        <a href="<?php echo SITE_URL; ?>" style="color: #90c33c;">Trang chủ</a>
        <i class="fas fa-chevron-right" style="margin: 0 10px; font-size: 12px;"></i>
        <span>Sản phẩm</span>
        <?php if ($current_category): ?>
            <i class="fas fa-chevron-right" style="margin: 0 10px; font-size: 12px;"></i>
            <span><?php echo htmlspecialchars($current_category['name']); ?></span>
        <?php endif; ?>
    </div>
</div>

<!-- Page Header -->
<div class="container">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #2d5016; font-size: 36px; margin-bottom: 10px;">
            <?php 
            if ($search) {
                echo 'Kết quả tìm kiếm: "' . htmlspecialchars($search) . '"';
            } elseif ($current_category) {
                echo htmlspecialchars($current_category['name']);
            } else {
                echo 'Tất Cả Sản Phẩm';
            }
            ?>
        </h1>
        <p style="color: #666;">Tìm thấy <?php echo $total; ?> sản phẩm</p>
    </div>
</div>

<!-- Filters -->
<div class="container">
    <div class="filters-section">
        <form method="GET" action="" class="filters-content">
            <?php if ($category_id): ?>
                <input type="hidden" name="category" value="<?php echo $category_id; ?>">
            <?php endif; ?>
            <?php if ($search): ?>
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
            <?php endif; ?>
            
            <div class="filter-group">
                <label>Danh mục:</label>
                <select name="category" onchange="this.form.submit()">
                    <option value="">Tất cả</option>
                    <?php
                    $categories = getCategories();
                    foreach ($categories as $cat):
                    ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Sắp xếp:</label>
                <select name="sort" onchange="this.form.submit()">
                    <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                    <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Giá tăng dần</option>
                    <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Giá giảm dần</option>
                    <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Tên A-Z</option>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Products Grid -->
<div class="container">
    <?php if (empty($products)): ?>
        <div style="text-align: center; padding: 50px; background: white; border-radius: 10px;">
            <i class="fas fa-box-open" style="font-size: 80px; color: #ddd; margin-bottom: 20px;"></i>
            <h3 style="color: #666; margin-bottom: 10px;">Không tìm thấy sản phẩm</h3>
            <p style="color: #999;">Vui lòng thử lại với từ khóa khác</p>
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary" style="margin-top: 20px;">
                Xem tất cả sản phẩm
            </a>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if ($product['sale_price']): ?>
                        <span class="product-badge">
                            -<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%
                        </span>
                    <?php endif; ?>
                    <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $product['id']; ?>">
                        <?php
                            $image_url = "https://via.placeholder.com/300x250?text=" . urlencode($product['name']);
                            if (!empty($product['image']) && file_exists(__DIR__ . '/assets/images/products/' . $product['image'])) {
                                $image_url = SITE_URL . '/assets/images/products/' . $product['image'];
                            }
                        ?>
                        <img src="<?php echo $image_url; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </a>
                </div>
                <div class="product-info">
                    <div class="product-category">
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </div>
                    <h3 class="product-name">
                        <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $product['id']; ?>">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </a>
                    </h3>
                    <?php if ($product['certification']): ?>
                    <div class="product-certification">
                        <span class="cert-badge"><?php echo htmlspecialchars($product['certification']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="product-price">
                        <span class="price-current">
                            <?php echo formatMoney($product['sale_price'] ?? $product['price']); ?>
                        </span>
                        <?php if ($product['sale_price']): ?>
                            <span class="price-old"><?php echo formatMoney($product['price']); ?></span>
                        <?php endif; ?>
                        <span style="color: #666; font-size: 14px;">/<?php echo $product['unit']; ?></span>
                    </div>
                    <div class="product-actions">
                        <button class="btn-add-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                            <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                        </button>
                        <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $product['id']; ?>" 
                           class="btn-view-detail">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                    <i class="fas fa-chevron-left"></i>
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
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>