# HƯỚNG DẪN CÀI ĐẶT WEBSITE BÁN HÀNG

## Yêu cầu hệ thống
- PHP >= 7.4
- MySQL >= 5.7
- XAMPP hoặc tương tự (Apache, MySQL)
- Composer (không bắt buộc)

## Các bước cài đặt

### 1. Cài đặt Database

1. Mở phpMyAdmin (http://localhost/phpmyadmin)
2. Tạo database mới tên `ecommerce_db`
3. Import file `database/schema.sql` vào database vừa tạo
4. Hoặc chạy các câu lệnh SQL trong file `database/schema.sql`

### 2. Cấu hình kết nối Database

Mở file `config/database.php` và cấu hình:
```php
private $host = "localhost";
private $database_name = "ecommerce_db";
private $username = "root";
private $password = "";
```

### 3. Cấu hình Firebase (Đăng nhập Google)

1. Truy cập https://console.firebase.google.com/
2. Tạo project mới
3. Vào Project Settings > General > Your apps
4. Thêm web app và lấy Firebase config
5. Mở file `config/config.php` và cập nhật:
```php
define('FIREBASE_API_KEY', 'your-api-key');
define('FIREBASE_AUTH_DOMAIN', 'your-project-id.firebaseapp.com');
define('FIREBASE_PROJECT_ID', 'your-project-id');
define('FIREBASE_STORAGE_BUCKET', 'your-project-id.appspot.com');
define('FIREBASE_MESSAGING_SENDER_ID', 'your-sender-id');
define('FIREBASE_APP_ID', 'your-app-id');
```

6. Bật Authentication > Sign-in method > Google

### 4. Cấu hình MoMo Payment

1. Đăng ký tài khoản MoMo Business: https://business.momo.vn/
2. Lấy thông tin Partner Code, Access Key, Secret Key
3. Cập nhật trong file `config/config.php`:
```php
define('MOMO_PARTNER_CODE', 'YOUR_PARTNER_CODE');
define('MOMO_ACCESS_KEY', 'YOUR_ACCESS_KEY');
define('MOMO_SECRET_KEY', 'YOUR_SECRET_KEY');
```

**Lưu ý:** Để test MoMo, sử dụng môi trường test:
- Endpoint: https://test-payment.momo.vn/v2/gateway/api/create
- Sử dụng tài khoản test của MoMo

### 5. Cấu hình SITE_URL

Mở file `config/config.php` và cập nhật:
```php
define('SITE_URL', 'http://localhost/duanbanscrcode');
```

### 6. Phân quyền thư mục (Linux/Mac)

```bash
chmod -R 755 duanbanscrcode
chmod -R 777 duanbanscrcode/uploads
```

### 7. Tạo thư mục uploads

```
duanbanscrcode/
  uploads/
    products/
    categories/
    users/
```

## Tài khoản mặc định

### Admin
- Email: admin@admin.com
- Password: password

### Test User
Bạn có thể tự đăng ký tài khoản mới hoặc đăng nhập bằng Google

## Cấu trúc thư mục

```
duanbanscrcode/
├── admin/              # Trang quản trị
│   ├── includes/       # Header/Footer admin
│   ├── index.php       # Dashboard
│   ├── products.php    # Quản lý sản phẩm
│   ├── orders.php      # Quản lý đơn hàng
│   └── ...
├── assets/             # CSS, JS, Images
│   ├── css/
│   ├── js/
│   └── images/
├── auth/               # Đăng nhập/Đăng ký
│   ├── login.php
│   ├── register.php
│   ├── google_auth.php
│   └── ...
├── config/             # Cấu hình
│   ├── config.php
│   └── database.php
├── database/           # SQL schema
│   └── schema.sql
├── includes/           # Header/Footer
│   ├── header.php
│   └── footer.php
├── payment/            # Thanh toán
│   ├── momo_payment.php
│   ├── momo_return.php
│   └── momo_ipn.php
├── uploads/            # Upload files
├── home.php            # Trang chủ mới
├── products.php        # Danh sách sản phẩm
├── product_detail.php  # Chi tiết sản phẩm
├── cart.php            # Giỏ hàng
├── checkout.php        # Thanh toán
├── orders.php          # Đơn hàng của tôi
└── index.php           # File gốc (redirect)
```

## Tính năng chính

### Người dùng
✅ Đăng ký/Đăng nhập (Email/Password)
✅ Đăng nhập bằng Google (Firebase)
✅ Quên mật khẩu
✅ Xem sản phẩm, tìm kiếm, lọc
✅ Xem chi tiết sản phẩm
✅ Thêm vào giỏ hàng
✅ Thanh toán (COD, MoMo, VNPay, Chuyển khoản)
✅ Xem lịch sử đơn hàng
✅ Quản lý thông tin cá nhân

### Admin
✅ Dashboard với thống kê
✅ Biểu đồ doanh thu 7 ngày
✅ Quản lý sản phẩm (CRUD)
✅ Quản lý danh mục
✅ Quản lý đơn hàng
✅ Quản lý người dùng
✅ Thống kê chi tiết

## Các trang chính

### User
- `/home.php` - Trang chủ
- `/products.php` - Danh sách sản phẩm
- `/product_detail.php?id=1` - Chi tiết sản phẩm
- `/cart.php` - Giỏ hàng
- `/checkout.php` - Thanh toán
- `/orders.php` - Đơn hàng của tôi
- `/auth/login.php` - Đăng nhập
- `/auth/register.php` - Đăng ký

### Admin
- `/admin` - Dashboard
- `/admin/products.php` - Quản lý sản phẩm
- `/admin/orders.php` - Quản lý đơn hàng
- `/admin/users.php` - Quản lý người dùng

## Troubleshooting

### Lỗi kết nối database
- Kiểm tra MySQL đã chạy chưa
- Kiểm tra thông tin trong `config/database.php`
- Import đúng file SQL

### Lỗi Firebase
- Kiểm tra API key có đúng không
- Kiểm tra domain đã add vào Firebase Console chưa
- Enable Google Sign-in trong Firebase Authentication

### Lỗi MoMo
- Kiểm tra Partner Code, Access Key, Secret Key
- Sử dụng môi trường test khi development
- Kiểm tra URL Return và IPN có đúng không

### Lỗi upload file
- Kiểm tra quyền thư mục uploads
- Kiểm tra upload_max_filesize trong php.ini
- Tạo đầy đủ các thư mục con

## Support

Nếu gặp vấn đề, vui lòng:
1. Kiểm tra error log trong XAMPP
2. Bật display_errors trong php.ini
3. Kiểm tra Console trong Browser (F12)

## License

MIT License - Free to use for personal and commercial projects
