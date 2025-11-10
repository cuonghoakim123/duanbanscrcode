-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 10, 2025 lúc 10:19 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `ecommerce_db`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `parent_id`, `status`, `created_at`, `updated_at`) VALUES
(5, 'Những mẫu giao diện đẹp đa ngành nghề', 'https://gugo.vn/wp-content/uploads/2021/06/fb1088de81e42c4e538967ec12cb5caa.png', 'Tuyển tập các mẫu giao diện website đẹp, được thiết kế tinh tế và chuyên nghiệp, có khả năng ứng dụng linh hoạt cho nhiều ngành nghề và lĩnh vực khác nhau. Cho dù bạn cần website cho doanh nghiệp, cửa hàng, dịch vụ, hay blog cá nhân, các mẫu theme đa ngành nghề này đều cung cấp một nền tảng vững chắc. Tất cả đều được tối ưu chuẩn SEO, tương thích hoàn hảo trên mọi thiết bị (responsive) và cho phép bạn tùy biến dễ dàng để tạo nên dấu ấn thương hiệu riêng.', NULL, NULL, 'active', '2025-11-08 12:44:45', '2025-11-08 12:44:45');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message_type` enum('user','bot') NOT NULL,
  `message` text NOT NULL,
  `ai_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `session_id`, `user_id`, `message_type`, `message`, `ai_response`, `created_at`) VALUES
(1, 'chat_1762613234697_nmb0t8c8v', NULL, 'user', 'hello', NULL, '2025-11-08 14:47:14'),
(2, 'chat_1762613234697_nmb0t8c8v', NULL, 'user', 'a', NULL, '2025-11-08 14:49:36'),
(3, 'chat_1762613234697_nmb0t8c8v', NULL, 'user', 'a', NULL, '2025-11-08 14:53:58'),
(4, 'chat_1762613234697_nmb0t8c8v', NULL, 'user', 'Giá thiết kế website là bao nhiêu?', NULL, '2025-11-08 14:55:08'),
(5, 'chat_1762613234697_nmb0t8c8v', NULL, 'bot', 'Giá thiết kế website là bao nhiêu?', 'Chúng tôi có các gói dịch vụ thiết kế website với giá từ 2-4 triệu đồng. Gói cơ bản từ 2 triệu bao gồm đầy đủ tính năng bán hàng online, responsive, SEO cơ bản. Gói cao cấp từ 4 triệu có thêm nhiều tính năng nâng cao và hỗ trợ chuyên sâu. Bạn muốn tư vấn chi tiết về gói nào?', '2025-11-08 14:55:08'),
(6, 'chat_1762613234697_nmb0t8c8v', NULL, 'user', 'Thời gian hoàn thành website?', NULL, '2025-11-08 14:55:15'),
(7, 'chat_1762613234697_nmb0t8c8v', NULL, 'bot', 'Thời gian hoàn thành website?', 'Thời gian hoàn thiện website khoảng 15 ngày làm việc. Để quá trình được nhanh chóng, bạn cần chuẩn bị: Logo công ty (nếu có), hình ảnh sắc nét về sản phẩm/dịch vụ, và thông tin giới thiệu về doanh nghiệp. Chúng tôi sẽ hỗ trợ bạn từ A đến Z!', '2025-11-08 14:55:15'),
(8, 'chat_1762613234697_nmb0t8c8v', 2, 'user', 'Giá thiết kế website là bao nhiêu?', NULL, '2025-11-09 15:02:39'),
(9, 'chat_1762613234697_nmb0t8c8v', 2, 'bot', 'Giá thiết kế website là bao nhiêu?', 'Chúng tôi có các gói dịch vụ thiết kế website với giá từ 2-4 triệu đồng. Gói cơ bản từ 2 triệu bao gồm đầy đủ tính năng bán hàng online, responsive, SEO cơ bản. Gói cao cấp từ 4 triệu có thêm nhiều tính năng nâng cao và hỗ trợ chuyên sâu. Bạn muốn tư vấn chi tiết về gói nào?', '2025-11-09 15:02:39');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chat_sessions`
--

CREATE TABLE `chat_sessions` (
  `id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chat_sessions`
--

INSERT INTO `chat_sessions` (`id`, `session_id`, `user_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 'chat_1762613234697_nmb0t8c8v', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-11-08 14:47:14', '2025-11-08 14:47:14');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('pending','read','replied','archived') DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `admin_note`, `created_at`, `updated_at`) VALUES
(1, 'Nguyễn cương', 'cuonghotran17022004@gmail.com', '0356012250', 'design', 'sdsadsa', 'pending', NULL, '2025-11-09 16:05:26', '2025-11-09 16:05:26');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `featured` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive','draft') DEFAULT 'active',
  `author_id` int(11) DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `news`
--

INSERT INTO `news` (`id`, `title`, `slug`, `excerpt`, `content`, `image`, `category`, `views`, `featured`, `status`, `author_id`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 'Xu hướng thiết kế website 2025: Minimalism và AI', 'xu-huong-thiet-ke-website-2025-minimalism-va-ai', 'Khám phá những xu hướng thiết kế website sẽ thống trị năm 2025 từ phong cách tối giản đến ứng dụng AI trong UX/UI.', '<p>Năm 2025 đánh dấu sự chuyển mình mạnh mẽ trong lĩnh vực thiết kế website. Hai xu hướng nổi bật nhất là Minimalism và ứng dụng AI trong UX/UI design.</p>\r\n    \r\n    <h3>Minimalism - Tối giản nhưng không đơn giản</h3>\r\n    <p>Thiết kế tối giản tiếp tục thống trị với nguyên tắc \"less is more\". Người dùng ngày càng yêu thích những giao diện sạch sẽ, tập trung vào nội dung chính.</p>\r\n    \r\n    <h3>AI trong UX/UI Design</h3>\r\n    <p>Trí tuệ nhân tạo đang cách mạng hóa cách chúng ta thiết kế và tối ưu trải nghiệm người dùng. Từ việc tự động đề xuất layout đến phân tích hành vi người dùng, AI giúp tạo ra những website thông minh và cá nhân hóa hơn.</p>\r\n    \r\n    <p>Hãy cùng khám phá những xu hướng này và cách áp dụng vào dự án website của bạn!</p>', 'https://www.saokim.com.vn/wp-content/uploads/2025/01/xu-huong-thiet-ke-website-AI-va-ca-nhan-hoa.jpg', 'Thiết kế', 1250, 1, 'active', NULL, '2025-01-15 10:00:00', '2025-11-09 16:24:55', '2025-11-09 16:26:31'),
(2, '5 lý do tại sao doanh nghiệp cần có website riêng', '5-ly-do-tai-sao-doanh-nghiep-can-co-website-rieng', 'Trong thời đại số, việc sở hữu một website chuyên nghiệp không chỉ là lựa chọn mà là điều cần thiết cho mọi doanh nghiệp.', '<p>Trong thời đại số hóa ngày nay, việc sở hữu một website chuyên nghiệp đã trở thành yêu cầu bắt buộc cho mọi doanh nghiệp muốn phát triển bền vững.</p>\r\n    \r\n    <h3>1. Tăng độ tin cậy và uy tín</h3>\r\n    <p>Website chuyên nghiệp giúp doanh nghiệp xây dựng hình ảnh thương hiệu, tăng độ tin cậy trong mắt khách hàng.</p>\r\n    \r\n    <h3>2. Tiếp cận khách hàng 24/7</h3>\r\n    <p>Website hoạt động 24/7, cho phép khách hàng tìm hiểu về sản phẩm/dịch vụ bất cứ lúc nào, bất cứ đâu.</p>\r\n    \r\n    <h3>3. Mở rộng thị trường</h3>\r\n    <p>Website giúp doanh nghiệp vượt qua rào cản địa lý, tiếp cận khách hàng trên toàn quốc và quốc tế.</p>\r\n    \r\n    <h3>4. Tiết kiệm chi phí marketing</h3>\r\n    <p>So với các hình thức quảng cáo truyền thống, website là công cụ marketing hiệu quả và tiết kiệm chi phí nhất.</p>\r\n    \r\n    <h3>5. Tăng doanh số bán hàng</h3>\r\n    <p>Website với giao diện đẹp, dễ sử dụng sẽ tăng tỷ lệ chuyển đổi, từ đó tăng doanh số bán hàng.</p>', 'https://www.twf.vn/sites/default/files/styles/blog_timeline/public/ly-do-can-thiet-ke-website.jpg?itok=Mxf32KZ6', 'Kinh doanh', 980, 0, 'active', NULL, '2025-01-10 09:30:00', '2025-11-09 16:24:55', '2025-11-10 06:13:22'),
(3, 'Hướng dẫn tối ưu SEO cho website bán hàng', 'huong-dan-toi-uu-seo-cho-website-ban-hang', 'Những kỹ thuật SEO cơ bản và nâng cao giúp website bán hàng của bạn lên top Google, tăng traffic tự nhiên.', '<p>SEO (Search Engine Optimization) là yếu tố quyết định thành công của website bán hàng. Dưới đây là những kỹ thuật SEO hiệu quả nhất.</p>\r\n    \r\n    <h3>1. Tối ưu từ khóa</h3>\r\n    <p>Nghiên cứu và sử dụng từ khóa phù hợp trong tiêu đề, mô tả, và nội dung sản phẩm.</p>\r\n    \r\n    <h3>2. Tối ưu hình ảnh</h3>\r\n    <p>Đặt tên file ảnh có ý nghĩa, thêm alt text mô tả sản phẩm, nén ảnh để tăng tốc độ tải trang.</p>\r\n    \r\n    <h3>3. Tối ưu tốc độ website</h3>\r\n    <p>Tốc độ tải trang là yếu tố quan trọng trong xếp hạng SEO. Sử dụng CDN, nén file, tối ưu database.</p>\r\n    \r\n    <h3>4. Xây dựng backlink chất lượng</h3>\r\n    <p>Backlink từ các website uy tín giúp tăng độ tin cậy và thứ hạng trên Google.</p>\r\n    \r\n    <h3>5. Tạo nội dung chất lượng</h3>\r\n    <p>Nội dung hữu ích, độc đáo và cập nhật thường xuyên sẽ thu hút cả người dùng và công cụ tìm kiếm.</p>', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS2E9mni8SdfLBwwu6rFrqhzBk19GFe_QjeBg&s', 'SEO', 1500, 0, 'active', NULL, '2025-01-05 14:20:00', '2025-11-09 16:24:55', '2025-11-10 06:13:56'),
(4, 'So sánh WordPress vs Website Code thuần', 'so-sanh-wordpress-vs-website-code-thuan', 'Phân tích ưu nhược điểm của WordPress và website code thuần để lựa chọn giải pháp phù hợp với nhu cầu.', '<p>WordPress và website code thuần là hai giải pháp phổ biến nhất để xây dựng website. Mỗi giải pháp đều có ưu nhược điểm riêng.</p>\r\n    \r\n    <h3>WordPress - Nền tảng CMS phổ biến</h3>\r\n    <h4>Ưu điểm:</h4>\r\n    <ul>\r\n        <li>Dễ sử dụng, không cần kiến thức lập trình</li>\r\n        <li>Nhiều theme và plugin có sẵn</li>\r\n        <li>Cộng đồng hỗ trợ lớn</li>\r\n        <li>Dễ bảo trì và cập nhật</li>\r\n    </ul>\r\n    <h4>Nhược điểm:</h4>\r\n    <ul>\r\n        <li>Hiệu năng có thể chậm với nhiều plugin</li>\r\n        <li>Bảo mật phụ thuộc vào plugin và theme</li>\r\n        <li>Khó tùy chỉnh sâu nếu không biết code</li>\r\n    </ul>\r\n    \r\n    <h3>Website Code thuần - Linh hoạt và tối ưu</h3>\r\n    <h4>Ưu điểm:</h4>\r\n    <ul>\r\n        <li>Hiệu năng cao, tải nhanh</li>\r\n        <li>Bảo mật tốt hơn</li>\r\n        <li>Tùy chỉnh hoàn toàn theo ý muốn</li>\r\n        <li>Không phụ thuộc vào plugin bên thứ ba</li>\r\n    </ul>\r\n    <h4>Nhược điểm:</h4>\r\n    <ul>\r\n        <li>Cần kiến thức lập trình</li>\r\n        <li>Thời gian phát triển lâu hơn</li>\r\n        <li>Chi phí ban đầu cao hơn</li>\r\n    </ul>\r\n    \r\n    <p>Việc lựa chọn giữa WordPress và code thuần phụ thuộc vào nhu cầu, ngân sách và khả năng kỹ thuật của bạn.</p>', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS3xiG0wN0XHihUfFn0ckB93Uo8fl9PiAjVdQ&s', 'Công nghệ', 875, 0, 'active', NULL, '2024-12-28 11:15:00', '2025-11-09 16:24:55', '2025-11-10 06:14:39'),
(5, 'Cách tăng tốc độ tải website gấp 3 lần', 'cach-tang-toc-do-tai-website-gap-3-lan', 'Những thủ thuật đơn giản nhưng hiệu quả để cải thiện đáng kể tốc độ tải trang của website.', '<p>Tốc độ tải trang là yếu tố quan trọng ảnh hưởng trực tiếp đến trải nghiệm người dùng và thứ hạng SEO. Dưới đây là những cách đơn giản để tăng tốc độ website.</p>\r\n    \r\n    <h3>1. Tối ưu hình ảnh</h3>\r\n    <p>Nén ảnh, sử dụng format WebP, lazy loading để giảm dung lượng và tăng tốc độ tải.</p>\r\n    \r\n    <h3>2. Sử dụng CDN</h3>\r\n    <p>Content Delivery Network giúp phân phối nội dung từ server gần người dùng nhất, giảm độ trễ.</p>\r\n    \r\n    <h3>3. Minify CSS và JavaScript</h3>\r\n    <p>Loại bỏ khoảng trắng, comment không cần thiết để giảm kích thước file.</p>\r\n    \r\n    <h3>4. Bật caching</h3>\r\n    <p>Sử dụng browser caching và server caching để lưu trữ tạm thời các file tĩnh.</p>\r\n    \r\n    <h3>5. Tối ưu database</h3>\r\n    <p>Xóa dữ liệu không cần thiết, tối ưu truy vấn, sử dụng index để tăng tốc độ truy xuất.</p>\r\n    \r\n    <h3>6. Sử dụng HTTP/2</h3>\r\n    <p>HTTP/2 cho phép tải nhiều file đồng thời, giảm thời gian chờ đợi.</p>\r\n    \r\n    <p>Áp dụng những thủ thuật này, bạn có thể tăng tốc độ website lên gấp 3 lần, cải thiện đáng kể trải nghiệm người dùng.</p>', 'https://eqvn.net/wp-content/uploads/2023/01/tang-toc-website.jpg', 'Tối ưu', 1100, 0, 'active', NULL, '2024-12-20 16:45:00', '2025-11-09 16:24:55', '2025-11-10 06:15:00'),
(6, 'Bảo mật website: 10 điều cần làm ngay', 'bao-mat-website-10-dieu-can-lam-ngay', 'Checklist bảo mật cơ bản giúp website của bạn an toàn trước các mối đe dọa phổ biến.', '<p>Bảo mật website là ưu tiên hàng đầu trong thời đại số. Dưới đây là 10 điều bạn cần làm ngay để bảo vệ website của mình.</p>\r\n    \r\n    <h3>1. Sử dụng SSL/HTTPS</h3>\r\n    <p>Mã hóa dữ liệu truyền tải giữa trình duyệt và server, bảo vệ thông tin người dùng.</p>\r\n    \r\n    <h3>2. Cập nhật phần mềm thường xuyên</h3>\r\n    <p>Cập nhật CMS, plugin, theme để vá các lỗ hổng bảo mật.</p>\r\n    \r\n    <h3>3. Sử dụng mật khẩu mạnh</h3>\r\n    <p>Mật khẩu phức tạp, kết hợp chữ hoa, chữ thường, số và ký tự đặc biệt.</p>\r\n    \r\n    <h3>4. Backup định kỳ</h3>\r\n    <p>Backup dữ liệu thường xuyên để có thể khôi phục khi bị tấn công.</p>\r\n    \r\n    <h3>5. Giới hạn đăng nhập</h3>\r\n    <p>Giới hạn số lần đăng nhập sai để chống brute force attack.</p>\r\n    \r\n    <h3>6. Ẩn thông tin server</h3>\r\n    <p>Ẩn version của server, CMS để tránh bị khai thác lỗ hổng.</p>\r\n    \r\n    <h3>7. Sử dụng firewall</h3>\r\n    <p>Web Application Firewall (WAF) giúp chặn các cuộc tấn công phổ biến.</p>\r\n    \r\n    <h3>8. Kiểm tra quyền file</h3>\r\n    <p>Đặt quyền file và thư mục đúng cách (644 cho file, 755 cho thư mục).</p>\r\n    \r\n    <h3>9. Xóa file không cần thiết</h3>\r\n    <p>Xóa file cài đặt, file test, file backup không cần thiết trên server.</p>\r\n    \r\n    <h3>10. Giám sát website</h3>\r\n    <p>Sử dụng công cụ giám sát để phát hiện sớm các hoạt động bất thường.</p>\r\n    \r\n    <p>Áp dụng những biện pháp này sẽ giúp website của bạn an toàn hơn trước các mối đe dọa bảo mật.</p>', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRYoyiWWtFl_jDXPc0mmVg-kfdQ5eJLeDUO-w&s', 'Bảo mật', 750, 0, 'active', NULL, '2024-12-15 10:30:00', '2025-11-09 16:24:55', '2025-11-10 06:15:29'),
(7, 'Mobile First: Thiết kế web ưu tiên di động', 'mobile-first-thiet-ke-web-uu-tien-di-dong', 'Tại sao Mobile First là chiến lược thiết kế quan trọng nhất hiện nay và cách áp dụng hiệu quả.', '<p>Với hơn 60% người dùng truy cập internet qua thiết bị di động, Mobile First đã trở thành chiến lược thiết kế bắt buộc.</p>\r\n    \r\n    <h3>Tại sao Mobile First?</h3>\r\n    <p>Thiết kế Mobile First giúp website tối ưu cho thiết bị di động từ đầu, đảm bảo trải nghiệm tốt nhất cho đại đa số người dùng.</p>\r\n    \r\n    <h3>Nguyên tắc Mobile First</h3>\r\n    <ul>\r\n        <li>Thiết kế cho màn hình nhỏ trước, sau đó mở rộng cho màn hình lớn</li>\r\n        <li>Ưu tiên nội dung quan trọng, loại bỏ yếu tố không cần thiết</li>\r\n        <li>Tối ưu tốc độ tải trên mạng di động</li>\r\n        <li>Thiết kế touch-friendly, nút bấm đủ lớn</li>\r\n    </ul>\r\n    \r\n    <h3>Cách áp dụng Mobile First</h3>\r\n    <ol>\r\n        <li>Bắt đầu với layout đơn giản cho mobile</li>\r\n        <li>Sử dụng CSS Media Queries để mở rộng cho desktop</li>\r\n        <li>Test trên nhiều thiết bị và trình duyệt khác nhau</li>\r\n        <li>Tối ưu hình ảnh và font chữ cho mobile</li>\r\n    </ol>\r\n    \r\n    <h3>Lợi ích của Mobile First</h3>\r\n    <ul>\r\n        <li>Tăng trải nghiệm người dùng trên mobile</li>\r\n        <li>Cải thiện thứ hạng SEO (Google ưu tiên mobile-friendly)</li>\r\n        <li>Tăng tỷ lệ chuyển đổi</li>\r\n        <li>Giảm bounce rate</li>\r\n    </ul>\r\n    \r\n    <p>Mobile First không chỉ là xu hướng mà là yêu cầu bắt buộc trong thiết kế website hiện đại.</p>', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRg33XmG2fiKwPvxjhkoS99CaHqh7IYyb13CA&s', 'Thiết kế', 920, 0, 'active', NULL, '2024-12-10 13:20:00', '2025-11-09 16:24:55', '2025-11-10 06:15:51'),
(8, 'Chatbot AI: Tương lai của chăm sóc khách hàng', 'chatbot-ai-tuong-lai-cua-cham-soc-khach-hang', 'Ứng dụng chatbot AI trên website giúp tự động hóa chăm sóc khách hàng 24/7 hiệu quả.', '<p>Chatbot AI đang cách mạng hóa cách doanh nghiệp tương tác với khách hàng, mang lại trải nghiệm tốt hơn và hiệu quả hơn.</p>\r\n    \r\n    <h3>Chatbot AI là gì?</h3>\r\n    <p>Chatbot AI là phần mềm sử dụng trí tuệ nhân tạo để tự động trả lời câu hỏi và hỗ trợ khách hàng thông qua chat.</p>\r\n    \r\n    <h3>Lợi ích của Chatbot AI</h3>\r\n    <ul>\r\n        <li><strong>24/7 Support:</strong> Hỗ trợ khách hàng mọi lúc, mọi nơi</li>\r\n        <li><strong>Tiết kiệm chi phí:</strong> Giảm chi phí nhân sự chăm sóc khách hàng</li>\r\n        <li><strong>Phản hồi nhanh:</strong> Trả lời ngay lập tức, không cần chờ đợi</li>\r\n        <li><strong>Xử lý đa nhiệm:</strong> Có thể hỗ trợ nhiều khách hàng cùng lúc</li>\r\n        <li><strong>Học hỏi liên tục:</strong> AI cải thiện khả năng trả lời theo thời gian</li>\r\n    </ul>\r\n    \r\n    <h3>Ứng dụng Chatbot AI</h3>\r\n    <ul>\r\n        <li>Trả lời câu hỏi thường gặp (FAQ)</li>\r\n        <li>Hỗ trợ đặt hàng và thanh toán</li>\r\n        <li>Theo dõi đơn hàng</li>\r\n        <li>Tư vấn sản phẩm/dịch vụ</li>\r\n        <li>Thu thập feedback từ khách hàng</li>\r\n    </ul>\r\n    \r\n    <h3>Cách tích hợp Chatbot AI</h3>\r\n    <ol>\r\n        <li>Chọn platform chatbot phù hợp (Dialogflow, IBM Watson, v.v.)</li>\r\n        <li>Xây dựng knowledge base với câu hỏi và câu trả lời</li>\r\n        <li>Tích hợp vào website qua API hoặc widget</li>\r\n        <li>Test và điều chỉnh để cải thiện độ chính xác</li>\r\n        <li>Giám sát và cập nhật thường xuyên</li>\r\n    </ol>\r\n    \r\n    <p>Chatbot AI không chỉ là công cụ hỗ trợ mà còn là cầu nối quan trọng giữa doanh nghiệp và khách hàng trong thời đại số.</p>', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcScXXSI8eHY_QGv0CnDV3bnp0SqlfZpHPiQgw&s', 'AI', 1350, 0, 'active', NULL, '2024-12-05 15:10:00', '2025-11-09 16:24:55', '2025-11-10 06:16:25'),
(9, 'Landing Page hiệu quả: Bí quyết chuyển đổi cao', 'landing-page-hieu-qua-bi-quyet-chuyen-doi-cao', 'Các yếu tố then chốt để tạo landing page có tỷ lệ chuyển đổi cao, tăng doanh số bán hàng.', '<p>Landing Page là trang đích quan trọng nhất trong chiến dịch marketing, quyết định trực tiếp đến tỷ lệ chuyển đổi và doanh số.</p>\r\n    \r\n    <h3>Yếu tố then chốt của Landing Page hiệu quả</h3>\r\n    \r\n    <h3>1. Headline hấp dẫn</h3>\r\n    <p>Tiêu đề rõ ràng, ngắn gọn, thể hiện giá trị cốt lõi và lợi ích người dùng nhận được.</p>\r\n    \r\n    <h3>2. Call-to-Action (CTA) rõ ràng</h3>\r\n    <p>Nút CTA nổi bật, màu sắc thu hút, text hành động mạnh mẽ (Đăng ký ngay, Mua ngay, v.v.)</p>\r\n    \r\n    <h3>3. Social Proof</h3>\r\n    <p>Đánh giá, testimonial, số liệu thống kê từ khách hàng để tăng độ tin cậy.</p>\r\n    \r\n    <h3>4. Thiết kế đơn giản, tập trung</h3>\r\n    <p>Loại bỏ yếu tố gây phân tâm, tập trung vào mục tiêu chuyển đổi chính.</p>\r\n    \r\n    <h3>5. Tối ưu cho mobile</h3>\r\n    <p>Đảm bảo landing page hiển thị và hoạt động tốt trên mọi thiết bị di động.</p>\r\n    \r\n    <h3>6. Form đơn giản</h3>\r\n    <p>Chỉ thu thập thông tin cần thiết, giảm số trường điền để tăng tỷ lệ hoàn thành form.</p>\r\n    \r\n    <h3>7. Tốc độ tải nhanh</h3>\r\n    <p>Landing page phải tải nhanh để không làm mất khách hàng tiềm năng.</p>\r\n    \r\n    <h3>8. A/B Testing</h3>\r\n    <p>Test nhiều phiên bản khác nhau để tìm ra phiên bản có tỷ lệ chuyển đổi cao nhất.</p>\r\n    \r\n    <h3>Bí quyết tăng chuyển đổi</h3>\r\n    <ul>\r\n        <li>Sử dụng video để giới thiệu sản phẩm/dịch vụ</li>\r\n        <li>Tạo cảm giác khẩn trương (limited time offer)</li>\r\n        <li>Hiển thị giá trị và lợi ích rõ ràng</li>\r\n        <li>Sử dụng màu sắc phù hợp với thương hiệu</li>\r\n        <li>Thêm trust badges và security seals</li>\r\n    </ul>\r\n    \r\n    <p>Với những yếu tố này, bạn có thể tạo landing page có tỷ lệ chuyển đổi cao, tăng đáng kể doanh số bán hàng.</p>', 'https://d1j8r0kxyu9tj8.cloudfront.net/files/1669121896QkOrernQKgjzcw3.jpg', 'Marketing', 1050, 0, 'active', NULL, '2024-11-28 09:00:00', '2025-11-09 16:24:55', '2025-11-10 06:16:43');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_code` varchar(50) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `note` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_fee` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `payment_method` enum('cod','momo','vnpay','bank_transfer') NOT NULL,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `order_status` enum('pending','confirmed','processing','shipping','completed','cancelled') DEFAULT 'pending',
  `transaction_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `sku` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `gallery` text DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `featured` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `content`, `price`, `sale_price`, `quantity`, `sku`, `image`, `gallery`, `views`, `featured`, `status`, `created_at`, `updated_at`) VALUES
(5, 5, 'Mẫu webssite bán nội thất 44', 'mau-webssite-ban-noi-that-44', '✨ Các Tính Năng Nổi Bật\r\nXây dựng trên nền tảng WordPress: Như mô tả ngắn đã nêu, mẫu website sử dụng mã nguồn mở WordPress phiên bản mới nhất. Điều này đảm bảo tính bảo mật, ổn định và cho phép bạn tận dụng kho plugin khổng lồ để mở rộng tính năng bất cứ lúc nào.\r\n\r\nThiết kế Giao diện Hiện Đại & Tinh Tế:\r\n\r\nBố cục (layout) được thiết kế tập trung vào hình ảnh sản phẩm, với các khối module và banner sắp xếp khoa học, giúp khách hàng dễ dàng tìm kiếm và khám phá sản phẩm.\r\n\r\nPhong cách sang trọng, phù hợp với ngành hàng nội thất và trang trí cao cấp.\r\n\r\nTối ưu Chuẩn SEO & Tương thích Mọi Thiết Bị (Responsive):\r\n\r\nGiao diện được tối ưu chuẩn SEO on-page từ cấu trúc code, thẻ tiêu đề, đến tốc độ tải trang. Điều này giúp website của bạn dễ dàng đạt thứ hạng cao trên các công cụ tìm kiếm như Google.\r\n\r\nThiết kế responsive 100%, tự động co giãn và hiển thị hoàn hảo trên mọi kích thước màn hình, từ PC, laptop đến máy tính bảng và điện thoại di động, mang lại trải nghiệm người dùng nhất quán.\r\n\r\nTính Năng Thương Mại Điện Tử Đầy Đủ (WooCommerce):\r\n\r\nQuản lý sản phẩm: Đăng tải sản phẩm dễ dàng với đầy đủ thông tin: tên, hình ảnh (gallery, zoom), giá, mô tả, thuộc tính (màu sắc, kích thước, chất liệu...).\r\n\r\nQuản lý kho hàng: Theo dõi số lượng tồn kho tự động.\r\n\r\nGiỏ hàng & Thanh toán: Quy trình giỏ hàng và thanh toán được tối ưu hóa, đơn giản, tích hợp nhiều cổng thanh toán phổ biến và các hình thức vận chuyển.\r\n\r\nQuản lý đơn hàng: Theo dõi và xử lý đơn hàng một cách chuyên nghiệp.\r\n\r\nDễ Dàng Tùy Biến và Quản Trị:\r\n\r\nTích hợp trình kéo-thả (Page Builder) trực quan, cho phép bạn tự do thay đổi bố cục, màu sắc, font chữ mà không cần biết nhiều về code.\r\n\r\nBảng quản trị (admin panel) thân thiện, giúp bạn dễ dàng quản lý nội dung, sản phẩm, đơn hàng và khách hàng.\r\n\r\nBạn hoàn toàn có thể code thêm các chức năng khác theo nhu cầu phát triển riêng của doanh nghiệp.\r\n\r\n🚀 Lợi Ích Khi Sử Dụng Mẫu Website Này\r\nTiết kiệm chi phí: Thay vì chi hàng chục triệu đồng để thiết kế website từ đầu, bạn có thể sở hữu ngay một website chuyên nghiệp với chi phí tối ưu.\r\n\r\nTiết kiệm thời gian: Triển khai nhanh chóng, đưa cửa hàng của bạn lên mạng chỉ trong thời gian ngắn.\r\n\r\nNâng tầm thương hiệu: Một website đẹp, chuyên nghiệp sẽ tạo dựng uy tín và ấn tượng tốt với khách hàng.\r\n\r\nTối ưu trải nghiệm khách hàng (UX/UI): Giúp khách hàng mua sắm dễ dàng và thoải mái hơn, từ đó tăng tỷ lệ chuyển đổi đơn hàng.', '🌟 Giới thiệu Mẫu Website Bán Nội Thất 44 - Giải Pháp Kinh Doanh Online Chuyên Nghiệp\r\nMẫu website bán nội thất 44 là một giải pháp giao diện toàn diện, được thiết kế đặc biệt cho các cửa hàng, showroom kinh doanh đồ nội thất, trang trí nhà cửa, và các sản phẩm liên quan. Với thiết kế hiện đại, sang trọng và tập trung vào việc trưng bày sản phẩm một cách tinh tế, mẫu website này sẽ giúp bạn nhanh chóng xây dựng một cửa hàng trực tuyến chuyên nghiệp, thu hút khách hàng và tăng doanh thu hiệu quả.', 999000.00, NULL, 99, 'T1', 'https://gugo.vn/wp-content/uploads/2025/08/noithat44-768x768.jpg', 'dsa', 1, 1, 'active', '2025-11-08 12:49:54', '2025-11-08 12:50:18'),
(6, 5, 'Mẫu website nhà thuốc 07', 'mau-website-nha-thuoc-07', 'Sử dụng mã nguồn mở WordPress phiên bản mới nhất.\r\nGiao diện được tối ưu chuẩn SEO, tương thích mọi thiết bị (máy tính, điện thoại, máy tính bảng).\r\nPhù hợp cho các nhà thuốc, chuỗi cửa hàng dược phẩm, phòng khám nhỏ.\r\nDễ dàng tùy biến, thêm chức năng như đặt hàng, tư vấn online, quản lý sản phẩm và tin tức sức khỏe.', 'Mẫu website nhà thuốc 07 được thiết kế dành riêng cho các cửa hàng dược phẩm, nhà thuốc tây hoặc chuỗi phân phối dược. Giao diện hiện đại, dễ sử dụng và tối ưu hóa trải nghiệm người dùng giúp khách hàng dễ dàng tìm kiếm, xem thông tin sản phẩm và liên hệ tư vấn nhanh chóng.\r\n\r\nTính năng nổi bật\r\n\r\n🎨 Thiết kế chuyên nghiệp: Màu sắc nhẹ nhàng, phù hợp ngành dược.\r\n\r\n📱 Giao diện responsive: Tự động hiển thị đẹp trên mọi thiết bị.\r\n\r\n🔍 Tối ưu SEO: Giúp website dễ dàng lên top Google.\r\n\r\n🛒 Quản lý sản phẩm dễ dàng: Thêm, sửa, xóa thuốc hoặc danh mục chỉ trong vài bước.\r\n\r\n💬 Tích hợp chat trực tuyến: Kết nối khách hàng qua Zalo, Messenger hoặc LiveChat.\r\n\r\n🧾 Tích hợp bài viết/blog: Chia sẻ tin tức, kiến thức y tế và chăm sóc sức khỏe.\r\n\r\n⚙️ Dễ dàng mở rộng: Có thể thêm các tính năng như đặt hàng, thanh toán online, hoặc quản lý đơn hàng.\r\n\r\nCông nghệ sử dụng\r\n\r\nNền tảng: WordPress mới nhất\r\n\r\nNgôn ngữ: PHP, HTML5, CSS3, JavaScript\r\n\r\nPlugin hỗ trợ: Yoast SEO, Elementor, WooCommerce, Contact Form 7, v.v.\r\n\r\nPhù hợp cho\r\n\r\nNhà thuốc, đại lý dược phẩm\r\n\r\nPhòng khám nhỏ hoặc trung tâm y tế\r\n\r\nCửa hàng bán dụng cụ y tế\r\n\r\nDoanh nghiệp muốn mở rộng kênh bán hàng trực tuyến\r\n\r\nLợi ích\r\n\r\nTăng độ uy tín và chuyên nghiệp cho thương hiệu\r\n\r\nGiúp khách hàng tìm kiếm, đặt hàng, hoặc tư vấn dễ dàng\r\n\r\nDễ quản lý, tiết kiệm chi phí vận hành', 2000000.00, 1700000.00, 20, 'T2', 'https://gugo.vn/wp-content/uploads/2025/08/nhathuoc7-600x600.jpg', '', 0, 1, 'active', '2025-11-10 08:16:30', '2025-11-10 08:17:36'),
(7, 5, 'Mẫu website bán thực phẩm sạch 29', 'mau-website-ban-thuc-pham-sach-29', 'Mẫu website bán thực phẩm sạch, nông sản hữu cơ được thiết kế trên nền tảng WordPress mới nhất.\r\nGiao diện hiện đại, chuẩn SEO, dễ quản lý sản phẩm và tối ưu hiển thị trên mọi thiết bị.\r\nPhù hợp cho cửa hàng, trang trại, doanh nghiệp kinh doanh rau củ, trái cây, thịt cá sạch, sản phẩm organic.', 'Mẫu website bán thực phẩm sạch 29 được thiết kế chuyên biệt cho các cửa hàng, trang trại hoặc doanh nghiệp kinh doanh sản phẩm nông nghiệp sạch – giúp bạn dễ dàng quảng bá thương hiệu và bán hàng trực tuyến một cách hiệu quả.\r\n\r\n🌿 Tính năng nổi bật\r\n\r\n🛍️ Quản lý sản phẩm tiện lợi: Thêm – sửa – xóa sản phẩm, cập nhật giá và khuyến mãi dễ dàng.\r\n\r\n📱 Giao diện responsive: Hiển thị đẹp và mượt trên điện thoại, máy tính bảng, laptop.\r\n\r\n🔍 Tối ưu chuẩn SEO: Dễ dàng xuất hiện trên Google với các từ khóa về thực phẩm sạch, rau hữu cơ...\r\n\r\n💬 Tích hợp chat trực tuyến: Hỗ trợ khách hàng qua Zalo, Messenger hoặc LiveChat.\r\n\r\n🧾 Giỏ hàng và thanh toán online: Kết nối WooCommerce, cho phép đặt hàng và thanh toán nhanh chóng.\r\n\r\n📰 Trang blog chia sẻ: Viết bài về mẹo chọn thực phẩm, dinh dưỡng, công thức nấu ăn.\r\n\r\n📦 Hỗ trợ giao hàng và quản lý đơn hàng: Quản lý tình trạng đơn, thông báo cho khách hàng.\r\n\r\n⚙️ Công nghệ sử dụng\r\n\r\nNền tảng: WordPress + WooCommerce\r\n\r\nNgôn ngữ: PHP, HTML5, CSS3, JavaScript\r\n\r\nPlugin đề xuất: Elementor, Yoast SEO, Contact Form 7, Slider Revolution\r\n\r\n🥗 Phù hợp cho\r\n\r\nCửa hàng bán thực phẩm sạch, rau củ quả hữu cơ\r\n\r\nTrang trại nông nghiệp công nghệ cao\r\n\r\nCửa hàng bán thịt cá, trứng, sữa, nông sản tươi\r\n\r\nThương hiệu thực phẩm muốn xây dựng kênh bán hàng online\r\n\r\n💚 Lợi ích khi sử dụng\r\n\r\nTạo dựng uy tín và niềm tin với khách hàng nhờ giao diện chuyên nghiệp\r\n\r\nGiúp doanh nghiệp tăng doanh số, mở rộng thị trường trực tuyến\r\n\r\nDễ sử dụng, không cần biết lập trình\r\n\r\nDễ dàng tùy biến màu sắc, bố cục theo thương hiệu', 1000000.00, 999000.00, 20, 'T3', 'https://gugo.vn/wp-content/uploads/2025/08/thucpham29-600x600.jpg', '', 0, 1, 'active', '2025-11-10 08:21:15', '2025-11-10 08:21:15'),
(8, 5, 'Mẫu web bán đồ chơi, thực phẩm thú cưng 03', 'mau-web-ban-o-choi-thuc-pham-thu-cung-03', 'Mẫu website bán đồ chơi và thực phẩm cho thú cưng được phát triển trên nền tảng WordPress mới nhất.\r\nThiết kế thân thiện, màu sắc sinh động, chuẩn SEO, dễ dàng quản lý sản phẩm và đơn hàng.\r\nPhù hợp cho cửa hàng pet shop, spa thú cưng, trung tâm chăm sóc chó mèo hoặc doanh nghiệp kinh doanh sản phẩm cho thú cưng.', 'Mẫu website bán đồ chơi, thực phẩm thú cưng 03 là lựa chọn lý tưởng cho các cửa hàng kinh doanh sản phẩm chăm sóc thú cưng như thức ăn, phụ kiện, quần áo và đồ chơi.\r\nGiao diện được tối ưu hóa để tạo cảm giác đáng yêu, thân thiện và dễ sử dụng, giúp khách hàng nhanh chóng tìm thấy sản phẩm họ cần.\r\n\r\n🐕 Tính năng nổi bật\r\n\r\n🛒 Quản lý sản phẩm dễ dàng: Thêm, chỉnh sửa, phân loại sản phẩm (thức ăn, đồ chơi, phụ kiện, quần áo…).\r\n\r\n📱 Thiết kế responsive: Tự động hiển thị đẹp trên điện thoại, máy tính bảng và máy tính.\r\n\r\n🔍 Chuẩn SEO & tốc độ tải nhanh: Giúp website dễ dàng lên top Google và tăng tỷ lệ chuyển đổi.\r\n\r\n💬 Tích hợp chat trực tuyến: Kết nối Zalo, Messenger, hoặc WhatsApp để hỗ trợ khách hàng.\r\n\r\n💳 Giỏ hàng và thanh toán online: Sử dụng WooCommerce hỗ trợ đặt hàng, tính phí ship, và thanh toán.\r\n\r\n📸 Thư viện hình ảnh sản phẩm sinh động: Trình bày bắt mắt, hấp dẫn người mua.\r\n\r\n🐾 Blog chia sẻ kinh nghiệm: Đăng bài viết hướng dẫn chăm sóc thú cưng, đánh giá sản phẩm, dinh dưỡng.\r\n\r\n⚙️ Công nghệ sử dụng\r\n\r\nNền tảng: WordPress + WooCommerce\r\n\r\nPlugin hỗ trợ: Elementor, Yoast SEO, Contact Form 7, Slider Revolution\r\n\r\nNgôn ngữ: PHP, HTML5, CSS3, JavaScript\r\n\r\n🏪 Phù hợp cho\r\n\r\nCửa hàng bán đồ cho chó mèo (pet shop)\r\n\r\nCơ sở spa, cắt tỉa, chăm sóc thú cưng\r\n\r\nNhà phân phối thức ăn, đồ chơi, phụ kiện thú cưng\r\n\r\nNgười nuôi hoặc yêu thích thú cưng muốn xây dựng kênh bán hàng online\r\n\r\n🐾 Lợi ích nổi bật\r\n\r\nTăng uy tín và chuyên nghiệp cho thương hiệu pet shop\r\n\r\nDễ quản lý sản phẩm, đơn hàng, khách hàng\r\n\r\nGiao diện thân thiện – phù hợp với khách hàng yêu thú cưng\r\n\r\nDễ tùy biến theo màu thương hiệu, không cần biết code', 1000000.00, 999000.00, 20, 'T4', 'https://gugo.vn/wp-content/uploads/2025/08/thucung3-600x600.jpg', '', 0, 1, 'active', '2025-11-10 08:33:33', '2025-11-10 08:33:33');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `project_statistics`
--

CREATE TABLE `project_statistics` (
  `id` int(11) NOT NULL,
  `stat_type` varchar(100) NOT NULL,
  `stat_key` varchar(255) NOT NULL,
  `stat_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `project_statistics`
--

INSERT INTO `project_statistics` (`id`, `stat_type`, `stat_key`, `stat_value`, `description`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'project_count', 'total_projects', '1000+', 'Tổng số dự án đã hoàn thành', 1, 'active', '2025-11-08 14:54:55', '2025-11-08 14:54:55'),
(2, 'project_count', 'active_websites', '850+', 'Website đang hoạt động', 2, 'active', '2025-11-08 14:54:55', '2025-11-08 14:54:55'),
(3, 'project_count', 'happy_clients', '900+', 'Khách hàng hài lòng', 3, 'active', '2025-11-08 14:54:55', '2025-11-08 14:54:55'),
(4, 'service', 'response_time', '24/7', 'Thời gian hỗ trợ khách hàng', 4, 'active', '2025-11-08 14:54:55', '2025-11-08 14:54:55'),
(5, 'service', 'warranty', 'Trọn đời', 'Bảo hành website', 5, 'active', '2025-11-08 14:54:55', '2025-11-08 14:54:55'),
(6, 'service', 'support', 'Từ A đến Z', 'Hỗ trợ khách hàng', 6, 'active', '2025-11-08 14:54:55', '2025-11-08 14:54:55'),
(7, 'pricing', 'starting_price', '2 triệu', 'Giá khởi điểm', 7, 'active', '2025-11-08 14:54:55', '2025-11-08 14:54:55'),
(8, 'pricing', 'premium_price', '4 triệu', 'Gói cao cấp', 8, 'active', '2025-11-08 14:54:55', '2025-11-08 14:54:55'),
(9, 'timeline', 'completion_time', '15 ngày', 'Thời gian hoàn thành', 9, 'active', '2025-11-08 14:54:55', '2025-11-08 14:54:55'),
(10, 'features', 'responsive', '100%', 'Website responsive', 10, 'active', '2025-11-08 14:54:55', '2025-11-08 14:54:55'),
(11, 'features', 'seo', 'Chuẩn SEO', 'Tối ưu SEO', 11, 'active', '2025-11-08 14:54:55', '2025-11-08 14:54:55'),
(12, 'features', 'ssl', 'Miễn phí', 'Chứng chỉ SSL', 12, 'active', '2025-11-08 14:54:55', '2025-11-08 14:54:55'),
(13, 'contact', 'phone_1', '0356-012250', 'Số điện thoại 1', 13, 'active', '2025-11-08 14:54:55', '2025-11-08 14:54:55'),
(14, 'contact', 'phone_2', '0355 999 141', 'Số điện thoại 2', 14, 'active', '2025-11-08 14:54:55', '2025-11-08 14:54:55'),
(15, 'contact', 'email', 'cuonghotran17022004@gmail.com', 'Email liên hệ', 15, 'active', '2025-11-08 14:54:55', '2025-11-08 14:54:55'),
(16, 'contact', 'address', 'KTX Khu B Đại Học Quốc Gia TP.HCM', 'Địa chỉ', 16, 'active', '2025-11-08 14:54:55', '2025-11-08 14:54:55');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `quick_replies`
--

CREATE TABLE `quick_replies` (
  `id` int(11) NOT NULL,
  `question` varchar(500) NOT NULL,
  `answer` text NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `click_count` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `quick_replies`
--

INSERT INTO `quick_replies` (`id`, `question`, `answer`, `category`, `icon`, `display_order`, `click_count`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Giá thiết kế website là bao nhiêu?', 'Chúng tôi có các gói dịch vụ thiết kế website với giá từ 2-4 triệu đồng. Gói cơ bản từ 2 triệu bao gồm đầy đủ tính năng bán hàng online, responsive, SEO cơ bản. Gói cao cấp từ 4 triệu có thêm nhiều tính năng nâng cao và hỗ trợ chuyên sâu. Bạn muốn tư vấn chi tiết về gói nào?', 'pricing', 'fa-dollar-sign', 1, 4, 'active', '2025-11-08 14:54:55', '2025-11-09 15:02:39'),
(2, 'Thời gian hoàn thành website?', 'Thời gian hoàn thiện website khoảng 15 ngày làm việc. Để quá trình được nhanh chóng, bạn cần chuẩn bị: Logo công ty (nếu có), hình ảnh sắc nét về sản phẩm/dịch vụ, và thông tin giới thiệu về doanh nghiệp. Chúng tôi sẽ hỗ trợ bạn từ A đến Z!', 'timeline', 'fa-clock', 2, 2, 'active', '2025-11-08 14:54:55', '2025-11-08 14:55:15'),
(3, 'Website có responsive không?', 'Có, tất cả website của DiamondDev Việt Nam đều được thiết kế responsive 100%. Website sẽ hiển thị tối ưu trên mọi thiết bị: máy tính, tablet, điện thoại. Giao diện sẽ tự động điều chỉnh để người dùng có trải nghiệm tốt nhất trên bất kỳ thiết bị nào.', 'features', 'fa-mobile-alt', 3, 0, 'active', '2025-11-08 14:54:55', '2025-11-08 14:54:55'),
(4, 'Có hỗ trợ SEO không?', 'Có, website của chúng tôi được thiết kế chuẩn SEO từ đầu. Bạn có thể tùy chỉnh thẻ tiêu đề (Title), mô tả (Meta Description), URL, và thêm alt cho hình ảnh. Cấu trúc website đã được tối ưu thân thiện với công cụ tìm kiếm, giúp website dễ dàng lên top Google.', 'features', 'fa-search', 4, 0, 'active', '2025-11-08 14:54:55', '2025-11-08 14:54:55'),
(5, 'Có tích hợp thanh toán online không?', 'Có, website hỗ trợ đầy đủ các phương thức thanh toán online như: MoMo, VNPay, chuyển khoản ngân hàng, và COD (thanh toán khi nhận hàng). Chúng tôi sẽ hướng dẫn bạn cấu hình và kết nối các cổng thanh toán một cách dễ dàng.', 'features', 'fa-credit-card', 5, 0, 'active', '2025-11-08 14:54:55', '2025-11-08 14:54:55'),
(6, 'Tôi không biết code có làm được không?', 'Hoàn toàn được! Với DiamondDev Việt Nam, bạn không cần biết code. Chúng tôi sẽ hỗ trợ bạn từ A đến Z: từ tư vấn, thiết kế, lập trình, đến hướng dẫn sử dụng. Bạn chỉ cần lên ý tưởng, chúng tôi sẽ biến nó thành hiện thực!', 'support', 'fa-question-circle', 6, 0, 'active', '2025-11-08 14:54:55', '2025-11-08 14:54:55'),
(7, 'Có bảo hành không?', 'Có, chúng tôi bảo hành website trọn đời. Hỗ trợ khách hàng 24/7 từ lúc tạo dựng sản phẩm đến quá trình vận hành. Bất kỳ vấn đề nào phát sinh, chúng tôi sẽ hỗ trợ xử lý ngay lập tức. Đồng thời, website của bạn sẽ được cập nhật các tính năng mới miễn phí.', 'support', 'fa-shield-alt', 7, 0, 'active', '2025-11-08 14:54:55', '2025-11-08 14:54:55'),
(8, 'Có tích hợp Google Analytics không?', 'Có, website được tích hợp sẵn Google Analytics. Bạn có thể xem toàn bộ báo cáo ngay trên trang quản trị DiamondDev mà không cần đăng nhập vào hệ thống phức tạp của Google. Theo dõi số lượng người truy cập, nguồn traffic, thiết bị sử dụng, và nhiều chỉ số khác.', 'features', 'fa-chart-line', 8, 0, 'active', '2025-11-08 14:54:55', '2025-11-08 14:54:55');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `rating`, `comment`, `status`, `created_at`) VALUES
(1, 5, 2, 5, 'quá tuyệt vời nè', 'approved', '2025-11-09 14:16:36');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price_from` decimal(10,2) DEFAULT NULL,
  `price_unit` varchar(50) DEFAULT NULL,
  `features` text DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `services`
--

INSERT INTO `services` (`id`, `name`, `slug`, `description`, `content`, `icon`, `image`, `price_from`, `price_unit`, `features`, `featured`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Thiết kế website', 'thiet-ke-website', 'Thiết kế website chuyên nghiệp, chuẩn SEO, tương thích mọi thiết bị. Giao diện đẹp mắt, thân thiện với người dùng.', 'Thiết kế website chuyên nghiệp, chuẩn SEO, tương thích mọi thiết bị. Giao diện đẹp mắt, thân thiện với người dùng. Chúng tôi cam kết mang đến cho bạn một website hiện đại, tối ưu trải nghiệm người dùng và đạt hiệu quả cao trong kinh doanh.', 'fas fa-laptop-code', 'https://drive.inet.vn/uploads/traht@inet.vn/Traht-Zozoweb/thiet-ke-website-theo-mau.jpg', 1500000.00, 'đ', 'Responsive design\r\nTối ưu SEO\r\nTốc độ tải nhanh\r\nBảo mật cao', 0, 'active', 1, '2025-11-10 06:32:31', '2025-11-10 07:05:46'),
(2, 'Website bán hàng', 'website-ban-hang', 'Hệ thống bán hàng online hoàn chỉnh với giỏ hàng, thanh toán, quản lý đơn hàng và khách hàng.', 'Hệ thống bán hàng online hoàn chỉnh với giỏ hàng, thanh toán, quản lý đơn hàng và khách hàng. Tích hợp đầy đủ các tính năng cần thiết cho một website thương mại điện tử chuyên nghiệp.', 'fas fa-shopping-cart', 'https://file.hstatic.net/200000472237/file/huong-dan-lam-website-ban-hang-2_cb481926a4204cf8a19898308e5b7ac0.jpg', 1000000.00, 'đ', 'Giỏ hàng thông minh\r\nThanh toán đa dạng\r\nQuản lý kho hàng\r\nBáo cáo doanh thu', 1, 'active', 2, '2025-11-10 06:32:31', '2025-11-10 07:08:33'),
(3, 'Ứng dụng di động', 'ung-dung-di-dong', 'Phát triển ứng dụng iOS và Android cho doanh nghiệp. Tích hợp đầy đủ tính năng theo yêu cầu.', 'Phát triển ứng dụng iOS và Android cho doanh nghiệp. Tích hợp đầy đủ tính năng theo yêu cầu. Ứng dụng được tối ưu hiệu suất, giao diện hiện đại và trải nghiệm người dùng tốt nhất.', 'fas fa-mobile-alt', 'https://geneat.vn/wp-content/uploads/2024/08/mydesign-1400x788.png', 3000000.00, 'đ', 'iOS & Android\r\nUI/UX hiện đại\r\nPush notification\r\nTích hợp API', 0, 'active', 3, '2025-11-10 06:32:31', '2025-11-10 07:11:57'),
(4, 'SEO - Marketing', 'seo-marketing', 'Tối ưu hóa website lên top Google, chạy quảng cáo Google Ads, Facebook Ads hiệu quả.', 'Tối ưu hóa website lên top Google, chạy quảng cáo Google Ads, Facebook Ads hiệu quả. Dịch vụ marketing online toàn diện giúp doanh nghiệp tăng trưởng doanh thu và mở rộng thị trường.', 'fas fa-search', 'https://vnseo.vn/wp-content/uploads/2024/12/seo-marketing-02.jpg', 3000000.00, 'đ/tháng', 'SEO tổng thể\r\nGoogle Ads\r\nFacebook Ads\r\nContent Marketing', 0, 'active', 4, '2025-11-10 06:32:31', '2025-11-10 07:12:29'),
(5, 'Hosting - Domain', 'hosting-domain', 'Cung cấp hosting tốc độ cao, bảo mật tốt. Hỗ trợ đăng ký và quản lý tên miền.', 'Cung cấp hosting tốc độ cao, bảo mật tốt. Hỗ trợ đăng ký và quản lý tên miền. Dịch vụ hosting ổn định, tốc độ nhanh với đội ngũ hỗ trợ chuyên nghiệp 24/7.', 'fas fa-server', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRC_RtQFdTSQ6o_7mVZLvD2pL8nHj1R4Nx2Aw&s', 300000.00, 'đ/tháng', 'SSL miễn phí\r\nBackup tự động\r\nUptime 99.9%\r\nHỗ trợ 24/7', 0, 'active', 5, '2025-11-10 06:32:31', '2025-11-10 07:13:18'),
(6, 'Bảo trì - Nâng cấp', 'bao-tri-nang-cap', 'Dịch vụ bảo trì, nâng cấp website định kỳ. Sửa lỗi, thêm tính năng mới theo yêu cầu.', 'Dịch vụ bảo trì, nâng cấp website định kỳ. Sửa lỗi, thêm tính năng mới theo yêu cầu. Đảm bảo website luôn hoạt động ổn định, cập nhật các tính năng mới nhất và bảo mật tốt nhất.', 'fas fa-tools', 'https://voip24h.vn/wp-content/uploads/2024/04/BAO_TRI-06-scaled.jpg', 1000000.00, 'đ/Năm', 'Bảo trì định kỳ\r\nSửa lỗi nhanh\r\nThêm tính năng\r\nTư vấn miễn phí', 0, 'active', 6, '2025-11-10 06:32:31', '2025-11-10 07:13:59');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','textarea','number','boolean','json') DEFAULT 'text',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'THẾ GIỚI WEBSITE ', 'text', 'Tên website', '2025-11-09 02:06:50', '2025-11-09 14:27:03'),
(2, 'site_email', 'cuonghotran17022004@gmail.com', 'text', 'Email liên hệ', '2025-11-09 02:06:50', '2025-11-09 14:27:03'),
(3, 'site_phone', '0355999141', 'text', 'Số điện thoại liên hệ', '2025-11-09 02:06:50', '2025-11-09 14:27:03'),
(4, 'site_address', '123 Đường ABC, Quận 1, TP.HCM', 'text', 'Địa chỉ', '2025-11-09 02:06:50', '2025-11-09 14:27:03'),
(5, 'site_description', 'Website bán hàng trực tuyến', 'text', 'Mô tả website', '2025-11-09 02:06:50', '2025-11-09 14:27:03'),
(6, 'currency', 'VND', 'text', 'Đơn vị tiền tệ', '2025-11-09 02:06:50', '2025-11-09 14:27:03'),
(7, 'items_per_page', '20', 'number', 'Số sản phẩm mỗi trang', '2025-11-09 02:06:50', '2025-11-09 14:27:03'),
(8, 'maintenance_mode', '0', 'boolean', 'Chế độ bảo trì', '2025-11-09 02:06:50', '2025-11-09 14:27:03');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `templates`
--

CREATE TABLE `templates` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` enum('business','ecommerce','restaurant','realestate','education','healthcare','beauty','other') DEFAULT 'other',
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `gallery` text DEFAULT NULL,
  `demo_url` varchar(500) DEFAULT NULL,
  `features` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `featured` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 0.00,
  `total_reviews` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `templates`
--

INSERT INTO `templates` (`id`, `name`, `slug`, `description`, `category`, `price`, `sale_price`, `image`, `gallery`, `demo_url`, `features`, `status`, `featured`, `views`, `rating`, `total_reviews`, `created_at`, `updated_at`) VALUES
(1, 'Business Pro', 'business-pro', 'Mẫu giao diện doanh nghiệp chuyên nghiệp', 'business', 2990000.00, 2790000.00, NULL, NULL, NULL, NULL, 'active', 1, 0, 0.00, 0, '2025-11-09 02:14:51', '2025-11-09 02:14:51'),
(2, 'Shop Online', 'shop-online', 'Mẫu website bán hàng online đầy đủ tính năng', 'ecommerce', 3990000.00, 3790000.00, NULL, NULL, NULL, NULL, 'active', 1, 0, 0.00, 0, '2025-11-09 02:14:51', '2025-11-09 02:14:51'),
(3, 'Restaurant Deluxe', 'restaurant-deluxe', 'Mẫu website nhà hàng sang trọng', 'restaurant', 2490000.00, 2290000.00, NULL, NULL, NULL, NULL, 'active', 1, 0, 0.00, 0, '2025-11-09 02:14:51', '2025-11-09 02:14:51'),
(4, 'Real Estate Plus', 'real-estate-plus', 'Mẫu website bất động sản chuyên nghiệp', 'realestate', 4990000.00, 4690000.00, NULL, NULL, NULL, NULL, 'active', 1, 0, 0.00, 0, '2025-11-09 02:14:51', '2025-11-09 02:14:51'),
(5, 'Edu Academy', 'edu-academy', 'Mẫu website giáo dục và đào tạo', 'education', 3490000.00, 3290000.00, NULL, NULL, NULL, NULL, 'active', 1, 0, 0.00, 0, '2025-11-09 02:14:51', '2025-11-09 02:14:51'),
(6, 'Medical Care', 'medical-care', 'Mẫu website y tế và chăm sóc sức khỏe', 'healthcare', 3990000.00, 3790000.00, NULL, NULL, NULL, NULL, 'active', 1, 0, 0.00, 0, '2025-11-09 02:14:51', '2025-11-09 02:14:51'),
(7, 'Beauty Salon', 'beauty-salon', 'Mẫu website làm đẹp và spa', 'beauty', 2690000.00, 2490000.00, NULL, NULL, NULL, NULL, 'active', 1, 0, 0.00, 0, '2025-11-09 02:14:51', '2025-11-09 02:14:51'),
(8, 'Corporate Elite', 'corporate-elite', 'Mẫu website doanh nghiệp cao cấp', 'business', 3490000.00, 3290000.00, NULL, NULL, NULL, NULL, 'active', 0, 0, 0.00, 0, '2025-11-09 02:14:51', '2025-11-09 02:14:51'),
(9, 'Fashion Store', 'fashion-store', 'Mẫu website thời trang và may mặc', 'ecommerce', 4490000.00, 4290000.00, NULL, NULL, NULL, NULL, 'active', 0, 0, 0.00, 0, '2025-11-09 02:14:51', '2025-11-09 02:14:51'),
(10, 'Cafe & Bistro', 'cafe-bistro', 'Mẫu website quán cà phê và bistro', 'restaurant', 2290000.00, 2090000.00, NULL, NULL, NULL, NULL, 'active', 0, 0, 0.00, 0, '2025-11-09 02:14:51', '2025-11-09 02:14:51');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `fullname` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `firebase_uid` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `fullname`, `phone`, `address`, `avatar`, `role`, `firebase_uid`, `google_id`, `email_verified`, `status`, `created_at`, `updated_at`) VALUES
(2, 'cuonghotran17022004@gmail.com', NULL, 'Cương 5169_Nguyễn', '0356012250', 'dsad', 'https://lh3.googleusercontent.com/a/ACg8ocIJJBSCnOG5ZYK7No5OzSBr3vFDTN5WxdKLYlKYq7POo2VnbmHA=s96-c', 'admin', 'PVUfQzn0dabUpvp6ebwOUHk0fDu2', '102552360634335386867', 1, 'active', '2025-11-08 11:18:00', '2025-11-08 12:31:37');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `idx_slug` (`slug`);

--
-- Chỉ mục cho bảng `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_session_id` (`session_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Chỉ mục cho bảng `chat_sessions`
--
ALTER TABLE `chat_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_session_id` (`session_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Chỉ mục cho bảng `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Chỉ mục cho bảng `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_featured` (`featured`),
  ADD KEY `idx_published_at` (`published_at`),
  ADD KEY `author_id` (`author_id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_order_code` (`order_code`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_order_status` (`order_status`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_order_id` (`order_id`);

--
-- Chỉ mục cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_featured` (`featured`);

--
-- Chỉ mục cho bảng `project_statistics`
--
ALTER TABLE `project_statistics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_stat` (`stat_type`,`stat_key`),
  ADD KEY `idx_stat_type` (`stat_type`),
  ADD KEY `idx_status` (`status`);

--
-- Chỉ mục cho bảng `quick_replies`
--
ALTER TABLE `quick_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_display_order` (`display_order`);

--
-- Chỉ mục cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Chỉ mục cho bảng `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_featured` (`featured`),
  ADD KEY `idx_sort_order` (`sort_order`);

--
-- Chỉ mục cho bảng `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_key` (`setting_key`);

--
-- Chỉ mục cho bảng `templates`
--
ALTER TABLE `templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_featured` (`featured`),
  ADD KEY `idx_slug` (`slug`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD UNIQUE KEY `firebase_uid` (`firebase_uid`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_firebase_uid` (`firebase_uid`),
  ADD KEY `idx_google_id` (`google_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `chat_sessions`
--
ALTER TABLE `chat_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `project_statistics`
--
ALTER TABLE `project_statistics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `quick_replies`
--
ALTER TABLE `quick_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `templates`
--
ALTER TABLE `templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carts_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `chat_sessions`
--
ALTER TABLE `chat_sessions`
  ADD CONSTRAINT `chat_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
