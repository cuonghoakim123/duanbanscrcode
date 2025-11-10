<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

// Xác định loại upload (product, template, news hoặc services)
$upload_type = $_POST['upload_type'] ?? 'product';
if ($upload_type == 'template') {
    $upload_dir = '../uploads/templates/';
    $file_prefix = 'template_';
} elseif ($upload_type == 'news') {
    $upload_dir = '../uploads/news/';
    $file_prefix = 'news_';
} elseif ($upload_type == 'services') {
    $upload_dir = '../uploads/services/';
    $file_prefix = 'service_';
} else {
    $upload_dir = '../uploads/products/';
    $file_prefix = 'product_';
}

$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$max_size = 5 * 1024 * 1024; // 5MB

// Tạo thư mục nếu chưa tồn tại
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Kiểm tra file đã upload
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Không có file được upload hoặc có lỗi xảy ra']);
    exit();
}

$file = $_FILES['image'];

// Kiểm tra loại file
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WebP)']);
    exit();
}

// Kiểm tra kích thước
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File quá lớn. Kích thước tối đa: 5MB']);
    exit();
}

// Tạo tên file unique
$file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$file_name = $file_prefix . time() . '_' . uniqid() . '.' . $file_extension;
$file_path = $upload_dir . $file_name;

// Upload file
if (move_uploaded_file($file['tmp_name'], $file_path)) {
    // Trả về URL ảnh (relative to site root)
    if ($upload_type == 'template') {
        $upload_path = '/uploads/templates/';
    } elseif ($upload_type == 'news') {
        $upload_path = '/uploads/news/';
    } elseif ($upload_type == 'services') {
        $upload_path = '/uploads/services/';
    } else {
        $upload_path = '/uploads/products/';
    }
    
    $image_url = SITE_URL . $upload_path . $file_name;
    echo json_encode([
        'success' => true,
        'message' => 'Upload thành công',
        'url' => $image_url,
        'path' => $upload_path . $file_name,
        'filename' => $file_name
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể upload file']);
}
?>

