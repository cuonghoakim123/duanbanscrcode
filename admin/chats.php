<?php
require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$title = 'Quản lý Chat';
$database = new Database();
$db = $database->getConnection();

// Lấy session_id từ URL
$selected_session = $_GET['session_id'] ?? '';

// Lấy danh sách sessions với thông tin user
try {
    $query = "SELECT ac.session_id, 
                     MAX(ac.created_at) as last_message,
                     COUNT(*) as message_count,
                     SUM(CASE WHEN ac.sender_type = 'user' AND ac.is_read = 0 THEN 1 ELSE 0 END) as unread_count,
                     MAX(ac.user_id) as user_id
              FROM admin_chats ac
              GROUP BY ac.session_id
              ORDER BY last_message DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Lấy thông tin user cho mỗi session
    foreach ($sessions as &$session) {
        // Set default values
        $session['user_name'] = 'Khách hàng';
        $session['user_email'] = '';
        $session['user_avatar'] = '';
        $session['user_phone'] = '';
        
        if ($session['user_id']) {
            try {
                $user_query = "SELECT fullname, email, avatar, phone FROM users WHERE id = :user_id";
                $user_stmt = $db->prepare($user_query);
                $user_stmt->bindParam(':user_id', $session['user_id']);
                $user_stmt->execute();
                $user_info = $user_stmt->fetch(PDO::FETCH_ASSOC);
                if ($user_info) {
                    $session['user_name'] = $user_info['fullname'] ?? 'Khách hàng';
                    $session['user_email'] = $user_info['email'] ?? '';
                    $session['user_avatar'] = $user_info['avatar'] ?? '';
                    $session['user_phone'] = $user_info['phone'] ?? '';
                }
            } catch (PDOException $e) {
                error_log('Error fetching user info: ' . $e->getMessage());
            }
        }
    }
    unset($session);
} catch (PDOException $e) {
    $sessions = [];
}

// Lấy tin nhắn của session được chọn và thông tin user
$messages = [];
$chat_user = null;
if ($selected_session) {
    try {
        // Lấy thông tin user của session này
        $query = "SELECT DISTINCT ac.user_id, u.fullname, u.email, u.avatar, u.phone
                  FROM admin_chats ac
                  LEFT JOIN users u ON ac.user_id = u.id
                  WHERE ac.session_id = :session_id AND ac.user_id IS NOT NULL
                  LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':session_id', $selected_session);
        $stmt->execute();
        $chat_user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Lấy tin nhắn
        $query = "SELECT * FROM admin_chats 
                  WHERE session_id = :session_id 
                  ORDER BY created_at ASC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':session_id', $selected_session);
        $stmt->execute();
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Đánh dấu đã đọc
        $query = "UPDATE admin_chats SET is_read = 1 WHERE session_id = :session_id AND sender_type = 'user'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':session_id', $selected_session);
        $stmt->execute();
    } catch (PDOException $e) {
        $messages = [];
    }
}

include 'includes/admin_header.php';
?>

<div class="zalo-chat-container">
    <div class="zalo-chat-wrapper">
        <!-- Sidebar bên trái -->
        <div class="zalo-sidebar">
            <!-- Header sidebar -->
            <div class="zalo-sidebar-header">
                <div class="zalo-sidebar-title">
                    <h4><i class="fas fa-comments"></i> Trò chuyện</h4>
                </div>
                <div class="zalo-sidebar-actions">
                    <button class="zalo-icon-btn" title="Thêm bạn">
                        <i class="fas fa-user-plus"></i>
                    </button>
                    <button class="zalo-icon-btn" title="Cài đặt">
                        <i class="fas fa-cog"></i>
                    </button>
        </div>
    </div>
    
            <!-- Search bar -->
            <div class="zalo-search-container">
                <div class="zalo-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="zalo-search-input" placeholder="Tìm kiếm" id="searchChats">
                </div>
            </div>
            
            <!-- Tabs -->
            <div class="zalo-tabs">
                <button class="zalo-tab active" data-tab="all">
                    <span>Tất cả</span>
                </button>
                <button class="zalo-tab" data-tab="unread">
                    <span>Chưa đọc</span>
                    <?php 
                    $total_unread = array_sum(array_column($sessions, 'unread_count'));
                    if ($total_unread > 0): 
                    ?>
                        <span class="zalo-badge"><?php echo $total_unread; ?></span>
                    <?php endif; ?>
                </button>
            </div>
            
            <!-- Danh sách chat -->
            <div class="zalo-chat-list" id="chatList">
                    <?php if (empty($sessions)): ?>
                    <div class="zalo-empty-state">
                        <i class="fas fa-comments"></i>
                        <p>Chưa có cuộc trò chuyện nào</p>
                        </div>
                    <?php else: ?>
                    <?php 
                    // Lấy tin nhắn cuối cùng cho mỗi session
                    foreach ($sessions as &$session) {
                        try {
                            $last_msg_query = "SELECT message FROM admin_chats WHERE session_id = :session_id ORDER BY created_at DESC LIMIT 1";
                            $last_stmt = $db->prepare($last_msg_query);
                            $last_stmt->bindParam(':session_id', $session['session_id']);
                            $last_stmt->execute();
                            $last_msg = $last_stmt->fetch(PDO::FETCH_ASSOC);
                            $session['last_message_text'] = $last_msg['message'] ?? '';
                        } catch (PDOException $e) {
                            $session['last_message_text'] = '';
                        }
                    }
                    unset($session);
                    
                    foreach ($sessions as $session): 
                        $user_name = $session['user_name'] ?? 'Khách hàng';
                        $user_avatar = $session['user_avatar'] ?? '';
                        $avatar_url = '';
                        
                        // Debug: Log avatar info
                        if (isset($_GET['debug']) && $_GET['debug'] == '1') {
                            error_log("User: $user_name, Avatar raw: " . var_export($user_avatar, true));
                        }
                        
                        // Xử lý avatar URL - cải thiện logic
                        if (!empty($user_avatar) && $user_avatar !== 'null' && trim($user_avatar) !== '') {
                            $user_avatar = trim($user_avatar);
                            
                            // Nếu là URL đầy đủ (http/https) - giữ nguyên
                            if (preg_match('/^https?:\/\//', $user_avatar)) {
                                $avatar_url = $user_avatar;
                            }
                            // Nếu bắt đầu bằng /uploads/
                            elseif (preg_match('/^\/uploads\//', $user_avatar)) {
                                $avatar_url = SITE_URL . $user_avatar;
                            }
                            // Nếu chứa uploads/ nhưng không bắt đầu bằng /
                            elseif (strpos($user_avatar, 'uploads/') !== false || strpos($user_avatar, 'uploads\\') !== false) {
                                // Normalize path
                                $user_avatar = str_replace('\\', '/', $user_avatar);
                                if (strpos($user_avatar, SITE_URL) === false) {
                                    $avatar_url = SITE_URL . '/' . ltrim($user_avatar, '/');
                                } else {
                                    $avatar_url = $user_avatar;
                                }
                            }
                            // Nếu chỉ là tên file hoặc đường dẫn tương đối
                            else {
                                // Thử nhiều đường dẫn có thể
                                $possible_paths = [
                                    __DIR__ . '/../uploads/users/' . basename($user_avatar),
                                    __DIR__ . '/../' . $user_avatar,
                                    __DIR__ . '/../uploads/' . basename($user_avatar)
                                ];
                                
                                foreach ($possible_paths as $path) {
                                    if (file_exists($path)) {
                                        $relative_path = str_replace(__DIR__ . '/../', '', $path);
                                        $avatar_url = SITE_URL . '/' . ltrim(str_replace('\\', '/', $relative_path), '/');
                                        break;
                                    }
                                }
                                
                                // Nếu không tìm thấy file, thử dùng trực tiếp
                                if (empty($avatar_url)) {
                                    $avatar_url = SITE_URL . '/uploads/users/' . basename($user_avatar);
                                }
                            }
                            
                            // Debug: Log final avatar URL
                            if (isset($_GET['debug']) && $_GET['debug'] == '1') {
                                error_log("Final avatar URL: $avatar_url");
                            }
                        }
                        
                        $initials = strtoupper(substr($user_name, 0, 2));
                        
                        // Tính thời gian tương đối
                        $last_time = strtotime($session['last_message']);
                        $time_diff = time() - $last_time;
                        if ($time_diff < 60) {
                            $time_text = 'Vừa xong';
                        } elseif ($time_diff < 3600) {
                            $time_text = floor($time_diff / 60) . ' phút';
                        } elseif ($time_diff < 86400) {
                            $time_text = floor($time_diff / 3600) . ' giờ';
                        } elseif ($time_diff < 604800) {
                            $time_text = floor($time_diff / 86400) . ' ngày';
                        } else {
                            $time_text = date('d/m/Y', $last_time);
                        }
                        
                        $last_msg_text = mb_substr($session['last_message_text'] ?? '', 0, 50);
                        if (mb_strlen($session['last_message_text'] ?? '') > 50) {
                            $last_msg_text .= '...';
                        }
                    ?>
                        <a href="chats.php?session_id=<?php echo urlencode($session['session_id']); ?>" 
                           class="zalo-chat-item <?php echo $selected_session === $session['session_id'] ? 'active' : ''; ?>"
                           data-unread="<?php echo $session['unread_count']; ?>">
                            <div class="zalo-chat-avatar">
                                <?php if (!empty($avatar_url)): ?>
                                    <img src="<?php echo htmlspecialchars($avatar_url); ?>" 
                                         alt="<?php echo htmlspecialchars($user_name); ?>"
                                         class="zalo-avatar-img"
                                         data-avatar-url="<?php echo htmlspecialchars($avatar_url); ?>"
                                         onerror="this.onerror=null; console.error('Avatar load error:', this.src); this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <?php endif; ?>
                                <div class="zalo-avatar-placeholder" style="display: <?php echo !empty($avatar_url) ? 'none' : 'flex'; ?>;">
                                    <?php echo $initials; ?>
                                    </div>
                                <!-- Online status indicator -->
                                <div class="zalo-online-status"></div>
                                    <?php if ($session['unread_count'] > 0): ?>
                                    <span class="zalo-unread-badge"><?php echo $session['unread_count']; ?></span>
                                    <?php endif; ?>
                                </div>
                            <div class="zalo-chat-info">
                                <div class="zalo-chat-header">
                                    <span class="zalo-chat-name"><?php echo htmlspecialchars($user_name); ?></span>
                                    <span class="zalo-chat-time"><?php echo $time_text; ?></span>
                                </div>
                                <div class="zalo-chat-preview">
                                    <span class="zalo-chat-message"><?php echo htmlspecialchars($last_msg_text ?: 'Chưa có tin nhắn'); ?></span>
                                </div>
                            </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
        </div>
        
        <!-- Chat window bên phải -->
        <div class="zalo-chat-window">
            <?php if ($selected_session): 
                $chat_user_name = $chat_user['fullname'] ?? 'Khách hàng';
                $chat_user_email = $chat_user['email'] ?? '';
                $chat_user_phone = $chat_user['phone'] ?? '';
                $chat_user_avatar = $chat_user['avatar'] ?? '';
                $chat_avatar_url = '';
                
                // Xử lý avatar URL cho chat header
                if (!empty($chat_user_avatar) && $chat_user_avatar !== 'null') {
                    $chat_user_avatar = trim($chat_user_avatar);
                    
                    // Nếu là URL đầy đủ (http/https)
                    if (preg_match('/^https?:\/\//', $chat_user_avatar)) {
                        $chat_avatar_url = $chat_user_avatar;
                    }
                    // Nếu bắt đầu bằng /uploads/
                    elseif (preg_match('/^\/uploads\//', $chat_user_avatar)) {
                        $chat_avatar_url = SITE_URL . $chat_user_avatar;
                    }
                    // Nếu chứa uploads/ nhưng không bắt đầu bằng /
                    elseif (strpos($chat_user_avatar, 'uploads/') !== false) {
                        if (strpos($chat_user_avatar, SITE_URL) === false) {
                            $chat_avatar_url = SITE_URL . '/' . ltrim($chat_user_avatar, '/');
                        } else {
                            $chat_avatar_url = $chat_user_avatar;
                        }
                    }
                    // Nếu chỉ là tên file, kiểm tra trong uploads/users
                    else {
                        $possible_path = __DIR__ . '/../uploads/users/' . basename($chat_user_avatar);
                        if (file_exists($possible_path)) {
                            $chat_avatar_url = SITE_URL . '/uploads/users/' . basename($chat_user_avatar);
                        }
                    }
                }
                
                $chat_initials = strtoupper(substr($chat_user_name, 0, 2));
            ?>
                <!-- Chat Header -->
                <div class="zalo-chat-header">
                    <div class="zalo-chat-header-left">
                        <div class="zalo-chat-header-avatar">
                            <?php if (!empty($chat_avatar_url)): ?>
                                <img src="<?php echo htmlspecialchars($chat_avatar_url); ?>" 
                                     alt="<?php echo htmlspecialchars($chat_user_name); ?>"
                                     class="zalo-avatar-img"
                                     data-avatar-url="<?php echo htmlspecialchars($chat_avatar_url); ?>"
                                     onerror="this.onerror=null; console.error('Avatar load error:', this.src); this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <?php endif; ?>
                            <div class="zalo-avatar-placeholder" style="display: <?php echo !empty($chat_avatar_url) ? 'none' : 'flex'; ?>;">
                                <?php echo $chat_initials; ?>
                            </div>
                            <!-- Online status indicator -->
                            <div class="zalo-online-status zalo-online-status-header"></div>
                        </div>
                        <div class="zalo-chat-header-info">
                            <h5><?php echo htmlspecialchars($chat_user_name); ?></h5>
                            <span class="zalo-chat-status">Đang hoạt động</span>
                        </div>
                    </div>
                    <div class="zalo-chat-header-right">
                        <button class="zalo-header-btn" title="Gọi thoại">
                            <i class="fas fa-phone"></i>
                        </button>
                        <button class="zalo-header-btn" title="Gọi video">
                            <i class="fas fa-video"></i>
                        </button>
                        <button class="zalo-header-btn" title="Tìm kiếm">
                            <i class="fas fa-search"></i>
                        </button>
                        <button class="zalo-header-btn" title="Thông tin">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <button class="zalo-header-btn" title="Thêm">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
            </div>
        </div>
        
                <!-- Messages Container -->
                <div class="zalo-messages-container" id="chatMessages">
                    <?php if (empty($messages)): ?>
                        <div class="zalo-empty-chat">
                            <i class="fas fa-comments"></i>
                            <p>Chưa có tin nhắn nào</p>
                    </div>
                    <?php else: ?>
                        <?php 
                        $prev_date = '';
                        foreach ($messages as $msg): 
                            $msg_date = date('Y-m-d', strtotime($msg['created_at']));
                            $show_date = ($msg_date !== $prev_date);
                            $prev_date = $msg_date;
                            $is_user = $msg['sender_type'] === 'user';
                        ?>
                            <?php if ($show_date): ?>
                                <div class="zalo-date-divider">
                                    <span><?php 
                                        $today = date('Y-m-d');
                                        $yesterday = date('Y-m-d', strtotime('-1 day'));
                                        if ($msg_date === $today) {
                                            echo 'Hôm nay';
                                        } elseif ($msg_date === $yesterday) {
                                            echo 'Hôm qua';
                                        } else {
                                            echo date('d/m/Y', strtotime($msg['created_at']));
                                        }
                                    ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="zalo-message-wrapper <?php echo $is_user ? 'zalo-message-received' : 'zalo-message-sent'; ?>" data-message-id="<?php echo $msg['id']; ?>">
                                <div class="zalo-message">
                                    <div class="zalo-message-content">
                                        <?php 
                                        $message_text = $msg['message'];
                                        
                                        // Xử lý message có chứa [IMAGE], [VIDEO], [FILE]
                                        if (preg_match('/\[IMAGE\](.*?)\[\/IMAGE\]/', $message_text, $matches)) {
                                            $image_url = $matches[1];
                                            echo '<img src="' . htmlspecialchars($image_url) . '" style="max-width: 300px; max-height: 300px; border-radius: 8px; display: block; margin: 5px 0;">';
                                        } elseif (preg_match('/\[VIDEO\](.*?)\[\/VIDEO\]/', $message_text, $matches)) {
                                            $video_url = $matches[1];
                                            echo '<div style="padding: 10px; background: rgba(255,255,255,0.1); border-radius: 8px; margin: 5px 0;">
                                                    <i class="fas fa-video" style="margin-right: 8px;"></i>
                                                    <a href="' . htmlspecialchars($video_url) . '" target="_blank" style="color: inherit;">Video</a>
                                                  </div>';
                                        } elseif (preg_match('/\[FILE\](.*?)\|(.*?)\[\/FILE\]/', $message_text, $matches)) {
                                            $file_url = $matches[1];
                                            $file_name = $matches[2];
                                            echo '<div style="padding: 10px; background: rgba(255,255,255,0.1); border-radius: 8px; margin: 5px 0;">
                                                    <i class="fas fa-file" style="margin-right: 8px;"></i>
                                                    <a href="' . htmlspecialchars($file_url) . '" target="_blank" style="color: inherit; text-decoration: none;">
                                                        <strong>' . htmlspecialchars($file_name) . '</strong>
                                                    </a>
                                                  </div>';
                                        } else {
                                            // Giữ nguyên emoji, chỉ escape HTML tags nguy hiểm
                                            $message_text = str_replace(['<', '>'], ['&lt;', '&gt;'], $message_text);
                                            $message_text = nl2br($message_text);
                                            echo $message_text;
                                        }
                                        ?>
                                    </div>
                                    <div class="zalo-message-time">
                                        <?php echo date('H:i', strtotime($msg['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </div>
                
                <!-- Input Container -->
                <div class="zalo-input-container">
                        <form id="replyForm" onsubmit="sendAdminReply(event)">
                        <div class="zalo-input-wrapper">
                            <button type="button" class="zalo-input-btn" title="Đính kèm file" onclick="toggleAttachMenu()">
                                <i class="fas fa-plus"></i>
                            </button>
                            <!-- Attach Menu -->
                            <div class="zalo-attach-menu" id="attachMenu">
                                <button type="button" class="zalo-attach-item" onclick="uploadImage()">
                                    <i class="fas fa-image"></i>
                                    <span>Ảnh/Video</span>
                                </button>
                                <button type="button" class="zalo-attach-item" onclick="uploadFile()">
                                    <i class="fas fa-file"></i>
                                    <span>File</span>
                                </button>
                                <button type="button" class="zalo-attach-item" onclick="shareLocation()">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>Vị trí</span>
                                </button>
                                <button type="button" class="zalo-attach-item" onclick="createPoll()">
                                    <i class="fas fa-poll"></i>
                                    <span>Poll</span>
                                </button>
                            </div>
                                <input type="text" 
                                   class="zalo-input" 
                                       id="replyMessage" 
                                   placeholder="Nhập @, tin nhắn tới <?php echo htmlspecialchars($chat_user_name); ?>"
                                   autocomplete="off"
                                       required>
                            <button type="button" class="zalo-input-btn" title="Emoji" onclick="toggleEmojiPicker()">
                                <i class="far fa-smile"></i>
                            </button>
                            <button type="submit" class="zalo-send-btn" title="Gửi">
                                <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        <!-- Emoji Picker -->
                        <div class="zalo-emoji-picker" id="emojiPicker">
                            <div class="zalo-emoji-tabs">
                                <button class="zalo-emoji-tab active" data-tab="emoji">
                                    <span>EMOJI</span>
                                </button>
                                <button class="zalo-emoji-tab" data-tab="sticker">
                                    <span>STICKER</span>
                                </button>
                    </div>
                            <div class="zalo-emoji-content" id="emojiContent">
                                <!-- Emoji categories will be loaded here -->
                            </div>
                            <div class="zalo-emoji-categories">
                                <button class="zalo-emoji-category active" data-category="recent" title="Gần đây">
                                    <i class="fas fa-clock"></i>
                                </button>
                                <button class="zalo-emoji-category" data-category="emotions" title="Cảm xúc">
                                    <i class="far fa-smile"></i>
                                </button>
                                <button class="zalo-emoji-category" data-category="gestures" title="Cử chỉ">
                                    <i class="fas fa-hand-paper"></i>
                                </button>
                                <button class="zalo-emoji-category" data-category="people" title="Người">
                                    <i class="fas fa-user"></i>
                                </button>
                                <button class="zalo-emoji-category" data-category="animals" title="Động vật">
                                    <i class="fas fa-paw"></i>
                                </button>
                                <button class="zalo-emoji-category" data-category="food" title="Đồ ăn">
                                    <i class="fas fa-utensils"></i>
                                </button>
                                <button class="zalo-emoji-category" data-category="travel" title="Du lịch">
                                    <i class="fas fa-plane"></i>
                                </button>
                                <button class="zalo-emoji-category" data-category="activities" title="Hoạt động">
                                    <i class="fas fa-futbol"></i>
                                </button>
                                <button class="zalo-emoji-category" data-category="objects" title="Đồ vật">
                                    <i class="fas fa-lightbulb"></i>
                                </button>
                                <button class="zalo-emoji-category" data-category="symbols" title="Ký hiệu">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                        </div>
                        <!-- Hidden file inputs -->
                        <input type="file" id="imageUpload" accept="image/*,video/*" style="display: none;" multiple>
                        <input type="file" id="fileUpload" style="display: none;" multiple>
                    </form>
                </div>
            <?php else: ?>
                <div class="zalo-empty-window">
                    <div class="zalo-empty-content">
                        <i class="fas fa-comments"></i>
                        <h4>Chọn một cuộc trò chuyện</h4>
                        <p>Chọn một cuộc trò chuyện từ danh sách bên trái để bắt đầu chat với khách hàng</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const currentSessionId = '<?php echo htmlspecialchars($selected_session); ?>';

// Đảm bảo các link có thể click được
document.addEventListener('DOMContentLoaded', function() {
    // Sửa lại selector cho đúng với class mới
    const chatLinks = document.querySelectorAll('.zalo-chat-item');
    chatLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Đảm bảo click hoạt động
            const href = this.getAttribute('href');
            if (href && href !== '#') {
                // Remove active từ tất cả items
                document.querySelectorAll('.zalo-chat-item').forEach(item => {
                    item.classList.remove('active');
                });
                // Add active cho item được click
                this.classList.add('active');
                
                // Navigate đến URL
                window.location.href = href;
            }
        });
    });
    
    // Scroll to bottom của chat messages
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        // Scroll to bottom với smooth behavior
        setTimeout(() => {
            chatMessages.scrollTo({
                top: chatMessages.scrollHeight,
                behavior: 'smooth'
            });
        }, 100);
    }
    
    // Tab filtering
    const tabs = document.querySelectorAll('.zalo-tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const tabType = this.getAttribute('data-tab');
            const chatItems = document.querySelectorAll('.zalo-chat-item');
            
            chatItems.forEach(item => {
                if (tabType === 'unread') {
                    const unread = parseInt(item.getAttribute('data-unread') || '0');
                    item.style.display = unread > 0 ? 'flex' : 'none';
                } else {
                    item.style.display = 'flex';
                }
            });
        });
    });
    
    // Search functionality
    const searchInput = document.getElementById('searchChats');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const chatItems = document.querySelectorAll('.zalo-chat-item');
            
            chatItems.forEach(item => {
                const name = item.querySelector('.zalo-chat-name')?.textContent.toLowerCase() || '';
                const message = item.querySelector('.zalo-chat-message')?.textContent.toLowerCase() || '';
                
                if (name.includes(searchTerm) || message.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
});

function sendAdminReply(event) {
    event.preventDefault();
    
    const messageInput = document.getElementById('replyMessage');
    const message = messageInput.value.trim();
    
    if (!message) {
        alert('Vui lòng nhập tin nhắn!');
        return;
    }
    
    if (!currentSessionId) {
        alert('Vui lòng chọn một cuộc trò chuyện!');
        return;
    }
    
    const form = document.getElementById('replyForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    const chatMessages = document.getElementById('chatMessages');
    
    // Tạo tin nhắn tạm thời với ID tạm
    const tempMessageId = 'temp_' + Date.now();
    const now = new Date();
    const timeStr = now.getHours().toString().padStart(2, '0') + ':' + 
                   now.getMinutes().toString().padStart(2, '0');
    
    // Tạo message wrapper ngay lập tức (optimistic update)
    const messageWrapper = document.createElement('div');
    messageWrapper.className = 'zalo-message-wrapper zalo-message-sent';
    messageWrapper.setAttribute('data-message-id', tempMessageId);
    messageWrapper.setAttribute('data-temp', 'true');
    
    // Format message - giữ nguyên emoji và text
    const messageContent = message.replace(/\n/g, '<br>');
    
    messageWrapper.innerHTML = `
        <div class="zalo-message">
            <div class="zalo-message-content">${messageContent}</div>
            <div class="zalo-message-time">${timeStr}</div>
        </div>
    `;
    
    // Thêm vào UI ngay lập tức
    if (chatMessages) {
        chatMessages.appendChild(messageWrapper);
        
        // Scroll to bottom ngay lập tức
        setTimeout(() => {
            chatMessages.scrollTo({
                top: chatMessages.scrollHeight,
                behavior: 'smooth'
            });
        }, 50);
    }
    
    // Clear input ngay lập tức
    messageInput.value = '';
    messageInput.focus();
    
    // Disable button và show loading
    submitBtn.disabled = true;
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    // Gửi request đến server
    const apiUrl = '../api/admin_chat.php?action=send';
    
    fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            message: message,
            sender_type: 'admin',
            session_id: currentSessionId
        }),
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Cập nhật message ID từ temp sang real ID
            if (messageWrapper && data.chat_id) {
                messageWrapper.setAttribute('data-message-id', data.chat_id);
                messageWrapper.removeAttribute('data-temp');
            }
            
            // Update lastMessageId để polling hoạt động đúng
            if (typeof lastMessageId !== 'undefined') {
                lastMessageId = data.chat_id || parseInt(messageWrapper.getAttribute('data-message-id'));
            }
        } else {
            // Nếu gửi thất bại, xóa tin nhắn tạm
            if (messageWrapper && messageWrapper.parentNode) {
                messageWrapper.remove();
            }
            alert('Lỗi: ' + (data.message || 'Không thể gửi tin nhắn'));
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        // Nếu có lỗi, xóa tin nhắn tạm
        if (messageWrapper && messageWrapper.parentNode) {
            messageWrapper.remove();
        }
        alert('Có lỗi xảy ra khi gửi tin nhắn! Vui lòng thử lại.');
    })
    .finally(() => {
        // Reset button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
}

// Helper function để escape HTML (nhưng giữ emoji)
function escapeHtml(text) {
    // Không escape emoji, chỉ escape HTML tags
    return text.replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

console.log('JavaScript loaded successfully');
console.log('Current Session ID:', currentSessionId);

// Debug avatar URLs
document.addEventListener('DOMContentLoaded', function() {
    const avatarImgs = document.querySelectorAll('.zalo-avatar-img');
    avatarImgs.forEach((img, index) => {
        const url = img.getAttribute('data-avatar-url') || img.src;
        console.log(`Avatar ${index + 1}:`, url);
        
        // Test if image loads
        const testImg = new Image();
        testImg.onload = function() {
            console.log(`Avatar ${index + 1} loaded successfully:`, url);
        };
        testImg.onerror = function() {
            console.error(`Avatar ${index + 1} failed to load:`, url);
        };
        testImg.src = url;
    });
});

// Toggle attach menu
function toggleAttachMenu() {
    const menu = document.getElementById('attachMenu');
    const emojiPicker = document.getElementById('emojiPicker');
    menu.classList.toggle('show');
    if (emojiPicker) emojiPicker.classList.remove('show');
}

// Toggle emoji picker
function toggleEmojiPicker() {
    const emojiPicker = document.getElementById('emojiPicker');
    const attachMenu = document.getElementById('attachMenu');
    if (emojiPicker) {
        emojiPicker.classList.toggle('show');
        if (attachMenu) attachMenu.classList.remove('show');
    }
}

// Close attach menu and emoji picker when clicking outside
document.addEventListener('click', function(e) {
    const menu = document.getElementById('attachMenu');
    const emojiPicker = document.getElementById('emojiPicker');
    const attachBtn = e.target.closest('.zalo-input-btn[onclick="toggleAttachMenu()"]');
    const emojiBtn = e.target.closest('.zalo-input-btn[onclick="toggleEmojiPicker()"]');
    
    if (menu && !menu.contains(e.target) && !attachBtn) {
        menu.classList.remove('show');
    }
    
    if (emojiPicker && !emojiPicker.contains(e.target) && !emojiBtn) {
        emojiPicker.classList.remove('show');
    }
});

// Emoji data
const emojiCategories = {
    emotions: {
        title: 'Cảm xúc',
        emojis: ['😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂', '🙂', '🙃', '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗', '😚', '😙', '😋', '😛', '😜', '🤪', '😝', '🤑', '🤗', '🤭', '🤫', '🤔', '🤐', '🤨', '😐', '😑', '😶', '😏', '😒', '🙄', '😬', '🤥', '😌', '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕', '🤢', '🤮', '🤧', '🥵', '🥶', '😶‍🌫️', '😵', '😵‍💫', '🤯', '🤠', '🥳', '😎', '🤓', '🧐']
    },
    gestures: {
        title: 'Cử chỉ',
        emojis: ['👋', '🤚', '🖐', '✋', '🖖', '👌', '🤌', '🤏', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '🖕', '👇', '☝️', '👍', '👎', '✊', '👊', '🤛', '🤜', '👏', '🙌', '👐', '🤲', '🤝', '🙏', '✍️', '💪', '🦾', '🦿', '🦵', '🦶', '👂', '🦻', '👃', '🧠', '🫀', '🫁', '🦷', '🦴', '👀', '👁', '👅', '👄']
    },
    people: {
        title: 'Người',
        emojis: ['👶', '🧒', '👦', '👧', '🧑', '👱', '👨', '🧔', '👨‍🦰', '👨‍🦱', '👨‍🦳', '👨‍🦲', '👩', '👩‍🦰', '🧑‍🦰', '👩‍🦱', '🧑‍🦱', '👩‍🦳', '🧑‍🦳', '👩‍🦲', '🧑‍🦲', '👱‍♀️', '👱‍♂️', '🧓', '👴', '👵', '🙍', '🙍‍♂️', '🙍‍♀️', '🙎', '🙎‍♂️', '🙎‍♀️', '🙅', '🙅‍♂️', '🙅‍♀️', '🙆', '🙆‍♂️', '🙆‍♀️', '💁', '💁‍♂️', '💁‍♀️', '🙋', '🙋‍♂️', '🙋‍♀️', '🧏', '🧏‍♂️', '🧏‍♀️', '🤦', '🤦‍♂️', '🤦‍♀️', '🤷', '🤷‍♂️', '🤷‍♀️']
    },
    animals: {
        title: 'Động vật',
        emojis: ['🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼', '🐨', '🐯', '🦁', '🐮', '🐷', '🐽', '🐸', '🐵', '🙈', '🙉', '🙊', '🐒', '🐔', '🐧', '🐦', '🐤', '🐣', '🐥', '🦆', '🦅', '🦉', '🦇', '🐺', '🐗', '🐴', '🦄', '🐝', '🐛', '🦋', '🐌', '🐞', '🐜', '🦟', '🦗', '🕷', '🦂', '🐢', '🐍', '🦎', '🦖', '🦕', '🐙', '🦑', '🦐', '🦞', '🦀', '🐡', '🐠', '🐟', '🐬', '🐳', '🐋', '🦈', '🐊', '🐅', '🐆', '🦓', '🦍', '🦧', '🐘', '🦛', '🦏', '🐪', '🐫', '🦒', '🦘', '🦬', '🐃', '🐂', '🐄', '🐎', '🐖', '🐏', '🐑', '🦙', '🐐', '🦌', '🐕', '🐩', '🦮', '🐕‍🦺', '🐈', '🐈‍⬛', '🪶', '🐓', '🦃', '🦤', '🦚', '🦜', '🦢', '🦩', '🕊', '🐇', '🦝', '🦨', '🦡', '🦫', '🦦', '🦥', '🐁', '🐀', '🐿', '🦔']
    },
    food: {
        title: 'Đồ ăn',
        emojis: ['🍏', '🍎', '🍐', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓', '🍈', '🍒', '🍑', '🥭', '🍍', '🥥', '🥝', '🍅', '🍆', '🥑', '🥦', '🥬', '🥒', '🌶', '🌽', '🥕', '🥔', '🍠', '🥐', '🥯', '🍞', '🥖', '🥨', '🧀', '🥚', '🍳', '🥞', '🥓', '🥩', '🍗', '🍖', '🦴', '🌭', '🍔', '🍟', '🍕', '🥪', '🥙', '🌮', '🌯', '🥗', '🥘', '🥫', '🍝', '🍜', '🍲', '🍛', '🍣', '🍱', '🥟', '🦪', '🍤', '🍙', '🍚', '🍘', '🍥', '🥠', '🥮', '🍢', '🍡', '🍧', '🍨', '🍦', '🥧', '🧁', '🍰', '🎂', '🍮', '🍭', '🍬', '🍫', '🍿', '🍩', '🍪', '🌰', '🥜', '🍯', '🥛', '🍼', '☕️', '🍵', '🧃', '🥤', '🍶', '🍺', '🍻', '🥂', '🍷', '🥃', '🍸', '🍹', '🧉', '🍾', '🧊']
    },
    travel: {
        title: 'Du lịch',
        emojis: ['🚗', '🚕', '🚙', '🚌', '🚎', '🏎', '🚓', '🚑', '🚒', '🚐', '🛻', '🚚', '🚛', '🚜', '🏍', '🛵', '🚲', '🛴', '🛹', '🛼', '🚁', '✈️', '🛩', '🛫', '🛬', '🪂', '💺', '🚀', '🛸', '🚂', '🚃', '🚄', '🚅', '🚆', '🚇', '🚈', '🚉', '🚊', '🚝', '🚞', '🚟', '🚠', '🚡', '⛱', '🎢', '🎡', '🎠', '🛝', '🚢', '⛴', '🛥', '🚤', '⛵️', '🛶', '🚣', '🛟', '⛽️', '🚨', '🚥', '🚦', '🛑', '🚧', '⚓️', '⛵️', '🛸', '🛰', '🚀', '🛩', '✈️', '🛫', '🛬', '🪂', '💺', '🚁', '🚟', '🚠', '🚡', '🛬', '🛫', '🛩', '✈️', '🛸', '🚀', '🛰', '🚁', '🪂', '💺', '🚂', '🚃', '🚄', '🚅', '🚆', '🚇', '🚈', '🚉', '🚊', '🚝', '🚞', '🚟', '🚠', '🚡', '⛱', '🎢', '🎡', '🎠', '🛝', '🚢', '⛴', '🛥', '🚤', '⛵️', '🛶', '🚣', '🛟', '⛽️', '🚨', '🚥', '🚦', '🛑', '🚧', '⚓️']
    },
    activities: {
        title: 'Hoạt động',
        emojis: ['⚽️', '🏀', '🏈', '⚾️', '🥎', '🎾', '🏐', '🏉', '🥏', '🎱', '🏓', '🏸', '🏒', '🏑', '🥍', '🏏', '🥅', '⛳️', '🏹', '🎣', '🥊', '🥋', '🎽', '🛹', '🛷', '⛸', '🥌', '🎿', '⛷', '🏂', '🏋️', '🤼', '🤸', '🤺', '🤾', '🏌️', '🏇', '🧘', '🏄', '🏊', '🤽', '🚣', '🧗', '🚵', '🚴', '🏆', '🥇', '🥈', '🥉', '🏅', '🎖', '🏵', '🎗', '🎫', '🎟', '🎪', '🤹', '🎭', '🩰', '🎨', '🎬', '🎤', '🎧', '🎼', '🎹', '🥁', '🎷', '🎺', '🎸', '🪗', '🎻', '🪕', '🎲', '♟', '🎯', '🎳', '🎮', '🎰', '🧩']
    },
    objects: {
        title: 'Đồ vật',
        emojis: ['⌚️', '📱', '📲', '💻', '⌨️', '🖥', '🖨', '🖱', '🖲', '🕹', '🗜', '💾', '💿', '📀', '📼', '📷', '📸', '📹', '🎥', '📽', '🎞', '📞', '☎️', '📟', '📠', '📺', '📻', '🎙', '🎚', '🎛', '⏱', '⏲', '⏰', '🕰', '⌛️', '⏳', '📡', '🔋', '🔌', '💡', '🔦', '🕯', '🧯', '🛢', '💸', '💵', '💴', '💶', '💷', '💰', '💳', '💎', '⚖️', '🪜', '🧰', '🪛', '🔧', '🔨', '⚒', '🛠', '⛏', '🪚', '🔩', '⚙️', '🪤', '🧱', '⛓', '🧲', '🔫', '💣', '🧨', '🪓', '🔪', '🗡', '⚔️', '🛡', '🚬', '⚰️', '🪦', '⚱️', '🏺', '🔮', '📿', '🧿', '💈', '⚗️', '🔭', '🔬', '🕳', '🩹', '🩺', '💊', '💉', '🩸', '🧬', '🦠', '🧫', '🧪', '🌡', '🧹', '🪠', '🧺', '🧻', '🚽', '🚰', '🚿', '🛁', '🛀', '🧼', '🪥', '🪒', '🧽', '🪣', '🧴', '🛎', '🔑', '🗝', '🚪', '🪑', '🪞', '🛋', '🛏', '🛌', '🧸', '🪆', '🖼', '🪞', '🪟', '🛍', '🛒', '🎁', '🎈', '🎉', '🎊', '🎎', '🎏', '🎐', '🪄', '🪅', '🎀', '🎗', '🏷', '🪧']
    },
    symbols: {
        title: 'Ký hiệu',
        emojis: ['❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟', '☮️', '✝️', '☪️', '🕉', '☸️', '✡️', '🔯', '🕎', '☯️', '☦️', '🛐', '⛎', '♈️', '♉️', '♊️', '♋️', '♌️', '♍️', '♎️', '♏️', '♐️', '♑️', '♒️', '♓️', '🆔', '⚛️', '🉑', '☢️', '☣️', '📴', '📳', '🈶', '🈚️', '🈸', '🈺', '🈷️', '✴️', '🆚', '💮', '🉐', '㊙️', '㊗️', '🈴', '🈵', '🈹', '🈲', '🅰️', '🅱️', '🆎', '🆑', '🅾️', '🆘', '❌', '⭕️', '🛑', '⛔️', '📛', '🚫', '💯', '💢', '♨️', '🚷', '🚯', '🚳', '🚱', '🔞', '📵', '🚭', '❗️', '❓', '❕', '❔', '‼️', '⁉️', '🔅', '🔆', '〽️', '⚠️', '🚸', '🔱', '⚜️', '🔰', '♻️', '✅', '🈯️', '💹', '❇️', '✳️', '❎', '🌐', '💠', 'Ⓜ️', '🌀', '💤', '🏧', '🚾', '♿️', '🅿️', '🈳', '🈂️', '🛂', '🛃', '🛄', '🛅', '🚹', '🚺', '🚼', '🚻', '🚮', '🎦', '📶', '🈁', '🔣', 'ℹ️', '🔤', '🔡', '🔠', '🆖', '🆗', '🆙', '🆒', '🆕', '🆓', '0️⃣', '1️⃣', '2️⃣', '3️⃣', '4️⃣', '5️⃣', '6️⃣', '7️⃣', '8️⃣', '9️⃣', '🔟', '🔢', '#️⃣', '*️⃣', '⏏️', '▶️', '⏸', '⏯', '⏹', '⏺', '⏭', '⏮', '⏩', '⏪', '⏫', '⏬', '◀️', '🔼', '🔽', '➡️', '⬅️', '⬆️', '⬇️', '↗️', '↘️', '↙️', '↖️', '↕️', '↔️', '↪️', '↩️', '⤴️', '⤵️', '🔀', '🔁', '🔂', '🔄', '🔃', '🎵', '🎶', '➕', '➖', '➗', '✖️', '💲', '💱', '™️', '©️', '®️', '〰️', '➰', '➿', '🔚', '🔙', '🔛', '🔜', '🔝', '✔️', '☑️', '🔘', '🔴', '🟠', '🟡', '🟢', '🔵', '🟣', '⚫️', '⚪️', '🟤', '🔶', '🔷', '🔸', '🔹', '🔺', '🔻', '💠', '🔘', '🔳', '🔲', '▪️', '▫️', '◾️', '◽️', '◼️', '◻️', '🟥', '🟧', '🟨', '🟩', '🟦', '🟪', '⬛️', '⬜️', '🟫', '🔈', '🔇', '🔉', '🔊', '🔔', '🔕', '📣', '📢', '💬', '💭', '🗯', '♠️', '♣️', '♥️', '♦️', '🃏', '🎴', '🀄️', '🕐', '🕑', '🕒', '🕓', '🕔', '🕕', '🕖', '🕗', '🕘', '🕙', '🕚', '🕛', '🕜', '🕝', '🕞', '🕟', '🕠', '🕡', '🕢', '🕣', '🕤', '🕥', '🕦', '🕧']
    }
};

// Initialize emoji picker
function initEmojiPicker() {
    const emojiContent = document.getElementById('emojiContent');
    if (!emojiContent) return;
    
    // Load default category (emotions)
    loadEmojiCategory('emotions');
    
    // Category buttons
    const categoryButtons = document.querySelectorAll('.zalo-emoji-category');
    categoryButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            categoryButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const category = this.getAttribute('data-category');
            loadEmojiCategory(category);
        });
    });
    
    // Tab switching
    const tabs = document.querySelectorAll('.zalo-emoji-tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            const tabType = this.getAttribute('data-tab');
            if (tabType === 'sticker') {
                emojiContent.innerHTML = '<div style="padding: 20px; text-align: center; color: #8e8e93;">Sticker đang được phát triển</div>';
            } else {
                loadEmojiCategory('emotions');
            }
        });
    });
}

// Load emoji category
function loadEmojiCategory(category) {
    const emojiContent = document.getElementById('emojiContent');
    if (!emojiContent || !emojiCategories[category]) return;
    
    const categoryData = emojiCategories[category];
    let html = `<div class="zalo-emoji-category-title">${categoryData.title}</div>`;
    html += '<div class="zalo-emoji-grid">';
    
    categoryData.emojis.forEach(emoji => {
        html += `<div class="zalo-emoji-item" onclick="insertEmoji('${emoji}')">${emoji}</div>`;
    });
    
    html += '</div>';
    emojiContent.innerHTML = html;
}

// Insert emoji into input
function insertEmoji(emoji) {
    const input = document.getElementById('replyMessage');
    if (input) {
        const cursorPos = input.selectionStart || input.value.length;
        const textBefore = input.value.substring(0, cursorPos);
        const textAfter = input.value.substring(cursorPos);
        input.value = textBefore + emoji + textAfter;
        input.focus();
        
        // Set cursor position after emoji
        const newPos = cursorPos + emoji.length;
        input.setSelectionRange(newPos, newPos);
        
        // Trigger input event để có thể submit bằng Enter
        input.dispatchEvent(new Event('input', { bubbles: true }));
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initEmojiPicker();
});

// Upload image/video function
function uploadImage() {
    const input = document.getElementById('imageUpload');
    if (input) {
        input.click();
        toggleAttachMenu();
    }
}

// Upload file function  
function uploadFile() {
    const input = document.getElementById('fileUpload');
    if (input) {
        input.click();
        toggleAttachMenu();
    }
}

// Share location function
function shareLocation() {
    alert('Tính năng chia sẻ vị trí đang được phát triển');
    toggleAttachMenu();
}

// Create poll function
function createPoll() {
    alert('Tính năng tạo poll đang được phát triển');
    toggleAttachMenu();
}

// Handle file uploads - cải thiện với upload thực tế
const imageUploadInput = document.getElementById('imageUpload');
const fileUploadInput = document.getElementById('fileUpload');

if (imageUploadInput) {
    imageUploadInput.addEventListener('change', function(e) {
        const files = e.target.files;
        if (files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (file.type.startsWith('image/') || file.type.startsWith('video/')) {
                    // Upload và preview file
                    uploadAndSendMediaFile(file);
                } else {
                    alert('Chỉ chấp nhận file ảnh hoặc video');
                }
            }
            // Reset input để có thể chọn lại file cùng tên
            this.value = '';
        }
    });
}

if (fileUploadInput) {
    fileUploadInput.addEventListener('change', function(e) {
        const files = e.target.files;
        if (files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                uploadAndSendFile(file);
            }
            // Reset input
            this.value = '';
        }
    });
}

// Upload và gửi media file thực tế
async function uploadAndSendMediaFile(file) {
    if (!currentSessionId) {
        alert('Vui lòng chọn một cuộc trò chuyện!');
        return;
    }
    
    // Kiểm tra kích thước file (max 20MB)
    if (file.size > 20 * 1024 * 1024) {
        alert('File quá lớn! Kích thước tối đa là 20MB');
        return;
    }
    
    const formData = new FormData();
    formData.append('image', file);
    formData.append('session_id', currentSessionId);
    formData.append('sender_type', 'admin');
    
    const chatMessages = document.getElementById('chatMessages');
    let messageWrapper = null;
    
    // Hiển thị preview ngay lập tức
    const reader = new FileReader();
    reader.onload = function(e) {
        if (chatMessages) {
            messageWrapper = document.createElement('div');
            messageWrapper.className = 'zalo-message-wrapper zalo-message-sent';
            messageWrapper.setAttribute('data-temp', 'true');
            const now = new Date();
            const timeStr = now.getHours().toString().padStart(2, '0') + ':' + 
                           now.getMinutes().toString().padStart(2, '0');
            
            if (file.type.startsWith('image/')) {
                messageWrapper.innerHTML = `
                    <div class="zalo-message">
                        <div class="zalo-message-content">
                            <img src="${e.target.result}" style="max-width: 300px; max-height: 300px; border-radius: 8px; display: block; cursor: pointer;" onclick="window.open(this.src, '_blank')">
                        </div>
                        <div class="zalo-message-time">${timeStr}</div>
                    </div>
                `;
            } else {
                messageWrapper.innerHTML = `
                    <div class="zalo-message">
                        <div class="zalo-message-content">
                            <div style="padding: 10px; background: rgba(255,255,255,0.1); border-radius: 8px;">
                                <i class="fas fa-video" style="margin-right: 8px;"></i>
                                ${file.name}
                                <div style="font-size: 12px; opacity: 0.7; margin-top: 5px;">
                                    ${(file.size / 1024 / 1024).toFixed(2)} MB
                                </div>
                            </div>
                        </div>
                        <div class="zalo-message-time">${timeStr}</div>
                    </div>
                `;
            }
            
            chatMessages.appendChild(messageWrapper);
            setTimeout(() => {
                chatMessages.scrollTo({
                    top: chatMessages.scrollHeight,
                    behavior: 'smooth'
                });
            }, 100);
        }
    };
    reader.readAsDataURL(file);
    
    // Upload file lên server
    try {
        const response = await fetch('../api/admin_chat.php?action=upload', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        
        const data = await response.json();
        if (data.success && messageWrapper) {
            // Cập nhật với URL thật từ server
            if (file.type.startsWith('image/') && data.file_url) {
                const img = messageWrapper.querySelector('img');
                if (img) {
                    img.src = data.file_url;
                }
            }
            // Cập nhật message ID
            if (data.chat_id) {
                messageWrapper.setAttribute('data-message-id', data.chat_id);
                messageWrapper.removeAttribute('data-temp');
            }
        } else {
            if (messageWrapper && messageWrapper.parentNode) {
                messageWrapper.remove();
            }
            alert('Lỗi upload: ' + (data.message || 'Không thể upload file'));
        }
    } catch (error) {
        console.error('Upload error:', error);
        if (messageWrapper && messageWrapper.parentNode) {
            messageWrapper.remove();
        }
        alert('Có lỗi xảy ra khi upload file!');
    }
}

// Upload và gửi file thực tế
async function uploadAndSendFile(file) {
    if (!currentSessionId) {
        alert('Vui lòng chọn một cuộc trò chuyện!');
        return;
    }
    
    // Kiểm tra kích thước file (max 20MB)
    if (file.size > 20 * 1024 * 1024) {
        alert('File quá lớn! Kích thước tối đa là 20MB');
        return;
    }
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('session_id', currentSessionId);
    formData.append('sender_type', 'admin');
    
    const chatMessages = document.getElementById('chatMessages');
    let messageWrapper = null;
    
    // Hiển thị preview ngay lập tức
    if (chatMessages) {
        messageWrapper = document.createElement('div');
        messageWrapper.className = 'zalo-message-wrapper zalo-message-sent';
        messageWrapper.setAttribute('data-temp', 'true');
        const now = new Date();
        const timeStr = now.getHours().toString().padStart(2, '0') + ':' + 
                       now.getMinutes().toString().padStart(2, '0');
        
        // Lấy icon theo extension
        const ext = file.name.split('.').pop().toLowerCase();
        let icon = 'fa-file';
        let iconColor = '#999';
        if (['pdf'].includes(ext)) {
            icon = 'fa-file-pdf';
            iconColor = '#dc3545';
        } else if (['doc', 'docx'].includes(ext)) {
            icon = 'fa-file-word';
            iconColor = '#0d6efd';
        } else if (['xls', 'xlsx'].includes(ext)) {
            icon = 'fa-file-excel';
            iconColor = '#198754';
        } else if (['zip', 'rar', '7z'].includes(ext)) {
            icon = 'fa-file-archive';
            iconColor = '#ffc107';
        }
        
        messageWrapper.innerHTML = `
            <div class="zalo-message">
                <div class="zalo-message-content">
                    <div style="padding: 10px; background: rgba(255,255,255,0.1); border-radius: 8px;">
                        <i class="fas ${icon}" style="margin-right: 8px; font-size: 20px; color: ${iconColor};"></i>
                        <strong>${escapeHtml(file.name)}</strong>
                        <div style="font-size: 12px; opacity: 0.7; margin-top: 5px;">
                            ${(file.size / 1024 / 1024).toFixed(2)} MB
                        </div>
                    </div>
                </div>
                <div class="zalo-message-time">${timeStr}</div>
            </div>
        `;
        
        chatMessages.appendChild(messageWrapper);
        setTimeout(() => {
            chatMessages.scrollTo({
                top: chatMessages.scrollHeight,
                behavior: 'smooth'
            });
        }, 100);
    }
    
    // Upload file lên server
    try {
        const response = await fetch('../api/admin_chat.php?action=upload', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        
        const data = await response.json();
        if (data.success && messageWrapper) {
            // Cập nhật với link download thật
            if (data.file_url) {
                const contentDiv = messageWrapper.querySelector('.zalo-message-content > div');
                if (contentDiv) {
                    contentDiv.innerHTML = `
                        <a href="${data.file_url}" target="_blank" style="color: inherit; text-decoration: none; display: flex; align-items: center;">
                            <i class="fas ${icon}" style="margin-right: 8px; font-size: 20px; color: ${iconColor};"></i>
                            <div>
                                <strong>${escapeHtml(data.file_name || file.name)}</strong>
                                <div style="font-size: 12px; opacity: 0.7; margin-top: 5px;">
                                    ${(file.size / 1024 / 1024).toFixed(2)} MB
                                </div>
                            </div>
                        </a>
                    `;
                }
            }
            // Cập nhật message ID
            if (data.chat_id) {
                messageWrapper.setAttribute('data-message-id', data.chat_id);
                messageWrapper.removeAttribute('data-temp');
            }
        } else {
            if (messageWrapper && messageWrapper.parentNode) {
                messageWrapper.remove();
            }
            alert('Lỗi upload: ' + (data.message || 'Không thể upload file'));
        }
    } catch (error) {
        console.error('Upload error:', error);
        if (messageWrapper && messageWrapper.parentNode) {
            messageWrapper.remove();
        }
        alert('Có lỗi xảy ra khi upload file!');
    }
}

// Send media file (placeholder)
function sendMediaFile(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            const messageWrapper = document.createElement('div');
            messageWrapper.className = 'zalo-message-wrapper zalo-message-sent';
            const now = new Date();
            const timeStr = now.getHours().toString().padStart(2, '0') + ':' + 
                           now.getMinutes().toString().padStart(2, '0');
            
            if (file.type.startsWith('image/')) {
                messageWrapper.innerHTML = `
                    <div class="zalo-message">
                        <div class="zalo-message-content">
                            <img src="${e.target.result}" style="max-width: 200px; border-radius: 8px;">
                            <div style="margin-top: 5px; font-size: 12px; opacity: 0.7;">${file.name}</div>
                        </div>
                        <div class="zalo-message-time">${timeStr}</div>
                    </div>
                `;
            } else {
                messageWrapper.innerHTML = `
                    <div class="zalo-message">
                        <div class="zalo-message-content">
                            <div style="padding: 10px; background: rgba(255,255,255,0.1); border-radius: 8px;">
                                <i class="fas fa-video" style="margin-right: 8px;"></i>
                                ${file.name}
                            </div>
                        </div>
                        <div class="zalo-message-time">${timeStr}</div>
                    </div>
                `;
            }
            
            chatMessages.appendChild(messageWrapper);
            setTimeout(() => {
                chatMessages.scrollTo({
                    top: chatMessages.scrollHeight,
                    behavior: 'smooth'
                });
            }, 100);
        }
    };
    reader.readAsDataURL(file);
}

// Send file (placeholder)
function sendFile(file) {
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        const messageWrapper = document.createElement('div');
        messageWrapper.className = 'zalo-message-wrapper zalo-message-sent';
        const now = new Date();
        const timeStr = now.getHours().toString().padStart(2, '0') + ':' + 
                       now.getMinutes().toString().padStart(2, '0');
        
        messageWrapper.innerHTML = `
            <div class="zalo-message">
                <div class="zalo-message-content">
                    <div style="padding: 10px; background: rgba(255,255,255,0.1); border-radius: 8px;">
                        <i class="fas fa-file" style="margin-right: 8px;"></i>
                        ${file.name}
                        <div style="font-size: 12px; opacity: 0.7; margin-top: 5px;">
                            ${(file.size / 1024 / 1024).toFixed(2)} MB
                        </div>
                    </div>
                </div>
                <div class="zalo-message-time">${timeStr}</div>
            </div>
        `;
        
        chatMessages.appendChild(messageWrapper);
        setTimeout(() => {
            chatMessages.scrollTo({
                top: chatMessages.scrollHeight,
                behavior: 'smooth'
            });
        }, 100);
    }
}

// Auto refresh tin nhắn mới mỗi 3 giây (không reload toàn bộ trang)
if (currentSessionId) {
    let lastMessageId = 0;
    const chatMessages = document.getElementById('chatMessages');
    
    // Lấy ID tin nhắn cuối cùng
    if (chatMessages) {
        const messages = chatMessages.querySelectorAll('.zalo-message-wrapper[data-message-id]');
        if (messages.length > 0) {
            const lastMessage = messages[messages.length - 1];
            lastMessageId = parseInt(lastMessage.getAttribute('data-message-id') || '0');
        }
    }
    
    // Polling để lấy tin nhắn mới
    setInterval(() => {
        fetch(`../api/admin_chat.php?action=get_messages&session_id=${encodeURIComponent(currentSessionId)}&last_id=${lastMessageId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.messages && data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        if (msg.id > lastMessageId) {
                            lastMessageId = msg.id;
                            // Thêm tin nhắn mới vào UI
                            // Kiểm tra xem tin nhắn đã tồn tại chưa (tránh duplicate)
                            const existingMsg = chatMessages.querySelector(`[data-message-id="${msg.id}"]`);
                            if (existingMsg) {
                                return; // Skip nếu đã có
                            }
                            
                            const messageWrapper = document.createElement('div');
                            messageWrapper.className = `zalo-message-wrapper ${msg.sender_type === 'user' ? 'zalo-message-received' : 'zalo-message-sent'}`;
                            messageWrapper.setAttribute('data-message-id', msg.id);
                            const msgDate = new Date(msg.created_at);
                            const timeStr = msgDate.getHours().toString().padStart(2, '0') + ':' + 
                                           msgDate.getMinutes().toString().padStart(2, '0');
                            // Giữ nguyên emoji, không escape HTML cho emoji
                            const messageContent = msg.message.replace(/\n/g, '<br>');
                            messageWrapper.innerHTML = `
                                <div class="zalo-message">
                                    <div class="zalo-message-content">${messageContent}</div>
                                    <div class="zalo-message-time">${timeStr}</div>
                                </div>
                            `;
                            if (chatMessages) {
                                chatMessages.appendChild(messageWrapper);
                                // Scroll to bottom
                                setTimeout(() => {
                                    chatMessages.scrollTo({
                                        top: chatMessages.scrollHeight,
                                        behavior: 'smooth'
                                    });
                                }, 100);
                            }
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching new messages:', error);
            });
    }, 3000);
}
</script>

<style>
/* Override admin layout for full screen chat */
.content-area {
    padding: 0 !important;
    margin: 0 !important;
}

/* Zalo Chat Container - Full Screen Layout */
.zalo-chat-container {
    position: fixed;
    top: 0;
    left: 260px; /* Sidebar width */
    right: 0;
    bottom: 0;
    background: #e5e5e5;
    overflow: hidden;
    z-index: 100;
}

.zalo-chat-wrapper {
    display: flex;
    height: 100vh;
    width: 100%;
}

/* Sidebar bên trái */
.zalo-sidebar {
    width: 360px;
    background: #ffffff;
    border-right: 1px solid #e5e5e5;
    display: flex;
    flex-direction: column;
    height: 100vh;
}

.zalo-sidebar-header {
    padding: 16px 20px;
    border-bottom: 1px solid #e5e5e5;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #ffffff;
}

.zalo-sidebar-title h4 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: #333;
}

.zalo-sidebar-actions {
    display: flex;
    gap: 8px;
}

.zalo-icon-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: transparent;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    transition: all 0.2s;
}

.zalo-icon-btn:hover {
    background: #f0f0f0;
    color: #333;
}

/* Search Box */
.zalo-search-container {
    padding: 12px 16px;
    border-bottom: 1px solid #e5e5e5;
}

.zalo-search-box {
    position: relative;
    display: flex;
    align-items: center;
}

.zalo-search-box i {
    position: absolute;
    left: 12px;
    color: #999;
    font-size: 14px;
}

.zalo-search-input {
    width: 100%;
    padding: 10px 12px 10px 36px;
    border: 1px solid #e5e5e5;
    border-radius: 20px;
    font-size: 14px;
    background: #f5f5f5;
    outline: none;
    transition: all 0.2s;
}

.zalo-search-input:focus {
    background: #fff;
    border-color: #0084ff;
}

/* Tabs */
.zalo-tabs {
    display: flex;
    padding: 0 16px;
    border-bottom: 1px solid #e5e5e5;
    gap: 8px;
}

.zalo-tab {
    padding: 12px 16px;
    border: none;
    background: transparent;
    cursor: pointer;
    font-size: 14px;
    color: #666;
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.zalo-tab.active {
    color: #0084ff;
    font-weight: 600;
}

.zalo-tab.active::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    right: 0;
    height: 2px;
    background: #0084ff;
}

.zalo-badge {
    background: #ff3b30;
    color: white;
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 10px;
    font-weight: 600;
}

/* Chat List */
.zalo-chat-list {
    flex: 1;
    overflow-y: auto;
    background: #ffffff;
}

.zalo-chat-item {
    display: flex;
    padding: 12px 16px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    text-decoration: none;
    color: inherit;
    transition: background 0.2s;
    position: relative;
}

.zalo-chat-item:hover {
    background: #f5f5f5;
}

.zalo-chat-item.active {
    background: #e8f4fd;
}

.zalo-chat-avatar {
    position: relative;
    width: 56px;
    height: 56px;
    flex-shrink: 0;
    margin-right: 12px;
}

.zalo-chat-avatar img,
.zalo-avatar-placeholder {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    object-fit: cover;
}

.zalo-avatar-placeholder {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 20px;
}

.zalo-avatar-img {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e5e5e5;
    display: block;
    background: #f0f0f0;
}

.zalo-chat-header-avatar .zalo-avatar-img {
    width: 40px;
    height: 40px;
}

.zalo-online-status {
    position: absolute;
    bottom: 2px;
    right: 8px;
    width: 14px;
    height: 14px;
    background: #00d084;
    border: 2px solid white;
    border-radius: 50%;
}

.zalo-online-status-header {
    bottom: 0;
    right: 0;
    width: 12px;
    height: 12px;
}

.zalo-unread-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background: #ff3b30;
    color: white;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 18px;
    text-align: center;
    border: 2px solid white;
}

.zalo-chat-info {
    flex: 1;
    min-width: 0;
}

.zalo-chat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}

.zalo-chat-name {
    font-size: 15px;
    font-weight: 600;
    color: #333;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.zalo-chat-time {
    font-size: 12px;
    color: #999;
    flex-shrink: 0;
    margin-left: 8px;
}

.zalo-chat-preview {
    display: flex;
    align-items: center;
}

.zalo-chat-message {
    font-size: 14px;
    color: #666;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.zalo-empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.zalo-empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}

/* Chat Window bên phải */
.zalo-chat-window {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #ffffff;
    height: 100vh;
}

/* Chat Header */
.zalo-chat-header {
    padding: 12px 20px;
    border-bottom: 1px solid #e5e5e5;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #ffffff;
}

.zalo-chat-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.zalo-chat-header-avatar {
    width: 40px;
    height: 40px;
    position: relative;
}

.zalo-chat-header-avatar img,
.zalo-chat-header-avatar .zalo-avatar-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.zalo-chat-header-info h5 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.zalo-chat-status {
    font-size: 12px;
    color: #999;
}

.zalo-chat-header-right {
    display: flex;
    gap: 8px;
}

.zalo-header-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: transparent;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    transition: all 0.2s;
}

.zalo-header-btn:hover {
    background: #f0f0f0;
}

.zalo-header-btn:nth-child(1) { /* Phone */
    color: #00d084;
}

.zalo-header-btn:nth-child(2) { /* Video */
    color: #0084ff;
}

/* Messages Container */
.zalo-messages-container {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #e5e5e5;
    background-image: 
        repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0,0,0,.03) 2px, rgba(0,0,0,.03) 4px);
}

.zalo-date-divider {
    text-align: center;
    margin: 20px 0;
    position: relative;
}

.zalo-date-divider::before {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    top: 50%;
    height: 1px;
    background: #ddd;
}

.zalo-date-divider span {
    background: #e5e5e5;
    padding: 4px 12px;
    color: #666;
    font-size: 12px;
    position: relative;
    z-index: 1;
    border-radius: 12px;
}

/* Message Bubbles */
.zalo-message-wrapper {
    display: flex;
    margin-bottom: 8px;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.zalo-message-received {
    justify-content: flex-start;
}

.zalo-message-sent {
    justify-content: flex-end;
}

.zalo-message {
    max-width: 60%;
    padding: 10px 14px;
    border-radius: 18px;
    word-wrap: break-word;
    word-break: break-word;
    position: relative;
}

.zalo-message-received .zalo-message {
    background: #ffffff;
    border-bottom-left-radius: 4px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.zalo-message-sent .zalo-message {
    background: #0084ff;
    color: white;
    border-bottom-right-radius: 4px;
}

.zalo-message-content {
    line-height: 1.5;
    font-size: 14px;
    margin-bottom: 4px;
    word-wrap: break-word;
    word-break: break-word;
}

/* Emoji styling - đảm bảo emoji hiển thị đúng size */
.zalo-message-content {
    font-family: 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji', sans-serif;
}

.zalo-message-content img {
    max-width: 100%;
    height: auto;
}

.zalo-message-time {
    font-size: 11px;
    opacity: 0.7;
    text-align: right;
    margin-top: 4px;
}

.zalo-message-received .zalo-message-time {
    text-align: left;
}

/* Input Container */
.zalo-input-container {
    padding: 12px 16px;
    background: #ffffff;
    border-top: 1px solid #e5e5e5;
}

.zalo-input-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f0f0f0;
    border-radius: 24px;
    padding: 6px 12px;
    position: relative;
}

.zalo-attach-menu {
    position: absolute;
    bottom: 60px;
    left: 10px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    padding: 8px;
    display: none;
    flex-direction: column;
    gap: 4px;
    min-width: 150px;
}

.zalo-attach-menu.show {
    display: flex;
}

.zalo-attach-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border: none;
    background: transparent;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s;
    font-size: 14px;
    color: #333;
    text-align: left;
    width: 100%;
}

.zalo-attach-item:hover {
    background: #f0f0f0;
}

.zalo-attach-item i {
    width: 20px;
    text-align: center;
    font-size: 16px;
}

.zalo-attach-item:nth-child(1) i { /* Image */
    color: #ff6b35;
}

.zalo-attach-item:nth-child(2) i { /* File */
    color: #4285f4;
}

.zalo-attach-item:nth-child(3) i { /* Location */
    color: #ea4335;
}

.zalo-attach-item:nth-child(4) i { /* Poll */
    color: #34a853;
}

.zalo-input-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: transparent;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    transition: all 0.2s;
    flex-shrink: 0;
}

.zalo-input-btn:hover {
    background: #e0e0e0;
    color: #333;
}

.zalo-input-btn:first-child { /* Plus button */
    background: #0084ff;
    color: white;
}

.zalo-input-btn:first-child:hover {
    background: #0073e6;
}

.zalo-input-btn:last-of-type { /* Emoji */
    color: #ffcc00;
}

.zalo-input {
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    font-size: 14px;
    padding: 8px 4px;
    color: #333;
}

.zalo-send-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: #0084ff;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    transition: all 0.2s;
    flex-shrink: 0;
}

.zalo-send-btn:hover {
    background: #0073e6;
    transform: scale(1.05);
}

.zalo-empty-window {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
}

.zalo-empty-content {
    text-align: center;
    color: #999;
}

.zalo-empty-content i {
    font-size: 64px;
    margin-bottom: 16px;
    opacity: 0.3;
}

.zalo-empty-content h4 {
    margin: 0 0 8px 0;
    color: #666;
}

.zalo-empty-chat {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.zalo-empty-chat i {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.3;
}

/* Scrollbar */
.zalo-chat-list::-webkit-scrollbar,
.zalo-messages-container::-webkit-scrollbar {
    width: 6px;
}

.zalo-chat-list::-webkit-scrollbar-track,
.zalo-messages-container::-webkit-scrollbar-track {
    background: transparent;
}

.zalo-chat-list::-webkit-scrollbar-thumb,
.zalo-messages-container::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}

.zalo-chat-list::-webkit-scrollbar-thumb:hover,
.zalo-messages-container::-webkit-scrollbar-thumb:hover {
    background: #999;
}

/* Emoji Picker */
.zalo-emoji-picker {
    position: absolute;
    bottom: 60px;
    right: 10px;
    width: 360px;
    height: 400px;
    background: #2c2c2e;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    display: none;
    flex-direction: column;
    overflow: hidden;
}

.zalo-emoji-picker.show {
    display: flex;
}

.zalo-emoji-tabs {
    display: flex;
    background: #1c1c1e;
    border-bottom: 1px solid #3a3a3c;
    padding: 0 12px;
}

.zalo-emoji-tab {
    padding: 12px 20px;
    border: none;
    background: transparent;
    color: #8e8e93;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    position: relative;
    transition: color 0.2s;
}

.zalo-emoji-tab.active {
    color: #0084ff;
}

.zalo-emoji-tab.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: #0084ff;
}

.zalo-emoji-content {
    flex: 1;
    overflow-y: auto;
    padding: 12px;
}

.zalo-emoji-category-title {
    color: #8e8e93;
    font-size: 12px;
    font-weight: 600;
    padding: 8px 4px;
    text-transform: uppercase;
}

.zalo-emoji-grid {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 4px;
    margin-bottom: 16px;
}

.zalo-emoji-item {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    cursor: pointer;
    border-radius: 8px;
    transition: background 0.2s;
    user-select: none;
}

.zalo-emoji-item:hover {
    background: rgba(255,255,255,0.1);
}

.zalo-emoji-categories {
    display: flex;
    background: #1c1c1e;
    border-top: 1px solid #3a3a3c;
    padding: 8px;
    overflow-x: auto;
    gap: 4px;
}

.zalo-emoji-categories::-webkit-scrollbar {
    height: 4px;
}

.zalo-emoji-categories::-webkit-scrollbar-thumb {
    background: #3a3a3c;
    border-radius: 2px;
}

.zalo-emoji-category {
    width: 40px;
    height: 40px;
    border: none;
    background: transparent;
    color: #8e8e93;
    font-size: 20px;
    cursor: pointer;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    flex-shrink: 0;
}

.zalo-emoji-category:hover {
    background: rgba(255,255,255,0.1);
}

.zalo-emoji-category.active {
    background: #0084ff;
    color: white;
}

/* Đảm bảo chat items có thể click được */
.zalo-chat-item {
    pointer-events: auto;
}

.zalo-chat-item * {
    pointer-events: none;
}
</style>

.chat-session-item {
    cursor: pointer !important;
    user-select: none;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
    padding: 15px !important;
    border-bottom: 1px solid #f0f0f0;
}

.chat-session-item:hover {
    background-color: #f8f9fa !important;
    border-left-color: #667eea;
    transform: translateX(2px);
}

.chat-session-item.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    border-left-color: #764ba2;
}

.chat-session-item.active:hover {
    background: linear-gradient(135deg, #5568d3 0%, #6a3f8f 100%) !important;
}

.chat-session-item * {
    pointer-events: none;
}

.chat-avatar {
    position: relative;
    flex-shrink: 0;
}

.chat-avatar img,
.chat-avatar .avatar-placeholder {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,0.3);
}

.chat-avatar .avatar-placeholder {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 18px;
}

.chat-session-item.active .chat-avatar img,
.chat-session-item.active .chat-avatar .avatar-placeholder {
    border-color: rgba(255,255,255,0.5);
}

.chat-user-name {
    font-size: 15px;
    font-weight: 600;
}

/* Chat Window */
.chat-window {
    height: calc(100vh - 200px);
    display: flex;
    flex-direction: column;
}

.chat-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 20px;
    border-bottom: none;
}

.chat-header-avatar {
    position: relative;
}

.chat-header-avatar img,
.chat-header-avatar .avatar-placeholder {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,0.3);
}

.chat-header-avatar .avatar-placeholder {
    background: rgba(255,255,255,0.2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 18px;
}

.chat-messages-container {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f8f9fa;
    scroll-behavior: smooth;
}

.chat-date-divider {
    text-align: center;
    margin: 20px 0;
    position: relative;
}

.chat-date-divider::before {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    top: 50%;
    height: 1px;
    background: #dee2e6;
}

.chat-date-divider span {
    background: #f8f9fa;
    padding: 5px 15px;
    color: #6c757d;
    font-size: 12px;
    position: relative;
    z-index: 1;
}

.chat-message-wrapper {
    display: flex;
    margin-bottom: 15px;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.chat-message-wrapper.user-message {
    justify-content: flex-end;
}

.chat-message-wrapper.admin-message {
    justify-content: flex-start;
}

.chat-message {
    max-width: 70%;
    padding: 12px 16px;
    border-radius: 18px;
    position: relative;
    word-wrap: break-word;
    word-break: break-word;
}

.user-message .chat-message {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom-right-radius: 4px;
}

.admin-message .chat-message {
    background: white;
    color: #2d3748;
    border: 1px solid #e2e8f0;
    border-bottom-left-radius: 4px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.message-content {
    margin-bottom: 5px;
    line-height: 1.5;
}

.message-time {
    font-size: 11px;
    opacity: 0.7;
    text-align: right;
}

.admin-message .message-time {
    text-align: left;
}

.chat-input-container {
    background: white;
    border-top: 1px solid #e2e8f0;
    padding: 15px;
}

.chat-input {
    border-radius: 25px;
    border: 1px solid #e2e8f0;
    padding: 12px 20px;
    font-size: 14px;
}

.chat-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.chat-send-btn {
    border-radius: 25px;
    padding: 12px 25px;
    font-weight: 600;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    transition: all 0.3s ease;
}

.chat-send-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.chat-send-btn:active {
    transform: translateY(0);
}

/* Scrollbar styling */
.chat-messages-container::-webkit-scrollbar,
.chat-sessions-list::-webkit-scrollbar {
    width: 6px;
}

.chat-messages-container::-webkit-scrollbar-track,
.chat-sessions-list::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.chat-messages-container::-webkit-scrollbar-thumb,
.chat-sessions-list::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.chat-messages-container::-webkit-scrollbar-thumb:hover,
.chat-sessions-list::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>

<?php include 'includes/admin_footer.php'; ?>

