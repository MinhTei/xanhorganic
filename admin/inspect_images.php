<?php
/**
 * admin/inspect_images.php
 * Debug helper: liệt kê một vài sản phẩm và giá trị trường `image` để kiểm tra
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdmin()) {
    echo "Access denied. Admin only.";
    exit;
}

echo "<h2>Products image inspect</h2>";

$res = $conn->query("SELECT id, name, image FROM products ORDER BY id ASC LIMIT 30");
if (!$res) {
    echo "DB error: " . htmlspecialchars($conn->error);
    exit;
}

echo "<table border=1 cellpadding=8 cellspacing=0 style='border-collapse:collapse;'>";
echo "<tr><th>ID</th><th>Name</th><th>Image field (raw)</th><th>Rendered</th></tr>";
while ($row = $res->fetch_assoc()) {
    $id = (int)$row['id'];
    $name = htmlspecialchars($row['name']);
    $img = $row['image'];
    $img_esc = htmlspecialchars($img);

    // Build a direct image tag for quick click/test
    $img_tag = '';
    if (!empty($img)) {
        $img_tag = '<img src="' . $img_esc . '" style="max-width:200px; max-height:150px;" alt="' . $name . '">';
    }

    echo "<tr>";
    echo "<td>{$id}</td>";
    echo "<td>{$name}</td>";
    echo "<td style='max-width:400px; word-break:break-all;'>{$img_esc}</td>";
    echo "<td>{$img_tag}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p>Notes: Click the image to open in a new tab if visible. If the field contains an Unsplash query URL (source.unsplash.com/?...), open that URL directly to see the image served.</p>";

echo "<p><a href=\"?refresh=1\">Refresh</a></p>";

?>
