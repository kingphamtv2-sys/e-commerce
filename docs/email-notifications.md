# Transactional Email Operations

## Configuration

Production credential chỉ cấu hình trong server `.env` hoặc secret manager:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=smtp.example.com
MAIL_PORT=465
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=orders@shop.example.com
MAIL_FROM_NAME="${APP_NAME}"
QUEUE_CONNECTION=database
```

Không đưa SMTP password/API key vào source, admin settings hoặc log. Admin chỉ
cấu hình notification switches, admin recipients và optional From override tại
**Admin → Email Notifications**.

Sau khi đổi `.env`:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan queue:restart
```

## Queue

Email jobs dùng queue `emails`, retry 3 lần với backoff. Production worker:

```bash
php artisan queue:work --queue=emails,default --sleep=3 --tries=3 --timeout=90
```

Với systemd, dùng `deploy/systemd/ecommerce-queue.service.example`. Theo dõi:

```bash
sudo systemctl status ecommerce-queue
php artisan queue:monitor emails:100
php artisan queue:failed
```

## Local test

Nhanh nhất là dùng log mailer:

```dotenv
MAIL_MAILER=log
QUEUE_CONNECTION=sync
```

```bash
php artisan optimize:clear
tail -f storage/logs/laravel.log
```

Hoặc chạy Mailpit ở port SMTP `1025`, UI `8025`, rồi đặt `MAIL_MAILER=smtp`,
`MAIL_SCHEME=null`, `MAIL_HOST=127.0.0.1`, `MAIL_PORT=1025`.

## Production smoke test

- Chạy migration và bảo đảm worker đang hoạt động.
- Gửi test email từ admin; kiểm tra inbox và `email_logs.status=sent`.
- Tạo COD order: customer và admin nhận đúng một email.
- Test payment sandbox thành công: nhận đúng một payment-success email.
- Gửi lại cùng callback/webhook: không tạo email log trùng.
- Bật payment-failed email, test failed/cancelled, rồi tắt lại nếu không dùng.
- Đổi order status và cancel order; kiểm tra đúng locale, snapshot items và currency.
- Kiểm tra `php artisan queue:failed` và Laravel log không chứa credential.

Query vận hành:

```bash
php artisan tinker
```

```php
App\Models\EmailLog::latest()->limit(20)->get([
    'event', 'recipient_email', 'status', 'attempts', 'error_message', 'created_at',
]);
```

## Failure handling

Order/payment transaction commit trước khi reserve email. Lỗi dispatch hoặc SMTP
chỉ chuyển `email_logs` sang `failed`; không rollback nghiệp vụ. Sau khi sửa SMTP:

```bash
php artisan queue:retry all
php artisan queue:restart
```

Nếu job đã hết retry nhưng cần gửi lại, xác minh recipient/event trước khi xử lý
thủ công; không xóa idempotency log hàng loạt vì có thể gây gửi trùng.
