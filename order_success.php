<?php
require_once 'config/config.php';
require_once 'config/database.php';

$order_code = isset($_GET['order_code']) ? $_GET['order_code'] : '';

if (empty($order_code)) {
    header('Location: ' . SITE_URL);
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Lấy thông tin đơn hàng
$query = "SELECT * FROM orders WHERE order_code = :order_code";
$stmt = $db->prepare($query);
$stmt->bindParam(':order_code', $order_code);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: ' . SITE_URL);
    exit();
}

$title = 'Đặt hàng thành công';
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 80px;"></i>
                    </div>
                    
                    <h2 class="text-success mb-3">Đặt hàng thành công!</h2>
                    
                    <p class="lead">Cảm ơn bạn đã đặt hàng tại <?php echo SITE_NAME; ?></p>
                    
                    <div class="alert alert-info">
                        <strong>Mã đơn hàng:</strong> <?php echo $order['order_code']; ?>
                    </div>
                    
                    <div class="row text-start my-4">
                        <div class="col-md-6">
                            <h5>Thông tin người nhận:</h5>
                            <p>
                                <strong>Họ tên:</strong> <?php echo htmlspecialchars($order['fullname']); ?><br>
                                <strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?><br>
                                <strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['phone']); ?><br>
                                <strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['address']); ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5>Thông tin đơn hàng:</h5>
                            <p>
                                <strong>Tổng tiền:</strong> <span class="text-danger"><?php echo number_format($order['total_amount']); ?>đ</span><br>
                                <strong>Phương thức thanh toán:</strong> 
                                <?php 
                                $payment_methods = [
                                    'cod' => 'Thanh toán khi nhận hàng',
                                    'momo' => 'MoMo',
                                    'vnpay' => 'VNPay',
                                    'bank_transfer' => 'Chuyển khoản ngân hàng'
                                ];
                                echo $payment_methods[$order['payment_method']];
                                ?><br>
                                <strong>Trạng thái thanh toán:</strong> 
                                <?php 
                                $payment_status = [
                                    'pending' => '<span class="badge bg-warning">Chưa thanh toán</span>',
                                    'paid' => '<span class="badge bg-success">Đã thanh toán</span>',
                                    'failed' => '<span class="badge bg-danger">Thất bại</span>'
                                ];
                                echo $payment_status[$order['payment_status']];
                                ?>
                            </p>
                        </div>
                    </div>
                    
                    <?php if($order['payment_method'] == 'bank_transfer' && $order['payment_status'] == 'pending'): ?>
                        <div class="alert alert-warning text-start">
                            <h5><i class="fas fa-university"></i> Thông tin chuyển khoản:</h5>
                            <p>
                                <strong>Ngân hàng:</strong> Vietcombank<br>
                                <strong>Số tài khoản:</strong> 1234567890<br>
                                <strong>Chủ tài khoản:</strong> <?php echo SITE_NAME; ?><br>
                                <strong>Nội dung:</strong> <?php echo $order['order_code']; ?>
                            </p>
                            <p class="mb-0 text-danger">
                                <i class="fas fa-info-circle"></i> Vui lòng chuyển khoản và gửi ảnh xác nhận cho chúng tôi
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <p>Chúng tôi đã gửi email xác nhận đơn hàng đến <strong><?php echo htmlspecialchars($order['email']); ?></strong></p>
                        <p class="text-muted">Bạn có thể theo dõi đơn hàng trong mục "Đơn hàng của tôi"</p>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
                        <a href="<?php echo SITE_URL; ?>/orders" class="btn btn-primary">
                            <i class="fas fa-list"></i> Xem đơn hàng của tôi
                        </a>
                        <a href="<?php echo SITE_URL; ?>" class="btn btn-outline-primary">
                            <i class="fas fa-home"></i> Về trang chủ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
