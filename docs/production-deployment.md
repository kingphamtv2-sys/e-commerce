# Production Deployment Guide

Runbook này triển khai Laravel e-commerce lên production trong phạm vi Task 28.

## 1. Production Baseline

Stack khuyến nghị:

* Ubuntu Server LTS.
* Nginx + PHP-FPM; Apache là phương án thay thế.
* PHP 8.2+, MySQL 8 hoặc MariaDB tương thích.
* Composer 2, Node.js LTS, npm và Git.
* systemd cho queue worker khi queue được sử dụng.

PHP extensions nên có:

```txt
cli, fpm, mysql, mbstring, xml, curl, zip, bcmath, intl, gd
```

Chỉ mở port `22`, `80`, `443`. Không public MySQL nếu database chỉ dùng nội bộ.

## 2. Directory and Document Root

Source:

```txt
/var/www/ecommerce-system
```

Document root bắt buộc:

```txt
/var/www/ecommerce-system/public
```

Không trỏ web server vào project root vì có thể làm lộ `.env`, source và log.

## 3. Pre-deployment Checklist

* [ ] Commit/tag cần deploy đã review và test.
* [ ] `php artisan test` pass ở build/staging.
* [ ] `composer audit` và `npm audit` đã được xem xét.
* [ ] `.env` không được Git track.
* [ ] Domain đã trỏ DNS tới server.
* [ ] Database và non-root database user đã tạo.
* [ ] Có snapshot/backup database trước migration khi nâng cấp.
* [ ] PHP-FPM, Composer, Node/npm, Git và web server đã cài.
* [ ] SSL certificate có thể cấp.
* [ ] Payment credentials đúng môi trường được lưu ngoài source.
* [ ] Đã xác định web server user/group, thường là `www-data`.

Kiểm tra `.env`:

```bash
git ls-files --error-unmatch .env
```

Kết quả đúng là lệnh báo path không được track. `.env`, `.env.production` và `.env.backup` đã nằm trong `.gitignore`.

## 4. First Deployment

```bash
sudo mkdir -p /var/www/ecommerce-system
sudo chown "$USER":www-data /var/www/ecommerce-system
git clone <repository-url> /var/www/ecommerce-system
cd /var/www/ecommerce-system
git checkout <release-tag-or-commit>
cp .env.production.example .env
chmod 640 .env
```

Điền `.env` trên server hoặc bằng secret manager. Không ghi password/secret vào source, script hay deployment log.

Các giá trị bắt buộc:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://shop.example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce_system
DB_USERNAME=ecommerce_app
DB_PASSWORD=<secret>

SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
FILESYSTEM_LOCAL_SERVE=false
```

`APP_URL` phải là HTTPS URL thật. Chỉ tạo `APP_KEY` một lần:

```bash
php artisan key:generate
```

Không thay `APP_KEY` sau khi có encrypted data.

### Database initialization

Tạo database `utf8mb4` và app user riêng, sau đó:

```bash
php artisan migrate:status
php artisan migrate --force
```

Không bao giờ chạy `php artisan migrate:fresh` trên production.

Chỉ seed lần đầu khi cần dữ liệu nền:

```bash
php artisan db:seed --force
```

Trước khi seed production phải cấu hình `SUPER_ADMIN_EMAIL` và `SUPER_ADMIN_PASSWORD`.

## 5. Repeatable Deployment

Fetch và checkout release trước:

```bash
git fetch --tags origin
git checkout <release-tag-or-commit>
```

Sau đó chạy script:

```bash
CONFIRM_PRODUCTION_DEPLOY=YES \
WEB_GROUP=www-data \
bash scripts/deploy-production.sh
```

Script thực hiện:

```bash
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan optimize:clear
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Script dừng trước migration nếu `.env` thiếu/bị track, chưa xác nhận deploy, `APP_ENV`/`APP_DEBUG` sai, `APP_URL` không phải HTTPS domain, `APP_KEY`/database password thiếu, database user là `root`, hoặc secure session cookie chưa bật.

Production luôn build bằng `npm run build`, không chạy Vite dev server.

## 6. Storage and Permissions

```bash
php artisan storage:link
test -L public/storage

sudo chgrp -R www-data storage bootstrap/cache
find storage bootstrap/cache -type d -exec chmod 775 {} +
find storage bootstrap/cache -type f -exec chmod 664 {} +
chmod 640 .env
```

Không dùng `chmod -R 777`; không cấp quyền ghi toàn bộ source cho web server.

```bash
sudo -u www-data test -w storage/framework
sudo -u www-data test -w storage/logs
sudo -u www-data test -w bootstrap/cache
```

## 7. Nginx

Mẫu: `deploy/nginx/ecommerce.conf.example`.

Mẫu TLS tham chiếu certificate đã tồn tại. Ở first deploy, cấp certificate theo mục SSL trước khi enable vhost này.

```bash
sudo cp deploy/nginx/ecommerce.conf.example /etc/nginx/sites-available/ecommerce-system
sudo editor /etc/nginx/sites-available/ecommerce-system
sudo ln -s /etc/nginx/sites-available/ecommerce-system /etc/nginx/sites-enabled/ecommerce-system
sudo nginx -t
sudo systemctl reload nginx
```

Thay domain, PHP-FPM socket và certificate path. Cấu hình phải redirect HTTPS, dùng `/public`, fallback tới `index.php`, chặn dotfiles và không thực thi PHP trong `/storage`.

## 8. Apache Alternative

Mẫu: `deploy/apache/ecommerce.conf.example`.

Mẫu TLS tham chiếu certificate đã tồn tại. Ở first deploy, cấp certificate theo mục SSL trước khi enable vhost này.

```bash
sudo a2enmod rewrite ssl headers proxy_fcgi setenvif
sudo a2ensite ecommerce-system
sudo apachectl configtest
sudo systemctl reload apache2
```

Document root vẫn là `/var/www/ecommerce-system/public`. Chỉ chọn Nginx hoặc Apache.

## 9. SSL and HTTPS

First deploy có thể cấp certificate bằng standalone mode khi port 80 chưa bị web server chiếm:

```bash
sudo apt install certbot
sudo systemctl stop nginx
sudo certbot certonly --standalone -d shop.example.com
sudo systemctl start nginx
sudo certbot renew --dry-run
```

Nếu chọn Apache, thay `nginx` trong hai lệnh `systemctl` bằng `apache2`.

Sau khi certificate tồn tại, enable cấu hình Nginx hoặc Apache ở trên. Nếu site HTTP đã hoạt động và đang dùng cấu hình do Certbot hỗ trợ, có thể dùng plugin tương ứng:

```bash
sudo apt install python3-certbot-nginx
sudo certbot --nginx -d shop.example.com

sudo apt install python3-certbot-apache
sudo certbot --apache -d shop.example.com
```

Sau khi HTTPS hoạt động:

* Dùng `APP_URL=https://shop.example.com`.
* Bật `SESSION_SECURE_COOKIE=true`.
* Payment return/webhook URL phải dùng HTTPS.
* Chạy lại `php artisan optimize:clear && php artisan config:cache`.
* Kiểm tra mixed content.

Nếu TLS kết thúc ở proxy/load balancer, cấu hình `TRUSTED_PROXIES` bằng IP/CIDR cụ thể; không dùng `*` trên production.

## 10. Queue Worker

Project dùng database queue cho transactional email. Queue worker là bắt buộc khi
`QUEUE_CONNECTION=database`; worker mẫu ưu tiên queue `emails` rồi tới `default`.

Khi bắt đầu dispatch job:

```bash
sudo cp deploy/systemd/ecommerce-queue.service.example /etc/systemd/system/ecommerce-queue.service
sudo editor /etc/systemd/system/ecommerce-queue.service
sudo systemctl daemon-reload
sudo systemctl enable --now ecommerce-queue
sudo systemctl status ecommerce-queue
```

Sau deployment có thay đổi job:

```bash
php artisan queue:restart
```

Đặt `RESTART_QUEUE_WORKERS=1` khi chạy deploy script nếu service đã bật.

Sau khi cấu hình SMTP, vào **Admin → Email Notifications** để gửi test email.
Không nhập SMTP password/API key trong admin; credential chỉ nằm trong production
`.env` hoặc secret manager. Xem checklist chi tiết tại `docs/email-notifications.md`.

## 11. Scheduler

`php artisan schedule:list` hiện báo không có scheduled task, vì vậy chưa cần cron.

Khi code có schedule:

```cron
* * * * * cd /var/www/ecommerce-system && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

```bash
php artisan schedule:list
php artisan schedule:run
```

Chỉ cần một scheduler runner; không tự thêm business schedule khi code chưa định nghĩa.

## 12. Post-deployment Checklist

### Infrastructure

* [ ] `APP_ENV=production`, `APP_DEBUG=false`.
* [ ] `APP_URL` là HTTPS domain thật.
* [ ] Document root kết thúc bằng `/public`.
* [ ] HTTP redirect HTTPS; certificate hợp lệ.
* [ ] `.env`, logs và private storage không truy cập qua HTTP.
* [ ] `public/storage` là symlink hợp lệ.
* [ ] `storage` và `bootstrap/cache` writable.
* [ ] `public/build/manifest.json` tồn tại.
* [ ] `php artisan migrate:status` không có migration pending.
* [ ] Config, route và view cache tạo thành công.
* [ ] Queue service chạy nếu code dispatch job.
* [ ] SMTP/provider production đã cấu hình; admin test email có `email_logs.status=sent`.
* [ ] Worker đang nghe `emails,default`; `php artisan queue:failed` không có lỗi mới.
* [ ] Scheduler chạy nếu `schedule:list` có task.

### Application smoke test

* [ ] `GET /health` trả HTTP 200 với JSON `{"status":"ok"}`.
* [ ] Homepage, catalog và product detail load qua HTTPS.
* [ ] CSS/JS và ảnh `/storage/...` tải được.
* [ ] Cart add/update/remove hoạt động.
* [ ] Coupon hợp lệ/không hợp lệ đúng.
* [ ] Checkout tính subtotal, discount, tax và total đúng.
* [ ] Tạo COD order test; order, payment snapshot và tồn kho đúng.
* [ ] Admin login, dashboard, order và report hoạt động.
* [ ] Product image upload hoạt động.
* [ ] Online payment chỉ test với mode/credential được phê duyệt.
* [ ] Callback sai signature không mark paid.
* [ ] Không có debug page hoặc lỗi nghiêm trọng.

HTTP smoke test:

```bash
curl -I http://shop.example.com
curl -I https://shop.example.com/health
curl -I https://shop.example.com/products
curl -I https://shop.example.com/.env
```

Kỳ vọng: HTTP redirect HTTPS; `/health` và `/products` hoạt động; `/.env` không trả nội dung.

## 13. Rollback Plan

Trước deploy, ghi release hiện tại, backup `.env` an toàn và snapshot database trước migration có rủi ro.

Rollback source/assets:

```bash
git checkout <previous-release-tag-or-commit>
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

Nếu lỗi `.env`, khôi phục bản an toàn rồi rebuild config cache.

Không tự động chạy `migrate:rollback` trên production. Chỉ rollback database sau khi review migration và có backup/restore plan; ưu tiên forward-fix hoặc restore snapshot trong maintenance window.

## 14. Remaining Operational Boundaries

Task 28 không thiết lập backup rotation, centralized logs, uptime monitoring hoặc alerting; các phần đó thuộc Task 29.

Không dùng production payment credential cho giao dịch test tùy tiện. Cần thống nhất test mode, amount và quy trình đối soát với provider.

Sau deployment, cài quy trình vận hành trong `docs/backup-logs-monitoring.md`.
