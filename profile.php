<?php
require_once 'config/config.php';
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

$title = 'Thông tin cá nhân';
$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

$error = '';
$success = '';

// Kiểm tra thông báo thành công từ session (sau khi redirect)
if (isset($_SESSION['profile_update_success'])) {
    $success = $_SESSION['profile_update_success'];
    unset($_SESSION['profile_update_success']);
}

// Lấy thông tin user - đảm bảo lấy đầy đủ thông tin
$query = "SELECT id, fullname, email, phone, address, role, password, created_at, avatar FROM users WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Kiểm tra nếu không tìm thấy user
if (!$user) {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

// Đảm bảo các giá trị mặc định nếu null
$user['fullname'] = $user['fullname'] ?? '';
$user['email'] = $user['email'] ?? '';
$user['phone'] = $user['phone'] ?? '';
$user['address'] = $user['address'] ?? '';
$user['role'] = $user['role'] ?? 'user';
$user['created_at'] = $user['created_at'] ?? date('Y-m-d H:i:s');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    
    // Validation
    if (empty($fullname)) {
        $error = 'Vui lòng nhập họ tên!';
    } elseif (strlen($fullname) < 2) {
        $error = 'Họ tên phải có ít nhất 2 ký tự!';
    } else {
        try {
            $db->beginTransaction();
            
            // Cập nhật thông tin cơ bản
            $query = "UPDATE users SET fullname = :fullname, phone = :phone, address = :address WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':fullname', $fullname);
            // Cho phép phone và address có thể là chuỗi rỗng hoặc null
            $phone_value = $phone ?: null;
            $address_value = $address ?: null;
            $stmt->bindParam(':phone', $phone_value, PDO::PARAM_STR);
            $stmt->bindParam(':address', $address_value, PDO::PARAM_STR);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                $error_info = $stmt->errorInfo();
                throw new Exception('Không thể cập nhật thông tin! ' . ($error_info[2] ?? ''));
            }
            
            // Kiểm tra số dòng bị ảnh hưởng
            if ($stmt->rowCount() === 0) {
                throw new Exception('Không có thông tin nào được cập nhật! Có thể dữ liệu không thay đổi.');
            }
            
            // Cập nhật session
            $_SESSION['user_name'] = $fullname;
            
            // Đổi mật khẩu nếu có
            if (!empty($current_password) && !empty($new_password)) {
                // Lấy lại password từ database để verify
                $query = "SELECT password FROM users WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $user_id);
                $stmt->execute();
                $user_password = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user_password && !empty($user_password['password']) && password_verify($current_password, $user_password['password'])) {
                    if (strlen($new_password) < 6) {
                        throw new Exception('Mật khẩu mới phải có ít nhất 6 ký tự!');
                    }
                    
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $query = "UPDATE users SET password = :password WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':password', $hashed_password);
                    $stmt->bindParam(':id', $user_id);
                    
                    if (!$stmt->execute()) {
                        throw new Exception('Không thể đổi mật khẩu!');
                    }
                    
                    $success = 'Cập nhật thông tin và đổi mật khẩu thành công!';
                } else {
                    throw new Exception('Mật khẩu hiện tại không đúng!');
                }
            } else {
                $success = 'Cập nhật thông tin thành công!';
            }
            
            $db->commit();
            
            // Redirect để refresh dữ liệu và tránh resubmit form
            $_SESSION['profile_update_success'] = $success;
            header('Location: ' . SITE_URL . '/profile.php');
            exit();
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-user-circle"></i> Thông tin cá nhân</h2>
    
    <div class="row">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <?php 
                    // Xử lý đường dẫn avatar - hỗ trợ nhiều định dạng
                    $avatar_url = '';
                    if (!empty($user['avatar'])) {
                        $avatar_url = trim($user['avatar']);
                        
                        // Nếu là URL đầy đủ (http/https), sử dụng trực tiếp
                        if (preg_match('/^https?:\/\//', $avatar_url)) {
                            // URL đầy đủ, giữ nguyên
                        }
                        // Nếu bắt đầu bằng /uploads/, thêm SITE_URL
                        elseif (preg_match('/^\/uploads\//', $avatar_url)) {
                            $avatar_url = SITE_URL . $avatar_url;
                        }
                        // Nếu bắt đầu bằng /, thêm SITE_URL
                        elseif (preg_match('/^\//', $avatar_url)) {
                            $avatar_url = SITE_URL . $avatar_url;
                        }
                        // Nếu chứa uploads/ nhưng không bắt đầu bằng /, thêm SITE_URL và /
                        elseif (strpos($avatar_url, 'uploads/') !== false) {
                            if (strpos($avatar_url, SITE_URL) === false) {
                                $avatar_url = SITE_URL . '/' . ltrim($avatar_url, '/');
                            }
                        }
                        // Nếu chỉ là tên file hoặc đường dẫn tương đối, thêm đường dẫn đầy đủ
                        else {
                            // Kiểm tra xem file có tồn tại trong uploads/users không
                            $possible_path = __DIR__ . '/uploads/users/' . basename($avatar_url);
                            if (file_exists($possible_path)) {
                                $avatar_url = SITE_URL . '/uploads/users/' . basename($avatar_url);
                            } else {
                                // Nếu không tìm thấy, dùng placeholder
                                $avatar_url = getAvatarPlaceholder(150);
                            }
                        }
                    } else {
                        $avatar_url = getAvatarPlaceholder(150);
                    }
                    ?>
                    <img src="<?php echo htmlspecialchars($avatar_url); ?>" 
                         id="avatarPreview"
                         class="rounded-circle mb-3" 
                         style="width: 150px; height: 150px; object-fit: cover; cursor: pointer; border: 3px solid #dee2e6;"
                         loading="lazy"
                         onerror="this.onerror=null; this.src='<?php echo getAvatarPlaceholder(150); ?>';"
                         onclick="document.getElementById('avatarModal').style.display='block'">
                    <h5><?php echo htmlspecialchars($user['fullname'] ?? 'User'); ?></h5>
                    <p class="text-muted"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                    <span class="badge <?php echo (isset($user['role']) && $user['role'] == 'admin') ? 'bg-danger' : 'bg-primary'; ?>">
                        <?php echo (isset($user['role']) && $user['role'] == 'admin') ? 'Quản trị viên' : 'Khách hàng'; ?>
                    </span>
                    <div class="mt-3">
                        <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('avatarModal').style.display='block'">
                            <i class="fas fa-image"></i> Đổi avatar
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm mt-3">
                <div class="list-group list-group-flush">
                    <a href="profile.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-user"></i> Thông tin cá nhân
                    </a>
                    <a href="javascript:void(0)" class="list-group-item list-group-item-action" onclick="initAdminChat()">
                        <i class="fas fa-comments"></i> Trò chuyện
                    </a>
                    <?php if(isset($user['role']) && $user['role'] == 'admin'): ?>
                        <a href="admin/" class="list-group-item list-group-item-action">
                            <i class="fas fa-tachometer-alt"></i> Quản trị
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Cập nhật thông tin</h5>
                </div>
                <div class="card-body">
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <h6 class="mb-3">Thông tin cơ bản</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Họ và tên</label>
                                <input type="text" name="fullname" class="form-control" required 
                                       value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" readonly 
                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ngày tham gia</label>
                                <input type="text" class="form-control" readonly 
                                       value="<?php echo isset($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Địa chỉ</label>
                            <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="mb-3">Đổi mật khẩu (tùy chọn)</h6>
                        
                        <?php if(isset($user['password']) && !empty($user['password'])): ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mật khẩu hiện tại</label>
                                    <input type="password" name="current_password" class="form-control">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mật khẩu mới</label>
                                    <input type="password" name="new_password" class="form-control" minlength="6">
                                    <small class="text-muted">Để trống nếu không muốn đổi mật khẩu</small>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Bạn đang đăng nhập bằng Google, không thể đổi mật khẩu.
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cập nhật thông tin
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal chọn/upload avatar -->
<div id="avatarModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-lg" style="margin: 50px auto; background: white; border-radius: 10px; max-width: 800px;">
        <div class="modal-content" style="border: none;">
            <div class="modal-header" style="border-bottom: 1px solid #dee2e6;">
                <h5 class="modal-title">Chọn hoặc Upload Avatar</h5>
                <button type="button" class="btn-close" onclick="document.getElementById('avatarModal').style.display='none'"></button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <ul class="nav nav-tabs mb-3" id="avatarTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button" role="tab">
                            <i class="fas fa-upload"></i> Upload ảnh mới
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="select-tab" data-bs-toggle="tab" data-bs-target="#select" type="button" role="tab">
                            <i class="fas fa-folder-open"></i> Chọn từ thư mục
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="avatarTabContent">
                    <!-- Tab Upload -->
                    <div class="tab-pane fade show active" id="upload" role="tabpanel">
                        <div class="mb-3">
                            <label class="form-label">Chọn file ảnh (JPG, PNG, GIF, WebP - tối đa 2MB)</label>
                            <input type="file" id="avatarUpload" class="form-control" accept="image/*">
                            <small class="text-muted">Kích thước khuyến nghị: 150x150px hoặc lớn hơn</small>
                        </div>
                        <div id="uploadPreview" class="text-center mb-3" style="display: none;">
                            <img id="uploadPreviewImg" src="" style="max-width: 200px; max-height: 200px; border-radius: 50%; border: 2px solid #dee2e6;">
                        </div>
                        <button type="button" class="btn btn-primary" id="btnUploadAvatar" onclick="uploadAvatar()">
                            <i class="fas fa-upload"></i> Upload Avatar
                        </button>
                    </div>
                    
                    <!-- Tab Chọn từ thư mục -->
                    <div class="tab-pane fade" id="select" role="tabpanel">
                        <div class="mb-3">
                            <label class="form-label">Chọn ảnh từ thư mục assets/images</label>
                            <div id="imageGallery" class="row g-2" style="max-height: 400px; overflow-y: auto;">
                                <?php
                                // Lấy danh sách ảnh từ thư mục assets/images
                                $images_dir = __DIR__ . '/assets/images/';
                                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                                
                                if (is_dir($images_dir)) {
                                    $files = scandir($images_dir);
                                    foreach ($files as $file) {
                                        if ($file != '.' && $file != '..') {
                                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                            if (in_array($ext, $allowed_extensions)) {
                                                $image_path = '/assets/images/' . $file;
                                                $image_url = SITE_URL . $image_path;
                                                echo '<div class="col-3 col-md-2">';
                                                echo '<div class="image-select-item" onclick="selectImage(\'' . htmlspecialchars($image_path) . '\', \'' . htmlspecialchars($image_url) . '\')" style="cursor: pointer; border: 2px solid #dee2e6; border-radius: 8px; padding: 5px; transition: all 0.3s;">';
                                                echo '<img src="' . htmlspecialchars($image_url) . '" class="img-fluid rounded" style="width: 100%; height: 80px; object-fit: cover;">';
                                                echo '</div>';
                                                echo '</div>';
                                            }
                                        }
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <div id="selectPreview" class="text-center mb-3" style="display: none;">
                            <img id="selectPreviewImg" src="" style="max-width: 200px; max-height: 200px; border-radius: 50%; border: 2px solid #dee2e6;">
                            <p class="mt-2"><strong id="selectPreviewPath"></strong></p>
                        </div>
                        <button type="button" class="btn btn-primary" id="btnSelectAvatar" onclick="selectAvatarFromFolder()" style="display: none;">
                            <i class="fas fa-check"></i> Chọn ảnh này
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let selectedImagePath = '';
let selectedImageUrl = '';

// Preview khi chọn file upload
document.getElementById('avatarUpload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('uploadPreviewImg').src = e.target.result;
            document.getElementById('uploadPreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// Chọn ảnh từ thư mục
function selectImage(path, url) {
    selectedImagePath = path;
    selectedImageUrl = url;
    
    // Highlight selected image
    document.querySelectorAll('.image-select-item').forEach(item => {
        item.style.borderColor = '#dee2e6';
    });
    event.currentTarget.style.borderColor = '#0d6efd';
    event.currentTarget.style.boxShadow = '0 0 0 3px rgba(13, 110, 253, 0.25)';
    
    // Show preview
    document.getElementById('selectPreviewImg').src = url;
    document.getElementById('selectPreviewPath').textContent = path;
    document.getElementById('selectPreview').style.display = 'block';
    document.getElementById('btnSelectAvatar').style.display = 'block';
}

// Upload avatar
function uploadAvatar() {
    const fileInput = document.getElementById('avatarUpload');
    const file = fileInput.files[0];
    
    if (!file) {
        alert('Vui lòng chọn file ảnh!');
        return;
    }
    
    const formData = new FormData();
    formData.append('avatar', file);
    
    const btn = document.getElementById('btnUploadAvatar');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang upload...';
    
    fetch('upload_avatar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cập nhật preview
            document.getElementById('avatarPreview').src = data.url;
            // Đóng modal
            document.getElementById('avatarModal').style.display = 'none';
            // Reload trang sau 1 giây
            setTimeout(() => {
                window.location.reload();
            }, 1000);
            alert('Cập nhật avatar thành công!');
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi upload avatar!');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-upload"></i> Upload Avatar';
    });
}

// Chọn avatar từ thư mục
function selectAvatarFromFolder() {
    if (!selectedImagePath) {
        alert('Vui lòng chọn một ảnh!');
        return;
    }
    
    const formData = new FormData();
    formData.append('select_image', '1');
    formData.append('image_path', selectedImagePath);
    
    const btn = document.getElementById('btnSelectAvatar');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang cập nhật...';
    
    fetch('upload_avatar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cập nhật preview
            document.getElementById('avatarPreview').src = data.url;
            // Đóng modal
            document.getElementById('avatarModal').style.display = 'none';
            // Reload trang sau 1 giây
            setTimeout(() => {
                window.location.reload();
            }, 1000);
            alert('Cập nhật avatar thành công!');
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi cập nhật avatar!');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Chọn ảnh này';
    });
}

// Đóng modal khi click bên ngoài
window.onclick = function(event) {
    const modal = document.getElementById('avatarModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<style>
.image-select-item:hover {
    transform: scale(1.05);
    border-color: #0d6efd !important;
}
</style>

<?php include 'includes/footer.php'; ?>
