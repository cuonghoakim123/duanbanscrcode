<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

$title = 'Dashboard - Quản trị';
$database = new Database();
$db = $database->getConnection();

// Thống kê tổng quan
$stats = [];

// Tổng doanh thu
$query = "SELECT SUM(total_amount) as total_revenue FROM orders WHERE payment_status = 'paid'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;

// Tổng đơn hàng
$query = "SELECT COUNT(*) as total_orders FROM orders";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

// Tổng sản phẩm
$query = "SELECT COUNT(*) as total_products FROM products WHERE status = 'active'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['products'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'];

// Tổng khách hàng
$query = "SELECT COUNT(*) as total_users FROM users WHERE role = 'user'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

// Đơn hàng chờ xử lý
$query = "SELECT COUNT(*) as pending_orders FROM orders WHERE order_status = 'pending'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['pending_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['pending_orders'];

// Đánh giá chờ duyệt
$query = "SELECT COUNT(*) as pending_reviews FROM reviews WHERE status = 'pending'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['pending_reviews'] = $stmt->fetch(PDO::FETCH_ASSOC)['pending_reviews'] ?? 0;

// Liên hệ chờ xử lý (nếu có bảng contacts)
$stats['pending_contacts'] = 0;
try {
    $query = "SELECT COUNT(*) as pending_contacts FROM contacts WHERE status = 'pending'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['pending_contacts'] = $stmt->fetch(PDO::FETCH_ASSOC)['pending_contacts'] ?? 0;
} catch (PDOException $e) {
    // Bảng chưa tồn tại, bỏ qua
}

// Đơn hàng mới nhất
$query = "SELECT * FROM orders ORDER BY created_at DESC LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/admin_header.php';
?>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-4">
        <div class="stats-card primary">
            <div class="stats-card-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stats-card-label">Tổng doanh thu</div>
            <div class="stats-card-value"><?php echo number_format($stats['revenue']); ?>đ</div>
            <div class="stats-card-change positive">
                <i class="fas fa-arrow-up"></i> Tổng cộng
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="stats-card success">
            <div class="stats-card-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stats-card-label">Tổng đơn hàng</div>
            <div class="stats-card-value"><?php echo number_format($stats['orders']); ?></div>
            <div class="stats-card-change">
                <i class="fas fa-info-circle"></i> Tất cả đơn hàng
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="stats-card info">
            <div class="stats-card-icon">
                <i class="fas fa-box"></i>
            </div>
            <div class="stats-card-label">Sản phẩm</div>
            <div class="stats-card-value"><?php echo number_format($stats['products']); ?></div>
            <div class="stats-card-change">
                <i class="fas fa-check-circle"></i> Đang hoạt động
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="stats-card warning">
            <div class="stats-card-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stats-card-label">Khách hàng</div>
            <div class="stats-card-value"><?php echo number_format($stats['users']); ?></div>
            <div class="stats-card-change">
                <i class="fas fa-user-check"></i> Người dùng
            </div>
        </div>
    </div>
</div>
    
<!-- Notifications and Quick Actions -->
<div class="row mb-4">
    <div class="col-md-3 mb-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5><i class="fas fa-bell"></i> Đơn hàng chờ xử lý</h5>
            </div>
            <div class="admin-card-body text-center">
                <div class="stats-card-value" style="font-size: 36px; color: var(--admin-danger); margin: 15px 0;">
                    <?php echo $stats['pending_orders']; ?>
                </div>
                <a href="orders.php?status=pending" class="btn-admin btn-admin-primary w-100">
                    <i class="fas fa-list"></i> Xem chi tiết
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5><i class="fas fa-star"></i> Đánh giá chờ duyệt</h5>
            </div>
            <div class="admin-card-body text-center">
                <div class="stats-card-value" style="font-size: 36px; color: var(--admin-warning); margin: 15px 0;">
                    <?php echo $stats['pending_reviews']; ?>
                </div>
                <a href="reviews.php?status=pending" class="btn-admin btn-admin-warning w-100">
                    <i class="fas fa-check-circle"></i> Xem chi tiết
                </a>
            </div>
        </div>
    </div>
    
    <?php if ($stats['pending_contacts'] > 0): ?>
    <div class="col-md-3 mb-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5><i class="fas fa-envelope"></i> Liên hệ chờ xử lý</h5>
            </div>
            <div class="admin-card-body text-center">
                <div class="stats-card-value" style="font-size: 36px; color: var(--admin-info); margin: 15px 0;">
                    <?php echo $stats['pending_contacts']; ?>
                </div>
                <a href="contacts.php?status=pending" class="btn-admin btn-admin-info w-100">
                    <i class="fas fa-eye"></i> Xem chi tiết
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="col-md-<?php echo $stats['pending_contacts'] > 0 ? '3' : '6'; ?> mb-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5><i class="fas fa-cog"></i> Thao tác nhanh</h5>
            </div>
            <div class="admin-card-body">
                <div class="d-grid gap-2">
                    <a href="products.php" class="btn-admin btn-admin-primary">
                        <i class="fas fa-box"></i> Quản lý sản phẩm
                    </a>
                    <a href="orders.php" class="btn-admin btn-admin-success">
                        <i class="fas fa-shopping-cart"></i> Quản lý đơn hàng
                    </a>
                    <a href="users.php" class="btn-admin btn-admin-info">
                        <i class="fas fa-users"></i> Quản lý người dùng
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="row">
    <div class="col-12">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5><i class="fas fa-shopping-bag"></i> Đơn hàng mới nhất</h5>
                <a href="orders.php" class="btn-admin btn-admin-primary btn-admin-sm">
                    <i class="fas fa-eye"></i> Xem tất cả
                </a>
            </div>
            <div class="admin-card-body">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Mã đơn hàng</th>
                                <th>Khách hàng</th>
                                <th>Tổng tiền</th>
                                <th>Thanh toán</th>
                                <th>Trạng thái</th>
                                <th>Ngày đặt</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($recent_orders)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-shopping-cart"></i>
                                            <h4>Chưa có đơn hàng</h4>
                                            <p>Chưa có đơn hàng nào trong hệ thống</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($recent_orders as $order): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($order['order_code']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($order['fullname']); ?></td>
                                        <td><strong style="color: var(--admin-danger);"><?php echo number_format($order['total_amount']); ?>đ</strong></td>
                                        <td>
                                            <?php 
                                            $payment_badges = [
                                                'pending' => 'warning',
                                                'paid' => 'success',
                                                'failed' => 'danger',
                                                'refunded' => 'info'
                                            ];
                                            $badge_class = $payment_badges[$order['payment_status']] ?? 'secondary';
                                            echo '<span class="badge-admin badge-' . $badge_class . '">' . ucfirst($order['payment_status']) . '</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $order_badges = [
                                                'pending' => 'warning',
                                                'confirmed' => 'info',
                                                'processing' => 'primary',
                                                'shipping' => 'primary',
                                                'completed' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                            $badge_class = $order_badges[$order['order_status']] ?? 'secondary';
                                            echo '<span class="badge-admin badge-' . $badge_class . '">' . ucfirst($order['order_status']) . '</span>';
                                            ?>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <a href="orders.php?view=<?php echo $order['id']; ?>" class="btn-admin btn-admin-primary btn-admin-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include 'includes/admin_footer.php'; ?>
