<?php
require_once 'config/config.php';
require_once 'config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
    exit();
}

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$upload_dir = __DIR__ . '/uploads/users/';
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$max_size = 2 * 1024 * 1024; // 2MB

// Tạo thư mục nếu chưa tồn tại
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Xử lý upload file
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['avatar'];
    
    // Kiểm tra loại file
    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WebP)']);
        exit();
    }
    
    // Kiểm tra kích thước
    if ($file['size'] > $max_size) {
        echo json_encode(['success' => false, 'message' => 'File quá lớn. Kích thước tối đa: 2MB']);
        exit();
    }
    
    // Tạo tên file unique
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $file_name = 'user_' . $user_id . '_' . time() . '_' . uniqid() . '.' . $file_extension;
    $file_path = $upload_dir . $file_name;
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        $avatar_path = '/uploads/users/' . $file_name;
        $avatar_url = SITE_URL . $avatar_path;
        
        // Cập nhật database
        try {
            $database = new Database();
            $db = $database->getConnection();
            $query = "UPDATE users SET avatar = :avatar WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':avatar', $avatar_path);
            $stmt->bindParam(':id', $user_id);
            
            if ($stmt->execute()) {
                // Cập nhật session
                $_SESSION['user_avatar'] = $avatar_url;
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Upload avatar thành công!',
                    'url' => $avatar_url,
                    'path' => $avatar_path
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể cập nhật database']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể upload file']);
    }
} 
// Xử lý chọn ảnh từ thư mục
elseif (isset($_POST['select_image']) && !empty($_POST['image_path'])) {
    $image_path = $_POST['image_path'];
    
    // Kiểm tra đường dẫn hợp lệ
    $allowed_dirs = [
        '/assets/images/',
        '/uploads/users/',
        '/uploads/products/'
    ];
    
    $is_valid = false;
    foreach ($allowed_dirs as $dir) {
        if (strpos($image_path, $dir) === 0) {
            $full_path = __DIR__ . $image_path;
            if (file_exists($full_path)) {
                $is_valid = true;
                break;
            }
        }
    }
    
    if (!$is_valid) {
        echo json_encode(['success' => false, 'message' => 'Đường dẫn ảnh không hợp lệ!']);
        exit();
    }
    
    // Cập nhật database
    try {
        $database = new Database();
        $db = $database->getConnection();
        $query = "UPDATE users SET avatar = :avatar WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':avatar', $image_path);
        $stmt->bindParam(':id', $user_id);
        
        if ($stmt->execute()) {
            // Cập nhật session
            $_SESSION['user_avatar'] = SITE_URL . $image_path;
            
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật avatar thành công!',
                'url' => SITE_URL . $image_path,
                'path' => $image_path
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể cập nhật database']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Không có dữ liệu được gửi']);
}
?>

