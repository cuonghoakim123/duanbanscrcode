<?php
require_once 'config/config.php';
require_once 'config/database.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: products.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Lấy thông tin sản phẩm
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.id = :id AND p.status = 'active'";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $product_id);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: products.php');
    exit();
}

// Tăng lượt xem
$query = "UPDATE products SET views = views + 1 WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $product_id);
$stmt->execute();

$title = $product['name'];

// Sản phẩm liên quan
$query = "SELECT * FROM products WHERE category_id = :category_id AND id != :id AND status = 'active' LIMIT 4";
$stmt = $db->prepare($query);
$stmt->bindParam(':category_id', $product['category_id']);
$stmt->bindParam(':id', $product_id);
$stmt->execute();
$related_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/products.php">Sản phẩm</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <!-- Product Image -->
        <div class="col-md-5">
            <img src="<?php echo $product['image'] ?: getProductPlaceholder(500, 500); ?>" 
                 class="img-fluid rounded shadow" alt="<?php echo htmlspecialchars($product['name']); ?>"
                 loading="lazy">
        </div>
        
        <!-- Product Info -->
        <div class="col-md-7">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <div class="mb-3">
                <span class="badge bg-secondary"><?php echo htmlspecialchars($product['category_name']); ?></span>
                <span class="text-muted ms-3"><i class="fas fa-eye"></i> <?php echo $product['views']; ?> lượt xem</span>
            </div>
            
            <div class="price mb-4">
                <?php if($product['sale_price']): ?>
                    <h3 class="text-danger"><?php echo number_format($product['sale_price']); ?>đ</h3>
                    <p class="text-muted">
                        Giá gốc: <span class="text-decoration-line-through"><?php echo number_format($product['price']); ?>đ</span>
                        <span class="badge bg-danger ms-2">
                            Giảm <?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%
                        </span>
                    </p>
                <?php else: ?>
                    <h3 class="text-danger"><?php echo number_format($product['price']); ?>đ</h3>
                <?php endif; ?>
            </div>
            
            <div class="mb-3">
                <strong>Mô tả:</strong>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>
            
            <div class="mb-3">
                <strong>Tình trạng:</strong> 
                <?php if($product['quantity'] > 0): ?>
                    <span class="text-success"><i class="fas fa-check-circle"></i> Còn hàng (<?php echo $product['quantity']; ?> sản phẩm)</span>
                <?php else: ?>
                    <span class="text-danger"><i class="fas fa-times-circle"></i> Hết hàng</span>
                <?php endif; ?>
            </div>
            
            <?php if($product['quantity'] > 0): ?>
                <div class="mb-4">
                    <div class="input-group" style="max-width: 200px;">
                        <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(-1)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" class="form-control text-center" id="quantity" value="1" min="1" max="<?php echo $product['quantity']; ?>">
                        <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(1)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex">
                    <button class="btn btn-primary btn-lg" onclick="addToCart(<?php echo $product_id; ?>)">
                        <i class="fas fa-shopping-cart"></i> Thêm vào giỏ hàng
                    </button>
                    <button class="btn btn-danger btn-lg" onclick="buyNow(<?php echo $product_id; ?>)">
                        <i class="fas fa-bolt"></i> Mua ngay
                    </button>
                </div>
            <?php else: ?>
                <button class="btn btn-secondary btn-lg" disabled>
                    <i class="fas fa-times"></i> Sản phẩm hết hàng
                </button>
            <?php endif; ?>
            
            <!-- Product Features -->
            <div class="mt-4 p-3 bg-light rounded">
                <h6><i class="fas fa-shield-alt text-primary"></i> Chính sách bán hàng</h6>
                <ul class="mb-0">
                    <li>Bảo hành chính hãng 12 tháng</li>
                    <li>1 đổi 1 trong 7 ngày nếu có lỗi từ nhà sản xuất</li>
                    <li>Giao hàng toàn quốc, thanh toán khi nhận hàng</li>
                    <li>Hỗ trợ trả góp 0% lãi suất</li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Product Details -->
    <div class="row mt-5">
        <div class="col-12">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#description">Mô tả chi tiết</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#specs">Thông số kỹ thuật</a>
                </li>
            </ul>
            <div class="tab-content p-4 border border-top-0">
                <div id="description" class="tab-pane fade show active">
                    <?php echo $product['content'] ?: '<p>Đang cập nhật...</p>'; ?>
                </div>
                <div id="specs" class="tab-pane fade">
                    <p>Đang cập nhật...</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php if(!empty($related_products)): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h4 class="mb-4">Sản phẩm liên quan</h4>
                <div class="row">
                    <?php foreach($related_products as $rel_product): ?>
                        <div class="col-md-3 mb-4">
                            <div class="card h-100 shadow-sm product-card">
                                <img src="<?php echo $rel_product['image'] ?: getProductPlaceholder(300, 200); ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($rel_product['name']); ?>"
                                     style="height: 200px; object-fit: cover;"
                                     loading="lazy">
                                <div class="card-body">
                                    <h6 class="card-title"><?php echo htmlspecialchars($rel_product['name']); ?></h6>
                                    <div class="price mb-2">
                                        <span class="text-danger fw-bold">
                                            <?php echo number_format($rel_product['sale_price'] ?: $rel_product['price']); ?>đ
                                        </span>
                                    </div>
                                    <a href="?id=<?php echo $rel_product['id']; ?>" class="btn btn-sm btn-outline-primary w-100">
                                        Xem chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function changeQuantity(change) {
    const input = document.getElementById('quantity');
    const newValue = parseInt(input.value) + change;
    const max = parseInt(input.max);
    
    if (newValue >= 1 && newValue <= max) {
        input.value = newValue;
    }
}

function addToCart(productId) {
    <?php if(!isset($_SESSION['user_id'])): ?>
        alert('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!');
        window.location.href = '<?php echo SITE_URL; ?>/auth/login.php';
        return;
    <?php endif; ?>
    
    const quantity = document.getElementById('quantity').value;
    
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
                updateCartCount();
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

function buyNow(productId) {
    addToCart(productId);
    setTimeout(() => {
        window.location.href = '<?php echo SITE_URL; ?>/cart.php';
    }, 500);
}
</script>

<?php include 'includes/footer.php'; ?>
