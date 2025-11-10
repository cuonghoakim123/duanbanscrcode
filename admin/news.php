<?php
require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

$title = 'Quản lý tin tức';
$database = new Database();
$db = $database->getConnection();

// Kiểm tra xem bảng news có tồn tại không
try {
    $test_query = "SELECT 1 FROM news LIMIT 1";
    $db->query($test_query);
} catch (PDOException $e) {
    $error = 'Bảng news chưa được tạo. Vui lòng chạy file database/news_table.sql';
    include 'includes/admin_header.php';
    echo '<div class="admin-card"><div class="admin-card-body"><div class="alert alert-danger">' . $error . '</div></div></div>';
    include 'includes/admin_footer.php';
    exit();
}

$success = '';
$error = '';

// Xử lý xóa news
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Lấy thông tin ảnh để xóa
    $query = "SELECT image FROM news WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $news = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Xóa news
    $query = "DELETE FROM news WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        // Xóa ảnh nếu có
        if ($news && $news['image'] && file_exists('../uploads/news/' . basename($news['image']))) {
            unlink('../uploads/news/' . basename($news['image']));
        }
        $success = 'Xóa tin tức thành công!';
    } else {
        $error = 'Có lỗi xảy ra!';
    }
}

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $news_id = (int)$_POST['news_id'];
    $status = $_POST['status'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    $query = "UPDATE news SET status = :status, featured = :featured WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':featured', $featured);
    $stmt->bindParam(':id', $news_id);
    
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
    $where_conditions[] = "(title LIKE :search OR excerpt LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Đếm tổng số news
$count_query = "SELECT COUNT(*) as total FROM news $where_clause";
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_news = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Lấy danh sách news
$query = "SELECT * FROM news $where_clause ORDER BY created_at DESC";
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$news_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách categories
$categories_query = "SELECT DISTINCT category FROM news WHERE category IS NOT NULL AND category != '' ORDER BY category";
$categories_stmt = $db->prepare($categories_query);
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);

include 'includes/admin_header.php';
?>

<div class="admin-card">
    <div class="admin-card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-newspaper"></i> Quản lý tin tức</h5>
        <a href="news_add.php" class="btn-admin btn-admin-primary">
            <i class="fas fa-plus"></i> Thêm tin tức mới
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
            <div class="col-md-4">
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
                <select class="form-control-admin" onchange="window.location.href='?category=' + this.value + '&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>'">
                    <option value="">Tất cả danh mục</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" 
                                <?php echo $category_filter == $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-control-admin" onchange="window.location.href='?category=<?php echo urlencode($category_filter); ?>&status=' + this.value + '&search=<?php echo urlencode($search); ?>'">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="draft" <?php echo $status_filter == 'draft' ? 'selected' : ''; ?>>Draft</option>
                </select>
            </div>
            <div class="col-md-2">
                <a href="news.php" class="btn-admin btn-admin-secondary w-100">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </div>
        
        <!-- News Table -->
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tiêu đề</th>
                        <th>Danh mục</th>
                        <th>Lượt xem</th>
                        <th>Trạng thái</th>
                        <th>Nổi bật</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($news_list)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                Chưa có tin tức nào. <a href="news_add.php">Thêm tin tức mới</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($news_list as $news): ?>
                            <tr>
                                <td><?php echo $news['id']; ?></td>
                                <td>
                                    <?php if ($news['image']): ?>
                                        <?php 
                                        $image_path = strpos($news['image'], 'http') === 0 ? $news['image'] : SITE_URL . '/uploads/news/' . basename($news['image']);
                                        ?>
                                        <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                             alt="<?php echo htmlspecialchars($news['title']); ?>"
                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                    <?php else: ?>
                                        <div style="width: 60px; height: 60px; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($news['title']); ?></strong>
                                    <?php if ($news['excerpt']): ?>
                                        <br><small class="text-muted"><?php echo mb_substr(htmlspecialchars($news['excerpt']), 0, 50); ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($news['category'] ?? 'N/A'); ?></span>
                                </td>
                                <td><?php echo number_format($news['views']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $news['status'] == 'active' ? 'success' : ($news['status'] == 'draft' ? 'warning' : 'secondary'); ?>">
                                        <?php echo ucfirst($news['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($news['featured']): ?>
                                        <span class="badge bg-warning"><i class="fas fa-star"></i> Nổi bật</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($news['created_at'])); ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="news_edit.php?id=<?php echo $news['id']; ?>" 
                                           class="btn-admin btn-admin-sm btn-admin-primary" 
                                           title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="news_id" value="<?php echo $news['id']; ?>">
                                            <input type="hidden" name="status" value="<?php echo $news['status'] == 'active' ? 'inactive' : 'active'; ?>">
                                            <input type="hidden" name="featured" value="<?php echo $news['featured'] ? '0' : '1'; ?>">
                                            <button type="submit" name="update_status" 
                                                    class="btn-admin btn-admin-sm btn-admin-<?php echo $news['status'] == 'active' ? 'warning' : 'success'; ?>" 
                                                    title="<?php echo $news['status'] == 'active' ? 'Vô hiệu hóa' : 'Kích hoạt'; ?>">
                                                <i class="fas fa-<?php echo $news['status'] == 'active' ? 'eye-slash' : 'eye'; ?>"></i>
                                            </button>
                                        </form>
                                        <a href="?delete=<?php echo $news['id']; ?>" 
                                           class="btn-admin btn-admin-sm btn-admin-danger" 
                                           onclick="return confirm('Bạn có chắc chắn muốn xóa tin tức này?');"
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
            <p class="text-muted">Tổng số: <strong><?php echo $total_news; ?></strong> tin tức</p>
        </div>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>

