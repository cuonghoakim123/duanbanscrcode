-- Insert dữ liệu dịch vụ mẫu vào bảng services
-- Chạy file này sau khi đã tạo bảng services bằng services_table.sql

USE ecommerce_db;

-- 1. Thiết kế website
INSERT INTO services (name, slug, description, content, icon, price_from, price_unit, features, featured, status, sort_order) VALUES
('Thiết kế website', 'thiet-ke-website', 
'Thiết kế website chuyên nghiệp, chuẩn SEO, tương thích mọi thiết bị. Giao diện đẹp mắt, thân thiện với người dùng.',
'Thiết kế website chuyên nghiệp, chuẩn SEO, tương thích mọi thiết bị. Giao diện đẹp mắt, thân thiện với người dùng. Chúng tôi cam kết mang đến cho bạn một website hiện đại, tối ưu trải nghiệm người dùng và đạt hiệu quả cao trong kinh doanh.',
'fas fa-laptop-code',
1500000,
'đ',
'Responsive design
Tối ưu SEO
Tốc độ tải nhanh
Bảo mật cao',
0,
'active',
1);

-- 2. Website bán hàng (Phổ biến - Featured)
INSERT INTO services (name, slug, description, content, icon, price_from, price_unit, features, featured, status, sort_order) VALUES
('Website bán hàng', 'website-ban-hang',
'Hệ thống bán hàng online hoàn chỉnh với giỏ hàng, thanh toán, quản lý đơn hàng và khách hàng.',
'Hệ thống bán hàng online hoàn chỉnh với giỏ hàng, thanh toán, quản lý đơn hàng và khách hàng. Tích hợp đầy đủ các tính năng cần thiết cho một website thương mại điện tử chuyên nghiệp.',
'fas fa-shopping-cart',
1000000,
'đ',
'Giỏ hàng thông minh
Thanh toán đa dạng
Quản lý kho hàng
Báo cáo doanh thu',
1,
'active',
2);

-- 3. Ứng dụng di động
INSERT INTO services (name, slug, description, content, icon, price_from, price_unit, features, featured, status, sort_order) VALUES
('Ứng dụng di động', 'ung-dung-di-dong',
'Phát triển ứng dụng iOS và Android cho doanh nghiệp. Tích hợp đầy đủ tính năng theo yêu cầu.',
'Phát triển ứng dụng iOS và Android cho doanh nghiệp. Tích hợp đầy đủ tính năng theo yêu cầu. Ứng dụng được tối ưu hiệu suất, giao diện hiện đại và trải nghiệm người dùng tốt nhất.',
'fas fa-mobile-alt',
3000000,
'đ',
'iOS & Android
UI/UX hiện đại
Push notification
Tích hợp API',
0,
'active',
3);

-- 4. SEO - Marketing
INSERT INTO services (name, slug, description, content, icon, price_from, price_unit, features, featured, status, sort_order) VALUES
('SEO - Marketing', 'seo-marketing',
'Tối ưu hóa website lên top Google, chạy quảng cáo Google Ads, Facebook Ads hiệu quả.',
'Tối ưu hóa website lên top Google, chạy quảng cáo Google Ads, Facebook Ads hiệu quả. Dịch vụ marketing online toàn diện giúp doanh nghiệp tăng trưởng doanh thu và mở rộng thị trường.',
'fas fa-search',
3000000,
'đ/tháng',
'SEO tổng thể
Google Ads
Facebook Ads
Content Marketing',
0,
'active',
4);

-- 5. Hosting - Domain
INSERT INTO services (name, slug, description, content, icon, price_from, price_unit, features, featured, status, sort_order) VALUES
('Hosting - Domain', 'hosting-domain',
'Cung cấp hosting tốc độ cao, bảo mật tốt. Hỗ trợ đăng ký và quản lý tên miền.',
'Cung cấp hosting tốc độ cao, bảo mật tốt. Hỗ trợ đăng ký và quản lý tên miền. Dịch vụ hosting ổn định, tốc độ nhanh với đội ngũ hỗ trợ chuyên nghiệp 24/7.',
'fas fa-server',
500000,
'đ/năm',
'SSL miễn phí
Backup tự động
Uptime 99.9%
Hỗ trợ 24/7',
0,
'active',
5);

-- 6. Bảo trì - Nâng cấp
INSERT INTO services (name, slug, description, content, icon, price_from, price_unit, features, featured, status, sort_order) VALUES
('Bảo trì - Nâng cấp', 'bao-tri-nang-cap',
'Dịch vụ bảo trì, nâng cấp website định kỳ. Sửa lỗi, thêm tính năng mới theo yêu cầu.',
'Dịch vụ bảo trì, nâng cấp website định kỳ. Sửa lỗi, thêm tính năng mới theo yêu cầu. Đảm bảo website luôn hoạt động ổn định, cập nhật các tính năng mới nhất và bảo mật tốt nhất.',
'fas fa-tools',
1000000,
'đ/tháng',
'Bảo trì định kỳ
Sửa lỗi nhanh
Thêm tính năng
Tư vấn miễn phí',
0,
'active',
6);

