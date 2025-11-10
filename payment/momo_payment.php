<?php
require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_GET['order_id'])) {
    header('Location: ' . SITE_URL);
    exit();
}

$order_id = (int)$_GET['order_id'];

$database = new Database();
$db = $database->getConnection();

// Lấy thông tin đơn hàng
$query = "SELECT * FROM orders WHERE id = :id AND user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $order_id);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: ' . SITE_URL);
    exit();
}

// Thông tin thanh toán MoMo
$endpoint = MOMO_ENDPOINT;
$partnerCode = MOMO_PARTNER_CODE;
$accessKey = MOMO_ACCESS_KEY;
$secretKey = MOMO_SECRET_KEY;
$orderInfo = "Thanh toán đơn hàng " . $order['order_code'];
$amount = (string)$order['total_amount'];
$orderId = $order['order_code'] . '_' . time();
$redirectUrl = MOMO_RETURN_URL;
$ipnUrl = MOMO_NOTIFY_URL;
$extraData = "";
$requestId = time() . "";
$requestType = "captureWallet";

// Tạo chữ ký
$rawHash = "accessKey=" . $accessKey . 
           "&amount=" . $amount . 
           "&extraData=" . $extraData . 
           "&ipnUrl=" . $ipnUrl . 
           "&orderId=" . $orderId . 
           "&orderInfo=" . $orderInfo . 
           "&partnerCode=" . $partnerCode . 
           "&redirectUrl=" . $redirectUrl . 
           "&requestId=" . $requestId . 
           "&requestType=" . $requestType;

$signature = hash_hmac("sha256", $rawHash, $secretKey);

$data = array(
    'partnerCode' => $partnerCode,
    'partnerName' => SITE_NAME,
    'storeId' => $partnerCode,
    'requestId' => $requestId,
    'amount' => $amount,
    'orderId' => $orderId,
    'orderInfo' => $orderInfo,
    'redirectUrl' => $redirectUrl,
    'ipnUrl' => $ipnUrl,
    'lang' => 'vi',
    'extraData' => $extraData,
    'requestType' => $requestType,
    'signature' => $signature
);

$result = execPostRequest($endpoint, json_encode($data));
$jsonResult = json_decode($result, true);

// Lưu transaction_id vào database
if (isset($jsonResult['orderId'])) {
    $query = "UPDATE orders SET transaction_id = :transaction_id WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':transaction_id', $jsonResult['orderId']);
    $stmt->bindParam(':id', $order_id);
    $stmt->execute();
}

// Redirect đến trang thanh toán MoMo
if (isset($jsonResult['payUrl'])) {
    header('Location: ' . $jsonResult['payUrl']);
    exit();
} else {
    echo "Lỗi kết nối đến MoMo. Vui lòng thử lại!";
}

function execPostRequest($url, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data))
    );
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
?>
