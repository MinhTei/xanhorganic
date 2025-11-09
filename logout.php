<?php
require_once 'includes/config.php';

// Xóa session
session_unset();
session_destroy();

// Redirect về trang chủ
redirect('index.php');
?>