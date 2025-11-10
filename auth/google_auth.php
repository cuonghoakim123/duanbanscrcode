<?php
require_once '../config/config.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $uid = $data['uid'];
    $email = $data['email'];
    $name = $data['name'];
    $photo = $data['photo'];
    $google_id = $data['google_id'];
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Kiểm tra user đã tồn tại chưa
    $query = "SELECT * FROM users WHERE email = :email OR firebase_uid = :uid";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':uid', $uid);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // User đã tồn tại, cập nhật thông tin
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $query = "UPDATE users SET firebase_uid = :uid, google_id = :google_id, avatar = :photo, email_verified = 1 WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':uid', $uid);
        $stmt->bindParam(':google_id', $google_id);
        $stmt->bindParam(':photo', $photo);
        $stmt->bindParam(':id', $user['id']);
        $stmt->execute();
        
        $user_id = $user['id'];
    } else {
        // Tạo user mới
        $query = "INSERT INTO users (email, fullname, firebase_uid, google_id, avatar, email_verified) 
                  VALUES (:email, :name, :uid, :google_id, :photo, 1)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':uid', $uid);
        $stmt->bindParam(':google_id', $google_id);
        $stmt->bindParam(':photo', $photo);
        $stmt->execute();
        
        $user_id = $db->lastInsertId();
    }
    
    // Lấy thông tin user
    $query = "SELECT * FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Regenerate session ID để bảo mật
    session_regenerate_id(true);
    
    // Tạo session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['fullname'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_avatar'] = $user['avatar'] ?? '';
    $_SESSION['login_method'] = 'google';
    
    // Log để debug
    error_log("Google Login Success: User ID = " . $user['id'] . ", Name = " . $user['fullname']);
    
    // Đảm bảo output buffering không can thiệp
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Đăng nhập thành công!',
        'user' => [
            'id' => $user['id'],
            'name' => $user['fullname'],
            'email' => $user['email'],
            'avatar' => $user['avatar'] ?? ''
        ]
    ]);
    
    // Đóng session sau khi gửi response
    session_write_close();
    exit();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
