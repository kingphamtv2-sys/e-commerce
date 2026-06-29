# Backup, Logs and Monitoring Guide

Runbook này áp dụng cho Task 29. Nó không thêm business feature và không public dữ liệu vận hành.

## 1. Operational Targets

Mục tiêu MVP:

* Recovery Point Objective (RPO): tối đa 24 giờ với backup hằng ngày.
* Recovery Time Objective (RTO): xác định bằng restore test thực tế; mục tiêu ban đầu dưới 4 giờ.
* Backup database, `storage/app/public` và `.env` đã mã hóa.
* Backup nằm ngoài web root tại `/var/backups/ecommerce-system`.
* Laravel dùng daily log, web server dùng logrotate của hệ điều hành.
* Uptime, tuổi backup và disk được kiểm tra mỗi 5 phút.
* Payment, queue và scheduler monitoring chỉ bật khi môi trường sử dụng.

RPO/RTO phải được điều chỉnh nếu lượng đơn hàng yêu cầu backup thường xuyên hơn.

## 2. Backup Layout and Security

```txt
/var/backups/ecommerce-system
├── backup-20260625T020000Z
│   ├── database.sql.gz
│   ├── storage-public.tar.gz
│   ├── production-env.age
│   ├── METADATA
│   ├── SHA256SUMS
│   └── SUCCESS
└── latest -> backup-20260625T020000Z
```

Quy tắc:

* Backup root phải là absolute path ngoài `/var/www/ecommerce-system/public`.
* Backup root và backup files dùng quyền riêng tư (`umask 077`).
* Không backup `public/storage` vì đây là symlink; nguồn thật là `storage/app/public`.
* Không backup cache, session, compiled views hoặc source đã có trong Git.
* `.env` không bao giờ được copy plaintext vào backup set.
* Chỉ backup `.env` bằng `age`; private decryption key phải nằm ngoài server production nếu có thể.
* Không upload backup vào public object storage/bucket.
* Có ít nhất một bản offsite hoặc snapshot khác failure domain trước go-live thương mại.
* Nginx/Apache, systemd và cron production nên được quản lý bằng configuration management; nếu có thay đổi ngoài Git, lưu chúng trong archive đã mã hóa cùng chính sách truy cập như `.env`.

## 3. One-time Setup

### Backup directories

```bash
sudo install -d -m 0700 -o deploy -g deploy /var/backups/ecommerce-system
sudo install -d -m 0750 -o root -g deploy /etc/ecommerce-system
```

### MySQL backup account

Tạo account có quyền đọc cần thiết. Điều chỉnh quyền theo MySQL/MariaDB version và chính sách DBA:

```sql
CREATE USER 'ecommerce_backup'@'127.0.0.1' IDENTIFIED BY '<generated-secret>';
GRANT SELECT, SHOW VIEW, TRIGGER, LOCK TABLES
ON ecommerce_system.* TO 'ecommerce_backup'@'127.0.0.1';
FLUSH PRIVILEGES;
```

Copy template và nhập credential trên server:

```bash
sudo cp deploy/mysql/backup-client.cnf.example /etc/ecommerce-system/backup-my.cnf
sudo editor /etc/ecommerce-system/backup-my.cnf
sudo chown deploy:deploy /etc/ecommerce-system/backup-my.cnf
sudo chmod 600 /etc/ecommerce-system/backup-my.cnf
```

Script dùng `--defaults-extra-file`; password không xuất hiện trong command line, source hay cron.

### Encrypted `.env` backup

Cài `age` và tạo key trên máy/operator vault an toàn:

```bash
age-keygen -o ecommerce-backup-key.txt
age-keygen -y ecommerce-backup-key.txt
```

Chỉ copy public recipient vào production:

```bash
sudo editor /etc/ecommerce-system/backup-age-recipients.txt
sudo chown deploy:deploy /etc/ecommerce-system/backup-age-recipients.txt
sudo chmod 600 /etc/ecommerce-system/backup-age-recipients.txt
```

Private key `ecommerce-backup-key.txt` không được commit hoặc lưu trong public web root. Nên lưu trong password manager/offline vault với quyền truy cập được kiểm soát.

## 4. Backup Process

Script: `scripts/backup-production.sh`.

```bash
cd /var/www/ecommerce-system
BACKUP_ROOT=/var/backups/ecommerce-system \
BACKUP_MYSQL_CONFIG=/etc/ecommerce-system/backup-my.cnf \
BACKUP_ENV_RECIPIENTS_FILE=/etc/ecommerce-system/backup-age-recipients.txt \
BACKUP_RETENTION_DAYS=14 \
bash scripts/backup-production.sh
```

Script:

1. Chỉ chạy khi `.env` có `APP_ENV=production`, `APP_DEBUG=false`.
2. Từ chối backup root trong public web root.
3. Dùng file MySQL client không cho group/others đọc.
4. Dùng `flock` để ngăn hai backup chạy đồng thời.
5. Dump database bằng consistent transaction và gzip.
6. Nén `storage/app/public`.
7. Mã hóa `.env` bằng `age`.
8. Tạo metadata và SHA-256 checksums.
9. Chỉ tạo marker `SUCCESS` và atomic rename sau khi mọi bước pass.
10. Xóa backup set quá retention.

Tắt backup `.env` chỉ trong tình huống đã có secret manager/versioning độc lập:

```bash
BACKUP_ENV=0 bash scripts/backup-production.sh
```

Việc này phải được ghi rõ trong runbook nội bộ.

## 5. Retention Policy

Mặc định script giữ daily backup trong 14 ngày:

```txt
Daily local: 14 days
Weekly offsite: 8 weeks
Monthly offsite: 12 months
Before risky deployment/migration: giữ tới khi release ổn định và restore test pass
```

Script chỉ tự xóa local directory tên `backup-*` cũ hơn `BACKUP_RETENTION_DAYS`. Weekly/monthly/offsite retention cần được cấu hình ở storage provider hoặc công cụ đồng bộ riêng.

Theo dõi dung lượng trước khi tăng retention.

## 6. Backup Verification

Kiểm tra latest backup:

```bash
bash scripts/verify-backup.sh /var/backups/ecommerce-system/latest
```

Script xác nhận:

* Marker `SUCCESS`.
* Database/storage archive tồn tại và không rỗng.
* SHA-256 checksum đúng.
* Gzip và tar archive đọc được.
* Encrypted `.env` tồn tại nếu metadata yêu cầu.

Đây là integrity check, chưa thay thế restore test.

## 7. Restore Process

Không restore trực tiếp lên production đang phục vụ traffic. Tạo maintenance window, xác nhận backup và snapshot trạng thái hiện tại trước.

### Database restore

Tạo database đích rỗng trên staging/restore host:

```bash
mysql --defaults-extra-file=/etc/ecommerce-system/restore-my.cnf \
    -e 'CREATE DATABASE ecommerce_restore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'

gzip -cd /var/backups/ecommerce-system/latest/database.sql.gz \
    | mysql --defaults-extra-file=/etc/ecommerce-system/restore-my.cnf ecommerce_restore
```

Dump không chứa lệnh tạo/xóa database, nên chỉ được nạp vào database đích đã tạo rõ ràng.

### Uploaded files restore

Restore vào staging root:

```bash
tar -xzf /var/backups/ecommerce-system/latest/storage-public.tar.gz \
    -C /var/www/ecommerce-restore

cd /var/www/ecommerce-restore
php artisan storage:link
sudo chgrp -R www-data storage bootstrap/cache
find storage bootstrap/cache -type d -exec chmod 775 {} +
find storage bootstrap/cache -type f -exec chmod 664 {} +
```

### `.env` restore

Giải mã ở host an toàn:

```bash
age --decrypt \
    --identity /secure/path/ecommerce-backup-key.txt \
    --output /secure/path/restored.env \
    /var/backups/ecommerce-system/latest/production-env.age
chmod 600 /secure/path/restored.env
```

Không in `.env` ra terminal/log. Khi restore staging, thay domain, database, mail và payment credential bằng giá trị staging; không tái sử dụng production payment secret ngoài production.

### Restore checklist

* [ ] Backup checksum và archive verification pass.
* [ ] Restore thực hiện trên isolated staging/restore environment.
* [ ] Database restore không báo lỗi.
* [ ] Uploaded files restore đúng path.
* [ ] `.env` được giải mã an toàn và chỉnh cho môi trường đích.
* [ ] `public/storage` là symlink đúng.
* [ ] Permissions của storage/cache đúng.
* [ ] `php artisan optimize:clear` chạy thành công.
* [ ] `php artisan migrate:status` không có trạng thái bất thường.
* [ ] Homepage, admin login, catalog và ảnh hoạt động.
* [ ] Orders, inventory, coupon usages và payment records tồn tại.
* [ ] Webhook logs và payment transaction history truy vấn được.
* [ ] Restore result được ghi ngày, backup ID, thời gian restore và người thực hiện.

## 8. Restore Test Checklist

Thực hiện trước go-live và ít nhất hằng tháng:

1. Chọn backup production mới nhất đã verify.
2. Tạo host/database staging cô lập.
3. Restore database và uploaded files.
4. Dùng staging `.env`; vô hiệu hóa mail/payment outbound thật.
5. Chạy application smoke tests.
6. So sánh row counts quan trọng: orders, order_items, inventory_stocks, payments, payment_transactions.
7. Mở ngẫu nhiên sản phẩm có ảnh và đơn hàng cũ.
8. Kiểm tra không có secret production trong response/log.
9. Ghi restore duration để đo RTO.
10. Xóa dữ liệu restore test theo chính sách bảo mật.

Restore test thất bại phải được xem như backup incident, không chỉ là lỗi staging.

## 9. Cron and Log Rotation

Templates:

* `deploy/cron/ecommerce-system.example`
* `deploy/logrotate/ecommerce-system`

```bash
sudo cp deploy/cron/ecommerce-system.example /etc/cron.d/ecommerce-system
sudo editor /etc/cron.d/ecommerce-system
sudo chmod 644 /etc/cron.d/ecommerce-system

sudo install -m 0640 -o deploy -g adm /dev/null /var/log/ecommerce-backup.log
sudo install -m 0640 -o deploy -g adm /dev/null /var/log/ecommerce-monitor.log
sudo install -m 0640 -o deploy -g adm /dev/null /var/log/ecommerce-scheduler.log

sudo cp deploy/logrotate/ecommerce-system /etc/logrotate.d/ecommerce-system
sudo logrotate --debug /etc/logrotate.d/ecommerce-system
```

Thay user/group `deploy` và `adm` trong templates nếu server dùng account khác.

Cron template:

* Backup hằng ngày lúc 02:00 UTC.
* Verify latest backup hằng tuần.
* Monitor mỗi 5 phút.
* Scheduler line để comment cho tới khi code có scheduled tasks.

Cron mail hoặc external monitor phải cảnh báo khi command exit khác 0.

## 10. Laravel Logs

Production `.env`:

```env
LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=warning
LOG_DAILY_DAYS=14
```

Laravel daily channel tự tạo `storage/logs/laravel-YYYY-MM-DD.log` và giữ 14 ngày. Không dùng logrotate lần hai trên cùng Laravel daily files.

Review:

```bash
ls -lh storage/logs
tail -n 200 storage/logs/laravel-*.log
rg -i 'emergency|alert|critical|error' storage/logs
```

Không log:

* Password/password confirmation.
* API key, payment hash secret hoặc full credential.
* `.env`.
* Authorization, cookie, session hoặc CSRF token.
* Card data.

Log được phép nên dùng order code, transaction number, gateway code và sanitized error category.

## 11. Web Server Logs

Nginx:

```bash
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/nginx/error.log
sudo logrotate --debug /etc/logrotate.d/nginx
```

Apache:

```bash
sudo tail -f /var/log/apache2/ecommerce-access.log
sudo tail -f /var/log/apache2/ecommerce-error.log
sudo logrotate --debug /etc/logrotate.d/apache2
```

Theo dõi 5xx, 429, 404 spike, upload failure và request đáng ngờ tới admin/payment. Không đưa secret vào URL vì access log thường ghi query string.

Logs và `/var/log` không được map vào document root hoặc download route.

## 12. Payment/IPN/Webhook Monitoring

Hệ thống lưu operational payment data trong:

* `payment_transactions`.
* `payment_webhook_logs`.
* `order_payments` và `payments`.

Các header nhạy cảm đã được loại trước khi lưu webhook headers. Gateway payload vẫn phải tiếp tục được sanitize tại adapter trước khi persist.

Queries tham khảo:

```sql
SELECT COUNT(*) AS invalid_signatures
FROM payment_webhook_logs
WHERE signature_valid = 0
  AND created_at >= UTC_TIMESTAMP() - INTERVAL 1 HOUR;

SELECT id, gateway_code, event_id, processing_error, created_at
FROM payment_webhook_logs
WHERE processing_error IS NOT NULL
ORDER BY id DESC
LIMIT 50;

SELECT transaction_number, gateway_code, status, created_at
FROM payment_transactions
WHERE status IN ('pending', 'processing')
  AND created_at < UTC_TIMESTAMP() - INTERVAL 1 HOUR;
```

Critical:

* Amount/currency mismatch.
* Webhook processing error.
* Paid transaction nhưng order chưa paid.
* Duplicate paid transition.

Warning:

* Invalid signature tăng bất thường.
* Pending transaction quá một giờ.
* Gateway failure rate tăng.

Không export payload/header đầy đủ vào alert.

## 13. Monitoring Script

Script: `scripts/monitor-production.sh`.

Mặc định kiểm tra:

* HTTPS health endpoint `/health`.
* Latest successful backup không quá 30 giờ.
* Disk usage `/var/www`, backup root và `/var/log`.
* Error-level Laravel log entries gần đây.

Chạy:

```bash
HEALTHCHECK_URL=https://shop.example.com/health \
BACKUP_ROOT=/var/backups/ecommerce-system \
bash scripts/monitor-production.sh
```

Exit codes:

```txt
0 = ok
1 = warning
2 = critical
```

Bật payment checks:

```bash
MONITOR_PAYMENT=1 \
MONITOR_MYSQL_CONFIG=/etc/ecommerce-system/backup-my.cnf \
bash scripts/monitor-production.sh
```

Alert webhook là optional và được truyền qua environment/secret store:

```bash
ALERT_WEBHOOK_URL='<secret-webhook-url>' bash scripts/monitor-production.sh
```

Script chỉ gửi summary count, không gửi logs, payload hoặc secret.

## 14. Queue Monitoring

Project có database queue tables nhưng hiện chưa có `ShouldQueue`, nên queue monitor mặc định tắt.

Khi queue được sử dụng:

```bash
MONITOR_QUEUE=1 \
QUEUE_SERVICE=ecommerce-queue.service \
bash scripts/monitor-production.sh

systemctl status ecommerce-queue
php artisan queue:failed
```

Monitor kiểm tra service, failed jobs và cảnh báo backlog trên 100 jobs. Không tự động retry tất cả failed jobs nếu chưa xem nguyên nhân/idempotency.

## 15. Scheduler Monitoring

`php artisan schedule:list` hiện không có task. Chưa bật scheduler cron/monitor.

Khi có schedule, cron phải touch heartbeat sau khi `schedule:run` thành công:

```cron
* * * * * deploy cd /var/www/ecommerce-system && /usr/bin/php artisan schedule:run >> /var/log/ecommerce-scheduler.log 2>&1 && touch /var/lib/ecommerce-system/scheduler.last-run
```

```bash
sudo install -d -m 0755 -o deploy -g deploy /var/lib/ecommerce-system
MONITOR_SCHEDULER=1 bash scripts/monitor-production.sh
```

Heartbeat mặc định critical nếu cũ hơn 180 giây.

## 16. Disk and Uptime Monitoring

Threshold:

| Usage | Action |
| --- | --- |
| 80% | Warning |
| 90% | Critical |
| 95% | Emergency cleanup/traffic decision |

Manual checks:

```bash
df -h /var/www /var/backups /var/log
du -sh storage/app/public storage/logs /var/backups/ecommerce-system
```

External uptime monitor nên gọi:

```txt
GET https://shop.example.com/health
```

Endpoint `/health` trả đúng JSON `{"status":"ok"}` và không expose database credential, environment, version hay filesystem path. `/up` mặc định của Laravel vẫn được giữ cho compatibility.

Khuyến nghị hai probe location và alert sau 2-3 lần lỗi liên tiếp để tránh false positive.

## 17. Error Alerting

MVP alert sources:

* Monitor script exit code qua cron mail/systemd.
* Optional Slack-compatible webhook summary.
* External uptime provider.
* Disk alert từ server provider.

Alert critical:

* Website down.
* Backup quá 30 giờ hoặc backup command fail.
* Disk >= 90%.
* Payment webhook processing error/status mismatch.
* Queue worker stopped hoặc failed jobs tồn tại.
* Scheduler heartbeat stale khi scheduler đã bật.

Alert không được chứa `.env`, SQL dump, payment payload, headers, passwords hay webhook URL.

## 18. Operational Checklist

### Daily

* [ ] `/health` hoạt động từ external monitor.
* [ ] Latest backup có marker `SUCCESS` và dưới 30 giờ.
* [ ] Không có disk critical.
* [ ] Laravel/web server logs không có lỗi tăng bất thường.
* [ ] Payment invalid signature/webhook error không bất thường.
* [ ] Queue service/failed jobs ổn nếu queue bật.

### Weekly

* [ ] Chạy `verify-backup.sh`.
* [ ] Kiểm tra retention không xóa backup mới.
* [ ] Kiểm tra backup/log sizes.
* [ ] Review Nginx/Apache 5xx, 429 và suspicious requests.
* [ ] Review payment pending/failed/webhook errors.
* [ ] Kiểm tra SSL expiry.

### Monthly

* [ ] Restore test trên isolated staging.
* [ ] Ghi RPO/RTO thực tế.
* [ ] Kiểm tra offsite backup và quyền truy cập.
* [ ] Dependency/security audit.
* [ ] Kiểm tra database/upload growth.
* [ ] Rotate/review operator access và backup encryption keys.

## 19. Incident Checklist

Khi backup/monitor báo lỗi:

1. Ghi nhận timestamp, host, release và alert category.
2. Không paste secret/payload đầy đủ vào ticket/chat.
3. Kiểm tra disk, DB connectivity, service status và sanitized logs.
4. Với payment mismatch, tạm dừng thao tác thủ công gây duplicate và đối chiếu transaction/order.
5. Với backup failure, sửa nguyên nhân và chạy lại backup; không chỉ xóa alert.
6. Verify backup mới.
7. Ghi root cause và preventive action.

## 20. Scope Boundaries and Remaining Risks

Task 29 chưa triển khai enterprise backup, immutable storage, centralized log search, SIEM hay distributed tracing.

Local backup không bảo vệ khỏi mất toàn server/ransomware. Offsite encrypted backup và restore test định kỳ vẫn bắt buộc cho production có dữ liệu thật.
