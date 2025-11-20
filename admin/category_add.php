<?php
require_once '../includes/config.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    redirect('login.php');
}

require_once '../includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $image_name = '';

    // Validate data
    if (empty($name)) {
        $error = 'Vui lòng điền tên danh mục.';
    }

    // Image upload handling (robust, same approach as products)
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
                    $new_image_name = createSlug($name ?: 'category') . '-' . time() . '-' . bin2hex(random_bytes(5)) . '.' . $ext;
                    $upload_dir = __DIR__ . '/../assets/images/categories/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                    $upload_path = $upload_dir . $new_image_name;

                    if (move_uploaded_file($tmp, $upload_path)) {
                        @chmod($upload_path, 0644);
                        $image_name = $new_image_name;
                    } else {
                        $error = 'Không thể lưu file ảnh lên server. Vui lòng kiểm tra quyền ghi.';
                    }
                }
            }
        }
    }

    if (empty($error)) {
        $sql = "INSERT INTO categories (name, description, image) 
                VALUES (?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sss",
            $name,
            $description,
            $image_name
        );

        if ($stmt->execute()) {
            $success = 'Thêm danh mục thành công!';
            $_POST = []; // Clear form
        } else {
            $error = 'Có lỗi xảy ra khi thêm danh mục: ' . $conn->error;
        }
    }
}
?>

<div class="container">
    <h1 style="color: #2d5016; margin-bottom: 20px;"><i class="fas fa-plus"></i> Thêm Danh Mục Mới</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Tên danh mục <span style="color: red;">*</span></label>
                <input type="text" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="description">Mô tả</label>
                <textarea id="description" name="description" rows="5"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="image">Ảnh đại diện danh mục</label>
                <input type="file" id="image" name="image" accept="image/*">
                <small>Chọn ảnh có định dạng .jpg, .png, .gif, .webp và dung lượng dưới 5MB.</small>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 15px;">
                <button type="submit" class="btn-submit" style="background-color: #28a745;">
                    <i class="fas fa-save"></i> Lưu Danh Mục
                </button>
                <a href="categories.php" class="btn" style="background-color: #6c757d; color: white; padding: 15px;">
                    <i class="fas fa-arrow-left"></i> Quay Lại
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
