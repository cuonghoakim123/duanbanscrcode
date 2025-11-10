// Chatbot JavaScript
let chatbotOpen = false;
let chatbotSessionId = null;
let quickReplies = [];

// Lấy hoặc tạo session_id từ localStorage
function getChatbotSessionId() {
    if (!chatbotSessionId) {
        chatbotSessionId = localStorage.getItem('chatbot_session_id');
        if (!chatbotSessionId) {
            chatbotSessionId = 'chat_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('chatbot_session_id', chatbotSessionId);
        }
    }
    return chatbotSessionId;
}

// Load quick replies
async function loadQuickReplies() {
    try {
        const response = await fetch(CHATBOT_API_URL.replace('chatbot.php', 'chatbot_quick_replies.php'));
        const data = await response.json();
        if (data.success && data.data) {
            quickReplies = data.data;
            return true;
        }
        return false;
    } catch (error) {
        console.error('Error loading quick replies:', error);
        return false;
    }
}

// Hiển thị quick replies
function displayQuickReplies() {
    const messagesContainer = document.getElementById('chatbotMessages');
    if (!messagesContainer) return;
    
    // Xóa quick replies cũ nếu có
    const existingQuickReplies = messagesContainer.querySelector('.quick-replies-container');
    if (existingQuickReplies) {
        existingQuickReplies.remove();
    }
    
    if (quickReplies.length === 0) return;
    
    const quickRepliesDiv = document.createElement('div');
    quickRepliesDiv.className = 'quick-replies-container';
    
    quickReplies.forEach(reply => {
        const button = document.createElement('button');
        button.className = 'quick-reply-btn';
        button.innerHTML = `<i class="fas ${reply.icon || 'fa-comment'}"></i> <span>${reply.question}</span>`;
        button.onclick = () => handleQuickReplyClick(reply.id, reply.question, reply.answer);
        quickRepliesDiv.appendChild(button);
    });
    
    // Thêm quick replies vào sau welcome message hoặc cuối messages container
    const welcomeMsg = messagesContainer.querySelector('.welcome-message');
    if (welcomeMsg) {
        welcomeMsg.insertAdjacentElement('afterend', quickRepliesDiv);
    } else {
        messagesContainer.appendChild(quickRepliesDiv);
    }
    
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// Xử lý click vào quick reply
async function handleQuickReplyClick(replyId, question, answer) {
    const messagesContainer = document.getElementById('chatbotMessages');
    
    // Xóa welcome message nếu có
    const welcomeMsg = messagesContainer.querySelector('.welcome-message');
    if (welcomeMsg) {
        welcomeMsg.remove();
    }
    
    // Ẩn quick replies
    const quickRepliesContainer = messagesContainer.querySelector('.quick-replies-container');
    if (quickRepliesContainer) {
        quickRepliesContainer.style.display = 'none';
    }
    
    // Hiển thị câu hỏi của user
    addMessage(question, 'user');
    
    // Hiển thị typing indicator
    const typingId = addTypingIndicator();
    
    // Gửi request đến chatbot API với quick_reply_id để lưu vào database và lấy answer
    try {
        const response = await fetch(CHATBOT_API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                message: question,
                session_id: getChatbotSessionId(),
                quick_reply_id: replyId
            })
        });
        
        const data = await response.json();
        
        // Xóa typing indicator
        removeTypingIndicator(typingId);
        
        if (data.success) {
            // Sử dụng answer từ server
            addMessage(data.message, 'bot');
        } else {
            // Fallback về answer từ client nếu server lỗi
            addMessage(answer, 'bot');
        }
        
        // Hiển thị lại quick replies sau khi trả lời
        setTimeout(() => {
            displayQuickReplies();
        }, 300);
    } catch (error) {
        console.error('Error handling quick reply:', error);
        removeTypingIndicator(typingId);
        // Fallback: hiển thị answer từ client
        addMessage(answer, 'bot');
        setTimeout(() => {
            displayQuickReplies();
        }, 300);
    }
}

function toggleChatbot() {
    const window = document.getElementById('chatbotWindow');
    const button = document.getElementById('chatbotButton');
    
    chatbotOpen = !chatbotOpen;
    
    if (chatbotOpen) {
        window.classList.add('active');
        button.style.display = 'none';
        // Load và hiển thị quick replies khi mở chatbot
        setTimeout(() => {
            if (quickReplies.length === 0) {
                loadQuickReplies().then(() => {
                    displayQuickReplies();
                });
            } else {
                displayQuickReplies();
            }
        }, 100);
        // Focus vào input
        setTimeout(() => {
            document.getElementById('chatbotInput').focus();
        }, 300);
    } else {
        window.classList.remove('active');
        button.style.display = 'flex';
    }
}

function handleChatbotKeyPress(event) {
    if (event.key === 'Enter') {
        sendChatbotMessage();
    }
}

async function sendChatbotMessage() {
    const input = document.getElementById('chatbotInput');
    const sendBtn = document.getElementById('chatbotSendBtn');
    const messagesContainer = document.getElementById('chatbotMessages');
    const message = input.value.trim();
    
    if (!message) return;
    
    // Disable input và button
    input.disabled = true;
    sendBtn.disabled = true;
    
    // Xóa welcome message nếu có
    const welcomeMsg = messagesContainer.querySelector('.welcome-message');
    if (welcomeMsg) {
        welcomeMsg.remove();
    }
    
    // Ẩn quick replies khi user gửi message
    const quickRepliesContainer = messagesContainer.querySelector('.quick-replies-container');
    if (quickRepliesContainer) {
        quickRepliesContainer.style.display = 'none';
    }
    
    // Hiển thị message của user
    addMessage(message, 'user');
    input.value = '';
    
    // Hiển thị typing indicator
    const typingId = addTypingIndicator();
    
    try {
        const response = await fetch(CHATBOT_API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                message: message,
                session_id: getChatbotSessionId()
            })
        });
        
        const data = await response.json();
        
        // Xóa typing indicator
        removeTypingIndicator(typingId);
        
        // Cập nhật session_id nếu có
        if (data.session_id) {
            chatbotSessionId = data.session_id;
            localStorage.setItem('chatbot_session_id', chatbotSessionId);
        }
        
        if (data.success) {
            addMessage(data.message, 'bot');
            // Hiển thị lại quick replies sau khi bot trả lời (nếu không phải từ quick reply)
            if (data.source !== 'quick_reply') {
                setTimeout(() => {
                    displayQuickReplies();
                }, 500);
            }
        } else {
            addMessage(data.message || 'Xin lỗi, có lỗi xảy ra. Vui lòng thử lại sau hoặc liên hệ trực tiếp: 0356-012250', 'bot');
        }
    } catch (error) {
        console.error('Chatbot error:', error);
        removeTypingIndicator(typingId);
        addMessage('Xin lỗi, không thể kết nối với server. Vui lòng kiểm tra kết nối internet hoặc liên hệ trực tiếp: 0356-012250', 'bot');
    } finally {
        // Enable lại input và button
        input.disabled = false;
        sendBtn.disabled = false;
        input.focus();
    }
}

function addMessage(text, type) {
    const messagesContainer = document.getElementById('chatbotMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}`;
    
    const avatar = document.createElement('div');
    avatar.className = 'message-avatar';
    avatar.innerHTML = type === 'bot' ? '<i class="fas fa-robot"></i>' : '<i class="fas fa-user"></i>';
    
    const content = document.createElement('div');
    content.className = 'message-content';
    content.textContent = text;
    
    const time = document.createElement('div');
    time.className = 'message-time';
    time.textContent = getCurrentTime();
    
    content.appendChild(time);
    messageDiv.appendChild(avatar);
    messageDiv.appendChild(content);
    
    messagesContainer.appendChild(messageDiv);
    
    // Scroll to bottom
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function addTypingIndicator() {
    const messagesContainer = document.getElementById('chatbotMessages');
    const typingDiv = document.createElement('div');
    typingDiv.className = 'message bot';
    typingDiv.id = 'typing-indicator';
    
    const avatar = document.createElement('div');
    avatar.className = 'message-avatar';
    avatar.innerHTML = '<i class="fas fa-robot"></i>';
    
    const indicator = document.createElement('div');
    indicator.className = 'typing-indicator';
    indicator.innerHTML = '<span></span><span></span><span></span>';
    
    typingDiv.appendChild(avatar);
    typingDiv.appendChild(indicator);
    messagesContainer.appendChild(typingDiv);
    
    // Scroll to bottom
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    
    return 'typing-indicator';
}

function removeTypingIndicator(id) {
    const indicator = document.getElementById(id);
    if (indicator) {
        indicator.remove();
    }
}

function getCurrentTime() {
    const now = new Date();
    const hours = now.getHours().toString().padStart(2, '0');
    const minutes = now.getMinutes().toString().padStart(2, '0');
    return `${hours}:${minutes}`;
}

// Auto scroll khi có message mới
const messagesContainer = document.getElementById('chatbotMessages');
if (messagesContainer) {
    const observer = new MutationObserver(() => {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    });
    observer.observe(messagesContainer, { childList: true });
}

// Load quick replies khi page load
document.addEventListener('DOMContentLoaded', function() {
    // Đợi một chút để đảm bảo chatbot elements đã được render
    setTimeout(() => {
        loadQuickReplies();
    }, 500);
});

