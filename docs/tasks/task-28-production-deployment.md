# Task 28: Production Deployment

## 1. Overview

Task này dùng để chuẩn bị và triển khai hệ thống e-commerce Laravel lên môi trường production.

Sau khi đã hoàn thành:

* Task 26: End-to-End Testing and Bug Fix
* Task 27: Security Hardening

hệ thống đã đủ điều kiện để chuẩn bị chạy trên server thật.

Task 28 tập trung vào:

* Chuẩn bị server production.
* Cài đặt PHP, MySQL/MariaDB, Nginx/Apache.
* Cấu hình domain.
* Cấu hình SSL/HTTPS.
* Cấu hình `.env` production.
* Deploy source code.
* Cài Composer dependencies.
* Build frontend assets.
* Chạy database migrations.
* Cấu hình storage.
* Cấu hình queue nếu có.
* Cấu hình scheduler nếu có.
* Cấu hình permissions.
* Cấu hình Laravel optimization.
* Kiểm tra deployment.
* Chuẩn bị rollback cơ bản.

Task này không thêm feature mới. Đây là task đưa hệ thống từ local/dev sang production.

---

## 2. Objectives

Sau khi hoàn thành Task 28, hệ thống cần đạt:

* Website chạy được trên domain thật.
* Website chạy bằng HTTPS.
* Laravel chạy với `APP_ENV=production`.
* Laravel chạy với `APP_DEBUG=false`.
* Source code được deploy đúng thư mục.
* `.env` production được cấu hình đúng.
* Database production được tạo đúng.
* Migration chạy thành công.
* Storage link hoạt động.
* Upload image hoạt động.
* Public catalog hoạt động.
* Cart/checkout/order hoạt động.
* Admin login hoạt động.
* Admin dashboard hoạt động.
* Payment COD hoạt động.
* Online payment/VNPAY production hoặc sandbox production test hoạt động theo cấu hình.
* Assets CSS/JS được build production.
* File/folder permissions đúng.
* Route/cache/config/view optimization hoạt động.
* Không expose `.env`, logs, source private files.
* Có checklist kiểm tra sau deployment.
* Có quy trình rollback cơ bản nếu deploy lỗi.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong Task 28:

* Server preparation.
* PHP extensions.
* Composer install production.
* Node/NPM build.
* Web server config.
* Domain config.
* SSL config.
* Production `.env`.
* Database setup.
* Migration.
* Seeder cần thiết.
* Storage link.
* File permissions.
* Laravel optimization.
* Queue worker setup nếu project dùng queue.
* Scheduler cron setup nếu project dùng scheduler.
* Payment production settings.
* Deployment verification.
* Basic rollback plan.
* Deployment checklist.

### 3.2. Out of Scope

Không làm trong Task 28:

* Không thêm feature mới.
* Không sửa logic business nếu không phải lỗi deployment.
* Không implement Backup/Monitoring chi tiết.
* Không implement Email Notification.
* Không implement Customer Account nâng cao.
* Không implement Shipping Management.
* Không implement CI/CD nâng cao.
* Không implement Docker production nếu chưa chọn Docker làm strategy chính.
* Không redesign UI.
* Không dùng Vue.js.

Backup, logs và monitoring chi tiết sẽ làm ở Task 29.

---

## 4. Recommended Production Stack

Stack khuyến nghị cho Laravel production:

| Component       | Recommendation                         |
| --------------- | -------------------------------------- |
| OS              | Ubuntu Server                          |
| Web Server      | Nginx                                  |
| PHP Runtime     | PHP-FPM                                |
| Database        | MySQL hoặc MariaDB                     |
| Cache           | File/database trước, Redis optional    |
| Queue           | sync/database trước, Redis optional    |
| SSL             | Let's Encrypt hoặc SSL provider        |
| Process Manager | systemd hoặc Supervisor nếu dùng queue |
| Deployment      | Git pull/manual deploy MVP             |

MVP có thể dùng manual deployment trước. CI/CD nâng cao có thể làm task sau.

---

## 5. Dependencies

Task này phụ thuộc:

| Task    | Dependency                     |
| ------- | ------------------------------ |
| Task 26 | End-to-End Testing and Bug Fix |
| Task 27 | Security Hardening             |

Task này chuẩn bị cho:

| Task    | Purpose                            |
| ------- | ---------------------------------- |
| Task 29 | Backup, Logs and Monitoring        |
| Task 30 | Email Notification                 |
| Task 31 | Customer Account and Order History |

---

## 6. Server Preparation

Server production cần chuẩn bị:

| Item          | Requirement                      |
| ------------- | -------------------------------- |
| SSH access    | Có user deploy hoặc admin        |
| Domain        | Đã trỏ DNS về server             |
| Firewall      | Chỉ mở port cần thiết            |
| PHP           | Version phù hợp Laravel 12       |
| Composer      | Installed                        |
| Node.js/NPM   | Installed để build assets        |
| MySQL/MariaDB | Installed                        |
| Nginx/Apache  | Installed                        |
| Git           | Installed                        |
| SSL tool      | Installed nếu dùng Let's Encrypt |

Ports cần mở:

| Port | Purpose |
| ---- | ------- |
| 22   | SSH     |
| 80   | HTTP    |
| 443  | HTTPS   |

Business rules:

* Không dùng root database user cho Laravel app.
* Không để APP_DEBUG=true trên production.
* Không đặt source code trong thư mục public web root trực tiếp ngoài thư mục `public`.
* Web server document root phải trỏ vào thư mục `public`.

---

## 7. Production Directory Structure

Cấu trúc deploy khuyến nghị:

```txt
/var/www/ecommerce-system
```

Bên trong:

```txt
/var/www/ecommerce-system
├── app
├── bootstrap
├── config
├── database
├── public
├── resources
├── routes
├── storage
├── vendor
├── .env
├── artisan
├── composer.json
├── package.json
```

Web server document root phải là:

```txt
/var/www/ecommerce-system/public
```

Không trỏ web server vào:

```txt
/var/www/ecommerce-system
```

---

## 8. Source Code Deployment

### 8.1. First Deployment

Các bước tổng quát:

```bash
cd /var/www
git clone <repository-url> ecommerce-system
cd ecommerce-system
```

Sau đó checkout branch production:

```bash
git checkout main
```

Hoặc nếu dùng branch riêng:

```bash
git checkout production
```

### 8.2. Update Deployment

Khi deploy bản mới:

```bash
cd /var/www/ecommerce-system
git pull origin main
```

Business rules:

* Không edit code trực tiếp trên production nếu không cần.
* Source production nên đến từ Git.
* Không commit `.env`.
* Không commit `vendor`.
* Không commit `node_modules`.

---

## 9. Environment Configuration

Tạo file `.env` production từ `.env.example`.

```bash
cp .env.example .env
```

### 9.1. Required Production ENV

Các cấu hình bắt buộc:

```env
APP_NAME="E-commerce System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce_system
DB_USERNAME=ecommerce_user
DB_PASSWORD=strong_password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

LOG_CHANNEL=stack
LOG_LEVEL=error
```

### 9.2. Security ENV

Khuyến nghị:

```env
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

Nếu production chưa có HTTPS, không bật `SESSION_SECURE_COOKIE=true` cho tới khi SSL hoạt động. Tuy nhiên production thật bắt buộc nên dùng HTTPS.

### 9.3. APP_KEY

Nếu chưa có APP_KEY:

```bash
php artisan key:generate
```

Business rules:

* Không đổi APP_KEY tùy tiện sau khi production có dữ liệu thật.
* APP_KEY ảnh hưởng encrypted data/session/cookie.
* Backup `.env` production an toàn.

---

## 10. Database Setup

Tạo database và user riêng cho app.

Yêu cầu:

| Item          | Requirement                          |
| ------------- | ------------------------------------ |
| Database name | ecommerce_system hoặc tên production |
| DB user       | Không dùng root                      |
| Charset       | utf8mb4                              |
| Collation     | utf8mb4_unicode_ci                   |
| Password      | Strong password                      |

Sau khi cấu hình `.env`, test connection bằng migration status:

```bash
php artisan migrate:status
```

Chạy migration:

```bash
php artisan migrate --force
```

Nếu production lần đầu cần seed dữ liệu cơ bản:

```bash
php artisan db:seed --force
```

Business rules:

* Không chạy `migrate:fresh` trên production có dữ liệu thật.
* Không xóa database production nếu chưa backup.
* Migration production phải chạy với `--force`.

---

## 11. Composer Dependencies

Cài dependency production:

```bash
composer install --no-dev --optimize-autoloader
```

Business rules:

* Không cài dev dependencies trên production nếu không cần.
* Nếu Composer bị lỗi extension PHP, cài extension còn thiếu.
* Không chạy composer update trên production nếu không có kế hoạch.

---

## 12. Frontend Assets Build

Cài Node dependencies và build assets:

```bash
npm install
npm run build
```

Nếu muốn giảm dev dependencies trên production sau build, có thể cân nhắc:

```bash
npm prune --production
```

Business rules:

* Production phải dùng build assets, không dùng `npm run dev`.
* Không chạy Vite dev server trên production.
* File build phải nằm đúng nơi Laravel/Vite cần.

---

## 13. Storage Setup

Laravel cần storage link:

```bash
php artisan storage:link
```

Các thư mục cần writable:

```txt
storage
bootstrap/cache
```

Set permission phù hợp cho web server user.

Business rules:

* Product images, variant images, banners phải hiển thị được.
* Public chỉ truy cập được file trong `public`.
* Không expose file private/log/env.

---

## 14. Web Server Configuration

### 14.1. Nginx Requirement

Nginx site phải:

* Trỏ document root vào `/public`.
* Forward request tới PHP-FPM.
* Hỗ trợ Laravel route fallback.
* Chặn truy cập `.env`.
* Chặn truy cập hidden files không cần thiết.
* Chặn truy cập storage/logs/source private.

Document root:

```txt
/var/www/ecommerce-system/public
```

### 14.2. Apache Alternative

Nếu dùng Apache:

* Bật rewrite module.
* Document root trỏ vào `public`.
* `.htaccess` hoạt động.
* Chặn hidden files/private files.

MVP khuyến nghị dùng Nginx nếu server mới.

---

## 15. SSL / HTTPS

Production cần HTTPS.

Yêu cầu:

* Domain trỏ về server.
* SSL certificate hoạt động.
* HTTP redirect sang HTTPS.
* APP_URL dùng `https://`.
* Payment return/IPN URL dùng HTTPS.
* Session secure cookie hoạt động khi HTTPS ổn định.

Kiểm tra:

```txt
https://your-domain.com
```

Business rules:

* Không dùng HTTP cho checkout/payment production.
* VNPAY/IPN/return URL nên dùng HTTPS.
* Mixed content không được xảy ra.

---

## 16. Laravel Optimization

Sau khi cấu hình production:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Nếu app dùng closure routes không cache được, cần sửa hoặc không dùng route cache.

Business rules:

* Sau khi đổi `.env`, phải clear/cache lại config.
* Nếu route cache lỗi, không ép route cache cho tới khi xử lý.
* Nếu view lỗi, clear view cache.

Rollback cache:

```bash
php artisan optimize:clear
```

---

## 17. Queue Setup

Nếu project dùng queue cho email/payment/log:

| Config           | Requirement                  |
| ---------------- | ---------------------------- |
| QUEUE_CONNECTION | database hoặc redis          |
| queue table      | Đã migrate                   |
| worker           | Chạy bằng Supervisor/systemd |

MVP nếu chưa dùng queue có thể dùng:

```env
QUEUE_CONNECTION=sync
```

Nhưng production tốt hơn nên dùng `database` hoặc `redis` cho tác vụ nặng.

Nếu dùng database queue:

```bash
php artisan queue:work
```

Cần process manager để tự restart worker.

Business rules:

* Không để queue worker chết mà không biết.
* Khi deploy code mới, queue worker cần restart.
* Email notification task sau có thể cần queue.

---

## 18. Scheduler Setup

Nếu project có scheduled commands:

* Coupon expiration.
* Payment expiry.
* Report cleanup.
* Backup sau này.
* Queue retry sau này.

Cần cron chạy Laravel scheduler:

```txt
* * * * * cd /var/www/ecommerce-system && php artisan schedule:run >> /dev/null 2>&1
```

Task 28 chỉ cấu hình nếu project đã có scheduler. Backup chi tiết sẽ làm Task 29.

---

## 19. Payment Production Configuration

### 19.1. COD

Kiểm tra:

* COD enabled/disabled đúng.
* Min/max amount đúng.
* Instruction đúng.
* Admin mark paid hoạt động.

### 19.2. Online Payment / VNPAY

Nếu dùng VNPAY:

| Config           | Requirement                       |
| ---------------- | --------------------------------- |
| TMN Code         | Production hoặc sandbox tương ứng |
| Hash Secret      | Đúng môi trường                   |
| Payment URL      | Đúng môi trường                   |
| Return URL       | HTTPS domain                      |
| IPN URL          | HTTPS domain                      |
| Currency         | VND                               |
| Signature verify | Hoạt động                         |
| Amount x100      | Đúng                              |
| Idempotency      | Hoạt động                         |

Business rules:

* Không dùng sandbox secret cho production thật.
* Không dùng production secret trên local không an toàn.
* Không log hash secret.
* IPN URL phải truy cập được từ VNPAY.

---

## 20. File Permissions

Các thư mục cần web server ghi:

```txt
storage
bootstrap/cache
```

Không nên set permission quá rộng nếu không cần.

Business rules:

* Web server user cần quyền ghi storage/cache.
* Source code không cần writable toàn bộ.
* `.env` chỉ user deploy/web server cần đọc.
* Không để file permission làm lộ secret.

---

## 21. Deployment Checklist

### 21.1. Pre-deployment Checklist

* [ ] Code đã commit.
* [ ] Code đã push Git.
* [ ] Task 26 test đã pass.
* [ ] Task 27 security hardening đã pass.
* [ ] `.env` không tracked.
* [ ] Production server sẵn sàng.
* [ ] Domain đã trỏ DNS.
* [ ] Database đã tạo.
* [ ] DB user không phải root.
* [ ] SSL có thể cài.
* [ ] Payment credentials sẵn sàng.
* [ ] Backup dữ liệu cũ nếu có.

### 21.2. Deployment Checklist

* [ ] Pull/clone source.
* [ ] Tạo `.env`.
* [ ] Set APP_ENV=production.
* [ ] Set APP_DEBUG=false.
* [ ] Set APP_URL=https domain.
* [ ] Configure database.
* [ ] Run composer install production.
* [ ] Run npm install/build.
* [ ] Run php artisan key:generate nếu cần.
* [ ] Run php artisan migrate --force.
* [ ] Run php artisan storage:link.
* [ ] Set file permissions.
* [ ] Configure Nginx/Apache.
* [ ] Configure SSL.
* [ ] Run Laravel cache commands.
* [ ] Configure queue worker nếu cần.
* [ ] Configure scheduler nếu cần.

### 21.3. Post-deployment Checklist

* [ ] Homepage load được.
* [ ] Public catalog load được.
* [ ] Product detail load được.
* [ ] Admin login được.
* [ ] Admin dashboard load được.
* [ ] Image upload hoạt động.
* [ ] Cart hoạt động.
* [ ] Coupon hoạt động.
* [ ] Checkout hoạt động.
* [ ] COD order hoạt động.
* [ ] Online payment sandbox/production test hoạt động.
* [ ] Admin order detail hoạt động.
* [ ] Report load được.
* [ ] No 500 error.
* [ ] No Laravel debug page.
* [ ] SSL valid.
* [ ] HTTP redirect HTTPS.
* [ ] Browser console không có lỗi nghiêm trọng.

---

## 22. Smoke Test After Deployment

Sau deployment, chạy smoke test nhanh.

### 22.1. Public Smoke Test

| ID            | Scenario          | Expected Result    |
| ------------- | ----------------- | ------------------ |
| SMOKE-PUB-001 | Mở homepage       | Load thành công    |
| SMOKE-PUB-002 | Mở catalog        | Product hiển thị   |
| SMOKE-PUB-003 | Mở product detail | Detail hiển thị    |
| SMOKE-PUB-004 | Add to cart       | Cart cập nhật      |
| SMOKE-PUB-005 | Apply coupon      | Discount đúng      |
| SMOKE-PUB-006 | Checkout          | Checkout page load |
| SMOKE-PUB-007 | Place COD order   | Order created      |

### 22.2. Admin Smoke Test

| ID            | Scenario     | Expected Result |
| ------------- | ------------ | --------------- |
| SMOKE-ADM-001 | Admin login  | Thành công      |
| SMOKE-ADM-002 | Dashboard    | Load số liệu    |
| SMOKE-ADM-003 | Product list | Load được       |
| SMOKE-ADM-004 | Upload image | Thành công      |
| SMOKE-ADM-005 | Order list   | Load được       |
| SMOKE-ADM-006 | Order detail | Load được       |
| SMOKE-ADM-007 | Report       | Load được       |

### 22.3. Payment Smoke Test

| ID            | Scenario                    | Expected Result       |
| ------------- | --------------------------- | --------------------- |
| SMOKE-PAY-001 | COD payment                 | Order unpaid/pending  |
| SMOKE-PAY-002 | Admin mark COD paid         | Paid status           |
| SMOKE-PAY-003 | Online payment redirect     | Redirect đúng         |
| SMOKE-PAY-004 | Online payment callback/IPN | Verify và update đúng |
| SMOKE-PAY-005 | Invalid payment callback    | Không mark paid       |

---

## 23. Rollback Plan

Production deployment cần có rollback cơ bản.

### 23.1. Source Rollback

Nếu deploy lỗi do source code:

```bash
git log --oneline
git checkout <previous-commit-or-tag>
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 23.2. Database Rollback

Cẩn thận với database production.

Business rules:

* Không rollback migration tùy tiện nếu có dữ liệu thật.
* Trước migration lớn phải backup database.
* Nếu migration chỉ thêm bảng/cột, rollback code thường an toàn hơn rollback DB.
* Task 29 sẽ làm backup/restore chi tiết.

### 23.3. Env Rollback

Nếu lỗi do `.env`:

* Khôi phục `.env` backup.
* Chạy `php artisan optimize:clear`.
* Chạy lại cache config nếu cần.

---

## 24. Production Error Handling

Production phải xử lý lỗi an toàn:

* Không hiện stack trace.
* Log lỗi vào `storage/logs`.
* User thấy trang lỗi thân thiện.
* Payment error không expose secret.
* Admin có thể xem trạng thái order/payment nếu cần.

Kiểm tra:

```bash
tail -f storage/logs/laravel.log
```

Business rules:

* Không để log file public.
* Không log password/payment secret.
* Không để log phình quá lớn mà không rotate. Log monitoring chi tiết ở Task 29.

---

## 25. Security Verification Before Go-live

Trước khi công khai website:

* [ ] APP_DEBUG=false.
* [ ] APP_ENV=production.
* [ ] HTTPS hoạt động.
* [ ] Admin URL protected.
* [ ] `.env` không truy cập được bằng browser.
* [ ] `/storage/logs` không truy cập public.
* [ ] Payment secret không expose.
* [ ] Upload file nguy hiểm bị chặn.
* [ ] User không xem được order người khác.
* [ ] Guest token hoạt động đúng.
* [ ] VNPAY/IPN signature verify đúng.
* [ ] CSRF hoạt động.
* [ ] Cookie secure hoạt động trên HTTPS.

---

## 26. Performance Verification

MVP production cần kiểm tra cơ bản:

* Homepage load ổn.
* Catalog query không quá chậm.
* Product detail load ổn.
* Cart/checkout không chậm bất thường.
* Admin orders/report load được.
* Dashboard không query quá nặng.
* Assets đã minify/build.
* Cache config/route/view hoạt động nếu không lỗi.

Advanced performance optimization sẽ làm ở task riêng nếu cần.

---

## 27. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type         | Description                    |
| ------------ | ------------------------------ |
| Docs         | Production deployment guide    |
| Config       | Production env example notes   |
| Server Notes | Nginx/Apache deployment notes  |
| Scripts      | Optional deploy script         |
| Tests        | Smoke test checklist           |
| Docs         | Rollback checklist             |
| Docs         | Payment production setup notes |

Task này chủ yếu là documentation + deployment preparation. Không bắt buộc tạo migration mới.

---

## 28. Optional Deploy Script

Có thể tạo script deploy cơ bản nếu phù hợp:

```txt
scripts/deploy-production.sh
```

Script có thể thực hiện:

* Pull source.
* Composer install.
* NPM build.
* Migrate.
* Clear/cache Laravel.
* Restart queue worker nếu có.

Business rules:

* Script không chứa secret.
* Script không hard-code password.
* Script cần confirmation trước migration nếu chạy production.
* Script phải dễ đọc và an toàn.

---

## 29. Commands Summary

Các lệnh thường dùng khi deploy:

```bash
cd /var/www/ecommerce-system

git pull origin main

composer install --no-dev --optimize-autoloader

npm install
npm run build

php artisan migrate --force
php artisan storage:link

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Nếu có queue:

```bash
php artisan queue:restart
```

Nếu lỗi cache:

```bash
php artisan optimize:clear
```

---

## 30. Error Handling During Deployment

| Error                  | Possible Cause                | Action                           |
| ---------------------- | ----------------------------- | -------------------------------- |
| 500 error              | APP_KEY/env/cache/permission  | Check log, clear cache           |
| DB connection failed   | Wrong DB config/user/password | Fix .env, clear config           |
| CSS/JS missing         | npm build chưa chạy           | Run npm run build                |
| Image not showing      | storage link/permission       | Run storage:link, fix permission |
| Admin login fails      | Session/cache/db issue        | Check session driver/table       |
| Route not found        | route cache old               | optimize:clear, route:cache      |
| Payment callback fails | APP_URL/IPN URL/signature     | Check payment config             |
| Permission denied      | storage/cache ownership       | Fix ownership/permission         |

---

## 31. Acceptance Criteria

Task này hoàn thành khi:

* [ ] Có tài liệu production deployment.
* [ ] Server production được chuẩn bị.
* [ ] Source code được deploy lên server.
* [ ] `.env` production được cấu hình đúng.
* [ ] `APP_ENV=production`.
* [ ] `APP_DEBUG=false`.
* [ ] `APP_URL` dùng HTTPS domain thật.
* [ ] Database production được tạo.
* [ ] App DB user không phải root.
* [ ] Composer dependencies được cài với `--no-dev`.
* [ ] Frontend assets được build bằng `npm run build`.
* [ ] Migrations chạy thành công với `--force`.
* [ ] Storage link hoạt động.
* [ ] File permissions đúng.
* [ ] Web server document root trỏ vào `/public`.
* [ ] SSL/HTTPS hoạt động.
* [ ] HTTP redirect HTTPS nếu có.
* [ ] Laravel config/route/view cache hoạt động hoặc có ghi chú nếu không dùng.
* [ ] Queue worker được cấu hình nếu project dùng queue.
* [ ] Scheduler được cấu hình nếu project dùng scheduler.
* [ ] COD payment hoạt động trên production.
* [ ] Online payment/VNPAY được cấu hình đúng môi trường.
* [ ] Admin login hoạt động.
* [ ] Public catalog/product/cart/checkout/order hoạt động.
* [ ] Smoke test pass.
* [ ] Không expose `.env`.
* [ ] Không expose logs.
* [ ] Không hiển thị Laravel debug page.
* [ ] Có rollback plan cơ bản.
* [ ] Không thêm feature mới.
* [ ] Không dùng Vue.js.

---

## 32. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-26-end-to-end-testing-and-bug-fix.md
* docs/tasks/task-27-security-hardening.md
* docs/tasks/task-28-production-deployment.md

Sau đó implement Task 28: Production Deployment theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 28.
* Không thêm feature business mới.
* Không redesign UI.
* Tạo hoặc cập nhật tài liệu hướng dẫn production deployment nếu cần.
* Chuẩn bị checklist deploy Laravel production.
* Đảm bảo production dùng APP_ENV=production.
* Đảm bảo production dùng APP_DEBUG=false.
* Đảm bảo APP_URL là HTTPS domain.
* Đảm bảo document root trỏ vào thư mục public.
* Đảm bảo `.env` không bị commit.
* Đảm bảo Composer install dùng --no-dev --optimize-autoloader.
* Đảm bảo frontend assets build bằng npm run build.
* Đảm bảo migration production dùng php artisan migrate --force.
* Đảm bảo storage:link hoạt động.
* Đảm bảo storage và bootstrap/cache có permission đúng.
* Đảm bảo có hướng dẫn config Nginx/Apache ở mức cần thiết.
* Đảm bảo có hướng dẫn SSL/HTTPS.
* Đảm bảo có hướng dẫn queue worker nếu project dùng queue.
* Đảm bảo có hướng dẫn scheduler nếu project dùng schedule.
* Đảm bảo có checklist smoke test sau deployment.
* Đảm bảo có rollback plan cơ bản.
* Không chạy migrate:fresh trên production.
* Không hard-code secret/password trong source hoặc script.
* Không dùng Vue.js.
* Sau khi làm xong, báo cáo:

  * File đã tạo/sửa.
  * Checklist deployment.
  * Lệnh cần chạy.
  * Rủi ro còn lại nếu có.
  * Cách test sau deployment.
