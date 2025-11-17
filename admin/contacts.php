<?php
require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit();
}

$title = 'Quản lý liên hệ';
$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';

// Kiểm tra xem bảng contacts có tồn tại không
try {
    $test_query = "SELECT 1 FROM contacts LIMIT 1";
    $db->query($test_query);
} catch (PDOException $e) {
    $error = 'Bảng contacts chưa được tạo. Vui lòng chạy file database/contacts_table.sql';
    include 'includes/admin_header.php';
    echo '<div class="admin-card"><div class="admin-card-body"><div class="alert alert-danger">' . $error . '</div></div></div>';
    include 'includes/admin_footer.php';
    exit();
}

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_contact'])) {
    $contact_id = (int)$_POST['contact_id'];
    $status = $_POST['status'];
    $admin_note = $_POST['admin_note'] ?? '';
    
    $query = "UPDATE contacts SET status = :status, admin_note = :admin_note WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':admin_note', $admin_note);
    $stmt->bindParam(':id', $contact_id);
    
    if ($stmt->execute()) {
        $success = 'Cập nhật trạng thái liên hệ thành công!';
    } else {
        $error = 'Có lỗi xảy ra!';
    }
}

// Xử lý xóa
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $query = "DELETE FROM contacts WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        $success = 'Xóa liên hệ thành công!';
    } else {
        $error = 'Có lỗi xảy ra!';
    }
}

// Lọc
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "status = :status";
    $params[':status'] = $status_filter;
}

if ($search) {
    $where_conditions[] = "(name LIKE :search OR email LIKE :search OR phone LIKE :search OR subject LIKE :search OR message LIKE :search)";
    $params[':search'] = "%$search%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Đếm tổng
$count_query = "SELECT COUNT(*) as total FROM contacts $where_clause";
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total / $limit);

// Lấy danh sách contacts
$query = "SELECT * FROM contacts 
          $where_clause
          ORDER BY created_at DESC 
          LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/admin_header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h5><i class="fas fa-envelope"></i> Quản lý liên hệ</h5>
        <div class="d-flex gap-2">
            <a href="?status=pending" class="btn-admin btn-admin-warning btn-admin-sm">
                <i class="fas fa-clock"></i> Chờ xử lý
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
        
        <?php if ($error && !strpos($error, 'Bảng contacts')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- Filter Form -->
        <form method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                        <option value="read" <?php echo $status_filter == 'read' ? 'selected' : ''; ?>>Đã đọc</option>
                        <option value="replied" <?php echo $status_filter == 'replied' ? 'selected' : ''; ?>>Đã trả lời</option>
                        <option value="archived" <?php echo $status_filter == 'archived' ? 'selected' : ''; ?>>Đã lưu trữ</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn-admin btn-admin-primary w-100">
                        <i class="fas fa-search"></i> Lọc
                    </button>
                </div>
                <div class="col-md-4 text-end">
                    <span class="text-muted">Tổng: <?php echo $total; ?> liên hệ</span>
                </div>
            </div>
        </form>
        
        <!-- Contacts Table -->
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Số điện thoại</th>
                        <th>Chủ đề</th>
                        <th>Tin nhắn</th>
                        <th>Trạng thái</th>
                        <th>Ngày gửi</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($contacts)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-envelope"></i>
                                    <h4>Chưa có liên hệ</h4>
                                    <p>Chưa có liên hệ nào trong hệ thống</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($contacts as $contact): ?>
                            <tr>
                                <td><?php echo $contact['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($contact['name']); ?></strong></td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>">
                                        <?php echo htmlspecialchars($contact['email']); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if ($contact['phone']): ?>
                                        <a href="tel:<?php echo htmlspecialchars($contact['phone']); ?>">
                                            <?php echo htmlspecialchars($contact['phone']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($contact['subject'] ?? '-'); ?></td>
                                <td>
                                    <div class="contact-message" style="max-width: 300px;">
                                        <?php echo htmlspecialchars(safe_substr($contact['message'], 0, 100)); ?>
                                        <?php if (strlen($contact['message']) > 100): ?>...<?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $status_badges = [
                                        'pending' => 'warning',
                                        'read' => 'info',
                                        'replied' => 'success',
                                        'archived' => 'secondary'
                                    ];
                                    $badge_class = $status_badges[$contact['status']] ?? 'secondary';
                                    echo '<span class="badge-admin badge-' . $badge_class . '">' . ucfirst($contact['status']) . '</span>';
                                    ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn-admin btn-admin-primary btn-admin-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#contactModal<?php echo $contact['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="?delete=<?php echo $contact['id']; ?>" 
                                           class="btn-admin btn-admin-danger btn-admin-sm"
                                           onclick="return confirm('Xóa liên hệ này?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Contact Modal -->
                            <div class="modal fade" id="contactModal<?php echo $contact['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Chi tiết liên hệ #<?php echo $contact['id']; ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST">
                                                <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                                                
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <strong>Họ và tên:</strong><br>
                                                        <?php echo htmlspecialchars($contact['name']); ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <strong>Email:</strong><br>
                                                        <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>">
                                                            <?php echo htmlspecialchars($contact['email']); ?>
                                                        </a>
                                                    </div>
                                                </div>
                                                
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <strong>Số điện thoại:</strong><br>
                                                        <?php if ($contact['phone']): ?>
                                                            <a href="tel:<?php echo htmlspecialchars($contact['phone']); ?>">
                                                                <?php echo htmlspecialchars($contact['phone']); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <strong>Chủ đề:</strong><br>
                                                        <?php echo htmlspecialchars($contact['subject'] ?? '-'); ?>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <strong>Tin nhắn:</strong><br>
                                                    <div class="p-3 bg-light rounded">
                                                        <?php echo nl2br(htmlspecialchars($contact['message'])); ?>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label"><strong>Trạng thái:</strong></label>
                                                    <select name="status" class="form-select">
                                                        <option value="pending" <?php echo $contact['status'] == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                                                        <option value="read" <?php echo $contact['status'] == 'read' ? 'selected' : ''; ?>>Đã đọc</option>
                                                        <option value="replied" <?php echo $contact['status'] == 'replied' ? 'selected' : ''; ?>>Đã trả lời</option>
                                                        <option value="archived" <?php echo $contact['status'] == 'archived' ? 'selected' : ''; ?>>Đã lưu trữ</option>
                                                    </select>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label"><strong>Ghi chú (Admin):</strong></label>
                                                    <textarea name="admin_note" class="form-control" rows="3" placeholder="Ghi chú của admin..."><?php echo htmlspecialchars($contact['admin_note'] ?? ''); ?></textarea>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <strong>Ngày gửi:</strong><br>
                                                    <?php echo date('d/m/Y H:i:s', strtotime($contact['created_at'])); ?>
                                                </div>
                                                
                                                <div class="modal-footer">
                                                    <button type="submit" name="update_contact" class="btn-admin btn-admin-primary">
                                                        <i class="fas fa-save"></i> Cập nhật
                                                    </button>
                                                    <button type="button" class="btn-admin btn-admin-secondary" data-bs-dismiss="modal">Đóng</button>
                                                </div>
                                            </form>
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
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>">
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
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>">
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
.contact-message {
    word-wrap: break-word;
}
</style>

<?php include 'includes/admin_footer.php'; ?>


