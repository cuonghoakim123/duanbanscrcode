<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';

header('Content-Type: application/json');
// CORS headers - cho phép credentials từ cùng origin
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
header('Access-Control-Allow-Origin: ' . $origin);
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Tạo bảng chat nếu chưa có
try {
    $db->exec("CREATE TABLE IF NOT EXISTS admin_chats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        product_id INT NULL,
        session_id VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        sender_type ENUM('user', 'admin') NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_session (session_id),
        INDEX idx_user (user_id),
        INDEX idx_product (product_id),
        INDEX idx_read (is_read)
    )");
} catch (PDOException $e) {
    // Bảng đã tồn tại hoặc có lỗi
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'send') {
    // Gửi tin nhắn
    $input = json_decode(file_get_contents('php://input'), true);
    $message = trim($input['message'] ?? '');
    $product_id = isset($input['product_id']) ? (int)$input['product_id'] : null;
    $sender_type = $input['sender_type'] ?? 'user';
    
    if (empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Tin nhắn không được để trống']);
        exit;
    }
    
    // Lấy user_id nếu đã đăng nhập
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Tạo hoặc lấy session_id
    $session_id = $input['session_id'] ?? '';
    if (empty($session_id)) {
        if (!isset($_SESSION['admin_chat_session_id'])) {
            $_SESSION['admin_chat_session_id'] = 'admin_chat_' . time() . '_' . uniqid();
        }
        $session_id = $_SESSION['admin_chat_session_id'];
    } else {
        $_SESSION['admin_chat_session_id'] = $session_id;
    }
    
    // Lưu tin nhắn
    try {
        $query = "INSERT INTO admin_chats (user_id, product_id, session_id, message, sender_type) 
                  VALUES (:user_id, :product_id, :session_id, :message, :sender_type)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':session_id', $session_id);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':sender_type', $sender_type);
        $stmt->execute();
        
        $chat_id = $db->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'chat_id' => $chat_id,
            'session_id' => $session_id,
            'message' => 'Tin nhắn đã được gửi'
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_messages') {
    // Lấy tin nhắn
    $session_id = $_GET['session_id'] ?? '';
    $last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
    
    if (empty($session_id) && isset($_SESSION['admin_chat_session_id'])) {
        $session_id = $_SESSION['admin_chat_session_id'];
    }
    
    if (empty($session_id)) {
        echo json_encode(['success' => true, 'messages' => []]);
        exit;
    }
    
    try {
        $query = "SELECT * FROM admin_chats 
                  WHERE session_id = :session_id AND id > :last_id 
                  ORDER BY created_at ASC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':session_id', $session_id);
        $stmt->bindParam(':last_id', $last_id);
        $stmt->execute();
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'messages' => $messages,
            'session_id' => $session_id
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_sessions') {
    // Lấy danh sách session (cho admin)
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    try {
        $query = "SELECT DISTINCT session_id, 
                         MAX(created_at) as last_message,
                         COUNT(*) as message_count,
                         SUM(CASE WHEN sender_type = 'user' AND is_read = 0 THEN 1 ELSE 0 END) as unread_count
                  FROM admin_chats 
                  GROUP BY session_id 
                  ORDER BY last_message DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'sessions' => $sessions]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'upload') {
    // Upload file/ảnh
    if (!isset($_FILES['image']) && !isset($_FILES['file'])) {
        echo json_encode(['success' => false, 'message' => 'Không có file được upload']);
        exit;
    }
    
    $file = $_FILES['image'] ?? $_FILES['file'];
    $session_id = $_POST['session_id'] ?? '';
    $sender_type = $_POST['sender_type'] ?? 'admin';
    
    if (empty($session_id)) {
        echo json_encode(['success' => false, 'message' => 'Session ID không được để trống']);
        exit;
    }
    
    // Validate file
    $allowed_image_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $allowed_video_types = ['video/mp4', 'video/webm', 'video/ogg'];
    $max_size = 20 * 1024 * 1024; // 20MB
    
    if ($file['size'] > $max_size) {
        echo json_encode(['success' => false, 'message' => 'File quá lớn! Tối đa 20MB']);
        exit;
    }
    
    // Tạo thư mục upload nếu chưa có
    $upload_dir = __DIR__ . '/../uploads/chats/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $file_name = 'chat_' . time() . '_' . uniqid() . '.' . $file_ext;
    $file_path = $upload_dir . $file_name;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // Tạo URL
        $file_url = SITE_URL . '/uploads/chats/' . $file_name;
        
        // Lấy user_id nếu đã đăng nhập
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        
        // Tạo message text với link file
        $is_image = in_array($file['type'], $allowed_image_types);
        $is_video = in_array($file['type'], $allowed_video_types);
        
        if ($is_image) {
            $message = '[IMAGE]' . $file_url . '[/IMAGE]';
        } elseif ($is_video) {
            $message = '[VIDEO]' . $file_url . '[/VIDEO]';
        } else {
            $message = '[FILE]' . $file_url . '|' . $file['name'] . '[/FILE]';
        }
        
        // Lưu vào database
        try {
            $query = "INSERT INTO admin_chats (user_id, session_id, message, sender_type) 
                      VALUES (:user_id, :session_id, :message, :sender_type)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':session_id', $session_id);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':sender_type', $sender_type);
            $stmt->execute();
            
            $chat_id = $db->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'chat_id' => $chat_id,
                'file_url' => $file_url,
                'file_name' => $file['name'],
                'file_type' => $file['type'],
                'message' => 'Upload thành công'
            ]);
        } catch (PDOException $e) {
            // Xóa file nếu lưu database thất bại
            @unlink($file_path);
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể upload file']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>

