<?php
session_start();
header('Content-Type: application/json');
require_once '../config/config.php';
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Lấy tham số
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
$current_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

try {
    // Lấy reviews mới nhất
    // Hiển thị cả reviews approved và reviews pending của chính user đó
    if ($last_id > 0) {
        // Lấy reviews mới hơn last_id
        if ($current_user_id > 0) {
            // Nếu đã đăng nhập: lấy cả approved và pending của chính user
            $reviews_query = "SELECT r.*, u.fullname as user_name, u.avatar as user_avatar, 
                             p.name as product_name, p.image as product_image
                             FROM reviews r
                             LEFT JOIN users u ON r.user_id = u.id
                             LEFT JOIN products p ON r.product_id = p.id
                             WHERE (r.status = 'approved' OR (r.status = 'pending' AND r.user_id = :current_user_id)) 
                             AND r.id > :last_id
                             ORDER BY r.created_at DESC
                             LIMIT :limit";
            $stmt = $db->prepare($reviews_query);
            $stmt->bindValue(':last_id', $last_id, PDO::PARAM_INT);
            $stmt->bindValue(':current_user_id', $current_user_id, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        } else {
            // Chưa đăng nhập: chỉ lấy approved
            $reviews_query = "SELECT r.*, u.fullname as user_name, u.avatar as user_avatar, 
                             p.name as product_name, p.image as product_image
                             FROM reviews r
                             LEFT JOIN users u ON r.user_id = u.id
                             LEFT JOIN products p ON r.product_id = p.id
                             WHERE r.status = 'approved' AND r.id > :last_id
                             ORDER BY r.created_at DESC
                             LIMIT :limit";
            $stmt = $db->prepare($reviews_query);
            $stmt->bindValue(':last_id', $last_id, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
    } else {
        // Lấy tất cả reviews
        if ($current_user_id > 0) {
            // Nếu đã đăng nhập: lấy cả approved và pending của chính user
            $reviews_query = "SELECT r.*, u.fullname as user_name, u.avatar as user_avatar, 
                             p.name as product_name, p.image as product_image
                             FROM reviews r
                             LEFT JOIN users u ON r.user_id = u.id
                             LEFT JOIN products p ON r.product_id = p.id
                             WHERE r.status = 'approved' OR (r.status = 'pending' AND r.user_id = :current_user_id)
                             ORDER BY r.created_at DESC
                             LIMIT :limit";
            $stmt = $db->prepare($reviews_query);
            $stmt->bindValue(':current_user_id', $current_user_id, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        } else {
            // Chưa đăng nhập: chỉ lấy approved
            $reviews_query = "SELECT r.*, u.fullname as user_name, u.avatar as user_avatar, 
                             p.name as product_name, p.image as product_image
                             FROM reviews r
                             LEFT JOIN users u ON r.user_id = u.id
                             LEFT JOIN products p ON r.product_id = p.id
                             WHERE r.status = 'approved'
                             ORDER BY r.created_at DESC
                             LIMIT :limit";
            $stmt = $db->prepare($reviews_query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
    }
    
    $stmt->execute();
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Tính thống kê
    $stats_query = "SELECT 
                    COUNT(*) as total_reviews,
                    AVG(rating) as avg_rating
                    FROM reviews 
                    WHERE status = 'approved'";
    $stats_stmt = $db->prepare($stats_query);
    $stats_stmt->execute();
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Format reviews
    $formatted_reviews = [];
    foreach ($reviews as $review) {
        // Xử lý avatar URL
        $user_avatar = null;
        if (!empty($review['user_avatar'])) {
            $user_avatar = $review['user_avatar'];
            // Nếu là relative path, thêm SITE_URL
            if (!preg_match('/^https?:\/\//', $user_avatar) && !preg_match('/^\//', $user_avatar)) {
                $user_avatar = SITE_URL . '/' . ltrim($user_avatar, '/');
            }
        }
        
        $formatted_reviews[] = [
            'id' => (int)$review['id'],
            'user_name' => $review['user_name'] ?? 'Khách hàng',
            'user_avatar' => $user_avatar,
            'rating' => (int)$review['rating'],
            'comment' => $review['comment'] ?? '',
            'product_name' => $review['product_name'] ?? null,
            'status' => $review['status'] ?? 'approved',
            'created_at' => $review['created_at'],
            'created_at_formatted' => date('d/m/Y', strtotime($review['created_at']))
        ];
    }
    
    // Format stats
    $total_reviews = (int)($stats['total_reviews'] ?? 0);
    $avg_rating = $total_reviews > 0 ? round((float)($stats['avg_rating'] ?? 0), 1) : 0;
    
    echo json_encode([
        'success' => true,
        'reviews' => $formatted_reviews,
        'stats' => [
            'total_reviews' => $total_reviews,
            'avg_rating' => $avg_rating
        ],
        'has_new' => count($formatted_reviews) > 0
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lấy dữ liệu đánh giá',
        'error' => $e->getMessage()
    ]);
}
?>

