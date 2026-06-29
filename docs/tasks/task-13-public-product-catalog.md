# Task 13: Public Product Catalog

## 1. Overview

Task này dùng để xây dựng màn hình danh sách sản phẩm ngoài frontend cho customer.

Public Product Catalog là trang khách hàng dùng để xem danh sách sản phẩm, tìm kiếm sản phẩm, lọc theo danh mục, lọc theo giá, xem trạng thái còn hàng và chuyển sang trang chi tiết sản phẩm.

Task này là bước đầu tiên của phần frontend public site.

UI cần theo hướng hiện đại, chuyên nghiệp, responsive và phù hợp với website bán hàng online.

Frontend trong MVP sử dụng:

* Laravel Blade
* Tailwind CSS
* Alpine.js nếu cần tương tác nhỏ
* Không dùng Vue.js trong MVP

---

## 2. Objectives

Sau khi hoàn thành task này, hệ thống cần có:

* Trang danh sách sản phẩm ngoài frontend.
* Layout public site hiện đại.
* Header public site.
* Product catalog page.
* Product card đẹp, rõ ràng.
* Hiển thị ảnh chính của sản phẩm.
* Hiển thị tên sản phẩm theo ngôn ngữ hiện tại.
* Hiển thị giá theo currency hiện tại.
* Hiển thị sale price nếu có.
* Hiển thị trạng thái tồn kho.
* Filter theo category.
* Filter theo khoảng giá.
* Search theo keyword.
* Sort sản phẩm.
* Pagination.
* Responsive tốt trên desktop, tablet, mobile.
* Empty state khi không có sản phẩm.
* SEO cơ bản cho catalog page.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong task này:

* Tạo public layout.
* Tạo public header.
* Tạo public footer đơn giản.
* Tạo trang product catalog.
* Hiển thị danh sách sản phẩm active.
* Hiển thị sản phẩm theo category active.
* Hiển thị product image chính.
* Hiển thị product name theo language hiện tại.
* Hiển thị product price theo currency hiện tại.
* Hiển thị sale price nếu có.
* Hiển thị stock status.
* Search product theo keyword.
* Filter product theo category.
* Filter product theo khoảng giá.
* Sort product.
* Pagination.
* Responsive UI.
* Modern product card UI.
* Breadcrumb cơ bản.
* SEO meta title và description cơ bản.

### 3.2. Out of Scope

Các phần chưa làm trong task này:

* Product Detail Page.
* Add to cart.
* Cart.
* Checkout.
* Wishlist.
* Product review.
* Compare product.
* Recently viewed products.
* Advanced faceted search.
* AJAX filter nâng cao.
* Infinite scroll.
* Product recommendation.
* Frontend login/register redesign.
* Payment.
* Order creation.

---

## 4. User Roles

| Role     | Permission                 |
| -------- | -------------------------- |
| Guest    | Có thể xem product catalog |
| Customer | Có thể xem product catalog |
| Admin    | Có thể xem product catalog |
| Staff    | Có thể xem product catalog |

Trang product catalog là public page.

Không yêu cầu login.

---

## 5. Functional Requirements

## FR-01: Public Product Catalog Page

Customer có thể xem danh sách sản phẩm tại:

`/products`

Trang cần hiển thị:

| Section        | Description                                         |
| -------------- | --------------------------------------------------- |
| Header         | Logo, menu, search, cart icon placeholder           |
| Breadcrumb     | Home / Products                                     |
| Page Title     | Products hoặc tên category nếu đang filter category |
| Filter Sidebar | Category, price, stock status nếu cần               |
| Product Grid   | Danh sách product card                              |
| Sort Dropdown  | Sort sản phẩm                                       |
| Pagination     | Phân trang                                          |
| Footer         | Footer public site                                  |

Expected behavior:

* Chỉ hiển thị product active.
* Chỉ hiển thị product thuộc category active.
* Product inactive không hiển thị.
* Category inactive không hiển thị trong filter.
* Nếu không có product, hiển thị empty state đẹp.

---

## FR-02: Modern Public Layout

Cần tạo layout frontend public riêng, không dùng admin layout.

Public layout cần có giao diện hiện đại:

* Full width background.
* Content container rộng hợp lý.
* Header sticky hoặc fixed nếu phù hợp.
* Khoảng cách section rõ ràng.
* Typography đẹp, dễ đọc.
* Card style hiện đại.
* Responsive tốt.
* Mobile-first.
* Màu sắc chuyên nghiệp.
* Button, badge, input đồng bộ style.
* Không dùng layout quá đơn điệu.

Public layout nên có các khu vực:

| Area         | Description                                  |
| ------------ | -------------------------------------------- |
| Top Header   | Logo, menu, search, account/cart placeholder |
| Main Content | Nội dung từng page                           |
| Footer       | Thông tin website, link nhanh                |

---

## FR-03: Header UI

Public header cần có:

| Element              | Description                              |
| -------------------- | ---------------------------------------- |
| Logo                 | Tên website hoặc logo từ system settings |
| Navigation           | Home, Products, Categories               |
| Search box           | Tìm kiếm sản phẩm                        |
| Language placeholder | Có thể hiển thị language hiện tại        |
| Currency placeholder | Có thể hiển thị currency hiện tại        |
| Account icon         | Placeholder cho login/account            |
| Cart icon            | Placeholder cho cart task sau            |

Business rules:

* Header phải responsive.
* Mobile cần có menu dạng hamburger nếu cần.
* Search box có thể submit về product catalog.
* Cart icon chỉ là placeholder, chưa cần cart logic.

---

## FR-04: Product Card UI

Product card cần đẹp, hiện đại và rõ ràng.

Mỗi product card hiển thị:

| Field              | Description                       |
| ------------------ | --------------------------------- |
| Image              | Ảnh chính của product             |
| Category           | Tên category nếu cần              |
| Product Name       | Tên theo language hiện tại        |
| Price              | Giá bán                           |
| Sale Price         | Giá khuyến mãi nếu có             |
| Discount Badge     | Badge giảm giá nếu có thể tính    |
| Stock Status       | Còn hàng, sắp hết hàng, hết hàng  |
| Featured Badge     | Nếu product featured              |
| View Detail Button | Link sang product detail task sau |

Expected behavior:

* Nếu không có ảnh, hiển thị placeholder đẹp.
* Nếu có sale price, hiển thị sale price nổi bật và price gốc bị gạch ngang.
* Nếu product out of stock, hiển thị badge rõ ràng.
* Product card có hover effect nhẹ.
* Product card không bị vỡ layout khi tên dài.
* Card grid phải đều nhau.

---

## FR-05: Product Image Display

Product catalog cần hiển thị ảnh chính của product.

Image fallback:

* Nếu product có main image active, dùng ảnh đó.
* Nếu không có main image, dùng ảnh active đầu tiên.
* Nếu không có ảnh, dùng placeholder.
* Ảnh cần hiển thị đúng tỷ lệ.
* Không để ảnh quá lớn làm chậm page.
* Không làm layout bị lệch khi ảnh thiếu.

---

## FR-06: Language Display

Product name cần hiển thị theo language hiện tại.

Trong MVP:

* Có thể dùng default language từ system settings.
* Nếu có language parameter hoặc session language thì dùng language đó.
* Nếu không có translation theo language hiện tại, fallback về default language.
* Nếu không có default language translation, fallback về translation đầu tiên.

Áp dụng cho:

* Product name.
* Product short description nếu có hiển thị.
* Category name.
* SEO meta nếu có.

---

## FR-07: Currency Display

Product price cần hiển thị theo currency hiện tại.

Trong MVP:

* Có thể dùng default currency từ system settings.
* Nếu có currency parameter hoặc session currency thì dùng currency đó.
* Giá lưu trong database theo base currency.
* Khi hiển thị ngoài frontend, convert sang currency hiện tại.
* Format giá theo currency hiện tại.

Expected behavior:

* VND hiển thị dạng 500,000 ₫.
* USD hiển thị dạng $20.00.
* JPY hiển thị dạng ¥2,941.
* Nếu currency không hợp lệ, fallback về default currency.

---

## FR-08: Category Filter

Customer có thể filter sản phẩm theo category.

Có thể dùng URL dạng:

`/products?category=ao-nam`

hoặc:

`/category/ao-nam`

Trong task này ưu tiên cách đơn giản:

`/products?category=slug`

Expected behavior:

* Chỉ hiển thị active category.
* Category name hiển thị theo language hiện tại.
* Có thể filter theo parent category hoặc child category.
* Khi filter category, page title có thể đổi theo tên category.
* Nếu category không tồn tại, hiển thị 404 hoặc empty state phù hợp.
* Không hiển thị category inactive ngoài frontend.

---

## FR-09: Search Product

Customer có thể tìm sản phẩm bằng keyword.

Search áp dụng cho:

* Product name theo translation.
* SKU nếu cần.
* Short description nếu cần.

Expected behavior:

* Search không phân biệt hoa thường nếu database hỗ trợ.
* Search chỉ trả về product active.
* Search giữ lại filter category, price nếu có.
* Nếu không có kết quả, hiển thị empty state.
* Search input hiển thị lại keyword đã nhập.

---

## FR-10: Price Filter

Customer có thể filter sản phẩm theo khoảng giá.

Filter đề xuất:

| Filter    | Description  |
| --------- | ------------ |
| Min Price | Giá nhỏ nhất |
| Max Price | Giá lớn nhất |

Expected behavior:

* Price filter dựa trên base currency trong database.
* Nếu input không hợp lệ, bỏ qua hoặc hiển thị validation nhẹ.
* Min price không được lớn hơn max price.
* Filter giữ lại search/category/sort hiện tại.
* UI filter phải dễ dùng trên desktop và mobile.

---

## FR-11: Stock Filter

Customer có thể filter sản phẩm theo trạng thái tồn kho nếu cần.

Trạng thái:

| Status       | Description  |
| ------------ | ------------ |
| in_stock     | Còn hàng     |
| low_stock    | Sắp hết hàng |
| out_of_stock | Hết hàng     |

Trong MVP, filter này có thể đơn giản hoặc chưa cần hiển thị nếu UI quá rối.

Nhưng product card cần hiển thị stock status.

---

## FR-12: Sort Products

Customer có thể sort sản phẩm.

Các option đề xuất:

| Sort       | Description      |
| ---------- | ---------------- |
| newest     | Mới nhất         |
| price_asc  | Giá thấp đến cao |
| price_desc | Giá cao đến thấp |
| name_asc   | Tên A-Z          |
| featured   | Sản phẩm nổi bật |

Expected behavior:

* Sort giữ lại filter hiện tại.
* Default sort là newest hoặc featured.
* Sort không làm mất pagination.
* Nếu sort không hợp lệ, fallback về default sort.

---

## FR-13: Pagination

Product catalog cần có pagination.

Expected behavior:

* Mỗi page hiển thị số lượng product hợp lý.
* Pagination giữ lại query filter hiện tại.
* Pagination responsive.
* Nếu page không tồn tại, hiển thị kết quả phù hợp.
* Không load toàn bộ product nếu dữ liệu lớn.

---

## FR-14: Breadcrumb

Catalog page cần có breadcrumb.

Ví dụ:

| Page            | Breadcrumb                      |
| --------------- | ------------------------------- |
| Product list    | Home / Products                 |
| Category filter | Home / Products / Category Name |
| Search result   | Home / Products / Search        |

Breadcrumb giúp UI chuyên nghiệp và tốt hơn cho SEO.

---

## FR-15: Empty State

Nếu không có sản phẩm, cần hiển thị empty state đẹp.

Empty state gồm:

* Icon hoặc illustration đơn giản.
* Message rõ ràng.
* Gợi ý xóa filter hoặc quay lại danh sách sản phẩm.
* Button về Products nếu đang filter/search.

Không để màn hình trắng hoặc chỉ hiện text đơn điệu.

---

## FR-16: Responsive Design

UI cần responsive tốt.

Breakpoints cần quan tâm:

| Device        | Requirement                               |
| ------------- | ----------------------------------------- |
| Mobile        | 1 column product grid, filter collapsible |
| Tablet        | 2 columns product grid                    |
| Desktop       | 3 hoặc 4 columns product grid             |
| Large Desktop | 4 hoặc 5 columns nếu phù hợp              |

Expected behavior:

* Header không vỡ layout trên mobile.
* Filter sidebar có thể chuyển thành collapsible drawer trên mobile.
* Product card đều nhau.
* Font size dễ đọc.
* Button dễ bấm trên mobile.

---

## FR-17: SEO Basic

Catalog page cần SEO cơ bản.

Cần có:

| SEO Item          | Description                     |
| ----------------- | ------------------------------- |
| Page title        | Products hoặc Category Name     |
| Meta description  | Mô tả trang danh sách sản phẩm  |
| Canonical URL     | URL hiện tại nếu phù hợp        |
| Product image alt | Dùng product name hoặc alt text |
| Heading structure | H1, H2 rõ ràng                  |

Trong task này chưa cần:

* Schema.org Product list.
* Sitemap.
* Open Graph nâng cao.

---

## FR-18: Performance

Product catalog cần chú ý performance.

Yêu cầu:

* Không query N+1.
* Load product translations hợp lý.
* Load category translations hợp lý.
* Load main image hợp lý.
* Load inventory stock hợp lý.
* Pagination thay vì load toàn bộ.
* Ảnh nên dùng kích thước hiển thị hợp lý.
* Không dùng JavaScript quá nặng.
* Không thêm thư viện frontend lớn nếu không cần.

---

## 6. UI / Screen Design

## 6.1. Public Layout

Public layout nên gồm:

| Area   | Description              |
| ------ | ------------------------ |
| Header | Logo, nav, search, icons |
| Main   | Nội dung page            |
| Footer | Link, copyright, contact |

UI style mong muốn:

* Hiện đại.
* Sạch sẽ.
* Nhiều khoảng trắng hợp lý.
* Card có shadow nhẹ hoặc border mềm.
* Button bo góc đẹp.
* Badge màu rõ ràng.
* Hover effect mượt.
* Không quá nhiều màu.
* Nhìn giống website thương mại điện tử chuyên nghiệp.

---

## 6.2. Product Catalog Page Layout

Desktop layout đề xuất:

| Area          | Description                    |
| ------------- | ------------------------------ |
| Top section   | Breadcrumb, title, description |
| Toolbar       | Result count, sort dropdown    |
| Left sidebar  | Filter category, price, stock  |
| Right content | Product grid                   |
| Bottom        | Pagination                     |

Mobile layout đề xuất:

| Area          | Description               |
| ------------- | ------------------------- |
| Header        | Logo + menu icon          |
| Search        | Full width search box     |
| Filter button | Mở filter drawer/collapse |
| Product grid  | 1 column                  |
| Pagination    | Dễ bấm                    |

---

## 6.3. Product Card Design

Product card cần có:

* Image area tỷ lệ cố định.
* Badge sale nếu có.
* Badge featured nếu có.
* Product name.
* Price area.
* Stock status.
* View detail link.

Style mong muốn:

* Card bo góc.
* Shadow hoặc border nhẹ.
* Hover nâng card nhẹ.
* Image zoom nhẹ khi hover nếu phù hợp.
* Text không bị tràn.
* Giá nổi bật.
* Sale price dễ nhìn.

---

## 6.4. Filter UI

Filter sidebar gồm:

| Filter Group | Fields                                     |
| ------------ | ------------------------------------------ |
| Categories   | Danh sách category active                  |
| Price        | Min price, max price                       |
| Stock        | In stock, low stock, out of stock nếu dùng |

Filter UI cần:

* Clear filters button.
* Apply filters button nếu cần.
* Giữ trạng thái filter hiện tại.
* Không làm page rối.
* Trên mobile có thể ẩn filter vào drawer/collapse.

---

## 6.5. Search UI

Search UI cần:

* Input keyword.
* Button search.
* Placeholder dễ hiểu.
* Giữ lại keyword sau khi search.
* Có thể đặt search ở header và trong catalog toolbar.

---

## 7. Data Source

Task này sử dụng dữ liệu từ các task trước:

| Data                    | Source  |
| ----------------------- | ------- |
| Products                | Task 10 |
| Product Translations    | Task 10 |
| Product Variants        | Task 10 |
| Product Images          | Task 11 |
| Categories              | Task 09 |
| Category Translations   | Task 09 |
| Languages               | Task 06 |
| Currencies              | Task 07 |
| Tax Classes / Tax Rates | Task 08 |
| Inventory Stocks        | Task 12 |
| System Settings         | Task 05 |

---

## 8. Route Design

Các route public cần có:

| Method | URL                | Description                                  |
| ------ | ------------------ | -------------------------------------------- |
| GET    | `/`                | Homepage đơn giản hoặc redirect tới products |
| GET    | `/products`        | Public product catalog                       |
| GET    | `/category/{slug}` | Category catalog nếu muốn hỗ trợ thêm        |
| GET    | `/search`          | Search page nếu muốn tách riêng              |

Trong MVP, ưu tiên đơn giản:

| Method | URL         | Description                              |
| ------ | ----------- | ---------------------------------------- |
| GET    | `/products` | Danh sách, search, filter, sort sản phẩm |

Không yêu cầu login.

---

## 9. Query / Filter Requirements

Catalog query cần hỗ trợ:

| Query     | Description           |
| --------- | --------------------- |
| keyword   | Từ khóa tìm kiếm      |
| category  | Category slug hoặc id |
| min_price | Giá nhỏ nhất          |
| max_price | Giá lớn nhất          |
| stock     | Trạng thái tồn kho    |
| sort      | Kiểu sắp xếp          |
| page      | Trang hiện tại        |

Expected behavior:

* Query filter có thể kết hợp.
* Query không hợp lệ cần được xử lý an toàn.
* Không để lỗi kỹ thuật hiển thị cho customer.
* Pagination giữ lại query hiện tại.

---

## 10. Business Logic

## 10.1. Product Catalog Flow

* Customer mở `/products`.
* Hệ thống xác định language hiện tại.
* Hệ thống xác định currency hiện tại.
* Hệ thống lấy danh sách product active.
* Hệ thống áp dụng search/filter/sort.
* Hệ thống load translation phù hợp.
* Hệ thống load image chính.
* Hệ thống load inventory stock.
* Hệ thống convert và format price.
* Hệ thống render product grid.
* Hệ thống hiển thị pagination.

---

## 10.2. Product Visibility Flow

Product được hiển thị ngoài frontend khi:

* Product status là active.
* Category status là active.
* Product chưa bị xóa.
* Product có translation hợp lệ hoặc có fallback translation.
* Product có thể hết hàng nhưng vẫn có thể hiển thị với badge Out of Stock.

---

## 10.3. Price Display Flow

* Hệ thống lấy product price.
* Nếu có sale price hợp lệ, dùng sale price làm giá chính.
* Price gốc vẫn hiển thị dạng gạch ngang.
* Hệ thống convert giá sang currency hiện tại nếu cần.
* Hệ thống format giá theo currency.
* Nếu currency lỗi, fallback về default currency.

---

## 10.4. Stock Display Flow

* Hệ thống lấy inventory stock của product hoặc variant.
* Nếu product có variant, có thể tính tổng available quantity của variants.
* Nếu product không có variant, dùng stock của product.
* Nếu available quantity bằng 0, hiển thị Out of Stock.
* Nếu available quantity thấp hơn threshold, hiển thị Low Stock.
* Nếu available quantity đủ, hiển thị In Stock.

---

## 10.5. Translation Fallback Flow

* Ưu tiên translation theo language hiện tại.
* Nếu không có, fallback về default language.
* Nếu vẫn không có, fallback về translation đầu tiên.
* Nếu product không có translation nào hợp lệ, không hiển thị product.

---

## 10.6. Category Filter Flow

* Customer chọn category.
* Hệ thống tìm category theo slug hoặc id.
* Hệ thống chỉ lấy category active.
* Hệ thống lọc product theo category.
* Nếu category có child categories, có thể bao gồm product thuộc child categories nếu phù hợp.
* Page title hiển thị category name.

---

## 10.7. Search Flow

* Customer nhập keyword.
* Hệ thống tìm trong product translations.
* Hệ thống chỉ trả về product active.
* Hệ thống giữ lại filter khác nếu có.
* Nếu không có kết quả, hiển thị empty state.

---

## 11. Error Handling

| Case                         | Expected Handling                         |
| ---------------------------- | ----------------------------------------- |
| Không có sản phẩm            | Hiển thị empty state                      |
| Category không tồn tại       | Hiển thị 404 hoặc empty state             |
| Category inactive            | Không hiển thị sản phẩm category đó       |
| Currency không hợp lệ        | Fallback default currency                 |
| Language không hợp lệ        | Fallback default language                 |
| Product không có ảnh         | Hiển thị placeholder                      |
| Product không có translation | Fallback hoặc không hiển thị              |
| Query filter không hợp lệ    | Bỏ qua filter hoặc hiển thị thông báo nhẹ |
| Page number không hợp lệ     | Hiển thị page phù hợp                     |
| Database thiếu inventory     | Hiển thị stock fallback an toàn           |

---

## 12. Security

Yêu cầu bảo mật:

* Public page không yêu cầu login.
* Không hiển thị product inactive.
* Không hiển thị category inactive.
* Validate và sanitize query input.
* Không hiển thị lỗi kỹ thuật ra frontend.
* Escape toàn bộ nội dung dynamic khi render.
* Không để search query gây lỗi SQL.
* Không expose thông tin nội bộ như cost price.
* Không expose admin-only fields.
* Không expose inventory log.

---

## 13. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type              | Description                                          |
| ----------------- | ---------------------------------------------------- |
| Public Layout     | Layout frontend public                               |
| Public Header     | Header frontend                                      |
| Public Footer     | Footer frontend                                      |
| Controller        | Public product catalog controller                    |
| Service           | Catalog service nếu cần                              |
| View              | Product catalog page                                 |
| Partial View      | Product card, filter sidebar, pagination, breadcrumb |
| Route             | Public routes                                        |
| Helper            | Language/currency display helper nếu cần             |
| CSS/Tailwind      | Cải thiện UI hiện đại                                |
| Placeholder Asset | Placeholder image nếu cần                            |

Lưu ý:

* Không sửa admin layout.
* Không làm hỏng các màn hình admin đã có.
* Không implement Product Detail Page trong task này.
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

`http://127.0.0.1:8000/products`

Nếu có homepage:

`http://127.0.0.1:8000/`

---

## 15. Test Cases

| Test Case ID | Scenario                        | Expected Result                                                               |
| ------------ | ------------------------------- | ----------------------------------------------------------------------------- |
| TC-001       | Guest vào `/products`           | Hiển thị product catalog                                                      |
| TC-002       | Product active có translation   | Hiển thị product                                                              |
| TC-003       | Product inactive                | Không hiển thị                                                                |
| TC-004       | Category inactive               | Product thuộc category đó không hiển thị hoặc category không xuất hiện filter |
| TC-005       | Product có ảnh chính            | Hiển thị ảnh chính                                                            |
| TC-006       | Product không có ảnh            | Hiển thị placeholder                                                          |
| TC-007       | Product có sale price           | Hiển thị sale price và price gốc                                              |
| TC-008       | Product hết hàng                | Hiển thị Out of Stock                                                         |
| TC-009       | Search keyword có kết quả       | Hiển thị danh sách đúng                                                       |
| TC-010       | Search keyword không có kết quả | Hiển thị empty state đẹp                                                      |
| TC-011       | Filter category                 | Hiển thị đúng product theo category                                           |
| TC-012       | Filter price                    | Hiển thị đúng product theo khoảng giá                                         |
| TC-013       | Sort price asc                  | Product sắp xếp giá thấp đến cao                                              |
| TC-014       | Sort price desc                 | Product sắp xếp giá cao đến thấp                                              |
| TC-015       | Pagination                      | Chuyển trang đúng và giữ filter                                               |
| TC-016       | Mobile layout                   | Giao diện không vỡ trên mobile                                                |
| TC-017       | Desktop layout                  | Product grid hiển thị hiện đại, cân đối                                       |
| TC-018       | Currency fallback               | Currency lỗi thì dùng default currency                                        |
| TC-019       | Language fallback               | Translation thiếu thì fallback đúng                                           |
| TC-020       | Không expose cost price         | Frontend không hiển thị cost price                                            |

---

## 16. Acceptance Criteria

Task này được xem là hoàn thành khi:

* [ ] Có public layout riêng cho frontend.
* [ ] Có public header hiện đại.
* [ ] Có public footer đơn giản.
* [ ] Có trang `/products`.
* [ ] Product catalog UI hiện đại, đẹp, responsive.
* [ ] Có product card hiện đại.
* [ ] Hiển thị ảnh chính hoặc placeholder.
* [ ] Hiển thị product name theo language hiện tại.
* [ ] Có translation fallback.
* [ ] Hiển thị price theo currency hiện tại.
* [ ] Có currency fallback.
* [ ] Hiển thị sale price nếu có.
* [ ] Hiển thị stock status.
* [ ] Chỉ hiển thị product active.
* [ ] Chỉ hiển thị category active.
* [ ] Có search product.
* [ ] Có filter category.
* [ ] Có filter price.
* [ ] Có sort product.
* [ ] Có pagination.
* [ ] Có empty state đẹp.
* [ ] Có breadcrumb cơ bản.
* [ ] Có SEO meta cơ bản.
* [ ] Responsive tốt trên mobile/tablet/desktop.
* [ ] Không dùng Vue.js.
* [ ] Không implement Product Detail Page trong task này.
* [ ] Không implement Cart trong task này.
* [ ] Không implement Checkout trong task này.

---

## 17. UI Quality Requirements

Vì task này là frontend public đầu tiên, UI cần được làm kỹ hơn admin CRUD.

Yêu cầu UI:

* Nhìn giống website bán hàng hiện đại.
* Không dùng bảng table cho product catalog.
* Product phải hiển thị dạng grid card.
* Có spacing rõ ràng.
* Có hover effect nhẹ.
* Có badge sale, featured, stock status.
* Có filter sidebar đẹp.
* Có toolbar phía trên product grid.
* Có empty state đẹp.
* Header phải nhìn chuyên nghiệp.
* Mobile phải dễ dùng.
* Không để page quá đơn điệu.
* Không dùng style mặc định sơ sài.

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

Sau đó implement Task 13: Public Product Catalog theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 13.
* Không làm sang task khác.
* Không tự ý thay đổi thiết kế lớn.
* Tạo UI frontend public thật hiện đại, chuyên nghiệp, responsive.
* Sử dụng Blade, Tailwind CSS và Alpine.js nếu cần.
* Không dùng Vue.js trong MVP.
* Không sửa admin layout nếu không cần.
* Không implement Product Detail Page, Cart, Checkout, Order hoặc Payment.
* Sau khi làm xong, báo cáo file đã tạo/sửa và lệnh cần chạy để test.
