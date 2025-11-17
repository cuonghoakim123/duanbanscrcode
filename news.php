<?php
// Bật error reporting trong development (tắt trong production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Tắt hiển thị lỗi trực tiếp, chỉ log
ini_set('log_errors', 1);

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/lang.php';

$title = lang('news_page_title');

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception('Không thể kết nối database');
    }
} catch (Exception $e) {
    error_log('Database Error in news.php: ' . $e->getMessage());
    die('Có lỗi xảy ra. Vui lòng thử lại sau.');
}

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$offset = ($page - 1) * $limit;




// Lấy tin tức từ database
try {
    // Lấy tin tức nổi bật (featured)
    $featured_query = "SELECT * FROM news WHERE status = 'active' AND featured = 1 ORDER BY published_at DESC, created_at DESC LIMIT 1";
    $featured_stmt = $db->prepare($featured_query);
    $featured_stmt->execute();
    $featured_news = $featured_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Nếu không có tin nổi bật, lấy tin mới nhất
    if (!$featured_news) {
        $featured_query = "SELECT * FROM news WHERE status = 'active' ORDER BY published_at DESC, created_at DESC LIMIT 1";
        $featured_stmt = $db->prepare($featured_query);
        $featured_stmt->execute();
        $featured_news = $featured_stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Lấy danh sách tin tức (loại trừ tin nổi bật)
    $news_list_query = "SELECT * FROM news WHERE status = 'active'";
    if ($featured_news) {
        $news_list_query .= " AND id != :featured_id";
    }
    $news_list_query .= " ORDER BY published_at DESC, created_at DESC LIMIT :limit OFFSET :offset";
    
    $news_list_stmt = $db->prepare($news_list_query);
    if ($featured_news) {
        $news_list_stmt->bindValue(':featured_id', $featured_news['id'], PDO::PARAM_INT);
    }
    $news_list_stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $news_list_stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $news_list_stmt->execute();
    $news_list = $news_list_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Lấy tổng số tin tức để phân trang
    $total_query = "SELECT COUNT(*) as total FROM news WHERE status = 'active'";
    if ($featured_news) {
        $total_query .= " AND id != " . $featured_news['id'];
    }
    $total_stmt = $db->prepare($total_query);
    $total_stmt->execute();
    $total_news = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_news / $limit);
    
    // Lấy danh sách categories
    $categories_query = "SELECT DISTINCT category, COUNT(*) as count FROM news WHERE status = 'active' AND category IS NOT NULL AND category != '' GROUP BY category ORDER BY category";
    $categories_stmt = $db->prepare($categories_query);
    $categories_stmt->execute();
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Lấy bài viết phổ biến (theo lượt xem)
    $popular_query = "SELECT * FROM news WHERE status = 'active' ORDER BY views DESC LIMIT 3";
    $popular_stmt = $db->prepare($popular_query);
    $popular_stmt->execute();
    $popular_news = $popular_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Nếu bảng news chưa tồn tại, sử dụng dữ liệu mẫu
    $featured_news = null;
    $news_list = [];
    $categories = [];
    $popular_news = [];
    $total_pages = 1;
}

// Hàm helper để lấy URL ảnh
function getNewsImageUrl($image) {
    if (empty($image)) {
        return null;
    }
    // Nếu là URL đầy đủ
    if (strpos($image, 'http') === 0) {
        return $image;
    }
    // Nếu bắt đầu bằng /
    if (strpos($image, '/') === 0) {
        return SITE_URL . $image;
    }
    // Nếu chỉ là tên file
    return SITE_URL . '/uploads/news/' . basename($image);
}

include 'includes/header.php';
?>

<div class="page-header bg-gradient-info text-white py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3"><?php echo lang('news_header_title'); ?></h1>
                <p class="lead mb-0"><?php echo lang('news_header_subtitle'); ?></p>
            </div>
            <div class="col-lg-4">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Tìm kiếm bài viết...">
                    <button class="btn btn-light"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Featured Post -->
            <?php if ($featured_news): ?>
            <div class="featured-post mb-5">
                <div class="card border-0 shadow-lg">
                    <div class="featured-image">
                        <?php 
                        $featured_image_url = getNewsImageUrl($featured_news['image']);
                        if ($featured_image_url): 
                        ?>
                            <img src="<?php echo htmlspecialchars($featured_image_url); ?>" 
                                 alt="<?php echo htmlspecialchars($featured_news['title']); ?>"
                                 style="width: 100%; height: 400px; object-fit: cover;">
                        <?php else: ?>
                            <div class="gradient-placeholder" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <i class="fas fa-newspaper fa-5x text-white opacity-50"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <?php if ($featured_news['featured']): ?>
                                <span class="badge bg-primary">Nổi bật</span>
                            <?php endif; ?>
                            <span class="text-muted">
                                <i class="far fa-clock"></i> 
                                <?php echo date('d/m/Y', strtotime($featured_news['published_at'] ?: $featured_news['created_at'])); ?>
                            </span>
                        </div>
                        <h2 class="card-title fw-bold mb-3"><?php echo htmlspecialchars($featured_news['title']); ?></h2>
                        <p class="card-text text-muted mb-3"><?php echo htmlspecialchars($featured_news['excerpt'] ?: safe_substr(strip_tags($featured_news['content']), 0, 150) . '...'); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="#" class="btn btn-primary"><?php echo lang('read_more'); ?> <i class="fas fa-arrow-right"></i></a>
                            <span class="text-muted"><i class="far fa-eye"></i> <?php echo number_format($featured_news['views']); ?> <?php echo lang('views'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- News Grid -->
            <div class="row g-4">
                <?php if (empty($news_list)): ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center py-5">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <p class="mb-0"><?php echo lang('no_news_yet'); ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($news_list as $news): ?>
                    <div class="col-md-6">
                        <div class="news-card h-100">
                            <div class="news-image">
                                <?php 
                                $news_image_url = getNewsImageUrl($news['image']);
                                if ($news_image_url): 
                                ?>
                                    <img src="<?php echo htmlspecialchars($news_image_url); ?>" 
                                         alt="<?php echo htmlspecialchars($news['title']); ?>"
                                         style="width: 100%; height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="gradient-placeholder" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                        <i class="fas fa-file-alt fa-3x text-white opacity-50"></i>
                                    </div>
                                <?php endif; ?>
                                <span class="category-badge"><?php echo htmlspecialchars($news['category'] ?? 'Khác'); ?></span>
                            </div>
                            <div class="news-content">
                                <div class="news-meta mb-2">
                                    <span><i class="far fa-calendar"></i> <?php echo date('d/m/Y', strtotime($news['published_at'] ?: $news['created_at'])); ?></span>
                                    <span><i class="far fa-eye"></i> <?php echo number_format($news['views']); ?></span>
                                </div>
                                <h5><?php echo htmlspecialchars($news['title']); ?></h5>
                                <p class="text-muted"><?php echo htmlspecialchars($news['excerpt'] ?: safe_substr(strip_tags($news['content']), 0, 100) . '...'); ?></p>
                                <a href="#" class="read-more"><?php echo lang('read_more'); ?> <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if (isset($total_pages) && $total_pages > 1): ?>
            <nav class="mt-5">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>"><i class="fas fa-chevron-left"></i></a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>"><i class="fas fa-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Categories -->
            <div class="sidebar-widget mb-4">
                <h5 class="widget-title"><?php echo lang('categories'); ?></h5>
                <ul class="category-list">
                    <?php if (empty($categories)): ?>
                        <li class="text-muted">Chưa có danh mục</li>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                            <li>
                                <a href="?category=<?php echo urlencode($cat['category']); ?>">
                                    <i class="fas fa-angle-right"></i> 
                                    <?php echo htmlspecialchars($cat['category']); ?> 
                                    <span>(<?php echo $cat['count']; ?>)</span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Popular Posts -->
            <div class="sidebar-widget mb-4">
                <h5 class="widget-title"><?php echo lang('popular_posts'); ?></h5>
                <?php if (empty($popular_news)): ?>
                    <p class="text-muted">Chưa có bài viết phổ biến</p>
                <?php else: ?>
                    <?php foreach ($popular_news as $popular): ?>
                    <div class="popular-post">
                        <div class="popular-image">
                            <?php 
                            $popular_image_url = getNewsImageUrl($popular['image']);
                            if ($popular_image_url): 
                            ?>
                                <img src="<?php echo htmlspecialchars($popular_image_url); ?>" 
                                     alt="<?php echo htmlspecialchars($popular['title']); ?>"
                                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                            <?php else: ?>
                                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px;"></div>
                            <?php endif; ?>
                        </div>
                        <div class="popular-content">
                            <h6><?php echo safe_substr(htmlspecialchars($popular['title']), 0, 50); ?>...</h6>
                            <small class="text-muted">
                                <i class="far fa-clock"></i> 
                                <?php echo date('d/m/Y', strtotime($popular['published_at'] ?: $popular['created_at'])); ?>
                            </small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Newsletter -->
            <div class="sidebar-widget newsletter-widget">
                <h5 class="widget-title text-white">Đăng ký nhận tin</h5>
                <p class="text-white-50">Nhận bài viết mới nhất qua email</p>
                <form>
                    <div class="mb-3">
                        <input type="email" class="form-control" placeholder="Email của bạn">
                    </div>
                    <button type="submit" class="btn btn-light w-100">Đăng ký</button>
                </form>
            </div>

            <!-- Tags -->
            <div class="sidebar-widget">
                <h5 class="widget-title">Tags</h5>
                <div class="tags">
                    <a href="#" class="tag">Website</a>
                    <a href="#" class="tag">SEO</a>
                    <a href="#" class="tag">Design</a>
                    <a href="#" class="tag">Marketing</a>
                    <a href="#" class="tag">E-commerce</a>
                    <a href="#" class="tag">WordPress</a>
                    <a href="#" class="tag">UI/UX</a>
                    <a href="#" class="tag">Mobile</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.featured-image {
    height: 400px;
    overflow: hidden;
}

.gradient-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.news-card {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 2px 20px rgba(0,0,0,0.08);
    background: white;
    transition: all 0.3s ease;
}

.news-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.news-image {
    height: 200px;
    position: relative;
}

.category-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(255,255,255,0.9);
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.news-content {
    padding: 20px;
}

.news-meta {
    display: flex;
    gap: 15px;
    font-size: 14px;
    color: #6c757d;
}

.news-card h5 {
    margin-bottom: 10px;
    font-weight: 600;
    line-height: 1.4;
}

.read-more {
    color: #0d6efd;
    text-decoration: none;
    font-weight: 600;
}

.read-more:hover {
    color: #0a58ca;
}

/* Sidebar */
.sidebar-widget {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
}

.widget-title {
    font-weight: 700;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 3px solid #0d6efd;
}

.category-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.category-list li {
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.category-list a {
    color: #333;
    text-decoration: none;
    display: flex;
    justify-content: space-between;
    transition: all 0.3s ease;
}

.category-list a:hover {
    color: #0d6efd;
    padding-left: 10px;
}

.category-list span {
    color: #6c757d;
    font-size: 14px;
}

.popular-post {
    display: flex;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
}

.popular-post:last-child {
    border-bottom: none;
}

.popular-content h6 {
    font-size: 14px;
    margin-bottom: 5px;
    line-height: 1.4;
}

.newsletter-widget {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.tag {
    padding: 8px 15px;
    background: #f8f9fa;
    border-radius: 20px;
    text-decoration: none;
    color: #333;
    font-size: 14px;
    transition: all 0.3s ease;
}

.tag:hover {
    background: #0d6efd;
    color: white;
}
</style>

<?php include 'includes/footer.php'; ?>
