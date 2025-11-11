<?php
require_once '../config/config.php';
require_once '../config/database.php';

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
                
                // Redirect
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
            $error = 'Email không tồn tại hoặc tài khoản đã bị khóa!';
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
                    <h3 class="text-center mb-4">Đăng nhập</h3>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['registered'])): ?>
                        <div class="alert alert-success">Đăng ký thành công! Vui lòng đăng nhập.</div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        
                        <div class="mb-3 text-end">
                            <a href="forgot_password.php">Quên mật khẩu?</a>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-sign-in-alt"></i> Đăng nhập
                        </button>
                    </form>
                    
                    <div class="text-center mb-3">
                        <span>hoặc đăng nhập với</span>
                    </div>
                    
                    <button class="btn btn-danger w-100 mb-3" id="btnGoogleLogin">
                        <i class="fab fa-google"></i> Đăng nhập bằng Google
                    </button>
                    
                    <div class="text-center">
                        Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
                    // Hiển thị thông báo thành công
                    alert('Đăng nhập thành công! Xin chào ' + data.user.name);
                    
                    // Redirect về trang chủ với reload để cập nhật session
                    window.location.replace('<?php echo SITE_URL; ?>');
                    
                    // Hoặc reload trang hiện tại sau 500ms để đảm bảo session được lưu
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    alert('Lỗi: ' + data.message);
                }
            } catch (error) {
                console.error('Firebase Error:', error);
                let errorMsg = 'Đăng nhập Google thất bại!';
                
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

<?php include '../includes/footer.php'; ?>
