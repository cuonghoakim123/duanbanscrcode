<?php
session_start();
header('Content-Type: application/json');
require_once '../config/config.php';
require_once '../config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để đánh giá!'
    ]);
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Lấy dữ liệu từ POST
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    $data = $_POST;
}

$rating = isset($data['rating']) ? (int)$data['rating'] : 0;
$comment = isset($data['comment']) ? trim($data['comment']) : '';
$product_id = isset($data['product_id']) && !empty($data['product_id']) ? (int)$data['product_id'] : null;
$user_id = $_SESSION['user_id'];

// Validate
if ($rating < 1 || $rating > 5) {
    echo json_encode([
        'success' => false,
        'message' => 'Mức đánh giá không hợp lệ!'
    ]);
    exit;
}

if (empty($comment) || strlen($comment) < 10) {
    echo json_encode([
        'success' => false,
        'message' => 'Nhận xét phải có ít nhất 10 ký tự!'
    ]);
    exit;
}

// Kiểm tra xem user đã đánh giá sản phẩm này chưa (nếu có product_id)
if ($product_id) {
    $check_query = "SELECT id FROM reviews WHERE user_id = :user_id AND product_id = :product_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $check_stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Bạn đã đánh giá sản phẩm này rồi!'
        ]);
        exit;
    }
    
    // Kiểm tra product có tồn tại không
    $product_check = "SELECT id FROM products WHERE id = :product_id AND status = 'active'";
    $product_stmt = $db->prepare($product_check);
    $product_stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $product_stmt->execute();
    
    if ($product_stmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Sản phẩm không tồn tại!'
        ]);
        exit;
    }
}

try {
    // Nếu không có product_id, lấy product đầu tiên hoặc để NULL
    if (!$product_id) {
        // Lấy product đầu tiên từ danh sách active
        $default_product = "SELECT id FROM products WHERE status = 'active' LIMIT 1";
        $default_stmt = $db->prepare($default_product);
        $default_stmt->execute();
        $default_result = $default_stmt->fetch(PDO::FETCH_ASSOC);
        if ($default_result) {
            $product_id = $default_result['id'];
        }
    }
    
    // Insert review (product_id có thể NULL)
    if ($product_id) {
        $insert_query = "INSERT INTO reviews (product_id, user_id, rating, comment, status) 
                         VALUES (:product_id, :user_id, :rating, :comment, 'pending')";
        $stmt = $db->prepare($insert_query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    } else {
        // Nếu không có product nào, vẫn cho phép đánh giá chung về dịch vụ
        // Cần đảm bảo database cho phép product_id NULL
        $insert_query = "INSERT INTO reviews (user_id, rating, comment, status) 
                         VALUES (:user_id, :rating, :comment, 'pending')";
        $stmt = $db->prepare($insert_query);
    }
    
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
    $stmt->bindParam(':comment', $comment);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Đánh giá của bạn đã được gửi thành công! Đang chờ được duyệt.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Có lỗi xảy ra khi lưu đánh giá!'
        ]);
    }
    
} catch (PDOException $e) {
    // Kiểm tra nếu lỗi do product_id NOT NULL constraint
    if (strpos($e->getMessage(), 'product_id') !== false) {
        // Thử lại với product_id mặc định
        try {
            $default_product = "SELECT id FROM products WHERE status = 'active' LIMIT 1";
            $default_stmt = $db->prepare($default_product);
            $default_stmt->execute();
            $default_result = $default_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($default_result) {
                $product_id = $default_result['id'];
                $insert_query = "INSERT INTO reviews (product_id, user_id, rating, comment, status) 
                                 VALUES (:product_id, :user_id, :rating, :comment, 'pending')";
                $stmt = $db->prepare($insert_query);
                $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
                $stmt->bindParam(':comment', $comment);
                
                if ($stmt->execute()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Đánh giá của bạn đã được gửi thành công! Đang chờ được duyệt.'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Có lỗi xảy ra khi lưu đánh giá!'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Không tìm thấy sản phẩm để đánh giá. Vui lòng chọn sản phẩm cụ thể.'
                ]);
            }
        } catch (PDOException $e2) {
            echo json_encode([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e2->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
        ]);
    }
}
?>

