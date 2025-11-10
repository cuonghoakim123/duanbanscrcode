<?php
require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

$title = 'Quản lý danh mục';
$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

// Xử lý thêm/sửa danh mục
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    if (empty($name)) {
        $error = 'Vui lòng nhập tên danh mục!';
    } else {
        // Tạo slug nếu chưa có
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        }
        
        if ($id) {
            // Cập nhật
            $query = "UPDATE categories SET name = :name, slug = :slug, description = :description, status = :status WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':status', $status);
            
            if ($stmt->execute()) {
                $success = 'Cập nhật danh mục thành công!';
            } else {
                $error = 'Có lỗi xảy ra khi cập nhật!';
            }
        } else {
            // Thêm mới
            $query = "INSERT INTO categories (name, slug, description, status) VALUES (:name, :slug, :description, :status)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':status', $status);
            
            if ($stmt->execute()) {
                $success = 'Thêm danh mục thành công!';
            } else {
                $error = 'Có lỗi xảy ra khi thêm!';
            }
        }
    }
}

// Xử lý xóa
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $query = "DELETE FROM categories WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $success = 'Xóa danh mục thành công!';
}

// Lấy danh sách danh mục
$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count
          FROM categories c 
          ORDER BY c.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh mục để sửa
$edit_category = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $query = "SELECT * FROM categories WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $edit_category = $stmt->fetch(PDO::FETCH_ASSOC);
}

include 'includes/admin_header.php';
?>

<!-- Add/Edit Category Modal -->
<div class="admin-card mb-4">
    <div class="admin-card-header">
        <h5><i class="fas fa-<?php echo $edit_category ? 'edit' : 'plus'; ?>"></i> <?php echo $edit_category ? 'Sửa danh mục' : 'Thêm danh mục mới'; ?></h5>
    </div>
    <div class="admin-card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <?php if ($edit_category): ?>
                <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group-admin">
                        <label class="form-label-admin">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control-admin" required 
                               value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>"
                               placeholder="Nhập tên danh mục">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group-admin">
                        <label class="form-label-admin">Slug (URL)</label>
                        <input type="text" name="slug" class="form-control-admin" 
                               value="<?php echo $edit_category ? htmlspecialchars($edit_category['slug']) : ''; ?>"
                               placeholder="Tự động tạo từ tên">
                        <small class="text-muted">Để trống để tự động tạo</small>
                    </div>
                </div>
            </div>
            
            <div class="form-group-admin">
                <label class="form-label-admin">Mô tả</label>
                <textarea name="description" class="form-control-admin" rows="3" 
                          placeholder="Nhập mô tả danh mục"><?php echo $edit_category ? htmlspecialchars($edit_category['description']) : ''; ?></textarea>
            </div>
            
            <div class="form-group-admin">
                <label class="form-label-admin">Trạng thái</label>
                <select name="status" class="form-control-admin">
                    <option value="active" <?php echo ($edit_category && $edit_category['status'] == 'active') ? 'selected' : ''; ?>>Hoạt động</option>
                    <option value="inactive" <?php echo ($edit_category && $edit_category['status'] == 'inactive') ? 'selected' : ''; ?>>Không hoạt động</option>
                </select>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn-admin btn-admin-primary">
                    <i class="fas fa-save"></i> <?php echo $edit_category ? 'Cập nhật' : 'Thêm mới'; ?>
                </button>
                <?php if ($edit_category): ?>
                    <a href="categories.php" class="btn-admin btn-admin-sm" style="background: #e2e8f0; color: var(--admin-text);">
                        <i class="fas fa-times"></i> Hủy
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Categories List -->
<div class="admin-card">
    <div class="admin-card-header">
        <h5><i class="fas fa-list"></i> Danh sách danh mục</h5>
    </div>
    <div class="admin-card-body">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên danh mục</th>
                        <th>Slug</th>
                        <th>Số sản phẩm</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($categories)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-tags"></i>
                                    <h4>Chưa có danh mục</h4>
                                    <p>Hãy thêm danh mục đầu tiên</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($categories as $category): ?>
                            <tr>
                                <td><?php echo $category['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($category['name']); ?></strong></td>
                                <td><code><?php echo htmlspecialchars($category['slug']); ?></code></td>
                                <td>
                                    <span class="badge-admin badge-info"><?php echo $category['product_count']; ?> sản phẩm</span>
                                </td>
                                <td>
                                    <span class="badge-admin badge-<?php echo $category['status'] == 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo $category['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($category['created_at'])); ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="categories.php?edit=<?php echo $category['id']; ?>" class="btn-admin btn-admin-primary btn-admin-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="categories.php?delete=<?php echo $category['id']; ?>" 
                                           class="btn-admin btn-admin-danger btn-admin-sm"
                                           onclick="return confirm('Bạn có chắc muốn xóa danh mục này?');">
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
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>

