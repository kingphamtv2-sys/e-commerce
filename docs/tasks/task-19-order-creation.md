Bạn tạo file mới:

```txt id="31m1ar"
docs/tasks/task-19-order-creation.md
```

Nội dung tài liệu như sau:

# Task 19: Order Creation

## 1. Overview

Task này dùng để xây dựng chức năng tạo đơn hàng chính thức từ checkout session.

Task này nằm sau:

* Task 15: Cart
* Task 16: Coupon
* Task 17: Checkout with Tax / Currency Snapshot
* Task 18: Payment COD

Ở Task 19, hệ thống sẽ chuyển dữ liệu từ checkout session thành order chính thức.

Order Creation cần:

* Validate checkout session.
* Validate cart lần cuối.
* Validate payment method đã chọn.
* Tạo order.
* Tạo order items.
* Snapshot đầy đủ product, variant, price, tax, currency, coupon, payment, address.
* Deduct hoặc reserve inventory theo business rule.
* Ghi inventory log nếu có.
* Ghi coupon usage nếu có.
* Mark checkout session completed.
* Mark cart converted.
* Clear active cart.
* Redirect customer tới order success page.

Task này không làm:

* Admin Order Management.
* Update trạng thái đơn hàng bởi admin.
* Online Payment.
* Shipping management nâng cao.
* Email notification nâng cao nếu chưa có mail task.

Frontend trong MVP sử dụng:

* Laravel Blade
* Tailwind CSS
* Alpine.js nếu cần
* Fetch API nếu cần
* Không dùng Vue.js.

---

## 2. Objectives

Sau khi hoàn thành Task 19, hệ thống cần có:

* Customer có thể place order từ checkout session hợp lệ.
* Guest có thể place order.
* Logged-in customer có thể place order.
* Hệ thống tạo order chính thức.
* Hệ thống tạo order items từ checkout session items.
* Order lưu snapshot đầy đủ tại thời điểm đặt hàng.
* Order lưu shipping address snapshot.
* Order lưu billing address snapshot.
* Order lưu contact information snapshot.
* Order lưu currency snapshot.
* Order lưu tax snapshot.
* Order lưu coupon snapshot nếu có.
* Order lưu payment method snapshot.
* Order status ban đầu rõ ràng.
* Payment status ban đầu phù hợp với COD.
* Inventory được cập nhật theo order.
* Coupon usage được ghi nhận khi order tạo thành công.
* Cart được chuyển trạng thái converted.
* Checkout session được chuyển trạng thái completed.
* Không tạo duplicate order khi customer bấm nhiều lần.
* Có order success page.
* Có order lookup token cho guest nếu cần.
* Không implement Admin Order Management trong task này.
* Không implement Online Payment trong task này.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong task này:

* Order database design.
* Order item database design.
* Order address snapshot.
* Order payment snapshot.
* Order tax snapshot nếu cần.
* Create order from checkout session.
* Final checkout validation.
* Final stock validation.
* Inventory deduction/reservation.
* Inventory log.
* Coupon usage creation.
* Cart converted.
* Checkout session completed.
* Prevent duplicate order submit.
* Order success page.
* Guest order access token.
* Basic customer order success display.
* Basic route for order success.

### 3.2. Out of Scope

Không làm trong Task 19:

* Không làm Admin Order Management.
* Không làm admin update order status.
* Không làm order cancel/refund.
* Không làm return/refund.
* Không làm shipment/tracking.
* Không làm invoice PDF.
* Không gửi email nâng cao.
* Không implement Online Payment.
* Không tích hợp payment gateway.
* Không làm customer order history nâng cao.
* Không làm review product.
* Không dùng Vue.js.

---

## 4. Dependencies

Task này phụ thuộc:

| Task      | Dependency                            |
| --------- | ------------------------------------- |
| Task 07   | Currency Management                   |
| Task 08   | Tax Management                        |
| Task 10   | Product Management                    |
| Task 10.1 | Product Options and Variants          |
| Task 10.2 | Variant Images                        |
| Task 12   | Inventory Management                  |
| Task 15   | Cart                                  |
| Task 16   | Coupon                                |
| Task 17   | Checkout with Tax / Currency Snapshot |
| Task 18   | Payment COD                           |

Task sau sẽ dùng order data:

| Task    | Purpose                |
| ------- | ---------------------- |
| Task 20 | Admin Order Management |
| Task 21 | Admin Dashboard        |
| Task 23 | Report                 |
| Task 24 | Online Payment         |
| Task 25 | Review Product         |

---

## 5. User Roles

| Role     | Permission                                    |
| -------- | --------------------------------------------- |
| Guest    | Có thể tạo order từ guest checkout session    |
| Customer | Có thể tạo order từ customer checkout session |
| Admin    | Không tạo public order trong task này         |
| Staff    | Không tạo public order trong task này         |

---

## 6. Order Creation Flow

Flow tổng quan:

1. Customer hoàn tất checkout information ở Task 17.
2. Customer chọn payment COD ở Task 18.
3. Customer click `Place Order`.
4. Backend validate checkout session.
5. Backend validate payment method.
6. Backend validate cart/items/stock lần cuối.
7. Backend tạo order trong transaction.
8. Backend tạo order items.
9. Backend tạo order address snapshots.
10. Backend tạo payment snapshot.
11. Backend tạo tax lines nếu cần.
12. Backend cập nhật inventory.
13. Backend ghi inventory logs.
14. Backend ghi coupon usage nếu có.
15. Backend mark checkout session completed.
16. Backend mark cart converted.
17. Backend clear active cart hoặc tạo cart mới rỗng sau này.
18. Backend redirect tới order success page.

---

## 7. Important Business Rule: Idempotency

Order Creation phải tránh tạo duplicate order.

Vấn đề:

* Customer bấm `Place Order` nhiều lần.
* Browser retry request.
* Network chậm.
* Customer refresh trang.

Yêu cầu:

* Mỗi checkout session chỉ được tạo một order.
* Nếu checkout session đã completed và đã có order, trả về order đó.
* Button `Place Order` phải disabled khi đang xử lý.
* Backend phải kiểm tra trạng thái checkout session trong transaction.
* Không dựa vào frontend để chống duplicate.

---

## 8. Order Number

Mỗi order cần có order number dễ đọc.

Ví dụ:

```txt
ORD-20260623-000001
```

Yêu cầu:

* Order number phải unique.
* Không dùng ID trần làm order number public nếu có thể.
* Order number nên dễ tìm trong admin.
* Có thể dùng prefix từ system settings nếu sau này cần.
* Không được duplicate khi tạo order đồng thời.

---

## 9. Order Status Rules

Order cần có trạng thái ban đầu.

### 9.1. Order Status

Các status đề xuất:

| Status     | Meaning                       |
| ---------- | ----------------------------- |
| pending    | Đơn mới tạo, chờ xử lý        |
| confirmed  | Admin đã xác nhận             |
| processing | Đang xử lý                    |
| shipped    | Đã giao cho đơn vị vận chuyển |
| completed  | Hoàn tất                      |
| cancelled  | Đã hủy                        |
| refunded   | Đã hoàn tiền                  |

Trong Task 19:

* Order mới tạo với COD nên có `order_status = pending`.

Admin update status sẽ xử lý ở Task 20.

### 9.2. Payment Status

Các payment status đề xuất:

| Status    | Meaning                 |
| --------- | ----------------------- |
| unpaid    | Chưa thanh toán         |
| pending   | Chờ thanh toán/xác nhận |
| paid      | Đã thanh toán           |
| failed    | Thanh toán thất bại     |
| refunded  | Đã hoàn tiền            |
| cancelled | Thanh toán bị hủy       |

Với COD:

* Payment status ban đầu nên là `unpaid` hoặc `pending`.
* Khuyến nghị MVP: `payment_status = unpaid`.
* Khi admin xác nhận đã thu tiền sau này, Task 20 sẽ update thành `paid`.

### 9.3. Fulfillment Status

Có thể có fulfillment status:

| Status      | Meaning             |
| ----------- | ------------------- |
| unfulfilled | Chưa giao           |
| processing  | Đang chuẩn bị       |
| shipped     | Đã giao cho shipper |
| delivered   | Đã giao             |
| cancelled   | Đã hủy              |

Trong Task 19:

* Có thể set `fulfillment_status = unfulfilled`.

---

## 10. Database Design

## 10.1. orders Table

Tạo bảng `orders`.

Fields đề xuất:

| Field                 | Type              | Description                      |
| --------------------- | ----------------- | -------------------------------- |
| id                    | bigint            | Primary key                      |
| order_number          | string            | Unique order number              |
| user_id               | nullable bigint   | Customer nếu logged-in           |
| guest_token           | nullable string   | Token để guest xem order         |
| checkout_session_id   | nullable bigint   | Checkout session nguồn           |
| cart_id               | nullable bigint   | Cart nguồn                       |
| customer_name         | string            | Snapshot customer name           |
| customer_email        | string            | Snapshot customer email          |
| customer_phone        | string            | Snapshot customer phone          |
| customer_note         | text nullable     | Note của customer                |
| order_status          | string            | pending, confirmed, completed... |
| payment_status        | string            | unpaid, pending, paid...         |
| fulfillment_status    | string nullable   | unfulfilled, shipped...          |
| base_currency_code    | string            | Base currency snapshot           |
| currency_code         | string            | Checkout currency snapshot       |
| currency_rate         | decimal           | Currency rate snapshot           |
| currency_symbol       | string nullable   | Currency symbol snapshot         |
| currency_position     | string nullable   | before/after                     |
| subtotal_amount       | decimal           | Subtotal snapshot                |
| discount_amount       | decimal default 0 | Discount snapshot                |
| taxable_amount        | decimal default 0 | Taxable amount snapshot          |
| tax_amount            | decimal default 0 | Tax amount snapshot              |
| shipping_amount       | decimal default 0 | Shipping amount snapshot         |
| grand_total_amount    | decimal           | Final total                      |
| coupon_id             | nullable bigint   | Coupon ID nếu có                 |
| coupon_code           | nullable string   | Coupon code snapshot             |
| coupon_name           | nullable string   | Coupon name snapshot             |
| coupon_discount_type  | nullable string   | Snapshot coupon type             |
| coupon_discount_value | nullable decimal  | Snapshot coupon value            |
| payment_method_code   | nullable string   | cod                              |
| payment_method_name   | nullable string   | Payment method snapshot          |
| payment_instruction   | nullable text     | Payment instruction snapshot     |
| ordered_at            | datetime          | Thời điểm đặt hàng               |
| paid_at               | nullable datetime | Thời điểm thanh toán             |
| cancelled_at          | nullable datetime | Thời điểm hủy                    |
| created_at            | timestamp         | Created time                     |
| updated_at            | timestamp         | Updated time                     |

Business rules:

* `order_number` unique.
* `guest_token` phải khó đoán.
* Guest order dùng guest_token để xem success page.
* Customer order có user_id.
* Amount phải lấy từ checkout session snapshot, không lấy từ frontend.
* Order không bị ảnh hưởng khi product/coupon/tax/currency thay đổi sau này.

---

## 10.2. order_items Table

Tạo bảng `order_items`.

Fields đề xuất:

| Field              | Type              | Description                     |
| ------------------ | ----------------- | ------------------------------- |
| id                 | bigint            | Primary key                     |
| order_id           | bigint            | Reference orders                |
| product_id         | bigint nullable   | Product ID để trace             |
| product_variant_id | bigint nullable   | Variant ID để trace             |
| product_name       | string            | Product name snapshot           |
| product_slug       | string nullable   | Product slug snapshot           |
| variant_name       | string nullable   | Variant name snapshot           |
| sku                | string nullable   | SKU snapshot                    |
| image_path         | string nullable   | Image snapshot                  |
| quantity           | integer           | Quantity ordered                |
| base_unit_price    | decimal           | Unit price in base currency     |
| unit_price         | decimal           | Unit price in order currency    |
| subtotal_amount    | decimal           | Unit price x quantity           |
| discount_amount    | decimal default 0 | Item discount                   |
| taxable_amount     | decimal           | Taxable amount                  |
| tax_class_id       | nullable bigint   | Tax class ID                    |
| tax_name           | nullable string   | Tax name snapshot               |
| tax_rate           | decimal default 0 | Tax rate snapshot               |
| tax_amount         | decimal default 0 | Item tax amount                 |
| total_amount       | decimal           | Item total after discount + tax |
| options_snapshot   | json nullable     | Variant option values snapshot  |
| created_at         | timestamp         | Created time                    |
| updated_at         | timestamp         | Updated time                    |

Business rules:

* Order item snapshot không đổi nếu product đổi sau khi order tạo.
* Product/variant ID có thể nullable để giữ order nếu product bị xóa mềm sau này.
* Quantity phải lớn hơn 0.
* Item total phải khớp checkout session item snapshot.

---

## 10.3. order_addresses Table

Tạo bảng `order_addresses` để lưu shipping và billing address snapshot.

Fields đề xuất:

| Field          | Type            | Description                   |
| -------------- | --------------- | ----------------------------- |
| id             | bigint          | Primary key                   |
| order_id       | bigint          | Reference orders              |
| type           | string          | shipping hoặc billing         |
| recipient_name | string          | Người nhận                    |
| phone          | string          | SĐT                           |
| address_line_1 | string          | Địa chỉ chính                 |
| address_line_2 | nullable string | Địa chỉ bổ sung               |
| city           | string          | Tỉnh/thành                    |
| district       | nullable string | Quận/huyện                    |
| ward           | nullable string | Phường/xã                     |
| postal_code    | nullable string | Postal code                   |
| country        | string          | Quốc gia                      |
| raw_address    | json nullable   | Full address snapshot nếu cần |
| created_at     | timestamp       | Created time                  |
| updated_at     | timestamp       | Updated time                  |

Business rules:

* Mỗi order cần có shipping address.
* Billing address có thể giống shipping hoặc riêng.
* Nếu billing same as shipping, vẫn nên tạo billing row để snapshot rõ ràng.
* Address không bị thay đổi nếu customer profile thay đổi sau này.

---

## 10.4. order_payments Table

Tạo bảng `order_payments` để lưu payment snapshot.

Fields đề xuất:

| Field               | Type              | Description                   |
| ------------------- | ----------------- | ----------------------------- |
| id                  | bigint            | Primary key                   |
| order_id            | bigint            | Reference orders              |
| payment_method_code | string            | cod                           |
| payment_method_name | string            | Payment name snapshot         |
| payment_status      | string            | unpaid, pending, paid...      |
| amount              | decimal           | Payment amount                |
| currency_code       | string            | Currency snapshot             |
| transaction_id      | nullable string   | Online transaction ID sau này |
| gateway_response    | json nullable     | Gateway response sau này      |
| instruction         | text nullable     | COD instruction               |
| paid_at             | nullable datetime | Paid time                     |
| created_at          | timestamp         | Created time                  |
| updated_at          | timestamp         | Updated time                  |

Business rules:

* COD không có transaction_id trong Task 19.
* Payment amount phải bằng order grand total.
* Payment status ban đầu theo COD là unpaid hoặc pending.
* Online payment sẽ mở rộng ở Task 24.

---

## 10.5. order_tax_lines Table

Tùy chọn nhưng khuyến nghị tạo để report rõ hơn.

Fields đề xuất:

| Field          | Type            | Description       |
| -------------- | --------------- | ----------------- |
| id             | bigint          | Primary key       |
| order_id       | bigint          | Reference orders  |
| tax_class_id   | nullable bigint | Tax class ID      |
| tax_name       | string          | Tax name snapshot |
| tax_rate       | decimal         | Tax rate snapshot |
| taxable_amount | decimal         | Taxable amount    |
| tax_amount     | decimal         | Tax amount        |
| created_at     | timestamp       | Created time      |
| updated_at     | timestamp       | Updated time      |

Business rules:

* Order tax lines lấy từ checkout session tax lines nếu có.
* Nếu không có checkout tax lines, có thể aggregate từ order_items.
* Tax line snapshot không đổi sau khi admin đổi tax.

---

## 10.6. order_status_histories Table

Có thể tạo để chuẩn bị cho Task 20.

Fields đề xuất:

| Field           | Type            | Description           |
| --------------- | --------------- | --------------------- |
| id              | bigint          | Primary key           |
| order_id        | bigint          | Reference orders      |
| from_status     | nullable string | Status cũ             |
| to_status       | string          | Status mới            |
| note            | text nullable   | Ghi chú               |
| changed_by      | nullable bigint | Admin/user ID nếu có  |
| changed_by_type | nullable string | admin/customer/system |
| created_at      | timestamp       | Created time          |
| updated_at      | timestamp       | Updated time          |

Trong Task 19:

* Tạo history đầu tiên: `Order created`.
* Admin update status sẽ thuộc Task 20.

---

## 11. Relationships

Relationships cần có:

| Model        | Relationship                       |
| ------------ | ---------------------------------- |
| Order        | belongsTo User nullable            |
| Order        | belongsTo CheckoutSession nullable |
| Order        | belongsTo Cart nullable            |
| Order        | hasMany OrderItem                  |
| Order        | hasMany OrderAddress               |
| Order        | hasMany OrderPayment               |
| Order        | hasMany OrderTaxLine               |
| Order        | hasMany OrderStatusHistory         |
| OrderItem    | belongsTo Order                    |
| OrderItem    | belongsTo Product nullable         |
| OrderItem    | belongsTo ProductVariant nullable  |
| OrderPayment | belongsTo Order                    |
| OrderAddress | belongsTo Order                    |
| OrderTaxLine | belongsTo Order                    |

---

## 12. Create Order Route

Public route đề xuất:

| Method | URL                                 | Name                 | Description                          |
| ------ | ----------------------------------- | -------------------- | ------------------------------------ |
| POST   | /checkout/place-order               | checkout.place-order | Tạo order từ checkout session        |
| GET    | /orders/{order}/success             | orders.success       | Order success page cho customer      |
| GET    | /guest-orders/{guest_token}/success | guest.orders.success | Order success page cho guest nếu cần |

Nếu checkout dùng token:

| Method | URL                           | Name                 | Description                   |
| ------ | ----------------------------- | -------------------- | ----------------------------- |
| POST   | /checkout/{token}/place-order | checkout.place-order | Tạo order theo checkout token |

Business rules:

* POST cần CSRF.
* Place order không được nhận amount từ frontend.
* Place order phải resolve checkout session từ current user/session/token.
* Success page phải validate quyền xem order.

---

## 13. Order Creation Validation

Trước khi tạo order, cần validate:

| Rule                         | Description                               |
| ---------------------------- | ----------------------------------------- |
| Checkout session exists      | Phải tồn tại                              |
| Checkout session active      | Status active                             |
| Checkout session not expired | Chưa hết hạn                              |
| Checkout session owned       | Thuộc current user/session                |
| Payment selected             | Có payment method                         |
| Payment method valid         | COD active hoặc snapshot hợp lệ theo rule |
| Cart exists                  | Có cart nguồn                             |
| Cart not converted           | Chưa converted                            |
| Cart not empty               | Có item                                   |
| Checkout items exist         | Có checkout item snapshot                 |
| Product active               | Product vẫn active trước khi tạo order    |
| Variant active               | Variant vẫn active                        |
| Stock valid                  | Quantity không vượt available stock       |
| Coupon revalidated           | Nếu coupon có                             |
| Total valid                  | Grand total >= 0                          |
| Address exists               | Có shipping/billing address               |
| Contact valid                | Có email/phone/name                       |

Nếu validation fail:

* Không tạo order.
* Không deduct stock.
* Không ghi coupon usage.
* Trả lỗi rõ ràng.
* Redirect về cart hoặc checkout nếu cần.

---

## 14. Transaction Requirement

Order creation phải chạy trong database transaction.

Trong transaction:

1. Lock checkout session nếu cần.
2. Kiểm tra checkout session chưa completed.
3. Kiểm tra chưa có order cho checkout session.
4. Validate stock lần cuối.
5. Tạo order.
6. Tạo order items.
7. Tạo order addresses.
8. Tạo order payment.
9. Tạo tax lines.
10. Deduct/reserve stock.
11. Ghi inventory logs.
12. Ghi coupon usage.
13. Update coupon used_count.
14. Mark checkout session completed.
15. Mark cart converted.
16. Commit transaction.

Nếu bất kỳ bước nào lỗi:

* Rollback toàn bộ.
* Không có order nửa vời.
* Không trừ stock nửa vời.
* Không tăng coupon usage nửa vời.

---

## 15. Inventory Rules

Task 19 cần cập nhật inventory khi order được tạo.

### 15.1. Recommended MVP Behavior

Khi order tạo thành công:

* Deduct stock ngay.
* Ghi inventory log type `order_created`.
* Không reserve riêng nếu hệ thống chưa có reservation flow.

Lý do:

* MVP đơn giản.
* COD order vẫn chiếm hàng.
* Tránh bán vượt stock.

### 15.2. Product Without Variant

Nếu product không có variant:

* Deduct stock theo product inventory.

### 15.3. Product With Variant

Nếu product có variant:

* Deduct stock theo variant inventory.
* Không deduct product parent nếu inventory quản lý theo variant.

### 15.4. Stock Validation

Trước khi deduct:

* available_quantity phải đủ.
* quantity order không vượt available.
* Nếu không đủ, rollback và báo lỗi.

### 15.5. Inventory Log

Ghi log gồm:

| Field              | Description    |
| ------------------ | -------------- |
| product_id         | Product        |
| product_variant_id | Variant nếu có |
| quantity_before    | Tồn trước      |
| quantity_change    | Số lượng giảm  |
| quantity_after     | Tồn sau        |
| type               | order_created  |
| reference_type     | order          |
| reference_id       | order_id       |
| note               | Order number   |

Nếu Task 12 đã có cấu trúc inventory log, dùng lại cấu trúc đó.

---

## 16. Coupon Usage Rules

Nếu order có coupon:

* Tạo coupon_usage record.
* Tăng coupon used_count.
* Snapshot coupon_code.
* Snapshot discount_amount.
* Gắn order_id.
* Gắn user_id nếu customer logged-in.
* Gắn cart_id nếu cần.

Business rules:

* Chỉ ghi coupon usage sau khi order tạo thành công.
* Không ghi coupon usage khi chỉ apply coupon ở cart.
* Nếu transaction rollback, coupon usage cũng rollback.
* Không cho vượt usage_limit nếu đã đạt limit tại thời điểm order.
* Với guest, usage_limit_per_user có thể hạn chế theo email nếu business muốn, nhưng MVP có thể chỉ enforce cho logged-in user.

---

## 17. Cart Conversion Rules

Sau khi order tạo thành công:

* Cart status đổi thành `converted`.
* Cart không còn là active cart.
* Có thể xóa cart items hoặc giữ lại để trace.
* Khuyến nghị giữ cart_items để trace nếu cart status converted.
* Header cart badge về 0.
* Nếu customer tiếp tục mua hàng, hệ thống tạo cart mới.

Business rules:

* Không xóa order data nếu cart bị clear sau này.
* Order không phụ thuộc cart sau khi tạo.
* Cart chỉ là nguồn để tạo order.

---

## 18. Checkout Session Completion

Sau khi order tạo thành công:

* Checkout session status đổi thành `completed`.
* Checkout session linked order nếu có field `order_id`.
* Không cho dùng lại checkout session để tạo order lần nữa.

Nếu customer refresh success page:

* Hiển thị order đã tạo.
* Không tạo order mới.

---

## 19. Order Snapshot Rules

Order phải snapshot đầy đủ để không phụ thuộc dữ liệu hiện tại.

Snapshot cần bao gồm:

| Data                  | Snapshot |
| --------------------- | -------- |
| Product name          | Yes      |
| Product slug          | Yes      |
| Variant name          | Yes      |
| Variant option values | Yes      |
| SKU                   | Yes      |
| Image path            | Yes      |
| Unit price            | Yes      |
| Discount              | Yes      |
| Tax name/rate         | Yes      |
| Currency code/rate    | Yes      |
| Coupon code/value     | Yes      |
| Payment method name   | Yes      |
| Shipping address      | Yes      |
| Billing address       | Yes      |
| Customer contact      | Yes      |

Business rules:

* Không lấy lại giá từ product khi tạo order nếu checkout session đã có snapshot.
* Có thể revalidate stock/status từ product hiện tại.
* Amount chính thức lấy từ checkout session snapshot sau khi revalidate.

---

## 20. Payment COD Rules

Task 19 chỉ dùng payment snapshot đã chọn ở Task 18.

Với COD:

| Field               | Value               |
| ------------------- | ------------------- |
| payment_method_code | cod                 |
| payment_status      | unpaid hoặc pending |
| paid_at             | null                |
| transaction_id      | null                |
| amount              | order grand total   |
| currency_code       | order currency      |

Business rules:

* Không set paid cho COD tại thời điểm tạo order.
* Admin sẽ update payment status ở Task 20.
* Nếu không có payment method selected, không cho tạo order.
* Online Payment không xử lý trong Task 19.

---

## 21. Order Success Page

Sau khi order tạo thành công, customer được chuyển tới success page.

Success page cần hiển thị:

| Element           | Description          |
| ----------------- | -------------------- |
| Success message   | Đặt hàng thành công  |
| Order number      | Mã đơn hàng          |
| Customer email    | Email nhận thông tin |
| Payment method    | COD                  |
| Payment status    | Unpaid/Pending       |
| Grand total       | Tổng tiền            |
| Shipping address  | Địa chỉ giao hàng    |
| Ordered items     | Danh sách item       |
| Continue shopping | Link về catalog/home |

Message ví dụ:

`Thank you! Your order has been placed successfully.`

Với COD có thể hiển thị thêm:

`Please prepare the payment when your order is delivered.`

---

## 22. Guest Order Access

Guest không có account, nên cần cách xem order success.

Options:

| Option       | Description                         |
| ------------ | ----------------------------------- |
| guest_token  | Token khó đoán trong URL            |
| session only | Chỉ xem được trong session hiện tại |

Khuyến nghị:

* Tạo `guest_token` khó đoán.
* Guest success URL dùng guest_token.
* Không expose order bằng ID đơn giản.
* Guest chỉ xem thông tin cơ bản.
* Customer logged-in xem order bằng user ownership.

Security:

* guest_token phải random.
* Không dùng email/order number đơn thuần để truy cập order.

---

## 23. Email Notification

Trong Task 19, email là optional.

MVP có thể:

* Không gửi email.
* Hoặc dùng mail log nếu đã cấu hình.
* Nếu gửi, chỉ gửi confirmation đơn giản.

Email nâng cao có thể làm sau.

---

## 24. API Response Requirements

### 24.1. Place Order Success

Response nếu AJAX:

| Field        | Description                |
| ------------ | -------------------------- |
| success      | true                       |
| message      | Order created successfully |
| order_id     | Order ID                   |
| order_number | Order number               |
| redirect_url | Order success URL          |
| cart_count   | 0                          |

Nếu form submit thường:

* Redirect tới order success page.
* Flash success message.

### 24.2. Error Response

Response lỗi nếu AJAX:

| Field        | Description                |
| ------------ | -------------------------- |
| success      | false                      |
| message      | Message lỗi                |
| errors       | Validation errors nếu có   |
| redirect_url | Optional cart/checkout URL |

Business rules:

* Không trả HTML error page cho AJAX.
* Không tạo order nếu lỗi.
* Không deduct stock nếu lỗi.
* Error message rõ ràng.

---

## 25. UI Requirements

### 25.1. Place Order Button

Button ở payment/checkout final step:

| State   | Display          |
| ------- | ---------------- |
| Default | Place Order      |
| Loading | Placing Order... |
| Success | Redirecting...   |
| Error   | Back to normal   |

Yêu cầu:

* Disable khi đang xử lý.
* Không cho submit nhiều lần.
* Có loading state.
* Nếu lỗi, hiển thị message rõ.

### 25.2. Final Review

Trước khi place order, customer nên thấy:

* Contact information.
* Shipping address.
* Billing address.
* Payment method.
* Order items.
* Subtotal.
* Discount.
* Tax.
* Shipping.
* Grand total.

### 25.3. Success Page UI

Yêu cầu:

* UI rõ ràng, chuyên nghiệp.
* Order number nổi bật.
* COD instruction hiển thị rõ.
* Mobile responsive.
* Không vỡ layout.

---

## 26. Security Requirements

Yêu cầu bảo mật:

* CSRF cho place order.
* Không tin amount từ frontend.
* Không tin product price từ frontend.
* Không tin tax từ frontend.
* Không tin discount từ frontend.
* Không tin payment status từ frontend.
* Validate checkout session ownership.
* Validate cart ownership.
* Validate stock ở backend.
* Checkout token phải khó đoán.
* Guest token phải khó đoán.
* Không cho xem order của user khác.
* Không expose lỗi kỹ thuật.
* Prevent duplicate order.
* Use database transaction.

---

## 27. Performance Requirements

* Eager load checkout session items.
* Eager load product/variant/inventory nếu cần validate.
* Không query lặp trong loop quá nhiều.
* Order creation service nên gom logic.
* Dùng transaction.
* Lock inventory row nếu cần để tránh oversell.
* Không xử lý tác vụ nặng trong request nếu chưa cần.

---

## 28. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type               | Description                                                                                   |
| ------------------ | --------------------------------------------------------------------------------------------- |
| Migration          | orders, order_items, order_addresses, order_payments, order_tax_lines, order_status_histories |
| Models             | Order, OrderItem, OrderAddress, OrderPayment, OrderTaxLine, OrderStatusHistory                |
| Controller         | OrderCreationController, OrderSuccessController                                               |
| Service            | OrderCreationService                                                                          |
| Service            | OrderNumberService                                                                            |
| Service            | InventoryDeductionService nếu cần                                                             |
| Request Validation | PlaceOrderRequest                                                                             |
| Routes             | Public place order and success routes                                                         |
| Blade Views        | Order success page                                                                            |
| Blade Partials     | Order summary/items                                                                           |
| JavaScript         | Place order submit handler nếu AJAX                                                           |
| Tests              | Order creation feature tests                                                                  |

---

## 29. Business Logic Services

### 29.1. OrderCreationService

Trách nhiệm:

* Validate checkout session.
* Prevent duplicate order.
* Create order.
* Create order items.
* Create addresses.
* Create payment snapshot.
* Create tax lines.
* Deduct inventory.
* Create coupon usage.
* Complete checkout session.
* Convert cart.

### 29.2. OrderNumberService

Trách nhiệm:

* Generate unique order number.
* Ensure no duplicate.
* Support prefix/date pattern if needed.

### 29.3. InventoryDeductionService

Trách nhiệm:

* Validate available stock.
* Deduct stock.
* Write inventory logs.
* Handle product-level or variant-level inventory.

### 29.4. CouponUsageService

Trách nhiệm:

* Validate final usage limit.
* Create coupon usage record.
* Increment coupon used_count.

---

## 30. Error Handling

| Scenario                    | Expected Result                  |
| --------------------------- | -------------------------------- |
| Checkout session missing    | Redirect checkout/cart           |
| Checkout session expired    | Show error                       |
| Checkout session completed  | Redirect existing order success  |
| Payment method missing      | Redirect payment step            |
| COD disabled after selected | Show payment unavailable         |
| Cart empty                  | Redirect cart                    |
| Product inactive            | Redirect cart                    |
| Variant inactive            | Redirect cart                    |
| Stock insufficient          | Redirect cart with stock message |
| Coupon expired before order | Show coupon invalid message      |
| Duplicate submit            | Return existing order            |
| Inventory update fails      | Rollback                         |
| Coupon usage fails          | Rollback                         |
| Database error              | Rollback and show general error  |

---

## 31. Test Cases

| Test Case ID | Scenario                                     | Expected Result                    |
| ------------ | -------------------------------------------- | ---------------------------------- |
| TC-001       | Guest place order with valid checkout COD    | Order created                      |
| TC-002       | Customer place order with valid checkout COD | Order created with user_id         |
| TC-003       | Place order without checkout session         | Redirect checkout/cart             |
| TC-004       | Place order with expired checkout session    | Error                              |
| TC-005       | Place order without payment method           | Redirect payment step              |
| TC-006       | Place order with empty cart                  | Error                              |
| TC-007       | Product inactive before place order          | Order not created                  |
| TC-008       | Variant inactive before place order          | Order not created                  |
| TC-009       | Stock insufficient before place order        | Order not created                  |
| TC-010       | Valid order creates order items              | Items created                      |
| TC-011       | Order item snapshots product name            | Snapshot correct                   |
| TC-012       | Order item snapshots variant options         | Snapshot correct                   |
| TC-013       | Order snapshots currency                     | Currency code/rate saved           |
| TC-014       | Order snapshots tax                          | Tax name/rate/amount saved         |
| TC-015       | Order snapshots coupon                       | Coupon data saved                  |
| TC-016       | Order snapshots COD payment                  | Payment row created                |
| TC-017       | COD order payment status                     | unpaid or pending                  |
| TC-018       | Order creation deducts inventory             | Stock reduced                      |
| TC-019       | Inventory log created                        | Log exists                         |
| TC-020       | Coupon usage created                         | Usage exists                       |
| TC-021       | Coupon used_count increased                  | used_count updated                 |
| TC-022       | Checkout session completed                   | Status completed                   |
| TC-023       | Cart converted                               | Cart status converted              |
| TC-024       | Header cart count after order                | Count 0                            |
| TC-025       | Double click place order                     | Only one order created             |
| TC-026       | Refresh after order created                  | Existing order shown               |
| TC-027       | Guest success page with token                | Guest can view                     |
| TC-028       | Wrong guest token                            | Access denied/not found            |
| TC-029       | Customer tries other user's order            | Forbidden                          |
| TC-030       | Task 19 does not implement admin management  | No admin order management required |
| TC-031       | Task 19 does not implement online payment    | No gateway transaction             |

---

## 32. Acceptance Criteria

Task này hoàn thành khi:

* [ ] Có bảng `orders`.
* [ ] Có bảng `order_items`.
* [ ] Có bảng `order_addresses`.
* [ ] Có bảng `order_payments`.
* [ ] Có bảng `order_tax_lines` nếu cần.
* [ ] Có bảng `order_status_histories` nếu chuẩn bị status history.
* [ ] Có route place order.
* [ ] Có order success page.
* [ ] Guest có thể tạo order từ checkout session hợp lệ.
* [ ] Customer có thể tạo order từ checkout session hợp lệ.
* [ ] Không cho tạo order nếu checkout session invalid/expired.
* [ ] Không cho tạo order nếu chưa chọn payment method.
* [ ] Không cho tạo order nếu cart rỗng.
* [ ] Không cho tạo order nếu product inactive.
* [ ] Không cho tạo order nếu variant inactive.
* [ ] Không cho tạo order nếu stock không đủ.
* [ ] Order number unique.
* [ ] Order snapshot customer contact.
* [ ] Order snapshot shipping address.
* [ ] Order snapshot billing address.
* [ ] Order snapshot currency code/rate.
* [ ] Order snapshot tax name/rate/amount.
* [ ] Order snapshot coupon nếu có.
* [ ] Order snapshot payment method COD.
* [ ] Order items snapshot product/variant/name/sku/options/image/price.
* [ ] Order total bằng checkout grand total.
* [ ] COD payment status ban đầu là unpaid hoặc pending.
* [ ] Inventory được deduct khi order tạo thành công.
* [ ] Inventory log được ghi khi deduct stock.
* [ ] Coupon usage được ghi khi order tạo thành công.
* [ ] Coupon used_count được tăng khi order tạo thành công.
* [ ] Checkout session chuyển completed.
* [ ] Cart chuyển converted.
* [ ] Header cart count về 0.
* [ ] Không tạo duplicate order khi submit nhiều lần.
* [ ] Place order chạy trong database transaction.
* [ ] Rollback nếu bất kỳ bước nào lỗi.
* [ ] Guest order success dùng token khó đoán.
* [ ] Không implement Admin Order Management trong Task 19.
* [ ] Không implement Online Payment trong Task 19.
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

`http://127.0.0.1:8000/checkout`

`http://127.0.0.1:8000/checkout/payment`

Order success URL sẽ phụ thuộc route được implement.

---

## 34. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-07-currency-management.md
* docs/tasks/task-08-tax-management.md
* docs/tasks/task-10-product-management-with-translation.md
* docs/tasks/task-10-1-product-options-and-variant-combinations.md
* docs/tasks/task-10-2-variant-images.md
* docs/tasks/task-12-inventory-management.md
* docs/tasks/task-15-cart.md
* docs/tasks/task-16-coupon.md
* docs/tasks/task-17-checkout-with-tax-currency-snapshot.md
* docs/tasks/task-18-payment-cod.md
* docs/tasks/task-19-order-creation.md

Sau đó implement Task 19: Order Creation theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 19.
* Tạo order chính thức từ checkout session hợp lệ.
* Không cho tạo order nếu checkout session invalid, expired hoặc không thuộc current user/session.
* Không cho tạo order nếu chưa chọn payment method.
* Không cho tạo order nếu cart rỗng, product inactive, variant inactive hoặc stock không đủ.
* Tạo orders, order_items, order_addresses, order_payments và các bảng liên quan nếu chưa có.
* Order number phải unique.
* Snapshot đầy đủ customer contact, shipping address, billing address.
* Snapshot đầy đủ product name, variant name, SKU, option values, image, price, quantity.
* Snapshot currency code, currency rate, currency symbol nếu có.
* Snapshot tax name, tax rate, tax amount.
* Snapshot coupon nếu có.
* Snapshot payment method COD từ checkout session.
* COD payment status ban đầu là unpaid hoặc pending.
* Tạo order trong database transaction.
* Deduct inventory khi order tạo thành công.
* Ghi inventory log khi deduct stock.
* Ghi coupon usage và tăng used_count khi order tạo thành công.
* Mark checkout session completed.
* Mark cart converted.
* Header cart count về 0 sau khi order thành công.
* Chống duplicate order khi customer bấm Place Order nhiều lần.
* Nếu checkout session đã completed và có order, redirect về order success.
* Tạo order success page.
* Guest order success phải dùng token khó đoán.
* Không implement Admin Order Management.
* Không implement Online Payment.
* Không implement refund/cancel/shipping management.
* Không dùng Vue.js.
* Dùng Blade + Tailwind CSS + Alpine.js hoặc Fetch API.
* Sau khi làm xong, báo cáo file đã tạo/sửa và lệnh cần chạy để test.
