<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/lang.php';

$title = lang('templates_page_title');

$database = new Database();
$db = $database->getConnection();

// Lấy danh mục
$categories_query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
$stmt = $db->prepare($categories_query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="page-header bg-gradient-primary text-white py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3"><?php echo lang('templates_header_title'); ?></h1>
                <p class="lead mb-0"><?php echo lang('templates_header_subtitle'); ?></p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="input-group">
                    <input type="text" class="form-control" id="searchTemplate" placeholder="<?php echo lang('templates_search_placeholder'); ?>">
                    <button class="btn btn-light"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <!-- Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="template-filter">
                <button class="filter-btn active" data-filter="all">
                    <i class="fas fa-th"></i> <?php echo lang('templates_filter_all'); ?>
                </button>
                <button class="filter-btn" data-filter="business">
                    <i class="fas fa-briefcase"></i> <?php echo lang('templates_category_business'); ?>
                </button>
                <button class="filter-btn" data-filter="ecommerce">
                    <i class="fas fa-shopping-cart"></i> <?php echo lang('templates_category_ecommerce'); ?>
                </button>
                <button class="filter-btn" data-filter="restaurant">
                    <i class="fas fa-utensils"></i> <?php echo lang('templates_category_restaurant'); ?>
                </button>
                <button class="filter-btn" data-filter="realestate">
                    <i class="fas fa-building"></i> <?php echo lang('templates_category_realestate'); ?>
                </button>
                <button class="filter-btn" data-filter="education">
                    <i class="fas fa-graduation-cap"></i> <?php echo lang('templates_category_education'); ?>
                </button>
                <button class="filter-btn" data-filter="healthcare">
                    <i class="fas fa-heartbeat"></i> <?php echo lang('templates_category_healthcare'); ?>
                </button>
                <button class="filter-btn" data-filter="beauty">
                    <i class="fas fa-spa"></i> <?php echo lang('templates_category_beauty'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Templates Grid -->
    <div class="row g-4" id="templatesGrid">
        <?php
        // Lấy templates từ database
        try {
            $templates_query = "SELECT * FROM templates WHERE status = 'active' ORDER BY featured DESC, created_at DESC";
            $stmt = $db->prepare($templates_query);
            $stmt->execute();
            $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Nếu bảng chưa tồn tại, sử dụng dữ liệu mẫu
            $templates = [
                ['name' => 'Business Pro', 'category' => 'business', 'price' => 2990000, 'sale_price' => null, 'image' => '', 'demo_url' => '', 'rating' => 5.0],
                ['name' => 'Shop Online', 'category' => 'ecommerce', 'price' => 3990000, 'sale_price' => null, 'image' => '', 'demo_url' => '', 'rating' => 5.0],
            ];
        }
        
        if (empty($templates)):
        ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-palette fa-5x text-muted mb-3"></i>
                <h4>Chưa có mẫu giao diện</h4>
                <p class="text-muted">Vui lòng quay lại sau</p>
            </div>
        <?php
        else:
            foreach ($templates as $template):
                $price = $template['sale_price'] ?? $template['price'];
                $category_map = [
                    'business' => 'business',
                    'ecommerce' => 'ecommerce',
                    'restaurant' => 'restaurant',
                    'realestate' => 'realestate',
                    'education' => 'education',
                    'healthcare' => 'healthcare',
                    'beauty' => 'beauty',
                    'other' => 'business'
                ];
                $template_category = $category_map[$template['category']] ?? 'business';
        ?>
        <div class="col-md-6 col-lg-4 template-item" data-category="<?php echo $template_category; ?>">
            <div class="template-card">
                <div class="template-image">
                    <?php if ($template['image']): ?>
                        <?php 
                        // Kiểm tra nếu là URL đầy đủ hoặc chỉ là tên file
                        $image_url = $template['image'];
                        if (!preg_match('/^https?:\/\//', $image_url) && !preg_match('/^\//', $image_url)) {
                            $image_url = SITE_URL . '/uploads/templates/' . $image_url;
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($image_url); ?>" 
                             alt="<?php echo htmlspecialchars($template['name']); ?>"
                             style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div class="placeholder-template" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas fa-laptop fa-5x text-white opacity-50"></i>
                        </div>
                    <?php endif; ?>
                    <div class="template-overlay">
                        <a href="#" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#templateDetailModal<?php echo $template['id'] ?? uniqid(); ?>">
                            <i class="fas fa-eye"></i> Xem chi tiết
                        </a>
                    </div>
                </div>
                <div class="template-info">
                    <h5><?php echo htmlspecialchars($template['name']); ?></h5>
                    <?php if ($template['description']): ?>
                        <p class="text-muted small mb-2"><?php echo htmlspecialchars(mb_substr($template['description'], 0, 60)); ?>...</p>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <?php if ($template['sale_price']): ?>
                                <span class="text-decoration-line-through text-muted small"><?php echo number_format($template['price']); ?>đ</span><br>
                                <span class="text-primary fw-bold"><?php echo number_format($template['sale_price']); ?>đ</span>
                            <?php else: ?>
                                <span class="text-primary fw-bold"><?php echo number_format($template['price']); ?>đ</span>
                            <?php endif; ?>
                        </div>
                        <div class="template-rating">
                            <?php 
                            $rating = $template['rating'] ?? 5.0;
                            for($i = 1; $i <= 5; $i++): 
                            ?>
                                <i class="fas fa-star <?php echo $i <= $rating ? 'text-warning' : 'text-muted'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Template Detail Modal -->
        <div class="modal fade" id="templateDetailModal<?php echo $template['id'] ?? uniqid(); ?>" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-palette"></i> <?php echo htmlspecialchars($template['name']); ?>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <?php if ($template['image']): ?>
                                    <?php 
                                    // Kiểm tra nếu là URL đầy đủ hoặc chỉ là tên file
                                    $image_url = $template['image'];
                                    if (!preg_match('/^https?:\/\//', $image_url) && !preg_match('/^\//', $image_url)) {
                                        $image_url = SITE_URL . '/uploads/templates/' . $image_url;
                                    }
                                    ?>
                                    <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                         alt="<?php echo htmlspecialchars($template['name']); ?>"
                                         class="img-fluid rounded shadow">
                                <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 400px;">
                                        <div class="text-center">
                                            <i class="fas fa-desktop fa-5x text-muted mb-3"></i>
                                            <p class="text-muted">Hình ảnh mẫu giao diện</p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($template['demo_url']): ?>
                                    <div class="mt-3">
                                        <a href="<?php echo htmlspecialchars($template['demo_url']); ?>" 
                                           target="_blank" 
                                           class="btn btn-outline-primary w-100">
                                            <i class="fas fa-external-link-alt"></i> Xem demo trực tiếp
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <h4 class="mb-3"><?php echo htmlspecialchars($template['name']); ?></h4>
                                
                                <?php if ($template['description']): ?>
                                    <div class="mb-4">
                                        <h6><i class="fas fa-info-circle text-primary"></i> Mô tả</h6>
                                        <p><?php echo nl2br(htmlspecialchars($template['description'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mb-4">
                                    <h6><i class="fas fa-tag text-primary"></i> Danh mục</h6>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($template['category'] ?? 'Khác'); ?></span>
                                </div>
                                
                                <div class="mb-4">
                                    <h6><i class="fas fa-star text-primary"></i> Đánh giá</h6>
                                    <div class="mb-2">
                                        <?php 
                                        $rating = $template['rating'] ?? 5.0;
                                        for($i = 1; $i <= 5; $i++): 
                                        ?>
                                            <i class="fas fa-star <?php echo $i <= $rating ? 'text-warning' : 'text-muted'; ?>" style="font-size: 20px;"></i>
                                        <?php endfor; ?>
                                        <span class="ms-2 fw-bold"><?php echo number_format($rating, 1); ?>/5.0</span>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <h6><i class="fas fa-dollar-sign text-primary"></i> Giá</h6>
                                    <div>
                                        <?php if ($template['sale_price']): ?>
                                            <span class="text-decoration-line-through text-muted fs-5"><?php echo number_format($template['price']); ?>đ</span>
                                            <span class="text-danger fw-bold fs-4 ms-2"><?php echo number_format($template['sale_price']); ?>đ</span>
                                            <?php 
                                            $discount = round((($template['price'] - $template['sale_price']) / $template['price']) * 100);
                                            ?>
                                            <span class="badge bg-danger ms-2">-<?php echo $discount; ?>%</span>
                                        <?php else: ?>
                                            <span class="text-primary fw-bold fs-4"><?php echo number_format($template['price']); ?>đ</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if ($template['features']): ?>
                                    <div class="mb-4">
                                        <h6><i class="fas fa-check-circle text-primary"></i> Tính năng</h6>
                                        <ul class="list-unstyled">
                                            <?php 
                                            $features = explode("\n", $template['features']);
                                            foreach ($features as $feature): 
                                                $feature = trim($feature);
                                                if (!empty($feature)):
                                            ?>
                                                <li class="mb-2">
                                                    <i class="fas fa-check text-success me-2"></i>
                                                    <?php echo htmlspecialchars($feature); ?>
                                                </li>
                                            <?php 
                                                endif;
                                            endforeach; 
                                            ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Đóng
                        </button>
                        <a href="<?php echo SITE_URL; ?>/contact.php?template=<?php echo urlencode($template['name']); ?>&template_id=<?php echo $template['id'] ?? ''; ?>" 
                           class="btn btn-primary btn-lg">
                            <i class="fas fa-phone-alt"></i> Liên hệ tư vấn ngay
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <?php 
            endforeach;
        endif;
        ?>
    </div>

    <!-- CTA Section -->
    <section class="my-5 py-5 bg-primary text-white rounded text-center">
        <div class="container">
            <h2 class="fw-bold mb-3">Không tìm thấy mẫu phù hợp?</h2>
            <p class="lead mb-4">Chúng tôi sẽ thiết kế riêng theo yêu cầu của bạn</p>
            <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-light btn-lg">
                <i class="fas fa-phone-alt"></i> Liên hệ tư vấn ngay
            </a>
        </div>
    </section>
</div>


<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.template-filter {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
}

.filter-btn {
    padding: 10px 20px;
    border: 2px solid transparent;
    background: white;
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
}

.filter-btn:hover,
.filter-btn.active {
    border-color: #0d6efd;
    color: #0d6efd;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
}

.template-card {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 2px 20px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    background: white;
}

.template-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.template-image {
    position: relative;
    overflow: hidden;
    aspect-ratio: 16/10;
}

.placeholder-template {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.template-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.template-card:hover .template-overlay {
    opacity: 1;
}

.template-info {
    padding: 20px;
}

.template-info h5 {
    margin-bottom: 10px;
    font-weight: 600;
}

.template-rating {
    font-size: 14px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const templateItems = document.querySelectorAll('.template-item');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.dataset.filter;
            
            // Update active button
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Filter items
            templateItems.forEach(item => {
                if (filter === 'all' || item.dataset.category === filter) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
    
    // Search functionality
    const searchInput = document.getElementById('searchTemplate');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        templateItems.forEach(item => {
            const title = item.querySelector('h5').textContent.toLowerCase();
            if (title.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
