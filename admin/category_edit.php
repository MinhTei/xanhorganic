<?php
require_once '../includes/config.php';

if (!isAdmin()) {
    redirect('login.php');
}

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($category_id <= 0) {
    redirect('admin/categories.php');
}

// Lấy thông tin danh mục
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();

if (!$category) {
    redirect('admin/categories.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];
    $image_name = $category['image']; // Giữ ảnh cũ

    if (empty($name)) {
        $error = 'Vui lòng điền tên danh mục.';
    }

    // Xử lý upload ảnh mới
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024;

        if (in_array($_FILES['image']['type'], $allowed_types) && $_FILES['image']['size'] <= $max_size) {
            $upload_dir = __DIR__ . '/../assets/images/categories/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $image_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_image_name = createSlug($name) . '-' . time() . '.' . $image_extension;
            $upload_path = $upload_dir . $new_image_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Xóa ảnh cũ
                if (!empty($image_name) && file_exists($upload_dir . $image_name)) {
                    unlink($upload_dir . $image_name);
                }
                $image_name = 'images/categories/' . $new_image_name;
            } else {
                $error = 'Không thể tải ảnh lên.';
            }
        } else {
            $error = 'Định dạng ảnh không hợp lệ hoặc dung lượng quá lớn (tối đa 5MB).';
        }
    }

    if (empty($error)) {
        $sql = "UPDATE categories SET name = ?, description = ?, image = ?, status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $name, $description, $image_name, $status, $category_id);

        if ($stmt->execute()) {
            $success = 'Cập nhật danh mục thành công!';
            // Reload category data
            $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->bind_param("i", $category_id);
            $stmt->execute();
            $category = $stmt->get_result()->fetch_assoc();
        } else {
            $error = 'Có lỗi xảy ra: ' . $conn->error;
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <h1 style="color: #2d5016; margin-bottom: 20px;">
        <i class="fas fa-edit"></i> Chỉnh Sửa Danh Mục
    </h1>

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
                <input type="text" id="name" name="name" required value="<?php echo safe_html($category['name']); ?>">
            </div>

            <div class="form-group">
                <label for="description">Mô tả</label>
                <textarea id="description" name="description" rows="5"><?php echo safe_html($category['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="status">Trạng thái</label>
                <select id="status" name="status">
                    <option value="active" <?php echo $category['status'] == 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                    <option value="inactive" <?php echo $category['status'] == 'inactive' ? 'selected' : ''; ?>>Tạm dừng</option>
                </select>
            </div>

            <div class="form-group">
                <label for="image">Ảnh đại diện danh mục</label>
                
                <?php if (!empty($category['image'])): ?>
                <div style="margin-bottom: 15px;">
                    <p style="color: #666; margin-bottom: 10px;">Ảnh hiện tại:</p>
                    <?php
                    $current_image = SITE_URL . '/assets/' . safe_html($category['image']);
                    $image_path = __DIR__ . '/../assets/' . $category['image'];
                    
                    if (!file_exists($image_path)) {
                        $current_image = 'https://via.placeholder.com/200?text=No+Image';
                    }
                    ?>
                    <img src="<?php echo $current_image; ?>" 
                         alt="<?php echo safe_html($category['name']); ?>"
                         style="max-width: 200px; height: auto; border-radius: 5px; border: 2px solid #ddd;">
                </div>
                <?php endif; ?>
                
                <input type="file" id="image" name="image" accept="image/*">
                <small>Để trống nếu không muốn thay đổi ảnh. Định dạng: .jpg, .png, .gif, .webp (tối đa 5MB)</small>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 15px;">
                <button type="submit" class="btn-submit" style="background-color: #28a745;">
                    <i class="fas fa-save"></i> Cập Nhật
                </button>
                <a href="<?php echo SITE_URL; ?>/admin/categories.php" 
                   class="btn" 
                   style="background-color: #6c757d; color: white; padding: 15px;">
                    <i class="fas fa-arrow-left"></i> Quay Lại
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>