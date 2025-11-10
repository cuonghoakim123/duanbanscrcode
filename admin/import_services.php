<?php
/**
 * Script import dữ liệu dịch vụ mẫu vào database
 * Chạy file này một lần để import các dịch vụ có sẵn
 * Truy cập: http://localhost/duanbanscrcode/admin/import_services.php
 */

require_once '../config/config.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$success_count = 0;
$error_count = 0;
$errors = [];

// Dữ liệu dịch vụ
$services_data = [
    [
        'name' => 'Thiết kế website',
        'slug' => 'thiet-ke-website',
        'description' => 'Thiết kế website chuyên nghiệp, chuẩn SEO, tương thích mọi thiết bị. Giao diện đẹp mắt, thân thiện với người dùng.',
        'content' => 'Thiết kế website chuyên nghiệp, chuẩn SEO, tương thích mọi thiết bị. Giao diện đẹp mắt, thân thiện với người dùng. Chúng tôi cam kết mang đến cho bạn một website hiện đại, tối ưu trải nghiệm người dùng và đạt hiệu quả cao trong kinh doanh.',
        'icon' => 'fas fa-laptop-code',
        'price_from' => 1500000,
        'price_unit' => 'đ',
        'features' => "Responsive design\nTối ưu SEO\nTốc độ tải nhanh\nBảo mật cao",
        'featured' => 0,
        'status' => 'active',
        'sort_order' => 1
    ],
    [
        'name' => 'Website bán hàng',
        'slug' => 'website-ban-hang',
        'description' => 'Hệ thống bán hàng online hoàn chỉnh với giỏ hàng, thanh toán, quản lý đơn hàng và khách hàng.',
        'content' => 'Hệ thống bán hàng online hoàn chỉnh với giỏ hàng, thanh toán, quản lý đơn hàng và khách hàng. Tích hợp đầy đủ các tính năng cần thiết cho một website thương mại điện tử chuyên nghiệp.',
        'icon' => 'fas fa-shopping-cart',
        'price_from' => 1000000,
        'price_unit' => 'đ',
        'features' => "Giỏ hàng thông minh\nThanh toán đa dạng\nQuản lý kho hàng\nBáo cáo doanh thu",
        'featured' => 1, // Phổ biến
        'status' => 'active',
        'sort_order' => 2
    ],
    [
        'name' => 'Ứng dụng di động',
        'slug' => 'ung-dung-di-dong',
        'description' => 'Phát triển ứng dụng iOS và Android cho doanh nghiệp. Tích hợp đầy đủ tính năng theo yêu cầu.',
        'content' => 'Phát triển ứng dụng iOS và Android cho doanh nghiệp. Tích hợp đầy đủ tính năng theo yêu cầu. Ứng dụng được tối ưu hiệu suất, giao diện hiện đại và trải nghiệm người dùng tốt nhất.',
        'icon' => 'fas fa-mobile-alt',
        'price_from' => 3000000,
        'price_unit' => 'đ',
        'features' => "iOS & Android\nUI/UX hiện đại\nPush notification\nTích hợp API",
        'featured' => 0,
        'status' => 'active',
        'sort_order' => 3
    ],
    [
        'name' => 'SEO - Marketing',
        'slug' => 'seo-marketing',
        'description' => 'Tối ưu hóa website lên top Google, chạy quảng cáo Google Ads, Facebook Ads hiệu quả.',
        'content' => 'Tối ưu hóa website lên top Google, chạy quảng cáo Google Ads, Facebook Ads hiệu quả. Dịch vụ marketing online toàn diện giúp doanh nghiệp tăng trưởng doanh thu và mở rộng thị trường.',
        'icon' => 'fas fa-search',
        'price_from' => 3000000,
        'price_unit' => 'đ/tháng',
        'features' => "SEO tổng thể\nGoogle Ads\nFacebook Ads\nContent Marketing",
        'featured' => 0,
        'status' => 'active',
        'sort_order' => 4
    ],
    [
        'name' => 'Hosting - Domain',
        'slug' => 'hosting-domain',
        'description' => 'Cung cấp hosting tốc độ cao, bảo mật tốt. Hỗ trợ đăng ký và quản lý tên miền.',
        'content' => 'Cung cấp hosting tốc độ cao, bảo mật tốt. Hỗ trợ đăng ký và quản lý tên miền. Dịch vụ hosting ổn định, tốc độ nhanh với đội ngũ hỗ trợ chuyên nghiệp 24/7.',
        'icon' => 'fas fa-server',
        'price_from' => 500000,
        'price_unit' => 'đ/năm',
        'features' => "SSL miễn phí\nBackup tự động\nUptime 99.9%\nHỗ trợ 24/7",
        'featured' => 0,
        'status' => 'active',
        'sort_order' => 5
    ],
    [
        'name' => 'Bảo trì - Nâng cấp',
        'slug' => 'bao-tri-nang-cap',
        'description' => 'Dịch vụ bảo trì, nâng cấp website định kỳ. Sửa lỗi, thêm tính năng mới theo yêu cầu.',
        'content' => 'Dịch vụ bảo trì, nâng cấp website định kỳ. Sửa lỗi, thêm tính năng mới theo yêu cầu. Đảm bảo website luôn hoạt động ổn định, cập nhật các tính năng mới nhất và bảo mật tốt nhất.',
        'icon' => 'fas fa-tools',
        'price_from' => 1000000,
        'price_unit' => 'đ/tháng',
        'features' => "Bảo trì định kỳ\nSửa lỗi nhanh\nThêm tính năng\nTư vấn miễn phí",
        'featured' => 0,
        'status' => 'active',
        'sort_order' => 6
    ]
];

// Kiểm tra xem bảng services có tồn tại không
try {
    $test_query = "SELECT 1 FROM services LIMIT 1";
    $db->query($test_query);
} catch (PDOException $e) {
    die('<div style="padding: 20px; font-family: Arial;">
        <h2 style="color: red;">Lỗi!</h2>
        <p>Bảng services chưa được tạo. Vui lòng chạy file <strong>database/services_table.sql</strong> trước.</p>
        <a href="services.php">Quay lại</a>
    </div>');
}

// Xử lý import
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['import'])) {
    foreach ($services_data as $service) {
        try {
            // Kiểm tra xem service đã tồn tại chưa
            $check_query = "SELECT id FROM services WHERE slug = :slug";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->bindParam(':slug', $service['slug']);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                // Cập nhật nếu đã tồn tại
                $query = "UPDATE services SET 
                    name = :name, 
                    description = :description, 
                    content = :content, 
                    icon = :icon, 
                    price_from = :price_from, 
                    price_unit = :price_unit, 
                    features = :features, 
                    featured = :featured, 
                    status = :status, 
                    sort_order = :sort_order,
                    updated_at = NOW()
                    WHERE slug = :slug";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':name', $service['name']);
                $stmt->bindParam(':description', $service['description']);
                $stmt->bindParam(':content', $service['content']);
                $stmt->bindParam(':icon', $service['icon']);
                $stmt->bindParam(':price_from', $service['price_from']);
                $stmt->bindParam(':price_unit', $service['price_unit']);
                $stmt->bindParam(':features', $service['features']);
                $stmt->bindParam(':featured', $service['featured']);
                $stmt->bindParam(':status', $service['status']);
                $stmt->bindParam(':sort_order', $service['sort_order']);
                $stmt->bindParam(':slug', $service['slug']);
                
                if ($stmt->execute()) {
                    $success_count++;
                } else {
                    $error_count++;
                    $errors[] = "Lỗi cập nhật: " . $service['name'];
                }
            } else {
                // Thêm mới
                $query = "INSERT INTO services (name, slug, description, content, icon, price_from, price_unit, features, featured, status, sort_order) 
                          VALUES (:name, :slug, :description, :content, :icon, :price_from, :price_unit, :features, :featured, :status, :sort_order)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':name', $service['name']);
                $stmt->bindParam(':slug', $service['slug']);
                $stmt->bindParam(':description', $service['description']);
                $stmt->bindParam(':content', $service['content']);
                $stmt->bindParam(':icon', $service['icon']);
                $stmt->bindParam(':price_from', $service['price_from']);
                $stmt->bindParam(':price_unit', $service['price_unit']);
                $stmt->bindParam(':features', $service['features']);
                $stmt->bindParam(':featured', $service['featured']);
                $stmt->bindParam(':status', $service['status']);
                $stmt->bindParam(':sort_order', $service['sort_order']);
                
                if ($stmt->execute()) {
                    $success_count++;
                } else {
                    $error_count++;
                    $errors[] = "Lỗi thêm: " . $service['name'];
                }
            }
        } catch (PDOException $e) {
            $error_count++;
            $errors[] = "Lỗi: " . $service['name'] . " - " . $e->getMessage();
        }
    }
}

include 'includes/admin_header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h5><i class="fas fa-database"></i> Import dữ liệu dịch vụ mẫu</h5>
    </div>
    <div class="admin-card-body">
        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['import'])): ?>
            <?php if ($success_count > 0): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 
                    <strong>Thành công!</strong> Đã import/cập nhật <strong><?php echo $success_count; ?></strong> dịch vụ.
                </div>
            <?php endif; ?>
            
            <?php if ($error_count > 0): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> 
                    <strong>Có lỗi!</strong> <?php echo $error_count; ?> dịch vụ gặp lỗi.
                    <?php if (!empty($errors)): ?>
                        <ul class="mt-2 mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="mt-3">
                <a href="services.php" class="btn-admin btn-admin-primary">
                    <i class="fas fa-list"></i> Xem danh sách dịch vụ
                </a>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                Script này sẽ import <strong><?php echo count($services_data); ?> dịch vụ mẫu</strong> vào database.
                <br>Nếu dịch vụ đã tồn tại (theo slug), nó sẽ được cập nhật.
            </div>
            
            <div class="table-responsive mt-4">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Tên dịch vụ</th>
                            <th>Icon</th>
                            <th>Giá từ</th>
                            <th>Nổi bật</th>
                            <th>Thứ tự</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services_data as $index => $service): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><strong><?php echo htmlspecialchars($service['name']); ?></strong></td>
                                <td><i class="<?php echo htmlspecialchars($service['icon']); ?>"></i></td>
                                <td><?php echo number_format($service['price_from']); ?> <?php echo htmlspecialchars($service['price_unit']); ?></td>
                                <td>
                                    <?php if ($service['featured']): ?>
                                        <span class="badge bg-warning"><i class="fas fa-star"></i> Có</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $service['sort_order']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <form method="POST" action="" class="mt-4">
                <button type="submit" name="import" class="btn-admin btn-admin-primary btn-lg">
                    <i class="fas fa-upload"></i> Import dữ liệu
                </button>
                <a href="services.php" class="btn-admin btn-admin-secondary btn-lg">
                    <i class="fas fa-times"></i> Hủy
                </a>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>

