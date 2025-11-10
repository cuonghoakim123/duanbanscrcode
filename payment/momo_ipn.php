<?php
require_once '../config/config.php';
require_once '../config/database.php';

// IPN (Instant Payment Notification) từ MoMo
$result = file_get_contents('php://input');
$data = json_decode($result, true);

if ($data) {
    $partnerCode = $data['partnerCode'] ?? '';
    $orderId = $data['orderId'] ?? '';
    $requestId = $data['requestId'] ?? '';
    $amount = $data['amount'] ?? 0;
    $orderInfo = $data['orderInfo'] ?? '';
    $orderType = $data['orderType'] ?? '';
    $transId = $data['transId'] ?? '';
    $resultCode = $data['resultCode'] ?? '';
    $message = $data['message'] ?? '';
    $payType = $data['payType'] ?? '';
    $responseTime = $data['responseTime'] ?? '';
    $extraData = $data['extraData'] ?? '';
    $signature = $data['signature'] ?? '';
    
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
            $query = "UPDATE orders SET payment_status = 'paid', order_status = 'confirmed', transaction_id = :transaction_id 
                      WHERE order_code = :order_code";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':transaction_id', $transId);
            $stmt->bindParam(':order_code', $order_code);
            $stmt->execute();
        } else {
            $query = "UPDATE orders SET payment_status = 'failed' WHERE order_code = :order_code";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':order_code', $order_code);
            $stmt->execute();
        }
        
        // Trả về kết quả cho MoMo
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'No data received']);
}
?>
