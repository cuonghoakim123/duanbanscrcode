<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/lang.php';

$title = lang('products_page_title');

$database = new Database();
$db = $database->getConnection();

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Filter
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build query
$where = "WHERE p.status = 'active'";
$params = [];

if ($category) {
    $where .= " AND c.slug = :category";
    $params[':category'] = $category;
}

if ($search) {
    $where .= " AND p.name LIKE :search";
    $params[':search'] = '%' . $search . '%';
}

// Sort
$order = 'ORDER BY p.created_at DESC';
switch($sort) {
    case 'price_asc':
        $order = 'ORDER BY COALESCE(p.sale_price, p.price) ASC';
        break;
    case 'price_desc':
        $order = 'ORDER BY COALESCE(p.sale_price, p.price) DESC';
        break;
    case 'name':
        $order = 'ORDER BY p.name ASC';
        break;
}

// Đếm tổng sản phẩm
$count_query = "SELECT COUNT(*) as total FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                $where";
$stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total_products = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_products / $limit);

// Lấy danh sách sản phẩm
$query = "SELECT p.*, c.name as category_name, c.slug as category_slug 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          $where $order 
          LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh mục
$query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hàm helper để lấy URL ảnh sản phẩm
function getProductImageUrl($image) {
    if (empty($image)) {
        return null;
    }
    
    // Nếu là URL đầy đủ (bắt đầu bằng http:// hoặc https://)
    if (strpos($image, 'http://') === 0 || strpos($image, 'https://') === 0) {
        return $image;
    }
    
    // Nếu bắt đầu bằng / (đường dẫn tuyệt đối từ root)
    if (strpos($image, '/') === 0) {
        return SITE_URL . $image;
    }
    
    // Nếu chứa uploads/products (đường dẫn tương đối)
    if (strpos($image, 'uploads/products') !== false) {
        // Đảm bảo bắt đầu bằng /
        if (strpos($image, '/') !== 0) {
            $image = '/' . $image;
        }
        return SITE_URL . $image;
    }
    
    // Nếu chỉ là tên file, thêm đường dẫn uploads/products
    return SITE_URL . '/uploads/products/' . basename($image);
}

include 'includes/header.php';
?>

<!-- Page Header -->
<div class="products-header bg-gradient-primary text-white py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-3"><?php echo lang('products_header_title'); ?></h1>
                <p class="lead mb-0"><?php echo str_replace('{count}', $total_products, lang('products_header_subtitle')); ?></p>
            </div>
            <div class="col-lg-6">
                <form method="GET" action="" class="search-box">
                    <div class="input-group input-group-lg">
                        <input type="text" name="search" class="form-control" placeholder="<?php echo lang('products_search_placeholder'); ?>" value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-light" type="submit">
                            <i class="fas fa-search"></i> <?php echo lang('products_search_btn'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <!-- Sidebar Filter -->
        <div class="col-lg-3 mb-4">
            <div class="filter-sidebar">
                <div class="filter-header">
                    <h5><i class="fas fa-sliders-h"></i> <?php echo lang('products_filter'); ?></h5>
                </div>
                
                <!-- Categories -->
                <div class="filter-section">
                    <h6 class="filter-title"><?php echo lang('products_categories'); ?></h6>
                    <ul class="category-list">
                        <li>
                            <a href="<?php echo SITE_URL; ?>/products.php" class="category-link <?php echo !$category ? 'active' : ''; ?>">
                                <i class="fas fa-th"></i> <?php echo lang('products_all_products'); ?>
                                <span class="count"><?php echo $total_products; ?></span>
                            </a>
                        </li>
                        <?php foreach($categories as $cat): ?>
                            <li>
                                <a href="?category=<?php echo $cat['slug']; ?>" 
                                   class="category-link <?php echo $category == $cat['slug'] ? 'active' : ''; ?>">
                                    <i class="fas fa-folder"></i> <?php echo htmlspecialchars($cat['name']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Price Range -->
                <div class="filter-section">
                    <h6 class="filter-title"><?php echo lang('products_price_range'); ?></h6>
                    <div class="price-filter">
                        <label class="price-option">
                            <input type="radio" name="price_range" value="all" checked>
                            <span><?php echo lang('products_price_all'); ?></span>
                        </label>
                        <label class="price-option">
                            <input type="radio" name="price_range" value="under5">
                            <span><?php echo lang('products_price_under5'); ?></span>
                        </label>
                        <label class="price-option">
                            <input type="radio" name="price_range" value="5to10">
                            <span><?php echo lang('products_price_5to10'); ?></span>
                        </label>
                        <label class="price-option">
                            <input type="radio" name="price_range" value="10to20">
                            <span><?php echo lang('products_price_10to20'); ?></span>
                        </label>
                        <label class="price-option">
                            <input type="radio" name="price_range" value="over20">
                            <span><?php echo lang('products_price_over20'); ?></span>
                        </label>
                    </div>
                </div>
                
                <!-- Contact CTA -->
                <div class="sidebar-cta">
                    <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                    <h6><?php echo lang('products_need_consult'); ?></h6>
                    <p class="small mb-3"><?php echo lang('products_consult_desc'); ?></p>
                    <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-primary w-100">
                        <i class="fas fa-phone-alt"></i> <?php echo lang('products_call_now'); ?>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Products List -->
        <div class="col-lg-9">
            <!-- Toolbar -->
            <div class="products-toolbar">
                <div class="toolbar-left">
                    <span class="result-count"><?php echo lang('products_showing'); ?> <strong><?php echo count($products); ?></strong> <?php echo lang('products_in_total'); ?> <strong><?php echo $total_products; ?></strong> <?php echo lang('products_products'); ?></span>
                </div>
                <div class="toolbar-right">
                    <select class="form-select" onchange="window.location.href='?<?php echo http_build_query(array_merge($_GET, ['sort' => ''])); ?>&sort=' + this.value">
                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>><?php echo lang('products_sort_newest'); ?></option>
                        <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>><?php echo lang('products_sort_price_asc'); ?></option>
                        <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>><?php echo lang('products_sort_price_desc'); ?></option>
                        <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>><?php echo lang('products_sort_name'); ?></option>
                    </select>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="row g-4">
                <?php if(empty($products)): ?>
                    <div class="col-12">
                        <div class="empty-state">
                            <i class="fas fa-inbox fa-5x text-muted mb-3"></i>
                            <h4><?php echo lang('products_no_results'); ?></h4>
                            <p class="text-muted"><?php echo $lang == 'vi' ? 'Vui lòng thử lại với từ khóa khác hoặc xem tất cả sản phẩm' : 'Please try again with different keywords or view all products'; ?></p>
                            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary"><?php echo $lang == 'vi' ? 'Xem tất cả' : 'View all'; ?></a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach($products as $product): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="product-card">
                                <div class="product-image">
                                    <?php if($product['sale_price']): ?>
                                        <div class="sale-badge">
                                            -<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%
                                        </div>
                                    <?php endif; ?>
                                    <?php if($product['featured']): ?>
                                        <div class="featured-badge">
                                            <i class="fas fa-star"></i> <?php echo $lang == 'vi' ? 'Nổi bật' : 'Featured'; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php 
                                    $product_image_url = getProductImageUrl($product['image']);
                                    if ($product_image_url): 
                                    ?>
                                        <img src="<?php echo htmlspecialchars($product_image_url); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             class="product-img"
                                             onerror="this.onerror=null; this.src=''; this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                        <div class="image-placeholder" style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                            <i class="fas fa-laptop-code fa-4x text-white opacity-50"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="image-placeholder" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                            <i class="fas fa-laptop-code fa-4x text-white opacity-50"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="product-overlay">
                                        <a href="<?php echo SITE_URL; ?>/product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-light btn-sm">
                                            <i class="fas fa-eye"></i> <?php echo lang('products_view_detail'); ?>
                                        </a>
                                        <button class="btn btn-primary btn-sm" onclick="addToCart(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-cart-plus"></i> <?php echo $lang == 'vi' ? 'Thêm vào giỏ' : 'Add to cart'; ?>
                                        </button>
                                    </div>
                                </div>
                                <div class="product-info">
                                    <span class="product-category">
                                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['category_name']); ?>
                                    </span>
                                    <h5 class="product-title">
                                        <a href="<?php echo SITE_URL; ?>/product_detail.php?id=<?php echo $product['id']; ?>">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </a>
                                    </h5>
                                    <p class="product-description"><?php echo htmlspecialchars(mb_substr($product['description'], 0, 80)); ?>...</p>
                                    <div class="product-rating">
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <span class="reviews">(50+)</span>
                                    </div>
                                    <div class="product-price">
                                        <?php if($product['sale_price']): ?>
                                            <span class="current-price"><?php echo number_format($product['sale_price']); ?>đ</span>
                                            <span class="original-price"><?php echo number_format($product['price']); ?>đ</span>
                                        <?php else: ?>
                                            <span class="current-price"><?php echo number_format($product['price']); ?>đ</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center pagination-modern">
                        <?php if($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.products-header {
    position: relative;
    overflow: hidden;
}

.search-box {
    background: rgba(255,255,255,0.1);
    padding: 20px;
    border-radius: 15px;
}

.filter-sidebar {
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 20px rgba(0,0,0,0.08);
    overflow: hidden;
}

.filter-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
}

.filter-header h5 {
    margin: 0;
    font-weight: 600;
}

.filter-section {
    padding: 20px;
    border-bottom: 1px solid #f0f0f0;
}

.filter-title {
    font-weight: 700;
    margin-bottom: 15px;
    color: #333;
}

.category-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.category-list li {
    margin-bottom: 10px;
}

.category-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 15px;
    border-radius: 8px;
    color: #666;
    text-decoration: none;
    transition: all 0.3s ease;
}

.category-link:hover {
    background: #f8f9fa;
    color: #0d6efd;
    transform: translateX(5px);
}

.category-link.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.category-link i {
    margin-right: 10px;
}

.count {
    background: rgba(0,0,0,0.1);
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 12px;
}

.price-filter {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.price-option {
    display: flex;
    align-items: center;
    padding: 8px;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.price-option:hover {
    background: #f8f9fa;
}

.price-option input {
    margin-right: 10px;
}

.sidebar-cta {
    padding: 25px;
    text-align: center;
    background: #f8f9fa;
}

.products-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: white;
    border-radius: 10px;
    margin-bottom: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.result-count {
    color: #666;
}

.toolbar-right select {
    min-width: 200px;
}

.product-card {
    border-radius: 15px;
    overflow: hidden;
    background: white;
    box-shadow: 0 2px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.product-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.product-image {
    position: relative;
    overflow: hidden;
    aspect-ratio: 4/3;
    background: #f8f9fa;
}

.product-image .product-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
    display: block;
    position: relative;
    z-index: 1;
}

.product-card:hover .product-image .product-img {
    transform: scale(1.1);
}

.image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 1;
}

.sale-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    background: #dc3545;
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 14px;
    z-index: 2;
}

.featured-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: #ffc107;
    color: #333;
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 12px;
    z-index: 2;
}

.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 10;
}

.product-card:hover .product-overlay {
    opacity: 1;
}

.product-info {
    padding: 20px;
}

.product-category {
    display: inline-block;
    font-size: 12px;
    color: #666;
    margin-bottom: 10px;
}

.product-category i {
    margin-right: 5px;
}

.product-title {
    margin: 10px 0;
    font-weight: 600;
    line-height: 1.4;
}

.product-title a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s ease;
}

.product-title a:hover {
    color: #0d6efd;
}

.product-description {
    font-size: 14px;
    color: #666;
    margin-bottom: 10px;
    line-height: 1.5;
}

.product-rating {
    margin-bottom: 15px;
    font-size: 14px;
}

.reviews {
    color: #999;
    margin-left: 5px;
}

.product-price {
    display: flex;
    align-items: center;
    gap: 10px;
}

.current-price {
    font-size: 24px;
    font-weight: 700;
    color: #dc3545;
}

.original-price {
    font-size: 16px;
    color: #999;
    text-decoration: line-through;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 15px;
}

.pagination-modern .page-link {
    border-radius: 8px;
    margin: 0 5px;
    border: none;
    color: #666;
}

.pagination-modern .page-item.active .page-link {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

@media (max-width: 768px) {
    .products-toolbar {
        flex-direction: column;
        gap: 15px;
    }
    
    .toolbar-right select {
        width: 100%;
    }
}
</style>

<script>
function addToCart(productId) {
    <?php if(!isset($_SESSION['user_id'])): ?>
        alert('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!');
        window.location.href = '<?php echo SITE_URL; ?>/auth/login.php';
        return;
    <?php endif; ?>
    
    const quantity = 1; // Mặc định số lượng là 1
    
    fetch('<?php echo SITE_URL; ?>/cart_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=add&product_id=' + productId + '&quantity=' + quantity
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cập nhật số lượng giỏ hàng
            if (data.cart_count !== undefined) {
                const cartCount = document.getElementById('cart-count');
                if (cartCount) {
                    cartCount.textContent = data.cart_count;
                    cartCount.style.display = data.cart_count > 0 ? 'block' : 'none';
                }
            } else {
                if (typeof updateCartCount === 'function') {
                    updateCartCount();
                }
            }
            
            // Hiển thị thông báo thành công
            if (typeof showToast === 'function') {
                showToast('Đã thêm sản phẩm vào giỏ hàng!', 'success');
            } else {
                alert('Đã thêm sản phẩm vào giỏ hàng!');
            }
        } else {
            alert(data.message || 'Có lỗi xảy ra!');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi thêm vào giỏ hàng!');
    });
}
</script>

<?php include 'includes/footer.php'; ?>
