<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

$title = 'Cài đặt hệ thống';
$database = new Database();
$db = $database->getConnection();

$success_message = '';
$error_message = '';

// Xử lý cập nhật settings
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $settings = $_POST['settings'] ?? [];
        
        // Xử lý maintenance_mode riêng (checkbox)
        if (!isset($settings['maintenance_mode'])) {
            $settings['maintenance_mode'] = '0';
        }
        
        foreach ($settings as $key => $value) {
            // Kiểm tra xem setting đã tồn tại chưa
            $check_query = "SELECT id FROM settings WHERE setting_key = :key";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->bindParam(':key', $key);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                // Update existing setting
                $update_query = "UPDATE settings SET setting_value = :value, updated_at = NOW() WHERE setting_key = :key";
                $update_stmt = $db->prepare($update_query);
                $update_stmt->bindParam(':key', $key);
                $update_stmt->bindParam(':value', $value);
                $update_stmt->execute();
            } else {
                // Insert new setting
                $insert_query = "INSERT INTO settings (setting_key, setting_value, setting_type, updated_at) VALUES (:key, :value, 'text', NOW())";
                $insert_stmt = $db->prepare($insert_query);
                $insert_stmt->bindParam(':key', $key);
                $insert_stmt->bindParam(':value', $value);
                $insert_stmt->execute();
            }
        }
        
        $success_message = 'Cài đặt đã được cập nhật thành công!';
        
        // Reload settings sau khi cập nhật
        $query = "SELECT setting_key, setting_value, setting_type, description FROM settings ORDER BY setting_key";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = [
                'value' => $row['setting_value'],
                'type' => $row['setting_type'],
                'description' => $row['description']
            ];
        }
        
        // Cập nhật lại default_settings
        $default_settings = [
            'site_name' => $settings['site_name']['value'] ?? 'Website Bán Hàng',
            'site_email' => $settings['site_email']['value'] ?? 'admin@example.com',
            'site_phone' => $settings['site_phone']['value'] ?? '0123456789',
            'site_address' => $settings['site_address']['value'] ?? '',
            'site_description' => $settings['site_description']['value'] ?? '',
            'currency' => $settings['currency']['value'] ?? 'VND',
            'items_per_page' => $settings['items_per_page']['value'] ?? '20',
            'maintenance_mode' => $settings['maintenance_mode']['value'] ?? '0'
        ];
    } catch (PDOException $e) {
        $error_message = 'Có lỗi xảy ra: ' . $e->getMessage();
    }
}

// Lấy tất cả settings
$settings = [];
try {
    $query = "SELECT setting_key, setting_value, setting_type, description FROM settings ORDER BY setting_key";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        $settings[$row['setting_key']] = [
            'value' => $row['setting_value'],
            'type' => $row['setting_type'],
            'description' => $row['description']
        ];
    }
} catch (PDOException $e) {
    $error_message = 'Không thể tải cài đặt: ' . $e->getMessage();
}

// Lấy giá trị mặc định nếu không có
$default_settings = [
    'site_name' => $settings['site_name']['value'] ?? 'Website Bán Hàng',
    'site_email' => $settings['site_email']['value'] ?? 'admin@example.com',
    'site_phone' => $settings['site_phone']['value'] ?? '0123456789',
    'site_address' => $settings['site_address']['value'] ?? '',
    'site_description' => $settings['site_description']['value'] ?? '',
    'currency' => $settings['currency']['value'] ?? 'VND',
    'items_per_page' => $settings['items_per_page']['value'] ?? '20',
    'maintenance_mode' => $settings['maintenance_mode']['value'] ?? '0'
];

include 'includes/admin_header.php';
?>

<div class="content-area">
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <!-- Thông tin website -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h5><i class="fas fa-globe me-2"></i>Thông tin website</h5>
            </div>
            <div class="admin-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Tên website <span class="text-danger">*</span></label>
                            <input type="text" name="settings[site_name]" class="form-control-admin" 
                                   value="<?php echo htmlspecialchars($default_settings['site_name']); ?>" required>
                            <small class="text-muted">Tên hiển thị của website</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Mô tả website</label>
                            <input type="text" name="settings[site_description]" class="form-control-admin" 
                                   value="<?php echo htmlspecialchars($default_settings['site_description']); ?>">
                            <small class="text-muted">Mô tả ngắn về website</small>
                        </div>
                    </div>
                </div>
                
                <div class="form-group-admin">
                    <label class="form-label-admin">Địa chỉ</label>
                    <textarea name="settings[site_address]" class="form-control-admin" rows="2"><?php echo htmlspecialchars($default_settings['site_address']); ?></textarea>
                    <small class="text-muted">Địa chỉ công ty/cửa hàng</small>
                </div>
            </div>
        </div>
        
        <!-- Thông tin liên hệ -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h5><i class="fas fa-envelope me-2"></i>Thông tin liên hệ</h5>
            </div>
            <div class="admin-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Email <span class="text-danger">*</span></label>
                            <input type="email" name="settings[site_email]" class="form-control-admin" 
                                   value="<?php echo htmlspecialchars($default_settings['site_email']); ?>" required>
                            <small class="text-muted">Email liên hệ chính</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" name="settings[site_phone]" class="form-control-admin" 
                                   value="<?php echo htmlspecialchars($default_settings['site_phone']); ?>" required>
                            <small class="text-muted">Số điện thoại liên hệ</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cài đặt hiển thị -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h5><i class="fas fa-eye me-2"></i>Cài đặt hiển thị</h5>
            </div>
            <div class="admin-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Đơn vị tiền tệ</label>
                            <select name="settings[currency]" class="form-control-admin">
                                <option value="VND" <?php echo $default_settings['currency'] == 'VND' ? 'selected' : ''; ?>>VND (₫)</option>
                                <option value="USD" <?php echo $default_settings['currency'] == 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                                <option value="EUR" <?php echo $default_settings['currency'] == 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                            </select>
                            <small class="text-muted">Đơn vị tiền tệ sử dụng</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Số sản phẩm mỗi trang</label>
                            <input type="number" name="settings[items_per_page]" class="form-control-admin" 
                                   value="<?php echo htmlspecialchars($default_settings['items_per_page']); ?>" 
                                   min="1" max="100" required>
                            <small class="text-muted">Số lượng sản phẩm hiển thị trên mỗi trang</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cài đặt hệ thống -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h5><i class="fas fa-cog me-2"></i>Cài đặt hệ thống</h5>
            </div>
            <div class="admin-card-body">
                <div class="form-group-admin">
                    <div class="d-flex align-items-center mb-2">
                        <input type="hidden" name="settings[maintenance_mode]" value="0">
                        <input type="checkbox" name="settings[maintenance_mode]" value="1" 
                               id="maintenance_mode" 
                               class="form-check-input me-2" 
                               style="width: 20px; height: 20px; cursor: pointer;"
                               <?php echo $default_settings['maintenance_mode'] == '1' ? 'checked' : ''; ?>
                               onchange="this.previousElementSibling.value = this.checked ? '1' : '0';">
                        <label for="maintenance_mode" class="form-label-admin mb-0" style="cursor: pointer;">
                            <strong>Bật chế độ bảo trì</strong>
                        </label>
                    </div>
                    <small class="text-muted d-block">Khi bật, website sẽ hiển thị thông báo bảo trì cho người dùng (trừ admin)</small>
                </div>
            </div>
        </div>
        
        <!-- Nút lưu -->
        <div class="admin-card">
            <div class="admin-card-body">
                <div class="d-flex justify-content-end gap-3">
                    <a href="<?php echo SITE_URL; ?>/admin" class="btn-admin btn-admin-secondary">
                        <i class="fas fa-times"></i> Hủy
                    </a>
                    <button type="submit" class="btn-admin btn-admin-primary">
                        <i class="fas fa-save"></i> Lưu cài đặt
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include 'includes/admin_footer.php'; ?>

<style>
.admin-card-body {
    padding: 25px;
}

.form-group-admin {
    margin-bottom: 25px;
}

.form-label-admin {
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--admin-text);
}

.form-control-admin {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid var(--admin-border);
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-control-admin:focus {
    outline: none;
    border-color: var(--admin-primary);
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.text-muted {
    font-size: 12px;
    color: var(--admin-text-light);
    display: block;
    margin-top: 5px;
}

.btn-admin-secondary {
    background: #e2e8f0;
    color: var(--admin-text);
}

.btn-admin-secondary:hover {
    background: #cbd5e0;
    color: var(--admin-text);
}

.alert {
    border-radius: 8px;
    padding: 15px 20px;
    margin-bottom: 25px;
}

.alert-success {
    background: #c6f6d5;
    border: 1px solid #48bb78;
    color: #22543d;
}

.alert-danger {
    background: #fed7d7;
    border: 1px solid #f56565;
    color: #742a2a;
}
</style>

