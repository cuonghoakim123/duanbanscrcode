<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Nhận dữ liệu từ MoMo
$partnerCode = $_GET['partnerCode'] ?? '';
$orderId = $_GET['orderId'] ?? '';
$requestId = $_GET['requestId'] ?? '';
$amount = $_GET['amount'] ?? 0;
$orderInfo = $_GET['orderInfo'] ?? '';
$orderType = $_GET['orderType'] ?? '';
$transId = $_GET['transId'] ?? '';
$resultCode = $_GET['resultCode'] ?? '';
$message = $_GET['message'] ?? '';
$payType = $_GET['payType'] ?? '';
$responseTime = $_GET['responseTime'] ?? '';
$extraData = $_GET['extraData'] ?? '';
$signature = $_GET['signature'] ?? '';

$database = new Database();
$db = $database->getConnection();

// Xác thực chữ ký
$secretKey = MOMO_SECRET_KEY;
$accessKey = MOMO_ACCESS_KEY;

$rawHash = "accessKey=" . $accessKey . 
           "&amount=" . $amount . 
           "&extraData=" . $extraData . 
           "&message=" . $message . 
           "&orderId=" . $orderId . 
           "&orderInfo=" . $orderInfo . 
           "&orderType=" . $orderType . 
           "&partnerCode=" . $partnerCode . 
           "&payType=" . $payType . 
           "&requestId=" . $requestId . 
           "&responseTime=" . $responseTime . 
           "&resultCode=" . $resultCode . 
           "&transId=" . $transId;

$checkSignature = hash_hmac("sha256", $rawHash, $secretKey);

if ($signature == $checkSignature) {
    // Lấy order_code từ orderId
    $order_code_parts = explode('_', $orderId);
    $order_code = $order_code_parts[0];
    
    // Cập nhật trạng thái đơn hàng
    if ($resultCode == 0) {
        // Thanh toán thành công
        $query = "UPDATE orders SET payment_status = 'paid', order_status = 'confirmed', transaction_id = :transaction_id 
                  WHERE order_code = :order_code";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':transaction_id', $transId);
        $stmt->bindParam(':order_code', $order_code);
        $stmt->execute();
        
        header('Location: ' . SITE_URL . '/order_success.php?order_code=' . $order_code);
    } else {
        // Thanh toán thất bại
        $query = "UPDATE orders SET payment_status = 'failed' WHERE order_code = :order_code";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':order_code', $order_code);
        $stmt->execute();
        
        header('Location: ' . SITE_URL . '/order_failed.php?message=' . urlencode($message));
    }
} else {
    echo "Chữ ký không hợp lệ!";
}
?>
