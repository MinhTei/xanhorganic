<?php
require_once '../includes/config.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    redirect('login.php');
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id <= 0) {
    redirect('admin/products.php');
}

// Lấy thông tin sản phẩm hiện tại
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    redirect('admin/products.php');
}

require_once '../includes/header.php';

$categories = getCategories();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $sale_price = !empty($_POST['sale_price']) ? filter_var($_POST['sale_price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
    $category_id = (int)$_POST['category_id'];
    $unit = trim($_POST['unit']);
    $stock = (int)$_POST['stock'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $certification = trim($_POST['certification']);
    $origin = trim($_POST['origin']);
    $image_name = $product['image']; // Giữ ảnh cũ nếu không có ảnh mới

    // Validate data
    if (empty($name) || empty($price) || empty($category_id) || empty($unit)) {
        $error = 'Vui lòng điền các trường bắt buộc (*).';
    }

    // Xử lý upload ảnh mới (improved validation)
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $max_size = 5 * 1024 * 1024; // 5MB
        $allowed_exts = ['jpg','jpeg','png','gif','webp'];

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Lỗi tải file (code: ' . $_FILES['image']['error'] . ').';
        } elseif ($_FILES['image']['size'] > $max_size) {
            $error = 'Dung lượng ảnh quá lớn. Vui lòng chọn file <= 5MB.';
        } else {
            $tmp = $_FILES['image']['tmp_name'];
            $origName = $_FILES['image']['name'];
            $info = @getimagesize($tmp);
            if ($info === false) {
                $error = 'Tệp không phải là ảnh hợp lệ.';
            } else {
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed_exts, true)) {
                    $error = 'Định dạng ảnh không được hỗ trợ.';
                } else {
                    $new_image_name = createSlug($name ?: 'product') . '-' . time() . '-' . bin2hex(random_bytes(5)) . '.' . $ext;
                    $upload_dir = __DIR__ . '/../assets/images/products/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                    $upload_path = $upload_dir . $new_image_name;

                    if (move_uploaded_file($tmp, $upload_path)) {
                        @chmod($upload_path, 0644);
                        // Xóa ảnh cũ nếu upload thành công và khác tên
                        if (!empty($image_name) && $image_name !== $new_image_name && file_exists(__DIR__ . '/../assets/images/products/' . $image_name)) {
                            @unlink(__DIR__ . '/../assets/images/products/' . $image_name);
                        }
                            // Lưu tên file vào DB
                            $image_name = $new_image_name;
                    } else {
                        $error = 'Không thể lưu file ảnh lên server. Vui lòng kiểm tra quyền ghi.';
                    }
                }
            }
        }
    }

    if (empty($error)) {
        $sql = "UPDATE products SET 
                    name = ?, 
                    description = ?, 
                    price = ?, 
                    sale_price = ?, 
                    category_id = ?, 
                    unit = ?, 
                    stock = ?, 
                    featured = ?, 
                    certification = ?, 
                    origin = ?, 
                    image = ? 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssddisiisssi",
            $name,
            $description,
            $price,
            $sale_price,
            $category_id,
            $unit,
            $stock,
            $featured,
            $certification,
            $origin,
            $image_name,
            $product_id
        );

        if ($stmt->execute()) {
            $success = 'Cập nhật sản phẩm thành công!';
            // Tải lại dữ liệu sản phẩm sau khi cập nhật
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
        } else {
            $error = 'Có lỗi xảy ra khi cập nhật sản phẩm: ' . $conn->error;
        }
    }
}
?>

<div class="container">
    <h1 style="color: #2d5016; margin-bottom: 20px;"><i class="fas fa-edit"></i> Chỉnh Sửa Sản Phẩm</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Tên sản phẩm <span style="color: red;">*</span></label>
                <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($product['name']); ?>">
            </div>

            <div class="form-group">
                <label for="description">Mô tả</label>
                <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="price">Giá gốc (VNĐ) <span style="color: red;">*</span></label>
                    <input type="number" id="price" name="price" required step="1000" value="<?php echo htmlspecialchars($product['price']); ?>">
                </div>
                <div class="form-group">
                    <label for="sale_price">Giá khuyến mãi (VNĐ)</label>
                    <input type="number" id="sale_price" name="sale_price" step="1000" value="<?php echo htmlspecialchars($product['sale_price']); ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="category_id">Danh mục <span style="color: red;">*</span></label>
                    <select id="category_id" name="category_id" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="unit">Đơn vị tính <span style="color: red;">*</span></label>
                    <input type="text" id="unit" name="unit" required placeholder="Vd: Kg, Hộp, Bó" value="<?php echo htmlspecialchars($product['unit']); ?>">
                </div>
                <div class="form-group">
                    <label for="stock">Tồn kho</label>
                    <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="certification">Chứng nhận</label>
                    <input type="text" id="certification" name="certification" placeholder="Vd: USDA, EU Organic" value="<?php echo htmlspecialchars($product['certification']); ?>">
                </div>
                <div class="form-group">
                    <label for="origin">Xuất xứ</label>
                    <input type="text" id="origin" name="origin" placeholder="Vd: Đà Lạt, Việt Nam" value="<?php echo htmlspecialchars($product['origin']); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="image">Ảnh đại diện sản phẩm</label>
                <input type="file" id="image" name="image" accept="image/*">
                <small>Để trống nếu không muốn thay đổi ảnh hiện tại.</small>
                <?php if ($product['image']): ?>
                    <div style="margin-top: 10px;">
                        <img src="<?php echo getProductImageUrl($product); ?>" alt="Ảnh hiện tại" style="max-width: 150px; height: auto; border-radius: 5px;">
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="featured" value="1" <?php echo $product['featured'] ? 'checked' : ''; ?>>
                    Đặt làm sản phẩm nổi bật
                </label>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 15px;">
                <button type="submit" class="btn-submit" style="background-color: #28a745;">
                    <i class="fas fa-save"></i> Cập Nhật
                </button>
                <a href="products.php" class="btn" style="background-color: #6c757d; color: white; padding: 15px;">
                    <i class="fas fa-arrow-left"></i> Quay Lại
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
