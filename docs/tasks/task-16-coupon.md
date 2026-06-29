Bạn tạo file mới:

```txt
docs/tasks/task-16-coupon.md
```

Nội dung tài liệu như sau:

# Task 16: Coupon

## 1. Overview

Task này dùng để xây dựng chức năng mã giảm giá cho hệ thống e-commerce.

Coupon được sử dụng để giảm giá cho giỏ hàng trước khi khách hàng checkout.

Coupon cần hỗ trợ:

* Quản lý coupon trong admin.
* Apply coupon ở cart page hoặc checkout page.
* Validate coupon theo điều kiện sử dụng.
* Tính discount preview trong cart.
* Lưu coupon đã apply vào cart.
* Remove coupon khỏi cart.
* Chuẩn bị dữ liệu để Checkout/Order task có thể snapshot coupon sau này.

Trong Task 16:

* Có thể tính discount tạm thời trong Cart.
* Chưa tạo Order.
* Chưa xử lý Payment.
* Chưa snapshot final discount vào Order.
* Checkout/Order task sau sẽ xử lý snapshot chính thức.

Frontend trong MVP sử dụng:

* Laravel Blade
* Tailwind CSS
* Alpine.js nếu cần
* Fetch API nếu cần
* Không dùng Vue.js.

---

## 2. Objectives

Sau khi hoàn thành task này, hệ thống cần có:

* Admin có thể tạo coupon.
* Admin có thể chỉnh sửa coupon.
* Admin có thể bật/tắt coupon.
* Admin có thể xóa hoặc disable coupon nếu chưa dùng.
* Public user có thể nhập coupon trong Cart.
* Public user có thể apply coupon bằng JavaScript, không reload page.
* Public user có thể remove coupon bằng JavaScript, không reload page.
* Cart summary cập nhật discount ngay sau khi apply/remove coupon.
* Coupon validate theo thời gian, trạng thái, số lần sử dụng, minimum order amount và sản phẩm/danh mục nếu có.
* Coupon hỗ trợ discount theo phần trăm.
* Coupon hỗ trợ discount theo số tiền cố định.
* Coupon không cho discount vượt subtotal.
* Coupon không áp dụng cho item không hợp lệ hoặc unavailable.
* Coupon không implement Checkout, Order hoặc Payment.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong task này:

* Database design cho coupon.
* Admin coupon management.
* Public apply coupon.
* Public remove coupon.
* Cart summary discount preview.
* Coupon validation rules.
* Coupon usage limit rules.
* Coupon minimum order amount.
* Coupon date range.
* Coupon active/inactive status.
* Coupon calculation service.
* AJAX apply/remove coupon.
* Cart UI coupon form.
* Toast feedback.
* Error message rõ ràng.

### 3.2. Out of Scope

Không làm trong task này:

* Không implement Checkout.
* Không implement Order.
* Không implement Payment.
* Không snapshot coupon vào order.
* Không trừ coupon usage final khi chưa tạo order.
* Không implement gift card.
* Không implement loyalty points.
* Không implement referral coupon.
* Không implement automatic coupon nâng cao.
* Không implement stack multiple coupons trong MVP.
* Không implement free shipping coupon nếu shipping chưa có.
* Không dùng Vue.js.

---

## 4. User Roles

| Role     | Permission                                |
| -------- | ----------------------------------------- |
| Admin    | Quản lý coupon                            |
| Staff    | Có thể quản lý coupon nếu được phân quyền |
| Guest    | Có thể apply coupon trong cart            |
| Customer | Có thể apply coupon trong cart            |

---

## 5. Coupon Types

Task 16 cần hỗ trợ các loại discount cơ bản.

| Type         | Description          |
| ------------ | -------------------- |
| Percentage   | Giảm theo phần trăm  |
| Fixed Amount | Giảm số tiền cố định |

Ví dụ:

| Coupon | Type         | Value  |
| ------ | ------------ | ------ |
| SALE10 | Percentage   | 10%    |
| NEW50K | Fixed Amount | 50,000 |

---

## 6. Database Design

## 6.1. coupons Table

Tạo bảng `coupons`.

Fields đề xuất:

| Field                | Type               | Description                  |
| -------------------- | ------------------ | ---------------------------- |
| id                   | bigint             | Primary key                  |
| code                 | string             | Mã coupon                    |
| name                 | string nullable    | Tên coupon                   |
| description          | text nullable      | Mô tả                        |
| discount_type        | string             | percentage hoặc fixed_amount |
| discount_value       | decimal            | Giá trị giảm                 |
| max_discount_amount  | decimal nullable   | Giảm tối đa cho percentage   |
| min_order_amount     | decimal nullable   | Giá trị đơn tối thiểu        |
| usage_limit          | integer nullable   | Tổng số lần được dùng        |
| usage_limit_per_user | integer nullable   | Số lần mỗi user được dùng    |
| used_count           | integer default 0  | Số lần đã dùng               |
| starts_at            | datetime nullable  | Thời gian bắt đầu            |
| ends_at              | datetime nullable  | Thời gian kết thúc           |
| status               | string             | active, inactive             |
| created_at           | timestamp          | Created time                 |
| updated_at           | timestamp          | Updated time                 |
| deleted_at           | nullable timestamp | Soft delete nếu cần          |

Business rules:

* Coupon code phải unique.
* Coupon code nên lưu uppercase.
* Coupon inactive không được apply.
* Coupon expired không được apply.
* Coupon chưa tới ngày bắt đầu không được apply.
* `discount_value` phải lớn hơn 0.
* Percentage coupon không được vượt 100%.
* Fixed amount không được âm.
* `used_count` không tăng ở Cart task nếu chưa checkout thành công.
* `used_count` chỉ nên tăng khi Order được tạo thành công ở task sau.

---

## 6.2. coupon_categories Table

Bảng này dùng nếu coupon chỉ áp dụng cho một số category.

Fields đề xuất:

| Field       | Type      | Description        |
| ----------- | --------- | ------------------ |
| id          | bigint    | Primary key        |
| coupon_id   | bigint    | Reference coupon   |
| category_id | bigint    | Reference category |
| created_at  | timestamp | Created time       |
| updated_at  | timestamp | Updated time       |

Business rules:

* Nếu coupon không có category restriction, áp dụng cho tất cả category.
* Nếu có category restriction, chỉ item thuộc category đó được tính discount.

---

## 6.3. coupon_products Table

Bảng này dùng nếu coupon chỉ áp dụng cho một số product.

Fields đề xuất:

| Field      | Type      | Description       |
| ---------- | --------- | ----------------- |
| id         | bigint    | Primary key       |
| coupon_id  | bigint    | Reference coupon  |
| product_id | bigint    | Reference product |
| created_at | timestamp | Created time      |
| updated_at | timestamp | Updated time      |

Business rules:

* Nếu coupon không có product restriction, áp dụng theo category hoặc toàn cart.
* Nếu có product restriction, chỉ product được chỉ định mới được tính discount.
* Product restriction ưu tiên cụ thể hơn category restriction.

---

## 6.4. coupon_usages Table

Bảng này dùng để tracking coupon đã được dùng sau khi checkout/order thành công.

Fields đề xuất:

| Field           | Type            | Description                             |
| --------------- | --------------- | --------------------------------------- |
| id              | bigint          | Primary key                             |
| coupon_id       | bigint          | Reference coupon                        |
| user_id         | nullable bigint | Customer dùng coupon                    |
| order_id        | nullable bigint | Order dùng coupon, sẽ dùng ở task Order |
| cart_id         | nullable bigint | Cart liên quan nếu cần                  |
| coupon_code     | string          | Snapshot code                           |
| discount_amount | decimal         | Số tiền giảm                            |
| used_at         | datetime        | Thời điểm dùng                          |
| created_at      | timestamp       | Created time                            |
| updated_at      | timestamp       | Updated time                            |

Business rules:

* Trong Task 16 chưa bắt buộc tạo usage record khi apply coupon.
* Usage record nên tạo khi order thành công ở Checkout/Order task.
* Có thể dùng bảng này để validate usage limit per user nếu đã có dữ liệu lịch sử.

---

## 6.5. carts Table Updates

Cần cập nhật cart để lưu coupon đã apply.

Fields có thể thêm vào `carts`:

| Field                  | Type              | Description               |
| ---------------------- | ----------------- | ------------------------- |
| coupon_id              | nullable bigint   | Coupon đang apply         |
| coupon_code            | nullable string   | Coupon code đã nhập       |
| coupon_discount_amount | decimal default 0 | Discount preview hiện tại |

Business rules:

* Cart chỉ hỗ trợ một coupon trong MVP.
* Khi remove coupon, clear các field coupon trong cart.
* Coupon discount trong cart chỉ là preview.
* Checkout/Order sẽ tính lại và snapshot chính thức.

---

## 7. Relationships

Relationships cần có:

| Model       | Relationship              |
| ----------- | ------------------------- |
| Coupon      | belongsToMany Category    |
| Coupon      | belongsToMany Product     |
| Coupon      | hasMany CouponUsage       |
| CouponUsage | belongsTo Coupon          |
| CouponUsage | belongsTo User nullable   |
| CouponUsage | belongsTo Order nullable  |
| Cart        | belongsTo Coupon nullable |

---

## 8. Admin Coupon Management

Admin cần có màn hình quản lý coupon.

## 8.1. Coupon List Page

URL đề xuất:

`/admin/coupons`

Danh sách coupon cần hiển thị:

| Column     | Description              |
| ---------- | ------------------------ |
| Code       | Mã coupon                |
| Name       | Tên coupon               |
| Type       | Loại discount            |
| Value      | Giá trị giảm             |
| Min Order  | Đơn tối thiểu            |
| Usage      | Used count / usage limit |
| Date Range | Thời gian áp dụng        |
| Status     | Active / Inactive        |
| Actions    | Edit, Disable, Delete    |

Filter đề xuất:

* Code.
* Status.
* Discount type.
* Active date.
* Expired.

---

## 8.2. Coupon Create Page

URL đề xuất:

`/admin/coupons/create`

Fields:

| Field                | Required | Description                  |
| -------------------- | -------- | ---------------------------- |
| Code                 | Yes      | Mã coupon                    |
| Name                 | No       | Tên coupon                   |
| Description          | No       | Mô tả                        |
| Discount Type        | Yes      | Percentage hoặc Fixed Amount |
| Discount Value       | Yes      | Giá trị giảm                 |
| Max Discount Amount  | No       | Giảm tối đa nếu percentage   |
| Min Order Amount     | No       | Giá trị cart tối thiểu       |
| Usage Limit          | No       | Tổng lượt dùng               |
| Usage Limit Per User | No       | Lượt dùng mỗi user           |
| Starts At            | No       | Ngày bắt đầu                 |
| Ends At              | No       | Ngày kết thúc                |
| Status               | Yes      | Active / Inactive            |
| Categories           | No       | Category áp dụng             |
| Products             | No       | Product áp dụng              |

---

## 8.3. Coupon Edit Page

URL đề xuất:

`/admin/coupons/{id}/edit`

Admin có thể chỉnh sửa:

* Name.
* Description.
* Discount value nếu chưa dùng hoặc nếu business cho phép.
* Min order amount.
* Date range.
* Usage limit.
* Category restriction.
* Product restriction.
* Status.

Business rules:

* Nếu coupon đã được dùng, cần cân nhắc không cho thay đổi code.
* Nếu coupon đã được dùng, không nên hard delete.
* Nên dùng disable thay vì delete với coupon đã có usage.

---

## 8.4. Coupon Delete / Disable

Business rules:

| Case                               | Behavior                                               |
| ---------------------------------- | ------------------------------------------------------ |
| Coupon chưa từng dùng              | Có thể delete                                          |
| Coupon đã có usage                 | Không hard delete, nên disable                         |
| Coupon active đang dùng trong cart | Có thể disable nhưng cart sẽ validate lại khi checkout |
| Coupon expired                     | Có thể giữ lại để xem lịch sử                          |

Delete UX:

* Không dùng browser confirm mặc định.
* Dùng custom confirmation modal theo admin UI.
* Delete/disable bằng form hoặc AJAX tùy admin design.
* Danger action không đặt sát Save.

---

## 9. Public Coupon UI

## 9.1. Cart Page Coupon Form

Cart page cần có form nhập coupon trong cart summary.

UI gồm:

| Element                | Description                  |
| ---------------------- | ---------------------------- |
| Coupon input           | Nhập mã coupon               |
| Apply button           | Apply coupon                 |
| Applied coupon display | Hiển thị coupon đang áp dụng |
| Remove coupon button   | Gỡ coupon                    |
| Error message          | Hiển thị lỗi                 |
| Discount line          | Hiển thị số tiền giảm        |

Expected behavior:

* Customer nhập coupon code.
* Click Apply.
* Gửi request bằng JavaScript.
* Không reload page.
* Nếu thành công:

  * Hiển thị coupon đã apply.
  * Hiển thị discount line.
  * Cart subtotal/total preview cập nhật.
  * Toast success.
* Nếu lỗi:

  * Hiển thị error message gần input.
  * Không reload page.

---

## 9.2. Checkout Page Coupon Form

Nếu Checkout page đã có sau này, coupon có thể hiển thị ở checkout.

Trong Task 16:

* Có thể chuẩn bị route/service để checkout dùng lại.
* Không implement checkout UI nếu Task 17 chưa làm.
* Cart page là nơi chính để test coupon.

---

## 10. Public Routes

Routes đề xuất:

| Method | URL          | Name               | Description             |
| ------ | ------------ | ------------------ | ----------------------- |
| POST   | /cart/coupon | cart.coupon.apply  | Apply coupon vào cart   |
| DELETE | /cart/coupon | cart.coupon.remove | Remove coupon khỏi cart |

Admin routes đề xuất:

| Method    | URL                            | Name                  | Description           |
| --------- | ------------------------------ | --------------------- | --------------------- |
| GET       | /admin/coupons                 | admin.coupons.index   | Coupon list           |
| GET       | /admin/coupons/create          | admin.coupons.create  | Create coupon form    |
| POST      | /admin/coupons                 | admin.coupons.store   | Store coupon          |
| GET       | /admin/coupons/{coupon}/edit   | admin.coupons.edit    | Edit coupon form      |
| PUT/PATCH | /admin/coupons/{coupon}        | admin.coupons.update  | Update coupon         |
| DELETE    | /admin/coupons/{coupon}        | admin.coupons.destroy | Delete coupon         |
| PATCH     | /admin/coupons/{coupon}/status | admin.coupons.status  | Update status nếu cần |

---

## 11. Coupon Validation Rules

## 11.1. General Validation

Khi apply coupon:

| Rule                         | Description                  |
| ---------------------------- | ---------------------------- |
| Code required                | Coupon code không được trống |
| Code exists                  | Coupon phải tồn tại          |
| Status active                | Coupon phải active           |
| Start date valid             | Coupon đã bắt đầu            |
| End date valid               | Coupon chưa hết hạn          |
| Usage limit valid            | Chưa vượt tổng lượt dùng     |
| Usage per user valid         | Customer chưa vượt lượt dùng |
| Cart not empty               | Cart không được rỗng         |
| Cart has valid items         | Cart phải có item hợp lệ     |
| Minimum order amount         | Subtotal đủ điều kiện        |
| Product/category restriction | Cart có item đủ điều kiện    |

---

## 11.2. Admin Validation

Khi tạo/cập nhật coupon:

| Field                | Rule                                       |
| -------------------- | ------------------------------------------ |
| code                 | Required, unique, max length               |
| discount_type        | Required, valid type                       |
| discount_value       | Required, numeric, greater than 0          |
| max_discount_amount  | Nullable, numeric, greater than or equal 0 |
| min_order_amount     | Nullable, numeric, greater than or equal 0 |
| usage_limit          | Nullable, integer, min 1                   |
| usage_limit_per_user | Nullable, integer, min 1                   |
| starts_at            | Nullable datetime                          |
| ends_at              | Nullable datetime, after starts_at nếu có  |
| status               | Required, valid status                     |
| categories           | Optional, existing category IDs            |
| products             | Optional, existing product IDs             |

Percentage rules:

* `discount_value` không được vượt 100.
* `max_discount_amount` nên bắt buộc nếu business muốn giới hạn giảm tối đa.
* Nếu không có `max_discount_amount`, coupon percentage có thể giảm theo toàn bộ eligible subtotal.

Fixed amount rules:

* `discount_value` là số tiền giảm.
* Discount không được vượt eligible subtotal.

---

## 12. Discount Calculation Rules

## 12.1. Eligible Subtotal

Coupon không nhất thiết áp dụng cho toàn bộ cart.

Eligible subtotal là tổng tiền của các item đủ điều kiện.

Item đủ điều kiện nếu:

* Product active.
* Variant active nếu có.
* Item còn hợp lệ.
* Product/category phù hợp với coupon restriction.
* Quantity hợp lệ.
* Stock hợp lệ.

---

## 12.2. Percentage Discount

Công thức:

| Step | Description                                         |
| ---- | --------------------------------------------------- |
| 1    | Tính eligible subtotal                              |
| 2    | Discount = eligible subtotal x percentage           |
| 3    | Nếu có max discount amount, discount không vượt max |
| 4    | Discount không vượt eligible subtotal               |

Ví dụ:

* Eligible subtotal: 1,000,000
* Coupon: 10%
* Max discount: 80,000
* Discount cuối: 80,000

---

## 12.3. Fixed Amount Discount

Công thức:

| Step | Description                           |
| ---- | ------------------------------------- |
| 1    | Tính eligible subtotal                |
| 2    | Discount = fixed amount               |
| 3    | Discount không vượt eligible subtotal |

Ví dụ:

* Eligible subtotal: 200,000
* Coupon: giảm 300,000
* Discount cuối: 200,000

---

## 12.4. Cart Total Preview

Cart summary sau coupon:

| Line            | Description         |
| --------------- | ------------------- |
| Subtotal        | Tổng tiền hàng      |
| Discount        | Số tiền giảm        |
| Estimated Total | Subtotal - discount |

Lưu ý:

* Estimated total chưa bao gồm tax.
* Estimated total chưa bao gồm shipping.
* Estimated total chưa phải order total chính thức.
* Checkout task sẽ tính lại final total.

Message gợi ý:

`Taxes and shipping will be calculated at checkout.`

---

## 13. Coupon Restrictions

Coupon có thể không có restriction hoặc có restriction theo product/category.

## 13.1. No Restriction

Nếu coupon không có product/category restriction:

* Áp dụng cho toàn cart subtotal.
* Item unavailable không được tính.

## 13.2. Category Restriction

Nếu coupon có category restriction:

* Chỉ item thuộc category được chọn mới eligible.
* Nếu cart không có item thuộc category đó, coupon không apply được.

## 13.3. Product Restriction

Nếu coupon có product restriction:

* Chỉ product được chọn mới eligible.
* Nếu cart không có product được chọn, coupon không apply được.

## 13.4. Product And Category Restriction

Nếu coupon có cả product và category restriction:

* Product restriction nên được ưu tiên.
* Có thể hiểu là item eligible nếu thuộc product list hoặc category list.
* Business rule cần rõ ràng trong service.
* MVP nên dùng logic đơn giản: eligible nếu product thuộc product restriction hoặc category restriction.

---

## 14. Usage Limit Rules

## 14.1. Total Usage Limit

Nếu coupon có `usage_limit`:

* Không cho apply nếu `used_count >= usage_limit`.
* `used_count` không tăng khi chỉ apply vào cart.
* `used_count` sẽ tăng khi order hoàn tất ở task sau.

## 14.2. Usage Limit Per User

Nếu coupon có `usage_limit_per_user`:

* Logged-in customer: kiểm tra coupon_usages theo user_id.
* Guest user: trong MVP có thể không enforce chính xác per-user.
* Guest vẫn có thể apply coupon nếu coupon cho phép guest.
* Per-user limit chính thức nên enforce khi checkout với user/customer info.

---

## 15. Guest Coupon Behavior

Guest user có thể apply coupon vào cart.

Expected behavior:

* Coupon lưu vào guest cart.
* Cart summary cập nhật discount.
* Nếu guest login sau đó, coupon đi theo cart merge nếu còn hợp lệ.
* Nếu coupon không còn hợp lệ sau login/merge, remove coupon và hiển thị message nếu cần.

---

## 16. Customer Coupon Behavior

Customer đã đăng nhập có thể apply coupon.

Expected behavior:

* Coupon lưu vào customer active cart.
* Có thể kiểm tra usage limit per user.
* Nếu customer đã dùng vượt limit, không cho apply.
* Cart summary cập nhật ngay.

---

## 17. Revalidation Rules

Coupon cần được validate lại trong các trường hợp:

* Apply coupon.
* View cart.
* Update cart item quantity.
* Remove cart item.
* Clear cart.
* Cart merge sau login.
* Checkout ở task sau.

Nếu coupon không còn hợp lệ:

* Remove coupon khỏi cart hoặc đánh dấu invalid.
* Hiển thị message rõ ràng.
* Cart summary không tính discount.

Ví dụ:

`The coupon is no longer valid and has been removed from your cart.`

---

## 18. Apply Coupon Flow

Flow:

* Customer nhập coupon code.
* Click Apply.
* Frontend gửi AJAX request.
* Backend lấy current cart.
* Validate cart not empty.
* Normalize coupon code uppercase.
* Find coupon.
* Validate coupon status/time/usage.
* Calculate eligible subtotal.
* Validate min order amount.
* Calculate discount.
* Save coupon to cart.
* Return JSON summary.
* Frontend cập nhật cart summary.

---

## 19. Remove Coupon Flow

Flow:

* Customer click remove coupon.
* Frontend gửi AJAX request.
* Backend lấy current cart.
* Clear coupon fields.
* Recalculate cart summary.
* Return JSON.
* Frontend cập nhật cart summary.

---

## 20. AJAX Response Requirements

## 20.1. Apply Coupon Success

Response cần có:

| Field                     | Description               |
| ------------------------- | ------------------------- |
| success                   | true                      |
| message                   | Message thành công        |
| coupon_code               | Coupon đã apply           |
| discount_amount           | Discount raw              |
| formatted_discount_amount | Discount formatted        |
| subtotal                  | Subtotal raw              |
| formatted_subtotal        | Subtotal formatted        |
| estimated_total           | Estimated total raw       |
| formatted_estimated_total | Estimated total formatted |
| cart_summary_html         | Optional HTML partial     |

## 20.2. Remove Coupon Success

Response cần có:

| Field                     | Description               |
| ------------------------- | ------------------------- |
| success                   | true                      |
| message                   | Message thành công        |
| subtotal                  | Subtotal raw              |
| formatted_subtotal        | Subtotal formatted        |
| estimated_total           | Estimated total raw       |
| formatted_estimated_total | Estimated total formatted |
| cart_summary_html         | Optional HTML partial     |

## 20.3. Error Response

Response lỗi cần có:

| Field   | Description              |
| ------- | ------------------------ |
| success | false                    |
| message | Message lỗi              |
| errors  | Validation errors nếu có |

Business rules:

* AJAX không redirect.
* AJAX không reload page.
* AJAX không trả HTML error page.
* Validation error hiển thị gần coupon input.

---

## 21. UI Requirements

## 21.1. Admin Coupon UI

Admin coupon UI cần rõ ràng, chuyên nghiệp.

Yêu cầu:

* List coupon dễ tìm kiếm.
* Create/edit form chia section hợp lý.
* Discount type rõ ràng.
* Date range dễ nhập.
* Usage limit dễ hiểu.
* Product/category restriction có thể chọn bằng select/search nếu đã có component.
* Status rõ ràng.
* Danger action có confirmation modal.
* Không dùng browser confirm mặc định.

---

## 21.2. Public Coupon UI In Cart

Coupon form nên nằm trong cart summary.

UI trạng thái:

| State    | Display                              |
| -------- | ------------------------------------ |
| Empty    | Input + Apply button                 |
| Applying | Button disabled, text Applying...    |
| Applied  | Hiển thị coupon code + remove button |
| Error    | Error message dưới input             |
| Removed  | Quay lại input                       |

Applied coupon display ví dụ:

`Coupon SALE10 applied`

Discount line:

`Discount: -100,000₫`

---

## 21.3. Loading UX

Khi apply/remove coupon:

* Button phải disabled.
* Hiển thị loading state.
* Không cho bấm nhiều lần.
* Không reload page.
* Cart summary cập nhật mượt.
* Toast success/error rõ ràng.

---

## 22. Security Requirements

Yêu cầu bảo mật:

* CSRF cho apply/remove coupon.
* Chỉ admin mới được quản lý coupon.
* Validate coupon ở backend.
* Không tin discount amount từ frontend.
* Không tin coupon data từ frontend.
* Discount phải tính ở backend.
* Không expose lỗi kỹ thuật.
* Không cho customer apply coupon vào cart không thuộc về họ.
* Không cho update coupon đã bị soft deleted.
* Không cho bypass usage limit.

---

## 23. Performance Requirements

* Eager load cart items, product, category, variant nếu cần.
* Coupon validation không query lặp quá nhiều.
* Coupon calculation nên gom trong service.
* Cart summary response nên trả đủ dữ liệu để frontend update UI.
* Không tính toán nặng ở Blade nếu có thể đưa vào service.

---

## 24. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type               | Description                                                              |
| ------------------ | ------------------------------------------------------------------------ |
| Migration          | coupons, coupon_categories, coupon_products, coupon_usages, update carts |
| Models             | Coupon, CouponUsage                                                      |
| Controller Admin   | Admin CouponController                                                   |
| Controller Public  | CartCouponController                                                     |
| Service            | CouponService hoặc CartDiscountService                                   |
| Request Validation | StoreCouponRequest, UpdateCouponRequest, ApplyCouponRequest              |
| Routes             | Admin coupon routes, public coupon routes                                |
| Blade Admin        | Coupon list/create/edit                                                  |
| Blade Public       | Coupon form in cart summary                                              |
| Blade Partials     | Cart summary with discount                                               |
| JavaScript         | Apply/remove coupon AJAX                                                 |
| Tests              | Coupon feature tests                                                     |

---

## 25. Route Design

## 25.1. Admin Routes

| Method    | URL                            | Name                  | Purpose                |
| --------- | ------------------------------ | --------------------- | ---------------------- |
| GET       | /admin/coupons                 | admin.coupons.index   | Coupon list            |
| GET       | /admin/coupons/create          | admin.coupons.create  | Create form            |
| POST      | /admin/coupons                 | admin.coupons.store   | Store coupon           |
| GET       | /admin/coupons/{coupon}/edit   | admin.coupons.edit    | Edit form              |
| PUT/PATCH | /admin/coupons/{coupon}        | admin.coupons.update  | Update coupon          |
| DELETE    | /admin/coupons/{coupon}        | admin.coupons.destroy | Delete coupon          |
| PATCH     | /admin/coupons/{coupon}/status | admin.coupons.status  | Update status optional |

---

## 25.2. Public Routes

| Method | URL          | Name               | Purpose       |
| ------ | ------------ | ------------------ | ------------- |
| POST   | /cart/coupon | cart.coupon.apply  | Apply coupon  |
| DELETE | /cart/coupon | cart.coupon.remove | Remove coupon |

---

## 26. Error Handling

| Scenario                | Expected Result                 |
| ----------------------- | ------------------------------- |
| Coupon code empty       | Show validation error           |
| Coupon not found        | Show invalid coupon message     |
| Coupon inactive         | Show unavailable coupon message |
| Coupon expired          | Show expired coupon message     |
| Coupon not started      | Show not available yet message  |
| Cart empty              | Show cart empty error           |
| Minimum order not met   | Show min order amount message   |
| No eligible item        | Show not applicable message     |
| Usage limit exceeded    | Show usage limit message        |
| Per-user limit exceeded | Show user limit message         |
| AJAX server error       | Show toast error                |
| Network error           | Show network error              |
| Admin unauthorized      | Block access                    |

---

## 27. Test Cases

| Test Case ID | Scenario                                           | Expected Result              |
| ------------ | -------------------------------------------------- | ---------------------------- |
| TC-001       | Admin mở coupon list                               | Hiển thị danh sách coupon    |
| TC-002       | Admin tạo percentage coupon                        | Coupon được tạo              |
| TC-003       | Admin tạo fixed amount coupon                      | Coupon được tạo              |
| TC-004       | Admin tạo duplicate code                           | Hiển thị lỗi                 |
| TC-005       | Admin tạo percentage > 100                         | Hiển thị lỗi                 |
| TC-006       | Admin tạo ends_at trước starts_at                  | Hiển thị lỗi                 |
| TC-007       | Guest apply coupon hợp lệ                          | Coupon apply thành công      |
| TC-008       | Customer apply coupon hợp lệ                       | Coupon apply thành công      |
| TC-009       | Apply coupon không tồn tại                         | Hiển thị lỗi                 |
| TC-010       | Apply inactive coupon                              | Hiển thị lỗi                 |
| TC-011       | Apply expired coupon                               | Hiển thị lỗi                 |
| TC-012       | Apply coupon chưa bắt đầu                          | Hiển thị lỗi                 |
| TC-013       | Apply coupon khi cart rỗng                         | Hiển thị lỗi                 |
| TC-014       | Apply coupon chưa đạt min order                    | Hiển thị lỗi                 |
| TC-015       | Apply percentage coupon có max discount            | Discount không vượt max      |
| TC-016       | Apply fixed coupon lớn hơn subtotal                | Discount không vượt subtotal |
| TC-017       | Coupon category restriction có eligible item       | Apply thành công             |
| TC-018       | Coupon category restriction không có eligible item | Hiển thị lỗi                 |
| TC-019       | Coupon product restriction có eligible item        | Apply thành công             |
| TC-020       | Coupon product restriction không có eligible item  | Hiển thị lỗi                 |
| TC-021       | Apply coupon bằng AJAX                             | Không reload page            |
| TC-022       | Remove coupon bằng AJAX                            | Không reload page            |
| TC-023       | Remove coupon thành công                           | Cart summary cập nhật        |
| TC-024       | Update cart quantity sau khi apply coupon          | Discount được tính lại       |
| TC-025       | Remove cart item sau khi apply coupon              | Discount được tính lại       |
| TC-026       | Clear cart sau khi apply coupon                    | Coupon được clear            |
| TC-027       | Guest login sau khi apply coupon                   | Coupon merge/revalidate đúng |
| TC-028       | Customer vượt usage per user                       | Không apply được             |
| TC-029       | Coupon usage limit exceeded                        | Không apply được             |
| TC-030       | Mobile cart coupon UI                              | Layout không vỡ              |

---

## 28. Acceptance Criteria

Task này hoàn thành khi:

* [ ] Có database table `coupons`.
* [ ] Có database table `coupon_categories` nếu làm category restriction.
* [ ] Có database table `coupon_products` nếu làm product restriction.
* [ ] Có database table `coupon_usages` để chuẩn bị tracking usage.
* [ ] Cart có thể lưu coupon đang apply.
* [ ] Admin có thể xem danh sách coupon.
* [ ] Admin có thể tạo coupon.
* [ ] Admin có thể chỉnh sửa coupon.
* [ ] Admin có thể bật/tắt coupon.
* [ ] Admin không hard delete coupon đã có usage nếu có rule liên quan.
* [ ] Coupon code unique.
* [ ] Coupon code được normalize uppercase.
* [ ] Hỗ trợ percentage coupon.
* [ ] Hỗ trợ fixed amount coupon.
* [ ] Hỗ trợ max discount amount cho percentage coupon.
* [ ] Hỗ trợ min order amount.
* [ ] Hỗ trợ starts_at và ends_at.
* [ ] Hỗ trợ usage limit.
* [ ] Hỗ trợ usage limit per user cho logged-in customer.
* [ ] Hỗ trợ product/category restriction ở mức MVP.
* [ ] Guest có thể apply coupon.
* [ ] Customer có thể apply coupon.
* [ ] Apply coupon bằng JavaScript, không reload page.
* [ ] Remove coupon bằng JavaScript, không reload page.
* [ ] Cart summary cập nhật discount sau khi apply coupon.
* [ ] Cart summary cập nhật lại sau khi remove coupon.
* [ ] Discount không vượt eligible subtotal.
* [ ] Coupon được revalidate khi cart thay đổi.
* [ ] Coupon invalid được remove hoặc hiển thị lỗi rõ.
* [ ] Không tính coupon cho unavailable item.
* [ ] Không tăng `used_count` khi chỉ apply coupon trong cart.
* [ ] Không tạo Order trong Task 16.
* [ ] Không implement Checkout trong Task 16.
* [ ] Không implement Payment trong Task 16.
* [ ] Không dùng Vue.js.

---

## 29. Commands

Sau khi implement, chạy các lệnh:

| Command                | Purpose               |
| ---------------------- | --------------------- |
| php artisan migrate    | Chạy migration        |
| php artisan route:list | Kiểm tra route        |
| php artisan test       | Chạy test nếu có      |
| npm run build          | Build frontend assets |
| php artisan serve      | Chạy local server     |

URL test:

`http://127.0.0.1:8000/admin/coupons`

`http://127.0.0.1:8000/cart`

---

## 30. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-15-cart.md
* docs/tasks/task-16-coupon.md

Sau đó implement Task 16: Coupon theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 16.
* Implement admin coupon management.
* Implement public apply coupon trong cart.
* Implement public remove coupon trong cart.
* Tạo các migration cần thiết cho coupons và các bảng liên quan.
* Coupon code phải unique và normalize uppercase.
* Hỗ trợ coupon percentage.
* Hỗ trợ coupon fixed amount.
* Hỗ trợ max discount amount cho percentage coupon.
* Hỗ trợ min order amount.
* Hỗ trợ starts_at và ends_at.
* Hỗ trợ usage limit.
* Hỗ trợ usage limit per user cho logged-in customer.
* Hỗ trợ product/category restriction ở mức MVP nếu đã có dữ liệu product/category.
* Apply coupon bằng JavaScript, không reload page.
* Remove coupon bằng JavaScript, không reload page.
* Cart summary phải cập nhật discount ngay sau khi apply/remove coupon.
* Discount không được vượt eligible subtotal.
* Không tính discount cho unavailable cart item.
* Coupon phải revalidate khi cart thay đổi.
* Không tăng used_count khi chỉ apply coupon vào cart.
* used_count/coupon_usages sẽ xử lý chính thức khi order thành công ở task sau.
* Không implement Checkout.
* Không implement Order.
* Không implement Payment.
* Không dùng Vue.js.
* Dùng Blade + Tailwind CSS + Alpine.js hoặc Fetch API.
* Sau khi làm xong, báo cáo file đã tạo/sửa và lệnh cần chạy để test.
