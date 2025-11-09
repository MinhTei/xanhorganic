<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$current_page = basename($_SERVER['PHP_SELF'], '.php');
$cart_count = getCartCount();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Thực phẩm hữu cơ chứng nhận quốc tế</title>
    
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">     
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                <div class="top-bar-left">
                    <i class="fas fa-phone"></i> Hotline: 1900 1234
                    <span class="divider">|</span>
                    <i class="fas fa-envelope"></i> info@xanhorganic.com
                </div>
                <div class="top-bar-right">
                    <?php if (isLoggedIn()): ?>
                        <a href="<?php echo SITE_URL; ?>/profile.php">
                            <i class="fas fa-user"></i> Xin chào, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?>
                        </a>
                        <span class="divider">|</span>
                        <a href="<?php echo SITE_URL; ?>/logout.php">
                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                        </a>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/login.php">
                            <i class="fas fa-sign-in-alt"></i> Đăng nhập
                        </a>
                        <span class="divider">|</span>
                        <a href="<?php echo SITE_URL; ?>/register.php">
                            <i class="fas fa-user-plus"></i> Đăng ký
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="<?php echo SITE_URL; ?>">
                        <h1><i class="fas fa-leaf"></i> Xanh Organic</h1>
                        <p>Thực phẩm hữu cơ chứng nhận</p>
                    </a>
                </div>

                <div class="search-box">
                    <form action="<?php echo SITE_URL; ?>/products.php" method="GET">
                        <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>

                <div class="header-right">
                    <div class="cart-icon">
                        <a href="<?php echo SITE_URL; ?>/cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <nav class="navbar">
        <div class="container">
            <ul class="nav-menu">
                <li><a href="<?php echo SITE_URL; ?>" class="<?php echo $current_page == 'index' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Trang chủ
                </a></li>
                <li><a href="<?php echo SITE_URL; ?>/products.php" class="<?php echo $current_page == 'products' ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i> Sản phẩm
                </a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle">
                        <i class="fas fa-list"></i> Danh mục <i class="fas fa-chevron-down"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <?php
                        // Sử dụng hàm getCategories() một lần nữa để lấy danh mục cho menu
                        $categories = getCategories();
                        foreach ($categories as $cat):
                        ?>
                        <li><a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $cat['id']; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li><a href="<?php echo SITE_URL; ?>/about.php" class="<?php echo $current_page == 'about' ? 'active' : ''; ?>">
                    <i class="fas fa-info-circle"></i> Giới thiệu
                </a></li>
                <li><a href="<?php echo SITE_URL; ?>/contact.php" class="<?php echo $current_page == 'contact' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope"></i> Liên hệ
                </a></li>
            </ul>
            
            <div class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <main class="main-content">