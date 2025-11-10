<?php
require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

$title = 'Quản lý người dùng';
$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $user_id = (int)$_POST['user_id'];
    $status = $_POST['status'];
    $role = $_POST['role'];
    
    $query = "UPDATE users SET status = :status, role = :role WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':role', $role);
    $stmt->bindParam(':id', $user_id);
    
    if ($stmt->execute()) {
        $success = 'Cập nhật người dùng thành công!';
    } else {
        $error = 'Có lỗi xảy ra!';
    }
}

// Xử lý xóa
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id != $_SESSION['user_id']) {
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $success = 'Xóa người dùng thành công!';
    } else {
        $error = 'Không thể xóa chính mình!';
    }
}

// Lọc
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$where_conditions = [];
$params = [];

if ($role_filter) {
    $where_conditions[] = "u.role = :role";
    $params[':role'] = $role_filter;
}

if ($status_filter) {
    $where_conditions[] = "u.status = :status";
    $params[':status'] = $status_filter;
}

if ($search) {
    $where_conditions[] = "(u.email LIKE :search OR u.fullname LIKE :search OR u.phone LIKE :search)";
    $params[':search'] = "%$search%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Đếm tổng
$count_query = "SELECT COUNT(*) as total FROM users u $where_clause";
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total / $limit);

// Lấy danh sách users
$query = "SELECT u.*, 
          (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,
          (SELECT SUM(total_amount) FROM orders WHERE user_id = u.id AND payment_status = 'paid') as total_spent
          FROM users u 
          $where_clause
          ORDER BY u.created_at DESC 
          LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Thống kê
$stats_query = "SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
    SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as customers,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users
    FROM users";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute();
$user_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

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

<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-3 mb-4">
        <div class="stats-card primary">
            <div class="stats-card-icon"><i class="fas fa-users"></i></div>
            <div class="stats-card-label">Tổng người dùng</div>
            <div class="stats-card-value"><?php echo number_format($user_stats['total_users']); ?></div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="stats-card success">
            <div class="stats-card-icon"><i class="fas fa-user-check"></i></div>
            <div class="stats-card-label">Đang hoạt động</div>
            <div class="stats-card-value"><?php echo number_format($user_stats['active_users']); ?></div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="stats-card info">
            <div class="stats-card-icon"><i class="fas fa-user-tie"></i></div>
            <div class="stats-card-label">Quản trị viên</div>
            <div class="stats-card-value"><?php echo number_format($user_stats['admins']); ?></div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="stats-card warning">
            <div class="stats-card-icon"><i class="fas fa-user-friends"></i></div>
            <div class="stats-card-label">Khách hàng</div>
            <div class="stats-card-value"><?php echo number_format($user_stats['customers']); ?></div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="admin-card mb-4">
    <div class="admin-card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control-admin" 
                       placeholder="Tìm kiếm email, tên, số điện thoại..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-control-admin">
                    <option value="">Tất cả vai trò</option>
                    <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Quản trị viên</option>
                    <option value="user" <?php echo $role_filter == 'user' ? 'selected' : ''; ?>>Khách hàng</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-control-admin">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                    <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Không hoạt động</option>
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

<!-- Users List -->
<div class="admin-card">
    <div class="admin-card-header">
        <h5><i class="fas fa-list"></i> Danh sách người dùng</h5>
    </div>
    <div class="admin-card-body">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Thông tin</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Đơn hàng</th>
                        <th>Tổng chi</th>
                        <th>Ngày tham gia</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($users)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-users"></i>
                                    <h4>Không có người dùng</h4>
                                    <p>Chưa có người dùng nào phù hợp với bộ lọc</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if($user['avatar']): ?>
                                            <img src="<?php echo htmlspecialchars($user['avatar']); ?>" 
                                                 class="rounded-circle" 
                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px; font-weight: 600;">
                                                <?php echo strtoupper(substr($user['fullname'], 0, 2)); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div><strong><?php echo htmlspecialchars($user['fullname']); ?></strong></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-admin badge-<?php echo $user['role'] == 'admin' ? 'danger' : 'primary'; ?>">
                                        <?php echo $user['role'] == 'admin' ? 'Admin' : 'User'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-admin badge-<?php echo $user['status'] == 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo $user['status'] == 'active' ? 'Hoạt động' : 'Khóa'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-admin badge-info"><?php echo $user['order_count']; ?> đơn</span>
                                </td>
                                <td>
                                    <strong><?php echo number_format($user['total_spent'] ?? 0); ?>đ</strong>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn-admin btn-admin-primary btn-admin-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editUserModal<?php echo $user['id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="users.php?delete=<?php echo $user['id']; ?>" 
                                               class="btn-admin btn-admin-danger btn-admin-sm"
                                               onclick="return confirm('Bạn có chắc muốn xóa người dùng này?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editUserModal<?php echo $user['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Sửa người dùng</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <div class="form-group-admin mb-3">
                                                            <label class="form-label-admin">Vai trò</label>
                                                            <select name="role" class="form-control-admin" required>
                                                                <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>Khách hàng</option>
                                                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Quản trị viên</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group-admin">
                                                            <label class="form-label-admin">Trạng thái</label>
                                                            <select name="status" class="form-control-admin" required>
                                                                <option value="active" <?php echo $user['status'] == 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                                                                <option value="inactive" <?php echo $user['status'] == 'inactive' ? 'selected' : ''; ?>>Không hoạt động</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn-admin" style="background: #e2e8f0; color: var(--admin-text);" data-bs-dismiss="modal">Hủy</button>
                                                        <button type="submit" name="update_user" class="btn-admin btn-admin-primary">Cập nhật</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
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
                    <a href="?page=<?php echo $i; ?>&role=<?php echo $role_filter; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>" 
                       class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>

