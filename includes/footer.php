    <?php
    // Đảm bảo lang.php đã được include
    if (!function_exists('lang')) {
        require_once __DIR__ . '/../config/lang.php';
    }
    ?>
    <!-- Footer -->
    <footer class="footer-modern bg-dark text-white mt-5">
        <div class="footer-main py-5">
            <div class="container">
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6">
                        <h5 class="footer-title"><i class="fas fa-laptop-code"></i> <?php echo SITE_NAME; ?></h5>
                        <p class="footer-text"><?php echo lang('footer_about_desc'); ?></p>
                        <div class="footer-social mt-4">
                            <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-tiktok"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <h5 class="footer-title"><?php echo lang('footer_about'); ?></h5>
                        <ul class="footer-links">
                            <li><a href="<?php echo SITE_URL; ?>/about.php"><?php echo lang('nav_about'); ?></a></li>
                            <li><a href="<?php echo SITE_URL; ?>/services.php"><?php echo lang('nav_services'); ?></a></li>
                            <li><a href="<?php echo SITE_URL; ?>/templates.php"><?php echo lang('nav_templates'); ?></a></li>
                            <li><a href="<?php echo SITE_URL; ?>/news.php"><?php echo lang('nav_news'); ?></a></li>
                            <li><a href="<?php echo SITE_URL; ?>/contact.php"><?php echo lang('nav_contact'); ?></a></li>
                        </ul>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <h5 class="footer-title"><?php echo lang('nav_products'); ?></h5>
                        <ul class="footer-links">
                            <li><a href="<?php echo SITE_URL; ?>/products.php"><?php echo $lang == 'vi' ? 'Tất cả sản phẩm' : 'All products'; ?></a></li>
                            <li><a href="<?php echo SITE_URL; ?>/products.php?category=website"><?php echo lang('services_ecommerce'); ?></a></li>
                            <li><a href="<?php echo SITE_URL; ?>/products.php?category=landing"><?php echo $lang == 'vi' ? 'Landing Page' : 'Landing Page'; ?></a></li>
                            <li><a href="<?php echo SITE_URL; ?>/products.php?category=app"><?php echo lang('services_mobile'); ?></a></li>
                            <li><a href="<?php echo SITE_URL; ?>/products.php?featured=1"><?php echo $lang == 'vi' ? 'Nổi bật' : 'Featured'; ?></a></li>
                        </ul>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <h5 class="footer-title"><?php echo lang('nav_contact'); ?></h5>
                        <ul class="footer-contact">
                            <li>
                                <i class="fas fa-map-marker-alt"></i>
                                <span>KTX Khu B Đại Học Quốc Gia TP.HCM</span>
                            </li>
                            <li>
                                <i class="fas fa-phone-alt"></i>
                                <span><a href="tel:0356-012-250">0356-012-250</a></span>
                            </li>
                            <li>
                                <i class="fas fa-envelope"></i>
                                <span><a href="mailto:info@yoursite.vn">cuonghotran17022004@gmail.com</a></span>
                            </li>
                            <li>
                                <i class="fas fa-clock"></i>
                                <span><?php echo lang('contact_monday_friday'); ?>: 8:00 - 18:00</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom py-3">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start">
                        <p class="mb-0">&copy; 2025 <?php echo SITE_NAME; ?>. <?php echo lang('footer_rights'); ?></p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <a href="#" class="footer-link"><?php echo $lang == 'vi' ? 'Điều khoản' : 'Terms'; ?></a>
                        <span class="mx-2">|</span>
                        <a href="#" class="footer-link"><?php echo $lang == 'vi' ? 'Chính sách' : 'Policy'; ?></a>
                        <span class="mx-2">|</span>
                        <a href="#" class="footer-link"><?php echo $lang == 'vi' ? 'Bảo mật' : 'Privacy'; ?></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top -->
    <button id="backToTop" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <style>
    .footer-modern {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    }
    
    .footer-title {
        font-weight: 700;
        margin-bottom: 20px;
        color: #fff;
        position: relative;
        padding-bottom: 10px;
    }
    
    .footer-title::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 50px;
        height: 3px;
        background: linear-gradient(90deg, #667eea, #764ba2);
    }
    
    .footer-text {
        color: rgba(255,255,255,0.8);
        line-height: 1.8;
    }
    
    .footer-social {
        display: flex;
        gap: 10px;
    }
    
    .social-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.1);
        color: white;
        border-radius: 50%;
        transition: all 0.3s ease;
    }
    
    .social-icon:hover {
        background: linear-gradient(135deg, #667eea, #764ba2);
        transform: translateY(-3px);
    }
    
    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .footer-links li {
        margin-bottom: 10px;
    }
    
    .footer-links a {
        color: rgba(255,255,255,0.8);
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-block;
    }
    
    .footer-links a:hover {
        color: #fff;
        padding-left: 5px;
    }
    
    .footer-contact {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .footer-contact li {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
        color: rgba(255,255,255,0.8);
    }
    
    .footer-contact i {
        color: #667eea;
        width: 20px;
    }
    
    .footer-contact a {
        color: rgba(255,255,255,0.8);
        text-decoration: none;
    }
    
    .footer-contact a:hover {
        color: #fff;
    }
    
    .footer-bottom {
        background: rgba(0,0,0,0.2);
        border-top: 1px solid rgba(255,255,255,0.1);
    }
    
    .footer-link {
        color: rgba(255,255,255,0.8);
        text-decoration: none;
    }
    
    .footer-link:hover {
        color: #fff;
    }
    
    .back-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
        z-index: 1000;
    }
    
    .back-to-top:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    }
    
    .back-to-top.show {
        display: flex;
    }
    </style>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <?php 
    // Load URL helper nếu chưa có
    if (!function_exists('asset_url')) {
        require_once __DIR__ . '/../config/url_helper.php';
    }
    ?>
    <script>
    // Define SITE_URL for JavaScript (must be before main.js)
    const SITE_URL = '<?php echo SITE_URL; ?>';
    </script>
    <script src="<?php echo asset_url('assets/js/main.js'); ?>"></script>
    
    <script>
    
    // Initialize AOS
    AOS.init({
        duration: 1000,
        once: true,
        offset: 100
    });
    
    // Back to top button
    const backToTop = document.getElementById('backToTop');
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTop.classList.add('show');
        } else {
            backToTop.classList.remove('show');
        }
    });
    
    backToTop.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Update cart count
    function updateCartCount() {
        fetch(SITE_URL + '/cart_handler.php?action=count')
            .then(response => response.json())
            .then(data => {
                if(data.count) {
                    document.getElementById('cart-count').textContent = data.count;
                }
            })
            .catch(error => console.log(error));
    }
    
    <?php if(isset($_SESSION['user_id'])): ?>
    updateCartCount();
    <?php endif; ?>
    </script>
    
    <?php if(isset($extra_js)) echo $extra_js; ?>
    
    <!-- Chatbot -->
    <link rel="stylesheet" href="<?php echo asset_url('assets/css/chatbot.css'); ?>">
    <div class="chatbot-container">
        <div class="chatbot-window" id="chatbotWindow">
            <div class="chatbot-header">
                <div class="chatbot-header-info">
                    <div class="chatbot-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="chatbot-header-text">
                        <h4><?php echo lang('chatbot_title'); ?></h4>
                        <p><?php echo lang('chatbot_subtitle'); ?></p>
                    </div>
                </div>
                <button class="chatbot-close" onclick="toggleChatbot()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="chatbot-messages" id="chatbotMessages">
                <div class="welcome-message">
                    <h5>👋 <?php echo lang('chatbot_welcome'); ?></h5>
                    <p><?php echo lang('chatbot_intro'); ?></p>
                    <ul style="text-align: left; display: inline-block; margin-top: 10px;">
                        <li><?php echo lang('chatbot_help_1'); ?></li>
                        <li><?php echo lang('chatbot_help_2'); ?></li>
                        <li><?php echo lang('chatbot_help_3'); ?></li>
                        <li><?php echo lang('chatbot_help_4'); ?></li>
                    </ul>
                    <p style="margin-top: 15px;"><?php echo lang('chatbot_start'); ?></p>
                </div>
            </div>
            <div class="chatbot-input-container">
                <input type="text" class="chatbot-input" id="chatbotInput" placeholder="<?php echo lang('common_chatbot_placeholder'); ?>" onkeypress="handleChatbotKeyPress(event)">
                <button class="chatbot-send" onclick="sendChatbotMessage()" id="chatbotSendBtn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
        <button class="chatbot-button" onclick="toggleChatbot()" id="chatbotButton">
            <i class="fas fa-comments"></i>
        </button>
    </div>
    <script>
        const CHATBOT_API_URL = '<?php echo page_url('api/chatbot.php'); ?>';
    </script>
    <script src="<?php echo asset_url('assets/js/chatbot.js'); ?>"></script>
</body>
</html>
