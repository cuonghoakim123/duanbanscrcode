<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Headers để tránh CORS và cache issues
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate dữ liệu
    if (!isset($data['email']) || !isset($data['name'])) {
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin bắt buộc']);
        exit();
    }
    
    $uid = $data['uid'] ?? '';
    $email = $data['email'];
    $name = $data['name'];
    $photo = $data['photo'] ?? '';
    $google_id = $data['google_id'] ?? '';
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Kiểm tra user đã tồn tại chưa
    $query = "SELECT * FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // User đã tồn tại
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Kiểm tra status
        if (isset($user['status']) && $user['status'] != 'active') {
            echo json_encode(['success' => false, 'message' => 'Tài khoản đã bị khóa']);
            exit();
        }
        
        $user_id = $user['id'];
    } else {
        // Tạo user mới - chỉ dùng các cột cơ bản
        $query = "INSERT INTO users (email, fullname, created_at) 
                  VALUES (:email, :name, NOW())";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':name', $name);
        
        try {
            $stmt->execute();
            $user_id = $db->lastInsertId();
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi tạo tài khoản: ' . $e->getMessage()]);
            exit();
        }
    }
    
    // Lấy thông tin user
    $query = "SELECT * FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Tạo session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['fullname'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'] ?? 'user';
    $_SESSION['login_method'] = 'google';
    
    // Lấy avatar từ database hoặc từ Google photo
    if (!empty($user['avatar'])) {
        $avatar_url = $user['avatar'];
        // Xử lý đường dẫn avatar
        if (preg_match('/^https?:\/\//', $avatar_url)) {
            $_SESSION['user_avatar'] = $avatar_url;
        } elseif (preg_match('/^\/uploads\//', $avatar_url)) {
            $_SESSION['user_avatar'] = SITE_URL . $avatar_url;
        } elseif (strpos($avatar_url, 'uploads/') !== false) {
            $_SESSION['user_avatar'] = SITE_URL . '/' . ltrim($avatar_url, '/');
        } else {
            $_SESSION['user_avatar'] = SITE_URL . '/uploads/users/' . basename($avatar_url);
        }
    } elseif (!empty($photo)) {
        // Nếu không có avatar trong DB nhưng có photo từ Google, dùng photo từ Google
        $_SESSION['user_avatar'] = $photo;
    } else {
        $_SESSION['user_avatar'] = null;
    }
    
    // Log để debug
    error_log("Google Login Success: User ID = " . $user['id'] . ", Name = " . $user['fullname']);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Đăng nhập thành công!',
        'user' => [
            'id' => $user['id'],
            'name' => $user['fullname'],
            'email' => $user['email']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
