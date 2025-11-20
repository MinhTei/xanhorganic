<?php
/**
 * admin/assign_images.php
 * Utility script to assign remote image URLs (Unsplash) to products and categories
 * for testing. Run only in development. Requires admin session.
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdmin()) {
    echo "Access denied. Admin only.";
    exit;
}

// Confirmation check to avoid accidental runs
if (!isset($_GET['confirm'])) {
    echo "This script will assign remote Unsplash images to products and categories for testing.<br>";
    echo "To proceed, open: <a href='?confirm=1'>?confirm=1</a>";
    exit;
}

// Assign images to products: for each active product, set image to a Unsplash query using product name
$updatedProducts = 0;
$sql = "SELECT id, name FROM products WHERE status='active'";
$res = $conn->query($sql);
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $id = (int)$row['id'];
        $name = $row['name'] ?: 'product';
        // Use a reasonably sized image
        $url = 'https://source.unsplash.com/800x600/?' . rawurlencode($name);

        $u = $conn->prepare("UPDATE products SET image = ? WHERE id = ?");
        $u->bind_param('si', $url, $id);
        if ($u->execute()) $updatedProducts++;
    }
}

// Assign images to categories: for each active category, set image to Unsplash by category name
$updatedCategories = 0;
$sql2 = "SELECT id, name FROM categories WHERE status='active'";
$res2 = $conn->query($sql2);
if ($res2 && $res2->num_rows > 0) {
    while ($row = $res2->fetch_assoc()) {
        $id = (int)$row['id'];
        $name = $row['name'] ?: 'category';
        $url = 'https://source.unsplash.com/1200x800/?' . rawurlencode($name);

        $u = $conn->prepare("UPDATE categories SET image = ? WHERE id = ?");
        $u->bind_param('si', $url, $id);
        if ($u->execute()) $updatedCategories++;
    }
}

echo "Assigned images to products: " . $updatedProducts . "\n";
echo "Assigned images to categories: " . $updatedCategories . "\n";

echo "<p>Done. Visit <a href=\"../index.php\">site home</a> or <a href=\"../products.php\">products</a> to view.</p>";

echo "<p>To rollback (clear image fields), visit <a href='?confirm=1&rollback=1'>?confirm=1&rollback=1</a></p>";

// Rollback handler
if (isset($_GET['rollback'])) {
    $conn->query("UPDATE products SET image = ''");
    $conn->query("UPDATE categories SET image = ''");
    echo "<p>Rolled back: cleared image fields for products and categories.</p>";
}

?>
