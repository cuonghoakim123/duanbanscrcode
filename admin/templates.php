<?php
require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

$title = 'Quản lý mẫu giao diện';
$database = new Database();
$db = $database->getConnection();

// Kiểm tra xem bảng templates có tồn tại không
try {
    $test_query = "SELECT 1 FROM templates LIMIT 1";
    $db->query($test_query);
} catch (PDOException $e) {
    $error = 'Bảng templates chưa được tạo. Vui lòng chạy file database/templates_table.sql';
    include 'includes/admin_header.php';
    echo '<div class="admin-card"><div class="admin-card-body"><div class="alert alert-danger">' . $error . '</div></div></div>';
    include 'includes/admin_footer.php';
    exit();
}

$success = '';
$error = '';

// Xử lý xóa template
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $query = "DELETE FROM templates WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        $success = 'Xóa mẫu giao diện thành công!';
    } else {
        $error = 'Có lỗi xảy ra!';
    }
}

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $template_id = (int)$_POST['template_id'];
    $status = $_POST['status'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    $query = "UPDATE templates SET status = :status, featured = :featured WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':featured', $featured);
    $stmt->bindParam(':id', $template_id);
    
    if ($stmt->execute()) {
        $success = 'Cập nhật trạng thái thành công!';
    } else {
        $error = 'Có lỗi xảy ra!';
    }
}

// Lọc
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$where_conditions = [];
$params = [];

if ($category_filter) {
    $where_conditions[] = "category = :category";
    $params[':category'] = $category_filter;
}

if ($status_filter) {
    $where_conditions[] = "status = :status";
    $params[':status'] = $status_filter;
}

if ($search) {
    $where_conditions[] = "(name LIKE :search OR description LIKE :search OR slug LIKE :search)";
    $params[':search'] = "%$search%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Đếm tổng
$count_query = "SELECT COUNT(*) as total FROM templates $where_clause";
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total / $limit);

// Lấy danh sách templates
$query = "SELECT * FROM templates 
          $where_clause
          ORDER BY created_at DESC 
          LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Categories
$categories = [
    'business' => 'Doanh nghiệp',
    'ecommerce' => 'Bán hàng',
    'restaurant' => 'Nhà hàng',
    'realestate' => 'Bất động sản',
    'education' => 'Giáo dục',
    'healthcare' => 'Y tế',
    'beauty' => 'Làm đẹp',
    'other' => 'Khác'
];

include 'includes/admin_header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h5><i class="fas fa-palette"></i> Quản lý mẫu giao diện</h5>
        <a href="template_add.php" class="btn-admin btn-admin-primary btn-admin-sm">
            <i class="fas fa-plus"></i> Thêm mẫu mới
        </a>
    </div>
    <div class="admin-card-body">
        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- Filter Form -->
        <form method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-select">
                        <option value="">Tất cả danh mục</option>
                        <?php foreach($categories as $key => $label): ?>
                            <option value="<?php echo $key; ?>" <?php echo $category_filter == $key ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Đang hoạt động</option>
                        <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Ngừng hoạt động</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn-admin btn-admin-primary w-100">
                        <i class="fas fa-search"></i> Lọc
                    </button>
                </div>
                <div class="col-md-3 text-end">
                    <span class="text-muted">Tổng: <?php echo $total; ?> mẫu</span>
                </div>
            </div>
        </form>
        
        <!-- Templates Table -->
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hình ảnh</th>
                        <th>Tên mẫu</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Lượt xem</th>
                        <th>Trạng thái</th>
                        <th>Nổi bật</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($templates)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-palette"></i>
                                    <h4>Chưa có mẫu giao diện</h4>
                                    <p>Hãy thêm mẫu giao diện mới</p>
                                    <a href="template_add.php" class="btn-admin btn-admin-primary mt-3">
                                        <i class="fas fa-plus"></i> Thêm mẫu mới
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($templates as $template): ?>
                            <tr>
                                <td><?php echo $template['id']; ?></td>
                                <td>
                                    <?php if ($template['image']): ?>
                                        <?php 
                                        // Kiểm tra nếu là URL đầy đủ hoặc chỉ là tên file
                                        $image_url = $template['image'];
                                        if (!preg_match('/^https?:\/\//', $image_url) && !preg_match('/^\//', $image_url)) {
                                            $image_url = SITE_URL . '/uploads/templates/' . $image_url;
                                        }
                                        ?>
                                        <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                             alt="<?php echo htmlspecialchars($template['name']); ?>" 
                                             class="template-thumb" 
                                             style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                                    <?php else: ?>
                                        <div style="width: 60px; height: 40px; background: #f8f9fa; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($template['name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($template['slug']); ?></small>
                                </td>
                                <td>
                                    <span class="badge-admin badge-info"><?php echo $categories[$template['category']] ?? $template['category']; ?></span>
                                </td>
                                <td>
                                    <?php if ($template['sale_price']): ?>
                                        <div>
                                            <span class="text-decoration-line-through text-muted small">
                                                <?php echo number_format($template['price']); ?>đ
                                            </span><br>
                                            <strong class="text-danger"><?php echo number_format($template['sale_price']); ?>đ</strong>
                                        </div>
                                    <?php else: ?>
                                        <strong><?php echo number_format($template['price']); ?>đ</strong>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo number_format($template['views']); ?></td>
                                <td>
                                    <?php 
                                    $status_badges = [
                                        'active' => 'success',
                                        'inactive' => 'secondary'
                                    ];
                                    $badge_class = $status_badges[$template['status']] ?? 'secondary';
                                    echo '<span class="badge-admin badge-' . $badge_class . '">' . ucfirst($template['status']) . '</span>';
                                    ?>
                                </td>
                                <td>
                                    <?php if ($template['featured']): ?>
                                        <span class="badge-admin badge-warning"><i class="fas fa-star"></i> Nổi bật</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="template_edit.php?id=<?php echo $template['id']; ?>" class="btn-admin btn-admin-primary btn-admin-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn-admin btn-admin-info btn-admin-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#statusModal<?php echo $template['id']; ?>">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                        <a href="?delete=<?php echo $template['id']; ?>" 
                                           class="btn-admin btn-admin-danger btn-admin-sm"
                                           onclick="return confirm('Xóa mẫu giao diện này?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Status Modal -->
                            <div class="modal fade" id="statusModal<?php echo $template['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Cập nhật trạng thái</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="template_id" value="<?php echo $template['id']; ?>">
                                                <div class="mb-3">
                                                    <label class="form-label">Trạng thái</label>
                                                    <select name="status" class="form-select">
                                                        <option value="active" <?php echo $template['status'] == 'active' ? 'selected' : ''; ?>>Đang hoạt động</option>
                                                        <option value="inactive" <?php echo $template['status'] == 'inactive' ? 'selected' : ''; ?>>Ngừng hoạt động</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="featured" value="1" id="featured<?php echo $template['id']; ?>"
                                                               <?php echo $template['featured'] ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="featured<?php echo $template['id']; ?>">
                                                            Mẫu nổi bật
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" name="update_status" class="btn-admin btn-admin-primary">
                                                    <i class="fas fa-save"></i> Cập nhật
                                                </button>
                                                <button type="button" class="btn-admin btn-admin-secondary" data-bs-dismiss="modal">Đóng</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&category=<?php echo $category_filter; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&category=<?php echo $category_filter; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&category=<?php echo $category_filter; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<style>
.template-thumb {
    border: 1px solid #dee2e6;
}
</style>

<?php include 'includes/admin_footer.php'; ?>

