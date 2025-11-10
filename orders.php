<?php
require_once 'config/config.php';
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

$title = 'Đơn hàng của tôi';
$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Lấy danh sách đơn hàng
$query = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-shopping-bag"></i> Đơn hàng của tôi</h2>
    
    <?php if(empty($orders)): ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle fa-3x mb-3 d-block"></i>
            <h5>Bạn chưa có đơn hàng nào</h5>
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary mt-3">
                <i class="fas fa-shopping-bag"></i> Mua sắm ngay
            </a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>Mã đơn hàng</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Thanh toán</th>
                        <th>Trạng thái đơn hàng</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $order): ?>
                        <tr>
                            <td><strong><?php echo $order['order_code']; ?></strong></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            <td><strong class="text-danger"><?php echo number_format($order['total_amount']); ?>đ</strong></td>
                            <td>
                                <?php 
                                $payment_status_badges = [
                                    'pending' => '<span class="badge bg-warning">Chưa thanh toán</span>',
                                    'paid' => '<span class="badge bg-success">Đã thanh toán</span>',
                                    'failed' => '<span class="badge bg-danger">Thất bại</span>',
                                    'refunded' => '<span class="badge bg-secondary">Đã hoàn tiền</span>'
                                ];
                                echo $payment_status_badges[$order['payment_status']];
                                ?>
                            </td>
                            <td>
                                <?php 
                                $order_status_badges = [
                                    'pending' => '<span class="badge bg-warning">Chờ xác nhận</span>',
                                    'confirmed' => '<span class="badge bg-info">Đã xác nhận</span>',
                                    'processing' => '<span class="badge bg-primary">Đang xử lý</span>',
                                    'shipping' => '<span class="badge bg-primary">Đang giao</span>',
                                    'completed' => '<span class="badge bg-success">Hoàn thành</span>',
                                    'cancelled' => '<span class="badge bg-danger">Đã hủy</span>'
                                ];
                                echo $order_status_badges[$order['order_status']];
                                ?>
                            </td>
                            <td>
                                <a href="<?php echo SITE_URL; ?>/order_detail.php?id=<?php echo $order['id']; ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Chi tiết
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
