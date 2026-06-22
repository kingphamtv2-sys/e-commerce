# Task 10.2: Variant Images

## 1. Overview

Task này dùng để bổ sung chức năng quản lý ảnh riêng cho từng product variant.

Sau Task 10.1, hệ thống đã hỗ trợ product options và variant combinations như:

* Color / Size cho quần áo
* Color / Storage cho điện thoại
* RAM / Storage / Color cho laptop

Task 10.2 sẽ giúp mỗi variant có ảnh riêng.

Ví dụ:

| Product     | Variant       | Image               |
| ----------- | ------------- | ------------------- |
| Áo thun nam | Black / M     | Ảnh áo màu đen      |
| Áo thun nam | White / M     | Ảnh áo màu trắng    |
| iPhone 15   | Black / 128GB | Ảnh iPhone màu đen  |
| iPhone 15   | Blue / 256GB  | Ảnh iPhone màu xanh |

Điều này giúp Product Detail Page hiển thị đúng ảnh khi customer chọn variant.

---

## 2. Objectives

Sau khi hoàn thành task này, hệ thống cần có:

* Admin có thể upload ảnh cho từng variant.
* Mỗi variant có thể có nhiều ảnh.
* Mỗi variant có một ảnh chính.
* Admin có thể set main image cho variant.
* Admin có thể xóa ảnh variant.
* Admin có thể cập nhật alt text cho ảnh variant.
* Admin có thể sắp xếp thứ tự ảnh variant.
* Product Detail Page có thể đổi ảnh theo variant được chọn.
* Product Catalog có thể fallback ảnh hợp lý.
* Cart và Order sau này có thể dùng ảnh variant đã chọn.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong task này:

* Tạo bảng lưu ảnh variant.
* Tạo chức năng upload ảnh cho variant.
* Hiển thị ảnh variant trong màn hình edit product.
* Hiển thị ảnh variant trong section Variant Combinations.
* Cho phép upload nhiều ảnh cho một variant.
* Cho phép set một ảnh làm main image của variant.
* Cho phép xóa ảnh variant.
* Cho phép cập nhật alt text.
* Cho phép cập nhật sort order.
* Cho phép bật/tắt ảnh variant nếu cần.
* Validate file upload.
* Lưu ảnh trong Laravel public storage.
* Fallback ảnh nếu variant không có ảnh.
* Chuẩn bị dữ liệu để Product Detail Page đổi gallery theo variant.
* Cập nhật nhẹ Product Detail Page nếu đã có Task 14.

### 3.2. Out of Scope

Các phần chưa làm trong task này:

* Variant image theo option value.
* Ảnh riêng theo color value dùng chung cho nhiều variant.
* Crop image.
* Resize image nâng cao.
* Image compression nâng cao.
* CDN.
* S3 upload.
* Watermark.
* Drag and drop advanced gallery.
* Bulk upload variant images.
* Import/export variant image bằng Excel.
* AI image generation.
* Cart logic.
* Checkout logic.
* Order creation logic.
* Review image.
* Customer upload image.

---

## 4. Why This Task Is Needed

Product image ở Task 11 chỉ quản lý ảnh chung của product.

Nhưng với sản phẩm có variant, ảnh chung là chưa đủ.

Ví dụ quần áo:

| Customer chọn | Ảnh nên hiển thị |
| ------------- | ---------------- |
| Black / M     | Áo màu đen       |
| White / M     | Áo màu trắng     |

Ví dụ điện thoại:

| Customer chọn | Ảnh nên hiển thị    |
| ------------- | ------------------- |
| Black / 128GB | Điện thoại màu đen  |
| Blue / 256GB  | Điện thoại màu xanh |

Nếu không có ảnh variant riêng, customer có thể chọn màu xanh nhưng vẫn thấy ảnh màu đen, gây trải nghiệm không tốt.

---

## 5. Design Decision

Trong MVP nâng cao, hệ thống sử dụng thiết kế:

`Variant has many images`

Tức là:

* Mỗi product variant có thể có nhiều ảnh.
* Mỗi product variant có một ảnh chính.
* Nếu variant không có ảnh, fallback về product image.
* Nếu product cũng không có ảnh, fallback về placeholder.

Không hard-code ảnh theo Color, Size, Storage.

Lý do:

* Dễ triển khai.
* Dễ hiểu.
* Phù hợp với quần áo và đồ điện tử.
* Dễ dùng cho Product Detail, Cart và Order sau này.
* Không làm hệ thống variant quá phức tạp ở MVP.

---

## 6. User Roles

| Role        | Permission                                |
| ----------- | ----------------------------------------- |
| super_admin | Có toàn quyền quản lý variant images      |
| admin       | Có quyền quản lý variant images           |
| staff       | Có thể xem hoặc thao tác giới hạn sau này |
| customer    | Không được truy cập admin upload          |

Customer chỉ xem ảnh variant ngoài frontend.

Customer không được upload hoặc chỉnh sửa ảnh variant.

---

## 7. Functional Requirements

## FR-01: Variant Image Management

Admin có thể quản lý ảnh cho từng variant trong màn hình edit product.

Trong product edit, tại section Variant Combinations, mỗi variant cần có khu vực quản lý ảnh.

Thông tin cần hiển thị:

| Field              | Description                    |
| ------------------ | ------------------------------ |
| Variant Name       | Tên variant, ví dụ Black / M   |
| SKU                | SKU của variant                |
| Current Main Image | Ảnh chính hiện tại của variant |
| Image Count        | Số ảnh của variant             |
| Actions            | Manage Images                  |

Expected behavior:

* Admin có thể mở màn hình hoặc modal quản lý ảnh variant.
* Nếu variant chưa có ảnh, hiển thị empty state.
* Nếu variant có ảnh, hiển thị thumbnail.
* Không làm rối màn hình edit product.

---

## FR-02: Upload Variant Images

Admin có thể upload một hoặc nhiều ảnh cho một variant.

Thông tin upload:

| Field      | Required | Description          |
| ---------- | -------- | -------------------- |
| Images     | Yes      | File ảnh             |
| Alt Text   | No       | Nội dung alt text    |
| Sort Order | No       | Thứ tự hiển thị      |
| Status     | No       | Active hoặc inactive |

Validation:

| Rule                    | Description                                                                           |
| ----------------------- | ------------------------------------------------------------------------------------- |
| File type               | Chỉ cho jpg, jpeg, png, webp                                                          |
| File size               | Giới hạn dung lượng hợp lý                                                            |
| File required           | Bắt buộc khi upload                                                                   |
| Variant exists          | Variant phải tồn tại                                                                  |
| Variant active/inactive | Có thể upload cho variant inactive nhưng không hiển thị frontend nếu variant inactive |

Expected behavior:

* Upload thành công thì ảnh xuất hiện trong danh sách.
* Ảnh đầu tiên có thể tự động làm main image nếu variant chưa có main image.
* File không hợp lệ thì hiển thị lỗi validation.
* Không upload file nguy hiểm.
* Không làm mất ảnh cũ khi upload ảnh mới.

---

## FR-03: Set Main Variant Image

Admin có thể set một ảnh làm ảnh chính của variant.

Business rules:

* Mỗi variant chỉ có một main image.
* Khi set một ảnh làm main, các ảnh khác của cùng variant phải không còn là main.
* Nếu xóa main image, hệ thống có thể tự chọn ảnh active đầu tiên làm main hoặc để rỗng.
* Main image dùng để hiển thị ngoài frontend và cart sau này.

---

## FR-04: Delete Variant Image

Admin có thể xóa ảnh variant.

Business rules:

* Xóa ảnh chỉ xóa ảnh của variant đó.
* Không xóa product image chung.
* Nếu ảnh đang là main image, sau khi xóa cần xử lý fallback.
* Không làm lỗi nếu file vật lý không tồn tại.
* Cần tránh xóa nhầm ảnh không thuộc variant hiện tại.

---

## FR-05: Update Variant Image Info

Admin có thể cập nhật thông tin ảnh variant.

Thông tin có thể cập nhật:

| Field      | Description          |
| ---------- | -------------------- |
| Alt Text   | Text mô tả ảnh       |
| Sort Order | Thứ tự hiển thị      |
| Status     | Active hoặc inactive |
| Is Main    | Ảnh chính            |

Expected behavior:

* Alt text dùng cho SEO và accessibility.
* Sort order dùng để sắp xếp gallery.
* Inactive image không hiển thị ngoài frontend.
* Main image nên là image active.

---

## FR-06: Variant Image Gallery

Mỗi variant có thể có gallery riêng.

Ví dụ:

| Variant   | Images                          |
| --------- | ------------------------------- |
| Black / M | black-front.jpg, black-back.jpg |
| White / M | white-front.jpg, white-back.jpg |

Expected behavior:

* Gallery hiển thị theo sort order.
* Chỉ image active mới hiển thị ngoài frontend.
* Admin có thể xem toàn bộ ảnh, bao gồm inactive nếu cần.
* Frontend ưu tiên gallery của variant được chọn.

---

## FR-07: Product Detail Integration

Product Detail Page cần có khả năng đổi ảnh khi customer chọn variant.

Expected behavior:

* Khi customer chưa chọn variant, hiển thị product images chung.
* Khi customer chọn variant có ảnh riêng, đổi main image và gallery sang ảnh của variant.
* Khi customer chọn variant không có ảnh riêng, giữ product images chung.
* Nếu product cũng không có ảnh, hiển thị placeholder.
* Nếu dùng Alpine.js, có thể đổi ảnh ngay trên frontend mà không reload page.
* Không cần API riêng nếu dữ liệu variant images đã được render sẵn.

Task này có thể cập nhật nhẹ Task 14 nếu Task 14 đã được implement.

---

## FR-08: Product Catalog Fallback

Product Catalog ở Task 13 có thể tiếp tục dùng product main image.

Nếu product không có product image nhưng có variant image, có thể fallback về ảnh main của variant active đầu tiên.

Priority gợi ý:

1. Product main image active
2. Product first active image
3. First active variant main image
4. First active variant image
5. Placeholder image

Trong task này chỉ cần fallback an toàn, không cần logic quá phức tạp.

---

## FR-09: Cart Compatibility

Task 15 Cart sau này cần hiển thị ảnh đúng variant customer đã chọn.

Task này chưa implement cart, nhưng dữ liệu cần hỗ trợ:

* Lấy main image của selected variant.
* Nếu variant không có image, fallback product image.
* Nếu không có product image, fallback placeholder.

---

## FR-10: Order Snapshot Compatibility

Task 19 Order Creation sau này cần lưu hoặc hiển thị ảnh liên quan đến variant.

Task này chưa implement order, nhưng cần đảm bảo:

* Variant image có thể lấy được từ variant.
* Order item sau này có thể hiển thị ảnh variant.
* Không phụ thuộc vào product image chung nếu customer đã chọn variant có ảnh riêng.

---

## FR-11: Image Fallback Rules

Hệ thống cần có fallback rõ ràng.

Khi hiển thị ảnh variant:

| Priority | Image Source               |
| -------- | -------------------------- |
| 1        | Variant main image active  |
| 2        | Variant first active image |
| 3        | Product main image active  |
| 4        | Product first active image |
| 5        | Placeholder image          |

Business rules:

* Không để giao diện bị lỗi nếu thiếu ảnh.
* Không để ảnh inactive hiển thị ngoài frontend.
* Không để ảnh của variant khác hiển thị nhầm.
* Placeholder cần nhìn chuyên nghiệp.

---

## FR-12: Image Storage

Ảnh variant cần được lưu trong Laravel public storage.

Yêu cầu:

* Lưu file trong thư mục riêng cho variant images.
* Đường dẫn lưu trong database.
* Có thể truy cập ảnh qua public URL.
* Không lưu file trực tiếp vào database.
* Không lưu file trong thư mục không public nếu frontend cần hiển thị.
* Cần dùng storage link nếu project chưa có.

---

## 8. UI / Screen Design

## 8.1. Admin Product Edit Integration

Trong product edit screen, tại phần Variant Combinations, thêm thông tin ảnh.

Variant table có thể hiển thị:

| Column      | Description          |
| ----------- | -------------------- |
| Variant     | Black / M            |
| SKU         | TSHIRT-BLACK-M       |
| Main Image  | Thumbnail ảnh chính  |
| Image Count | Số ảnh               |
| Price       | Giá                  |
| Stock       | Tồn kho nếu có       |
| Status      | Active hoặc inactive |
| Actions     | Edit, Images         |

Expected behavior:

* Thumbnail nhỏ, gọn.
* Nếu chưa có ảnh, hiển thị placeholder nhỏ.
* Có button Manage Images cho từng variant.
* Không làm table quá dài hoặc vỡ layout.

---

## 8.2. Manage Variant Images Screen

Có thể tạo màn hình riêng:

`/admin/product-variants/{variant}/images`

Hoặc dùng modal trong product edit.

MVP ưu tiên màn hình riêng để dễ làm.

Màn hình cần có:

| Section      | Description                     |
| ------------ | ------------------------------- |
| Variant Info | Product name, variant name, SKU |
| Upload Form  | Upload image                    |
| Image List   | Danh sách ảnh variant           |
| Actions      | Set main, edit, delete          |

Image list hiển thị:

| Column     | Description             |
| ---------- | ----------------------- |
| Thumbnail  | Ảnh nhỏ                 |
| Alt Text   | Alt text                |
| Is Main    | Có phải ảnh chính không |
| Sort Order | Thứ tự                  |
| Status     | Active/inactive         |
| Actions    | Set main, edit, delete  |

---

## 8.3. Upload Form UI

Upload form gồm:

| Field      | Description             |
| ---------- | ----------------------- |
| Images     | Chọn một hoặc nhiều ảnh |
| Alt Text   | Text mô tả              |
| Sort Order | Thứ tự                  |
| Status     | Active/inactive         |

Yêu cầu UI:

* Form rõ ràng, dễ dùng.
* Có preview sau khi upload hoặc sau khi lưu.
* Có thông báo lỗi validation rõ ràng.
* Có nút quay lại product edit.
* Không làm layout admin bị vỡ.

---

## 8.4. Product Detail UI Impact

Khi customer chọn variant ở Product Detail:

* Main image đổi theo variant nếu có ảnh riêng.
* Thumbnail gallery đổi theo variant nếu có ảnh riêng.
* Nếu variant không có ảnh, giữ gallery product.
* UI không bị nhảy layout.
* Ảnh variant có alt text phù hợp.

---

## 9. Database Design

## 9.1. Table: variant_images

Bảng `variant_images` dùng để lưu ảnh riêng cho từng product variant.

| Column             | Type            | Nullable | Default        | Description          |
| ------------------ | --------------- | -------- | -------------- | -------------------- |
| id                 | bigint unsigned | No       | auto increment | Primary key          |
| product_variant_id | bigint unsigned | No       | null           | Variant              |
| image_path         | varchar(255)    | No       | null           | Đường dẫn ảnh        |
| alt_text           | varchar(255)    | Yes      | null           | Alt text             |
| is_main            | tinyint         | No       | 0              | 1 là ảnh chính       |
| sort_order         | int             | No       | 0              | Thứ tự hiển thị      |
| status             | tinyint         | No       | 1              | 1 active, 0 inactive |
| created_at         | timestamp       | Yes      | null           | Created time         |
| updated_at         | timestamp       | Yes      | null           | Updated time         |

Indexes:

| Index                           | Description          |
| ------------------------------- | -------------------- |
| product_variant_id              | Tìm ảnh theo variant |
| product_variant_id + is_main    | Tìm main image       |
| product_variant_id + sort_order | Sắp xếp gallery      |
| status                          | Lọc active/inactive  |

Business rules:

* Một variant có thể có nhiều ảnh.
* Một variant chỉ có một main image.
* Ảnh inactive không hiển thị ngoài frontend.
* Không tạo ảnh không thuộc variant.

---

## 9.2. Relationship

Quan hệ dữ liệu:

| Relationship                             | Description                      |
| ---------------------------------------- | -------------------------------- |
| Product Variant has many Variant Images  | Một variant có nhiều ảnh         |
| Variant Image belongs to Product Variant | Ảnh thuộc một variant            |
| Product has many Product Images          | Ảnh chung của product từ Task 11 |
| Product Variant belongs to Product       | Variant thuộc product            |

---

## 9.3. Image Priority

Khi hiển thị ảnh, priority cần như sau:

| Context                          | Priority                                                                                            |
| -------------------------------- | --------------------------------------------------------------------------------------------------- |
| Product Catalog                  | Product main image → Product first image → Variant main image → Placeholder                         |
| Product Detail chưa chọn variant | Product main image → Product first image → Placeholder                                              |
| Product Detail đã chọn variant   | Variant main image → Variant first image → Product main image → Placeholder                         |
| Cart sau này                     | Selected variant main image → Product main image → Placeholder                                      |
| Order sau này                    | Selected variant image snapshot hoặc selected variant main image → Product main image → Placeholder |

---

## 10. Route Design

Các route admin có thể cần:

| Method | URL                                        | Description            |
| ------ | ------------------------------------------ | ---------------------- |
| GET    | `/admin/product-variants/{variant}/images` | Danh sách ảnh variant  |
| POST   | `/admin/product-variants/{variant}/images` | Upload ảnh variant     |
| PUT    | `/admin/variant-images/{image}`            | Cập nhật thông tin ảnh |
| POST   | `/admin/variant-images/{image}/set-main`   | Set ảnh chính          |
| DELETE | `/admin/variant-images/{image}`            | Xóa ảnh variant        |

Tất cả route trên cần được bảo vệ bởi admin authentication.

---

## 11. Validation Rules

## 11.1. Upload Variant Image Validation

| Field      | Rule                             |
| ---------- | -------------------------------- |
| images     | Required                         |
| images.*   | Image file, jpg, jpeg, png, webp |
| alt_text   | Optional, max 255 characters     |
| sort_order | Optional, integer, min 0         |
| status     | Optional                         |

Business validation:

* Variant phải tồn tại.
* File upload phải là ảnh hợp lệ.
* Không cho upload file nguy hiểm.
* Không cho upload nếu không có quyền admin.
* Nếu upload nhiều ảnh, từng ảnh phải validate riêng.

---

## 11.2. Update Variant Image Validation

| Field      | Rule                          |
| ---------- | ----------------------------- |
| alt_text   | Optional, max 255 characters  |
| sort_order | Optional, integer, min 0      |
| status     | Required hoặc optional tùy UI |
| is_main    | Optional                      |

Business validation:

* Image phải tồn tại.
* Image phải thuộc variant hợp lệ.
* Nếu set main image, chỉ một image của variant được main.
* Main image nên là active image.

---

## 12. Business Logic

## 12.1. Upload Variant Image Flow

* Admin mở màn hình quản lý ảnh variant.
* Admin chọn ảnh upload.
* Hệ thống validate file.
* Hệ thống lưu file vào storage.
* Hệ thống tạo record variant image.
* Nếu variant chưa có main image, ảnh đầu tiên được set main.
* Hệ thống redirect back với thông báo thành công.

---

## 12.2. Set Main Image Flow

* Admin chọn một ảnh variant.
* Hệ thống kiểm tra ảnh thuộc variant.
* Hệ thống bỏ main image cũ của variant.
* Hệ thống set ảnh được chọn làm main image.
* Hệ thống redirect back với thông báo thành công.

---

## 12.3. Delete Variant Image Flow

* Admin chọn xóa ảnh.
* Hệ thống kiểm tra ảnh thuộc variant.
* Hệ thống xóa record ảnh.
* Hệ thống xóa file vật lý nếu phù hợp.
* Nếu ảnh bị xóa là main image, hệ thống chọn ảnh active tiếp theo làm main nếu có.
* Hệ thống redirect back với thông báo thành công.

---

## 12.4. Product Detail Variant Image Flow

* Customer mở product detail.
* Hệ thống load product images.
* Hệ thống load variants.
* Hệ thống load variant images.
* Khi customer chọn variant:

  * Nếu variant có image active, đổi gallery sang variant images.
  * Nếu variant không có image active, dùng product images.
  * Nếu không có product images, dùng placeholder.

---

## 12.5. Fallback Image Flow

* Hệ thống kiểm tra variant main image active.
* Nếu không có, kiểm tra variant first active image.
* Nếu không có, kiểm tra product main image active.
* Nếu không có, kiểm tra product first active image.
* Nếu không có, dùng placeholder.

---

## 13. Error Handling

| Case                          | Expected Handling                                  |
| ----------------------------- | -------------------------------------------------- |
| Variant không tồn tại         | Hiển thị lỗi hoặc 404                              |
| Upload file không phải ảnh    | Hiển thị lỗi validation                            |
| Upload file quá lớn           | Hiển thị lỗi validation                            |
| Image không thuộc variant     | Không cho thao tác                                 |
| Set main image không tồn tại  | Hiển thị lỗi phù hợp                               |
| Xóa main image                | Tự fallback hoặc để rỗng an toàn                   |
| File vật lý không tồn tại     | Không làm lỗi trắng màn hình                       |
| Storage chưa linked           | Hiển thị hướng dẫn chạy storage link hoặc fallback |
| Customer truy cập route admin | Bị chặn                                            |
| Variant inactive              | Không hiển thị ảnh ngoài frontend                  |
| Image inactive                | Không hiển thị ngoài frontend                      |

---

## 14. Security

Yêu cầu bảo mật:

* Chỉ admin mới được upload, sửa, xóa variant images.
* Customer không được truy cập route admin.
* Validate file upload cẩn thận.
* Chỉ cho phép image mime type an toàn.
* Không upload file PHP, JS, HTML hoặc file nguy hiểm.
* Không tin tưởng file extension.
* Không expose path nội bộ không cần thiết.
* Không cho xóa ảnh không thuộc variant hiện tại.
* Form phải có CSRF protection.
* Escape alt text khi hiển thị.
* Không hiển thị lỗi kỹ thuật ra frontend.

---

## 15. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type                  | Description                                     |
| --------------------- | ----------------------------------------------- |
| Model                 | Variant Image model                             |
| Model Update          | Cập nhật Product Variant relationship           |
| Controller            | Admin variant image controller                  |
| Request               | Validate upload/update variant image            |
| Migration             | Tạo bảng variant_images                         |
| View                  | Manage variant images screen                    |
| View Update           | Product edit variant section                    |
| Route                 | Admin routes cho variant images                 |
| Storage               | Lưu ảnh variant vào public storage              |
| Product Detail Update | Cập nhật gallery theo variant nếu Task 14 đã có |
| Catalog Update        | Fallback ảnh nếu cần                            |

Lưu ý:

* Không làm lại Product Image Upload của Task 11.
* Không sửa admin layout lớn.
* Không implement Cart trong task này.
* Không implement Checkout trong task này.
* Không implement Order trong task này.
* Không dùng Vue.js trong MVP.

---

## 16. Commands

Sau khi Codex implement xong, chạy các lệnh kiểm tra sau:

| Command                  | Purpose                              |
| ------------------------ | ------------------------------------ |
| php artisan migrate      | Chạy migration                       |
| php artisan storage:link | Tạo public storage link nếu chưa có  |
| php artisan route:list   | Kiểm tra route                       |
| php artisan serve        | Chạy local server                    |
| npm run build            | Build frontend nếu có thay đổi asset |

URL test admin:

`http://127.0.0.1:8000/admin/products`

Vào màn hình edit product, chọn một variant và mở phần quản lý ảnh variant.

URL test frontend nếu Task 14 đã có:

`http://127.0.0.1:8000/products/{slug}`

---

## 17. Test Cases

| Test Case ID | Scenario                                 | Expected Result                                |
| ------------ | ---------------------------------------- | ---------------------------------------------- |
| TC-001       | Admin mở màn hình quản lý ảnh variant    | Hiển thị danh sách ảnh                         |
| TC-002       | Variant chưa có ảnh                      | Hiển thị empty state                           |
| TC-003       | Upload ảnh hợp lệ                        | Upload thành công                              |
| TC-004       | Upload nhiều ảnh hợp lệ                  | Tạo nhiều ảnh variant                          |
| TC-005       | Upload file không phải ảnh               | Hiển thị lỗi validation                        |
| TC-006       | Upload file quá lớn                      | Hiển thị lỗi validation                        |
| TC-007       | Variant chưa có main image               | Ảnh đầu tiên được set main                     |
| TC-008       | Set ảnh khác làm main                    | Chỉ ảnh đó là main                             |
| TC-009       | Xóa ảnh thường                           | Xóa thành công                                 |
| TC-010       | Xóa main image                           | Fallback main image an toàn                    |
| TC-011       | Cập nhật alt text                        | Alt text được lưu                              |
| TC-012       | Cập nhật sort order                      | Gallery sắp xếp đúng                           |
| TC-013       | Set image inactive                       | Frontend không hiển thị image đó               |
| TC-014       | Customer truy cập route admin image      | Bị chặn                                        |
| TC-015       | Product Detail chọn variant có ảnh       | Gallery đổi sang variant images                |
| TC-016       | Product Detail chọn variant không có ảnh | Fallback product images                        |
| TC-017       | Product không có ảnh nào                 | Hiển thị placeholder                           |
| TC-018       | Product Catalog fallback variant image   | Hiển thị ảnh fallback nếu product không có ảnh |
| TC-019       | Image không thuộc variant                | Không cho thao tác                             |
| TC-020       | Storage file bị thiếu                    | Không lỗi trắng màn hình                       |

---

## 18. Acceptance Criteria

Task này được xem là hoàn thành khi:

* [ ] Có bảng variant_images.
* [ ] Product Variant có thể có nhiều images.
* [ ] Admin có thể upload ảnh cho variant.
* [ ] Admin có thể upload nhiều ảnh.
* [ ] Admin có thể set main image cho variant.
* [ ] Mỗi variant chỉ có một main image.
* [ ] Admin có thể xóa ảnh variant.
* [ ] Admin có thể cập nhật alt text.
* [ ] Admin có thể cập nhật sort order.
* [ ] Admin có thể bật/tắt ảnh variant.
* [ ] Product edit screen hiển thị thông tin ảnh variant.
* [ ] Có màn hình hoặc modal quản lý ảnh variant.
* [ ] File upload được validate an toàn.
* [ ] Ảnh lưu trong public storage.
* [ ] Product Detail có thể đổi ảnh theo variant nếu Task 14 đã có.
* [ ] Có fallback từ variant image sang product image.
* [ ] Có placeholder nếu không có ảnh.
* [ ] Customer không truy cập được route admin.
* [ ] Không làm hỏng Product Image Upload Task 11.
* [ ] Không implement Cart trong task này.
* [ ] Không implement Checkout trong task này.
* [ ] Không implement Order trong task này.

---

## 19. Impacted Tasks

Task này ảnh hưởng đến các task sau:

| Task      | Impact                                                     |
| --------- | ---------------------------------------------------------- |
| Task 10.1 | Variant combinations cần có ảnh riêng                      |
| Task 11   | Product image vẫn là ảnh chung, variant image là ảnh riêng |
| Task 13   | Product Catalog có thể fallback variant image              |
| Task 14   | Product Detail đổi gallery theo variant                    |
| Task 15   | Cart hiển thị ảnh variant customer đã chọn                 |
| Task 19   | Order item có thể lưu hoặc hiển thị ảnh variant            |
| Task 20   | Admin Order hiển thị ảnh variant trong order item          |

Nếu các task trên đã implement trước, Codex cần cập nhật tương thích ở mức cần thiết, không làm vượt phạm vi.

---

## 20. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-10-product-management-with-translation.md
* docs/tasks/task-10-1-product-options-and-variant-combinations.md
* docs/tasks/task-10-2-variant-images.md
* docs/tasks/task-11-product-image-upload.md
* docs/tasks/task-14-product-detail-page.md

Sau đó implement Task 10.2: Variant Images theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 10.2.
* Không làm sang task khác.
* Không tự ý thay đổi thiết kế lớn.
* Mỗi product variant có thể có nhiều ảnh.
* Mỗi product variant chỉ có một main image.
* Dùng bảng riêng variant_images, không hard-code image fields cố định cho từng option.
* Không thay thế Product Image Upload của Task 11.
* Product images là ảnh chung của sản phẩm.
* Variant images là ảnh riêng của từng variant.
* Nếu cần cập nhật nhẹ Product Detail Page để đổi ảnh theo variant thì được phép.
* Không implement Cart, Checkout, Order hoặc Payment.
* Không dùng Vue.js trong MVP.
* Sau khi làm xong, báo cáo file đã tạo/sửa và lệnh cần chạy để test.
