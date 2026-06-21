Task 14 là:

`Task 14: Product Detail Page`

Bạn tạo file:

`docs/tasks/task-14-product-detail-page.md`

và copy nội dung dưới đây vào file.

# Task 14: Product Detail Page

## 1. Overview

Task này dùng để xây dựng trang chi tiết sản phẩm ngoài frontend cho customer.

Product Detail Page là trang khách hàng dùng để xem đầy đủ thông tin của một sản phẩm, bao gồm:

* Hình ảnh sản phẩm
* Tên sản phẩm
* Giá bán
* Giá khuyến mãi
* Mô tả sản phẩm
* Danh mục
* Trạng thái tồn kho
* Variant nếu có
* SEO meta
* Breadcrumb
* Sản phẩm liên quan

UI cần theo hướng hiện đại, chuyên nghiệp, responsive và phù hợp với website bán hàng online.

Frontend trong MVP sử dụng:

* Laravel Blade
* Tailwind CSS
* Alpine.js nếu cần tương tác nhỏ
* Không dùng Vue.js trong MVP

---

## 2. Objectives

Sau khi hoàn thành task này, hệ thống cần có:

* Trang chi tiết sản phẩm public.
* UI hiện đại, đẹp và responsive.
* Hiển thị ảnh chính và gallery ảnh sản phẩm.
* Hiển thị tên sản phẩm theo ngôn ngữ hiện tại.
* Hiển thị mô tả sản phẩm theo ngôn ngữ hiện tại.
* Hiển thị giá theo currency hiện tại.
* Hiển thị sale price nếu có.
* Hiển thị SKU.
* Hiển thị category.
* Hiển thị stock status.
* Hiển thị variant nếu product có variant.
* Có chọn quantity.
* Có nút Add to Cart dạng placeholder hoặc disabled nếu Task 15 chưa làm.
* Có breadcrumb.
* Có SEO meta theo product translation.
* Có related products cơ bản.
* Không làm cart logic trong task này.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong task này:

* Tạo route public cho product detail.
* Tạo controller hoặc service lấy product detail.
* Tạo view product detail.
* Hiển thị thông tin product active.
* Hiển thị translation theo language hiện tại.
* Fallback translation nếu thiếu language.
* Hiển thị product image gallery.
* Hiển thị main image.
* Hiển thị price theo currency hiện tại.
* Hiển thị sale price nếu có.
* Hiển thị category.
* Hiển thị stock status.
* Hiển thị variant cơ bản.
* Chọn variant bằng UI nếu product có variant.
* Chọn quantity.
* Breadcrumb.
* Related products cơ bản.
* SEO meta title và description.
* Responsive UI.
* Modern product detail UI.

### 3.2. Out of Scope

Các phần chưa làm trong task này:

* Add to cart thật sự.
* Cart page.
* Checkout.
* Order creation.
* Wishlist.
* Product review.
* Product rating.
* Product comparison.
* Recently viewed products.
* Product question/answer.
* Variant image riêng.
* Stock reservation.
* Payment.
* Recommendation nâng cao.
* AJAX add to cart.
* Vue.js frontend.

---

## 4. User Roles

| Role     | Permission                |
| -------- | ------------------------- |
| Guest    | Có thể xem product detail |
| Customer | Có thể xem product detail |
| Admin    | Có thể xem product detail |
| Staff    | Có thể xem product detail |

Trang product detail là public page.

Không yêu cầu login.

---

## 5. Functional Requirements

## FR-01: Product Detail Page

Customer có thể xem chi tiết sản phẩm bằng URL public.

URL đề xuất:

`/products/{slug}`

Hoặc nếu muốn hỗ trợ language sau này:

`/{language}/products/{slug}`

Trong MVP, ưu tiên đơn giản:

`/products/{slug}`

Trang cần hiển thị:

| Section             | Description                    |
| ------------------- | ------------------------------ |
| Header              | Dùng public header từ Task 13  |
| Breadcrumb          | Home / Products / Product Name |
| Product Images      | Main image và gallery          |
| Product Info        | Name, SKU, price, stock        |
| Variant Selector    | Chọn variant nếu có            |
| Quantity Selector   | Chọn số lượng                  |
| Action Area         | Add to Cart placeholder        |
| Product Description | Mô tả sản phẩm                 |
| Product Meta        | Category, tags nếu có sau này  |
| Related Products    | Sản phẩm cùng category         |
| Footer              | Dùng public footer từ Task 13  |

Expected behavior:

* Chỉ hiển thị product active.
* Product inactive không truy cập được hoặc hiển thị 404.
* Product thuộc category inactive không nên hiển thị.
* Product không có translation hợp lệ thì không hiển thị.
* Nếu slug không tồn tại, hiển thị 404.
* UI phải responsive tốt trên mobile.

---

## FR-02: Product Detail Modern UI

Product detail page cần có giao diện hiện đại.

Yêu cầu UI:

* Layout 2 cột trên desktop.
* Bên trái là product image gallery.
* Bên phải là product info.
* Trên mobile chuyển thành 1 cột.
* Product image lớn, rõ, không vỡ layout.
* Thumbnail gallery đẹp.
* Price nổi bật.
* Sale price dễ nhìn.
* Stock badge rõ ràng.
* Button lớn, dễ bấm.
* Spacing rộng rãi.
* Typography chuyên nghiệp.
* Không dùng table cho layout chính.
* Không để giao diện đơn điệu.

---

## FR-03: Product Image Gallery

Trang chi tiết cần hiển thị ảnh sản phẩm.

Yêu cầu:

| Element          | Description                                 |
| ---------------- | ------------------------------------------- |
| Main Image       | Ảnh chính lớn                               |
| Thumbnail Images | Danh sách ảnh nhỏ                           |
| Placeholder      | Ảnh mặc định nếu product không có ảnh       |
| Alt Text         | Alt text từ product image hoặc product name |

Expected behavior:

* Nếu product có main image active, dùng ảnh đó làm ảnh chính.
* Nếu không có main image, dùng ảnh active đầu tiên.
* Nếu không có ảnh, dùng placeholder đẹp.
* Thumbnail click được để đổi main image nếu dùng Alpine.js.
* Image không làm layout bị nhảy.
* Image cần có tỷ lệ cố định, ví dụ square hoặc 4:5.
* Không load ảnh quá lớn nếu không cần.

---

## FR-04: Product Translation Display

Thông tin product cần hiển thị theo language hiện tại.

Áp dụng cho:

* Product name
* Short description
* Description
* Meta title
* Meta description
* Category name

Fallback logic:

* Ưu tiên translation theo language hiện tại.
* Nếu không có, fallback về default language.
* Nếu vẫn không có, fallback về translation đầu tiên.
* Nếu không có translation nào hợp lệ, không hiển thị product.

---

## FR-05: Product Price Display

Giá sản phẩm cần hiển thị theo currency hiện tại.

Hiển thị:

| Field          | Description                  |
| -------------- | ---------------------------- |
| Price          | Giá gốc                      |
| Sale Price     | Giá khuyến mãi               |
| Current Price  | Giá đang bán                 |
| Discount Badge | Phần trăm giảm nếu tính được |

Expected behavior:

* Nếu có sale price hợp lệ, hiển thị sale price nổi bật.
* Giá gốc bị gạch ngang nếu có sale price.
* Nếu không có sale price, chỉ hiển thị price.
* Giá được convert sang currency hiện tại.
* Giá được format theo currency hiện tại.
* Nếu currency không hợp lệ, fallback về default currency.
* Không hiển thị cost price ngoài frontend.

---

## FR-06: Product SKU

Trang detail cần hiển thị SKU.

Business rules:

* Nếu product không có variant, hiển thị product SKU.
* Nếu product có variant và customer chọn variant, hiển thị SKU của variant.
* Nếu chưa chọn variant, có thể hiển thị product SKU hoặc text yêu cầu chọn variant.
* SKU chỉ là thông tin hiển thị, không cho customer chỉnh sửa.

---

## FR-07: Product Category

Trang detail cần hiển thị category của product.

Expected behavior:

* Category hiển thị theo language hiện tại.
* Category name có thể link về catalog filter theo category.
* Nếu category inactive, product không nên hiển thị public.
* Breadcrumb nên có category nếu phù hợp.

Ví dụ breadcrumb:

`Home / Products / Áo nam / Áo thun nam màu đen`

---

## FR-08: Product Stock Status

Trang detail cần hiển thị trạng thái tồn kho.

Các trạng thái:

| Status       | Display      |
| ------------ | ------------ |
| In Stock     | Còn hàng     |
| Low Stock    | Sắp hết hàng |
| Out of Stock | Hết hàng     |

Business rules:

* Product không có variant thì lấy stock của product.
* Product có variant thì lấy stock theo variant được chọn.
* Nếu chưa chọn variant, có thể hiển thị trạng thái tổng quan.
* Nếu available quantity bằng 0, button Add to Cart cần disabled hoặc hiển thị Out of Stock.
* Không hiển thị inventory log ngoài frontend.

---

## FR-09: Product Variant Selector

Nếu product có variant, trang detail cần cho customer chọn variant.

Ví dụ variant:

| Product     | Variant     |
| ----------- | ----------- |
| Áo thun nam | Size M      |
| Áo thun nam | Size L      |
| iPhone 15   | 128GB Black |
| iPhone 15   | 256GB Blue  |

Expected behavior:

* Hiển thị danh sách variant active.
* Variant inactive không hiển thị.
* Khi chọn variant, UI cập nhật:

  * SKU
  * Price nếu variant có price riêng
  * Sale price nếu variant có sale price riêng
  * Stock status
* Có thể dùng Alpine.js để đổi thông tin trên UI.
* Nếu product có variant, customer cần chọn variant trước khi Add to Cart ở task sau.
* Trong task này chưa cần add cart thật.

---

## FR-10: Quantity Selector

Trang detail cần có quantity selector.

Expected behavior:

* Quantity mặc định là 1.
* Quantity không được nhỏ hơn 1.
* Quantity không được vượt quá available quantity nếu có thể kiểm tra.
* Nếu product out of stock, quantity selector có thể disabled.
* Quantity sẽ được dùng ở Task 15 Cart.
* Trong task này chỉ cần UI và validation cơ bản phía frontend nếu phù hợp.

---

## FR-11: Add to Cart Placeholder

Task này chưa implement cart logic, nhưng cần có khu vực action.

Yêu cầu:

* Hiển thị button Add to Cart.
* Button có thể disabled hoặc chỉ là placeholder nếu Task 15 chưa làm.
* Nếu product out of stock, button disabled.
* Nếu product có variant mà chưa chọn variant, button disabled hoặc hiển thị message.
* Không tạo cart item trong task này.
* Không làm route add to cart trong task này.

Text gợi ý:

* Add to Cart
* Out of Stock
* Please select a variant

---

## FR-12: Product Description

Trang detail cần hiển thị mô tả sản phẩm.

Các phần:

| Section           | Description                     |
| ----------------- | ------------------------------- |
| Short Description | Hiển thị gần product info       |
| Description       | Hiển thị phía dưới              |
| Additional Info   | Placeholder nếu cần mở rộng sau |

Expected behavior:

* Description theo language hiện tại.
* Nếu description trống, có thể không hiển thị section.
* Nội dung cần được render an toàn.
* Không để description làm vỡ layout.

---

## FR-13: Related Products

Trang detail nên hiển thị sản phẩm liên quan cơ bản.

Logic đề xuất:

* Lấy product cùng category.
* Chỉ lấy product active.
* Không lấy product hiện tại.
* Giới hạn số lượng, ví dụ 4 hoặc 8 sản phẩm.
* Hiển thị bằng product card giống Task 13.
* Nếu không có related products, có thể ẩn section.

Related products cần hiển thị:

* Image
* Name
* Price
* Sale price nếu có
* Link đến detail page

---

## FR-14: Breadcrumb

Trang detail cần có breadcrumb.

Ví dụ:

`Home / Products / Category Name / Product Name`

Business rules:

* Product name theo language hiện tại.
* Category name theo language hiện tại.
* Link Products trỏ về `/products`.
* Link category có thể trỏ về `/products?category=slug`.

---

## FR-15: SEO Basic

Trang detail cần SEO cơ bản.

Cần có:

| SEO Item         | Description                                     |
| ---------------- | ----------------------------------------------- |
| Page title       | Product meta title hoặc product name            |
| Meta description | Product meta description hoặc short description |
| Canonical URL    | Product detail URL                              |
| Image alt        | Product image alt hoặc product name             |
| H1               | Product name                                    |
| Breadcrumb       | Hỗ trợ SEO tốt hơn                              |

Trong task này chưa cần:

* Schema.org Product.
* Open Graph nâng cao.
* Twitter Card.
* Product review schema.
* Sitemap.

---

## FR-16: Product Visibility

Product chỉ hiển thị ngoài frontend khi:

* Product active.
* Product chưa bị xóa.
* Category active.
* Category chưa bị xóa.
* Product có translation hợp lệ hoặc fallback translation.
* Slug tồn tại theo language hiện tại hoặc fallback hợp lý.

Nếu không thỏa điều kiện, trả về 404 hoặc hiển thị trang lỗi phù hợp.

---

## FR-17: Responsive Design

UI cần responsive tốt.

Breakpoints cần quan tâm:

| Device        | Requirement                     |
| ------------- | ------------------------------- |
| Mobile        | 1 column, image trên, info dưới |
| Tablet        | 1 hoặc 2 columns tùy width      |
| Desktop       | 2 columns rõ ràng               |
| Large Desktop | Content rộng, gallery đẹp       |

Expected behavior:

* Header không vỡ layout.
* Image gallery dùng tốt trên mobile.
* Button dễ bấm trên mobile.
* Description dễ đọc.
* Related products responsive grid.
* Không có horizontal scroll bất thường.

---

## FR-18: Performance

Product detail page cần chú ý performance.

Yêu cầu:

* Không query N+1.
* Load product translations hợp lý.
* Load category translations hợp lý.
* Load product images hợp lý.
* Load inventory stock hợp lý.
* Load variants hợp lý.
* Load related products giới hạn số lượng.
* Không thêm thư viện frontend lớn nếu không cần.
* Ưu tiên Blade, Tailwind, Alpine.js nhẹ.

---

## 6. UI / Screen Design

## 6.1. Product Detail Layout

Desktop layout đề xuất:

| Area                | Description                                    |
| ------------------- | ---------------------------------------------- |
| Header              | Public header                                  |
| Breadcrumb          | Home / Products / Category / Product           |
| Left Column         | Main image + thumbnails                        |
| Right Column        | Product info, price, variant, quantity, button |
| Description Section | Full width dưới product info                   |
| Related Products    | Grid sản phẩm liên quan                        |
| Footer              | Public footer                                  |

Mobile layout:

| Area               | Description                     |
| ------------------ | ------------------------------- |
| Header             | Mobile header                   |
| Image Gallery      | Full width                      |
| Product Info       | Bên dưới image                  |
| Variant / Quantity | Dễ thao tác                     |
| Description        | Collapsible hoặc section thường |
| Related Products   | Grid 1-2 columns                |

---

## 6.2. Product Info Section

Product info cần có:

| Element            | Description                 |
| ------------------ | --------------------------- |
| Category           | Category nhỏ phía trên name |
| Product Name       | H1 rõ ràng                  |
| SKU                | SKU nhỏ                     |
| Price              | Giá nổi bật                 |
| Sale Badge         | Badge giảm giá              |
| Stock Badge        | Còn hàng / hết hàng         |
| Short Description  | Mô tả ngắn                  |
| Variant Selector   | Nếu có variant              |
| Quantity Selector  | Chọn số lượng               |
| Add to Cart Button | Placeholder cho Task 15     |

---

## 6.3. Image Gallery UI

Image gallery cần có:

* Main image lớn.
* Thumbnail images.
* Active thumbnail state.
* Placeholder nếu thiếu ảnh.
* Hover effect nhẹ.
* Không để ảnh méo.
* Alt text hợp lý.

---

## 6.4. Variant UI

Variant UI có thể hiển thị dưới dạng:

* Button chips.
* Select dropdown.
* Card option nhỏ.

Yêu cầu:

* Variant active dễ nhận biết.
* Variant out of stock có thể disabled nếu có dữ liệu stock.
* Khi chọn variant, thông tin price/stock/SKU nên cập nhật nếu dùng Alpine.js.
* Không cần API riêng trong task này nếu có thể render data sẵn trong page.

---

## 6.5. Related Products UI

Related products dùng card giống catalog.

Yêu cầu:

* Section title: Related Products.
* Grid responsive.
* Product card hiện đại.
* Có image, name, price.
* Click vào card hoặc button để đi đến detail page.
* Nếu không có sản phẩm liên quan, ẩn section.

---

## 7. Data Source

Task này sử dụng dữ liệu từ các task trước:

| Data                  | Source  |
| --------------------- | ------- |
| Products              | Task 10 |
| Product Translations  | Task 10 |
| Product Variants      | Task 10 |
| Product Images        | Task 11 |
| Categories            | Task 09 |
| Category Translations | Task 09 |
| Languages             | Task 06 |
| Currencies            | Task 07 |
| Inventory Stocks      | Task 12 |
| System Settings       | Task 05 |
| Public Layout         | Task 13 |

---

## 8. Route Design

Các route public cần có:

| Method | URL                | Description         |
| ------ | ------------------ | ------------------- |
| GET    | `/products/{slug}` | Product detail page |

Có thể dùng thêm nếu cần:

| Method | URL                          | Description                                 |
| ------ | ---------------------------- | ------------------------------------------- |
| GET    | `/products/{slug}/{variant}` | Product detail với variant nếu muốn sau này |

Trong MVP, ưu tiên đơn giản:

`GET /products/{slug}`

Không yêu cầu login.

---

## 9. Query / Parameter Requirements

Product detail có thể nhận:

| Parameter | Description                      |
| --------- | -------------------------------- |
| slug      | Product slug                     |
| variant   | Variant id hoặc SKU nếu cần      |
| currency  | Currency hiện tại nếu dùng query |
| language  | Language hiện tại nếu dùng query |

Expected behavior:

* Slug không hợp lệ thì 404.
* Currency không hợp lệ thì fallback default currency.
* Language không hợp lệ thì fallback default language.
* Variant không hợp lệ thì bỏ qua hoặc hiển thị message nhẹ.

---

## 10. Business Logic

## 10.1. Product Detail Flow

* Customer mở `/products/{slug}`.
* Hệ thống xác định language hiện tại.
* Hệ thống xác định currency hiện tại.
* Hệ thống tìm product theo slug trong product translations.
* Hệ thống kiểm tra product active.
* Hệ thống kiểm tra category active.
* Hệ thống load translation phù hợp.
* Hệ thống load product images.
* Hệ thống load product variants.
* Hệ thống load inventory stock.
* Hệ thống convert và format price.
* Hệ thống load related products.
* Hệ thống render product detail page.

---

## 10.2. Product Slug Resolve Flow

* Tìm product translation theo slug và language hiện tại.
* Nếu không tìm thấy, có thể tìm slug ở default language.
* Nếu vẫn không tìm thấy, trả về 404.
* Product tìm được phải active.
* Product category phải active.

---

## 10.3. Product Image Flow

* Lấy danh sách product images active.
* Main image là ảnh có is_main.
* Nếu không có main image, dùng ảnh đầu tiên theo sort order.
* Nếu không có ảnh, dùng placeholder.
* Gallery thumbnails hiển thị tất cả ảnh active theo sort order.

---

## 10.4. Price Flow

* Nếu product không có variant được chọn:

  * Dùng product price và sale price.
* Nếu product có variant được chọn:

  * Nếu variant có price riêng thì dùng variant price.
  * Nếu không, dùng product price.
  * Nếu variant có sale price riêng thì dùng variant sale price.
  * Nếu không, dùng product sale price.
* Convert price sang currency hiện tại.
* Format price theo currency.
* Không hiển thị cost price.

---

## 10.5. Stock Flow

* Nếu product không có variant:

  * Dùng inventory stock của product.
* Nếu product có variant:

  * Khi chưa chọn variant, có thể hiển thị tổng quan stock.
  * Khi chọn variant, dùng inventory stock của variant.
* Nếu available quantity bằng 0, hiển thị Out of Stock.
* Nếu available quantity thấp hơn threshold, hiển thị Low Stock.
* Nếu còn hàng, hiển thị In Stock.

---

## 10.6. Related Products Flow

* Lấy sản phẩm cùng category.
* Loại trừ product hiện tại.
* Chỉ lấy product active.
* Chỉ lấy product thuộc category active.
* Giới hạn số lượng sản phẩm.
* Hiển thị theo product card.
* Nếu không có sản phẩm liên quan, không hiển thị section.

---

## 11. Error Handling

| Case                                                | Expected Handling               |
| --------------------------------------------------- | ------------------------------- |
| Slug không tồn tại                                  | Trả về 404                      |
| Product inactive                                    | Trả về 404                      |
| Product bị xóa                                      | Trả về 404                      |
| Category inactive                                   | Trả về 404                      |
| Product không có ảnh                                | Hiển thị placeholder            |
| Product không có translation theo language hiện tại | Fallback translation            |
| Product không có translation nào                    | Không hiển thị hoặc 404         |
| Currency không hợp lệ                               | Fallback default currency       |
| Language không hợp lệ                               | Fallback default language       |
| Variant inactive                                    | Không hiển thị variant          |
| Inventory thiếu                                     | Hiển thị stock fallback an toàn |

---

## 12. Security

Yêu cầu bảo mật:

* Public page không yêu cầu login.
* Không hiển thị product inactive.
* Không hiển thị category inactive.
* Không expose cost price.
* Không expose inventory log.
* Không expose admin-only fields.
* Escape nội dung dynamic khi render.
* Không hiển thị lỗi kỹ thuật ra frontend.
* Không cho query input làm lỗi SQL.
* Không dùng dữ liệu slug trực tiếp một cách không an toàn.

---

## 13. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type              | Description                                                     |
| ----------------- | --------------------------------------------------------------- |
| Controller        | Public product detail controller                                |
| Service           | Product detail service nếu cần                                  |
| View              | Product detail page                                             |
| Partial View      | Image gallery, product info, variant selector, related products |
| Route             | Public product detail route                                     |
| Public Layout     | Dùng lại public layout từ Task 13                               |
| Helper            | Language/currency/price display helper nếu cần                  |
| Tailwind UI       | Cải thiện UI hiện đại                                           |
| Placeholder Asset | Placeholder image nếu cần                                       |

Lưu ý:

* Không sửa admin layout.
* Không làm hỏng product catalog page Task 13.
* Không implement Cart trong task này.
* Không implement Checkout trong task này.
* Không dùng Vue.js trong MVP.

---

## 14. Commands

Sau khi Codex implement xong, chạy các lệnh kiểm tra sau:

| Command                | Purpose                              |
| ---------------------- | ------------------------------------ |
| php artisan route:list | Kiểm tra route public                |
| php artisan serve      | Chạy local server                    |
| npm run build          | Build frontend nếu có thay đổi asset |

URL test:

`http://127.0.0.1:8000/products/{slug}`

Ví dụ:

`http://127.0.0.1:8000/products/ao-thun-nam`

Slug thực tế cần lấy từ sản phẩm đã tạo trong admin.

---

## 15. Test Cases

| Test Case ID | Scenario                            | Expected Result                    |
| ------------ | ----------------------------------- | ---------------------------------- |
| TC-001       | Guest vào product detail hợp lệ     | Hiển thị trang chi tiết            |
| TC-002       | Product inactive                    | Trả về 404 hoặc không hiển thị     |
| TC-003       | Product thuộc category inactive     | Trả về 404 hoặc không hiển thị     |
| TC-004       | Slug không tồn tại                  | Trả về 404                         |
| TC-005       | Product có ảnh chính                | Hiển thị ảnh chính                 |
| TC-006       | Product không có ảnh                | Hiển thị placeholder               |
| TC-007       | Product có nhiều ảnh                | Hiển thị gallery                   |
| TC-008       | Product có sale price               | Hiển thị sale price và price gốc   |
| TC-009       | Product không có sale price         | Chỉ hiển thị price                 |
| TC-010       | Product có variant                  | Hiển thị variant selector          |
| TC-011       | Chọn variant                        | Cập nhật SKU/price/stock nếu có    |
| TC-012       | Product hết hàng                    | Hiển thị Out of Stock              |
| TC-013       | Product sắp hết hàng                | Hiển thị Low Stock                 |
| TC-014       | Product còn hàng                    | Hiển thị In Stock                  |
| TC-015       | Translation thiếu language hiện tại | Fallback đúng                      |
| TC-016       | Currency không hợp lệ               | Fallback default currency          |
| TC-017       | Related products có dữ liệu         | Hiển thị sản phẩm liên quan        |
| TC-018       | Related products rỗng               | Ẩn section hoặc hiển thị hợp lý    |
| TC-019       | Mobile layout                       | Không vỡ giao diện                 |
| TC-020       | Không expose cost price             | Frontend không hiển thị cost price |

---

## 16. Acceptance Criteria

Task này được xem là hoàn thành khi:

* [ ] Có route product detail public.
* [ ] Có trang product detail UI hiện đại.
* [ ] Dùng public layout từ Task 13.
* [ ] Hiển thị product active theo slug.
* [ ] Product inactive không hiển thị.
* [ ] Category inactive không hiển thị.
* [ ] Hiển thị product name theo language hiện tại.
* [ ] Có translation fallback.
* [ ] Hiển thị product image gallery.
* [ ] Có image placeholder nếu thiếu ảnh.
* [ ] Hiển thị price theo currency hiện tại.
* [ ] Có currency fallback.
* [ ] Hiển thị sale price nếu có.
* [ ] Không hiển thị cost price.
* [ ] Hiển thị SKU.
* [ ] Hiển thị category.
* [ ] Hiển thị stock status.
* [ ] Hiển thị variant selector nếu có variant.
* [ ] Có quantity selector.
* [ ] Có Add to Cart placeholder.
* [ ] Có breadcrumb.
* [ ] Có SEO meta cơ bản.
* [ ] Có related products cơ bản.
* [ ] Responsive tốt trên mobile/tablet/desktop.
* [ ] Không dùng Vue.js.
* [ ] Không implement Cart trong task này.
* [ ] Không implement Checkout trong task này.
* [ ] Không implement Order trong task này.

---

## 17. UI Quality Requirements

Vì đây là trang customer nhìn nhiều trước khi mua hàng, UI cần làm kỹ.

Yêu cầu UI:

* Nhìn giống product detail của website e-commerce hiện đại.
* Không dùng layout sơ sài.
* Product image phải lớn và đẹp.
* Product info phải rõ ràng, dễ đọc.
* Giá phải nổi bật.
* Sale price phải nổi bật.
* Variant selector phải dễ thao tác.
* Quantity selector phải dễ dùng.
* Button phải lớn và chuyên nghiệp.
* Related products phải dùng card đẹp.
* Mobile phải dễ mua hàng.
* Không để page quá trống hoặc quá rối.
* Không dùng màu sắc thiếu đồng bộ với public catalog.

---

## 18. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-09-category-management-with-translation.md
* docs/tasks/task-10-product-management-with-translation.md
* docs/tasks/task-11-product-image-upload.md
* docs/tasks/task-12-inventory-management.md
* docs/tasks/task-13-public-product-catalog.md
* docs/tasks/task-14-product-detail-page.md

Sau đó implement Task 14: Product Detail Page theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 14.
* Không làm sang task khác.
* Không tự ý thay đổi thiết kế lớn.
* Tạo UI product detail thật hiện đại, chuyên nghiệp, responsive.
* Sử dụng Blade, Tailwind CSS và Alpine.js nếu cần.
* Không dùng Vue.js trong MVP.
* Không sửa admin layout nếu không cần.
* Không implement Cart, Checkout, Order hoặc Payment.
* Add to Cart chỉ là placeholder nếu Task 15 chưa làm.
* Sau khi làm xong, báo cáo file đã tạo/sửa và lệnh cần chạy để test.
