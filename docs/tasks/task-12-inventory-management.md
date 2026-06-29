Bạn tạo file:

`docs/tasks/task-12-inventory-management.md`

và copy nội dung dưới đây vào file.

# Task 12: Inventory Management

## 1. Overview

Task này dùng để xây dựng chức năng quản lý tồn kho cho hệ thống e-commerce.

Inventory Management cho phép admin theo dõi, điều chỉnh và kiểm tra số lượng tồn kho của sản phẩm.

Hệ thống cần hỗ trợ tồn kho cho:

* Product không có variant
* Product có variant
* Số lượng tồn kho hiện tại
* Số lượng đang giữ chỗ
* Số lượng có thể bán
* Cảnh báo tồn kho thấp
* Lịch sử thay đổi tồn kho

Inventory sẽ được dùng ở các task sau:

* Public Product Catalog
* Product Detail Page
* Cart
* Checkout
* Order Creation
* Admin Report

---

## 2. Objectives

Sau khi hoàn thành task này, hệ thống cần có:

* Màn hình danh sách tồn kho.
* Hiển thị tồn kho theo product hoặc variant.
* Có thể điều chỉnh số lượng tồn kho.
* Có thể tăng tồn kho.
* Có thể giảm tồn kho.
* Có thể set lại tồn kho.
* Có thể xem lịch sử thay đổi tồn kho.
* Có cảnh báo sản phẩm sắp hết hàng.
* Không cho tồn kho bị âm.
* Tự động tạo stock record cho product hoặc variant nếu chưa có.
* Sidebar admin có menu Inventory.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong task này:

* Quản lý tồn kho sản phẩm.
* Quản lý tồn kho variant.
* Hiển thị danh sách inventory.
* Điều chỉnh số lượng tồn kho thủ công.
* Ghi log mỗi lần thay đổi tồn kho.
* Hiển thị lịch sử thay đổi tồn kho.
* Lọc inventory theo keyword.
* Lọc inventory theo category.
* Lọc inventory theo trạng thái tồn kho.
* Cảnh báo low stock.
* Cấu hình low stock threshold.
* Cập nhật menu Inventory trong admin sidebar.

### 3.2. Out of Scope

Các phần chưa làm trong task này:

* Trừ tồn kho khi checkout.
* Giữ hàng trong cart.
* Tự động cộng lại tồn kho khi cancel order.
* Multi warehouse.
* Multi branch inventory.
* Barcode.
* Import/export tồn kho bằng Excel.
* Purchase order.
* Supplier management.
* Inventory valuation report.
* Stock transfer.
* Public stock notification.
* Inventory reservation nâng cao.

---

## 4. User Roles

| Role        | Permission                                           |
| ----------- | ---------------------------------------------------- |
| super_admin | Có toàn quyền quản lý inventory                      |
| admin       | Có quyền quản lý inventory                           |
| staff       | Có thể xem và điều chỉnh tồn kho nếu được phân quyền |
| customer    | Không được truy cập                                  |

Route quản lý inventory chỉ dành cho khu vực admin.

Customer không được truy cập các màn hình Inventory Management.

---

## 5. Functional Requirements

## FR-01: Inventory List

Admin có thể xem danh sách tồn kho tại:

`/admin/inventory`

Danh sách cần hiển thị:

| Field               | Description                        |
| ------------------- | ---------------------------------- |
| Product             | Tên sản phẩm theo default language |
| Variant             | Variant nếu có                     |
| SKU                 | SKU product hoặc variant           |
| Category            | Danh mục                           |
| Quantity            | Số lượng tồn kho                   |
| Reserved Quantity   | Số lượng đang giữ chỗ              |
| Available Quantity  | Số lượng có thể bán                |
| Low Stock Threshold | Ngưỡng cảnh báo tồn kho thấp       |
| Stock Status        | In Stock, Low Stock, Out of Stock  |
| Updated At          | Lần cập nhật gần nhất              |
| Actions             | Adjust, History                    |

Expected behavior:

* Product có variant thì quản lý tồn kho theo variant.
* Product không có variant thì quản lý tồn kho theo product.
* Available quantity = quantity - reserved quantity.
* Có filter theo keyword hoặc SKU.
* Có filter theo category.
* Có filter theo stock status.
* Có pagination.
* Table cần hiển thị full width theo admin layout.

---

## FR-02: Inventory Detail

Admin có thể xem chi tiết tồn kho của một product hoặc variant.

Có thể hiển thị tại:

`/admin/inventory/{id}`

hoặc dùng modal/detail section trong inventory list.

Thông tin cần hiển thị:

| Field               | Description               |
| ------------------- | ------------------------- |
| Product Name        | Tên sản phẩm              |
| Variant Name        | Variant nếu có            |
| SKU                 | SKU                       |
| Current Quantity    | Tồn kho hiện tại          |
| Reserved Quantity   | Số lượng đang giữ chỗ     |
| Available Quantity  | Số lượng có thể bán       |
| Low Stock Threshold | Ngưỡng cảnh báo           |
| Stock Status        | Trạng thái tồn kho        |
| Recent Logs         | Lịch sử thay đổi gần nhất |

---

## FR-03: Adjust Inventory

Admin có thể điều chỉnh tồn kho.

URL gợi ý:

`/admin/inventory/{id}/adjust`

Các loại điều chỉnh:

| Adjustment Type | Description              |
| --------------- | ------------------------ |
| Increase        | Cộng thêm tồn kho        |
| Decrease        | Trừ bớt tồn kho          |
| Set             | Set lại số lượng tồn kho |

Thông tin cần nhập:

| Field           | Required | Description                 |
| --------------- | -------- | --------------------------- |
| Adjustment Type | Yes      | Increase, decrease hoặc set |
| Quantity        | Yes      | Số lượng điều chỉnh         |
| Reason          | No       | Lý do điều chỉnh            |
| Note            | No       | Ghi chú nội bộ              |

Expected behavior:

* Quantity phải là số hợp lệ.
* Không cho quantity âm.
* Nếu decrease làm tồn kho nhỏ hơn reserved quantity thì không cho lưu.
* Nếu set quantity nhỏ hơn reserved quantity thì không cho lưu.
* Sau khi điều chỉnh, hệ thống ghi inventory log.
* Sau khi điều chỉnh, quay lại inventory list hoặc inventory detail.

---

## FR-04: Inventory Logs

Mỗi lần tồn kho thay đổi, hệ thống cần ghi log.

Inventory log cần lưu:

| Field           | Description                 |
| --------------- | --------------------------- |
| Product         | Product liên quan           |
| Variant         | Variant liên quan nếu có    |
| Type            | Loại thay đổi               |
| Quantity Before | Số lượng trước khi thay đổi |
| Quantity Change | Số lượng thay đổi           |
| Quantity After  | Số lượng sau khi thay đổi   |
| Reason          | Lý do thay đổi              |
| Note            | Ghi chú                     |
| Created By      | Admin thực hiện             |
| Created At      | Thời gian thực hiện         |

Các loại log:

| Type            | Description                                |
| --------------- | ------------------------------------------ |
| initial         | Tạo tồn kho ban đầu                        |
| increase        | Cộng tồn kho                               |
| decrease        | Trừ tồn kho                                |
| set             | Set lại tồn kho                            |
| order_reserved  | Giữ hàng cho order, dùng ở task sau        |
| order_released  | Trả lại hàng, dùng ở task sau              |
| order_confirmed | Trừ hàng khi đơn xác nhận, dùng ở task sau |

Trong task này chỉ xử lý:

* initial
* increase
* decrease
* set

---

## FR-05: Low Stock Warning

Hệ thống cần cảnh báo sản phẩm sắp hết hàng.

Business rules:

* Nếu available quantity bằng 0 thì trạng thái là Out of Stock.
* Nếu available quantity lớn hơn 0 nhưng nhỏ hơn hoặc bằng low stock threshold thì trạng thái là Low Stock.
* Nếu available quantity lớn hơn low stock threshold thì trạng thái là In Stock.
* Low stock threshold có thể cấu hình theo từng product hoặc variant.
* Nếu chưa cấu hình threshold, dùng giá trị mặc định.

---

## FR-06: Stock Status

Hệ thống cần xác định stock status.

Các trạng thái:

| Status       | Condition                                                   |
| ------------ | ----------------------------------------------------------- |
| In Stock     | Available quantity lớn hơn low stock threshold              |
| Low Stock    | Available quantity lớn hơn 0 và nhỏ hơn hoặc bằng threshold |
| Out of Stock | Available quantity bằng 0                                   |

Business rules:

* Không cho tồn kho âm trong MVP.
* Stock status dùng để hiển thị trong admin.
* Public site sẽ dùng stock status ở task sau.

---

## FR-07: Product Without Variant

Nếu product không có variant, inventory được quản lý theo product.

Ví dụ:

| Product     | SKU        | Quantity |
| ----------- | ---------- | -------: |
| Áo thun nam | TSHIRT-001 |      100 |

Business rules:

* Mỗi product không có variant có một inventory stock record.
* SKU dùng SKU của product.
* Khi product đã tồn tại nhưng chưa có inventory, hệ thống cần tạo stock record mặc định.

---

## FR-08: Product With Variant

Nếu product có variant, inventory được quản lý theo variant.

Ví dụ:

| Product     | Variant | SKU      | Quantity |
| ----------- | ------- | -------- | -------: |
| Áo thun nam | Size M  | TSHIRT-M |       50 |
| Áo thun nam | Size L  | TSHIRT-L |       30 |

Business rules:

* Mỗi variant có một inventory stock record.
* SKU dùng SKU của variant.
* Product cha có thể không cần stock riêng nếu đã có variant.
* Khi variant đã tồn tại nhưng chưa có inventory, hệ thống cần tạo stock record mặc định.

---

## FR-09: Reserved Quantity

Reserved quantity dùng để giữ hàng trong quá trình checkout hoặc order ở task sau.

Trong task này:

* Reserved quantity có thể tồn tại trong database.
* Mặc định reserved quantity là 0.
* Admin chỉ cần xem reserved quantity.
* Chưa cần xử lý reserve/release tự động.

Business rules:

* Available quantity = quantity - reserved quantity.
* Reserved quantity không được lớn hơn quantity.
* Reserved quantity không được âm.
* Nếu reserved quantity bất thường, hệ thống không được làm lỗi màn hình admin.

---

## FR-10: Auto Create Stock Record

Hệ thống cần tự động tạo inventory stock record nếu product hoặc variant chưa có tồn kho.

Expected behavior:

* Nếu product không có variant và chưa có stock record, tạo stock record mặc định quantity 0.
* Nếu variant chưa có stock record, tạo stock record mặc định quantity 0.
* Không tạo trùng stock record.
* Có thể ghi log type initial khi tạo stock đầu tiên.

---

## FR-11: Inventory Search and Filter

Inventory list cần hỗ trợ tìm kiếm và lọc.

Filter cần có:

| Filter       | Description                                  |
| ------------ | -------------------------------------------- |
| Keyword      | Tìm theo product name hoặc SKU               |
| Category     | Lọc theo category                            |
| Stock Status | In Stock, Low Stock, Out of Stock            |
| Product Type | Product without variant hoặc variant nếu cần |

Expected behavior:

* Filter có thể kết hợp với nhau.
* Kết quả có pagination.
* Filter không làm mất layout admin.

---

## FR-12: Inventory Cache

Nếu hệ thống có cache product hoặc inventory, cache cần clear khi:

* Adjust inventory
* Create stock record
* Update low stock threshold
* Update reserved quantity ở task sau

Trong task này, nếu chưa có cache inventory thì có thể bỏ qua cache nâng cao.

---

## 6. UI / Screen Design

## 6.1. Screen List

| Screen           | URL                            | Description                             |
| ---------------- | ------------------------------ | --------------------------------------- |
| Inventory List   | `/admin/inventory`             | Danh sách tồn kho                       |
| Inventory Detail | `/admin/inventory/{id}`        | Chi tiết tồn kho nếu cần                |
| Adjust Inventory | `/admin/inventory/{id}/adjust` | Điều chỉnh tồn kho                      |
| Inventory Logs   | `/admin/inventory/{id}/logs`   | Lịch sử tồn kho nếu dùng màn hình riêng |

Có thể chọn cách đơn giản:

* Inventory list hiển thị tất cả stock records.
* Adjust inventory dùng form hoặc modal.
* History có thể hiển thị trong detail page.

---

## 6.2. Inventory List Screen

Màn hình danh sách cần có:

* Page title: Inventory
* Filter keyword
* Filter category
* Filter stock status
* Table danh sách tồn kho
* Badge stock status
* Action buttons: Adjust, History

Table columns:

| Column              | Description        |
| ------------------- | ------------------ |
| Product             | Tên sản phẩm       |
| Variant             | Tên variant nếu có |
| SKU                 | SKU                |
| Category            | Danh mục           |
| Quantity            | Tồn kho            |
| Reserved            | Đang giữ chỗ       |
| Available           | Có thể bán         |
| Low Stock Threshold | Ngưỡng cảnh báo    |
| Status              | Trạng thái         |
| Updated At          | Lần cập nhật       |
| Actions             | Các nút thao tác   |

---

## 6.3. Adjust Inventory Form

Form điều chỉnh tồn kho gồm:

| Label              | Field           |
| ------------------ | --------------- |
| Product            | Display only    |
| Variant            | Display only    |
| Current Quantity   | Display only    |
| Reserved Quantity  | Display only    |
| Available Quantity | Display only    |
| Adjustment Type    | adjustment_type |
| Quantity           | quantity        |
| Reason             | reason          |
| Note               | note            |

Button:

* Save
* Back

---

## 6.4. Inventory History Screen

Màn hình lịch sử cần hiển thị:

| Column     | Description       |
| ---------- | ----------------- |
| Date       | Thời gian         |
| Type       | Loại thay đổi     |
| Before     | Số lượng trước    |
| Change     | Số lượng thay đổi |
| After      | Số lượng sau      |
| Reason     | Lý do             |
| Note       | Ghi chú           |
| Created By | Người thực hiện   |

---

## 7. Database Design

## 7.1. Table: inventory_stocks

Bảng `inventory_stocks` dùng để lưu tồn kho hiện tại.

| Column              | Type            | Nullable | Default        | Description       |
| ------------------- | --------------- | -------- | -------------- | ----------------- |
| id                  | bigint unsigned | No       | auto increment | Primary key       |
| product_id          | bigint unsigned | No       | null           | Product           |
| product_variant_id  | bigint unsigned | Yes      | null           | Variant nếu có    |
| quantity            | int             | No       | 0              | Số lượng tồn kho  |
| reserved_quantity   | int             | No       | 0              | Số lượng đang giữ |
| low_stock_threshold | int             | No       | 5              | Ngưỡng cảnh báo   |
| created_at          | timestamp       | Yes      | null           | Created time      |
| updated_at          | timestamp       | Yes      | null           | Updated time      |

Indexes:

| Index                                  | Description                  |
| -------------------------------------- | ---------------------------- |
| product_id                             | Tìm tồn kho theo product     |
| product_variant_id                     | Tìm tồn kho theo variant     |
| unique product_id + product_variant_id | Không tạo trùng stock record |
| quantity                               | Lọc theo số lượng            |
| reserved_quantity                      | Tính available quantity      |

Lưu ý:

* Với product không có variant, product_variant_id sẽ rỗng.
* Với product có variant, product_variant_id sẽ có giá trị.
* Không tạo nhiều stock record cho cùng một product hoặc variant.

---

## 7.2. Table: inventory_logs

Bảng `inventory_logs` dùng để lưu lịch sử thay đổi tồn kho.

| Column             | Type            | Nullable | Default        | Description       |
| ------------------ | --------------- | -------- | -------------- | ----------------- |
| id                 | bigint unsigned | No       | auto increment | Primary key       |
| inventory_stock_id | bigint unsigned | No       | null           | Stock record      |
| product_id         | bigint unsigned | No       | null           | Product           |
| product_variant_id | bigint unsigned | Yes      | null           | Variant nếu có    |
| type               | varchar(50)     | No       | null           | Loại thay đổi     |
| quantity_before    | int             | No       | 0              | Số lượng trước    |
| quantity_change    | int             | No       | 0              | Số lượng thay đổi |
| quantity_after     | int             | No       | 0              | Số lượng sau      |
| reason             | varchar(255)    | Yes      | null           | Lý do             |
| note               | text            | Yes      | null           | Ghi chú           |
| created_by         | bigint unsigned | Yes      | null           | Admin thực hiện   |
| created_at         | timestamp       | Yes      | null           | Created time      |
| updated_at         | timestamp       | Yes      | null           | Updated time      |

Indexes:

| Index              | Description          |
| ------------------ | -------------------- |
| inventory_stock_id | Tìm log theo stock   |
| product_id         | Tìm log theo product |
| product_variant_id | Tìm log theo variant |
| type               | Lọc theo loại log    |
| created_by         | Lọc theo admin       |
| created_at         | Sắp xếp lịch sử      |

---

## 7.3. Relationship

Quan hệ dữ liệu:

| Relationship                             | Description                          |
| ---------------------------------------- | ------------------------------------ |
| Product has inventory stock              | Product không variant có stock riêng |
| Product Variant has inventory stock      | Variant có stock riêng               |
| Inventory Stock has many Inventory Logs  | Một stock có nhiều log               |
| Inventory Log belongs to Inventory Stock | Log thuộc một stock                  |
| Inventory Log belongs to User            | Log được tạo bởi admin               |

---

## 8. Route Design

Các route cần có:

| Method | URL                            | Description                             |
| ------ | ------------------------------ | --------------------------------------- |
| GET    | `/admin/inventory`             | Danh sách tồn kho                       |
| GET    | `/admin/inventory/{id}`        | Chi tiết tồn kho                        |
| GET    | `/admin/inventory/{id}/adjust` | Form điều chỉnh tồn kho                 |
| POST   | `/admin/inventory/{id}/adjust` | Lưu điều chỉnh tồn kho                  |
| GET    | `/admin/inventory/{id}/logs`   | Lịch sử tồn kho nếu dùng màn hình riêng |

Tất cả route trên cần được bảo vệ bởi admin authentication.

---

## 9. Validation Rules

## 9.1. Adjust Inventory Validation

| Field           | Rule                                           |
| --------------- | ---------------------------------------------- |
| adjustment_type | Required, chỉ nhận increase, decrease hoặc set |
| quantity        | Required, integer, min 0                       |
| reason          | Optional, max 255 characters                   |
| note            | Optional                                       |

Business validation:

* Quantity phải là số nguyên không âm.
* Decrease không được làm quantity sau điều chỉnh nhỏ hơn reserved quantity.
* Set không được nhỏ hơn reserved quantity.
* Inventory stock phải tồn tại.
* Admin phải có quyền truy cập.

---

## 9.2. Low Stock Threshold Validation

| Field               | Rule                     |
| ------------------- | ------------------------ |
| low_stock_threshold | Required, integer, min 0 |

Business validation:

* Threshold không được âm.
* Threshold có thể bằng 0 nếu không muốn cảnh báo sớm.

---

## 10. Business Logic

## 10.1. Inventory List Flow

* Admin mở màn hình inventory.
* Hệ thống kiểm tra và tạo stock record còn thiếu nếu cần.
* Hệ thống load danh sách product hoặc variant có tồn kho.
* Hệ thống áp dụng filter nếu có.
* Hệ thống tính available quantity.
* Hệ thống xác định stock status.
* Hệ thống hiển thị danh sách có pagination.

---

## 10.2. Adjust Increase Flow

* Admin chọn adjustment type là increase.
* Admin nhập quantity.
* Hệ thống lấy quantity hiện tại.
* Hệ thống cộng thêm quantity.
* Hệ thống cập nhật inventory stock.
* Hệ thống ghi inventory log.
* Hệ thống redirect với thông báo thành công.

---

## 10.3. Adjust Decrease Flow

* Admin chọn adjustment type là decrease.
* Admin nhập quantity.
* Hệ thống lấy quantity hiện tại.
* Hệ thống kiểm tra quantity sau khi trừ không nhỏ hơn reserved quantity.
* Nếu hợp lệ, hệ thống trừ tồn kho.
* Hệ thống cập nhật inventory stock.
* Hệ thống ghi inventory log.
* Hệ thống redirect với thông báo thành công.

---

## 10.4. Adjust Set Flow

* Admin chọn adjustment type là set.
* Admin nhập quantity mới.
* Hệ thống kiểm tra quantity mới không nhỏ hơn reserved quantity.
* Nếu hợp lệ, hệ thống set lại tồn kho.
* Hệ thống cập nhật inventory stock.
* Hệ thống ghi inventory log.
* Hệ thống redirect với thông báo thành công.

---

## 10.5. Stock Status Flow

* Hệ thống tính available quantity bằng quantity trừ reserved quantity.
* Nếu available quantity bằng 0, trạng thái là Out of Stock.
* Nếu available quantity lớn hơn 0 và nhỏ hơn hoặc bằng low stock threshold, trạng thái là Low Stock.
* Nếu available quantity lớn hơn low stock threshold, trạng thái là In Stock.
* Trạng thái được hiển thị bằng badge trong admin.

---

## 10.6. Auto Create Stock Flow

* Hệ thống tìm product không có variant nhưng chưa có stock.
* Hệ thống tạo stock record mặc định quantity 0.
* Hệ thống tìm variant chưa có stock.
* Hệ thống tạo stock record mặc định quantity 0.
* Hệ thống không tạo trùng stock record.
* Hệ thống có thể ghi log type initial khi tạo stock đầu tiên.

---

## 11. Error Handling

| Case                                           | Expected Handling            |
| ---------------------------------------------- | ---------------------------- |
| Inventory stock không tồn tại                  | Hiển thị lỗi phù hợp         |
| Quantity nhập âm                               | Hiển thị lỗi validation      |
| Decrease làm tồn kho nhỏ hơn reserved quantity | Không cho lưu                |
| Set quantity nhỏ hơn reserved quantity         | Không cho lưu                |
| Adjustment type không hợp lệ                   | Hiển thị lỗi validation      |
| Product đã bị xóa                              | Không làm lỗi trắng màn hình |
| Variant đã bị xóa                              | Không làm lỗi trắng màn hình |
| Guest truy cập inventory                       | Redirect login               |
| Customer truy cập inventory                    | Chặn truy cập                |

---

## 12. Security

Yêu cầu bảo mật:

* Chỉ admin mới được truy cập Inventory Management.
* Customer không được truy cập.
* Validate toàn bộ input.
* Không lưu dữ liệu request ngoài danh sách field cho phép.
* Form phải có CSRF protection.
* Không hiển thị lỗi kỹ thuật trực tiếp ra màn hình production.
* Mỗi lần thay đổi tồn kho cần ghi log.
* Không cho điều chỉnh làm tồn kho không hợp lệ.

---

## 13. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type                | Description                                             |
| ------------------- | ------------------------------------------------------- |
| Model               | Inventory Stock model, Inventory Log model              |
| Service             | Inventory service                                       |
| Controller          | Admin inventory controller                              |
| Request             | Validate adjust inventory                               |
| Migration           | Tạo bảng inventory_stocks và inventory_logs nếu chưa có |
| Seeder              | Inventory seeder nếu cần                                |
| View                | Inventory list, adjust form, history                    |
| Route               | Admin routes cho Inventory Management                   |
| Sidebar             | Cập nhật menu Inventory trong admin sidebar             |
| Product Integration | Tích hợp với product và variant đã có                   |

Lưu ý:

* Nếu bảng `inventory_stocks` và `inventory_logs` đã tồn tại thì không tạo migration trùng.
* Không sửa các module không liên quan.
* Không implement Cart trong task này.
* Không implement Checkout trong task này.
* Không implement Order trong task này.

---

## 14. Commands

Sau khi Codex implement xong, chạy các lệnh kiểm tra sau:

| Command                | Purpose                              |
| ---------------------- | ------------------------------------ |
| php artisan migrate    | Chạy migration                       |
| php artisan db:seed    | Chạy seeder nếu có                   |
| php artisan route:list | Kiểm tra route                       |
| php artisan serve      | Chạy local server                    |
| npm run build          | Build frontend nếu có thay đổi asset |

URL test:

`http://127.0.0.1:8000/admin/inventory`

---

## 15. Test Cases

| Test Case ID | Scenario                                | Expected Result                           |
| ------------ | --------------------------------------- | ----------------------------------------- |
| TC-001       | Guest vào `/admin/inventory`            | Redirect login                            |
| TC-002       | Customer vào `/admin/inventory`         | Bị chặn                                   |
| TC-003       | Admin vào `/admin/inventory`            | Hiển thị danh sách tồn kho                |
| TC-004       | Product chưa có stock record            | Hệ thống tạo hoặc hiển thị stock mặc định |
| TC-005       | Variant chưa có stock record            | Hệ thống tạo hoặc hiển thị stock mặc định |
| TC-006       | Increase inventory hợp lệ               | Tồn kho tăng và ghi log                   |
| TC-007       | Decrease inventory hợp lệ               | Tồn kho giảm và ghi log                   |
| TC-008       | Decrease vượt quá tồn kho cho phép      | Không cho lưu                             |
| TC-009       | Set inventory hợp lệ                    | Tồn kho được set lại và ghi log           |
| TC-010       | Set inventory nhỏ hơn reserved quantity | Không cho lưu                             |
| TC-011       | Quantity nhập âm                        | Hiển thị lỗi validation                   |
| TC-012       | Xem inventory logs                      | Hiển thị lịch sử thay đổi                 |
| TC-013       | Tồn kho bằng 0                          | Hiển thị Out of Stock                     |
| TC-014       | Tồn kho thấp hơn threshold              | Hiển thị Low Stock                        |
| TC-015       | Tồn kho cao hơn threshold               | Hiển thị In Stock                         |
| TC-016       | Filter theo SKU                         | Hiển thị đúng kết quả                     |
| TC-017       | Filter theo stock status                | Hiển thị đúng kết quả                     |

---

## 16. Acceptance Criteria

Task này được xem là hoàn thành khi:

* [ ] Có màn hình danh sách inventory.
* [ ] Có thể xem tồn kho theo product hoặc variant.
* [ ] Có thể điều chỉnh tồn kho.
* [ ] Có thể increase inventory.
* [ ] Có thể decrease inventory.
* [ ] Có thể set inventory.
* [ ] Không cho tồn kho nhỏ hơn reserved quantity.
* [ ] Có ghi inventory log khi thay đổi tồn kho.
* [ ] Có thể xem lịch sử tồn kho.
* [ ] Có low stock threshold.
* [ ] Có stock status: In Stock, Low Stock, Out of Stock.
* [ ] Có filter inventory theo keyword hoặc SKU.
* [ ] Có filter inventory theo stock status.
* [ ] Có tự tạo stock record nếu chưa có.
* [ ] Admin sidebar có menu Inventory.
* [ ] Customer không truy cập được Inventory Management.
* [ ] Chạy được migration.
* [ ] Không implement Cart trong task này.
* [ ] Không implement Checkout trong task này.
* [ ] Không implement Order trong task này.

---

## 17. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-10-product-management-with-translation.md
* docs/tasks/task-11-product-image-upload.md
* docs/tasks/task-12-inventory-management.md

Sau đó implement Task 12: Inventory Management theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 12.
* Không làm sang task khác.
* Không tự ý thay đổi thiết kế lớn.
* Không implement Cart, Checkout, Order, Public Catalog hoặc Report.
* Sau khi làm xong, báo cáo file đã tạo/sửa và lệnh cần chạy để test.
