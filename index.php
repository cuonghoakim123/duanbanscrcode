<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/lang.php';

$title = lang('page_title');

$database = new Database();
$db = $database->getConnection();

// Lấy sản phẩm nổi bật
$query = "SELECT * FROM products WHERE status = 'active' AND featured = 1 ORDER BY created_at DESC LIMIT 12";
$stmt = $db->prepare($query);
$stmt->execute();
$featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh mục - ưu tiên các danh mục theo ngành hàng
$categories_config = [
    'doanh-nghiep' => [
        'name' => 'Doanh nghiệp',
        'icon' => 'fa-building',
        'color' => '#4299e1',
        'bg_color' => 'rgba(66, 153, 225, 0.1)'
    ],
    'ban-hang' => [
        'name' => 'Bán hàng',
        'icon' => 'fa-shopping-cart',
        'color' => '#48bb78',
        'bg_color' => 'rgba(72, 187, 120, 0.1)'
    ],
    'nha-hang' => [
        'name' => 'Nhà hàng',
        'icon' => 'fa-utensils',
        'color' => '#ed8936',
        'bg_color' => 'rgba(237, 137, 54, 0.1)'
    ],
    'bat-dong-san' => [
        'name' => 'Bất động sản',
        'icon' => 'fa-home',
        'color' => '#f59e0b',
        'bg_color' => 'rgba(245, 158, 11, 0.1)'
    ],
    'giao-duc' => [
        'name' => 'Giáo dục',
        'icon' => 'fa-graduation-cap',
        'color' => '#9f7aea',
        'bg_color' => 'rgba(159, 122, 234, 0.1)'
    ],
    'y-te' => [
        'name' => 'Y tế',
        'icon' => 'fa-heartbeat',
        'color' => '#f56565',
        'bg_color' => 'rgba(245, 101, 101, 0.1)'
    ],
    'lam-dep' => [
        'name' => 'Làm đẹp',
        'icon' => 'fa-spa',
        'color' => '#ec4899',
        'bg_color' => 'rgba(236, 72, 153, 0.1)'
    ]
];

// Lấy các categories từ database
$query = "SELECT * FROM categories WHERE status = 'active'";
$stmt = $db->prepare($query);
$stmt->execute();
$all_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tạo mảng categories với thông tin đầy đủ
$categories = [];
foreach($categories_config as $slug => $config) {
    // Tìm category trong database
    $cat = null;
    foreach($all_categories as $c) {
        if ($c['slug'] == $slug) {
            $cat = $c;
            break;
        }
    }
    // Nếu không tìm thấy, tạo category với thông tin từ config
    if (!$cat) {
        // Lấy tên category theo ngôn ngữ
        $category_key = 'category_' . $slug;
        $category_name = lang($category_key, $config['name']); // Fallback về config nếu không có key
        
        $cat = [
            'id' => 0,
            'name' => $category_name,
            'slug' => $slug,
            'description' => '',
            'status' => 'active'
        ];
    } else {
        // Cập nhật tên category theo ngôn ngữ nếu có key
        $category_key = 'category_' . $slug;
        $translated_name = lang($category_key);
        if ($translated_name != $category_key) { // Nếu có translation
            $cat['name'] = $translated_name;
        }
    }
    // Thêm config vào category
    $cat['icon'] = $config['icon'];
    $cat['color'] = $config['color'];
    $cat['bg_color'] = $config['bg_color'];
    $categories[] = $cat;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title . ' - ' . SITE_NAME; ?></title>
    <meta name="description" content="<?php echo $lang == 'vi' ? 'Dịch vụ thiết kế website chuyên nghiệp, giá rẻ, chuẩn SEO. Hơn 1000+ mẫu giao diện đẹp, phù hợp mọi ngành nghề' : 'Professional, cheap, SEO-standard website design services. Over 1000+ beautiful templates suitable for all industries'; ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/landing.css">
</head>
<body>

<!-- Top Bar -->
<div class="top-bar bg-dark text-white py-2">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <i class="fas fa-phone-alt"></i> <?php echo lang('topbar_hotline'); ?>: <strong>0355-999-141</strong>
                <span class="ms-3"><i class="fas fa-envelope"></i> cuonghotran17022004@gmail.com</span>
            </div>
            <div class="col-md-6 text-end">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span class="text-white me-3">
                        <i class="fas fa-user"></i> <?php echo lang('topbar_welcome'); ?>, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
                    </span>
                    <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="text-white text-decoration-none">
                        <i class="fas fa-sign-out-alt"></i> <?php echo lang('nav_logout'); ?>
                    </a>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/auth/login.php" class="text-white text-decoration-none me-3">
                        <i class="fas fa-sign-in-alt"></i> <?php echo lang('nav_login'); ?>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/auth/register.php" class="text-white text-decoration-none">
                        <i class="fas fa-user-plus"></i> <?php echo lang('nav_register'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Header -->
<header class="header-main sticky-top bg-white shadow-sm">
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo SITE_URL; ?>">
                <img src="<?php echo SITE_URL; ?>/assets/images/2.jpg" alt="<?php echo SITE_NAME; ?>" 
                     style="height: 60px; width: auto; object-fit: contain;" 
                     class="me-2">
                <span class="fw-bold text-primary d-none d-md-inline" style="font-size: 1.5rem;"><?php echo SITE_NAME; ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link active" href="<?php echo SITE_URL; ?>"><?php echo lang('nav_home'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/about.php"><?php echo lang('nav_about'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/templates.php"><?php echo lang('nav_templates'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/services.php"><?php echo lang('nav_services'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/products.php"><?php echo lang('nav_products'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>#reviews"><?php echo lang('nav_reviews'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/news.php"><?php echo lang('nav_news'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/contact.php"><?php echo lang('nav_contact'); ?></a></li>
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
                                <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle" id="cart-count">0</span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <?php if(isset($_SESSION['user_avatar']) && !empty($_SESSION['user_avatar'])): ?>
                                    <img src="<?php echo htmlspecialchars($_SESSION['user_avatar']); ?>" alt="Avatar" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
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

                    <?php endif; ?>
                </ul>

            </div>
        </nav>
    </div>
</header>

<!-- Hero Section -->
<section id="home" class="hero-section">
    <!-- Decorative Elements -->
    <div class="hero-decorative circle-1"></div>
    <div class="hero-decorative circle-2"></div>
    <div class="hero-decorative circle-3"></div>
    <div class="hero-decorative x-mark">
        <i class="fas fa-times"></i>
    </div>
    
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <h1 class="display-4 fw-bold mb-4"><?php echo lang('hero_title'); ?></h1>
                <p class="lead mb-4"><?php echo lang('hero_subtitle'); ?> <strong style="color: #d32f2f;">999k</strong></p>
                <div class="d-flex gap-3 mb-4">
                    <a href="tel:0939445228" class="btn btn-primary btn-lg">
                        <i class="fas fa-phone-alt"></i> <?php echo lang('hero_cta'); ?> <strong>0939 445 228</strong>
                    </a>
                </div>
                <p class="text-muted"><?php echo lang('hero_desc'); ?></p>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="hero-image-wrapper">
                    <div class="floating-card card-1"></div>
                    <div class="floating-card card-2"></div>
                    <div class="floating-card card-3"></div>
                    <div class="hero-illustration">
                        <img src="<?php echo SITE_URL; ?>/assets/images/3.png" 
                             alt="<?php echo lang('alt_hero_image'); ?>" 
                             class="img-fluid hero-image">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Benefits Section -->
<section id="about" class="benefits-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <!-- Left Side - Image -->
            <div class="col-lg-6 mb-5 mb-lg-0" data-aos="fade-right">
                <div class="benefits-image-wrapper">
                    <img src="<?php echo SITE_URL; ?>/assets/images/4.png" 
                         alt="Thiết kế website chuyên nghiệp" 
                         class="img-fluid benefits-desktop-image">
                </div>
            </div>
            
            <!-- Right Side - Benefits -->
            <div class="col-lg-6" data-aos="fade-left">
                <div class="benefits-content">
                    <h2 class="benefits-title mb-3">
                        <?php echo lang('benefits_title'); ?><br>
                        <span class="text-primary"><?php echo lang('benefits_subtitle'); ?></span>
                    </h2>
                    <p class="benefits-intro mb-5">
                        <?php echo lang('benefits_desc'); ?>
                    </p>
                    
                    <div class="row g-4">
                        <!-- Benefit 1 -->
                        <div class="col-md-6" data-aos="fade-up" data-aos-delay="100">
                            <div class="benefit-card-new">
                                <div class="benefit-icon benefit-icon-1">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <h5 class="benefit-title-new"><?php echo lang('benefit_1_title'); ?></h5>
                                <p class="benefit-text"><?php echo lang('benefit_1_desc'); ?></p>
                            </div>
                        </div>
                        
                        <!-- Benefit 2 -->
                        <div class="col-md-6" data-aos="fade-up" data-aos-delay="200">
                            <div class="benefit-card-new">
                                <div class="benefit-icon benefit-icon-2">
                                    <i class="fas fa-bullhorn"></i>
                                </div>
                                <h5 class="benefit-title-new"><?php echo lang('benefit_3_title'); ?></h5>
                                <p class="benefit-text"><?php echo lang('benefit_3_desc'); ?></p>
                            </div>
                        </div>
                        
                        <!-- Benefit 3 -->
                        <div class="col-md-6" data-aos="fade-up" data-aos-delay="300">
                            <div class="benefit-card-new">
                                <div class="benefit-icon benefit-icon-3">
                                    <i class="fas fa-lightbulb"></i>
                                </div>
                                <h5 class="benefit-title-new"><?php echo lang('benefit_2_title'); ?></h5>
                                <p class="benefit-text"><?php echo lang('benefit_2_desc'); ?></p>
                            </div>
                        </div>
                        
                        <!-- Benefit 4 -->
                        <div class="col-md-6" data-aos="fade-up" data-aos-delay="400">
                            <div class="benefit-card-new">
                                <div class="benefit-icon benefit-icon-4">
                                    <i class="fas fa-headset"></i>
                                </div>
                                <h5 class="benefit-title-new"><?php echo lang('benefit_4_title'); ?></h5>
                                <p class="benefit-text"><?php echo lang('benefit_4_desc'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section id="services" class="categories-section py-5">
    <div class="container position-relative">
        <!-- Decorative Elements -->
        <div class="category-decoration category-dec-1"></div>
        <div class="category-decoration category-dec-2"></div>
        <div class="category-decoration category-dec-3"></div>
        <div class="category-decoration category-dec-4"></div>
        
        <div class="text-center mb-5">
            <h2 class="fw-bold categories-title"><?php echo lang('templates_title'); ?></h2>
            <p class="text-muted"><?php echo lang('templates_subtitle'); ?></p>
        </div>
        
        <!-- Center Image -->
        <div class="text-center mb-5" data-aos="fade-up">
            <img src="<?php echo SITE_URL; ?>/assets/images/5.png" 
                         alt="<?php echo lang('alt_templates_image'); ?>"
                 class="img-fluid categories-center-image">
        </div>
        
        <!-- Categories Grid -->
        <div class="row g-4 justify-content-center categories-grid-modern">
            <?php foreach($categories as $index => $cat): ?>
                <div class="col-6 col-sm-4 col-md-3 col-lg-auto" data-aos="zoom-in" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                    <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $cat['slug']; ?>" 
                       class="category-card-modern" 
                       style="--category-color: <?php echo $cat['color']; ?>; --category-bg: <?php echo $cat['bg_color']; ?>">
                        <div class="category-icon-modern">
                            <i class="fas <?php echo $cat['icon']; ?>"></i>
                        </div>
                        <h6 class="category-name-modern"><?php echo htmlspecialchars($cat['name']); ?></h6>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Products Section -->
<section id="products" class="products-section py-5">
    <div class="container position-relative">
        <!-- Logo -->
        <div class="text-center mb-4" data-aos="fade-down">
            <img src="<?php echo SITE_URL; ?>/assets/images/6.png" 
                 alt="Logo" 
                 class="products-section-logo">
        </div>
        
        <!-- Flash Sale Badge -->
        <div class="text-center mb-3">
            <span class="flash-sale-badge"><?php echo lang('products_flash_sale'); ?></span>
        </div>
        
        <div class="text-center mb-5">
            <h2 class="fw-bold products-section-title"><?php echo lang('products_title'); ?></h2>
            <p class="products-section-subtitle"><?php echo lang('products_subtitle'); ?></p>
        </div>
        <div class="row g-4">
            <?php foreach($featured_products as $index => $product): ?>
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 50; ?>">
                    <div class="product-card h-100">
                        <?php if($product['sale_price']): ?>
                            <div class="sale-badge">-<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%</div>
                        <?php endif; ?>
                        <div class="product-image">
                            <img src="<?php echo $product['image'] ?: getProductPlaceholder(300, 200); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 loading="lazy">
                            <div class="product-overlay">
                                <a href="<?php echo SITE_URL; ?>/product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-light btn-sm">
                                    <i class="fas fa-eye"></i> <?php echo lang('products_view_detail'); ?>
                                </a>
                            </div>
                        </div>
                        <div class="product-info">
                            <h6 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h6>
                            <div class="product-price">
                                <?php if($product['sale_price']): ?>
                                    <span class="current-price"><?php echo number_format($product['sale_price']); ?><?php echo $lang == 'vi' ? 'đ' : ' ' . lang('products_currency'); ?></span>
                                    <span class="old-price"><?php echo number_format($product['price']); ?><?php echo $lang == 'vi' ? 'đ' : ' ' . lang('products_currency'); ?></span>
                                <?php else: ?>
                                    <span class="current-price"><?php echo number_format($product['price']); ?><?php echo $lang == 'vi' ? 'đ' : ' ' . lang('products_currency'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary btn-lg">
                <?php echo lang('products_view_more'); ?> <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section id="pricing" class="pricing-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold"><?php echo lang('pricing_title'); ?></h2>
            <p class="text-muted"><?php echo lang('pricing_subtitle'); ?></p>
        </div>
        <div class="row g-4">
            <div class="col-md-4" data-aos="flip-up" data-aos-delay="100">
                <div class="pricing-card">
                    <div class="pricing-header bg-secondary text-white">
                        <h4><?php echo lang('pricing_basic'); ?></h4>
                        <div class="pricing-price">
                            <span class="amount">999,000</span>
                            <span class="currency"><?php echo lang('products_currency'); ?></span>
                        </div>
                    </div>
                    <div class="pricing-body">
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_template'); ?></li>
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_domain'); ?></li>
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_storage'); ?></li>
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_bandwidth'); ?></li>
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_language'); ?></li>
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_seo'); ?></li>
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_chat'); ?></li>
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_content'); ?></li>
                        </ul>
                        <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-outline-secondary w-100"><?php echo lang('pricing_consult'); ?></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-aos="flip-up" data-aos-delay="200">
                <div class="pricing-card featured">
                    <div class="badge-popular"><?php echo lang('pricing_popular'); ?></div>
                    <div class="pricing-header bg-primary text-white">
                        <h4><?php echo lang('pricing_professional'); ?></h4>
                        <div class="pricing-price">
                            <span class="amount">10,000,000</span>
                            <span class="currency"><?php echo lang('products_currency'); ?></span>
                        </div>
                    </div>
                    <div class="pricing-body">
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_custom'); ?></li>
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_domain'); ?></li>
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_storage_unlimited'); ?></li>
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_bandwidth'); ?></li>
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_languages'); ?></li>
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_seo_advanced'); ?></li>
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_features'); ?></li>
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_marketing'); ?></li>
                        </ul>
                        <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-primary w-100"><?php echo lang('pricing_consult'); ?></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-aos="flip-up" data-aos-delay="300">
                <div class="pricing-card">
                    <div class="pricing-header bg-warning text-dark">
                        <h4><?php echo lang('pricing_premium'); ?></h4>
                        <div class="pricing-price">
                            <span class="amount">25,000,000</span>
                            <span class="currency"><?php echo lang('products_currency'); ?></span>
                        </div>
                    </div>
                    <div class="pricing-body">
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_exclusive'); ?></li>
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_domain_vn'); ?></li>
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_server'); ?></li>
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_bandwidth'); ?></li>
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_languages'); ?></li>
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_seo_expert'); ?></li>
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_api'); ?></li>
                            <li><i class="fas fa-check text-success"></i> <?php echo lang('pricing_vip'); ?></li>
                        </ul>
                        <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-outline-warning w-100"><?php echo lang('pricing_consult'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Professional Design Section -->
<section class="professional-design-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="professional-design-title"><?php echo lang('professional_title'); ?></h2>
        </div>
        <div class="row align-items-center">
            <!-- Left Column - Text Content -->
            <div class="col-lg-6 mb-5 mb-lg-0" data-aos="fade-right">
                <!-- First Feature Box -->
                <div class="feature-box-modern mb-4">
                    <i class="fas fa-mobile-alt feature-icon feature-icon-1"></i>
                    <span><?php echo lang('professional_responsive'); ?></span>
                </div>
                
                <!-- Main Text -->
                <p class="professional-design-text mb-4">
                    <?php echo lang('professional_responsive_desc'); ?>
                </p>
                
                <!-- Additional Feature Boxes -->
                <div class="feature-box-modern mb-3">
                    <i class="fas fa-shield-alt feature-icon feature-icon-2"></i>
                    <span><?php echo lang('professional_ssl'); ?></span>
                </div>
                <p class="professional-design-text mb-4">
                    <?php echo lang('professional_ssl_desc'); ?>
                </p>
                
                <div class="feature-box-modern mb-3">
                    <i class="fas fa-tablet-alt feature-icon feature-icon-3"></i>
                    <span><?php echo lang('professional_tablet'); ?></span>
                </div>
                <p class="professional-design-text mb-4">
                    <?php echo lang('professional_tablet_desc'); ?>
                </p>
                
                <div class="feature-box-modern mb-4">
                    <i class="fas fa-chart-line feature-icon feature-icon-4"></i>
                    <span><?php echo lang('professional_analytics'); ?></span>
                </div>
                <p class="professional-design-text mb-4">
                    <?php echo lang('professional_analytics_desc'); ?>
                </p>
                
                <!-- Call to Action Button -->
                <a href="<?php echo SITE_URL; ?>/contact.php" class="btn-contact-now">
                    <?php echo lang('professional_contact'); ?>
                </a>
            </div>
            
            <!-- Right Column - Mockups -->
            <div class="col-lg-6" data-aos="fade-left">
                <div class="mockups-wrapper position-relative">
                    <!-- Dashboard Mockup -->
                    <div class="mockup-dashboard">
                        <div class="mockup-content">
                            <div class="mockup-sidebar">
                                <div class="sidebar-item">Home</div>
                                <div class="sidebar-item">Updates</div>
                                <div class="sidebar-item">Posts</div>
                                <div class="sidebar-item">Media</div>
                                <div class="sidebar-item">Pages</div>
                                <div class="sidebar-item">Comments</div>
                            </div>
                            <div class="mockup-main">
                                <div class="mockup-widget">Rank Math Overview</div>
                                <div class="mockup-widget">Latest Blog Posts</div>
                                <div class="mockup-widget">Elementor Overview</div>
                                <div class="mockup-widget">Recently Edited</div>
                                <div class="mockup-widget">News & Update</div>
                            </div>
                        </div>
                        <!-- Orange Trend Line -->
                        <div class="trend-line"></div>
                    </div>
                    
                    <!-- Google Analytics Mockup -->
                    <div class="mockup-analytics">
                        <div class="analytics-logo">
                            <div class="analytics-bars">
                                <div class="bar bar-1"></div>
                                <div class="bar bar-2"></div>
                                <div class="bar bar-3"></div>
                            </div>
                            <div class="analytics-text">Google Analytics</div>
                        </div>
                        <div class="analytics-content">
                            <div class="analytics-icon"></div>
                            <div class="analytics-chart">
                                <div class="chart-bar"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SEO & Advertising Section -->
<section class="seo-advertising-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <!-- Left Column - Question, Title & Google Search Mockup -->
            <div class="col-lg-6 mb-5 mb-lg-0" data-aos="fade-right">
                <p class="seo-question mb-3"><?php echo lang('seo_question'); ?></p>
                <h2 class="seo-main-title mb-4"><?php echo lang('seo_title'); ?></h2>
                
                <!-- Google Search Mockup with Image -->
                <div class="google-search-mockup position-relative">
                    <img src="<?php echo SITE_URL; ?>/assets/images/8.png" 
                         alt="Google Search Results" 
                         class="img-fluid google-search-image">
                    
                    <!-- Decorative Elements -->
                    <div class="seo-megaphone">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div class="seo-google-box">
                        <div class="google-logo-text">Google</div>
                        <div class="seo-badge">SEO</div>
                        <div class="seo-steps">
                            <div class="seo-step" data-step="02"></div>
                            <div class="seo-step" data-step="03"></div>
                            <div class="seo-step" data-step="04"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Benefits -->
            <div class="col-lg-6" data-aos="fade-left">
                <!-- Benefit 1 -->
                <div class="seo-benefit-item mb-4">
                    <div class="seo-benefit-header">
                        <i class="fas fa-check-circle seo-check-icon"></i>
                        <h4 class="seo-benefit-title"><?php echo lang('seo_seo_title'); ?></h4>
                    </div>
                    <p class="seo-benefit-text">
                        <?php echo lang('seo_seo_desc'); ?>
                    </p>
                </div>
                
                <!-- Benefit 2 -->
                <div class="seo-benefit-item mb-4">
                    <div class="seo-benefit-header">
                        <i class="fas fa-check-circle seo-check-icon"></i>
                        <h4 class="seo-benefit-title"><?php echo lang('seo_ads_title'); ?></h4>
                    </div>
                    <p class="seo-benefit-text">
                        <?php echo lang('seo_ads_desc'); ?>
                    </p>
                </div>
                
                <!-- Benefit 3 -->
                <div class="seo-benefit-item mb-4">
                    <div class="seo-benefit-header">
                        <i class="fas fa-check-circle seo-check-icon"></i>
                        <h4 class="seo-benefit-title"><?php echo lang('seo_shopping_title'); ?></h4>
                    </div>
                    <p class="seo-benefit-text">
                        <?php echo lang('seo_shopping_desc'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold"><?php echo lang('why_title'); ?><br><?php echo lang('why_title2'); ?></h2>
            <p class="text-muted"><?php echo lang('why_subtitle'); ?></p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4" data-aos="fade-up">
                <div class="feature-box">
                    <i class="fas fa-rocket fa-3x text-primary mb-3"></i>
                    <h5><?php echo lang('why_fast'); ?></h5>
                    <p><?php echo lang('why_fast_desc'); ?></p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-box">
                    <i class="fas fa-cog fa-3x text-success mb-3"></i>
                    <h5><?php echo lang('why_easy'); ?></h5>
                    <p><?php echo lang('why_easy_desc'); ?></p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-box">
                    <i class="fas fa-dollar-sign fa-3x text-warning mb-3"></i>
                    <h5><?php echo lang('why_cheap'); ?></h5>
                    <p><?php echo lang('why_cheap_desc'); ?></p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-box">
                    <i class="fas fa-star fa-3x text-danger mb-3"></i>
                    <h5><?php echo lang('why_beautiful'); ?></h5>
                    <p><?php echo lang('why_beautiful_desc'); ?></p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-box">
                    <i class="fas fa-mobile-alt fa-3x text-info mb-3"></i>
                    <h5><?php echo lang('why_mobile'); ?></h5>
                    <p><?php echo lang('why_mobile_desc'); ?></p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="500">
                <div class="feature-box">
                    <i class="fas fa-shield-alt fa-3x text-secondary mb-3"></i>
                    <h5><?php echo lang('why_warranty'); ?></h5>
                    <p><?php echo lang('why_warranty_desc'); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>



<!-- FAQ Section -->
<section class="faq-section py-5">
    <div class="container">
        <h2 class="faq-title text-center mb-5"><?php echo lang('faq_title'); ?><br><?php echo lang('faq_title2'); ?></h2>
        
        <div class="faq-accordion">
            <!-- FAQ Item 1 -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(1)">
                    <span class="faq-number">1.</span>
                    <span class="faq-question-text"><?php echo lang('faq_q1'); ?></span>
                    <i class="fas fa-chevron-down faq-chevron"></i>
                </div>
                <div class="faq-answer">
                    <p><?php echo lang('faq_a1_1'); ?></p>
                    <p><?php echo lang('faq_a1_2'); ?></p>
                </div>
            </div>
            
            <!-- FAQ Item 2 -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(2)">
                    <span class="faq-number">2.</span>
                    <span class="faq-question-text"><?php echo lang('faq_q2'); ?></span>
                    <i class="fas fa-chevron-down faq-chevron"></i>
                </div>
                <div class="faq-answer">
                    <p><?php echo lang('faq_a2'); ?></p>
                </div>
            </div>
            
            <!-- FAQ Item 3 -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(3)">
                    <span class="faq-number">3.</span>
                    <span class="faq-question-text"><?php echo lang('faq_q3'); ?></span>
                    <i class="fas fa-chevron-down faq-chevron"></i>
                </div>
                <div class="faq-answer">
                    <p><?php echo lang('faq_a3'); ?></p>
                </div>
            </div>
            
            <!-- FAQ Item 4 -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(4)">
                    <span class="faq-number">4.</span>
                    <span class="faq-question-text"><?php echo lang('faq_q4'); ?></span>
                    <i class="fas fa-chevron-down faq-chevron"></i>
                </div>
                <div class="faq-answer">
                    <p><?php echo lang('faq_a4'); ?></p>
                </div>
            </div>
            
            <!-- FAQ Item 5 -->
            <div class="faq-item active">
                <div class="faq-question" onclick="toggleFaq(5)">
                    <span class="faq-number">5.</span>
                    <span class="faq-question-text"><?php echo lang('faq_q5'); ?></span>
                    <i class="fas fa-chevron-up faq-chevron"></i>
                </div>
                <div class="faq-answer show">
                    <p><?php echo lang('faq_a5_1'); ?></p>
                    <ul class="faq-list">
                        <li><?php echo lang('faq_a5_2'); ?></li>
                        <li><?php echo lang('faq_a5_3'); ?></li>
                        <li><?php echo lang('faq_a5_4'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="contact-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-6" data-aos="fade-right">
                <h2 class="fw-bold mb-4"><?php echo lang('contact_title'); ?></h2>
                <p class="lead mb-4"><?php echo lang('contact_subtitle'); ?></p>
                <div class="contact-info">
                    <div class="info-item mb-3">
                        <i class="fas fa-map-marker-alt text-primary fa-2x"></i>
                        <div class="ms-3">
                            <h6><?php echo lang('contact_address'); ?></h6>
                            <p>Tòa nhà ABC, 123 Đường XYZ, Quận 1, TP.HCM</p>
                        </div>
                    </div>
                    <div class="info-item mb-3">
                        <i class="fas fa-phone text-primary fa-2x"></i>
                        <div class="ms-3">
                            <h6><?php echo lang('contact_phone'); ?></h6>
                            <p>0356-012250 - 0355 999 141</p>
                        </div>
                    </div>
                    <div class="info-item mb-3">
                        <i class="fas fa-envelope text-primary fa-2x"></i>
                        <div class="ms-3">
                            <h6><?php echo lang('contact_email'); ?></h6>
                            <p>cuonghotran17022004@gmail.com - support@yoursite.vn</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="contact-form bg-light p-4 rounded shadow">
                    <form>
                        <div class="mb-3">
                            <input type="text" class="form-control" placeholder="<?php echo lang('contact_name'); ?> *" required>
                        </div>
                        <div class="mb-3">
                            <input type="tel" class="form-control" placeholder="<?php echo lang('contact_phone_number'); ?> *" required>
                        </div>
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="<?php echo lang('contact_email'); ?>">
                        </div>
                        <div class="mb-3">
                            <select class="form-select">
                                <option><?php echo lang('contact_service'); ?></option>
                                <option><?php echo $lang == 'vi' ? 'Gói cơ bản - 999.000đ' : 'Basic Package - 999,000 VND'; ?></option>
                                <option><?php echo $lang == 'vi' ? 'Gói chuyên nghiệp - 10.000.000đ' : 'Professional Package - 10,000,000 VND'; ?></option>
                                <option><?php echo $lang == 'vi' ? 'Gói cao cấp - 25.000.000đ' : 'Premium Package - 25,000,000 VND'; ?></option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" rows="4" placeholder="<?php echo lang('contact_message'); ?>"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="fas fa-paper-plane"></i> <?php echo lang('contact_submit'); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Reviews Section -->
<section class="reviews-section py-5 bg-light" id="reviews">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-down">
            <h2 class="fw-bold mb-3"><?php echo lang('reviews_title'); ?></h2>
            <p class="text-muted"><?php echo lang('reviews_subtitle'); ?></p>
        </div>
        
        <!-- Review Stats (sẽ được cập nhật bằng JavaScript realtime) -->
        <div class="text-center mb-4" data-aos="fade-up" id="reviewStatsContainer" style="display: none;">
            <div class="review-summary">
                <div class="review-summary-rating">
                    <div class="rating-number" id="ratingNumber">0.0</div>
                    <div class="rating-stars-large" id="ratingStars">
                        <i class="fas fa-star text-muted"></i>
                        <i class="fas fa-star text-muted"></i>
                        <i class="fas fa-star text-muted"></i>
                        <i class="fas fa-star text-muted"></i>
                        <i class="fas fa-star text-muted"></i>
                    </div>
                    <p class="text-muted mt-2" id="totalReviewsText"><?php echo lang('reviews_based_on'); ?> 0 <?php echo lang('reviews_reviews'); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Reviews Container (sẽ được load bằng AJAX realtime) -->
        <div class="row g-4" id="reviewsContainer">
            <!-- Reviews sẽ được load ở đây bằng JavaScript -->
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden"><?php echo lang('common_loading'); ?></span>
                </div>
                <p class="text-muted mt-3"><?php echo lang('common_loading'); ?></p>
            </div>
        </div>
        
        <!-- Reviews mẫu ban đầu (fallback - chỉ hiển thị nếu API lỗi) -->
        <div class="row g-4" id="reviewsFallback" style="display: none;">
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                <div class="review-card h-100">
                    <div class="review-header">
                        <div class="review-avatar">
                            <div class="avatar-placeholder">N</div>
                        </div>
                        <div class="review-info">
                            <h6 class="review-name"><?php echo lang('review_fallback_1_name'); ?></h6>
                            <div class="review-rating">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                        </div>
                    </div>
                    <div class="review-body">
                        <p class="review-comment"><?php echo lang('review_fallback_1_comment'); ?></p>
                    </div>
                    <div class="review-footer">
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> 15/01/2025
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                <div class="review-card h-100">
                    <div class="review-header">
                        <div class="review-avatar">
                            <div class="avatar-placeholder">T</div>
                        </div>
                        <div class="review-info">
                            <h6 class="review-name"><?php echo lang('review_fallback_2_name'); ?></h6>
                            <div class="review-rating">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                        </div>
                    </div>
                    <div class="review-body">
                        <p class="review-comment"><?php echo lang('review_fallback_2_comment'); ?></p>
                    </div>
                    <div class="review-footer">
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> 12/01/2025
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
                <div class="review-card h-100">
                    <div class="review-header">
                        <div class="review-avatar">
                            <div class="avatar-placeholder">L</div>
                        </div>
                        <div class="review-info">
                            <h6 class="review-name"><?php echo lang('review_fallback_3_name'); ?></h6>
                            <div class="review-rating">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-muted"></i>
                            </div>
                        </div>
                    </div>
                    <div class="review-body">
                        <p class="review-comment"><?php echo lang('review_fallback_3_comment'); ?></p>
                    </div>
                    <div class="review-footer">
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> 10/01/2025
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- View More Reviews Button -->
        <div class="text-center mt-5" id="viewMoreReviewsBtn" style="display: none;" data-aos="fade-up">
            <a href="<?php echo SITE_URL; ?>/products.php#reviews" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-star"></i> <?php echo lang('reviews_view_more'); ?>
            </a>
        </div>
        
        <!-- Review Form Section (chỉ hiển thị khi đã đăng nhập) -->
        <div class="mt-5" id="reviewFormSection" style="display: none;">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="review-form-card" data-aos="fade-up">
                        <h4 class="mb-4 text-center">
                            <i class="fas fa-star text-warning"></i> <?php echo lang('reviews_write'); ?>
                        </h4>
                        <form id="reviewForm">
                            <div class="mb-3">
                                <label class="form-label fw-bold"><?php echo lang('reviews_rating'); ?> <span class="text-danger">*</span></label>
                                <div class="rating-input mb-2">
                                    <input type="radio" name="rating" id="rating5" value="5" required>
                                    <label for="rating5" class="star-label"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" name="rating" id="rating4" value="4">
                                    <label for="rating4" class="star-label"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" name="rating" id="rating3" value="3">
                                    <label for="rating3" class="star-label"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" name="rating" id="rating2" value="2">
                                    <label for="rating2" class="star-label"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" name="rating" id="rating1" value="1">
                                    <label for="rating1" class="star-label"><i class="fas fa-star"></i></label>
                                </div>
                                <small class="text-muted"><?php echo lang('reviews_select_rating'); ?></small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="reviewComment" class="form-label fw-bold"><?php echo lang('reviews_comment'); ?> <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="reviewComment" name="comment" rows="4" 
                                          placeholder="<?php echo lang('common_share_experience'); ?>" required></textarea>
                                <small class="text-muted"><?php echo lang('reviews_min_chars'); ?></small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="reviewProduct" class="form-label fw-bold"><?php echo lang('reviews_product'); ?></label>
                                <select class="form-select" id="reviewProduct" name="product_id">
                                    <option value=""><?php echo lang('reviews_select_product'); ?></option>
                                    <?php
                                    // Lấy danh sách sản phẩm
                                    try {
                                        $products_query = "SELECT id, name FROM products WHERE status = 'active' ORDER BY name";
                                        $products_stmt = $db->prepare($products_query);
                                        $products_stmt->execute();
                                        $products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);
                                        foreach($products as $product):
                                    ?>
                                    <option value="<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></option>
                                    <?php
                                        endforeach;
                                    } catch(PDOException $e) {
                                        // Ignore error
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitReviewBtn">
                                    <i class="fas fa-paper-plane"></i> <?php echo lang('reviews_submit'); ?>
                                </button>
                            </div>
                            
                            <div id="reviewFormMessage" class="mt-3"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Login Prompt (chỉ hiển thị khi chưa đăng nhập) -->
        <div class="mt-5" id="reviewLoginPrompt" style="display: none;">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="review-login-prompt" data-aos="fade-up">
                        <div class="text-center">
                            <i class="fas fa-lock fa-3x text-muted mb-3"></i>
                            <h5 class="mb-3"><?php echo lang('reviews_login_prompt'); ?></h5>
                            <p class="text-muted mb-4"><?php echo lang('reviews_login_desc'); ?></p>
                            <div class="d-flex gap-3 justify-content-center flex-wrap">
                                <a href="<?php echo SITE_URL; ?>/auth/login.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt"></i> <?php echo lang('nav_login'); ?>
                                </a>
                                <a href="<?php echo SITE_URL; ?>/auth/register.php" class="btn btn-outline-primary">
                                    <i class="fas fa-user-plus"></i> <?php echo lang('nav_register'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer bg-dark text-white pt-5 pb-3">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5><?php echo lang('footer_about'); ?></h5>
                <p><?php echo lang('footer_about_desc'); ?></p>
                <div class="social-links">
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-2x"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-youtube fa-2x"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-2x"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-linkedin fa-2x"></i></a>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <h5><?php echo lang('footer_services'); ?></h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo SITE_URL; ?>/services.php" class="text-white-50"><?php echo lang('footer_service_1'); ?></a></li>
                    <li><a href="<?php echo SITE_URL; ?>/services.php" class="text-white-50"><?php echo lang('footer_service_2'); ?></a></li>
                    <li><a href="<?php echo SITE_URL; ?>/services.php" class="text-white-50"><?php echo lang('footer_service_3'); ?></a></li>
                    <li><a href="<?php echo SITE_URL; ?>/services.php" class="text-white-50"><?php echo lang('footer_service_4'); ?></a></li>
                    <li><a href="<?php echo SITE_URL; ?>/services.php" class="text-white-50"><?php echo lang('footer_service_5'); ?></a></li>
                    <li><a href="<?php echo SITE_URL; ?>/services.php" class="text-white-50"><?php echo lang('footer_service_6'); ?></a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5><?php echo lang('footer_contact_info'); ?></h5>
                <ul class="list-unstyled">
                    <li><i class="fas fa-map-marker-alt me-2"></i> KTX Khu B Đại Học Quốc Gia TP.HCM</li>
                    <li><i class="fas fa-phone me-2"></i> 0356012250</li>
                    <li><i class="fas fa-envelope me-2"></i> cuonghotran17022004@gmail.com</li>
                    <li><i class="fas fa-globe me-2"></i> https://www.youtube.com/@DiamondDevFullstack2025</li>
                </ul>
            </div>
        </div>
        <hr class="bg-secondary">
        <div class="text-center">
            <p class="mb-0">&copy; 2025 <?php echo SITE_NAME; ?>. <?php echo lang('footer_rights'); ?></p>
        </div>
    </div>
</footer>

<!-- Back to Top -->
<a href="#home" class="back-to-top">
    <i class="fas fa-arrow-up"></i>
</a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js" defer></script>
<script src="<?php echo SITE_URL; ?>/assets/js/main.js" defer></script>
<style>
/* Reviews Section Styles */
.reviews-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    position: relative;
    overflow: hidden;
}

.reviews-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 30%, rgba(13, 110, 253, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(255, 193, 7, 0.05) 0%, transparent 50%);
    z-index: 1;
}

.reviews-section .container {
    position: relative;
    z-index: 2;
}

.review-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 1px solid #f0f0f0;
    position: relative;
}

.review-card::before {
    content: '"';
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 60px;
    color: #e3f2fd;
    font-family: Georgia, serif;
    line-height: 1;
    opacity: 0.5;
}

.review-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
}

.review-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.review-avatar {
    margin-right: 15px;
}

.avatar-img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e3f2fd;
}

.avatar-placeholder {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 20px;
    border: 3px solid #e3f2fd;
}

.review-info {
    flex: 1;
}

.review-name {
    font-weight: 600;
    margin-bottom: 5px;
    color: #333;
    font-size: 16px;
}

.review-rating {
    font-size: 14px;
}

.review-rating .fa-star {
    margin-right: 2px;
}

.review-body {
    margin-bottom: 15px;
}

.review-comment {
    color: #555;
    line-height: 1.8;
    margin-bottom: 10px;
    font-size: 14px;
    font-style: italic;
    position: relative;
    z-index: 1;
}

.review-product {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px dashed #e0e0e0;
}

.review-footer {
    padding-top: 15px;
    border-top: 1px solid #f0f0f0;
}

.review-footer i {
    margin-right: 5px;
}

.review-summary {
    margin-bottom: 30px;
}

.review-summary-rating {
    display: inline-block;
    text-align: center;
    padding: 20px 40px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
}

.rating-number {
    font-size: 48px;
    font-weight: 800;
    color: #1976d2;
    line-height: 1;
    margin-bottom: 10px;
}

.rating-stars-large {
    font-size: 24px;
    margin-bottom: 10px;
}

.rating-stars-large .fa-star {
    margin: 0 2px;
}

/* Realtime Update Indicator */
.realtime-indicator {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #28a745;
    color: white;
    padding: 10px 20px;
    border-radius: 25px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    display: none;
    align-items: center;
    gap: 10px;
    animation: slideInUp 0.3s ease;
}

.realtime-indicator.show {
    display: flex;
}

.realtime-indicator .spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

@keyframes slideInUp {
    from {
        transform: translateY(100px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* New Review Animation */
.review-card.new-review {
    animation: newReviewPulse 0.6s ease;
}

@keyframes newReviewPulse {
    0% {
        transform: scale(0.9);
        opacity: 0;
    }
    50% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

/* Loading State */
.reviews-loading {
    text-align: center;
    padding: 40px;
}

.reviews-loading .spinner-border {
    width: 3rem;
    height: 3rem;
    border-width: 0.3em;
}

/* Review Form Styles */
.review-form-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #f0f0f0;
}

.rating-input {
    display: flex;
    gap: 5px;
    justify-content: center;
    align-items: center;
    flex-direction: row-reverse;
}

.rating-input input[type="radio"] {
    display: none;
}

.rating-input .star-label {
    font-size: 32px;
    color: #ddd;
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
}

.rating-input .star-label:hover {
    color: #ffc107;
    transform: scale(1.1);
}

/* Khi hover vào một sao, tất cả sao bên phải cũng sáng */
.rating-input input[type="radio"]:hover ~ .star-label,
.rating-input .star-label:hover ~ .star-label {
    color: #ffc107;
}

/* Khi chọn một sao, tất cả sao từ đó trở về trước đều sáng */
.rating-input input[type="radio"]:checked ~ .star-label {
    color: #ffc107;
}

/* Tất cả sao sau sao được chọn đều tối */
.rating-input input[type="radio"]:checked + .star-label ~ .star-label {
    color: #ddd;
}

/* Login Prompt Styles */
.review-login-prompt {
    background: white;
    border-radius: 15px;
    padding: 40px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    border: 2px dashed #e0e0e0;
}

.review-login-prompt i {
    opacity: 0.5;
}

/* Pending Review Style */
.review-card.review-pending {
    border: 2px dashed #ffc107;
    background: linear-gradient(135deg, #fff9e6 0%, #ffffff 100%);
    opacity: 0.9;
}

.review-pending .badge {
    font-size: 11px;
    padding: 4px 8px;
}

/* Language Switcher */
.language-option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 15px;
    transition: all 0.3s ease;
}

.language-option:hover {
    background: #f8f9fa;
}

.language-option.active {
    background: #e7f3ff;
    font-weight: 600;
}

.language-option i {
    font-size: 14px;
}

/* Responsive */
@media (max-width: 768px) {
    .review-card {
        padding: 20px;
    }
    
    .review-form-card {
        padding: 20px;
    }
    
    .review-login-prompt {
        padding: 30px 20px;
    }
    
    .review-header {
        flex-direction: column;
        text-align: center;
    }
    
    .review-avatar {
        margin-right: 0;
        margin-bottom: 10px;
    }
    
    .language-option {
        padding: 12px 15px;
    }
}
</style>
<script>
// Chờ DOM load xong
document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 1000,
            once: true
        });
    }
    
    // Update cart count
    function updateCartCount() {
        <?php if(isset($_SESSION['user_id'])): ?>
        fetch('<?php echo SITE_URL; ?>/cart_handler.php?action=count')
            .then(response => response.json())
            .then(data => {
                const cartCountEl = document.getElementById('cart-count');
                if(cartCountEl && data.count) {
                    cartCountEl.textContent = data.count;
                }
            })
            .catch(error => console.log('Cart count error:', error));
        <?php endif; ?>
    }
    
    // Call updateCartCount when page loads
    <?php if(isset($_SESSION['user_id'])): ?>
    updateCartCount();
    <?php endif; ?>
    
    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
    
    // Back to top button
    const backToTop = document.querySelector('.back-to-top');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });
    }
    
    // Sticky header
    const header = document.querySelector('.header-main');
    if (header) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }
    
    // Realtime Reviews
    if (document.getElementById('reviewsContainer')) {
        initRealtimeReviews();
    }
    
    // Review Form
    initReviewForm();
});

// Realtime Reviews Function
function initRealtimeReviews() {
    const reviewsContainer = document.getElementById('reviewsContainer');
    const reviewsFallback = document.getElementById('reviewsFallback');
    const reviewsApiUrl = '<?php echo SITE_URL; ?>/api/get_reviews.php';
    let lastReviewId = 0;
    let currentReviews = [];
    let updateInterval = null;
    
    // Tạo realtime indicator
    const indicator = document.createElement('div');
    indicator.className = 'realtime-indicator';
    const lang = '<?php echo $lang; ?>';
    const updatingText = lang === 'vi' ? 'Đang cập nhật đánh giá mới...' : 'Updating new reviews...';
    indicator.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div><span>' + updatingText + '</span>';
    document.body.appendChild(indicator);
    
    // Hàm render review card
    function renderReviewCard(review, index, isNew = false) {
        const avatar = review.user_avatar 
            ? `<img src="${escapeHtml(review.user_avatar)}" alt="${escapeHtml(review.user_name)}" class="avatar-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">`
            : '';
        const avatarPlaceholder = `<div class="avatar-placeholder" ${review.user_avatar ? 'style="display:none;"' : ''}>${(review.user_name || 'U').charAt(0).toUpperCase()}</div>`;
        
        const stars = Array.from({length: 5}, (_, i) => {
            const isActive = i < review.rating;
            return `<i class="fas fa-star ${isActive ? 'text-warning' : 'text-muted'}"></i>`;
        }).join('');
        
        const lang = '<?php echo $lang; ?>';
        const productLabel = lang === 'vi' ? 'Sản phẩm' : 'Product';
        const productInfo = review.product_name 
            ? `<div class="review-product"><small class="text-muted"><i class="fas fa-box"></i> ${productLabel}: ${escapeHtml(review.product_name)}</small></div>`
            : '';
        
        // Hiển thị badge nếu review đang pending
        const pendingText = lang === 'vi' ? 'Đang chờ duyệt' : 'Pending approval';
        const statusBadge = review.status === 'pending' 
            ? `<span class="badge bg-warning text-dark ms-2"><i class="fas fa-clock"></i> ${pendingText}</span>`
            : '';
        
        const delay = (index % 3) * 100;
        const newClass = isNew ? 'new-review' : '';
        const aosDelay = isNew ? 0 : delay;
        const pendingClass = review.status === 'pending' ? 'review-pending' : '';
        
        return `
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="${aosDelay}" data-review-id="${review.id}">
                <div class="review-card h-100 ${newClass} ${pendingClass}">
                    <div class="review-header">
                        <div class="review-avatar">
                            ${avatar}
                            ${avatarPlaceholder}
                        </div>
                        <div class="review-info">
                            <h6 class="review-name">
                                ${escapeHtml(review.user_name)}
                                ${statusBadge}
                            </h6>
                            <div class="review-rating">${stars}</div>
                        </div>
                    </div>
                    <div class="review-body">
                        <p class="review-comment">${escapeHtml(review.comment || '').replace(/\n/g, '<br>')}</p>
                        ${productInfo}
                    </div>
                    <div class="review-footer">
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> ${review.created_at_formatted}
                        </small>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Hàm escape HTML
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }
    
    // Hàm load reviews
    async function loadReviews(showNewIndicator = false) {
        try {
            const url = lastReviewId > 0 
                ? `${reviewsApiUrl}?limit=10&last_id=${lastReviewId}`
                : `${reviewsApiUrl}?limit=10`;
            
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                // Luôn cập nhật stats (kể cả khi không có reviews)
                if (data.stats) {
                    updateReviewStats(data.stats);
                }
                
                // Nếu có reviews mới
                if (data.reviews && data.reviews.length > 0) {
                    if (lastReviewId === 0) {
                        // Lần đầu load - render tất cả
                        renderAllReviews(data.reviews);
                        // Ẩn fallback nếu có
                        if (reviewsFallback) {
                            reviewsFallback.style.display = 'none';
                        }
                    } else {
                        // Có reviews mới - thêm vào đầu
                        addNewReviews(data.reviews);
                        if (showNewIndicator && data.reviews.length > 0) {
                            showNewReviewsIndicator(data.reviews.length);
                        }
                    }
                    
                    // Cập nhật lastReviewId
                    if (data.reviews.length > 0) {
                        lastReviewId = Math.max(...data.reviews.map(r => r.id), lastReviewId);
                    }
                } else {
                    // Không có reviews - hiển thị fallback nếu đây là lần đầu load
                    if (lastReviewId === 0) {
                        if (reviewsFallback && reviewsContainer) {
                            // Kiểm tra xem container có đang hiển thị loading không
                            const loadingSpinner = reviewsContainer.querySelector('.col-12.text-center');
                            if (loadingSpinner) {
                                reviewsFallback.style.display = 'flex';
                                reviewsContainer.style.display = 'none';
                            }
                        }
                    }
                }
            } else {
                console.error('API returned error:', data.message || 'Unknown error');
                // Hiển thị fallback nếu lỗi
                if (lastReviewId === 0 && reviewsContainer) {
                    const loadingSpinner = reviewsContainer.querySelector('.col-12.text-center');
                    if (loadingSpinner && reviewsFallback) {
                        reviewsFallback.style.display = 'flex';
                        reviewsContainer.style.display = 'none';
                    }
                }
            }
        } catch (error) {
            console.error('Error loading reviews:', error);
            // Hiển thị fallback nếu lỗi
            if (lastReviewId === 0 && reviewsContainer) {
                const loadingSpinner = reviewsContainer.querySelector('.col-12.text-center');
                if (loadingSpinner && reviewsFallback) {
                    reviewsFallback.style.display = 'flex';
                    reviewsContainer.style.display = 'none';
                } else if (loadingSpinner) {
                    loadingSpinner.innerHTML = '<p class="text-danger">Không thể tải đánh giá. Vui lòng thử lại sau.</p>';
                }
            }
        }
    }
    
    // Hàm render tất cả reviews
    function renderAllReviews(reviews) {
        if (!reviewsContainer) return;
        
        if (reviews.length === 0) {
            // Không có reviews - hiển thị fallback
            if (reviewsFallback) {
                reviewsFallback.style.display = 'flex';
                reviewsContainer.style.display = 'none';
            } else {
                // Xóa loading spinner
                const loadingSpinner = reviewsContainer.querySelector('.col-12.text-center');
                if (loadingSpinner) {
                    loadingSpinner.remove();
                }
                reviewsContainer.innerHTML = '<div class="col-12 text-center py-5"><p class="text-muted"><?php echo lang('common_no_reviews'); ?></p></div>';
            }
            return;
        }
        
        // Ẩn fallback nếu có reviews
        if (reviewsFallback) {
            reviewsFallback.style.display = 'none';
        }
        reviewsContainer.style.display = 'flex';
        
        // Xóa loading spinner
        const loadingSpinner = reviewsContainer.querySelector('.col-12.text-center');
        if (loadingSpinner) {
            loadingSpinner.remove();
        }
        
        reviewsContainer.innerHTML = reviews.map((review, index) => 
            renderReviewCard(review, index)
        ).join('');
        
        currentReviews = reviews;
        
        // Re-initialize AOS
        if (typeof AOS !== 'undefined') {
            AOS.refresh();
        }
    }
    
    // Hàm thêm reviews mới
    function addNewReviews(newReviews) {
        if (!reviewsContainer || newReviews.length === 0) return;
        
        // Lọc bỏ reviews đã có
        const existingIds = new Set(currentReviews.map(r => r.id));
        const trulyNewReviews = newReviews.filter(r => !existingIds.has(r.id));
        
        if (trulyNewReviews.length === 0) return;
        
        // Đảm bảo container có class row
        if (!reviewsContainer.classList.contains('row')) {
            reviewsContainer.classList.add('row', 'g-4');
        }
        
        // Thêm vào đầu container với animation
        trulyNewReviews.forEach((review, index) => {
            const newCard = renderReviewCard(review, currentReviews.length + index, true);
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = newCard.trim();
            const cardElement = tempDiv.firstElementChild;
            
            // Thêm vào đầu container
            reviewsContainer.insertBefore(cardElement, reviewsContainer.firstChild);
            
            // Trigger animation sau khi element được thêm vào DOM
            requestAnimationFrame(() => {
                cardElement.querySelector('.review-card').classList.add('new-review');
            });
        });
        
        // Cập nhật currentReviews (giữ 10 reviews mới nhất)
        currentReviews = [...trulyNewReviews, ...currentReviews].slice(0, 10);
        
        // Giới hạn số lượng reviews hiển thị (giữ 10 reviews mới nhất)
        setTimeout(() => {
            const reviewElements = Array.from(reviewsContainer.querySelectorAll('[data-review-id]'));
            if (reviewElements.length > 10) {
                // Sắp xếp theo ID và giữ lại 10 reviews mới nhất
                reviewElements.sort((a, b) => {
                    const idA = parseInt(a.dataset.reviewId);
                    const idB = parseInt(b.dataset.reviewId);
                    return idB - idA; // Sắp xếp giảm dần (mới nhất trước)
                });
                
                // Xóa các reviews cũ hơn
                for (let i = 10; i < reviewElements.length; i++) {
                    reviewElements[i].remove();
                }
            }
        }, 100);
        
        // Re-initialize AOS
        setTimeout(() => {
            if (typeof AOS !== 'undefined') {
                AOS.refresh();
            }
        }, 200);
    }
    
    // Hàm cập nhật stats
    function updateReviewStats(stats) {
        const statsContainer = document.getElementById('reviewStatsContainer');
        const ratingNumber = document.getElementById('ratingNumber');
        const ratingStars = document.getElementById('ratingStars');
        const totalReviewsText = document.getElementById('totalReviewsText');
        const viewMoreBtn = document.getElementById('viewMoreReviewsBtn');
        
        if (stats && stats.total_reviews > 0) {
            // Hiển thị stats container
            if (statsContainer) {
                statsContainer.style.display = 'block';
            }
            
            if (ratingNumber) {
                ratingNumber.textContent = stats.avg_rating.toFixed(1);
            }
            
            if (ratingStars) {
                const avgRating = Math.round(stats.avg_rating);
                ratingStars.innerHTML = Array.from({length: 5}, (_, i) => {
                    const isActive = i < avgRating;
                    return `<i class="fas fa-star ${isActive ? 'text-warning' : 'text-muted'}"></i>`;
                }).join('');
            }
            
        if (totalReviewsText) {
            const lang = '<?php echo $lang; ?>';
            const basedOn = lang === 'vi' ? 'Dựa trên' : 'Based on';
            const reviews = lang === 'vi' ? 'đánh giá' : 'reviews';
            totalReviewsText.textContent = `${basedOn} ${stats.total_reviews} ${reviews}`;
        }
            
            // Hiển thị nút xem thêm nếu có > 6 reviews
            if (viewMoreBtn && stats.total_reviews > 6) {
                viewMoreBtn.style.display = 'block';
            } else if (viewMoreBtn) {
                viewMoreBtn.style.display = 'none';
            }
        } else {
            // Ẩn stats nếu không có reviews
            if (statsContainer) {
                statsContainer.style.display = 'none';
            }
            if (viewMoreBtn) {
                viewMoreBtn.style.display = 'none';
            }
        }
    }
    
    // Hàm hiển thị indicator có reviews mới
    function showNewReviewsIndicator(count) {
        if (indicator) {
            const lang = '<?php echo $lang; ?>';
            const message = lang === 'vi' 
                ? `Có ${count} đánh giá mới!`
                : `${count} new review${count > 1 ? 's' : ''}!`;
            indicator.querySelector('span').textContent = message;
            indicator.classList.add('show');
            
            setTimeout(() => {
                indicator.classList.remove('show');
            }, 3000);
        }
    }
    
    // Load reviews ban đầu
    loadReviews();
    
    // Set interval để check reviews mới mỗi 10 giây
    updateInterval = setInterval(() => {
        loadReviews(true); // true = hiển thị indicator khi có review mới
    }, 10000); // 10 giây
    
    // Cleanup khi page unload
    window.addEventListener('beforeunload', () => {
        if (updateInterval) {
            clearInterval(updateInterval);
        }
    });
    
    // Expose function để có thể gọi từ bên ngoài
    window.reloadReviews = () => {
        lastReviewId = 0;
        loadReviews();
    };
}

// Review Form Function
function initReviewForm() {
    const reviewFormSection = document.getElementById('reviewFormSection');
    const reviewLoginPrompt = document.getElementById('reviewLoginPrompt');
    const reviewForm = document.getElementById('reviewForm');
    const submitBtn = document.getElementById('submitReviewBtn');
    const formMessage = document.getElementById('reviewFormMessage');
    
    // Kiểm tra xem user đã đăng nhập chưa
    // Giả sử có biến global hoặc check từ PHP session
    const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    
    if (isLoggedIn) {
        // Hiển thị form đánh giá
        if (reviewFormSection) {
            reviewFormSection.style.display = 'block';
        }
        if (reviewLoginPrompt) {
            reviewLoginPrompt.style.display = 'none';
        }
        
        // Xử lý submit form
        if (reviewForm) {
            reviewForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const formData = new FormData(reviewForm);
                const rating = formData.get('rating');
                const comment = formData.get('comment').trim();
                const productId = formData.get('product_id') || null;
                
                // Validate
                if (!rating) {
                    showFormMessage('<?php echo $lang == 'vi' ? 'Vui lòng chọn mức đánh giá!' : 'Please select a rating!'; ?>', 'danger');
                    return;
                }
                
                if (!comment || comment.length < 10) {
                    showFormMessage('<?php echo lang('reviews_min_chars'); ?>', 'danger');
                    return;
                }
                
                // Disable button
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span><?php echo $lang == 'vi' ? 'Đang gửi...' : 'Sending...'; ?>';
                }
                
                try {
                    const response = await fetch('<?php echo SITE_URL; ?>/api/submit_review.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            rating: parseInt(rating),
                            comment: comment,
                            product_id: productId ? parseInt(productId) : null
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        showFormMessage('<?php echo lang('reviews_success'); ?>', 'success');
                        reviewForm.reset();
                        
                        // Reset rating stars
                        document.querySelectorAll('.rating-input input[type="radio"]').forEach(radio => {
                            radio.checked = false;
                        });
                        
                        // Reload reviews ngay lập tức để hiển thị review mới
                        setTimeout(() => {
                            if (window.reloadReviews) {
                                window.reloadReviews();
                            }
                        }, 500);
                    } else {
                        showFormMessage(data.message || '<?php echo $lang == 'vi' ? 'Có lỗi xảy ra, vui lòng thử lại!' : 'An error occurred, please try again!'; ?>', 'danger');
                    }
                } catch (error) {
                    console.error('Error submitting review:', error);
                    showFormMessage('<?php echo $lang == 'vi' ? 'Có lỗi xảy ra khi gửi đánh giá. Vui lòng thử lại!' : 'An error occurred while submitting the review. Please try again!'; ?>', 'danger');
                } finally {
                    // Enable button
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> <?php echo lang('reviews_submit'); ?>';
                    }
                }
            });
        }
    } else {
        // Hiển thị prompt đăng nhập
        if (reviewFormSection) {
            reviewFormSection.style.display = 'none';
        }
        if (reviewLoginPrompt) {
            reviewLoginPrompt.style.display = 'block';
        }
    }
    
    function showFormMessage(message, type) {
        if (formMessage) {
            formMessage.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
            
            // Auto dismiss sau 5 giây
            setTimeout(() => {
                const alert = formMessage.querySelector('.alert');
                if (alert) {
                    alert.classList.remove('show');
                    setTimeout(() => {
                        formMessage.innerHTML = '';
                    }, 300);
                }
            }, 5000);
        }
    }
}

// FAQ Accordion Toggle
function toggleFaq(num) {
    const item = document.querySelector(`.faq-item:nth-child(${num})`);
    const answer = item.querySelector('.faq-answer');
    const chevron = item.querySelector('.faq-chevron');
    const question = item.querySelector('.faq-question');
    const questionText = item.querySelector('.faq-question-text');
    
    // Close all other items
    document.querySelectorAll('.faq-item').forEach((otherItem, index) => {
        if (index + 1 !== num) {
            otherItem.classList.remove('active');
            otherItem.querySelector('.faq-answer').classList.remove('show');
            const otherChevron = otherItem.querySelector('.faq-chevron');
            otherChevron.classList.remove('fa-chevron-up');
            otherChevron.classList.add('fa-chevron-down');
            otherItem.querySelector('.faq-question').classList.remove('active');
            otherItem.querySelector('.faq-question-text').classList.remove('active');
        }
    });
    
    // Toggle current item
    if (item.classList.contains('active')) {
        item.classList.remove('active');
        answer.classList.remove('show');
        chevron.classList.remove('fa-chevron-up');
        chevron.classList.add('fa-chevron-down');
        question.classList.remove('active');
        questionText.classList.remove('active');
    } else {
        item.classList.add('active');
        answer.classList.add('show');
        chevron.classList.remove('fa-chevron-down');
        chevron.classList.add('fa-chevron-up');
        question.classList.add('active');
        questionText.classList.add('active');
    }
}
</script>
</body>
</html>

	<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>
	<style>img:is([sizes="auto" i], [sizes^="auto," i]) { contain-intrinsic-size: 3000px 1500px }</style>
	
	<!-- Chatbot -->
	<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/chatbot.css">
	<div class="chatbot-container">
		<div class="chatbot-window" id="chatbotWindow">
			<div class="chatbot-header">
				<div class="chatbot-header-info">
					<div class="chatbot-avatar">
						<i class="fas fa-robot"></i>
					</div>
					<div class="chatbot-header-text">
						<h4><?php echo lang('chatbot_title'); ?></h4>
						<p><?php echo lang('chatbot_subtitle'); ?></p>
					</div>
				</div>
				<button class="chatbot-close" onclick="toggleChatbot()">
					<i class="fas fa-times"></i>
				</button>
			</div>
			<div class="chatbot-messages" id="chatbotMessages">
				<div class="welcome-message">
					<h5>👋 <?php echo lang('chatbot_welcome'); ?></h5>
					<p><?php echo lang('chatbot_intro'); ?></p>
					<ul style="text-align: left; display: inline-block; margin-top: 10px;">
						<li><?php echo lang('chatbot_help_1'); ?></li>
						<li><?php echo lang('chatbot_help_2'); ?></li>
						<li><?php echo lang('chatbot_help_3'); ?></li>
						<li><?php echo lang('chatbot_help_4'); ?></li>
					</ul>
					<p style="margin-top: 15px;"><?php echo lang('chatbot_start'); ?></p>
				</div>
			</div>
			<div class="chatbot-input-container">
				<input type="text" class="chatbot-input" id="chatbotInput" placeholder="<?php echo lang('common_chatbot_placeholder'); ?>" onkeypress="handleChatbotKeyPress(event)">
				<button class="chatbot-send" onclick="sendChatbotMessage()" id="chatbotSendBtn">
					<i class="fas fa-paper-plane"></i>
				</button>
			</div>
		</div>
		<button class="chatbot-button" onclick="toggleChatbot()" id="chatbotButton">
			<i class="fas fa-comments"></i>
		</button>
	</div>
	<script>
		const CHATBOT_API_URL = '<?php echo SITE_URL; ?>/api/chatbot.php';
	</script>
	<script src="<?php echo SITE_URL; ?>/assets/js/chatbot.js"></script>
