<?php
require_once '../config/config.php';
require_once '../config/database.php';

$title = 'Quên mật khẩu';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $error = 'Vui lòng nhập email!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ!';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        // Kiểm tra email có tồn tại không
        $query = "SELECT id FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Tạo token reset
            $token = bin2hex(random_bytes(32));
            
            // Lưu token vào database
            $query = "INSERT INTO password_resets (email, token) VALUES (:email, :token)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            
            // Gửi email (trong thực tế sử dụng PHPMailer hoặc SMTP)
            if (!function_exists('page_url')) {
                require_once __DIR__ . '/../config/url_helper.php';
            }
            $reset_link = page_url('auth/reset_password.php') . '?token=' . $token;
            
            // Demo: hiển thị link reset (trong thực tế gửi qua email)
            $success = "Link đặt lại mật khẩu: <a href='$reset_link'>$reset_link</a>";
        } else {
            $error = 'Email không tồn tại trong hệ thống!';
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
                    <h3 class="text-center mb-4">Quên mật khẩu</h3>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <p class="text-muted">Nhập email của bạn để nhận link đặt lại mật khẩu</p>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-paper-plane"></i> Gửi link đặt lại mật khẩu
                        </button>
                    </form>
                    
                    <div class="text-center">
                        <a href="<?php echo SITE_URL; ?>/auth/login.php"><i class="fas fa-arrow-left"></i> Quay lại đăng nhập</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
