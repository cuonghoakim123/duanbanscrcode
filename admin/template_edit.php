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
    
    // Xử lý gallery: normalize tất cả URLs và loại bỏ /admin/uploads/
    if (!empty($gallery)) {
        // Tách các URLs (có thể cách nhau bởi dấu phẩy)
        $gallery_urls = array_map('trim', explode(',', $gallery));
        $normalized_urls = [];
        
        foreach ($gallery_urls as $url) {
            if (empty($url)) continue;
            
            // Sửa đường dẫn sai nếu có /admin/uploads/templates/
            if (strpos($url, '/admin/uploads/templates/') !== false) {
                $url = str_replace('/admin/uploads/templates/', '/uploads/templates/', $url);
            }
            if (strpos($url, 'admin/uploads/templates/') !== false) {
                $url = str_replace('admin/uploads/templates/', 'uploads/templates/', $url);
            }
            
            // Nếu là URL đầy đủ từ SITE_URL, extract tên file hoặc giữ nguyên
            if (strpos($url, SITE_URL) === 0) {
                if (strpos($url, '/uploads/templates/') !== false) {
                    // Chỉ lưu tên file
                    $url = basename(str_replace(SITE_URL . '/uploads/templates/', '', $url));
                }
            }
            // Nếu là URL external, giữ nguyên
            elseif (preg_match('/^https?:\/\//', $url)) {
                // Giữ nguyên URL external
            }
            // Nếu là đường dẫn relative, extract tên file
            else {
                $url = basename($url);
            }
            
            if (!empty($url)) {
                $normalized_urls[] = $url;
            }
        }
        
        // Kết hợp lại thành chuỗi, cách nhau bởi dấu phẩy
        $gallery = implode(', ', $normalized_urls);
    }
    $demo_url = trim($_POST['demo_url'] ?? '');
    $features = trim($_POST['features'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = $_POST['status'] ?? 'active';
    
    // Xử lý image: chỉ lưu tên file vào database (không lưu URL đầy đủ)
    if (!empty($image)) {
        $original_image = $image;
        
        // Bước 1: Sửa đường dẫn sai nếu có /admin/uploads/templates/
        $image = str_replace('/admin/uploads/templates/', '/uploads/templates/', $image);
        $image = str_replace('admin/uploads/templates/', 'uploads/templates/', $image);
        
        // Bước 2: Loại bỏ SITE_URL nếu có
        if (strpos($image, SITE_URL) === 0) {
            $image = str_replace(SITE_URL, '', $image);
            $image = ltrim($image, '/');
        }
        
        // Bước 3: Loại bỏ /uploads/templates/ nếu có
        if (strpos($image, 'uploads/templates/') === 0) {
            $image = str_replace('uploads/templates/', '', $image);
        }
        if (strpos($image, '/uploads/templates/') === 0) {
            $image = str_replace('/uploads/templates/', '', $image);
        }
        
        // Bước 4: Nếu là URL external, extract tên file từ path
        if (preg_match('/^https?:\/\//', $image)) {
            $parsed = parse_url($image);
            $image = isset($parsed['path']) ? basename($parsed['path']) : basename($image);
        }
        
        // Bước 5: Đảm bảo chỉ lấy tên file (loại bỏ mọi đường dẫn còn lại)
        $image = basename($image);
        
        // Debug log
        if ($original_image !== $image) {
            error_log("Template Edit - Image normalized: '$original_image' -> '$image'");
        }
        
        // Đảm bảo không rỗng
        if (empty($image)) {
            error_log("Template Edit - ERROR: Image became empty! Original: '$original_image'");
            $image = basename($original_image); // Fallback
        }
    } else {
        // Nếu image rỗng, có thể giữ nguyên giá trị cũ trong database
        // Không làm gì, để database giữ nguyên giá trị cũ
    }
    
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
                // Log để debug
                error_log("Template updated successfully - ID: $template_id, Image: '$image'");
                
                // Verify: Kiểm tra lại giá trị đã được lưu
                $verify_query = "SELECT image FROM templates WHERE id = :id";
                $verify_stmt = $db->prepare($verify_query);
                $verify_stmt->bindParam(':id', $template_id);
                $verify_stmt->execute();
                $verified = $verify_stmt->fetch(PDO::FETCH_ASSOC);
                error_log("Template verify - ID: $template_id, Saved image: '" . ($verified['image'] ?? 'NULL') . "'");
                
                // Redirect với timestamp để tránh cache
                header('Location: templates.php?msg=updated&t=' . time());
                exit();
            } else {
                $error = 'Có lỗi xảy ra khi cập nhật mẫu giao diện!';
                error_log("Template update failed - ID: $template_id, Error: " . implode(', ', $stmt->errorInfo()));
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
                                        <input type="text" name="image" id="imageUrl" class="form-control-admin" 
                                               value="<?php echo htmlspecialchars($template['image'] ?? ''); ?>"
                                               placeholder="Tên file ảnh (sẽ tự động điền sau khi upload)">
                                        <button type="button" class="btn-admin btn-admin-sm" style="background: #4299e1; color: white;" onclick="clearImage()">
                                            <i class="fas fa-times"></i> Xóa
                                        </button>
                                    </div>
                                    <small class="text-muted">Sau khi chọn file ảnh, tên file sẽ tự động được điền vào đây</small>
                                </div>
                                <div id="imagePreview" class="mt-3" style="<?php echo $template['image'] ? '' : 'display: none;'; ?>">
                                    <?php 
                                    $preview_url = '';
                                    if ($template['image']) {
                                        $image_value = trim($template['image']);
                                        
                                        // Nếu là URL external (http/https), kiểm tra và sửa nếu cần
                                        if (preg_match('/^https?:\/\//', $image_value)) {
                                            // Kiểm tra xem có chứa /admin/uploads/ không (sai) và sửa lại
                                            if (strpos($image_value, '/admin/uploads/templates/') !== false) {
                                                $image_value = str_replace('/admin/uploads/templates/', '/uploads/templates/', $image_value);
                                            }
                                            $preview_url = $image_value;
                                        }
                                        // Nếu là URL đầy đủ từ SITE_URL, extract tên file
                                        elseif (strpos($image_value, SITE_URL) === 0) {
                                            // Kiểm tra và sửa nếu có /admin/uploads/
                                            if (strpos($image_value, '/admin/uploads/templates/') !== false) {
                                                $image_value = str_replace('/admin/uploads/templates/', '/uploads/templates/', $image_value);
                                            }
                                            // Extract tên file
                                            if (strpos($image_value, '/uploads/templates/') !== false) {
                                                $filename = basename(str_replace(SITE_URL . '/uploads/templates/', '', $image_value));
                                                $preview_url = 'http://localhost/duanbanscrcode/uploads/templates/' . $filename;
                                            } else {
                                                $preview_url = $image_value;
                                            }
                                        }
                                        // Nếu là đường dẫn relative bắt đầu bằng /uploads/templates/
                                        elseif (strpos($image_value, '/uploads/templates/') === 0) {
                                            $filename = basename($image_value);
                                            $preview_url = 'http://localhost/duanbanscrcode/uploads/templates/' . $filename;
                                        }
                                        // Nếu là đường dẫn có /admin/uploads/ (sai), sửa lại
                                        elseif (strpos($image_value, '/admin/uploads/templates/') !== false || strpos($image_value, 'admin/uploads/templates/') !== false) {
                                            // Extract tên file và sửa đường dẫn
                                            $filename = basename($image_value);
                                            $preview_url = 'http://localhost/duanbanscrcode/uploads/templates/' . $filename;
                                        }
                                        // Nếu chỉ là tên file hoặc đường dẫn tương đối
                                        else {
                                            // Lấy chỉ tên file (loại bỏ mọi đường dẫn)
                                            $filename = basename($image_value);
                                            // Xây dựng absolute URL
                                            $preview_url = 'http://localhost/duanbanscrcode/uploads/templates/' . $filename;
                                        }
                                        
                                        // All URLs should already be absolute at this point
                                        // No need for additional processing
                                    }
                                    ?>
                                    <img id="previewImg" src="<?php echo htmlspecialchars($preview_url); ?>" alt="Preview" 
                                         style="max-width: 300px; max-height: 300px; border-radius: 8px; border: 2px solid var(--admin-border); object-fit: contain;"
                                         onerror="handleImageError(this);"
                                         onload="handleImageLoad(this);">
                                    <div id="imageError" class="alert alert-warning" style="display: none; margin-top: 10px;">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        <span id="imageErrorText">Không thể tải ảnh. Vui lòng kiểm tra đường dẫn hoặc upload lại ảnh.</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group-admin">
                                <label class="form-label-admin">Thư viện ảnh</label>
                                <div class="mb-3">
                                    <input type="file" id="galleryUpload" class="form-control-admin" accept="image/*" multiple style="padding: 8px;">
                                    <small class="text-muted">Chọn nhiều file ảnh</small>
                                </div>
                                <textarea name="gallery" id="galleryUrls" class="form-control-admin" rows="3" 
                                          placeholder="URLs hình ảnh, cách nhau bởi dấu phẩy"><?php 
                                    // Normalize gallery URLs khi hiển thị
                                    if (!empty($template['gallery'])) {
                                        $gallery_urls = array_map('trim', explode(',', $template['gallery']));
                                        $normalized_gallery = [];
                                        foreach ($gallery_urls as $url) {
                                            if (empty($url)) continue;
                                            // Sửa đường dẫn sai nếu có /admin/uploads/
                                            if (strpos($url, '/admin/uploads/templates/') !== false) {
                                                $url = str_replace('/admin/uploads/templates/', '/uploads/templates/', $url);
                                            }
                                            if (strpos($url, 'admin/uploads/templates/') !== false) {
                                                $url = str_replace('admin/uploads/templates/', 'uploads/templates/', $url);
                                            }
                                            $normalized_gallery[] = $url;
                                        }
                                        echo htmlspecialchars(implode(', ', $normalized_gallery));
                                    }
                                ?></textarea>
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
// Image error handling functions
function handleImageError(img) {
    console.log('Image load error for:', img.src);
    const errorDiv = document.getElementById('imageError');
    const errorText = document.getElementById('imageErrorText');
    if (errorDiv && errorText) {
        errorText.textContent = 'Không thể tải ảnh: ' + img.src;
        errorDiv.style.display = 'block';
    }
    img.style.display = 'none';
}

function handleImageLoad(img) {
    console.log('Image loaded successfully:', img.src);
    const errorDiv = document.getElementById('imageError');
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
    img.style.display = 'block';
}

// Clear image function
function clearImage() {
    const imageUrl = document.getElementById('imageUrl');
    const imagePreview = document.getElementById('imagePreview');
    const imageUpload = document.getElementById('imageUpload');
    
    if (imageUrl) imageUrl.value = '';
    if (imagePreview) imagePreview.style.display = 'none';
    if (imageUpload) imageUpload.value = '';
}

// Helper function để normalize image URL - loại bỏ /admin/ và đảm bảo absolute URL
function normalizeImageUrl(url, siteUrl) {
    if (!url || !url.trim()) return '';
    
    let normalized = url.trim();
    
    // Sửa đường dẫn sai nếu có /admin/uploads/templates/
    if (normalized.indexOf('/admin/uploads/templates/') !== -1) {
        normalized = normalized.replace('/admin/uploads/templates/', '/uploads/templates/');
    }
    if (normalized.indexOf('admin/uploads/templates/') !== -1) {
        normalized = normalized.replace('admin/uploads/templates/', 'uploads/templates/');
    }
    
    // Nếu là URL external (http/https), trả về sau khi sửa
    if (normalized.match(/^https?:\/\//)) {
        return normalized;
    }
    
    // Nếu là URL đầy đủ từ SITE_URL, extract tên file
    if (normalized.indexOf(siteUrl) === 0) {
        if (normalized.indexOf('/uploads/templates/') !== -1) {
            const filename = normalized.split('/uploads/templates/')[1].split('/').pop();
            return siteUrl + '/uploads/templates/' + filename;
        }
        return normalized;
    }
    
    // Nếu là đường dẫn relative bắt đầu bằng /uploads/templates/
    if (normalized.indexOf('/uploads/templates/') === 0) {
        const filename = normalized.replace('/uploads/templates/', '').split('/').pop();
        return siteUrl + '/uploads/templates/' + filename;
    }
    
    // Nếu là đường dẫn absolute từ root (bắt đầu bằng /)
    if (normalized.indexOf('/') === 0) {
        return siteUrl + normalized;
    }
    
    // Chỉ là tên file, thêm prefix
    const filename = normalized.split('/').pop();
    return siteUrl + '/uploads/templates/' + filename;
}

// Image error handler
function handleImageError(img) {
    console.error('Image load error:', img.src);
    img.style.display = 'none';
    const errorDiv = document.getElementById('imageError');
    if (errorDiv) {
        errorDiv.style.display = 'block';
        const errorText = document.getElementById('imageErrorText');
        if (errorText) {
            errorText.textContent = 'Không thể tải ảnh: ' + img.src.split('/').pop() + '. Vui lòng kiểm tra đường dẫn hoặc upload lại ảnh.';
        }
    }
}

// Image load handler
function handleImageLoad(img) {
    console.log('Image loaded successfully:', img.src);
    img.style.display = 'block';
    const errorDiv = document.getElementById('imageError');
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto generate slug from name
    const nameInput = document.querySelector('input[name="name"]');
    if (nameInput) {
        nameInput.addEventListener('input', function() {
            const slugInput = document.querySelector('input[name="slug"]');
            if (slugInput && (!slugInput.value || slugInput.dataset.manual !== 'true')) {
                const slug = this.value.toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                slugInput.value = slug;
            }
        });
    }

    // Mark slug as manually edited
    const slugInput = document.querySelector('input[name="slug"]');
    if (slugInput) {
        slugInput.addEventListener('input', function() {
            this.dataset.manual = 'true';
        });
    }

    // Upload image function
    window.uploadTemplateImage = function(file, type) {
        if (!file) {
            alert('Vui lòng chọn file ảnh');
            return;
        }

        // Kiểm tra kích thước file (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('File quá lớn. Kích thước tối đa: 5MB');
            return;
        }

        // Kiểm tra loại file
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WebP)');
            return;
        }

        const formData = new FormData();
        formData.append('image', file);
        formData.append('upload_type', 'template');
        
        // Hiển thị loading
        const imageUpload = document.getElementById('imageUpload');
        const originalText = imageUpload ? imageUpload.nextElementSibling : null;
        if (originalText && originalText.tagName === 'SMALL') {
            originalText.textContent = 'Đang upload...';
            originalText.style.color = '#0d6efd';
        }
        
        const xhr = new XMLHttpRequest();
        
        xhr.addEventListener('load', function() {
            if (originalText) {
                originalText.textContent = 'Chọn file ảnh (JPG, PNG, GIF, WebP - Tối đa 5MB)';
                originalText.style.color = '';
            }

            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        if (type === 'image') {
                            // Lưu chỉ tên file vào database (không lưu URL đầy đủ)
                            const imageUrlInput = document.getElementById('imageUrl');
                            let filename = '';
                            
                            if (imageUrlInput) {
                                if (response.filename) {
                                    filename = response.filename;
                                    imageUrlInput.value = filename;
                                } else if (response.url) {
                                    // Nếu không có filename, extract từ URL
                                    const urlParts = response.url.split('/');
                                    filename = urlParts[urlParts.length - 1];
                                    imageUrlInput.value = filename;
                                }
                            }
                            
                            // Hiển thị preview với URL đầy đủ
                            const previewImg = document.getElementById('previewImg');
                            const imagePreview = document.getElementById('imagePreview');
                            
                            if (previewImg && response.url) {
                                const siteUrl = '<?php echo SITE_URL; ?>';
                                // Sử dụng helper function để normalize URL
                                const normalizedUrl = normalizeImageUrl(response.url, siteUrl);
                                previewImg.src = normalizedUrl;
                                
                                console.log('Setting uploaded image src to:', normalizedUrl);
                                
                                previewImg.onerror = function() {
                                    console.error('Failed to load uploaded image:', normalizedUrl);
                                    handleImageError(this);
                                };
                                
                                previewImg.onload = function() {
                                    console.log('Uploaded image loaded successfully:', normalizedUrl);
                                    handleImageLoad(this);
                                };
                            }
                            
                            if (imagePreview) {
                                imagePreview.style.display = 'block';
                            }
                            
                            // Trigger update preview với giá trị filename mới
                            if (imageUrlInput && filename) {
                                // Gọi updatePreview để đảm bảo URL được xây dựng đúng
                                setTimeout(function() {
                                    if (typeof window.updatePreview === 'function') {
                                        window.updatePreview(filename);
                                    } else {
                                        // Fallback: trigger input event
                                        imageUrlInput.dispatchEvent(new Event('input'));
                                    }
                                }, 100);
                            }
                            
                            console.log('Upload successful. Filename:', filename, 'URL:', response.url);
                        } else if (type === 'gallery') {
                            const galleryUrls = document.getElementById('galleryUrls');
                            const siteUrl = '<?php echo SITE_URL; ?>';
                            
                            if (galleryUrls && response.url) {
                                // Normalize URL trước khi lưu
                                const normalizedUrl = normalizeImageUrl(response.url, siteUrl);
                                const currentUrls = galleryUrls.value.trim();
                                // Chỉ lưu tên file hoặc URL đúng (không có /admin/)
                                const urlToSave = normalizedUrl.indexOf(siteUrl + '/uploads/templates/') === 0 
                                    ? normalizedUrl.replace(siteUrl + '/uploads/templates/', '') 
                                    : normalizedUrl;
                                const newUrl = currentUrls ? currentUrls + ', ' + urlToSave : urlToSave;
                                galleryUrls.value = newUrl;
                            }
                            
                            // Add to gallery preview
                            const galleryPreview = document.getElementById('galleryPreview');
                            if (galleryPreview && response.url) {
                                const siteUrl = '<?php echo SITE_URL; ?>';
                                const normalizedUrl = normalizeImageUrl(response.url, siteUrl);
                                
                                const imgDiv = document.createElement('div');
                                imgDiv.style.position = 'relative';
                                imgDiv.style.width = '100px';
                                imgDiv.style.height = '100px';
                                imgDiv.innerHTML = `
                                    <img src="${normalizedUrl}" alt="Gallery" 
                                         style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px; border: 1px solid var(--admin-border);"
                                         onerror="this.parentElement.remove();">
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            style="position: absolute; top: -5px; right: -5px; border-radius: 50%; width: 24px; height: 24px; padding: 0;"
                                            onclick="removeGalleryImage(this, '${normalizedUrl}')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                `;
                                galleryPreview.appendChild(imgDiv);
                            }
                        }
                    } else {
                        alert('Lỗi: ' + (response.message || 'Upload thất bại'));
                    }
                } catch (e) {
                    console.error('Parse error:', e);
                    alert('Có lỗi xảy ra khi xử lý phản hồi từ server');
                }
            } else {
                alert('Có lỗi xảy ra khi upload ảnh (Status: ' + xhr.status + ')');
            }
        });
        
        xhr.addEventListener('error', function() {
            if (originalText) {
                originalText.textContent = 'Chọn file ảnh (JPG, PNG, GIF, WebP - Tối đa 5MB)';
                originalText.style.color = '';
            }
            alert('Có lỗi xảy ra khi upload ảnh. Vui lòng kiểm tra kết nối mạng.');
        });
        
        xhr.open('POST', 'upload_image.php');
        xhr.send(formData);
    };

    // Remove gallery image
    window.removeGalleryImage = function(button, url) {
        const galleryUrls = document.getElementById('galleryUrls');
        if (galleryUrls) {
            const siteUrl = '<?php echo SITE_URL; ?>';
            // Normalize URL để so sánh
            const normalizedUrl = normalizeImageUrl(url, siteUrl);
            // Extract tên file từ URL để so sánh
            const filename = normalizedUrl.split('/').pop();
            
            // Tách các URLs và filter
            let urls = galleryUrls.value.split(',').map(u => u.trim());
            urls = urls.filter(u => {
                const uNormalized = normalizeImageUrl(u, siteUrl);
                const uFilename = uNormalized.split('/').pop();
                // So sánh cả URL đầy đủ và tên file
                return u !== url && u !== normalizedUrl && u !== filename && uNormalized !== normalizedUrl && uFilename !== filename;
            });
            galleryUrls.value = urls.join(', ');
        }
        if (button && button.parentElement) {
            button.parentElement.remove();
        }
    };

    // Clear image
    window.clearImage = function() {
        const imageUrl = document.getElementById('imageUrl');
        const imageUpload = document.getElementById('imageUpload');
        const imagePreview = document.getElementById('imagePreview');
        
        if (imageUrl) imageUrl.value = '';
        if (imageUpload) imageUpload.value = '';
        if (imagePreview) imagePreview.style.display = 'none';
    };

    // Image upload handlers
    const imageUpload = document.getElementById('imageUpload');
    if (imageUpload) {
        imageUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                uploadTemplateImage(file, 'image');
            }
        });
    }

    const galleryUpload = document.getElementById('galleryUpload');
    if (galleryUpload) {
        galleryUpload.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            files.forEach(file => {
                uploadTemplateImage(file, 'gallery');
            });
        });
    }

    // Image URL preview
    const imageUrlInput = document.getElementById('imageUrl');
    if (imageUrlInput) {
        // Function to update preview - expose to global scope
        window.updatePreview = function(value) {
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            if (!preview || !previewImg) return;
            
            if (value && value.trim()) {
                const siteUrl = '<?php echo SITE_URL; ?>';
                // Sử dụng helper function để normalize URL
                const normalizedUrl = normalizeImageUrl(value, siteUrl);
                previewImg.src = normalizedUrl;
                
                console.log('Setting preview image src to:', normalizedUrl);
                preview.style.display = 'block';
                
                // Thêm error và load handlers
                previewImg.onerror = function() {
                    handleImageError(this);
                };
                
                previewImg.onload = function() {
                    handleImageLoad(this);
                };
            } else {
                preview.style.display = 'none';
            }
        };
        
        // Update preview khi input thay đổi
        imageUrlInput.addEventListener('input', function() {
            if (typeof window.updatePreview === 'function') {
                window.updatePreview(this.value);
            }
        });
        
        // Update preview khi blur (khi người dùng rời khỏi input)
        imageUrlInput.addEventListener('blur', function() {
            if (typeof window.updatePreview === 'function') {
                window.updatePreview(this.value);
            }
        });
        
        // Update preview ban đầu nếu có giá trị
        if (imageUrlInput.value) {
            // Delay một chút để đảm bảo DOM đã sẵn sàng
            setTimeout(function() {
                if (typeof window.updatePreview === 'function') {
                    window.updatePreview(imageUrlInput.value);
                }
            }, 100);
        }
    }
    
    // Kiểm tra và hiển thị ảnh ban đầu nếu có
    const initialPreview = document.getElementById('previewImg');
    if (initialPreview && initialPreview.src) {
        console.log('Initial preview image src:', initialPreview.src);
        
        // Đảm bảo error handler được attach
        initialPreview.onerror = function() {
            console.error('Initial image load error:', this.src);
            handleImageError(this);
        };
        
        initialPreview.onload = function() {
            console.log('Initial image loaded successfully:', this.src);
            handleImageLoad(this);
        };
        
        // Kiểm tra xem ảnh có tồn tại không bằng cách thử load lại
        const testImg = new Image();
        testImg.onload = function() {
            console.log('Image exists and can be loaded:', initialPreview.src);
        };
        testImg.onerror = function() {
            console.error('Image does not exist or cannot be loaded:', initialPreview.src);
            // Không cần làm gì, error handler của previewImg sẽ xử lý
        };
        testImg.src = initialPreview.src;
    }
    
    // Load và hiển thị gallery preview ban đầu
    const galleryUrls = document.getElementById('galleryUrls');
    const galleryPreview = document.getElementById('galleryPreview');
    if (galleryUrls && galleryPreview && galleryUrls.value) {
        const siteUrl = '<?php echo SITE_URL; ?>';
        const urls = galleryUrls.value.split(',').map(u => u.trim()).filter(u => u);
        
        urls.forEach(function(url) {
            if (!url) return;
            
            // Normalize URL
            const normalizedUrl = normalizeImageUrl(url, siteUrl);
            
            // Tạo div cho mỗi ảnh
            const imgDiv = document.createElement('div');
            imgDiv.style.position = 'relative';
            imgDiv.style.width = '100px';
            imgDiv.style.height = '100px';
            imgDiv.innerHTML = `
                <img src="${normalizedUrl}" alt="Gallery" 
                     style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px; border: 1px solid var(--admin-border);"
                     onerror="this.parentElement.remove();">
                <button type="button" class="btn btn-sm btn-danger" 
                        style="position: absolute; top: -5px; right: -5px; border-radius: 50%; width: 24px; height: 24px; padding: 0;"
                        onclick="removeGalleryImage(this, '${normalizedUrl}')">
                    <i class="fas fa-times"></i>
                </button>
            `;
            galleryPreview.appendChild(imgDiv);
        });
    }
});
</script>

<?php include 'includes/admin_footer.php'; ?>

