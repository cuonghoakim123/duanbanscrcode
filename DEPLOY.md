# Hướng dẫn Deploy lên Hosting

## Bước 1: Chuẩn bị file .env

1. Copy file `.env.example` thành `.env`:
   ```bash
   cp .env.example .env
   ```

2. Chỉnh sửa file `.env` với thông tin hosting của bạn:

```env
# Website Configuration
SITE_URL=https://yourdomain.com
SITE_NAME=Thế giới website

# Database Configuration
DB_HOST=localhost
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password

# Firebase Configuration (giữ nguyên hoặc cập nhật)
FIREBASE_API_KEY=your_firebase_api_key
FIREBASE_AUTH_DOMAIN=your_firebase_auth_domain
FIREBASE_PROJECT_ID=your_firebase_project_id
FIREBASE_STORAGE_BUCKET=your_firebase_storage_bucket
FIREBASE_MESSAGING_SENDER_ID=your_firebase_messaging_sender_id
FIREBASE_APP_ID=your_firebase_app_id
FIREBASE_MEASUREMENT_ID=your_firebase_measurement_id

# MoMo Payment Configuration
MOMO_ENDPOINT=https://test-payment.momo.vn/v2/gateway/api/create
MOMO_PARTNER_CODE=your_momo_partner_code
MOMO_ACCESS_KEY=your_momo_access_key
MOMO_SECRET_KEY=your_momo_secret_key

# Google Gemini API Configuration
GEMINI_API_KEY=your_gemini_api_key
GEMINI_API_URL=https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent

# Session Configuration
SESSION_COOKIE_SECURE=1  # Đặt 1 nếu dùng HTTPS
SESSION_COOKIE_HTTPONLY=1

# Timezone
TIMEZONE=Asia/Ho_Chi_Minh
```

## Bước 2: Upload files lên hosting

1. Upload toàn bộ files lên hosting (trừ file `.env` - sẽ tạo trên hosting)
2. Đảm bảo các thư mục có quyền ghi:
   - `uploads/` (và các thư mục con)
   - `uploads/products/`
   - `uploads/templates/`
   - `uploads/news/`
   - `uploads/services/`
   - `uploads/users/`

## Bước 3: Tạo database

1. Tạo database mới trên hosting
2. Import file `database/schema.sql` vào database
3. Cập nhật thông tin database trong file `.env`

## Bước 4: Cấu hình trên hosting

### Cấu hình PHP

- PHP version: 7.4 trở lên
- Extensions cần thiết:
  - PDO
  - PDO_MySQL
  - mbstring
  - GD (cho xử lý ảnh)
  - OpenSSL

### Cấu hình .htaccess (nếu cần)

Tạo file `.htaccess` ở thư mục gốc nếu hosting hỗ trợ:

```apache
# Enable rewrite engine
RewriteEngine On

# Redirect to HTTPS (nếu có SSL)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Protect .env file
<Files .env>
    Order allow,deny
    Deny from all
</Files>

# Protect config files
<FilesMatch "^(config\.php|database\.php)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

## Bước 5: Kiểm tra

1. Truy cập website để kiểm tra
2. Kiểm tra kết nối database
3. Kiểm tra upload ảnh
4. Kiểm tra đăng nhập/đăng ký

## Lưu ý quan trọng

- **KHÔNG** commit file `.env` lên Git (đã có trong .gitignore)
- **KHÔNG** chia sẻ file `.env` với người khác
- Đảm bảo file `.env` có quyền bảo mật (chmod 600 trên Linux)
- Cập nhật `SITE_URL` trong `.env` đúng với domain hosting
- Nếu dùng HTTPS, đặt `SESSION_COOKIE_SECURE=1`

## Troubleshooting

### Lỗi kết nối database
- Kiểm tra thông tin DB_HOST, DB_NAME, DB_USER, DB_PASS trong `.env`
- Kiểm tra database đã được tạo chưa
- Kiểm tra user database có quyền truy cập

### Lỗi upload ảnh
- Kiểm tra quyền ghi của thư mục `uploads/`
- Kiểm tra cấu hình PHP upload_max_filesize và post_max_size

### Lỗi session
- Kiểm tra quyền ghi của thư mục session (thường là /tmp)
- Kiểm tra cấu hình SESSION_COOKIE_SECURE nếu dùng HTTPS

