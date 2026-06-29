# Task 32: Shipping Management

## 1. Overview

Task này dùng để xây dựng chức năng quản lý vận chuyển cho hệ thống e-commerce.

Ở các task trước, checkout đang dùng shipping đơn giản, thường là `shipping_amount = 0` hoặc một giá trị cố định. Task 32 sẽ bổ sung hệ thống shipping rõ ràng hơn để admin có thể cấu hình phí vận chuyển và customer có thể chọn phương thức giao hàng khi checkout.

Shipping Management cần hỗ trợ:

* Admin quản lý shipping zones.
* Admin quản lý shipping methods.
* Admin cấu hình phí vận chuyển cố định.
* Admin cấu hình miễn phí vận chuyển theo điều kiện.
* Admin cấu hình shipping theo khu vực.
* Admin bật/tắt shipping method.
* Customer chọn shipping method khi checkout.
* Checkout tính shipping fee từ backend.
* Order snapshot shipping method và shipping fee.
* Admin order detail hiển thị shipping method.
* Report có thể dùng shipping amount từ order snapshot.

Frontend sử dụng:

* Admin: Laravel Blade + Tailwind CSS + Alpine.js hoặc Fetch API nếu cần.
* Public: Laravel Blade + Tailwind CSS + Alpine.js hoặc Fetch API nếu cần.
* Không dùng Vue.js.

---

## 2. Objectives

Sau khi hoàn thành Task 32, hệ thống cần đạt:

* Admin có thể tạo shipping zone.
* Admin có thể chỉnh sửa shipping zone.
* Admin có thể bật/tắt shipping zone.
* Admin có thể tạo shipping method.
* Admin có thể chỉnh sửa shipping method.
* Admin có thể bật/tắt shipping method.
* Admin có thể cấu hình phí vận chuyển cố định.
* Admin có thể cấu hình miễn phí vận chuyển theo giá trị đơn hàng.
* Admin có thể cấu hình phí vận chuyển theo khu vực.
* Customer thấy danh sách shipping methods hợp lệ ở checkout.
* Customer chọn được shipping method.
* Checkout summary cập nhật shipping amount.
* Grand total cập nhật sau khi chọn shipping method.
* Shipping amount được tính ở backend.
* Không tin shipping amount từ frontend.
* Order lưu shipping snapshot.
* Admin order detail hiển thị shipping snapshot.
* Shipping vẫn hoạt động với coupon/tax/currency snapshot.
* Không implement shipping carrier API trong Task 32.
* Không implement tracking API trong Task 32.
* Không dùng Vue.js.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong Task 32:

* Shipping zones.
* Shipping methods.
* Flat rate shipping.
* Free shipping threshold.
* Zone-based shipping.
* Checkout shipping method selection.
* Shipping fee calculation.
* Shipping snapshot into checkout session.
* Shipping snapshot into order.
* Admin shipping settings UI.
* Public checkout shipping UI.
* Validation.
* Security.
* Tests.

### 3.2. Out of Scope

Không làm trong Task 32:

* Không tích hợp shipping carrier API.
* Không tích hợp GHN/GHTK/Viettel Post/Ahamove.
* Không tính phí realtime từ đơn vị vận chuyển.
* Không in vận đơn.
* Không tạo mã vận đơn tự động.
* Không tracking shipment realtime.
* Không quản lý kho nhiều địa điểm.
* Không split shipment.
* Không international shipping phức tạp.
* Không refund shipping fee nâng cao.
* Không return shipping.
* Không dùng Vue.js.

Các phần shipping carrier/tracking nâng cao có thể làm ở task sau.

---

## 4. Dependencies

Task này phụ thuộc:

| Task    | Dependency                            |
| ------- | ------------------------------------- |
| Task 05 | System Settings                       |
| Task 07 | Currency Management                   |
| Task 15 | Cart                                  |
| Task 16 | Coupon                                |
| Task 17 | Checkout with Tax / Currency Snapshot |
| Task 19 | Order Creation                        |
| Task 20 | Admin Order Management                |
| Task 23 | Report                                |
| Task 31 | Customer Account and Order History    |

---

## 5. User Roles

| Role     | Permission                           |
| -------- | ------------------------------------ |
| Admin    | Quản lý shipping zones/methods       |
| Staff    | Quản lý shipping nếu được phân quyền |
| Customer | Chọn shipping method khi checkout    |
| Guest    | Chọn shipping method khi checkout    |

Business rules:

* Admin/staff mới được truy cập admin shipping routes.
* Customer/guest chỉ xem shipping methods hợp lệ ở checkout.
* Customer/guest không được tự gửi shipping fee tùy ý.
* Backend luôn tính lại shipping fee.

---

## 6. Shipping Concepts

### 6.1. Shipping Zone

Shipping zone là khu vực giao hàng.

Ví dụ:

* Vietnam.
* Hanoi.
* Ho Chi Minh City.
* Domestic.
* International.
* North Vietnam.
* South Vietnam.

MVP có thể hỗ trợ zone theo:

* Country.
* City/province.
* District optional.

### 6.2. Shipping Method

Shipping method là phương thức giao hàng trong một zone.

Ví dụ:

* Standard Shipping.
* Express Shipping.
* Free Shipping.
* Local Pickup.
* Flat Rate.

### 6.3. Shipping Rate

Shipping rate là phí vận chuyển.

MVP cần hỗ trợ:

* Flat rate.
* Free shipping if order subtotal >= threshold.
* Optional min/max order amount.
* Optional zone restriction.

---

## 7. Shipping Business Rules

Business rules tổng quan:

* Shipping method inactive không hiển thị ở checkout.
* Shipping zone inactive không dùng.
* Shipping method chỉ hiển thị nếu địa chỉ giao hàng thuộc zone.
* Nếu không có shipping method hợp lệ, không cho checkout tiếp.
* Shipping amount phải được tính ở backend.
* Shipping amount phải snapshot vào checkout session.
* Shipping amount phải snapshot vào order.
* Order cũ không bị ảnh hưởng nếu admin đổi shipping fee sau này.
* Shipping fee có thể chịu tax hoặc không tùy setting nếu cần.
* MVP mặc định shipping fee không chịu tax, trừ khi hệ thống có setting riêng.

---

## 8. Shipping Calculation Rules

### 8.1. Flat Rate

Flat rate là phí cố định.

Ví dụ:

| Method            | Fee        |
| ----------------- | ---------- |
| Standard Shipping | 30,000 VND |
| Express Shipping  | 60,000 VND |

Rules:

* Fee lấy từ backend.
* Fee có thể được convert theo currency nếu checkout dùng currency khác base currency.
* Snapshot currency ở Task 17 vẫn áp dụng.

### 8.2. Free Shipping

Free shipping có thể cấu hình:

| Rule             | Description                        |
| ---------------- | ---------------------------------- |
| Always free      | Shipping amount = 0                |
| Free over amount | Miễn phí nếu subtotal >= threshold |
| Free by coupon   | Optional, không bắt buộc Task 32   |

MVP yêu cầu:

* Free shipping by minimum order subtotal.
* Subtotal nên tính sau hay trước discount cần rõ rule.

Khuyến nghị MVP:

* Free shipping threshold tính theo subtotal sau discount, trước tax.
* Nếu business muốn khác, ghi rõ trong settings.

### 8.3. Min/Max Order Amount

Shipping method có thể có điều kiện:

| Field            | Rule                                  |
| ---------------- | ------------------------------------- |
| min_order_amount | Chỉ available nếu order amount >= min |
| max_order_amount | Chỉ available nếu order amount <= max |

### 8.4. Currency Conversion

Nếu hệ thống có multi-currency:

* Shipping fee gốc nên lưu theo base currency.
* Checkout hiển thị theo current currency.
* Checkout session snapshot:

  * base_shipping_amount.
  * shipping_amount.
  * currency_code.
  * currency_rate.

Nếu chưa có `base_shipping_amount`, vẫn phải đảm bảo order snapshot giữ shipping amount tại thời điểm checkout.

---

## 9. Database Design

## 9.1. shipping_zones Table

Tạo bảng `shipping_zones`.

Fields đề xuất:

| Field       | Type               | Description                |
| ----------- | ------------------ | -------------------------- |
| id          | bigint             | Primary key                |
| name        | string             | Tên zone                   |
| code        | string nullable    | Mã zone                    |
| description | text nullable      | Mô tả                      |
| countries   | json nullable      | Danh sách country codes    |
| cities      | json nullable      | Danh sách city/province    |
| districts   | json nullable      | Danh sách district nếu cần |
| sort_order  | integer default 0  | Thứ tự                     |
| status      | string             | active/inactive            |
| created_at  | timestamp          | Created                    |
| updated_at  | timestamp          | Updated                    |
| deleted_at  | nullable timestamp | Soft delete nếu cần        |

Business rules:

* Zone active mới được dùng.
* Zone có thể match theo country/city/district.
* MVP có thể match country + city.
* Nếu countries/cities null, có thể hiểu là all, tùy rule.
* Không hard delete nếu đã có shipping method/order liên quan.

---

## 9.2. shipping_methods Table

Tạo bảng `shipping_methods`.

Fields đề xuất:

| Field                       | Type               | Description                            |
| --------------------------- | ------------------ | -------------------------------------- |
| id                          | bigint             | Primary key                            |
| shipping_zone_id            | bigint nullable    | Zone áp dụng                           |
| code                        | string             | flat_rate, free_shipping, local_pickup |
| name                        | string             | Tên method                             |
| description                 | text nullable      | Mô tả                                  |
| type                        | string             | flat_rate, free_shipping, pickup       |
| base_fee                    | decimal default 0  | Phí base currency                      |
| free_shipping_min_amount    | decimal nullable   | Miễn phí nếu đạt amount                |
| min_order_amount            | decimal nullable   | Min order amount                       |
| max_order_amount            | decimal nullable   | Max order amount                       |
| estimated_delivery_min_days | integer nullable   | Số ngày tối thiểu                      |
| estimated_delivery_max_days | integer nullable   | Số ngày tối đa                         |
| sort_order                  | integer default 0  | Thứ tự                                 |
| status                      | string             | active/inactive                        |
| created_at                  | timestamp          | Created                                |
| updated_at                  | timestamp          | Updated                                |
| deleted_at                  | nullable timestamp | Soft delete nếu cần                    |

Business rules:

* Method active mới hiển thị.
* Method thuộc zone inactive không hiển thị.
* `code` có thể không unique toàn hệ thống nếu nhiều zone, nhưng nên unique trong zone nếu cần.
* `base_fee` không âm.
* Free shipping method có fee = 0.
* Flat rate method có base_fee >= 0.
* Không hard delete method đã dùng trong order snapshot.

---

## 9.3. checkout_sessions Table Updates

Task 17 đã có `shipping_amount`. Task 32 cần bổ sung shipping snapshot chi tiết hơn nếu chưa có.

Fields đề xuất:

| Field                       | Type              | Description                   |
| --------------------------- | ----------------- | ----------------------------- |
| shipping_method_id          | nullable bigint   | Method được chọn              |
| shipping_method_code        | nullable string   | Snapshot code                 |
| shipping_method_name        | nullable string   | Snapshot name                 |
| shipping_method_description | nullable text     | Snapshot description          |
| shipping_zone_id            | nullable bigint   | Zone                          |
| shipping_zone_name          | nullable string   | Snapshot zone                 |
| base_shipping_amount        | decimal default 0 | Shipping in base currency     |
| shipping_amount             | decimal default 0 | Shipping in checkout currency |
| shipping_estimated_delivery | nullable string   | Snapshot delivery estimate    |

Business rules:

* Checkout session phải lưu method đã chọn.
* Nếu shipping address thay đổi, shipping method cần revalidate.
* Nếu method không còn hợp lệ, bắt customer chọn lại.
* Shipping amount không lấy từ frontend.

---

## 9.4. orders Table Updates

Order cần snapshot shipping method.

Fields đề xuất nếu chưa có:

| Field                       | Type              | Description           |
| --------------------------- | ----------------- | --------------------- |
| shipping_method_id          | nullable bigint   | Method ID             |
| shipping_method_code        | nullable string   | Snapshot code         |
| shipping_method_name        | nullable string   | Snapshot name         |
| shipping_method_description | nullable text     | Snapshot description  |
| shipping_zone_id            | nullable bigint   | Zone ID               |
| shipping_zone_name          | nullable string   | Snapshot zone name    |
| base_shipping_amount        | decimal default 0 | Base shipping amount  |
| shipping_amount             | decimal default 0 | Order shipping amount |
| shipping_estimated_delivery | nullable string   | Estimate snapshot     |

Business rules:

* Order shipping snapshot không đổi khi admin đổi method sau này.
* Order total dùng shipping_amount từ checkout session snapshot.
* Admin order detail hiển thị shipping snapshot.

---

## 10. Relationships

| Model           | Relationship                      |
| --------------- | --------------------------------- |
| ShippingZone    | hasMany ShippingMethod            |
| ShippingMethod  | belongsTo ShippingZone            |
| CheckoutSession | belongsTo ShippingMethod nullable |
| Order           | belongsTo ShippingMethod nullable |

---

## 11. Admin Shipping Zone Management

URL đề xuất:

`/admin/shipping/zones`

### 11.1. Zone List

Columns:

| Column        | Description     |
| ------------- | --------------- |
| Name          | Tên zone        |
| Code          | Mã zone         |
| Countries     | Countries       |
| Cities        | Cities          |
| Methods Count | Số method       |
| Status        | Active/inactive |
| Sort          | Sort order      |
| Actions       | Edit, More      |

### 11.2. Zone Form

Fields:

| Field       | Required | Description      |
| ----------- | -------- | ---------------- |
| name        | Yes      | Tên zone         |
| code        | No       | Mã zone          |
| description | No       | Mô tả            |
| countries   | No       | Countries        |
| cities      | No       | Cities/provinces |
| districts   | No       | Districts        |
| sort_order  | No       | Sort             |
| status      | Yes      | active/inactive  |

MVP có thể cho admin nhập countries/cities dạng multi-select hoặc textarea mỗi dòng một giá trị.

### 11.3. Zone Delete/Disable

Business rules:

* Nên disable thay vì hard delete nếu zone đã có methods/orders.
* Delete dùng custom confirmation modal.
* Không dùng browser confirm mặc định.

---

## 12. Admin Shipping Method Management

URL đề xuất:

`/admin/shipping/methods`

Hoặc nằm trong zone detail:

`/admin/shipping/zones/{zone}/methods`

### 12.1. Method List

Columns:

| Column         | Description       |
| -------------- | ----------------- |
| Name           | Tên method        |
| Zone           | Shipping zone     |
| Type           | flat/free/pickup  |
| Fee            | Base fee          |
| Free Threshold | Free min amount   |
| Min/Max Order  | Conditions        |
| Estimate       | Delivery estimate |
| Status         | Active/inactive   |
| Sort           | Sort              |
| Actions        | Edit, More        |

### 12.2. Method Form

Fields:

| Field                       | Required     | Description                    |
| --------------------------- | ------------ | ------------------------------ |
| shipping_zone_id            | No/Yes       | Zone                           |
| name                        | Yes          | Tên method                     |
| code                        | Yes          | Code                           |
| description                 | No           | Mô tả                          |
| type                        | Yes          | flat_rate/free_shipping/pickup |
| base_fee                    | Yes for flat | Fee                            |
| free_shipping_min_amount    | No           | Threshold                      |
| min_order_amount            | No           | Min order                      |
| max_order_amount            | No           | Max order                      |
| estimated_delivery_min_days | No           | Min days                       |
| estimated_delivery_max_days | No           | Max days                       |
| sort_order                  | No           | Sort                           |
| status                      | Yes          | active/inactive                |

### 12.3. Method Validation

Rules:

* name required.
* code required.
* type valid.
* base_fee numeric >= 0.
* free_shipping_min_amount nullable numeric >= 0.
* min_order_amount nullable numeric >= 0.
* max_order_amount nullable numeric >= min_order_amount.
* estimated days numeric >= 0.
* max days >= min days.
* status active/inactive.

---

## 13. Public Checkout Shipping Step

Checkout cần hiển thị shipping methods sau khi customer nhập shipping address.

### 13.1. Checkout UI

Trên checkout page, thêm section:

`Shipping Method`

Hiển thị:

| Element            | Description            |
| ------------------ | ---------------------- |
| Method name        | Tên phương thức        |
| Description        | Mô tả                  |
| Fee                | Phí vận chuyển         |
| Estimated delivery | Thời gian giao dự kiến |
| Radio selected     | Chọn method            |

### 13.2. Behavior

Flow:

1. Customer nhập shipping address.
2. Backend xác định zone phù hợp.
3. Backend lấy shipping methods active.
4. Customer chọn shipping method.
5. Backend tính shipping amount.
6. Checkout summary cập nhật subtotal, discount, tax, shipping, grand total.
7. Checkout session lưu shipping snapshot.
8. Customer tiếp tục payment.

Business rules:

* Nếu address thay đổi, shipping method cần revalidate.
* Nếu method không còn phù hợp, clear selected method.
* Nếu không có method, hiển thị message và không cho payment.
* Không tin fee từ frontend.
* AJAX response trả summary mới nếu dùng fetch.

---

## 14. Shipping Calculation Service

Nên có `ShippingCalculationService`.

Trách nhiệm:

* Resolve shipping zone từ address.
* Get available shipping methods.
* Validate selected method.
* Calculate shipping fee.
* Convert fee theo currency nếu cần.
* Return shipping summary.
* Prepare snapshot data.

### 14.1. Inputs

| Input               | Description      |
| ------------------- | ---------------- |
| checkout_session    | Checkout session |
| shipping_address    | Shipping address |
| cart/checkout items | Items            |
| subtotal            | Subtotal         |
| discount            | Discount         |
| currency snapshot   | Currency         |

### 14.2. Outputs

| Output               | Description                |
| -------------------- | -------------------------- |
| available_methods    | Methods hợp lệ             |
| selected_method      | Method được chọn           |
| base_shipping_amount | Base fee                   |
| shipping_amount      | Display/order currency fee |
| estimated_delivery   | Estimate text              |
| errors               | Nếu không hợp lệ           |

---

## 15. Checkout Recalculation Rules

Khi shipping method thay đổi:

* Recalculate shipping amount.
* Recalculate grand total.
* Tax có thể cần recalculate nếu shipping taxable.
* Coupon có thể cần revalidate nếu coupon phụ thuộc grand total.
* Payment method min/max amount có thể cần revalidate.

MVP recommendation:

* Shipping fee không chịu tax.
* Coupon discount áp dụng cho product subtotal, không áp dụng shipping.
* Grand total = subtotal - discount + tax + shipping.
* Payment method min/max check sau khi có shipping.

---

## 16. Order Creation Integration

Task 19 Order Creation cần copy shipping snapshot từ checkout session sang order.

Yêu cầu:

* Order lưu shipping_method_code.
* Order lưu shipping_method_name.
* Order lưu shipping_zone_name nếu có.
* Order lưu base_shipping_amount.
* Order lưu shipping_amount.
* Order grand_total_amount bao gồm shipping amount.
* Order item không chứa shipping fee.
* Order summary hiển thị shipping line.

Business rules:

* Không tạo order nếu shipping required nhưng chưa chọn method.
* Nếu shipping method inactive sau khi customer chọn nhưng trước order, cần revalidate.
* Nếu selected shipping method không còn hợp lệ, redirect checkout shipping step.

---

## 17. Admin Order Management Integration

Admin order detail cần hiển thị shipping info.

Section:

`Shipping Information`

Fields:

| Field              | Description                  |
| ------------------ | ---------------------------- |
| Shipping Method    | Snapshot name                |
| Shipping Zone      | Snapshot zone                |
| Shipping Fee       | Shipping amount              |
| Estimated Delivery | Snapshot                     |
| Shipping Address   | Order shipping address       |
| Fulfillment Status | Existing status from Task 20 |

Business rules:

* Không tính lại shipping fee trong admin order detail.
* Admin chỉ xem snapshot.
* Update fulfillment/tracking nâng cao không thuộc Task 32.

---

## 18. Customer Account Integration

Customer order detail ở Task 31 cần hiển thị:

* Shipping method name.
* Shipping amount.
* Estimated delivery nếu có.
* Shipping address snapshot.
* Fulfillment status.

Business rules:

* Customer không được sửa shipping method sau khi order tạo trong Task 32.
* Customer không được tự cập nhật tracking/fulfillment.

---

## 19. Report Integration

Task 23 Report đã có `shipping_amount`.

Task 32 cần đảm bảo:

* Sales report tính shipping amount đúng.
* Order report hiển thị shipping amount nếu cần.
* Revenue report có thể tách shipping amount.
* Không tính lại shipping từ method hiện tại.
* Report dùng order snapshot.

---

## 20. Shipping Method Availability Rules

Một method available nếu:

| Condition                   | Required        |
| --------------------------- | --------------- |
| Method status active        | Yes             |
| Zone active                 | Yes nếu có zone |
| Address matches zone        | Yes             |
| Order subtotal meets min    | Yes nếu có      |
| Order subtotal below max    | Yes nếu có      |
| Currency supported          | Yes nếu có rule |
| Cart has shippable products | Yes             |

MVP không cần phân biệt digital/non-shippable product nếu chưa có product type.

---

## 21. Local Pickup

Local pickup optional trong MVP.

Nếu implement:

* Shipping amount = 0.
* Method type = pickup.
* Customer vẫn cần contact info.
* Shipping address có thể optional hoặc vẫn required tùy business.
* Order shipping method snapshot = Local Pickup.

Nếu chưa cần, chỉ để type có thể mở rộng.

---

## 22. Free Shipping Display

Nếu method được miễn phí:

Display:

`Free`

Không hiển thị `0.00` nếu UI muốn đẹp.

Nếu method flat rate được free do threshold:

* Có thể hiển thị original fee gạch ngang.
* Hiển thị `Free shipping applied`.

MVP có thể hiển thị đơn giản:

`Free`

---

## 23. Admin UI / UX Requirements

Admin shipping UI cần:

* Danh sách rõ ràng.
* Status badge.
* Zone/method tabs nếu cần.
* Form validation rõ.
* Fee format dễ hiểu.
* Conditions dễ hiểu.
* Empty state.
* Delete/disable confirmation modal.
* Không dùng browser confirm mặc định.
* Mobile admin layout không vỡ.

---

## 24. Public UI / UX Requirements

Checkout shipping UI cần:

* Shipping method card/radio rõ ràng.
* Fee hiển thị rõ.
* Estimated delivery rõ.
* Selected state rõ.
* Loading state khi calculate.
* Error message nếu không có method.
* Checkout summary cập nhật mượt.
* Mobile responsive.
* Không reload nếu dùng AJAX.

---

## 25. Security Requirements

Yêu cầu:

* Admin shipping routes có auth/admin middleware.
* CSRF cho create/update/delete.
* Customer không gửi shipping fee tự ý.
* Backend tính lại shipping amount.
* Validate selected shipping method.
* Validate address ownership nếu dùng saved address.
* Không cho customer chọn method inactive.
* Không cho customer chọn method ngoài zone.
* Không expose admin-only data.
* No mass assignment sensitive fields.

---

## 26. Error Handling

| Scenario                        | Expected Result            |
| ------------------------------- | -------------------------- |
| No shipping zone match          | Show no shipping available |
| No method available             | Block payment/place order  |
| Method inactive after selected  | Ask customer select again  |
| Address changed                 | Recalculate shipping       |
| Invalid method ID               | Validation error           |
| Fee calculation error           | Safe error message         |
| Admin invalid fee               | Validation error           |
| Delete zone with methods        | Block or soft delete       |
| Order creation without shipping | Block if shipping required |

---

## 27. Routes

### 27.1. Admin Routes

| Method | URL                                   | Name                           | Purpose       |
| ------ | ------------------------------------- | ------------------------------ | ------------- |
| GET    | /admin/shipping/zones                 | admin.shipping.zones.index     | Zone list     |
| GET    | /admin/shipping/zones/create          | admin.shipping.zones.create    | Create zone   |
| POST   | /admin/shipping/zones                 | admin.shipping.zones.store     | Store zone    |
| GET    | /admin/shipping/zones/{zone}/edit     | admin.shipping.zones.edit      | Edit zone     |
| PATCH  | /admin/shipping/zones/{zone}          | admin.shipping.zones.update    | Update zone   |
| DELETE | /admin/shipping/zones/{zone}          | admin.shipping.zones.destroy   | Delete zone   |
| GET    | /admin/shipping/methods               | admin.shipping.methods.index   | Method list   |
| GET    | /admin/shipping/methods/create        | admin.shipping.methods.create  | Create method |
| POST   | /admin/shipping/methods               | admin.shipping.methods.store   | Store method  |
| GET    | /admin/shipping/methods/{method}/edit | admin.shipping.methods.edit    | Edit method   |
| PATCH  | /admin/shipping/methods/{method}      | admin.shipping.methods.update  | Update method |
| DELETE | /admin/shipping/methods/{method}      | admin.shipping.methods.destroy | Delete method |

### 27.2. Public Checkout Routes

| Method | URL                            | Name                          | Purpose                      |
| ------ | ------------------------------ | ----------------------------- | ---------------------------- |
| GET    | /checkout/shipping-methods     | checkout.shipping.methods     | Get available methods        |
| POST   | /checkout/shipping-method      | checkout.shipping.select      | Select shipping method       |
| POST   | /checkout/shipping/recalculate | checkout.shipping.recalculate | Recalculate summary optional |

Routes có thể dùng trong checkout controller hiện tại nếu phù hợp.

---

## 28. Files Expected

Codex có thể tạo hoặc cập nhật:

| Type              | Description                              |
| ----------------- | ---------------------------------------- |
| Migration         | shipping_zones                           |
| Migration         | shipping_methods                         |
| Migration         | update checkout_sessions shipping fields |
| Migration         | update orders shipping fields            |
| Models            | ShippingZone, ShippingMethod             |
| Controller Admin  | AdminShippingZoneController              |
| Controller Admin  | AdminShippingMethodController            |
| Controller Public | CheckoutShippingController               |
| Service           | ShippingCalculationService               |
| Service           | ShippingZoneResolver                     |
| Request           | StoreShippingZoneRequest                 |
| Request           | UpdateShippingZoneRequest                |
| Request           | StoreShippingMethodRequest               |
| Request           | UpdateShippingMethodRequest              |
| Request           | SelectShippingMethodRequest              |
| Blade Admin       | shipping zones index/create/edit         |
| Blade Admin       | shipping methods index/create/edit       |
| Blade Public      | checkout shipping method partial         |
| Tests             | Shipping management tests                |

---

## 29. Test Cases

| ID       | Scenario                            | Expected Result                                   |
| -------- | ----------------------------------- | ------------------------------------------------- |
| SHIP-001 | Admin tạo shipping zone             | Thành công                                        |
| SHIP-002 | Admin sửa shipping zone             | Thành công                                        |
| SHIP-003 | Admin disable zone                  | Methods không hiển thị checkout                   |
| SHIP-004 | Admin tạo flat rate method          | Thành công                                        |
| SHIP-005 | Admin tạo free shipping method      | Thành công                                        |
| SHIP-006 | Fee âm                              | Validation error                                  |
| SHIP-007 | Max order < min order               | Validation error                                  |
| SHIP-008 | Customer nhập address thuộc zone    | Methods hiển thị                                  |
| SHIP-009 | Customer nhập address không có zone | Không có method                                   |
| SHIP-010 | Customer chọn flat rate             | Shipping amount cập nhật                          |
| SHIP-011 | Customer chọn free shipping         | Shipping amount = 0                               |
| SHIP-012 | Subtotal chưa đạt threshold         | Free shipping không available hoặc fee không free |
| SHIP-013 | Address đổi sau chọn method         | Shipping revalidate                               |
| SHIP-014 | Method inactive sau khi chọn        | Không cho place order                             |
| SHIP-015 | Customer gửi fee giả                | Backend bỏ qua fee frontend                       |
| SHIP-016 | Checkout grand total                | Bao gồm shipping                                  |
| SHIP-017 | Order created                       | Snapshot shipping đúng                            |
| SHIP-018 | Admin order detail                  | Hiển thị shipping snapshot                        |
| SHIP-019 | Customer order detail               | Hiển thị shipping snapshot                        |
| SHIP-020 | Report sales                        | Shipping amount đúng                              |
| SHIP-021 | Delete dùng modal                   | Không browser confirm                             |
| SHIP-022 | Mobile checkout shipping            | Layout không vỡ                                   |

---

## 30. Acceptance Criteria

Task này hoàn thành khi:

* [ ] Có bảng `shipping_zones`.
* [ ] Có bảng `shipping_methods`.
* [ ] Admin có thể quản lý shipping zones.
* [ ] Admin có thể quản lý shipping methods.
* [ ] Admin có thể bật/tắt zone.
* [ ] Admin có thể bật/tắt method.
* [ ] Admin có thể cấu hình flat rate.
* [ ] Admin có thể cấu hình free shipping threshold.
* [ ] Admin có thể cấu hình min/max order amount cho shipping method.
* [ ] Checkout hiển thị shipping methods hợp lệ theo address.
* [ ] Customer có thể chọn shipping method.
* [ ] Shipping amount được tính ở backend.
* [ ] Không tin shipping amount từ frontend.
* [ ] Checkout summary cập nhật shipping amount.
* [ ] Grand total bao gồm shipping amount.
* [ ] Checkout session lưu shipping snapshot.
* [ ] Order lưu shipping snapshot.
* [ ] Order creation revalidate shipping method trước khi tạo order.
* [ ] Admin order detail hiển thị shipping method/fee snapshot.
* [ ] Customer order detail hiển thị shipping method/fee snapshot.
* [ ] Report dùng shipping amount từ order snapshot.
* [ ] Nếu không có shipping method hợp lệ, không cho payment/place order.
* [ ] Delete/disable dùng custom confirmation modal.
* [ ] Admin routes có auth/admin middleware.
* [ ] CSRF cho write actions.
* [ ] Mobile responsive.
* [ ] Không implement carrier API.
* [ ] Không implement tracking API.
* [ ] Không dùng Vue.js.

---

## 31. Commands

Sau khi implement, chạy:

```bash
php artisan migrate
php artisan route:list
php artisan test
npm run build
```

Nếu cache lỗi:

```bash
php artisan optimize:clear
```

---

## 32. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-05-system-settings.md
* docs/tasks/task-07-currency-management.md
* docs/tasks/task-15-cart.md
* docs/tasks/task-16-coupon.md
* docs/tasks/task-17-checkout-with-tax-currency-snapshot.md
* docs/tasks/task-19-order-creation.md
* docs/tasks/task-20-admin-order-management.md
* docs/tasks/task-23-report.md
* docs/tasks/task-31-customer-account-and-order-history.md
* docs/tasks/task-32-shipping-management.md

Sau đó implement Task 32: Shipping Management theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 32.
* Tạo shipping zones.
* Tạo shipping methods.
* Admin có thể list/create/edit/delete hoặc disable shipping zones.
* Admin có thể list/create/edit/delete hoặc disable shipping methods.
* Hỗ trợ flat rate shipping.
* Hỗ trợ free shipping threshold.
* Hỗ trợ min/max order amount cho shipping method.
* Checkout hiển thị shipping methods hợp lệ theo shipping address.
* Customer có thể chọn shipping method ở checkout.
* Shipping amount phải được tính ở backend.
* Không tin shipping amount từ frontend.
* Khi shipping method thay đổi, checkout summary và grand total phải cập nhật.
* Checkout session phải lưu shipping snapshot.
* Order creation phải revalidate shipping method.
* Order phải lưu shipping method/zone/fee snapshot.
* Admin order detail hiển thị shipping snapshot.
* Customer order detail hiển thị shipping snapshot.
* Report dùng shipping amount từ order snapshot.
* Nếu không có shipping method hợp lệ, không cho customer tiếp tục payment/place order.
* Delete/disable dùng custom confirmation modal, không dùng browser confirm mặc định.
* Admin routes phải có admin/auth middleware.
* CSRF cho mọi write action.
* Không implement shipping carrier API.
* Không implement tracking API.
* Không implement shipment label.
* Không dùng Vue.js.
* Dùng Blade + Tailwind CSS + Alpine.js hoặc Fetch API nếu cần.
* Sau khi làm xong, báo cáo:

  * File đã tạo/sửa.
  * Migration đã thêm.
  * Routes đã thêm.
  * Shipping calculation rules.
  * Test đã chạy.
  * Cách test checkout shipping.
