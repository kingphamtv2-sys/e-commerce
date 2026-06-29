Bạn tạo file:

`docs/tasks/task-09-category-management-with-translation.md`

và copy nội dung dưới đây vào file.

# Task 09: Category Management with Translation

## 1. Overview

Task này dùng để xây dựng chức năng quản lý danh mục sản phẩm cho hệ thống e-commerce.

Category Management cần hỗ trợ đa ngôn ngữ, nghĩa là một category có thể có nhiều bản dịch theo từng language.

Ví dụ:

| Language | Category Name | Slug        |
| -------- | ------------- | ----------- |
| vi       | Áo nam        | ao-nam      |
| en       | Men's Shirts  | mens-shirts |
| ja       | メンズシャツ        | mens-shirts |

Category sẽ được dùng cho:

* Product Management
* Public Product Catalog
* Product Filter
* SEO URL
* Menu danh mục ngoài frontend

---

## 2. Objectives

Sau khi hoàn thành task này, hệ thống cần có:

* Màn hình danh sách category trong admin.
* Chức năng thêm category.
* Chức năng sửa category.
* Chức năng xóa category.
* Chức năng bật hoặc tắt category.
* Hỗ trợ category cha/con.
* Hỗ trợ translation theo nhiều ngôn ngữ.
* Hỗ trợ slug riêng theo từng ngôn ngữ.
* Hỗ trợ SEO title và SEO description theo từng ngôn ngữ.
* Có thể chọn image cho category.
* Có thể sắp xếp category bằng sort order.
* Sidebar admin có menu Categories.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong task này:

* Quản lý danh sách category.
* Thêm category mới.
* Chỉnh sửa category.
* Xóa category.
* Bật hoặc tắt category.
* Chọn parent category.
* Nhập sort order.
* Nhập image path.
* Nhập bản dịch category theo từng language active.
* Validate dữ liệu category.
* Validate dữ liệu translation.
* Tạo slug tự động nếu admin không nhập.
* Đảm bảo slug unique theo từng language.
* Cập nhật menu Categories trong admin sidebar.

### 3.2. Out of Scope

Các phần chưa làm trong task này:

* Product Management.
* Product Translation.
* Public Category Page.
* Public Product Catalog.
* Upload image nâng cao.
* Category icon.
* Drag and drop category tree.
* SEO sitemap.
* Frontend category menu.
* Import/export category.
* Auto translate.

---

## 4. User Roles

| Role        | Permission                                |
| ----------- | ----------------------------------------- |
| super_admin | Có toàn quyền quản lý category            |
| admin       | Có quyền quản lý category                 |
| staff       | Có thể xem hoặc thao tác giới hạn sau này |
| customer    | Không được truy cập                       |

Route quản lý category chỉ dành cho khu vực admin.

Customer không được truy cập các màn hình Category Management.

---

## 5. Functional Requirements

## FR-01: Category List

Admin có thể xem danh sách category tại:

`/admin/categories`

Danh sách cần hiển thị:

| Field           | Description                        |
| --------------- | ---------------------------------- |
| ID              | Mã category                        |
| Name            | Tên category theo default language |
| Parent Category | Category cha nếu có                |
| Image           | Ảnh category nếu có                |
| Status          | Active hoặc Inactive               |
| Sort Order      | Thứ tự hiển thị                    |
| Created At      | Ngày tạo                           |
| Actions         | Edit, Delete                       |

Expected behavior:

* Hiển thị tên category theo default language.
* Nếu category chưa có translation ở default language, fallback sang translation đầu tiên có sẵn.
* Có filter theo keyword.
* Có filter theo status.
* Có filter theo parent category nếu cần.
* Có pagination.

---

## FR-02: Create Category

Admin có thể thêm category mới tại:

`/admin/categories/create`

Thông tin chung:

| Field           | Required | Description            |
| --------------- | -------- | ---------------------- |
| Parent Category | No       | Category cha           |
| Image           | No       | Đường dẫn ảnh category |
| Sort Order      | No       | Thứ tự hiển thị        |
| Status          | Yes      | Active hoặc Inactive   |

Thông tin dịch theo từng language:

| Field            | Required                      | Description     |
| ---------------- | ----------------------------- | --------------- |
| Name             | Required for default language | Tên category    |
| Slug             | No                            | URL slug        |
| Description      | No                            | Mô tả category  |
| Meta Title       | No                            | SEO title       |
| Meta Description | No                            | SEO description |

Expected behavior:

* Category cần có ít nhất translation cho default language.
* Nếu slug trống, hệ thống tự tạo slug từ name.
* Slug phải unique theo từng language.
* Có thể nhập translation cho nhiều language trong cùng một form.
* Sau khi tạo thành công, quay lại danh sách category.

---

## FR-03: Edit Category

Admin có thể sửa category tại:

`/admin/categories/{id}/edit`

Admin có thể sửa:

* Parent category
* Image
* Sort order
* Status
* Name theo từng language
* Slug theo từng language
* Description theo từng language
* Meta title theo từng language
* Meta description theo từng language

Expected behavior:

* Không cho chọn chính category hiện tại làm parent.
* Không cho chọn category con của chính nó làm parent.
* Slug phải unique theo từng language, ngoại trừ translation hiện tại.
* Nếu xóa name của default language, hiển thị lỗi validation.
* Sau khi cập nhật thành công, quay lại danh sách category.

---

## FR-04: Delete Category

Admin có thể xóa category nếu category chưa được sử dụng.

Business rules:

* Không nên xóa category đang có product.
* Không nên xóa category đang có category con.
* Nếu category có product hoặc category con, hiển thị lỗi và không cho xóa.
* Nếu category chưa có dữ liệu liên quan, cho phép xóa.
* Khi xóa category, translation của category cũng cần được xóa theo.

---

## FR-05: Enable / Disable Category

Admin có thể bật hoặc tắt category.

Business rules:

* Category inactive không nên hiển thị ở public site.
* Category inactive không nên xuất hiện trong filter public.
* Product thuộc category inactive có thể vẫn tồn tại, nhưng category không hiển thị ngoài frontend.
* Không ảnh hưởng đến order cũ.

---

## FR-06: Category Parent / Child

Hệ thống cần hỗ trợ category cha/con.

Ví dụ:

| Parent     | Child    |
| ---------- | -------- |
| Thời trang | Áo nam   |
| Thời trang | Quần nam |
| Điện thoại | iPhone   |
| Điện thoại | Samsung  |

Business rules:

* Category có thể không có parent.
* Category có thể có nhiều category con.
* Không cho tạo vòng lặp parent-child.
* Không cho category chọn chính nó làm parent.
* Không cho category chọn category con của nó làm parent.

---

## FR-07: Category Translation

Mỗi category có thể có nhiều translation.

Ví dụ:

| Category ID | Language | Name         | Slug           |
| ----------- | -------- | ------------ | -------------- |
| 1           | vi       | Áo nam       | ao-nam         |
| 1           | en       | Men's Shirts | mens-shirts    |
| 1           | ja       | メンズシャツ       | mens-shirts-ja |

Business rules:

* Mỗi category chỉ có một translation cho một language.
* Translation của default language là bắt buộc.
* Translation của language khác có thể optional.
* Nếu translation của language hiện tại không tồn tại, fallback về default language.
* Slug phải unique trong cùng một language.

---

## FR-08: Category Slug

Slug dùng để tạo URL category ngoài frontend.

Ví dụ:

`/vi/category/ao-nam`

`/en/category/mens-shirts`

Business rules:

* Slug nên dùng chữ thường.
* Slug không nên có dấu tiếng Việt.
* Slug không nên chứa khoảng trắng.
* Nếu admin không nhập slug, hệ thống tự tạo từ name.
* Slug phải unique theo từng language.
* Nếu slug bị trùng, hệ thống cần báo lỗi hoặc tự tạo slug khác tùy cách implement.

---

## FR-09: SEO Fields

Mỗi translation cần hỗ trợ SEO.

Fields:

| Field            | Description |
| ---------------- | ----------- |
| Meta Title       | Tiêu đề SEO |
| Meta Description | Mô tả SEO   |

Business rules:

* Nếu meta title trống, có thể dùng category name.
* Nếu meta description trống, có thể dùng description.
* SEO fields không bắt buộc trong admin.

---

## FR-10: Category Image

Category có thể có image.

Trong task này, image có thể lưu dạng path text.

Ví dụ:

`categories/fashion.jpg`

Business rules:

* Image không bắt buộc.
* Upload image nâng cao có thể làm ở task sau.
* Nếu có image, list screen có thể hiển thị preview nhỏ.
* Nếu không có image, hiển thị placeholder hoặc để trống.

---

## FR-11: Cache Category

Danh sách category nên được cache để giảm query database.

Khi có thay đổi sau đây, cache cần được clear:

* Create category
* Update category
* Delete category
* Enable hoặc disable category
* Update translation

---

## 6. UI / Screen Design

## 6.1. Screen List

| Screen          | URL                           | Description        |
| --------------- | ----------------------------- | ------------------ |
| Category List   | `/admin/categories`           | Danh sách category |
| Create Category | `/admin/categories/create`    | Thêm category      |
| Edit Category   | `/admin/categories/{id}/edit` | Sửa category       |

---

## 6.2. Category List Screen

Màn hình danh sách cần có:

* Page title: Categories
* Button: Add Category
* Filter keyword
* Filter status
* Filter parent category nếu cần
* Table danh sách category
* Action buttons: Edit, Delete

Table columns:

| Column     | Description                        |
| ---------- | ---------------------------------- |
| ID         | Mã category                        |
| Image      | Ảnh category                       |
| Name       | Tên category theo default language |
| Parent     | Category cha                       |
| Status     | Active hoặc Inactive               |
| Sort Order | Thứ tự                             |
| Created At | Ngày tạo                           |
| Actions    | Các nút thao tác                   |

---

## 6.3. Create / Edit Form

Form nên chia thành 2 phần:

### General Information

| Label           | Field      |
| --------------- | ---------- |
| Parent Category | parent_id  |
| Image           | image      |
| Sort Order      | sort_order |
| Status          | status     |

### Translation Information

Nên hiển thị theo tab language:

* Vietnamese
* English
* Japanese

Mỗi tab gồm:

| Label            | Field            |
| ---------------- | ---------------- |
| Name             | name             |
| Slug             | slug             |
| Description      | description      |
| Meta Title       | meta_title       |
| Meta Description | meta_description |

Button:

* Save
* Back

---

## 7. Database Design

## 7.1. Table: categories

Bảng `categories` dùng để lưu thông tin kỹ thuật của category.

| Column     | Type            | Nullable | Default        | Description          |
| ---------- | --------------- | -------- | -------------- | -------------------- |
| id         | bigint unsigned | No       | auto increment | Primary key          |
| parent_id  | bigint unsigned | Yes      | null           | Category cha         |
| image      | varchar(500)    | Yes      | null           | Đường dẫn ảnh        |
| sort_order | int             | No       | 0              | Thứ tự hiển thị      |
| status     | tinyint         | No       | 1              | 1 active, 0 inactive |
| created_at | timestamp       | Yes      | null           | Created time         |
| updated_at | timestamp       | Yes      | null           | Updated time         |
| deleted_at | timestamp       | Yes      | null           | Soft delete          |

Indexes:

| Index      | Description         |
| ---------- | ------------------- |
| parent_id  | Tìm category con    |
| status     | Lọc active/inactive |
| sort_order | Sắp xếp             |

---

## 7.2. Table: category_translations

Bảng `category_translations` dùng để lưu nội dung dịch của category.

| Column           | Type            | Nullable | Default        | Description        |
| ---------------- | --------------- | -------- | -------------- | ------------------ |
| id               | bigint unsigned | No       | auto increment | Primary key        |
| category_id      | bigint unsigned | No       | null           | Liên kết category  |
| language_code    | varchar(10)     | No       | null           | vi, en, ja         |
| name             | varchar(255)    | No       | null           | Tên category       |
| slug             | varchar(255)    | No       | null           | Slug theo language |
| description      | text            | Yes      | null           | Mô tả category     |
| meta_title       | varchar(255)    | Yes      | null           | SEO title          |
| meta_description | text            | Yes      | null           | SEO description    |
| created_at       | timestamp       | Yes      | null           | Created time       |
| updated_at       | timestamp       | Yes      | null           | Updated time       |

Indexes:

| Index                              | Description                                          |
| ---------------------------------- | ---------------------------------------------------- |
| category_id                        | Tìm translation theo category                        |
| language_code                      | Tìm translation theo language                        |
| unique category_id + language_code | Một category chỉ có một translation cho một language |
| unique language_code + slug        | Slug không trùng trong cùng language                 |

---

## 7.3. Relationship

Quan hệ dữ liệu:

| Relationship                          | Description                               |
| ------------------------------------- | ----------------------------------------- |
| Category has many CategoryTranslation | Một category có nhiều bản dịch            |
| Category belongs to Parent Category   | Một category có thể có category cha       |
| Category has many Child Categories    | Một category có thể có nhiều category con |
| Category has many Products            | Một category có nhiều product ở task sau  |

---

## 8. Route Design

Các route cần có:

| Method | URL                           | Description        |
| ------ | ----------------------------- | ------------------ |
| GET    | `/admin/categories`           | Danh sách category |
| GET    | `/admin/categories/create`    | Form thêm category |
| POST   | `/admin/categories`           | Lưu category mới   |
| GET    | `/admin/categories/{id}/edit` | Form sửa category  |
| PUT    | `/admin/categories/{id}`      | Cập nhật category  |
| DELETE | `/admin/categories/{id}`      | Xóa category       |

Tất cả route trên cần được bảo vệ bởi admin authentication.

---

## 9. Validation Rules

## 9.1. General Category Validation

| Field      | Rule                           |
| ---------- | ------------------------------ |
| parent_id  | Optional, exists in categories |
| image      | Optional, max 500 characters   |
| sort_order | Optional, integer, min 0       |
| status     | Required                       |

---

## 9.2. Translation Validation

Default language translation:

| Field            | Rule                                              |
| ---------------- | ------------------------------------------------- |
| name             | Required, max 255 characters                      |
| slug             | Optional, unique per language, max 255 characters |
| description      | Optional                                          |
| meta_title       | Optional, max 255 characters                      |
| meta_description | Optional                                          |

Other language translations:

| Field            | Rule                                              |
| ---------------- | ------------------------------------------------- |
| name             | Optional, max 255 characters                      |
| slug             | Optional, unique per language, max 255 characters |
| description      | Optional                                          |
| meta_title       | Optional, max 255 characters                      |
| meta_description | Optional                                          |

Business validation:

* Category phải có translation cho default language.
* Nếu language translation có name thì slug có thể tự generate.
* Nếu slug được nhập thì phải unique theo language.
* Không cho category chọn chính nó làm parent.
* Không cho category chọn category con của nó làm parent.

---

## 10. Business Logic

## 10.1. Create Category Flow

* Admin mở màn hình tạo category.
* Hệ thống load danh sách active languages.
* Hệ thống load danh sách category có thể chọn làm parent.
* Admin nhập thông tin general.
* Admin nhập translation theo từng language.
* Hệ thống validate dữ liệu.
* Hệ thống tạo category.
* Hệ thống tạo category translations.
* Hệ thống tự generate slug nếu slug trống.
* Hệ thống clear cache category.
* Hệ thống redirect về danh sách category với thông báo thành công.

---

## 10.2. Update Category Flow

* Admin mở màn hình sửa category.
* Hệ thống load category hiện tại.
* Hệ thống load translations hiện tại.
* Hệ thống load danh sách active languages.
* Admin cập nhật thông tin.
* Hệ thống validate dữ liệu.
* Hệ thống kiểm tra parent category hợp lệ.
* Hệ thống cập nhật category.
* Hệ thống cập nhật hoặc tạo mới translation theo từng language.
* Hệ thống xóa hoặc bỏ qua translation rỗng tùy cách implement.
* Hệ thống clear cache category.
* Hệ thống redirect về danh sách category với thông báo thành công.

---

## 10.3. Delete Category Flow

* Admin click delete category.
* Hệ thống kiểm tra category có category con hay không.
* Hệ thống kiểm tra category có product hay không.
* Nếu có category con hoặc product, không cho xóa.
* Nếu không có ràng buộc, xóa category.
* Hệ thống xóa hoặc soft delete translation liên quan.
* Hệ thống clear cache category.
* Hệ thống redirect về danh sách category với thông báo phù hợp.

---

## 10.4. Parent Category Flow

* Khi tạo hoặc sửa category, admin có thể chọn parent category.
* Hệ thống không cho chọn chính nó làm parent.
* Hệ thống không cho chọn category con của nó làm parent.
* Hệ thống cho phép parent_id rỗng nếu category là cấp cao nhất.

---

## 10.5. Translation Fallback Flow

* Hệ thống cần hiển thị category theo language hiện tại nếu có translation.
* Nếu không có translation theo language hiện tại, fallback về default language.
* Nếu vẫn không có default language, fallback về translation đầu tiên có sẵn.

---

## 10.6. Slug Generation Flow

* Nếu admin nhập slug, hệ thống dùng slug đó sau khi normalize.
* Nếu admin không nhập slug, hệ thống tạo slug từ name.
* Slug cần unique theo language.
* Nếu slug trùng, hệ thống cần xử lý bằng validation error hoặc tự thêm hậu tố.
* Cách xử lý cần nhất quán trong create và update.

---

## 11. Error Handling

| Case                                  | Expected Handling       |
| ------------------------------------- | ----------------------- |
| Thiếu name default language           | Hiển thị lỗi validation |
| Slug bị trùng trong cùng language     | Hiển thị lỗi validation |
| Parent category không tồn tại         | Hiển thị lỗi validation |
| Chọn chính nó làm parent              | Không cho lưu           |
| Chọn category con làm parent          | Không cho lưu           |
| Xóa category có category con          | Không cho xóa           |
| Xóa category có product               | Không cho xóa           |
| Guest truy cập category management    | Redirect login          |
| Customer truy cập category management | Chặn truy cập           |

---

## 12. Security

Yêu cầu bảo mật:

* Chỉ admin mới được truy cập Category Management.
* Customer không được truy cập.
* Validate toàn bộ input.
* Không lưu dữ liệu request ngoài danh sách field cho phép.
* Form phải có CSRF protection.
* Không hiển thị lỗi kỹ thuật trực tiếp ra màn hình production.
* Nội dung nhập từ admin cần được escape khi hiển thị.
* Không cho tạo vòng lặp parent-child.

---

## 13. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type       | Description                                              |
| ---------- | -------------------------------------------------------- |
| Model      | Category model, Category Translation model               |
| Service    | Category service nếu cần                                 |
| Controller | Admin category controller                                |
| Request    | Validate create/update category                          |
| Seeder     | Category seeder nếu cần dữ liệu mẫu                      |
| Migration  | Tạo bảng categories và category_translations nếu chưa có |
| View       | List, create, edit, form category                        |
| Route      | Admin routes cho Category Management                     |
| Sidebar    | Cập nhật menu Categories trong admin sidebar             |

Lưu ý:

* Nếu bảng `categories` và `category_translations` đã tồn tại thì không tạo migration trùng.
* Không sửa các module không liên quan.
* Không implement Product Management trong task này.

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

`http://127.0.0.1:8000/admin/categories`

---

## 15. Test Cases

| Test Case ID | Scenario | Expected Result |
|---|---|
| TC-001 | Guest vào `/admin/categories` | Redirect login |
| TC-002 | Customer vào `/admin/categories` | Bị chặn |
| TC-003 | Admin vào `/admin/categories` | Hiển thị danh sách category |
| TC-004 | Tạo category với default language hợp lệ | Tạo thành công |
| TC-005 | Tạo category thiếu name default language | Hiển thị lỗi validation |
| TC-006 | Tạo category có translation nhiều language | Lưu đúng translations |
| TC-007 | Tạo category không nhập slug | Hệ thống tự tạo slug |
| TC-008 | Tạo category với slug trùng trong cùng language | Hiển thị lỗi validation |
| TC-009 | Sửa category hợp lệ | Cập nhật thành công |
| TC-010 | Sửa parent thành chính nó | Không cho lưu |
| TC-011 | Sửa parent thành category con | Không cho lưu |
| TC-012 | Xóa category chưa có product và category con | Xóa thành công |
| TC-013 | Xóa category có category con | Không cho xóa |
| TC-014 | Disable category | Category chuyển inactive |
| TC-015 | Hiển thị fallback translation | Hiển thị đúng language fallback |

---

## 16. Acceptance Criteria

Task này được xem là hoàn thành khi:

* [ ] Có màn hình danh sách category.
* [ ] Có màn hình thêm category.
* [ ] Có màn hình sửa category.
* [ ] Có chức năng xóa category.
* [ ] Có chức năng bật hoặc tắt category.
* [ ] Có hỗ trợ parent category.
* [ ] Có hỗ trợ translation theo active languages.
* [ ] Default language translation là bắt buộc.
* [ ] Slug unique theo từng language.
* [ ] Có tự generate slug nếu slug trống.
* [ ] Không tạo được parent-child loop.
* [ ] Không xóa được category có category con.
* [ ] Không xóa được category có product.
* [ ] Có SEO fields theo từng language.
* [ ] Admin sidebar có menu Categories.
* [ ] Customer không truy cập được Category Management.
* [ ] Chạy được migration.
* [ ] Không implement Product Management trong task này.

---

## 17. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-09-category-management-with-translation.md

Sau đó implement Task 09: Category Management with Translation theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 09.
* Không làm sang task khác.
* Không tự ý thay đổi thiết kế lớn.
* Không implement Product Management, Product Image, Public Catalog hoặc Checkout.
* Sau khi làm xong, báo cáo file đã tạo/sửa và lệnh cần chạy để test.
