<?php
require_once '../config/config.php';
require_once '../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Khởi tạo database
$database = new Database();
$db = $database->getConnection();

$input = json_decode(file_get_contents('php://input'), true);
$message = $input['message'] ?? '';
$session_id = $input['session_id'] ?? '';
$quick_reply_id = $input['quick_reply_id'] ?? null;

// Nếu là quick reply, không cần message
if (!$quick_reply_id && empty($message)) {
    echo json_encode(['error' => 'Message is required']);
    exit;
}

// Tạo hoặc lấy session_id
if (empty($session_id)) {
    // Ưu tiên lấy từ session, nếu không có thì tạo mới
    if (!isset($_SESSION['chatbot_session_id'])) {
        $_SESSION['chatbot_session_id'] = 'chat_' . time() . '_' . uniqid('', true);
    }
    $session_id = $_SESSION['chatbot_session_id'];
} else {
    // Lưu session_id từ client vào session
    $_SESSION['chatbot_session_id'] = $session_id;
}

// Lấy user_id nếu đã đăng nhập
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Tạo hoặc cập nhật chat session
try {
    $stmt = $db->prepare("SELECT id, user_id FROM chat_sessions WHERE session_id = ?");
    $stmt->execute([$session_id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$session) {
        // Tạo session mới
        $stmt = $db->prepare("INSERT INTO chat_sessions (session_id, user_id, ip_address, user_agent) VALUES (?, ?, ?, ?)");
        $stmt->execute([$session_id, $user_id, $ip_address, $user_agent]);
    } else {
        // Cập nhật session nếu user đăng nhập
        if ($user_id && !$session['user_id']) {
            $stmt = $db->prepare("UPDATE chat_sessions SET user_id = ? WHERE session_id = ?");
            $stmt->execute([$user_id, $session_id]);
        }
    }
} catch (PDOException $e) {
    // Nếu bảng chưa tồn tại, tiếp tục mà không lưu vào DB
    error_log("Chatbot DB Error: " . $e->getMessage());
}

// Kiểm tra xem có quick reply phù hợp không
$quick_reply_answer = null;
try {
    $stmt = $db->prepare("SELECT answer FROM quick_replies WHERE question LIKE ? AND status = 'active' LIMIT 1");
    $search_query = '%' . $message . '%';
    $stmt->execute([$search_query]);
    $quick_reply = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($quick_reply) {
        $quick_reply_answer = $quick_reply['answer'];
        // Tăng click count
        $stmt = $db->prepare("UPDATE quick_replies SET click_count = click_count + 1 WHERE question LIKE ? AND status = 'active' LIMIT 1");
        $stmt->execute([$search_query]);
    }
} catch (PDOException $e) {
    error_log("Chatbot DB Error: " . $e->getMessage());
}

// Lấy lịch sử chat gần đây (10 tin nhắn cuối) để context tốt hơn - LẤY TRƯỚC KHI INSERT MESSAGE MỚI
$chat_history = [];
try {
    $stmt = $db->prepare("SELECT message_type, message, ai_response FROM chat_messages WHERE session_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$session_id]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $chat_history = array_reverse($history); // Đảo ngược để có thứ tự từ cũ đến mới
} catch (PDOException $e) {
    error_log("Chatbot DB Error: " . $e->getMessage());
}

// Lấy thống kê dự án để thêm vào context
$project_stats = [];
try {
    $stmt = $db->prepare("SELECT stat_key, stat_value, description FROM project_statistics WHERE status = 'active' ORDER BY display_order ASC");
    $stmt->execute();
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($stats as $stat) {
        $project_stats[$stat['stat_key']] = $stat['stat_value'];
    }
} catch (PDOException $e) {
    error_log("Chatbot DB Error: " . $e->getMessage());
}

// Nếu là quick reply, lấy answer từ database và trả về ngay (XỬ LÝ TRƯỚC)
if ($quick_reply_id) {
    // Tạo hoặc lấy session_id
    if (empty($session_id)) {
        if (!isset($_SESSION['chatbot_session_id'])) {
            $_SESSION['chatbot_session_id'] = 'chat_' . time() . '_' . uniqid('', true);
        }
        $session_id = $_SESSION['chatbot_session_id'];
    } else {
        $_SESSION['chatbot_session_id'] = $session_id;
    }
    
    // Lấy user_id nếu đã đăng nhập
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Tạo hoặc cập nhật chat session
    try {
        $stmt = $db->prepare("SELECT id, user_id FROM chat_sessions WHERE session_id = ?");
        $stmt->execute([$session_id]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$session) {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $stmt = $db->prepare("INSERT INTO chat_sessions (session_id, user_id, ip_address, user_agent) VALUES (?, ?, ?, ?)");
            $stmt->execute([$session_id, $user_id, $ip_address, $user_agent]);
        } else if ($user_id && !$session['user_id']) {
            $stmt = $db->prepare("UPDATE chat_sessions SET user_id = ? WHERE session_id = ?");
            $stmt->execute([$user_id, $session_id]);
        }
    } catch (PDOException $e) {
        error_log("Chatbot DB Error: " . $e->getMessage());
    }
    
    try {
        $stmt = $db->prepare("SELECT answer, question FROM quick_replies WHERE id = ? AND status = 'active'");
        $stmt->execute([$quick_reply_id]);
        $quick_reply = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($quick_reply) {
            // Lưu message của user
            $stmt = $db->prepare("INSERT INTO chat_messages (session_id, user_id, message_type, message) VALUES (?, ?, 'user', ?)");
            $stmt->execute([$session_id, $user_id, $quick_reply['question']]);
            
            // Lưu response của bot
            $stmt = $db->prepare("INSERT INTO chat_messages (session_id, user_id, message_type, message, ai_response) VALUES (?, ?, 'bot', ?, ?)");
            $stmt->execute([$session_id, $user_id, $quick_reply['question'], $quick_reply['answer']]);
            
            // Tăng click count
            $stmt = $db->prepare("UPDATE quick_replies SET click_count = click_count + 1 WHERE id = ?");
            $stmt->execute([$quick_reply_id]);
            
            echo json_encode([
                'success' => true,
                'message' => $quick_reply['answer'],
                'session_id' => $session_id,
                'source' => 'quick_reply'
            ]);
            exit;
        } else {
            echo json_encode([
                'error' => 'Quick reply not found',
                'message' => 'Câu trả lời không tồn tại. Vui lòng thử lại.'
            ]);
            exit;
        }
    } catch (PDOException $e) {
        error_log("Chatbot DB Error: " . $e->getMessage());
        echo json_encode([
            'error' => 'Database error',
            'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau.'
        ]);
        exit;
    }
}

// Lưu message của user vào database - SAU KHI ĐÃ LẤY LỊCH SỬ
try {
    $stmt = $db->prepare("INSERT INTO chat_messages (session_id, user_id, message_type, message) VALUES (?, ?, 'user', ?)");
    $stmt->execute([$session_id, $user_id, $message]);
    $user_message_id = $db->lastInsertId();
} catch (PDOException $e) {
    error_log("Chatbot DB Error: " . $e->getMessage());
    $user_message_id = null;
}

// System prompt cho chatbot tư vấn website với thống kê từ database
$stats_text = "";
if (!empty($project_stats)) {
    $stats_text = "\n\nThống kê dự án DiamondDev Việt Nam:\n";
    if (isset($project_stats['total_projects'])) $stats_text .= "- Tổng số dự án: " . $project_stats['total_projects'] . "\n";
    if (isset($project_stats['active_websites'])) $stats_text .= "- Website đang hoạt động: " . $project_stats['active_websites'] . "\n";
    if (isset($project_stats['happy_clients'])) $stats_text .= "- Khách hàng hài lòng: " . $project_stats['happy_clients'] . "\n";
    if (isset($project_stats['starting_price'])) $stats_text .= "- Giá khởi điểm: " . $project_stats['starting_price'] . "\n";
    if (isset($project_stats['completion_time'])) $stats_text .= "- Thời gian hoàn thành: " . $project_stats['completion_time'] . "\n";
    if (isset($project_stats['response_time'])) $stats_text .= "- Hỗ trợ: " . $project_stats['response_time'] . "\n";
    if (isset($project_stats['warranty'])) $stats_text .= "- Bảo hành: " . $project_stats['warranty'] . "\n";
}

$systemPrompt = "Bạn là một chatbot tư vấn chuyên nghiệp của DiamondDev Việt Nam - công ty chuyên thiết kế website bán hàng. 
Nhiệm vụ của bạn là tư vấn cho khách hàng về dịch vụ thiết kế website:
- Giá cả: Từ 2-4 triệu cho website bán hàng chuyên nghiệp
- Thời gian: Khoảng 15 ngày để hoàn thiện
- Tính năng: Responsive, SEO, SSL, Google Analytics, thanh toán trực tuyến
- Hỗ trợ: Từ A đến Z, không cần biết code
- Liên hệ: 0356-012250 hoặc 0355 999 141
- Email: cuonghotran17022004@gmail.com" . $stats_text . "

Hãy trả lời một cách thân thiện, chuyên nghiệp và hữu ích. Nếu khách hỏi về thông tin không liên quan, hãy nhẹ nhàng hướng họ về dịch vụ thiết kế website.";

// Xây dựng context từ lịch sử chat
$context = $systemPrompt . "\n\n";
if (!empty($chat_history)) {
    $context .= "Lịch sử trò chuyện trước đó:\n";
    foreach ($chat_history as $hist) {
        if ($hist['message_type'] === 'user') {
            $context .= "Khách hàng: " . $hist['message'] . "\n";
        } else {
            $context .= "Bạn: " . ($hist['ai_response'] ?? $hist['message']) . "\n";
        }
    }
    $context .= "\n";
}

// Nếu có quick reply phù hợp, ưu tiên sử dụng nó (KHÔNG CẦN GỌI AI)
if ($quick_reply_answer) {
    $reply = $quick_reply_answer;
    
    // Lưu response vào database
    try {
        $stmt = $db->prepare("INSERT INTO chat_messages (session_id, user_id, message_type, message, ai_response) VALUES (?, ?, 'bot', ?, ?)");
        $stmt->execute([$session_id, $user_id, $message, $reply]);
    } catch (PDOException $e) {
        error_log("Chatbot DB Error: " . $e->getMessage());
    }
    
    echo json_encode([
        'success' => true,
        'message' => $reply,
        'session_id' => $session_id,
        'source' => 'quick_reply'
    ]);
    exit;
}

// Nếu không có quick reply, gọi AI
$fullMessage = $context . "Khách hàng hỏi: " . $message;

// Gọi API Gemini
$apiKey = GEMINI_API_KEY;
$apiUrl = GEMINI_API_URL . '?key=' . $apiKey;

$data = [
    'contents' => [
        [
            'parts' => [
                [
                    'text' => $fullMessage
                ]
            ]
        ]
    ]
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode([
        'error' => 'API Error',
        'message' => 'Không thể kết nối với dịch vụ. Vui lòng thử lại sau.',
        'debug' => $response
    ]);
    exit;
}

$result = json_decode($response, true);

if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    $reply = trim($result['candidates'][0]['content']['parts'][0]['text']);
    
    // Lưu response của AI vào database
    try {
        $stmt = $db->prepare("INSERT INTO chat_messages (session_id, user_id, message_type, message, ai_response) VALUES (?, ?, 'bot', ?, ?)");
        $stmt->execute([$session_id, $user_id, $message, $reply]);
    } catch (PDOException $e) {
        error_log("Chatbot DB Error: " . $e->getMessage());
    }
    
    echo json_encode([
        'success' => true,
        'message' => $reply,
        'session_id' => $session_id,
        'source' => 'ai'
    ]);
} else {
    $error_message = 'Xin lỗi, tôi không thể trả lời câu hỏi này. Vui lòng liên hệ trực tiếp: 0356-012250';
    
    // Lưu error response vào database
    try {
        $stmt = $db->prepare("INSERT INTO chat_messages (session_id, user_id, message_type, message, ai_response) VALUES (?, ?, 'bot', ?, ?)");
        $stmt->execute([$session_id, $user_id, $message, $error_message]);
    } catch (PDOException $e) {
        error_log("Chatbot DB Error: " . $e->getMessage());
    }
    
    echo json_encode([
        'error' => 'Invalid response',
        'message' => $error_message,
        'session_id' => $session_id
    ]);
}
?>

