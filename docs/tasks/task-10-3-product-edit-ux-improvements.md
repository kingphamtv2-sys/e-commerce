# Task 10.3: Product Edit UX Improvements

## 1. Overview

Task này dùng để cải thiện trải nghiệm sử dụng màn hình tạo và chỉnh sửa sản phẩm.

Mục tiêu là giúp admin thao tác nhanh hơn, ít bị reload page, tránh mất dữ liệu và tạo cảm giác hiện đại hơn.

Các phần cần cải thiện:

* Sau khi tạo mới product thành công, redirect về màn hình edit của product vừa tạo.
* Tạo variant bằng JavaScript, không reload page.
* Upload hình ảnh bằng JavaScript, không reload page.
* Thêm giá trị cho tùy chọn sản phẩm bằng JavaScript, không reload page.
* Hiển thị loading overlay khi xử lý các thao tác bằng JavaScript.

Frontend trong MVP sử dụng:

* Blade
* Tailwind CSS
* Alpine.js nếu cần
* Fetch API
* Không dùng Vue.js

---

## 2. Objectives

Sau khi hoàn thành task này, hệ thống cần có:

* Product create flow hợp lý hơn.
* Sau khi admin tạo product mới, hệ thống tự chuyển sang product edit page.
* Admin có thể tạo variant mà không reload trang.
* Admin có thể upload product images hoặc variant images mà không reload trang.
* Admin có thể thêm option value mà không reload trang.
* Khi xử lý bằng JavaScript, màn hình hiển thị loading overlay.
* Khi xử lý thành công, UI cập nhật ngay trên màn hình.
* Khi xử lý lỗi, lỗi hiển thị ngay tại khu vực liên quan.
* Không làm mất dữ liệu các phần khác đang nhập trên form.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong task này:

* Cập nhật behavior sau khi tạo mới product.
* Cập nhật nút `Save Changes` khi tạo mới product.
* Cập nhật nút `Create Variant` hoặc `Tạo biến thể` để xử lý bằng JavaScript.
* Cập nhật upload product images bằng JavaScript.
* Cập nhật upload variant images bằng JavaScript nếu variant image đã có.
* Cập nhật nút `Add Value` hoặc `Thêm giá trị` trong product options bằng JavaScript.
* Thêm loading overlay dùng chung cho màn hình product create/edit.
* Trả response JSON cho các request JavaScript.
* Cập nhật UI sau khi thao tác thành công.
* Hiển thị lỗi validation không reload page.

### 3.2. Out of Scope

Các phần chưa làm trong task này:

* Không viết lại toàn bộ Product Management.
* Không đổi database design nếu không cần.
* Không implement Cart.
* Không implement Checkout.
* Không implement Order.
* Không dùng Vue.js.
* Không làm realtime websocket.
* Không làm drag and drop advanced upload.
* Không làm autosave toàn bộ form theo thời gian thực.

---

## 4. Requirement 1: Redirect To Edit Page After Create

Khi admin tạo product mới và nhấn nút `Save Changes`, hệ thống không redirect về product list.

Expected behavior:

* Admin mở màn hình create product.
* Admin nhập thông tin product.
* Admin nhấn `Save Changes`.
* Nếu tạo product thành công, hệ thống redirect đến màn hình edit của product vừa tạo.
* Không redirect về product list.

Ví dụ flow:

| Step                               | Expected Result                              |
| ---------------------------------- | -------------------------------------------- |
| Admin vào `/admin/products/create` | Hiển thị form tạo product                    |
| Admin nhập dữ liệu                 | Dữ liệu hợp lệ                               |
| Admin nhấn Save Changes            | Tạo product                                  |
| Tạo thành công                     | Redirect đến `/admin/products/{id}/edit`     |
| Tạo thất bại                       | Hiển thị lỗi validation, giữ dữ liệu đã nhập |

Lý do:

* Sau khi tạo product, admin thường cần tiếp tục thêm:

  * Images
  * Options
  * Option values
  * Variant combinations
  * Variant images
  * Inventory

Vì vậy redirect về edit page sẽ hợp lý hơn redirect về list.

---

## 5. Requirement 2: Create Variant By JavaScript

Nút `Tạo biến thể` hoặc `Create Variant` cần xử lý bằng JavaScript.

Expected behavior:

* Admin chọn option values.
* Admin nhập SKU, price, sale price, status.
* Admin nhấn `Tạo biến thể`.
* Hệ thống hiển thị loading overlay.
* Request được gửi bằng JavaScript.
* Trang không reload.
* Nếu thành công:

  * Variant mới xuất hiện ngay trong Variant Combinations table.
  * Form tạo variant được reset nếu phù hợp.
  * Loading overlay biến mất.
  * Hiển thị thông báo thành công.
* Nếu thất bại:

  * Trang không reload.
  * Loading overlay biến mất.
  * Lỗi validation hiển thị tại form tạo variant.
  * Dữ liệu admin đã nhập không bị mất.

Validation cần xử lý:

| Case                     | Expected Result               |
| ------------------------ | ----------------------------- |
| SKU trống                | Hiển thị lỗi tại field SKU    |
| SKU trùng                | Hiển thị lỗi tại field SKU    |
| Thiếu option value       | Hiển thị lỗi tại variant form |
| Duplicate combination    | Hiển thị lỗi rõ ràng          |
| Sale price lớn hơn price | Hiển thị lỗi validation       |
| Product không tồn tại    | Hiển thị lỗi phù hợp          |

---

## 6. Requirement 3: Upload Images By JavaScript

Upload hình ảnh trong màn hình product cần xử lý bằng JavaScript, không reload page.

Áp dụng cho:

| Image Type     | Description                               |
| -------------- | ----------------------------------------- |
| Product Images | Ảnh chung của sản phẩm                    |
| Variant Images | Ảnh riêng của variant nếu Task 10.2 đã có |

Expected behavior:

* Admin chọn file ảnh.
* Admin nhấn upload hoặc file auto upload nếu UI hỗ trợ.
* Hệ thống hiển thị loading overlay.
* Request upload được gửi bằng JavaScript.
* Trang không reload.
* Nếu upload thành công:

  * Ảnh mới xuất hiện ngay trong gallery/list.
  * Nếu là ảnh đầu tiên, có thể tự set main image.
  * Loading overlay biến mất.
  * Hiển thị thông báo thành công.
* Nếu upload thất bại:

  * Trang không reload.
  * Loading overlay biến mất.
  * Hiển thị lỗi validation.
  * Không làm mất các ảnh cũ.

Validation cần xử lý:

| Case                | Expected Result         |
| ------------------- | ----------------------- |
| File không phải ảnh | Hiển thị lỗi validation |
| File quá lớn        | Hiển thị lỗi validation |
| Không chọn file     | Hiển thị lỗi validation |
| Storage lỗi         | Hiển thị lỗi phù hợp    |
| User không có quyền | Chặn thao tác           |
| CSRF lỗi            | Hiển thị lỗi phù hợp    |

---

## 7. Requirement 4: Add Option Value By JavaScript

Nút `Thêm giá trị` trong tùy chọn sản phẩm cần xử lý bằng JavaScript.

Ví dụ:

| Option  | Values             |
| ------- | ------------------ |
| Color   | Black, White, Blue |
| Size    | S, M, L, XL        |
| Storage | 128GB, 256GB       |

Expected behavior:

* Admin nhập value mới.
* Admin nhấn `Thêm giá trị`.
* Hệ thống hiển thị loading overlay.
* Request được gửi bằng JavaScript.
* Trang không reload.
* Nếu thành công:

  * Value mới xuất hiện ngay trong option value list.
  * Value mới có thể được dùng ngay trong form tạo variant.
  * Input được reset nếu phù hợp.
  * Loading overlay biến mất.
  * Hiển thị thông báo thành công.
* Nếu thất bại:

  * Trang không reload.
  * Loading overlay biến mất.
  * Hiển thị lỗi tại khu vực option value.
  * Dữ liệu admin đã nhập không bị mất.

Validation cần xử lý:

| Case                           | Expected Result |
| ------------------------------ | --------------- |
| Value trống                    | Hiển thị lỗi    |
| Value trùng trong cùng option  | Hiển thị lỗi    |
| Option không tồn tại           | Hiển thị lỗi    |
| Color code không hợp lệ nếu có | Hiển thị lỗi    |
| User không có quyền            | Chặn thao tác   |

---

## 8. Loading Overlay Requirement

Khi xử lý thao tác bằng JavaScript, cần hiển thị loading overlay.

Áp dụng cho:

* Tạo variant.
* Upload product image.
* Upload variant image.
* Thêm option value.
* Lưu option riêng nếu có.
* Lưu variant riêng nếu có.
* Set main image nếu xử lý bằng JavaScript.
* Delete image nếu xử lý bằng JavaScript.

Loading overlay cần có:

| Element              | Description                                   |
| -------------------- | --------------------------------------------- |
| Overlay background   | Che nhẹ màn hình hoặc section đang xử lý      |
| Spinner              | Hiển thị đang xử lý                           |
| Text                 | Ví dụ: Processing..., Saving..., Uploading... |
| Disabled interaction | Tránh admin bấm nhiều lần                     |

Expected behavior:

* Overlay hiển thị ngay khi bắt đầu request.
* Overlay tắt khi request thành công hoặc thất bại.
* Nếu request lỗi, overlay vẫn phải tắt.
* Không để overlay bị treo vĩnh viễn.
* Có thể dùng overlay toàn màn hình hoặc overlay theo section.
* MVP ưu tiên dùng overlay toàn màn hình để dễ thống nhất.

### 8.1. Smooth Overlay Behavior

* Overlay phải fade in và fade out bằng CSS transition, không bật/tắt đột ngột.
* Request hoàn thành rất nhanh không được làm overlay nhấp nháy; có thể dùng một khoảng delay ngắn trước khi hiển thị.
* Khi overlay đã hiển thị, cần giữ một khoảng thời gian tối thiểu hợp lý để animation không bị giựt.
* Overlay luôn phải đóng trong nhánh hoàn tất của request, kể cả request thành công, validation lỗi, server lỗi hoặc lỗi kết nối.

---

## 8.2. Add Product Option By JavaScript

Nút `Thêm tùy chọn` trong Product Options phải gửi request bằng Fetch API và không reload trang.

Expected behavior:

* Option mới xuất hiện ngay trong Product Options list bằng Blade partial.
* Khu vực thêm option value của option mới xuất hiện ngay và dùng được ngay.
* Nếu option mới active, Variant Combination form tự thêm selector tương ứng.
* Nếu product chưa có option, Variant Combination form được kích hoạt ngay sau khi thêm option active đầu tiên.
* Khi thêm value active, value mới xuất hiện ngay trong selector tạo variant của đúng option.
* Admin có thể thêm option, thêm value rồi tạo variant liên tục mà không reload trang.
* Validation duplicate option, duplicate value và duplicate variant combination vẫn được kiểm tra ở backend.

---

## 9. API / Response Requirement

Các thao tác JavaScript cần nhận response rõ ràng.

Response thành công nên có:

| Field   | Description                                      |
| ------- | ------------------------------------------------ |
| success | true                                             |
| message | Message thành công                               |
| data    | Dữ liệu mới cần update UI                        |
| html    | HTML partial nếu dùng render server-side partial |

Response lỗi validation nên có:

| Field   | Description              |
| ------- | ------------------------ |
| success | false                    |
| message | Message lỗi              |
| errors  | Danh sách lỗi theo field |

Business rules:

* Request bình thường vẫn có thể hoạt động nếu không phải AJAX, nếu cần backward compatibility.
* Request AJAX cần trả JSON.
* Không redirect khi request AJAX thành công.
* Không trả HTML lỗi Laravel mặc định cho request AJAX.
* CSRF phải được xử lý đúng.

---

## 10. UI Update Requirement

Sau khi thao tác JavaScript thành công, UI cần cập nhật ngay.

| Action               | UI Update                                                     |
| -------------------- | ------------------------------------------------------------- |
| Create Variant       | Thêm row mới vào Variant Combinations table                   |
| Add Option Value     | Thêm chip/value mới vào option value list và variant selector |
| Upload Product Image | Thêm thumbnail mới vào product image gallery                  |
| Upload Variant Image | Thêm thumbnail mới vào variant image gallery                  |
| Set Main Image       | Cập nhật badge main image                                     |
| Delete Image         | Xóa thumbnail khỏi UI                                         |

Không bắt admin reload page để thấy dữ liệu mới.

---

## 11. Duplicate Submit Prevention

Khi đang xử lý JavaScript request:

* Disable nút đang xử lý.
* Hiển thị loading overlay.
* Không cho submit trùng request.
* Nếu admin bấm nhiều lần, không tạo duplicate variant.
* Nếu admin upload nhiều lần, không tạo duplicate ngoài ý muốn.
* Backend vẫn phải validate duplicate, không chỉ dựa vào frontend.

---

## 12. Error Handling

| Case                   | Expected Handling                           |
| ---------------------- | ------------------------------------------- |
| Request timeout        | Tắt loading overlay và hiển thị lỗi         |
| Validation error       | Hiển thị lỗi tại form tương ứng             |
| Server error           | Hiển thị message lỗi chung                  |
| CSRF expired           | Hiển thị thông báo reload/login lại nếu cần |
| Network error          | Hiển thị lỗi kết nối                        |
| Duplicate variant      | Không cho tạo, hiển thị lỗi                 |
| Duplicate option value | Không cho tạo, hiển thị lỗi                 |
| Upload image lỗi       | Không làm mất ảnh cũ                        |
| User bấm nhiều lần     | Không tạo duplicate                         |

---

## 13. Security

Yêu cầu bảo mật:

* Tất cả request JavaScript phải có CSRF token.
* Chỉ admin mới được thao tác.
* Validate toàn bộ input ở backend.
* Không tin tưởng dữ liệu từ JavaScript.
* Không upload file nguy hiểm.
* Không expose lỗi kỹ thuật chi tiết ra UI.
* Không cho tạo option/value/variant không thuộc product hiện tại.
* Không cho upload image vào variant/product không thuộc quyền thao tác.

---

## 14. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type                       | Description                                     |
| -------------------------- | ----------------------------------------------- |
| Product Controller         | Cập nhật redirect sau create                    |
| Product Option Controller  | Hỗ trợ AJAX add option value nếu cần            |
| Product Variant Controller | Hỗ trợ AJAX create variant                      |
| Product Image Controller   | Hỗ trợ AJAX upload image                        |
| Variant Image Controller   | Hỗ trợ AJAX upload variant image nếu có         |
| Request Validation         | Validate AJAX và form thường                    |
| Blade Views                | Cập nhật product create/edit UI                 |
| Partial Views              | Row variant, option value chip, image thumbnail |
| JavaScript                 | Fetch API hoặc Alpine.js handlers               |
| Loading Overlay            | Component dùng chung                            |
| Routes                     | Đảm bảo route hỗ trợ AJAX                       |

Lưu ý:

* Không dùng Vue.js.
* Không làm lại toàn bộ UI.
* Không phá Product Create/Edit hiện tại.
* Không implement Cart trong task này.
* Không implement Checkout trong task này.
* Không implement Order trong task này.

---

## 15. Test Cases

| Test Case ID | Scenario                           | Expected Result                       |
| ------------ | ---------------------------------- | ------------------------------------- |
| TC-001       | Tạo product mới thành công         | Redirect về product edit page         |
| TC-002       | Tạo product mới bị validation lỗi  | Giữ form create và hiển thị lỗi       |
| TC-003       | Nhấn Tạo biến thể                  | Không reload page                     |
| TC-004       | Tạo biến thể thành công            | Variant xuất hiện ngay trong table    |
| TC-005       | Tạo biến thể SKU trùng             | Hiển thị lỗi, không reload            |
| TC-006       | Tạo biến thể duplicate combination | Hiển thị lỗi, không reload            |
| TC-007       | Upload product image               | Không reload page, ảnh xuất hiện ngay |
| TC-008       | Upload variant image               | Không reload page, ảnh xuất hiện ngay |
| TC-009       | Upload file không hợp lệ           | Hiển thị lỗi, không reload            |
| TC-010       | Nhấn Thêm giá trị option           | Không reload page                     |
| TC-011       | Thêm giá trị thành công            | Value xuất hiện ngay trong list       |
| TC-012       | Thêm giá trị trùng                 | Hiển thị lỗi, không reload            |
| TC-013       | JS request đang xử lý              | Loading overlay hiển thị              |
| TC-014       | JS request hoàn tất                | Loading overlay biến mất              |
| TC-015       | JS request lỗi server              | Overlay tắt và hiển thị lỗi           |
| TC-016       | Admin bấm nhiều lần                | Không tạo dữ liệu duplicate           |
| TC-017       | Customer gọi AJAX route            | Bị chặn                               |
| TC-018       | CSRF token thiếu                   | Request bị từ chối an toàn            |

---

## 16. Acceptance Criteria

Task này được xem là hoàn thành khi:

* [ ] Sau khi tạo product mới thành công, redirect về màn hình edit của product vừa tạo.
* [ ] Không redirect về product list sau khi tạo mới.
* [ ] Nút `Tạo biến thể` xử lý bằng JavaScript.
* [ ] Tạo biến thể không reload page.
* [ ] Variant mới xuất hiện ngay sau khi tạo thành công.
* [ ] Lỗi tạo variant hiển thị tại form, không reload page.
* [ ] Upload product image xử lý bằng JavaScript.
* [ ] Upload image không reload page.
* [ ] Ảnh mới xuất hiện ngay sau khi upload thành công.
* [ ] Upload variant image xử lý bằng JavaScript nếu Task 10.2 đã có.
* [ ] Nút `Thêm giá trị` ở tùy chọn sản phẩm xử lý bằng JavaScript.
* [ ] Thêm option value không reload page.
* [ ] Option value mới xuất hiện ngay sau khi tạo thành công.
* [ ] Có loading overlay khi xử lý bằng JavaScript.
* [ ] Loading overlay tắt sau khi request hoàn tất hoặc lỗi.
* [ ] Có xử lý validation error cho AJAX request.
* [ ] Có CSRF protection cho AJAX request.
* [ ] Không tạo duplicate khi bấm nút nhiều lần.
* [ ] Không dùng Vue.js.
* [ ] Không implement Cart, Checkout, Order hoặc Payment.

---

## 17. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-10-product-management-with-translation.md
* docs/tasks/task-10-1-product-options-and-variant-combinations.md
* docs/tasks/task-10-2-variant-images.md
* docs/tasks/task-10-3-product-edit-ux-improvements.md
* docs/tasks/task-11-product-image-upload.md

Sau đó implement Task 10.3: Product Edit UX Improvements theo đúng tài liệu.

Yêu cầu:

* Khi tạo product mới thành công, không redirect về product list, hãy redirect về product edit page của product vừa tạo.
* Nút Tạo biến thể phải xử lý bằng JavaScript, không reload page.
* Upload hình ảnh phải xử lý bằng JavaScript, không reload page.
* Nút Thêm giá trị ở tùy chọn sản phẩm phải xử lý bằng JavaScript, không reload page.
* Khi xử lý bằng JavaScript, hiển thị loading overlay.
* Loading overlay phải tắt khi request thành công hoặc thất bại.
* UI phải cập nhật ngay sau khi thao tác thành công.
* Lỗi validation phải hiển thị ngay trên màn hình, không reload page.
* Dùng Blade, Tailwind CSS, Alpine.js hoặc Fetch API.
* Không dùng Vue.js.
* Không implement Cart, Checkout, Order hoặc Payment.
* Sau khi làm xong, báo cáo file đã tạo/sửa và lệnh cần chạy để test.

## 18. Additional UX Requirement: Async Add Option, Live Variant Update and Smooth Loading Overlay

## 18.1. Overview

Bổ sung yêu cầu cho màn hình Product Create/Edit:

* Khi admin nhấn nút `Thêm tùy chọn`, trang không được reload.
* Việc thêm tùy chọn sản phẩm phải xử lý bằng JavaScript.
* Khi thêm tùy chọn thành công, dữ liệu phải cập nhật ngay tới khu vực `Variant Combinations`.
* Khi thêm giá trị cho tùy chọn, dữ liệu cũng phải cập nhật ngay tới selector tạo biến thể.
* Loading overlay khi xử lý bằng JavaScript phải có chuyển động mượt, không mở/tắt đột ngột.

Mục tiêu:

* Admin thao tác mượt hơn.
* Không cần reload page sau khi thêm option/value.
* Không làm mất dữ liệu đang nhập ở các section khác.
* Variant combination form luôn đồng bộ với Product Options.
* Loading overlay nhìn chuyên nghiệp, không bị giựt.

---

## 18.2. Async Add Product Option

Nút `Thêm tùy chọn` cần xử lý bằng JavaScript.

Expected behavior:

* Admin nhập tên option.
* Admin nhấn `Thêm tùy chọn`.
* Hệ thống hiển thị loading overlay mượt.
* Request được gửi bằng JavaScript.
* Trang không reload.
* Nếu thành công:

  * Option mới xuất hiện ngay trong danh sách Product Options.
  * Option mới có khu vực để thêm option values.
  * Option mới xuất hiện ngay trong khu vực tạo Variant Combination.
  * Loading overlay tắt mượt.
  * Hiển thị thông báo thành công.
* Nếu thất bại:

  * Trang không reload.
  * Loading overlay tắt mượt.
  * Hiển thị lỗi validation tại form thêm option.
  * Dữ liệu admin đã nhập không bị mất.

Validation cần xử lý:

| Case                                 | Expected Result             |
| ------------------------------------ | --------------------------- |
| Option name trống                    | Hiển thị lỗi validation     |
| Option name trùng trong cùng product | Hiển thị lỗi validation     |
| Product không tồn tại                | Hiển thị lỗi phù hợp        |
| User không có quyền                  | Chặn thao tác               |
| CSRF lỗi                             | Hiển thị lỗi phù hợp        |
| Request lỗi                          | Overlay tắt và hiển thị lỗi |

---

## 18.3. Live Update To Variant Combination Area

Khi thêm product option thành công, khu vực tạo variant combination phải cập nhật ngay.

Ví dụ ban đầu product có:

| Option | Values       |
| ------ | ------------ |
| Color  | Black, White |

Variant form đang có:

| Field          |
| -------------- |
| Color selector |
| SKU            |
| Price          |
| Sale Price     |
| Status         |

Sau khi admin thêm option mới:

| Option | Values       |
| ------ | ------------ |
| Color  | Black, White |
| Size   | M, L         |

Variant form phải tự cập nhật thành:

| Field          |
| -------------- |
| Color selector |
| Size selector  |
| SKU            |
| Price          |
| Sale Price     |
| Status         |

Expected behavior:

* Option mới xuất hiện ngay trong variant creation form.
* Không cần reload page.
* Không làm mất dữ liệu đang nhập trong variant form nếu có thể giữ được.
* Nếu option mới chưa có value, selector hiển thị empty state hoặc message yêu cầu thêm value.
* Không cho tạo variant nếu chưa chọn đủ value cho tất cả active options.
* Khi option mới được thêm, existing variants table không bị mất dữ liệu.
* Không để variant form bị lỗi khi option mới vừa được thêm.

---

## 18.4. Add Option Value Impact To Variant Combination

Khi admin nhấn `Thêm giá trị` cho một option, dữ liệu phải cập nhật ngay vào variant combination form.

Ví dụ admin thêm option value:

| Option | New Value |
| ------ | --------- |
| Size   | XL        |

Variant form cần tự cập nhật:

| Selector | Values   |
| -------- | -------- |
| Size     | M, L, XL |

Expected behavior:

* Option value mới xuất hiện ngay trong list của option.
* Option value mới xuất hiện ngay trong selector tạo variant.
* Không reload page.
* Không cần admin refresh browser.
* Nếu đang mở form tạo variant, value mới có thể chọn được ngay.
* Nếu option trước đó chưa có value, sau khi thêm value đầu tiên thì variant form cần cập nhật trạng thái sẵn sàng nếu các option khác cũng đã có value.

---

## 18.5. Variant Combination Form State

Khi option hoặc option value thay đổi bằng JavaScript, variant combination form cần quản lý state rõ ràng.

Các state cần quan tâm:

| State                   | Description                                             |
| ----------------------- | ------------------------------------------------------- |
| No options              | Product chưa có option nào                              |
| Option without values   | Có option nhưng chưa có value                           |
| Ready to create variant | Tất cả active options có ít nhất một value              |
| Dirty variant form      | Admin đang nhập variant nhưng option/value vừa thay đổi |
| Invalid combination     | Form thiếu value hoặc combination trùng                 |

Expected behavior:

* Nếu product chưa có option, hiển thị message yêu cầu tạo option trước.
* Nếu option chưa có value, hiển thị message yêu cầu thêm value trước.
* Nếu tất cả option có value, form tạo variant cho phép chọn đủ values.
* Không cho tạo variant nếu thiếu value cho một option active.
* Không tạo duplicate variant combination.
* Không để variant form bị lỗi hoặc trắng dữ liệu khi option/value thay đổi bằng JavaScript.

---

## 18.6. Smooth Loading Overlay Requirement

Loading overlay hiện tại không được mở/tắt đột ngột.

Cần cải thiện để chuyển động thật mượt.

Áp dụng cho tất cả thao tác JavaScript:

* Thêm tùy chọn.
* Thêm giá trị cho tùy chọn.
* Tạo biến thể.
* Upload product image.
* Upload variant image.
* Lưu option.
* Lưu option value.
* Lưu variant.
* Set main image.
* Delete image nếu xử lý bằng JavaScript.

Yêu cầu UI:

| Requirement            | Description                                              |
| ---------------------- | -------------------------------------------------------- |
| Smooth fade in         | Overlay xuất hiện bằng hiệu ứng mờ dần                   |
| Smooth fade out        | Overlay biến mất bằng hiệu ứng mờ dần                    |
| No sudden jump         | Không hiện/tắt đột ngột                                  |
| Spinner animation      | Spinner xoay mượt                                        |
| Backdrop               | Có nền mờ nhẹ hoặc blur nhẹ                              |
| Disabled interaction   | Không cho bấm lặp trong lúc đang xử lý                   |
| Friendly text          | Hiển thị text như Saving..., Processing..., Uploading... |
| Minimum visible time   | Overlay nên hiển thị đủ ngắn nhưng không bị nhấp nháy    |
| Reduced motion support | Nếu user giảm motion, animation nên nhẹ hoặc tắt         |

Expected behavior:

* Overlay hiển thị ngay khi bắt đầu request, nhưng chuyển động mượt.
* Overlay tắt khi request thành công hoặc thất bại, nhưng fade out mượt.
* Nếu request quá nhanh, overlay không được nháy giựt.
* Nếu request lỗi, overlay vẫn phải tắt.
* Không để overlay bị treo vĩnh viễn.
* Không tạo cảm giác lag hoặc giật màn hình.
* Có thể dùng overlay toàn màn hình hoặc section overlay, nhưng MVP ưu tiên overlay toàn màn hình thống nhất.

---

## 18.7. Loading Overlay Timing

Để tránh cảm giác giựt, loading overlay cần có timing hợp lý.

Yêu cầu:

| Timing Rule              | Description                                                      |
| ------------------------ | ---------------------------------------------------------------- |
| Fade in duration         | Khoảng 150ms - 250ms                                             |
| Fade out duration        | Khoảng 150ms - 300ms                                             |
| Minimum display duration | Khoảng 300ms - 500ms để tránh nhấp nháy                          |
| Fast request behavior    | Nếu request quá nhanh, vẫn tắt overlay mượt                      |
| Error behavior           | Khi lỗi, overlay tắt trước hoặc đồng thời hiển thị error message |

Không cần dùng animation phức tạp.

Chỉ cần cảm giác:

* mở mượt
* tắt mượt
* không giật
* không nhấp nháy

---

## 18.8. UI Update Requirement

Sau khi thêm option bằng JavaScript thành công, UI cần cập nhật các khu vực sau:

| UI Area                   | Required Update                            |
| ------------------------- | ------------------------------------------ |
| Product Options List      | Thêm option mới                            |
| Option Values Area        | Hiển thị khu vực thêm value cho option mới |
| Variant Combination Form  | Thêm selector mới tương ứng option mới     |
| Variant Combination Table | Giữ nguyên dữ liệu hiện tại                |
| Error Area                | Clear lỗi cũ nếu thành công                |
| Success Message           | Hiển thị message thành công                |
| Loading Overlay           | Tắt mượt sau khi cập nhật UI               |

Sau khi thêm option value bằng JavaScript thành công, UI cần cập nhật:

| UI Area                 | Required Update                       |
| ----------------------- | ------------------------------------- |
| Option Value List       | Thêm value mới                        |
| Variant Selector        | Thêm value mới vào selector tương ứng |
| Variant Form Validation | Kiểm tra lại trạng thái form          |
| Success Message         | Hiển thị message thành công           |
| Loading Overlay         | Tắt mượt sau khi cập nhật UI          |

---

## 18.9. API / Response Requirement

Response khi thêm option thành công cần trả đủ dữ liệu để frontend cập nhật UI.

Response thành công nên có:

| Field                  | Description                                              |
| ---------------------- | -------------------------------------------------------- |
| success                | true                                                     |
| message                | Message thành công                                       |
| option                 | Dữ liệu option vừa tạo                                   |
| option_html            | HTML partial của option nếu dùng server-rendered partial |
| option_value_area_html | HTML khu vực thêm value cho option mới                   |
| variant_selector_html  | HTML selector mới cho variant form nếu cần               |

Response khi thêm option value thành công nên có:

| Field                | Description                                             |
| -------------------- | ------------------------------------------------------- |
| success              | true                                                    |
| message              | Message thành công                                      |
| option_value         | Dữ liệu option value vừa tạo                            |
| option_value_html    | HTML partial của value nếu dùng server-rendered partial |
| selector_option_html | HTML option/chip để thêm vào variant selector           |

Response lỗi validation nên có:

| Field   | Description              |
| ------- | ------------------------ |
| success | false                    |
| message | Message lỗi              |
| errors  | Danh sách lỗi theo field |

Business rules:

* Request AJAX không redirect.
* Request AJAX trả JSON.
* Validation lỗi phải trả JSON rõ ràng.
* Không trả HTML error page cho AJAX request.
* CSRF phải được xử lý đúng.

---

## 18.10. Duplicate Prevention

Backend vẫn phải kiểm tra duplicate, không chỉ dựa vào frontend.

Cần tránh:

* Tạo trùng option name trong cùng product.
* Tạo trùng option value trong cùng option.
* Tạo duplicate variant combination.
* Tạo duplicate do admin bấm nút nhiều lần.
* Tạo dữ liệu không thuộc product hiện tại.

Expected behavior:

* Button bị disable khi request đang xử lý.
* Loading overlay hiển thị mượt trong lúc request.
* Backend validate duplicate.
* Nếu duplicate, hiển thị lỗi rõ ràng và không reload page.
* Overlay tắt mượt sau khi lỗi được xử lý.

---

## 18.11. Acceptance Criteria Additions

Bổ sung acceptance criteria:

* [ ] Nút `Thêm tùy chọn` xử lý bằng JavaScript.
* [ ] Thêm tùy chọn không reload page.
* [ ] Khi thêm tùy chọn thành công, option mới xuất hiện ngay trong Product Options list.
* [ ] Khi thêm tùy chọn thành công, khu vực thêm option value cho option mới xuất hiện ngay.
* [ ] Khi thêm tùy chọn thành công, Variant Combination form có selector mới tương ứng.
* [ ] Khi thêm option value thành công, value mới xuất hiện ngay trong variant selector.
* [ ] Admin không cần reload page để dùng option/value mới tạo cho biến thể.
* [ ] Loading overlay hiển thị khi thêm tùy chọn.
* [ ] Loading overlay hiển thị khi thêm option value.
* [ ] Loading overlay hiển thị khi tạo biến thể.
* [ ] Loading overlay có hiệu ứng fade in mượt.
* [ ] Loading overlay có hiệu ứng fade out mượt.
* [ ] Loading overlay không bị nhấp nháy khi request quá nhanh.
* [ ] Loading overlay không bị treo khi request lỗi.
* [ ] Lỗi validation hiển thị tại đúng khu vực, không reload page.
* [ ] Không tạo duplicate option khi bấm nhiều lần.
* [ ] Không tạo duplicate option value khi bấm nhiều lần.
* [ ] Không tạo duplicate variant combination.
* [ ] Không dùng Vue.js.
* [ ] Không implement Cart, Checkout, Order hoặc Payment.
