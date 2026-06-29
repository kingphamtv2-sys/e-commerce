# Task 24: Online Payment

## 1. Overview

Task này dùng để xây dựng chức năng thanh toán online cho hệ thống e-commerce.

Sau các task trước, hệ thống đã có:

* Cart.
* Coupon.
* Checkout session.
* COD payment.
* Order creation.
* Admin order management.

Task 24 sẽ mở rộng hệ thống để hỗ trợ online payment.

Online payment cần hỗ trợ:

* Admin cấu hình payment gateway.
* Customer chọn phương thức thanh toán online.
* Tạo payment transaction.
* Redirect customer sang payment gateway nếu gateway yêu cầu.
* Nhận callback/return từ gateway.
* Nhận webhook/IPN từ gateway nếu có.
* Verify payment response.
* Cập nhật order payment status.
* Cập nhật order status nếu phù hợp.
* Ghi payment history/log.
* Chống duplicate callback/webhook.
* Xử lý payment success, failed, cancelled, expired.

Task này nên thiết kế theo hướng **gateway abstraction**, để sau này có thể thêm nhiều cổng thanh toán như:

* VNPay.
* MoMo.
* PayPal.
* Stripe.
* Bank transfer online.
* Other payment providers.

MVP có thể implement một gateway cụ thể nếu project đã chọn, hoặc implement mock/sandbox gateway để test flow online payment.

Frontend sử dụng:

* Admin: Laravel Blade + Tailwind CSS + Alpine.js nếu cần.
* Public: Laravel Blade + Tailwind CSS + Alpine.js nếu cần.
* Không dùng Vue.js.

---

## 2. Objectives

Sau khi hoàn thành Task 24, hệ thống cần có:

* Admin có thể bật/tắt online payment.
* Admin có thể cấu hình gateway.
* Admin có thể cấu hình sandbox/live mode.
* Admin có thể cấu hình gateway credentials.
* Public checkout payment page hiển thị online payment nếu active.
* Customer có thể chọn online payment.
* Hệ thống có thể tạo order với payment status pending.
* Hệ thống có thể tạo payment transaction.
* Hệ thống có thể redirect customer tới payment gateway nếu cần.
* Hệ thống có return/callback URL.
* Hệ thống có webhook/IPN endpoint nếu gateway hỗ trợ.
* Hệ thống verify payment response ở backend.
* Payment success cập nhật order payment status thành paid.
* Payment failed/cancelled cập nhật status phù hợp.
* Không tạo duplicate payment transaction.
* Không mark paid nếu payment chưa verify.
* Admin có thể xem payment transaction trong order detail.
* Payment flow hoạt động cho guest và logged-in customer.
* Không dùng Vue.js.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong Task 24:

* Online payment settings trong admin.
* Payment gateway abstraction.
* Payment method online ở checkout payment page.
* Create online payment transaction.
* Payment redirect flow.
* Payment return/callback handling.
* Payment webhook/IPN handling nếu cần.
* Signature verification hoặc provider verification.
* Update order payment status.
* Update order payment record.
* Payment transaction logs.
* Payment retry nếu payment failed/pending.
* Payment success page.
* Payment failed page.
* Admin order detail hiển thị payment transaction.
* Idempotency cho callback/webhook.
* Security validation.

### 3.2. Out of Scope

Không làm trong Task 24:

* Không implement tất cả payment gateways cùng lúc.
* Không làm refund online nâng cao.
* Không làm partial refund.
* Không làm subscription/recurring payment.
* Không lưu card.
* Không PCI card processing trực tiếp.
* Không xử lý card data trên server nếu dùng gateway redirect.
* Không implement fraud detection nâng cao.
* Không implement payment installment.
* Không implement wallet nội bộ.
* Không làm report nâng cao ngoài payment report đã có.
* Không dùng Vue.js.

---

## 4. Dependencies

Task này phụ thuộc:

| Task    | Dependency                            |
| ------- | ------------------------------------- |
| Task 05 | System Settings                       |
| Task 17 | Checkout with Tax / Currency Snapshot |
| Task 18 | Payment COD                           |
| Task 19 | Order Creation                        |
| Task 20 | Admin Order Management                |
| Task 23 | Report                                |

Task này mở rộng:

| Area               | Extension                           |
| ------------------ | ----------------------------------- |
| Checkout Payment   | Thêm online payment method          |
| Order Creation     | Hỗ trợ order pending online payment |
| Admin Order Detail | Hiển thị transaction                |
| Payment Report     | Có thêm online payment data         |

---

## 5. User Roles

| Role     | Permission                                         |
| -------- | -------------------------------------------------- |
| Guest    | Có thể thanh toán online nếu checkout/order hợp lệ |
| Customer | Có thể thanh toán online nếu checkout/order hợp lệ |
| Admin    | Cấu hình và quản lý payment settings               |
| Staff    | Xem payment transaction nếu có quyền               |

Business rules:

* Public user chỉ thao tác payment của order/checkout thuộc về họ.
* Admin/staff chỉ truy cập admin payment routes nếu có quyền.
* Callback/webhook từ gateway phải được verify, không dùng auth thường.

---

## 6. Payment Flow Options

Có hai flow phổ biến.

## 6.1. Recommended Flow: Create Order Before Payment

Flow khuyến nghị cho Task 24:

1. Customer hoàn tất checkout.
2. Customer chọn online payment.
3. Customer click `Place Order`.
4. System tạo order với `payment_status = pending`.
5. System tạo payment transaction.
6. System redirect customer tới payment gateway.
7. Gateway xử lý thanh toán.
8. Gateway redirect customer về return URL.
9. Gateway gửi webhook/IPN nếu có.
10. System verify payment.
11. Nếu success, update payment_status = paid.
12. Nếu failed/cancelled, update payment_status = failed/cancelled.
13. Customer xem payment result page.

Ưu điểm:

* Có order number trước khi gửi gateway.
* Gateway reference rõ ràng.
* Dễ trace payment.
* Phù hợp với nhiều provider.

---

## 6.2. Alternative Flow: Payment Before Order

Không khuyến nghị trong MVP.

Vì:

* Khó quản lý stock/order reference.
* Payment success nhưng order chưa tạo dễ lỗi.
* Cần xử lý phức tạp hơn.

Task 24 nên dùng flow create order trước payment.

---

## 7. Online Payment Method Behavior

Online payment là một payment method trong checkout.

### 7.1. Payment Page

Payment page cần hiển thị:

| Method         | Description    |
| -------------- | -------------- |
| COD            | Từ Task 18     |
| Online Payment | Gateway online |

Nếu có nhiều gateway:

| Method | Description                      |
| ------ | -------------------------------- |
| VNPay  | Thanh toán qua VNPay             |
| MoMo   | Thanh toán qua MoMo              |
| PayPal | PayPal                           |
| Stripe | Card/Apple Pay/Google Pay nếu có |

MVP có thể:

* Hiển thị một option `Online Payment`.
* Gateway cụ thể chọn từ admin setting.
* Hoặc hiển thị nhiều gateway nếu đã implement.

### 7.2. Selected State

Khi customer chọn online payment:

* Payment card active rõ ràng.
* Hiển thị mô tả gateway.
* Hiển thị note redirect nếu cần.
* Button hiển thị `Pay Now` hoặc `Place Order and Pay`.

---

## 8. Admin Online Payment Settings

Admin cần cấu hình online payment.

URL đề xuất:

`/admin/settings/payment`

hoặc:

`/admin/payment-methods`

### 8.1. Common Settings

Fields đề xuất:

| Field            | Required | Description            |
| ---------------- | -------- | ---------------------- |
| Enabled          | Yes      | Bật/tắt online payment |
| Display Name     | Yes      | Tên hiển thị           |
| Description      | No       | Mô tả                  |
| Gateway          | Yes      | Gateway code           |
| Environment      | Yes      | sandbox/live           |
| Sort Order       | No       | Thứ tự hiển thị        |
| Min Order Amount | No       | Đơn tối thiểu          |
| Max Order Amount | No       | Đơn tối đa             |
| Status           | Yes      | active/inactive        |

### 8.2. Gateway Credentials

Credentials tùy gateway.

Fields generic:

| Field          | Description     |
| -------------- | --------------- |
| merchant_id    | Merchant ID     |
| client_id      | Client ID       |
| client_secret  | Client secret   |
| api_key        | API key         |
| secret_key     | Secret key      |
| webhook_secret | Webhook secret  |
| return_url     | Return URL      |
| webhook_url    | Webhook/IPN URL |

Business rules:

* Credentials không hiển thị public.
* Secret fields nên được masked trong UI.
* Secret fields không nên log ra file.
* Nếu để trống secret khi update, giữ secret cũ.
* Sandbox/live credentials cần tách biệt nếu có.

---

## 9. Database Design

## 9.1. payment_methods Table

Nếu Task 18 chưa tạo bảng `payment_methods`, Task 24 nên tạo hoặc dùng settings.

Khuyến nghị Task 24 dùng bảng `payment_methods` để dễ mở rộng.

Fields đề xuất:

| Field            | Type                    | Description                      |
| ---------------- | ----------------------- | -------------------------------- |
| id               | bigint                  | Primary key                      |
| code             | string                  | cod, online, vnpay, momo, stripe |
| name             | string                  | Tên hiển thị                     |
| description      | text nullable           | Mô tả                            |
| gateway_code     | string nullable         | Gateway provider                 |
| environment      | string                  | sandbox/live                     |
| config           | json nullable           | Public/non-secret config         |
| credentials      | encrypted json nullable | Secret credentials               |
| min_order_amount | decimal nullable        | Min amount                       |
| max_order_amount | decimal nullable        | Max amount                       |
| sort_order       | integer default 0       | Sort                             |
| status           | string                  | active/inactive                  |
| created_at       | timestamp               | Created time                     |
| updated_at       | timestamp               | Updated time                     |

Business rules:

* `code` unique.
* Secret credentials cần encrypt nếu có thể.
* Payment method inactive không hiển thị public.
* COD có thể migrate vào bảng này hoặc vẫn dùng settings cũ.
* Gateway provider không hard-code trong Blade.

---

## 9.2. payment_transactions Table

Tạo bảng `payment_transactions`.

Fields đề xuất:

| Field                  | Type              | Description                                           |
| ---------------------- | ----------------- | ----------------------------------------------------- |
| id                     | bigint            | Primary key                                           |
| order_id               | bigint            | Reference order                                       |
| order_payment_id       | bigint nullable   | Reference order_payments                              |
| checkout_session_id    | bigint nullable   | Checkout session nếu cần                              |
| user_id                | bigint nullable   | Customer                                              |
| transaction_number     | string            | Internal unique transaction number                    |
| gateway_code           | string            | Gateway provider                                      |
| payment_method_code    | string            | online/vnpay/momo/stripe                              |
| gateway_transaction_id | string nullable   | Transaction ID từ gateway                             |
| gateway_reference      | string nullable   | Reference/order info gửi gateway                      |
| status                 | string            | pending, processing, paid, failed, cancelled, expired |
| amount                 | decimal           | Amount                                                |
| currency_code          | string            | Currency                                              |
| request_payload        | json nullable     | Payload gửi gateway, không chứa secret                |
| response_payload       | json nullable     | Response từ gateway                                   |
| webhook_payload        | json nullable     | Webhook payload nếu có                                |
| failure_reason         | text nullable     | Lý do lỗi                                             |
| paid_at                | datetime nullable | Paid time                                             |
| expired_at             | datetime nullable | Expired time                                          |
| created_at             | timestamp         | Created time                                          |
| updated_at             | timestamp         | Updated time                                          |

Business rules:

* transaction_number unique.
* Không lưu secret trong payload.
* Gateway transaction ID có thể nullable lúc mới tạo.
* Mỗi payment attempt tạo một payment transaction.
* Order có thể có nhiều transaction nếu retry.
* Chỉ một transaction thành công được mark paid cho một order.

---

## 9.3. payment_webhook_logs Table

Tạo bảng `payment_webhook_logs` để debug và idempotency.

Fields đề xuất:

| Field            | Type                  | Description                       |
| ---------------- | --------------------- | --------------------------------- |
| id               | bigint                | Primary key                       |
| gateway_code     | string                | Gateway                           |
| event_id         | string nullable       | Event ID từ gateway               |
| transaction_id   | bigint nullable       | Payment transaction               |
| order_id         | bigint nullable       | Order                             |
| event_type       | string nullable       | payment.succeeded, payment.failed |
| payload          | json                  | Raw webhook payload               |
| headers          | json nullable         | Request headers nếu cần           |
| signature_valid  | boolean default false | Signature valid                   |
| processed        | boolean default false | Đã xử lý                          |
| processed_at     | datetime nullable     | Processed time                    |
| processing_error | text nullable         | Lỗi xử lý                         |
| created_at       | timestamp             | Created time                      |
| updated_at       | timestamp             | Updated time                      |

Business rules:

* Webhook phải idempotent.
* Nếu gateway có event_id, không xử lý trùng event_id.
* Luôn log webhook để debug.
* Không log secret.

---

## 9.4. orders Table Updates

Có thể cần thêm fields:

| Field                | Description            |
| -------------------- | ---------------------- |
| payment_expires_at   | Payment expiry time    |
| payment_completed_at | Payment completed time |

Nếu đã có paid_at/payment status ở orders/order_payments thì không cần thêm nhiều.

---

## 9.5. order_payments Table Updates

Task 19 đã có `order_payments`.

Task 24 cần đảm bảo có fields:

| Field               | Description                          |
| ------------------- | ------------------------------------ |
| payment_method_code | online/vnpay/momo/stripe             |
| payment_method_name | Snapshot name                        |
| payment_status      | unpaid/pending/paid/failed/cancelled |
| amount              | Payment amount                       |
| currency_code       | Currency                             |
| transaction_id      | Gateway transaction ID               |
| gateway_response    | JSON                                 |
| paid_at             | Paid time                            |

Nếu thiếu, cập nhật migration.

---

## 10. Payment Status Rules

Payment transaction statuses:

| Status     | Meaning                              |
| ---------- | ------------------------------------ |
| pending    | Transaction created, waiting payment |
| processing | Gateway processing                   |
| paid       | Payment successful                   |
| failed     | Payment failed                       |
| cancelled  | Customer cancelled                   |
| expired    | Payment expired                      |
| refunded   | Refunded sau này                     |

Order payment statuses:

| Status    | Meaning             |
| --------- | ------------------- |
| unpaid    | Chưa thanh toán     |
| pending   | Đang chờ thanh toán |
| paid      | Đã thanh toán       |
| failed    | Thanh toán thất bại |
| cancelled | Thanh toán bị hủy   |
| refunded  | Đã hoàn tiền        |

Business rules:

* Khi tạo online payment transaction: transaction status = pending.
* Order payment status = pending.
* Payment success: transaction status = paid, order payment status = paid.
* Payment failed: transaction status = failed, order payment status = failed hoặc pending nếu cho retry.
* Customer cancelled: transaction status = cancelled.
* Payment expired: transaction status = expired.
* Không set paid nếu chưa verify gateway response.

---

## 11. Order Status Rules For Online Payment

Khi order online payment mới được tạo:

| Case              | Order Status                        | Payment Status |
| ----------------- | ----------------------------------- | -------------- |
| Waiting payment   | pending                             | pending        |
| Payment paid      | pending hoặc confirmed              | paid           |
| Payment failed    | pending hoặc cancelled tùy business | failed         |
| Payment cancelled | pending/cancelled tùy business      | cancelled      |
| Payment expired   | cancelled hoặc pending expired      |                |

MVP recommendation:

* Khi order tạo và chờ payment: `order_status = pending`, `payment_status = pending`.
* Khi payment success: `payment_status = paid`, order_status vẫn `pending` để admin xác nhận.
* Không tự chuyển order_status sang completed.
* Nếu payment failed/cancelled: payment_status update, order_status vẫn pending hoặc cancelled theo setting.
* Admin sẽ xử lý order ở Task 20.

---

## 12. Gateway Abstraction

Cần thiết kế service abstraction để không phụ thuộc một gateway.

### 12.1. PaymentGatewayInterface

Gateway service cần hỗ trợ các hành vi:

| Method Concept   | Purpose                        |
| ---------------- | ------------------------------ |
| createPayment    | Tạo payment request            |
| verifyReturn     | Verify dữ liệu return/callback |
| handleWebhook    | Xử lý webhook/IPN              |
| queryTransaction | Optional kiểm tra trạng thái   |
| refund           | Optional out of scope          |

Không cần viết chi tiết code trong tài liệu.

### 12.2. Gateway Implementations

Các gateway implementation có thể có:

| Gateway | Class Concept        |
| ------- | -------------------- |
| mock    | MockPaymentGateway   |
| vnpay   | VnpayPaymentGateway  |
| momo    | MomoPaymentGateway   |
| stripe  | StripePaymentGateway |
| paypal  | PaypalPaymentGateway |

MVP có thể implement:

* Mock gateway để test flow.
* Một gateway thật nếu project đã chọn.

### 12.3. Gateway Selection

PaymentGatewayManager chịu trách nhiệm:

* Lấy payment method active.
* Resolve gateway implementation.
* Load config/credentials.
* Không expose secret ra view.

---

## 13. Online Payment Flow

## 13.1. Select Online Payment

Flow:

1. Customer ở payment page.
2. Customer chọn online payment.
3. Backend validate checkout session.
4. Backend save payment method snapshot.
5. Customer click `Place Order and Pay`.
6. Backend tạo order nếu chưa có.
7. Backend tạo payment transaction.
8. Backend gọi gateway create payment.
9. Gateway trả redirect_url hoặc payment data.
10. Customer được redirect sang gateway.

---

## 13.2. Payment Return Flow

Payment return là khi customer quay về từ gateway.

Flow:

1. Gateway redirect customer về return URL.
2. Backend nhận request.
3. Backend tìm transaction/order.
4. Backend verify signature/response.
5. Nếu success:

   * Update transaction paid.
   * Update order payment paid.
   * Set paid_at.
   * Redirect success page.
6. Nếu failed/cancelled:

   * Update transaction failed/cancelled.
   * Update order payment status.
   * Redirect failed page hoặc retry page.
7. Ghi log.

Business rules:

* Không trust query params nếu chưa verify.
* Return URL có thể không đủ tin cậy bằng webhook.
* Nếu gateway có webhook, webhook là source of truth hoặc cần sync logic rõ.

---

## 13.3. Webhook/IPN Flow

Webhook/IPN là server-to-server notification từ gateway.

Flow:

1. Gateway gửi webhook đến system.
2. Backend log webhook.
3. Backend verify signature.
4. Backend kiểm tra event_id idempotency.
5. Backend tìm transaction/order.
6. Backend update payment status nếu event hợp lệ.
7. Backend mark webhook processed.
8. Return response OK cho gateway.

Business rules:

* Webhook endpoint không dùng CSRF.
* Phải verify signature/secret.
* Không xử lý webhook không hợp lệ.
* Không mark paid nếu signature invalid.
* Webhook phải idempotent.
* Duplicate webhook không được tạo duplicate update.

---

## 14. Payment Retry

Nếu online payment failed/cancelled/expired, customer có thể retry.

Business rules:

* Retry tạo payment transaction mới.
* Không tạo order mới nếu order đã tồn tại và chưa paid.
* Không cho retry nếu order đã paid.
* Không cho retry nếu order cancelled/completed.
* Payment retry page hiển thị order summary và payment method.
* Admin vẫn thấy các payment attempts trong order detail.

---

## 15. Payment Expiry

Online payment transaction nên có expiry.

Rules:

* Payment transaction có expired_at.
* Nếu quá hạn mà chưa paid, status chuyển expired.
* Customer có thể retry nếu order vẫn hợp lệ.
* Không cần cron trong MVP nếu chưa có.
* Có thể check expiry khi customer return/retry.

---

## 16. Admin Payment Settings UI

Admin settings cần chuyên nghiệp.

Sections:

| Section     | Fields                              |
| ----------- | ----------------------------------- |
| General     | Enable online payment, display name |
| Gateway     | Gateway selection, sandbox/live     |
| Credentials | API keys/secrets                    |
| Limits      | Min/max order amount                |
| URLs        | Return URL, webhook URL             |
| Status      | Active/inactive                     |

UX requirements:

* Secret fields masked.
* Có help text.
* Có test mode badge.
* Có Save button.
* Validation errors rõ.
* Không hiển thị secret hiện tại dạng plain text.

---

## 17. Public Payment UI

Checkout payment page cần hiển thị online payment.

Payment card cần có:

| Element       | Description                    |
| ------------- | ------------------------------ |
| Radio/select  | Chọn online payment            |
| Display name  | Tên method                     |
| Description   | Mô tả                          |
| Gateway logo  | Optional                       |
| Sandbox badge | Không hiển thị public nếu live |
| Instruction   | Redirect message               |
| Pay button    | Place Order and Pay            |

Button text:

* `Pay Now`
* `Place Order and Pay`
* `Continue to Payment`

Loading state:

* `Redirecting to payment...`

---

## 18. Payment Result Pages

Task 24 cần có pages hoặc states:

### 18.1. Payment Success Page

Hiển thị:

* Payment successful.
* Order number.
* Paid amount.
* Payment method.
* Transaction reference.
* Link order success/order detail.
* Continue shopping.

### 18.2. Payment Failed Page

Hiển thị:

* Payment failed.
* Reason nếu có.
* Retry payment button.
* Change payment method button.
* Contact support nếu cần.

### 18.3. Payment Pending Page

Nếu gateway báo pending:

* Hiển thị pending message.
* Order number.
* Instruction.
* Refresh/check status button nếu có.

---

## 19. Admin Order Detail Extension

Task 20 order detail cần hiển thị thêm online payment transaction.

### 19.1. Payment Transactions Section

Hiển thị:

| Column                 | Description                   |
| ---------------------- | ----------------------------- |
| Transaction Number     | Internal transaction          |
| Gateway                | Gateway code                  |
| Gateway Transaction ID | Provider transaction          |
| Amount                 | Amount                        |
| Currency               | Currency                      |
| Status                 | pending/paid/failed           |
| Created At             | Time                          |
| Paid At                | Paid time                     |
| Action                 | View payload/details optional |

### 19.2. Payment Actions

Admin có thể:

* View transaction.
* Check status nếu gateway hỗ trợ.
* Mark as paid thủ công chỉ nếu business cho phép, nhưng cần hạn chế.
* Không refund trong Task 24 MVP.

---

## 20. Report Extension

Payment report từ Task 23 cần nhận dữ liệu online payment.

Task 24 cần đảm bảo:

* Online payment transaction được lưu.
* Payment method code rõ.
* Payment status rõ.
* Report có thể tính paid/failed/pending online payment.
* Không cần sửa report nâng cao nếu đã generic.

---

## 21. Validation Rules

### 21.1. Admin Settings Validation

| Field            | Rule                         |
| ---------------- | ---------------------------- |
| enabled          | Boolean                      |
| display_name     | Required if enabled          |
| gateway_code     | Required if enabled          |
| environment      | sandbox/live                 |
| credentials      | Required based on gateway    |
| min_order_amount | Nullable numeric min 0       |
| max_order_amount | Nullable numeric min 0       |
| max_order_amount | Greater than min if both set |
| status           | active/inactive              |

### 21.2. Public Payment Validation

| Rule                   | Description                   |
| ---------------------- | ----------------------------- |
| Checkout session valid | Active and owned              |
| Order valid            | If already created            |
| Payment method active  | Online payment enabled        |
| Amount valid           | Grand total > 0               |
| Currency supported     | Gateway supports currency     |
| Order not paid         | Cannot pay already paid order |
| Order not cancelled    | Cannot pay cancelled order    |
| Stock still valid      | Before order creation         |
| Payment limits valid   | Min/max amount                |

### 21.3. Callback/Webhook Validation

| Rule               | Description                    |
| ------------------ | ------------------------------ |
| Signature valid    | Required                       |
| Transaction exists | Required                       |
| Amount matches     | Gateway amount equals expected |
| Currency matches   | Currency matches               |
| Order matches      | Order reference matches        |
| Status valid       | Recognized status              |
| Idempotency        | Do not process duplicate event |

---

## 22. Security Requirements

Yêu cầu bảo mật:

* Không lưu card data.
* Không xử lý card trực tiếp nếu không cần.
* Secret credentials phải bảo vệ.
* Không log secret.
* Callback/webhook phải verify signature.
* Không mark paid từ frontend request.
* Không trust query params without verification.
* Amount/currency phải match transaction.
* Webhook endpoint không dùng CSRF nhưng phải verify signature.
* Public payment routes phải validate ownership.
* Transaction number/token phải khó đoán.
* Prevent duplicate processing.
* Use database transaction for status update.
* Không expose lỗi kỹ thuật ra UI.

---

## 23. Idempotency Rules

Online payment callback/webhook có thể gửi nhiều lần.

Business rules:

* Nếu transaction đã paid, không xử lý paid lần nữa.
* Nếu webhook event_id đã processed, bỏ qua.
* Nếu return và webhook cùng success, chỉ update một lần.
* Nếu failed sau paid, không được chuyển paid về failed.
* Nếu paid sau pending, update paid.
* Status transition phải an toàn.

Status priority suggestion:

| Current   | Incoming | Behavior                             |
| --------- | -------- | ------------------------------------ |
| pending   | paid     | Update paid                          |
| pending   | failed   | Update failed                        |
| failed    | paid     | Allow if gateway confirms paid       |
| paid      | failed   | Ignore or log conflict               |
| paid      | paid     | No-op                                |
| cancelled | paid     | Need business decision, log conflict |

---

## 24. Database Transaction Requirements

Khi update payment success:

* Lock transaction row nếu cần.
* Lock order/payment row nếu cần.
* Verify amount/currency.
* Update payment transaction.
* Update order payment.
* Update order payment_status.
* Set paid_at.
* Create payment history/note if available.
* Commit.

Nếu lỗi:

* Rollback.
* Log error.
* Không mark paid nửa vời.

---

## 25. Routes

### 25.1. Admin Routes

| Method | URL                                          | Name                                 | Description                    |
| ------ | -------------------------------------------- | ------------------------------------ | ------------------------------ |
| GET    | /admin/settings/payment/online               | admin.settings.payment.online.edit   | Online payment settings        |
| PATCH  | /admin/settings/payment/online               | admin.settings.payment.online.update | Update online payment settings |
| GET    | /admin/orders/{order}/payments/{transaction} | admin.orders.payments.show           | View transaction optional      |

### 25.2. Public Routes

| Method | URL                           | Name                          | Description              |
| ------ | ----------------------------- | ----------------------------- | ------------------------ |
| POST   | /checkout/payment/online      | checkout.payment.online.store | Select online payment    |
| POST   | /checkout/place-order-and-pay | checkout.place-order-and-pay  | Create order and payment |
| GET    | /payment/return/{gateway}     | payment.return                | Gateway return           |
| POST   | /payment/webhook/{gateway}    | payment.webhook               | Gateway webhook/IPN      |
| GET    | /payment/success/{order}      | payment.success               | Payment success page     |
| GET    | /payment/failed/{order}       | payment.failed                | Payment failed page      |
| POST   | /orders/{order}/payment/retry | orders.payment.retry          | Retry payment            |

Business rules:

* Webhook route không dùng CSRF.
* Return route GET/POST tùy gateway.
* Place order and pay cần CSRF.
* Retry cần CSRF.
* Success/failed pages validate access.

---

## 26. API Response Requirements

### 26.1. Create Payment Success

Response nếu AJAX:

| Field              | Description         |
| ------------------ | ------------------- |
| success            | true                |
| message            | Payment created     |
| order_number       | Order number        |
| transaction_number | Transaction number  |
| payment_status     | pending             |
| redirect_url       | Gateway payment URL |

Nếu gateway không redirect:

| Field        | Description                          |
| ------------ | ------------------------------------ |
| success      | true                                 |
| payment_data | Required client data nếu gateway cần |
| next_action  | redirect/display_qr/none             |

### 26.2. Payment Error Response

| Field        | Description       |
| ------------ | ----------------- |
| success      | false             |
| message      | Error message     |
| errors       | Validation errors |
| redirect_url | Optional          |

Business rules:

* Không trả secret.
* Không trả raw sensitive payload.
* Error message thân thiện.

---

## 27. UI / UX Requirements

### 27.1. Public UX

Yêu cầu:

* Online payment card rõ ràng.
* Loading state khi redirect.
* Không cho bấm nhiều lần.
* Nếu gateway lỗi, hiển thị lỗi rõ.
* Payment failed page có retry.
* Payment pending page có hướng dẫn.
* Mobile responsive.

### 27.2. Admin UX

Yêu cầu:

* Settings form rõ ràng.
* Secret fields masked.
* Transaction list trong order detail dễ đọc.
* Status badge rõ.
* Payment logs không hiển thị secret.
* Danger/critical actions có confirmation.

---

## 28. Error Handling

| Scenario                      | Expected Result                      |
| ----------------------------- | ------------------------------------ |
| Gateway disabled              | Không hiển thị online payment        |
| Missing credentials           | Admin warning, public không cho dùng |
| Checkout invalid              | Redirect cart/checkout               |
| Order already paid            | Không tạo payment mới                |
| Gateway create payment failed | Show error                           |
| Customer cancels payment      | Payment cancelled page               |
| Payment failed                | Payment failed page                  |
| Payment pending               | Payment pending page                 |
| Webhook invalid signature     | Log and reject                       |
| Amount mismatch               | Do not mark paid                     |
| Currency mismatch             | Do not mark paid                     |
| Duplicate webhook             | No-op                                |
| Return without transaction    | Error page                           |
| Server error                  | Show generic error and log           |

---

## 29. Performance Requirements

* Không query quá nhiều khi xử lý webhook.
* Webhook processing nhanh.
* Có thể queue xử lý webhook sau nếu phức tạp, nhưng MVP xử lý sync được.
* Payment logs không quá lớn nếu payload lớn.
* Index transaction_number, gateway_transaction_id, order_id, status.
* Order detail eager load payment transactions.

---

## 30. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type               | Description                                                                                |
| ------------------ | ------------------------------------------------------------------------------------------ |
| Migration          | payment_methods, payment_transactions, payment_webhook_logs, update order_payments nếu cần |
| Models             | PaymentMethod, PaymentTransaction, PaymentWebhookLog                                       |
| Controller Admin   | OnlinePaymentSettingsController                                                            |
| Controller Public  | OnlinePaymentController                                                                    |
| Controller Public  | PaymentReturnController                                                                    |
| Controller Public  | PaymentWebhookController                                                                   |
| Service            | PaymentGatewayManager                                                                      |
| Service            | PaymentTransactionService                                                                  |
| Service            | OnlinePaymentService                                                                       |
| Gateway            | MockPaymentGateway hoặc selected gateway adapter                                           |
| Request Validation | OnlinePaymentSettingsRequest, CreateOnlinePaymentRequest                                   |
| Routes             | Admin/public payment routes                                                                |
| Blade Admin        | Online payment settings                                                                    |
| Blade Public       | Payment result pages                                                                       |
| Blade Partials     | Online payment card, transaction table                                                     |
| JavaScript         | Payment selection/redirect loading state                                                   |
| Tests              | Online payment feature tests                                                               |

---

## 31. Test Cases

| Test Case ID | Scenario                         | Expected Result                  |
| ------------ | -------------------------------- | -------------------------------- |
| TC-001       | Admin mở online payment settings | Hiển thị settings                |
| TC-002       | Admin bật online payment         | Public thấy online payment       |
| TC-003       | Admin tắt online payment         | Public không thấy online payment |
| TC-004       | Missing credentials              | Không cho public dùng gateway    |
| TC-005       | Customer chọn online payment     | Payment method selected          |
| TC-006       | Place order and pay              | Order + transaction created      |
| TC-007       | Payment transaction created      | Status pending                   |
| TC-008       | Gateway redirect URL             | Customer được redirect           |
| TC-009       | Payment return success valid     | Order payment paid               |
| TC-010       | Payment return failed valid      | Payment failed                   |
| TC-011       | Webhook success valid            | Order payment paid               |
| TC-012       | Webhook invalid signature        | Không mark paid                  |
| TC-013       | Amount mismatch                  | Không mark paid                  |
| TC-014       | Currency mismatch                | Không mark paid                  |
| TC-015       | Duplicate webhook                | Không duplicate update           |
| TC-016       | Return and webhook both success  | Chỉ paid một lần                 |
| TC-017       | Already paid order retry         | Không tạo payment mới            |
| TC-018       | Failed payment retry             | Tạo transaction mới              |
| TC-019       | Cancelled order retry            | Bị chặn                          |
| TC-020       | Admin order detail               | Hiển thị transaction             |
| TC-021       | Payment failed page              | Có retry button                  |
| TC-022       | Payment success page             | Hiển thị order/payment info      |
| TC-023       | Guest payment success            | Access bằng token/session hợp lệ |
| TC-024       | Unauthorized order access        | Bị chặn                          |
| TC-025       | Mobile payment page              | Layout không vỡ                  |

---

## 32. Acceptance Criteria

Task này hoàn thành khi:

* [ ] Có online payment settings trong admin.
* [ ] Admin có thể bật/tắt online payment.
* [ ] Admin có thể cấu hình gateway sandbox/live.
* [ ] Admin có thể cấu hình credentials an toàn.
* [ ] Public checkout payment page hiển thị online payment khi active.
* [ ] Customer có thể chọn online payment.
* [ ] Có flow create order and pay.
* [ ] Order online payment ban đầu có payment_status pending.
* [ ] Payment transaction được tạo khi bắt đầu online payment.
* [ ] Có transaction_number unique.
* [ ] Có redirect flow tới gateway hoặc mock gateway.
* [ ] Có payment return/callback route.
* [ ] Có webhook/IPN route nếu gateway hỗ trợ.
* [ ] Payment response được verify ở backend.
* [ ] Payment success update transaction paid.
* [ ] Payment success update order payment_status paid.
* [ ] Payment success set paid_at.
* [ ] Payment failed/cancelled update status phù hợp.
* [ ] Không mark paid nếu signature invalid.
* [ ] Không mark paid nếu amount/currency mismatch.
* [ ] Duplicate callback/webhook không tạo duplicate update.
* [ ] Payment retry hoạt động cho failed/cancelled/pending expired nếu business cho phép.
* [ ] Không cho retry nếu order đã paid.
* [ ] Admin order detail hiển thị payment transaction.
* [ ] Payment result pages hiển thị rõ.
* [ ] Không lưu card data.
* [ ] Không log secret.
* [ ] Không dùng Vue.js.

---

## 33. Commands

Sau khi implement, chạy các lệnh:

| Command                | Purpose               |
| ---------------------- | --------------------- |
| php artisan migrate    | Chạy migration        |
| php artisan route:list | Kiểm tra route        |
| php artisan test       | Chạy test nếu có      |
| npm run build          | Build frontend assets |
| php artisan serve      | Chạy local server     |

URL test:

`http://127.0.0.1:8000/admin/settings/payment/online`

`http://127.0.0.1:8000/checkout/payment`

Payment result URL tùy route implement.

---

## 34. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-17-checkout-with-tax-currency-snapshot.md
* docs/tasks/task-18-payment-cod.md
* docs/tasks/task-19-order-creation.md
* docs/tasks/task-20-admin-order-management.md
* docs/tasks/task-23-report.md
* docs/tasks/task-24-online-payment.md

Sau đó implement Task 24: Online Payment theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 24.
* Implement online payment settings trong admin.
* Thiết kế theo gateway abstraction để có thể thêm nhiều payment gateway sau này.
* Nếu chưa có gateway thật được chọn, implement mock/sandbox gateway để test full flow.
* Tạo payment_methods, payment_transactions, payment_webhook_logs nếu cần.
* Public checkout payment page hiển thị online payment nếu active.
* Customer có thể chọn online payment.
* Implement flow create order and pay.
* Tạo order với payment_status pending trước khi redirect gateway.
* Tạo payment transaction với status pending.
* Redirect customer tới gateway/mock gateway.
* Implement payment return/callback route.
* Implement webhook/IPN route nếu gateway/mock gateway hỗ trợ.
* Verify payment response ở backend.
* Không mark paid nếu chưa verify.
* Không mark paid nếu amount/currency mismatch.
* Không mark paid nếu signature invalid.
* Payment success update order payment_status paid và set paid_at.
* Payment failed/cancelled update status phù hợp.
* Callback/webhook phải idempotent, không xử lý trùng.
* Cho retry payment nếu transaction failed/cancelled/expired và order chưa paid.
* Không cho retry nếu order đã paid hoặc cancelled.
* Admin order detail hiển thị payment transactions.
* Không lưu card data.
* Không log secret credentials.
* Không implement refund nâng cao.
* Không implement subscription/recurring payment.
* Không dùng Vue.js.
* Dùng Blade + Tailwind CSS + Alpine.js hoặc Fetch API nếu cần.
* Sau khi làm xong, báo cáo file đã tạo/sửa và lệnh cần chạy để test.
