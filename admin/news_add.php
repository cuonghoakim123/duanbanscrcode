<?php
require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

$title = 'Thêm tin tức mới';
$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

// Categories
$categories = [
    'Thiết kế' => 'Thiết kế',
    'SEO' => 'SEO',
    'Marketing' => 'Marketing',
    'Công nghệ' => 'Công nghệ',
    'Bảo mật' => 'Bảo mật',
    'AI' => 'AI',
    'Kinh doanh' => 'Kinh doanh',
    'Tối ưu' => 'Tối ưu',
    'Khác' => 'Khác'
];

// Xử lý form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = $_POST['category'] ?? 'Khác';
    $image = trim($_POST['image'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = $_POST['status'] ?? 'draft';
    $author_id = $_SESSION['user_id'];
    $published_at = $status == 'active' ? date('Y-m-d H:i:s') : null;
    
    // Validation
    if (empty($title)) {
        $error = 'Vui lòng nhập tiêu đề!';
    } else {
        // Tạo slug nếu chưa có
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        }
        
        // Kiểm tra slug đã tồn tại chưa
        try {
            $check_query = "SELECT id FROM news WHERE slug = :slug";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->bindParam(':slug', $slug);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                $error = 'Slug đã tồn tại! Vui lòng chọn giá trị khác.';
            } else {
                // Thêm news
                $query = "INSERT INTO news (title, slug, excerpt, content, category, image, featured, status, author_id, published_at) 
                          VALUES (:title, :slug, :excerpt, :content, :category, :image, :featured, :status, :author_id, :published_at)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':slug', $slug);
                $stmt->bindParam(':excerpt', $excerpt);
                $stmt->bindParam(':content', $content);
                $stmt->bindParam(':category', $category);
                $stmt->bindParam(':image', $image);
                $stmt->bindParam(':featured', $featured);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':author_id', $author_id);
                $stmt->bindParam(':published_at', $published_at);
                
                if ($stmt->execute()) {
                    header('Location: news.php?msg=added');
                    exit();
                } else {
                    $error = 'Có lỗi xảy ra khi thêm tin tức!';
                }
            }
        } catch (PDOException $e) {
            $error = 'Bảng news chưa được tạo. Vui lòng chạy file database/news_table.sql. Error: ' . $e->getMessage();
        }
    }
}

include 'includes/admin_header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h5><i class="fas fa-plus"></i> Thêm tin tức mới</h5>
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
                                <label class="form-label-admin">Tiêu đề <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control-admin" 
                                       value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                                       required placeholder="Nhập tiêu đề tin tức">
                            </div>
                            
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Slug</label>
                                <input type="text" name="slug" class="form-control-admin" 
                                       value="<?php echo isset($_POST['slug']) ? htmlspecialchars($_POST['slug']) : ''; ?>"
                                       placeholder="tieu-de-tin-tuc (tự động tạo nếu để trống)">
                                <small class="text-muted">URL thân thiện SEO</small>
                            </div>
                            
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Mô tả ngắn</label>
                                <textarea name="excerpt" class="form-control-admin" rows="3" 
                                          placeholder="Mô tả ngắn về tin tức"><?php echo isset($_POST['excerpt']) ? htmlspecialchars($_POST['excerpt']) : ''; ?></textarea>
                            </div>
                            
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Nội dung</label>
                                <textarea name="content" class="form-control-admin" rows="10" 
                                          placeholder="Nội dung chi tiết của tin tức"><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                                <small class="text-muted">Có thể sử dụng HTML để format nội dung</small>
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
                    <!-- Danh mục và trạng thái -->
                    <div class="admin-card mb-4">
                        <div class="admin-card-header">
                            <h5><i class="fas fa-cog"></i> Cài đặt</h5>
                        </div>
                        <div class="admin-card-body">
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Danh mục <span class="text-danger">*</span></label>
                                <select name="category" class="form-control-admin" required>
                                    <?php foreach($categories as $key => $label): ?>
                                        <option value="<?php echo $key; ?>" 
                                                <?php echo (isset($_POST['category']) && $_POST['category'] == $key) ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Trạng thái</label>
                                <select name="status" class="form-control-admin">
                                    <option value="draft" <?php echo (isset($_POST['status']) && $_POST['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                    <option value="active" <?php echo (isset($_POST['status']) && $_POST['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            
                            <div class="form-group-admin mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="featured" id="featured" 
                                           <?php echo (isset($_POST['featured']) && $_POST['featured']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="featured">
                                        Tin nổi bật
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="admin-card">
                        <div class="admin-card-body">
                            <button type="submit" class="btn-admin btn-admin-primary w-100 mb-2">
                                <i class="fas fa-save"></i> Lưu tin tức
                            </button>
                            <a href="news.php" class="btn-admin btn-admin-secondary w-100">
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
// Auto generate slug from title
document.querySelector('input[name="title"]').addEventListener('input', function() {
    const slugInput = document.querySelector('input[name="slug"]');
    if (!slugInput.dataset.manual) {
        const title = this.value;
        const slug = title.toLowerCase()
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
function uploadNewsImage(file) {
    const formData = new FormData();
    formData.append('image', file);
    formData.append('upload_type', 'news');
    
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
        uploadNewsImage(file);
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
            document.getElementById('previewImg').src = '<?php echo SITE_URL; ?>/uploads/news/' + url;
        }
        document.getElementById('imagePreview').style.display = 'block';
    }
});
</script>

<?php include 'includes/admin_footer.php'; ?>

