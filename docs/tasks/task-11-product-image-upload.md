Task tiếp theo là:

`Task 11: Product Image Upload`

Bạn tạo file:

`docs/tasks/task-11-product-image-upload.md`

và copy nội dung dưới đây vào file.

# Task 11: Product Image Upload

## 1. Overview

Task này dùng để xây dựng chức năng quản lý hình ảnh sản phẩm cho hệ thống e-commerce.

Product Image Upload cho phép admin upload, quản lý và sắp xếp hình ảnh cho từng sản phẩm.

Mỗi sản phẩm có thể có nhiều hình ảnh:

* Ảnh chính
* Ảnh phụ
* Ảnh gallery
* Ảnh theo thứ tự hiển thị

Hình ảnh sản phẩm sẽ được dùng cho:

* Admin Product Management
* Public Product Catalog
* Product Detail Page
* Cart
* Checkout
* Order item snapshot nếu cần
* SEO image preview nếu mở rộng sau này

---

## 2. Objectives

Sau khi hoàn thành task này, hệ thống cần có:

* Có thể upload nhiều ảnh cho một product.
* Có thể xem danh sách ảnh của product.
* Có thể set ảnh chính cho product.
* Có thể xóa ảnh product.
* Có thể sắp xếp ảnh bằng sort order.
* Có thể bật hoặc tắt ảnh nếu cần.
* Có preview ảnh trong admin.
* Có validate file upload.
* Lưu ảnh vào storage phù hợp.
* Public URL của ảnh có thể hiển thị được.
* Không ảnh hưởng đến thông tin product đã làm ở Task 10.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong task này:

* Upload product image.
* Upload nhiều ảnh cho một product.
* Hiển thị danh sách ảnh trong màn hình edit product hoặc màn hình riêng.
* Set một ảnh làm ảnh chính.
* Xóa ảnh product.
* Sắp xếp ảnh bằng sort order.
* Lưu đường dẫn ảnh vào database.
* Validate file ảnh.
* Hiển thị preview ảnh.
* Tạo storage link nếu cần.
* Cập nhật Product Management để hỗ trợ quản lý ảnh.

### 3.2. Out of Scope

Các phần chưa làm trong task này:

* Resize ảnh tự động.
* Crop ảnh.
* CDN.
* Upload ảnh lên S3.
* Watermark.
* Image compression nâng cao.
* Variant image riêng.
* Drag and drop nâng cao.
* Public Product Catalog.
* Product Detail Page frontend.
* SEO image sitemap.
* AI image generation.
* Bulk image import.

---

## 4. User Roles

| Role        | Permission                                |
| ----------- | ----------------------------------------- |
| super_admin | Có toàn quyền quản lý product image       |
| admin       | Có quyền quản lý product image            |
| staff       | Có thể xem hoặc thao tác giới hạn sau này |
| customer    | Không được truy cập                       |

Chỉ admin mới được upload, xóa hoặc thay đổi ảnh sản phẩm.

Customer chỉ xem ảnh ở public site trong các task sau.

---

## 5. Functional Requirements

## FR-01: Product Image List

Admin có thể xem danh sách ảnh của product.

Có thể hiển thị trong:

`/admin/products/{id}/edit`

hoặc màn hình riêng:

`/admin/products/{id}/images`

Danh sách ảnh cần hiển thị:

| Field      | Description                    |
| ---------- | ------------------------------ |
| Preview    | Ảnh preview                    |
| File Path  | Đường dẫn ảnh                  |
| Alt Text   | Nội dung alt text              |
| Main Image | Có phải ảnh chính không        |
| Sort Order | Thứ tự hiển thị                |
| Status     | Active hoặc Inactive           |
| Actions    | Delete, Set Main, Edit nếu cần |

Expected behavior:

* Ảnh hiển thị theo sort order tăng dần.
* Ảnh chính nên được hiển thị rõ bằng badge.
* Nếu product chưa có ảnh, hiển thị trạng thái empty.
* Nếu ảnh không tồn tại trên storage, không làm hỏng màn hình admin.

---

## FR-02: Upload Product Image

Admin có thể upload ảnh cho product.

Thông tin upload:

| Field      | Required | Description             |
| ---------- | -------- | ----------------------- |
| Product    | Yes      | Product được gán ảnh    |
| Image File | Yes      | File ảnh                |
| Alt Text   | No       | Nội dung alt text       |
| Sort Order | No       | Thứ tự hiển thị         |
| Is Main    | No       | Có phải ảnh chính không |
| Status     | Yes      | Active hoặc Inactive    |

Expected behavior:

* Chỉ cho phép file ảnh hợp lệ.
* Không cho upload file nguy hiểm.
* File upload được lưu trong storage.
* Database lưu đường dẫn ảnh.
* Nếu ảnh upload là ảnh đầu tiên của product, có thể tự set làm ảnh chính.
* Nếu admin chọn ảnh mới là main image, các ảnh khác của product phải bỏ main.
* Sau khi upload thành công, quay lại màn hình quản lý ảnh hoặc edit product.

---

## FR-03: Upload Multiple Images

Admin có thể upload nhiều ảnh cùng lúc.

Expected behavior:

* Hệ thống xử lý từng ảnh hợp lệ.
* Nếu một ảnh lỗi validation, hệ thống cần báo lỗi rõ ràng.
* Không nên làm mất các ảnh đã tồn tại.
* Có thể set sort order tự động theo thứ tự upload.
* Nếu product chưa có main image, ảnh đầu tiên có thể được set làm main.

---

## FR-04: Set Main Image

Admin có thể chọn một ảnh làm ảnh chính của product.

Business rules:

* Mỗi product chỉ có một main image.
* Khi một ảnh được set main, các ảnh khác của product không còn là main.
* Ảnh main nên là ảnh active.
* Nếu ảnh inactive, không nên cho set làm main.
* Nếu xóa ảnh main, hệ thống có thể tự chọn ảnh active đầu tiên làm main hoặc để product không có main image.

---

## FR-05: Delete Product Image

Admin có thể xóa ảnh khỏi product.

Business rules:

* Khi xóa ảnh, cần xóa record database.
* Nếu có thể, cần xóa file vật lý trong storage.
* Nếu file vật lý không tồn tại, vẫn có thể xóa record database.
* Nếu ảnh bị xóa là main image, cần xử lý main image còn lại hợp lý.
* Không xóa product khi xóa image.

---

## FR-06: Update Image Information

Admin có thể cập nhật thông tin ảnh.

Có thể cập nhật:

* Alt text
* Sort order
* Status
* Main image flag

Expected behavior:

* Alt text có thể rỗng.
* Sort order phải là số hợp lệ.
* Status active/inactive.
* Nếu set ảnh làm main thì ảnh đó phải active.

---

## FR-07: Image Preview

Admin cần xem được preview ảnh.

Expected behavior:

* Ảnh preview hiển thị trong danh sách image.
* Preview nên có kích thước nhỏ, không làm vỡ layout.
* Nếu ảnh lỗi hoặc không tồn tại, hiển thị placeholder hoặc text báo lỗi.
* Không để ảnh quá lớn làm chậm admin page.

---

## FR-08: File Validation

File upload cần được validate.

Yêu cầu:

| Rule      | Description                                         |
| --------- | --------------------------------------------------- |
| File type | Chỉ cho phép ảnh                                    |
| Extension | jpg, jpeg, png, webp                                |
| File size | Giới hạn dung lượng hợp lý                          |
| File name | Không dùng trực tiếp tên file gốc nếu không an toàn |

Business rules:

* Không cho upload PHP, JS, HTML hoặc file nguy hiểm.
* Không tin tưởng MIME type từ client.
* Tên file nên được normalize hoặc tạo mới để tránh trùng.
* Nếu upload thất bại, hiển thị lỗi rõ ràng.

---

## FR-09: Storage

Ảnh sản phẩm cần được lưu vào storage phù hợp.

Đề xuất lưu trong khu vực public storage của Laravel.

Ví dụ path logic:

| Type           | Example                              |
| -------------- | ------------------------------------ |
| Storage folder | product-images                       |
| Public path    | storage/product-images/file-name.jpg |

Business rules:

* File phải có thể truy cập từ browser để hiển thị preview.
* Cần đảm bảo storage link hoạt động.
* Không lưu ảnh trực tiếp vào source code nếu không cần.
* Không commit file upload vào git.

---

## FR-10: Product Integration

Task này cần tích hợp với Product Management đã làm ở Task 10.

Expected behavior:

* Trong màn hình edit product, admin có thể thấy khu vực Product Images.
* Admin có thể upload ảnh cho product hiện tại.
* Admin có thể xóa ảnh.
* Admin có thể set ảnh chính.
* Product list có thể hiển thị ảnh chính nếu thuận tiện.
* Không làm thay đổi logic tạo/sửa product translation.

---

## FR-11: Main Image Fallback

Khi cần hiển thị ảnh chính của product, hệ thống cần có fallback.

Logic mong muốn:

* Nếu product có main image active, dùng ảnh đó.
* Nếu không có main image, dùng ảnh active đầu tiên theo sort order.
* Nếu không có ảnh active, hiển thị placeholder hoặc để trống.

---

## FR-12: Cache Product Image

Nếu hệ thống có cache product hoặc product image, cache cần được clear khi:

* Upload image
* Delete image
* Set main image
* Update image information
* Change image status
* Change sort order

---

## 6. UI / Screen Design

## 6.1. Screen List

| Screen               | URL                               | Description                 |
| -------------------- | --------------------------------- | --------------------------- |
| Product Edit         | `/admin/products/{id}/edit`       | Có khu vực quản lý image    |
| Product Image List   | `/admin/products/{id}/images`     | Màn hình riêng nếu cần      |
| Upload Product Image | `/admin/products/{id}/images`     | Upload ảnh cho product      |
| Edit Product Image   | `/admin/product-images/{id}/edit` | Sửa thông tin image nếu cần |

Có thể chọn cách đơn giản:

* Quản lý ảnh trực tiếp trong màn hình edit product.
* Không bắt buộc tạo màn hình image riêng nếu chưa cần.

---

## 6.2. Product Images Section

Trong màn hình edit product, thêm section:

`Product Images`

Section này cần có:

* Upload input cho một hoặc nhiều ảnh.
* Button upload.
* Danh sách ảnh hiện tại.
* Preview ảnh.
* Badge main image.
* Input sort order nếu cần.
* Button Set Main.
* Button Delete.
* Trạng thái active/inactive nếu có.

---

## 6.3. Product Image Table

Table hoặc grid hiển thị:

| Column     | Description          |
| ---------- | -------------------- |
| Preview    | Ảnh nhỏ              |
| Alt Text   | Nội dung alt         |
| Main       | Badge ảnh chính      |
| Sort Order | Thứ tự               |
| Status     | Active hoặc Inactive |
| Actions    | Set Main, Delete     |

Nếu dùng grid:

* Ảnh hiển thị dạng card.
* Mỗi card có preview, alt text, sort order, actions.

---

## 6.4. Upload Form

Form upload gồm:

| Label       | Field      |
| ----------- | ---------- |
| Images      | images     |
| Alt Text    | alt_text   |
| Sort Order  | sort_order |
| Set as Main | is_main    |
| Status      | status     |

Nếu upload nhiều ảnh cùng lúc:

* Alt text có thể để trống.
* Sort order có thể tự tăng.
* Is main chỉ áp dụng nếu upload một ảnh hoặc áp dụng cho ảnh đầu tiên.

---

## 7. Database Design

## 7.1. Table: product_images

Bảng `product_images` dùng để lưu hình ảnh sản phẩm.

| Column     | Type            | Nullable | Default        | Description            |
| ---------- | --------------- | -------- | -------------- | ---------------------- |
| id         | bigint unsigned | No       | auto increment | Primary key            |
| product_id | bigint unsigned | No       | null           | Product                |
| image_path | varchar(500)    | No       | null           | Đường dẫn ảnh          |
| alt_text   | varchar(255)    | Yes      | null           | Alt text               |
| is_main    | tinyint         | No       | 0              | 1 main image, 0 normal |
| sort_order | int             | No       | 0              | Thứ tự hiển thị        |
| status     | tinyint         | No       | 1              | 1 active, 0 inactive   |
| created_at | timestamp       | Yes      | null           | Created time           |
| updated_at | timestamp       | Yes      | null           | Updated time           |

Indexes:

| Index      | Description          |
| ---------- | -------------------- |
| product_id | Tìm ảnh theo product |
| is_main    | Tìm ảnh chính        |
| status     | Lọc ảnh active       |
| sort_order | Sắp xếp ảnh          |

---

## 7.2. Relationship

Quan hệ dữ liệu:

| Relationship                     | Description               |
| -------------------------------- | ------------------------- |
| Product has many Product Images  | Một product có nhiều ảnh  |
| Product Image belongs to Product | Một ảnh thuộc một product |

---

## 8. Route Design

Các route có thể cần:

| Method | URL                                   | Description                                   |
| ------ | ------------------------------------- | --------------------------------------------- |
| GET    | `/admin/products/{id}/images`         | Danh sách ảnh product nếu dùng màn hình riêng |
| POST   | `/admin/products/{id}/images`         | Upload ảnh product                            |
| PUT    | `/admin/product-images/{id}`          | Cập nhật thông tin ảnh                        |
| PUT    | `/admin/product-images/{id}/set-main` | Set ảnh chính                                 |
| DELETE | `/admin/product-images/{id}`          | Xóa ảnh                                       |

Tất cả route trên cần được bảo vệ bởi admin authentication.

Nếu quản lý ảnh trực tiếp trong màn hình edit product, vẫn cần route xử lý upload, set main và delete.

---

## 9. Validation Rules

## 9.1. Upload Image Validation

| Field      | Rule                             |
| ---------- | -------------------------------- |
| product_id | Required, product must exist     |
| images     | Required                         |
| image file | Must be valid image              |
| extension  | jpg, jpeg, png, webp             |
| file size  | Must not exceed configured limit |
| alt_text   | Optional, max 255 characters     |
| sort_order | Optional, integer, min 0         |
| is_main    | Optional                         |
| status     | Required                         |

---

## 9.2. Update Image Validation

| Field      | Rule                         |
| ---------- | ---------------------------- |
| alt_text   | Optional, max 255 characters |
| sort_order | Optional, integer, min 0     |
| is_main    | Optional                     |
| status     | Required                     |

Business validation:

* Không set inactive image làm main image.
* Product image phải thuộc về product hợp lệ.
* Không thao tác image không tồn tại.

---

## 10. Business Logic

## 10.1. Upload Image Flow

* Admin mở màn hình edit product.
* Admin chọn một hoặc nhiều ảnh.
* Hệ thống validate file upload.
* Hệ thống lưu file vào storage.
* Hệ thống tạo record product image.
* Nếu product chưa có main image, ảnh đầu tiên được set main.
* Nếu admin chọn set main, ảnh được upload sẽ thành main image.
* Hệ thống clear cache product image nếu có.
* Hệ thống redirect về màn hình edit product với thông báo thành công.

---

## 10.2. Set Main Image Flow

* Admin click Set Main trên một ảnh.
* Hệ thống kiểm tra ảnh có tồn tại không.
* Hệ thống kiểm tra ảnh có active không.
* Hệ thống bỏ main flag khỏi các ảnh khác của product.
* Hệ thống set ảnh hiện tại làm main.
* Hệ thống clear cache product image nếu có.
* Hệ thống redirect back với thông báo thành công.

---

## 10.3. Delete Image Flow

* Admin click Delete image.
* Hệ thống kiểm tra ảnh có tồn tại không.
* Hệ thống xóa file vật lý nếu có thể.
* Hệ thống xóa record image.
* Nếu ảnh bị xóa là main image, hệ thống xử lý fallback main image.
* Hệ thống clear cache product image nếu có.
* Hệ thống redirect back với thông báo phù hợp.

---

## 10.4. Update Image Flow

* Admin cập nhật alt text, sort order hoặc status.
* Hệ thống validate dữ liệu.
* Nếu ảnh được set main thì ảnh phải active.
* Hệ thống cập nhật image record.
* Nếu set main, các ảnh khác của product bị bỏ main.
* Hệ thống clear cache product image nếu có.
* Hệ thống redirect back với thông báo thành công.

---

## 10.5. Main Image Fallback Flow

* Hệ thống kiểm tra product có main image active hay không.
* Nếu có, trả về main image.
* Nếu không có, lấy ảnh active đầu tiên theo sort order.
* Nếu không có ảnh active, trả về placeholder hoặc null.

---

## 11. Error Handling

| Case                                       | Expected Handling              |
| ------------------------------------------ | ------------------------------ |
| Upload file không phải ảnh                 | Hiển thị lỗi validation        |
| Upload file quá lớn                        | Hiển thị lỗi validation        |
| Product không tồn tại                      | Hiển thị lỗi phù hợp           |
| Image không tồn tại                        | Hiển thị lỗi phù hợp           |
| Set inactive image làm main                | Không cho set, hiển thị lỗi    |
| Xóa image không tồn tại file vật lý        | Vẫn có thể xóa record database |
| Storage chưa link public                   | Hướng dẫn chạy storage link    |
| Guest truy cập product image management    | Redirect login                 |
| Customer truy cập product image management | Chặn truy cập                  |

---

## 12. Security

Yêu cầu bảo mật:

* Chỉ admin mới được upload hoặc xóa product image.
* Customer không được truy cập route quản lý image.
* Validate toàn bộ file upload.
* Không cho upload file nguy hiểm.
* Không dùng trực tiếp tên file gốc nếu không an toàn.
* Không hiển thị lỗi kỹ thuật trực tiếp ra màn hình production.
* Form upload phải có CSRF protection.
* File upload phải lưu đúng thư mục cho phép.
* Không cho path traversal.
* Không ghi đè file không liên quan.

---

## 13. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type         | Description                                       |
| ------------ | ------------------------------------------------- |
| Model        | Product Image model                               |
| Controller   | Admin product image controller                    |
| Request      | Validate upload/update product image              |
| Service      | Product image service nếu cần                     |
| Migration    | Tạo bảng product_images nếu chưa có               |
| View         | Product image section trong màn hình edit product |
| Route        | Admin routes cho product image                    |
| Storage      | Cấu hình public storage nếu cần                   |
| Product View | Cập nhật màn hình product edit/list nếu cần       |

Lưu ý:

* Nếu bảng `product_images` đã tồn tại thì không tạo migration trùng.
* Không sửa các module không liên quan.
* Không implement Public Product Catalog trong task này.
* Không implement Product Detail Page trong task này.
* Không implement Inventory Management trong task này.

---

## 14. Commands

Sau khi Codex implement xong, chạy các lệnh kiểm tra sau:

| Command                  | Purpose                              |
| ------------------------ | ------------------------------------ |
| php artisan migrate      | Chạy migration                       |
| php artisan storage:link | Tạo public storage link nếu chưa có  |
| php artisan route:list   | Kiểm tra route                       |
| php artisan serve        | Chạy local server                    |
| npm run build            | Build frontend nếu có thay đổi asset |

URL test:

`http://127.0.0.1:8000/admin/products`

Vào màn hình edit product bất kỳ để kiểm tra upload image.

---

## 15. Test Cases

| Test Case ID | Scenario                                 | Expected Result                             |
| ------------ | ---------------------------------------- | ------------------------------------------- |
| TC-001       | Guest upload product image               | Redirect login                              |
| TC-002       | Customer upload product image            | Bị chặn                                     |
| TC-003       | Admin mở edit product                    | Thấy khu vực Product Images                 |
| TC-004       | Upload ảnh hợp lệ                        | Upload thành công và hiển thị preview       |
| TC-005       | Upload nhiều ảnh hợp lệ                  | Tất cả ảnh hợp lệ được lưu                  |
| TC-006       | Upload file không phải ảnh               | Hiển thị lỗi validation                     |
| TC-007       | Upload file quá lớn                      | Hiển thị lỗi validation                     |
| TC-008       | Product chưa có ảnh, upload ảnh đầu tiên | Ảnh đầu tiên được set main                  |
| TC-009       | Set ảnh khác làm main                    | Chỉ ảnh đó là main                          |
| TC-010       | Set inactive image làm main              | Không cho set                               |
| TC-011       | Xóa ảnh thường                           | Xóa thành công                              |
| TC-012       | Xóa ảnh main                             | Xóa thành công và xử lý fallback main image |
| TC-013       | Cập nhật alt text                        | Alt text được lưu                           |
| TC-014       | Cập nhật sort order                      | Thứ tự hiển thị thay đổi                    |
| TC-015       | Ảnh không tồn tại trên storage           | Admin page không bị lỗi trắng               |

---

## 16. Acceptance Criteria

Task này được xem là hoàn thành khi:

* [ ] Có thể upload ảnh cho product.
* [ ] Có thể upload nhiều ảnh cho product.
* [ ] Có thể xem preview ảnh trong admin.
* [ ] Có thể set một ảnh làm main image.
* [ ] Chỉ có một main image cho một product.
* [ ] Có thể xóa ảnh product.
* [ ] Có thể cập nhật alt text.
* [ ] Có thể cập nhật sort order.
* [ ] Có validate file upload.
* [ ] Không upload được file nguy hiểm.
* [ ] File được lưu trong storage phù hợp.
* [ ] Ảnh hiển thị được qua public URL.
* [ ] Product edit screen có khu vực quản lý image.
* [ ] Customer không truy cập được route quản lý image.
* [ ] Chạy được migration.
* [ ] Không implement Public Product Catalog trong task này.
* [ ] Không implement Product Detail Page trong task này.
* [ ] Không implement Inventory Management trong task này.

---

## 17. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-10-product-management-with-translation.md
* docs/tasks/task-11-product-image-upload.md

Sau đó implement Task 11: Product Image Upload theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 11.
* Không làm sang task khác.
* Không tự ý thay đổi thiết kế lớn.
* Không implement Public Product Catalog, Product Detail Page, Cart, Checkout, Order hoặc Inventory Management.
* Sau khi làm xong, báo cáo file đã tạo/sửa và lệnh cần chạy để test.
