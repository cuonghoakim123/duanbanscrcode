<?php
require_once 'config/config.php';
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

$title = 'Thanh toán';
$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Lấy giỏ hàng
$query = "SELECT c.*, p.name, p.price, p.sale_price, p.image, p.quantity as stock 
          FROM carts c 
          INNER JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = :user_id AND p.status = 'active'";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

// Lấy thông tin user
$query = "SELECT * FROM users WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$total = 0;
foreach($cart_items as $item) {
    $price = $item['sale_price'] ?: $item['price'];
    $total += $price * $item['quantity'];
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $note = trim($_POST['note']);
    $payment_method = $_POST['payment_method'];
    
    if (empty($fullname) || empty($email) || empty($phone) || empty($address)) {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    } else {
        // Tạo mã đơn hàng
        $order_code = 'DH' . time();
        
        try {
            $db->beginTransaction();
            
            // Tạo đơn hàng
            $query = "INSERT INTO orders (user_id, order_code, fullname, email, phone, address, note, total_amount, payment_method) 
                      VALUES (:user_id, :order_code, :fullname, :email, :phone, :address, :note, :total_amount, :payment_method)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':order_code', $order_code);
            $stmt->bindParam(':fullname', $fullname);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':note', $note);
            $stmt->bindParam(':total_amount', $total);
            $stmt->bindParam(':payment_method', $payment_method);
            $stmt->execute();
            
            $order_id = $db->lastInsertId();
            
            // Thêm chi tiết đơn hàng
            foreach($cart_items as $item) {
                $price = $item['sale_price'] ?: $item['price'];
                $subtotal = $price * $item['quantity'];
                
                $query = "INSERT INTO order_items (order_id, product_id, product_name, product_image, price, quantity, total) 
                          VALUES (:order_id, :product_id, :product_name, :product_image, :price, :quantity, :total)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':order_id', $order_id);
                $stmt->bindParam(':product_id', $item['product_id']);
                $stmt->bindParam(':product_name', $item['name']);
                $stmt->bindParam(':product_image', $item['image']);
                $stmt->bindParam(':price', $price);
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->bindParam(':total', $subtotal);
                $stmt->execute();
                
                // Giảm số lượng sản phẩm
                $query = "UPDATE products SET quantity = quantity - :quantity WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->bindParam(':id', $item['product_id']);
                $stmt->execute();
            }
            
            // Xóa giỏ hàng
            $query = "DELETE FROM carts WHERE user_id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $db->commit();
            
            // Xử lý thanh toán
            if ($payment_method == 'momo') {
                // Chuyển sang thanh toán MoMo
                header('Location: ' . SITE_URL . '/payment/momo_payment.php?order_id=' . $order_id);
                exit();
            } elseif ($payment_method == 'vnpay') {
                // Chuyển sang thanh toán VNPay
                header('Location: ' . SITE_URL . '/payment/vnpay_payment.php?order_id=' . $order_id);
                exit();
            } else {
                // COD hoặc chuyển khoản
                header('Location: ' . SITE_URL . '/order_success.php?order_code=' . $order_code);
                exit();
            }
            
        } catch(Exception $e) {
            $db->rollBack();
            $error = 'Có lỗi xảy ra: ' . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-credit-card"></i> Thanh toán</h2>
    
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="row">
            <!-- Thông tin giao hàng -->
            <div class="col-md-7">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Thông tin giao hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" name="fullname" class="form-control" required 
                                       value="<?php echo htmlspecialchars($user['fullname']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required 
                                       value="<?php echo htmlspecialchars($user['email']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control" required 
                                       value="<?php echo htmlspecialchars($user['phone']); ?>">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                                <textarea name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Ghi chú đơn hàng</label>
                                <textarea name="note" class="form-control" rows="2" placeholder="Ghi chú về đơn hàng..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Phương thức thanh toán -->
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Phương thức thanh toán</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" value="cod" id="cod" checked>
                            <label class="form-check-label" for="cod">
                                <strong>Thanh toán khi nhận hàng (COD)</strong>
                                <p class="text-muted small mb-0">Thanh toán bằng tiền mặt khi nhận hàng</p>
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" value="momo" id="momo">
                            <label class="form-check-label" for="momo">
                                <strong>Thanh toán qua MoMo</strong>
                                <p class="text-muted small mb-0">Thanh toán an toàn qua ví điện tử MoMo</p>
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" value="vnpay" id="vnpay">
                            <label class="form-check-label" for="vnpay">
                                <strong>Thanh toán qua VNPay</strong>
                                <p class="text-muted small mb-0">Thanh toán qua cổng VNPay (ATM, Visa, MasterCard)</p>
                            </label>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" value="bank_transfer" id="bank">
                            <label class="form-check-label" for="bank">
                                <strong>Chuyển khoản ngân hàng</strong>
                                <p class="text-muted small mb-0">Chuyển khoản trực tiếp vào tài khoản ngân hàng</p>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Đơn hàng -->
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Đơn hàng của bạn</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach($cart_items as $item): 
                            $price = $item['sale_price'] ?: $item['price'];
                            $subtotal = $price * $item['quantity'];
                        ?>
                            <div class="d-flex mb-3">
                                <img src="<?php echo $item['image'] ?: getProductPlaceholder(60, 60); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="me-3" style="width: 60px; height: 60px; object-fit: cover;"
                                     loading="lazy">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <small class="text-muted">Số lượng: <?php echo $item['quantity']; ?></small>
                                </div>
                                <div class="text-end">
                                    <strong class="text-danger"><?php echo number_format($subtotal); ?>đ</strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        
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
                            <button type="submit" class="btn btn-danger btn-lg">
                                <i class="fas fa-check"></i> Đặt hàng
                            </button>
                            <a href="<?php echo SITE_URL; ?>/cart.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại giỏ hàng
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
