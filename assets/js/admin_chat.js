// Admin Chat Real-time
let adminChatSessionId = null;
let adminChatProductId = null;
let adminChatPollingInterval = null;
let lastMessageId = 0;

// Helper function: Lấy base URL
function getAdminChatBaseUrl() {
    if (typeof SITE_URL !== 'undefined' && SITE_URL) {
        return SITE_URL;
    }
    const origin = window.location.origin;
    const pathname = window.location.pathname;
    const pathParts = pathname.split('/').filter(p => p && !p.includes('.'));
    if (pathParts.length > 0) {
        return origin + '/' + pathParts[0];
    }
    return origin;
}

// Khởi tạo chat với admin
function initAdminChat(productId = null, productName = '') {
    console.log('initAdminChat called', productId, productName);
    // Tạo hoặc lấy session_id
    if (!adminChatSessionId) {
        adminChatSessionId = localStorage.getItem('admin_chat_session_id');
        if (!adminChatSessionId) {
            adminChatSessionId = 'admin_chat_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('admin_chat_session_id', adminChatSessionId);
        }
    }
    
    adminChatProductId = productId;
    
    // Mở cửa sổ chat
    openAdminChatWindow(productId, productName);
    
    // Load tin nhắn cũ
    loadAdminChatMessages();
    
    // Bắt đầu polling để nhận tin nhắn mới
    startAdminChatPolling();
}

// Mở cửa sổ chat với admin
function openAdminChatWindow(productId, productName) {
    // Tạo modal chat nếu chưa có
    let chatModal = document.getElementById('adminChatModal');
    if (!chatModal) {
        chatModal = document.createElement('div');
        chatModal.id = 'adminChatModal';
        chatModal.className = 'admin-chat-modal';
        chatModal.innerHTML = `
            <div class="admin-chat-window">
                <div class="admin-chat-header">
                    <div class="admin-chat-header-info">
                        <i class="fas fa-user-shield"></i>
                        <div>
                            <h5>Chat với Admin</h5>
                            <small id="adminChatStatus">Đang kết nối...</small>
                        </div>
                    </div>
                    <button class="admin-chat-close" onclick="closeAdminChat()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="admin-chat-messages" id="adminChatMessages"></div>
                <div class="admin-chat-input-container">
                    <input type="text" 
                           class="admin-chat-input" 
                           id="adminChatInput" 
                           placeholder="Nhập tin nhắn của bạn..."
                           onkeypress="if(event.key === 'Enter') sendAdminChatMessage()">
                    <button class="admin-chat-send" onclick="sendAdminChatMessage()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(chatModal);
        
        // Thêm CSS nếu chưa có
        if (!document.getElementById('adminChatStyles')) {
            const style = document.createElement('style');
            style.id = 'adminChatStyles';
            style.textContent = `
                .admin-chat-modal {
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    width: 380px;
                    height: 600px;
                    background: white;
                    border-radius: 15px;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                    z-index: 10000;
                    display: flex;
                    flex-direction: column;
                    overflow: hidden;
                    animation: slideUp 0.3s ease;
                }
                @keyframes slideUp {
                    from { transform: translateY(100px); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }
                .admin-chat-window {
                    display: flex;
                    flex-direction: column;
                    height: 100%;
                }
                .admin-chat-header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 15px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .admin-chat-header-info {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                .admin-chat-header-info i {
                    font-size: 24px;
                }
                .admin-chat-header-info h5 {
                    margin: 0;
                    font-size: 16px;
                }
                .admin-chat-header-info small {
                    opacity: 0.9;
                    font-size: 12px;
                }
                .admin-chat-close {
                    background: rgba(255,255,255,0.2);
                    border: none;
                    color: white;
                    width: 30px;
                    height: 30px;
                    border-radius: 50%;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .admin-chat-close:hover {
                    background: rgba(255,255,255,0.3);
                }
                .admin-chat-messages {
                    flex: 1;
                    overflow-y: auto;
                    padding: 15px;
                    background: #f8f9fa;
                }
                .admin-chat-message {
                    margin-bottom: 15px;
                    display: flex;
                    gap: 10px;
                }
                .admin-chat-message.user {
                    flex-direction: row-reverse;
                }
                .admin-chat-message-content {
                    max-width: 70%;
                    padding: 10px 15px;
                    border-radius: 18px;
                    word-wrap: break-word;
                }
                .admin-chat-message.user .admin-chat-message-content {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    border-bottom-right-radius: 4px;
                }
                .admin-chat-message.admin .admin-chat-message-content {
                    background: white;
                    color: #333;
                    border-bottom-left-radius: 4px;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                }
                .admin-chat-message-time {
                    font-size: 11px;
                    opacity: 0.7;
                    margin-top: 5px;
                }
                .admin-chat-input-container {
                    padding: 15px;
                    background: white;
                    border-top: 1px solid #e0e0e0;
                    display: flex;
                    gap: 10px;
                }
                .admin-chat-input {
                    flex: 1;
                    border: 1px solid #e0e0e0;
                    border-radius: 25px;
                    padding: 10px 20px;
                    outline: none;
                }
                .admin-chat-input:focus {
                    border-color: #667eea;
                }
                .admin-chat-send {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border: none;
                    color: white;
                    width: 45px;
                    height: 45px;
                    border-radius: 50%;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .admin-chat-send:hover {
                    transform: scale(1.1);
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    // Hiển thị modal
    chatModal.style.display = 'flex';
    
    // Thêm thông tin sản phẩm nếu có
    if (productName) {
        const messagesContainer = document.getElementById('adminChatMessages');
        const productInfo = document.createElement('div');
        productInfo.className = 'admin-chat-message user';
        productInfo.innerHTML = `
            <div class="admin-chat-message-content">
                <strong>Sản phẩm quan tâm:</strong> ${productName}
                <div class="admin-chat-message-time">${getCurrentTime()}</div>
            </div>
        `;
        messagesContainer.appendChild(productInfo);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
}

// Đóng chat
function closeAdminChat() {
    console.log('closeAdminChat called');
    const chatModal = document.getElementById('adminChatModal');
    if (chatModal) {
        chatModal.style.display = 'none';
    }
    stopAdminChatPolling();
}

// Gửi tin nhắn
async function sendAdminChatMessage() {
    console.log('sendAdminChatMessage called');
    const input = document.getElementById('adminChatInput');
    const message = input.value.trim();
    
    if (!message) return;
    
    input.disabled = true;
    input.value = '';
    
    // Hiển thị tin nhắn ngay
    addAdminChatMessage(message, 'user');
    
    try {
        const baseUrl = getAdminChatBaseUrl();
        const response = await fetch(baseUrl + '/api/admin_chat.php?action=send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                message: message,
                product_id: adminChatProductId,
                sender_type: 'user'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            adminChatSessionId = data.session_id;
            localStorage.setItem('admin_chat_session_id', adminChatSessionId);
            lastMessageId = Math.max(lastMessageId, data.chat_id || 0);
        }
    } catch (error) {
        console.error('Error sending message:', error);
    } finally {
        input.disabled = false;
        input.focus();
    }
}

// Load tin nhắn
async function loadAdminChatMessages() {
    if (!adminChatSessionId) return;
    
    try {
        const baseUrl = getAdminChatBaseUrl();
        const response = await fetch(`${baseUrl}/api/admin_chat.php?action=get_messages&session_id=${adminChatSessionId}&last_id=${lastMessageId}`);
        const data = await response.json();
        
        if (data.success && data.messages) {
            data.messages.forEach(msg => {
                addAdminChatMessage(msg.message, msg.sender_type, msg.created_at);
                lastMessageId = Math.max(lastMessageId, msg.id);
            });
        }
    } catch (error) {
        console.error('Error loading messages:', error);
    }
}

// Thêm tin nhắn vào UI
function addAdminChatMessage(message, senderType, timestamp = null) {
    const messagesContainer = document.getElementById('adminChatMessages');
    if (!messagesContainer) return;
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `admin-chat-message ${senderType}`;
    
    const time = timestamp ? formatTime(timestamp) : getCurrentTime();
    
    messageDiv.innerHTML = `
        <div class="admin-chat-message-content">
            ${message}
            <div class="admin-chat-message-time">${time}</div>
        </div>
    `;
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// Bắt đầu polling
function startAdminChatPolling() {
    if (adminChatPollingInterval) return;
    
    adminChatPollingInterval = setInterval(() => {
        loadAdminChatMessages();
    }, 2000); // Poll mỗi 2 giây
}

// Dừng polling
function stopAdminChatPolling() {
    if (adminChatPollingInterval) {
        clearInterval(adminChatPollingInterval);
        adminChatPollingInterval = null;
    }
}

// Helper functions
function getCurrentTime() {
    const now = new Date();
    return now.getHours().toString().padStart(2, '0') + ':' + 
           now.getMinutes().toString().padStart(2, '0');
}

function formatTime(timestamp) {
    const date = new Date(timestamp);
    return date.getHours().toString().padStart(2, '0') + ':' + 
           date.getMinutes().toString().padStart(2, '0');
}

// Export functions to window for onclick handlers
window.initAdminChat = initAdminChat;
window.closeAdminChat = closeAdminChat;
window.sendAdminChatMessage = sendAdminChatMessage;
window.openAdminChatWindow = openAdminChatWindow;

