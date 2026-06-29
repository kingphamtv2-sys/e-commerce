# Task 30: Email Notification

## 1. Overview

Task này dùng để xây dựng hệ thống gửi email notification cho e-commerce system.

Sau khi hệ thống đã có:

* Order Creation.
* Payment COD.
* Online Payment.
* Admin Order Management.
* Production Deployment.
* Backup, Logs and Monitoring.

Task 30 sẽ bổ sung email notification cho các sự kiện quan trọng trong hệ thống.

Email notification cần hỗ trợ:

* Gửi email xác nhận đơn hàng.
* Gửi email thông báo thanh toán thành công.
* Gửi email thông báo thanh toán thất bại.
* Gửi email thông báo cập nhật trạng thái đơn hàng.
* Gửi email thông báo hủy đơn.
* Gửi email cho admin khi có đơn hàng mới.
* Gửi email test từ admin.
* Quản lý template email cơ bản.
* Hỗ trợ đa ngôn ngữ nếu customer đang dùng language tương ứng.
* Gửi email qua queue nếu production dùng queue.
* Ghi log trạng thái gửi email.
* Không làm ảnh hưởng tốc độ đặt hàng/thanh toán.

Frontend/admin sử dụng:

* Laravel Blade.
* Tailwind CSS.
* Alpine.js nếu cần.
* Fetch API nếu cần.
* Không dùng Vue.js.

---

## 2. Objectives

Sau khi hoàn thành Task 30, hệ thống cần đạt:

* Admin có thể cấu hình email notification cơ bản.
* Admin có thể gửi test email.
* Customer nhận email khi đặt hàng thành công.
* Customer nhận email khi thanh toán online thành công.
* Customer nhận email khi thanh toán online thất bại nếu cần.
* Customer nhận email khi admin cập nhật trạng thái đơn hàng.
* Customer nhận email khi order bị hủy.
* Admin nhận email khi có đơn hàng mới.
* Email hiển thị đúng order number, customer info, items, totals, payment method.
* Email dùng đúng currency snapshot của order.
* Email dùng đúng language của customer/order nếu có.
* Email không gửi trùng khi callback/webhook payment bị gọi nhiều lần.
* Email được queue nếu queue được cấu hình.
* Email sending failure không làm fail order creation/payment flow.
* Có email log hoặc notification log để kiểm tra.
* Không log password/API secret/payment secret.
* Không dùng Vue.js.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong Task 30:

* Email configuration review.
* Admin email notification settings.
* Test email function.
* Order confirmation email.
* Payment success email.
* Payment failed/cancelled email.
* Order status updated email.
* Order cancelled email.
* Admin new order email.
* Email templates bằng Blade.
* Email translation support.
* Email queue support.
* Email log/notification log.
* Email retry handling cơ bản.
* Error handling.
* Tests.
* Production mail checklist.

### 3.2. Out of Scope

Không làm trong Task 30:

* Không implement marketing email campaign.
* Không implement newsletter.
* Không implement abandoned cart email.
* Không implement review reminder email.
* Không implement product review email.
* Không implement SMS notification.
* Không implement push notification.
* Không implement chat notification.
* Không implement advanced email builder.
* Không implement drag-and-drop email template editor.
* Không implement customer notification preferences nâng cao.
* Không implement email analytics nâng cao.
* Không implement open/click tracking.
* Không dùng Vue.js.

Review Product email bị loại khỏi Task 30 để tránh mở rộng phạm vi sang Task 25.

---

## 4. Dependencies

Task này phụ thuộc:

| Task    | Dependency                  |
| ------- | --------------------------- |
| Task 05 | System Settings             |
| Task 06 | Language Management         |
| Task 07 | Currency Management         |
| Task 18 | Payment COD                 |
| Task 19 | Order Creation              |
| Task 20 | Admin Order Management      |
| Task 24 | Online Payment              |
| Task 28 | Production Deployment       |
| Task 29 | Backup, Logs and Monitoring |

---

## 5. User Roles

| Role     | Permission                                        |
| -------- | ------------------------------------------------- |
| Guest    | Nhận email order nếu nhập email hợp lệ            |
| Customer | Nhận email order/payment/status                   |
| Admin    | Cấu hình email notification, nhận email order mới |
| Staff    | Có thể nhận email order mới nếu được cấu hình     |

Business rules:

* Guest order dùng email snapshot trong order.
* Customer order dùng email snapshot trong order, không phụ thuộc email profile hiện tại.
* Admin notification gửi tới email được cấu hình trong admin settings.
* Staff notification optional.

---

## 6. Email Events

### 6.1. Required Email Events

| Event                  | Recipient      | Trigger                                         |
| ---------------------- | -------------- | ----------------------------------------------- |
| Order Created          | Customer/Guest | Order tạo thành công                            |
| New Order Admin Alert  | Admin          | Order tạo thành công                            |
| Payment Success        | Customer/Guest | Online payment paid                             |
| Payment Failed         | Customer/Guest | Online payment failed/cancelled nếu cần         |
| Order Status Updated   | Customer/Guest | Admin đổi order status                          |
| Payment Status Updated | Customer/Guest | Admin mark paid hoặc payment status đổi nếu cần |
| Order Cancelled        | Customer/Guest | Admin hủy order                                 |

### 6.2. Optional Email Events

| Event                    | Recipient | Note                         |
| ------------------------ | --------- | ---------------------------- |
| Low Stock Alert          | Admin     | Có thể làm sau hoặc optional |
| Payment Pending Reminder | Customer  | Out of scope MVP             |
| Review Reminder          | Customer  | Out of scope Task 30         |
| Abandoned Cart           | Customer  | Out of scope Task 30         |

---

## 7. Email Configuration

Email có thể cấu hình qua `.env` và admin settings.

### 7.1. ENV Mail Settings

Production cần có mail config:

| Key               | Description                   |
| ----------------- | ----------------------------- |
| MAIL_MAILER       | smtp/log/ses/mailgun/postmark |
| MAIL_HOST         | SMTP host                     |
| MAIL_PORT         | SMTP port                     |
| MAIL_USERNAME     | SMTP username                 |
| MAIL_PASSWORD     | SMTP password                 |
| MAIL_ENCRYPTION   | tls/ssl/null                  |
| MAIL_FROM_ADDRESS | Default from email            |
| MAIL_FROM_NAME    | Default from name             |

Business rules:

* Không commit mail password.
* Không log mail password.
* `.env.example` chỉ chứa placeholder.
* Local nên dùng log/mailpit/mailtrap.
* Production dùng SMTP/provider thật.

### 7.2. Admin Email Settings

Admin có thể cấu hình:

| Setting                      | Description                          |
| ---------------------------- | ------------------------------------ |
| email_notifications_enabled  | Bật/tắt email notification           |
| admin_order_email_enabled    | Bật/tắt email admin khi có order mới |
| customer_order_email_enabled | Bật/tắt email xác nhận order         |
| payment_email_enabled        | Bật/tắt email payment                |
| order_status_email_enabled   | Bật/tắt email trạng thái             |
| admin_notification_emails    | Danh sách email admin nhận thông báo |
| email_from_name              | Tên người gửi nếu muốn override      |
| email_from_address           | Email người gửi nếu muốn override    |

Business rules:

* Nếu email notification disabled, không gửi email business.
* Test email vẫn có thể dùng để kiểm tra cấu hình nếu admin cho phép.
* Admin notification emails có thể là nhiều email, phân tách bằng comma hoặc lưu JSON.
* Email gửi đi phải validate địa chỉ hợp lệ.

---

## 8. Email Templates

Email templates nên dùng Blade.

### 8.1. Required Templates

| Template             | Purpose                       |
| -------------------- | ----------------------------- |
| order-created        | Email xác nhận đơn hàng       |
| admin-new-order      | Email báo admin có đơn mới    |
| payment-success      | Email thanh toán thành công   |
| payment-failed       | Email thanh toán thất bại     |
| order-status-updated | Email cập nhật trạng thái đơn |
| order-cancelled      | Email hủy đơn                 |
| test-email           | Email test cấu hình           |

### 8.2. Template Content Requirements

Email order cần hiển thị:

| Data             | Requirement                            |
| ---------------- | -------------------------------------- |
| Store name       | Từ system settings                     |
| Logo             | Optional                               |
| Order number     | Required                               |
| Customer name    | Required                               |
| Customer email   | Required                               |
| Customer phone   | Required nếu có                        |
| Order items      | Product name, variant, quantity, price |
| Subtotal         | Required                               |
| Discount         | Nếu có                                 |
| Tax              | Nếu có                                 |
| Shipping         | Nếu có                                 |
| Grand total      | Required                               |
| Payment method   | COD/VNPAY/online                       |
| Payment status   | unpaid/pending/paid                    |
| Shipping address | Required                               |
| Order status     | Required                               |
| Support contact  | Optional                               |

Business rules:

* Email phải dùng order snapshot.
* Không lấy lại giá từ product hiện tại.
* Không tính lại tax/currency.
* Không hiển thị dữ liệu nhạy cảm.
* Không hiển thị payment secret/gateway secret.

---

## 9. Language / Translation Rules

Email cần hỗ trợ đa ngôn ngữ ở mức cơ bản.

Business rules:

* Nếu order có language_code snapshot, dùng language đó.
* Nếu không có language snapshot, dùng current app default language.
* Nếu translation email thiếu, fallback default language.
* Nội dung order item dùng snapshot theo thời điểm order.
* Subject email cũng cần dịch.
* Currency format theo order currency snapshot.

Các email cần translation:

| Email                | Translation Required               |
| -------------------- | ---------------------------------- |
| Order Created        | Yes                                |
| Payment Success      | Yes                                |
| Payment Failed       | Yes                                |
| Order Status Updated | Yes                                |
| Order Cancelled      | Yes                                |
| Admin New Order      | Có thể dùng default admin language |

---

## 10. Email Queue

Production nên gửi email qua queue.

### 10.1. Queue Rules

Business rules:

* Order creation không bị chậm vì gửi email.
* Payment callback/webhook không bị chậm vì gửi email.
* Nếu email fail, order/payment vẫn không rollback.
* Failed email có thể retry.
* Queue worker cần chạy trên production nếu dùng queue.
* Nếu queue chưa cấu hình, có thể dùng sync ở local nhưng production nên dùng database/redis queue.

### 10.2. Queue Events

Các email nên dispatch sau khi transaction database đã commit.

Events đề xuất:

| Event              | Email                            |
| ------------------ | -------------------------------- |
| OrderCreated       | Order confirmation + admin alert |
| PaymentSucceeded   | Payment success email            |
| PaymentFailed      | Payment failed email             |
| OrderStatusChanged | Order status update email        |
| OrderCancelled     | Order cancelled email            |

Business rules:

* Không gửi email trước khi order transaction commit.
* Không gửi payment success email trước khi payment status được mark paid.
* Không gửi trùng email nếu payment webhook duplicate.

---

## 11. Email Idempotency

Email không được gửi trùng trong các trường hợp:

* Customer bấm place order nhiều lần.
* Payment return và IPN cùng báo success.
* VNPAY IPN gửi lại nhiều lần.
* Admin bấm mark paid nhiều lần.
* Admin update status cùng trạng thái.

### 11.1. Required Rules

| Email                | Idempotency Rule                                            |
| -------------------- | ----------------------------------------------------------- |
| Order Created        | Mỗi order gửi customer tối đa 1 lần                         |
| Admin New Order      | Mỗi order gửi admin tối đa 1 lần                            |
| Payment Success      | Mỗi order/payment transaction success gửi tối đa 1 lần      |
| Payment Failed       | Mỗi payment transaction failed gửi tối đa 1 lần nếu enabled |
| Order Cancelled      | Mỗi order cancelled gửi tối đa 1 lần                        |
| Order Status Updated | Gửi khi status thật sự thay đổi                             |

---

## 12. Email Log / Notification Log

Cần có cách kiểm tra email đã gửi hay chưa.

### 12.1. Recommended Table: email_logs

Tạo bảng `email_logs` nếu chưa có.

Fields đề xuất:

| Field                  | Type              | Description                       |
| ---------------------- | ----------------- | --------------------------------- |
| id                     | bigint            | Primary key                       |
| event                  | string            | order_created, payment_success... |
| notifiable_type        | nullable string   | Order/User nếu cần                |
| notifiable_id          | nullable bigint   | ID liên quan                      |
| order_id               | nullable bigint   | Order liên quan                   |
| payment_transaction_id | nullable bigint   | Transaction liên quan             |
| recipient_email        | string            | Người nhận                        |
| subject                | string            | Subject                           |
| status                 | string            | pending, sent, failed             |
| error_message          | text nullable     | Lỗi nếu fail                      |
| sent_at                | datetime nullable | Thời điểm gửi                     |
| created_at             | timestamp         | Created time                      |
| updated_at             | timestamp         | Updated time                      |

Business rules:

* Không lưu nội dung email quá nhạy cảm nếu không cần.
* Không lưu password/secret/token nhạy cảm.
* Có unique rule hoặc check để tránh gửi trùng email quan trọng.
* Email log giúp admin/dev debug production.

### 12.2. Optional Admin Email Log Page

Optional route:

`/admin/email-logs`

Nếu implement:

* Chỉ admin/staff được xem.
* Có filter event/status/date.
* Không hiển thị secret.
* Có thể xem error message.
* Không cần resend trong MVP trừ khi muốn.

MVP có thể chỉ cần bảng log, chưa cần UI nếu muốn đơn giản.

---

## 13. Admin Test Email

Admin cần có nút gửi test email.

### 13.1. Test Email Behavior

Flow:

1. Admin vào Email Settings.
2. Nhập email nhận test.
3. Click Send Test Email.
4. Backend validate email.
5. Gửi test email.
6. Ghi email log.
7. Hiển thị success/error.

Business rules:

* Test email chỉ admin được gửi.
* Có rate limit để tránh spam.
* Không gửi test email nếu mail config thiếu.
* Error message thân thiện, không lộ password SMTP.

---

## 14. Order Created Email

### 14.1. Trigger

Gửi khi order tạo thành công ở Task 19.

Recipient:

* Customer email snapshot trong order.
* Guest email snapshot trong order.

Subject gợi ý:

`Your order {order_number} has been placed`

Nội dung chính:

* Cảm ơn customer.
* Order number.
* Order date.
* Order items.
* Total amount.
* Payment method.
* Payment status.
* Shipping address.
* Link xem order nếu có.

### 14.2. COD Specific Message

Nếu payment method là COD:

* Hiển thị hướng dẫn thanh toán khi nhận hàng.
* Payment status là unpaid/pending.
* Không nói đã thanh toán.

---

## 15. Admin New Order Email

### 15.1. Trigger

Gửi khi order tạo thành công.

Recipient:

* Admin notification emails.

Subject gợi ý:

`New order received: {order_number}`

Nội dung:

* Order number.
* Customer name.
* Customer email/phone.
* Grand total.
* Payment method.
* Payment status.
* Link admin order detail.

Business rules:

* Chỉ gửi nếu admin order email enabled.
* Nếu nhiều admin emails, gửi từng email hoặc cùng email tùy implementation.
* Không gửi nếu danh sách admin email trống.

---

## 16. Payment Success Email

### 16.1. Trigger

Gửi khi online payment được verify thành công.

Recipient:

* Customer/guest order email snapshot.

Subject gợi ý:

`Payment received for order {order_number}`

Nội dung:

* Order number.
* Payment amount.
* Payment method.
* Transaction number nếu có.
* Paid at.
* Link order success/detail nếu có.

Business rules:

* Chỉ gửi sau khi payment status đã update thành paid.
* Không gửi trùng khi IPN duplicate.
* Không gửi nếu payment đã paid trước đó và không có status change mới.

---

## 17. Payment Failed / Cancelled Email

### 17.1. Trigger

Gửi khi online payment failed/cancelled nếu setting enabled.

Recipient:

* Customer/guest order email snapshot.

Subject gợi ý:

`Payment was not completed for order {order_number}`

Nội dung:

* Order number.
* Payment status.
* Reason nếu có.
* Retry payment link nếu có.
* Support contact.

Business rules:

* Không expose raw gateway error quá kỹ thuật.
* Không gửi quá nhiều email nếu customer retry nhiều lần trong thời gian ngắn, trừ khi business yêu cầu.
* Payment failed email có thể disabled by default nếu sợ spam.

---

## 18. Order Status Updated Email

### 18.1. Trigger

Gửi khi admin cập nhật order status ở Task 20.

Recipient:

* Customer/guest order email snapshot.

Subject gợi ý:

`Order {order_number} status updated`

Nội dung:

* Order number.
* Old status.
* New status.
* Admin note public nếu có.
* Link xem order nếu có.

Business rules:

* Chỉ gửi khi status thật sự thay đổi.
* Không gửi internal note cho customer.
* Không gửi nếu setting disabled.
* Cancelled nên dùng template riêng nếu có.

---

## 19. Order Cancelled Email

### 19.1. Trigger

Gửi khi admin hủy order.

Recipient:

* Customer/guest order email snapshot.

Subject gợi ý:

`Order {order_number} has been cancelled`

Nội dung:

* Order number.
* Cancel reason nếu public được phép.
* Payment status.
* Refund note nếu có, nhưng refund nâng cao không thuộc Task 30.
* Support contact.

Business rules:

* Không gửi internal cancel reason nếu reason chỉ dành cho admin.
* Nếu order paid, chỉ thông báo hủy, không tự nói refund thành công nếu refund chưa xử lý.
* Không gửi trùng.

---

## 20. Email Formatting Rules

Email cần chuyên nghiệp và dễ đọc.

Yêu cầu:

* Header có store name/logo nếu có.
* Body rõ ràng.
* Table order items dễ đọc.
* Grand total nổi bật.
* Button/link rõ.
* Footer có support contact.
* Responsive email ở mức cơ bản.
* Không phụ thuộc CSS phức tạp khó render trong email client.
* Không dùng JavaScript trong email.
* Không dùng external asset không cần thiết.
* Image/logo dùng absolute URL.

---

## 21. Security Requirements

Yêu cầu bảo mật:

* Không gửi password.
* Không gửi payment secret.
* Không gửi full gateway payload.
* Không gửi admin internal note cho customer.
* Không expose admin-only URL cho customer nếu không có quyền.
* Guest order link nếu có phải dùng token khó đoán.
* Email log không chứa secret.
* SMTP password không hiển thị full trong UI.
* Test email route có admin middleware.
* Email preview nếu có phải bảo vệ bằng admin middleware.
* Không render raw HTML từ user input trong email nếu chưa sanitize.

---

## 22. Error Handling

| Scenario                | Expected Result                             |
| ----------------------- | ------------------------------------------- |
| SMTP config missing     | Log failed, show admin warning              |
| Email send failed       | Order/payment không rollback                |
| Invalid recipient email | Skip and log failed                         |
| Queue worker stopped    | Email pending/failed, không ảnh hưởng order |
| Template missing        | Log error                                   |
| Translation missing     | Fallback default language                   |
| Duplicate payment IPN   | Không gửi trùng payment success email       |
| Admin email list empty  | Không gửi admin alert, log warning nếu cần  |
| Test email failed       | Show safe error message                     |

---

## 23. Production Mail Checklist

Trước production cần kiểm tra:

* [ ] MAIL_MAILER đúng.
* [ ] MAIL_HOST đúng.
* [ ] MAIL_PORT đúng.
* [ ] MAIL_USERNAME đúng.
* [ ] MAIL_PASSWORD đúng.
* [ ] MAIL_ENCRYPTION đúng.
* [ ] MAIL_FROM_ADDRESS đúng domain.
* [ ] MAIL_FROM_NAME đúng.
* [ ] SPF/DKIM/DMARC nếu dùng domain riêng.
* [ ] Test email gửi thành công.
* [ ] Queue worker chạy nếu dùng queue.
* [ ] Email log không chứa secret.
* [ ] Order created email hoạt động.
* [ ] Payment success email hoạt động.
* [ ] Admin new order email hoạt động.

---

## 24. Routes

### 24.1. Admin Routes

Routes đề xuất:

| Method | URL                          | Name                        | Purpose                   |
| ------ | ---------------------------- | --------------------------- | ------------------------- |
| GET    | /admin/settings/email        | admin.settings.email.edit   | Email settings            |
| PATCH  | /admin/settings/email        | admin.settings.email.update | Update email settings     |
| POST   | /admin/settings/email/test   | admin.settings.email.test   | Send test email           |
| GET    | /admin/email-logs            | admin.email-logs.index      | Optional email log list   |
| GET    | /admin/email-logs/{emailLog} | admin.email-logs.show       | Optional email log detail |

Business rules:

* Tất cả admin routes cần admin/auth middleware.
* Test email cần rate limit.
* Email logs không expose secret.

---

## 25. Database Design

### 25.1. email_logs Table

Khuyến nghị tạo bảng `email_logs`.

Fields:

| Field                  | Type              | Description                 |
| ---------------------- | ----------------- | --------------------------- |
| id                     | bigint            | Primary key                 |
| event                  | string            | Event name                  |
| order_id               | nullable bigint   | Related order               |
| payment_transaction_id | nullable bigint   | Related transaction         |
| recipient_email        | string            | Recipient                   |
| subject                | string            | Subject                     |
| status                 | string            | pending/sent/failed/skipped |
| error_message          | text nullable     | Error                       |
| sent_at                | datetime nullable | Sent time                   |
| created_at             | timestamp         | Created                     |
| updated_at             | timestamp         | Updated                     |

### 25.2. orders Table Optional Fields

Optional fields nếu muốn idempotency đơn giản:

| Field                            | Description                     |
| -------------------------------- | ------------------------------- |
| order_confirmation_email_sent_at | Customer order email sent time  |
| admin_new_order_email_sent_at    | Admin alert sent time           |
| payment_success_email_sent_at    | Payment success email sent time |
| cancelled_email_sent_at          | Cancelled email sent time       |

MVP khuyến nghị dùng `email_logs` để linh hoạt hơn.

---

## 26. Services / Classes

Codex có thể tạo các service:

| Service                  | Responsibility                         |
| ------------------------ | -------------------------------------- |
| EmailNotificationService | Dispatch email theo event              |
| EmailLogService          | Ghi log sent/failed                    |
| OrderEmailDataService    | Chuẩn bị dữ liệu order snapshot        |
| EmailSettingsService     | Lấy settings email                     |
| EmailTemplateService     | Optional template/translation handling |

Mailable/Notification classes đề xuất:

| Class Concept          | Purpose                     |
| ---------------------- | --------------------------- |
| OrderCreatedMail       | Customer order confirmation |
| AdminNewOrderMail      | Admin alert                 |
| PaymentSuccessMail     | Payment paid                |
| PaymentFailedMail      | Payment failed              |
| OrderStatusUpdatedMail | Status update               |
| OrderCancelledMail     | Cancelled order             |
| TestEmailMail          | Test email                  |

---

## 27. Testing Requirements

### 27.1. Local Testing

Local có thể dùng:

| Driver          | Purpose            |
| --------------- | ------------------ |
| log             | Ghi email vào log  |
| array           | Test automated     |
| mailpit/mailhog | UI test local      |
| mailtrap        | Sandbox email test |

### 27.2. Test Cases

| ID        | Scenario                           | Expected Result                       |
| --------- | ---------------------------------- | ------------------------------------- |
| EMAIL-001 | Admin mở email settings            | Thành công                            |
| EMAIL-002 | Admin update email settings        | Lưu đúng                              |
| EMAIL-003 | Admin send test email              | Email sent/logged                     |
| EMAIL-004 | Invalid test email                 | Validation error                      |
| EMAIL-005 | Order created COD                  | Customer order email sent             |
| EMAIL-006 | Order created                      | Admin new order email sent            |
| EMAIL-007 | Online payment success             | Payment success email sent            |
| EMAIL-008 | Payment failed                     | Payment failed email sent nếu enabled |
| EMAIL-009 | Admin update order status          | Status email sent                     |
| EMAIL-010 | Admin cancel order                 | Cancel email sent                     |
| EMAIL-011 | Duplicate IPN success              | Không gửi trùng                       |
| EMAIL-012 | Queue email fail                   | Order không rollback                  |
| EMAIL-013 | Missing translation                | Fallback default                      |
| EMAIL-014 | Guest order email                  | Gửi tới guest email snapshot          |
| EMAIL-015 | Customer email changed after order | Email vẫn dùng order snapshot         |
| EMAIL-016 | Email log                          | Log sent/failed đúng                  |
| EMAIL-017 | Customer không thấy internal note  | Pass                                  |
| EMAIL-018 | Email template responsive          | Hiển thị ổn                           |
| EMAIL-019 | Secrets not logged                 | Pass                                  |

---

## 28. Commands

Các lệnh cần chạy:

```bash id="t4jtsx"
php artisan migrate
php artisan route:list
php artisan test
npm run build
```

Nếu dùng queue:

```bash id="8gaeqr"
php artisan queue:work
php artisan queue:failed
php artisan queue:restart
```

Nếu đổi config mail:

```bash id="4i6svh"
php artisan optimize:clear
php artisan config:cache
```

---

## 29. Monitoring Requirements

Sau khi bật email notification, cần theo dõi:

* Email failed count.
* Queue failed jobs.
* SMTP errors.
* Admin test email result.
* Payment success email duplicate.
* Order created email duplicate.
* Laravel logs.
* Mail provider bounce/complaint nếu có.

Task 29 đã có backup/logs/monitoring, Task 30 cần đảm bảo email errors có thể được ghi log.

---

## 30. Acceptance Criteria

Task này hoàn thành khi:

* [ ] Có email settings trong admin hoặc cấu hình rõ qua `.env`.
* [ ] Admin có thể gửi test email.
* [ ] Có email template xác nhận order.
* [ ] Có email template admin new order.
* [ ] Có email template payment success.
* [ ] Có email template payment failed/cancelled nếu enabled.
* [ ] Có email template order status updated.
* [ ] Có email template order cancelled.
* [ ] Customer/guest nhận email khi order tạo thành công.
* [ ] Admin nhận email khi có order mới nếu enabled.
* [ ] Online payment success gửi email sau khi payment được verify paid.
* [ ] Payment failed/cancelled gửi email nếu enabled.
* [ ] Admin update order status gửi email nếu enabled.
* [ ] Admin cancel order gửi email nếu enabled.
* [ ] Email dùng order snapshot, không tính lại giá từ product hiện tại.
* [ ] Email format currency theo order currency snapshot.
* [ ] Email hỗ trợ fallback language.
* [ ] Email không gửi trùng khi duplicate webhook/IPN.
* [ ] Email sending failure không rollback order/payment.
* [ ] Có email log hoặc cách kiểm tra email sent/failed.
* [ ] Queue được hỗ trợ nếu production dùng queue.
* [ ] Email không log password/API key/payment secret.
* [ ] Admin email routes có admin/auth middleware.
* [ ] Test email có validation và rate limit.
* [ ] Không implement marketing/newsletter.
* [ ] Không implement review reminder email.
* [ ] Không dùng Vue.js.

---

## 31. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-05-system-settings.md
* docs/tasks/task-06-language-management.md
* docs/tasks/task-18-payment-cod.md
* docs/tasks/task-19-order-creation.md
* docs/tasks/task-20-admin-order-management.md
* docs/tasks/task-24-online-payment.md
* docs/tasks/task-28-production-deployment.md
* docs/tasks/task-29-backup-logs-and-monitoring.md
* docs/tasks/task-30-email-notification.md

Sau đó implement Task 30: Email Notification theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 30.
* Không thêm marketing email/newsletter.
* Không implement review reminder email.
* Không đụng tới Review Product.
* Tạo email notification settings trong admin hoặc dùng settings hiện tại nếu phù hợp.
* Tạo chức năng gửi test email cho admin.
* Tạo email xác nhận đơn hàng cho customer/guest.
* Tạo email thông báo đơn hàng mới cho admin.
* Tạo email thông báo thanh toán thành công.
* Tạo email thông báo thanh toán thất bại/cancelled nếu setting enabled.
* Tạo email thông báo order status updated.
* Tạo email thông báo order cancelled.
* Email phải dùng order snapshot.
* Email phải format tiền theo order currency snapshot.
* Email phải hỗ trợ language/fallback language nếu dữ liệu language có sẵn.
* Email phải gửi qua queue nếu queue được cấu hình.
* Email sending failure không được rollback order/payment.
* Không gửi trùng email khi duplicate payment callback/webhook/IPN.
* Tạo email_logs hoặc cơ chế log sent/failed.
* Admin test email route phải có admin/auth middleware và rate limit.
* Không log SMTP password, API key, payment secret.
* Không expose secret trong admin UI.
* Dùng Laravel Blade email templates.
* Không dùng Vue.js.
* Sau khi làm xong, báo cáo:

  * File đã tạo/sửa.
  * Email events đã implement.
  * Settings đã thêm.
  * Queue/log behavior.
  * Lệnh cần chạy.
  * Cách test email local và production.
