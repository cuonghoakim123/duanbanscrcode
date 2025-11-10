<?php
require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

$title = 'Thêm dịch vụ mới';
$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

// Xử lý form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $description = trim($_POST['description'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $icon = trim($_POST['icon'] ?? '');
    $image = trim($_POST['image'] ?? '');
    $price_from = !empty($_POST['price_from']) ? (float)$_POST['price_from'] : null;
    $price_unit = trim($_POST['price_unit'] ?? 'đ');
    $features = trim($_POST['features'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = $_POST['status'] ?? 'active';
    $sort_order = (int)$_POST['sort_order'];
    
    // Validation
    if (empty($name)) {
        $error = 'Vui lòng nhập tên dịch vụ!';
    } else {
        // Tạo slug nếu chưa có
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        }
        
        // Kiểm tra slug đã tồn tại chưa
        try {
            $check_query = "SELECT id FROM services WHERE slug = :slug";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->bindParam(':slug', $slug);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                $error = 'Slug đã tồn tại! Vui lòng chọn giá trị khác.';
            } else {
                // Thêm service
                $query = "INSERT INTO services (name, slug, description, content, icon, image, price_from, price_unit, features, featured, status, sort_order) 
                          VALUES (:name, :slug, :description, :content, :icon, :image, :price_from, :price_unit, :features, :featured, :status, :sort_order)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':slug', $slug);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':content', $content);
                $stmt->bindParam(':icon', $icon);
                $stmt->bindParam(':image', $image);
                $stmt->bindParam(':price_from', $price_from);
                $stmt->bindParam(':price_unit', $price_unit);
                $stmt->bindParam(':features', $features);
                $stmt->bindParam(':featured', $featured);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':sort_order', $sort_order);
                
                if ($stmt->execute()) {
                    header('Location: services.php?msg=added');
                    exit();
                } else {
                    $error = 'Có lỗi xảy ra khi thêm dịch vụ!';
                }
            }
        } catch (PDOException $e) {
            $error = 'Bảng services chưa được tạo. Vui lòng chạy file database/services_table.sql. Error: ' . $e->getMessage();
        }
    }
}

include 'includes/admin_header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h5><i class="fas fa-plus"></i> Thêm dịch vụ mới</h5>
    </div>
    <div class="admin-card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <!-- Thông tin cơ bản -->
                    <div class="admin-card mb-4">
                        <div class="admin-card-header">
                            <h5><i class="fas fa-info-circle"></i> Thông tin cơ bản</h5>
                        </div>
                        <div class="admin-card-body">
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Tên dịch vụ <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control-admin" 
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                       required placeholder="Nhập tên dịch vụ">
                            </div>
                            
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Slug</label>
                                <input type="text" name="slug" class="form-control-admin" 
                                       value="<?php echo isset($_POST['slug']) ? htmlspecialchars($_POST['slug']) : ''; ?>"
                                       placeholder="ten-dich-vu (tự động tạo nếu để trống)">
                                <small class="text-muted">URL thân thiện SEO</small>
                            </div>
                            
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Mô tả ngắn</label>
                                <textarea name="description" class="form-control-admin" rows="3" 
                                          placeholder="Mô tả ngắn về dịch vụ"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Nội dung chi tiết</label>
                                <textarea name="content" class="form-control-admin" rows="10" 
                                          placeholder="Nội dung chi tiết của dịch vụ"><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                                <small class="text-muted">Có thể sử dụng HTML để format nội dung</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tính năng -->
                    <div class="admin-card mb-4">
                        <div class="admin-card-header">
                            <h5><i class="fas fa-list"></i> Tính năng</h5>
                        </div>
                        <div class="admin-card-body">
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Danh sách tính năng</label>
                                <textarea name="features" class="form-control-admin" rows="6" 
                                          placeholder="Mỗi tính năng một dòng, ví dụ:&#10;Responsive design&#10;Tối ưu SEO&#10;Tốc độ tải nhanh"><?php echo isset($_POST['features']) ? htmlspecialchars($_POST['features']) : ''; ?></textarea>
                                <small class="text-muted">Mỗi tính năng một dòng</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hình ảnh và Icon -->
                    <div class="admin-card mb-4">
                        <div class="admin-card-header">
                            <h5><i class="fas fa-image"></i> Hình ảnh & Icon</h5>
                        </div>
                        <div class="admin-card-body">
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Icon (Font Awesome)</label>
                                <input type="text" name="icon" class="form-control-admin" 
                                       value="<?php echo isset($_POST['icon']) ? htmlspecialchars($_POST['icon']) : ''; ?>"
                                       placeholder="fas fa-laptop-code">
                                <small class="text-muted">Ví dụ: fas fa-laptop-code, fas fa-shopping-cart</small>
                            </div>
                            
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Ảnh đại diện</label>
                                <div class="mb-3">
                                    <input type="file" id="imageUpload" class="form-control-admin" accept="image/*" style="padding: 8px;">
                                    <small class="text-muted">Chọn file ảnh (JPG, PNG, GIF, WebP - Tối đa 5MB)</small>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex gap-2 align-items-center">
                                        <input type="text" name="image" id="imageUrl" class="form-control-admin" 
                                               value="<?php echo isset($_POST['image']) ? htmlspecialchars($_POST['image']) : ''; ?>"
                                               placeholder="Hoặc nhập URL/đường dẫn hình ảnh">
                                        <button type="button" class="btn-admin btn-admin-sm" style="background: #4299e1; color: white;" onclick="clearImage()">
                                            <i class="fas fa-times"></i> Xóa
                                        </button>
                                    </div>
                                    <small class="text-muted">Có thể upload file hoặc nhập URL/đường dẫn</small>
                                </div>
                                <div id="imagePreview" class="mt-3" style="display: none;">
                                    <img id="previewImg" src="" alt="Preview" 
                                         style="max-width: 300px; max-height: 300px; border-radius: 8px; border: 2px solid var(--admin-border); object-fit: contain;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <!-- Giá và cài đặt -->
                    <div class="admin-card mb-4">
                        <div class="admin-card-header">
                            <h5><i class="fas fa-cog"></i> Cài đặt</h5>
                        </div>
                        <div class="admin-card-body">
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Giá từ</label>
                                <div class="d-flex gap-2">
                                    <input type="number" name="price_from" class="form-control-admin" 
                                           value="<?php echo isset($_POST['price_from']) ? htmlspecialchars($_POST['price_from']) : ''; ?>"
                                           placeholder="0" step="0.01" min="0">
                                    <input type="text" name="price_unit" class="form-control-admin" 
                                           value="<?php echo isset($_POST['price_unit']) ? htmlspecialchars($_POST['price_unit']) : 'đ'; ?>"
                                           placeholder="đ" style="width: 80px;">
                                </div>
                                <small class="text-muted">Ví dụ: 1500000 và đ</small>
                            </div>
                            
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Thứ tự hiển thị</label>
                                <input type="number" name="sort_order" class="form-control-admin" 
                                       value="<?php echo isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0; ?>"
                                       placeholder="0" min="0">
                                <small class="text-muted">Số nhỏ hơn sẽ hiển thị trước</small>
                            </div>
                            
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Trạng thái</label>
                                <select name="status" class="form-control-admin">
                                    <option value="active" <?php echo (isset($_POST['status']) && $_POST['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            
                            <div class="form-group-admin mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="featured" id="featured" 
                                           <?php echo (isset($_POST['featured']) && $_POST['featured']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="featured">
                                        Dịch vụ nổi bật
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="admin-card">
                        <div class="admin-card-body">
                            <button type="submit" class="btn-admin btn-admin-primary w-100 mb-2">
                                <i class="fas fa-save"></i> Lưu dịch vụ
                            </button>
                            <a href="services.php" class="btn-admin btn-admin-secondary w-100">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Auto generate slug from name
document.querySelector('input[name="name"]').addEventListener('input', function() {
    const slugInput = document.querySelector('input[name="slug"]');
    if (!slugInput.dataset.manual) {
        const name = this.value;
        const slug = name.toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');
        slugInput.value = slug;
    }
});

// Mark slug as manually edited
document.querySelector('input[name="slug"]').addEventListener('input', function() {
    this.dataset.manual = 'true';
});

// Upload image function
function uploadServiceImage(file) {
    const formData = new FormData();
    formData.append('image', file);
    formData.append('upload_type', 'services');
    
    const xhr = new XMLHttpRequest();
    
    xhr.addEventListener('load', function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                // Lưu đường dẫn ảnh
                if (response.path) {
                    document.getElementById('imageUrl').value = response.path;
                } else if (response.url) {
                    document.getElementById('imageUrl').value = response.url;
                }
                document.getElementById('previewImg').src = response.url;
                document.getElementById('imagePreview').style.display = 'block';
            } else {
                alert('Lỗi: ' + response.message);
            }
        } else {
            alert('Có lỗi xảy ra khi upload ảnh');
        }
    });
    
    xhr.open('POST', 'upload_image.php');
    xhr.send(formData);
}

// Handle image upload
document.getElementById('imageUpload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        uploadServiceImage(file);
    }
});

// Clear image
function clearImage() {
    document.getElementById('imageUrl').value = '';
    document.getElementById('imageUpload').value = '';
    document.getElementById('imagePreview').style.display = 'none';
}

// Preview image from URL
document.getElementById('imageUrl').addEventListener('blur', function() {
    const url = this.value;
    if (url) {
        // Nếu là URL đầy đủ
        if (url.startsWith('http')) {
            document.getElementById('previewImg').src = url;
        } else if (url.startsWith('/')) {
            document.getElementById('previewImg').src = '<?php echo SITE_URL; ?>' + url;
        } else {
            document.getElementById('previewImg').src = '<?php echo SITE_URL; ?>/uploads/services/' + url;
        }
        document.getElementById('imagePreview').style.display = 'block';
    }
});
</script>

<?php include 'includes/admin_footer.php'; ?>

