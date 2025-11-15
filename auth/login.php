<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/url_helper.php';

$title = 'Đăng nhập';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validation
    if (empty($email) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ!';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM users WHERE email = :email AND status = 'active'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $user['password'])) {
                // Đăng nhập thành công
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['fullname'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Lấy avatar từ database nếu có
                if (!empty($user['avatar'])) {
                    $avatar_url = $user['avatar'];
                    // Xử lý đường dẫn avatar
                    if (preg_match('/^https?:\/\//', $avatar_url)) {
                        // URL đầy đủ, giữ nguyên
                        $_SESSION['user_avatar'] = $avatar_url;
                    } elseif (preg_match('/^\/uploads\//', $avatar_url)) {
                        $_SESSION['user_avatar'] = SITE_URL . $avatar_url;
                    } elseif (strpos($avatar_url, 'uploads/') !== false) {
                        $_SESSION['user_avatar'] = SITE_URL . '/' . ltrim($avatar_url, '/');
                    } else {
                        $_SESSION['user_avatar'] = SITE_URL . '/uploads/users/' . basename($avatar_url);
                    }
                } else {
                    // Nếu không có avatar, set null để hiển thị icon mặc định
                    $_SESSION['user_avatar'] = null;
                }
                
                // Remember me functionality
                if ($remember) {
                    setcookie('user_email', $email, time() + (30 * 24 * 60 * 60), '/'); // 30 days
                } else {
                    setcookie('user_email', '', time() - 3600, '/');
                }
                
                // Redirect
                if ($user['role'] == 'admin') {
                    header('Location: ../admin/index.php');
                } else {
                    // Redirect to intended page or home
                    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '../index.php';
                    header('Location: ' . $redirect);
                }
                exit();
            } else {
                $error = 'Mật khẩu không chính xác!';
            }
        } else {
            $error = 'Email không tồn tại hoặc tài khoản đã bị khóa!';
        }
    }
}

// Get success message if registered
if (isset($_GET['registered'])) {
    $success = 'Đăng ký thành công! Vui lòng đăng nhập.';
}

// Get email from cookie if remember me was set
$savedEmail = isset($_COOKIE['user_email']) ? $_COOKIE['user_email'] : '';

// Background image URL - dùng đường dẫn tương đối từ file login.php
// Vì login.php nằm trong thư mục auth/, nên cần dùng ../ để lên root
$bgImageUrl = '../assets/images/15.jpg';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title . ' - ' . SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <style>
        .auth-container {
            background-image: url('../assets/images/15.jpg') !important;
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
        }
    </style>
</head>
<body>
<div class="auth-container" style="background-image: url('../assets/images/15.jpg'); background-size: cover !important; background-position: center !important; background-repeat: no-repeat !important;">
    <div class="auth-card">
        <div class="auth-card-header">
            <div class="auth-icon">
                <img src="../assets/images/16.jpg" alt="Login" style="width: 190px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(255, 255, 255, 0.3);">
            </div>
            <h2>Đăng nhập để tiếp tục</h2>
        </div>
        <div class="auth-card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="loginForm">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-envelope me-2"></i>Email
                    </label>
                    <div class="input-group">
                        <i class="fas fa-envelope input-group-icon"></i>
                        <input type="email" name="email" id="email" class="form-control" 
                               placeholder="Nhập email của bạn" 
                               required 
                               value="<?php echo isset($email) ? htmlspecialchars($email) : htmlspecialchars($savedEmail); ?>"
                               autocomplete="email">
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
                               required
                               autocomplete="current-password">
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye" id="passwordToggle"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember" <?php echo $savedEmail ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="remember">
                            Ghi nhớ đăng nhập
                        </label>
                    </div>
                    <a href="forgot_password.php" class="auth-link">
                        Quên mật khẩu?
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
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    
    if (!email || !password) {
        e.preventDefault();
        return false;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Email không hợp lệ!');
        return false;
    }
    
    const btn = document.getElementById('submitBtn');
    btn.classList.add('loading');
    btn.innerHTML = '<span></span>';
});

// Auto-focus on first empty field
window.addEventListener('DOMContentLoaded', function() {
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    
    if (!email.value) {
        email.focus();
    } else if (!password.value) {
        password.focus();
    }
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
                const response = await fetch('google_auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
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
                    // Redirect về trang chủ
                    window.location.href = '../index.php';
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
