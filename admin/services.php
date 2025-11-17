<?php
require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

$title = 'Quản lý dịch vụ';
$database = new Database();
$db = $database->getConnection();

// Kiểm tra xem bảng services có tồn tại không
try {
    $test_query = "SELECT 1 FROM services LIMIT 1";
    $db->query($test_query);
} catch (PDOException $e) {
    $error = 'Bảng services chưa được tạo. Vui lòng chạy file database/services_table.sql';
    include 'includes/admin_header.php';
    echo '<div class="admin-card"><div class="admin-card-body"><div class="alert alert-danger">' . $error . '</div></div></div>';
    include 'includes/admin_footer.php';
    exit();
}

$success = '';
$error = '';

// Xử lý xóa service
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Lấy thông tin ảnh để xóa
    $query = "SELECT image FROM services WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Xóa service
    $query = "DELETE FROM services WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        // Xóa ảnh nếu có
        if ($service && $service['image'] && file_exists('../uploads/services/' . basename($service['image']))) {
            unlink('../uploads/services/' . basename($service['image']));
        }
        $success = 'Xóa dịch vụ thành công!';
    } else {
        $error = 'Có lỗi xảy ra!';
    }
}

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $service_id = (int)$_POST['service_id'];
    $status = $_POST['status'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $sort_order = (int)$_POST['sort_order'];
    
    $query = "UPDATE services SET status = :status, featured = :featured, sort_order = :sort_order WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':featured', $featured);
    $stmt->bindParam(':sort_order', $sort_order);
    $stmt->bindParam(':id', $service_id);
    
    if ($stmt->execute()) {
        $success = 'Cập nhật trạng thái thành công!';
    } else {
        $error = 'Có lỗi xảy ra!';
    }
}

// Lọc
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "status = :status";
    $params[':status'] = $status_filter;
}

if ($search) {
    $where_conditions[] = "(name LIKE :search OR description LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Đếm tổng số services
$count_query = "SELECT COUNT(*) as total FROM services $where_clause";
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_services = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Lấy danh sách services
$query = "SELECT * FROM services $where_clause ORDER BY sort_order ASC, created_at DESC";
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$services_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/admin_header.php';
?>

<div class="admin-card">
    <div class="admin-card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-concierge-bell"></i> Quản lý dịch vụ</h5>
        <a href="service_add.php" class="btn-admin btn-admin-primary">
            <i class="fas fa-plus"></i> Thêm dịch vụ mới
        </a>
    </div>
    <div class="admin-card-body">
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-6">
                <form method="GET" action="" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control-admin" 
                           placeholder="Tìm kiếm..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn-admin btn-admin-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <div class="col-md-3">
                <select class="form-control-admin" onchange="window.location.href='?status=' + this.value + '&search=<?php echo urlencode($search); ?>'">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <a href="services.php" class="btn-admin btn-admin-secondary w-100">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </div>
        
        <!-- Services Table -->
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Icon/Ảnh</th>
                        <th>Tên dịch vụ</th>
                        <th>Giá từ</th>
                        <th>Thứ tự</th>
                        <th>Trạng thái</th>
                        <th>Nổi bật</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($services_list)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                Chưa có dịch vụ nào. <a href="service_add.php">Thêm dịch vụ mới</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($services_list as $service): ?>
                            <tr>
                                <td><?php echo $service['id']; ?></td>
                                <td>
                                    <?php if ($service['image']): ?>
                                        <?php 
                                        $image_path = strpos($service['image'], 'http') === 0 ? $service['image'] : SITE_URL . '/uploads/services/' . basename($service['image']);
                                        ?>
                                        <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                             alt="<?php echo htmlspecialchars($service['name']); ?>"
                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                    <?php elseif ($service['icon']): ?>
                                        <i class="<?php echo htmlspecialchars($service['icon']); ?> fa-2x text-primary"></i>
                                    <?php else: ?>
                                        <div style="width: 60px; height: 60px; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-concierge-bell text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($service['name']); ?></strong>
                                    <?php if ($service['description']): ?>
                                        <br><small class="text-muted"><?php echo safe_substr(htmlspecialchars($service['description']), 0, 50); ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($service['price_from']): ?>
                                        <strong style="color: var(--admin-danger);">
                                            <?php echo number_format($service['price_from']); ?>
                                            <?php echo $service['price_unit'] ? htmlspecialchars($service['price_unit']) : 'đ'; ?>
                                        </strong>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                        <input type="hidden" name="status" value="<?php echo $service['status']; ?>">
                                        <input type="hidden" name="featured" value="<?php echo $service['featured']; ?>">
                                        <input type="number" name="sort_order" value="<?php echo $service['sort_order']; ?>" 
                                               class="form-control-admin" style="width: 80px; display: inline-block;"
                                               onchange="this.form.submit()">
                                    </form>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $service['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($service['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($service['featured']): ?>
                                        <span class="badge bg-warning"><i class="fas fa-star"></i> Nổi bật</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($service['created_at'])); ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="service_edit.php?id=<?php echo $service['id']; ?>" 
                                           class="btn-admin btn-admin-sm btn-admin-primary" 
                                           title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                            <input type="hidden" name="status" value="<?php echo $service['status'] == 'active' ? 'inactive' : 'active'; ?>">
                                            <input type="hidden" name="featured" value="<?php echo $service['featured']; ?>">
                                            <input type="hidden" name="sort_order" value="<?php echo $service['sort_order']; ?>">
                                            <button type="submit" name="update_status" 
                                                    class="btn-admin btn-admin-sm btn-admin-<?php echo $service['status'] == 'active' ? 'warning' : 'success'; ?>" 
                                                    title="<?php echo $service['status'] == 'active' ? 'Vô hiệu hóa' : 'Kích hoạt'; ?>">
                                                <i class="fas fa-<?php echo $service['status'] == 'active' ? 'eye-slash' : 'eye'; ?>"></i>
                                            </button>
                                        </form>
                                        <a href="?delete=<?php echo $service['id']; ?>" 
                                           class="btn-admin btn-admin-sm btn-admin-danger" 
                                           onclick="return confirm('Bạn có chắc chắn muốn xóa dịch vụ này?');"
                                           title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            <p class="text-muted">Tổng số: <strong><?php echo $total_services; ?></strong> dịch vụ</p>
        </div>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>

