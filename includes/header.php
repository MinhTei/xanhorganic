<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$current_page = basename($_SERVER['PHP_SELF'], '.php');
$cart_count = getCartCount();
$wishlist_count = isLoggedIn() ? getWishlistCount() : 0;
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
    <!-- Top Bar -->
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
                            <i class="fas fa-user"></i> Xin chào, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>
                        </a>
                        <span class="divider">|</span>
                        <a href="<?php echo SITE_URL; ?>/wishlist.php" title="Sản phẩm yêu thích">
                            <i class="fas fa-heart"></i> Yêu thích (<?php echo $wishlist_count; ?>)
                        </a>
                        <span class="divider">|</span>
                        <a href="<?php echo SITE_URL; ?>/history.php" title="Lịch sử mua hàng">
                            <i class="fas fa-history"></i> Lịch sử
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

    <!-- Header -->
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
                    <?php if (isLoggedIn()): ?>
                    <!-- Wishlist Icon -->
                    <div class="cart-icon" style="margin-right: 15px;">
                        <a href="<?php echo SITE_URL; ?>/wishlist.php" title="Danh sách yêu thích">
                            <i class="fas fa-heart" style="color: #e74c3c;"></i>
                            <?php if ($wishlist_count > 0): ?>
                            <span class="cart-count" style="background: #e74c3c;"><?php echo $wishlist_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                    
                    <!-- Order History Icon -->
                    <div class="cart-icon" style="margin-right: 15px;">
                        <a href="<?php echo SITE_URL; ?>/history.php" title="Lịch sử mua hàng">
                            <i class="fas fa-history" style="color: #90c33c;"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Cart Icon -->
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

    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <ul class="nav-menu">
                <!-- Danh mục (đầu tiên) -->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle">
                        <i class="fas fa-list"></i> Danh mục <i class="fas fa-chevron-down"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <?php
                        $categories = getCategories();
                        foreach ($categories as $cat):
                        ?>
                        <li><a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $cat['id']; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                
                <!-- Trang chủ -->
                <li><a href="<?php echo SITE_URL; ?>" class="<?php echo $current_page == 'index' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Trang chủ
                </a></li>
                
                <!-- Sản phẩm -->
                <li><a href="<?php echo SITE_URL; ?>/products.php" class="<?php echo $current_page == 'products' ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i> Sản phẩm
                </a></li>
                
                <!-- Tin tức -->
                <li><a href="<?php echo SITE_URL; ?>/news.php" class="<?php echo $current_page == 'news' ? 'active' : ''; ?>">
                    <i class="fas fa-newspaper"></i> Tin tức
                </a></li>
                
                <!-- Giới thiệu -->
                <li><a href="<?php echo SITE_URL; ?>/about.php" class="<?php echo $current_page == 'about' ? 'active' : ''; ?>">
                    <i class="fas fa-info-circle"></i> Giới thiệu
                </a></li>
                
                <!-- Liên hệ -->
                <li><a href="<?php echo SITE_URL; ?>/contact.php" class="<?php echo $current_page == 'contact' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope"></i> Liên hệ
                </a></li>
                
                <!-- Menu Quản lý (chỉ hiện với Admin) -->
                <?php if (isAdmin()): ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" style="background: rgba(255,255,255,0.1);">
                        <i class="fas fa-cog"></i> Quản lý <i class="fas fa-chevron-down"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo SITE_URL; ?>/admin/index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a></li>
                        <li><a href="<?php echo SITE_URL; ?>/admin/products.php">
                            <i class="fas fa-box"></i> Quản lý sản phẩm
                        </a></li>
                        <li><a href="<?php echo SITE_URL; ?>/admin/categories.php">
                            <i class="fas fa-tags"></i> Quản lý danh mục
                        </a></li>
                        <li><a href="<?php echo SITE_URL; ?>/admin/orders.php">
                            <i class="fas fa-shopping-cart"></i> Quản lý đơn hàng
                        </a></li>
                        <li><a href="<?php echo SITE_URL; ?>/admin/users.php">
                            <i class="fas fa-users"></i> Quản lý người dùng
                        </a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
            
            <div class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <main class="main-content">
    
    <!-- Toast Notification Container -->
    <div id="toast-container"></div>