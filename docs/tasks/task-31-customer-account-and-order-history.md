# Task 31: Customer Account and Order History

## 1. Overview

Task này dùng để xây dựng khu vực tài khoản khách hàng cho e-commerce system.

Sau khi hệ thống đã có:

* Authentication.
* Cart.
* Checkout.
* Order Creation.
* Payment COD.
* Online Payment.
* Admin Order Management.
* Email Notification.

Task 31 sẽ bổ sung khu vực để customer tự quản lý thông tin cá nhân và xem lịch sử đơn hàng.

Customer Account cần hỗ trợ:

* Customer dashboard.
* Xem thông tin tài khoản.
* Cập nhật profile cơ bản.
* Đổi mật khẩu.
* Quản lý địa chỉ giao hàng.
* Xem lịch sử đơn hàng.
* Xem chi tiết đơn hàng.
* Xem trạng thái đơn hàng.
* Xem trạng thái thanh toán.
* Xem thông tin payment method.
* Xem sản phẩm đã mua.
* Xem order total snapshot.
* Guest order lookup cơ bản nếu cần.
* Không cho customer xem order của người khác.

Frontend public sử dụng:

* Laravel Blade.
* Tailwind CSS.
* Alpine.js nếu cần.
* Fetch API nếu cần.
* Không dùng Vue.js.

---

## 2. Objectives

Sau khi hoàn thành Task 31, hệ thống cần đạt:

* Customer có khu vực tài khoản riêng.
* Customer có thể xem dashboard tài khoản.
* Customer có thể cập nhật profile cơ bản.
* Customer có thể đổi mật khẩu.
* Customer có thể quản lý danh sách địa chỉ.
* Customer có thể đặt địa chỉ mặc định.
* Customer có thể xem danh sách order của mình.
* Customer có thể xem chi tiết order của mình.
* Customer có thể xem order items snapshot.
* Customer có thể xem payment status.
* Customer có thể xem order status.
* Customer có thể xem shipping/billing address snapshot.
* Customer có thể xem payment transaction cơ bản nếu có.
* Guest có thể xem order bằng guest token nếu hệ thống đã có guest order token.
* Không cho customer xem order của user khác.
* Không cho guest xem order nếu token sai.
* UI responsive và đồng bộ với public frontend.
* Không implement Review Product trong Task 31.
* Không implement Refund/Return nâng cao trong Task 31.
* Không dùng Vue.js.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong Task 31:

* Customer account layout.
* Customer account dashboard.
* Customer profile page.
* Update profile.
* Change password.
* Customer address management.
* Default shipping address.
* Default billing address nếu cần.
* Customer order history.
* Customer order detail.
* Guest order lookup cơ bản nếu phù hợp.
* Order access authorization.
* Order status display.
* Payment status display.
* Payment method display.
* Order item snapshot display.
* Responsive UI.
* Empty state.
* Validation.
* Security tests.

### 3.2. Out of Scope

Không làm trong Task 31:

* Không implement Review Product.
* Không implement review reminder.
* Không implement wishlist.
* Không implement loyalty points.
* Không implement refund request nâng cao.
* Không implement return request nâng cao.
* Không implement reorder nâng cao.
* Không implement customer notification preferences nâng cao.
* Không implement social login.
* Không implement two-factor authentication.
* Không implement customer deletion/GDPR nâng cao.
* Không implement shipping carrier tracking API.
* Không implement invoice PDF.
* Không dùng Vue.js.

---

## 4. Dependencies

Task này phụ thuộc:

| Task    | Dependency             |
| ------- | ---------------------- |
| Task 03 | Authentication         |
| Task 13 | Public Product Catalog |
| Task 14 | Product Detail Page    |
| Task 15 | Cart                   |
| Task 17 | Checkout               |
| Task 19 | Order Creation         |
| Task 20 | Admin Order Management |
| Task 24 | Online Payment         |
| Task 27 | Security Hardening     |
| Task 30 | Email Notification     |

---

## 5. User Roles

| Role     | Permission                                          |
| -------- | --------------------------------------------------- |
| Guest    | Có thể xem guest order bằng token hợp lệ nếu hỗ trợ |
| Customer | Quản lý tài khoản và xem order của chính mình       |
| Admin    | Không dùng customer account area, dùng admin area   |
| Staff    | Không dùng customer account area, dùng admin area   |

Business rules:

* Customer chỉ xem được dữ liệu của chính mình.
* Guest chỉ xem được order bằng guest token hợp lệ.
* Admin không cần dùng customer account routes.
* Customer account routes phải yêu cầu customer login, ngoại trừ guest order lookup nếu có.

---

## 6. Customer Account Pages

Các trang đề xuất:

| Page               | URL                               | Purpose                    |
| ------------------ | --------------------------------- | -------------------------- |
| Account Dashboard  | /account                          | Tổng quan tài khoản        |
| Profile            | /account/profile                  | Xem/cập nhật thông tin     |
| Change Password    | /account/password                 | Đổi mật khẩu               |
| Addresses          | /account/addresses                | Quản lý địa chỉ            |
| Create Address     | /account/addresses/create         | Tạo địa chỉ                |
| Edit Address       | /account/addresses/{address}/edit | Sửa địa chỉ                |
| Orders             | /account/orders                   | Lịch sử đơn hàng           |
| Order Detail       | /account/orders/{order}           | Chi tiết đơn hàng          |
| Guest Order Lookup | /guest-orders/lookup              | Optional tra cứu đơn guest |
| Guest Order Detail | /guest-orders/{guest_token}       | Optional xem đơn guest     |

---

## 7. Customer Account Layout

Customer account area cần có layout riêng hoặc dùng public layout với sidebar account.

### 7.1. Desktop Layout

Desktop nên có:

* Header public.
* Account sidebar.
* Main content.
* Breadcrumb nếu có.

Sidebar items:

| Item            | URL                |
| --------------- | ------------------ |
| Dashboard       | /account           |
| Profile         | /account/profile   |
| Addresses       | /account/addresses |
| Orders          | /account/orders    |
| Change Password | /account/password  |
| Logout          | POST /logout       |

### 7.2. Mobile Layout

Mobile nên có:

* Account menu dạng dropdown hoặc collapsible.
* Cards xếp dọc.
* Table order có thể chuyển thành card layout.
* Không bị horizontal overflow.

---

## 8. Customer Dashboard

URL:

`/account`

Dashboard hiển thị tổng quan:

| Widget               | Description                |
| -------------------- | -------------------------- |
| Welcome              | Xin chào customer          |
| Recent Orders        | 3-5 order gần nhất         |
| Pending Orders Count | Số đơn đang chờ            |
| Unpaid Orders Count  | Số đơn chưa thanh toán     |
| Default Address      | Địa chỉ mặc định nếu có    |
| Quick Links          | Profile, addresses, orders |

Business rules:

* Nếu customer chưa có order, hiển thị empty state.
* Nếu chưa có address, hiển thị link thêm địa chỉ.
* Dashboard không query quá nặng.

---

## 9. Customer Profile

URL:

`/account/profile`

Profile fields đề xuất:

| Field              | Description    |
| ------------------ | -------------- |
| name               | Tên khách hàng |
| email              | Email          |
| phone              | Số điện thoại  |
| date_of_birth      | Optional       |
| gender             | Optional       |
| preferred_language | Optional       |
| preferred_currency | Optional       |

Business rules:

* Email có thể cho update hoặc không tùy auth hiện tại.
* Nếu cho update email, cần validate unique.
* Nếu hệ thống có email verification, email update có thể cần verify lại.
* Phone validate cơ bản.
* Không cho customer tự set role/admin fields.
* Không cho update password từ profile form, dùng Change Password riêng.

---

## 10. Change Password

URL:

`/account/password`

Fields:

| Field                 | Description           |
| --------------------- | --------------------- |
| current_password      | Mật khẩu hiện tại     |
| password              | Mật khẩu mới          |
| password_confirmation | Xác nhận mật khẩu mới |

Business rules:

* Current password phải đúng.
* Password mới phải đủ rule.
* Sau khi đổi password, có thể giữ login hoặc logout các session khác nếu implement.
* Không log password.
* Validation error rõ ràng.
* CSRF required.

---

## 11. Customer Address Management

Customer cần quản lý địa chỉ để dùng cho checkout sau này.

### 11.1. customer_addresses Table

Tạo bảng `customer_addresses` nếu chưa có.

Fields đề xuất:

| Field               | Type                  | Description                 |
| ------------------- | --------------------- | --------------------------- |
| id                  | bigint                | Primary key                 |
| user_id             | bigint                | Customer                    |
| label               | string nullable       | Home, Office...             |
| recipient_name      | string                | Người nhận                  |
| phone               | string                | SĐT                         |
| address_line_1      | string                | Địa chỉ chính               |
| address_line_2      | string nullable       | Địa chỉ bổ sung             |
| city                | string                | Tỉnh/thành                  |
| district            | string nullable       | Quận/huyện                  |
| ward                | string nullable       | Phường/xã                   |
| postal_code         | string nullable       | Postal code                 |
| country             | string                | Quốc gia                    |
| is_default_shipping | boolean default false | Địa chỉ giao hàng mặc định  |
| is_default_billing  | boolean default false | Địa chỉ thanh toán mặc định |
| created_at          | timestamp             | Created                     |
| updated_at          | timestamp             | Updated                     |

Business rules:

* Một customer có nhiều addresses.
* Mỗi customer chỉ có một default shipping address.
* Mỗi customer chỉ có một default billing address.
* Customer chỉ CRUD address của chính mình.
* Khi checkout, có thể chọn address đã lưu.
* Order vẫn dùng address snapshot, không phụ thuộc address hiện tại.

### 11.2. Address List

URL:

`/account/addresses`

Hiển thị:

| Field          | Description               |
| -------------- | ------------------------- |
| Label          | Home/Office               |
| Recipient      | Người nhận                |
| Phone          | SĐT                       |
| Address        | Địa chỉ                   |
| Default badges | Default shipping/billing  |
| Actions        | Edit, Delete, Set default |

### 11.3. Address Create/Edit

Yêu cầu:

* Form dễ dùng.
* Validate required fields.
* Có checkbox default shipping.
* Có checkbox default billing.
* Nếu set default mới, bỏ default cũ.
* Không dùng browser confirm cho delete.
* Delete dùng custom confirmation modal.

### 11.4. Address Delete

Business rules:

* Customer có thể delete address của mình.
* Không delete order address snapshot.
* Nếu delete default address, có thể không còn default hoặc chọn address khác làm default.
* Delete phải có confirmation.

---

## 12. Order History

URL:

`/account/orders`

Customer order history hiển thị danh sách đơn hàng của customer đã login.

### 12.1. Columns

Desktop table columns:

| Column         | Description           |
| -------------- | --------------------- |
| Order Number   | Mã đơn                |
| Ordered At     | Ngày đặt              |
| Order Status   | Trạng thái đơn        |
| Payment Status | Trạng thái thanh toán |
| Payment Method | COD/VNPAY             |
| Grand Total    | Tổng tiền             |
| Actions        | View                  |

Mobile có thể dùng card layout.

### 12.2. Filters

Filters cơ bản:

| Filter         | Values                                               |
| -------------- | ---------------------------------------------------- |
| Order Status   | pending, confirmed, processing, completed, cancelled |
| Payment Status | unpaid, pending, paid, failed                        |
| Date Range     | Optional                                             |
| Search         | Order number                                         |

MVP có thể chỉ cần search order number và status filter.

### 12.3. Pagination

Yêu cầu:

* Có pagination.
* Không load toàn bộ orders.
* Filter giữ query string.

### 12.4. Empty State

Nếu chưa có order:

* Hiển thị message.
* Button `Continue Shopping`.

---

## 13. Order Detail

URL:

`/account/orders/{order}`

Order detail hiển thị order snapshot.

### 13.1. Sections

| Section          | Description                                  |
| ---------------- | -------------------------------------------- |
| Order Header     | Order number, ordered date                   |
| Status Summary   | Order/payment/fulfillment status             |
| Customer Info    | Customer snapshot                            |
| Shipping Address | Address snapshot                             |
| Billing Address  | Address snapshot                             |
| Order Items      | Items snapshot                               |
| Payment Info     | Payment method/status                        |
| Order Summary    | Subtotal/discount/tax/shipping/grand total   |
| Timeline         | Basic status history nếu có                  |
| Actions          | Continue shopping, retry payment nếu phù hợp |

### 13.2. Order Items

Fields:

| Field        | Description              |
| ------------ | ------------------------ |
| Image        | Image snapshot/fallback  |
| Product Name | Product snapshot         |
| Variant      | Variant/options snapshot |
| SKU          | SKU snapshot             |
| Unit Price   | Unit price snapshot      |
| Quantity     | Quantity                 |
| Total        | Item total               |

Business rules:

* Không lấy lại price từ product hiện tại.
* Không tính lại tax/currency.
* Dùng order snapshot.
* Nếu product đã inactive/delete, order vẫn hiển thị snapshot.
* Có thể link product nếu product còn active, nhưng không bắt buộc.

### 13.3. Payment Info

Hiển thị:

| Field              | Description                                   |
| ------------------ | --------------------------------------------- |
| Payment Method     | COD/VNPAY/online                              |
| Payment Status     | unpaid/pending/paid/failed                    |
| Payment Amount     | Amount                                        |
| Paid At            | Nếu có                                        |
| Transaction Number | Nếu online payment có                         |
| Retry Payment      | Nếu payment failed/pending và order được phép |

Business rules:

* Không cho retry nếu order paid.
* Không cho retry nếu order cancelled/completed.
* Retry payment dùng flow Task 24 nếu đã có.

---

## 14. Guest Order Lookup

Guest order lookup là optional nhưng nên có nếu hệ thống cho guest checkout.

### 14.1. Guest Order Detail by Token

Nếu Task 19 đã tạo `guest_token`, guest có thể xem order qua URL token.

Route:

`/guest-orders/{guest_token}`

Business rules:

* Token phải khó đoán.
* Không dùng order ID tuần tự làm public access.
* Nếu token sai, trả 404.
* Guest chỉ xem được thông tin cơ bản.
* Không hiển thị admin notes.
* Không hiển thị dữ liệu nhạy cảm.

### 14.2. Guest Order Lookup Form Optional

URL:

`/guest-orders/lookup`

Fields:

| Field        | Description    |
| ------------ | -------------- |
| order_number | Mã đơn         |
| email        | Email đặt hàng |

Business rules:

* Validate email + order_number.
* Nếu match, gửi link order qua email hoặc hiển thị hạn chế.
* MVP có thể không implement lookup form nếu guest token link đã có trong email.
* Không reveal order existence quá rõ để tránh dò đơn.

---

## 15. Customer Order Authorization

Đây là phần bảo mật quan trọng.

Business rules:

* Customer logged-in chỉ xem order có `user_id = current_user.id`.
* Guest chỉ xem order bằng `guest_token`.
* Customer không thể xem order của customer khác.
* Customer không thể update status/payment/order item.
* Customer không thể mark paid.
* Customer không thể cancel order trong Task 31 nếu chưa có cancel request feature.
* Admin order routes tách riêng, không dùng chung public controller nếu không cần.

Test cases:

| ID         | Scenario                          | Expected Result |
| ---------- | --------------------------------- | --------------- |
| ACCSEC-001 | Customer A xem order Customer B   | Forbidden/404   |
| ACCSEC-002 | Guest token sai                   | 404             |
| ACCSEC-003 | Customer gọi admin order route    | Forbidden       |
| ACCSEC-004 | Customer POST update order status | Bị chặn         |
| ACCSEC-005 | Guest xem order không token       | Bị chặn         |

---

## 16. Customer Address Authorization

Business rules:

* Customer chỉ CRUD address của mình.
* Address ID của user khác không được xem/sửa/xóa.
* Delete address phải CSRF.
* Update address phải CSRF.
* Không cho mass assignment `user_id`.

---

## 17. Checkout Integration

Task 31 có thể tích hợp nhẹ với checkout.

### 17.1. Use Saved Address in Checkout

Nếu customer đã login và có saved address:

* Checkout page có thể hiển thị dropdown chọn address.
* Khi chọn address, autofill shipping/billing fields.
* Customer vẫn có thể nhập address mới.
* Có checkbox `Save this address` nếu muốn.

### 17.2. Scope Limit

Nếu checkout integration phức tạp, chỉ chuẩn bị address management trong Task 31 và tích hợp sâu ở Shipping/Checkout enhancement task sau.

Business rules:

* Order address vẫn snapshot.
* Sửa saved address sau khi order không ảnh hưởng order cũ.

---

## 18. UI / UX Requirements

### 18.1. General UI

Customer account UI cần:

* Gọn gàng.
* Dễ dùng.
* Đồng bộ public theme.
* Responsive.
* Có breadcrumb hoặc title rõ.
* Form validation rõ.
* Toast success/error.
* Empty state đẹp.
* Loading state nếu dùng AJAX.

### 18.2. Order History UI

Yêu cầu:

* Order status badge rõ.
* Payment status badge rõ.
* Tổng tiền nổi bật.
* View action rõ.
* Mobile không vỡ layout.
* Empty state có CTA mua hàng.

### 18.3. Order Detail UI

Yêu cầu:

* Order number nổi bật.
* Status rõ ràng.
* Items dễ đọc.
* Total summary rõ.
* Address chia card.
* Payment info chia card.
* Timeline nếu có dễ hiểu.
* Không hiển thị thông tin admin internal.

### 18.4. Address UI

Yêu cầu:

* Address list dạng card.
* Default badge rõ.
* Add/Edit/Delete dễ dùng.
* Delete dùng custom modal.
* Không dùng browser confirm mặc định.

---

## 19. Status Display Rules

Order status display:

| Status     | Label      |
| ---------- | ---------- |
| pending    | Pending    |
| confirmed  | Confirmed  |
| processing | Processing |
| completed  | Completed  |
| cancelled  | Cancelled  |

Payment status display:

| Status    | Label     |
| --------- | --------- |
| unpaid    | Unpaid    |
| pending   | Pending   |
| paid      | Paid      |
| failed    | Failed    |
| cancelled | Cancelled |
| refunded  | Refunded  |

Fulfillment status display:

| Status      | Label       |
| ----------- | ----------- |
| unfulfilled | Unfulfilled |
| processing  | Processing  |
| shipped     | Shipped     |
| delivered   | Delivered   |
| cancelled   | Cancelled   |

Labels có thể dịch theo current language.

---

## 20. Translation / Language Rules

Customer account public UI cần dùng current language.

Business rules:

* Labels/buttons/messages phải translatable nếu hệ thống đã có translation structure.
* Order snapshot không bị dịch lại nếu snapshot đã lưu text tại thời điểm order.
* Status label có thể dịch.
* Currency format theo order currency snapshot.
* Address format có thể theo country nếu sau này cần.

---

## 21. Currency Rules

Order history/detail phải hiển thị theo order snapshot:

* `currency_code`
* `currency_symbol`
* `currency_position`
* `currency_rate`
* order amount fields

Business rules:

* Không format order cũ bằng currency hiện tại của customer nếu order có currency snapshot.
* Không tính lại amount.
* Nếu thiếu currency symbol, fallback currency code.

---

## 22. Database Design

### 22.1. customer_addresses Table

Tạo nếu chưa có.

Fields:

| Field               | Type            | Description      |
| ------------------- | --------------- | ---------------- |
| id                  | bigint          | Primary key      |
| user_id             | bigint          | Customer         |
| label               | string nullable | Label            |
| recipient_name      | string          | Recipient        |
| phone               | string          | Phone            |
| address_line_1      | string          | Address          |
| address_line_2      | string nullable | Extra address    |
| city                | string          | City             |
| district            | string nullable | District         |
| ward                | string nullable | Ward             |
| postal_code         | string nullable | Postal code      |
| country             | string          | Country          |
| is_default_shipping | boolean         | Default shipping |
| is_default_billing  | boolean         | Default billing  |
| created_at          | timestamp       | Created          |
| updated_at          | timestamp       | Updated          |

### 22.2. users Table Optional Fields

Nếu chưa có, có thể bổ sung:

| Field                 | Description    |
| --------------------- | -------------- |
| phone                 | Customer phone |
| preferred_language_id | Optional       |
| preferred_currency_id | Optional       |

Chỉ thêm nếu phù hợp với database hiện tại.

---

## 23. Routes

### 23.1. Customer Auth Routes

Routes yêu cầu customer login:

| Method | URL                                  | Name                      | Purpose              |
| ------ | ------------------------------------ | ------------------------- | -------------------- |
| GET    | /account                             | account.dashboard         | Customer dashboard   |
| GET    | /account/profile                     | account.profile.edit      | Profile form         |
| PATCH  | /account/profile                     | account.profile.update    | Update profile       |
| GET    | /account/password                    | account.password.edit     | Change password form |
| PATCH  | /account/password                    | account.password.update   | Update password      |
| GET    | /account/addresses                   | account.addresses.index   | Address list         |
| GET    | /account/addresses/create            | account.addresses.create  | Create address       |
| POST   | /account/addresses                   | account.addresses.store   | Store address        |
| GET    | /account/addresses/{address}/edit    | account.addresses.edit    | Edit address         |
| PATCH  | /account/addresses/{address}         | account.addresses.update  | Update address       |
| DELETE | /account/addresses/{address}         | account.addresses.destroy | Delete address       |
| PATCH  | /account/addresses/{address}/default | account.addresses.default | Set default          |
| GET    | /account/orders                      | account.orders.index      | Order history        |
| GET    | /account/orders/{order}              | account.orders.show       | Order detail         |

### 23.2. Guest Order Routes Optional

| Method | URL                         | Name                       | Purpose            |
| ------ | --------------------------- | -------------------------- | ------------------ |
| GET    | /guest-orders/lookup        | guest.orders.lookup        | Lookup form        |
| POST   | /guest-orders/lookup        | guest.orders.lookup.submit | Lookup submit      |
| GET    | /guest-orders/{guest_token} | guest.orders.show          | Guest order detail |

Business rules:

* Customer routes require auth.
* Guest order routes do not require auth but require valid token/lookup.
* POST/PATCH/DELETE require CSRF.

---

## 24. Controllers / Services

Codex có thể tạo:

| Class                      | Responsibility           |
| -------------------------- | ------------------------ |
| CustomerAccountController  | Account dashboard        |
| CustomerProfileController  | Profile update           |
| CustomerPasswordController | Password change          |
| CustomerAddressController  | Address CRUD             |
| CustomerOrderController    | Order history/detail     |
| GuestOrderController       | Guest order access       |
| CustomerAddressService     | Default address handling |
| CustomerOrderAccessService | Authorization helper     |

---

## 25. Validation Rules

### 25.1. Profile Update

| Field                 | Rule                                                    |
| --------------------- | ------------------------------------------------------- |
| name                  | Required, max length                                    |
| email                 | Required, email, unique except current user if editable |
| phone                 | Nullable/string/max length                              |
| preferred_language_id | Nullable exists languages                               |
| preferred_currency_id | Nullable exists currencies                              |

### 25.2. Password Update

| Field                 | Rule                               |
| --------------------- | ---------------------------------- |
| current_password      | Required and correct               |
| password              | Required, confirmed, strong enough |
| password_confirmation | Required                           |

### 25.3. Address

| Field               | Rule     |
| ------------------- | -------- |
| recipient_name      | Required |
| phone               | Required |
| address_line_1      | Required |
| city                | Required |
| country             | Required |
| postal_code         | Nullable |
| is_default_shipping | Boolean  |
| is_default_billing  | Boolean  |

### 25.4. Order Filters

| Field          | Rule                                   |
| -------------- | -------------------------------------- |
| status         | Nullable valid order status            |
| payment_status | Nullable valid payment status          |
| q              | Nullable string                        |
| date_from      | Nullable date                          |
| date_to        | Nullable date after_or_equal date_from |

---

## 26. Security Requirements

Yêu cầu:

* Customer account routes phải require auth.
* Customer chỉ xem/sửa dữ liệu của mình.
* Guest order token phải validate.
* CSRF cho mọi write action.
* Password không log.
* Không mass assign user_id.
* Không expose admin internal notes.
* Không expose payment gateway secret.
* Không cho customer update order/payment status.
* Không cho customer access admin route.
* Delete address dùng confirmation modal.
* Không dùng browser confirm nếu project đã chuẩn hóa modal.

---

## 27. Error Handling

| Scenario                         | Expected Result  |
| -------------------------------- | ---------------- |
| Customer chưa login vào /account | Redirect login   |
| Customer xem order người khác    | 403 hoặc 404     |
| Guest token sai                  | 404              |
| Address không thuộc user         | 403 hoặc 404     |
| Current password sai             | Validation error |
| Email duplicate                  | Validation error |
| Order không tồn tại              | 404              |
| No orders                        | Empty state      |
| No addresses                     | Empty state      |
| Payment retry không hợp lệ       | Error message    |
| AJAX error                       | Show safe error  |

---

## 28. Testing Requirements

### 28.1. Test Cases

| ID         | Scenario                                | Expected Result             |
| ---------- | --------------------------------------- | --------------------------- |
| ACC-001    | Customer mở /account                    | Dashboard hiển thị          |
| ACC-002    | Guest mở /account                       | Redirect login              |
| ACC-003    | Customer update profile                 | Lưu thành công              |
| ACC-004    | Customer update email duplicate         | Validation error            |
| ACC-005    | Customer change password đúng           | Thành công                  |
| ACC-006    | Current password sai                    | Validation error            |
| ADDR-001   | Customer tạo address                    | Thành công                  |
| ADDR-002   | Customer sửa address của mình           | Thành công                  |
| ADDR-003   | Customer sửa address user khác          | Bị chặn                     |
| ADDR-004   | Set default shipping                    | Chỉ một default shipping    |
| ADDR-005   | Set default billing                     | Chỉ một default billing     |
| ADDR-006   | Delete address                          | Thành công với confirmation |
| ORDACC-001 | Customer xem order list                 | Chỉ thấy order của mình     |
| ORDACC-002 | Customer xem order detail của mình      | Thành công                  |
| ORDACC-003 | Customer xem order user khác            | Bị chặn                     |
| ORDACC-004 | Order detail hiển thị items snapshot    | Đúng                        |
| ORDACC-005 | Order detail hiển thị currency snapshot | Đúng                        |
| ORDACC-006 | Order detail hiển thị payment info      | Đúng                        |
| ORDACC-007 | Guest xem order bằng token đúng         | Thành công                  |
| ORDACC-008 | Guest token sai                         | 404                         |
| ORDACC-009 | Order history filter                    | Kết quả đúng                |
| ORDACC-010 | Mobile account pages                    | Layout không vỡ             |

---

## 29. UI Files Expected

Codex có thể tạo hoặc cập nhật:

| Type       | Description                         |
| ---------- | ----------------------------------- |
| Layout     | Customer account layout/sidebar     |
| View       | account dashboard                   |
| View       | profile edit                        |
| View       | password change                     |
| View       | address index/create/edit           |
| View       | order history                       |
| View       | order detail                        |
| View       | guest order lookup/detail optional  |
| Partials   | order status badge                  |
| Partials   | payment status badge                |
| Partials   | address card                        |
| Partials   | order item row                      |
| JavaScript | Delete modal/address action nếu cần |

---

## 30. Commands

Sau khi implement, chạy:

```bash id="ub57bi"
php artisan migrate
php artisan route:list
php artisan test
npm run build
```

Nếu có cache:

```bash id="nnxk1s"
php artisan optimize:clear
```

---

## 31. Acceptance Criteria

Task này hoàn thành khi:

* [ ] Có customer account dashboard.
* [ ] Customer account routes yêu cầu login.
* [ ] Customer có thể cập nhật profile.
* [ ] Customer có thể đổi mật khẩu.
* [ ] Customer có thể quản lý địa chỉ.
* [ ] Customer có thể set default shipping address.
* [ ] Customer có thể set default billing address nếu implement.
* [ ] Customer không xem/sửa được address của user khác.
* [ ] Customer có thể xem order history của mình.
* [ ] Customer có thể filter/search order history cơ bản.
* [ ] Customer có thể xem order detail của mình.
* [ ] Customer không xem được order của user khác.
* [ ] Guest có thể xem order bằng token nếu implement guest route.
* [ ] Guest token sai bị chặn.
* [ ] Order detail dùng order snapshot.
* [ ] Order detail không tính lại giá từ product hiện tại.
* [ ] Order detail format tiền theo currency snapshot.
* [ ] Order detail hiển thị payment method/status.
* [ ] Order detail không hiển thị admin internal notes.
* [ ] UI responsive.
* [ ] Empty state đẹp khi chưa có order/address.
* [ ] Delete address dùng confirmation modal.
* [ ] CSRF hoạt động cho write actions.
* [ ] Không implement Review Product.
* [ ] Không implement Refund/Return nâng cao.
* [ ] Không dùng Vue.js.

---

## 32. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-03-authentication.md
* docs/tasks/task-15-cart.md
* docs/tasks/task-17-checkout-with-tax-currency-snapshot.md
* docs/tasks/task-19-order-creation.md
* docs/tasks/task-20-admin-order-management.md
* docs/tasks/task-24-online-payment.md
* docs/tasks/task-27-security-hardening.md
* docs/tasks/task-30-email-notification.md
* docs/tasks/task-31-customer-account-and-order-history.md

Sau đó implement Task 31: Customer Account and Order History theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 31.
* Tạo customer account dashboard.
* Tạo customer profile page.
* Cho customer cập nhật profile cơ bản.
* Cho customer đổi mật khẩu.
* Tạo customer address management.
* Customer có thể tạo/sửa/xóa địa chỉ của chính mình.
* Customer có thể set default shipping address.
* Customer có thể set default billing address nếu phù hợp.
* Customer không được xem/sửa/xóa address của user khác.
* Tạo customer order history.
* Customer chỉ xem được order của chính mình.
* Customer có thể xem order detail của chính mình.
* Order detail phải dùng order snapshot.
* Order detail không tính lại giá/tax/currency từ dữ liệu hiện tại.
* Order detail hiển thị payment method, payment status, order status.
* Order detail không hiển thị admin internal notes.
* Implement guest order detail bằng guest token nếu project đã có guest_token.
* Guest token sai phải bị chặn.
* CSRF cho mọi write action.
* UI responsive, đồng bộ public frontend.
* Delete address dùng custom confirmation modal, không dùng browser confirm mặc định.
* Không implement Review Product.
* Không implement Refund/Return nâng cao.
* Không implement Wishlist/Loyalty.
* Không dùng Vue.js.
* Dùng Blade + Tailwind CSS + Alpine.js hoặc Fetch API nếu cần.
* Sau khi làm xong, báo cáo:

  * File đã tạo/sửa.
  * Routes đã thêm.
  * Database migration nếu có.
  * Test đã chạy.
  * Cách test customer account và order history.
