<?php
require_once 'config/config.php';
require_once 'config/database.php';

$title = 'Trang chủ';

$database = new Database();
$db = $database->getConnection();

// Lấy sản phẩm nổi bật
$query = "SELECT * FROM products WHERE status = 'active' AND featured = 1 ORDER BY created_at DESC LIMIT 8";
$stmt = $db->prepare($query);
$stmt->execute();
$featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy sản phẩm mới nhất
$query = "SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC LIMIT 8";
$stmt = $db->prepare($query);
$stmt->execute();
$latest_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<!-- Hero Banner -->
<div class="hero-banner bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4 fw-bold">Chào mừng đến với <?php echo SITE_NAME; ?></h1>
                <p class="lead">Nơi cung cấp các sản phẩm công nghệ chính hãng, giá tốt nhất thị trường</p>
                <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-light btn-lg">
                    <i class="fas fa-shopping-cart"></i> Mua sắm ngay
                </a>
            </div>
            <div class="col-md-6">
                <img src="<?php echo SITE_URL; ?>/assets/images/banner.png" alt="Banner" class="img-fluid" 
                     onerror="this.src='<?php echo getPlaceholderImage(600, 400, 'Banner', 'e3f2fd', '1976d2'); ?>'"
                     loading="lazy">
            </div>
        </div>
    </div>
</div>

<!-- Danh mục sản phẩm -->
<div class="container my-5">
    <div class="row text-center">
        <div class="col-md-3 mb-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-mobile-alt fa-3x text-primary mb-3"></i>
                    <h5>Điện thoại</h5>
                    <a href="<?php echo SITE_URL; ?>/products.php?category=dien-thoai" class="btn btn-sm btn-outline-primary">Xem ngay</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-laptop fa-3x text-primary mb-3"></i>
                    <h5>Laptop</h5>
                    <a href="<?php echo SITE_URL; ?>/products.php?category=laptop" class="btn btn-sm btn-outline-primary">Xem ngay</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-tablet-alt fa-3x text-primary mb-3"></i>
                    <h5>Tablet</h5>
                    <a href="<?php echo SITE_URL; ?>/products.php?category=tablet" class="btn btn-sm btn-outline-primary">Xem ngay</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-headphones fa-3x text-primary mb-3"></i>
                    <h5>Phụ kiện</h5>
                    <a href="<?php echo SITE_URL; ?>/products.php?category=phu-kien" class="btn btn-sm btn-outline-primary">Xem ngay</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sản phẩm nổi bật -->
<div class="container my-5">
    <h2 class="text-center mb-4">
        <i class="fas fa-star text-warning"></i> Sản phẩm nổi bật
    </h2>
    <div class="row">
        <?php foreach($featured_products as $product): ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm product-card">
                    <?php if($product['sale_price']): ?>
                        <div class="badge bg-danger position-absolute m-2">
                            -<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%
                        </div>
                    <?php endif; ?>
                    <img src="<?php echo $product['image'] ?: getProductPlaceholder(300, 200); ?>" 
                         class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>"
                         style="height: 200px; object-fit: cover;"
                         loading="lazy">
                    <div class="card-body">
                        <h6 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h6>
                        <div class="price mb-2">
                            <?php if($product['sale_price']): ?>
                                <span class="text-danger fw-bold"><?php echo number_format($product['sale_price']); ?>đ</span>
                                <span class="text-muted text-decoration-line-through ms-2"><?php echo number_format($product['price']); ?>đ</span>
                            <?php else: ?>
                                <span class="text-danger fw-bold"><?php echo number_format($product['price']); ?>đ</span>
                            <?php endif; ?>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="<?php echo SITE_URL; ?>/product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Sản phẩm mới nhất -->
<div class="container my-5">
    <h2 class="text-center mb-4">
        <i class="fas fa-fire text-danger"></i> Sản phẩm mới nhất
    </h2>
    <div class="row">
        <?php foreach($latest_products as $product): ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm product-card">
                    <?php if($product['sale_price']): ?>
                        <div class="badge bg-danger position-absolute m-2">
                            -<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%
                        </div>
                    <?php endif; ?>
                    <img src="<?php echo $product['image'] ?: getProductPlaceholder(300, 200); ?>" 
                         class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>"
                         style="height: 200px; object-fit: cover;"
                         loading="lazy">
                    <div class="card-body">
                        <h6 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h6>
                        <div class="price mb-2">
                            <?php if($product['sale_price']): ?>
                                <span class="text-danger fw-bold"><?php echo number_format($product['sale_price']); ?>đ</span>
                                <span class="text-muted text-decoration-line-through ms-2"><?php echo number_format($product['price']); ?>đ</span>
                            <?php else: ?>
                                <span class="text-danger fw-bold"><?php echo number_format($product['price']); ?>đ</span>
                            <?php endif; ?>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="<?php echo SITE_URL; ?>/product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Ưu điểm -->
<div class="bg-light py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3">
                <i class="fas fa-shipping-fast fa-3x text-primary mb-3"></i>
                <h5>Giao hàng nhanh</h5>
                <p>Miễn phí vận chuyển đơn từ 500k</p>
            </div>
            <div class="col-md-3">
                <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                <h5>Bảo hành chính hãng</h5>
                <p>Cam kết 100% hàng chính hãng</p>
            </div>
            <div class="col-md-3">
                <i class="fas fa-sync-alt fa-3x text-primary mb-3"></i>
                <h5>Đổi trả dễ dàng</h5>
                <p>Đổi trả trong vòng 7 ngày</p>
            </div>
            <div class="col-md-3">
                <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                <h5>Hỗ trợ 24/7</h5>
                <p>Tư vấn nhiệt tình, chuyên nghiệp</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
