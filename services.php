<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/lang.php';

$title = lang('services_page_title');

// Kết nối database và lấy danh sách services
$database = new Database();
$db = $database->getConnection();

$services = [];
try {
    $query = "SELECT * FROM services WHERE status = 'active' ORDER BY sort_order ASC, created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Nếu bảng chưa tồn tại, sử dụng mảng rỗng
    $services = [];
}

include 'includes/header.php';
?>

<div class="page-header bg-gradient-success text-white py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3"><?php echo lang('services_header_title'); ?></h1>
                <p class="lead mb-0"><?php echo lang('services_header_subtitle'); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <!-- Dịch vụ chính -->
    <div class="row g-4 mb-5">
        <?php if (empty($services)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> Chưa có dịch vụ nào được cập nhật. Vui lòng quay lại sau.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($services as $service): ?>
                <?php
                // Xử lý icon class và màu sắc
                $icon_class = $service['icon'] ?: 'fas fa-concierge-bell';
                $icon_colors = [
                    'fa-laptop-code' => 'text-primary',
                    'fa-shopping-cart' => 'text-success',
                    'fa-mobile-alt' => 'text-info',
                    'fa-search' => 'text-warning',
                    'fa-server' => 'text-danger',
                    'fa-tools' => 'text-secondary'
                ];
                $icon_color = 'text-primary';
                foreach ($icon_colors as $icon_key => $color) {
                    if (strpos($icon_class, $icon_key) !== false) {
                        $icon_color = $color;
                        break;
                    }
                }
                
                // Xử lý features (mỗi dòng một tính năng)
                $features_list = [];
                if (!empty($service['features'])) {
                    $features_lines = explode("\n", $service['features']);
                    foreach ($features_lines as $line) {
                        $line = trim($line);
                        if (!empty($line)) {
                            $features_list[] = $line;
                        }
                    }
                }
                
                // Xử lý giá
                $price_display = '';
                if ($service['price_from']) {
                    $price_display = lang('services_price_from') . ' ' . number_format($service['price_from']);
                    if ($service['price_unit']) {
                        $price_display .= $service['price_unit'];
                    } else {
                        $price_display .= ($lang == 'vi' ? 'đ' : ' VND');
                    }
                }
                
                // Xác định màu nút
                $btn_class = $service['featured'] ? 'btn-success' : 'btn-primary';
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="service-card h-100 <?php echo $service['featured'] ? 'featured' : ''; ?>">
                        <?php if ($service['featured']): ?>
                            <div class="featured-badge"><?php echo lang('services_popular'); ?></div>
                        <?php endif; ?>
                        
                        <div class="service-icon">
                            <?php if ($service['image']): ?>
                                <?php 
                                // Xử lý đường dẫn ảnh - hỗ trợ nhiều định dạng
                                $image_url = trim($service['image']);
                                
                                // Nếu là URL đầy đủ (http/https), sử dụng trực tiếp
                                if (preg_match('/^https?:\/\//', $image_url)) {
                                    // URL đầy đủ, giữ nguyên
                                }
                                // Nếu bắt đầu bằng /uploads/, thêm SITE_URL
                                elseif (preg_match('/^\/uploads\//', $image_url)) {
                                    $image_url = SITE_URL . $image_url;
                                }
                                // Nếu bắt đầu bằng /, thêm SITE_URL
                                elseif (preg_match('/^\//', $image_url)) {
                                    $image_url = SITE_URL . $image_url;
                                }
                                // Nếu chứa uploads/ nhưng không bắt đầu bằng /, thêm SITE_URL và /
                                elseif (strpos($image_url, 'uploads/') !== false) {
                                    if (strpos($image_url, SITE_URL) === false) {
                                        $image_url = SITE_URL . '/' . ltrim($image_url, '/');
                                    }
                                }
                                // Nếu chỉ là tên file hoặc đường dẫn tương đối, thêm đường dẫn đầy đủ
                                else {
                                    $image_url = SITE_URL . '/uploads/services/' . basename($image_url);
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                     alt="<?php echo htmlspecialchars($service['name']); ?>"
                                     style="width: 100%; max-width: 600px; max-height: 200px; object-fit: contain; border-radius: 10px;"
                                     onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <i class="<?php echo htmlspecialchars($icon_class); ?> fa-3x <?php echo $icon_color; ?>" style="display: none;"></i>
                            <?php else: ?>
                                <i class="<?php echo htmlspecialchars($icon_class); ?> fa-3x <?php echo $icon_color; ?>"></i>
                            <?php endif; ?>
                        </div>
                        
                        <h4><?php echo htmlspecialchars($service['name']); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($service['description']); ?></p>
                        
                        <?php if (!empty($features_list)): ?>
                            <ul class="service-features">
                                <?php foreach ($features_list as $feature): ?>
                                    <li><i class="fas fa-check text-success"></i> <?php echo htmlspecialchars($feature); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        
                        <?php if ($price_display): ?>
                            <div class="price-tag"><?php echo $price_display; ?></div>
                        <?php endif; ?>
                        
                        <a href="<?php echo SITE_URL; ?>/contact.php" class="btn <?php echo $btn_class; ?> w-100 mt-3">
                            <?php echo lang('services_consult'); ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Quy trình -->
    <section class="my-5 py-5 bg-light rounded">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Quy trình thực hiện dự án</h2>
                <p class="text-muted">Chuyên nghiệp, minh bạch và hiệu quả</p>
            </div>
            <div class="timeline">
                <div class="timeline-item left">
                    <div class="timeline-content">
                        <span class="timeline-number">1</span>
                        <h5>Tiếp nhận & Tư vấn</h5>
                        <p>Lắng nghe nhu cầu, phân tích và đưa ra giải pháp tối ưu</p>
                    </div>
                </div>
                <div class="timeline-item right">
                    <div class="timeline-content">
                        <span class="timeline-number">2</span>
                        <h5>Ký hợp đồng & Thanh toán</h5>
                        <p>Thỏa thuận chi tiết, ký hợp đồng và thanh toán 50% để khởi động</p>
                    </div>
                </div>
                <div class="timeline-item left">
                    <div class="timeline-content">
                        <span class="timeline-number">3</span>
                        <h5>Thiết kế giao diện</h5>
                        <p>Tạo mockup, chỉnh sửa theo ý kiến khách hàng đến khi hoàn thiện</p>
                    </div>
                </div>
                <div class="timeline-item right">
                    <div class="timeline-content">
                        <span class="timeline-number">4</span>
                        <h5>Lập trình & Phát triển</h5>
                        <p>Code website, tích hợp tính năng theo đúng yêu cầu</p>
                    </div>
                </div>
                <div class="timeline-item left">
                    <div class="timeline-content">
                        <span class="timeline-number">5</span>
                        <h5>Test & Nghiệm thu</h5>
                        <p>Kiểm tra kỹ lưỡng, sửa lỗi và nghiệm thu cùng khách hàng</p>
                    </div>
                </div>
                <div class="timeline-item right">
                    <div class="timeline-content">
                        <span class="timeline-number">6</span>
                        <h5>Bàn giao & Hỗ trợ</h5>
                        <p>Bàn giao website, hướng dẫn và hỗ trợ vận hành</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Cam kết -->
    <section class="my-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Cam kết của chúng tôi</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-3 col-6">
                <div class="commitment-item text-center">
                    <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                    <h5>Bảo hành trọn đời</h5>
                    <p class="text-muted small">Hỗ trợ sửa lỗi miễn phí suốt thời gian sử dụng</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="commitment-item text-center">
                    <i class="fas fa-clock fa-3x text-success mb-3"></i>
                    <h5>Đúng thời gian</h5>
                    <p class="text-muted small">Cam kết hoàn thành đúng deadline đã thỏa thuận</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="commitment-item text-center">
                    <i class="fas fa-headset fa-3x text-info mb-3"></i>
                    <h5>Hỗ trợ 24/7</h5>
                    <p class="text-muted small">Đội ngũ luôn sẵn sàng hỗ trợ mọi lúc mọi nơi</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="commitment-item text-center">
                    <i class="fas fa-award fa-3x text-warning mb-3"></i>
                    <h5>Chất lượng cao</h5>
                    <p class="text-muted small">Sản phẩm đạt chuẩn quốc tế, tối ưu hiệu suất</p>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.bg-gradient-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.service-card {
    padding: 30px;
    border-radius: 15px;
    background: white;
    box-shadow: 0 2px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    position: relative;
}

.service-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.service-card.featured {
    border: 3px solid #28a745;
}

.featured-badge {
    position: absolute;
    top: -15px;
    right: 20px;
    background: #28a745;
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: bold;
}

.service-icon {
    margin-bottom: 20px;
    text-align: center;
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.service-icon img {
    transition: transform 0.3s ease;
}

.service-card:hover .service-icon img {
    transform: scale(1.05);
}

.service-card h4 {
    margin-bottom: 15px;
    font-weight: 700;
}

.service-features {
    list-style: none;
    padding: 0;
    margin: 20px 0;
}

.service-features li {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.price-tag {
    font-size: 24px;
    font-weight: bold;
    color: #0d6efd;
    margin-top: 20px;
}

/* Timeline */
.timeline {
    position: relative;
    max-width: 1000px;
    margin: 0 auto;
}

.timeline::after {
    content: '';
    position: absolute;
    width: 4px;
    background: linear-gradient(to bottom, #667eea, #764ba2);
    top: 0;
    bottom: 0;
    left: 50%;
    margin-left: -2px;
}

.timeline-item {
    position: relative;
    width: 50%;
    padding: 20px 40px;
}

.timeline-item.left {
    left: 0;
}

.timeline-item.right {
    left: 50%;
}

.timeline-content {
    padding: 20px 30px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    position: relative;
}

.timeline-number {
    position: absolute;
    width: 50px;
    height: 50px;
    line-height: 50px;
    text-align: center;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 50%;
    font-weight: bold;
    font-size: 20px;
    top: 20px;
}

.timeline-item.left .timeline-number {
    right: -65px;
}

.timeline-item.right .timeline-number {
    left: -65px;
}

@media (max-width: 768px) {
    .timeline::after {
        left: 30px;
    }
    
    .timeline-item {
        width: 100%;
        padding-left: 70px;
        padding-right: 25px;
        left: 0 !important;
    }
    
    .timeline-item.left .timeline-number,
    .timeline-item.right .timeline-number {
        left: 5px;
    }
}

.commitment-item {
    padding: 20px;
    transition: all 0.3s ease;
}

.commitment-item:hover {
    transform: scale(1.05);
}
</style>

<?php include 'includes/footer.php'; ?>
