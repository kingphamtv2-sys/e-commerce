Bạn tạo file mới:

```txt
docs/tasks/task-15-cart.md
```

Nội dung tài liệu như sau:

# Task 15: Cart

## 1. Overview

Task này dùng để xây dựng chức năng giỏ hàng cho public frontend của hệ thống e-commerce.

Cart là bước trung gian giữa Product Detail và Checkout.

Khách hàng có thể:

* Thêm sản phẩm vào giỏ hàng.
* Thêm biến thể sản phẩm vào giỏ hàng.
* Xem danh sách sản phẩm trong giỏ hàng.
* Tăng hoặc giảm số lượng.
* Xóa sản phẩm khỏi giỏ hàng.
* Xóa toàn bộ giỏ hàng.
* Xem subtotal tạm tính.
* Tiếp tục mua hàng.
* Đi tới Checkout ở task sau.

Cart cần hỗ trợ cả:

* Guest user chưa đăng nhập.
* Customer đã đăng nhập.

Frontend trong MVP sử dụng:

* Laravel Blade
* Tailwind CSS
* Alpine.js nếu cần
* Fetch API nếu cần
* Không dùng Vue.js

---

## 2. Objectives

Sau khi hoàn thành task này, hệ thống cần có:

* Cart hoạt động cho guest user.
* Cart hoạt động cho logged-in customer.
* Guest cart được lưu theo session.
* Customer cart được lưu theo user.
* Khi customer đăng nhập, guest cart có thể merge vào customer cart.
* Add to cart bằng JavaScript, không reload page.
* Update quantity bằng JavaScript, không reload page.
* Remove cart item bằng JavaScript, không reload page.
* Clear cart bằng JavaScript, không reload page.
* Cart icon trên header hiển thị số lượng item.
* Cart page hiển thị sản phẩm, biến thể, ảnh, giá, quantity và subtotal.
* Cart kiểm tra product status, variant status và inventory trước khi thêm hoặc cập nhật quantity.
* Không cho thêm quá số lượng available stock.
* Product có variants thì bắt buộc chọn variant trước khi add to cart.
* Product không có variants thì có thể add product trực tiếp.
* Cart không xử lý Coupon.
* Cart không xử lý Checkout.
* Cart không tạo Order.
* Cart không xử lý Payment.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong task này:

* Cart database design.
* Cart service/business logic.
* Add to cart.
* View cart.
* Update cart item quantity.
* Remove cart item.
* Clear cart.
* Cart count in header.
* Guest cart.
* Customer cart.
* Merge cart after login.
* Basic stock validation.
* Basic unavailable item handling.
* AJAX responses for cart actions.
* Public cart UI.
* Header cart badge.
* Empty cart page.
* Toast feedback.

### 3.2. Out of Scope

Không làm trong task này:

* Không implement Coupon.
* Không implement Checkout.
* Không implement Tax calculation snapshot.
* Không implement Currency snapshot for order.
* Không implement Order.
* Không implement Payment.
* Không implement Shipping fee.
* Không implement Wishlist.
* Không implement Save for later.
* Không implement advanced cart recommendation.
* Không implement abandoned cart email.
* Không implement multi-address checkout.
* Không dùng Vue.js.

---

## 4. User Roles

| Role     | Permission                         |
| -------- | ---------------------------------- |
| Guest    | Có thể dùng cart bằng session      |
| Customer | Có thể dùng cart theo user account |
| Admin    | Không dùng public cart             |
| Staff    | Không dùng public cart             |

---

## 5. Cart Behavior Summary

Cart cần hoạt động như sau:

| Case                           | Behavior                              |
| ------------------------------ | ------------------------------------- |
| Guest add product              | Lưu cart theo session                 |
| Logged-in customer add product | Lưu cart theo user                    |
| Guest đăng nhập                | Merge guest cart vào customer cart    |
| Add same product again         | Tăng quantity thay vì tạo dòng mới    |
| Add same variant again         | Tăng quantity thay vì tạo dòng mới    |
| Add different variant          | Tạo cart item riêng                   |
| Product inactive               | Không cho add                         |
| Variant inactive               | Không cho add                         |
| Product out of stock           | Không cho add                         |
| Quantity vượt stock            | Giới hạn theo available stock         |
| Item không còn hợp lệ          | Hiển thị unavailable state trong cart |

---

## 6. Database Design

## 6.1. carts Table

Tạo bảng `carts` để lưu cart chính.

Fields đề xuất:

| Field         | Type              | Description                           |
| ------------- | ----------------- | ------------------------------------- |
| id            | bigint            | Primary key                           |
| user_id       | nullable bigint   | Customer owner nếu đã đăng nhập       |
| session_id    | nullable string   | Guest cart session identifier         |
| status        | string            | active, converted, abandoned          |
| currency_code | nullable string   | Currency hiện tại để hiển thị nếu cần |
| expires_at    | nullable datetime | Hết hạn cho guest cart nếu cần        |
| created_at    | timestamp         | Created time                          |
| updated_at    | timestamp         | Updated time                          |

Business rules:

* Một customer chỉ nên có một active cart.
* Một guest session chỉ nên có một active cart.
* Khi cart đã checkout thành order sau này, status có thể đổi thành `converted`.
* Guest cart có thể hết hạn sau một khoảng thời gian.
* Không xóa cart ngay khi checkout; task Order/Checkout sẽ xử lý sau.

---

## 6.2. cart_items Table

Tạo bảng `cart_items` để lưu từng dòng item trong cart.

Fields đề xuất:

| Field              | Type             | Description                                      |
| ------------------ | ---------------- | ------------------------------------------------ |
| id                 | bigint           | Primary key                                      |
| cart_id            | bigint           | Reference carts                                  |
| product_id         | bigint           | Product được thêm                                |
| product_variant_id | nullable bigint  | Variant nếu product có variant                   |
| quantity           | integer          | Số lượng                                         |
| unit_price         | decimal nullable | Giá hiện tại tại thời điểm cập nhật cart nếu cần |
| created_at         | timestamp        | Created time                                     |
| updated_at         | timestamp        | Updated time                                     |

Business rules:

* Nếu product có variants, `product_variant_id` là bắt buộc.
* Nếu product không có variants, `product_variant_id` là null.
* Không tạo hai cart item giống nhau cho cùng product/variant.
* Nếu item đã tồn tại, tăng quantity.
* Quantity phải lớn hơn 0.
* Quantity không được vượt available stock.
* Giá trong cart chỉ là giá tạm tính, Order snapshot sẽ xử lý ở Checkout/Order task.

Unique rule đề xuất:

| Rule                                      | Description                              |
| ----------------------------------------- | ---------------------------------------- |
| cart_id + product_id + product_variant_id | Không duplicate cùng item trong một cart |

Lưu ý với database nullable unique:

* Cần xử lý duplicate product không variant ở backend cẩn thận.
* Không chỉ dựa vào unique index nếu database xử lý null khác nhau.

---

## 6.3. Relationships

Relationships cần có:

| Model          | Relationship                      |
| -------------- | --------------------------------- |
| Cart           | belongsTo User                    |
| Cart           | hasMany CartItem                  |
| CartItem       | belongsTo Cart                    |
| CartItem       | belongsTo Product                 |
| CartItem       | belongsTo ProductVariant nullable |
| Product        | hasMany CartItem                  |
| ProductVariant | hasMany CartItem                  |

---

## 7. Price Rules

Cart hiển thị giá tạm tính, chưa phải final order price.

### 7.1. Product Without Variant

Nếu product không có variants:

| Price Source       | Priority                     |
| ------------------ | ---------------------------- |
| product.sale_price | Ưu tiên nếu có và hợp lệ     |
| product.price      | Dùng nếu không có sale_price |

### 7.2. Product With Variant

Nếu product có variant:

| Price Source       | Priority                                |
| ------------------ | --------------------------------------- |
| variant.sale_price | Ưu tiên nếu có và hợp lệ                |
| variant.price      | Dùng nếu có                             |
| product.sale_price | Fallback nếu variant không có giá riêng |
| product.price      | Fallback cuối                           |

### 7.3. Cart Price Recalculation

Business rules:

* Cart subtotal nên được tính lại khi view cart hoặc update quantity.
* Nếu giá sản phẩm thay đổi sau khi customer thêm vào cart, cart nên hiển thị giá hiện tại.
* Nếu muốn hiển thị cảnh báo giá thay đổi, có thể làm sau.
* Checkout/Order task sẽ snapshot price chính thức.

---

## 8. Inventory Rules

Cart cần kiểm tra inventory nhưng chưa trừ kho.

### 8.1. Add To Cart

Khi add to cart:

* Product phải active.
* Category nếu có status thì cần hợp lệ.
* Nếu product có variants, variant phải active.
* Product hoặc variant phải còn hàng.
* Quantity request phải lớn hơn 0.
* Quantity mới sau khi cộng dồn không được vượt available stock.

### 8.2. Update Quantity

Khi update quantity:

* Quantity phải lớn hơn 0.
* Quantity không được vượt available stock.
* Nếu quantity bằng 0, nên dùng remove action thay vì update.
* Nếu product/variant hết hàng, hiển thị lỗi.

### 8.3. Stock Reservation

Trong Task 15:

* Cart không reserve stock.
* Cart không deduct stock.
* Cart chỉ validate available stock.
* Stock deduction hoặc reservation sẽ xử lý ở Checkout/Order/Inventory task sau.

---

## 9. Product Variant Rules

Product variants đã được thiết kế ở Task 10.1.

Cart cần tương thích với product variants.

### 9.1. Product Has Variants

Nếu product có active variants:

* Customer phải chọn variant trước khi add to cart.
* Không cho add product parent trực tiếp.
* Cart item phải lưu `product_variant_id`.
* Cart page phải hiển thị variant name hoặc option values.

Ví dụ hiển thị:

`Black / XL`

hoặc:

`Color: Black, Size: XL`

### 9.2. Product Without Variants

Nếu product không có active variants:

* Customer có thể add product trực tiếp.
* Cart item lưu `product_id`.
* `product_variant_id` là null.

---

## 10. Product Image Rules

Cart item cần hiển thị ảnh phù hợp.

Image fallback priority:

| Priority | Image                      |
| -------- | -------------------------- |
| 1        | Variant main image active  |
| 2        | Variant first active image |
| 3        | Product main image active  |
| 4        | Product first active image |
| 5        | Placeholder image          |

Business rules:

* Nếu cart item có variant, ưu tiên variant image.
* Nếu variant không có ảnh, dùng product image.
* Nếu không có ảnh nào, dùng placeholder.

---

## 11. Guest Cart

Guest cart dùng session để nhận diện.

Expected behavior:

* Khi guest add product vào cart, hệ thống tạo hoặc lấy active cart theo session.
* Cart badge trên header hiển thị count.
* Guest có thể xem cart page.
* Guest có thể update quantity.
* Guest có thể remove item.
* Guest có thể clear cart.
* Guest cart tồn tại trong session.
* Có thể lưu session_id trong `carts` table.

---

## 12. Customer Cart

Customer cart dùng user_id để nhận diện.

Expected behavior:

* Khi customer đã đăng nhập, cart được lưu theo user.
* Customer có thể quay lại và thấy cart cũ.
* Mỗi customer chỉ có một active cart.
* Nếu customer có nhiều active cart do dữ liệu cũ, hệ thống nên chọn cart mới nhất hoặc merge lại.

---

## 13. Merge Guest Cart After Login

Khi guest đăng nhập thành customer:

* Nếu guest cart có item.
* Nếu customer đã có active cart.
* Hệ thống merge guest cart vào customer cart.

Merge rules:

| Case                          | Behavior                                   |
| ----------------------------- | ------------------------------------------ |
| Same product + same variant   | Cộng quantity                              |
| Same product without variant  | Cộng quantity                              |
| Different variant             | Tạo item riêng                             |
| Quantity sau merge vượt stock | Giới hạn theo available stock hoặc báo lỗi |
| Guest item không hợp lệ       | Bỏ qua hoặc đánh dấu unavailable           |
| Customer chưa có cart         | Gán guest cart cho customer                |

Sau khi merge:

* Guest cart không còn active riêng.
* Customer cart là cart chính.
* Header cart badge cập nhật.

---

## 14. Public Routes

Routes đề xuất:

| Method | URL                | Name               | Description                                |
| ------ | ------------------ | ------------------ | ------------------------------------------ |
| GET    | /cart              | cart.index         | Xem cart page                              |
| POST   | /cart/items        | cart.items.store   | Add to cart                                |
| PATCH  | /cart/items/{item} | cart.items.update  | Update quantity                            |
| DELETE | /cart/items/{item} | cart.items.destroy | Remove cart item                           |
| DELETE | /cart              | cart.clear         | Clear cart                                 |
| GET    | /cart/summary      | cart.summary       | Lấy cart summary cho header/drawer nếu cần |

Lưu ý:

* Các action thay đổi cart cần CSRF.
* AJAX request nên trả JSON.
* GET `/cart` trả Blade view.
* Cart item update/delete phải kiểm tra quyền sở hữu cart.

---

## 15. API Response Requirements

Các action AJAX cần trả JSON.

### 15.1. Add To Cart Success

Response cần có:

| Field          | Description           |
| -------------- | --------------------- |
| success        | true                  |
| message        | Message thành công    |
| cart_count     | Tổng số lượng item    |
| cart_subtotal  | Subtotal formatted    |
| item           | Cart item data        |
| mini_cart_html | Optional HTML partial |
| cart_row_html  | Optional HTML partial |

### 15.2. Update Quantity Success

Response cần có:

| Field              | Description             |
| ------------------ | ----------------------- |
| success            | true                    |
| message            | Message thành công      |
| item_quantity      | Quantity mới            |
| item_subtotal      | Subtotal của item       |
| cart_count         | Tổng quantity           |
| cart_subtotal      | Cart subtotal           |
| available_quantity | Available stock nếu cần |

### 15.3. Remove Item Success

Response cần có:

| Field           | Description        |
| --------------- | ------------------ |
| success         | true               |
| message         | Message thành công |
| removed_item_id | ID item đã xóa     |
| cart_count      | Tổng quantity mới  |
| cart_subtotal   | Cart subtotal mới  |
| is_empty        | Cart có rỗng không |

### 15.4. Error Response

Response lỗi cần có:

| Field   | Description              |
| ------- | ------------------------ |
| success | false                    |
| message | Message lỗi              |
| errors  | Validation errors nếu có |

Business rules:

* AJAX không redirect.
* AJAX không trả HTML error page.
* Validation lỗi trả JSON.
* Unauthorized access trả lỗi rõ ràng.

---

## 16. UI Requirements

## 16.1. Header Cart Badge

Header public frontend cần có cart icon.

Expected behavior:

* Hiển thị cart count.
* Count là tổng quantity trong cart.
* Sau add/update/remove/clear, badge cập nhật ngay.
* Nếu cart rỗng, badge có thể ẩn hoặc hiển thị 0.
* Click cart icon dẫn tới `/cart` hoặc mở mini cart drawer nếu có.

---

## 16.2. Add To Cart Button

Ở Product Detail page:

* Product không có variants: hiển thị quantity selector và Add to Cart.
* Product có variants: bắt buộc chọn variant trước khi Add to Cart.
* Nếu chưa chọn variant, hiển thị warning.
* Nếu hết hàng, disable Add to Cart.
* Add to Cart xử lý bằng JavaScript, không reload page.
* Sau khi add thành công, hiển thị toast success.
* Header cart count cập nhật ngay.

Button states:

| State            | Display              |
| ---------------- | -------------------- |
| Default          | Add to Cart          |
| Loading          | Adding...            |
| Success          | Added                |
| Out of stock     | Out of Stock         |
| Variant required | Select variant first |

---

## 16.3. Cart Page

URL:

`/cart`

Cart page cần hiện đại, rõ ràng và responsive.

Cart page cần hiển thị:

| Element             | Description                     |
| ------------------- | ------------------------------- |
| Product image       | Ảnh product hoặc variant        |
| Product name        | Tên sản phẩm                    |
| Variant info        | Option values nếu có            |
| SKU                 | SKU product hoặc variant nếu có |
| Unit price          | Giá hiện tại                    |
| Quantity control    | Tăng/giảm hoặc input            |
| Item subtotal       | Unit price x quantity           |
| Remove item         | Xóa item                        |
| Cart subtotal       | Tổng tạm tính                   |
| Continue shopping   | Quay lại catalog                |
| Proceed to checkout | Đi tới checkout task sau        |

Lưu ý:

* Nút Proceed to Checkout có thể dẫn tới `/checkout` nếu route đã có sau này.
* Nếu Checkout chưa implement, có thể hiển thị disabled hoặc link sẽ dùng trong task sau.
* Không implement Checkout trong Task 15.

---

## 16.4. Quantity Control

Cart item quantity control cần dễ dùng.

Expected behavior:

* Có nút giảm quantity.
* Có input quantity.
* Có nút tăng quantity.
* Update bằng JavaScript.
* Không reload page.
* Nếu quantity vượt stock, hiển thị lỗi.
* Nếu quantity giảm về 0, nên hỏi remove hoặc không cho nhỏ hơn 1.
* Button đang xử lý phải disabled.

---

## 16.5. Remove Cart Item

Remove item cần chuyên nghiệp.

Expected behavior:

* Không dùng browser confirm mặc định.
* Có thể dùng custom confirmation modal hoặc inline remove action tùy thiết kế.
* Remove bằng JavaScript.
* Không reload page.
* Sau khi remove thành công:

  * Row item fade out nhẹ.
  * Item bị remove khỏi DOM.
  * Cart subtotal cập nhật.
  * Header cart count cập nhật.
  * Nếu cart rỗng, hiển thị empty cart state.

---

## 16.6. Clear Cart

Clear cart dùng để xóa toàn bộ item.

Expected behavior:

* Có confirmation modal.
* Không dùng browser confirm mặc định.
* Submit bằng JavaScript.
* Không reload page.
* Sau khi clear thành công:

  * Cart items biến mất.
  * Cart subtotal về 0.
  * Header cart count về 0.
  * Hiển thị empty cart state.
  * Hiển thị toast success.

---

## 16.7. Empty Cart State

Nếu cart rỗng, hiển thị empty state đẹp.

Empty state nên có:

* Icon hoặc illustration đơn giản.
* Message rõ ràng.
* Button `Continue Shopping`.
* Không hiển thị table trống.

Message gợi ý:

`Your cart is empty.`

---

## 16.8. Mini Cart / Cart Drawer

Mini cart hoặc cart drawer là optional trong MVP.

Nếu làm, cần có:

* Danh sách item ngắn.
* Product image.
* Product name.
* Quantity.
* Subtotal.
* View Cart button.
* Checkout button nếu checkout đã có.
* Remove item bằng AJAX.

Nếu không làm drawer trong MVP, header cart icon chỉ cần dẫn tới `/cart`.

---

## 17. Loading UX

Các action cart cần loading state mượt:

* Add to cart.
* Update quantity.
* Remove item.
* Clear cart.

Yêu cầu:

* Button đang xử lý phải disabled.
* Không cho bấm nhiều lần.
* Loading text phù hợp.
* Có spinner nhỏ nếu cần.
* Không reload page.
* Không làm layout bị giật.
* Toast success/error rõ ràng.

---

## 18. Toast / Message Behavior

Sau mỗi action cần feedback rõ.

| Action          | Success Message             |
| --------------- | --------------------------- |
| Add to cart     | Added to cart successfully. |
| Update quantity | Cart updated.               |
| Remove item     | Item removed from cart.     |
| Clear cart      | Cart cleared.               |

Error message ví dụ:

| Error                  | Message                              |
| ---------------------- | ------------------------------------ |
| Product inactive       | This product is no longer available. |
| Variant inactive       | This variant is no longer available. |
| Out of stock           | This item is out of stock.           |
| Quantity exceeds stock | Only X item(s) available.            |
| Invalid quantity       | Please enter a valid quantity.       |

---

## 19. Validation Rules

### 19.1. Add To Cart

| Field              | Rule                             |
| ------------------ | -------------------------------- |
| product_id         | Required, exists, active         |
| product_variant_id | Required if product has variants |
| quantity           | Required, integer, min 1         |
| quantity           | Must not exceed available stock  |

### 19.2. Update Cart Item

| Field     | Rule                            |
| --------- | ------------------------------- |
| quantity  | Required, integer, min 1        |
| quantity  | Must not exceed available stock |
| cart_item | Must belong to current cart     |

### 19.3. Remove Cart Item

| Field     | Rule                        |
| --------- | --------------------------- |
| cart_item | Must belong to current cart |

### 19.4. Clear Cart

| Rule                  | Description                    |
| --------------------- | ------------------------------ |
| Current cart required | Clear only current active cart |
| Ownership required    | User/session must own cart     |

---

## 20. Business Logic

## 20.1. Get Current Cart

System cần có logic lấy current cart:

| User State | Cart Lookup               |
| ---------- | ------------------------- |
| Guest      | Active cart by session_id |
| Logged-in  | Active cart by user_id    |

Nếu chưa có cart:

* Tạo cart mới khi add item.
* Không nhất thiết tạo cart khi chỉ view empty cart.

---

## 20.2. Add Item Logic

Add item flow:

* Validate product.
* Validate variant nếu có.
* Validate quantity.
* Resolve current cart.
* Check existing cart item.
* Nếu item đã tồn tại, cộng quantity.
* Nếu chưa tồn tại, tạo cart item.
* Validate final quantity không vượt available stock.
* Recalculate cart subtotal.
* Return JSON hoặc redirect phù hợp.

---

## 20.3. Update Quantity Logic

Update quantity flow:

* Validate item thuộc current cart.
* Validate quantity.
* Validate product/variant availability.
* Validate stock.
* Update quantity.
* Recalculate subtotal.
* Return JSON.

---

## 20.4. Remove Item Logic

Remove item flow:

* Validate item thuộc current cart.
* Delete cart item.
* Recalculate subtotal.
* Nếu cart empty, return empty state info.
* Return JSON.

---

## 20.5. Clear Cart Logic

Clear cart flow:

* Resolve current cart.
* Delete all cart items.
* Return cart empty response.
* Header cart count về 0.

---

## 20.6. Recalculate Cart Summary

Cart summary gồm:

| Field              | Description         |
| ------------------ | ------------------- |
| total_items        | Tổng quantity       |
| subtotal           | Tổng tiền hàng      |
| formatted_subtotal | Subtotal formatted  |
| currency_code      | Currency hiện tại   |
| is_empty           | Cart rỗng hay không |

Lưu ý:

* Cart chưa tính coupon.
* Cart chưa tính tax final.
* Cart chưa tính shipping.
* Cart chưa tạo order.
* Có thể hiển thị note: `Taxes, discounts and shipping calculated at checkout.`

---

## 21. Unavailable Items

Khi cart chứa item không còn hợp lệ:

Ví dụ:

* Product bị inactive.
* Variant bị inactive.
* Product bị xóa mềm.
* Variant bị xóa mềm.
* Stock không đủ.
* Price không còn hợp lệ.

Expected behavior:

* Cart page vẫn load được.
* Item hiển thị trạng thái unavailable.
* Customer có thể remove item.
* Không cho proceed checkout nếu còn unavailable item.
* Hiển thị message rõ ràng.

Ví dụ:

`This item is no longer available. Please remove it from your cart.`

---

## 22. Currency Display

Cart cần hiển thị currency theo currency hiện tại của frontend.

Business rules:

* Dùng currency active/current từ hệ thống.
* Cart chỉ hiển thị formatted price.
* Không snapshot currency trong Task 15.
* Currency snapshot sẽ xử lý ở Checkout/Order task.
* Nếu currency conversion chưa hoàn chỉnh, hiển thị theo default currency.

---

## 23. Tax Display

Trong Task 15:

* Không tính tax final.
* Không snapshot tax.
* Không tạo tax line.
* Có thể hiển thị note:

`Taxes will be calculated at checkout.`

Tax calculation chính thức sẽ xử lý ở Task 17.

---

## 24. Coupon

Trong Task 15:

* Không implement coupon.
* Không có apply coupon form.
* Không có discount line.

Coupon sẽ xử lý ở Task 16.

---

## 25. Checkout Button

Cart page có thể có button:

`Proceed to Checkout`

Expected behavior:

* Nếu Checkout route chưa implement, button có thể disabled hoặc route sẽ được bổ sung ở Task 17.
* Nếu cart empty, disable checkout.
* Nếu cart có unavailable item, disable checkout.
* Nếu cart có quantity vượt stock, disable checkout.
* Không implement checkout trong task này.

---

## 26. Security Requirements

Yêu cầu bảo mật:

* CSRF cho tất cả POST/PATCH/DELETE.
* Không cho update cart item không thuộc current cart.
* Không cho remove cart item không thuộc current cart.
* Không cho customer truy cập cart của user khác.
* Không tin quantity từ frontend.
* Validate stock ở backend.
* Validate product/variant status ở backend.
* Không expose lỗi kỹ thuật ra UI.
* Không dùng product price gửi từ frontend để tính cart.
* Không dùng variant price gửi từ frontend để tính cart.
* Price phải lấy từ database.

---

## 27. Performance Requirements

Cart thường nhỏ nhưng vẫn cần tối ưu:

* Eager load product, translation, images, variant, variant option values nếu cần.
* Không query lặp quá nhiều khi render cart.
* Cart count ở header nên lấy từ service/helper.
* AJAX response nên trả đủ dữ liệu để update UI, tránh reload full page.
* Không lưu dữ liệu quá lớn vào session.

---

## 28. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type             | Description                                     |
| ---------------- | ----------------------------------------------- |
| Migration        | carts, cart_items                               |
| Models           | Cart, CartItem                                  |
| Controller       | CartController                                  |
| Service          | CartService nếu cần                             |
| Requests         | AddToCartRequest, UpdateCartItemRequest nếu cần |
| Routes           | Public cart routes                              |
| Middleware/Event | Merge guest cart after login nếu cần            |
| Blade Views      | cart index page                                 |
| Blade Partials   | cart item row, empty cart, cart summary         |
| Frontend JS      | Add/update/remove/clear cart AJAX               |
| Header View      | Cart badge count                                |
| Tests            | Feature tests cho cart                          |

---

## 29. Route Design

Routes nên đặt trong public/web routes.

| Method | URL                | Controller Action | Purpose           |
| ------ | ------------------ | ----------------- | ----------------- |
| GET    | /cart              | index             | Render cart page  |
| POST   | /cart/items        | store             | Add item          |
| PATCH  | /cart/items/{item} | update            | Update quantity   |
| DELETE | /cart/items/{item} | destroy           | Remove item       |
| DELETE | /cart              | clear             | Clear cart        |
| GET    | /cart/summary      | summary           | Cart summary JSON |

Naming convention:

| Route Name         | Purpose         |
| ------------------ | --------------- |
| cart.index         | Cart page       |
| cart.items.store   | Add item        |
| cart.items.update  | Update quantity |
| cart.items.destroy | Remove item     |
| cart.clear         | Clear cart      |
| cart.summary       | Cart summary    |

---

## 30. UI Pages

## 30.1. Cart Page Layout

Cart page desktop layout đề xuất:

| Left Area         | Right Area                  |
| ----------------- | --------------------------- |
| Cart items list   | Cart summary box            |
| Quantity controls | Subtotal                    |
| Remove item       | Continue / Checkout buttons |

Mobile layout:

* Items hiển thị dạng card.
* Summary box nằm dưới danh sách item.
* Buttons full width.
* Quantity control dễ bấm.

---

## 30.2. Cart Item Display

Mỗi cart item cần hiển thị:

* Image.
* Product name.
* Variant values nếu có.
* SKU nếu có.
* Unit price.
* Quantity control.
* Item subtotal.
* Remove button.
* Stock warning nếu có.

---

## 30.3. Cart Summary Display

Cart summary cần hiển thị:

| Line     | Description                                                |
| -------- | ---------------------------------------------------------- |
| Subtotal | Tổng tiền hàng                                             |
| Discount | Không hiển thị trong Task 15                               |
| Tax      | Note only                                                  |
| Shipping | Note only                                                  |
| Total    | Có thể hiển thị bằng subtotal tạm thời hoặc không hiển thị |

Note:

`Taxes, discounts and shipping will be calculated at checkout.`

---

## 31. Error Handling

| Scenario               | Expected Result                |
| ---------------------- | ------------------------------ |
| Product not found      | Show error message             |
| Product inactive       | Show unavailable error         |
| Variant required       | Ask customer to select variant |
| Variant invalid        | Show error                     |
| Quantity invalid       | Show validation error          |
| Quantity exceeds stock | Show available stock message   |
| Cart item not owned    | Return unauthorized/forbidden  |
| AJAX server error      | Show toast error               |
| Network error          | Show network error             |
| Cart empty             | Show empty state               |

---

## 32. Accessibility Requirements

Cart UI cần đảm bảo:

* Buttons có label rõ ràng.
* Quantity input có label hoặc aria-label.
* Toast không che nội dung chính.
* Modal confirmation nếu có phải focus đúng.
* Keyboard có thể thao tác được.
* Remove buttons có text hoặc tooltip rõ.
* Color không phải tín hiệu duy nhất cho lỗi.

---

## 33. Test Cases

| Test Case ID | Scenario                                             | Expected Result                    |
| ------------ | ---------------------------------------------------- | ---------------------------------- |
| TC-001       | Guest mở cart empty                                  | Hiển thị empty cart state          |
| TC-002       | Guest add product không variant                      | Item được thêm vào cart            |
| TC-003       | Guest add product có variant nhưng chưa chọn variant | Hiển thị lỗi                       |
| TC-004       | Guest add product variant hợp lệ                     | Variant được thêm vào cart         |
| TC-005       | Add cùng product lần 2                               | Quantity tăng                      |
| TC-006       | Add cùng variant lần 2                               | Quantity tăng                      |
| TC-007       | Add variant khác                                     | Tạo item riêng                     |
| TC-008       | Add quantity vượt stock                              | Hiển thị lỗi                       |
| TC-009       | Update quantity hợp lệ                               | Quantity và subtotal cập nhật      |
| TC-010       | Update quantity vượt stock                           | Hiển thị lỗi                       |
| TC-011       | Remove cart item                                     | Item bị remove, không reload page  |
| TC-012       | Clear cart                                           | Cart rỗng, không reload page       |
| TC-013       | Header cart badge update sau add                     | Count cập nhật                     |
| TC-014       | Header cart badge update sau remove                  | Count cập nhật                     |
| TC-015       | Cart item dùng variant image                         | Hiển thị variant image             |
| TC-016       | Variant không có image                               | Fallback product image             |
| TC-017       | Product inactive trong cart                          | Hiển thị unavailable               |
| TC-018       | Variant inactive trong cart                          | Hiển thị unavailable               |
| TC-019       | Guest đăng nhập                                      | Guest cart merge vào customer cart |
| TC-020       | Customer có cart cũ và guest cart                    | Items được merge                   |
| TC-021       | Update cart item không thuộc cart                    | Bị chặn                            |
| TC-022       | Remove cart item không thuộc cart                    | Bị chặn                            |
| TC-023       | AJAX add success                                     | Không reload page                  |
| TC-024       | AJAX update success                                  | Không reload page                  |
| TC-025       | AJAX remove success                                  | Không reload page                  |
| TC-026       | Mobile cart page                                     | Layout không vỡ                    |
| TC-027       | Cart không apply coupon                              | Không có coupon behavior           |
| TC-028       | Cart không tạo order                                 | Không có order được tạo            |

---

## 34. Acceptance Criteria

Task này hoàn thành khi:

* [ ] Có database tables cho carts và cart_items.
* [ ] Guest user có thể add item vào cart.
* [ ] Logged-in customer có thể add item vào cart.
* [ ] Customer có một active cart.
* [ ] Guest cart dùng session.
* [ ] Guest cart merge vào customer cart sau login.
* [ ] Product không variant có thể add trực tiếp.
* [ ] Product có variants bắt buộc chọn variant.
* [ ] Same product/variant add lần nữa thì tăng quantity.
* [ ] Không tạo duplicate cart item cho cùng product/variant.
* [ ] Không cho add inactive product.
* [ ] Không cho add inactive variant.
* [ ] Không cho add quá available stock.
* [ ] Không trừ stock ở Cart task.
* [ ] Cart page hiển thị product image.
* [ ] Cart page ưu tiên variant image nếu có.
* [ ] Cart page hiển thị product name.
* [ ] Cart page hiển thị variant values nếu có.
* [ ] Cart page hiển thị unit price, quantity và item subtotal.
* [ ] Cart summary hiển thị subtotal.
* [ ] Header cart badge hiển thị cart count.
* [ ] Add to cart bằng JavaScript, không reload page.
* [ ] Update quantity bằng JavaScript, không reload page.
* [ ] Remove item bằng JavaScript, không reload page.
* [ ] Clear cart bằng JavaScript, không reload page.
* [ ] Cart UI có loading state.
* [ ] Cart UI có toast success/error.
* [ ] Empty cart state hiển thị đẹp.
* [ ] Unavailable item được xử lý rõ ràng.
* [ ] Cart không implement Coupon.
* [ ] Cart không implement Checkout.
* [ ] Cart không implement Order.
* [ ] Cart không implement Payment.
* [ ] Không dùng Vue.js.

---

## 35. Commands

Sau khi implement, chạy các lệnh:

| Command                | Purpose               |
| ---------------------- | --------------------- |
| php artisan migrate    | Chạy migration        |
| php artisan route:list | Kiểm tra route        |
| php artisan test       | Chạy test nếu có      |
| npm run build          | Build frontend assets |
| php artisan serve      | Chạy local server     |

URL test:

`http://127.0.0.1:8000/cart`

---

## 36. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-10-product-management-with-translation.md
* docs/tasks/task-10-1-product-options-and-variant-combinations.md
* docs/tasks/task-10-2-variant-images.md
* docs/tasks/task-11-product-image-upload.md
* docs/tasks/task-12-inventory-management.md
* docs/tasks/task-13-public-product-catalog.md
* docs/tasks/task-14-product-detail-page.md
* docs/tasks/task-15-cart.md

Sau đó implement Task 15: Cart theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 15.
* Implement cart cho guest user bằng session.
* Implement cart cho logged-in customer bằng user_id.
* Implement merge guest cart vào customer cart sau login.
* Tạo carts và cart_items nếu chưa có.
* Add to cart phải hỗ trợ product không variant và product có variant.
* Product có variants thì bắt buộc chọn variant.
* Không cho add inactive product hoặc inactive variant.
* Không cho add quá available stock.
* Cart không reserve stock.
* Cart không deduct stock.
* Add to cart bằng JavaScript, không reload page.
* Update quantity bằng JavaScript, không reload page.
* Remove item bằng JavaScript, không reload page.
* Clear cart bằng JavaScript, không reload page.
* Header cart badge phải cập nhật sau các cart actions.
* Cart page phải hiển thị image, product name, variant values, unit price, quantity, item subtotal và subtotal.
* Cart item image phải ưu tiên variant image rồi fallback product image.
* Có empty cart state.
* Có loading state và toast feedback.
* Không implement Coupon.
* Không implement Checkout.
* Không implement Order.
* Không implement Payment.
* Không dùng Vue.js.
* Dùng Blade + Tailwind CSS + Alpine.js hoặc Fetch API.
* Sau khi làm xong, báo cáo file đã tạo/sửa và lệnh cần chạy để test.
