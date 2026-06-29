Task tiếp theo là:

`Task 10: Product Management with Translation`

Bạn tạo file:

`docs/tasks/task-10-product-management-with-translation.md`

và copy nội dung dưới đây vào file.

# Task 10: Product Management with Translation

## 1. Overview

Task này dùng để xây dựng chức năng quản lý sản phẩm cho hệ thống e-commerce.

Product Management cần hỗ trợ đa ngôn ngữ, nghĩa là một sản phẩm có thể có nhiều bản dịch theo từng language.

Ví dụ:

| Language | Product Name        | Slug                 |
| -------- | ------------------- | -------------------- |
| vi       | Áo thun nam màu đen | ao-thun-nam-mau-den  |
| en       | Black Men T-Shirt   | black-men-t-shirt    |
| ja       | 黒いメンズTシャツ           | black-men-t-shirt-ja |

Sản phẩm sẽ được dùng cho:

* Public Product Catalog
* Product Detail Page
* Cart
* Checkout
* Order
* Inventory
* Report
* SEO URL

---

## 2. Objectives

Sau khi hoàn thành task này, hệ thống cần có:

* Màn hình danh sách product trong admin.
* Chức năng thêm product.
* Chức năng sửa product.
* Chức năng xóa product.
* Chức năng bật hoặc tắt product.
* Hỗ trợ product translation theo nhiều ngôn ngữ.
* Hỗ trợ slug riêng theo từng ngôn ngữ.
* Hỗ trợ SEO title và SEO description theo từng ngôn ngữ.
* Có thể gán product vào category.
* Có thể gán tax class cho product.
* Có thể nhập SKU.
* Có thể nhập price, sale price, cost price.
* Có thể đánh dấu featured product.
* Có thể tạo product variant cơ bản.
* Sidebar admin có menu Products.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong task này:

* Quản lý danh sách product.
* Thêm product mới.
* Chỉnh sửa product.
* Xóa product.
* Bật hoặc tắt product.
* Gán category cho product.
* Gán tax class cho product.
* Nhập SKU.
* Nhập price.
* Nhập sale price.
* Nhập cost price.
* Bật hoặc tắt featured flag.
* Nhập bản dịch product theo từng active language.
* Validate dữ liệu product.
* Validate dữ liệu product translation.
* Tạo slug tự động nếu admin không nhập.
* Đảm bảo slug unique theo từng language.
* Quản lý product variant cơ bản.
* Cập nhật menu Products trong admin sidebar.

### 3.2. Out of Scope

Các phần chưa làm trong task này:

* Product image upload.
* Product gallery.
* Inventory management.
* Public Product Catalog.
* Product Detail Page.
* Cart.
* Checkout.
* Order.
* Product review.
* Product import/export.
* Product bundle.
* Flash sale.
* Product comparison.
* Product recommendation.
* Advanced product attributes.
* Variant inventory nâng cao.

---

## 4. User Roles

| Role        | Permission                                |
| ----------- | ----------------------------------------- |
| super_admin | Có toàn quyền quản lý product             |
| admin       | Có quyền quản lý product                  |
| staff       | Có thể xem hoặc thao tác giới hạn sau này |
| customer    | Không được truy cập                       |

Route quản lý product chỉ dành cho khu vực admin.

Customer không được truy cập các màn hình Product Management.

---

## 5. Functional Requirements

## FR-01: Product List

Admin có thể xem danh sách product tại:

`/admin/products`

Danh sách cần hiển thị:

| Field      | Description                       |
| ---------- | --------------------------------- |
| ID         | Mã product                        |
| SKU        | Mã SKU                            |
| Name       | Tên product theo default language |
| Category   | Danh mục                          |
| Price      | Giá bán                           |
| Sale Price | Giá khuyến mãi                    |
| Status     | Active hoặc Inactive              |
| Featured   | Có phải sản phẩm nổi bật không    |
| Created At | Ngày tạo                          |
| Actions    | Edit, Delete                      |

Expected behavior:

* Hiển thị tên product theo default language.
* Nếu product chưa có translation ở default language, fallback sang translation đầu tiên có sẵn.
* Có filter theo keyword.
* Có filter theo category.
* Có filter theo status.
* Có filter theo featured.
* Có pagination.
* Có sắp xếp theo ngày tạo hoặc giá nếu cần.

---

## FR-02: Create Product

Admin có thể thêm product mới tại:

`/admin/products/create`

Thông tin chung:

| Field      | Required | Description          |
| ---------- | -------- | -------------------- |
| Category   | Yes      | Danh mục sản phẩm    |
| Tax Class  | No       | Nhóm thuế            |
| SKU        | Yes      | Mã sản phẩm          |
| Price      | Yes      | Giá bán              |
| Sale Price | No       | Giá khuyến mãi       |
| Cost Price | No       | Giá vốn              |
| Status     | Yes      | Active hoặc Inactive |
| Featured   | No       | Sản phẩm nổi bật     |

Thông tin dịch theo từng language:

| Field             | Required                      | Description     |
| ----------------- | ----------------------------- | --------------- |
| Name              | Required for default language | Tên sản phẩm    |
| Slug              | No                            | URL slug        |
| Short Description | No                            | Mô tả ngắn      |
| Description       | No                            | Mô tả đầy đủ    |
| Meta Title        | No                            | SEO title       |
| Meta Description  | No                            | SEO description |

Expected behavior:

* Product cần có translation cho default language.
* SKU không được trùng.
* Price phải hợp lệ.
* Sale price nếu nhập thì không nên lớn hơn price.
* Nếu slug trống, hệ thống tự tạo slug từ name.
* Slug phải unique theo từng language.
* Có thể nhập translation cho nhiều language trong cùng một form.
* Sau khi tạo thành công, quay lại danh sách product.

---

## FR-03: Edit Product

Admin có thể sửa product tại:

`/admin/products/{id}/edit`

Admin có thể sửa:

* Category
* Tax class
* SKU
* Price
* Sale price
* Cost price
* Status
* Featured flag
* Name theo từng language
* Slug theo từng language
* Short description theo từng language
* Description theo từng language
* Meta title theo từng language
* Meta description theo từng language
* Product variants cơ bản

Expected behavior:

* SKU phải unique, ngoại trừ product hiện tại.
* Slug phải unique theo từng language, ngoại trừ translation hiện tại.
* Nếu xóa name của default language, hiển thị lỗi validation.
* Sau khi cập nhật thành công, quay lại danh sách product.

---

## FR-04: Delete Product

Admin có thể xóa product nếu product chưa phát sinh dữ liệu quan trọng.

Business rules:

* Không nên hard delete product đã phát sinh order.
* Nếu product đã có order, nên không cho xóa hoặc chỉ soft delete.
* Nếu product chưa có dữ liệu liên quan, cho phép xóa.
* Khi xóa product, translation và variant liên quan cần được xử lý phù hợp.
* Product bị xóa hoặc inactive không hiển thị ngoài public site.

Trong giai đoạn hiện tại, nếu chưa có order, có thể cho phép xóa hoặc soft delete theo database design.

---

## FR-05: Enable / Disable Product

Admin có thể bật hoặc tắt product.

Business rules:

* Product inactive không hiển thị ở public site.
* Product inactive không nên được thêm vào cart.
* Product inactive vẫn có thể hiển thị trong admin.
* Không ảnh hưởng đến order cũ.

---

## FR-06: Product Translation

Mỗi product có thể có nhiều translation.

Ví dụ:

| Product ID | Language | Name        | Slug           |
| ---------- | -------- | ----------- | -------------- |
| 1          | vi       | Áo thun nam | ao-thun-nam    |
| 1          | en       | Men T-Shirt | men-t-shirt    |
| 1          | ja       | メンズTシャツ     | men-t-shirt-ja |

Business rules:

* Mỗi product chỉ có một translation cho một language.
* Translation của default language là bắt buộc.
* Translation của language khác có thể optional.
* Nếu translation của language hiện tại không tồn tại, fallback về default language.
* Slug phải unique trong cùng một language.

---

## FR-07: Product Slug

Slug dùng để tạo URL product ngoài frontend.

Ví dụ:

`/vi/products/ao-thun-nam`

`/en/products/men-t-shirt`

Business rules:

* Slug nên dùng chữ thường.
* Slug không nên có dấu tiếng Việt.
* Slug không nên chứa khoảng trắng.
* Nếu admin không nhập slug, hệ thống tự tạo từ name.
* Slug phải unique theo từng language.
* Nếu slug bị trùng, hệ thống cần báo lỗi hoặc tự tạo slug khác tùy cách implement.
* Cách xử lý slug cần nhất quán giữa create và update.

---

## FR-08: Product Price

Product cần có các loại giá:

| Field      | Description              |
| ---------- | ------------------------ |
| Price      | Giá bán chính            |
| Sale Price | Giá khuyến mãi           |
| Cost Price | Giá vốn, chỉ dùng nội bộ |

Business rules:

* Price là bắt buộc.
* Price không được nhỏ hơn 0.
* Sale price có thể rỗng.
* Sale price nếu nhập không được nhỏ hơn 0.
* Sale price nếu nhập không nên lớn hơn price.
* Cost price có thể rỗng.
* Cost price chỉ dùng cho admin/report, không hiển thị public.
* Giá lưu theo base currency, mặc định là VND.

---

## FR-09: Product SKU

Mỗi product cần có SKU.

Business rules:

* SKU là bắt buộc.
* SKU không được trùng.
* SKU nên được trim khoảng trắng.
* SKU có thể chứa chữ, số, dấu gạch ngang hoặc gạch dưới.
* SKU dùng cho quản lý sản phẩm, order item và inventory sau này.

---

## FR-10: Product Category

Mỗi product cần thuộc về một category.

Business rules:

* Category là bắt buộc.
* Chỉ nên chọn category active.
* Nếu category inactive, không nên cho chọn khi tạo product mới.
* Nếu category bị inactive sau khi product đã tạo, product vẫn tồn tại nhưng public có thể không hiển thị category.

---

## FR-11: Product Tax Class

Product có thể được gán tax class.

Business rules:

* Tax class có thể optional.
* Nếu product không có tax class, checkout sau này có thể dùng default tax class hoặc tax rate 0.
* Chỉ nên chọn tax class active.
* Tax class sẽ được dùng ở checkout để tính tax.

---

## FR-12: Featured Product

Admin có thể đánh dấu product là featured.

Business rules:

* Featured product có thể hiển thị ở homepage hoặc khu vực nổi bật sau này.
* Task này chỉ cần lưu trạng thái featured.
* Chưa cần làm public homepage featured section.

---

## FR-13: Product Variant Basic

Product có thể có variant cơ bản.

Ví dụ:

| Product     | Variant     |
| ----------- | ----------- |
| Áo thun nam | Size M      |
| Áo thun nam | Size L      |
| iPhone 15   | 128GB Black |
| iPhone 15   | 256GB Blue  |

Variant fields:

| Field      | Required | Description           |
| ---------- | -------- | --------------------- |
| SKU        | Yes      | SKU của variant       |
| Name       | Yes      | Tên variant           |
| Price      | No       | Giá riêng của variant |
| Sale Price | No       | Giá khuyến mãi riêng  |
| Status     | Yes      | Active hoặc Inactive  |

Business rules:

* Variant SKU không được trùng.
* Variant name là bắt buộc nếu tạo variant.
* Nếu variant price rỗng, dùng product price.
* Nếu variant sale price rỗng, dùng product sale price.
* Inventory cho variant sẽ làm ở task sau.
* Product image cho variant chưa làm trong task này.

---

## FR-14: SEO Fields

Mỗi translation cần hỗ trợ SEO.

Fields:

| Field            | Description |
| ---------------- | ----------- |
| Meta Title       | Tiêu đề SEO |
| Meta Description | Mô tả SEO   |

Business rules:

* Nếu meta title trống, có thể dùng product name.
* Nếu meta description trống, có thể dùng short description.
* SEO fields không bắt buộc trong admin.

---

## FR-15: Cache Product

Danh sách product có thể được cache ở các task public sau.

Trong task admin này, nếu có tạo cache cho product thì cần clear cache khi:

* Create product
* Update product
* Delete product
* Enable hoặc disable product
* Update translation
* Update variant

---

## 6. UI / Screen Design

## 6.1. Screen List

| Screen         | URL                         | Description       |
| -------------- | --------------------------- | ----------------- |
| Product List   | `/admin/products`           | Danh sách product |
| Create Product | `/admin/products/create`    | Thêm product      |
| Edit Product   | `/admin/products/{id}/edit` | Sửa product       |

---

## 6.2. Product List Screen

Màn hình danh sách cần có:

* Page title: Products
* Button: Add Product
* Filter keyword
* Filter category
* Filter status
* Filter featured
* Table danh sách product
* Action buttons: Edit, Delete

Table columns:

| Column     | Description                       |
| ---------- | --------------------------------- |
| ID         | Mã product                        |
| SKU        | Mã SKU                            |
| Name       | Tên product theo default language |
| Category   | Danh mục                          |
| Price      | Giá bán                           |
| Sale Price | Giá khuyến mãi                    |
| Status     | Active hoặc Inactive              |
| Featured   | Có hoặc không                     |
| Created At | Ngày tạo                          |
| Actions    | Các nút thao tác                  |

---

## 6.3. Create / Edit Form

Form nên chia thành các phần:

### General Information

| Label      | Field        |
| ---------- | ------------ |
| Category   | category_id  |
| Tax Class  | tax_class_id |
| SKU        | sku          |
| Price      | price        |
| Sale Price | sale_price   |
| Cost Price | cost_price   |
| Status     | status       |
| Featured   | is_featured  |

### Translation Information

Nên hiển thị theo tab language:

* Vietnamese
* English
* Japanese

Mỗi tab gồm:

| Label             | Field             |
| ----------------- | ----------------- |
| Name              | name              |
| Slug              | slug              |
| Short Description | short_description |
| Description       | description       |
| Meta Title        | meta_title        |
| Meta Description  | meta_description  |

### Variant Information

Có thể hiển thị dạng bảng nhập nhiều dòng:

| Field        | Description          |
| ------------ | -------------------- |
| Variant SKU  | SKU của variant      |
| Variant Name | Tên variant          |
| Price        | Giá riêng            |
| Sale Price   | Giá khuyến mãi riêng |
| Status       | Active hoặc Inactive |

Button:

* Save
* Back

---

## 7. Database Design

## 7.1. Table: products

Bảng `products` dùng để lưu thông tin kỹ thuật của sản phẩm.

| Column       | Type            | Nullable | Default        | Description          |
| ------------ | --------------- | -------- | -------------- | -------------------- |
| id           | bigint unsigned | No       | auto increment | Primary key          |
| category_id  | bigint unsigned | No       | null           | Category             |
| tax_class_id | bigint unsigned | Yes      | null           | Tax class            |
| sku          | varchar(100)    | No       | null           | Product SKU          |
| price        | decimal(15,2)   | No       | 0.00           | Giá bán              |
| sale_price   | decimal(15,2)   | Yes      | null           | Giá khuyến mãi       |
| cost_price   | decimal(15,2)   | Yes      | null           | Giá vốn              |
| status       | tinyint         | No       | 1              | 1 active, 0 inactive |
| is_featured  | tinyint         | No       | 0              | 1 featured, 0 normal |
| created_at   | timestamp       | Yes      | null           | Created time         |
| updated_at   | timestamp       | Yes      | null           | Updated time         |
| deleted_at   | timestamp       | Yes      | null           | Soft delete          |

Indexes:

| Index        | Description          |
| ------------ | -------------------- |
| unique sku   | Không cho trùng SKU  |
| category_id  | Lọc theo category    |
| tax_class_id | Lọc theo tax class   |
| status       | Lọc active/inactive  |
| is_featured  | Lọc featured         |
| price        | Sort/filter theo giá |

---

## 7.2. Table: product_translations

Bảng `product_translations` dùng để lưu nội dung dịch của sản phẩm.

| Column            | Type            | Nullable | Default        | Description        |
| ----------------- | --------------- | -------- | -------------- | ------------------ |
| id                | bigint unsigned | No       | auto increment | Primary key        |
| product_id        | bigint unsigned | No       | null           | Liên kết product   |
| language_code     | varchar(10)     | No       | null           | vi, en, ja         |
| name              | varchar(255)    | No       | null           | Tên sản phẩm       |
| slug              | varchar(255)    | No       | null           | Slug theo language |
| short_description | text            | Yes      | null           | Mô tả ngắn         |
| description       | longtext        | Yes      | null           | Mô tả đầy đủ       |
| meta_title        | varchar(255)    | Yes      | null           | SEO title          |
| meta_description  | text            | Yes      | null           | SEO description    |
| created_at        | timestamp       | Yes      | null           | Created time       |
| updated_at        | timestamp       | Yes      | null           | Updated time       |

Indexes:

| Index                             | Description                                         |
| --------------------------------- | --------------------------------------------------- |
| product_id                        | Tìm translation theo product                        |
| language_code                     | Tìm translation theo language                       |
| unique product_id + language_code | Một product chỉ có một translation cho một language |
| unique language_code + slug       | Slug không trùng trong cùng language                |

---

## 7.3. Table: product_variants

Bảng `product_variants` dùng để lưu biến thể cơ bản của sản phẩm.

| Column     | Type            | Nullable | Default        | Description           |
| ---------- | --------------- | -------- | -------------- | --------------------- |
| id         | bigint unsigned | No       | auto increment | Primary key           |
| product_id | bigint unsigned | No       | null           | Liên kết product      |
| sku        | varchar(100)    | No       | null           | Variant SKU           |
| name       | varchar(255)    | No       | null           | Variant name          |
| price      | decimal(15,2)   | Yes      | null           | Giá riêng của variant |
| sale_price | decimal(15,2)   | Yes      | null           | Giá khuyến mãi riêng  |
| status     | tinyint         | No       | 1              | 1 active, 0 inactive  |
| created_at | timestamp       | Yes      | null           | Created time          |
| updated_at | timestamp       | Yes      | null           | Updated time          |
| deleted_at | timestamp       | Yes      | null           | Soft delete           |

Indexes:

| Index      | Description              |
| ---------- | ------------------------ |
| unique sku | Không cho trùng SKU      |
| product_id | Tìm variant theo product |
| status     | Lọc active/inactive      |

---

## 7.4. Relationship

Quan hệ dữ liệu:

| Relationship                          | Description                            |
| ------------------------------------- | -------------------------------------- |
| Product belongs to Category           | Một product thuộc một category         |
| Product belongs to Tax Class          | Một product có thể thuộc một tax class |
| Product has many Product Translations | Một product có nhiều bản dịch          |
| Product has many Product Variants     | Một product có nhiều biến thể          |
| Product has many Product Images       | Sẽ làm ở Task 11                       |
| Product has inventory stock           | Sẽ làm ở Task 12                       |

---

## 8. Route Design

Các route cần có:

| Method | URL                         | Description       |
| ------ | --------------------------- | ----------------- |
| GET    | `/admin/products`           | Danh sách product |
| GET    | `/admin/products/create`    | Form thêm product |
| POST   | `/admin/products`           | Lưu product mới   |
| GET    | `/admin/products/{id}/edit` | Form sửa product  |
| PUT    | `/admin/products/{id}`      | Cập nhật product  |
| DELETE | `/admin/products/{id}`      | Xóa product       |

Tất cả route trên cần được bảo vệ bởi admin authentication.

---

## 9. Validation Rules

## 9.1. General Product Validation

| Field        | Rule                                                    |
| ------------ | ------------------------------------------------------- |
| category_id  | Required, exists in categories                          |
| tax_class_id | Optional, exists in tax classes                         |
| sku          | Required, unique, max 100 characters                    |
| price        | Required, numeric, min 0                                |
| sale_price   | Optional, numeric, min 0, should not greater than price |
| cost_price   | Optional, numeric, min 0                                |
| status       | Required                                                |
| is_featured  | Optional                                                |

Khi update, rule unique cần bỏ qua product hiện tại.

---

## 9.2. Translation Validation

Default language translation:

| Field             | Rule                                              |
| ----------------- | ------------------------------------------------- |
| name              | Required, max 255 characters                      |
| slug              | Optional, unique per language, max 255 characters |
| short_description | Optional                                          |
| description       | Optional                                          |
| meta_title        | Optional, max 255 characters                      |
| meta_description  | Optional                                          |

Other language translations:

| Field             | Rule                                              |
| ----------------- | ------------------------------------------------- |
| name              | Optional, max 255 characters                      |
| slug              | Optional, unique per language, max 255 characters |
| short_description | Optional                                          |
| description       | Optional                                          |
| meta_title        | Optional, max 255 characters                      |
| meta_description  | Optional                                          |

Business validation:

* Product phải có translation cho default language.
* Nếu language translation có name thì slug có thể tự generate.
* Nếu slug được nhập thì phải unique theo language.
* Slug không được trùng trong cùng language.

---

## 9.3. Variant Validation

| Field              | Rule                                                            |
| ------------------ | --------------------------------------------------------------- |
| variant_sku        | Required nếu tạo variant, unique                                |
| variant_name       | Required nếu tạo variant                                        |
| variant_price      | Optional, numeric, min 0                                        |
| variant_sale_price | Optional, numeric, min 0, should not greater than variant price |
| variant_status     | Required nếu tạo variant                                        |

Business validation:

* Nếu một dòng variant không có SKU và name thì có thể bỏ qua.
* Nếu có nhập một field quan trọng của variant thì phải validate đầy đủ.
* Variant SKU không được trùng với variant khác.
* Variant SKU không nên trùng với product SKU.

---

## 10. Business Logic

## 10.1. Create Product Flow

* Admin mở màn hình tạo product.
* Hệ thống load danh sách active categories.
* Hệ thống load danh sách active tax classes.
* Hệ thống load danh sách active languages.
* Admin nhập thông tin general.
* Admin nhập translation theo từng language.
* Admin nhập variant nếu có.
* Hệ thống validate dữ liệu.
* Hệ thống tạo product.
* Hệ thống tạo product translations.
* Hệ thống tạo product variants nếu có.
* Hệ thống tự generate slug nếu slug trống.
* Hệ thống clear cache product nếu có.
* Hệ thống redirect về danh sách product với thông báo thành công.

---

## 10.2. Update Product Flow

* Admin mở màn hình sửa product.
* Hệ thống load product hiện tại.
* Hệ thống load translations hiện tại.
* Hệ thống load variants hiện tại.
* Hệ thống load danh sách active categories.
* Hệ thống load danh sách active tax classes.
* Hệ thống load danh sách active languages.
* Admin cập nhật thông tin.
* Hệ thống validate dữ liệu.
* Hệ thống cập nhật product.
* Hệ thống cập nhật hoặc tạo mới translation theo từng language.
* Hệ thống cập nhật hoặc tạo mới variants.
* Hệ thống xử lý variant bị xóa nếu admin remove khỏi form.
* Hệ thống clear cache product nếu có.
* Hệ thống redirect về danh sách product với thông báo thành công.

---

## 10.3. Delete Product Flow

* Admin click delete product.
* Hệ thống kiểm tra product đã phát sinh order hay chưa.
* Nếu đã có order, không hard delete.
* Nếu chưa có dữ liệu quan trọng, cho phép xóa hoặc soft delete.
* Translation và variant liên quan cần được xử lý phù hợp.
* Hệ thống clear cache product nếu có.
* Hệ thống redirect về danh sách product với thông báo phù hợp.

---

## 10.4. Translation Fallback Flow

* Hệ thống cần hiển thị product theo language hiện tại nếu có translation.
* Nếu không có translation theo language hiện tại, fallback về default language.
* Nếu vẫn không có default language, fallback về translation đầu tiên có sẵn.

---

## 10.5. Slug Generation Flow

* Nếu admin nhập slug, hệ thống dùng slug đó sau khi normalize.
* Nếu admin không nhập slug, hệ thống tạo slug từ name.
* Slug cần unique theo language.
* Nếu slug trùng, hệ thống cần xử lý bằng validation error hoặc tự thêm hậu tố.
* Cách xử lý cần nhất quán trong create và update.

---

## 10.6. Product Price Flow

* Giá sản phẩm được lưu theo base currency.
* Nếu sale price có giá trị thì public sau này sẽ dùng sale price làm giá bán hiện tại.
* Nếu sale price rỗng thì dùng price.
* Cost price chỉ dùng cho nội bộ admin/report.

---

## 10.7. Variant Price Flow

* Nếu variant có price riêng thì dùng variant price.
* Nếu variant không có price riêng thì dùng product price.
* Nếu variant có sale price riêng thì dùng variant sale price.
* Nếu variant không có sale price riêng thì dùng product sale price.
* Inventory theo variant sẽ làm ở task sau.

---

## 11. Error Handling

| Case                                 | Expected Handling                   |
| ------------------------------------ | ----------------------------------- |
| Thiếu name default language          | Hiển thị lỗi validation             |
| Product SKU bị trùng                 | Hiển thị lỗi validation             |
| Variant SKU bị trùng                 | Hiển thị lỗi validation             |
| Slug bị trùng trong cùng language    | Hiển thị lỗi validation             |
| Category không tồn tại               | Hiển thị lỗi validation             |
| Tax class không tồn tại              | Hiển thị lỗi validation             |
| Price nhỏ hơn 0                      | Hiển thị lỗi validation             |
| Sale price lớn hơn price             | Hiển thị lỗi validation             |
| Xóa product đã phát sinh order       | Không hard delete hoặc hiển thị lỗi |
| Guest truy cập product management    | Redirect login                      |
| Customer truy cập product management | Chặn truy cập                       |

---

## 12. Security

Yêu cầu bảo mật:

* Chỉ admin mới được truy cập Product Management.
* Customer không được truy cập.
* Validate toàn bộ input.
* Không lưu dữ liệu request ngoài danh sách field cho phép.
* Form phải có CSRF protection.
* Không hiển thị lỗi kỹ thuật trực tiếp ra màn hình production.
* Nội dung nhập từ admin cần được escape khi hiển thị.
* Không cho customer thao tác product management.

---

## 13. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type       | Description                                                           |
| ---------- | --------------------------------------------------------------------- |
| Model      | Product model, Product Translation model, Product Variant model       |
| Service    | Product service nếu cần                                               |
| Controller | Admin product controller                                              |
| Request    | Validate create/update product                                        |
| Seeder     | Product seeder nếu cần dữ liệu mẫu                                    |
| Migration  | Tạo bảng products, product_translations, product_variants nếu chưa có |
| View       | List, create, edit, form product                                      |
| Route      | Admin routes cho Product Management                                   |
| Sidebar    | Cập nhật menu Products trong admin sidebar                            |

Lưu ý:

* Nếu bảng `products`, `product_translations`, `product_variants` đã tồn tại thì không tạo migration trùng.
* Không sửa các module không liên quan.
* Không implement Product Image Upload trong task này.
* Không implement Inventory Management trong task này.

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

`http://127.0.0.1:8000/admin/products`

---

## 15. Test Cases

| Test Case ID | Scenario                                       | Expected Result                 |
| ------------ | ---------------------------------------------- | ------------------------------- |
| TC-001       | Guest vào `/admin/products`                    | Redirect login                  |
| TC-002       | Customer vào `/admin/products`                 | Bị chặn                         |
| TC-003       | Admin vào `/admin/products`                    | Hiển thị danh sách product      |
| TC-004       | Tạo product với default language hợp lệ        | Tạo thành công                  |
| TC-005       | Tạo product thiếu name default language        | Hiển thị lỗi validation         |
| TC-006       | Tạo product có translation nhiều language      | Lưu đúng translations           |
| TC-007       | Tạo product không nhập slug                    | Hệ thống tự tạo slug            |
| TC-008       | Tạo product với slug trùng trong cùng language | Hiển thị lỗi validation         |
| TC-009       | Tạo product với SKU trùng                      | Hiển thị lỗi validation         |
| TC-010       | Tạo product với price âm                       | Hiển thị lỗi validation         |
| TC-011       | Tạo product với sale price lớn hơn price       | Hiển thị lỗi validation         |
| TC-012       | Sửa product hợp lệ                             | Cập nhật thành công             |
| TC-013       | Disable product                                | Product chuyển inactive         |
| TC-014       | Tạo product có variant                         | Variant được lưu đúng           |
| TC-015       | Tạo variant SKU trùng                          | Hiển thị lỗi validation         |
| TC-016       | Xóa product chưa phát sinh order               | Xóa hoặc soft delete thành công |
| TC-017       | Hiển thị fallback translation                  | Hiển thị đúng language fallback |

---

## 16. Acceptance Criteria

Task này được xem là hoàn thành khi:

* [ ] Có màn hình danh sách product.
* [ ] Có màn hình thêm product.
* [ ] Có màn hình sửa product.
* [ ] Có chức năng xóa product.
* [ ] Có chức năng bật hoặc tắt product.
* [ ] Có hỗ trợ category.
* [ ] Có hỗ trợ tax class.
* [ ] Có hỗ trợ SKU.
* [ ] Có hỗ trợ price, sale price, cost price.
* [ ] Có hỗ trợ featured product.
* [ ] Có hỗ trợ translation theo active languages.
* [ ] Default language translation là bắt buộc.
* [ ] Slug unique theo từng language.
* [ ] Có tự generate slug nếu slug trống.
* [ ] Có hỗ trợ product variant cơ bản.
* [ ] Variant SKU unique.
* [ ] Có SEO fields theo từng language.
* [ ] Admin sidebar có menu Products.
* [ ] Customer không truy cập được Product Management.
* [ ] Chạy được migration.
* [ ] Không implement Product Image Upload trong task này.
* [ ] Không implement Inventory Management trong task này.

---

## 17. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-10-product-management-with-translation.md

Sau đó implement Task 10: Product Management with Translation theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 10.
* Không làm sang task khác.
* Không tự ý thay đổi thiết kế lớn.
* Không implement Product Image Upload, Inventory Management, Public Catalog, Cart, Checkout hoặc Order.
* Sau khi làm xong, báo cáo file đã tạo/sửa và lệnh cần chạy để test.
