<?php
// Hàm tự động detect SITE_URL từ server
function getSiteUrl() {
    // Kiểm tra nếu đã define rồi thì dùng luôn
    if (defined('SITE_URL')) {
        return SITE_URL;
    }
    
    // Tự động detect từ server
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Lấy script name để xác định base path
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $script_dir = dirname($script_name);
    
    // Xác định base path - tương thích cả Windows và Linux hosting
    // Normalize đường dẫn trước khi kiểm tra
    $script_dir = str_replace('\\', '/', $script_dir);
    // Nếu script ở root (/, \), base_path là rỗng
    if ($script_dir == '/' || $script_dir == '\\' || $script_dir == '.' || empty($script_dir)) {
        $base_path = '';
    } else {
        $base_path = $script_dir;
        // Đảm bảo có leading slash
        if (strpos($base_path, '/') !== 0) {
            $base_path = '/' . $base_path;
        }
    }
    
    // Tạo URL
    $url = $protocol . $host . $base_path;
    // Loại bỏ trailing slash và double slash
    $url = rtrim($url, '/');
    $url = preg_replace('#([^:])//+#', '$1/', $url);
    
    return $url;
}

// Cấu hình chung của website
// Tự động detect SITE_URL từ server (hoạt động trên cả localhost và hosting)
$detected_url = getSiteUrl();
define('SITE_URL', $detected_url);
define('SITE_NAME', 'Shop Bán Hàng Chuyên Nghiệp');

// Cấu hình Firebase - ĐÃ KẾT NỐI THÀNH CÔNG
define('FIREBASE_API_KEY', 'AIzaSyBvJAJR_H2dsSNkWunD_N0JiAhnj80Eql4');
define('FIREBASE_AUTH_DOMAIN', 'duanbanscrcode.firebaseapp.com');
define('FIREBASE_PROJECT_ID', 'duanbanscrcode');
define('FIREBASE_STORAGE_BUCKET', 'duanbanscrcode.firebasestorage.app');
define('FIREBASE_MESSAGING_SENDER_ID', '515720110095');
define('FIREBASE_APP_ID', '1:515720110095:web:3d30a434c01051ab2b437a');
define('FIREBASE_MEASUREMENT_ID', 'G-MLLT69QR5W'); // Optional - for Analytics

// Cấu hình MoMo Payment - Đã xóa (không sử dụng thanh toán)
// define('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create');
// define('MOMO_PARTNER_CODE', 'YOUR_PARTNER_CODE');
// define('MOMO_ACCESS_KEY', 'YOUR_ACCESS_KEY');
// define('MOMO_SECRET_KEY', 'YOUR_SECRET_KEY');
// define('MOMO_RETURN_URL', SITE_URL . '/payment/momo_return.php');
// define('MOMO_NOTIFY_URL', SITE_URL . '/payment/momo_ipn.php');

// Cấu hình session
if (session_status() === PHP_SESSION_NONE) {
    // Cấu hình session cookie - tự động detect HTTPS cho hosting
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
                (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https');
    
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', $is_https ? 1 : 0); // Tự động bật nếu dùng HTTPS
    ini_set('session.cookie_samesite', 'Lax');
    
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Helper function: Tạo placeholder image SVG (nhanh hơn, không cần request external)
function getPlaceholderImage($width = 300, $height = 200, $text = 'Image', $bgColor = 'f8f9fa', $textColor = '6c757d') {
    $svg = '<svg width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg">
        <rect width="' . $width . '" height="' . $height . '" fill="#' . $bgColor . '"/>
        <text x="50%" y="50%" font-family="Arial, sans-serif" font-size="14" fill="#' . $textColor . '" text-anchor="middle" dominant-baseline="middle">' . htmlspecialchars($text) . '</text>
    </svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

// Helper function: Lấy placeholder cho product
function getProductPlaceholder($width = 300, $height = 200) {
    return getPlaceholderImage($width, $height, 'Product', 'e9ecef', '495057');
}

// Helper function: Lấy placeholder cho avatar với icon user đẹp hơn
function getAvatarPlaceholder($size = 150) {
    // Tạo SVG đơn giản và đẹp hơn với gradient
    $unique_id = 'avatar_' . md5($size . time());
    $svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg width="' . $size . '" height="' . $size . '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <defs>
        <linearGradient id="grad' . $unique_id . '" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#e9ecef;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#dee2e6;stop-opacity:1" />
        </linearGradient>
    </defs>
    <circle cx="50" cy="50" r="50" fill="url(#grad' . $unique_id . ')"/>
    <circle cx="50" cy="38" r="13" fill="#6c757d" opacity="0.8"/>
    <path d="M 25 72 Q 25 62 50 62 Q 75 62 75 72 L 75 80 L 25 80 Z" fill="#6c757d" opacity="0.8"/>
</svg>';
    // Sử dụng base64 để đảm bảo tương thích tốt hơn
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

/**
 * Helper function: Cắt chuỗi an toàn, tự động chọn mb_substr hoặc substr
 * @param string $string Chuỗi cần cắt
 * @param int $start Vị trí bắt đầu
 * @param int|null $length Độ dài cần lấy
 * @return string Chuỗi đã cắt
 */
function safe_substr($string, $start, $length = null) {
    if (empty($string)) {
        return '';
    }
    
    // Sử dụng mb_substr nếu có (xử lý tốt tiếng Việt), nếu không dùng substr
    if (function_exists('mb_substr')) {
        return $length !== null ? mb_substr($string, $start, $length) : mb_substr($string, $start);
    } else {
        return $length !== null ? substr($string, $start, $length) : substr($string, $start);
    }
}

/**
 * Helper function: Build template image URL từ giá trị trong database
 * @param string $image_value Giá trị image từ database
 * @param bool $add_cache_busting Có thêm cache busting không (dùng filemtime)
 * @return string URL đầy đủ của ảnh
 */
function buildTemplateImageUrl($image_value, $add_cache_busting = false) {
    if (empty($image_value)) {
        return '';
    }
    
    $image_value = trim($image_value);
    $image_url = '';
    
    // Bước 1: Sửa /admin/uploads/ nếu có (sai)
    $image_value = str_replace('/admin/uploads/templates/', '/uploads/templates/', $image_value);
    $image_value = str_replace('admin/uploads/templates/', 'uploads/templates/', $image_value);
    
    // Bước 2: Nếu là URL external (http/https), giữ nguyên
    if (preg_match('/^https?:\/\//', $image_value)) {
        $image_url = $image_value;
    }
    // Bước 3: Nếu là URL từ SITE_URL, extract tên file
    elseif (strpos($image_value, SITE_URL) === 0) {
        // Loại bỏ SITE_URL
        $temp = str_replace(SITE_URL, '', $image_value);
        $temp = ltrim($temp, '/');
        // Loại bỏ uploads/templates/ nếu có
        if (strpos($temp, 'uploads/templates/') === 0) {
            $filename = basename(str_replace('uploads/templates/', '', $temp));
        } else {
            $filename = basename($temp);
        }
        $image_url = 'http://localhost/duanbanscrcode/uploads/templates/' . $filename;
    }
    // Bước 4: Nếu là đường dẫn relative hoặc chỉ tên file
    else {
        // Loại bỏ uploads/templates/ nếu có
        $temp = $image_value;
        if (strpos($temp, 'uploads/templates/') === 0) {
            $temp = str_replace('uploads/templates/', '', $temp);
        }
        if (strpos($temp, '/uploads/templates/') === 0) {
            $temp = str_replace('/uploads/templates/', '', $temp);
        }
        // Lấy chỉ tên file (loại bỏ mọi đường dẫn)
        $filename = basename($temp);
        // Build absolute URL - use absolute path to avoid base tag issues
        $image_url = 'http://localhost/duanbanscrcode/uploads/templates/' . $filename;
    }
    
    // Thêm cache busting nếu được yêu cầu
    if ($add_cache_busting && $image_url && !preg_match('/^https?:\/\//', $image_url)) {
        $file_path = dirname(__DIR__) . '/uploads/templates/' . basename($image_url);
        if (file_exists($file_path)) {
            $file_time = filemtime($file_path);
            $image_url .= '?v=' . $file_time;
        }
    }
    
    return $image_url;
}
?>
