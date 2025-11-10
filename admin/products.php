<?php
require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

$title = 'Quản lý sản phẩm';
$database = new Database();
$db = $database->getConnection();

// Xử lý xóa sản phẩm
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $query = "DELETE FROM products WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    header('Location: products.php?msg=deleted');
    exit();
}

// Lấy chi tiết sản phẩm để xem
$view_product = null;
if (isset($_GET['view'])) {
    $view_id = (int)$_GET['view'];
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $view_id);
    $stmt->execute();
    $view_product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($view_product && $view_product['gallery']) {
        $view_product['gallery_images'] = array_filter(array_map('trim', explode(',', $view_product['gallery'])));
    }
}

// Chỉ lấy danh sách sản phẩm nếu không đang xem chi tiết
$products = [];
$total_pages = 0;
if (!$view_product) {
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          ORDER BY p.created_at DESC 
          LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Đếm tổng sản phẩm
$query = "SELECT COUNT(*) as total FROM products";
$stmt = $db->prepare($query);
$stmt->execute();
$total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total / $limit);
}

include 'includes/admin_header.php';
?>

<?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
        $messages = [
            'added' => 'Thêm sản phẩm thành công!',
            'updated' => 'Cập nhật sản phẩm thành công!',
            'deleted' => 'Xóa sản phẩm thành công!'
        ];
        echo $messages[$_GET['msg']] ?? 'Thao tác thành công!';
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($view_product): ?>
    <!-- Product Detail View -->
    <div class="admin-card mb-4">
        <div class="admin-card-header">
            <h5><i class="fas fa-eye"></i> Chi tiết sản phẩm: <?php echo htmlspecialchars($view_product['name']); ?></h5>
            <div class="d-flex gap-2">
                <a href="product_edit.php?id=<?php echo $view_product['id']; ?>" class="btn-admin btn-admin-primary btn-admin-sm">
                    <i class="fas fa-edit"></i> Sửa
                </a>
                <a href="products.php" class="btn-admin btn-admin-sm" style="background: #e2e8f0; color: var(--admin-text);">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-4">
                        <h6 class="mb-3">Hình ảnh đại diện</h6>
                        <?php if($view_product['image']): ?>
                            <img src="<?php echo htmlspecialchars($view_product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($view_product['name']); ?>" 
                                 class="img-fluid rounded" 
                                 style="max-width: 100%; border: 2px solid var(--admin-border);">
                        <?php else: ?>
                            <div style="width: 100%; height: 300px; background: #f8f9fa; border: 2px dashed var(--admin-border); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if(!empty($view_product['gallery_images'])): ?>
                        <div>
                            <h6 class="mb-3">Thư viện ảnh</h6>
                            <div class="row g-2">
                                <?php foreach($view_product['gallery_images'] as $gallery_img): ?>
                                    <div class="col-6">
                                        <img src="<?php echo htmlspecialchars($gallery_img); ?>" 
                                             alt="Gallery" 
                                             class="img-fluid rounded" 
                                             style="width: 100%; height: 100px; object-fit: cover; border: 1px solid var(--admin-border);">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="col-md-8">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td><?php echo $view_product['id']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>SKU:</strong></td>
                                    <td><?php echo htmlspecialchars($view_product['sku']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Danh mục:</strong></td>
                                    <td><?php echo htmlspecialchars($view_product['category_name'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Slug:</strong></td>
                                    <td><code><?php echo htmlspecialchars($view_product['slug']); ?></code></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <td><strong>Giá:</strong></td>
                                    <td><strong style="color: var(--admin-danger); font-size: 18px;"><?php echo number_format($view_product['price']); ?>đ</strong></td>
                                </tr>
                                <?php if($view_product['sale_price']): ?>
                                    <tr>
                                        <td><strong>Giá KM:</strong></td>
                                        <td><strong style="color: var(--admin-success); font-size: 18px;"><?php echo number_format($view_product['sale_price']); ?>đ</strong></td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <td><strong>Số lượng:</strong></td>
                                    <td>
                                        <span class="badge-admin badge-<?php echo $view_product['quantity'] > 0 ? 'success' : 'danger'; ?>">
                                            <?php echo number_format($view_product['quantity']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Lượt xem:</strong></td>
                                    <td><?php echo number_format($view_product['views']); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="mb-2">Trạng thái</h6>
                        <div class="d-flex gap-2">
                            <span class="badge-admin badge-<?php echo $view_product['status'] == 'active' ? 'success' : 'danger'; ?>">
                                <?php echo $view_product['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động'; ?>
                            </span>
                            <?php if($view_product['featured']): ?>
                                <span class="badge-admin badge-warning">
                                    <i class="fas fa-star"></i> Nổi bật
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if($view_product['description']): ?>
                        <div class="mb-4">
                            <h6 class="mb-2">Mô tả ngắn</h6>
                            <p><?php echo nl2br(htmlspecialchars($view_product['description'])); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($view_product['content']): ?>
                        <div class="mb-4">
                            <h6 class="mb-2">Nội dung chi tiết</h6>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid var(--admin-border);">
                                <?php echo $view_product['content']; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-4">
                        <table class="table table-bordered">
                            <tr>
                                <td><strong>Ngày tạo:</strong></td>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($view_product['created_at'])); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Ngày cập nhật:</strong></td>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($view_product['updated_at'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>

<div class="admin-card mb-4">
    <div class="admin-card-header">
        <h5><i class="fas fa-box"></i> Danh sách sản phẩm</h5>
        <a href="product_add.php" class="btn-admin btn-admin-primary btn-admin-sm">
            <i class="fas fa-plus"></i> Thêm sản phẩm mới
        </a>
    </div>
    <div class="admin-card-body">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hình ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Giá KM</th>
                        <th>Số lượng</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($products)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-box"></i>
                                    <h4>Chưa có sản phẩm</h4>
                                    <p>Hãy thêm sản phẩm đầu tiên</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                    <?php foreach($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                    <img src="<?php echo $product['image'] ?: (function_exists('getProductPlaceholder') ? getProductPlaceholder(80, 80) : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiBmaWxsPSIjZTllY2VmIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxMiIgZmlsbD0iIzY5NzU3ZCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkltYWdlPC90ZXh0Pjwvc3ZnPg=='); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;"
                                     loading="lazy">
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($product['name']); ?></strong><br>
                                    <small class="text-muted">SKU: <?php echo htmlspecialchars($product['sku']); ?></small>
                            </td>
                                <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                                <td><strong><?php echo number_format($product['price']); ?>đ</strong></td>
                                <td>
                                    <?php if($product['sale_price']): ?>
                                        <strong style="color: var(--admin-danger);"><?php echo number_format($product['sale_price']); ?>đ</strong>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                            </td>
                            <td>
                                    <span class="badge-admin badge-<?php echo $product['quantity'] > 0 ? 'success' : 'danger'; ?>">
                                    <?php echo $product['quantity']; ?>
                                </span>
                            </td>
                            <td>
                                    <span class="badge-admin badge-<?php echo $product['status'] == 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo $product['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động'; ?>
                                </span>
                            </td>
                            <td>
                                    <div class="d-flex gap-2">
                                        <a href="products.php?view=<?php echo $product['id']; ?>" 
                                           class="btn-admin btn-admin-sm" style="background: #4299e1; color: white;" title="Xem">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                <a href="product_edit.php?id=<?php echo $product['id']; ?>" 
                                           class="btn-admin btn-admin-primary btn-admin-sm" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete=<?php echo $product['id']; ?>" 
                                           class="btn-admin btn-admin-danger btn-admin-sm" 
                                   onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')" 
                                   title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </a>
                                    </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if($total_pages > 1): ?>
            <nav class="pagination-admin">
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" 
                       class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/admin_footer.php'; ?>
