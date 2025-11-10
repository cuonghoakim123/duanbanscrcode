<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Nếu đã đăng nhập, redirect về trang chủ
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
        header('Location: ' . SITE_URL . '/admin');
    } else {
        header('Location: ' . SITE_URL);
    }
    exit();
}

$title = 'Đăng nhập';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        // Kiểm tra email có tồn tại không (không cần status để debug)
        $query = "SELECT * FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Kiểm tra status
            if ($user['status'] != 'active') {
                $error = 'Tài khoản của bạn đã bị khóa! Vui lòng liên hệ quản trị viên.';
            } 
            // Kiểm tra password có tồn tại không
            else if (empty($user['password'])) {
                $error = 'Tài khoản chưa có mật khẩu! Vui lòng sử dụng "Quên mật khẩu" để đặt mật khẩu mới.';
            }
            // Kiểm tra mật khẩu
            else if (password_verify($password, $user['password'])) {
                // Đăng nhập thành công
                // Regenerate session ID để bảo mật
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['fullname'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_avatar'] = $user['avatar'] ?? '';
                $_SESSION['login_method'] = 'email';
                
                // Redirect (PHP sẽ tự động lưu session khi script kết thúc)
                if ($user['role'] == 'admin') {
                    header('Location: ' . SITE_URL . '/admin');
                } else {
                    header('Location: ' . SITE_URL);
                }
                exit();
            } else {
                $error = 'Mật khẩu không chính xác!';
            }
        } else {
            $error = 'Email không tồn tại trong hệ thống!';
        }
    }
}

// Không include header để có full screen design
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title . ' - ' . SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/auth.css">
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-card-header">
            <div class="auth-icon">
                <i class="fas fa-sign-in-alt"></i>
            </div>
            <h2>Chào mừng trở lại!</h2>
            <p>Đăng nhập để tiếp tục</p>
        </div>
        <div class="auth-card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>Đăng ký thành công! Vui lòng đăng nhập.
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="loginForm">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-envelope me-2"></i>Email
                    </label>
                    <div class="input-group">
                        <i class="fas fa-envelope input-group-icon"></i>
                        <input type="email" name="email" class="form-control" 
                               placeholder="Nhập email của bạn" 
                               required 
                               value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock me-2"></i>Mật khẩu
                    </label>
                    <div class="input-group">
                        <i class="fas fa-lock input-group-icon"></i>
                        <input type="password" name="password" id="password" class="form-control" 
                               placeholder="Nhập mật khẩu" 
                               required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye" id="passwordToggle"></i>
                        </button>
                    </div>
                </div>
                
                <div class="forgot-password">
                    <a href="forgot_password.php" class="auth-link">
                        <i class="fas fa-key me-1"></i>Quên mật khẩu?
                    </a>
                </div>
                
                <button type="submit" class="btn-auth" id="submitBtn">
                    <span>
                        <i class="fas fa-sign-in-alt"></i> Đăng nhập
                    </span>
                </button>
            </form>
            
            <div class="auth-divider">hoặc</div>
            
            <button class="btn-google" id="btnGoogleLogin">
                <i class="fab fa-google"></i> Đăng nhập bằng Google
            </button>
            
            <div class="auth-footer">
                Chưa có tài khoản? 
                <a href="register.php" class="auth-link">Đăng ký ngay</a>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const toggle = document.getElementById(inputId + 'Toggle');
    
    if (input.type === 'password') {
        input.type = 'text';
        toggle.classList.remove('fa-eye');
        toggle.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        toggle.classList.remove('fa-eye-slash');
        toggle.classList.add('fa-eye');
    }
}

// Form loading state
document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.classList.add('loading');
    btn.innerHTML = '<span></span>';
});
</script>

<!-- Firebase SDK -->
<script type="module">
    import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.7.0/firebase-app.js';
    import { getAuth, GoogleAuthProvider, signInWithPopup } from 'https://www.gstatic.com/firebasejs/10.7.0/firebase-auth.js';
    
    // Kiểm tra Firebase config
    const apiKey = "<?php echo FIREBASE_API_KEY; ?>";
    if (!apiKey || apiKey === 'AIzaSyBxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx') {
        console.error('Firebase chưa được cấu hình! Vui lòng xem file FIREBASE_SETUP.md');
        document.getElementById('btnGoogleLogin').addEventListener('click', (e) => {
            e.preventDefault();
            alert('Firebase chưa được cấu hình!\n\nVui lòng:\n1. Tạo project tại https://console.firebase.google.com\n2. Cập nhật config/config.php với Firebase credentials\n3. Xem hướng dẫn chi tiết trong file FIREBASE_SETUP.md');
        });
    } else {
        const firebaseConfig = {
            apiKey: apiKey,
            authDomain: "<?php echo FIREBASE_AUTH_DOMAIN; ?>",
            projectId: "<?php echo FIREBASE_PROJECT_ID; ?>",
            storageBucket: "<?php echo FIREBASE_STORAGE_BUCKET; ?>",
            messagingSenderId: "<?php echo FIREBASE_MESSAGING_SENDER_ID; ?>",
            appId: "<?php echo FIREBASE_APP_ID; ?>"
        };
        
        const app = initializeApp(firebaseConfig);
        const auth = getAuth(app);
        const provider = new GoogleAuthProvider();
        
        document.getElementById('btnGoogleLogin').addEventListener('click', async () => {
            try {
                const result = await signInWithPopup(auth, provider);
                const user = result.user;
                
                // Gửi thông tin user về server
                const response = await fetch('<?php echo SITE_URL; ?>/auth/google_auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    credentials: 'include', // Quan trọng: gửi cookies để session hoạt động
                    body: JSON.stringify({
                        uid: user.uid,
                        email: user.email,
                        name: user.displayName,
                        photo: user.photoURL,
                        google_id: user.providerData[0].uid
                    })
                });
                
                const data = await response.json();
                console.log('Login response:', data);
                
                if (data.success) {
                    // Redirect về trang chủ ngay lập tức
                    // Session đã được set trên server, chỉ cần redirect
                    window.location.href = '<?php echo SITE_URL; ?>';
                } else {
                    alert('Lỗi: ' + data.message);
                }
            } catch (error) {
                console.error('Firebase Error:', error);
                let errorMsg = 'Đăng nhập Google thất bại!';
                
                if (error.code === 'auth/popup-closed-by-user') {
                    errorMsg = 'Bạn đã đóng cửa sổ đăng nhập!';
                } else if (error.code === 'auth/unauthorized-domain') {
                    errorMsg = 'Domain chưa được authorize trong Firebase Console!\nVui lòng thêm localhost vào Authorized domains.';
                } else if (error.code === 'auth/api-key-not-valid') {
                    errorMsg = 'API Key không hợp lệ!\nVui lòng kiểm tra lại config/config.php';
                }
                
                alert(errorMsg + '\n\nLỗi chi tiết: ' + error.message);
            }
        });
    }
</script>
</body>
</html>
