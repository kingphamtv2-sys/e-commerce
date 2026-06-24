# Task 22: Banner Management with Translation

## 1. Overview

Task này dùng để xây dựng chức năng quản lý banner trong admin và hiển thị banner ở public frontend.

Banner được dùng cho các khu vực như:

* Homepage hero banner.
* Homepage section banner.
* Category page banner.
* Product catalog banner.
* Sidebar banner.
* Promotional banner.
* Header/top announcement banner nếu cần.

Banner cần hỗ trợ đa ngôn ngữ để admin có thể nhập nội dung khác nhau theo từng language.

Banner cần hỗ trợ:

* Quản lý banner trong admin.
* Upload ảnh banner.
* Upload ảnh mobile nếu cần.
* Nội dung banner theo language.
* Button text theo language.
* Link URL.
* Vị trí hiển thị.
* Trạng thái active/inactive.
* Lịch hiển thị theo thời gian.
* Sort order.
* Hiển thị banner trên public frontend.

Frontend sử dụng:

* Admin: Laravel Blade + Tailwind CSS + Alpine.js nếu cần.
* Public: Laravel Blade + Tailwind CSS + Alpine.js nếu cần.
* Không dùng Vue.js.

---

## 2. Objectives

Sau khi hoàn thành Task 22, hệ thống cần có:

* Admin có thể xem danh sách banner.
* Admin có thể tạo banner.
* Admin có thể chỉnh sửa banner.
* Admin có thể xóa hoặc disable banner.
* Admin có thể upload ảnh desktop banner.
* Admin có thể upload ảnh mobile banner nếu cần.
* Admin có thể nhập nội dung banner theo từng language.
* Admin có thể chọn vị trí hiển thị banner.
* Admin có thể sắp xếp banner bằng sort order.
* Admin có thể bật/tắt banner.
* Admin có thể cấu hình thời gian bắt đầu/kết thúc hiển thị.
* Public frontend có thể hiển thị banner theo vị trí.
* Public frontend tự lấy translation theo current language.
* Public frontend fallback về default language nếu translation thiếu.
* Banner không hiển thị nếu inactive hoặc hết hạn.
* Banner UI responsive.
* Không dùng Vue.js.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong task này:

* Database design cho banners.
* Database design cho banner translations.
* Admin banner list page.
* Admin banner create page.
* Admin banner edit page.
* Admin banner delete/disable.
* Banner image upload.
* Banner mobile image upload nếu cần.
* Banner translation form theo language.
* Banner position management cơ bản.
* Banner status.
* Banner sort order.
* Banner date schedule.
* Public banner rendering.
* Banner helper/service để lấy banner theo position.
* Validation.
* Security.
* Responsive UI.
* Test cases.

### 3.2. Out of Scope

Không làm trong Task 22:

* Không làm page builder nâng cao.
* Không làm drag-and-drop visual builder.
* Không làm A/B testing.
* Không làm banner analytics.
* Không tracking impression/click nâng cao.
* Không làm campaign management phức tạp.
* Không làm personalization theo user.
* Không làm banner theo customer segment.
* Không implement Cart.
* Không implement Checkout.
* Không implement Order.
* Không implement Payment.
* Không dùng Vue.js.

---

## 4. Dependencies

Task này phụ thuộc các task trước:

| Task    | Dependency                                    |
| ------- | --------------------------------------------- |
| Task 04 | Admin Layout                                  |
| Task 06 | Language Management                           |
| Task 11 | Product Image Upload hoặc file upload pattern |
| Task 13 | Public Product Catalog                        |
| Task 14 | Product Detail Page                           |

Task này có thể hỗ trợ:

| Area            | Usage                          |
| --------------- | ------------------------------ |
| Homepage        | Hero banner / promotion banner |
| Category page   | Category top banner            |
| Product catalog | Catalog banner                 |
| Product detail  | Related promotion banner       |
| Admin dashboard | Quick access if needed         |

---

## 5. User Roles

| Role     | Permission                                |
| -------- | ----------------------------------------- |
| Admin    | Toàn quyền quản lý banner                 |
| Staff    | Có thể quản lý banner nếu được phân quyền |
| Customer | Chỉ xem banner public                     |
| Guest    | Chỉ xem banner public                     |

Business rules:

* Chỉ admin/staff được truy cập admin banner routes.
* Customer/guest không được truy cập admin banner routes.
* Public banner chỉ hiển thị banner hợp lệ.

---

## 6. Banner Positions

Banner cần có vị trí hiển thị.

### 6.1. Recommended Positions

Các position đề xuất:

| Position            | Description                          |
| ------------------- | ------------------------------------ |
| home_hero           | Banner lớn đầu trang chủ             |
| home_top            | Banner phía trên trang chủ           |
| home_middle         | Banner giữa trang chủ                |
| home_bottom         | Banner dưới trang chủ                |
| catalog_top         | Banner đầu trang catalog             |
| category_top        | Banner đầu trang category            |
| product_detail      | Banner trong trang chi tiết sản phẩm |
| sidebar             | Banner sidebar                       |
| header_announcement | Banner/announcement trên header      |

MVP có thể hỗ trợ trước:

* home_hero.
* catalog_top.
* category_top.
* sidebar.

### 6.2. Position Behavior

Business rules:

* Một position có thể có nhiều banner.
* Banner cùng position sắp xếp theo sort order.
* Nếu position chỉ nên hiển thị một banner, lấy banner active đầu tiên theo sort order.
* Nếu position có nhiều banner, frontend có thể hiển thị dạng list/grid/slider tùy UI.
* Không cần slider phức tạp trong MVP.

---

## 7. Database Design

## 7.1. banners Table

Tạo bảng `banners`.

Fields đề xuất:

| Field             | Type               | Description           |
| ----------------- | ------------------ | --------------------- |
| id                | bigint             | Primary key           |
| position          | string             | Vị trí hiển thị       |
| image_path        | nullable string    | Ảnh desktop           |
| mobile_image_path | nullable string    | Ảnh mobile            |
| link_url          | nullable string    | URL khi click         |
| link_target       | string             | same_tab hoặc new_tab |
| sort_order        | integer default 0  | Thứ tự hiển thị       |
| status            | string             | active, inactive      |
| starts_at         | nullable datetime  | Thời gian bắt đầu     |
| ends_at           | nullable datetime  | Thời gian kết thúc    |
| background_color  | nullable string    | Màu nền optional      |
| text_color        | nullable string    | Màu chữ optional      |
| button_color      | nullable string    | Màu button optional   |
| created_at        | timestamp          | Created time          |
| updated_at        | timestamp          | Updated time          |
| deleted_at        | nullable timestamp | Soft delete nếu cần   |

Business rules:

* `position` bắt buộc.
* `status` mặc định inactive hoặc active tùy business.
* `starts_at` null nghĩa là có thể hiển thị ngay.
* `ends_at` null nghĩa là không có ngày kết thúc.
* `sort_order` dùng để sắp xếp banner trong cùng position.
* `link_target` mặc định same_tab.
* Ảnh desktop nên có.
* Ảnh mobile optional.

---

## 7.2. banner_translations Table

Tạo bảng `banner_translations`.

Fields đề xuất:

| Field       | Type            | Description         |
| ----------- | --------------- | ------------------- |
| id          | bigint          | Primary key         |
| banner_id   | bigint          | Reference banners   |
| language_id | bigint          | Reference languages |
| title       | nullable string | Tiêu đề banner      |
| subtitle    | nullable string | Phụ đề              |
| description | nullable text   | Mô tả               |
| button_text | nullable string | Text button         |
| image_alt   | nullable string | Alt text cho ảnh    |
| created_at  | timestamp       | Created time        |
| updated_at  | timestamp       | Updated time        |

Business rules:

* Một banner có nhiều translations.
* Mỗi banner chỉ có một translation cho một language.
* Default language nên có title hoặc image_alt nếu banner cần accessibility.
* Nếu translation current language không có, fallback default language.
* Nếu cả current và default đều thiếu, vẫn có thể hiển thị image banner nhưng text để trống.

Unique rule:

| Rule                    | Description                 |
| ----------------------- | --------------------------- |
| banner_id + language_id | Không duplicate translation |

---

## 7.3. Optional banner_clicks Table

Không làm trong MVP, nhưng có thể chuẩn bị sau.

Fields nếu sau này cần:

| Field       | Description |
| ----------- | ----------- |
| banner_id   | Banner      |
| language_id | Language    |
| clicked_at  | Time        |
| ip_address  | Optional    |
| user_agent  | Optional    |

Task 22 không implement tracking.

---

## 8. Relationships

Relationships cần có:

| Model             | Relationship              |
| ----------------- | ------------------------- |
| Banner            | hasMany BannerTranslation |
| BannerTranslation | belongsTo Banner          |
| BannerTranslation | belongsTo Language        |
| Language          | hasMany BannerTranslation |

---

## 9. Admin Banner List

URL đề xuất:

`/admin/banners`

Danh sách banner cần hiển thị:

| Column   | Description                 |
| -------- | --------------------------- |
| Image    | Thumbnail                   |
| Title    | Title theo default language |
| Position | Vị trí hiển thị             |
| Status   | Active/inactive badge       |
| Schedule | starts_at / ends_at         |
| Sort     | Sort order                  |
| Updated  | Last updated                |
| Actions  | Edit, More                  |

### 9.1. Search

Admin có thể search theo:

* Title.
* Subtitle.
* Position.
* Link URL.

### 9.2. Filters

Filters đề xuất:

| Filter                | Values                         |
| --------------------- | ------------------------------ |
| Position              | Banner positions               |
| Status                | active/inactive                |
| Language completeness | Missing translation optional   |
| Schedule              | active now, scheduled, expired |

### 9.3. Actions

Actions trong list:

| Action    | Description              |
| --------- | ------------------------ |
| Edit      | Chỉnh sửa banner         |
| Preview   | Optional                 |
| Duplicate | Optional                 |
| Disable   | Tắt banner               |
| Delete    | Xóa banner nếu được phép |

Danger action như Delete phải nằm trong More dropdown và dùng custom confirmation modal.

Không dùng browser confirm mặc định.

---

## 10. Admin Banner Create

URL đề xuất:

`/admin/banners/create`

Create form gồm các section:

| Section           | Description                                                    |
| ----------------- | -------------------------------------------------------------- |
| Basic Information | Position, status, sort order                                   |
| Images            | Desktop image, mobile image                                    |
| Link              | Link URL, link target                                          |
| Schedule          | starts_at, ends_at                                             |
| Design Options    | Background/text/button color optional                          |
| Translations      | Title/subtitle/description/button text/image alt theo language |

### 10.1. Create Behavior

Expected behavior:

* Admin nhập thông tin banner.
* Admin upload image.
* Admin nhập translation theo default language.
* Admin nhấn Save.
* Nếu thành công, redirect về edit page hoặc banner list.
* Nếu lỗi, giữ lại dữ liệu đã nhập.
* Validation error hiển thị rõ.

Khuyến nghị:

* Sau khi tạo thành công, redirect về edit page của banner vừa tạo.
* Điều này giúp admin tiếp tục chỉnh translations/images dễ hơn.

---

## 11. Admin Banner Edit

URL đề xuất:

`/admin/banners/{banner}/edit`

Edit form cần cho phép:

* Cập nhật position.
* Cập nhật status.
* Cập nhật sort order.
* Cập nhật image.
* Cập nhật mobile image.
* Cập nhật link URL.
* Cập nhật link target.
* Cập nhật schedule.
* Cập nhật translations.
* Xóa/replace image nếu cần.

### 11.1. Translation UI

Translation UI nên theo tab language.

Ví dụ:

| Tab        | Fields                                               |
| ---------- | ---------------------------------------------------- |
| English    | title, subtitle, description, button_text, image_alt |
| Vietnamese | title, subtitle, description, button_text, image_alt |
| Japanese   | title, subtitle, description, button_text, image_alt |

Business rules:

* Default language tab nên hiển thị đầu tiên.
* Tab có lỗi validation cần badge error.
* Tab thiếu translation có thể hiển thị badge warning.
* Không mất dữ liệu khi chuyển tab.

---

## 12. Image Upload Rules

Banner cần hỗ trợ upload ảnh.

### 12.1. Desktop Image

Desktop image dùng cho màn hình lớn.

Yêu cầu:

* Image field có preview.
* Có thể replace image.
* Có thể remove image nếu business cho phép.
* File lưu trong Laravel public storage.
* Path lưu vào `image_path`.

### 12.2. Mobile Image

Mobile image optional.

Yêu cầu:

* Nếu có mobile image, public frontend dùng mobile image trên màn hình nhỏ.
* Nếu không có mobile image, fallback desktop image.
* Path lưu vào `mobile_image_path`.

### 12.3. Validation

Rules đề xuất:

| Field        | Rule                                |
| ------------ | ----------------------------------- |
| image        | Nullable hoặc required tùy business |
| mobile_image | Nullable image                      |
| image type   | jpg, jpeg, png, webp                |
| file size    | Giới hạn hợp lý                     |
| dimensions   | Optional                            |

### 12.4. Storage

Business rules:

* Ảnh banner lưu trong `storage/app/public/banners`.
* Public URL dùng storage link.
* Khi replace image, có thể xóa file cũ nếu không dùng nữa.
* Không upload file nguy hiểm.
* Không tin MIME từ frontend.

---

## 13. Banner Schedule Rules

Banner có thể hiển thị theo thời gian.

Fields:

* starts_at.
* ends_at.

Rules:

| Case                              | Display                  |
| --------------------------------- | ------------------------ |
| starts_at null, ends_at null      | Luôn hiển thị nếu active |
| starts_at <= now, ends_at null    | Hiển thị                 |
| starts_at null, ends_at >= now    | Hiển thị                 |
| now between starts_at and ends_at | Hiển thị                 |
| now < starts_at                   | Chưa hiển thị            |
| now > ends_at                     | Hết hạn, không hiển thị  |

Validation:

* ends_at phải sau starts_at nếu cả hai có giá trị.
* Timezone dùng theo app timezone.

---

## 14. Banner Status Rules

Status đề xuất:

| Status   | Description                         |
| -------- | ----------------------------------- |
| active   | Có thể hiển thị nếu schedule hợp lệ |
| inactive | Không hiển thị                      |

Business rules:

* Banner inactive không hiển thị public.
* Banner active nhưng chưa tới starts_at không hiển thị.
* Banner active nhưng đã qua ends_at không hiển thị.
* Admin list vẫn hiển thị tất cả status.

---

## 15. Link Rules

Banner có thể có link.

Fields:

| Field       | Description           |
| ----------- | --------------------- |
| link_url    | URL khi click         |
| link_target | same_tab hoặc new_tab |

Business rules:

* link_url optional.
* Nếu link_url trống, banner không clickable.
* Nếu link_target là new_tab, public link mở tab mới.
* Validate link_url là URL hợp lệ hoặc path nội bộ.
* Không cho JavaScript URL nguy hiểm.
* External link nên xử lý an toàn.

---

## 16. Public Banner Rendering

Public frontend cần có cách render banner theo position.

### 16.1. Banner Service

Nên có service/helper lấy banner:

* Theo position.
* Theo current language.
* Theo active status.
* Theo schedule.
* Theo sort order.
* Có fallback translation.

### 16.2. Display Rules

Khi render public:

* Chỉ lấy banner active.
* Chỉ lấy banner trong schedule.
* Sắp xếp theo sort_order.
* Dùng current language translation.
* Fallback default language.
* Nếu không có text, vẫn hiển thị image.
* Nếu không có image và không có content, không render.

### 16.3. Banner Image Responsive

Public frontend cần dùng:

| Screen   | Image                                |
| -------- | ------------------------------------ |
| Mobile   | mobile_image_path nếu có             |
| Desktop  | image_path                           |
| Fallback | image_path nếu mobile image không có |

### 16.4. Public Positions

Các vị trí cần render ở MVP:

| Position     | Page                  |
| ------------ | --------------------- |
| home_hero    | Home page nếu có      |
| catalog_top  | Product catalog       |
| category_top | Category page nếu có  |
| sidebar      | Sidebar nếu layout có |

Nếu homepage chưa có task riêng, vẫn chuẩn bị component/partial để sau này dùng.

---

## 17. Public Banner UI

Banner UI cần hiện đại và responsive.

### 17.1. Hero Banner

Hero banner có thể hiển thị:

* Background image.
* Title.
* Subtitle.
* Description.
* Button.
* Link.

### 17.2. Promo Banner

Promo banner có thể hiển thị:

* Image.
* Short title.
* Button.
* Link.

### 17.3. Accessibility

Yêu cầu:

* Image có alt text.
* Button text rõ ràng.
* Link có aria-label nếu cần.
* Text đủ tương phản nếu dùng overlay.
* Không chỉ dùng ảnh nếu cần nội dung SEO/accessibility.

---

## 18. Admin UI / UX Requirements

Admin banner UI cần:

* Gọn gàng.
* Dễ upload ảnh.
* Có preview ảnh.
* Có tab translation theo language.
* Có badge warning nếu thiếu translation.
* Có status badge.
* Có schedule status: active now, scheduled, expired.
* Có action More dropdown.
* Delete dùng custom modal.
* Không dùng browser confirm mặc định.
* Mobile admin layout không vỡ.

---

## 19. Delete / Disable Rules

Banner có thể delete hoặc disable.

Business rules:

| Case                                   | Behavior            |
| -------------------------------------- | ------------------- |
| Banner chưa dùng hoặc không quan trọng | Có thể soft delete  |
| Banner đang active                     | Nên confirmation rõ |
| Banner muốn tạm ẩn                     | Dùng inactive       |
| Banner có tracking sau này             | Không hard delete   |

Delete UX:

* Dùng custom confirmation modal.
* Không dùng browser confirm.
* Nếu delete bằng AJAX, không reload page.
* Sau delete thành công, remove row khỏi UI.
* Nếu lỗi, modal không đóng và hiển thị lỗi.

---

## 20. Validation Rules

### 20.1. Banner Validation

| Field        | Rule                                      |
| ------------ | ----------------------------------------- |
| position     | Required, valid position                  |
| image        | Nullable image                            |
| mobile_image | Nullable image                            |
| link_url     | Nullable, safe URL/path                   |
| link_target  | Required, same_tab hoặc new_tab           |
| sort_order   | Nullable integer                          |
| status       | Required, active/inactive                 |
| starts_at    | Nullable datetime                         |
| ends_at      | Nullable datetime, after starts_at nếu có |
| colors       | Nullable, valid color format nếu có       |

### 20.2. Translation Validation

| Field       | Rule                 |
| ----------- | -------------------- |
| title       | Nullable, max length |
| subtitle    | Nullable, max length |
| description | Nullable             |
| button_text | Nullable, max length |
| image_alt   | Nullable, max length |
| language_id | Required, exists     |

Default language rule:

* Nên yêu cầu ít nhất image hoặc title cho default language.
* Nếu banner không có image, title nên required.
* Nếu banner có image, title optional nhưng image_alt khuyến nghị có.

---

## 21. Security Requirements

Yêu cầu bảo mật:

* Admin routes phải có admin/auth middleware.
* CSRF cho create/update/delete.
* Validate upload file.
* Không cho upload file nguy hiểm.
* Không expose storage path nội bộ.
* Không cho JavaScript URL trong link_url.
* Không cho customer/guest truy cập admin banner.
* Public chỉ render banner active/scheduled valid.
* Không expose lỗi kỹ thuật ra UI.

---

## 22. Performance Requirements

* Public banner query cần tối ưu.
* Eager load translations theo current/default language nếu cần.
* Không query banner lặp nhiều lần trong một request.
* Có thể cache banner theo position/language nếu cần.
* Khi admin update banner, cache cần clear nếu có cache.
* Banner image cần tối ưu kích thước thực tế.
* Không load ảnh quá lớn không cần thiết trên mobile.

---

## 23. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type               | Description                                             |
| ------------------ | ------------------------------------------------------- |
| Migration          | banners, banner_translations                            |
| Models             | Banner, BannerTranslation                               |
| Controller Admin   | AdminBannerController                                   |
| Service            | BannerService                                           |
| Request Validation | StoreBannerRequest, UpdateBannerRequest                 |
| Routes             | Admin banner routes, public banner route/helper nếu cần |
| Blade Admin        | Banner list/create/edit                                 |
| Blade Public       | Banner partial/component                                |
| Blade Partials     | Banner row, banner form, translation tabs               |
| Storage            | Banner image upload path                                |
| JavaScript         | Image preview, delete modal, AJAX delete nếu có         |
| Tests              | Banner feature tests                                    |

---

## 24. Route Design

### 24.1. Admin Routes

| Method    | URL                            | Name                  | Purpose                |
| --------- | ------------------------------ | --------------------- | ---------------------- |
| GET       | /admin/banners                 | admin.banners.index   | Banner list            |
| GET       | /admin/banners/create          | admin.banners.create  | Create form            |
| POST      | /admin/banners                 | admin.banners.store   | Store banner           |
| GET       | /admin/banners/{banner}/edit   | admin.banners.edit    | Edit form              |
| PUT/PATCH | /admin/banners/{banner}        | admin.banners.update  | Update banner          |
| DELETE    | /admin/banners/{banner}        | admin.banners.destroy | Delete banner          |
| PATCH     | /admin/banners/{banner}/status | admin.banners.status  | Update status optional |

### 24.2. Public Usage

Public banner có thể dùng:

* Blade component.
* View partial.
* Service call from controller.
* View composer nếu phù hợp.

Không nhất thiết cần public route riêng.

---

## 25. Error Handling

| Scenario                  | Expected Result               |
| ------------------------- | ----------------------------- |
| Upload invalid file       | Show validation error         |
| Upload too large          | Show validation error         |
| Missing position          | Show validation error         |
| Invalid date range        | Show validation error         |
| Invalid link URL          | Show validation error         |
| Missing translation       | Show warning/error tùy rule   |
| Banner not found          | 404                           |
| Unauthorized admin access | Forbidden                     |
| Public no banner          | Không render hoặc empty state |
| Delete failed             | Show error in modal           |

---

## 26. Test Cases

| Test Case ID | Scenario                           | Expected Result                  |
| ------------ | ---------------------------------- | -------------------------------- |
| TC-001       | Admin mở banner list               | Hiển thị danh sách banner        |
| TC-002       | Admin tạo banner với image         | Banner được tạo                  |
| TC-003       | Admin tạo banner với translations  | Translations được lưu            |
| TC-004       | Admin tạo banner thiếu position    | Hiển thị lỗi                     |
| TC-005       | Admin upload file không phải image | Hiển thị lỗi                     |
| TC-006       | Admin upload mobile image          | Mobile image được lưu            |
| TC-007       | Admin edit banner                  | Banner được cập nhật             |
| TC-008       | Admin edit translation             | Translation được cập nhật        |
| TC-009       | Duplicate translation language     | Không duplicate                  |
| TC-010       | Banner inactive                    | Không hiển thị public            |
| TC-011       | Banner active trong schedule       | Hiển thị public                  |
| TC-012       | Banner chưa tới starts_at          | Không hiển thị                   |
| TC-013       | Banner đã qua ends_at              | Không hiển thị                   |
| TC-014       | Current language có translation    | Hiển thị đúng translation        |
| TC-015       | Current language thiếu translation | Fallback default language        |
| TC-016       | Banner position catalog_top        | Hiển thị ở catalog nếu implement |
| TC-017       | Mobile có mobile image             | Hiển thị mobile image            |
| TC-018       | Mobile không có mobile image       | Fallback desktop image           |
| TC-019       | Delete banner                      | Banner bị xóa/soft delete        |
| TC-020       | Delete dùng modal                  | Không dùng browser confirm       |
| TC-021       | Customer truy cập admin banner     | Bị chặn                          |
| TC-022       | Public không có banner             | Page vẫn load bình thường        |
| TC-023       | Banner sort order                  | Hiển thị đúng thứ tự             |
| TC-024       | Banner link same tab               | Link đúng                        |
| TC-025       | Banner link new tab                | Link đúng target                 |
| TC-026       | Mobile admin banner page           | Layout không vỡ                  |

---

## 27. Acceptance Criteria

Task này hoàn thành khi:

* [ ] Có bảng `banners`.
* [ ] Có bảng `banner_translations`.
* [ ] Có model Banner.
* [ ] Có model BannerTranslation.
* [ ] Admin có thể xem danh sách banner.
* [ ] Admin có thể tạo banner.
* [ ] Admin có thể chỉnh sửa banner.
* [ ] Admin có thể delete hoặc disable banner.
* [ ] Admin có thể upload desktop image.
* [ ] Admin có thể upload mobile image nếu cần.
* [ ] Admin có thể chọn banner position.
* [ ] Admin có thể nhập translations theo language.
* [ ] Translation fallback về default language hoạt động.
* [ ] Banner có status active/inactive.
* [ ] Banner có starts_at/ends_at.
* [ ] Banner chỉ hiển thị public khi active và schedule hợp lệ.
* [ ] Banner sort order hoạt động.
* [ ] Banner link URL hoạt động.
* [ ] Banner link target hoạt động.
* [ ] Public frontend có thể render banner theo position.
* [ ] Public banner responsive.
* [ ] Image alt text lấy từ translation.
* [ ] Delete dùng custom confirmation modal.
* [ ] Không dùng browser confirm mặc định.
* [ ] Admin routes được bảo vệ bằng admin/auth middleware.
* [ ] Customer/guest không truy cập được admin banner.
* [ ] Không implement page builder nâng cao.
* [ ] Không implement tracking/analytics.
* [ ] Không dùng Vue.js.

---

## 28. Commands

Sau khi implement, chạy các lệnh:

| Command                  | Purpose                          |
| ------------------------ | -------------------------------- |
| php artisan migrate      | Chạy migration                   |
| php artisan route:list   | Kiểm tra route                   |
| php artisan test         | Chạy test nếu có                 |
| npm run build            | Build frontend assets            |
| php artisan storage:link | Đảm bảo public storage hoạt động |
| php artisan serve        | Chạy local server                |

URL test:

`http://127.0.0.1:8000/admin/banners`

Public test tùy vị trí được render:

`http://127.0.0.1:8000/`

`http://127.0.0.1:8000/products`

---

## 29. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-04-admin-layout.md
* docs/tasks/task-06-language-management.md
* docs/tasks/task-11-product-image-upload.md
* docs/tasks/task-13-public-product-catalog.md
* docs/tasks/task-14-product-detail-page.md
* docs/tasks/task-22-banner-management-with-translation.md

Sau đó implement Task 22: Banner Management with Translation theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 22.
* Tạo quản lý banner trong admin.
* Tạo bảng banners và banner_translations nếu chưa có.
* Banner hỗ trợ position, image, mobile image, link_url, link_target, sort_order, status, starts_at, ends_at.
* Banner hỗ trợ translation theo language: title, subtitle, description, button_text, image_alt.
* Admin có thể list/create/edit/delete banner.
* Admin có thể upload và preview banner image.
* Admin có thể nhập translation theo từng language.
* Current language thiếu translation thì fallback default language.
* Public frontend có thể render banner theo position.
* Public chỉ hiển thị banner active và trong schedule.
* Banner sort order phải hoạt động.
* Delete phải dùng custom confirmation modal, không dùng browser confirm mặc định.
* Admin routes phải được bảo vệ bởi admin/auth middleware.
* Customer/guest không được truy cập admin banner routes.
* Dùng Blade + Tailwind CSS + Alpine.js hoặc Fetch API nếu cần.
* Không dùng Vue.js.
* Không implement page builder nâng cao.
* Không implement tracking/analytics.
* Không implement Cart, Checkout, Order hoặc Payment trong task này.
* Sau khi làm xong, báo cáo file đã tạo/sửa và lệnh cần chạy để test.