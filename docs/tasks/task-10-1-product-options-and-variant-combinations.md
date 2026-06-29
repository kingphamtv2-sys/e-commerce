Bạn tạo file:

`docs/tasks/task-10-1-product-options-and-variant-combinations.md`

và copy nội dung dưới đây vào file.

# Task 10.1: Product Options and Variant Combinations

## 1. Overview

Task này dùng để nâng cấp hệ thống product variant từ dạng đơn giản sang dạng linh hoạt.

Mục tiêu là giúp một product có thể tạo nhiều option và nhiều tổ hợp variant khác nhau, dùng được cho nhiều ngành hàng như:

* Quần áo
* Giày dép
* Đồ điện tử
* Mỹ phẩm
* Phụ kiện
* Đồ gia dụng

Ví dụ quần áo:

| Option | Values             |
| ------ | ------------------ |
| Color  | Black, White, Blue |
| Size   | S, M, L, XL        |

Variant combinations:

| Variant   | SKU            |
| --------- | -------------- |
| Black / M | TSHIRT-BLACK-M |
| Black / L | TSHIRT-BLACK-L |
| White / M | TSHIRT-WHITE-M |

Ví dụ đồ điện tử:

| Option  | Values              |
| ------- | ------------------- |
| Color   | Black, Blue, Pink   |
| Storage | 128GB, 256GB, 512GB |

Variant combinations:

| Variant       | SKU              |
| ------------- | ---------------- |
| Black / 128GB | IPHONE-BLACK-128 |
| Black / 256GB | IPHONE-BLACK-256 |
| Blue / 128GB  | IPHONE-BLUE-128  |

Task này cần làm trước Task 15 Cart để Cart có thể lưu đúng variant customer đã chọn.

---

## 2. Objectives

Sau khi hoàn thành task này, hệ thống cần có:

* Product có thể có nhiều option.
* Mỗi option có nhiều value.
* Có thể tạo variant từ tổ hợp option values.
* Variant dùng được cho quần áo, điện tử và nhiều loại sản phẩm khác.
* Mỗi variant có SKU riêng.
* Mỗi variant có price riêng nếu cần.
* Mỗi variant có sale price riêng nếu cần.
* Mỗi variant có status riêng.
* Mỗi variant có thể liên kết với inventory ở Task 12.
* Product detail page có thể hiển thị option selector theo Task 14.
* Cart ở Task 15 có thể lưu đúng variant đã chọn.
* Order ở Task 19 có thể lưu snapshot variant.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong task này:

* Tạo Product Options.
* Tạo Product Option Values.
* Tạo Variant Combinations.
* Liên kết variant với option values.
* Quản lý option trong màn hình product edit.
* Quản lý option value trong màn hình product edit.
* Quản lý variant combinations trong màn hình product edit.
* Tạo SKU riêng cho từng variant.
* Cho phép nhập giá riêng cho từng variant.
* Cho phép nhập sale price riêng cho từng variant.
* Cho phép bật hoặc tắt từng variant.
* Cho phép sort order cho option và option value.
* Hiển thị danh sách variant rõ ràng trong admin.
* Cập nhật Product Management để dùng variant combination mới.
* Chuẩn bị dữ liệu cho Inventory, Product Detail, Cart và Order.

### 3.2. Out of Scope

Các phần chưa làm trong task này:

* Inventory adjustment chi tiết.
* Add to cart.
* Checkout.
* Order creation.
* Variant image riêng.
* Variant translation riêng.
* Option template dùng chung toàn hệ thống.
* Import/export variant bằng Excel.
* Tự động generate toàn bộ combination nâng cao.
* Barcode.
* Bulk price update.
* Multi warehouse.
* Public variant selector hoàn chỉnh nếu Task 14 đã làm rồi thì chỉ cập nhật nhẹ.

---

## 4. Why This Task Is Needed

Task 10 trước đó chỉ hỗ trợ variant cơ bản như:

* Size M
* Size L
* 128GB Black
* 256GB Blue

Cách này vẫn dùng được nhưng chưa đủ tốt nếu product có nhiều option riêng biệt.

Ví dụ customer cần chọn:

* Màu trước
* Sau đó chọn size

Hoặc với điện thoại:

* Chọn màu
* Sau đó chọn dung lượng

Nếu không có Product Options và Variant Combinations, Task 15 Cart sẽ khó xác định chính xác customer đã chọn variant nào.

Vì vậy cần bổ sung task này trước khi làm Cart.

---

## 5. User Roles

| Role        | Permission                                        |
| ----------- | ------------------------------------------------- |
| super_admin | Có toàn quyền quản lý product options và variants |
| admin       | Có quyền quản lý product options và variants      |
| staff       | Có thể xem hoặc thao tác giới hạn sau này         |
| customer    | Không được truy cập admin                         |

Route quản lý options và variants chỉ dành cho admin.

Customer chỉ sử dụng variant selector ở frontend trong task public sau.

---

## 6. Functional Requirements

## FR-01: Product Options

Admin có thể tạo nhiều option cho một product.

Ví dụ:

| Product     | Options             |
| ----------- | ------------------- |
| Áo thun nam | Color, Size         |
| iPhone 15   | Color, Storage      |
| Laptop      | RAM, Storage, Color |

Option fields:

| Field        | Description                            |
| ------------ | -------------------------------------- |
| Product      | Product sở hữu option                  |
| Name         | Tên option, ví dụ Color, Size, Storage |
| Display Name | Tên hiển thị nếu cần                   |
| Sort Order   | Thứ tự hiển thị                        |
| Status       | Active hoặc Inactive                   |

Business rules:

* Một product có thể có nhiều option.
* Option name là bắt buộc.
* Option name không nên trùng trong cùng một product.
* Option inactive không hiển thị ở frontend.
* Option cần có sort order để hiển thị đúng thứ tự.

---

## FR-02: Product Option Values

Mỗi option có thể có nhiều value.

Ví dụ:

| Option  | Values              |
| ------- | ------------------- |
| Color   | Black, White, Blue  |
| Size    | S, M, L, XL         |
| Storage | 128GB, 256GB, 512GB |
| RAM     | 8GB, 16GB, 32GB     |

Option value fields:

| Field         | Description                    |
| ------------- | ------------------------------ |
| Option        | Option sở hữu value            |
| Value         | Giá trị, ví dụ Black, M, 128GB |
| Display Value | Giá trị hiển thị nếu cần       |
| Color Code    | Mã màu nếu option là color     |
| Sort Order    | Thứ tự hiển thị                |
| Status        | Active hoặc Inactive           |

Business rules:

* Một option có thể có nhiều value.
* Value là bắt buộc.
* Value không nên trùng trong cùng một option.
* Color code chỉ dùng nếu option là color.
* Option value inactive không hiển thị ở frontend.
* Option value cần có sort order.

---

## FR-03: Variant Combinations

Admin có thể tạo variant từ tổ hợp option values.

Ví dụ product Áo thun nam:

| Color | Size | SKU            |
| ----- | ---- | -------------- |
| Black | M    | TSHIRT-BLACK-M |
| Black | L    | TSHIRT-BLACK-L |
| White | M    | TSHIRT-WHITE-M |
| White | L    | TSHIRT-WHITE-L |

Ví dụ product iPhone:

| Color | Storage | SKU              |
| ----- | ------- | ---------------- |
| Black | 128GB   | IPHONE-BLACK-128 |
| Black | 256GB   | IPHONE-BLACK-256 |
| Blue  | 128GB   | IPHONE-BLUE-128  |

Variant fields:

| Field      | Description                 |
| ---------- | --------------------------- |
| Product    | Product sở hữu variant      |
| SKU        | SKU riêng của variant       |
| Name       | Tên variant hiển thị        |
| Price      | Giá riêng nếu có            |
| Sale Price | Giá khuyến mãi riêng nếu có |
| Status     | Active hoặc Inactive        |

Business rules:

* Mỗi variant thuộc về một product.
* Mỗi variant cần có SKU unique.
* Một tổ hợp option values không được tạo trùng.
* Variant inactive không hiển thị ở frontend.
* Nếu variant không có price riêng, dùng product price.
* Nếu variant không có sale price riêng, dùng product sale price.
* Inventory sẽ quản lý theo variant nếu product có variant.

---

## FR-04: Variant Option Values

Mỗi variant cần lưu các option value đã chọn.

Ví dụ:

Variant: TSHIRT-BLACK-M

| Option | Value |
| ------ | ----- |
| Color  | Black |
| Size   | M     |

Variant: IPHONE-BLACK-128

| Option  | Value |
| ------- | ----- |
| Color   | Black |
| Storage | 128GB |

Business rules:

* Một variant có nhiều option values.
* Mỗi variant chỉ được chọn một value cho mỗi option.
* Một tổ hợp option values không được trùng trong cùng product.
* Nếu product có 2 options, variant cần đủ value cho 2 options.
* Nếu product có 3 options, variant cần đủ value cho 3 options.

---

## FR-05: Product Edit Integration

Task này cần tích hợp vào màn hình Product Edit đã làm ở Task 10.

Trong màn hình edit product, cần có section:

* Product Options
* Option Values
* Variant Combinations

Expected behavior:

* Admin có thể tạo option trong product edit.
* Admin có thể tạo option values trong product edit.
* Admin có thể tạo variant combinations trong product edit.
* Admin có thể sửa SKU, price, sale price, status của variant.
* Admin có thể xóa variant nếu chưa được sử dụng.
* Admin có thể disable variant.
* Không làm hỏng phần translation của product.

### FR-05.1: Async Save

Các form chỉnh sửa Product Option, Product Option Value và Variant Combination trong màn hình Product Edit phải hỗ trợ lưu bất đồng bộ.

Yêu cầu:

* Nút Save riêng của từng option, option value và variant combination phải gửi dữ liệu bằng JavaScript và Fetch API, không reload trang.
* Mỗi form con phải hiển thị rõ một trong các trạng thái: `unsaved`, `saving`, `saved`, `error`.
* Khi người dùng thay đổi input hoặc checkbox, trạng thái phải chuyển về `unsaved`.
* Khi request đang chạy, trạng thái phải là `saving`; khi thành công chuyển sang `saved`; khi validation hoặc server lỗi chuyển sang `error` và hiển thị thông báo phù hợp.
* Nút Save Changes của product form phải lưu tuần tự tất cả option, option value và variant combination còn thay đổi trước khi submit product form.
* Nếu bất kỳ form con nào lưu thất bại, product form không được submit và UI phải đưa người dùng tới form lỗi.
* Validation phía server và database constraint vẫn là nguồn xác thực cuối cùng để không tạo duplicate option, option value hoặc variant combination.
* Không dùng Vue.js. Có thể dùng Blade, Tailwind CSS, Alpine.js và Fetch API.

---

## FR-06: Product Create Integration

Trong màn hình create product, có thể hỗ trợ variant ngay hoặc để admin tạo product trước rồi edit variant sau.

Để đơn giản cho MVP:

* Product create chỉ cần tạo product basic.
* Sau khi product được tạo, admin vào edit product để tạo options và variants.

Business rules:

* Không bắt buộc tạo variant khi tạo product.
* Product không có variant vẫn bán được như product thường.
* Product có variant thì frontend cần customer chọn variant trước khi add to cart ở Task 15.

---

## FR-07: Variant SKU

Mỗi variant cần có SKU riêng.

Business rules:

* Variant SKU là bắt buộc.
* Variant SKU phải unique toàn hệ thống.
* Variant SKU không nên trùng với product SKU.
* SKU nên được trim khoảng trắng.
* SKU dùng cho inventory, cart, order và admin report.

---

## FR-08: Variant Price

Variant có thể có giá riêng.

Business rules:

* Nếu variant price có giá trị, dùng variant price.
* Nếu variant price rỗng, dùng product price.
* Nếu variant sale price có giá trị, dùng variant sale price.
* Nếu variant sale price rỗng, dùng product sale price.
* Sale price không nên lớn hơn price áp dụng.
* Price không được âm.
* Cost price variant chưa cần trong task này.

---

## FR-09: Variant Status

Variant có trạng thái riêng.

Business rules:

* Variant active mới hiển thị ngoài frontend.
* Variant inactive không cho customer chọn.
* Product active nhưng variant inactive thì variant đó không bán.
* Nếu toàn bộ variant inactive, product có thể hiển thị Out of Stock hoặc không cho chọn variant.

---

## FR-10: Variant Inventory Compatibility

Inventory đã làm ở Task 12 cần tương thích với variant combination.

Business rules:

* Product không có variant thì inventory theo product.
* Product có variant thì inventory theo variant.
* Khi tạo variant mới, hệ thống cần có thể tạo inventory stock record cho variant.
* Khi xóa hoặc disable variant, inventory không nên bị mất dữ liệu quan trọng.
* Inventory log không bị xóa khi variant đã có thay đổi tồn kho.

Trong task này chỉ cần đảm bảo cấu trúc dữ liệu variant sẵn sàng cho Inventory.

---

## FR-11: Product Detail Compatibility

Task 14 Product Detail Page cần dùng variant options để hiển thị selector.

Expected behavior ở frontend sau khi cập nhật:

* Nếu product có option Color, hiển thị Color selector.
* Nếu product có option Size, hiển thị Size selector.
* Nếu product có option Storage, hiển thị Storage selector.
* Customer chọn đủ option values thì hệ thống xác định được variant.
* Variant được chọn sẽ quyết định SKU, price, sale price và stock.

Task này tập trung admin/data, nhưng cần chuẩn bị đủ dữ liệu cho frontend.

---

## FR-12: Cart Compatibility

Task 15 Cart cần lưu đúng variant.

Khi add to cart sau này, cart item cần biết:

* Product
* Variant nếu có
* Option values đã chọn
* SKU tại thời điểm add
* Price tại thời điểm add
* Quantity

Task này chưa implement Cart nhưng phải thiết kế variant để Cart dùng được.

---

## FR-13: Order Snapshot Compatibility

Task 19 Order Creation cần lưu snapshot variant.

Order item sau này cần lưu:

* Product name
* Product SKU
* Variant SKU
* Variant display name
* Selected options
* Price
* Quantity

Task này chưa implement Order nhưng phải đảm bảo variant data rõ ràng.

---

## 7. UI / Screen Design

## 7.1. Product Options Section

Trong product edit, thêm section:

`Product Options`

Section này hiển thị danh sách options của product.

Columns:

| Column      | Description          |
| ----------- | -------------------- |
| Option Name | Color, Size, Storage |
| Sort Order  | Thứ tự               |
| Status      | Active hoặc Inactive |
| Actions     | Edit, Delete         |

Admin có thể:

* Add option
* Edit option
* Delete option
* Enable hoặc disable option

---

## 7.2. Product Option Values Section

Mỗi option có danh sách values.

Ví dụ UI:

| Option | Values             |
| ------ | ------------------ |
| Color  | Black, White, Blue |
| Size   | S, M, L, XL        |

Value display nên rõ ràng:

* Nếu option là color và có color code, hiển thị swatch màu.
* Nếu option là text, hiển thị chip hoặc badge.

Admin có thể:

* Add value
* Edit value
* Delete value
* Enable hoặc disable value

---

## 7.3. Variant Combinations Section

Trong product edit, thêm section:

`Variant Combinations`

Table hiển thị:

| Column     | Description                 |
| ---------- | --------------------------- |
| Variant    | Tổ hợp option values        |
| SKU        | SKU variant                 |
| Price      | Giá riêng                   |
| Sale Price | Giá sale riêng              |
| Stock      | Tồn kho nếu đã có inventory |
| Status     | Active hoặc Inactive        |
| Actions    | Edit, Delete                |

Ví dụ:

| Variant   | SKU            |   Price | Stock | Status |
| --------- | -------------- | ------: | ----: | ------ |
| Black / M | TSHIRT-BLACK-M | 200,000 |    20 | Active |
| Black / L | TSHIRT-BLACK-L | 200,000 |    10 | Active |
| White / M | TSHIRT-WHITE-M | 190,000 |    15 | Active |

---

## 7.4. Create Variant UI

Admin có thể tạo variant bằng cách chọn option values.

Ví dụ product có options Color và Size:

| Field      | Value          |
| ---------- | -------------- |
| Color      | Black          |
| Size       | M              |
| SKU        | TSHIRT-BLACK-M |
| Price      | Optional       |
| Sale Price | Optional       |
| Status     | Active         |

Expected behavior:

* Form hiển thị dropdown hoặc select theo từng option.
* Admin phải chọn đủ option values.
* Không cho tạo combination trùng.
* SKU là bắt buộc.
* Nếu product chưa có option hoặc option value, cần hiển thị hướng dẫn tạo option trước.

---

## 7.5. Modern Admin UI

UI vẫn dùng admin layout hiện tại.

Yêu cầu:

* Section rõ ràng, không rối.
* Option values nên hiển thị dạng chip/badge.
* Variant table full width.
* Button Add Option, Add Value, Add Variant rõ ràng.
* Form không quá dài gây khó dùng.
* Có empty state nếu product chưa có option/variant.
* Không làm hỏng layout full màn hình đã chỉnh trước đó.

---

## 8. Database Design

## 8.1. Table: product_options

Bảng `product_options` dùng để lưu các option của product.

| Column       | Type            | Nullable | Default        | Description                    |
| ------------ | --------------- | -------- | -------------- | ------------------------------ |
| id           | bigint unsigned | No       | auto increment | Primary key                    |
| product_id   | bigint unsigned | No       | null           | Product                        |
| name         | varchar(100)    | No       | null           | Option name, ví dụ Color, Size |
| display_name | varchar(100)    | Yes      | null           | Tên hiển thị                   |
| sort_order   | int             | No       | 0              | Thứ tự hiển thị                |
| status       | tinyint         | No       | 1              | 1 active, 0 inactive           |
| created_at   | timestamp       | Yes      | null           | Created time                   |
| updated_at   | timestamp       | Yes      | null           | Updated time                   |

Indexes:

| Index             | Description                                    |
| ----------------- | ---------------------------------------------- |
| product_id        | Tìm option theo product                        |
| product_id + name | Không cho trùng option name trong cùng product |
| status            | Lọc active/inactive                            |
| sort_order        | Sắp xếp option                                 |

---

## 8.2. Table: product_option_values

Bảng `product_option_values` dùng để lưu giá trị của option.

| Column            | Type            | Nullable | Default        | Description                  |
| ----------------- | --------------- | -------- | -------------- | ---------------------------- |
| id                | bigint unsigned | No       | auto increment | Primary key                  |
| product_option_id | bigint unsigned | No       | null           | Product option               |
| value             | varchar(100)    | No       | null           | Value, ví dụ Black, M, 128GB |
| display_value     | varchar(100)    | Yes      | null           | Giá trị hiển thị             |
| color_code        | varchar(20)     | Yes      | null           | Mã màu nếu là color          |
| sort_order        | int             | No       | 0              | Thứ tự hiển thị              |
| status            | tinyint         | No       | 1              | 1 active, 0 inactive         |
| created_at        | timestamp       | Yes      | null           | Created time                 |
| updated_at        | timestamp       | Yes      | null           | Updated time                 |

Indexes:

| Index                     | Description                             |
| ------------------------- | --------------------------------------- |
| product_option_id         | Tìm value theo option                   |
| product_option_id + value | Không cho trùng value trong cùng option |
| status                    | Lọc active/inactive                     |
| sort_order                | Sắp xếp value                           |

---

## 8.3. Update Table: product_variants

Bảng `product_variants` đã có từ Task 10 cần được dùng làm bảng lưu variant combination.

Nếu bảng hiện tại còn đơn giản, cần cập nhật để phù hợp với variant combination.

Fields cần có:

| Column     | Description             |
| ---------- | ----------------------- |
| id         | Primary key             |
| product_id | Product                 |
| sku        | SKU riêng của variant   |
| name       | Tên variant hiển thị    |
| price      | Giá riêng nếu có        |
| sale_price | Giá sale riêng nếu có   |
| status     | Active hoặc inactive    |
| created_at | Created time            |
| updated_at | Updated time            |
| deleted_at | Soft delete nếu đã dùng |

Business rules:

* SKU unique.
* Variant thuộc một product.
* Variant name có thể tự tạo từ option values.
* Variant có thể soft delete.

---

## 8.4. Table: product_variant_option_values

Bảng `product_variant_option_values` dùng để nối variant với option values.

| Column                  | Type            | Nullable | Default        | Description  |
| ----------------------- | --------------- | -------- | -------------- | ------------ |
| id                      | bigint unsigned | No       | auto increment | Primary key  |
| product_variant_id      | bigint unsigned | No       | null           | Variant      |
| product_option_id       | bigint unsigned | No       | null           | Option       |
| product_option_value_id | bigint unsigned | No       | null           | Option value |
| created_at              | timestamp       | Yes      | null           | Created time |
| updated_at              | timestamp       | Yes      | null           | Updated time |

Indexes:

| Index                                  | Description                                 |
| -------------------------------------- | ------------------------------------------- |
| product_variant_id                     | Tìm option values theo variant              |
| product_option_id                      | Tìm theo option                             |
| product_option_value_id                | Tìm theo value                              |
| product_variant_id + product_option_id | Một variant chỉ có một value cho một option |

Lưu ý quan trọng:

* Cần đảm bảo không tạo duplicate combination trong cùng product.
* Có thể kiểm tra duplicate bằng logic service khi lưu variant.

---

## 8.5. Relationship

Quan hệ dữ liệu:

| Relationship                                   | Description                             |
| ---------------------------------------------- | --------------------------------------- |
| Product has many Product Options               | Một product có nhiều option             |
| Product Option has many Product Option Values  | Một option có nhiều value               |
| Product has many Product Variants              | Một product có nhiều variant            |
| Product Variant has many Variant Option Values | Một variant có nhiều option values      |
| Product Variant belongs to many Option Values  | Variant được tạo từ nhiều option values |
| Product Variant has inventory stock            | Inventory theo variant ở Task 12        |

---

## 9. Route Design

Các route admin có thể cần:

| Method | URL                                      | Description             |
| ------ | ---------------------------------------- | ----------------------- |
| POST   | `/admin/products/{product}/options`      | Tạo option              |
| PUT    | `/admin/product-options/{option}`        | Cập nhật option         |
| DELETE | `/admin/product-options/{option}`        | Xóa option              |
| POST   | `/admin/product-options/{option}/values` | Tạo option value        |
| PUT    | `/admin/product-option-values/{value}`   | Cập nhật option value   |
| DELETE | `/admin/product-option-values/{value}`   | Xóa option value        |
| POST   | `/admin/products/{product}/variants`     | Tạo variant combination |
| PUT    | `/admin/product-variants/{variant}`      | Cập nhật variant        |
| DELETE | `/admin/product-variants/{variant}`      | Xóa variant             |

Tất cả route trên cần được bảo vệ bởi admin authentication.

Nếu muốn đơn giản hơn, có thể xử lý option và variant trực tiếp trong ProductController, nhưng không được làm code khó bảo trì.

---

## 10. Validation Rules

## 10.1. Product Option Validation

| Field        | Rule                                                |
| ------------ | --------------------------------------------------- |
| name         | Required, max 100 characters, unique within product |
| display_name | Optional, max 100 characters                        |
| sort_order   | Optional, integer, min 0                            |
| status       | Required                                            |

Business validation:

* Product phải tồn tại.
* Option name không được trùng trong cùng product.
* Không xóa option nếu option đang được dùng bởi variant, trừ khi xử lý xóa variant liên quan rõ ràng.

---

## 10.2. Product Option Value Validation

| Field         | Rule                                               |
| ------------- | -------------------------------------------------- |
| value         | Required, max 100 characters, unique within option |
| display_value | Optional, max 100 characters                       |
| color_code    | Optional, max 20 characters                        |
| sort_order    | Optional, integer, min 0                           |
| status        | Required                                           |

Business validation:

* Option phải tồn tại.
* Value không được trùng trong cùng option.
* Color code chỉ cần validate đơn giản.
* Không xóa option value nếu value đang được dùng bởi variant, trừ khi xử lý variant liên quan rõ ràng.

---

## 10.3. Variant Combination Validation

| Field                  | Rule                                         |
| ---------------------- | -------------------------------------------- |
| SKU                    | Required, unique, max 100 characters         |
| Selected option values | Required, đủ value cho tất cả active options |
| Price                  | Optional, numeric, min 0                     |
| Sale Price             | Optional, numeric, min 0                     |
| Status                 | Required                                     |

Business validation:

* Product phải tồn tại.
* Variant SKU không được trùng.
* Variant SKU không nên trùng product SKU.
* Phải chọn đủ option values cho các active options.
* Mỗi option chỉ được chọn một value.
* Không được tạo duplicate combination.
* Sale price không nên lớn hơn price áp dụng.
* Option value được chọn phải thuộc option của product hiện tại.

---

## 11. Business Logic

## 11.1. Create Option Flow

* Admin mở edit product.
* Admin thêm option mới.
* Hệ thống validate option name.
* Hệ thống lưu option.
* Hệ thống redirect back với thông báo thành công.
* Hệ thống hiển thị option trong Product Options section.

---

## 11.2. Create Option Value Flow

* Admin chọn option.
* Admin thêm value mới.
* Hệ thống validate value.
* Hệ thống lưu option value.
* Hệ thống redirect back với thông báo thành công.
* Hệ thống hiển thị value trong option.

---

## 11.3. Create Variant Combination Flow

* Admin đã tạo options và option values.
* Admin mở form tạo variant.
* Admin chọn một value cho mỗi option.
* Admin nhập SKU.
* Admin nhập price hoặc sale price nếu cần.
* Hệ thống validate dữ liệu.
* Hệ thống kiểm tra duplicate combination.
* Hệ thống tạo variant.
* Hệ thống lưu liên kết variant với option values.
* Hệ thống tạo hoặc chuẩn bị inventory stock cho variant nếu phù hợp.
* Hệ thống redirect back với thông báo thành công.

---

## 11.4. Update Variant Flow

* Admin sửa variant.
* Admin có thể đổi SKU, price, sale price, status.
* Admin có thể đổi option values nếu cần.
* Hệ thống validate dữ liệu.
* Hệ thống kiểm tra duplicate combination.
* Hệ thống cập nhật variant.
* Hệ thống cập nhật liên kết option values.
* Hệ thống clear cache product nếu có.
* Hệ thống redirect back với thông báo thành công.

---

## 11.5. Delete Variant Flow

* Admin click delete variant.
* Hệ thống kiểm tra variant có dữ liệu inventory/order hay chưa.
* Nếu chưa có dữ liệu quan trọng, cho phép xóa hoặc soft delete.
* Nếu đã có dữ liệu quan trọng, không hard delete, nên disable.
* Hệ thống không làm mất inventory log quan trọng.
* Hệ thống redirect back với thông báo phù hợp.

---

## 11.6. Duplicate Combination Flow

* Hệ thống lấy danh sách option value IDs được chọn.
* Hệ thống so sánh với các variant hiện tại của cùng product.
* Nếu đã tồn tại variant có cùng bộ option values, không cho tạo mới.
* Khi update variant, bỏ qua chính variant hiện tại.
* Hiển thị lỗi rõ ràng cho admin.

---

## 11.7. Variant Display Name Flow

Variant name có thể được nhập thủ công hoặc tự tạo.

Nếu không nhập name, hệ thống có thể tự tạo từ option values.

Ví dụ:

| Option Values         | Variant Name          |
| --------------------- | --------------------- |
| Black + M             | Black / M             |
| Blue + 128GB          | Blue / 128GB          |
| 16GB + 512GB + Silver | 16GB / 512GB / Silver |

Business rules:

* Variant name nên dễ đọc.
* Tên variant dùng để hiển thị trong admin, cart và order snapshot sau này.
* Nếu admin đổi option value, variant name cần được cập nhật hoặc giữ theo cách nhất quán.

---

## 12. Error Handling

| Case                                   | Expected Handling                            |
| -------------------------------------- | -------------------------------------------- |
| Product không tồn tại                  | Hiển thị lỗi phù hợp                         |
| Option name trùng                      | Hiển thị lỗi validation                      |
| Option value trùng                     | Hiển thị lỗi validation                      |
| SKU trùng                              | Hiển thị lỗi validation                      |
| Thiếu option value khi tạo variant     | Hiển thị lỗi validation                      |
| Chọn option value không thuộc product  | Không cho lưu                                |
| Tạo duplicate combination              | Không cho lưu                                |
| Sale price lớn hơn price               | Hiển thị lỗi validation                      |
| Xóa option đang dùng bởi variant       | Không cho xóa hoặc yêu cầu xóa variant trước |
| Xóa option value đang dùng bởi variant | Không cho xóa hoặc yêu cầu xóa variant trước |
| Xóa variant đã có order sau này        | Không hard delete                            |
| Customer truy cập route admin variant  | Bị chặn                                      |

---

## 13. Security

Yêu cầu bảo mật:

* Chỉ admin mới được quản lý product options và variants.
* Customer không được truy cập route admin.
* Validate toàn bộ input.
* Không lưu dữ liệu request ngoài danh sách field cho phép.
* Không expose cost price nếu có sau này.
* Form phải có CSRF protection.
* Không hiển thị lỗi kỹ thuật trực tiếp ra màn hình production.
* Nội dung nhập từ admin cần được escape khi hiển thị.
* Không cho tạo dữ liệu variant không thuộc product hiện tại.

---

## 14. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type                  | Description                                                                                  |
| --------------------- | -------------------------------------------------------------------------------------------- |
| Model                 | Product Option model, Product Option Value model, Product Variant Option Value model         |
| Model Update          | Cập nhật Product Variant model nếu cần                                                       |
| Controller            | Admin product option controller, product option value controller, product variant controller |
| Request               | Validate option, option value, variant combination                                           |
| Service               | Product variant service nếu cần                                                              |
| Migration             | Tạo hoặc cập nhật bảng liên quan options và variant combinations                             |
| View                  | Product edit sections cho options, values, variants                                          |
| Route                 | Admin routes cho option và variant                                                           |
| Inventory Integration | Đảm bảo variant tương thích inventory                                                        |
| Product Integration   | Cập nhật Product Management UI                                                               |

Lưu ý:

* Nếu bảng liên quan đã tồn tại thì không tạo migration trùng.
* Không sửa module không liên quan.
* Không implement Cart trong task này.
* Không implement Checkout trong task này.
* Không implement Order trong task này.
* Không implement public add to cart trong task này.

---

## 15. Commands

Sau khi Codex implement xong, chạy các lệnh kiểm tra sau:

| Command                | Purpose                              |
| ---------------------- | ------------------------------------ |
| php artisan migrate    | Chạy migration                       |
| php artisan route:list | Kiểm tra route                       |
| php artisan serve      | Chạy local server                    |
| npm run build          | Build frontend nếu có thay đổi asset |

URL test:

`http://127.0.0.1:8000/admin/products`

Vào màn hình edit product để kiểm tra:

* Product Options
* Product Option Values
* Variant Combinations

---

## 16. Test Cases

| Test Case ID | Scenario | Expected Result |
|---|---|
| TC-001 | Admin thêm option Color | Tạo thành công |
| TC-002 | Admin thêm option Size | Tạo thành công |
| TC-003 | Admin thêm option trùng tên trong cùng product | Hiển thị lỗi validation |
| TC-004 | Admin thêm value Black cho Color | Tạo thành công |
| TC-005 | Admin thêm value M cho Size | Tạo thành công |
| TC-006 | Admin thêm value trùng trong cùng option | Hiển thị lỗi validation |
| TC-007 | Tạo variant Black / M | Tạo thành công |
| TC-008 | Tạo variant Black / M lần hai | Không cho tạo duplicate combination |
| TC-009 | Tạo variant thiếu Size | Hiển thị lỗi validation |
| TC-010 | Tạo variant SKU trùng | Hiển thị lỗi validation |
| TC-011 | Tạo variant sale price lớn hơn price | Hiển thị lỗi validation |
| TC-012 | Disable variant | Variant chuyển inactive |
| TC-013 | Xóa option đang được variant sử dụng | Không cho xóa hoặc hiển thị lỗi phù hợp |
| TC-014 | Xóa option value đang được variant sử dụng | Không cho xóa hoặc hiển thị lỗi phù hợp |
| TC-015 | Product quần áo có Color + Size | Tạo được variants đúng |
| TC-016 | Product điện tử có Color + Storage | Tạo được variants đúng |
| TC-017 | Product laptop có RAM + Storage + Color | Tạo được variants đúng |
| TC-018 | Customer truy cập route admin variant | Bị chặn |
| TC-019 | Product không có variant | Vẫn hoạt động như product thường |
| TC-020 | Inventory có thể nhận diện variant | Không lỗi khi xem inventory |

---

## 17. Acceptance Criteria

Task này được xem là hoàn thành khi:

* [ ] Product có thể tạo nhiều options.
* [ ] Mỗi option có thể tạo nhiều values.
* [ ] Có thể tạo variant từ tổ hợp option values.
* [ ] Không tạo được duplicate combination.
* [ ] Variant có SKU riêng.
* [ ] Variant SKU unique.
* [ ] Variant có price riêng nếu cần.
* [ ] Variant có sale price riêng nếu cần.
* [ ] Variant có status riêng.
* [ ] Product quần áo dùng được Color + Size.
* [ ] Product điện tử dùng được Color + Storage.
* [ ] Product laptop dùng được RAM + Storage + Color.
* [ ] Product không có variant vẫn hoạt động bình thường.
* [ ] Product edit screen có section quản lý options và variants.
* [ ] Không xóa được option/value đang được variant sử dụng nếu chưa xử lý dữ liệu liên quan.
* [ ] Dữ liệu variant sẵn sàng cho Inventory.
* [ ] Dữ liệu variant sẵn sàng cho Product Detail Page.
* [ ] Dữ liệu variant sẵn sàng cho Cart.
* [ ] Customer không truy cập được admin variant routes.
* [ ] Chạy được migration.
* [ ] Không implement Cart trong task này.
* [ ] Không implement Checkout trong task này.
* [ ] Không implement Order trong task này.

---

## 18. Impacted Tasks

Task này ảnh hưởng đến các task sau:

| Task    | Impact                                            |
| ------- | ------------------------------------------------- |
| Task 10 | Nâng cấp Product Variant Basic                    |
| Task 12 | Inventory cần quản lý theo variant combination    |
| Task 14 | Product Detail cần selector theo options          |
| Task 15 | Cart cần lưu variant customer chọn                |
| Task 19 | Order item cần lưu snapshot variant               |
| Task 20 | Admin Order cần hiển thị variant trong order item |

Nếu các task trên đã làm rồi, Codex cần cập nhật tương thích nhưng không làm vượt phạm vi quá lớn.

---

## 19. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-10-product-management-with-translation.md
* docs/tasks/task-10-1-product-options-and-variant-combinations.md
* docs/tasks/task-12-inventory-management.md
* docs/tasks/task-14-product-detail-page.md

Sau đó implement Task 10.1: Product Options and Variant Combinations theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 10.1.
* Không làm sang task khác.
* Không tự ý thay đổi thiết kế lớn.
* Nâng cấp variant để hỗ trợ nhiều option linh hoạt như Color, Size, Storage, RAM.
* Không hard-code riêng cho quần áo hoặc đồ điện tử.
* Không dùng fixed columns như color, size, storage trong product_variants.
* Thiết kế phải hỗ trợ option động theo từng product.
* Không implement Cart, Checkout, Order hoặc Payment.
* Không implement frontend add to cart.
* Nếu cần cập nhật nhẹ Task 10 Product Edit để quản lý options và variants thì được phép.
* Sau khi làm xong, báo cáo file đã tạo/sửa và lệnh cần chạy để test.

## 20. Async Save and Full Form Save Behavior

## 20.1. Overview

Product Edit screen cần hỗ trợ lưu Product Options, Product Option Values và Variant Combinations theo hướng hiện đại.

Admin có thể lưu từng phần bằng JavaScript mà không reload trang.

Ngoài ra, nếu admin chưa bấm nút lưu riêng của từng option hoặc variant, khi admin bấm nút `Save Changes` của toàn bộ product form, hệ thống vẫn phải lưu tất cả dữ liệu đang chỉnh sửa.

Mục tiêu:

* Trải nghiệm mượt hơn.
* Không bắt admin reload trang nhiều lần.
* Tránh mất dữ liệu khi admin quên bấm lưu từng option/variant.
* Product edit form hoạt động nhất quán.

---

## 20.2. Individual Async Save

Các nút lưu riêng cần dùng JavaScript để submit dữ liệu mà không reload page.

Áp dụng cho:

| Area                                      | Save Behavior                |
| ----------------------------------------- | ---------------------------- |
| Product Option                            | Save bằng JS                 |
| Product Option Value                      | Save bằng JS                 |
| Variant Combination                       | Save bằng JS                 |
| Variant Price / Sale Price / SKU / Status | Save bằng JS nếu chỉnh riêng |
| Sort Order                                | Save bằng JS nếu chỉnh riêng |

Expected behavior:

* Khi admin bấm nút Save của một option, chỉ option đó được lưu.
* Khi admin bấm nút Save của một option value, chỉ option value đó được lưu.
* Khi admin bấm nút Save của một variant, chỉ variant đó được lưu.
* Trang không reload.
* Sau khi lưu thành công, hiển thị trạng thái `Saved`.
* Nếu lỗi validation, hiển thị lỗi ngay tại dòng/form đang chỉnh.
* Không redirect sau khi lưu bằng JS.
* Không làm mất dữ liệu các phần khác đang chỉnh.

---

## 20.3. Dirty State Tracking

UI cần biết phần nào đang có thay đổi chưa lưu.

Mỗi option, option value hoặc variant cần có trạng thái:

| State  | Meaning              |
| ------ | -------------------- |
| saved  | Đã lưu               |
| dirty  | Có thay đổi chưa lưu |
| saving | Đang lưu             |
| error  | Lưu thất bại         |

Expected behavior:

* Khi admin chỉnh field, trạng thái chuyển thành `dirty`.
* Khi đang lưu, trạng thái chuyển thành `saving`.
* Khi lưu thành công, trạng thái chuyển thành `saved`.
* Khi lưu lỗi, trạng thái chuyển thành `error`.
* UI nên hiển thị badge hoặc text nhỏ như `Unsaved`, `Saving...`, `Saved`, `Error`.

---

## 20.4. Full Product Form Save

Khi admin bấm nút `Save Changes` của toàn bộ product form, hệ thống cần lưu cả:

* Product basic information
* Product translations
* Product options chưa lưu
* Product option values chưa lưu
* Variant combinations chưa lưu
* Các thay đổi variant chưa lưu

Expected behavior:

* Nếu admin đã bấm lưu riêng từng phần trước đó, full form save không tạo dữ liệu trùng.
* Nếu admin chưa bấm lưu riêng, full form save vẫn lưu tất cả dữ liệu đang hiển thị trên màn hình.
* Nếu có lỗi ở option hoặc variant, hệ thống không được mất dữ liệu admin đã nhập.
* Nếu có lỗi validation, hiển thị lỗi rõ tại section tương ứng.
* Nếu lưu thành công, hiển thị message thành công chung.
* Không tạo duplicate option, option value hoặc variant combination.

---

## 20.5. Save Strategy

Hệ thống có thể dùng một trong hai chiến lược sau.

### Strategy A: Full Form Submits Nested Data

Khi bấm `Save Changes`, form gửi toàn bộ dữ liệu product kèm options và variants.

Data cần bao gồm:

| Data Group            | Description                    |
| --------------------- | ------------------------------ |
| product               | Thông tin product              |
| translations          | Translation của product        |
| options               | Danh sách product options      |
| option_values         | Danh sách option values        |
| variants              | Danh sách variant combinations |
| variant_option_values | Option values của từng variant |

Ưu điểm:

* Full form save đơn giản cho admin.
* Không mất dữ liệu chưa lưu.
* Dễ kiểm soát transaction.

Nhược điểm:

* Payload lớn hơn.
* Validation phức tạp hơn.

---

### Strategy B: Full Form Save Triggers Async Save For Dirty Items

Khi bấm `Save Changes`, JavaScript sẽ:

* Lưu product basic information.
* Tìm tất cả option/variant đang ở trạng thái dirty.
* Gọi async save cho từng phần chưa lưu.
* Nếu tất cả thành công, hiển thị message thành công.
* Nếu có lỗi, hiển thị lỗi tại section tương ứng.

Ưu điểm:

* Tận dụng lại endpoint save riêng.
* UI mượt.

Nhược điểm:

* Cần xử lý lỗi nhiều request.
* Cần tránh race condition.

---

## 20.6. Recommended MVP Approach

Trong MVP, ưu tiên cách an toàn và dễ bảo trì:

`Individual Save dùng JS + Full Form Save gửi nested data`

Tức là:

* Nút Save riêng của option/variant dùng JS để lưu nhanh không reload.
* Nút Save Changes của toàn bộ product form vẫn có khả năng gửi toàn bộ dữ liệu đang có trên màn hình.
* Backend cần xử lý update/create/delete option và variant một cách an toàn.
* Không tạo duplicate nếu dữ liệu đã được lưu bằng JS trước đó.

---

## 20.7. Validation Behavior

Validation cần hoạt động cho cả hai trường hợp:

| Case                  | Expected Behavior                                       |
| --------------------- | ------------------------------------------------------- |
| Save riêng bằng JS    | Trả lỗi validation cho section đó                       |
| Full form save        | Trả lỗi validation cho đúng option/value/variant bị lỗi |
| SKU trùng             | Hiển thị lỗi tại variant                                |
| Option name trùng     | Hiển thị lỗi tại option                                 |
| Option value trùng    | Hiển thị lỗi tại option value                           |
| Duplicate combination | Hiển thị lỗi tại variant combination                    |
| Thiếu option value    | Hiển thị lỗi tại variant                                |

---

## 20.8. Transaction Requirement

Khi bấm `Save Changes` toàn bộ product form, hệ thống nên xử lý theo transaction.

Expected behavior:

* Nếu toàn bộ dữ liệu hợp lệ, lưu tất cả.
* Nếu một phần bị lỗi, rollback phần cần thiết.
* Không để product lưu một nửa nhưng variant lỗi gây dữ liệu không nhất quán.
* Không tạo duplicate records khi submit lại.

---

## 20.9. Frontend Requirement

Frontend có thể dùng:

* Blade
* Tailwind CSS
* Alpine.js
* Fetch API

Không dùng Vue.js trong MVP.

Yêu cầu UI:

* Không reload khi lưu từng option.
* Có loading state khi đang lưu.
* Có success state khi lưu thành công.
* Có error state khi lưu thất bại.
* Có cảnh báo khi rời trang nếu còn dữ liệu chưa lưu.
* Nút `Save Changes` của product form cần lưu cả dữ liệu chưa lưu trong options và variants.

---

## 20.10. Acceptance Criteria Additions

Bổ sung acceptance criteria:

* [ ] Nút Save của Product Option lưu bằng JS và không reload trang.
* [ ] Nút Save của Product Option Value lưu bằng JS và không reload trang.
* [ ] Nút Save của Variant Combination lưu bằng JS và không reload trang.
* [ ] UI hiển thị trạng thái unsaved/saving/saved/error.
* [ ] Khi admin chỉnh option hoặc variant, UI đánh dấu dữ liệu chưa lưu.
* [ ] Khi bấm Save Changes của toàn bộ product form, hệ thống lưu cả option/variant chưa lưu.
* [ ] Full form save không tạo duplicate option.
* [ ] Full form save không tạo duplicate option value.
* [ ] Full form save không tạo duplicate variant combination.
* [ ] Lỗi validation hiển thị đúng tại section option hoặc variant.
* [ ] Không mất dữ liệu admin đã nhập khi validation lỗi.
* [ ] Không dùng Vue.js.
* [ ] Không implement Cart trong phần này.
