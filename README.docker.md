# Docker Setup Guide

## Yêu cầu
- Docker Desktop (Windows/Mac) hoặc Docker Engine (Linux)
- Docker Compose v2+

## Cài đặt và chạy

### 1. Chuẩn bị môi trường

Sao chép file cấu hình môi trường:
```bash
cp .env.docker .env
```

Hoặc tạo file `.env` của riêng bạn dựa trên `.env.example`.

**Lưu ý quan trọng:** Trong file `.env`, đảm bảo:
- `DB_HOST=db` (tên service MySQL trong docker-compose)
- `DB_PASS=root123` (hoặc mật khẩu bạn muốn đặt)
- `SITE_URL=http://localhost:8080`

### 2. Build và khởi động containers

```bash
docker-compose up -d --build
```

Lệnh này sẽ:
- Build Docker image cho ứng dụng PHP
- Tải MySQL 8.0 image
- Tải phpMyAdmin image
- Tạo network để các container giao tiếp
- Import database từ file `database/schema.sql`
- Khởi động tất cả services

### 3. Kiểm tra trạng thái containers

```bash
docker-compose ps
```

Tất cả containers phải ở trạng thái "Up".

### 4. Truy cập ứng dụng

- **Website:** http://localhost:8080
- **phpMyAdmin:** http://localhost:8081
  - Server: db
  - Username: root
  - Password: root123 (hoặc giá trị DB_PASS trong .env)

## Các lệnh hữu ích

### Xem logs
```bash
# Tất cả services
docker-compose logs -f

# Chỉ web service
docker-compose logs -f web

# Chỉ database
docker-compose logs -f db
```

### Khởi động lại services
```bash
docker-compose restart
```

### Dừng containers
```bash
docker-compose stop
```

### Dừng và xóa containers
```bash
docker-compose down
```

### Dừng và xóa containers + volumes (xóa dữ liệu database)
```bash
docker-compose down -v
```

### Truy cập vào container
```bash
# Vào web container
docker-compose exec web bash

# Vào MySQL container
docker-compose exec db mysql -uroot -proot123 ecommerce_db
```

### Import lại database
```bash
docker-compose exec db mysql -uroot -proot123 ecommerce_db < database/schema.sql
```

### Rebuild khi thay đổi Dockerfile
```bash
docker-compose up -d --build
```

## Cấu trúc Docker

- **db**: MySQL 8.0 database server
  - Port: 3306
  - Volume: Lưu trữ persistent data
  - Auto import: schema.sql khi khởi động lần đầu

- **web**: PHP 8.1 + Apache web server
  - Port: 8080
  - PHP Extensions: PDO, MySQLi, GD, mbstring, etc.
  - Apache mod_rewrite enabled

- **phpmyadmin**: Web-based MySQL admin tool
  - Port: 8081
  - Tự động kết nối với db container

## Troubleshooting

### Database connection failed
Kiểm tra:
1. File `.env` có `DB_HOST=db` chứ không phải `localhost`
2. Đợi database service khởi động hoàn tất (có thể mất 20-30s)
3. Check logs: `docker-compose logs db`

### Port đã được sử dụng
Nếu port 8080 hoặc 3306 đã được dùng, chỉnh sửa trong `docker-compose.yml`:
```yaml
ports:
  - "8090:80"  # Thay đổi 8080 thành 8090
```

### Permission issues với uploads folder
```bash
docker-compose exec web chown -R www-data:www-data /var/www/html/uploads
docker-compose exec web chmod -R 755 /var/www/html/uploads
```

### Xóa và rebuild từ đầu
```bash
docker-compose down -v
docker system prune -a
docker-compose up -d --build
```

## Production Notes

Khi deploy lên production:
1. Đổi tất cả các mật khẩu mặc định
2. Sử dụng HTTPS (cấu hình SSL/TLS)
3. Tắt phpMyAdmin hoặc bảo mật bằng authentication
4. Set `SESSION_COOKIE_SECURE=1` trong .env
5. Backup database thường xuyên
6. Cấu hình firewall cho các ports
