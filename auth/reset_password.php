<?php
require_once '../config/config.php';
require_once '../config/database.php';

$title = 'Đặt lại mật khẩu';
$error = '';
$success = '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

if (empty($token)) {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

// Kiểm tra token hợp lệ
$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM password_resets WHERE token = :token AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
$stmt = $db->prepare($query);
$stmt->bindParam(':token', $token);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    $error = 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn!';
} else {
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($password)) {
            $error = 'Vui lòng nhập mật khẩu mới!';
        } elseif (strlen($password) < 6) {
            $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
        } elseif ($password !== $confirm_password) {
            $error = 'Mật khẩu xác nhận không khớp!';
        } else {
            // Cập nhật mật khẩu
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "UPDATE users SET password = :password WHERE email = :email";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':email', $reset['email']);
            
            if ($stmt->execute()) {
                // Xóa token đã sử dụng
                $query = "DELETE FROM password_resets WHERE token = :token";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':token', $token);
                $stmt->execute();
                
                $success = 'Đặt lại mật khẩu thành công!';
                header('refresh:2;url=' . SITE_URL . '/auth/login.php');
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại!';
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4">Đặt lại mật khẩu</h3>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Mật khẩu mới</label>
                                <input type="password" name="password" class="form-control" required minlength="6">
                                <small class="text-muted">Tối thiểu 6 ký tự</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Xác nhận mật khẩu</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-check"></i> Đặt lại mật khẩu
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <div class="text-center">
                        <a href="<?php echo SITE_URL; ?>/auth/login.php"><i class="fas fa-arrow-left"></i> Quay lại đăng nhập</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
