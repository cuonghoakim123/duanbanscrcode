<?php
require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

$title = 'Thêm sản phẩm mới';
$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

// Lấy danh sách danh mục
$query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Xử lý form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_id = (int)$_POST['category_id'];
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $description = trim($_POST['description'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $price = floatval($_POST['price']);
    $sale_price = !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null;
    $quantity = (int)$_POST['quantity'];
    $sku = trim($_POST['sku']);
    $image = trim($_POST['image'] ?? '');
    $gallery = trim($_POST['gallery'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = $_POST['status'] ?? 'active';
    
    // Validation
    if (empty($name)) {
        $error = 'Vui lòng nhập tên sản phẩm!';
    } elseif (empty($category_id)) {
        $error = 'Vui lòng chọn danh mục!';
    } elseif (empty($price) || $price <= 0) {
        $error = 'Vui lòng nhập giá hợp lệ!';
    } elseif (empty($sku)) {
        $error = 'Vui lòng nhập SKU!';
    } else {
        // Tạo slug nếu chưa có
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        }
        
        // Kiểm tra slug và SKU đã tồn tại chưa
        $check_query = "SELECT id FROM products WHERE slug = :slug OR sku = :sku";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':slug', $slug);
        $check_stmt->bindParam(':sku', $sku);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            $error = 'Slug hoặc SKU đã tồn tại! Vui lòng chọn giá trị khác.';
        } else {
            // Thêm sản phẩm
            $query = "INSERT INTO products (category_id, name, slug, description, content, price, sale_price, quantity, sku, image, gallery, featured, status) 
                      VALUES (:category_id, :name, :slug, :description, :content, :price, :sale_price, :quantity, :sku, :image, :gallery, :featured, :status)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':sale_price', $sale_price);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':sku', $sku);
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':gallery', $gallery);
            $stmt->bindParam(':featured', $featured);
            $stmt->bindParam(':status', $status);
            
            if ($stmt->execute()) {
                header('Location: products.php?msg=added');
                exit();
            } else {
                $error = 'Có lỗi xảy ra khi thêm sản phẩm!';
            }
        }
    }
}

include 'includes/admin_header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h5><i class="fas fa-plus"></i> Thêm sản phẩm mới</h5>
        <a href="products.php" class="btn-admin btn-admin-sm" style="background: #e2e8f0; color: var(--admin-text);">
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
        
        <form method="POST" action="" id="productForm" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <!-- Thông tin cơ bản -->
                    <div class="admin-card mb-4">
                        <div class="admin-card-header">
                            <h5><i class="fas fa-info-circle"></i> Thông tin cơ bản</h5>
                        </div>
                        <div class="admin-card-body">
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Tên sản phẩm <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control-admin" required 
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                       placeholder="Nhập tên sản phẩm">
                            </div>
                            
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Slug (URL)</label>
                                <input type="text" name="slug" class="form-control-admin" 
                                       value="<?php echo isset($_POST['slug']) ? htmlspecialchars($_POST['slug']) : ''; ?>"
                                       placeholder="Tự động tạo từ tên">
                                <small class="text-muted">Để trống để tự động tạo từ tên sản phẩm</small>
                            </div>
                            
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Mô tả ngắn</label>
                                <textarea name="description" class="form-control-admin" rows="3" 
                                          placeholder="Mô tả ngắn về sản phẩm"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Nội dung chi tiết</label>
                                <textarea name="content" class="form-control-admin" rows="6" 
                                          placeholder="Nội dung mô tả chi tiết sản phẩm"><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
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
                                               value="<?php echo isset($_POST['image']) ? htmlspecialchars($_POST['image']) : ''; ?>"
                                               placeholder="Hoặc nhập URL hình ảnh">
                                        <button type="button" class="btn-admin btn-admin-sm" style="background: #4299e1; color: white;" onclick="clearImage()">
                                            <i class="fas fa-times"></i> Xóa
                                        </button>
                                    </div>
                                    <small class="text-muted">Có thể upload file hoặc nhập URL</small>
                                </div>
                                <div id="imagePreview" class="mt-3" style="display: none;">
                                    <div class="position-relative d-inline-block">
                                        <img id="previewImg" src="" alt="Preview" 
                                             style="max-width: 300px; max-height: 300px; border-radius: 8px; border: 2px solid var(--admin-border); object-fit: contain;">
                                        <div id="uploadProgress" class="mt-2" style="display: none;">
                                            <div class="progress" style="height: 20px;">
                                                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                                     role="progressbar" style="width: 0%">0%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group-admin">
                                <label class="form-label-admin">Thư viện ảnh</label>
                                <div class="mb-3">
                                    <input type="file" id="galleryUpload" class="form-control-admin" accept="image/*" multiple style="padding: 8px;">
                                    <small class="text-muted">Chọn nhiều file ảnh (JPG, PNG, GIF, WebP - Tối đa 5MB mỗi file)</small>
                                </div>
                                <textarea name="gallery" id="galleryUrls" class="form-control-admin" rows="3" 
                                          placeholder="URLs hình ảnh, cách nhau bởi dấu phẩy hoặc upload nhiều ảnh"><?php echo isset($_POST['gallery']) ? htmlspecialchars($_POST['gallery']) : ''; ?></textarea>
                                <div id="galleryPreview" class="mt-3 d-flex flex-wrap gap-2"></div>
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
                                <select name="category_id" class="form-control-admin" required>
                                    <option value="">-- Chọn danh mục --</option>
                                    <?php foreach($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Giá (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control-admin" required 
                                       min="0" step="1000"
                                       value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>"
                                       placeholder="0">
                            </div>
                            
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Giá khuyến mãi (VNĐ)</label>
                                <input type="number" name="sale_price" class="form-control-admin" 
                                       min="0" step="1000"
                                       value="<?php echo isset($_POST['sale_price']) ? htmlspecialchars($_POST['sale_price']) : ''; ?>"
                                       placeholder="0">
                                <small class="text-muted">Để trống nếu không có khuyến mãi</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kho và SKU -->
                    <div class="admin-card mb-4">
                        <div class="admin-card-header">
                            <h5><i class="fas fa-warehouse"></i> Kho & SKU</h5>
                        </div>
                        <div class="admin-card-body">
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">SKU <span class="text-danger">*</span></label>
                                <input type="text" name="sku" class="form-control-admin" required 
                                       value="<?php echo isset($_POST['sku']) ? htmlspecialchars($_POST['sku']) : ''; ?>"
                                       placeholder="Mã SKU duy nhất">
                            </div>
                            
                            <div class="form-group-admin mb-3">
                                <label class="form-label-admin">Số lượng <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" class="form-control-admin" required 
                                       min="0"
                                       value="<?php echo isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : '0'; ?>"
                                       placeholder="0">
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
                                    <option value="active" <?php echo (!isset($_POST['status']) || $_POST['status'] == 'active') ? 'selected' : ''; ?>>Hoạt động</option>
                                    <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : ''; ?>>Không hoạt động</option>
                                </select>
                            </div>
                            
                            <div class="form-group-admin">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="featured" id="featured" value="1"
                                           <?php echo (isset($_POST['featured']) && $_POST['featured']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="featured">
                                        <i class="fas fa-star"></i> Sản phẩm nổi bật
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn-admin btn-admin-primary">
                            <i class="fas fa-save"></i> Lưu sản phẩm
                        </button>
                        <a href="products.php" class="btn-admin" style="background: #e2e8f0; color: var(--admin-text); text-align: center;">
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

// Upload image
document.getElementById('imageUpload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        uploadImage(file, 'image');
    }
});

// Upload gallery images
document.getElementById('galleryUpload').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    files.forEach(file => {
        uploadImage(file, 'gallery');
    });
});

// Function to upload image
function uploadImage(file, type) {
    const formData = new FormData();
    formData.append('image', file);
    
    const progressDiv = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    
    if (type === 'image') {
        progressDiv.style.display = 'block';
        progressBar.style.width = '0%';
        progressBar.textContent = '0%';
    }
    
    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable && type === 'image') {
            const percentComplete = (e.loaded / e.total) * 100;
            progressBar.style.width = percentComplete + '%';
            progressBar.textContent = Math.round(percentComplete) + '%';
        }
    });
    
    xhr.addEventListener('load', function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                if (type === 'image') {
                    document.getElementById('imageUrl').value = response.url;
                    document.getElementById('previewImg').src = response.url;
                    document.getElementById('imagePreview').style.display = 'block';
                    progressDiv.style.display = 'none';
                } else if (type === 'gallery') {
                    const galleryUrls = document.getElementById('galleryUrls');
                    const currentUrls = galleryUrls.value.trim();
                    const newUrl = currentUrls ? currentUrls + ', ' + response.url : response.url;
                    galleryUrls.value = newUrl;
                    
                    // Add to gallery preview
                    const galleryPreview = document.getElementById('galleryPreview');
                    const imgDiv = document.createElement('div');
                    imgDiv.style.position = 'relative';
                    imgDiv.style.width = '100px';
                    imgDiv.style.height = '100px';
                    imgDiv.innerHTML = `
                        <img src="${response.url}" alt="Gallery" 
                             style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px; border: 1px solid var(--admin-border);">
                        <button type="button" class="btn btn-sm btn-danger" 
                                style="position: absolute; top: -5px; right: -5px; border-radius: 50%; width: 24px; height: 24px; padding: 0;"
                                onclick="removeGalleryImage(this, '${response.url}')">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    galleryPreview.appendChild(imgDiv);
                }
            } else {
                alert('Lỗi: ' + response.message);
                if (type === 'image') {
                    progressDiv.style.display = 'none';
                }
            }
        } else {
            alert('Có lỗi xảy ra khi upload ảnh');
            if (type === 'image') {
                progressDiv.style.display = 'none';
            }
        }
    });
    
    xhr.addEventListener('error', function() {
        alert('Có lỗi xảy ra khi upload ảnh');
        if (type === 'image') {
            progressDiv.style.display = 'none';
        }
    });
    
    xhr.open('POST', 'upload_image.php');
    xhr.send(formData);
}

// Remove gallery image
function removeGalleryImage(button, url) {
    const galleryUrls = document.getElementById('galleryUrls');
    let urls = galleryUrls.value.split(',').map(u => u.trim()).filter(u => u !== url);
    galleryUrls.value = urls.join(', ');
    button.parentElement.remove();
}

// Clear image
function clearImage() {
    document.getElementById('imageUrl').value = '';
    document.getElementById('imageUpload').value = '';
    document.getElementById('imagePreview').style.display = 'none';
    document.getElementById('uploadProgress').style.display = 'none';
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

// Gallery URL preview
document.getElementById('galleryUrls').addEventListener('input', function() {
    const galleryPreview = document.getElementById('galleryPreview');
    const urls = this.value.split(',').map(u => u.trim()).filter(u => u);
    galleryPreview.innerHTML = '';
    
    urls.forEach(url => {
        const imgDiv = document.createElement('div');
        imgDiv.style.position = 'relative';
        imgDiv.style.width = '100px';
        imgDiv.style.height = '100px';
        imgDiv.innerHTML = `
            <img src="${url}" alt="Gallery" 
                 style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px; border: 1px solid var(--admin-border);"
                 onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2U5ZWNlZiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTIiIGZpbGw9IiM2OTc1N2QiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5FcnJvcjwvdGV4dD48L3N2Zz4='">
            <button type="button" class="btn btn-sm btn-danger" 
                    style="position: absolute; top: -5px; right: -5px; border-radius: 50%; width: 24px; height: 24px; padding: 0;"
                    onclick="removeGalleryImage(this, '${url}')">
                <i class="fas fa-times"></i>
            </button>
        `;
        galleryPreview.appendChild(imgDiv);
    });
});
</script>

<?php include 'includes/admin_footer.php'; ?>

