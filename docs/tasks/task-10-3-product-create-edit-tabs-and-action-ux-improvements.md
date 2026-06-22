Đúng, vậy mình hiểu lại như sau: bạn muốn **Task 10.3 là một task riêng, rõ ràng**, tên là:

`Task 10.3: Product Create/Edit Tabs and Action UX Improvements`

Bạn nên tạo file mới hoặc đổi tên file cũ thành:

`docs/tasks/task-10-3-product-create-edit-tabs-and-action-ux-improvements.md`

và copy nội dung dưới đây vào file.

# Task 10.3: Product Create/Edit Tabs and Action UX Improvements

## 1. Overview

Task này dùng để thiết kế lại trải nghiệm màn hình tạo và chỉnh sửa sản phẩm trong admin.

Hiện tại màn hình Product Create/Edit có nhiều chức năng:

* Thông tin cơ bản sản phẩm
* Nội dung đa ngôn ngữ
* Ảnh sản phẩm
* Tùy chọn sản phẩm
* Giá trị tùy chọn
* Tổ hợp biến thể
* Ảnh riêng của biến thể
* Tồn kho
* SEO
* Các action như lưu, thêm tùy chọn, thêm giá trị, tạo biến thể, upload ảnh, xóa ảnh

Nếu đặt tất cả trong một màn hình dài, admin sẽ khó thao tác, dễ nhầm lẫn và trải nghiệm không chuyên nghiệp.

Task này sẽ cải thiện theo 2 hướng chính:

1. Chia màn hình Product Create/Edit thành nhiều tab rõ ràng.
2. Cải thiện toàn bộ action UX để thao tác hợp lý, dễ hiểu, không reload page ở các thao tác nhỏ.

Frontend trong MVP sử dụng:

* Laravel Blade
* Tailwind CSS
* Alpine.js nếu cần
* Fetch API nếu cần
* Không dùng Vue.js

---

## 2. Objectives

Sau khi hoàn thành task này, hệ thống cần có:

* Product Create screen rõ ràng, dễ nhập dữ liệu ban đầu.
* Product Edit screen chia thành các tab hợp lý.
* Sau khi tạo product thành công, redirect về trang edit của product vừa tạo.
* Không redirect về product list sau khi tạo product.
* Chuyển tab không reload page.
* Chuyển tab không làm mất dữ liệu đang nhập.
* Các action được chia nhóm rõ ràng.
* `Save Changes` là action chính.
* Các action nhỏ xử lý bằng JavaScript, không reload page.
* Add Option không reload page.
* Add Option Value không reload page.
* Create Variant không reload page.
* Upload Product Image không reload page.
* Upload Variant Image không reload page.
* Khi thêm option/value thì dữ liệu ảnh hưởng ngay tới form tạo variant.
* Loading overlay hiển thị mượt, không giựt.
* Button đang xử lý phải disabled.
* Tab có lỗi validation hiển thị badge error.
* Tab có dữ liệu chưa lưu hiển thị unsaved state.
* Danger actions có confirmation.
* Row actions gọn, dễ dùng, không rối.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong task này:

* Tạo tab layout cho Product Create/Edit.
* Cập nhật Product Create behavior.
* Cập nhật Product Edit behavior.
* Cải thiện main action bar.
* Cải thiện tab-level actions.
* Cải thiện row actions.
* Cải thiện danger actions.
* Cải thiện loading overlay.
* Cập nhật AJAX behavior cho các thao tác nhỏ.
* Cập nhật live update giữa Options và Variants.
* Cập nhật validation error display theo tab.
* Cập nhật unsaved state.
* Cập nhật responsive UI cho tab.

### 3.2. Out of Scope

Không làm trong task này:

* Không làm lại toàn bộ Product Management.
* Không thay đổi database nếu không cần.
* Không implement Cart.
* Không implement Checkout.
* Không implement Order.
* Không implement Payment.
* Không dùng Vue.js.
* Không làm autosave realtime.
* Không làm import/export product.
* Không làm bulk update product.
* Không làm drag and drop nâng cao.

---

## 4. Product Create Page

URL:

`/admin/products/create`

Product Create page chỉ dùng để tạo product ban đầu.

### 4.1. Enabled Sections

Khi product chưa tồn tại, chỉ nên cho nhập các phần cần thiết:

| Section / Tab  | Status                              |
| -------------- | ----------------------------------- |
| General        | Enabled                             |
| Translations   | Enabled                             |
| SEO            | Enabled hoặc gộp trong Translations |
| Images         | Disabled hoặc hidden                |
| Options        | Disabled hoặc hidden                |
| Variants       | Disabled hoặc hidden                |
| Variant Images | Disabled hoặc hidden                |
| Inventory      | Disabled hoặc hidden                |

Lý do:

* Product images cần `product_id`.
* Product options cần `product_id`.
* Variants cần `product_id`.
* Variant images cần `variant_id`.
* Inventory cần `product_id` hoặc `variant_id`.

### 4.2. Create Save Behavior

Khi admin nhấn `Save Changes` ở màn hình tạo product:

| Case             | Expected Result                            |
| ---------------- | ------------------------------------------ |
| Create success   | Redirect về trang edit của product vừa tạo |
| Validation error | Giữ lại form create và hiển thị lỗi        |
| System error     | Hiển thị message lỗi phù hợp               |

Sau khi tạo thành công, redirect về:

`/admin/products/{id}/edit`

Không redirect về:

`/admin/products`

### 4.3. Disabled Tab Message

Nếu vẫn hiển thị các tab nâng cao ở Create page, các tab chưa dùng được cần có message:

`Please save the product first before managing images, options, variants and inventory.`

---

## 5. Product Edit Page

URL:

`/admin/products/{id}/edit`

Product Edit page hiển thị đầy đủ các tab.

### 5.1. Required Tabs

| Tab            | Description            |
| -------------- | ---------------------- |
| General        | Thông tin cơ bản       |
| Translations   | Nội dung đa ngôn ngữ   |
| Images         | Ảnh chung của product  |
| Options        | Tùy chọn sản phẩm      |
| Variants       | Tổ hợp biến thể        |
| Variant Images | Ảnh riêng của biến thể |
| Inventory      | Tồn kho                |
| SEO            | SEO fields             |

### 5.2. Tab Behavior

Expected behavior:

* Chuyển tab không reload page.
* Chuyển tab không làm mất dữ liệu đang nhập.
* Active tab phải được highlight rõ.
* Tab có lỗi validation phải có badge error.
* Tab có dữ liệu chưa lưu phải có unsaved state.
* Nếu tab disabled, cần hiển thị lý do.
* Mobile layout không bị vỡ khi có nhiều tab.

### 5.3. Tab States

| State    | Description             |
| -------- | ----------------------- |
| Active   | Tab đang mở             |
| Saved    | Tab đã lưu              |
| Unsaved  | Tab có dữ liệu chưa lưu |
| Error    | Tab có lỗi validation   |
| Disabled | Tab chưa thể dùng       |

---

## 6. Tab Details

## 6.1. General Tab

Dùng để quản lý thông tin cơ bản.

Fields:

| Field      | Description            |
| ---------- | ---------------------- |
| Category   | Danh mục               |
| Tax Class  | Nhóm thuế              |
| SKU        | SKU chính              |
| Price      | Giá gốc                |
| Sale Price | Giá khuyến mãi         |
| Cost Price | Giá vốn nếu có         |
| Status     | Active / Inactive      |
| Featured   | Sản phẩm nổi bật       |
| Sort Order | Thứ tự hiển thị nếu có |

Actions:

| Action       | Behavior         |
| ------------ | ---------------- |
| Save Changes | Lưu toàn bộ form |

---

## 6.2. Translations Tab

Dùng để quản lý nội dung đa ngôn ngữ.

Fields theo từng language:

| Field             | Description     |
| ----------------- | --------------- |
| Name              | Tên sản phẩm    |
| Slug              | Slug            |
| Short Description | Mô tả ngắn      |
| Description       | Mô tả chi tiết  |
| Meta Title        | SEO title       |
| Meta Description  | SEO description |

Actions:

| Action                     | Behavior         |
| -------------------------- | ---------------- |
| Save Changes               | Lưu toàn bộ form |
| Auto Generate Slug         | Optional         |
| Copy From Default Language | Optional         |

Business rules:

* Default language translation là bắt buộc.
* Slug unique theo language.
* Nếu language block có lỗi, tab cần badge error.

---

## 6.3. Images Tab

Dùng để quản lý ảnh chung của product.

Actions:

| Action         | Behavior                          |
| -------------- | --------------------------------- |
| Upload Images  | Upload bằng JS, không reload page |
| Set Main Image | Set ảnh chính                     |
| Edit Alt Text  | Cập nhật alt text                 |
| Delete Image   | Xóa ảnh, cần confirmation         |

Expected behavior:

* Upload product image không reload page.
* Ảnh mới xuất hiện ngay sau khi upload thành công.
* Nếu ảnh đầu tiên được upload, có thể tự set main image.
* Delete image cần confirmation.
* Không làm mất dữ liệu ở tab khác.

---

## 6.4. Options Tab

Dùng để quản lý product options và option values.

Ví dụ:

| Option  | Values             |
| ------- | ------------------ |
| Color   | Black, White, Blue |
| Size    | S, M, L, XL        |
| Storage | 128GB, 256GB       |

Actions:

| Action         | Behavior                                 |
| -------------- | ---------------------------------------- |
| Add Option     | Thêm tùy chọn bằng JS, không reload page |
| Add Value      | Thêm giá trị bằng JS, không reload page  |
| Save Option    | Lưu option riêng bằng JS nếu có          |
| Save Value     | Lưu value riêng bằng JS nếu có           |
| Disable Option | Tắt option                               |
| Delete Option  | Xóa option, cần confirmation             |
| Delete Value   | Xóa value, cần confirmation              |

Expected behavior:

* `Add Option` xử lý bằng JavaScript.
* Trang không reload khi thêm option.
* Option mới xuất hiện ngay trong Options tab.
* Option mới có khu vực thêm value ngay sau khi tạo.
* Khi thêm option thành công, Variants tab/form phải cập nhật selector mới.
* `Add Value` xử lý bằng JavaScript.
* Trang không reload khi thêm value.
* Value mới xuất hiện ngay trong option card.
* Value mới xuất hiện ngay trong selector tạo variant.
* Không tạo duplicate option.
* Không tạo duplicate option value.

---

## 6.5. Variants Tab

Dùng để tạo và quản lý tổ hợp biến thể.

Ví dụ:

| Product | Variant               |
| ------- | --------------------- |
| Áo thun | Black / M             |
| Áo thun | White / L             |
| iPhone  | Black / 128GB         |
| Laptop  | 16GB / 512GB / Silver |

Actions:

| Action            | Behavior                                    |
| ----------------- | ------------------------------------------- |
| Create Variant    | Tạo variant bằng JS, không reload page      |
| Edit Variant      | Sửa SKU, price, sale price, status          |
| Duplicate Variant | Optional                                    |
| Manage Images     | Đi tới Variant Images                       |
| Disable Variant   | Tắt variant                                 |
| Delete Variant    | Xóa variant nếu được phép, cần confirmation |

Expected behavior:

* `Create Variant` xử lý bằng JavaScript.
* Trang không reload khi tạo variant.
* Variant mới xuất hiện ngay trong table.
* Form tạo variant tự cập nhật khi option hoặc value thay đổi.
* Không cho tạo variant nếu thiếu value cho một option active.
* Không tạo duplicate combination.
* Row action phải gọn, không quá nhiều button ngang hàng.

---

## 6.6. Variant Images Tab

Dùng để quản lý ảnh riêng của từng variant.

Actions:

| Action                | Behavior                     |
| --------------------- | ---------------------------- |
| Select Variant        | Chọn variant cần quản lý ảnh |
| Upload Variant Images | Upload ảnh variant bằng JS   |
| Set Main Image        | Set ảnh chính                |
| Delete Image          | Xóa ảnh, cần confirmation    |

Expected behavior:

* Nếu product chưa có variant, hiển thị empty state.
* Nếu variant chưa có ảnh, hiển thị empty state.
* Upload variant image không reload page.
* Ảnh upload thành công xuất hiện ngay.
* Nếu variant không có ảnh, frontend fallback về product image.

---

## 6.7. Inventory Tab

Dùng để quản lý tồn kho.

Actions:

| Action         | Behavior                |
| -------------- | ----------------------- |
| Adjust Stock   | Điều chỉnh tồn kho      |
| Save Threshold | Lưu low stock threshold |
| View Logs      | Xem lịch sử tồn kho     |

Expected behavior:

* Product không có variant thì quản lý stock theo product.
* Product có variant thì quản lý stock theo variant.
* Inventory action nên tách biệt với product basic save.
* Adjust stock cần confirmation nếu giảm số lượng lớn.
* Không cho tồn kho nhỏ hơn reserved quantity.

---

## 6.8. SEO Tab

Dùng để quản lý SEO fields.

Fields:

| Field                  | Description     |
| ---------------------- | --------------- |
| Meta Title             | SEO title       |
| Meta Description       | SEO description |
| Slug Preview           | Xem trước slug  |
| Search Snippet Preview | Optional        |

Actions:

| Action                     | Behavior         |
| -------------------------- | ---------------- |
| Save Changes               | Lưu toàn bộ form |
| Preview Snippet            | Optional         |
| Generate From Product Name | Optional         |

Business rules:

* Thay đổi slug cần cảnh báo vì có thể ảnh hưởng URL public.
* SEO fields có thể lấy từ translation nếu đã có.

---

## 7. Action UX Design

## 7.1. Action Groups

Các action phải được chia thành 4 nhóm:

| Group            | Purpose                     | Example                                      |
| ---------------- | --------------------------- | -------------------------------------------- |
| Primary Action   | Hành động chính             | Save Changes                                 |
| Secondary Action | Hành động phụ               | Back to List, Preview                        |
| Inline Action    | Hành động trong tab/section | Add Option, Add Value, Create Variant        |
| Danger Action    | Hành động nguy hiểm         | Delete Product, Delete Image, Delete Variant |

Business rules:

* Mỗi màn hình chỉ nên có một primary action chính.
* `Save Changes` là primary action chính.
* Danger action không đặt sát primary action.
* Action ít dùng đưa vào dropdown `More Actions`.
* Row actions cần gọn, không quá nhiều button.
* Danger action cần confirmation.

---

## 7.2. Main Action Bar

Product Edit page cần có action bar rõ ràng.

Main actions:

| Action         | Type      | Behavior                                     |
| -------------- | --------- | -------------------------------------------- |
| Save Changes   | Primary   | Lưu toàn bộ dữ liệu product trong tất cả tab |
| Preview        | Secondary | Mở product detail public nếu có              |
| Back to List   | Secondary | Quay về product list                         |
| More Actions   | Dropdown  | Chứa action ít dùng                          |
| Delete Product | Danger    | Xóa product, cần confirmation                |

Expected behavior:

* `Save Changes` luôn dễ thấy.
* `Back to List` không nổi bật hơn `Save Changes`.
* `Delete Product` không đặt sát `Save Changes`.
* Khi có unsaved data, action bar hiển thị `Unsaved changes`.
* Khi lưu thành công, action bar hiển thị `Saved`.
* Khi lỗi validation, action bar hiển thị message lỗi và tab lỗi có badge.

Action bar có thể sticky ở trên hoặc dưới form.

---

## 7.3. Tab-Level Actions

Mỗi tab chỉ hiển thị các action liên quan đến tab đó.

| Tab            | Main Inline Action    |
| -------------- | --------------------- |
| Images         | Upload Images         |
| Options        | Add Option            |
| Variants       | Create Variant        |
| Variant Images | Upload Variant Images |
| Inventory      | Adjust Stock          |
| SEO            | Generate / Preview    |

Business rules:

* Không đặt quá nhiều button lớn trong một tab.
* Action thường dùng đặt rõ ràng.
* Action ít dùng đưa vào dropdown nhỏ.
* Danger action đặt trong dropdown hoặc khu vực riêng.
* Nếu action đang xử lý, disable button đó.

---

## 7.4. Row Action Design

Trong table như variants, images, inventory, không nên đặt quá nhiều button lớn.

Variant row action đề xuất:

| Display | Contains                   |
| ------- | -------------------------- |
| Edit    | Sửa variant                |
| Images  | Quản lý ảnh variant        |
| More    | Duplicate, Disable, Delete |

Image row/card action đề xuất:

| Display  | Contains                |
| -------- | ----------------------- |
| Set Main | Set ảnh chính           |
| Edit     | Sửa alt text/sort order |
| More     | Disable, Delete         |

Business rules:

* Không để Delete nằm sát Edit nếu dễ bấm nhầm.
* Delete cần confirmation.
* Disable nên ưu tiên hơn Delete nếu dữ liệu đã liên quan inventory/order.
* Nếu action không thể dùng, disable và hiển thị lý do.

---

## 8. AJAX Action Requirements

### 8.1. Modal Action Requirements

Trong Product Edit, các form thao tác nhỏ phải mở bằng modal popup thay vì chiếm diện tích cố định hoặc chuyển sang màn hình quản lý khác:

* Add Product Option.
* Add Product Option Value.
* Create Variant Combination.
* Upload Product Images.
* Upload Variant Images.
* Adjust Inventory.

Business rules:

* Modal mở và đóng bằng transition mượt, hỗ trợ đóng bằng nút close, backdrop và phím Escape.
* Submit modal dùng Fetch API, CSRF và JSON response; không reload page.
* Validation error hiển thị ngay bên trong modal và modal không được đóng khi request thất bại.
* Modal chỉ tự đóng khi request thành công.
* Button submit bị disabled trong lúc request để chống submit trùng.
* Sau thành công, list, table, selector, gallery, image count, stock quantity, stock status và recent inventory log liên quan phải cập nhật ngay.
* Quản lý ảnh variant trong Product Edit không được bắt buộc chuyển sang trang ảnh riêng.
* Điều chỉnh tồn kho trong Product Edit không được chuyển sang trang Inventory Adjust riêng.

---

Các action sau phải xử lý bằng JavaScript, không reload page:

| Action               | Requirement                    |
| -------------------- | ------------------------------ |
| Add Option           | Không reload page              |
| Add Option Value     | Không reload page              |
| Create Variant       | Không reload page              |
| Upload Product Image | Không reload page              |
| Upload Variant Image | Không reload page              |
| Set Main Image       | Không reload page nếu xử lý JS |
| Delete Image         | Không reload page nếu xử lý JS |
| Save Option          | Không reload page nếu có       |
| Save Variant         | Không reload page nếu có       |

Yêu cầu chung:

* Dùng CSRF token.
* AJAX request trả JSON.
* Validation error trả JSON rõ ràng.
* Không redirect khi AJAX request thành công.
* UI cập nhật ngay sau khi thành công.
* Không làm mất dữ liệu các tab khác.
* Button đang xử lý phải disabled.
* Không tạo duplicate khi bấm nhiều lần.

---

## 9. Live Update Between Options and Variants

Khi admin thêm option hoặc option value, khu vực Variant form phải cập nhật ngay.

### 9.1. Add Option Flow

* Admin nhập option name.
* Admin nhấn `Add Option`.
* Request gửi bằng JavaScript.
* Nếu thành công:

  * Option mới xuất hiện trong Options tab.
  * Khu vực thêm value cho option mới xuất hiện.
  * Variant form có selector mới tương ứng option mới.
  * Không reload page.

### 9.2. Add Option Value Flow

* Admin nhập value mới trong option card.
* Admin nhấn `Add Value`.
* Request gửi bằng JavaScript.
* Nếu thành công:

  * Value mới xuất hiện trong option card.
  * Value mới xuất hiện trong selector tương ứng ở Variant form.
  * Không reload page.

Expected behavior:

* Admin không cần reload page để dùng option/value mới.
* Nếu option mới chưa có value, selector hiển thị empty state.
* Không cho tạo variant nếu thiếu value cho một option active.
* Existing variants table không bị mất dữ liệu.

---

## 10. Smooth Loading Overlay

Loading overlay phải mượt, không mở/tắt đột ngột.

Áp dụng cho tất cả AJAX actions:

* Add option
* Add option value
* Create variant
* Upload product image
* Upload variant image
* Save option
* Save value
* Save variant
* Set main image
* Delete image

Yêu cầu UI:

| Requirement              | Description                                  |
| ------------------------ | -------------------------------------------- |
| Smooth fade in           | Overlay xuất hiện bằng hiệu ứng mờ dần       |
| Smooth fade out          | Overlay biến mất bằng hiệu ứng mờ dần        |
| No flicker               | Không nhấp nháy khi request quá nhanh        |
| Spinner animation        | Spinner xoay mượt                            |
| Backdrop                 | Nền mờ nhẹ hoặc blur nhẹ                     |
| Disabled interaction     | Hạn chế bấm lặp                              |
| Friendly text            | Saving..., Processing..., Uploading...       |
| Minimum display duration | Overlay hiển thị đủ ngắn nhưng không bị giựt |

Timing gợi ý:

| Timing                   | Value         |
| ------------------------ | ------------- |
| Fade in duration         | 150ms - 250ms |
| Fade out duration        | 150ms - 300ms |
| Minimum display duration | 300ms - 500ms |

Expected behavior:

* Overlay hiển thị khi bắt đầu request.
* Overlay tắt khi request thành công hoặc thất bại.
* Nếu request quá nhanh, overlay không bị nháy giựt.
* Nếu request lỗi, overlay vẫn phải tắt.
* Không để overlay bị treo.

---

## 11. Unsaved Changes Behavior

Product edit screen cần quản lý dữ liệu chưa lưu.

Expected behavior:

* Khi admin sửa field, tab chuyển sang trạng thái `Unsaved`.
* Action bar hiển thị `Unsaved changes`.
* Khi admin bấm `Save Changes`, lưu dữ liệu của tất cả tab.
* Nếu lưu thành công, trạng thái chuyển thành `Saved`.
* Nếu validation lỗi, tab lỗi chuyển thành `Error`.
* Chuyển tab không làm mất dữ liệu.
* Nếu admin rời khỏi page khi còn unsaved data, có thể hiển thị cảnh báo.

---

## 12. Validation Error Behavior

Validation error cần hiển thị rõ theo field và tab.

Expected behavior:

* Lỗi field hiển thị gần field.
* Lỗi section hiển thị trong section.
* Tab có lỗi cần badge error.
* Nếu lỗi ở tab khác, admin vẫn biết tab nào đang lỗi.
* AJAX validation error không reload page.
* Full form validation error không làm mất dữ liệu đã nhập.
* Error message phải rõ ràng, không hiển thị lỗi kỹ thuật.

---

## 13. Confirmation Rules

Các action nguy hiểm cần confirmation.

| Action                     | Confirmation |
| -------------------------- | ------------ |
| Delete Product             | Required     |
| Delete Option              | Required     |
| Delete Option Value        | Required     |
| Delete Variant             | Required     |
| Delete Product Image       | Required     |
| Delete Variant Image       | Required     |
| Disable Variant with Stock | Recommended  |
| Reduce Inventory           | Recommended  |

Confirmation message cần rõ ràng.

Ví dụ:

`Are you sure you want to delete this variant? This action cannot be undone.`

Nếu variant đã có inventory/order sau này, không nên hard delete. Nên hiển thị:

`This variant has related data. Please disable it instead of deleting.`

---

## 14. Toast / Message Behavior

Sau mỗi action cần feedback rõ ràng.

| Result           | UI Feedback                       |
| ---------------- | --------------------------------- |
| Success          | Toast success hoặc inline success |
| Validation Error | Inline error tại field/section    |
| Server Error     | Toast error chung                 |
| Network Error    | Toast error kết nối               |
| Unsaved Changes  | Badge hoặc text ở action bar      |

Business rules:

* Success message không nên quá lớn.
* Validation error phải nằm gần field bị lỗi.
* Error ở tab khác cần làm tab đó có badge error.
* Message tự động ẩn sau vài giây nếu không quan trọng.

---

## 15. Responsive Requirements

Tab UI cần hoạt động tốt trên mobile.

Expected behavior:

* Desktop: tab nằm ngang rõ ràng.
* Mobile: tab có thể horizontal scroll hoặc dropdown.
* Không bị vỡ layout khi có nhiều tab.
* Action bar không che nội dung quan trọng.
* Button dễ bấm trên mobile.
* Table variants có thể scroll ngang nếu cần.
* Image gallery responsive.

---

## 16. API / Response Requirement

AJAX response thành công nên có:

| Field   | Description                                   |
| ------- | --------------------------------------------- |
| success | true                                          |
| message | Message thành công                            |
| data    | Dữ liệu mới cần update UI                     |
| html    | HTML partial nếu dùng server-rendered partial |

AJAX response lỗi nên có:

| Field   | Description              |
| ------- | ------------------------ |
| success | false                    |
| message | Message lỗi              |
| errors  | Danh sách lỗi theo field |

Business rules:

* AJAX request không redirect.
* AJAX request trả JSON.
* Validation lỗi trả JSON rõ ràng.
* Không trả HTML error page cho AJAX request.
* CSRF phải được xử lý đúng.

---

## 17. Security

Yêu cầu bảo mật:

* Tất cả AJAX request phải có CSRF token.
* Chỉ admin mới được thao tác.
* Validate toàn bộ input ở backend.
* Không tin tưởng dữ liệu từ JavaScript.
* Không upload file nguy hiểm.
* Không expose lỗi kỹ thuật chi tiết ra UI.
* Không cho tạo option/value/variant không thuộc product hiện tại.
* Không cho upload image vào product/variant không hợp lệ.
* Danger actions cần confirmation.

---

## 18. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type                       | Description                                             |
| -------------------------- | ------------------------------------------------------- |
| Product Controller         | Cập nhật create/edit/save behavior                      |
| Product Option Controller  | AJAX add option/value                                   |
| Product Variant Controller | AJAX create/update variant                              |
| Product Image Controller   | AJAX upload image                                       |
| Variant Image Controller   | AJAX upload variant image nếu có                        |
| Request Validation         | Validate form và AJAX                                   |
| Blade Views                | Product create/edit tab layout                          |
| Partial Views              | Tab partials, option card, variant row, image thumbnail |
| JavaScript                 | Fetch API hoặc Alpine.js handlers                       |
| Loading Overlay            | Component overlay dùng chung                            |
| Routes                     | Đảm bảo route hỗ trợ AJAX                               |
| CSS/Tailwind               | Cải thiện tab/action UI                                 |

Lưu ý:

* Không dùng Vue.js.
* Không làm lại toàn bộ Product Management nếu không cần.
* Không phá admin layout hiện tại.
* Không implement Cart, Checkout, Order hoặc Payment.

---

## 19. Commands

Sau khi Codex implement xong, chạy các lệnh kiểm tra:

| Command                | Purpose                              |
| ---------------------- | ------------------------------------ |
| php artisan route:list | Kiểm tra route                       |
| php artisan migrate    | Chạy migration nếu có                |
| php artisan serve      | Chạy local server                    |
| npm run build          | Build frontend nếu có thay đổi asset |

URL test:

`http://127.0.0.1:8000/admin/products/create`

`http://127.0.0.1:8000/admin/products/{id}/edit`

---

## 20. Test Cases

| Test Case ID | Scenario                      | Expected Result                                       |
| ------------ | ----------------------------- | ----------------------------------------------------- |
| TC-001       | Mở product create page        | Hiển thị create form hợp lý                           |
| TC-002       | Tạo product thành công        | Redirect về edit page của product vừa tạo             |
| TC-003       | Tạo product validation lỗi    | Giữ dữ liệu và hiển thị lỗi                           |
| TC-004       | Mở product edit page          | Hiển thị đầy đủ tabs                                  |
| TC-005       | Chuyển tab                    | Không reload page                                     |
| TC-006       | Chuyển tab khi đang nhập      | Không mất dữ liệu                                     |
| TC-007       | Add Option                    | Không reload page                                     |
| TC-008       | Add Option thành công         | Option mới xuất hiện ngay                             |
| TC-009       | Add Option trùng              | Hiển thị lỗi, không reload                            |
| TC-010       | Add Option Value              | Không reload page                                     |
| TC-011       | Add Option Value thành công   | Value xuất hiện trong option card và variant selector |
| TC-012       | Create Variant                | Không reload page                                     |
| TC-013       | Create Variant thành công     | Variant xuất hiện ngay trong table                    |
| TC-014       | Create duplicate variant      | Hiển thị lỗi                                          |
| TC-015       | Upload Product Image          | Không reload page                                     |
| TC-016       | Upload Variant Image          | Không reload page                                     |
| TC-017       | AJAX request đang xử lý       | Button disabled và overlay hiển thị                   |
| TC-018       | AJAX request hoàn tất         | Overlay tắt mượt                                      |
| TC-019       | AJAX request quá nhanh        | Overlay không nhấp nháy                               |
| TC-020       | Tab có validation error       | Tab hiển thị badge error                              |
| TC-021       | Tab có unsaved data           | Tab hiển thị unsaved state                            |
| TC-022       | Delete image                  | Có confirmation                                       |
| TC-023       | Delete variant                | Có confirmation                                       |
| TC-024       | Mobile layout                 | Tab không bị vỡ                                       |
| TC-025       | Customer gọi admin AJAX route | Bị chặn                                               |

---

## 21. Acceptance Criteria

Task này được xem là hoàn thành khi:

* [ ] Product Create/Edit screen được chia thành tabs.
* [ ] Product Create page chỉ hiển thị hoặc ưu tiên thông tin cần thiết để tạo product.
* [ ] Sau khi create product thành công, redirect về edit page.
* [ ] Không redirect về product list sau khi create.
* [ ] Product Edit page hiển thị đầy đủ tabs.
* [ ] Chuyển tab không reload page.
* [ ] Chuyển tab không làm mất dữ liệu đang nhập.
* [ ] Tab active được highlight rõ.
* [ ] Tab có lỗi validation hiển thị badge error.
* [ ] Tab có dữ liệu chưa lưu hiển thị unsaved state.
* [ ] Product Edit page có action bar rõ ràng.
* [ ] `Save Changes` là primary action chính.
* [ ] Danger actions không đặt sát `Save Changes`.
* [ ] Row actions gọn gàng, không quá nhiều button.
* [ ] Action ít dùng được gom vào `More Actions`.
* [ ] Add Option xử lý bằng JavaScript, không reload page.
* [ ] Add Option Value xử lý bằng JavaScript, không reload page.
* [ ] Create Variant xử lý bằng JavaScript, không reload page.
* [ ] Upload Product Image xử lý bằng JavaScript, không reload page.
* [ ] Upload Variant Image xử lý bằng JavaScript nếu có Variant Images.
* [ ] Khi thêm option, Variant form cập nhật selector mới ngay.
* [ ] Khi thêm option value, Variant selector cập nhật value mới ngay.
* [ ] Loading overlay fade in/fade out mượt.
* [ ] Overlay không nhấp nháy khi request quá nhanh.
* [ ] Overlay không bị treo khi request lỗi.
* [ ] Button đang xử lý phải disabled.
* [ ] Không tạo duplicate khi bấm nhiều lần.
* [ ] Validation error hiển thị đúng field hoặc tab.
* [ ] Danger actions có confirmation.
* [ ] Mobile layout không bị vỡ.
* [ ] Không dùng Vue.js.
* [ ] Không implement Cart, Checkout, Order hoặc Payment.

---

## 22. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-10-product-management-with-translation.md
* docs/tasks/task-10-1-product-options-and-variant-combinations.md
* docs/tasks/task-10-2-variant-images.md
* docs/tasks/task-10-3-product-create-edit-tabs-and-action-ux-improvements.md
* docs/tasks/task-11-product-image-upload.md
* docs/tasks/task-12-inventory-management.md

Sau đó implement Task 10.3: Product Create/Edit Tabs and Action UX Improvements theo đúng tài liệu.

Yêu cầu:

* Đây là task riêng cho màn hình Product Create/Edit, không phải chỉ chỉnh nhẹ task cũ.
* Chia màn hình Product Create/Edit thành tabs: General, Translations, Images, Options, Variants, Variant Images, Inventory, SEO.
* Product Create page chỉ cần nhập dữ liệu cần thiết ban đầu.
* Khi tạo product thành công, redirect về Product Edit page của product vừa tạo.
* Không redirect về product list sau khi tạo product.
* Product Edit page cần hiển thị đầy đủ tabs.
* Chuyển tab không reload page và không làm mất dữ liệu đang nhập.
* Cải thiện toàn bộ action UX theo nhóm: primary, secondary, inline, danger.
* `Save Changes` là primary action chính.
* Danger actions không đặt sát `Save Changes`.
* Row actions phải gọn, các action ít dùng đưa vào `More`.
* Add Option, Add Option Value, Create Variant, Upload Product Image, Upload Variant Image phải xử lý bằng JavaScript, không reload page.
* Khi thêm option thành công, Variant form phải cập nhật selector mới ngay.
* Khi thêm option value thành công, Variant selector phải cập nhật value mới ngay.
* Loading overlay phải fade in/fade out mượt, không giựt, không nhấp nháy.
* Button đang xử lý phải disabled để tránh submit nhiều lần.
* Tab có validation error cần hiển thị badge error.
* Tab có unsaved data cần hiển thị unsaved state.
* Danger actions cần confirmation.
* Dùng Blade, Tailwind CSS, Alpine.js hoặc Fetch API.
* Không dùng Vue.js.
* Không implement Cart, Checkout, Order hoặc Payment.
* Sau khi làm xong, báo cáo file đã tạo/sửa và lệnh cần chạy để test.


## 23. Additional UX Requirement: Modal Popup Actions in Product Edit

## 23.1. Overview

Màn hình Product Edit cần ưu tiên thao tác trực tiếp bằng popup/modal thay vì bắt admin chuyển qua trang khác.

Các chức năng sau cần hiển thị bằng popup:

* Tạo tùy chọn sản phẩm
* Tạo biến thể sản phẩm
* Điều chỉnh tồn kho
* Upload ảnh product
* Upload ảnh variant

Mục tiêu:

* Admin thao tác nhanh ngay trong màn hình edit sản phẩm.
* Không phải chuyển qua trang quản lý khác.
* Không reload page sau khi lưu.
* Sau khi lưu thành công, list/table/gallery tự động cập nhật ngay.
* UI hiện đại, dễ sử dụng, phù hợp tab layout.

---

## 23.2. Modal UX Rules

Tất cả popup/modal cần tuân theo các nguyên tắc sau:

| Rule                     | Description                                                   |
| ------------------------ | ------------------------------------------------------------- |
| Open without page reload | Mở modal không reload page                                    |
| Submit by JavaScript     | Submit form trong modal bằng JavaScript                       |
| Close after success      | Thành công thì đóng modal                                     |
| Keep open on error       | Lỗi validation thì giữ modal và hiển thị lỗi                  |
| Update current UI        | Thành công thì cập nhật list/table/gallery hiện tại           |
| Smooth animation         | Modal mở/tắt mượt                                             |
| Loading state            | Có loading overlay hoặc loading state trong modal             |
| Disable duplicate submit | Disable button khi đang xử lý                                 |
| Escape key               | Có thể đóng modal bằng ESC nếu chưa submit                    |
| Backdrop click           | Có thể đóng modal bằng backdrop nếu không có dữ liệu chưa lưu |
| Confirmation             | Nếu đang nhập dở mà đóng modal, cần cảnh báo nếu phù hợp      |

Không dùng Vue.js.

Có thể dùng:

* Blade
* Tailwind CSS
* Alpine.js
* Fetch API

---

## 23.3. Add Product Option Modal

Nút `Add Option` hoặc `Thêm tùy chọn` trong tab `Options` cần mở popup để nhập thông tin.

### Modal fields

| Field        | Required | Description                |
| ------------ | -------- | -------------------------- |
| Option Name  | Yes      | Ví dụ Color, Size, Storage |
| Display Name | No       | Tên hiển thị nếu cần       |
| Sort Order   | No       | Thứ tự hiển thị            |
| Status       | Yes      | Active hoặc inactive       |

### Expected behavior

* Admin bấm `Add Option`.
* Modal mở ra.
* Admin nhập thông tin option.
* Admin bấm `Save`.
* Hệ thống gửi request bằng JavaScript.
* Trang không reload.
* Nếu thành công:

  * Modal đóng lại.
  * Option mới xuất hiện ngay trong Options list.
  * Option mới có khu vực thêm value.
  * Variant form tự cập nhật selector mới tương ứng.
  * Hiển thị success message.
* Nếu lỗi:

  * Modal không đóng.
  * Hiển thị validation error ngay trong modal.
  * Dữ liệu đã nhập không bị mất.

### Validation

| Case                  | Expected Result          |
| --------------------- | ------------------------ |
| Option name trống     | Hiển thị lỗi trong modal |
| Option name trùng     | Hiển thị lỗi trong modal |
| Product không tồn tại | Hiển thị lỗi phù hợp     |
| User không có quyền   | Chặn thao tác            |

---

## 23.4. Add Option Value Modal

Nút `Add Value` hoặc `Thêm giá trị` trong mỗi option card cần mở popup để nhập value.

### Modal fields

| Field         | Required | Description                     |
| ------------- | -------- | ------------------------------- |
| Value         | Yes      | Ví dụ Black, White, M, L, 128GB |
| Display Value | No       | Tên hiển thị nếu cần            |
| Color Code    | No       | Dùng nếu option là Color        |
| Sort Order    | No       | Thứ tự                          |
| Status        | Yes      | Active hoặc inactive            |

### Expected behavior

* Admin bấm `Add Value` trong một option card.
* Modal mở ra đúng theo option đang chọn.
* Admin nhập value.
* Admin bấm `Save`.
* Submit bằng JavaScript.
* Trang không reload.
* Nếu thành công:

  * Modal đóng lại.
  * Value mới xuất hiện trong option card.
  * Value mới xuất hiện ngay trong selector tạo variant.
  * Nếu option trước đó chưa có value, variant form cập nhật trạng thái.
* Nếu lỗi:

  * Modal không đóng.
  * Hiển thị lỗi ngay trong modal.

---

## 23.5. Create Variant Modal

Nút `Create Variant` hoặc `Tạo biến thể` trong tab `Variants` cần mở popup để admin nhập thông tin variant.

### Modal fields

| Field         | Required | Description                               |
| ------------- | -------- | ----------------------------------------- |
| Option Values | Yes      | Chọn đủ value cho từng option active      |
| SKU           | Yes      | SKU riêng của variant                     |
| Name          | No       | Tên variant, có thể auto từ option values |
| Price         | No       | Giá riêng nếu có                          |
| Sale Price    | No       | Giá khuyến mãi riêng nếu có               |
| Status        | Yes      | Active hoặc inactive                      |

### Expected behavior

* Admin bấm `Create Variant`.
* Modal mở ra.
* Modal hiển thị selector theo các option hiện tại.
* Nếu product chưa có option, hiển thị message yêu cầu tạo option trước.
* Nếu option chưa có value, hiển thị message yêu cầu thêm value trước.
* Admin nhập thông tin variant.
* Admin bấm `Save`.
* Submit bằng JavaScript.
* Trang không reload.
* Nếu thành công:

  * Modal đóng lại.
  * Variant mới xuất hiện ngay trong Variant table.
  * Inventory tab/table có thể cập nhật stock row tương ứng nếu cần.
  * Variant Images tab có thể cập nhật danh sách variant.
  * Hiển thị success message.
* Nếu lỗi:

  * Modal không đóng.
  * Hiển thị lỗi validation ngay trong modal.

### Validation

| Case                             | Expected Result          |
| -------------------------------- | ------------------------ |
| Chưa chọn đủ option values       | Hiển thị lỗi trong modal |
| SKU trống                        | Hiển thị lỗi trong modal |
| SKU trùng                        | Hiển thị lỗi trong modal |
| Duplicate combination            | Hiển thị lỗi trong modal |
| Sale price lớn hơn price         | Hiển thị lỗi trong modal |
| Option value không thuộc product | Không cho lưu            |

---

## 23.6. Inventory Adjustment Modal

Trong tab `Inventory`, admin cần điều chỉnh tồn kho trực tiếp bằng popup ngay trong màn hình edit product.

Không bắt admin chuyển sang trang quản lý tồn kho riêng.

### Trigger

Có thể mở modal từ:

| Location                       | Action                   |
| ------------------------------ | ------------------------ |
| Inventory tab product row      | Adjust Stock             |
| Inventory tab variant row      | Adjust Stock             |
| Variant row trong Variants tab | Adjust Stock nếu phù hợp |

### Modal fields

| Field              | Required     | Description              |
| ------------------ | ------------ | ------------------------ |
| Product / Variant  | Display only | Tên product hoặc variant |
| Current Quantity   | Display only | Tồn kho hiện tại         |
| Reserved Quantity  | Display only | Số lượng đang giữ        |
| Available Quantity | Display only | Có thể bán               |
| Adjustment Type    | Yes          | Increase, Decrease, Set  |
| Quantity           | Yes          | Số lượng điều chỉnh      |
| Reason             | No           | Lý do                    |
| Note               | No           | Ghi chú                  |

### Expected behavior

* Admin bấm `Adjust Stock`.
* Modal mở ra ngay trong Product Edit.
* Admin nhập thông tin điều chỉnh.
* Submit bằng JavaScript.
* Trang không reload.
* Nếu thành công:

  * Modal đóng lại.
  * Inventory quantity cập nhật ngay trong Inventory tab.
  * Stock status cập nhật ngay.
  * Variant table có thể cập nhật stock nếu đang hiển thị stock.
  * Inventory log cập nhật nếu đang hiển thị recent logs.
  * Hiển thị success message.
* Nếu lỗi:

  * Modal không đóng.
  * Hiển thị lỗi validation ngay trong modal.

### Business rules

* Không cho quantity âm.
* Decrease không được làm quantity nhỏ hơn reserved quantity.
* Set không được nhỏ hơn reserved quantity.
* Cần ghi inventory log.
* Không reload page.
* Không chuyển sang `/admin/inventory`.

---

## 23.7. Product Image Upload Modal

Trong tab `Images`, upload product images cần hiển thị bằng popup.

### Modal fields

| Field      | Required | Description        |
| ---------- | -------- | ------------------ |
| Images     | Yes      | Một hoặc nhiều ảnh |
| Alt Text   | No       | Alt text           |
| Sort Order | No       | Thứ tự             |
| Status     | No       | Active/inactive    |

### Expected behavior

* Admin bấm `Upload Images`.
* Modal mở ra.
* Admin chọn ảnh.
* Submit bằng JavaScript.
* Trang không reload.
* Nếu thành công:

  * Modal đóng lại.
  * Ảnh mới xuất hiện ngay trong product image gallery.
  * Nếu là ảnh đầu tiên, có thể tự set main image.
  * Hiển thị success message.
* Nếu lỗi:

  * Modal không đóng.
  * Hiển thị lỗi trong modal.
  * Ảnh cũ không bị mất.

---

## 23.8. Variant Image Upload Modal

Trong tab `Variant Images`, admin cần upload ảnh biến thể bằng popup, không chuyển sang trang quản lý ảnh riêng.

### Trigger

Có thể mở modal từ:

| Location                                  | Action                           |
| ----------------------------------------- | -------------------------------- |
| Variant Images tab                        | Upload Images                    |
| Variant row trong Variants tab            | Manage Images hoặc Upload Images |
| Image count / thumbnail trong variant row | Click để mở modal                |

### Modal fields

| Field      | Required | Description                                       |
| ---------- | -------- | ------------------------------------------------- |
| Variant    | Yes      | Chọn variant hoặc auto theo variant đang thao tác |
| Images     | Yes      | Một hoặc nhiều ảnh                                |
| Alt Text   | No       | Alt text                                          |
| Sort Order | No       | Thứ tự                                            |
| Status     | No       | Active/inactive                                   |

### Expected behavior

* Admin bấm `Upload Variant Images` hoặc `Manage Images`.
* Modal mở ra.
* Nếu mở từ một variant cụ thể, variant đó được chọn sẵn.
* Admin upload ảnh bằng JavaScript.
* Trang không reload.
* Nếu thành công:

  * Modal đóng lại hoặc giữ lại nếu admin muốn upload thêm.
  * Variant image gallery cập nhật ngay.
  * Thumbnail hoặc image count trong variant row cập nhật ngay.
  * Nếu là ảnh đầu tiên, có thể tự set main image.
* Nếu lỗi:

  * Modal không đóng.
  * Hiển thị lỗi trong modal.
  * Ảnh cũ không bị mất.

Không chuyển sang trang:

`/admin/product-variants/{variant}/images`

trừ khi cần fallback hoặc link phụ.

---

## 23.9. Modal Loading Behavior

Khi submit trong modal:

* Button `Save` hoặc `Upload` phải disabled.
* Text button chuyển thành `Saving...`, `Uploading...`, hoặc `Processing...`.
* Loading overlay hiển thị mượt.
* Modal không được đóng khi request đang xử lý.
* Nếu request thành công, modal đóng sau khi UI cập nhật.
* Nếu request lỗi, modal giữ nguyên và overlay tắt.
* Không tạo duplicate khi admin bấm nhiều lần.

Yêu cầu animation:

| Requirement              | Description                          |
| ------------------------ | ------------------------------------ |
| Modal fade in            | Mở modal mượt                        |
| Modal fade out           | Đóng modal mượt                      |
| Backdrop fade            | Nền mờ xuất hiện mượt                |
| Overlay no flicker       | Không nhấp nháy nếu request nhanh    |
| Minimum display duration | Overlay hiển thị đủ để không bị giựt |

---

## 23.10. UI Update After Modal Success

Sau khi modal submit thành công, UI cần tự cập nhật.

| Modal                | UI cần cập nhật                                                 |
| -------------------- | --------------------------------------------------------------- |
| Add Option           | Options list, option card, variant selector                     |
| Add Option Value     | Option value list, variant selector                             |
| Create Variant       | Variant table, inventory row, variant images selector           |
| Adjust Stock         | Inventory quantity, available quantity, stock status, stock log |
| Upload Product Image | Product image gallery                                           |
| Upload Variant Image | Variant image gallery, variant row thumbnail/image count        |

Không reload page.

Không bắt admin tự refresh.

---

## 23.11. Error Handling In Modal

Lỗi cần hiển thị ngay trong modal.

| Error Type       | Display                                   |
| ---------------- | ----------------------------------------- |
| Validation error | Field error trong modal                   |
| Duplicate error  | Message rõ trong modal                    |
| Permission error | Toast hoặc message trong modal            |
| Server error     | Message chung, không hiển thị stack trace |
| Network error    | Message lỗi kết nối                       |
| CSRF error       | Message yêu cầu reload/login lại nếu cần  |

Modal không được tự đóng khi lỗi.

Dữ liệu admin đã nhập không được mất.

---

## 23.12. Acceptance Criteria Additions

Bổ sung acceptance criteria:

* [ ] Add Option mở modal nhập thông tin.
* [ ] Add Option submit bằng JavaScript, không reload page.
* [ ] Add Option thành công thì option list cập nhật ngay.
* [ ] Add Option thành công thì variant selector cập nhật ngay.
* [ ] Add Option Value mở modal nhập thông tin.
* [ ] Add Option Value submit bằng JavaScript, không reload page.
* [ ] Add Option Value thành công thì option value list và variant selector cập nhật ngay.
* [ ] Create Variant mở modal nhập thông tin.
* [ ] Create Variant submit bằng JavaScript, không reload page.
* [ ] Create Variant thành công thì variant table cập nhật ngay.
* [ ] Inventory Adjust mở modal ngay trong Product Edit.
* [ ] Inventory Adjust submit bằng JavaScript, không chuyển sang trang inventory.
* [ ] Inventory Adjust thành công thì stock trong tab Inventory cập nhật ngay.
* [ ] Product Image Upload mở modal.
* [ ] Product Image Upload submit bằng JavaScript, không reload page.
* [ ] Variant Image Upload mở modal.
* [ ] Variant Image Upload submit bằng JavaScript, không chuyển sang trang quản lý ảnh riêng.
* [ ] Upload thành công thì gallery/list cập nhật ngay.
* [ ] Modal có loading state mượt.
* [ ] Modal không đóng khi validation lỗi.
* [ ] Button trong modal bị disabled khi đang xử lý.
* [ ] Không tạo duplicate khi admin bấm nhiều lần.
* [ ] Không dùng Vue.js.
* [ ] Không implement Cart, Checkout, Order hoặc Payment.


## 24. Additional UX Requirement: Professional Delete Modal and Remove Saved Labels

## 24.1. Overview

Hiện tại chức năng xóa trong danh sách:

* Product Options / Tùy chọn sản phẩm
* Product Variants / Biến thể sản phẩm

đang chưa chuyên nghiệp vì:

* Vẫn dùng popup JavaScript mặc định của browser.
* Popup nhìn đơn điệu, không đồng bộ với admin UI.
* Sau khi confirm delete thì trang bị reload.
* Danh sách Product Options và Product Variants đang hiển thị text `Đã lưu` hoặc `Saved`, làm giao diện bị rối.

Cần cải thiện để:

* Dùng modal xác nhận hiện đại.
* Xóa bằng JavaScript/AJAX.
* Không reload page sau khi confirm.
* UI tự cập nhật sau khi xóa.
* Xóa text `Đã lưu` khỏi danh sách tùy chọn và biến thể.

---

## 24.2. Remove Browser Confirm

Không được dùng native browser confirm cho các action sau:

* Delete Product Option
* Delete Product Option Value
* Delete Product Variant

Không dùng:

`window.confirm()`

Không dùng popup mặc định của browser.

Thay vào đó phải dùng custom confirmation modal theo style admin UI.

---

## 24.3. Delete Product Option Modal

Khi admin bấm xóa một Product Option, hệ thống cần mở modal xác nhận.

Modal cần có:

| Element       | Description                                      |
| ------------- | ------------------------------------------------ |
| Title         | Delete Product Option                            |
| Message       | Hiển thị option sắp xóa                          |
| Warning       | Cảnh báo ảnh hưởng tới option values và variants |
| Cancel Button | Đóng modal                                       |
| Delete Button | Confirm delete, màu danger                       |
| Loading State | Hiển thị khi đang xử lý                          |

Ví dụ nội dung:

Title:

`Delete Product Option`

Message:

`Are you sure you want to delete option "Color"?`

Warning:

`This may remove related option values and affect variant combinations. This action cannot be undone.`

Expected behavior:

* Admin click Delete option.
* Modal mở ra mượt.
* Admin click Cancel thì modal đóng.
* Admin click Delete thì gửi AJAX request.
* Trang không reload.
* Nếu xóa thành công:

  * Modal đóng.
  * Option card bị remove khỏi UI.
  * Option values liên quan biến mất khỏi UI.
  * Variant creation form cập nhật lại selector.
  * Variant table cập nhật nếu cần.
  * Hiển thị success toast.
* Nếu xóa thất bại:

  * Modal không đóng.
  * Hiển thị lỗi trong modal.
  * Trang không reload.

---

## 24.4. Delete Product Variant Modal

Khi admin bấm xóa một Product Variant, hệ thống cần mở modal xác nhận.

Modal cần có:

| Element       | Description                             |
| ------------- | --------------------------------------- |
| Title         | Delete Variant                          |
| Message       | Hiển thị variant sắp xóa                |
| Warning       | Cảnh báo ảnh hưởng tới inventory/images |
| Cancel Button | Đóng modal                              |
| Delete Button | Confirm delete, màu danger              |
| Loading State | Hiển thị khi đang xử lý                 |

Ví dụ nội dung:

Title:

`Delete Variant`

Message:

`Are you sure you want to delete variant "Black / XL"?`

Warning:

`This action cannot be undone. If this variant has stock, images, or related data, consider disabling it instead.`

Expected behavior:

* Admin click Delete variant.
* Modal mở ra mượt.
* Admin click Cancel thì modal đóng.
* Admin click Delete thì gửi AJAX request.
* Trang không reload.
* Nếu xóa thành công:

  * Modal đóng.
  * Variant row bị remove khỏi table.
  * Inventory tab cập nhật nếu có stock row liên quan.
  * Variant Images tab cập nhật selector/list nếu cần.
  * Hiển thị success toast.
* Nếu xóa thất bại:

  * Modal không đóng.
  * Hiển thị lỗi trong modal.
  * Trang không reload.

---

## 24.5. Delete By AJAX

Tất cả delete action trong Product Options và Product Variants phải xử lý bằng JavaScript.

Yêu cầu:

* Request gửi bằng AJAX hoặc Fetch API.
* Có CSRF token.
* Backend trả JSON.
* Không redirect.
* Không reload page.
* Button Delete bị disabled khi đang xử lý.
* Modal hiển thị loading state.
* Không tạo request trùng nếu admin bấm nhiều lần.

Success response cần đủ dữ liệu để frontend cập nhật UI.

Error response cần có message rõ ràng để hiển thị trong modal.

---

## 24.6. UI Update After Delete

Sau khi xóa thành công, UI phải cập nhật ngay.

| Delete Target        | UI Update                                   |
| -------------------- | ------------------------------------------- |
| Product Option       | Remove option card khỏi Options tab         |
| Product Option       | Remove selector tương ứng khỏi Variant form |
| Product Option Value | Remove value khỏi option card               |
| Product Option Value | Remove value khỏi variant selector          |
| Product Variant      | Remove variant row khỏi Variant table       |
| Product Variant      | Update Inventory tab nếu có                 |
| Product Variant      | Update Variant Images tab nếu có            |

Không reload page.

Không bắt admin refresh browser.

---

## 24.7. Delete Animation

Khi xóa thành công, item không nên biến mất quá đột ngột.

Yêu cầu:

* Option card hoặc variant row fade out nhẹ trước khi remove khỏi DOM.
* Animation ngắn, khoảng 150ms - 300ms.
* Không làm chậm thao tác.
* Không gây giật layout.

---

## 24.8. Disable Instead Of Delete

Nếu Product Option hoặc Product Variant đã có dữ liệu liên quan, hệ thống nên ưu tiên disable thay vì hard delete.

Ví dụ dữ liệu liên quan:

* Variant đang dùng option value.
* Variant đã có inventory stock.
* Variant đã có inventory log.
* Variant có ảnh riêng.
* Sau này variant đã phát sinh order.

Expected behavior:

* Nếu không thể hard delete, modal hiển thị message rõ ràng.
* Gợi ý admin dùng Disable thay vì Delete.
* Không reload page.
* Không xóa dữ liệu quan trọng.

Ví dụ message:

`This variant has related inventory data. Please disable it instead of deleting.`

---

## 24.9. Shared Delete Modal

Nên tạo một modal dùng chung cho các delete action trong Product Edit.

Dùng chung cho:

* Delete Product Option
* Delete Product Option Value
* Delete Product Variant
* Delete Product Image
* Delete Variant Image

Yêu cầu:

* Modal dùng chung một style.
* Nội dung title/message/warning thay đổi theo item.
* Không tạo nhiều modal trùng lặp không cần thiết.
* Modal mở/tắt mượt.
* Loading state trong modal phải rõ ràng.

---

## 24.10. Remove Saved Text

Trong danh sách:

* Product Options
* Product Variants

cần xóa text:

* `Đã lưu`
* `Saved`

Yêu cầu:

* Không hiển thị text `Đã lưu` trong option card.
* Không hiển thị text `Saved` trong option card.
* Không hiển thị text `Đã lưu` trong variant row.
* Không hiển thị text `Saved` trong variant row.
* Không dùng badge saved ở danh sách options/variants.

Lý do:

* Làm UI bị rối.
* Không cần thiết sau khi đã có toast success.
* Admin chỉ cần thấy message khi vừa thao tác thành công.

Thay thế bằng:

* Success toast ngắn khi lưu/xóa thành công.
* Error message rõ khi thao tác lỗi.
* Unsaved state chỉ hiển thị khi thật sự có dữ liệu chưa lưu.

---

## 24.11. Toast Feedback

Sau khi delete thành công, hiển thị toast success.

Ví dụ:

* `Option deleted successfully.`
* `Variant deleted successfully.`

Nếu lỗi:

* `Cannot delete this option because it is being used by variants.`
* `Cannot delete this variant because it has inventory data.`

Toast nên:

* Hiển thị gọn.
* Tự ẩn sau vài giây.
* Không che khu vực form chính.
* Đồng bộ với admin UI.

---

## 24.12. Acceptance Criteria Additions

* [ ] Không còn dùng `window.confirm()` cho Delete Option.
* [ ] Không còn dùng `window.confirm()` cho Delete Variant.
* [ ] Delete Option dùng custom modal hiện đại.
* [ ] Delete Variant dùng custom modal hiện đại.
* [ ] Modal có title, message, warning, cancel button và delete button.
* [ ] Delete action submit bằng AJAX.
* [ ] Sau khi confirm delete, trang không reload.
* [ ] Delete thành công thì item bị remove khỏi UI.
* [ ] Delete thành công thì các list/selector liên quan được cập nhật.
* [ ] Delete lỗi thì modal không đóng và hiển thị lỗi.
* [ ] Button Delete bị disabled khi đang xử lý.
* [ ] Có loading state trong modal.
* [ ] Modal mở/tắt mượt.
* [ ] Item bị xóa có fade out nhẹ trước khi biến mất.
* [ ] Có success toast sau khi xóa thành công.
* [ ] Có error message rõ nếu xóa thất bại.
* [ ] Không hard delete dữ liệu đã có liên kết quan trọng.
* [ ] Xóa hoàn toàn text `Đã lưu` khỏi Product Options list.
* [ ] Xóa hoàn toàn text `Saved` khỏi Product Options list.
* [ ] Xóa hoàn toàn text `Đã lưu` khỏi Product Variants list.
* [ ] Xóa hoàn toàn text `Saved` khỏi Product Variants list.
* [ ] Không dùng Vue.js.
* [ ] Không implement Cart, Checkout, Order hoặc Payment.
