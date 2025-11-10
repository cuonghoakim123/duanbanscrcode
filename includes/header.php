<?php
// Đảm bảo lang.php đã được include trước khi sử dụng
if (!function_exists('lang')) {
    require_once __DIR__ . '/../config/lang.php';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/landing.css">
    <?php if(isset($extra_css)) echo $extra_css; ?>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo SITE_URL; ?>">
                <img src="<?php echo SITE_URL; ?>/assets/images/2.jpg" alt="<?php echo SITE_NAME; ?>" 
                     style="height: 50px; width: auto; object-fit: contain;" 
                     class="me-2">
                <span class="fw-bold text-primary d-none d-md-inline"><?php echo SITE_NAME; ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>"><i class="fas fa-home"></i> <?php echo lang('nav_home'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/about.php"><i class="fas fa-info-circle"></i> <?php echo lang('nav_about'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/templates.php"><i class="fas fa-th"></i> <?php echo lang('nav_templates'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/services.php"><i class="fas fa-cogs"></i> <?php echo lang('nav_services'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/products.php"><i class="fas fa-shopping-bag"></i> <?php echo lang('nav_products'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/news.php"><i class="fas fa-newspaper"></i> <?php echo lang('nav_news'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/contact.php"><i class="fas fa-phone-alt"></i> <?php echo lang('nav_contact'); ?></a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-globe me-1"></i> <span><?php echo strtoupper($lang); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                            <?php
                            // Tạo URL với lang parameter
                            $current_url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                            $query_params = $_GET;
                            $query_params['lang'] = 'vi';
                            $vi_url = $current_url . '?' . http_build_query($query_params);
                            
                            $query_params['lang'] = 'en';
                            $en_url = $current_url . '?' . http_build_query($query_params);
                            ?>
                            <li><a class="dropdown-item language-option <?php echo $lang == 'vi' ? 'active bg-light' : ''; ?>" 
                                   href="<?php echo $vi_url; ?>"
                                   data-lang="vi">
                                <span class="me-2">🇻🇳</span>Tiếng Việt
                                <?php if($lang == 'vi'): ?><i class="fas fa-check ms-2 text-success"></i><?php endif; ?>
                            </a></li>
                            <li><a class="dropdown-item language-option <?php echo $lang == 'en' ? 'active bg-light' : ''; ?>" 
                                   href="<?php echo $en_url; ?>"
                                   data-lang="en">
                                <span class="me-2">🇬🇧</span>English
                                <?php if($lang == 'en'): ?><i class="fas fa-check ms-2 text-success"></i><?php endif; ?>
                            </a></li>
                        </ul>
                    </li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="<?php echo SITE_URL; ?>/cart.php">
                                <i class="fas fa-shopping-cart"></i>
                                <?php 
                                // Lấy số lượng giỏ hàng từ database
                                $cart_count = 0;
                                if (isset($_SESSION['user_id'])) {
                                    require_once __DIR__ . '/../config/database.php';
                                    $database = new Database();
                                    $db = $database->getConnection();
                                    $count_query = "SELECT SUM(quantity) as total FROM carts WHERE user_id = :user_id";
                                    $count_stmt = $db->prepare($count_query);
                                    $count_stmt->bindParam(':user_id', $_SESSION['user_id']);
                                    $count_stmt->execute();
                                    $count_result = $count_stmt->fetch(PDO::FETCH_ASSOC);
                                    $cart_count = (int)($count_result['total'] ?? 0);
                                }
                                ?>
                                <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle" 
                                      id="cart-count" 
                                      style="<?php echo $cart_count > 0 ? '' : 'display: none;'; ?>">
                                    <?php echo $cart_count; ?>
                                </span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <?php if(isset($_SESSION['user_avatar']) && !empty($_SESSION['user_avatar'])): ?>
                                    <?php 
                                    // Xử lý đường dẫn avatar - hỗ trợ nhiều định dạng
                                    $avatar_url = trim($_SESSION['user_avatar']);
                                    
                                    // Nếu là URL đầy đủ (http/https), sử dụng trực tiếp
                                    if (preg_match('/^https?:\/\//', $avatar_url)) {
                                        // URL đầy đủ, giữ nguyên
                                    }
                                    // Nếu bắt đầu bằng /uploads/, thêm SITE_URL
                                    elseif (preg_match('/^\/uploads\//', $avatar_url)) {
                                        $avatar_url = SITE_URL . $avatar_url;
                                    }
                                    // Nếu bắt đầu bằng /, thêm SITE_URL
                                    elseif (preg_match('/^\//', $avatar_url)) {
                                        $avatar_url = SITE_URL . $avatar_url;
                                    }
                                    // Nếu chứa uploads/ nhưng không bắt đầu bằng /, thêm SITE_URL và /
                                    elseif (strpos($avatar_url, 'uploads/') !== false) {
                                        if (strpos($avatar_url, SITE_URL) === false) {
                                            $avatar_url = SITE_URL . '/' . ltrim($avatar_url, '/');
                                        }
                                    }
                                    // Nếu chỉ là tên file hoặc đường dẫn tương đối, thêm đường dẫn đầy đủ
                                    else {
                                        $avatar_url = SITE_URL . '/uploads/users/' . basename($avatar_url);
                                    }
                                    ?>
                                    <img src="<?php echo htmlspecialchars($avatar_url); ?>" 
                                         alt="Avatar" 
                                         class="rounded-circle me-2" 
                                         style="width: 32px; height: 32px; object-fit: cover;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                                    <i class="fas fa-user-circle me-2" style="display: none;"></i>
                                <?php else: ?>
                                    <i class="fas fa-user-circle me-2"></i>
                                <?php endif; ?>
                                <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li class="px-3 py-2 border-bottom">
                                    <small class="text-muted"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></small>
                                </li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/profile.php"><i class="fas fa-user"></i> <?php echo lang('nav_profile'); ?></a></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/orders.php"><i class="fas fa-box"></i> <?php echo lang('nav_orders'); ?></a></li>
                                <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-primary" href="<?php echo SITE_URL; ?>/admin"><i class="fas fa-tachometer-alt"></i> <?php echo lang('nav_admin'); ?></a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo SITE_URL; ?>/auth/logout.php"><i class="fas fa-sign-out-alt"></i> <?php echo lang('nav_logout'); ?></a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-primary px-3 ms-2" href="<?php echo SITE_URL; ?>/auth/login.php">
                                <i class="fas fa-sign-in-alt"></i> <?php echo lang('nav_login'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary text-white px-3 ms-2" href="<?php echo SITE_URL; ?>/auth/register.php">
                                <i class="fas fa-user-plus"></i> <?php echo lang('nav_register'); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
