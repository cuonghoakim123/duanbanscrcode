<?php
require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

$title = 'Quản lý đánh giá';
$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_review'])) {
    $review_id = (int)$_POST['review_id'];
    $status = $_POST['status'];
    
    $query = "UPDATE reviews SET status = :status WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $review_id);
    
    if ($stmt->execute()) {
        $success = 'Cập nhật trạng thái đánh giá thành công!';
    } else {
        $error = 'Có lỗi xảy ra!';
    }
}

// Xử lý xóa
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $query = "DELETE FROM reviews WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        $success = 'Xóa đánh giá thành công!';
    } else {
        $error = 'Có lỗi xảy ra!';
    }
}

// Lọc
$status_filter = $_GET['status'] ?? '';
$rating_filter = $_GET['rating'] ?? '';
$search = $_GET['search'] ?? '';

$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "r.status = :status";
    $params[':status'] = $status_filter;
}

if ($rating_filter) {
    $where_conditions[] = "r.rating = :rating";
    $params[':rating'] = $rating_filter;
}

if ($search) {
    $where_conditions[] = "(p.name LIKE :search OR u.fullname LIKE :search OR u.email LIKE :search OR r.comment LIKE :search)";
    $params[':search'] = "%$search%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Đếm tổng
$count_query = "SELECT COUNT(*) as total FROM reviews r 
                LEFT JOIN products p ON r.product_id = p.id 
                LEFT JOIN users u ON r.user_id = u.id 
                $where_clause";
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total / $limit);

// Lấy danh sách reviews
$query = "SELECT r.*, p.name as product_name, p.image as product_image, p.slug as product_slug,
          u.fullname as user_name, u.email as user_email
          FROM reviews r 
          LEFT JOIN products p ON r.product_id = p.id 
          LEFT JOIN users u ON r.user_id = u.id 
          $where_clause
          ORDER BY r.created_at DESC 
          LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/admin_header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h5><i class="fas fa-star"></i> Quản lý đánh giá</h5>
        <div class="d-flex gap-2">
            <a href="?status=pending" class="btn-admin btn-admin-warning btn-admin-sm">
                <i class="fas fa-clock"></i> Chờ duyệt
            </a>
        </div>
    </div>
    <div class="admin-card-body">
        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- Filter Form -->
        <form method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Chờ duyệt</option>
                        <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Đã duyệt</option>
                        <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Từ chối</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="rating" class="form-select">
                        <option value="">Tất cả sao</option>
                        <option value="5" <?php echo $rating_filter == '5' ? 'selected' : ''; ?>>5 sao</option>
                        <option value="4" <?php echo $rating_filter == '4' ? 'selected' : ''; ?>>4 sao</option>
                        <option value="3" <?php echo $rating_filter == '3' ? 'selected' : ''; ?>>3 sao</option>
                        <option value="2" <?php echo $rating_filter == '2' ? 'selected' : ''; ?>>2 sao</option>
                        <option value="1" <?php echo $rating_filter == '1' ? 'selected' : ''; ?>>1 sao</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn-admin btn-admin-primary w-100">
                        <i class="fas fa-search"></i> Lọc
                    </button>
                </div>
                <div class="col-md-3 text-end">
                    <span class="text-muted">Tổng: <?php echo $total; ?> đánh giá</span>
                </div>
            </div>
        </form>
        
        <!-- Reviews Table -->
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sản phẩm</th>
                        <th>Người dùng</th>
                        <th>Đánh giá</th>
                        <th>Nội dung</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($reviews)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-star"></i>
                                    <h4>Chưa có đánh giá</h4>
                                    <p>Chưa có đánh giá nào trong hệ thống</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($reviews as $review): ?>
                            <tr>
                                <td><?php echo $review['id']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($review['product_image']): ?>
                                            <?php 
                                            // Xử lý đường dẫn ảnh - hỗ trợ nhiều định dạng
                                            $product_image_url = trim($review['product_image']);
                                            
                                            // Nếu là URL đầy đủ (http/https), sử dụng trực tiếp
                                            if (preg_match('/^https?:\/\//', $product_image_url)) {
                                                // URL đầy đủ, giữ nguyên
                                            }
                                            // Nếu bắt đầu bằng /uploads/, thêm SITE_URL
                                            elseif (preg_match('/^\/uploads\//', $product_image_url)) {
                                                $product_image_url = SITE_URL . $product_image_url;
                                            }
                                            // Nếu bắt đầu bằng /, thêm SITE_URL
                                            elseif (preg_match('/^\//', $product_image_url)) {
                                                $product_image_url = SITE_URL . $product_image_url;
                                            }
                                            // Nếu chứa uploads/ nhưng không bắt đầu bằng /, thêm SITE_URL và /
                                            elseif (strpos($product_image_url, 'uploads/') !== false) {
                                                if (strpos($product_image_url, SITE_URL) === false) {
                                                    $product_image_url = SITE_URL . '/' . ltrim($product_image_url, '/');
                                                }
                                            }
                                            // Nếu chỉ là tên file hoặc đường dẫn tương đối, thêm đường dẫn đầy đủ
                                            else {
                                                $product_image_url = SITE_URL . '/uploads/products/' . basename($product_image_url);
                                            }
                                            ?>
                                            <img src="<?php echo htmlspecialchars($product_image_url); ?>" 
                                                 alt="<?php echo htmlspecialchars($review['product_name']); ?>" 
                                                 class="product-thumb me-2" 
                                                 style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;"
                                                 onerror="this.style.display='none';">
                                        <?php endif; ?>
                                        <div>
                                            <strong><?php echo htmlspecialchars($review['product_name'] ?? 'N/A'); ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($review['user_name'] ?? 'N/A'); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($review['user_email'] ?? ''); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="rating-display">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                        <span class="ms-1">(<?php echo $review['rating']; ?>)</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="review-comment" style="max-width: 300px;">
                                        <?php echo htmlspecialchars(mb_substr($review['comment'], 0, 100)); ?>
                                        <?php if (mb_strlen($review['comment']) > 100): ?>...<?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $status_badges = [
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'rejected' => 'danger'
                                    ];
                                    $badge_class = $status_badges[$review['status']] ?? 'secondary';
                                    echo '<span class="badge-admin badge-' . $badge_class . '">' . ucfirst($review['status']) . '</span>';
                                    ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn-admin btn-admin-primary btn-admin-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#reviewModal<?php echo $review['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($review['status'] != 'approved'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" name="update_review" class="btn-admin btn-admin-success btn-admin-sm" 
                                                        onclick="return confirm('Duyệt đánh giá này?');">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($review['status'] != 'rejected'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" name="update_review" class="btn-admin btn-admin-danger btn-admin-sm" 
                                                        onclick="return confirm('Từ chối đánh giá này?');">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <a href="?delete=<?php echo $review['id']; ?>" 
                                           class="btn-admin btn-admin-danger btn-admin-sm"
                                           onclick="return confirm('Xóa đánh giá này?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Review Modal -->
                            <div class="modal fade" id="reviewModal<?php echo $review['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Chi tiết đánh giá #<?php echo $review['id']; ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <strong>Sản phẩm:</strong><br>
                                                    <?php echo htmlspecialchars($review['product_name'] ?? 'N/A'); ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Người dùng:</strong><br>
                                                    <?php echo htmlspecialchars($review['user_name'] ?? 'N/A'); ?><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($review['user_email'] ?? ''); ?></small>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Đánh giá:</strong><br>
                                                <div class="rating-display">
                                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>" style="font-size: 20px;"></i>
                                                    <?php endfor; ?>
                                                    <span class="ms-2">(<?php echo $review['rating']; ?>/5)</span>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Nội dung:</strong><br>
                                                <div class="p-3 bg-light rounded">
                                                    <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <strong>Trạng thái:</strong><br>
                                                    <?php 
                                                    $badge_class = $status_badges[$review['status']] ?? 'secondary';
                                                    echo '<span class="badge-admin badge-' . $badge_class . '">' . ucfirst($review['status']) . '</span>';
                                                    ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Ngày tạo:</strong><br>
                                                    <?php echo date('d/m/Y H:i:s', strtotime($review['created_at'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" name="update_review" class="btn-admin btn-admin-success">
                                                    <i class="fas fa-check"></i> Duyệt
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" name="update_review" class="btn-admin btn-admin-danger">
                                                    <i class="fas fa-times"></i> Từ chối
                                                </button>
                                            </form>
                                            <button type="button" class="btn-admin btn-admin-secondary" data-bs-dismiss="modal">Đóng</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&rating=<?php echo $rating_filter; ?>&search=<?php echo urlencode($search); ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&rating=<?php echo $rating_filter; ?>&search=<?php echo urlencode($search); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&rating=<?php echo $rating_filter; ?>&search=<?php echo urlencode($search); ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<style>
.rating-display {
    display: flex;
    align-items: center;
}

.review-comment {
    word-wrap: break-word;
}

.product-thumb {
    border: 1px solid #dee2e6;
}
</style>

<?php include 'includes/admin_footer.php'; ?>


