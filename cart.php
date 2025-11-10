<?php
require_once 'config/config.php';
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

$title = 'Giỏ hàng';
$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Lấy giỏ hàng
$query = "SELECT c.*, p.name, p.price, p.sale_price, p.image, p.quantity as stock 
          FROM carts c 
          INNER JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = :user_id AND p.status = 'active'
          ORDER BY c.created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;

include 'includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-shopping-cart"></i> Giỏ hàng của bạn</h2>
    
    <?php if(empty($cart_items)): ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle fa-3x mb-3 d-block"></i>
            <h5>Giỏ hàng của bạn đang trống</h5>
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary mt-3">
                <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Đơn giá</th>
                                    <th>Số lượng</th>
                                    <th>Thành tiền</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($cart_items as $item): 
                                    $price = $item['sale_price'] ?: $item['price'];
                                    $subtotal = $price * $item['quantity'];
                                    $total += $subtotal;
                                ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo $item['image'] ?: getProductPlaceholder(80, 80); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                     class="me-3" style="width: 80px; height: 80px; object-fit: cover;"
                                                     loading="lazy">
                                                <div>
                                                    <h6><?php echo htmlspecialchars($item['name']); ?></h6>
                                                    <?php if($item['stock'] < $item['quantity']): ?>
                                                        <small class="text-danger">Chỉ còn <?php echo $item['stock']; ?> sản phẩm</small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <?php echo number_format($price); ?>đ
                                        </td>
                                        <td class="align-middle">
                                            <div class="input-group" style="max-width: 120px;">
                                                <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] - 1; ?>)">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number" class="form-control text-center" value="<?php echo $item['quantity']; ?>" 
                                                       min="1" max="<?php echo $item['stock']; ?>" readonly>
                                                <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] + 1; ?>)">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="align-middle fw-bold text-danger">
                                            <?php echo number_format($subtotal); ?>đ
                                        </td>
                                        <td class="align-middle">
                                            <button class="btn btn-sm btn-outline-danger" onclick="removeItem(<?php echo $item['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Thông tin đơn hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tạm tính:</span>
                            <strong><?php echo number_format($total); ?>đ</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Phí vận chuyển:</span>
                            <strong class="text-success">Miễn phí</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <h5>Tổng cộng:</h5>
                            <h5 class="text-danger"><?php echo number_format($total); ?>đ</h5>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="<?php echo SITE_URL; ?>/checkout.php" class="btn btn-danger btn-lg">
                                <i class="fas fa-credit-card"></i> Thanh toán
                            </a>
                            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function updateQuantity(cartId, newQuantity) {
    if (newQuantity < 1) {
        if (!confirm('Bạn có muốn xóa sản phẩm này khỏi giỏ hàng?')) {
            return;
        }
    }
    
    fetch('<?php echo SITE_URL; ?>/cart_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=update&cart_id=' + cartId + '&quantity=' + newQuantity
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cập nhật số lượng giỏ hàng trước khi reload
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            }
            location.reload();
        }
    });
}

function removeItem(cartId) {
    if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
        return;
    }
    
    fetch('<?php echo SITE_URL; ?>/cart_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=remove&cart_id=' + cartId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cập nhật số lượng giỏ hàng trước khi reload
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            }
            location.reload();
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
