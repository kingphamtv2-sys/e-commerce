# Task 29: Backup, Logs and Monitoring

## 1. Overview

Task này dùng để xây dựng quy trình backup, logging và monitoring cho hệ thống e-commerce Laravel sau khi đã chuẩn bị production deployment.

Sau các task trước, hệ thống đã có:

* Core e-commerce features.
* End-to-end testing.
* Security hardening.
* Production deployment checklist.

Task 29 tập trung vào việc đảm bảo hệ thống có thể:

* Sao lưu database.
* Sao lưu uploaded files.
* Sao lưu cấu hình quan trọng.
* Kiểm tra và khôi phục backup.
* Theo dõi Laravel logs.
* Theo dõi web server logs.
* Theo dõi payment logs.
* Theo dõi queue/scheduler nếu có.
* Theo dõi lỗi nghiêm trọng.
* Theo dõi dung lượng ổ đĩa.
* Theo dõi trạng thái website.
* Chuẩn bị cảnh báo khi có sự cố.

Task này không thêm business feature mới. Đây là task vận hành production.

---

## 2. Objectives

Sau khi hoàn thành Task 29, hệ thống cần đạt:

* Có quy trình backup database.
* Có quy trình backup uploaded files.
* Có quy trình backup `.env` production an toàn.
* Có thư mục lưu backup rõ ràng.
* Có retention policy cho backup.
* Có restore checklist.
* Có thể test restore trên môi trường staging/local.
* Laravel logs được cấu hình hợp lý.
* Payment/IPN/webhook logs được kiểm tra.
* Queue logs được kiểm tra nếu dùng queue.
* Scheduler logs được kiểm tra nếu dùng cron.
* Web server logs được kiểm tra.
* Có monitoring cơ bản cho uptime, disk, error logs.
* Có cảnh báo cơ bản nếu website lỗi hoặc disk gần đầy.
* Không lưu backup trong public web root.
* Không expose backup/log ra public.
* Không log secret/password/payment secret.
* Không dùng Vue.js.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong Task 29:

* Database backup.
* Uploaded files backup.
* `.env` backup an toàn.
* Backup retention.
* Backup restore checklist.
* Backup verification.
* Laravel log configuration.
* Log rotation.
* Payment log review.
* Queue worker monitoring nếu có.
* Scheduler monitoring nếu có.
* Web server log review.
* Disk usage monitoring.
* Basic uptime monitoring.
* Basic error alerting.
* Documentation for operations.
* Optional backup script.
* Optional restore script.
* Optional cron setup.

### 3.2. Out of Scope

Không làm trong Task 29:

* Không implement business feature mới.
* Không redesign UI.
* Không implement Email Notification feature cho khách hàng.
* Không implement advanced observability platform.
* Không implement distributed tracing.
* Không implement full SIEM.
* Không implement enterprise backup system.
* Không implement CI/CD nâng cao.
* Không implement auto-scaling.
* Không implement refund/return/shipping feature.
* Không dùng Vue.js.

---

## 4. Dependencies

Task này phụ thuộc:

| Task    | Dependency            |
| ------- | --------------------- |
| Task 27 | Security Hardening    |
| Task 28 | Production Deployment |

Task này hỗ trợ vận hành các module:

| Module    | Reason                        |
| --------- | ----------------------------- |
| Orders    | Cần bảo vệ dữ liệu đơn hàng   |
| Payments  | Cần log và backup transaction |
| Products  | Cần backup sản phẩm và ảnh    |
| Inventory | Cần backup tồn kho            |
| Reports   | Cần dữ liệu chính xác         |
| Admin     | Cần log lỗi và cảnh báo       |

---

## 5. Backup Strategy

Backup cần bao gồm 3 nhóm chính:

| Backup Type     | Content                                           |
| --------------- | ------------------------------------------------- |
| Database Backup | MySQL/MariaDB database                            |
| File Backup     | Uploaded images, banners, public storage          |
| Config Backup   | `.env`, web server config, queue/scheduler config |

Business rules:

* Database backup là bắt buộc.
* Uploaded files backup là bắt buộc.
* `.env` backup phải bảo mật.
* Backup không được nằm trong thư mục public.
* Backup cần có retention policy.
* Backup cần được test restore.
* Backup cần có timestamp rõ ràng.
* Không chỉ backup source code vì source code đã có Git.

---

## 6. Backup Storage Location

Backup không được lưu trong thư mục public web root.

Không lưu ở:

```txt
/var/www/ecommerce-system/public
```

Vị trí đề xuất:

```txt
/var/backups/ecommerce-system
```

Cấu trúc đề xuất:

```txt
/var/backups/ecommerce-system
├── database
├── storage
├── config
├── logs
└── restore-tests
```

Business rules:

* Thư mục backup chỉ user deploy/root được đọc.
* Web server không được public thư mục backup.
* Backup chứa `.env` phải hạn chế quyền đọc.
* Nếu có cloud backup, upload lên storage riêng sau khi backup local.

---

## 7. Database Backup

Database backup cần chạy định kỳ.

### 7.1. Backup Command

Với MySQL/MariaDB:

```bash
mysqldump -u ecommerce_user -p ecommerce_system > ecommerce_system_YYYYMMDD_HHMMSS.sql
```

Hoặc nén lại:

```bash
mysqldump -u ecommerce_user -p ecommerce_system | gzip > ecommerce_system_YYYYMMDD_HHMMSS.sql.gz
```

Business rules:

* Không hard-code password trong script nếu không cần.
* Nếu dùng script tự động, dùng file config an toàn hoặc user có quyền phù hợp.
* Backup filename phải có timestamp.
* Backup phải bao gồm toàn bộ database production.
* Không chạy `migrate:fresh` trên production.

### 7.2. Database Backup Frequency

Khuyến nghị MVP:

| Environment                 | Frequency                         |
| --------------------------- | --------------------------------- |
| Production có đơn hàng thật | Daily                             |
| Production traffic cao      | Every 6 hours hoặc hourly nếu cần |
| Before deployment           | Manual backup                     |
| Before migration lớn        | Manual backup                     |
| Before payment change       | Manual backup                     |

### 7.3. Retention Policy

Khuyến nghị:

| Backup Type    | Retention             |
| -------------- | --------------------- |
| Daily backup   | 7 - 14 days           |
| Weekly backup  | 4 - 8 weeks           |
| Monthly backup | 6 - 12 months nếu cần |

MVP có thể dùng:

```txt
Giữ daily backup 14 ngày gần nhất.
```

---

## 8. Uploaded Files Backup

Uploaded files bao gồm:

* Product images.
* Variant images.
* Banner images.
* Logo/favicon.
* Other public uploaded files.

Vị trí thường gặp:

```txt
storage/app/public
public/storage
```

Business rules:

* Backup source chính nên là `storage/app/public`.
* `public/storage` thường là symlink, không cần backup riêng nếu đã backup storage gốc.
* Không backup cache/temp không cần thiết.
* File backup cần nén nếu dung lượng lớn.

Command gợi ý:

```bash
tar -czf storage_public_YYYYMMDD_HHMMSS.tar.gz storage/app/public
```

---

## 9. Config Backup

Các file cấu hình quan trọng:

| File/Config                  | Reason                               |
| ---------------------------- | ------------------------------------ |
| `.env`                       | Production secrets/config            |
| Nginx site config            | Web server routing                   |
| Supervisor config            | Queue worker                         |
| Cron config                  | Scheduler/backup                     |
| SSL notes                    | Domain/SSL operation                 |
| Payment credentials location | Không backup plain nếu không an toàn |

Business rules:

* `.env` backup cần bảo mật.
* Không upload `.env` backup vào public storage.
* Không commit `.env` vào Git.
* Có thể lưu `.env` backup encrypted nếu có quy trình.
* Không log nội dung `.env`.

---

## 10. Backup Automation

Có thể tạo script:

```txt
scripts/backup-production.sh
```

Script backup có thể làm:

* Tạo timestamp.
* Backup database.
* Backup storage/app/public.
* Backup `.env` nếu được phép.
* Nén file backup.
* Xóa backup cũ theo retention.
* Ghi log backup thành công/thất bại.

Business rules:

* Script không chứa password plain text nếu không cần.
* Script không chứa payment secret.
* Script không đặt backup vào public.
* Script cần fail rõ ràng nếu backup lỗi.
* Script cần ghi log vào file riêng.

---

## 11. Cron Backup

Có thể dùng cron để chạy backup hằng ngày.

Ví dụ lịch chạy:

```txt
0 2 * * * /var/www/ecommerce-system/scripts/backup-production.sh >> /var/log/ecommerce-backup.log 2>&1
```

Business rules:

* Backup nên chạy giờ thấp điểm.
* Cron output cần ghi log.
* Nếu backup failed, cần có cách phát hiện.
* Cron không nên chạy quá thường xuyên gây nặng server.

---

## 12. Backup Verification

Backup không có ý nghĩa nếu không test restore.

### 12.1. Verification Checklist

Sau khi backup:

* File database backup tồn tại.
* File storage backup tồn tại.
* File size hợp lý.
* File không rỗng.
* File nén mở được.
* Database dump có nội dung.
* Backup log không có lỗi.
* Retention cleanup không xóa nhầm backup mới.

### 12.2. Restore Test

Định kỳ test restore trên local/staging:

1. Tạo database test.
2. Restore database backup.
3. Restore storage files.
4. Cấu hình `.env` staging/local.
5. Chạy app.
6. Kiểm tra admin/order/product/image.

Business rules:

* Không test restore trên production đang chạy.
* Không restore backup cũ đè production nếu chưa có kế hoạch rollback.
* Restore test nên làm ít nhất trước go-live và định kỳ sau đó.

---

## 13. Restore Procedure

### 13.1. Restore Database

Nếu backup dạng `.sql`:

```bash
mysql -u ecommerce_user -p ecommerce_system < ecommerce_system_backup.sql
```

Nếu backup dạng `.sql.gz`:

```bash
gunzip < ecommerce_system_backup.sql.gz | mysql -u ecommerce_user -p ecommerce_system
```

### 13.2. Restore Uploaded Files

Nếu backup dạng `.tar.gz`:

```bash
tar -xzf storage_public_backup.tar.gz -C /var/www/ecommerce-system
```

Sau đó kiểm tra:

```bash
php artisan storage:link
```

### 13.3. Restore Checklist

* [ ] Database restored.
* [ ] Storage restored.
* [ ] `.env` correct.
* [ ] Permissions correct.
* [ ] `php artisan optimize:clear`.
* [ ] App loads.
* [ ] Admin login works.
* [ ] Product images show.
* [ ] Orders exist.
* [ ] Payment records exist.

---

## 14. Laravel Logs

Laravel logs nằm ở:

```txt
storage/logs/laravel.log
```

### 14.1. Log Requirements

Yêu cầu:

* Production log level phù hợp.
* Không log password.
* Không log payment secret.
* Không log full `.env`.
* Không log card data.
* Không log full session/cookie.
* Payment errors cần log transaction number/order number.
* Order creation errors cần log order/checkout reference.
* Upload errors cần log file validation reason, không log binary.

### 14.2. LOG_LEVEL

Production khuyến nghị:

```env
LOG_LEVEL=error
```

Hoặc:

```env
LOG_LEVEL=warning
```

Trong giai đoạn đầu go-live có thể dùng `warning`, sau ổn định có thể dùng `error`.

---

## 15. Log Rotation

Log file không được phình vô hạn.

Cần kiểm tra:

* Laravel log rotation.
* Nginx/Apache log rotation.
* Backup log rotation.
* Queue worker log rotation.
* Cron log rotation.

Business rules:

* Không để `storage/logs/laravel.log` quá lớn.
* Không để `/var/log/nginx` đầy disk.
* Không để backup log đầy disk.
* Logs cũ có thể nén/xóa theo retention.

---

## 16. Web Server Logs

Nếu dùng Nginx, kiểm tra:

```txt
/var/log/nginx/access.log
/var/log/nginx/error.log
```

Cần theo dõi:

* 500 errors.
* 404 tăng bất thường.
* Payment callback errors.
* Upload errors.
* Too many requests.
* Suspicious admin access.

Business rules:

* Web server logs không public.
* Error logs cần được rotate.
* Không log secret trong URL nếu có thể.
* Payment callback không nên đưa secret vào query ngoài tham số gateway bắt buộc.

---

## 17. Payment Logs and Monitoring

Payment là khu vực cần theo dõi kỹ.

### 17.1. Payment Logs

Cần theo dõi:

| Area              | What to Monitor                 |
| ----------------- | ------------------------------- |
| COD               | Admin mark paid/cancel          |
| Online payment    | Transaction pending/paid/failed |
| VNPAY IPN         | Signature valid/invalid         |
| VNPAY return      | Customer result                 |
| Webhook logs      | Duplicate/failed processing     |
| Amount mismatch   | Critical alert                  |
| Invalid signature | Warning/security alert          |

### 17.2. Payment Monitoring Rules

* Invalid signature nhiều lần cần cảnh báo.
* Amount mismatch là critical.
* Payment success nhưng order chưa paid là critical.
* Order paid nhiều lần là critical.
* IPN failed nhiều lần cần kiểm tra.
* Transaction pending quá lâu cần kiểm tra.

---

## 18. Queue Monitoring

Nếu project dùng queue:

Cần theo dõi:

* Queue worker đang chạy.
* Failed jobs.
* Queue backlog.
* Worker restart sau deployment.
* Queue logs.

Commands:

```bash
php artisan queue:failed
```

Nếu có failed job:

```bash
php artisan queue:retry all
```

Business rules:

* Không để queue worker chết.
* Không để failed jobs tồn đọng không biết.
* Nếu dùng Supervisor/systemd, cần auto restart.
* Sau deployment cần `php artisan queue:restart`.

---

## 19. Scheduler Monitoring

Nếu project dùng scheduler:

Cần theo dõi:

* Cron Laravel scheduler đang chạy.
* Backup schedule có chạy không.
* Payment expiry schedule nếu có.
* Report cleanup nếu có.
* Scheduler log không lỗi.

Cron example:

```txt
* * * * * cd /var/www/ecommerce-system && php artisan schedule:run >> /var/log/ecommerce-scheduler.log 2>&1
```

Business rules:

* Scheduler log cần rotate.
* Nếu backup chạy qua scheduler/cron, cần kiểm tra backup output.
* Không chạy task nặng quá thường xuyên.

---

## 20. Disk Monitoring

Disk đầy có thể làm hệ thống lỗi nghiêm trọng.

Cần theo dõi:

* `/var/www`
* `/var/backups`
* `/var/log`
* Database storage
* Uploaded files storage

Command kiểm tra:

```bash
df -h
du -sh storage
du -sh /var/backups/ecommerce-system
du -sh storage/logs
```

Alert threshold đề xuất:

| Disk Usage | Action            |
| ---------- | ----------------- |
| >= 80%     | Warning           |
| >= 90%     | Critical          |
| >= 95%     | Emergency cleanup |

Business rules:

* Backup retention phải tránh đầy disk.
* Log rotation phải hoạt động.
* Upload files tăng nhanh cần theo dõi.

---

## 21. Uptime Monitoring

Cần có kiểm tra website còn sống.

MVP có thể kiểm tra:

* Homepage HTTP 200.
* Admin login page HTTP 200.
* Product catalog HTTP 200.
* Checkout page nếu có cart thì không phù hợp health check.
* Payment return/IPN route không nên dùng làm public health check.

Có thể tạo route health check nếu cần:

```txt
/health
```

Health check nên trả:

```txt
OK
```

Business rules:

* Health check không expose sensitive info.
* Không trả database credentials.
* Không trả app version nếu không cần.
* Nếu check database, chỉ trả pass/fail đơn giản.

---

## 22. Error Alerting

MVP cần có cảnh báo cơ bản.

Có thể cảnh báo khi:

* Website down.
* 500 errors tăng.
* Disk usage cao.
* Backup failed.
* Payment IPN failed.
* Queue worker stopped.
* Failed jobs tồn đọng.
* Invalid payment signature bất thường.
* Database backup không tạo trong ngày.

Alert channels có thể là:

* Email admin.
* Telegram.
* Slack.
* Server monitoring provider.
* Manual log review trong MVP.

Task 29 chỉ yêu cầu chuẩn bị cơ chế hoặc checklist. Email notification business sẽ làm task khác.

---

## 23. Monitoring Dashboard Optional

Có thể tạo admin page đơn giản:

```txt
/admin/system-health
```

MVP optional.

Thông tin có thể hiển thị:

* App environment.
* Last backup time.
* Backup status.
* Disk usage.
* Laravel log latest errors.
* Queue failed jobs count.
* Payment failed count.
* Scheduler status.

Business rules:

* Chỉ admin được xem.
* Không hiển thị secrets.
* Không hiển thị full `.env`.
* Không expose system path nhạy cảm nếu không cần.

---

## 24. Security Rules

Backup/log/monitoring phải tuân thủ security:

* Backup không public.
* Logs không public.
* `.env` backup không public.
* Payment secret không log.
* Password không log.
* Backup file permission an toàn.
* Health check không expose sensitive info.
* Monitoring admin page phải có admin middleware.
* Export logs không chứa secret.
* Cloud backup credentials không commit Git.

---

## 25. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type       | Description                              |
| ---------- | ---------------------------------------- |
| Docs       | Backup/log/monitoring guide              |
| Script     | Optional backup-production.sh            |
| Script     | Optional restore checklist/script        |
| Config     | Optional log config notes                |
| Route      | Optional health check route              |
| Controller | Optional SystemHealthController          |
| Blade      | Optional admin system health page        |
| Command    | Optional custom backup/health command    |
| Tests      | Optional health route/admin access tests |

Task này có thể chỉ tạo documentation và scripts nếu chưa muốn thêm admin system health page.

---

## 26. Suggested Backup Script Requirements

Nếu tạo backup script, script cần:

* Tạo timestamp.
* Tạo thư mục nếu chưa có.
* Backup database.
* Backup storage/app/public.
* Backup `.env` nếu option enabled.
* Nén backup.
* Xóa backup cũ theo retention.
* Ghi log.
* Trả exit code khác 0 nếu lỗi.
* Không chứa secret hard-code.

Pseudo-flow:

```txt
1. Set project path.
2. Set backup path.
3. Create timestamp.
4. Dump database.
5. Compress storage.
6. Copy .env securely if allowed.
7. Remove old backups.
8. Write success/failure log.
```

Không cần implement quá phức tạp trong MVP.

---

## 27. Suggested Health Check Requirements

Nếu tạo `/health`:

Response:

```json
{
  "status": "ok"
}
```

Business rules:

* Không cần auth nếu chỉ trả `ok`.
* Không expose database details.
* Nếu check database, chỉ trả ok/error.
* Không trả `.env`, config, server path.
* Có thể giới hạn bằng IP nếu cần.

Nếu tạo `/admin/system-health`, route phải có admin middleware.

---

## 28. Operational Checklist

### 28.1. Daily Checklist

* [ ] Website còn truy cập được.
* [ ] Không có Laravel error critical.
* [ ] Backup hôm nay đã tạo.
* [ ] Disk usage dưới ngưỡng.
* [ ] Payment transactions không lỗi bất thường.
* [ ] Queue worker chạy nếu có.
* [ ] No failed jobs nghiêm trọng.

### 28.2. Weekly Checklist

* [ ] Test mở backup file.
* [ ] Kiểm tra backup retention.
* [ ] Kiểm tra log size.
* [ ] Kiểm tra web server errors.
* [ ] Kiểm tra payment failed/invalid signature.
* [ ] Kiểm tra storage upload growth.

### 28.3. Monthly Checklist

* [ ] Test restore backup trên staging/local.
* [ ] Kiểm tra security updates.
* [ ] Kiểm tra dependency audit.
* [ ] Kiểm tra SSL expiry.
* [ ] Kiểm tra database size.
* [ ] Kiểm tra backup offsite nếu có.

---

## 29. Commands Summary

### 29.1. Backup Commands

```bash
mysqldump -u ecommerce_user -p ecommerce_system | gzip > ecommerce_system_YYYYMMDD_HHMMSS.sql.gz
tar -czf storage_public_YYYYMMDD_HHMMSS.tar.gz storage/app/public
```

### 29.2. Restore Commands

```bash
gunzip < ecommerce_system_backup.sql.gz | mysql -u ecommerce_user -p ecommerce_system
tar -xzf storage_public_backup.tar.gz -C /var/www/ecommerce-system
php artisan storage:link
php artisan optimize:clear
```

### 29.3. Log Commands

```bash
tail -f storage/logs/laravel.log
tail -f /var/log/nginx/error.log
php artisan queue:failed
df -h
```

### 29.4. Laravel Commands

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

---

## 30. Error Handling

| Problem                        | Expected Action                       |
| ------------------------------ | ------------------------------------- |
| Backup database failed         | Log error, alert admin                |
| Backup storage failed          | Log error, alert admin                |
| Backup file size 0             | Treat as failed                       |
| Disk > 90%                     | Alert and cleanup old logs/backups    |
| Laravel log has critical error | Investigate immediately               |
| Payment amount mismatch        | Critical alert                        |
| Invalid signature repeated     | Security warning                      |
| Queue worker stopped           | Restart and investigate               |
| Failed jobs exist              | Retry or inspect                      |
| Scheduler not running          | Fix cron                              |
| Restore failed                 | Document issue and fix backup process |

---

## 31. Acceptance Criteria

Task này hoàn thành khi:

* [ ] Có tài liệu backup/logs/monitoring.
* [ ] Có kế hoạch backup database.
* [ ] Có kế hoạch backup uploaded files.
* [ ] Có kế hoạch backup `.env` an toàn.
* [ ] Backup không nằm trong public web root.
* [ ] Có retention policy.
* [ ] Có restore checklist.
* [ ] Có quy trình test restore.
* [ ] Laravel logs được kiểm tra.
* [ ] Web server logs được kiểm tra.
* [ ] Payment logs/IPN/webhook logs được kiểm tra.
* [ ] Queue monitoring có hướng dẫn nếu dùng queue.
* [ ] Scheduler monitoring có hướng dẫn nếu dùng scheduler.
* [ ] Disk monitoring có hướng dẫn.
* [ ] Uptime monitoring có hướng dẫn.
* [ ] Error alerting có hướng dẫn.
* [ ] Logs không chứa password/secret/payment secret.
* [ ] Backup files không public.
* [ ] Optional backup script không hard-code secret.
* [ ] Optional health check không expose sensitive info.
* [ ] Có daily/weekly/monthly checklist.
* [ ] Không thêm business feature mới.
* [ ] Không dùng Vue.js.

---

## 32. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-27-security-hardening.md
* docs/tasks/task-28-production-deployment.md
* docs/tasks/task-29-backup-logs-and-monitoring.md

Sau đó implement Task 29: Backup, Logs and Monitoring theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 29.
* Không thêm business feature mới.
* Không redesign UI.
* Tạo hoặc cập nhật tài liệu backup/logs/monitoring nếu cần.
* Chuẩn bị quy trình backup database.
* Chuẩn bị quy trình backup uploaded files.
* Chuẩn bị quy trình backup `.env` production an toàn.
* Backup không được nằm trong public web root.
* Có retention policy.
* Có restore checklist.
* Có test restore checklist.
* Kiểm tra Laravel logs.
* Kiểm tra web server logs.
* Kiểm tra payment logs/IPN/webhook logs.
* Kiểm tra queue monitoring nếu project dùng queue.
* Kiểm tra scheduler monitoring nếu project dùng scheduler.
* Chuẩn bị disk monitoring.
* Chuẩn bị uptime monitoring.
* Chuẩn bị error alerting cơ bản.
* Không log password/API key/payment secret.
* Không expose backup/logs ra public.
* Có thể tạo optional backup script nếu phù hợp.
* Có thể tạo optional health check route nếu phù hợp.
* Health check không được expose sensitive info.
* Nếu tạo admin system health page thì phải có admin middleware.
* Không hard-code secret/password trong source hoặc script.
* Không dùng Vue.js.
* Sau khi làm xong, báo cáo:

  * File đã tạo/sửa.
  * Backup process.
  * Restore process.
  * Monitoring checklist.
  * Lệnh cần chạy.
  * Rủi ro còn lại nếu có.
