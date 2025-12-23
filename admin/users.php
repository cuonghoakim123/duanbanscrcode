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

// Xử lý thêm user mới
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $status = $_POST['status'] ?? 'active';
    
    // Validation
    if (empty($fullname) || empty($email) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ!';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
    } else {
        // Kiểm tra email đã tồn tại
        $check_query = "SELECT id FROM users WHERE email = :email";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            $error = 'Email đã tồn tại!';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO users (fullname, email, phone, address, password, role, status, created_at) 
                      VALUES (:fullname, :email, :phone, :address, :password, :role, :status, NOW())";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':fullname', $fullname);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':status', $status);
            
            if ($stmt->execute()) {
                $success = 'Thêm người dùng thành công!';
            } else {
                $error = 'Có lỗi xảy ra khi thêm người dùng!';
            }
        }
    }
}

// Xử lý cập nhật user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $user_id = (int)$_POST['user_id'];
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $status = $_POST['status'] ?? 'active';
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($fullname) || empty($email)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ!';
    } else {
        // Kiểm tra email đã tồn tại (trừ user hiện tại)
        $check_query = "SELECT id FROM users WHERE email = :email AND id != :id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->bindParam(':id', $user_id);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            $error = 'Email đã tồn tại!';
        } else {
            // Cập nhật với hoặc không có password
            if (!empty($password)) {
                if (strlen($password) < 6) {
                    $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $query = "UPDATE users SET fullname = :fullname, email = :email, phone = :phone, 
                             address = :address, password = :password, role = :role, status = :status 
                             WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':password', $hashed_password);
                }
            } else {
                $query = "UPDATE users SET fullname = :fullname, email = :email, phone = :phone, 
                         address = :address, role = :role, status = :status 
                         WHERE id = :id";
                $stmt = $db->prepare($query);
            }
            
            if (!isset($error) || empty($error)) {
                $stmt->bindParam(':fullname', $fullname);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':address', $address);
                $stmt->bindParam(':role', $role);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':id', $user_id);
                
                if ($stmt->execute()) {
                    $success = 'Cập nhật người dùng thành công!';
                } else {
                    $error = 'Có lỗi xảy ra!';
                }
            }
        }
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

// Lấy thông tin user để edit (nếu có)
$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $query = "SELECT * FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $edit_id);
    $stmt->execute();
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
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
$query = "SELECT u.* FROM users u 
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

<!-- Filters and Add Button -->
<div class="admin-card mb-4">
    <div class="admin-card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control-admin" 
                       placeholder="Tìm kiếm email, tên, số điện thoại..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <select name="role" class="form-control-admin">
                    <option value="">Tất cả vai trò</option>
                    <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Quản trị viên</option>
                    <option value="user" <?php echo $role_filter == 'user' ? 'selected' : ''; ?>>Khách hàng</option>
                </select>
            </div>
            <div class="col-md-2">
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
            <div class="col-md-3 text-end">
                <button type="button" class="btn-admin btn-admin-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus"></i> Thêm người dùng
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
                        <th>Liên hệ</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Ngày tham gia</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($users)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
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
                                        <?php if(!empty($user['avatar'])): ?>
                                            <img src="<?php echo htmlspecialchars($user['avatar']); ?>" 
                                                 class="rounded-circle" 
                                                 style="width: 40px; height: 40px; object-fit: cover;"
                                                 onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'40\'%3E%3Crect width=\'40\' height=\'40\' fill=\'%23667eea\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'white\' font-size=\'16\' font-weight=\'bold\'%3E<?php echo strtoupper(substr($user['fullname'], 0, 2)); ?>%3C/text%3E%3C/svg%3E';">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px; font-weight: 600; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                <?php echo strtoupper(substr($user['fullname'] ?? 'U', 0, 2)); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div><strong><?php echo htmlspecialchars($user['fullname'] ?? 'N/A'); ?></strong></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($user['email'] ?? ''); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if(!empty($user['phone'])): ?>
                                        <div><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['phone']); ?></div>
                                    <?php endif; ?>
                                    <?php if(!empty($user['address'])): ?>
                                        <div class="text-muted small"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars(mb_substr($user['address'], 0, 30)) . (mb_strlen($user['address']) > 30 ? '...' : ''); ?></div>
                                    <?php endif; ?>
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
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Sửa thông tin người dùng</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label-admin">Họ và tên <span class="text-danger">*</span></label>
                                                                <input type="text" name="fullname" class="form-control-admin" 
                                                                       value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label-admin">Email <span class="text-danger">*</span></label>
                                                                <input type="email" name="email" class="form-control-admin" 
                                                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label-admin">Số điện thoại</label>
                                                                <input type="text" name="phone" class="form-control-admin" 
                                                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label-admin">Mật khẩu mới (để trống nếu không đổi)</label>
                                                                <input type="password" name="password" class="form-control-admin" 
                                                                       placeholder="Nhập mật khẩu mới...">
                                                                <small class="text-muted">Tối thiểu 6 ký tự</small>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label-admin">Vai trò <span class="text-danger">*</span></label>
                                                                <select name="role" class="form-control-admin" required>
                                                                    <option value="user" <?php echo ($user['role'] ?? 'user') == 'user' ? 'selected' : ''; ?>>Khách hàng</option>
                                                                    <option value="admin" <?php echo ($user['role'] ?? 'user') == 'admin' ? 'selected' : ''; ?>>Quản trị viên</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label-admin">Trạng thái <span class="text-danger">*</span></label>
                                                                <select name="status" class="form-control-admin" required>
                                                                    <option value="active" <?php echo ($user['status'] ?? 'active') == 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                                                                    <option value="inactive" <?php echo ($user['status'] ?? 'active') == 'inactive' ? 'selected' : ''; ?>>Không hoạt động</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-12 mb-3">
                                                                <label class="form-label-admin">Địa chỉ</label>
                                                                <textarea name="address" class="form-control-admin" rows="2"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                                            </div>
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm người dùng mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label-admin">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="fullname" class="form-control-admin" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label-admin">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control-admin" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label-admin">Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control-admin" required minlength="6">
                            <small class="text-muted">Tối thiểu 6 ký tự</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label-admin">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control-admin">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label-admin">Vai trò <span class="text-danger">*</span></label>
                            <select name="role" class="form-control-admin" required>
                                <option value="user">Khách hàng</option>
                                <option value="admin">Quản trị viên</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label-admin">Trạng thái <span class="text-danger">*</span></label>
                            <select name="status" class="form-control-admin" required>
                                <option value="active">Hoạt động</option>
                                <option value="inactive">Không hoạt động</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label-admin">Địa chỉ</label>
                            <textarea name="address" class="form-control-admin" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-admin" style="background: #e2e8f0; color: var(--admin-text);" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" name="add_user" class="btn-admin btn-admin-success">Thêm người dùng</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>
