# Task 18: Payment COD

## 1. Overview

Task này dùng để xây dựng phương thức thanh toán COD cho hệ thống e-commerce.

COD là viết tắt của Cash On Delivery, nghĩa là khách hàng thanh toán khi nhận hàng.

Task này nằm sau:

* Task 15: Cart
* Task 16: Coupon
* Task 17: Checkout with Tax / Currency Snapshot

Task này nằm trước:

* Task 19: Order Creation

Trong Task 18, hệ thống cần:

* Cho admin cấu hình phương thức thanh toán COD.
* Cho customer chọn COD ở bước thanh toán.
* Validate checkout session trước khi chọn COD.
* Lưu payment method snapshot vào checkout session.
* Chuẩn bị dữ liệu payment để Task 19 tạo order.
* Không xử lý online payment.
* Không tạo order chính thức trong Task 18.
* Không trừ tồn kho trong Task 18.

Frontend trong MVP sử dụng:

* Laravel Blade
* Tailwind CSS
* Alpine.js nếu cần
* Fetch API nếu cần
* Không dùng Vue.js.

---

## 2. Objectives

Sau khi hoàn thành Task 18, hệ thống cần có:

* Admin có thể bật/tắt phương thức COD.
* Admin có thể cấu hình tên hiển thị COD.
* Admin có thể cấu hình mô tả/hướng dẫn COD.
* Admin có thể cấu hình giới hạn đơn hàng tối thiểu/tối đa cho COD nếu cần.
* Checkout payment page hiển thị phương thức COD nếu COD đang active.
* Guest có thể chọn COD nếu checkout session hợp lệ.
* Customer đã đăng nhập có thể chọn COD nếu checkout session hợp lệ.
* Khi chọn COD, hệ thống lưu payment method vào checkout session.
* Khi chọn COD, hệ thống snapshot payment method name, code, amount và status.
* Payment status của COD ban đầu là `pending` hoặc `unpaid`.
* Checkout session phải được validate lại trước khi xác nhận COD.
* Không cho chọn COD nếu cart/checkout session không hợp lệ.
* Không tạo order trong Task 18.
* Không tạo online payment transaction trong Task 18.
* Không deduct stock trong Task 18.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong task này:

* COD payment settings trong admin.
* Enable/disable COD.
* Public payment step trong checkout.
* Select COD payment method.
* Validate checkout session.
* Save payment method snapshot to checkout session.
* Payment summary UI.
* COD instruction display.
* AJAX select COD nếu cần.
* Error handling.
* Prepare data for Order Creation task.

### 3.2. Out of Scope

Không làm trong Task 18:

* Không implement online payment.
* Không tích hợp payment gateway.
* Không tạo order chính thức.
* Không tạo order items.
* Không trừ tồn kho.
* Không reserve stock.
* Không gửi email xác nhận đơn hàng.
* Không tạo invoice.
* Không tạo shipment.
* Không quản lý trạng thái giao hàng.
* Không xử lý refund.
* Không dùng Vue.js.

---

## 4. Dependencies

Task này phụ thuộc các task trước:

| Task    | Dependency                            |
| ------- | ------------------------------------- |
| Task 05 | System Settings                       |
| Task 07 | Currency Management                   |
| Task 08 | Tax Management                        |
| Task 15 | Cart                                  |
| Task 16 | Coupon                                |
| Task 17 | Checkout with Tax / Currency Snapshot |

Task này chuẩn bị dữ liệu cho:

| Task    | Purpose        |
| ------- | -------------- |
| Task 19 | Order Creation |

---

## 5. User Roles

| Role     | Permission                              |
| -------- | --------------------------------------- |
| Guest    | Có thể chọn COD nếu checkout hợp lệ     |
| Customer | Có thể chọn COD nếu checkout hợp lệ     |
| Admin    | Có thể cấu hình COD                     |
| Staff    | Có thể cấu hình COD nếu được phân quyền |

---

## 6. COD Business Meaning

COD là phương thức thanh toán không cần giao dịch online ngay tại thời điểm đặt hàng.

Business rules:

* Customer không cần nhập thông tin thẻ.
* Customer sẽ thanh toán cho shipper hoặc nhân viên giao hàng khi nhận hàng.
* Payment status ban đầu là `pending` hoặc `unpaid`.
* Order payment status sẽ được cập nhật sau khi admin xác nhận đã thu tiền.
* Task 18 chỉ chọn và snapshot COD.
* Task 19 sẽ tạo order với payment method là COD.

---

## 7. Payment Flow

Flow tổng quan:

1. Customer hoàn tất checkout information ở Task 17.
2. Customer đi tới bước payment.
3. System validate checkout session.
4. System hiển thị COD nếu COD đang active.
5. Customer chọn COD.
6. Customer xác nhận phương thức thanh toán.
7. System lưu payment method snapshot vào checkout session.
8. System chuyển sang bước tạo order ở Task 19 hoặc chuẩn bị nút `Place Order`.
9. Task 19 tạo order dựa trên checkout session có payment method COD.

---

## 8. Admin COD Settings

Admin cần có nơi cấu hình COD.

Có thể đặt trong:

`/admin/settings/payment`

hoặc:

`/admin/payment-methods`

hoặc trong System Settings nếu project đã có cấu trúc settings.

### 8.1. COD Settings Fields

| Field            | Required | Description                          |
| ---------------- | -------- | ------------------------------------ |
| Enabled          | Yes      | Bật/tắt COD                          |
| Display Name     | Yes      | Tên hiển thị, ví dụ Cash on Delivery |
| Description      | No       | Mô tả ngắn                           |
| Instruction      | No       | Hướng dẫn hiển thị cho customer      |
| Min Order Amount | No       | Giá trị đơn tối thiểu để dùng COD    |
| Max Order Amount | No       | Giá trị đơn tối đa để dùng COD       |
| Sort Order       | No       | Thứ tự hiển thị                      |
| Status           | Yes      | Active / Inactive                    |

### 8.2. Example Display

Display Name:

`Cash on Delivery`

Description:

`Pay with cash when your order is delivered.`

Instruction:

`Please prepare the exact amount when receiving your order.`

---

## 9. Database Design

Có 2 hướng triển khai.

## 9.1. Recommended MVP: Store COD Settings In system_settings

Nếu Task 05 đã có System Settings, có thể lưu COD settings trong bảng settings.

Settings keys đề xuất:

| Key                          | Description       |
| ---------------------------- | ----------------- |
| payment_cod_enabled          | Bật/tắt COD       |
| payment_cod_display_name     | Tên hiển thị COD  |
| payment_cod_description      | Mô tả COD         |
| payment_cod_instruction      | Hướng dẫn COD     |
| payment_cod_min_order_amount | Giá trị tối thiểu |
| payment_cod_max_order_amount | Giá trị tối đa    |
| payment_cod_sort_order       | Thứ tự hiển thị   |

Ưu điểm:

* Đơn giản.
* Phù hợp MVP.
* Không cần tạo bảng payment methods riêng.

---

## 9.2. Optional Advanced: payment_methods Table

Nếu muốn mở rộng nhiều payment methods sau này, có thể tạo bảng `payment_methods`.

Fields đề xuất:

| Field            | Type              | Description                        |
| ---------------- | ----------------- | ---------------------------------- |
| id               | bigint            | Primary key                        |
| code             | string            | cod, bank_transfer, online_gateway |
| name             | string            | Tên hiển thị                       |
| description      | text nullable     | Mô tả                              |
| instruction      | text nullable     | Hướng dẫn                          |
| config           | json nullable     | Config riêng                       |
| min_order_amount | decimal nullable  | Giá trị đơn tối thiểu              |
| max_order_amount | decimal nullable  | Giá trị đơn tối đa                 |
| sort_order       | integer default 0 | Thứ tự hiển thị                    |
| status           | string            | active, inactive                   |
| created_at       | timestamp         | Created time                       |
| updated_at       | timestamp         | Updated time                       |

Business rules:

* `code` phải unique.
* COD có code cố định là `cod`.
* Payment method inactive không hiển thị public.
* MVP có thể chỉ dùng COD.

---

## 9.3. checkout_sessions Table Updates

Task 17 đã tạo `checkout_sessions`.

Task 18 cần bổ sung hoặc sử dụng các fields payment snapshot.

Fields đề xuất:

| Field                 | Type              | Description                   |
| --------------------- | ----------------- | ----------------------------- |
| payment_method_code   | nullable string   | Ví dụ cod                     |
| payment_method_name   | nullable string   | Tên method snapshot           |
| payment_status        | nullable string   | pending, unpaid, paid, failed |
| payment_amount        | decimal nullable  | Amount cần thanh toán         |
| payment_currency_code | nullable string   | Currency snapshot             |
| payment_instruction   | text nullable     | Instruction snapshot          |
| payment_selected_at   | datetime nullable | Thời điểm chọn payment        |

Business rules:

* COD payment status ban đầu là `pending` hoặc `unpaid`.
* Payment amount phải bằng checkout grand total.
* Payment currency code phải bằng checkout currency code.
* Payment method name phải snapshot để sau này admin đổi tên không ảnh hưởng order.
* Task 19 sẽ copy payment snapshot từ checkout session sang order.

---

## 9.4. Optional checkout_payments Table

Nếu muốn tách payment selection khỏi checkout session, có thể tạo bảng `checkout_payments`.

Fields đề xuất:

| Field               | Type          | Description          |
| ------------------- | ------------- | -------------------- |
| id                  | bigint        | Primary key          |
| checkout_session_id | bigint        | Checkout session     |
| payment_method_code | string        | cod                  |
| payment_method_name | string        | Snapshot name        |
| payment_status      | string        | pending/unpaid       |
| amount              | decimal       | Payment amount       |
| currency_code       | string        | Currency             |
| instruction         | text nullable | Instruction snapshot |
| selected_at         | datetime      | Selected time        |
| created_at          | timestamp     | Created time         |
| updated_at          | timestamp     | Updated time         |

MVP khuyến nghị lưu trực tiếp vào `checkout_sessions` để đơn giản.

---

## 10. Payment Status Rules

COD payment status cần rõ ràng.

| Status    | Meaning                                      |
| --------- | -------------------------------------------- |
| pending   | Đã chọn COD, chờ tạo order hoặc chờ thu tiền |
| unpaid    | Chưa thu tiền                                |
| paid      | Đã thu tiền sau khi giao hàng                |
| failed    | Payment thất bại nếu có                      |
| cancelled | Payment bị hủy                               |

Trong Task 18:

* Khi customer chọn COD, dùng `pending` hoặc `unpaid`.
* Không set `paid`.
* Admin update paid sẽ thuộc Order Management task sau.
* Online payment status không xử lý trong task này.

Khuyến nghị MVP:

* Checkout session dùng `payment_status = pending`.
* Order sau này có thể dùng `payment_status = unpaid` hoặc `pending`.

---

## 11. COD Availability Rules

COD chỉ hiển thị khi thỏa các điều kiện:

| Condition               | Rule                      |
| ----------------------- | ------------------------- |
| COD enabled             | COD setting active        |
| Checkout session active | Session còn hiệu lực      |
| Cart valid              | Cart vẫn hợp lệ           |
| Grand total valid       | Total > 0                 |
| Min order amount        | Grand total >= min nếu có |
| Max order amount        | Grand total <= max nếu có |
| Currency valid          | Currency checkout hợp lệ  |
| Address valid           | Shipping address đã có    |

Nếu không thỏa điều kiện:

* Không hiển thị COD.
* Hoặc hiển thị disabled kèm lý do.
* Không cho submit chọn COD.

Ví dụ message:

`Cash on Delivery is not available for this order.`

---

## 12. Public Payment Page

URL đề xuất:

`/checkout/payment`

Hoặc nếu dùng token:

`/checkout/{token}/payment`

Payment page cần hiển thị:

| Section                | Description                 |
| ---------------------- | --------------------------- |
| Payment Methods        | Danh sách payment methods   |
| COD Option             | Cash on Delivery            |
| COD Instruction        | Hướng dẫn thanh toán        |
| Order Summary          | Checkout summary từ Task 17 |
| Back To Checkout       | Quay lại thông tin checkout |
| Continue / Place Order | Đi tiếp sang Task 19        |

### 12.1. COD Option UI

COD option nên hiển thị dạng card hoặc radio option.

Nội dung:

* Tên phương thức.
* Mô tả.
* Hướng dẫn.
* Badge nếu cần: `Pay on delivery`.
* Radio selected state.
* Error message nếu không khả dụng.

### 12.2. Payment Page Behavior

Expected behavior:

* Customer mở payment page.
* System validate checkout session.
* System hiển thị COD nếu available.
* Customer chọn COD.
* Customer click continue.
* Request gửi backend.
* Backend lưu COD snapshot vào checkout session.
* System trả success.
* Customer chuyển sang bước tạo order ở Task 19.

---

## 13. Public Routes

Routes đề xuất:

| Method | URL                       | Name                       | Description              |
| ------ | ------------------------- | -------------------------- | ------------------------ |
| GET    | /checkout/payment         | checkout.payment.index     | Hiển thị payment step    |
| POST   | /checkout/payment/cod     | checkout.payment.cod.store | Chọn COD                 |
| GET    | /checkout/payment/summary | checkout.payment.summary   | Optional payment summary |

Nếu checkout dùng token:

| Method | URL                           | Name                       | Description             |
| ------ | ----------------------------- | -------------------------- | ----------------------- |
| GET    | /checkout/{token}/payment     | checkout.payment.index     | Payment step theo token |
| POST   | /checkout/{token}/payment/cod | checkout.payment.cod.store | Chọn COD theo token     |

Business rules:

* POST cần CSRF.
* Route payment phải validate ownership của checkout session.
* AJAX request trả JSON nếu dùng JavaScript.
* Nếu dùng form submit thường, vẫn không được tạo order trong Task 18.

---

## 14. Admin Routes

Nếu COD settings nằm trong System Settings:

| Method    | URL                     | Name                          | Description             |
| --------- | ----------------------- | ----------------------------- | ----------------------- |
| GET       | /admin/settings/payment | admin.settings.payment.edit   | Payment settings        |
| PUT/PATCH | /admin/settings/payment | admin.settings.payment.update | Update payment settings |

Nếu dùng Payment Methods riêng:

| Method | URL                             | Name                             | Description              |
| ------ | ------------------------------- | -------------------------------- | ------------------------ |
| GET    | /admin/payment-methods          | admin.payment-methods.index      | Danh sách payment method |
| GET    | /admin/payment-methods/cod/edit | admin.payment-methods.cod.edit   | Edit COD                 |
| PATCH  | /admin/payment-methods/cod      | admin.payment-methods.cod.update | Update COD               |

MVP khuyến nghị dùng System Settings.

---

## 15. Validation Rules

## 15.1. Admin COD Settings Validation

| Field            | Rule                                                |
| ---------------- | --------------------------------------------------- |
| enabled          | Boolean                                             |
| display_name     | Required if enabled                                 |
| description      | Nullable, max length                                |
| instruction      | Nullable                                            |
| min_order_amount | Nullable, numeric, min 0                            |
| max_order_amount | Nullable, numeric, min 0                            |
| max_order_amount | Must be greater than min_order_amount if both exist |
| sort_order       | Nullable integer                                    |
| status           | active/inactive nếu có                              |

---

## 15.2. Public Select COD Validation

Before saving COD:

| Rule                         | Description                |
| ---------------------------- | -------------------------- |
| Checkout session exists      | Session phải tồn tại       |
| Checkout session active      | Status active              |
| Checkout session not expired | Chưa hết hạn               |
| Ownership valid              | Thuộc current user/session |
| Cart still valid             | Cart vẫn hợp lệ            |
| Address valid                | Contact/shipping đã có     |
| COD enabled                  | COD đang active            |
| Amount valid                 | Grand total > 0            |
| Min/max order valid          | Thỏa min/max COD           |
| Currency valid               | Currency snapshot hợp lệ   |

---

## 16. Business Logic

## 16.1. Select COD Flow

Flow:

1. Resolve current checkout session.
2. Validate checkout session active.
3. Validate checkout session ownership.
4. Revalidate cart if needed.
5. Revalidate checkout summary amount.
6. Check COD enabled.
7. Check COD min/max order amount.
8. Snapshot payment method details.
9. Save payment method into checkout session.
10. Return success response.
11. Redirect or return next URL for Task 19.

---

## 16.2. Payment Snapshot Data

When customer selects COD, save:

| Field                 | Value                    |
| --------------------- | ------------------------ |
| payment_method_code   | cod                      |
| payment_method_name   | Current COD display name |
| payment_status        | pending hoặc unpaid      |
| payment_amount        | checkout grand total     |
| payment_currency_code | checkout currency code   |
| payment_instruction   | Current COD instruction  |
| payment_selected_at   | Current datetime         |

Business rules:

* Do not trust payment amount from frontend.
* Payment amount must come from checkout session recalculation.
* Snapshot must not change if admin edits COD name after checkout.
* Task 19 copies this snapshot into order.

---

## 16.3. Revalidation Before COD

Trước khi lưu COD, cần revalidate:

* Cart items.
* Product status.
* Variant status.
* Stock quantity.
* Coupon validity.
* Tax summary.
* Currency summary.
* Grand total.
* Checkout session expiry.

Nếu checkout không còn hợp lệ:

* Không lưu COD.
* Trả lỗi rõ ràng.
* Có thể redirect về cart hoặc checkout.

---

## 17. API Response Requirements

## 17.1. Select COD Success

Response JSON đề xuất:

| Field                    | Description                    |
| ------------------------ | ------------------------------ |
| success                  | true                           |
| message                  | COD selected successfully      |
| payment_method_code      | cod                            |
| payment_method_name      | Snapshot name                  |
| payment_status           | pending/unpaid                 |
| payment_amount           | Raw payment amount             |
| formatted_payment_amount | Formatted amount               |
| currency_code            | Currency code                  |
| next_url                 | URL bước tiếp theo cho Task 19 |

Business rules:

* `next_url` có thể là route tạo order ở Task 19 sau này.
* Trong Task 18, nếu Task 19 chưa implement, có thể redirect lại payment page với selected state.
* Không tạo order trong response này.

---

## 17.2. Error Response

Response lỗi đề xuất:

| Field        | Description                            |
| ------------ | -------------------------------------- |
| success      | false                                  |
| message      | Message lỗi                            |
| errors       | Validation errors nếu có               |
| redirect_url | Optional nếu cần quay về cart/checkout |

Business rules:

* AJAX không trả HTML error page.
* Lỗi phải hiển thị gần payment method hoặc trong alert.
* Nếu checkout session invalid, redirect về cart hoặc checkout.

---

## 18. UI Requirements

## 18.1. Admin UI

Admin COD settings cần đơn giản, dễ hiểu.

Yêu cầu:

* Toggle bật/tắt COD rõ ràng.
* Input display name.
* Textarea description.
* Textarea instruction.
* Min/max order amount.
* Save button.
* Success/error message.
* Không dùng popup browser confirm mặc định nếu có danger action.

---

## 18.2. Public Payment UI

Payment page cần hiện đại, rõ ràng.

Yêu cầu:

* COD hiển thị dạng card/radio.
* Selected state rõ ràng.
* Nếu COD disabled, không cho chọn.
* Nếu không có payment method khả dụng, hiển thị message.
* Order summary hiển thị bên phải trên desktop.
* Mobile layout không vỡ.
* Continue button rõ ràng.
* Loading state khi submit.
* Error message rõ ràng.

---

## 18.3. COD Selected State

Nếu checkout session đã chọn COD:

* COD card hiển thị selected.
* Payment summary hiển thị payment method.
* Button có thể hiển thị `Continue to Place Order`.
* Instruction hiển thị lại cho customer.

---

## 18.4. No Payment Method Available

Nếu không có payment method nào khả dụng:

Hiển thị message:

`No payment method is available for this order.`

Và:

* Disable continue button.
* Cho customer quay lại checkout/cart.
* Không tạo order.

---

## 19. Loading UX

Khi customer chọn COD:

* Button bị disabled.
* Text button đổi thành `Processing...`.
* Không cho submit nhiều lần.
* Có spinner nếu cần.
* Nếu thành công, chuyển tiếp mượt.
* Nếu lỗi, loading tắt và hiển thị lỗi.

Không dùng reload page nếu sử dụng AJAX.

---

## 20. Security Requirements

Yêu cầu bảo mật:

* CSRF cho POST select COD.
* Không cho chọn COD cho checkout session của user/session khác.
* Không tin amount từ frontend.
* Không tin payment method name từ frontend.
* Không tin payment status từ frontend.
* Payment amount phải lấy từ backend.
* Payment method phải được validate từ settings/database.
* Checkout token phải khó đoán.
* Không expose lỗi kỹ thuật.
* Không cho dùng checkout session expired.
* Không cho chọn COD nếu COD disabled.

---

## 21. Error Handling

| Scenario                   | Expected Result                  |
| -------------------------- | -------------------------------- |
| Checkout session missing   | Redirect checkout/cart           |
| Checkout session expired   | Ask customer to refresh checkout |
| Checkout session not owned | Forbidden                        |
| Cart empty                 | Redirect cart                    |
| Product inactive           | Redirect cart with warning       |
| Variant inactive           | Redirect cart with warning       |
| Quantity exceeds stock     | Redirect cart with warning       |
| Coupon invalid             | Recalculate or warning           |
| COD disabled               | Show payment unavailable         |
| Grand total invalid        | Show error                       |
| Amount below COD minimum   | Show min amount error            |
| Amount above COD maximum   | Show max amount error            |
| Network error              | Show retry message               |
| Server error               | Show general error               |

---

## 22. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type               | Description                                           |
| ------------------ | ----------------------------------------------------- |
| Migration          | Update checkout_sessions payment fields nếu chưa có   |
| Settings           | COD payment settings                                  |
| Controller Admin   | Payment settings controller nếu cần                   |
| Controller Public  | CheckoutPaymentController hoặc CodPaymentController   |
| Service            | PaymentMethodService, CodPaymentService nếu cần       |
| Request Validation | UpdateCodSettingsRequest, SelectCodPaymentRequest     |
| Routes             | Admin payment routes, public checkout payment routes  |
| Blade Admin        | Payment settings form                                 |
| Blade Public       | Checkout payment page                                 |
| Blade Partials     | Payment method card, COD instruction, payment summary |
| JavaScript         | Select COD AJAX nếu cần                               |
| Tests              | Payment COD feature tests                             |

---

## 23. Commands

Sau khi implement, chạy các lệnh:

| Command                | Purpose               |
| ---------------------- | --------------------- |
| php artisan migrate    | Chạy migration nếu có |
| php artisan route:list | Kiểm tra route        |
| php artisan test       | Chạy test nếu có      |
| npm run build          | Build frontend assets |
| php artisan serve      | Chạy local server     |

URL test:

`http://127.0.0.1:8000/admin/settings/payment`

`http://127.0.0.1:8000/checkout/payment`

---

## 24. Test Cases

| Test Case ID | Scenario                                | Expected Result                         |
| ------------ | --------------------------------------- | --------------------------------------- |
| TC-001       | Admin mở payment settings               | Hiển thị COD settings                   |
| TC-002       | Admin bật COD                           | COD active                              |
| TC-003       | Admin tắt COD                           | COD không hiển thị public               |
| TC-004       | Admin lưu display name COD              | Public hiển thị tên mới                 |
| TC-005       | Admin cấu hình min order amount         | COD chỉ available khi đủ min            |
| TC-006       | Admin cấu hình max order amount         | COD không available khi vượt max        |
| TC-007       | Guest mở payment với checkout hợp lệ    | Hiển thị COD                            |
| TC-008       | Customer mở payment với checkout hợp lệ | Hiển thị COD                            |
| TC-009       | Checkout session expired                | Không cho chọn COD                      |
| TC-010       | Checkout session không thuộc user       | Bị chặn                                 |
| TC-011       | Cart empty                              | Redirect về cart                        |
| TC-012       | Product inactive                        | Không cho chọn COD                      |
| TC-013       | Variant inactive                        | Không cho chọn COD                      |
| TC-014       | Quantity vượt stock                     | Không cho chọn COD                      |
| TC-015       | COD disabled                            | Không hiển thị hoặc disabled            |
| TC-016       | Order amount thấp hơn min COD           | Hiển thị lỗi                            |
| TC-017       | Order amount cao hơn max COD            | Hiển thị lỗi                            |
| TC-018       | Customer chọn COD thành công            | Checkout session lưu payment method     |
| TC-019       | COD selected                            | Payment status là pending/unpaid        |
| TC-020       | Payment amount                          | Bằng checkout grand total               |
| TC-021       | Currency snapshot                       | Payment currency đúng checkout currency |
| TC-022       | Submit nhiều lần                        | Không tạo duplicate hoặc lỗi            |
| TC-023       | AJAX select COD                         | Không reload page nếu dùng AJAX         |
| TC-024       | Task 18 không tạo order                 | Không có order được tạo                 |
| TC-025       | Task 18 không deduct stock              | Stock không đổi                         |
| TC-026       | Mobile payment page                     | Layout không vỡ                         |

---

## 25. Acceptance Criteria

Task này hoàn thành khi:

* [ ] Admin có thể bật/tắt COD.
* [ ] Admin có thể cấu hình tên hiển thị COD.
* [ ] Admin có thể cấu hình mô tả/hướng dẫn COD.
* [ ] Admin có thể cấu hình min/max order amount cho COD nếu cần.
* [ ] Public payment page hiển thị COD nếu COD active.
* [ ] COD không hiển thị hoặc disabled nếu COD inactive.
* [ ] Guest có thể chọn COD với checkout session hợp lệ.
* [ ] Customer có thể chọn COD với checkout session hợp lệ.
* [ ] Không cho chọn COD nếu checkout session expired.
* [ ] Không cho chọn COD nếu checkout session không thuộc current user/session.
* [ ] Không cho chọn COD nếu cart không hợp lệ.
* [ ] Không cho chọn COD nếu product inactive, variant inactive hoặc quantity vượt stock.
* [ ] Không cho chọn COD nếu order amount không thỏa min/max COD.
* [ ] Khi chọn COD, checkout session lưu `payment_method_code = cod`.
* [ ] Khi chọn COD, checkout session lưu payment method name snapshot.
* [ ] Khi chọn COD, checkout session lưu payment amount.
* [ ] Khi chọn COD, checkout session lưu payment currency.
* [ ] Payment status ban đầu là pending hoặc unpaid.
* [ ] Payment amount lấy từ backend, không lấy từ frontend.
* [ ] Có loading state khi chọn COD.
* [ ] Có error handling rõ ràng.
* [ ] Mobile payment page không vỡ layout.
* [ ] Không tạo Order trong Task 18.
* [ ] Không tạo Payment gateway transaction trong Task 18.
* [ ] Không deduct stock trong Task 18.
* [ ] Không dùng Vue.js.

---

## 26. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-05-system-settings.md
* docs/tasks/task-15-cart.md
* docs/tasks/task-16-coupon.md
* docs/tasks/task-17-checkout-with-tax-currency-snapshot.md
* docs/tasks/task-18-payment-cod.md

Sau đó implement Task 18: Payment COD theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 18.
* Implement COD payment settings trong admin.
* Cho admin bật/tắt COD.
* Cho admin cấu hình display name, description, instruction, min/max order amount nếu cần.
* Public checkout payment page hiển thị COD nếu COD đang active.
* Validate checkout session trước khi chọn COD.
* Không cho chọn COD nếu checkout session không hợp lệ, expired hoặc không thuộc current user/session.
* Không cho chọn COD nếu cart empty, product inactive, variant inactive hoặc quantity vượt stock.
* Không cho chọn COD nếu grand total không thỏa min/max COD.
* Khi customer chọn COD, lưu payment method snapshot vào checkout session.
* Snapshot gồm payment_method_code, payment_method_name, payment_status, payment_amount, payment_currency_code, payment_instruction, payment_selected_at.
* Payment amount phải lấy từ backend checkout session, không lấy từ frontend.
* Payment status ban đầu là pending hoặc unpaid.
* Có loading state và error message rõ ràng.
* Dùng Blade + Tailwind CSS + Alpine.js hoặc Fetch API.
* Không dùng Vue.js.
* Không implement Online Payment.
* Không tạo Order trong Task 18.
* Không deduct stock trong Task 18.
* Sau khi làm xong, báo cáo file đã tạo/sửa và lệnh cần chạy để test.
