<?php
require_once '../config/config.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

// Lấy tên trang hiện tại để highlight menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title . ' - Admin' : 'Admin Panel'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/admin/assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="admin-avatar">
                <?php 
                $initials = strtoupper(substr($_SESSION['user_name'], 0, 2));
                echo $initials;
                ?>
            </div>
            <h4><i class="fas fa-shield-alt"></i> Admin Panel</h4>
            <div class="admin-info">
                <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
            </div>
        </div>
        
        <nav class="sidebar-menu">
            <a href="<?php echo SITE_URL; ?>/admin" class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/products.php" class="nav-link <?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i>
                <span>Sản phẩm</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/categories.php" class="nav-link <?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">
                <i class="fas fa-tags"></i>
                <span>Danh mục</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/orders.php" class="nav-link <?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i>
                <span>Đơn hàng</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/users.php" class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Người dùng</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/reviews.php" class="nav-link <?php echo $current_page == 'reviews.php' ? 'active' : ''; ?>">
                <i class="fas fa-star"></i>
                <span>Đánh giá</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/contacts.php" class="nav-link <?php echo $current_page == 'contacts.php' ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i>
                <span>Liên hệ</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/templates.php" class="nav-link <?php echo $current_page == 'templates.php' || $current_page == 'template_add.php' || $current_page == 'template_edit.php' ? 'active' : ''; ?>">
                <i class="fas fa-palette"></i>
                <span>Mẫu giao diện</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/news.php" class="nav-link <?php echo $current_page == 'news.php' || $current_page == 'news_add.php' || $current_page == 'news_edit.php' ? 'active' : ''; ?>">
                <i class="fas fa-newspaper"></i>
                <span>Tin tức</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/services.php" class="nav-link <?php echo $current_page == 'services.php' || $current_page == 'service_add.php' || $current_page == 'service_edit.php' ? 'active' : ''; ?>">
                <i class="fas fa-concierge-bell"></i>
                <span>Dịch vụ</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/settings.php" class="nav-link <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>Cài đặt</span>
            </a>
            
            <div class="sidebar-divider"></div>
            
            <a href="<?php echo SITE_URL; ?>" class="nav-link">
                <i class="fas fa-home"></i>
                <span>Về trang chủ</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Đăng xuất</span>
            </a>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h1><?php echo isset($title) ? $title : 'Dashboard'; ?></h1>
            <div class="top-bar-actions">
                <a href="<?php echo SITE_URL; ?>" class="btn-admin btn-admin-primary btn-admin-sm" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Xem website
                </a>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
