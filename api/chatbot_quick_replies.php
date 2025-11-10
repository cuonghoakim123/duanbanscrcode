<?php
require_once '../config/config.php';
require_once '../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$database = new Database();
$db = $database->getConnection();

// Lấy danh sách quick replies
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $category = $_GET['category'] ?? null;
        
        $query = "SELECT id, question, answer, category, icon, display_order 
                  FROM quick_replies 
                  WHERE status = 'active'";
        
        $params = [];
        if ($category) {
            $query .= " AND category = ?";
            $params[] = $category;
        }
        
        $query .= " ORDER BY display_order ASC, id ASC";
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $replies = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $replies
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Database error',
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Xử lý click vào quick reply
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $reply_id = $input['reply_id'] ?? null;
    $question = $input['question'] ?? '';
    
    if (!$reply_id && empty($question)) {
        echo json_encode(['error' => 'Reply ID or question is required']);
        exit;
    }
    
    try {
        // Tăng click count
        if ($reply_id) {
            $stmt = $db->prepare("UPDATE quick_replies SET click_count = click_count + 1 WHERE id = ?");
            $stmt->execute([$reply_id]);
            
            // Lấy answer
            $stmt = $db->prepare("SELECT answer, question FROM quick_replies WHERE id = ?");
            $stmt->execute([$reply_id]);
            $reply = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($reply) {
                echo json_encode([
                    'success' => true,
                    'question' => $reply['question'],
                    'answer' => $reply['answer']
                ]);
            } else {
                echo json_encode(['error' => 'Reply not found']);
            }
        } else {
            // Tìm quick reply theo question
            $stmt = $db->prepare("SELECT id, answer FROM quick_replies WHERE question LIKE ? AND status = 'active' LIMIT 1");
            $stmt->execute(['%' . $question . '%']);
            $reply = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($reply) {
                // Tăng click count
                $stmt = $db->prepare("UPDATE quick_replies SET click_count = click_count + 1 WHERE id = ?");
                $stmt->execute([$reply['id']]);
                
                echo json_encode([
                    'success' => true,
                    'answer' => $reply['answer']
                ]);
            } else {
                echo json_encode(['error' => 'No matching reply found']);
            }
        }
    } catch (PDOException $e) {
        echo json_encode([
            'error' => 'Database error',
            'message' => $e->getMessage()
        ]);
    }
    exit;
}
?>

