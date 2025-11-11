<?php
/**
 * URL Helper Functions
 * Các hàm hỗ trợ xử lý URL để tránh lỗi double slash
 */

/**
 * Tạo URL đầy đủ từ đường dẫn tương đối
 * @param string $path Đường dẫn tương đối (ví dụ: '/assets/css/style.css' hoặc 'assets/css/style.css')
 * @return string URL đầy đủ
 */
function asset_url($path) {
    if (empty($path)) {
        return SITE_URL;
    }
    
    // Loại bỏ leading slash nếu có
    $path = ltrim($path, '/');
    
    // Tạo URL đầy đủ
    $url = SITE_URL . '/' . $path;
    
    // Loại bỏ double slash (trừ sau http:// hoặc https://)
    $url = preg_replace('#([^:])//+#', '$1/', $url);
    
    return $url;
}

/**
 * Tạo URL cho trang
 * @param string $page Tên trang (ví dụ: 'products.php' hoặc '/products.php')
 * @return string URL đầy đủ
 */
function page_url($page) {
    if (empty($page)) {
        return SITE_URL;
    }
    
    // Thêm leading slash nếu chưa có
    if (strpos($page, '/') !== 0) {
        $page = '/' . $page;
    }
    
    $url = SITE_URL . $page;
    
    // Loại bỏ double slash (trừ sau http:// hoặc https://)
    $url = preg_replace('#([^:])//+#', '$1/', $url);
    
    return $url;
}

/**
 * Tạo URL cho uploads
 * @param string $path Đường dẫn file trong uploads (ví dụ: 'products/image.jpg')
 * @return string URL đầy đủ
 */
function upload_url($path) {
    if (empty($path)) {
        return '';
    }
    
    // Loại bỏ leading slash nếu có
    $path = ltrim($path, '/');
    
    // Tạo URL đầy đủ
    $url = SITE_URL . '/uploads/' . $path;
    
    // Loại bỏ double slash
    $url = preg_replace('#([^:])//+#', '$1/', $url);
    
    return $url;
}

