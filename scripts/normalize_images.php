<?php
// scripts/normalize_images.php
// Usage: run from project root with: php scripts/normalize_images.php
// This script will replace stored paths like 'images/products/foo.jpg' -> 'foo.jpg'
// and 'images/categories/bar.jpg' -> 'bar.jpg'. It will prompt for confirmation before applying.

require_once __DIR__ . '/../includes/config.php';

echo "This script will normalize image paths in the database by removing 'images/products/' and 'images/categories/' prefixes.\n";
echo "It will show the number of affected rows for products and categories, then ask for confirmation.\n\n";

// Count affected products
$res = $conn->query("SELECT COUNT(*) as cnt FROM products WHERE image LIKE 'images/products/%'");
$prodCount = $res ? (int)$res->fetch_assoc()['cnt'] : 0;
$res = $conn->query("SELECT COUNT(*) as cnt FROM categories WHERE image LIKE 'images/categories/%'");
$catCount = $res ? (int)$res->fetch_assoc()['cnt'] : 0;

echo "Products with prefixed image paths: $prodCount\n";
echo "Categories with prefixed image paths: $catCount\n\n";

if ($prodCount === 0 && $catCount === 0) {
    echo "Nothing to do. Exiting.\n";
    exit(0);
}

echo "Type 'yes' to proceed and update the database: ";
$handle = fopen('php://stdin', 'r');
$line = trim(fgets($handle));
if (strtolower($line) !== 'yes') {
    echo "Aborted by user. No changes made.\n";
    exit(0);
}

// Begin transaction if supported
$conn->begin_transaction();
$errors = [];

// Update products
$updateProdSql = "UPDATE products SET image = REPLACE(image, 'images/products/', '') WHERE image LIKE 'images/products/%'";
if (!$conn->query($updateProdSql)) {
    $errors[] = 'products: ' . $conn->error;
}

// Update categories
$updateCatSql = "UPDATE categories SET image = REPLACE(image, 'images/categories/', '') WHERE image LIKE 'images/categories/%'";
if (!$conn->query($updateCatSql)) {
    $errors[] = 'categories: ' . $conn->error;
}

if ($errors) {
    $conn->rollback();
    echo "Errors occurred, transaction rolled back:\n";
    foreach ($errors as $e) echo " - $e\n";
    exit(1);
}

$conn->commit();

echo "Normalization complete.\n";
echo "You may want to verify that image files exist under 'assets/images/products' and 'assets/images/categories' and then clear browser caches.\n";

?>