<?php
require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

$title = 'Quản lý đơn hàng';
$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';

// Xử lý cập nhật trạng thái đơn hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $order_status = $_POST['order_status'];
    $payment_status = $_POST['payment_status'] ?? null;
    
    if ($payment_status) {
        $query = "UPDATE orders SET order_status = :order_status, payment_status = :payment_status WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':order_status', $order_status);
        $stmt->bindParam(':payment_status', $payment_status);
        $stmt->bindParam(':id', $order_id);
    } else {
        $query = "UPDATE orders SET order_status = :order_status WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':order_status', $order_status);
        $stmt->bindParam(':id', $order_id);
    }
    
    if ($stmt->execute()) {
        $success = 'Cập nhật trạng thái đơn hàng thành công!';
    } else {
        $error = 'Có lỗi xảy ra!';
    }
}

// Lọc đơn hàng
$status_filter = $_GET['status'] ?? '';
$payment_filter = $_GET['payment'] ?? '';
$search = $_GET['search'] ?? '';

// Xây dựng query
$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "o.order_status = :status";
    $params[':status'] = $status_filter;
}

if ($payment_filter) {
    $where_conditions[] = "o.payment_status = :payment";
    $params[':payment'] = $payment_filter;
}

if ($search) {
    $where_conditions[] = "(o.order_code LIKE :search OR o.fullname LIKE :search OR o.email LIKE :search)";
    $params[':search'] = "%$search%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Đếm tổng
$count_query = "SELECT COUNT(*) as total FROM orders o $where_clause";
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total / $limit);

// Lấy danh sách đơn hàng
$query = "SELECT o.*, u.email as user_email 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          $where_clause
          ORDER BY o.created_at DESC 
          LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy chi tiết đơn hàng nếu có view
$order_detail = null;
if (isset($_GET['view'])) {
    $order_id = (int)$_GET['view'];
    $query = "SELECT o.*, u.email as user_email FROM orders o 
              LEFT JOIN users u ON o.user_id = u.id 
              WHERE o.id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $order_id);
    $stmt->execute();
    $order_detail = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order_detail) {
        $query = "SELECT * FROM order_items WHERE order_id = :order_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Thống kê
$stats_query = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN order_status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as revenue
    FROM orders";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute();
$order_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

include 'includes/admin_header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($order_detail): ?>
    <!-- Order Detail View -->
    <div class="admin-card mb-4">
        <div class="admin-card-header">
            <h5><i class="fas fa-receipt"></i> Chi tiết đơn hàng #<?php echo $order_detail['order_code']; ?></h5>
            <a href="orders.php" class="btn-admin btn-admin-sm" style="background: #e2e8f0; color: var(--admin-text);">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
        <div class="admin-card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="mb-3">Thông tin khách hàng</h6>
                    <table class="table table-bordered">
                        <tr><td><strong>Họ tên:</strong></td><td><?php echo htmlspecialchars($order_detail['fullname']); ?></td></tr>
                        <tr><td><strong>Email:</strong></td><td><?php echo htmlspecialchars($order_detail['email']); ?></td></tr>
                        <tr><td><strong>Điện thoại:</strong></td><td><?php echo htmlspecialchars($order_detail['phone']); ?></td></tr>
                        <tr><td><strong>Địa chỉ:</strong></td><td><?php echo htmlspecialchars($order_detail['address']); ?></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="mb-3">Thông tin đơn hàng</h6>
                    <table class="table table-bordered">
                        <tr><td><strong>Mã đơn:</strong></td><td><?php echo $order_detail['order_code']; ?></td></tr>
                        <tr><td><strong>Ngày đặt:</strong></td><td><?php echo date('d/m/Y H:i', strtotime($order_detail['created_at'])); ?></td></tr>
                        <tr><td><strong>Phương thức:</strong></td><td><?php echo strtoupper($order_detail['payment_method']); ?></td></tr>
                        <tr><td><strong>Thanh toán:</strong></td>
                            <td><span class="badge-admin badge-<?php echo $order_detail['payment_status'] == 'paid' ? 'success' : 'warning'; ?>">
                                <?php echo ucfirst($order_detail['payment_status']); ?>
                            </span></td>
                        </tr>
                        <tr><td><strong>Trạng thái:</strong></td>
                            <td><span class="badge-admin badge-<?php 
                                echo $order_detail['order_status'] == 'completed' ? 'success' : 
                                    ($order_detail['order_status'] == 'cancelled' ? 'danger' : 'warning'); 
                            ?>">
                                <?php echo ucfirst($order_detail['order_status']); ?>
                            </span></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <h6 class="mb-3">Sản phẩm trong đơn hàng</h6>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Hình ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($order_items as $item): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo $item['product_image'] ?: (function_exists('getProductPlaceholder') ? getProductPlaceholder(60, 60) : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjYwIiBoZWlnaHQ9IjYwIiBmaWxsPSIjZTllY2VmIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxMiIgZmlsbD0iIzY5NzU3ZCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkltYWdlPC90ZXh0Pjwvc3ZnPg=='); ?>" 
                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                </td>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><?php echo number_format($item['price']); ?>đ</td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><strong><?php echo number_format($item['total']); ?>đ</strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end"><strong>Tổng cộng:</strong></td>
                            <td><strong style="color: var(--admin-danger); font-size: 18px;">
                                <?php echo number_format($order_detail['total_amount']); ?>đ
                            </strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="mt-4">
                <h6 class="mb-3">Cập nhật trạng thái</h6>
                <form method="POST" action="" class="row g-3">
                    <input type="hidden" name="order_id" value="<?php echo $order_detail['id']; ?>">
                    <div class="col-md-4">
                        <label class="form-label-admin">Trạng thái đơn hàng</label>
                        <select name="order_status" class="form-control-admin" required>
                            <option value="pending" <?php echo $order_detail['order_status'] == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                            <option value="confirmed" <?php echo $order_detail['order_status'] == 'confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
                            <option value="processing" <?php echo $order_detail['order_status'] == 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                            <option value="shipping" <?php echo $order_detail['order_status'] == 'shipping' ? 'selected' : ''; ?>>Đang giao hàng</option>
                            <option value="completed" <?php echo $order_detail['order_status'] == 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                            <option value="cancelled" <?php echo $order_detail['order_status'] == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-admin">Trạng thái thanh toán</label>
                        <select name="payment_status" class="form-control-admin" required>
                            <option value="pending" <?php echo $order_detail['payment_status'] == 'pending' ? 'selected' : ''; ?>>Chờ thanh toán</option>
                            <option value="paid" <?php echo $order_detail['payment_status'] == 'paid' ? 'selected' : ''; ?>>Đã thanh toán</option>
                            <option value="failed" <?php echo $order_detail['payment_status'] == 'failed' ? 'selected' : ''; ?>>Thanh toán thất bại</option>
                            <option value="refunded" <?php echo $order_detail['payment_status'] == 'refunded' ? 'selected' : ''; ?>>Đã hoàn tiền</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" name="update_status" class="btn-admin btn-admin-primary w-100">
                            <i class="fas fa-save"></i> Cập nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3 mb-4">
            <div class="stats-card primary">
                <div class="stats-card-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="stats-card-label">Tổng đơn hàng</div>
                <div class="stats-card-value"><?php echo number_format($order_stats['total_orders']); ?></div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="stats-card warning">
                <div class="stats-card-icon"><i class="fas fa-clock"></i></div>
                <div class="stats-card-label">Chờ xử lý</div>
                <div class="stats-card-value"><?php echo number_format($order_stats['pending']); ?></div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="stats-card success">
                <div class="stats-card-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stats-card-label">Hoàn thành</div>
                <div class="stats-card-value"><?php echo number_format($order_stats['completed']); ?></div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="stats-card info">
                <div class="stats-card-icon"><i class="fas fa-dollar-sign"></i></div>
                <div class="stats-card-label">Doanh thu</div>
                <div class="stats-card-value"><?php echo number_format($order_stats['revenue']); ?>đ</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="admin-card mb-4">
        <div class="admin-card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control-admin" 
                           placeholder="Tìm kiếm mã đơn, tên, email..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control-admin">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                        <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
                        <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                        <option value="shipping" <?php echo $status_filter == 'shipping' ? 'selected' : ''; ?>>Đang giao hàng</option>
                        <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                        <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="payment" class="form-control-admin">
                        <option value="">Tất cả thanh toán</option>
                        <option value="pending" <?php echo $payment_filter == 'pending' ? 'selected' : ''; ?>>Chờ thanh toán</option>
                        <option value="paid" <?php echo $payment_filter == 'paid' ? 'selected' : ''; ?>>Đã thanh toán</option>
                        <option value="failed" <?php echo $payment_filter == 'failed' ? 'selected' : ''; ?>>Thất bại</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn-admin btn-admin-primary w-100">
                        <i class="fas fa-search"></i> Lọc
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders List -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h5><i class="fas fa-list"></i> Danh sách đơn hàng</h5>
        </div>
        <div class="admin-card-body">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Tổng tiền</th>
                            <th>Thanh toán</th>
                            <th>Trạng thái</th>
                            <th>Ngày đặt</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($orders)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fas fa-shopping-cart"></i>
                                        <h4>Không có đơn hàng</h4>
                                        <p>Chưa có đơn hàng nào phù hợp với bộ lọc</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($orders as $order): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($order['order_code']); ?></strong></td>
                                    <td>
                                        <div><?php echo htmlspecialchars($order['fullname']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                                    </td>
                                    <td><strong style="color: var(--admin-danger);"><?php echo number_format($order['total_amount']); ?>đ</strong></td>
                                    <td>
                                        <span class="badge-admin badge-<?php 
                                            echo $order['payment_status'] == 'paid' ? 'success' : 
                                                ($order['payment_status'] == 'failed' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-admin badge-<?php 
                                            echo $order['order_status'] == 'completed' ? 'success' : 
                                                ($order['order_status'] == 'cancelled' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst($order['order_status']); ?>
                                        </span>
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
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav class="pagination-admin">
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&payment=<?php echo $payment_filter; ?>&search=<?php echo urlencode($search); ?>" 
                           class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/admin_footer.php'; ?>

