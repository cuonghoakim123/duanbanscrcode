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
    
    // Xác định base path
    // Nếu script ở root (/, \), base_path là rỗng
    if ($script_dir == '/' || $script_dir == '\\' || $script_dir == '.') {
        $base_path = '';
    } else {
        // Chuyển đổi backslash thành forward slash
        $base_path = str_replace('\\', '/', $script_dir);
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

// Cấu hình MoMo Payment
define('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create');
define('MOMO_PARTNER_CODE', 'YOUR_PARTNER_CODE');
define('MOMO_ACCESS_KEY', 'YOUR_ACCESS_KEY');
define('MOMO_SECRET_KEY', 'YOUR_SECRET_KEY');
define('MOMO_RETURN_URL', SITE_URL . '/payment/momo_return.php');
define('MOMO_NOTIFY_URL', SITE_URL . '/payment/momo_ipn.php');

// Cấu hình session
if (session_status() === PHP_SESSION_NONE) {
    // Cấu hình session cookie
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Đặt 1 nếu dùng HTTPS
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

// Helper function: Lấy placeholder cho avatar
function getAvatarPlaceholder($size = 150) {
    return getPlaceholderImage($size, $size, 'Avatar', 'dee2e6', '6c757d');
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
?>
