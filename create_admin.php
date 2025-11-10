<?php
/**
 * Script tạo tài khoản admin
 * Chạy file này một lần để tạo tài khoản admin, sau đó xóa file này để bảo mật
 */

require_once 'config/config.php';
require_once 'config/database.php';

// Thông tin tài khoản admin
$admin_email = 'admin@gmail.com';
$admin_password = 'admin123'; // Mật khẩu mặc định
$admin_fullname = 'Administrator';
$admin_phone = '0355999141';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Kiểm tra xem admin đã tồn tại chưa
    $check_query = "SELECT id FROM users WHERE email = :email";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':email', $admin_email);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        echo "<h2 style='color: orange;'>⚠️ Tài khoản admin đã tồn tại!</h2>";
        echo "<p>Email: <strong>$admin_email</strong></p>";
        echo "<p>Để tạo tài khoản mới, vui lòng đổi email trong file create_admin.php</p>";
        echo "<hr>";
        echo "<h3>Thông tin tài khoản hiện tại:</h3>";
        echo "<p><strong>Email:</strong> $admin_email</p>";
        echo "<p><strong>Mật khẩu:</strong> (đã được lưu trong database)</p>";
        echo "<p><strong>Vai trò:</strong> Admin</p>";
        echo "<br>";
        echo "<a href='auth/login.php' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Đăng nhập ngay</a>";
    } else {
        // Mã hóa mật khẩu
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
        
        // Tạo tài khoản admin
        $query = "INSERT INTO users (email, password, fullname, phone, role, email_verified, status) 
                  VALUES (:email, :password, :fullname, :phone, 'admin', TRUE, 'active')";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $admin_email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':fullname', $admin_fullname);
        $stmt->bindParam(':phone', $admin_phone);
        
        if ($stmt->execute()) {
            echo "<h2 style='color: green;'>✅ Tạo tài khoản admin thành công!</h2>";
            echo "<div style='background: #f0f0f0; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
            echo "<h3>Thông tin đăng nhập:</h3>";
            echo "<table style='width: 100%; border-collapse: collapse;'>";
            echo "<tr><td style='padding: 10px; border: 1px solid #ddd;'><strong>Email:</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>$admin_email</td></tr>";
            echo "<tr><td style='padding: 10px; border: 1px solid #ddd;'><strong>Mật khẩu:</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>$admin_password</td></tr>";
            echo "<tr><td style='padding: 10px; border: 1px solid #ddd;'><strong>Vai trò:</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>Administrator</td></tr>";
            echo "<tr><td style='padding: 10px; border: 1px solid #ddd;'><strong>Trạng thái:</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>Active</td></tr>";
            echo "</table>";
            echo "</div>";
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; margin: 20px 0;'>";
            echo "<strong>⚠️ Lưu ý bảo mật:</strong><br>";
            echo "1. Vui lòng đổi mật khẩu sau khi đăng nhập lần đầu<br>";
            echo "2. Xóa file <code>create_admin.php</code> sau khi đã tạo tài khoản<br>";
            echo "3. Không chia sẻ thông tin đăng nhập với người khác";
            echo "</div>";
            echo "<br>";
            echo "<a href='auth/login.php' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Đăng nhập ngay</a>";
            echo "<a href='index.php' style='display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>Về trang chủ</a>";
        } else {
            echo "<h2 style='color: red;'>❌ Có lỗi xảy ra khi tạo tài khoản!</h2>";
            echo "<p>Vui lòng kiểm tra lại kết nối database.</p>";
        }
    }
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</h2>";
}
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 50px auto;
        padding: 20px;
        background: #f5f5f5;
    }
    h2 {
        margin-top: 0;
    }
    code {
        background: #e9ecef;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: monospace;
    }
</style>

