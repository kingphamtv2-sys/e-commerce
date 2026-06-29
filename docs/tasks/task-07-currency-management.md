Dưới đây là bản **Task 07: Currency Management** đã bỏ phần hướng dẫn code, không có đoạn code mẫu. Bạn copy vào file:

`docs/tasks/task-07-currency-management.md`

# Task 07: Currency Management

## 1. Overview

Task này dùng để xây dựng chức năng quản lý tiền tệ cho hệ thống e-commerce.

Hệ thống cần hỗ trợ nhiều loại tiền tệ như:

* VND
* USD
* JPY

Giá sản phẩm trong hệ thống sẽ được lưu theo tiền tệ mặc định. Khi khách hàng chọn loại tiền tệ khác, hệ thống sẽ chuyển đổi giá để hiển thị.

Khi tạo đơn hàng, hệ thống cần lưu lại currency và exchange rate tại thời điểm đặt hàng để đơn hàng cũ không bị thay đổi khi admin cập nhật tỷ giá mới.

---

## 2. Objectives

Sau khi hoàn thành task này, hệ thống cần có:

* Màn hình danh sách currency trong admin.
* Chức năng thêm currency.
* Chức năng sửa currency.
* Chức năng xóa currency.
* Chức năng bật hoặc tắt currency.
* Chức năng thiết lập default currency.
* Chỉ có một currency mặc định trong hệ thống.
* Không được xóa default currency.
* Không được disable default currency.
* Có dữ liệu mặc định cho VND, USD, JPY.
* Có xử lý chuyển đổi tiền tệ.
* Có xử lý format giá tiền.
* Sidebar admin có menu Currencies.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong task này:

* Quản lý danh sách currencies.
* Thêm currency mới.
* Chỉnh sửa currency.
* Xóa currency.
* Bật hoặc tắt currency.
* Set default currency.
* Validate dữ liệu currency.
* Seed dữ liệu currency mặc định.
* Tạo logic chuyển đổi tiền tệ.
* Tạo logic format giá tiền.
* Cập nhật menu Currencies trong admin sidebar.

### 3.2. Out of Scope

Các phần chưa làm trong task này:

* Tự động cập nhật tỷ giá từ API bên ngoài.
* Public currency switcher ngoài frontend.
* Checkout.
* Order creation.
* Payment.
* Online payment đa tiền tệ.
* Report đa currency.
* Currency theo từng quốc gia.
* Đồng bộ tỷ giá tự động theo lịch.

---

## 4. User Roles

| Role        | Permission                                |
| ----------- | ----------------------------------------- |
| super_admin | Có toàn quyền quản lý currency            |
| admin       | Có quyền quản lý currency                 |
| staff       | Có thể xem hoặc thao tác giới hạn sau này |
| customer    | Không được truy cập                       |

Route quản lý currency chỉ dành cho khu vực admin.

Customer không được truy cập các màn hình currency management.

---

## 5. Functional Requirements

## FR-01: Currency List

Admin có thể xem danh sách currency tại:

`/admin/currencies`

Danh sách cần hiển thị:

| Field           | Description                     |
| --------------- | ------------------------------- |
| Code            | Mã tiền tệ, ví dụ VND, USD, JPY |
| Name            | Tên tiền tệ                     |
| Symbol          | Ký hiệu tiền tệ                 |
| Exchange Rate   | Tỷ giá so với base currency     |
| Decimal Places  | Số chữ số thập phân             |
| Symbol Position | Vị trí ký hiệu tiền             |
| Default         | Có phải currency mặc định không |
| Status          | Active hoặc Inactive            |
| Actions         | Edit, Delete, Set Default       |

---

## FR-02: Create Currency

Admin có thể thêm currency mới tại:

`/admin/currencies/create`

Thông tin cần nhập:

| Field              | Required | Description                     |
| ------------------ | -------- | ------------------------------- |
| Code               | Yes      | Mã tiền tệ                      |
| Name               | Yes      | Tên tiền tệ                     |
| Symbol             | Yes      | Ký hiệu tiền                    |
| Exchange Rate      | Yes      | Tỷ giá so với base currency     |
| Decimal Places     | Yes      | Số chữ số thập phân             |
| Symbol Position    | Yes      | before hoặc after               |
| Thousand Separator | No       | Dấu phân cách hàng nghìn        |
| Decimal Separator  | No       | Dấu phân cách thập phân         |
| Status             | Yes      | Active hoặc Inactive            |
| Is Default         | No       | Có phải currency mặc định không |

Expected behavior:

* Currency code là bắt buộc.
* Currency code không được trùng.
* Currency code nên được lưu ở dạng viết hoa.
* Exchange rate phải lớn hơn 0.
* Nếu currency mới được chọn làm default, các currency khác phải bị bỏ default.
* Sau khi tạo thành công, quay lại danh sách currency.

---

## FR-03: Edit Currency

Admin có thể sửa currency tại:

`/admin/currencies/{id}/edit`

Admin có thể sửa:

* Code
* Name
* Symbol
* Exchange rate
* Decimal places
* Symbol position
* Thousand separator
* Decimal separator
* Status
* Default flag

Expected behavior:

* Không được disable currency mặc định.
* Nếu currency được set default thì currency đó phải active.
* Nếu set currency hiện tại làm default, các currency khác phải bị bỏ default.
* Sau khi cập nhật thành công, quay lại danh sách currency.

---

## FR-04: Delete Currency

Admin có thể xóa currency nếu currency đó không phải default.

Business rules:

* Không được xóa default currency.
* Không nên xóa currency đã phát sinh đơn hàng trong tương lai.
* Nếu không thể xóa, hệ thống cần hiển thị thông báo lỗi rõ ràng.

---

## FR-05: Set Default Currency

Admin có thể chọn một currency làm mặc định.

Expected behavior:

* Currency được chọn phải active.
* Currency được chọn sẽ trở thành default currency.
* Các currency khác sẽ không còn là default.
* Hệ thống chỉ được có một default currency.
* Sau khi set default thành công, quay lại danh sách currency.

---

## FR-06: Disable Currency

Admin có thể disable currency không sử dụng.

Business rules:

* Không được disable default currency.
* Currency inactive không được sử dụng ở public site.
* Currency inactive không nên xuất hiện trong danh sách chọn currency của customer.

---

## FR-07: Currency Conversion

Hệ thống cần hỗ trợ chuyển đổi giá tiền.

Nguyên tắc:

* Product price lưu theo base currency.
* Base currency mặc định là VND.
* Currency khác sử dụng exchange rate để convert.
* Exchange rate thể hiện giá trị của 1 đơn vị currency đó so với base currency.

Ví dụ:

| Currency | Exchange Rate | Meaning            |
| -------- | ------------: | ------------------ |
| VND      |             1 | 1 VND = 1 VND      |
| USD      |         25000 | 1 USD = 25,000 VND |
| JPY      |           170 | 1 JPY = 170 VND    |

Ví dụ chuyển đổi:

|  Base Price | Target Currency | Exchange Rate |    Display Price |
| ----------: | --------------- | ------------: | ---------------: |
| 500,000 VND | USD             |        25,000 |           20 USD |
| 500,000 VND | JPY             |           170 | khoảng 2,941 JPY |

---

## FR-08: Currency Format

Hệ thống cần format giá theo cấu hình của từng currency.

Ví dụ:

| Currency | Amount | Display   |
| -------- | -----: | --------- |
| VND      | 500000 | 500,000 ₫ |
| USD      |     20 | $20.00    |
| JPY      |   2941 | ¥2,941    |

Format phụ thuộc vào:

* Symbol
* Decimal places
* Symbol position
* Thousand separator
* Decimal separator

---

## FR-09: Default Currency

Hệ thống cần có một default currency.

Default currency đề xuất:

| Code | Name            | Symbol |
| ---- | --------------- | ------ |
| VND  | Vietnamese Dong | ₫      |

Business rules:

* Chỉ có một default currency.
* Default currency không được xóa.
* Default currency không được disable.
* Nếu chưa có default currency, hệ thống cần tạo default currency từ seeder.

---

## FR-10: Cache Currency

Danh sách currency nên được cache để giảm query database.

Khi có thay đổi sau đây, cache cần được clear:

* Create currency
* Update currency
* Delete currency
* Set default currency
* Enable hoặc disable currency

---

## 6. UI / Screen Design

## 6.1. Screen List

| Screen          | URL                           | Description       |
| --------------- | ----------------------------- | ----------------- |
| Currency List   | `/admin/currencies`           | Danh sách tiền tệ |
| Create Currency | `/admin/currencies/create`    | Thêm tiền tệ      |
| Edit Currency   | `/admin/currencies/{id}/edit` | Sửa tiền tệ       |

---

## 6.2. Currency List Screen

Màn hình danh sách cần có:

* Page title: Currencies
* Button: Add Currency
* Table hiển thị danh sách currency
* Action buttons: Edit, Delete, Set Default

Table columns:

| Column         | Description          |
| -------------- | -------------------- |
| Code           | Mã tiền tệ           |
| Name           | Tên tiền tệ          |
| Symbol         | Ký hiệu              |
| Exchange Rate  | Tỷ giá               |
| Decimal Places | Số chữ số thập phân  |
| Format         | Cách hiển thị tiền   |
| Default        | Badge default        |
| Status         | Active hoặc Inactive |
| Actions        | Các nút thao tác     |

---

## 6.3. Create / Edit Form

Form gồm các field:

| Label              | Field              |
| ------------------ | ------------------ |
| Code               | code               |
| Name               | name               |
| Symbol             | symbol             |
| Exchange Rate      | exchange_rate      |
| Decimal Places     | decimal_places     |
| Symbol Position    | symbol_position    |
| Thousand Separator | thousand_separator |
| Decimal Separator  | decimal_separator  |
| Status             | status             |
| Default Currency   | is_default         |

Button:

* Save
* Back

---

## 7. Database Design

## 7.1. Table: currencies

Bảng `currencies` dùng để lưu thông tin tiền tệ.

| Column             | Type            | Nullable | Default        | Description                 |
| ------------------ | --------------- | -------- | -------------- | --------------------------- |
| id                 | bigint unsigned | No       | auto increment | Primary key                 |
| code               | varchar(10)     | No       | null           | VND, USD, JPY               |
| name               | varchar(100)    | No       | null           | Currency name               |
| symbol             | varchar(10)     | No       | null           | ₫, $, ¥                     |
| exchange_rate      | decimal(15,6)   | No       | 1.000000       | Tỷ giá so với base currency |
| decimal_places     | tinyint         | No       | 0              | Số chữ số thập phân         |
| symbol_position    | varchar(20)     | No       | after          | before hoặc after           |
| thousand_separator | varchar(5)      | Yes      | ,              | Dấu phân cách hàng nghìn    |
| decimal_separator  | varchar(5)      | Yes      | .              | Dấu phân cách thập phân     |
| is_default         | tinyint         | No       | 0              | 1 là default currency       |
| status             | tinyint         | No       | 1              | 1 active, 0 inactive        |
| created_at         | timestamp       | Yes      | null           | Created time                |
| updated_at         | timestamp       | Yes      | null           | Updated time                |

Indexes:

| Index       | Description                  |
| ----------- | ---------------------------- |
| unique code | Không cho trùng mã currency  |
| status      | Dùng để lọc active/inactive  |
| is_default  | Dùng để tìm default currency |

---

## 7.2. Default Data

Dữ liệu mặc định:

| Code | Name            | Symbol | Exchange Rate | Decimal Places | Symbol Position | Default | Status |
| ---- | --------------- | ------ | ------------: | -------------: | --------------- | ------- | ------ |
| VND  | Vietnamese Dong | ₫      |             1 |              0 | after           | Yes     | Active |
| USD  | US Dollar       | $      |         25000 |              2 | before          | No      | Active |
| JPY  | Japanese Yen    | ¥      |           170 |              0 | before          | No      | Active |

---

## 8. Route Design

Các route cần có:

| Method | URL                                  | Description          |
| ------ | ------------------------------------ | -------------------- |
| GET    | `/admin/currencies`                  | Danh sách currency   |
| GET    | `/admin/currencies/create`           | Form thêm currency   |
| POST   | `/admin/currencies`                  | Lưu currency mới     |
| GET    | `/admin/currencies/{id}/edit`        | Form sửa currency    |
| PUT    | `/admin/currencies/{id}`             | Cập nhật currency    |
| DELETE | `/admin/currencies/{id}`             | Xóa currency         |
| PUT    | `/admin/currencies/{id}/set-default` | Set default currency |

Tất cả route trên cần được bảo vệ bởi admin authentication.

---

## 9. Validation Rules

## 9.1. Create Currency

| Field              | Rule                                |
| ------------------ | ----------------------------------- |
| code               | Required, unique, max 10 characters |
| name               | Required, max 100 characters        |
| symbol             | Required, max 10 characters         |
| exchange_rate      | Required, numeric, greater than 0   |
| decimal_places     | Required, integer, min 0, max 6     |
| symbol_position    | Required, only before or after      |
| thousand_separator | Optional, max 5 characters          |
| decimal_separator  | Optional, max 5 characters          |
| status             | Required                            |
| is_default         | Optional                            |

---

## 9.2. Update Currency

| Field              | Rule                                                        |
| ------------------ | ----------------------------------------------------------- |
| code               | Required, unique except current currency, max 10 characters |
| name               | Required, max 100 characters                                |
| symbol             | Required, max 10 characters                                 |
| exchange_rate      | Required, numeric, greater than 0                           |
| decimal_places     | Required, integer, min 0, max 6                             |
| symbol_position    | Required, only before or after                              |
| thousand_separator | Optional, max 5 characters                                  |
| decimal_separator  | Optional, max 5 characters                                  |
| status             | Required                                                    |
| is_default         | Optional                                                    |

---

## 10. Business Logic

## 10.1. Create Currency Flow

* Admin mở màn hình tạo currency.
* Admin nhập thông tin currency.
* Hệ thống validate dữ liệu.
* Hệ thống chuyển currency code thành chữ hoa.
* Nếu currency được chọn làm default, các currency khác bị bỏ default.
* Hệ thống lưu currency mới.
* Hệ thống clear cache currency.
* Hệ thống redirect về danh sách currency với thông báo thành công.

---

## 10.2. Update Currency Flow

* Admin mở màn hình sửa currency.
* Admin cập nhật thông tin.
* Hệ thống validate dữ liệu.
* Hệ thống chuyển currency code thành chữ hoa.
* Nếu currency hiện tại là default, không cho disable.
* Nếu currency được chọn làm default, currency đó phải active.
* Nếu currency được chọn làm default, các currency khác bị bỏ default.
* Hệ thống lưu thay đổi.
* Hệ thống clear cache currency.
* Hệ thống redirect về danh sách currency với thông báo thành công.

---

## 10.3. Delete Currency Flow

* Admin click delete currency.
* Hệ thống kiểm tra currency có phải default hay không.
* Nếu là default currency, không cho xóa.
* Nếu không phải default currency, cho phép xóa.
* Hệ thống clear cache currency.
* Hệ thống redirect về danh sách currency với thông báo phù hợp.

---

## 10.4. Set Default Currency Flow

* Admin chọn Set Default.
* Hệ thống kiểm tra currency có active hay không.
* Nếu currency inactive, không cho set default.
* Nếu currency active, set currency hiện tại thành default.
* Các currency khác bị bỏ default.
* Hệ thống clear cache currency.
* Hệ thống redirect về danh sách currency với thông báo thành công.

---

## 10.5. Convert Currency Flow

* Hệ thống nhận số tiền cần convert.
* Hệ thống xác định currency gốc.
* Hệ thống xác định currency đích.
* Hệ thống quy đổi số tiền về base currency nếu cần.
* Hệ thống chuyển từ base currency sang currency đích.
* Hệ thống trả về giá trị đã convert.

---

## 10.6. Format Currency Flow

* Hệ thống nhận amount và currency code.
* Hệ thống lấy thông tin currency.
* Hệ thống làm tròn theo decimal places.
* Hệ thống format theo thousand separator và decimal separator.
* Hệ thống đặt symbol trước hoặc sau amount theo symbol position.
* Hệ thống trả về chuỗi giá tiền đã format.

---

## 11. Error Handling

| Case                                  | Expected Handling               |
| ------------------------------------- | ------------------------------- |
| Currency code bị trùng                | Hiển thị lỗi validation         |
| Exchange rate nhỏ hơn hoặc bằng 0     | Hiển thị lỗi validation         |
| Xóa default currency                  | Không cho xóa, hiển thị lỗi     |
| Disable default currency              | Không cho disable, hiển thị lỗi |
| Set inactive currency làm default     | Không cho set, hiển thị lỗi     |
| Guest truy cập currency management    | Redirect login                  |
| Customer truy cập currency management | Chặn truy cập                   |
| Seeder chạy nhiều lần                 | Không tạo duplicate data        |

---

## 12. Security

Yêu cầu bảo mật:

* Chỉ admin mới được truy cập Currency Management.
* Customer không được truy cập.
* Validate toàn bộ input.
* Không lưu dữ liệu request ngoài danh sách field cho phép.
* Không cho xóa default currency.
* Không cho disable default currency.
* Form phải có CSRF protection.
* Không hiển thị lỗi kỹ thuật trực tiếp ra màn hình production.

---

## 13. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type       | Description                                  |
| ---------- | -------------------------------------------- |
| Model      | Currency model                               |
| Service    | Currency service                             |
| Controller | Admin currency controller                    |
| Request    | Validate create/update currency              |
| Seeder     | Currency seeder                              |
| Migration  | Tạo bảng currencies nếu chưa có              |
| View       | List, create, edit, form currency            |
| Route      | Admin routes cho currency management         |
| Sidebar    | Cập nhật menu Currencies trong admin sidebar |

Lưu ý:

* Nếu bảng `currencies` đã tồn tại thì không tạo migration trùng.
* Nếu seeder currency đã tồn tại thì cập nhật, không tạo duplicate.
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

`http://127.0.0.1:8000/admin/currencies`

---

## 15. Test Cases

| Test Case ID | Scenario                              | Expected Result             |
| ------------ | ------------------------------------- | --------------------------- |
| TC-001       | Guest vào `/admin/currencies`         | Redirect login              |
| TC-002       | Customer vào `/admin/currencies`      | Bị chặn                     |
| TC-003       | Admin vào `/admin/currencies`         | Hiển thị danh sách currency |
| TC-004       | Tạo currency hợp lệ                   | Tạo thành công              |
| TC-005       | Tạo currency code trùng               | Hiển thị lỗi validation     |
| TC-006       | Tạo currency với exchange rate bằng 0 | Hiển thị lỗi validation     |
| TC-007       | Sửa currency hợp lệ                   | Cập nhật thành công         |
| TC-008       | Xóa currency không phải default       | Xóa thành công              |
| TC-009       | Xóa default currency                  | Không cho xóa               |
| TC-010       | Disable default currency              | Không cho disable           |
| TC-011       | Set currency active làm default       | Set thành công              |
| TC-012       | Set currency inactive làm default     | Không cho set               |
| TC-013       | Convert 500,000 VND sang USD          | Kết quả là 20 USD           |
| TC-014       | Convert 20 USD sang VND               | Kết quả là 500,000 VND      |
| TC-015       | Format 500,000 VND                    | Hiển thị 500,000 ₫          |
| TC-016       | Format 20 USD                         | Hiển thị $20.00             |
| TC-017       | Chạy seeder nhiều lần                 | Không duplicate currency    |

---

## 16. Acceptance Criteria

Task này được xem là hoàn thành khi:

* [ ] Có màn hình danh sách currency.
* [ ] Có màn hình thêm currency.
* [ ] Có màn hình sửa currency.
* [ ] Có chức năng xóa currency.
* [ ] Có chức năng set default currency.
* [ ] Có dữ liệu seed VND, USD, JPY.
* [ ] Chỉ có một default currency.
* [ ] Không xóa được default currency.
* [ ] Không disable được default currency.
* [ ] Không set inactive currency làm default.
* [ ] Currency code được lưu dạng uppercase.
* [ ] Exchange rate được validate lớn hơn 0.
* [ ] Có logic convert currency.
* [ ] Có logic format currency.
* [ ] Admin sidebar có menu Currencies.
* [ ] Customer không truy cập được currency management.
* [ ] Chạy được migration và seeder.
* [ ] Không implement checkout, order, payment trong task này.

---

## 17. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-07-currency-management.md

Sau đó implement Task 07: Currency Management theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 07.
* Không làm sang task khác.
* Không tự ý thay đổi thiết kế lớn.
* Không implement checkout, order, payment hoặc public currency switcher.
* Sau khi làm xong, báo cáo file đã tạo/sửa và lệnh cần chạy để test.
