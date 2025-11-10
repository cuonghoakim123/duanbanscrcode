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

// Lấy thông tin user
$query = "SELECT * FROM users WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    
    if (empty($fullname)) {
        $error = 'Vui lòng nhập họ tên!';
    } else {
        // Cập nhật thông tin cơ bản
        $query = "UPDATE users SET fullname = :fullname, phone = :phone, address = :address WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':id', $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $fullname;
            
            // Đổi mật khẩu nếu có
            if (!empty($current_password) && !empty($new_password)) {
                if (password_verify($current_password, $user['password'])) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $query = "UPDATE users SET password = :password WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':password', $hashed_password);
                    $stmt->bindParam(':id', $user_id);
                    $stmt->execute();
                    
                    $success = 'Cập nhật thông tin và đổi mật khẩu thành công!';
                } else {
                    $error = 'Mật khẩu hiện tại không đúng!';
                }
            } else {
                $success = 'Cập nhật thông tin thành công!';
            }
            
            // Refresh user data
            $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = 'Có lỗi xảy ra, vui lòng thử lại!';
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
                            $avatar_url = SITE_URL . '/uploads/users/' . basename($avatar_url);
                        }
                    } else {
                        $avatar_url = getAvatarPlaceholder(150);
                    }
                    ?>
                    <img src="<?php echo htmlspecialchars($avatar_url); ?>" 
                         class="rounded-circle mb-3" 
                         style="width: 150px; height: 150px; object-fit: cover;"
                         loading="lazy"
                         onerror="this.onerror=null; this.src='<?php echo getAvatarPlaceholder(150); ?>';">
                    <h5><?php echo htmlspecialchars($user['fullname']); ?></h5>
                    <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    <span class="badge <?php echo $user['role'] == 'admin' ? 'bg-danger' : 'bg-primary'; ?>">
                        <?php echo $user['role'] == 'admin' ? 'Quản trị viên' : 'Khách hàng'; ?>
                    </span>
                </div>
            </div>
            
            <div class="card shadow-sm mt-3">
                <div class="list-group list-group-flush">
                    <a href="profile.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-user"></i> Thông tin cá nhân
                    </a>
                    <a href="orders.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-shopping-bag"></i> Đơn hàng của tôi
                    </a>
                    <?php if($user['role'] == 'admin'): ?>
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
                                       value="<?php echo htmlspecialchars($user['fullname']); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" readonly 
                                       value="<?php echo htmlspecialchars($user['email']); ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['phone']); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ngày tham gia</label>
                                <input type="text" class="form-control" readonly 
                                       value="<?php echo date('d/m/Y', strtotime($user['created_at'])); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Địa chỉ</label>
                            <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="mb-3">Đổi mật khẩu (tùy chọn)</h6>
                        
                        <?php if($user['password']): ?>
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

<?php include 'includes/footer.php'; ?>
