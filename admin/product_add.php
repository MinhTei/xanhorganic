<?php
require_once '../includes/config.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    redirect('login.php');
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
    $image_name = '';

    // Validate data
    if (empty($name) || empty($price) || empty($category_id) || empty($unit)) {
        $error = 'Vui lòng điền các trường bắt buộc (*).';
    }

    // Image upload handling (improved validation)
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $max_size = 5 * 1024 * 1024; // 5MB
        $allowed_exts = ['jpg','jpeg','png','gif','webp'];

        // Check upload error
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Lỗi tải file (code: ' . $_FILES['image']['error'] . ').';
        } elseif ($_FILES['image']['size'] > $max_size) {
            $error = 'Dung lượng ảnh quá lớn. Vui lòng chọn file <= 5MB.';
        } else {
            $tmp = $_FILES['image']['tmp_name'];
            $origName = $_FILES['image']['name'];

            // Verify it's an image
            $info = @getimagesize($tmp);
            if ($info === false) {
                $error = 'Tệp không phải là ảnh hợp lệ.';
            } else {
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed_exts, true)) {
                    $error = 'Định dạng ảnh không được hỗ trợ.';
                } else {
                    
                    $image_name = createSlug($name ?: 'product') . '-' . time() . '-' . bin2hex(random_bytes(5)) . '.' . $ext;
                    $upload_dir = __DIR__ . '/../assets/images/products/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                    $upload_path = $upload_dir . $image_name;

                    if (!move_uploaded_file($tmp, $upload_path)) {
                        $error = 'Không thể lưu file ảnh lên server. Vui lòng kiểm tra quyền ghi.';
                            $image_name = '';
                    } else {
                        @chmod($upload_path, 0644);
                          
                    }
                }
            }
        }
    }

    if (empty($error)) {
        $sql = "INSERT INTO products (name, description, price, sale_price, category_id, unit, stock, featured, certification, origin, image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssddisiisss",
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
            $image_name 
        );

        if ($stmt->execute()) {
            $success = 'Thêm sản phẩm thành công!';
            $_POST = []; // Clear form
        } else {
            $error = 'Có lỗi xảy ra khi thêm sản phẩm: ' . $conn->error;
        }
    }
}
?>

<div class="container">
    <h1 style="color: #2d5016; margin-bottom: 20px;"><i class="fas fa-plus"></i> Thêm Sản Phẩm Mới</h1>

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
                <input type="text" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="description">Mô tả</label>
                <textarea id="description" name="description" rows="5"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="price">Giá gốc (VNĐ) <span style="color: red;">*</span></label>
                    <input type="number" id="price" name="price" required step="1000" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="sale_price">Giá khuyến mãi (VNĐ)</label>
                    <input type="number" id="sale_price" name="sale_price" step="1000" value="<?php echo isset($_POST['sale_price']) ? htmlspecialchars($_POST['sale_price']) : ''; ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="category_id">Danh mục <span style="color: red;">*</span></label>
                    <select id="category_id" name="category_id" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="unit">Đơn vị tính <span style="color: red;">*</span></label>
                    <input type="text" id="unit" name="unit" required placeholder="Vd: Kg, Hộp, Bó" value="<?php echo isset($_POST['unit']) ? htmlspecialchars($_POST['unit']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="stock">Tồn kho</label>
                    <input type="number" id="stock" name="stock" value="<?php echo isset($_POST['stock']) ? htmlspecialchars($_POST['stock']) : '0'; ?>">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="certification">Chứng nhận</label>
                    <input type="text" id="certification" name="certification" placeholder="Vd: USDA, EU Organic" value="<?php echo isset($_POST['certification']) ? htmlspecialchars($_POST['certification']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="origin">Xuất xứ</label>
                    <input type="text" id="origin" name="origin" placeholder="Vd: Đà Lạt, Việt Nam" value="<?php echo isset($_POST['origin']) ? htmlspecialchars($_POST['origin']) : ''; ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="image">Ảnh đại diện sản phẩm</label>
                <input type="file" id="image" name="image" accept="image/*">
                <small>Chọn ảnh có định dạng .jpg, .png, .gif, .webp và dung lượng dưới 5MB.</small>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="featured" value="1" <?php echo isset($_POST['featured']) ? 'checked' : ''; ?>>
                    Đặt làm sản phẩm nổi bật
                </label>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 15px;">
                <button type="submit" class="btn-submit" style="background-color: #28a745;">
                    <i class="fas fa-save"></i> Lưu Sản Phẩm
                </button>
                <a href="products.php" class="btn" style="background-color: #6c757d; color: white; padding: 15px;">
                    <i class="fas fa-arrow-left"></i> Quay Lại
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
