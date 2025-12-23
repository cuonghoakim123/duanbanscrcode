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

// Xử lý thông báo từ URL
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'updated') {
        $success = 'Cập nhật mẫu giao diện thành công!';
    } elseif ($_GET['msg'] == 'added') {
        $success = 'Thêm mẫu giao diện thành công!';
    }
}

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

// Xử lý cập nhật hình ảnh
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_image'])) {
    $template_id = (int)$_POST['template_id'];
    $new_image = trim($_POST['new_image'] ?? '');
    
    // Xử lý image: chỉ lưu tên file vào database
    if (!empty($new_image)) {
        // Sửa đường dẫn sai nếu có /admin/uploads/templates/
        $new_image = str_replace('/admin/uploads/templates/', '/uploads/templates/', $new_image);
        $new_image = str_replace('admin/uploads/templates/', 'uploads/templates/', $new_image);
        
        // Loại bỏ SITE_URL nếu có
        if (strpos($new_image, SITE_URL) === 0) {
            $new_image = str_replace(SITE_URL, '', $new_image);
            $new_image = ltrim($new_image, '/');
        }
        
        // Loại bỏ /uploads/templates/ nếu có
        if (strpos($new_image, 'uploads/templates/') === 0) {
            $new_image = str_replace('uploads/templates/', '', $new_image);
        }
        if (strpos($new_image, '/uploads/templates/') === 0) {
            $new_image = str_replace('/uploads/templates/', '', $new_image);
        }
        
        // Nếu là URL external, extract tên file từ path
        if (preg_match('/^https?:\/\//', $new_image)) {
            $parsed = parse_url($new_image);
            $new_image = isset($parsed['path']) ? basename($parsed['path']) : basename($new_image);
        }
        
        // Đảm bảo chỉ lấy tên file
        $new_image = basename($new_image);
        
        // Cập nhật image trong database
        $query = "UPDATE templates SET image = :image WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':image', $new_image);
        $stmt->bindParam(':id', $template_id);
        
        if ($stmt->execute()) {
            $success = 'Cập nhật hình ảnh thành công!';
        } else {
            $error = 'Có lỗi xảy ra khi cập nhật hình ảnh!';
        }
    } else {
        $error = 'Vui lòng chọn hình ảnh!';
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

// Lấy danh sách templates - ORDER BY updated_at để hiển thị mới nhất trước
$query = "SELECT * FROM templates 
          $where_clause
          ORDER BY updated_at DESC, created_at DESC 
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
                                    <?php 
                                    // Sử dụng helper function để build URL ảnh (không cần cache busting trong admin)
                                    $image_url = buildTemplateImageUrl($template['image'] ?? '', false);
                                    $image_value_db = $template['image'] ?? '';
                                    
                                    if ($image_url): 
                                    ?>
                                        <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                             alt="<?php echo htmlspecialchars($template['name']); ?>" 
                                             class="template-thumb" 
                                             style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;"
                                             onerror="this.onerror=null; this.parentElement.innerHTML='<div style=\'width: 60px; height: 40px; background: #f8f9fa; border-radius: 4px; display: flex; align-items: center; justify-content: center;\' title=\'Image: <?php echo htmlspecialchars(addslashes($image_value_db)); ?>\'><i class=\'fas fa-image text-muted\'></i></div>';">
                                    <?php else: ?>
                                        <div style="width: 60px; height: 40px; background: #f8f9fa; border-radius: 4px; display: flex; align-items: center; justify-content: center;" title="No image in database">
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
                                        <a href="template_edit.php?id=<?php echo $template['id']; ?>" class="btn-admin btn-admin-primary btn-admin-sm" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn-admin btn-admin-info btn-admin-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#statusModal<?php echo $template['id']; ?>" title="Cài đặt">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                        <button type="button" class="btn-admin btn-admin-warning btn-admin-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#imageModal<?php echo $template['id']; ?>" title="Cập nhật hình ảnh">
                                            <i class="fas fa-image"></i>
                                        </button>
                                        <a href="?delete=<?php echo $template['id']; ?>" 
                                           class="btn-admin btn-admin-danger btn-admin-sm"
                                           onclick="return confirm('Xóa mẫu giao diện này?');" title="Xóa">
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

                            <!-- Image Update Modal -->
                            <div class="modal fade" id="imageModal<?php echo $template['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Cập nhật hình ảnh: <?php echo htmlspecialchars($template['name']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <!-- Hiển thị ảnh hiện tại -->
                                            <div class="mb-3">
                                                <label class="form-label">Hình ảnh hiện tại:</label>
                                                <div class="text-center">
                                                    <?php 
                                                    $current_image_url = buildTemplateImageUrl($template['image'] ?? '', false);
                                                    if ($current_image_url): 
                                                    ?>
                                                        <img src="<?php echo htmlspecialchars($current_image_url); ?>" 
                                                             alt="Current image" 
                                                             style="max-width: 200px; max-height: 150px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6;"
                                                             onerror="this.parentElement.innerHTML='<div class=\'text-muted\'><i class=\'fas fa-image\'></i> Không thể tải ảnh hiện tại</div>';">
                                                    <?php else: ?>
                                                        <div class="text-muted">
                                                            <i class="fas fa-image"></i> Chưa có ảnh
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Upload ảnh mới -->
                                            <div class="mb-3">
                                                <label class="form-label">Chọn ảnh mới:</label>
                                                <div class="image-upload-area">
                                                    <input type="file" class="form-control" id="imageUpload_<?php echo $template['id']; ?>" accept="image/*" style="padding: 8px;">
                                                    <small class="text-muted d-block mt-2">Chấp nhận JPG, PNG, GIF, WebP - Tối đa 5MB<br>Kéo thả file vào đây hoặc click để chọn</small>
                                                </div>
                                            </div>

                                            <!-- Preview ảnh mới -->
                                            <div class="mb-3" id="imagePreview_<?php echo $template['id']; ?>" style="display: none;">
                                                <label class="form-label">Xem trước:</label>
                                                <div class="text-center">
                                                    <img id="previewImg_<?php echo $template['id']; ?>" src="" alt="Preview" 
                                                         style="max-width: 200px; max-height: 150px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6;">
                                                </div>
                                            </div>

                                            <!-- Form cập nhật -->
                                            <form method="POST" id="imageForm_<?php echo $template['id']; ?>">
                                                <input type="hidden" name="template_id" value="<?php echo $template['id']; ?>">
                                                <input type="hidden" name="new_image" id="newImagePath_<?php echo $template['id']; ?>" value="">
                                                
                                                <div class="d-flex justify-content-end gap-2">
                                                    <button type="button" class="btn-admin btn-admin-primary" 
                                                            onclick="uploadTemplateImage(<?php echo $template['id']; ?>)"
                                                            id="uploadBtn_<?php echo $template['id']; ?>">
                                                        <i class="fas fa-cloud-upload-alt"></i> Upload & Cập nhật
                                                    </button>
                                                    <button type="button" class="btn-admin btn-admin-secondary" data-bs-dismiss="modal">Đóng</button>
                                                </div>
                                            </form>
                                        </div>
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
.btn-admin.btn-admin-warning {
    background-color: #f59e0b;
    border-color: #f59e0b;
    color: white;
}
.btn-admin.btn-admin-warning:hover {
    background-color: #d97706;
    border-color: #d97706;
    color: white;
}
.modal-body .text-center img {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.form-control:focus {
    border-color: #4299e1;
    box-shadow: 0 0 0 0.2rem rgba(66, 153, 225, 0.25);
}
.image-upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    transition: border-color 0.3s;
}
.image-upload-area:hover {
    border-color: #4299e1;
}
.image-upload-area.dragover {
    border-color: #4299e1;
    background-color: rgba(66, 153, 225, 0.05);
}
</style>

<script>
// Preview image when selected
document.addEventListener('DOMContentLoaded', function() {
    // Thiết lập preview cho tất cả các input file
    const fileInputs = document.querySelectorAll('input[type="file"][id^="imageUpload_"]');
    fileInputs.forEach(function(input) {
        const templateId = input.id.split('_')[1];
        input.addEventListener('change', function() {
            previewImage(this, templateId);
        });
    });

    // Add drag and drop functionality
    fileInputs.forEach(function(input) {
        const templateId = input.id.split('_')[1];
        const uploadArea = input.parentElement;
        
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', function() {
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                input.files = files;
                previewImage(input, templateId);
            }
        });
    });
});

function previewImage(input, templateId) {
    const preview = document.getElementById('imagePreview_' + templateId);
    const previewImg = document.getElementById('previewImg_' + templateId);
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WebP)!');
            input.value = '';
            preview.style.display = 'none';
            return;
        }
        
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('File quá lớn! Kích thước tối đa: 5MB');
            input.value = '';
            preview.style.display = 'none';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
}

function uploadTemplateImage(templateId) {
    const fileInput = document.getElementById('imageUpload_' + templateId);
    const uploadBtn = document.getElementById('uploadBtn_' + templateId);
    const newImagePath = document.getElementById('newImagePath_' + templateId);
    
    if (!fileInput.files || !fileInput.files[0]) {
        alert('Vui lòng chọn file ảnh!');
        return;
    }
    
    const file = fileInput.files[0];
    
    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        alert('Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WebP)!');
        return;
    }
    
    // Validate file size (5MB)
    if (file.size > 5 * 1024 * 1024) {
        alert('File quá lớn! Kích thước tối đa: 5MB');
        return;
    }
    
    // Disable button and show loading
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang upload...';
    
    // Create form data
    const formData = new FormData();
    formData.append('image', file);
    formData.append('upload_type', 'template');
    
    // Upload file
    fetch('upload_image.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Set the image path for form submission
            newImagePath.value = data.filename; // Chỉ lưu filename
            
            // Submit form để cập nhật database
            const form = document.getElementById('imageForm_' + templateId);
            const updateFormData = new FormData(form);
            updateFormData.append('update_image', '1');
            
            return fetch(window.location.href, {
                method: 'POST',
                body: updateFormData
            });
        } else {
            throw new Error(data.message || 'Upload failed');
        }
    })
    .then(response => {
        if (response.ok) {
            // Success - reload page to show updated image
            alert('Cập nhật hình ảnh thành công!');
            window.location.reload();
        } else {
            throw new Error('Failed to update database');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra: ' + error.message);
    })
    .finally(() => {
        // Re-enable button
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Upload & Cập nhật';
    });
}

// Show success message if redirected with success parameter
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('image_updated') === '1') {
        // Scroll to top and highlight success
        window.scrollTo(0, 0);
        
        // You can add any additional success handling here
        setTimeout(() => {
            // Clean URL
            if (window.history.replaceState) {
                const newUrl = window.location.href.replace(/[?&]image_updated=1/, '');
                window.history.replaceState(null, '', newUrl);
            }
        }, 1000);
    }
});
</script>

<?php include 'includes/admin_footer.php'; ?>

