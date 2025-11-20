<?php
// Cấu hình kết nối database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); 
define('DB_NAME', 'xanh_organic');

// Cấu hình website
define('SITE_NAME', 'Xanh Organic');
define('SITE_URL', 'http://localhost/xanhorganic');
define('ADMIN_EMAIL', 'admin@xanhorganic.com');

// Kết nối database
try {
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
} catch (Exception $e) {
 die("Lỗi kết nối database: " . $e->getMessage());
}

// Khởi động session
if (session_status() === PHP_SESSION_NONE) {
 session_start();
}

// Hàm escape string để bảo mật
function escape($string) {
global $conn;
if ($string === null) {
return '';
 }
return $conn->real_escape_string((string)$string);
}

// Hàm redirect
function redirect($url) {
 if (!headers_sent()) {
 header("Location: " . SITE_URL . "/" . $url, true, 302);
 exit();
} else {
 $full_url = SITE_URL . "/" . $url;
 echo "<script>window.location.href='" . $full_url . "';</script>";
 exit(); 
 }
}

// Hàm kiểm tra đăng nhập
function isLoggedIn() {
return isset($_SESSION['user_id']);
}

// Hàm kiểm tra admin
function isAdmin() {
 return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

// Hàm format tiền VND
function formatMoney($amount) {
return number_format($amount, 0, ',', '.') . 'đ';
}

// Hàm tạo slug từ chuỗi tiếng Việt
function createSlug($string) {
$string = trim(mb_strtolower($string ?? ''));
$string = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $string);
$string = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $string); 
$string = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $string);
 $string = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $string);
 $string = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $string);
 $string = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $string);
 $string = preg_replace('/(đ)/', 'd', $string);
 $string = preg_replace('/[^a-z0-9-\s]/', '', $string);
 $string = preg_replace('/([\s]+)/', '-', $string);
 return $string;
}

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');
// Debug mode (set to true for development to show reset links)
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', false);
}
// SMTP / Mail settings (leave empty to use PHP mail())
if (!defined('MAIL_FROM')) define('MAIL_FROM', 'no-reply@xanhorganic.local');
if (!defined('MAIL_FROM_NAME')) define('MAIL_FROM_NAME', 'Xanh Organic');
if (!defined('SMTP_HOST')) define('SMTP_HOST', '');
if (!defined('SMTP_USER')) define('SMTP_USER', '');
if (!defined('SMTP_PASS')) define('SMTP_PASS', '');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 587);
if (!defined('SMTP_SECURE')) define('SMTP_SECURE', 'tls'); // 'tls' or 'ssl' or ''
// KHÔNG CÓ THẺ ĐÓNG PHP 
?>