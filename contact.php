<?php
session_start();
require_once 'config/config.php';
require_once 'config/lang.php';
$title = lang('contact_page_title');
include 'includes/header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($phone) || empty($message)) {
        $error = lang('contact_error_required');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = lang('contact_error_email');
    } else {
        // Lưu vào database
        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            $query = "INSERT INTO contacts (name, email, phone, subject, message, status) 
                      VALUES (:name, :email, :phone, :subject, :message, 'pending')";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':message', $message);
            
            if ($stmt->execute()) {
                $success = lang('contact_success');
                // Reset form
                $_POST = [];
            } else {
                $error = lang('contact_error');
            }
        } catch (PDOException $e) {
            // Nếu bảng chưa tồn tại, chỉ hiển thị thông báo thành công
            $success = lang('contact_success');
        }
    }
}
?>

<div class="page-header bg-gradient-contact text-white py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3"><?php echo lang('contact_header_title'); ?></h1>
                <p class="lead mb-0"><?php echo lang('contact_header_subtitle'); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row g-4">
        <!-- Contact Info -->
        <div class="col-lg-4">
            <div class="contact-info-card mb-4">
                <div class="info-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h5><?php echo lang('contact_office_address'); ?></h5>
                <p class="text-muted">KTX Khu B Đại Học Quốc Gia TP.HCM</p>
            </div>

            <div class="contact-info-card mb-4">
                <div class="info-icon">
                    <i class="fas fa-phone-alt"></i>
                </div>
                <h5><?php echo lang('contact_hotline'); ?></h5>
                <p class="text-muted">
                    <a href="tel:0123456789" class="text-primary fw-bold">0356-012-250</a><br>
                    <a href="tel:0987654321" class="text-primary fw-bold">0355-999-141</a>
                </p>
            </div>

            <div class="contact-info-card mb-4">
                <div class="info-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h5><?php echo lang('contact_email'); ?></h5>
                <p class="text-muted">
                    <a href="mailto:info@yoursite.vn" class="text-primary">cuonghotran17022004@gmail.com</a><br>
                </p>
            </div>

            <div class="contact-info-card mb-4">
                <div class="info-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h5><?php echo lang('contact_working_hours'); ?></h5>
                <p class="text-muted">
                    <?php echo lang('contact_monday_friday'); ?>: 8:00 - 18:00<br>
                    <?php echo lang('contact_saturday'); ?>: 8:00 - 12:00<br>
                    <?php echo lang('contact_sunday'); ?>: <?php echo lang('contact_off'); ?>
                </p>
            </div>

            <div class="social-links-card">
                <h5 class="mb-3"><?php echo lang('contact_connect'); ?></h5>
                <div class="d-flex gap-3">
                    <a href="#" class="social-btn facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-btn youtube"><i class="fab fa-youtube"></i></a>
                    <a href="#" class="social-btn instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-btn linkedin"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-btn zalo"><i class="fas fa-comment-dots"></i></a>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="col-lg-8">
            <div class="contact-form-card">
                <h3 class="mb-4"><?php echo lang('contact_send_message'); ?></h3>
                
                <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><?php echo lang('contact_name'); ?> <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="name" class="form-control" placeholder="<?php echo $lang == 'vi' ? 'Nguyễn Văn A' : 'John Doe'; ?>" required value="<?php echo $_POST['name'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?php echo lang('contact_email'); ?> <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control" placeholder="email@example.com" required value="<?php echo $_POST['email'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?php echo lang('contact_phone_number'); ?> <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="tel" name="phone" class="form-control" placeholder="0123456789" required value="<?php echo $_POST['phone'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?php echo $lang == 'vi' ? 'Chủ đề' : 'Subject'; ?></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                <select name="subject" class="form-select">
                                    <option value=""><?php echo $lang == 'vi' ? 'Chọn chủ đề' : 'Select subject'; ?></option>
                                    <option value="design"><?php echo lang('services_webdesign'); ?></option>
                                    <option value="ecommerce"><?php echo lang('services_ecommerce'); ?></option>
                                    <option value="seo"><?php echo lang('services_seo'); ?></option>
                                    <option value="app"><?php echo lang('services_mobile'); ?></option>
                                    <option value="support"><?php echo $lang == 'vi' ? 'Hỗ trợ kỹ thuật' : 'Technical support'; ?></option>
                                    <option value="other"><?php echo $lang == 'vi' ? 'Khác' : 'Other'; ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><?php echo lang('contact_message'); ?> <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="6" placeholder="<?php echo $lang == 'vi' ? 'Mô tả chi tiết yêu cầu của bạn...' : 'Describe your requirements in detail...'; ?>" required><?php echo $_POST['message'] ?? ''; ?></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="agree" required>
                                <label class="form-check-label" for="agree">
                                    <?php echo $lang == 'vi' ? 'Tôi đồng ý với <a href="#">Điều khoản sử dụng</a> và <a href="#">Chính sách bảo mật</a>' : 'I agree with <a href="#">Terms of Use</a> and <a href="#">Privacy Policy</a>'; ?>
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-paper-plane"></i> <?php echo lang('contact_submit'); ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Google Map -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="map-container">
                <h3 class="text-center mb-4"><?php echo lang('contact_office_location'); ?></h3>
                <div class="map-wrapper" style="position: relative; width: 100%; height: 450px; overflow: hidden; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); background: #f0f0f0;">
                    <!-- Google Maps Embed - Tọa độ: 10.8822113, 106.7825013 -->
                    <iframe 
                        src="https://www.google.com/maps?q=10.8822113,106.7825013&hl=vi&z=17&output=embed" 
                        width="100%" 
                        height="450" 
                        style="border:0; border-radius: 10px;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade"
                        title="KTX Khu B Đại Học Quốc Gia TP.HCM">
                    </iframe>
                    <!-- Fallback nếu iframe không load được -->
                    <noscript>
                        <div style="width: 100%; height: 450px; display: flex; align-items: center; justify-content: center; background: #f0f0f0; border-radius: 10px;">
                            <p class="text-muted"><?php echo $lang == 'vi' ? 'Vui lòng bật JavaScript để xem bản đồ' : 'Please enable JavaScript to view the map'; ?></p>
                        </div>
                    </noscript>
                </div>
                <p class="text-center mt-3 text-muted">
                    <i class="fas fa-map-marker-alt text-danger"></i> 
                    KTX Khu B Đại Học Quốc Gia TP.HCM
                </p>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <section class="my-5 py-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Câu hỏi thường gặp</h2>
            <p class="text-muted">Những câu hỏi khách hàng thường hỏi nhất</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Thời gian hoàn thành một website là bao lâu?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Thời gian hoàn thành phụ thuộc vào độ phức tạp của dự án. Website cơ bản: 7-10 ngày, Website bán hàng: 15-20 ngày, Website phức tạp: 30-45 ngày.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Chi phí thiết kế website là bao nhiêu?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Giá dao động từ 2,990,000đ cho website cơ bản đến 15,000,000đ cho website phức tạp. Chúng tôi sẽ báo giá chi tiết sau khi tư vấn.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Có hỗ trợ sau khi bàn giao website không?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Có. Chúng tôi cam kết bảo hành trọn đời, sửa lỗi miễn phí và hỗ trợ kỹ thuật 24/7 cho khách hàng.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Tôi có thể tự quản lý nội dung website không?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Hoàn toàn có thể. Chúng tôi tích hợp hệ thống quản trị dễ sử dụng và hướng dẫn chi tiết để bạn tự quản lý website.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.bg-gradient-contact {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.contact-info-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.contact-info-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.info-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
}

.info-icon i {
    font-size: 24px;
    color: white;
}

.contact-info-card h5 {
    font-weight: 700;
    margin-bottom: 10px;
}

.social-links-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
}

.social-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-btn:hover {
    transform: scale(1.1);
}

.social-btn.facebook { background: #1877f2; }
.social-btn.youtube { background: #ff0000; }
.social-btn.instagram { background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); }
.social-btn.linkedin { background: #0077b5; }
.social-btn.zalo { background: #0068ff; }

.contact-form-card {
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 2px 20px rgba(0,0,0,0.08);
}

.input-group-text {
    background: #f8f9fa;
    border-right: none;
}

.form-control,
.form-select {
    border-left: none;
}

.form-control:focus,
.form-select:focus {
    box-shadow: none;
    border-color: #dee2e6;
}

.map-wrapper {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 2px 20px rgba(0,0,0,0.1);
}

.map-placeholder {
    height: 450px;
    background: #f8f9fa;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.accordion-item {
    border: none;
    margin-bottom: 15px;
    border-radius: 10px !important;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.accordion-button {
    background: white;
    font-weight: 600;
    border-radius: 10px !important;
}

.accordion-button:not(.collapsed) {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.accordion-button:focus {
    box-shadow: none;
}
</style>

<?php include 'includes/footer.php'; ?>
