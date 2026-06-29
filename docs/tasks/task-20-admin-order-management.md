# Task 20: Admin Order Management

## 1. Overview

Task này dùng để xây dựng chức năng quản lý đơn hàng trong Admin.

Sau Task 19, hệ thống đã có thể tạo order chính thức từ checkout session. Task 20 sẽ cho admin/staff quản lý các order đó.

Admin Order Management cần hỗ trợ:

* Xem danh sách đơn hàng.
* Tìm kiếm đơn hàng.
* Lọc đơn hàng theo trạng thái, payment, ngày tạo, customer.
* Xem chi tiết đơn hàng.
* Xem thông tin khách hàng.
* Xem địa chỉ giao hàng/thanh toán.
* Xem danh sách sản phẩm trong đơn.
* Xem payment method.
* Xem payment status.
* Cập nhật order status.
* Cập nhật payment status cho COD.
* Cập nhật fulfillment/shipping status cơ bản.
* Hủy đơn nếu được phép.
* Hoàn stock khi hủy đơn nếu cần.
* Ghi lịch sử thay đổi trạng thái.
* Ghi admin note.
* Không implement online payment trong task này.
* Không implement shipping carrier integration trong task này.

Frontend admin sử dụng:

* Laravel Blade
* Tailwind CSS
* Alpine.js nếu cần
* Fetch API nếu cần
* Không dùng Vue.js.

---

## 2. Objectives

Sau khi hoàn thành Task 20, hệ thống cần có:

* Admin có thể xem danh sách order.
* Admin có thể tìm kiếm order theo order number, customer name, email, phone.
* Admin có thể lọc order theo order status.
* Admin có thể lọc order theo payment status.
* Admin có thể lọc order theo fulfillment status.
* Admin có thể lọc order theo payment method.
* Admin có thể lọc order theo date range.
* Admin có thể xem chi tiết order.
* Admin có thể xem order items snapshot.
* Admin có thể xem currency/tax/coupon/payment snapshot.
* Admin có thể cập nhật order status hợp lệ.
* Admin có thể cập nhật payment status cho COD.
* Admin có thể cập nhật fulfillment status cơ bản.
* Admin có thể hủy order nếu order còn được hủy.
* Admin có thể restock khi hủy order nếu order đã deduct stock.
* Admin có thể thêm internal note.
* Hệ thống ghi order status history.
* Hệ thống ghi payment history nếu có.
* Hệ thống không làm mất snapshot order cũ.
* UI quản lý đơn hàng rõ ràng, chuyên nghiệp, responsive.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong Task 20:

* Admin order list.
* Admin order detail.
* Order filters.
* Order search.
* Order status update.
* Payment status update.
* Fulfillment status update cơ bản.
* Cancel order.
* Restock on cancel nếu cần.
* Order internal notes.
* Order status histories.
* Basic order timeline.
* Basic print order page nếu đơn giản.
* Permission check cho admin/staff.
* Confirmation modal cho danger action.
* Loading state cho update bằng JavaScript nếu có.

### 3.2. Out of Scope

Không làm trong Task 20:

* Không implement public customer order history nâng cao.
* Không implement online payment gateway.
* Không implement refund online.
* Không implement partial refund.
* Không implement shipping carrier integration.
* Không implement shipment tracking API.
* Không implement invoice PDF nâng cao.
* Không implement email notification nâng cao.
* Không implement report/dashboard nâng cao.
* Không implement review product.
* Không dùng Vue.js.

---

## 4. Dependencies

Task này phụ thuộc:

| Task    | Dependency           |
| ------- | -------------------- |
| Task 04 | Admin Layout         |
| Task 12 | Inventory Management |
| Task 18 | Payment COD          |
| Task 19 | Order Creation       |

Task sau có thể dùng dữ liệu từ Task 20:

| Task    | Purpose         |
| ------- | --------------- |
| Task 21 | Admin Dashboard |
| Task 23 | Report          |
| Task 24 | Online Payment  |
| Task 25 | Review Product  |

---

## 5. User Roles

| Role     | Permission                                       |
| -------- | ------------------------------------------------ |
| Admin    | Toàn quyền quản lý đơn hàng                      |
| Staff    | Có thể xem/cập nhật đơn hàng nếu được phân quyền |
| Customer | Không truy cập admin order management            |
| Guest    | Không truy cập admin order management            |

Business rules:

* Chỉ admin/staff được truy cập admin order routes.
* Customer/guest bị chặn.
* Nếu hệ thống chưa có permission chi tiết, dùng admin middleware hiện tại.
* Sau này có thể mở rộng permission theo role.

---

## 6. Admin Order List

URL đề xuất:

`/admin/orders`

Order list cần hiển thị bảng danh sách order.

### 6.1. Columns

Các cột đề xuất:

| Column             | Description                       |
| ------------------ | --------------------------------- |
| Order Number       | Mã đơn                            |
| Customer           | Tên khách hàng                    |
| Email / Phone      | Thông tin liên hệ                 |
| Grand Total        | Tổng tiền                         |
| Payment Method     | COD hoặc method khác              |
| Payment Status     | unpaid, pending, paid             |
| Order Status       | pending, confirmed, processing... |
| Fulfillment Status | unfulfilled, shipped...           |
| Ordered At         | Ngày đặt                          |
| Actions            | View, Update, More                |

### 6.2. Search

Admin có thể search theo:

* Order number.
* Customer name.
* Customer email.
* Customer phone.
* SKU sản phẩm nếu làm được.
* Product name snapshot nếu làm được.

### 6.3. Filters

Filters cần có:

| Filter             | Values                                               |
| ------------------ | ---------------------------------------------------- |
| Order Status       | pending, confirmed, processing, completed, cancelled |
| Payment Status     | unpaid, pending, paid, failed, refunded              |
| Fulfillment Status | unfulfilled, processing, shipped, delivered          |
| Payment Method     | cod, online nếu có sau này                           |
| Date Range         | ordered_at from/to                                   |
| Customer Type      | guest, customer                                      |
| Coupon Used        | yes/no nếu có                                        |
| Min/Max Total      | Optional                                             |

### 6.4. Sorting

Sorting đề xuất:

* Newest first mặc định.
* Oldest first.
* Total high to low.
* Total low to high.

### 6.5. Pagination

Yêu cầu:

* Danh sách order phải có pagination.
* Không load toàn bộ orders một lần.
* Filter/search phải giữ query khi chuyển trang.

---

## 7. Admin Order Detail

URL đề xuất:

`/admin/orders/{order}`

Order detail cần hiển thị đầy đủ thông tin order snapshot.

### 7.1. Main Sections

Order detail gồm:

| Section          | Description                                    |
| ---------------- | ---------------------------------------------- |
| Order Header     | Order number, status, ordered time             |
| Customer Info    | Name, email, phone                             |
| Shipping Address | Địa chỉ giao hàng                              |
| Billing Address  | Địa chỉ thanh toán                             |
| Order Items      | Sản phẩm trong đơn                             |
| Payment Info     | COD/payment status                             |
| Coupon Info      | Coupon snapshot nếu có                         |
| Tax Summary      | Tax lines                                      |
| Order Summary    | Subtotal, discount, tax, shipping, grand total |
| Status Actions   | Update status/payment/fulfillment              |
| Internal Notes   | Ghi chú nội bộ                                 |
| Timeline         | Lịch sử đơn hàng                               |

---

## 8. Order Detail Header

Header của order detail cần hiển thị:

| Field              | Description        |
| ------------------ | ------------------ |
| Order Number       | Mã đơn             |
| Order Status       | Status hiện tại    |
| Payment Status     | Payment status     |
| Fulfillment Status | Fulfillment status |
| Ordered At         | Ngày đặt           |
| Customer Type      | Guest / Customer   |
| Grand Total        | Tổng tiền          |
| Back To List       | Quay lại danh sách |

Action chính:

* Update Status.
* Mark as Paid nếu COD.
* Cancel Order nếu được phép.
* Print Order nếu có.
* More Actions.

Danger action như Cancel không đặt sát action chính.

---

## 9. Order Items Display

Order items phải hiển thị snapshot, không phụ thuộc product hiện tại.

Fields:

| Field        | Description                   |
| ------------ | ----------------------------- |
| Image        | Image snapshot                |
| Product Name | Product name snapshot         |
| Variant      | Variant name/options snapshot |
| SKU          | SKU snapshot                  |
| Unit Price   | Unit price snapshot           |
| Quantity     | Quantity                      |
| Subtotal     | Item subtotal                 |
| Discount     | Item discount                 |
| Tax          | Item tax                      |
| Total        | Item total                    |

Business rules:

* Nếu product đã bị xóa hoặc đổi tên, order vẫn hiển thị snapshot cũ.
* Nếu image path không còn tồn tại, fallback placeholder.
* Không tự tính lại giá từ product hiện tại.
* Không cho sửa order item trong MVP.

---

## 10. Order Summary Display

Order summary hiển thị:

| Line           | Description      |
| -------------- | ---------------- |
| Subtotal       | Tổng tiền hàng   |
| Discount       | Coupon discount  |
| Taxable Amount | Amount chịu thuế |
| Tax            | Tổng tax         |
| Shipping       | Shipping amount  |
| Grand Total    | Tổng cuối        |

Business rules:

* Tất cả amount lấy từ order snapshot.
* Format theo currency snapshot của order.
* Không dùng currency hiện tại để thay đổi order cũ.
* Không tính lại tax/coupon ở admin detail.

---

## 11. Payment Management

Task 20 cần hỗ trợ quản lý payment status cơ bản, đặc biệt COD.

### 11.1. Payment Info

Order detail hiển thị:

| Field               | Description                 |
| ------------------- | --------------------------- |
| Payment Method      | COD                         |
| Payment Method Name | Snapshot name               |
| Payment Status      | unpaid/pending/paid         |
| Payment Amount      | Amount                      |
| Currency            | Currency                    |
| Paid At             | Thời điểm thanh toán nếu có |
| Instruction         | COD instruction             |

### 11.2. COD Mark As Paid

Với COD, admin có thể xác nhận đã thu tiền.

Expected behavior:

* Admin click `Mark as Paid`.
* Hiển thị confirmation modal.
* Sau confirm, update payment status thành `paid`.
* Set `paid_at`.
* Update order payment status thành `paid`.
* Ghi status history/payment history.
* Không reload page nếu dùng AJAX.
* Hiển thị toast success.

Business rules:

* Chỉ cho mark as paid nếu payment method là COD.
* Chỉ cho mark as paid nếu payment status chưa paid.
* Không cho mark as paid nếu order đã cancelled.
* Không set paid cho order đã refund/cancelled.

### 11.3. Payment Status Update

Admin có thể cập nhật payment status nếu cần.

Allowed statuses:

* unpaid.
* pending.
* paid.
* failed.
* refunded.
* cancelled.

Business rules:

* Không cho chuyển status tùy tiện nếu không hợp lệ.
* COD thường chỉ chuyển unpaid/pending -> paid.
* Online payment sau này sẽ có rules riêng ở Task 24.

---

## 12. Order Status Management

Admin cần cập nhật order status.

### 12.1. Order Statuses

Status đề xuất:

| Status     | Description       |
| ---------- | ----------------- |
| pending    | Đơn mới tạo       |
| confirmed  | Admin đã xác nhận |
| processing | Đang xử lý        |
| completed  | Hoàn tất          |
| cancelled  | Đã hủy            |

Nếu muốn thêm shipping flow:

| Status    | Description         |
| --------- | ------------------- |
| shipped   | Đã giao cho shipper |
| delivered | Đã giao tới khách   |

MVP có thể dùng:

* pending.
* confirmed.
* processing.
* completed.
* cancelled.

### 12.2. Allowed Transitions

Business rules đề xuất:

| From       | To                                            |
| ---------- | --------------------------------------------- |
| pending    | confirmed, cancelled                          |
| confirmed  | processing, cancelled                         |
| processing | completed, cancelled                          |
| completed  | Không chuyển về trạng thái thấp hơn trong MVP |
| cancelled  | Không chuyển trạng thái khác trong MVP        |

Nếu có shipped/delivered:

| From       | To        |
| ---------- | --------- |
| processing | shipped   |
| shipped    | delivered |
| delivered  | completed |

### 12.3. Update Status UX

Expected behavior:

* Admin click Update Status.
* Có modal hoặc dropdown chọn status mới.
* Admin có thể nhập note.
* Submit bằng JavaScript nếu có.
* Không reload page nếu AJAX.
* Status badge cập nhật ngay.
* Timeline cập nhật ngay.
* Toast success.

---

## 13. Fulfillment Status Management

Fulfillment status dùng để quản lý trạng thái giao hàng cơ bản.

Statuses đề xuất:

| Status      | Description          |
| ----------- | -------------------- |
| unfulfilled | Chưa xử lý giao hàng |
| processing  | Đang chuẩn bị        |
| shipped     | Đã giao cho shipper  |
| delivered   | Đã giao              |
| cancelled   | Hủy giao hàng        |

Trong MVP:

* Có thể cập nhật đơn giản bằng dropdown.
* Không tích hợp shipping carrier.
* Không tạo tracking code bắt buộc.
* Tracking code có thể để optional.

Fields optional:

| Field            | Description       |
| ---------------- | ----------------- |
| tracking_number  | Mã vận đơn        |
| shipping_carrier | Đơn vị vận chuyển |
| shipped_at       | Ngày gửi          |
| delivered_at     | Ngày giao         |

Nếu chưa muốn shipping fields trong Task 20, có thể để fulfillment status cơ bản.

---

## 14. Cancel Order

Admin có thể hủy order nếu order còn được phép hủy.

### 14.1. Cancel Rules

Có thể cancel nếu:

* Order status là pending.
* Order status là confirmed.
* Order status là processing nếu business cho phép.
* Order chưa completed.
* Order chưa cancelled.

Không cho cancel nếu:

* Order đã completed.
* Order đã delivered/completed.
* Order đã cancelled.
* Có rule business không cho hủy.

### 14.2. Cancel Modal

Cancel là danger action, cần custom modal.

Modal cần có:

| Element          | Description                        |
| ---------------- | ---------------------------------- |
| Title            | Cancel Order                       |
| Message          | Xác nhận hủy đơn                   |
| Warning          | Cảnh báo ảnh hưởng tồn kho/payment |
| Reason           | Lý do hủy                          |
| Restock Checkbox | Có hoàn lại tồn kho không          |
| Cancel Button    | Đóng modal                         |
| Confirm Cancel   | Xác nhận hủy                       |

Không dùng browser confirm mặc định.

### 14.3. Cancel Behavior

Khi hủy order:

* Update order_status = cancelled.
* Update fulfillment_status = cancelled nếu phù hợp.
* Nếu payment chưa paid, payment_status có thể giữ unpaid hoặc cancelled.
* Nếu payment đã paid, không tự refund trong Task 20.
* Nếu restock enabled, hoàn lại tồn kho.
* Ghi inventory log type `order_cancelled` nếu restock.
* Ghi order status history.
* Ghi internal note với lý do hủy.
* Không xóa order.

### 14.4. Restock Rules

Nếu order đã deduct stock ở Task 19:

* Hủy order có thể hoàn lại stock.
* Chỉ restock một lần.
* Cần tránh double restock nếu admin bấm nhiều lần.
* Cần ghi log.

Fields có thể thêm vào orders:

| Field            | Description           |
| ---------------- | --------------------- |
| restocked_at     | Thời điểm đã hoàn kho |
| cancelled_reason | Lý do hủy             |
| cancelled_by     | Admin hủy             |

Nếu chưa có fields này, có thể dùng status history/internal notes.

---

## 15. Internal Notes

Admin cần có ghi chú nội bộ cho order.

### 15.1. order_notes Table

Tạo bảng `order_notes` nếu chưa có.

Fields đề xuất:

| Field                  | Type            | Description              |
| ---------------------- | --------------- | ------------------------ |
| id                     | bigint          | Primary key              |
| order_id               | bigint          | Reference order          |
| user_id                | nullable bigint | Admin/staff tạo note     |
| type                   | string          | internal/customer/system |
| note                   | text            | Nội dung note            |
| is_visible_to_customer | boolean         | MVP false                |
| created_at             | timestamp       | Created time             |
| updated_at             | timestamp       | Updated time             |

Business rules:

* MVP chỉ cần internal notes.
* Internal note không hiển thị public cho customer.
* System note có thể dùng cho status changes.

### 15.2. Note UX

Expected behavior:

* Admin nhập note.
* Submit.
* Note xuất hiện trong timeline hoặc notes section.
* Không reload page nếu dùng AJAX.
* Không cho note trống.

---

## 16. Order Timeline / History

Order detail cần timeline để theo dõi thay đổi.

Timeline có thể hiển thị từ:

* order_status_histories.
* order_notes.
* payment status changes.
* inventory restock events.

### 16.1. Timeline Items

Hiển thị:

| Item            | Example                   |
| --------------- | ------------------------- |
| Order created   | Order created by customer |
| Status changed  | pending -> confirmed      |
| Payment updated | unpaid -> paid            |
| Order cancelled | Cancelled by admin        |
| Stock restocked | Inventory restored        |
| Internal note   | Admin note                |

### 16.2. Status History

Nếu Task 19 đã tạo `order_status_histories`, Task 20 dùng lại.

Fields cần có:

* from_status.
* to_status.
* note.
* changed_by.
* changed_by_type.
* created_at.

---

## 17. Database Updates

Task 19 đã tạo các bảng order chính. Task 20 có thể cần bổ sung.

### 17.1. orders Table Updates

Fields optional:

| Field            | Description                     |
| ---------------- | ------------------------------- |
| cancelled_reason | Lý do hủy                       |
| cancelled_by     | Admin ID                        |
| restocked_at     | Đã hoàn stock lúc nào           |
| tracking_number  | Mã tracking nếu làm fulfillment |
| shipping_carrier | Carrier nếu có                  |
| shipped_at       | Thời điểm shipped               |
| delivered_at     | Thời điểm delivered             |

Chỉ thêm nếu cần trong MVP.

### 17.2. order_notes Table

Khuyến nghị tạo bảng này trong Task 20.

### 17.3. order_payment_histories Table Optional

Nếu muốn tracking payment status riêng:

| Field       | Description |
| ----------- | ----------- |
| order_id    | Order       |
| from_status | Status cũ   |
| to_status   | Status mới  |
| note        | Ghi chú     |
| changed_by  | Admin       |
| created_at  | Time        |

MVP có thể dùng order_status_histories hoặc order_notes để ghi payment changes.

---

## 18. Admin Routes

Routes đề xuất:

| Method | URL                                      | Name                                   | Description               |
| ------ | ---------------------------------------- | -------------------------------------- | ------------------------- |
| GET    | /admin/orders                            | admin.orders.index                     | Order list                |
| GET    | /admin/orders/{order}                    | admin.orders.show                      | Order detail              |
| PATCH  | /admin/orders/{order}/status             | admin.orders.status.update             | Update order status       |
| PATCH  | /admin/orders/{order}/payment-status     | admin.orders.payment-status.update     | Update payment status     |
| PATCH  | /admin/orders/{order}/fulfillment-status | admin.orders.fulfillment-status.update | Update fulfillment status |
| POST   | /admin/orders/{order}/cancel             | admin.orders.cancel                    | Cancel order              |
| POST   | /admin/orders/{order}/notes              | admin.orders.notes.store               | Add internal note         |
| GET    | /admin/orders/{order}/print              | admin.orders.print                     | Print order optional      |

Business rules:

* All admin routes require admin authentication.
* Update routes cần CSRF.
* AJAX request trả JSON.
* GET routes trả Blade view.

---

## 19. Validation Rules

### 19.1. Update Order Status

| Field      | Rule                         |
| ---------- | ---------------------------- |
| status     | Required, valid order status |
| note       | Nullable, max length         |
| order      | Must exist                   |
| transition | Must be allowed              |

### 19.2. Update Payment Status

| Field          | Rule                           |
| -------------- | ------------------------------ |
| payment_status | Required, valid payment status |
| note           | Nullable                       |
| order          | Must exist                     |
| transition     | Must be allowed                |

### 19.3. Cancel Order

| Field   | Rule                                   |
| ------- | -------------------------------------- |
| reason  | Required or nullable based on business |
| restock | Boolean                                |
| order   | Must be cancellable                    |

### 19.4. Add Note

| Field | Rule                 |
| ----- | -------------------- |
| note  | Required, max length |
| type  | internal by default  |

---

## 20. API Response Requirements

AJAX response thành công:

| Field              | Description                |
| ------------------ | -------------------------- |
| success            | true                       |
| message            | Success message            |
| order_status       | Current order status       |
| payment_status     | Current payment status     |
| fulfillment_status | Current fulfillment status |
| html               | Optional updated partial   |
| timeline_html      | Optional updated timeline  |

AJAX response lỗi:

| Field   | Description              |
| ------- | ------------------------ |
| success | false                    |
| message | Error message            |
| errors  | Validation errors nếu có |

Business rules:

* AJAX không trả HTML error page.
* AJAX không redirect nếu không cần.
* Modal lỗi không tự đóng.
* UI cập nhật ngay khi success.

---

## 21. UI / UX Requirements

### 21.1. Admin Order List UX

Yêu cầu:

* Table rõ ràng, dễ scan.
* Status badge màu sắc rõ.
* Filter nằm phía trên table.
* Search dễ thấy.
* Pagination rõ.
* Row action gọn.
* Không quá nhiều button trên mỗi row.
* Action chính là View.
* Các action ít dùng đưa vào More Actions.

### 21.2. Order Detail UX

Yêu cầu:

* Header rõ trạng thái.
* Summary amount nổi bật.
* Customer/address/order items chia card.
* Status actions nằm bên phải hoặc action bar.
* Timeline dễ đọc.
* Danger action như Cancel nằm trong More Actions hoặc khu vực riêng.
* Không đặt Cancel sát các action thường.
* Mobile layout không vỡ.

### 21.3. Modal UX

Các action nguy hiểm hoặc quan trọng cần modal:

* Cancel Order.
* Mark as Paid.
* Update status nếu cần confirmation.
* Restock confirmation.

Modal cần:

* Title rõ.
* Message rõ.
* Warning nếu ảnh hưởng stock/payment.
* Cancel button.
* Confirm button.
* Loading state.
* Không dùng browser confirm mặc định.

### 21.4. Loading State

Khi update:

* Button bị disabled.
* Text chuyển thành Processing/Saving.
* Không cho bấm nhiều lần.
* Modal không đóng khi request đang xử lý.
* Success thì modal đóng và UI cập nhật.
* Error thì modal giữ nguyên và hiển thị lỗi.

---

## 22. Status Badge Design

Badge cần nhất quán.

Order status:

| Status     | Suggested Display |
| ---------- | ----------------- |
| pending    | Pending           |
| confirmed  | Confirmed         |
| processing | Processing        |
| completed  | Completed         |
| cancelled  | Cancelled         |

Payment status:

| Status    | Suggested Display |
| --------- | ----------------- |
| unpaid    | Unpaid            |
| pending   | Pending           |
| paid      | Paid              |
| failed    | Failed            |
| refunded  | Refunded          |
| cancelled | Cancelled         |

Fulfillment status:

| Status      | Suggested Display |
| ----------- | ----------------- |
| unfulfilled | Unfulfilled       |
| processing  | Processing        |
| shipped     | Shipped           |
| delivered   | Delivered         |
| cancelled   | Cancelled         |

---

## 23. Business Logic

### 23.1. Update Order Status Flow

Flow:

1. Admin chọn status mới.
2. Backend validate allowed transition.
3. Update order status.
4. Create status history.
5. Create system note nếu cần.
6. Return JSON.
7. UI cập nhật badge/timeline.

### 23.2. Mark COD As Paid Flow

Flow:

1. Admin click Mark as Paid.
2. Modal confirmation mở.
3. Admin confirm.
4. Backend validate order/payment.
5. Update payment status = paid.
6. Set paid_at.
7. Update order payment row.
8. Create history/note.
9. Return JSON.
10. UI cập nhật payment badge.

### 23.3. Cancel Order Flow

Flow:

1. Admin click Cancel Order.
2. Modal mở.
3. Admin nhập reason.
4. Admin chọn restock nếu cần.
5. Backend validate cancellable.
6. Start transaction.
7. Update order status cancelled.
8. Update fulfillment status cancelled nếu cần.
9. Update payment status nếu phù hợp.
10. Restock inventory nếu checked.
11. Create inventory logs.
12. Create status history.
13. Create internal note.
14. Commit.
15. Return JSON.
16. UI cập nhật.

### 23.4. Add Internal Note Flow

Flow:

1. Admin nhập note.
2. Submit.
3. Backend validate.
4. Create order note.
5. Return JSON.
6. UI thêm note vào notes/timeline.

---

## 24. Inventory Restock Rules

Restock khi hủy order cần cẩn thận.

Business rules:

* Chỉ restock nếu order đã deduct stock.
* Không restock nếu đã restocked trước đó.
* Restock phải chạy trong transaction.
* Ghi inventory log.
* Product không variant thì restock product inventory.
* Product có variant thì restock variant inventory.
* Nếu inventory record không tồn tại, hiển thị lỗi rõ hoặc tạo record tùy Task 12 rule.
* Không tự restock khi order completed.

---

## 25. Security Requirements

Yêu cầu bảo mật:

* Admin auth cho toàn bộ routes.
* CSRF cho POST/PATCH.
* Validate allowed transitions ở backend.
* Không tin status từ frontend.
* Không cho staff không quyền update nếu có permission.
* Không cho customer truy cập admin routes.
* Không expose lỗi kỹ thuật.
* Danger action cần confirmation.
* Restock không được chạy nhiều lần.
* Payment paid không được set nhiều lần ngoài ý muốn.

---

## 26. Performance Requirements

* Eager load order items, addresses, payments, notes, histories.
* Order list dùng pagination.
* Search/filter cần index nếu dữ liệu lớn.
* Không load timeline quá nặng nếu nhiều dữ liệu.
* Detail page không query lặp trong loop.
* AJAX response trả partial cần thiết, không render toàn page nếu không cần.

---

## 27. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type               | Description                                                                                     |
| ------------------ | ----------------------------------------------------------------------------------------------- |
| Migration          | order_notes, optional fields for orders                                                         |
| Models             | OrderNote, update Order relationships                                                           |
| Controller         | AdminOrderController                                                                            |
| Controller         | AdminOrderStatusController                                                                      |
| Controller         | AdminOrderPaymentController                                                                     |
| Controller         | AdminOrderNoteController                                                                        |
| Service            | OrderStatusService                                                                              |
| Service            | OrderPaymentService                                                                             |
| Service            | OrderCancellationService                                                                        |
| Service            | OrderRestockService                                                                             |
| Request Validation | UpdateOrderStatusRequest, UpdatePaymentStatusRequest, CancelOrderRequest, StoreOrderNoteRequest |
| Routes             | Admin order routes                                                                              |
| Blade Views        | admin orders index/show/print                                                                   |
| Blade Partials     | status badges, order items, timeline, notes                                                     |
| JavaScript         | AJAX status/payment/cancel/note handlers                                                        |
| Tests              | Admin order management feature tests                                                            |

---

## 28. Error Handling

| Scenario                       | Expected Result           |
| ------------------------------ | ------------------------- |
| Order not found                | 404                       |
| Customer access admin order    | Forbidden                 |
| Invalid status transition      | Error message             |
| Mark paid on cancelled order   | Error message             |
| Mark paid when already paid    | Error or no-op            |
| Cancel completed order         | Error message             |
| Cancel already cancelled order | Error message             |
| Restock already done           | Prevent duplicate restock |
| Inventory restock fails        | Rollback                  |
| Note empty                     | Validation error          |
| AJAX network error             | Show retry message        |
| Server error                   | Show general error        |

---

## 29. Test Cases

| Test Case ID | Scenario                          | Expected Result            |
| ------------ | --------------------------------- | -------------------------- |
| TC-001       | Admin mở order list               | Hiển thị danh sách order   |
| TC-002       | Admin search order number         | Kết quả đúng               |
| TC-003       | Admin filter order status         | Kết quả đúng               |
| TC-004       | Admin filter payment status       | Kết quả đúng               |
| TC-005       | Admin filter date range           | Kết quả đúng               |
| TC-006       | Admin mở order detail             | Hiển thị đầy đủ snapshot   |
| TC-007       | Order detail hiển thị items       | Items snapshot đúng        |
| TC-008       | Order detail hiển thị address     | Address snapshot đúng      |
| TC-009       | Order detail hiển thị payment COD | Payment info đúng          |
| TC-010       | Order detail hiển thị coupon      | Coupon snapshot đúng       |
| TC-011       | Admin update pending -> confirmed | Status update thành công   |
| TC-012       | Admin update invalid transition   | Bị chặn                    |
| TC-013       | Admin mark COD as paid            | Payment status = paid      |
| TC-014       | Mark paid order cancelled         | Bị chặn                    |
| TC-015       | Admin cancel pending order        | Order cancelled            |
| TC-016       | Cancel completed order            | Bị chặn                    |
| TC-017       | Cancel order with restock         | Stock được hoàn            |
| TC-018       | Restock duplicate                 | Không restock lần 2        |
| TC-019       | Cancel creates status history     | History created            |
| TC-020       | Mark paid creates history/note    | History/note created       |
| TC-021       | Admin add internal note           | Note created               |
| TC-022       | Empty note                        | Validation error           |
| TC-023       | AJAX update status                | Không reload page nếu AJAX |
| TC-024       | AJAX cancel                       | Không reload page nếu AJAX |
| TC-025       | Customer access admin orders      | Forbidden                  |
| TC-026       | Mobile order detail               | Layout không vỡ            |
| TC-027       | Order list pagination             | Hoạt động đúng             |
| TC-028       | Print order optional              | Hiển thị print view nếu có |

---

## 30. Acceptance Criteria

Task này hoàn thành khi:

* [ ] Có admin order list page.
* [ ] Có admin order detail page.
* [ ] Order list có search.
* [ ] Order list có filter theo order status.
* [ ] Order list có filter theo payment status.
* [ ] Order list có filter theo date range.
* [ ] Order list có pagination.
* [ ] Order detail hiển thị customer info.
* [ ] Order detail hiển thị shipping/billing address.
* [ ] Order detail hiển thị order items snapshot.
* [ ] Order detail hiển thị subtotal/discount/tax/shipping/grand total.
* [ ] Order detail hiển thị currency snapshot đúng.
* [ ] Order detail hiển thị payment method COD.
* [ ] Order detail hiển thị payment status.
* [ ] Admin có thể cập nhật order status hợp lệ.
* [ ] Invalid status transition bị chặn.
* [ ] Admin có thể mark COD as paid.
* [ ] Mark as paid set paid_at.
* [ ] Admin có thể update fulfillment status cơ bản nếu implement.
* [ ] Admin có thể cancel order nếu order cancellable.
* [ ] Cancel order cần confirmation modal.
* [ ] Không dùng browser confirm mặc định.
* [ ] Cancel order có thể restock nếu cần.
* [ ] Restock không chạy trùng.
* [ ] Inventory log được ghi khi restock.
* [ ] Order status history được ghi khi status thay đổi.
* [ ] Payment status change được ghi history/note.
* [ ] Admin có thể thêm internal note.
* [ ] Timeline hiển thị status changes và notes.
* [ ] Danger actions không đặt sát action chính.
* [ ] UI có loading state khi update.
* [ ] AJAX update không reload page nếu dùng JavaScript.
* [ ] Admin routes được bảo vệ bởi auth/admin middleware.
* [ ] Customer/guest không truy cập được admin orders.
* [ ] Không implement Online Payment trong Task 20.
* [ ] Không implement Report/Dashboard trong Task 20.
* [ ] Không dùng Vue.js.

---

## 31. Commands

Sau khi implement, chạy các lệnh:

| Command                | Purpose               |
| ---------------------- | --------------------- |
| php artisan migrate    | Chạy migration nếu có |
| php artisan route:list | Kiểm tra route        |
| php artisan test       | Chạy test nếu có      |
| npm run build          | Build frontend assets |
| php artisan serve      | Chạy local server     |

URL test:

`http://127.0.0.1:8000/admin/orders`

`http://127.0.0.1:8000/admin/orders/{order}`

---

## 32. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-04-admin-layout.md
* docs/tasks/task-12-inventory-management.md
* docs/tasks/task-18-payment-cod.md
* docs/tasks/task-19-order-creation.md
* docs/tasks/task-20-admin-order-management.md

Sau đó implement Task 20: Admin Order Management theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 20.
* Tạo admin order list page.
* Tạo admin order detail page.
* Order list có search, filter và pagination.
* Order detail hiển thị snapshot đầy đủ: customer, address, items, currency, tax, coupon, payment, total.
* Admin có thể cập nhật order status theo transition hợp lệ.
* Admin có thể cập nhật payment status cho COD.
* Admin có thể mark COD as paid.
* Admin có thể cancel order nếu order còn được hủy.
* Cancel order phải dùng custom confirmation modal, không dùng browser confirm mặc định.
* Cancel order có thể restock nếu cần.
* Không cho restock trùng.
* Ghi inventory log khi restock.
* Ghi order status history khi status thay đổi.
* Admin có thể thêm internal note.
* Timeline hiển thị status history và notes.
* Danger actions không đặt sát action chính.
* Có loading state và error message rõ ràng.
* AJAX update không reload page nếu dùng JavaScript.
* Admin routes phải được bảo vệ bởi admin/auth middleware.
* Không implement Online Payment.
* Không implement Report/Dashboard.
* Không implement Customer Order History nâng cao.
* Không dùng Vue.js.
* Dùng Blade + Tailwind CSS + Alpine.js hoặc Fetch API.
* Sau khi làm xong, báo cáo file đã tạo/sửa và lệnh cần chạy để test.
