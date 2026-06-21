Task kế tiếp là:

```txt
Task 08: Tax Management
```

Bạn tạo file:

```txt
docs/tasks/task-08-tax-management.md
```

và copy nội dung dưới đây vào file.

# Task 08: Tax Management

## 1. Overview

Task này dùng để xây dựng chức năng quản lý thuế cho hệ thống e-commerce.

Tax Management cho phép admin cấu hình các nhóm thuế và mức thuế áp dụng cho sản phẩm.

Hệ thống cần hỗ trợ:

* Tax class
* Tax rate
* Thuế theo quốc gia
* Thuế theo khu vực nếu cần
* Sản phẩm chịu thuế hoặc miễn thuế
* Cấu hình giá đã bao gồm thuế hoặc chưa bao gồm thuế

Các module sau sẽ sử dụng Tax Management:

* Product Management
* Cart
* Checkout
* Order Creation
* Report

---

## 2. Objectives

Sau khi hoàn thành task này, hệ thống cần có:

* Màn hình quản lý Tax Classes.
* Màn hình quản lý Tax Rates.
* Có thể thêm, sửa, xóa tax class.
* Có thể thêm, sửa, xóa tax rate.
* Có thể bật hoặc tắt tax class.
* Có thể bật hoặc tắt tax rate.
* Có dữ liệu mặc định cho standard tax, reduced tax, tax free.
* Có logic lấy tax rate theo tax class.
* Có logic tính thuế.
* Có logic tính thuế trong trường hợp giá đã bao gồm thuế.
* Sidebar admin có menu Tax Classes và Tax Rates.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong task này:

* Quản lý Tax Classes.
* Quản lý Tax Rates.
* Thêm tax class.
* Sửa tax class.
* Xóa tax class.
* Bật hoặc tắt tax class.
* Thêm tax rate.
* Sửa tax rate.
* Xóa tax rate.
* Bật hoặc tắt tax rate.
* Seed dữ liệu tax mặc định.
* Validate dữ liệu tax.
* Tạo logic tính thuế cơ bản.
* Cập nhật menu admin sidebar.

### 3.2. Out of Scope

Các phần chưa làm trong task này:

* Gán tax class vào product.
* Tính thuế trong cart.
* Tính thuế trong checkout.
* Lưu tax snapshot vào order.
* Report thuế.
* Tax theo customer group.
* Tax theo nhiều warehouse.
* Tax theo shipping address nâng cao.
* Tích hợp hóa đơn điện tử.
* Export báo cáo thuế.

---

## 4. User Roles

| Role        | Permission                                |
| ----------- | ----------------------------------------- |
| super_admin | Có toàn quyền quản lý tax                 |
| admin       | Có quyền quản lý tax                      |
| staff       | Có thể xem hoặc thao tác giới hạn sau này |
| customer    | Không được truy cập                       |

Route quản lý tax chỉ dành cho khu vực admin.

Customer không được truy cập các màn hình Tax Management.

---

## 5. Functional Requirements

## FR-01: Tax Class List

Admin có thể xem danh sách tax class tại:

`/admin/tax-classes`

Danh sách cần hiển thị:

| Field       | Description          |
| ----------- | -------------------- |
| Code        | Mã tax class         |
| Name        | Tên tax class        |
| Description | Mô tả                |
| Status      | Active hoặc Inactive |
| Actions     | Edit, Delete         |

Ví dụ tax class:

| Code         | Name         | Description     |
| ------------ | ------------ | --------------- |
| standard_tax | Standard Tax | Thuế tiêu chuẩn |
| reduced_tax  | Reduced Tax  | Thuế giảm       |
| tax_free     | Tax Free     | Không tính thuế |

---

## FR-02: Create Tax Class

Admin có thể thêm tax class mới tại:

`/admin/tax-classes/create`

Thông tin cần nhập:

| Field       | Required | Description          |
| ----------- | -------- | -------------------- |
| Code        | Yes      | Mã tax class         |
| Name        | Yes      | Tên tax class        |
| Description | No       | Mô tả tax class      |
| Status      | Yes      | Active hoặc Inactive |

Expected behavior:

* Tax class code là bắt buộc.
* Tax class code không được trùng.
* Tax class code nên được lưu ở dạng lowercase.
* Sau khi tạo thành công, quay lại danh sách tax class.

---

## FR-03: Edit Tax Class

Admin có thể sửa tax class tại:

`/admin/tax-classes/{id}/edit`

Admin có thể sửa:

* Code
* Name
* Description
* Status

Expected behavior:

* Không được trùng code với tax class khác.
* Nếu tax class đã được sử dụng bởi product trong tương lai, cần cẩn thận khi xóa hoặc đổi code.
* Sau khi cập nhật thành công, quay lại danh sách tax class.

---

## FR-04: Delete Tax Class

Admin có thể xóa tax class nếu tax class đó chưa được sử dụng.

Business rules:

* Không nên xóa tax class đã được gán cho product.
* Không nên xóa tax class đã có tax rate.
* Nếu không thể xóa, hệ thống cần hiển thị thông báo lỗi rõ ràng.

Trong giai đoạn hiện tại, nếu chưa có product sử dụng tax class, có thể cho phép xóa nếu không có ràng buộc dữ liệu.

---

## FR-05: Tax Rate List

Admin có thể xem danh sách tax rate tại:

`/admin/tax-rates`

Danh sách cần hiển thị:

| Field        | Description          |
| ------------ | -------------------- |
| Tax Class    | Nhóm thuế            |
| Country Code | Mã quốc gia          |
| Region       | Khu vực              |
| Rate         | Mức thuế phần trăm   |
| Priority     | Độ ưu tiên           |
| Status       | Active hoặc Inactive |
| Actions      | Edit, Delete         |

Ví dụ tax rate:

| Tax Class    | Country | Rate |
| ------------ | ------- | ---: |
| Standard Tax | VN      |  10% |
| Reduced Tax  | VN      |   5% |
| Tax Free     | VN      |   0% |

---

## FR-06: Create Tax Rate

Admin có thể thêm tax rate mới tại:

`/admin/tax-rates/create`

Thông tin cần nhập:

| Field        | Required | Description                   |
| ------------ | -------- | ----------------------------- |
| Tax Class    | Yes      | Nhóm thuế                     |
| Country Code | No       | Mã quốc gia, ví dụ VN, JP, US |
| Region       | No       | Khu vực hoặc tỉnh/thành       |
| Rate         | Yes      | Mức thuế phần trăm            |
| Priority     | No       | Độ ưu tiên                    |
| Status       | Yes      | Active hoặc Inactive          |

Expected behavior:

* Tax class là bắt buộc.
* Rate là bắt buộc.
* Rate không được nhỏ hơn 0.
* Rate không nên lớn hơn 100.
* Country code nếu nhập nên lưu dạng uppercase.
* Sau khi tạo thành công, quay lại danh sách tax rate.

---

## FR-07: Edit Tax Rate

Admin có thể sửa tax rate tại:

`/admin/tax-rates/{id}/edit`

Admin có thể sửa:

* Tax class
* Country code
* Region
* Rate
* Priority
* Status

Expected behavior:

* Rate phải hợp lệ.
* Country code nên được lưu dạng uppercase.
* Sau khi cập nhật thành công, quay lại danh sách tax rate.

---

## FR-08: Delete Tax Rate

Admin có thể xóa tax rate nếu tax rate đó chưa phát sinh dữ liệu liên quan.

Business rules:

* Không nên xóa tax rate đã được dùng trong order snapshot trong tương lai.
* Nếu đã phát sinh order, nên disable thay vì delete.
* Trong giai đoạn hiện tại, có thể cho phép xóa nếu chưa có ràng buộc dữ liệu.

---

## FR-09: Enable / Disable Tax Class

Admin có thể bật hoặc tắt tax class.

Business rules:

* Tax class inactive không nên hiển thị khi tạo hoặc sửa product.
* Nếu tax class inactive, hệ thống không nên dùng để tính thuế cho dữ liệu mới.
* Dữ liệu cũ vẫn được giữ nguyên.

---

## FR-10: Enable / Disable Tax Rate

Admin có thể bật hoặc tắt tax rate.

Business rules:

* Tax rate inactive không được dùng để tính thuế.
* Nếu không tìm thấy tax rate active, hệ thống có thể trả về tax rate 0%.
* Dữ liệu order cũ không bị ảnh hưởng vì order sẽ lưu tax snapshot ở task sau.

---

## FR-11: Tax Calculation

Hệ thống cần có logic tính thuế cơ bản.

Trường hợp giá chưa bao gồm thuế:

|    Amount | Tax Rate | Tax Amount |     Total |
| --------: | -------: | ---------: | --------: |
| 1,000,000 |      10% |    100,000 | 1,100,000 |

Trường hợp giá đã bao gồm thuế:

| Display Price | Tax Rate | Base Amount | Included Tax |
| ------------: | -------: | ----------: | -----------: |
|     1,100,000 |      10% |   1,000,000 |      100,000 |

---

## FR-12: Tax Settings Integration

Task này cần đọc được setting đã tạo ở Task 05:

| Setting Key       | Meaning                               |
| ----------------- | ------------------------------------- |
| tax_enabled       | Bật hoặc tắt tính thuế                |
| price_include_tax | Giá sản phẩm đã bao gồm thuế hay chưa |

Business rules:

* Nếu `tax_enabled = false`, hệ thống trả về tax amount là 0.
* Nếu `price_include_tax = false`, thuế được cộng thêm vào giá.
* Nếu `price_include_tax = true`, thuế được tách ra từ giá đã bao gồm thuế.

---

## FR-13: Cache Tax Data

Danh sách tax class và tax rate nên được cache để giảm query database.

Khi có thay đổi sau đây, cache cần được clear:

* Create tax class
* Update tax class
* Delete tax class
* Create tax rate
* Update tax rate
* Delete tax rate
* Enable hoặc disable tax class
* Enable hoặc disable tax rate

---

## 6. UI / Screen Design

## 6.1. Screen List

| Screen           | URL                            | Description         |
| ---------------- | ------------------------------ | ------------------- |
| Tax Class List   | `/admin/tax-classes`           | Danh sách nhóm thuế |
| Create Tax Class | `/admin/tax-classes/create`    | Thêm nhóm thuế      |
| Edit Tax Class   | `/admin/tax-classes/{id}/edit` | Sửa nhóm thuế       |
| Tax Rate List    | `/admin/tax-rates`             | Danh sách mức thuế  |
| Create Tax Rate  | `/admin/tax-rates/create`      | Thêm mức thuế       |
| Edit Tax Rate    | `/admin/tax-rates/{id}/edit`   | Sửa mức thuế        |

---

## 6.2. Tax Class List Screen

Màn hình danh sách tax class cần có:

* Page title: Tax Classes
* Button: Add Tax Class
* Table hiển thị danh sách tax class
* Action buttons: Edit, Delete

Table columns:

| Column      | Description          |
| ----------- | -------------------- |
| Code        | Mã tax class         |
| Name        | Tên tax class        |
| Description | Mô tả                |
| Status      | Active hoặc Inactive |
| Actions     | Các nút thao tác     |

---

## 6.3. Tax Class Create / Edit Form

Form gồm các field:

| Label       | Field       |
| ----------- | ----------- |
| Code        | code        |
| Name        | name        |
| Description | description |
| Status      | status      |

Button:

* Save
* Back

---

## 6.4. Tax Rate List Screen

Màn hình danh sách tax rate cần có:

* Page title: Tax Rates
* Button: Add Tax Rate
* Table hiển thị danh sách tax rate
* Action buttons: Edit, Delete

Table columns:

| Column       | Description          |
| ------------ | -------------------- |
| Tax Class    | Nhóm thuế            |
| Country Code | Mã quốc gia          |
| Region       | Khu vực              |
| Rate         | Mức thuế             |
| Priority     | Độ ưu tiên           |
| Status       | Active hoặc Inactive |
| Actions      | Các nút thao tác     |

---

## 6.5. Tax Rate Create / Edit Form

Form gồm các field:

| Label        | Field        |
| ------------ | ------------ |
| Tax Class    | tax_class_id |
| Country Code | country_code |
| Region       | region       |
| Rate         | rate         |
| Priority     | priority     |
| Status       | status       |

Button:

* Save
* Back

---

## 7. Database Design

## 7.1. Table: tax_classes

Bảng `tax_classes` dùng để lưu nhóm thuế.

| Column      | Type            | Nullable | Default        | Description          |
| ----------- | --------------- | -------- | -------------- | -------------------- |
| id          | bigint unsigned | No       | auto increment | Primary key          |
| name        | varchar(255)    | No       | null           | Tax class name       |
| code        | varchar(100)    | No       | null           | Tax class code       |
| description | text            | Yes      | null           | Description          |
| status      | tinyint         | No       | 1              | 1 active, 0 inactive |
| created_at  | timestamp       | Yes      | null           | Created time         |
| updated_at  | timestamp       | Yes      | null           | Updated time         |

Indexes:

| Index       | Description                  |
| ----------- | ---------------------------- |
| unique code | Không cho trùng mã tax class |
| status      | Dùng để lọc active/inactive  |

---

## 7.2. Table: tax_rates

Bảng `tax_rates` dùng để lưu mức thuế.

| Column       | Type            | Nullable | Default        | Description          |
| ------------ | --------------- | -------- | -------------- | -------------------- |
| id           | bigint unsigned | No       | auto increment | Primary key          |
| tax_class_id | bigint unsigned | No       | null           | Liên kết tax class   |
| country_code | varchar(10)     | Yes      | null           | VN, JP, US           |
| region       | varchar(100)    | Yes      | null           | Khu vực              |
| rate         | decimal(8,4)    | No       | 0.0000         | Mức thuế phần trăm   |
| priority     | int             | No       | 0              | Độ ưu tiên           |
| status       | tinyint         | No       | 1              | 1 active, 0 inactive |
| created_at   | timestamp       | Yes      | null           | Created time         |
| updated_at   | timestamp       | Yes      | null           | Updated time         |

Indexes:

| Index        | Description                   |
| ------------ | ----------------------------- |
| tax_class_id | Tìm tax rate theo tax class   |
| country_code | Tìm tax rate theo quốc gia    |
| status       | Lọc active/inactive           |
| priority     | Sắp xếp khi có nhiều tax rate |

---

## 7.3. Default Data

Tax classes mặc định:

| Code         | Name         | Description     | Status |
| ------------ | ------------ | --------------- | ------ |
| standard_tax | Standard Tax | Thuế tiêu chuẩn | Active |
| reduced_tax  | Reduced Tax  | Thuế giảm       | Active |
| tax_free     | Tax Free     | Không tính thuế | Active |

Tax rates mặc định:

| Tax Class    | Country Code | Region |    Rate | Priority | Status |
| ------------ | ------------ | ------ | ------: | -------: | ------ |
| standard_tax | VN           | null   | 10.0000 |        1 | Active |
| reduced_tax  | VN           | null   |  5.0000 |        1 | Active |
| tax_free     | VN           | null   |  0.0000 |        1 | Active |

---

## 8. Route Design

Các route Tax Classes cần có:

| Method | URL                            | Description         |
| ------ | ------------------------------ | ------------------- |
| GET    | `/admin/tax-classes`           | Danh sách tax class |
| GET    | `/admin/tax-classes/create`    | Form thêm tax class |
| POST   | `/admin/tax-classes`           | Lưu tax class mới   |
| GET    | `/admin/tax-classes/{id}/edit` | Form sửa tax class  |
| PUT    | `/admin/tax-classes/{id}`      | Cập nhật tax class  |
| DELETE | `/admin/tax-classes/{id}`      | Xóa tax class       |

Các route Tax Rates cần có:

| Method | URL                          | Description        |
| ------ | ---------------------------- | ------------------ |
| GET    | `/admin/tax-rates`           | Danh sách tax rate |
| GET    | `/admin/tax-rates/create`    | Form thêm tax rate |
| POST   | `/admin/tax-rates`           | Lưu tax rate mới   |
| GET    | `/admin/tax-rates/{id}/edit` | Form sửa tax rate  |
| PUT    | `/admin/tax-rates/{id}`      | Cập nhật tax rate  |
| DELETE | `/admin/tax-rates/{id}`      | Xóa tax rate       |

Tất cả route trên cần được bảo vệ bởi admin authentication.

---

## 9. Validation Rules

## 9.1. Create / Update Tax Class

| Field       | Rule                                 |
| ----------- | ------------------------------------ |
| code        | Required, unique, max 100 characters |
| name        | Required, max 255 characters         |
| description | Optional                             |
| status      | Required                             |

Khi update, rule unique cần bỏ qua tax class hiện tại.

---

## 9.2. Create / Update Tax Rate

| Field        | Rule                              |
| ------------ | --------------------------------- |
| tax_class_id | Required, exists in tax_classes   |
| country_code | Optional, max 10 characters       |
| region       | Optional, max 100 characters      |
| rate         | Required, numeric, min 0, max 100 |
| priority     | Required, integer, min 0          |
| status       | Required                          |

---

## 10. Business Logic

## 10.1. Create Tax Class Flow

* Admin mở màn hình tạo tax class.
* Admin nhập thông tin tax class.
* Hệ thống validate dữ liệu.
* Hệ thống chuyển code thành lowercase.
* Hệ thống lưu tax class.
* Hệ thống clear cache tax.
* Hệ thống redirect về danh sách tax class với thông báo thành công.

---

## 10.2. Update Tax Class Flow

* Admin mở màn hình sửa tax class.
* Admin cập nhật thông tin.
* Hệ thống validate dữ liệu.
* Hệ thống chuyển code thành lowercase.
* Hệ thống lưu thay đổi.
* Hệ thống clear cache tax.
* Hệ thống redirect về danh sách tax class với thông báo thành công.

---

## 10.3. Delete Tax Class Flow

* Admin click delete tax class.
* Hệ thống kiểm tra tax class có đang được sử dụng hay không.
* Nếu đang được sử dụng, không cho xóa.
* Nếu không có ràng buộc dữ liệu, cho phép xóa.
* Hệ thống clear cache tax.
* Hệ thống redirect về danh sách tax class với thông báo phù hợp.

---

## 10.4. Create Tax Rate Flow

* Admin mở màn hình tạo tax rate.
* Admin chọn tax class.
* Admin nhập country code, region, rate, priority, status.
* Hệ thống validate dữ liệu.
* Hệ thống chuyển country code thành uppercase nếu có nhập.
* Hệ thống lưu tax rate.
* Hệ thống clear cache tax.
* Hệ thống redirect về danh sách tax rate với thông báo thành công.

---

## 10.5. Update Tax Rate Flow

* Admin mở màn hình sửa tax rate.
* Admin cập nhật thông tin.
* Hệ thống validate dữ liệu.
* Hệ thống chuyển country code thành uppercase nếu có nhập.
* Hệ thống lưu thay đổi.
* Hệ thống clear cache tax.
* Hệ thống redirect về danh sách tax rate với thông báo thành công.

---

## 10.6. Delete Tax Rate Flow

* Admin click delete tax rate.
* Hệ thống kiểm tra tax rate có đang được sử dụng hay không.
* Nếu đang được sử dụng, không cho xóa.
* Nếu không có ràng buộc dữ liệu, cho phép xóa.
* Hệ thống clear cache tax.
* Hệ thống redirect về danh sách tax rate với thông báo phù hợp.

---

## 10.7. Tax Calculation Flow

* Hệ thống nhận amount, tax class và thông tin địa chỉ nếu có.
* Hệ thống kiểm tra setting `tax_enabled`.
* Nếu tax bị tắt, tax amount là 0.
* Nếu tax được bật, hệ thống tìm tax rate active phù hợp.
* Nếu không tìm thấy tax rate, tax amount là 0.
* Nếu giá chưa bao gồm thuế, hệ thống tính thuế cộng thêm.
* Nếu giá đã bao gồm thuế, hệ thống tách phần thuế trong giá.
* Hệ thống trả về tax rate, tax amount và total amount.

---

## 11. Error Handling

| Case                             | Expected Handling           |
| -------------------------------- | --------------------------- |
| Tax class code bị trùng          | Hiển thị lỗi validation     |
| Tax rate nhỏ hơn 0               | Hiển thị lỗi validation     |
| Tax rate lớn hơn 100             | Hiển thị lỗi validation     |
| Tax class không tồn tại          | Hiển thị lỗi validation     |
| Xóa tax class đang được dùng     | Không cho xóa, hiển thị lỗi |
| Xóa tax rate đang được dùng      | Không cho xóa, hiển thị lỗi |
| Guest truy cập tax management    | Redirect login              |
| Customer truy cập tax management | Chặn truy cập               |
| Seeder chạy nhiều lần            | Không tạo duplicate data    |

---

## 12. Security

Yêu cầu bảo mật:

* Chỉ admin mới được truy cập Tax Management.
* Customer không được truy cập.
* Validate toàn bộ input.
* Không lưu dữ liệu request ngoài danh sách field cho phép.
* Không cho xóa dữ liệu đang được sử dụng.
* Form phải có CSRF protection.
* Không hiển thị lỗi kỹ thuật trực tiếp ra màn hình production.

---

## 13. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type       | Description                                                |
| ---------- | ---------------------------------------------------------- |
| Model      | Tax class model, tax rate model                            |
| Service    | Tax service                                                |
| Controller | Admin tax class controller, admin tax rate controller      |
| Request    | Validate create/update tax class, create/update tax rate   |
| Seeder     | Tax class seeder, tax rate seeder                          |
| Migration  | Tạo bảng tax_classes và tax_rates nếu chưa có              |
| View       | List, create, edit, form cho tax class và tax rate         |
| Route      | Admin routes cho Tax Management                            |
| Sidebar    | Cập nhật menu Tax Classes và Tax Rates trong admin sidebar |

Lưu ý:

* Nếu bảng `tax_classes` và `tax_rates` đã tồn tại thì không tạo migration trùng.
* Nếu seeder tax đã tồn tại thì cập nhật, không tạo duplicate.
* Không sửa các module không liên quan.

---

## 14. Commands

Sau khi Codex implement xong, chạy các lệnh kiểm tra sau:

| Command                | Purpose                              |
| ---------------------- | ------------------------------------ |
| php artisan migrate    | Chạy migration                       |
| php artisan db:seed    | Chạy seeder                          |
| php artisan route:list | Kiểm tra route                       |
| php artisan serve      | Chạy local server                    |
| npm run build          | Build frontend nếu có thay đổi asset |

URL test:

`http://127.0.0.1:8000/admin/tax-classes`

`http://127.0.0.1:8000/admin/tax-rates`

---

## 15. Test Cases

| Test Case ID | Scenario                          | Expected Result              |
| ------------ | --------------------------------- | ---------------------------- |
| TC-001       | Guest vào `/admin/tax-classes`    | Redirect login               |
| TC-002       | Customer vào `/admin/tax-classes` | Bị chặn                      |
| TC-003       | Admin vào `/admin/tax-classes`    | Hiển thị danh sách tax class |
| TC-004       | Tạo tax class hợp lệ              | Tạo thành công               |
| TC-005       | Tạo tax class code trùng          | Hiển thị lỗi validation      |
| TC-006       | Sửa tax class hợp lệ              | Cập nhật thành công          |
| TC-007       | Xóa tax class chưa sử dụng        | Xóa thành công               |
| TC-008       | Admin vào `/admin/tax-rates`      | Hiển thị danh sách tax rate  |
| TC-009       | Tạo tax rate hợp lệ               | Tạo thành công               |
| TC-010       | Tạo tax rate nhỏ hơn 0            | Hiển thị lỗi validation      |
| TC-011       | Tạo tax rate lớn hơn 100          | Hiển thị lỗi validation      |
| TC-012       | Sửa tax rate hợp lệ               | Cập nhật thành công          |
| TC-013       | Xóa tax rate chưa sử dụng         | Xóa thành công               |
| TC-014       | Tính thuế khi tax enabled         | Tax amount được tính đúng    |
| TC-015       | Tính thuế khi tax disabled        | Tax amount bằng 0            |
| TC-016       | Tính thuế khi price include tax   | Tách tax amount đúng         |
| TC-017       | Chạy seeder nhiều lần             | Không duplicate tax data     |

---

## 16. Acceptance Criteria

Task này được xem là hoàn thành khi:

* [ ] Có màn hình danh sách tax class.
* [ ] Có màn hình thêm tax class.
* [ ] Có màn hình sửa tax class.
* [ ] Có chức năng xóa tax class.
* [ ] Có màn hình danh sách tax rate.
* [ ] Có màn hình thêm tax rate.
* [ ] Có màn hình sửa tax rate.
* [ ] Có chức năng xóa tax rate.
* [ ] Có dữ liệu seed standard tax, reduced tax, tax free.
* [ ] Có tax rate mặc định cho VN.
* [ ] Tax class code được lưu dạng lowercase.
* [ ] Country code được lưu dạng uppercase.
* [ ] Tax rate được validate từ 0 đến 100.
* [ ] Có logic tính tax cơ bản.
* [ ] Có logic xử lý price include tax.
* [ ] Có đọc setting `tax_enabled`.
* [ ] Có đọc setting `price_include_tax`.
* [ ] Admin sidebar có menu Tax Classes và Tax Rates.
* [ ] Customer không truy cập được Tax Management.
* [ ] Chạy được migration và seeder.
* [ ] Không implement product, cart, checkout, order trong task này.

---

## 17. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-08-tax-management.md

Sau đó implement Task 08: Tax Management theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 08.
* Không làm sang task khác.
* Không tự ý thay đổi thiết kế lớn.
* Không implement product, cart, checkout, order hoặc report.
* Sau khi làm xong, báo cáo file đã tạo/sửa và lệnh cần chạy để test.
