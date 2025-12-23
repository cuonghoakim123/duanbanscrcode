<?php
require_once '../config/config.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

// Lấy tên trang hiện tại để highlight menu
$current_page = basename($_SERVER['PHP_SELF']);

// Tính toán đường dẫn CSS - đảm bảo đúng từ bất kỳ file admin nào
$css_file_path = dirname(__DIR__) . '/assets/css/admin.css';
$css_version = file_exists($css_file_path) ? filemtime($css_file_path) : time();

// Lấy script đang chạy để tính base path - tương thích cả Windows và Linux
$script_name = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$script_dir = dirname($script_name);
// Normalize đường dẫn - chuyển backslash thành forward slash (tương thích Linux hosting)
$script_dir = str_replace('\\', '/', $script_dir);
// Loại bỏ /admin nếu có để lấy base
$base_path = str_replace('/admin', '', $script_dir);
$base_path = rtrim($base_path, '/');
// Kiểm tra empty hoặc root paths - tương thích cả Windows (\ hoặc .) và Linux
if (empty($base_path) || $base_path == '\\' || $base_path == '.' || $base_path == '/') {
    $base_path = '';
} else {
    // Đảm bảo có leading slash
    if (strpos($base_path, '/') !== 0) {
        $base_path = '/' . $base_path;
    }
}

// Tạo đường dẫn CSS - dùng absolute URLs để tránh vấn đề base tag
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Sử dụng hard-coded absolute URL để tránh path confusion
$admin_css_path = 'http://localhost/duanbanscrcode/admin/assets/css/admin.css?v=' . $css_version;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title . ' - Admin' : 'Admin Panel'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $admin_css_path; ?>">
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
            <a href="index.php" class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="products.php" class="nav-link <?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i>
                <span>Sản phẩm</span>
            </a>
            <a href="categories.php" class="nav-link <?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">
                <i class="fas fa-tags"></i>
                <span>Danh mục</span>
            </a>
            <a href="users.php" class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Người dùng</span>
            </a>
            <a href="reviews.php" class="nav-link <?php echo $current_page == 'reviews.php' ? 'active' : ''; ?>">
                <i class="fas fa-star"></i>
                <span>Đánh giá</span>
            </a>
            <a href="contacts.php" class="nav-link <?php echo $current_page == 'contacts.php' ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i>
                <span>Liên hệ</span>
            </a>
            <a href="chats.php" class="nav-link <?php echo $current_page == 'chats.php' ? 'active' : ''; ?>">
                <i class="fas fa-comments"></i>
                <span>Chat với khách hàng</span>
            </a>
            <a href="templates.php" class="nav-link <?php echo $current_page == 'templates.php' || $current_page == 'template_add.php' || $current_page == 'template_edit.php' ? 'active' : ''; ?>">
                <i class="fas fa-palette"></i>
                <span>Mẫu giao diện</span>
            </a>
            <a href="news.php" class="nav-link <?php echo $current_page == 'news.php' || $current_page == 'news_add.php' || $current_page == 'news_edit.php' ? 'active' : ''; ?>">
                <i class="fas fa-newspaper"></i>
                <span>Tin tức</span>
            </a>
            <a href="services.php" class="nav-link <?php echo $current_page == 'services.php' || $current_page == 'service_add.php' || $current_page == 'service_edit.php' ? 'active' : ''; ?>">
                <i class="fas fa-concierge-bell"></i>
                <span>Dịch vụ</span>
            </a>
            <a href="settings.php" class="nav-link <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>Cài đặt</span>
            </a>
            
            <div class="sidebar-divider"></div>
            
            <a href="<?php echo $protocol . $host . $base_path . '/'; ?>" class="nav-link">
                <i class="fas fa-home"></i>
                <span>Về trang chủ</span>
            </a>
            <a href="<?php echo $protocol . $host . $base_path . '/auth/logout.php'; ?>" class="nav-link">
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
                <a href="<?php echo $protocol . $host . $base_path . '/'; ?>" class="btn-admin btn-admin-primary btn-admin-sm" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Xem website
                </a>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
