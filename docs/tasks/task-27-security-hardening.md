# Task 27: Security Hardening

## 1. Overview

Task này dùng để rà soát và gia cố bảo mật cho hệ thống e-commerce trước khi đưa lên production.

Sau khi đã hoàn thành end-to-end testing ở Task 26, hệ thống đã có đủ các chức năng core như:

* Authentication.
* Admin management.
* Product/category/inventory.
* Cart/coupon/checkout.
* COD payment.
* Online payment.
* Order management.
* Dashboard/report.
* Banner management.

Task 27 tập trung vào việc làm cho hệ thống an toàn hơn:

* Bảo vệ môi trường production.
* Bảo vệ admin routes.
* Bảo vệ customer/order data.
* Bảo vệ file upload.
* Bảo vệ payment flow.
* Bảo vệ secrets/API keys.
* Bảo vệ session/cookie/CSRF.
* Kiểm tra rate limit.
* Kiểm tra logging.
* Kiểm tra permission.
* Kiểm tra route exposure.
* Kiểm tra error handling.
* Kiểm tra dependency security.

Task này không thêm feature mới cho business. Đây là task hardening và bug fix bảo mật.

Frontend/admin vẫn dùng:

* Laravel Blade.
* Tailwind CSS.
* Alpine.js nếu cần.
* Fetch API nếu cần.
* Không dùng Vue.js.

---

## 2. Objectives

Sau khi hoàn thành Task 27, hệ thống cần đạt:

* Production environment được cấu hình an toàn.
* `.env` không bị commit lên Git.
* `APP_DEBUG=false` ở production.
* Admin routes được bảo vệ đúng.
* Customer/guest không truy cập được admin.
* Customer không xem được order của user khác.
* Guest order token đủ khó đoán.
* CSRF protection hoạt động.
* Session/cookie config an toàn.
* File upload được validate đúng.
* Không upload được file nguy hiểm.
* Payment secrets không bị expose.
* VNPAY/online payment signature verify đúng.
* Payment webhook/IPN idempotent.
* Không mark paid nếu amount/currency/signature sai.
* Login/admin/payment/coupon/order submit có rate limit phù hợp.
* Không expose lỗi Laravel/SQL ra public.
* Logs không ghi secret/password/payment secret.
* Route list không expose route nguy hiểm.
* Dependency audit được chạy.
* Không còn bug critical/high về bảo mật trong phạm vi task.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong Task 27:

* Production environment security.
* `.env` and secrets audit.
* Laravel debug/error config.
* Admin route protection.
* Customer/order authorization.
* Guest token security.
* CSRF/session/cookie security.
* Login rate limit.
* Coupon apply rate limit.
* Checkout/place order rate limit.
* Payment callback/webhook validation.
* VNPAY signature validation.
* Payment amount/currency validation.
* File upload validation.
* Storage/public file exposure check.
* Input validation and mass assignment check.
* XSS basic check.
* Security headers basic setup.
* CORS check.
* Error page check.
* Logging sensitive data check.
* Composer/npm dependency audit.
* Route audit.
* Basic security tests.

### 3.2. Out of Scope

Không làm trong Task 27:

* Không implement feature mới.
* Không redesign UI.
* Không implement Production Deployment chi tiết.
* Không implement Backup/Monitoring chi tiết.
* Không implement Email Notification.
* Không implement Customer Account nâng cao.
* Không implement Shipping Management.
* Không implement Refund/Return.
* Không implement advanced WAF.
* Không implement penetration testing chuyên sâu.
* Không implement 2FA nếu chưa có yêu cầu riêng.
* Không thay đổi logic business lớn.
* Không dùng Vue.js.

---

## 4. Dependencies

Task này phụ thuộc các task đã hoàn thành:

| Task    | Module                         |
| ------- | ------------------------------ |
| Task 03 | Authentication                 |
| Task 04 | Admin Layout                   |
| Task 05 | System Settings                |
| Task 11 | Product Image Upload           |
| Task 15 | Cart                           |
| Task 16 | Coupon                         |
| Task 17 | Checkout                       |
| Task 18 | Payment COD                    |
| Task 19 | Order Creation                 |
| Task 20 | Admin Order Management         |
| Task 24 | Online Payment                 |
| Task 26 | End-to-End Testing and Bug Fix |

Task này chuẩn bị cho:

| Task    | Purpose                     |
| ------- | --------------------------- |
| Task 28 | Production Deployment       |
| Task 29 | Backup, Logs and Monitoring |

---

## 5. Production Environment Security

Production environment phải an toàn.

### 5.1. Required Production Settings

Kiểm tra `.env` production:

| Key                   | Required Value          |
| --------------------- | ----------------------- |
| APP_ENV               | production              |
| APP_DEBUG             | false                   |
| APP_KEY               | Có giá trị hợp lệ       |
| APP_URL               | Domain thật, dùng https |
| LOG_LEVEL             | error hoặc warning      |
| SESSION_SECURE_COOKIE | true nếu dùng HTTPS     |
| SESSION_HTTP_ONLY     | true                    |
| SESSION_SAME_SITE     | lax hoặc strict         |
| DB_USERNAME           | Không dùng root         |
| DB_PASSWORD           | Strong password         |

Business rules:

* Không dùng `APP_DEBUG=true` trên production.
* Không dùng database root user cho app.
* Không dùng password rỗng.
* Không expose `.env`.
* Không commit `.env`.

---

## 6. Git Secret Audit

Cần kiểm tra Git để đảm bảo không commit secret.

Kiểm tra:

| Item                   | Requirement            |
| ---------------------- | ---------------------- |
| .env                   | Không tracked          |
| API keys               | Không nằm trong source |
| VNPAY secret           | Không commit           |
| DB password            | Không commit           |
| Storage private key    | Không commit           |
| Log file               | Không commit           |
| Payment payload secret | Không commit           |

Commands gợi ý:

```bash id="tq7ev0"
git status
git ls-files | grep .env
```

Nếu `.env` bị tracked, xử lý:

```bash id="u6tqht"
git rm --cached .env
```

`.gitignore` cần có:

```txt id="723hnq"
.env
/vendor
/node_modules
/storage/logs
/storage/framework/cache
/storage/framework/sessions
/storage/framework/views
/bootstrap/cache/*.php
```

---

## 7. Authentication Security

### 7.1. Login Security

Yêu cầu:

* Login có rate limit.
* Sai password nhiều lần bị throttle.
* Password không log ra file.
* Password được hash bằng Laravel default hasher.
* Logout invalidate session.
* Session regenerate sau login.

### 7.2. Admin Auth

Admin routes phải yêu cầu:

* User đã login.
* User có role admin hoặc staff được phép.
* Customer thường không vào được admin.
* Guest redirect login hoặc bị forbidden.

### 7.3. Test Cases

| ID          | Scenario                           | Expected Result |
| ----------- | ---------------------------------- | --------------- |
| AUTHSEC-001 | Guest vào /admin                   | Redirect login  |
| AUTHSEC-002 | Customer vào /admin                | Forbidden       |
| AUTHSEC-003 | Login sai nhiều lần                | Bị throttle     |
| AUTHSEC-004 | Logout xong back admin             | Không vào được  |
| AUTHSEC-005 | Password không xuất hiện trong log | Pass            |

---

## 8. Authorization Security

Hệ thống cần kiểm tra quyền truy cập dữ liệu.

### 8.1. Admin Routes

Các route admin cần middleware:

* `/admin`
* `/admin/products`
* `/admin/categories`
* `/admin/orders`
* `/admin/reports`
* `/admin/settings`
* `/admin/banners`
* `/admin/payment-methods` nếu có

Business rules:

* Không route admin nào public.
* AJAX admin routes cũng phải có middleware.
* Delete/update admin routes phải bảo vệ bằng auth/admin middleware.

### 8.2. Customer / Guest Order Access

Business rules:

* Customer chỉ xem order của chính họ.
* Guest chỉ xem order bằng guest token hợp lệ.
* Guest token phải khó đoán.
* Không cho xem order bằng ID tuần tự nếu không có quyền.
* Không expose order detail của user khác.

### 8.3. Test Cases

| ID        | Scenario                        | Expected Result            |
| --------- | ------------------------------- | -------------------------- |
| AUTHZ-001 | Customer A xem order Customer B | Forbidden                  |
| AUTHZ-002 | Guest token sai                 | Not found/forbidden        |
| AUTHZ-003 | Guest dùng ID order trực tiếp   | Bị chặn nếu không có token |
| AUTHZ-004 | Admin xem order                 | Thành công                 |
| AUTHZ-005 | Customer gọi admin AJAX update  | Forbidden                  |

---

## 9. CSRF / Session / Cookie Security

### 9.1. CSRF

Các route POST/PATCH/DELETE phải có CSRF:

* Login/logout.
* Admin create/update/delete.
* Cart add/update/remove.
* Coupon apply/remove.
* Checkout update.
* Place order.
* COD select.
* Online payment create/retry.
* Admin order status update.

Webhook/IPN payment có thể exclude CSRF nhưng phải verify signature.

### 9.2. Session/Cookie

Yêu cầu:

* Session cookie httpOnly.
* Secure cookie bật trên HTTPS.
* SameSite phù hợp.
* Regenerate session sau login.
* Không lưu thông tin nhạy cảm không cần thiết trong session.

### 9.3. Test Cases

| ID       | Scenario                         | Expected Result                                 |
| -------- | -------------------------------- | ----------------------------------------------- |
| CSRF-001 | POST thiếu CSRF tới admin update | Bị chặn                                         |
| CSRF-002 | POST thiếu CSRF tới cart         | Bị chặn                                         |
| CSRF-003 | Payment webhook không dùng CSRF  | Không bị CSRF chặn, nhưng phải verify signature |
| SESS-001 | Session regenerate sau login     | Pass                                            |
| SESS-002 | Cookie secure trên HTTPS         | Pass                                            |

---

## 10. Rate Limiting

Cần thêm hoặc kiểm tra rate limit cho các điểm nhạy cảm.

### 10.1. Required Rate Limits

| Area                  | Requirement                                           |
| --------------------- | ----------------------------------------------------- |
| Login                 | Throttle login attempts                               |
| Password reset nếu có | Throttle                                              |
| Coupon apply          | Throttle theo IP/session/user                         |
| Checkout place order  | Throttle tránh spam                                   |
| Payment retry         | Throttle                                              |
| Payment callback/IPN  | Không throttle gây lỗi gateway, nhưng cần idempotency |
| Search nếu nặng       | Optional throttle                                     |
| Review Product        | Không xử lý trong task này                            |

### 10.2. Business Rules

* Không để user spam apply coupon.
* Không để user spam place order.
* Không để user spam payment retry tạo transaction vô hạn.
* Rate limit phải trả message rõ ràng.

---

## 11. File Upload Security

Áp dụng cho:

* Product images.
* Variant images.
* Banner images.
* Logo/favicon nếu có.

### 11.1. Upload Validation

Yêu cầu:

| Check                | Requirement                              |
| -------------------- | ---------------------------------------- |
| File type            | jpg, jpeg, png, webp nếu cho phép        |
| MIME validation      | Có                                       |
| File size limit      | Có                                       |
| Extension validation | Có                                       |
| Dangerous file       | Bị chặn                                  |
| SVG                  | Chỉ cho phép nếu sanitize hoặc không cho |
| PHP/JS/HTML upload   | Bị chặn                                  |
| Filename             | Không dùng filename gốc trực tiếp        |
| Storage path         | Không path traversal                     |

### 11.2. Public Storage

Business rules:

* Chỉ public file cần public.
* Không public `.env`, logs, private files.
* Upload path không cho execute PHP.
* Khi replace/delete image, không xóa nhầm file ngoài phạm vi storage.

### 11.3. Test Cases

| ID         | Scenario                            | Expected Result      |
| ---------- | ----------------------------------- | -------------------- |
| UPLOAD-001 | Upload JPG hợp lệ                   | Thành công           |
| UPLOAD-002 | Upload PHP file đổi đuôi            | Bị chặn              |
| UPLOAD-003 | Upload file quá lớn                 | Bị chặn              |
| UPLOAD-004 | Upload SVG nếu không support        | Bị chặn              |
| UPLOAD-005 | Filename có ký tự lạ/path traversal | Không ảnh hưởng path |
| UPLOAD-006 | Public không truy cập được .env/log | Pass                 |

---

## 12. Payment Security

Payment là khu vực cực kỳ quan trọng.

### 12.1. Online Payment General Rules

Yêu cầu:

* Không tin dữ liệu payment từ frontend.
* Không mark paid từ frontend request.
* Amount lấy từ order/payment transaction trong backend.
* Currency phải khớp.
* Transaction phải thuộc order.
* Order chưa paid mới xử lý paid.
* Callback/webhook phải idempotent.
* Không xử lý duplicate callback nhiều lần.

### 12.2. VNPAY Security Rules

Nếu dùng VNPAY:

* Verify `vnp_SecureHash`.
* Loại bỏ `vnp_SecureHash` và `vnp_SecureHashType` trước khi ký lại.
* Sort params đúng theo key.
* Dùng `vnp_HashSecret` từ config/secret storage.
* Kiểm tra `vnp_TxnRef` tồn tại.
* Kiểm tra amount khớp.
* Kiểm tra currency VND nếu applicable.
* Chỉ mark paid khi:

  * checksum hợp lệ.
  * amount hợp lệ.
  * transaction hợp lệ.
  * `vnp_ResponseCode = 00`.
  * `vnp_TransactionStatus = 00`.
* Return URL chỉ hiển thị kết quả, không là source of truth chính nếu có IPN.
* IPN phải trả response đúng chuẩn.
* Duplicate IPN không update trùng.

### 12.3. COD Security Rules

COD không có gateway nhưng vẫn cần:

* Không cho customer tự set payment paid.
* Admin mark paid cần quyền admin.
* Không mark paid order cancelled.
* Payment status change cần history/note nếu có.

### 12.4. Payment Secrets

Yêu cầu:

* Không log `VNPAY_HASH_SECRET`.
* Không hiển thị full secret trong admin UI.
* Không trả secret trong JSON.
* Không commit secret.
* Nếu lưu DB, phải encrypt hoặc bảo vệ.

### 12.5. Test Cases

| ID         | Scenario                      | Expected Result                  |
| ---------- | ----------------------------- | -------------------------------- |
| PAYSEC-001 | VNPAY valid signature success | Mark paid                        |
| PAYSEC-002 | VNPAY invalid signature       | Không mark paid                  |
| PAYSEC-003 | VNPAY amount mismatch         | Không mark paid                  |
| PAYSEC-004 | Duplicate IPN                 | Không xử lý trùng                |
| PAYSEC-005 | Return URL fake paid params   | Không mark paid nếu không verify |
| PAYSEC-006 | Customer gọi API mark paid    | Bị chặn                          |
| PAYSEC-007 | Secret không có trong log     | Pass                             |

---

## 13. Input Validation and Mass Assignment

### 13.1. Validation

Kiểm tra các form quan trọng:

* Admin settings.
* Language/currency/tax.
* Category/product.
* Product image.
* Inventory adjustment.
* Coupon.
* Checkout address.
* Payment settings.
* Order status update.
* Banner.
* Report filters.

Yêu cầu:

* Required fields có validation.
* Numeric fields validate min/max.
* Date fields validate đúng.
* Status fields chỉ nhận allowed values.
* URL fields không cho `javascript:` URL.
* Email/phone validate phù hợp.
* Quantity không âm.
* Price không âm.
* Rate không âm hoặc vượt rule.

### 13.2. Mass Assignment

Yêu cầu:

* Không để user update field nhạy cảm ngoài ý muốn.
* Public request không update `is_admin`, `payment_status`, `order_status`, `paid_at`.
* Form request/controller chỉ lấy fields được phép.
* Model fillable/guarded hợp lý.

---

## 14. XSS and HTML Safety

Các field có thể chứa text từ admin/user:

* Product name.
* Product description.
* Category name.
* Banner title/description.
* Review Product bị loại trừ task này.
* Customer note.
* Address.
* Internal note.

Yêu cầu:

* Blade output mặc định escape.
* Chỉ dùng HTML raw nếu đã sanitize hoặc tin cậy.
* Không render user input bằng `{!! !!}` nếu không cần.
* URL phải safe.
* Banner link không cho `javascript:`.

Test:

| ID      | Scenario                         | Expected Result |
| ------- | -------------------------------- | --------------- |
| XSS-001 | Nhập `<script>` vào product name | Không execute   |
| XSS-002 | Nhập script vào checkout note    | Không execute   |
| XSS-003 | Banner link `javascript:`        | Bị chặn         |
| XSS-004 | Internal note script             | Không execute   |

---

## 15. Security Headers

Cần kiểm tra hoặc bổ sung security headers cơ bản.

Khuyến nghị:

| Header                    | Purpose                       |
| ------------------------- | ----------------------------- |
| X-Frame-Options           | Chống clickjacking cơ bản     |
| X-Content-Type-Options    | Chống MIME sniffing           |
| Referrer-Policy           | Giảm lộ referrer              |
| Content-Security-Policy   | Optional, nếu không phá asset |
| Strict-Transport-Security | Bật khi production dùng HTTPS |
| Permissions-Policy        | Optional                      |

Business rules:

* Không cấu hình CSP quá chặt làm hỏng Tailwind/Alpine/Vite assets.
* HSTS chỉ bật khi chắc chắn domain dùng HTTPS ổn định.
* Có thể implement middleware security headers cơ bản.

---

## 16. CORS Security

Nếu hệ thống chủ yếu Blade server-rendered:

* CORS nên hạn chế.
* Không dùng `*` cho credentials.
* API nếu có chỉ cho domain cần thiết.
* Payment webhook không phụ thuộc CORS.

Yêu cầu:

* Không mở CORS quá rộng nếu không cần.
* Không expose admin API cho origin lạ.

---

## 17. Error Handling Security

Production không được lộ lỗi kỹ thuật.

Yêu cầu:

* `APP_DEBUG=false`.
* 500 page thân thiện.
* 404 page thân thiện.
* Validation error rõ nhưng không lộ SQL.
* Payment error không lộ secret/payload nhạy cảm.
* Admin error có log nhưng UI không lộ stack trace.
* Public không thấy exception stack.

Test:

| ID      | Scenario             | Expected Result                 |
| ------- | -------------------- | ------------------------------- |
| ERR-001 | Route không tồn tại  | 404 page                        |
| ERR-002 | Server error giả lập | Không lộ stack trace production |
| ERR-003 | Validation error     | Message thân thiện              |
| ERR-004 | Payment error        | Không lộ secret                 |

---

## 18. Logging Security

Kiểm tra Laravel logs.

Không được log:

* Password.
* Password confirmation.
* VNPAY hash secret.
* API secret.
* Full payment secret.
* DB password.
* Session cookie.
* CSRF token.
* Full card data nếu sau này có.

Có thể log:

* Order number.
* Transaction number.
* Payment status.
* Gateway response đã loại secret.
* Error message đã sanitize.

Yêu cầu:

* Payment webhook log không chứa secret.
* Request payload log cần mask sensitive keys.
* Production log level phù hợp.

---

## 19. Database Security

Yêu cầu:

* App DB user không dùng root.
* DB password mạnh.
* DB user chỉ có quyền trên database của app.
* Không expose database port public nếu không cần.
* Migration không chứa data secret.
* Backup task sẽ làm sau, nhưng không để backup SQL trong public web root.

---

## 20. Route Audit

Chạy:

```bash id="yp6yuk"
php artisan route:list
```

Kiểm tra:

| Check                | Requirement                       |
| -------------------- | --------------------------------- |
| Admin routes         | Có middleware auth/admin          |
| Public routes        | Không expose admin action         |
| Payment webhook      | Không CSRF nhưng verify signature |
| Delete/update routes | Không GET                         |
| Debug/test routes    | Không còn trên production         |
| Temporary routes     | Xóa hoặc bảo vệ                   |
| Storage routes       | Không expose private file         |

---

## 21. Dependency Security

Chạy audit cơ bản:

```bash id="s2d2fq"
composer audit
npm audit
```

Business rules:

* Ghi lại vulnerability nếu có.
* Update package nếu an toàn.
* Không update major package gây vỡ hệ thống nếu chưa cần.
* Critical/high vulnerability cần xử lý trước production.
* Nếu npm audit có warning từ dev dependency không ảnh hưởng production, ghi chú rõ.

---

## 22. UI Security Check

Kiểm tra UI để tránh thao tác nguy hiểm:

* Delete dùng custom confirmation modal.
* Cancel order dùng confirmation modal.
* Mark paid dùng confirmation modal nếu cần.
* Danger action không đặt sát action chính.
* Error message không lộ kỹ thuật.
* Admin secret fields masked.
* Payment settings không hiển thị full secret.

---

## 23. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type             | Description                                 |
| ---------------- | ------------------------------------------- |
| Middleware       | Security headers middleware nếu cần         |
| Middleware       | Rate limit config nếu cần                   |
| Requests         | Bổ sung validation nếu thiếu                |
| Policies/Gates   | Order/customer/admin access nếu cần         |
| Controllers      | Fix authorization/validation bug            |
| Config           | Session/cookie/security config nếu cần      |
| Payment Services | Hardening VNPAY/online payment verification |
| Upload Services  | Hardening upload validation                 |
| Views            | Error pages, masked secret UI nếu cần       |
| Tests            | Security feature tests                      |
| Docs             | Update basic design security section        |

---

## 24. Commands

Chạy các lệnh:

```bash id="rztsn0"
php artisan optimize:clear
php artisan route:list
php artisan test
composer audit
npm audit
npm run build
```

Nếu môi trường không có `composer audit` hoặc `npm audit`, ghi rõ trong report.

---

## 25. Test Cases Summary

| Area          | Required                              |
| ------------- | ------------------------------------- |
| Auth          | Login throttle, admin protection      |
| Authorization | Customer/order ownership              |
| CSRF          | POST/PATCH/DELETE protected           |
| Payment       | Signature/amount/currency/idempotency |
| Upload        | Dangerous file blocked                |
| XSS           | Script input escaped                  |
| Secrets       | Not exposed/logged                    |
| Routes        | Admin routes protected                |
| Error         | No stack trace in production          |
| Dependencies  | Audit run                             |
| UI            | Danger action confirmation            |

---

## 26. Acceptance Criteria

Task này hoàn thành khi:

* [ ] `.env` không bị tracked bởi Git.
* [ ] `.env.example` không chứa secret thật.
* [ ] Production config yêu cầu `APP_DEBUG=false`.
* [ ] Admin routes có auth/admin middleware.
* [ ] Customer/guest không truy cập được admin.
* [ ] Customer không xem được order của user khác.
* [ ] Guest order token được validate đúng.
* [ ] CSRF hoạt động với form/action quan trọng.
* [ ] Payment webhook/IPN verify signature.
* [ ] VNPAY callback/IPN verify secure hash đúng.
* [ ] Payment không mark paid nếu signature sai.
* [ ] Payment không mark paid nếu amount/currency mismatch.
* [ ] Payment callback/webhook idempotent.
* [ ] Payment secrets không bị log/expose.
* [ ] File upload chặn file nguy hiểm.
* [ ] File upload validate MIME/extension/size.
* [ ] XSS cơ bản được chặn bằng escaped output.
* [ ] Banner/product/customer note không execute script.
* [ ] Rate limit login hoạt động.
* [ ] Rate limit coupon/order/payment retry nếu cần.
* [ ] Error page production không lộ stack trace.
* [ ] Laravel logs không chứa secret/password.
* [ ] Route audit không còn route debug nguy hiểm.
* [ ] `composer audit` đã chạy hoặc ghi chú nếu không chạy được.
* [ ] `npm audit` đã chạy hoặc ghi chú nếu không chạy được.
* [ ] `php artisan test` chạy không có lỗi critical.
* [ ] `npm run build` chạy thành công.
* [ ] Không thêm feature mới.
* [ ] Không dùng Vue.js.

---

## 27. Bug Fix Reporting Format

Sau khi hoàn thành, báo cáo theo format:

| Section               | Content                     |
| --------------------- | --------------------------- |
| Commands Run          | Lệnh đã chạy                |
| Security Issues Found | Lỗi bảo mật phát hiện       |
| Fixes Applied         | File đã sửa và nội dung sửa |
| Remaining Risks       | Rủi ro còn lại nếu có       |
| Manual Tests          | Test thủ công đã chạy       |
| Automated Tests       | Test tự động đã chạy        |
| Production Notes      | Ghi chú khi deploy          |

---

## 28. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-03-authentication.md
* docs/tasks/task-04-admin-layout.md
* docs/tasks/task-05-system-settings.md
* docs/tasks/task-11-product-image-upload.md
* docs/tasks/task-15-cart.md
* docs/tasks/task-16-coupon.md
* docs/tasks/task-17-checkout-with-tax-currency-snapshot.md
* docs/tasks/task-18-payment-cod.md
* docs/tasks/task-19-order-creation.md
* docs/tasks/task-20-admin-order-management.md
* docs/tasks/task-24-online-payment.md
* docs/tasks/task-26-end-to-end-testing-and-bug-fix.md
* docs/tasks/task-27-security-hardening.md

Sau đó implement Task 27: Security Hardening theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 27.
* Không thêm feature business mới.
* Không redesign toàn bộ UI.
* Kiểm tra `.env` không bị tracked.
* Kiểm tra `.env.example` không chứa secret thật.
* Đảm bảo production dùng APP_DEBUG=false.
* Đảm bảo admin routes được bảo vệ bởi auth/admin middleware.
* Đảm bảo customer/guest không truy cập admin.
* Đảm bảo customer không xem/sửa order của user khác.
* Đảm bảo guest order access dùng token hợp lệ.
* Kiểm tra CSRF cho các POST/PATCH/DELETE quan trọng.
* Payment webhook/IPN được exclude CSRF nếu cần nhưng bắt buộc verify signature.
* VNPAY/IPN phải verify secure hash.
* Không mark paid nếu signature sai.
* Không mark paid nếu amount/currency mismatch.
* Payment callback/webhook phải idempotent.
* Không log/expose payment secret.
* File upload phải validate type, MIME, extension, size.
* Không cho upload file nguy hiểm.
* Kiểm tra XSS cơ bản, không dùng raw HTML cho user input nếu không sanitize.
* Thêm hoặc kiểm tra rate limit cho login, coupon apply, place order, payment retry nếu phù hợp.
* Kiểm tra error handling không lộ stack trace ở production.
* Kiểm tra route list không còn route debug nguy hiểm.
* Chạy php artisan test, php artisan route:list, composer audit, npm audit, npm run build nếu môi trường cho phép.
* Không dùng Vue.js.
* Sau khi làm xong, báo cáo:

  * Lệnh đã chạy.
  * Lỗi bảo mật phát hiện.
  * File đã sửa.
  * Test đã chạy.
  * Rủi ro còn lại nếu có.
