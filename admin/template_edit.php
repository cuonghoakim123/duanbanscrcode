<?php
require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

$title = 'Sửa mẫu giao diện';
$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

// Lấy ID template
$template_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$template_id) {
    header('Location: templates.php');
    exit();
}

// Lấy thông tin template
try {
    $query = "SELECT * FROM templates WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $template_id);
    $stmt->execute();
    $template = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$template) {
        header('Location: templates.php');
        exit();
    }
} catch (PDOException $e) {
    $error = 'Bảng templates chưa được tạo. Vui lòng chạy file database/templates_table.sql';
    include 'includes/admin_header.php';
    echo '<div class="admin-card"><div class="admin-card-body"><div class="alert alert-danger">' . $error . '</div></div></div>';
    include 'includes/admin_footer.php';
    exit();
}

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

// Xử lý form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? 'other';
    $price = floatval($_POST['price']);
    $sale_price = !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null;
    $image = trim($_POST['image'] ?? '');
    $gallery = trim($_POST['gallery'] ?? '');
    $demo_url = trim($_POST['demo_url'] ?? '');
    $features = trim($_POST['features'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = $_POST['status'] ?? 'active';
    
    // Validation
    if (empty($name)) {
        $error = 'Vui lòng nhập tên mẫu giao diện!';
    } elseif (empty($price) || $price <= 0) {
        $error = 'Vui lòng nhập giá hợp lệ!';
    } else {
        // Tạo slug nếu chưa có
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        }
        
        // Kiểm tra slug đã tồn tại chưa (trừ template hiện tại)
        $check_query = "SELECT id FROM templates WHERE slug = :slug AND id != :id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':slug', $slug);
        $check_stmt->bindParam(':id', $template_id);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            $error = 'Slug đã tồn tại! Vui lòng chọn giá trị khác.';
        } else {
            // Cập nhật template
            $query = "UPDATE templates SET 
                      name = :name, 
                      slug = :slug, 
                      description = :description, 
                      category = :category, 
                      price = :price, 
                      sale_price = :sale_price, 
                      image = :image, 
                      gallery = :gallery, 
                      demo_url = :demo_url, 
                      features = :features, 
                      featured = :featured, 
                      status = :status
                      WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':sale_price', $sale_price);
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':gallery', $gallery);
            $stmt->bindParam(':demo_url', $demo_url);
            $stmt->bindParam(':features', $features);
            $stmt->bindParam(':featured', $featured);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $template_id);
            
            if ($stmt->execute()) {
                header('Location: templates.php?msg=updated');
                exit();
            } else {
                $error = 'Có lỗi xảy ra khi cập nhật mẫu giao diện!';
            }
        }
    }
}

include 'includes/admin_header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h5><i class="fas fa-edit"></i> Sửa mẫu giao diện: <?php echo htmlspecialchars($template['name']); ?></h5>
        <a href="templates.php" class="btn-admin btn-admin-sm" style="background: #e2e8f0; color: var(--admin-text);">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
    <div class="admin-card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="templateForm">
            <div class="row">
                <div class="col-md-8">
                    <!-- Thông tin cơ bản -->
                    <div class="admin-card mb-4">
                        <div class="admin-card-header">
                            <h5><i class="fas fa-info-circle"></i> Thông tin cơ bản</h5>
                        </div>
                        <div class="admin-card-body">
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Tên mẫu giao diện <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control-admin" required 
                                       value="<?php echo htmlspecialchars($template['name']); ?>"
                                       placeholder="Nhập tên mẫu giao diện">
                            </div>
                            
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Slug (URL)</label>
                                <input type="text" name="slug" class="form-control-admin" 
                                       value="<?php echo htmlspecialchars($template['slug']); ?>"
                                       placeholder="Tự động tạo từ tên">
                                <small class="text-muted">Để trống để tự động tạo từ tên</small>
                            </div>
                            
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Mô tả</label>
                                <textarea name="description" class="form-control-admin" rows="3" 
                                          placeholder="Mô tả về mẫu giao diện"><?php echo htmlspecialchars($template['description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Tính năng</label>
                                <textarea name="features" class="form-control-admin" rows="4" 
                                          placeholder="Liệt kê các tính năng, mỗi tính năng một dòng"><?php echo htmlspecialchars($template['features'] ?? ''); ?></textarea>
                                <small class="text-muted">Mỗi tính năng một dòng</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hình ảnh -->
                    <div class="admin-card mb-4">
                        <div class="admin-card-header">
                            <h5><i class="fas fa-image"></i> Hình ảnh</h5>
                        </div>
                        <div class="admin-card-body">
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Ảnh đại diện</label>
                                <div class="mb-3">
                                    <input type="file" id="imageUpload" class="form-control-admin" accept="image/*" style="padding: 8px;">
                                    <small class="text-muted">Chọn file ảnh (JPG, PNG, GIF, WebP - Tối đa 5MB)</small>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex gap-2 align-items-center">
                                        <input type="url" name="image" id="imageUrl" class="form-control-admin" 
                                               value="<?php echo htmlspecialchars($template['image'] ?? ''); ?>"
                                               placeholder="Hoặc nhập URL hình ảnh">
                                        <button type="button" class="btn-admin btn-admin-sm" style="background: #4299e1; color: white;" onclick="clearImage()">
                                            <i class="fas fa-times"></i> Xóa
                                        </button>
                                    </div>
                                </div>
                                <div id="imagePreview" class="mt-3" style="<?php echo $template['image'] ? '' : 'display: none;'; ?>">
                                    <?php 
                                    $preview_url = '';
                                    if ($template['image']) {
                                        $preview_url = $template['image'];
                                        if (!preg_match('/^https?:\/\//', $preview_url) && !preg_match('/^\//', $preview_url)) {
                                            $preview_url = SITE_URL . '/uploads/templates/' . $preview_url;
                                        }
                                    }
                                    ?>
                                    <img id="previewImg" src="<?php echo htmlspecialchars($preview_url); ?>" alt="Preview" 
                                         style="max-width: 300px; max-height: 300px; border-radius: 8px; border: 2px solid var(--admin-border); object-fit: contain;">
                                </div>
                            </div>
                            
                            <div class="form-group-admin">
                                <label class="form-label-admin">Thư viện ảnh</label>
                                <div class="mb-3">
                                    <input type="file" id="galleryUpload" class="form-control-admin" accept="image/*" multiple style="padding: 8px;">
                                    <small class="text-muted">Chọn nhiều file ảnh</small>
                                </div>
                                <textarea name="gallery" id="galleryUrls" class="form-control-admin" rows="3" 
                                          placeholder="URLs hình ảnh, cách nhau bởi dấu phẩy"><?php echo htmlspecialchars($template['gallery'] ?? ''); ?></textarea>
                                <div id="galleryPreview" class="mt-3 d-flex flex-wrap gap-2"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Demo URL -->
                    <div class="admin-card mb-4">
                        <div class="admin-card-header">
                            <h5><i class="fas fa-link"></i> Demo URL</h5>
                        </div>
                        <div class="admin-card-body">
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">URL Demo</label>
                                <input type="url" name="demo_url" class="form-control-admin" 
                                       value="<?php echo htmlspecialchars($template['demo_url'] ?? ''); ?>"
                                       placeholder="https://demo.example.com">
                                <small class="text-muted">Link xem demo mẫu giao diện</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <!-- Danh mục và giá -->
                    <div class="admin-card mb-4">
                        <div class="admin-card-header">
                            <h5><i class="fas fa-tags"></i> Danh mục & Giá</h5>
                        </div>
                        <div class="admin-card-body">
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Danh mục <span class="text-danger">*</span></label>
                                <select name="category" class="form-control-admin" required>
                                    <?php foreach($categories as $key => $label): ?>
                                        <option value="<?php echo $key; ?>" 
                                                <?php echo $template['category'] == $key ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Giá (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control-admin" required 
                                       min="0" step="1000"
                                       value="<?php echo $template['price']; ?>"
                                       placeholder="0">
                            </div>
                            
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Giá khuyến mãi (VNĐ)</label>
                                <input type="number" name="sale_price" class="form-control-admin" 
                                       min="0" step="1000"
                                       value="<?php echo $template['sale_price'] ?? ''; ?>"
                                       placeholder="0">
                                <small class="text-muted">Để trống nếu không có khuyến mãi</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tùy chọn -->
                    <div class="admin-card mb-4">
                        <div class="admin-card-header">
                            <h5><i class="fas fa-cog"></i> Tùy chọn</h5>
                        </div>
                        <div class="admin-card-body">
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Trạng thái</label>
                                <select name="status" class="form-control-admin">
                                    <option value="active" <?php echo $template['status'] == 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                                    <option value="inactive" <?php echo $template['status'] == 'inactive' ? 'selected' : ''; ?>>Không hoạt động</option>
                                </select>
                            </div>
                            
                            <div class="form-group-admin">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="featured" id="featured" value="1"
                                           <?php echo $template['featured'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="featured">
                                        <i class="fas fa-star"></i> Mẫu nổi bật
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn-admin btn-admin-primary">
                            <i class="fas fa-save"></i> Cập nhật mẫu giao diện
                        </button>
                        <a href="templates.php" class="btn-admin" style="background: #e2e8f0; color: var(--admin-text); text-align: center;">
                            <i class="fas fa-times"></i> Hủy
                        </a>
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
    if (!slugInput.value || slugInput.dataset.manual !== 'true') {
        const slug = this.value.toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        slugInput.value = slug;
    }
});

// Mark slug as manually edited
document.querySelector('input[name="slug"]').addEventListener('input', function() {
    this.dataset.manual = 'true';
});

// Clear image
function clearImage() {
    document.getElementById('imageUrl').value = '';
    document.getElementById('imageUpload').value = '';
    document.getElementById('imagePreview').style.display = 'none';
}

// Image URL preview
document.getElementById('imageUrl').addEventListener('input', function() {
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    if (this.value) {
        previewImg.src = this.value;
        preview.style.display = 'block';
    } else if (!document.getElementById('imageUpload').files.length) {
        preview.style.display = 'none';
    }
});
</script>

<?php include 'includes/admin_footer.php'; ?>

