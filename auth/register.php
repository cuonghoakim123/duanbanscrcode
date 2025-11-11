<?php
require_once '../config/config.php';
require_once '../config/database.php';

$title = 'Đăng ký tài khoản';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate
    if (empty($fullname) || empty($email) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ!';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp!';
    } else {
        // Kiểm tra email đã tồn tại
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT id FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error = 'Email đã được sử dụng!';
        } else {
            // Mã hóa mật khẩu
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Thêm user mới
            $query = "INSERT INTO users (email, password, fullname, phone) VALUES (:email, :password, :fullname, :phone)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':fullname', $fullname);
            $stmt->bindParam(':phone', $phone);
            
            if ($stmt->execute()) {
                $success = 'Đăng ký thành công! Vui lòng đăng nhập.';
                header('refresh:2;url=login.php');
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại!';
            }
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
                <i class="fas fa-user-plus"></i>
            </div>
            <h2>Tạo tài khoản mới</h2>
            <p>Tham gia cùng chúng tôi ngay hôm nay</p>
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
                    <br><small>Đang chuyển hướng...</small>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="registerForm">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-user me-2"></i>Họ và tên <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <i class="fas fa-user input-group-icon"></i>
                        <input type="text" name="fullname" class="form-control" 
                               placeholder="Nhập họ và tên của bạn" 
                               required 
                               value="<?php echo isset($fullname) ? htmlspecialchars($fullname) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-envelope me-2"></i>Email <span class="text-danger">*</span>
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
                        <i class="fas fa-phone me-2"></i>Số điện thoại
                    </label>
                    <div class="input-group">
                        <i class="fas fa-phone input-group-icon"></i>
                        <input type="text" name="phone" class="form-control" 
                               placeholder="Nhập số điện thoại (tùy chọn)" 
                               value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock me-2"></i>Mật khẩu <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <i class="fas fa-lock input-group-icon"></i>
                        <input type="password" name="password" id="password" class="form-control" 
                               placeholder="Tối thiểu 6 ký tự" 
                               required 
                               minlength="6"
                               onkeyup="checkPasswordStrength(this.value)">
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye" id="passwordToggle"></i>
                        </button>
                    </div>
                    <div class="password-strength" id="passwordStrength">
                        <div class="password-strength-bar"></div>
                    </div>
                    <small class="text-muted">Mật khẩu phải có ít nhất 6 ký tự</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock me-2"></i>Xác nhận mật khẩu <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <i class="fas fa-lock input-group-icon"></i>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" 
                               placeholder="Nhập lại mật khẩu" 
                               required
                               onkeyup="checkPasswordMatch()">
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye" id="confirm_passwordToggle"></i>
                        </button>
                    </div>
                    <small id="passwordMatch" class="text-muted"></small>
                </div>
                
                <button type="submit" class="btn-auth" id="submitBtn">
                    <span>
                        <i class="fas fa-user-plus"></i> Đăng ký
                    </span>
                </button>
            </form>
            
            <div class="auth-divider">hoặc</div>
            
            <button class="btn-google" id="btnGoogleSignup">
                <i class="fab fa-google"></i> Đăng ký bằng Google
            </button>
            
            <div class="auth-footer">
                Đã có tài khoản? 
                <a href="login.php" class="auth-link">Đăng nhập ngay</a>
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

function checkPasswordStrength(password) {
    const strengthBar = document.getElementById('passwordStrength');
    const strength = strengthBar.querySelector('.password-strength-bar');
    
    if (password.length === 0) {
        strengthBar.className = 'password-strength';
        strength.style.width = '0%';
        return;
    }
    
    let strengthValue = 0;
    if (password.length >= 6) strengthValue += 1;
    if (password.length >= 8) strengthValue += 1;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strengthValue += 1;
    if (/\d/.test(password)) strengthValue += 1;
    if (/[^a-zA-Z\d]/.test(password)) strengthValue += 1;
    
    strengthBar.className = 'password-strength';
    if (strengthValue <= 2) {
        strengthBar.classList.add('weak');
    } else if (strengthValue <= 4) {
        strengthBar.classList.add('medium');
    } else {
        strengthBar.classList.add('strong');
    }
}

function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const matchMsg = document.getElementById('passwordMatch');
    
    if (confirmPassword.length === 0) {
        matchMsg.textContent = '';
        matchMsg.className = 'text-muted';
        return;
    }
    
    if (password === confirmPassword) {
        matchMsg.textContent = '✓ Mật khẩu khớp';
        matchMsg.className = 'text-success';
    } else {
        matchMsg.textContent = '✗ Mật khẩu không khớp';
        matchMsg.className = 'text-danger';
    }
}

// Form loading state
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Mật khẩu xác nhận không khớp!');
        return false;
    }
    
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
        document.getElementById('btnGoogleSignup').addEventListener('click', (e) => {
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
        
        document.getElementById('btnGoogleSignup').addEventListener('click', async () => {
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
                console.log('Register response:', data);
                
                if (data.success) {
                    // Redirect về trang chủ ngay lập tức
                    window.location.href = '<?php echo SITE_URL; ?>';
                } else {
                    alert('Lỗi: ' + data.message);
                }
            } catch (error) {
                console.error('Firebase Error:', error);
                let errorMsg = 'Đăng ký Google thất bại!';
                
                if (error.code === 'auth/popup-closed-by-user') {
                    errorMsg = 'Bạn đã đóng cửa sổ đăng nhập!';
                } else if (error.code === 'auth/unauthorized-domain') {
                    const currentDomain = window.location.hostname;
                    errorMsg = 'Domain chưa được authorize trong Firebase Console!\n\n' +
                               'Domain hiện tại: ' + currentDomain + '\n\n' +
                               'Vui lòng:\n' +
                               '1. Truy cập Firebase Console: https://console.firebase.google.com/\n' +
                               '2. Vào Settings > Authorized domains\n' +
                               '3. Thêm domain: ' + currentDomain + '\n\n' +
                               'Xem hướng dẫn chi tiết trong file FIREBASE_DOMAIN_SETUP.md';
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
