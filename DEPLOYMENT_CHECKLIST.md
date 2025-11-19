# Deployment Checklist - Tương thích Hosting

## ✅ Đã kiểm tra và sửa các vấn đề tương thích

### 1. **Đường dẫn File System**
- ✅ Đã normalize backslash (`\`) thành forward slash (`/`) - tương thích Linux hosting
- ✅ Tất cả file paths dùng `dirname(__DIR__)` và forward slash
- ✅ Không có hardcode Windows paths (`C:\`, `\`)

### 2. **SITE_URL Auto-detect**
- ✅ `config/config.php` tự động detect SITE_URL từ server
- ✅ Hoạt động trên cả localhost và hosting
- ✅ Tự động detect HTTPS/HTTP
- ✅ Xử lý base path đúng cho cả root và subdirectory

### 3. **Session Cookie Security**
- ✅ Tự động detect HTTPS và set `cookie_secure` đúng
- ✅ Hỗ trợ proxy headers (`HTTP_X_FORWARDED_PROTO`)
- ✅ Tương thích với shared hosting và VPS

### 4. **Admin CSS Loading**
- ✅ Dùng cả absolute URL (từ SITE_URL) và relative path
- ✅ Base tag được tính toán động
- ✅ Cache busting với `filemtime()`
- ✅ Hoạt động trên cả localhost và hosting

### 5. **Admin Navigation Links**
- ✅ Relative paths cho internal admin links
- ✅ Absolute URLs cho external links (trang chủ, logout)
- ✅ Tất cả links được tính toán động

## 📋 Checklist trước khi deploy

### Database
- [ ] Export database từ localhost
- [ ] Import database vào hosting
- [ ] Cập nhật thông tin database trong `config/database.php`
- [ ] Kiểm tra charset database (UTF-8)

### File Uploads
- [ ] Tạo thư mục `uploads/` và các subfolders:
  - `uploads/products/`
  - `uploads/templates/`
  - `uploads/news/`
  - `uploads/services/`
  - `uploads/users/`
- [ ] Set permissions: `755` cho folders, `644` cho files
- [ ] Kiểm tra `upload_max_filesize` trong php.ini

### Cấu hình
- [ ] SITE_URL sẽ tự động detect - không cần chỉnh
- [ ] Kiểm tra Firebase credentials (nếu dùng)
- [ ] Cập nhật MoMo Payment credentials (nếu dùng)
- [ ] Kiểm tra `.htaccess` file (nếu có)

### Security
- [ ] Session sẽ tự động dùng HTTPS nếu detect được
- [ ] Kiểm tra file permissions
- [ ] Đảm bảo `.env` hoặc config sensitive không bị expose

### Testing
- [ ] Test đăng nhập admin
- [ ] Test upload file
- [ ] Test CSS/JS loading
- [ ] Test các links navigation
- [ ] Test responsive trên mobile

## 🔍 Các điểm cần lưu ý

### 1. **File Permissions (Linux)**
```bash
# Folders
chmod 755 uploads uploads/*

# Files
chmod 644 *.php
```

### 2. **PHP Settings**
- `upload_max_filesize` >= 5MB
- `post_max_size` >= 5MB
- `memory_limit` >= 128M
- `max_execution_time` >= 30

### 3. **Database Connection**
File `config/database.php` cần cập nhật:
```php
private $host = 'localhost'; // hoặc IP hosting
private $db_name = 'your_database';
private $username = 'your_username';
private $password = 'your_password';
```

### 4. **HTTPS/SSL**
- Nếu hosting có SSL, session sẽ tự động dùng HTTPS
- Đảm bảo redirect HTTP → HTTPS nếu cần

## ✅ Đã tối ưu cho

- ✅ Windows (XAMPP) - Localhost
- ✅ Linux hosting (shared/VPS)
- ✅ Subdirectory deployment (`/subfolder/admin/`)
- ✅ Root deployment (`/admin/`)
- ✅ HTTPS và HTTP tự động detect
- ✅ Proxy/load balancer (X-Forwarded-Proto)

## 🚀 Code đã sẵn sàng deploy

Tất cả code đã được kiểm tra và tối ưu để hoạt động trên cả localhost và hosting Linux mà không cần thay đổi gì.

