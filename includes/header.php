<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <?php 
    // Load URL helper nếu chưa có
    if (!function_exists('asset_url')) {
        require_once __DIR__ . '/../config/url_helper.php';
    }
    
    // Load avatar từ database nếu session không có
    if (isset($_SESSION['user_id']) && (!isset($_SESSION['user_avatar']) || empty($_SESSION['user_avatar']))) {
        try {
            require_once __DIR__ . '/../config/database.php';
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT avatar FROM users WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $_SESSION['user_id']);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!empty($user['avatar'])) {
                    $avatar_url = trim($user['avatar']);
                    // Xử lý đường dẫn avatar
                    if (preg_match('/^https?:\/\//', $avatar_url)) {
                        // URL đầy đủ, giữ nguyên
                        $_SESSION['user_avatar'] = $avatar_url;
                    } elseif (preg_match('/^\/uploads\//', $avatar_url)) {
                        // Đường dẫn tuyệt đối từ root
                        $_SESSION['user_avatar'] = SITE_URL . $avatar_url;
                    } elseif (strpos($avatar_url, 'uploads/') !== false) {
                        // Chứa uploads/ nhưng không bắt đầu bằng /
                        if (strpos($avatar_url, SITE_URL) === false) {
                            $_SESSION['user_avatar'] = SITE_URL . '/' . ltrim($avatar_url, '/');
                        } else {
                            $_SESSION['user_avatar'] = $avatar_url;
                        }
                    } else {
                        // Chỉ là tên file, kiểm tra xem file có tồn tại không
                        $possible_path = __DIR__ . '/../uploads/users/' . basename($avatar_url);
                        if (file_exists($possible_path)) {
                            $_SESSION['user_avatar'] = SITE_URL . '/uploads/users/' . basename($avatar_url);
                        } else {
                            // File không tồn tại, dùng null để hiển thị placeholder
                            $_SESSION['user_avatar'] = null;
                        }
                    }
                } else {
                    $_SESSION['user_avatar'] = null;
                }
            }
        } catch (Exception $e) {
            // Nếu có lỗi, set null để hiển thị icon mặc định
            $_SESSION['user_avatar'] = null;
        }
    }
    ?>
    <link rel="stylesheet" href="<?php echo asset_url('assets/css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('assets/css/landing.css'); ?>">
    <?php if(isset($extra_css)) echo $extra_css; ?>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="<?php echo SITE_URL; ?>">
                <i class="fas fa-laptop-code"></i> <?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>"><i class="fas fa-home"></i> Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/about.php"><i class="fas fa-info-circle"></i> Giới thiệu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/templates.php"><i class="fas fa-th"></i> Mẫu giao diện</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/services.php"><i class="fas fa-cogs"></i> Dịch vụ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/products.php"><i class="fas fa-shopping-bag"></i> Sản phẩm</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/news.php"><i class="fas fa-newspaper"></i> Tin tức</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/contact.php"><i class="fas fa-phone-alt"></i> Liên hệ</a>
                    </li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <!-- Đã xóa giỏ hàng -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <?php 
                                // Lấy avatar từ session hoặc hiển thị placeholder
                                $user_avatar = isset($_SESSION['user_avatar']) && !empty($_SESSION['user_avatar']) && $_SESSION['user_avatar'] !== 'null' ? $_SESSION['user_avatar'] : null;
                                
                                // Load hàm getAvatarPlaceholder nếu chưa có
                                if (!function_exists('getAvatarPlaceholder')) {
                                    require_once __DIR__ . '/../config/config.php';
                                }
                                
                                // Đảm bảo getAvatarPlaceholder được gọi đúng cách
                                $placeholder_url = getAvatarPlaceholder(32);
                                
                                if ($user_avatar && $user_avatar !== 'null'): ?>
                                    <img src="<?php echo htmlspecialchars($user_avatar); ?>" 
                                         alt="Avatar" 
                                         class="rounded-circle me-2" 
                                         style="width: 32px; height: 32px; object-fit: cover; border: 2px solid #e0e0e0; background: #f0f0f0; display: block;"
                                         onerror="this.onerror=null; this.src='<?php echo htmlspecialchars($placeholder_url); ?>';">
                                <?php else: ?>
                                    <img src="<?php echo htmlspecialchars($placeholder_url); ?>" 
                                         alt="Avatar" 
                                         class="rounded-circle me-2" 
                                         style="width: 32px; height: 32px; object-fit: cover; border: 2px solid #e0e0e0; background: #f0f0f0; display: block;">
                                <?php endif; ?>
                                <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li class="px-3 py-2 border-bottom">
                                    <small class="text-muted"><?php echo $_SESSION['user_email']; ?></small>
                                </li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/profile.php"><i class="fas fa-user"></i> Thông tin cá nhân</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="initAdminChat()"><i class="fas fa-comments"></i> Chat với admin</a></li>
                                <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-primary" href="<?php echo SITE_URL; ?>/admin"><i class="fas fa-tachometer-alt"></i> Quản trị</a></li>
                                    <li><a class="dropdown-item text-primary" href="<?php echo SITE_URL; ?>/admin/chats.php"><i class="fas fa-comments"></i> Quản lý chat</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo SITE_URL; ?>/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-primary px-3 ms-2" href="<?php echo SITE_URL; ?>/auth/login.php">
                                <i class="fas fa-sign-in-alt"></i> Đăng nhập
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary text-white px-3 ms-2" href="<?php echo SITE_URL; ?>/auth/register.php">
                                <i class="fas fa-user-plus"></i> Đăng ký
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
