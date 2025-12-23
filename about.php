<?php
session_start();
require_once 'config/config.php';
require_once 'config/lang.php';
$title = lang('about_title');
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="about-hero-section py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-5 mb-4 mb-lg-0" data-aos="fade-right">
                <div class="about-image-wrapper position-relative">
                    <div class="about-image-frame">
                        <img src="<?php echo SITE_URL; ?>/assets/images/9.jpg" 
                             alt="Nguyễn Đại Kim Cương - Founder DiamondDev" 
                             class="about-profile-image">
                    </div>
                    <div class="about-image-decoration"></div>
                </div>
            </div>
            <div class="col-lg-7" data-aos="fade-left">
                <div class="about-hero-content">
                    <span class="about-badge"><?php echo lang('about_founder'); ?></span>
                    <h1 class="about-hero-title">NGUYỄN ĐẠI KIM CƯƠNG</h1>
                    <p class="about-hero-subtitle"><?php echo lang('about_developer'); ?></p>
                    <p class="about-hero-description">
                        <?php echo lang('about_intro'); ?>
                    </p>
                    <div class="about-contact-info mt-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="contact-item">
                                    <i class="fas fa-phone-alt"></i>
                                    <span>0355 999 141</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="contact-item">
                                    <i class="fas fa-envelope"></i>
                                    <span>cuonghotran17022004@gmail.com</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="contact-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>17/02/2004</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="contact-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo lang('about_location'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Company Section -->
<section class="about-company-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center mb-5" data-aos="fade-up">
                <h2 class="section-title"><?php echo lang('about_company_title'); ?></h2>
                <p class="section-subtitle">
                    <?php echo lang('about_company_desc'); ?>
                </p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="about-stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <h3 class="stat-number">1000+</h3>
                    <p class="stat-label"><?php echo lang('about_projects'); ?></p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="about-stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="stat-number">900+</h3>
                    <p class="stat-label"><?php echo lang('about_customers'); ?></p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="about-stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="stat-number">24/7</h3>
                    <p class="stat-label"><?php echo lang('about_support'); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Education & Skills Section -->
<section class="about-skills-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mb-5 mb-lg-0" data-aos="fade-right">
                <h2 class="section-title mb-4"><?php echo lang('about_education'); ?></h2>
                <div class="education-card">
                    <div class="education-year">2022 - 2025</div>
                    <h4 class="education-school"><?php echo lang('about_school'); ?></h4>
                    <p class="education-major">
                        <i class="fas fa-graduation-cap"></i> <?php echo lang('about_major'); ?>
                    </p>
                    <p class="education-degree">
                        <i class="fas fa-star"></i> Degree classification: Good
                    </p>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <h2 class="section-title mb-4"><?php echo lang('about_skills'); ?></h2>
                <div class="skills-grid">
                    <div class="skill-category">
                        <h5><i class="fas fa-code"></i> Frontend Development</h5>
                        <div class="skill-tags">
                            <span class="skill-tag">HTML</span>
                            <span class="skill-tag">CSS</span>
                            <span class="skill-tag">JavaScript</span>
                            <span class="skill-tag">React.js</span>
                            <span class="skill-tag">Vue.js</span>
                            <span class="skill-tag">WordPress</span>
                            <span class="skill-tag">Bootstrap</span>

                            
                        </div>
                    </div>
                    <div class="skill-category">
                        <h5><i class="fas fa-server"></i> Backend Development</h5>
                        <div class="skill-tags">
                            <span class="skill-tag">Node.js</span>
                            <span class="skill-tag">PHP</span>
                            <span class="skill-tag">Python</span>
                            <span class="skill-tag">Java</span>
                            <span class="skill-tag">C#</span>

         
                            <span class="skill-tag">Laravel</span>
                            <span class="skill-tag">MySQL</span>
                            <span class="skill-tag">MongoDB</span>
                            <span class="skill-tag">PostgreSQL</span>
                            
                        </div>
                    </div>
                    <div class="skill-category">
                        <h5><i class="fas fa-mobile-alt"></i> Mobile Development</h5>
                        <div class="skill-tags">
                            <span class="skill-tag">Flutter</span>
                            <span class="skill-tag">Java </span>
                            <span class="skill-tag">Kotlin</span>
                            <span class="skill-tag">Mobile App Development</span>
                            <span class="skill-tag">React Native</span>
                            <span class="skill-tag">UI/UX Knowledge</span>

                        </div>
                    </div>
                    <div class="skill-category">
                        <h5><i class="fas fa-tools"></i> Tools & Environment</h5>
                        <div class="skill-tags">
                            <span class="skill-tag">Git/GitHub</span>
                            <span class="skill-tag">VS Code</span>
                            <span class="skill-tag">Docker</span>
                            <span class="skill-tag">Cursor</span>
                            <span class="skill-tag">Figma</span>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Objective Section -->
<section class="about-objective-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto" data-aos="fade-up">
                <div class="objective-card">
                    <h2 class="section-title text-center mb-4"><?php echo lang('about_objective'); ?></h2>
                    <p class="objective-text">
                        <?php echo lang('about_objective_text1'); ?>
                    </p>
                    <p class="objective-text">
                        <?php echo lang('about_objective_text2'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="about-values-section py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="section-title">Giá trị cốt lõi</h2>
            <p class="section-subtitle">Những nguyên tắc định hướng mọi hoạt động của chúng tôi</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h4>Tận tâm</h4>
                    <p>Đặt lợi ích khách hàng lên hàng đầu, luôn lắng nghe và thấu hiểu nhu cầu của từng khách hàng.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h4>Sáng tạo</h4>
                    <p>Không ngừng học hỏi, cập nhật công nghệ mới để mang đến những giải pháp sáng tạo và hiệu quả nhất.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4>Chất lượng</h4>
                    <p>Cam kết chất lượng dịch vụ, bảo hành trọn đời và hỗ trợ khách hàng 24/7 một cách tận tâm.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Template Repository Section -->
<section class="about-template-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                <div class="template-content">
                    <span class="template-badge">SỰ LỰA CHỌN HOÀN HẢO</span>
                    <h2 class="template-title">KHO GIAO DIỆN LỚN</h2>
                    <p class="template-subtitle">với hơn +900 mẫu, thoải mái chọn lựa</p>
                    <div class="template-divider"></div>
                    <p class="template-description">
                        Kho giao diện của <strong>DiamondDev Việt Nam</strong> đa dạng lĩnh vực và phong phú về số lượng 
                        được thiết kế bởi các chuyên gia sáng tạo hàng đầu. Website của bạn sẽ luôn đẹp và thu hút trên 
                        mọi thiết bị giúp bạn gia tăng doanh số nhanh chóng.
                    </p>
                    <a href="<?php echo SITE_URL; ?>" class="btn-template-primary">
                        XEM THÊM NHIỀU MẪU GIAO DIỆN <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="template-image-wrapper">
                    <img src="<?php echo SITE_URL; ?>/assets/images/10.png" 
                         alt="Kho giao diện DiamondDev" 
                         class="template-image">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Easy Management Section -->
<section class="about-management-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                <div class="management-image-wrapper">
                    <img src="<?php echo SITE_URL; ?>/assets/images/11.png" 
                         alt="Quản lý dễ dàng với DiamondDev" 
                         class="management-image">
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="management-content">
                    <h2 class="management-title">QUẢN LÝ DỄ DÀNG</h2>
                    <p class="management-subtitle">Đơn giản hóa việc quản trị web</p>
                    <div class="management-divider"></div>
                    <p class="management-description">
                        Với 10 năm kinh nghiệm làm web, chúng tôi hiểu rõ hơn ai hết, khách hàng muốn website phải 
                        cực kỳ dễ dàng quản lý. <strong>DiamondDev Việt Nam</strong> dễ quản lý hơn MS Word, vì vậy bạn 
                        yên tâm về cách sử dụng!
                    </p>
                    <p class="management-description">
                        Chỉ cần vi tính văn phòng và 30 phút tìm hiểu, bạn đã có thể làm chủ <strong>DiamondDev Việt Nam</strong>. 
                        Nhưng nếu vẫn chưa chinh phục được, bạn cứ thoải mái liên hệ, chuyên viên của chúng tôi sẽ 
                        hướng dẫn bạn tận tình.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Rich Features Section -->
<section class="about-features-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                <div class="features-content">
                    <h2 class="features-title">TÍNH NĂNG PHONG PHÚ</h2>
                    <p class="features-intro">
                        Không giới hạn tính năng, dễ dàng tùy biến thêm khi nhu cầu phát triển
                    </p>
                    <div class="features-divider"></div>
                    <p class="features-description">
                        Với 10 nhóm chính: <strong>Sản phẩm</strong>, <strong>tin tức</strong>, <strong>banner</strong>, 
                        <strong>dịch vụ</strong>, <strong>tuyển dụng</strong>, <strong>hỗ trợ trực tuyến</strong>, 
                        <strong>thành viên</strong>, <strong>đơn hàng</strong>, <strong>thanh toán</strong>, 
                        <strong>giới thiệu</strong> .. và hơn <strong>100 tính năng phụ</strong>, hệ thống 
                        <span class="highlight-text">DỊCH VỤ THIẾT KẾ WEBSITE</span> của 
                        <strong>DiamondDev Việt Nam</strong> đầy đủ nhu cầu của 1 website cao cấp.
                    </p>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="features-image-wrapper">
                    <img src="<?php echo SITE_URL; ?>/assets/images/12.png" 
                         alt="Tính năng phong phú DiamondDev" 
                         class="features-image">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Cost Optimization Section -->
<section class="about-cost-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                <div class="cost-image-wrapper">
                    <img src="<?php echo SITE_URL; ?>/assets/images/10.png" 
                         alt="Tối ưu chi phí với DiamondDev" 
                         class="cost-image">
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="cost-content">
                    <h2 class="cost-title">TỐI ƯU CHI PHÍ</h2>
                    <p class="cost-subtitle">Đầu tư thấp giá trị lâu dài</p>
                    <div class="cost-divider"></div>
                    <p class="cost-description">
                        Với doanh nghiệp mới, chi phí là điều rất đáng lo ngại. Chúng tôi đã làm tốt nhiệm vụ hỗ trợ 
                        khách hàng này, <strong>không phí phát sinh</strong>, <strong>không phí hosting</strong>, 
                        mua và sử dụng, <strong>trả phí hàng năm tiện lợi</strong>!
                    </p>
                    <p class="cost-description">
                        Theo thống kê của chúng tôi, khi sử dụng dịch vụ của chúng tôi, quý khách hàng đã tiết kiệm được 
                        hơn <strong class="highlight-amount">20 triệu VNĐ</strong> chi phí. Con số này không nhỏ, đủ để bạn có 
                        1 chiến lược Marketing hoành tráng cùng với chúng tôi.
                    </p>
                    <a href="tel:0355999141" class="btn-cost-primary">
                        <i class="fas fa-phone-alt"></i> GỌI NGAY 0355 999 141
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 24/7 Support Section -->
<section class="about-support-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                <div class="support-content">
                    <h2 class="support-title">HỖ TRỢ 24/7</h2>
                    <p class="support-subtitle">Tận tâm, chu đáo, trách nhiệm</p>
                    <div class="support-divider"></div>
                    <p class="support-description">
                        Hệ thống hỗ trợ khách hàng với quy trình quản lý khách hàng chuyên nghiệp, đảm bảo đáp ứng 
                        <strong>24/7</strong> những yêu cầu của khách hàng.
                    </p>
                    <p class="support-description">
                        Đội ngũ kỹ thuật cũng như CSKH luôn sẵn sàng nhận yêu cầu hỗ trợ của quý khách.
                    </p>
                    <div class="support-features mt-4">
                        <div class="support-feature-item">
                            <i class="fas fa-clock"></i>
                            <span>Hỗ trợ 24/7 không ngừng nghỉ</span>
                        </div>
                        <div class="support-feature-item">
                            <i class="fas fa-headset"></i>
                            <span>Đội ngũ CSKH chuyên nghiệp</span>
                        </div>
                        <div class="support-feature-item">
                            <i class="fas fa-tools"></i>
                            <span>Kỹ thuật viên luôn sẵn sàng</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="support-image-wrapper">
                    <img src="<?php echo SITE_URL; ?>/assets/images/13.png" 
                         alt="Hỗ trợ 24/7 DiamondDev" 
                         class="support-image">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="about-cta-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="cta-title">Sẵn sàng bắt đầu dự án của bạn?</h2>
                <p class="cta-text">Hãy liên hệ với chúng tôi ngay hôm nay để được tư vấn miễn phí!</p>
                <div class="cta-buttons mt-4">
                    <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-phone-alt"></i> Liên hệ ngay
                    </a>
                    <a href="<?php echo SITE_URL; ?>" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-home"></i> Về trang chủ
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* About Hero Section */
.about-hero-section {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    padding: 80px 0;
}

.about-image-wrapper {
    position: relative;
    max-width: 500px;
    margin: 0 auto;
}

.about-image-frame {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    z-index: 2;
}

.about-profile-image {
    width: 100%;
    height: auto;
    display: block;
    border-radius: 20px;
}

.about-image-decoration {
    position: absolute;
    top: -20px;
    left: -20px;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #4caf50 0%, #26a69a 100%);
    border-radius: 20px;
    z-index: 1;
    opacity: 0.1;
}

.about-hero-content {
    padding: 20px;
}

.about-badge {
    display: inline-block;
    background: linear-gradient(135deg, #4caf50 0%, #26a69a 100%);
    color: white;
    padding: 8px 20px;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 20px;
}

.about-hero-title {
    font-size: 3rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 10px;
    line-height: 1.2;
}

.about-hero-subtitle {
    font-size: 1.5rem;
    color: #4caf50;
    font-weight: 600;
    margin-bottom: 25px;
}

.about-hero-description {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #555;
    margin-bottom: 30px;
}

.about-contact-info .contact-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.about-contact-info .contact-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
}

.about-contact-info .contact-item i {
    color: #4caf50;
    font-size: 18px;
    width: 24px;
    text-align: center;
}

.about-contact-info .contact-item span {
    color: #333;
    font-size: 15px;
}

/* About Company Section */
.about-company-section {
    padding: 80px 0;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 15px;
}

.section-subtitle {
    font-size: 1.1rem;
    color: #666;
    line-height: 1.8;
}

.about-stat-card {
    text-align: center;
    padding: 40px 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.about-stat-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    background: linear-gradient(135deg, #4caf50 0%, #26a69a 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 10px;
}

.stat-label {
    font-size: 1rem;
    color: #666;
    margin: 0;
}

/* Education & Skills Section */
.about-skills-section {
    padding: 80px 0;
}

.education-card {
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
}

.education-year {
    display: inline-block;
    background: linear-gradient(135deg, #4caf50 0%, #26a69a 100%);
    color: white;
    padding: 8px 20px;
    border-radius: 25px;
    font-weight: 600;
    margin-bottom: 20px;
}

.education-school {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 15px;
}

.education-major,
.education-degree {
    font-size: 1.1rem;
    color: #555;
    margin-bottom: 10px;
}

.education-major i,
.education-degree i {
    color: #4caf50;
    margin-right: 8px;
}

.skills-grid {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.skill-category {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
}

.skill-category h5 {
    font-size: 1.2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 15px;
}

.skill-category h5 i {
    color: #4caf50;
    margin-right: 10px;
}

.skill-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.skill-tag {
    display: inline-block;
    padding: 8px 16px;
    background: linear-gradient(135deg, #4caf50 0%, #26a69a 100%);
    color: white;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.skill-tag:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
}

/* Objective Section */
.about-objective-section {
    padding: 80px 0;
}

.objective-card {
    background: white;
    padding: 50px;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.objective-text {
    font-size: 1.1rem;
    line-height: 1.9;
    color: #555;
    margin-bottom: 20px;
}

/* Values Section */
.about-values-section {
    padding: 80px 0;
}

.value-card {
    text-align: center;
    padding: 40px 30px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    height: 100%;
}

.value-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.value-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 25px;
    background: linear-gradient(135deg, #4caf50 0%, #26a69a 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
}

.value-card h4 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 15px;
}

.value-card p {
    font-size: 1rem;
    line-height: 1.7;
    color: #666;
    margin: 0;
}

/* Template Repository Section */
.about-template-section {
    padding: 100px 0;
    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    position: relative;
    overflow: hidden;
}

.about-template-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="%234caf50" opacity="0.1"/></svg>');
    opacity: 0.3;
}

.template-content {
    position: relative;
    z-index: 2;
    padding: 20px;
}

.template-badge {
    display: inline-block;
    color: #d32f2f;
    font-size: 1rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 15px;
}

.template-title {
    font-size: 3.5rem;
    font-weight: 800;
    color: #1b5e20;
    margin-bottom: 15px;
    line-height: 1.2;
}

.template-subtitle {
    font-size: 1.3rem;
    color: #666;
    font-weight: 500;
    margin-bottom: 20px;
}

.template-divider {
    width: 80px;
    height: 4px;
    background: linear-gradient(90deg, #1b5e20 0%, #4caf50 100%);
    border-radius: 2px;
    margin-bottom: 30px;
}

.template-description {
    font-size: 1.1rem;
    line-height: 1.9;
    color: #555;
    margin-bottom: 35px;
}

.template-description strong {
    color: #1b5e20;
    font-weight: 700;
}

.btn-template-primary {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #4caf50 0%, #26a69a 100%);
    color: white;
    padding: 16px 35px;
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 5px 20px rgba(76, 175, 80, 0.3);
    border: none;
}

.btn-template-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(76, 175, 80, 0.4);
    color: white;
    text-decoration: none;
}

.btn-template-primary i {
    transition: transform 0.3s ease;
}

.btn-template-primary:hover i {
    transform: translateX(5px);
}

.template-image-wrapper {
    position: relative;
    z-index: 2;
    text-align: center;
}

.template-image {
    width: 100%;
    height: auto;
    max-width: 600px;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    animation: floatImage 6s ease-in-out infinite;
}

@keyframes floatImage {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}

/* Easy Management Section */
.about-management-section {
    padding: 100px 0;
    background: #ffffff;
    position: relative;
    overflow: hidden;
}

.about-management-section::before {
    content: '';
    position: absolute;
    top: -100px;
    right: -100px;
    width: 300px;
    height: 300px;
    background: linear-gradient(135deg, rgba(255, 182, 193, 0.2) 0%, rgba(144, 238, 144, 0.2) 100%);
    border-radius: 50%;
    filter: blur(60px);
    z-index: 1;
}

.about-management-section::after {
    content: '';
    position: absolute;
    bottom: -100px;
    left: -100px;
    width: 300px;
    height: 300px;
    background: linear-gradient(135deg, rgba(144, 238, 144, 0.2) 0%, rgba(255, 182, 193, 0.2) 100%);
    border-radius: 50%;
    filter: blur(60px);
    z-index: 1;
}

.management-image-wrapper {
    position: relative;
    z-index: 2;
    text-align: center;
}

.management-image {
    width: 100%;
    height: auto;
    max-width: 550px;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
    animation: floatImage 6s ease-in-out infinite;
}

.management-content {
    position: relative;
    z-index: 2;
    padding: 20px;
}

.management-title {
    font-size: 3rem;
    font-weight: 800;
    color: #4caf50;
    margin-bottom: 15px;
    line-height: 1.2;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.management-subtitle {
    font-size: 1.3rem;
    color: #333;
    font-weight: 600;
    margin-bottom: 25px;
}

.management-divider {
    width: 80px;
    height: 4px;
    background: linear-gradient(90deg, #4caf50 0%, #26a69a 100%);
    border-radius: 2px;
    margin-bottom: 30px;
}

.management-description {
    font-size: 1.1rem;
    line-height: 1.9;
    color: #555;
    margin-bottom: 20px;
}

.management-description:last-child {
    margin-bottom: 0;
}

.management-description strong {
    color: #4caf50;
    font-weight: 700;
}

/* Rich Features Section */
.about-features-section {
    padding: 100px 0;
    background: linear-gradient(135deg, #f1f8f4 0%, #e8f5e9 100%);
    position: relative;
    overflow: hidden;
}

.about-features-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 30%, rgba(76, 175, 80, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(255, 152, 0, 0.05) 0%, transparent 50%);
    z-index: 1;
}

.features-content {
    position: relative;
    z-index: 2;
    padding: 20px;
}

.features-title {
    font-size: 3rem;
    font-weight: 800;
    color: #1b5e20;
    margin-bottom: 20px;
    line-height: 1.2;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.features-intro {
    font-size: 1.2rem;
    color: #555;
    font-weight: 500;
    margin-bottom: 25px;
    line-height: 1.6;
}

.features-divider {
    width: 80px;
    height: 4px;
    background: linear-gradient(90deg, #1b5e20 0%, #4caf50 100%);
    border-radius: 2px;
    margin-bottom: 30px;
}

.features-description {
    font-size: 1.1rem;
    line-height: 1.9;
    color: #555;
    margin-bottom: 0;
}

.features-description strong {
    color: #1b5e20;
    font-weight: 700;
}

.highlight-text {
    color: #ff6f00;
    font-weight: 700;
    font-size: 1.15rem;
}

.features-image-wrapper {
    position: relative;
    z-index: 2;
    text-align: center;
}

.features-image {
    width: 100%;
    height: auto;
    max-width: 600px;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
    animation: floatImage 6s ease-in-out infinite;
    animation-delay: 0.5s;
}

/* Cost Optimization Section */
.about-cost-section {
    padding: 100px 0;
    background: #ffffff;
    position: relative;
    overflow: hidden;
}

.about-cost-section::before {
    content: '';
    position: absolute;
    top: -150px;
    left: -150px;
    width: 400px;
    height: 400px;
    background: linear-gradient(135deg, rgba(76, 175, 80, 0.08) 0%, rgba(255, 193, 7, 0.08) 100%);
    border-radius: 50%;
    filter: blur(80px);
    z-index: 1;
}

.about-cost-section::after {
    content: '';
    position: absolute;
    bottom: -150px;
    right: -150px;
    width: 400px;
    height: 400px;
    background: linear-gradient(135deg, rgba(255, 193, 7, 0.08) 0%, rgba(76, 175, 80, 0.08) 100%);
    border-radius: 50%;
    filter: blur(80px);
    z-index: 1;
}

.cost-image-wrapper {
    position: relative;
    z-index: 2;
    text-align: center;
}

.cost-image {
    width: 100%;
    height: auto;
    max-width: 550px;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
    animation: floatImage 6s ease-in-out infinite;
    animation-delay: 1s;
}

.cost-content {
    position: relative;
    z-index: 2;
    padding: 20px;
}

.cost-title {
    font-size: 3rem;
    font-weight: 800;
    color: #4caf50;
    margin-bottom: 15px;
    line-height: 1.2;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.cost-subtitle {
    font-size: 1.3rem;
    color: #333;
    font-weight: 600;
    margin-bottom: 25px;
}

.cost-divider {
    width: 80px;
    height: 4px;
    background: linear-gradient(90deg, #4caf50 0%, #26a69a 100%);
    border-radius: 2px;
    margin-bottom: 30px;
}

.cost-description {
    font-size: 1.1rem;
    line-height: 1.9;
    color: #555;
    margin-bottom: 20px;
}

.cost-description:last-of-type {
    margin-bottom: 30px;
}

.cost-description strong {
    color: #1b5e20;
    font-weight: 700;
}

.highlight-amount {
    color: #ff6f00;
    font-weight: 800;
    font-size: 1.2rem;
}

.btn-cost-primary {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: linear-gradient(135deg, #4caf50 0%, #26a69a 100%);
    color: white;
    padding: 18px 40px;
    border-radius: 50px;
    font-size: 1.2rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 5px 25px rgba(76, 175, 80, 0.3);
    border: none;
}

.btn-cost-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 35px rgba(76, 175, 80, 0.4);
    color: white;
    text-decoration: none;
}

.btn-cost-primary i {
    font-size: 1.3rem;
    transition: transform 0.3s ease;
}

.btn-cost-primary:hover i {
    transform: scale(1.1);
}

/* 24/7 Support Section */
.about-support-section {
    padding: 100px 0;
    background: linear-gradient(135deg, #e3f2fd 0%, #f1f8f4 100%);
    position: relative;
    overflow: hidden;
}

.about-support-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 15% 20%, rgba(33, 150, 243, 0.08) 0%, transparent 50%),
        radial-gradient(circle at 85% 80%, rgba(76, 175, 80, 0.08) 0%, transparent 50%);
    z-index: 1;
}

.support-content {
    position: relative;
    z-index: 2;
    padding: 20px;
}

.support-title {
    font-size: 3rem;
    font-weight: 800;
    color: #1976d2;
    margin-bottom: 15px;
    line-height: 1.2;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.support-subtitle {
    font-size: 1.3rem;
    color: #333;
    font-weight: 600;
    margin-bottom: 25px;
}

.support-divider {
    width: 80px;
    height: 4px;
    background: linear-gradient(90deg, #1976d2 0%, #4caf50 100%);
    border-radius: 2px;
    margin-bottom: 30px;
}

.support-description {
    font-size: 1.1rem;
    line-height: 1.9;
    color: #555;
    margin-bottom: 20px;
}

.support-description:last-of-type {
    margin-bottom: 0;
}

.support-description strong {
    color: #1976d2;
    font-weight: 700;
}

.support-features {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-top: 30px;
}

.support-feature-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.support-feature-item:hover {
    transform: translateX(5px);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12);
}

.support-feature-item i {
    font-size: 1.5rem;
    color: #1976d2;
    width: 30px;
    text-align: center;
}

.support-feature-item span {
    font-size: 1rem;
    color: #333;
    font-weight: 500;
}

.support-image-wrapper {
    position: relative;
    z-index: 2;
    text-align: center;
}

.support-image {
    width: 100%;
    height: auto;
    max-width: 550px;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
    animation: floatImage 6s ease-in-out infinite;
    animation-delay: 1.5s;
}

/* CTA Section */
.about-cta-section {
    padding: 80px 0;
    background: linear-gradient(135deg, #4caf50 0%, #26a69a 100%);
    color: white;
}

.cta-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: white;
    margin-bottom: 20px;
}

.cta-text {
    font-size: 1.2rem;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 30px;
}

.cta-buttons .btn {
    padding: 15px 40px;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 50px;
    transition: all 0.3s ease;
}

.cta-buttons .btn-primary {
    background: white;
    color: #4caf50;
    border: 2px solid white;
}

.cta-buttons .btn-primary:hover {
    background: transparent;
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.cta-buttons .btn-outline-primary {
    background: transparent;
    color: white;
    border: 2px solid white;
}

.cta-buttons .btn-outline-primary:hover {
    background: white;
    color: #4caf50;
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

/* Responsive */
@media (max-width: 768px) {
    .about-hero-title {
        font-size: 2rem;
    }
    
    .about-hero-subtitle {
        font-size: 1.2rem;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .about-image-wrapper {
        max-width: 100%;
    }
    
    .objective-card {
        padding: 30px 20px;
    }
    
    .template-title {
        font-size: 2.5rem;
    }
    
    .template-subtitle {
        font-size: 1.1rem;
    }
    
    .template-description {
        font-size: 1rem;
    }
    
    .btn-template-primary {
        padding: 14px 25px;
        font-size: 1rem;
        width: 100%;
        justify-content: center;
    }
    
    .template-image {
        max-width: 100%;
    }
    
    .about-template-section {
        padding: 60px 0;
    }
    
    .management-title {
        font-size: 2.2rem;
    }
    
    .management-subtitle {
        font-size: 1.1rem;
    }
    
    .management-description {
        font-size: 1rem;
    }
    
    .management-image {
        max-width: 100%;
    }
    
    .about-management-section {
        padding: 60px 0;
    }
    
    .features-title {
        font-size: 2.2rem;
    }
    
    .features-intro {
        font-size: 1.1rem;
    }
    
    .features-description {
        font-size: 1rem;
    }
    
    .features-image {
        max-width: 100%;
    }
    
    .about-features-section {
        padding: 60px 0;
    }
    
    .cost-title {
        font-size: 2.2rem;
    }
    
    .cost-subtitle {
        font-size: 1.1rem;
    }
    
    .cost-description {
        font-size: 1rem;
    }
    
    .cost-image {
        max-width: 100%;
    }
    
    .btn-cost-primary {
        padding: 16px 30px;
        font-size: 1.1rem;
        width: 100%;
        justify-content: center;
    }
    
    .about-cost-section {
        padding: 60px 0;
    }
    
    .support-title {
        font-size: 2.2rem;
    }
    
    .support-subtitle {
        font-size: 1.1rem;
    }
    
    .support-description {
        font-size: 1rem;
    }
    
    .support-feature-item {
        padding: 12px 15px;
    }
    
    .support-feature-item i {
        font-size: 1.3rem;
    }
    
    .support-feature-item span {
        font-size: 0.95rem;
    }
    
    .support-image {
        max-width: 100%;
    }
    
    .about-support-section {
        padding: 60px 0;
    }
    
    .cta-title {
        font-size: 2rem;
    }
    
    .cta-buttons .btn {
        display: block;
        width: 100%;
        margin-bottom: 15px;
    }
    
    .cta-buttons .btn:last-child {
        margin-bottom: 0;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
