<?php
/**
 * File kiểm tra SITE_URL sau khi deploy
 * Truy cập: https://yourdomain.com/check_url.php
 * Sau khi kiểm tra xong, nên xóa file này để bảo mật
 */

require_once __DIR__ . '/config/config.php';

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm tra SITE_URL</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .info {
            background: #e7f3ff;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 15px 0;
            border-radius: 4px;
        }
        .success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .test-item {
            margin: 10px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .test-item strong {
            color: #007bff;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔍 Kiểm tra SITE_URL</h1>
        
        <div class="info">
            <strong>Lưu ý:</strong> Sau khi kiểm tra xong, nên xóa file này để bảo mật.
        </div>
        
        <div class="test-item">
            <strong>SITE_URL hiện tại:</strong><br>
            <code><?php echo SITE_URL; ?></code>
        </div>
        
        <div class="test-item">
            <strong>HTTP_HOST:</strong><br>
            <code><?php echo $_SERVER['HTTP_HOST'] ?? 'N/A'; ?></code>
        </div>
        
        <div class="test-item">
            <strong>REQUEST_URI:</strong><br>
            <code><?php echo $_SERVER['REQUEST_URI'] ?? 'N/A'; ?></code>
        </div>
        
        <div class="test-item">
            <strong>SCRIPT_NAME:</strong><br>
            <code><?php echo $_SERVER['SCRIPT_NAME'] ?? 'N/A'; ?></code>
        </div>
        
        <div class="test-item">
            <strong>HTTPS:</strong><br>
            <code><?php echo (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'Yes' : 'No'; ?></code>
        </div>
        
        <?php
        // Test các URL
        require_once __DIR__ . '/config/url_helper.php';
        
        $test_css = asset_url('assets/css/style.css');
        $test_js = asset_url('assets/js/main.js');
        $test_page = page_url('products.php');
        $test_upload = upload_url('products/test.jpg');
        ?>
        
        <h2 style="margin-top: 30px;">Test URL Helper Functions:</h2>
        
        <div class="test-item">
            <strong>asset_url('assets/css/style.css'):</strong><br>
            <code><?php echo $test_css; ?></code>
        </div>
        
        <div class="test-item">
            <strong>asset_url('assets/js/main.js'):</strong><br>
            <code><?php echo $test_js; ?></code>
        </div>
        
        <div class="test-item">
            <strong>page_url('products.php'):</strong><br>
            <code><?php echo $test_page; ?></code>
        </div>
        
        <div class="test-item">
            <strong>upload_url('products/test.jpg'):</strong><br>
            <code><?php echo $test_upload; ?></code>
        </div>
        
        <?php
        // Kiểm tra double slash
        $has_double_slash = (strpos(SITE_URL, '//') !== false && strpos(SITE_URL, 'http://') === false && strpos(SITE_URL, 'https://') === false);
        ?>
        
        <div class="info <?php echo $has_double_slash ? 'error' : 'success'; ?>">
            <?php if ($has_double_slash): ?>
                <strong>⚠️ Cảnh báo:</strong> SITE_URL có double slash! Cần kiểm tra lại.
            <?php else: ?>
                <strong>✅ OK:</strong> SITE_URL không có double slash.
            <?php endif; ?>
        </div>
        
        <a href="<?php echo SITE_URL; ?>" class="btn">Về trang chủ</a>
    </div>
</body>
</html>












