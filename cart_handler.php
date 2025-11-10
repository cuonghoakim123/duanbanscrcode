<?php
require_once 'config/config.php';
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
    exit();
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

switch($action) {
    case 'add':
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        
        // Kiểm tra sản phẩm tồn tại và còn hàng
        $query = "SELECT quantity FROM products WHERE id = :id AND status = 'active'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $product_id);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại!']);
            exit();
        }
        
        if ($product['quantity'] < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Không đủ số lượng!']);
            exit();
        }
        
        // Kiểm tra sản phẩm đã có trong giỏ hàng chưa
        $query = "SELECT * FROM carts WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Cập nhật số lượng
            $query = "UPDATE carts SET quantity = quantity + :quantity WHERE user_id = :user_id AND product_id = :product_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
        } else {
            // Thêm mới
            $query = "INSERT INTO carts (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->execute();
        }
        
        echo json_encode(['success' => true, 'message' => 'Đã thêm vào giỏ hàng!']);
        break;
        
    case 'update':
        $cart_id = (int)$_POST['cart_id'];
        $quantity = (int)$_POST['quantity'];
        
        if ($quantity <= 0) {
            // Xóa sản phẩm
            $query = "DELETE FROM carts WHERE id = :id AND user_id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $cart_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
        } else {
            // Cập nhật số lượng
            $query = "UPDATE carts SET quantity = :quantity WHERE id = :id AND user_id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':id', $cart_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
        }
        
        echo json_encode(['success' => true]);
        break;
        
    case 'remove':
        $cart_id = (int)$_POST['cart_id'];
        
        $query = "DELETE FROM carts WHERE id = :id AND user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $cart_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        echo json_encode(['success' => true]);
        break;
        
    case 'count':
        $query = "SELECT SUM(quantity) as total FROM carts WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'count' => $result['total'] ?? 0]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
