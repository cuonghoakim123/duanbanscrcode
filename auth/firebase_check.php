<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm tra Firebase Config</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php
require_once '../config/config.php';
require_once '../config/database.php';
?>
    <div class="container my-5">
        <h1 class="text-center mb-4"><i class="fab fa-google"></i> Kiểm tra cấu hình Firebase</h1>
        
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-cog"></i> Trạng thái cấu hình</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <tr>
                                <td><strong>API Key:</strong></td>
                                <td>
                                    <?php 
                                    $apiKeyOk = FIREBASE_API_KEY !== 'AIzaSyBxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
                                    echo $apiKeyOk ? 
                                        '<span class="badge bg-success"><i class="fas fa-check"></i> OK</span> ' . substr(FIREBASE_API_KEY, 0, 20) . '...' : 
                                        '<span class="badge bg-danger"><i class="fas fa-times"></i> Chưa cấu hình</span>';
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Auth Domain:</strong></td>
                                <td>
                                    <?php 
                                    $authDomainOk = FIREBASE_AUTH_DOMAIN !== 'your-project-id.firebaseapp.com';
                                    echo $authDomainOk ? 
                                        '<span class="badge bg-success"><i class="fas fa-check"></i> OK</span> ' . FIREBASE_AUTH_DOMAIN : 
                                        '<span class="badge bg-danger"><i class="fas fa-times"></i> Chưa cấu hình</span>';
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Project ID:</strong></td>
                                <td>
                                    <?php 
                                    $projectIdOk = FIREBASE_PROJECT_ID !== 'your-project-id';
                                    echo $projectIdOk ? 
                                        '<span class="badge bg-success"><i class="fas fa-check"></i> OK</span> ' . FIREBASE_PROJECT_ID : 
                                        '<span class="badge bg-danger"><i class="fas fa-times"></i> Chưa cấu hình</span>';
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Storage Bucket:</strong></td>
                                <td>
                                    <?php 
                                    $bucketOk = FIREBASE_STORAGE_BUCKET !== 'your-project-id.appspot.com';
                                    echo $bucketOk ? 
                                        '<span class="badge bg-success"><i class="fas fa-check"></i> OK</span> ' . FIREBASE_STORAGE_BUCKET : 
                                        '<span class="badge bg-danger"><i class="fas fa-times"></i> Chưa cấu hình</span>';
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Messaging Sender ID:</strong></td>
                                <td>
                                    <?php 
                                    $senderIdOk = FIREBASE_MESSAGING_SENDER_ID !== '123456789012';
                                    echo $senderIdOk ? 
                                        '<span class="badge bg-success"><i class="fas fa-check"></i> OK</span> ' . FIREBASE_MESSAGING_SENDER_ID : 
                                        '<span class="badge bg-danger"><i class="fas fa-times"></i> Chưa cấu hình</span>';
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>App ID:</strong></td>
                                <td>
                                    <?php 
                                    $appIdOk = FIREBASE_APP_ID !== '1:123456789012:web:abcdef1234567890';
                                    echo $appIdOk ? 
                                        '<span class="badge bg-success"><i class="fas fa-check"></i> OK</span> ' . substr(FIREBASE_APP_ID, 0, 25) . '...' : 
                                        '<span class="badge bg-danger"><i class="fas fa-times"></i> Chưa cấu hình</span>';
                                    ?>
                                </td>
                            </tr>
                        </table>
                        
                        <?php
                        $allConfigured = $apiKeyOk && $authDomainOk && $projectIdOk && $bucketOk && $senderIdOk && $appIdOk;
                        
                        if ($allConfigured): ?>
                            <div class="alert alert-success">
                                <h5><i class="fas fa-check-circle"></i> Tất cả cấu hình đã OK!</h5>
                                <p class="mb-0">Bạn có thể sử dụng Google Login ngay bây giờ.</p>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <h5><i class="fas fa-exclamation-triangle"></i> Cần cấu hình Firebase</h5>
                                <p>Vui lòng làm theo các bước sau:</p>
                                <ol>
                                    <li>Tạo project tại <a href="https://console.firebase.google.com" target="_blank">Firebase Console</a></li>
                                    <li>Thêm Web App và lấy configuration</li>
                                    <li>Bật Google Authentication</li>
                                    <li>Thêm <code>localhost</code> vào Authorized domains</li>
                                    <li>Cập nhật <code>config/config.php</code> với các giá trị từ Firebase</li>
                                </ol>
                                <a href="../FIREBASE_SETUP.md" target="_blank" class="btn btn-primary">
                                    <i class="fas fa-book"></i> Xem hướng dẫn chi tiết
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card shadow mt-4">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fas fa-database"></i> Kiểm tra Database</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            $database = new Database();
                            $db = $database->getConnection();
                            
                            // Kiểm tra các cột cần thiết
                            $stmt = $db->query("DESCRIBE users");
                            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            
                            $requiredColumns = ['google_id', 'firebase_uid', 'avatar', 'email_verified'];
                            $missingColumns = array_diff($requiredColumns, $columns);
                            
                            if (empty($missingColumns)): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> Database đã có đầy đủ các cột cần thiết!
                                </div>
                                <ul class="list-group">
                                    <?php foreach ($requiredColumns as $col): ?>
                                        <li class="list-group-item">
                                            <i class="fas fa-check text-success"></i> <code><?php echo $col; ?></code>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-times-circle"></i> Thiếu các cột sau trong bảng users:
                                </div>
                                <ul class="list-group mb-3">
                                    <?php foreach ($missingColumns as $col): ?>
                                        <li class="list-group-item text-danger">
                                            <i class="fas fa-times"></i> <code><?php echo $col; ?></code>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <a href="../database/firebase_update.sql" target="_blank" class="btn btn-warning">
                                    <i class="fas fa-database"></i> Download SQL Update
                                </a>
                            <?php endif;
                        } catch (Exception $e) {
                            echo '<div class="alert alert-danger">Lỗi kết nối database: ' . $e->getMessage() . '</div>';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="card shadow mt-4">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-vial"></i> Test Login</h5>
                    </div>
                    <div class="card-body text-center">
                        <?php if ($allConfigured): ?>
                            <p>Firebase đã sẵn sàng! Nhấn nút bên dưới để test đăng nhập:</p>
                            <a href="login.php" class="btn btn-danger btn-lg">
                                <i class="fab fa-google"></i> Test Google Login
                            </a>
                        <?php else: ?>
                            <p class="text-muted">Vui lòng hoàn tất cấu hình Firebase trước khi test.</p>
                            <button class="btn btn-secondary btn-lg" disabled>
                                <i class="fab fa-google"></i> Test Google Login
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="../index.php" class="btn btn-outline-primary">
                        <i class="fas fa-home"></i> Về trang chủ
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            padding: 20px;
        }
    </style>
</body>
</html>
