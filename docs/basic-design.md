# Basic Design - E-commerce System

## 1. Overview

Tài liệu này mô tả thiết kế tổng quan cho hệ thống e-commerce.

Hệ thống cho phép khách hàng xem sản phẩm, tìm kiếm sản phẩm, thêm vào giỏ hàng, đặt hàng, thanh toán và theo dõi đơn hàng.

Admin có thể quản lý sản phẩm, danh mục, tồn kho, đơn hàng, khách hàng, mã giảm giá, banner, báo cáo doanh thu và các thiết lập hệ thống như ngôn ngữ, tiền tệ, thuế.

Hệ thống được thiết kế có hỗ trợ:

* Đa ngôn ngữ
* Đa tiền tệ
* Thiết lập thuế
* Quản lý sản phẩm
* Quản lý tồn kho
* Quản lý đơn hàng
* Thanh toán COD và thanh toán online
* Admin dashboard
* Báo cáo doanh thu

---

## 2. Project Goals

Mục tiêu chính của hệ thống:

* Xây dựng website bán hàng trực tuyến chuyên nghiệp.
* Cho phép khách hàng mua hàng dễ dàng.
* Cho phép admin quản lý toàn bộ hoạt động bán hàng.
* Hỗ trợ nhiều ngôn ngữ như Vietnamese, English, Japanese.
* Hỗ trợ nhiều loại tiền tệ như VND, USD, JPY.
* Cho phép cấu hình thuế theo hệ thống.
* Thiết kế database rõ ràng, dễ mở rộng.
* Có thể phát triển thêm các tính năng nâng cao trong tương lai.

---

## 3. Target Users

## 3.1. Customer

Customer là người dùng mua hàng trên website.

Customer có thể:

* Xem danh sách sản phẩm.
* Xem chi tiết sản phẩm.
* Tìm kiếm sản phẩm.
* Lọc sản phẩm theo danh mục, giá, trạng thái.
* Thêm sản phẩm vào giỏ hàng.
* Cập nhật số lượng trong giỏ hàng.
* Đặt hàng.
* Thanh toán.
* Xem lịch sử đơn hàng.
* Đánh giá sản phẩm sau khi mua hàng.

---

## 3.2. Admin

Admin là người quản trị hệ thống.

Admin có thể:

* Quản lý dashboard.
* Quản lý danh mục sản phẩm.
* Quản lý sản phẩm.
* Quản lý hình ảnh sản phẩm.
* Quản lý tồn kho.
* Quản lý đơn hàng.
* Quản lý khách hàng.
* Quản lý mã giảm giá.
* Quản lý banner.
* Quản lý ngôn ngữ.
* Quản lý tiền tệ.
* Quản lý thuế.
* Quản lý thiết lập hệ thống.
* Xem báo cáo doanh thu.

---

## 3.3. Staff

Staff là nhân viên quản trị có quyền hạn giới hạn.

Staff có thể được phân quyền để:

* Xem đơn hàng.
* Cập nhật trạng thái đơn hàng.
* Quản lý sản phẩm.
* Quản lý tồn kho.
* Không được thay đổi các thiết lập quan trọng của hệ thống nếu không có quyền.

---

## 4. System Scope

## 4.1. In Scope

Các chức năng nằm trong phạm vi phát triển:

* Authentication
* Admin layout
* System settings
* Language management
* Currency management
* Tax management
* Category management with translation
* Product management with translation
* Product image upload
* Inventory management
* Public product catalog
* Product detail page
* Cart
* Coupon
* Checkout
* Payment COD
* Order creation
* Admin order management
* Admin dashboard
* Banner management
* Report
* Online payment
* Product review

---

## 4.2. Out of Scope

Các chức năng chưa làm trong giai đoạn đầu:

* Multi-vendor marketplace
* Affiliate system
* Loyalty point
* Flash sale nâng cao
* Live chat
* AI recommendation
* Mobile app
* Warehouse multi-branch nâng cao
* ERP integration
* Accounting integration
* Shipping provider integration nâng cao

---

## 5. Technology Stack

## 5.1. Backend

Đề xuất sử dụng:

* PHP 8.2+
* Laravel 11 hoặc Laravel 12
* MySQL 8
* Redis
* Laravel Queue
* Laravel Scheduler
* Laravel Validation
* Laravel Policy / Gate
* Laravel Storage

---

## 5.2. Frontend

Giai đoạn đầu có thể sử dụng:

* Blade Template
* Tailwind CSS
* Alpine.js

Nếu muốn làm frontend hiện đại hơn trong tương lai có thể nâng cấp sang:

* Vue.js
* React
* Inertia.js

---

## 5.3. Infrastructure

Môi trường deploy đề xuất:

* Ubuntu Server
* Nginx
* PHP-FPM
* MySQL
* Redis
* Docker
* SSL certificate
* GitHub hoặc GitLab

---

## 6. Main Modules

## 6.1. Authentication Module

Chức năng:

* Đăng ký tài khoản.
* Đăng nhập.
* Đăng xuất.
* Quên mật khẩu.
* Cập nhật thông tin cá nhân.
* Phân quyền admin, staff, customer.

Đối tượng sử dụng:

* Customer
* Admin
* Staff

---

## 6.2. Admin Layout Module

Chức năng:

* Xây dựng giao diện quản trị.
* Sidebar menu.
* Header.
* Breadcrumb.
* Notification.
* Common layout cho toàn bộ admin.
* Responsive admin interface.

Các menu chính:

* Dashboard
* Products
* Categories
* Inventory
* Orders
* Customers
* Coupons
* Banners
* Reports
* Settings

---

## 6.3. System Settings Module

Chức năng:

* Thiết lập tên website.
* Thiết lập logo.
* Thiết lập email hệ thống.
* Thiết lập số điện thoại.
* Thiết lập địa chỉ công ty.
* Thiết lập ngôn ngữ mặc định.
* Thiết lập tiền tệ mặc định.
* Thiết lập bật/tắt thuế.
* Thiết lập giá đã bao gồm thuế hay chưa.
* Thiết lập bật/tắt đa ngôn ngữ.
* Thiết lập bật/tắt đa tiền tệ.

Ví dụ setting:

```txt
site_name = My E-commerce
default_language = vi
default_currency = VND
tax_enabled = true
price_include_tax = false
multi_language_enabled = true
multi_currency_enabled = true
```

---

## 6.4. Language Management Module

Chức năng:

* Thêm ngôn ngữ.
* Sửa ngôn ngữ.
* Xóa ngôn ngữ.
* Bật/tắt ngôn ngữ.
* Thiết lập ngôn ngữ mặc định.
* Sắp xếp thứ tự hiển thị ngôn ngữ.

Ví dụ ngôn ngữ:

```txt
vi - Tiếng Việt
en - English
ja - 日本語
```

Ngôn ngữ mặc định đề xuất:

```txt
vi
```

---

## 6.5. Currency Management Module

Chức năng:

* Thêm tiền tệ.
* Sửa tiền tệ.
* Xóa tiền tệ.
* Bật/tắt tiền tệ.
* Thiết lập tiền tệ mặc định.
* Thiết lập tỷ giá.
* Thiết lập ký hiệu tiền tệ.
* Thiết lập số chữ số thập phân.

Ví dụ currency:

```txt
VND - Vietnamese Dong - ₫
USD - US Dollar - $
JPY - Japanese Yen - ¥
```

Nguyên tắc thiết kế:

* Giá sản phẩm nên lưu theo tiền tệ mặc định.
* Khi hiển thị cho khách hàng, hệ thống convert sang currency đang chọn.
* Khi tạo đơn hàng, hệ thống phải lưu snapshot currency và exchange rate tại thời điểm đặt hàng.

Ví dụ:

```txt
Base currency: VND
Product price: 500,000 VND

Customer chọn USD
Exchange rate: 25,000
Display price: 20 USD
```

---

## 6.6. Tax Management Module

Chức năng:

* Thiết lập thuế mặc định.
* Quản lý tax class.
* Quản lý tax rate.
* Thiết lập thuế theo quốc gia hoặc khu vực.
* Thiết lập sản phẩm chịu thuế hoặc miễn thuế.
* Thiết lập giá đã bao gồm thuế hoặc chưa bao gồm thuế.

Ví dụ:

```txt
Product price: 1,000,000 VND
Tax rate: 10%
Tax amount: 100,000 VND
Total: 1,100,000 VND
```

Nếu giá đã bao gồm thuế:

```txt
Display price: 1,100,000 VND
Tax rate: 10%
Base price: 1,000,000 VND
Included tax: 100,000 VND
```

---

## 6.7. Category Management Module

Chức năng:

* Thêm danh mục.
* Sửa danh mục.
* Xóa danh mục.
* Bật/tắt danh mục.
* Hỗ trợ danh mục cha/con.
* Hỗ trợ dịch tên danh mục theo nhiều ngôn ngữ.
* Hỗ trợ SEO title, SEO description theo từng ngôn ngữ.

Ví dụ:

```txt
vi: Áo nam
en: Men's Shirts
ja: メンズシャツ
```

---

## 6.8. Product Management Module

Chức năng:

* Thêm sản phẩm.
* Sửa sản phẩm.
* Xóa sản phẩm.
* Bật/tắt sản phẩm.
* Gán sản phẩm vào danh mục.
* Thiết lập SKU.
* Thiết lập giá bán.
* Thiết lập giá khuyến mãi.
* Thiết lập giá vốn.
* Thiết lập tax class.
* Hỗ trợ dịch thông tin sản phẩm theo nhiều ngôn ngữ.
* Hỗ trợ SEO title, SEO description theo từng ngôn ngữ.

Thông tin sản phẩm gồm 2 loại:

Thông tin không dịch:

* SKU
* Price
* Sale price
* Cost price
* Category
* Tax class
* Status
* Featured flag

Thông tin có dịch:

* Product name
* Slug
* Short description
* Description
* Meta title
* Meta description

---

## 6.9. Product Image Upload Module

Chức năng:

* Upload nhiều ảnh cho sản phẩm.
* Chọn ảnh chính.
* Sắp xếp thứ tự ảnh.
* Xóa ảnh.
* Hiển thị gallery ảnh ở trang chi tiết sản phẩm.

---

## 6.10. Inventory Management Module

Chức năng:

* Quản lý tồn kho sản phẩm.
* Quản lý tồn kho theo biến thể sản phẩm nếu có.
* Nhập kho.
* Điều chỉnh tồn kho thủ công.
* Trừ kho khi đơn hàng được tạo.
* Hoàn kho khi đơn hàng bị hủy.
* Cảnh báo sắp hết hàng.
* Lưu lịch sử thay đổi tồn kho.

Loại inventory log:

```txt
import
export
order
cancel_order
manual_adjust
```

---

## 6.11. Public Product Catalog Module

Chức năng:

* Hiển thị danh sách sản phẩm.
* Hiển thị sản phẩm theo danh mục.
* Tìm kiếm sản phẩm.
* Lọc theo giá.
* Lọc theo danh mục.
* Sắp xếp theo mới nhất, giá thấp, giá cao.
* Hiển thị giá theo currency đang chọn.
* Hiển thị nội dung theo language đang chọn.

URL đề xuất:

```txt
/vi/products
/en/products
/ja/products

/vi/category/ao-nam
/en/category/mens-shirts
```

---

## 6.12. Product Detail Page Module

Chức năng:

* Hiển thị thông tin chi tiết sản phẩm.
* Hiển thị hình ảnh sản phẩm.
* Hiển thị giá sản phẩm.
* Hiển thị giá khuyến mãi nếu có.
* Hiển thị tồn kho.
* Hiển thị mô tả sản phẩm.
* Hiển thị sản phẩm liên quan.
* Cho phép thêm sản phẩm vào giỏ hàng.

URL đề xuất:

```txt
/vi/products/ao-thun-nam
/en/products/men-t-shirt
/ja/products/mens-t-shirt
```

---

## 6.13. Cart Module

Chức năng:

* Thêm sản phẩm vào giỏ hàng.
* Xem giỏ hàng.
* Cập nhật số lượng.
* Xóa sản phẩm khỏi giỏ hàng.
* Tính subtotal.
* Validate tồn kho.
* Lưu giỏ hàng theo user hoặc session.

Nguyên tắc:

* User đã đăng nhập: cart gắn với user_id.
* User chưa đăng nhập: cart gắn với session_id.
* Khi user đăng nhập, có thể merge cart từ session vào user cart.

---

## 6.14. Coupon Module

Chức năng:

* Tạo mã giảm giá.
* Giảm theo số tiền cố định.
* Giảm theo phần trăm.
* Thiết lập giá trị đơn hàng tối thiểu.
* Thiết lập số lần sử dụng tối đa.
* Thiết lập thời gian bắt đầu và kết thúc.
* Bật/tắt coupon.

Loại coupon:

```txt
fixed
percent
```

---

## 6.15. Checkout Module

Chức năng:

* Nhập thông tin người nhận.
* Nhập địa chỉ giao hàng.
* Chọn phương thức thanh toán.
* Áp dụng coupon.
* Tính thuế.
* Tính phí giao hàng.
* Tính tổng tiền.
* Validate tồn kho trước khi tạo đơn hàng.

Checkout flow:

```txt
Cart
→ Input shipping information
→ Validate stock
→ Calculate subtotal
→ Apply coupon
→ Calculate tax
→ Calculate shipping fee
→ Calculate total
→ Create order
→ Create order items
→ Create payment
→ Reduce inventory
→ Clear cart
→ Show thank you page
```

---

## 6.16. Payment COD Module

Chức năng:

* Cho phép customer chọn thanh toán khi nhận hàng.
* Tạo payment record với trạng thái pending.
* Order được tạo với payment_status là unpaid hoặc pending.
* Admin xác nhận đơn hàng và xử lý giao hàng.

---

## 6.17. Online Payment Module

Chức năng:

* Tích hợp thanh toán online.
* Redirect customer sang cổng thanh toán.
* Nhận callback từ cổng thanh toán.
* Verify payment signature.
* Cập nhật trạng thái payment.
* Cập nhật trạng thái order.

Cổng thanh toán có thể tích hợp:

* VNPay
* MoMo
* ZaloPay

Giai đoạn đầu chỉ cần làm COD trước, online payment làm sau.

---

## 6.18. Order Management Module

Chức năng customer:

* Xem lịch sử đơn hàng.
* Xem chi tiết đơn hàng.
* Theo dõi trạng thái đơn hàng.
* Hủy đơn hàng nếu đơn chưa xử lý.

Chức năng admin:

* Xem danh sách đơn hàng.
* Xem chi tiết đơn hàng.
* Tìm kiếm đơn hàng.
* Lọc đơn hàng theo trạng thái.
* Cập nhật trạng thái đơn hàng.
* Cập nhật trạng thái thanh toán.
* In thông tin đơn hàng nếu cần.

Trạng thái đơn hàng:

```txt
pending
confirmed
processing
shipping
completed
cancelled
refunded
```

Trạng thái thanh toán:

```txt
pending
paid
failed
refunded
```

---

## 6.19. Admin Dashboard Module

Chức năng:

* Hiển thị tổng doanh thu.
* Hiển thị tổng đơn hàng.
* Hiển thị đơn hàng mới.
* Hiển thị sản phẩm sắp hết hàng.
* Hiển thị top sản phẩm bán chạy.
* Hiển thị doanh thu theo ngày.
* Hiển thị doanh thu theo tháng.
* Hiển thị khách hàng mới.

---

## 6.20. Banner Management Module

Chức năng:

* Thêm banner.
* Sửa banner.
* Xóa banner.
* Bật/tắt banner.
* Thiết lập vị trí hiển thị.
* Thiết lập thời gian bắt đầu/kết thúc.
* Hỗ trợ dịch nội dung banner theo ngôn ngữ.

Vị trí banner:

```txt
home_slider
home_middle
category_top
product_detail
```

---

## 6.21. Report Module

Chức năng:

* Báo cáo doanh thu.
* Báo cáo đơn hàng.
* Báo cáo sản phẩm bán chạy.
* Báo cáo khách hàng.
* Báo cáo tồn kho.
* Export CSV nếu cần.

---

## 6.22. Product Review Module

Chức năng:

* Customer đánh giá sản phẩm.
* Chỉ customer đã mua hàng mới được đánh giá.
* Customer chấm sao từ 1 đến 5.
* Customer viết bình luận.
* Admin duyệt hoặc ẩn review.
* Hiển thị review ở trang chi tiết sản phẩm.

---

## 6.23. Security Hardening Module

Mục tiêu:

* Gia cố hệ thống trước khi triển khai production.
* Không thêm business feature mới.
* Bảo vệ admin routes, dữ liệu order, file upload và payment flow.
* Kiểm soát session, cookie, CSRF, rate limit và security headers.
* Không để lộ secret, lỗi kỹ thuật hoặc private storage.
* Bổ sung automated security tests cho các luồng quan trọng.

Phạm vi chính:

```txt
Production configuration
→ Authentication and authorization
→ Order access control
→ CSRF, session and cookie
→ Rate limiting
→ File upload validation
→ Payment verification and idempotency
→ Security headers
→ Route, log and dependency audit
```

---

## 6.24. Production Deployment Module

Mục tiêu:

* Triển khai source đã kiểm thử lên Laravel production.
* Bắt buộc `APP_ENV=production`, `APP_DEBUG=false` và `APP_URL` dùng HTTPS.
* Web server chỉ public thư mục `public`.
* Cài Composer dependency production và build Vite assets.
* Chạy migration bằng `php artisan migrate --force`.
* Cấu hình storage link, permissions, cache và HTTPS.
* Chỉ bật queue worker/scheduler khi code thực sự sử dụng.
* Có smoke test và rollback plan cơ bản.

Chi tiết vận hành được mô tả trong `docs/production-deployment.md`.

---

## 6.25. Backup, Logs and Monitoring Module

Mục tiêu:

* Backup database, uploaded files và `.env` đã mã hóa.
* Không lưu backup/log trong public web root.
* Áp dụng retention và checksum verification.
* Test restore định kỳ trên môi trường cô lập.
* Theo dõi Laravel/web server/payment logs.
* Theo dõi uptime, backup age và disk usage.
* Chỉ bật queue/scheduler monitoring khi các thành phần đó được sử dụng.
* Alert chỉ chứa trạng thái tổng hợp, không chứa secret hoặc payment payload.

Chi tiết vận hành được mô tả trong `docs/backup-logs-monitoring.md`.

---

## 7. Basic Database Design

## 7.1. Main Tables

Danh sách bảng chính:

```txt
users
roles
permissions
system_settings
languages
currencies
tax_classes
tax_rates
categories
category_translations
products
product_translations
product_images
product_variants
inventory_stocks
inventory_logs
carts
cart_items
coupons
orders
order_items
payments
shipping_addresses
banners
banner_translations
reviews
```

---

## 7.2. Table Overview

## users

Lưu thông tin người dùng.

Các thông tin chính:

* id
* name
* email
* password
* phone
* role
* status

---

## system_settings

Lưu thiết lập hệ thống dạng key-value.

Các thông tin chính:

* id
* key
* value
* type
* group

---

## languages

Lưu danh sách ngôn ngữ được hỗ trợ.

Các thông tin chính:

* id
* code
* name
* native_name
* is_default
* status
* sort_order

---

## currencies

Lưu danh sách tiền tệ được hỗ trợ.

Các thông tin chính:

* id
* code
* name
* symbol
* exchange_rate
* decimal_places
* is_default
* status

---

## tax_classes

Lưu nhóm thuế.

Các thông tin chính:

* id
* name
* description
* status

---

## tax_rates

Lưu mức thuế.

Các thông tin chính:

* id
* tax_class_id
* country_code
* region
* rate
* priority
* status

---

## categories

Lưu thông tin kỹ thuật của danh mục.

Các thông tin chính:

* id
* parent_id
* image
* sort_order
* status

---

## category_translations

Lưu nội dung dịch của danh mục.

Các thông tin chính:

* id
* category_id
* language_code
* name
* slug
* description
* meta_title
* meta_description

---

## products

Lưu thông tin kỹ thuật của sản phẩm.

Các thông tin chính:

* id
* category_id
* tax_class_id
* sku
* price
* sale_price
* cost_price
* status
* is_featured

---

## product_translations

Lưu nội dung dịch của sản phẩm.

Các thông tin chính:

* id
* product_id
* language_code
* name
* slug
* short_description
* description
* meta_title
* meta_description

---

## product_images

Lưu hình ảnh sản phẩm.

Các thông tin chính:

* id
* product_id
* image_path
* sort_order
* is_main

---

## product_variants

Lưu biến thể sản phẩm.

Các thông tin chính:

* id
* product_id
* sku
* name
* price
* stock_quantity
* status

---

## inventory_stocks

Lưu số lượng tồn kho hiện tại.

Các thông tin chính:

* id
* product_id
* product_variant_id
* quantity
* low_stock_threshold

---

## inventory_logs

Lưu lịch sử thay đổi tồn kho.

Các thông tin chính:

* id
* product_id
* product_variant_id
* type
* quantity
* before_quantity
* after_quantity
* note
* created_by

---

## carts

Lưu giỏ hàng.

Các thông tin chính:

* id
* user_id
* session_id

---

## cart_items

Lưu sản phẩm trong giỏ hàng.

Các thông tin chính:

* id
* cart_id
* product_id
* product_variant_id
* quantity
* price

---

## coupons

Lưu mã giảm giá.

Các thông tin chính:

* id
* code
* type
* value
* min_order_amount
* max_discount_amount
* usage_limit
* used_count
* start_date
* end_date
* status

---

## orders

Lưu đơn hàng.

Các thông tin chính:

* id
* user_id
* order_code
* customer_name
* customer_phone
* customer_email
* shipping_address
* currency_code
* exchange_rate
* subtotal
* discount_amount
* tax_amount
* shipping_fee
* total_amount
* payment_method
* payment_status
* order_status
* note

---

## order_items

Lưu sản phẩm trong đơn hàng.

Các thông tin chính:

* id
* order_id
* product_id
* product_variant_id
* product_name
* product_sku
* price
* quantity
* subtotal
* tax_rate
* tax_amount
* total

---

## payments

Lưu thông tin thanh toán.

Các thông tin chính:

* id
* order_id
* payment_method
* transaction_id
* amount
* status
* paid_at
* raw_response

---

## banners

Lưu thông tin kỹ thuật của banner.

Các thông tin chính:

* id
* image
* link_url
* position
* sort_order
* start_at
* end_at
* status

---

## banner_translations

Lưu nội dung dịch của banner.

Các thông tin chính:

* id
* banner_id
* language_code
* title
* subtitle
* button_text

---

## reviews

Lưu đánh giá sản phẩm.

Các thông tin chính:

* id
* user_id
* product_id
* order_id
* rating
* comment
* status

---

## 8. Basic Relationship Design

Quan hệ chính:

```txt
users 1-n orders
users 1-n reviews

categories 1-n categories
categories 1-n category_translations
categories 1-n products

products 1-n product_translations
products 1-n product_images
products 1-n product_variants
products 1-n inventory_stocks
products 1-n inventory_logs
products 1-n reviews

tax_classes 1-n tax_rates
tax_classes 1-n products

carts 1-n cart_items
users 1-n carts

orders 1-n order_items
orders 1-1 payments
orders n-1 users

banners 1-n banner_translations
```

---

## 9. URL Design

## 9.1. Public URLs

Public URL nên có language prefix.

Ví dụ:

```txt
/vi
/en
/ja

/vi/products
/en/products
/ja/products

/vi/products/{slug}
/en/products/{slug}
/ja/products/{slug}

/vi/category/{slug}
/en/category/{slug}
/ja/category/{slug}

/vi/cart
/en/cart
/ja/cart

/vi/checkout
/en/checkout
/ja/checkout

/vi/orders
/en/orders
/ja/orders
```

---

## 9.2. Admin URLs

Admin URL không cần language prefix ở giai đoạn đầu.

Ví dụ:

```txt
/admin/login
/admin/dashboard

/admin/settings
/admin/languages
/admin/currencies
/admin/tax-classes
/admin/tax-rates

/admin/categories
/admin/products
/admin/inventory
/admin/orders
/admin/customers
/admin/coupons
/admin/banners
/admin/reports
```

---

## 10. Role and Permission Design

## 10.1. Roles

Hệ thống có các role chính:

```txt
super_admin
admin
staff
customer
```

---

## 10.2. Permission Overview

## super_admin

Có toàn quyền trên hệ thống.

Có thể:

* Quản lý admin.
* Quản lý settings.
* Quản lý language.
* Quản lý currency.
* Quản lý tax.
* Quản lý toàn bộ dữ liệu.

---

## admin

Có quyền quản lý bán hàng.

Có thể:

* Quản lý product.
* Quản lý category.
* Quản lý inventory.
* Quản lý order.
* Quản lý customer.
* Quản lý coupon.
* Quản lý banner.
* Xem report.

---

## staff

Có quyền thao tác giới hạn.

Có thể:

* Xem đơn hàng.
* Cập nhật trạng thái đơn hàng.
* Xem sản phẩm.
* Cập nhật tồn kho nếu được cấp quyền.

---

## customer

Có quyền mua hàng.

Có thể:

* Xem sản phẩm.
* Thêm vào giỏ hàng.
* Đặt hàng.
* Xem lịch sử đơn hàng.
* Đánh giá sản phẩm đã mua.

---

## 11. Multi-language Design

## 11.1. Language Selection

Customer có thể chọn ngôn ngữ trên website.

Ngôn ngữ được lưu vào:

* Session
* Cookie
* User profile nếu đã đăng nhập

Thứ tự ưu tiên lấy language:

```txt
URL language prefix
→ User selected language
→ Cookie language
→ Browser language
→ Default language
```

---

## 11.2. Translation Data

Các dữ liệu cần dịch:

* Category name
* Category slug
* Category description
* Product name
* Product slug
* Product short description
* Product description
* Product meta title
* Product meta description
* Banner title
* Banner subtitle
* Static page content

Các dữ liệu không cần dịch:

* SKU
* Price
* Stock quantity
* Order code
* Payment status
* Inventory quantity

---

## 11.3. Translation Fallback

Nếu không có bản dịch cho ngôn ngữ hiện tại, hệ thống sẽ fallback về ngôn ngữ mặc định.

Ví dụ:

```txt
Current language: en
Product chưa có bản dịch en
Fallback language: vi
Hiển thị bản dịch vi
```

---

## 12. Currency Design

## 12.1. Base Currency

Hệ thống cần có một currency mặc định.

Đề xuất:

```txt
Base currency: VND
```

Giá sản phẩm trong database sẽ lưu theo base currency.

---

## 12.2. Display Currency

Customer có thể chọn currency muốn hiển thị.

Currency được lưu vào:

* Session
* Cookie
* User profile nếu đã đăng nhập

Thứ tự ưu tiên lấy currency:

```txt
User selected currency
→ Cookie currency
→ Default currency
```

---

## 12.3. Currency Conversion

Công thức chuyển đổi:

```txt
display_price = base_price / exchange_rate
```

Ví dụ:

```txt
Base currency: VND
Product price: 500000
USD exchange_rate: 25000
Display price: 20 USD
```

---

## 12.4. Order Currency Snapshot

Khi tạo order, hệ thống phải lưu lại:

* currency_code
* exchange_rate
* subtotal
* discount_amount
* tax_amount
* shipping_fee
* total_amount

Mục tiêu:

* Đơn hàng cũ không bị thay đổi khi admin cập nhật tỷ giá mới.
* Báo cáo doanh thu có dữ liệu chính xác.
* Có thể kiểm tra lại giá trị đơn hàng tại thời điểm khách mua.

---

## 13. Tax Design

## 13.1. Tax Configuration

Admin có thể cấu hình:

* Bật/tắt thuế.
* Giá đã bao gồm thuế hay chưa.
* Tax class.
* Tax rate.
* Tax theo quốc gia hoặc khu vực nếu cần.

---

## 13.2. Tax Calculation

Nếu giá chưa bao gồm thuế:

```txt
tax_amount = taxable_amount * tax_rate / 100
total = taxable_amount + tax_amount + shipping_fee
```

Nếu giá đã bao gồm thuế:

```txt
base_amount = price_include_tax / (1 + tax_rate / 100)
tax_amount = price_include_tax - base_amount
```

---

## 13.3. Order Tax Snapshot

Khi tạo order item, hệ thống cần lưu:

* tax_rate
* tax_amount
* total

Mục tiêu:

* Đơn hàng cũ vẫn đúng khi admin thay đổi tax rate.
* Có thể xem lại thuế của từng sản phẩm tại thời điểm mua hàng.

---

## 14. Checkout Design

## 14.1. Checkout Steps

Checkout flow:

```txt
1. Customer xem cart
2. Customer nhập thông tin nhận hàng
3. Customer chọn payment method
4. Customer nhập coupon nếu có
5. Hệ thống validate tồn kho
6. Hệ thống tính subtotal
7. Hệ thống tính discount
8. Hệ thống tính tax
9. Hệ thống tính shipping fee
10. Hệ thống tính total
11. Hệ thống tạo order
12. Hệ thống tạo order items
13. Hệ thống tạo payment
14. Hệ thống trừ tồn kho
15. Hệ thống xóa cart
16. Hệ thống hiển thị thank you page
```

---

## 14.2. Checkout Calculation

Công thức:

```txt
subtotal = sum(item_price * quantity)
discount_amount = coupon_discount
taxable_amount = subtotal - discount_amount
tax_amount = taxable_amount * tax_rate
shipping_fee = configured_shipping_fee
total_amount = taxable_amount + tax_amount + shipping_fee
```

---

## 15. Order Design

## 15.1. Order Code

Order code nên được generate tự động.

Ví dụ:

```txt
ORD202606180001
```

Format đề xuất:

```txt
ORD + YYYYMMDD + running_number
```

---

## 15.2. Order Status

Trạng thái đơn hàng:

```txt
pending
confirmed
processing
shipping
completed
cancelled
refunded
```

Ý nghĩa:

* pending: Đơn hàng mới tạo.
* confirmed: Admin đã xác nhận.
* processing: Đơn đang được chuẩn bị.
* shipping: Đơn đang giao.
* completed: Đơn đã hoàn thành.
* cancelled: Đơn đã hủy.
* refunded: Đơn đã hoàn tiền.

---

## 15.3. Payment Status

Trạng thái thanh toán:

```txt
pending
paid
failed
refunded
```

Ý nghĩa:

* pending: Chưa thanh toán hoặc đang chờ thanh toán.
* paid: Đã thanh toán.
* failed: Thanh toán thất bại.
* refunded: Đã hoàn tiền.

---

## 16. Security Design

Thiết kế bảo mật được gia cố trong Task 27, áp dụng theo nguyên tắc defense in depth. Mỗi request nhạy cảm phải được bảo vệ ở nhiều lớp gồm route middleware, authorization, validation và business service.

## 16.1. Production Environment

Production sử dụng cấu hình tối thiểu:

| Key                   | Giá trị yêu cầu          |
| --------------------- | ------------------------ |
| APP_ENV               | production               |
| APP_DEBUG             | false                    |
| APP_URL               | HTTPS domain thật        |
| LOG_LEVEL             | warning hoặc error       |
| SESSION_ENCRYPT       | true                     |
| SESSION_SECURE_COOKIE | true                     |
| SESSION_HTTP_ONLY     | true                     |
| SESSION_SAME_SITE     | lax hoặc strict          |
| FILESYSTEM_LOCAL_SERVE | false                    |

Nguyên tắc:

* `.env`, password database và payment secret không được commit.
* App phải dùng database user riêng, không dùng root.
* `APP_KEY` và super admin password phải được cấp qua environment.
* Chỉ trust reverse proxy xác định trong production.
* Không public log, session, cache hoặc private storage.

File `.env.production.example` được dùng làm mẫu cấu hình, không chứa secret thật.

## 16.2. Authentication and Admin Authorization

* Password được hash bằng Laravel hasher.
* Login giới hạn tối đa 5 lần thử theo email và IP trước khi throttle.
* Session được regenerate sau khi login và invalidate khi logout.
* Toàn bộ `/admin/*` dùng middleware `auth`, `admin` và `admin.locale`.
* Middleware admin chỉ chấp nhận user đang active có role `super_admin`, `admin` hoặc `staff`.
* Guest được redirect tới login; customer hoặc user bị khóa nhận HTTP 403.
* Các action create, update, delete và AJAX trong admin không có public route thay thế.

## 16.3. Order Access Control

Không dùng ID tuần tự làm cơ chế bảo vệ trang kết quả đơn hàng.

* Mỗi order có `success_token` ngẫu nhiên dài 80 ký tự.
* Customer đã đăng nhập chỉ xem được order có `user_id` của chính mình.
* Customer khác dù biết token hợp lệ vẫn nhận HTTP 403.
* Guest chỉ xem được guest order khi cung cấp đúng token ngẫu nhiên của order.
* Token không tồn tại trả HTTP 404.
* Payment retry phải kiểm tra quyền sở hữu order trước khi tạo transaction mới.

## 16.4. CSRF, Session and Cookie

* Route web thay đổi dữ liệu bằng POST, PUT, PATCH hoặc DELETE phải qua CSRF middleware.
* Chỉ payment return và webhook được loại trừ CSRF vì gateway bên ngoài không có session.
* Payment endpoint loại trừ CSRF bắt buộc phải verify gateway, signature và transaction.
* Session cookie dùng `HttpOnly`; bật `Secure` khi chạy HTTPS production.
* Session production được encrypt và dùng `SameSite=lax` hoặc chặt hơn.
* Không lưu password, payment secret hoặc dữ liệu nhạy cảm không cần thiết trong session.

## 16.5. Rate Limiting

Các public action dễ bị spam sử dụng throttle:

| Route action              | Giới hạn |
| ------------------------- | -------- |
| Login                     | 5 lần theo email/IP |
| Apply coupon              | 10 request/phút |
| Place COD order           | 5 request/phút |
| Place and pay online      | 5 request/phút |
| Retry online payment      | 5 request/phút |
| Password reset/verify     | 6 request/phút |

Payment webhook không throttle để tránh gateway retry thất bại; thay vào đó dùng idempotency.

## 16.6. File Upload Security

Áp dụng cho product image, variant image và banner image:

* Chỉ chấp nhận `jpg`, `jpeg`, `png`, `webp`.
* Validate đồng thời extension, MIME type và kích thước file.
* Không cho upload SVG, PHP, JavaScript hoặc HTML.
* Tên file lưu trữ do hệ thống sinh, không dùng trực tiếp tên file từ client.
* File chỉ được ghi và xóa trong disk/path được cấu hình.
* Local private disk không expose route download/upload tự động.

## 16.7. Input, Mass Assignment and XSS

* Request quan trọng dùng Form Request hoặc validate danh sách field cho phép.
* Numeric, status, date, URL, quantity, price và rate phải có min/max hoặc allow-list phù hợp.
* Public request không được cập nhật role, `payment_status`, `order_status`, `paid_at` hoặc field quản trị.
* Blade dùng escaped output `{{ }}` cho dữ liệu admin/customer nhập.
* Không render raw HTML từ product, banner, address, note hoặc payment payload nếu chưa sanitize.
* URL banner phải dùng scheme an toàn; không chấp nhận `javascript:`.

## 16.8. Payment Security

Backend là source of truth cho toàn bộ payment state:

* Amount và currency được lấy từ order/payment transaction, không lấy theo dữ liệu frontend.
* Return và webhook phải verify gateway signature trước khi đổi trạng thái.
* Transaction reference, order, amount, currency và trạng thái gateway phải khớp.
* Chỉ mark paid khi transaction hợp lệ và order chưa paid.
* Webhook dùng cặp `gateway_code` và `event_id` để chống xử lý trùng.
* Duplicate webhook trả lại kết quả đã xử lý, không cập nhật payment/order lần thứ hai.
* Header webhook được lọc trước khi log.
* Không log hoặc trả về payment secret, password, session cookie và CSRF token.
* Customer không có endpoint để tự mark order là paid.

## 16.9. Browser Security Headers

Global middleware thêm các header:

```txt
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=(), microphone=(), geolocation=()
```

`Strict-Transport-Security` chỉ được thêm khi request dùng HTTPS và app chạy trong production. Content Security Policy chưa bật trong Task 27 để tránh phá Vite, Alpine hoặc inline Blade assets; CSP sẽ được triển khai riêng sau khi audit asset.

## 16.10. Error, Logging and Route Security

* Production luôn dùng `APP_DEBUG=false`.
* Public response không hiển thị stack trace, SQL hoặc secret.
* Log kỹ thuật chỉ ghi identifier cần thiết như order code, transaction code và trạng thái.
* Admin route luôn có authentication và role middleware.
* Update/delete không dùng HTTP GET.
* Không giữ debug route, test route hoặc private local storage route trong production.
* CORS không mở wildcard kèm credentials; Blade application không cần public cross-origin admin API.

## 16.11. Security Verification

Trước production cần chạy:

```bash
php artisan route:list
php artisan test
composer audit
npm audit
npm run build
```

Automated tests tối thiểu phải xác nhận:

* Customer không xem được order của customer khác.
* Owner và guest chỉ mở được đúng token-based order page.
* Coupon, place order và payment retry có rate limit.
* Private local storage routes không tồn tại.
* Browser security headers được trả về.
* Payment signature/amount sai không mark paid và duplicate webhook không xử lý trùng.

---

## 17. Performance Design

Các điểm cần chú ý hiệu năng:

* Sử dụng pagination cho danh sách sản phẩm.
* Index các cột thường search như slug, sku, status, category_id.
* Cache system settings.
* Cache language list.
* Cache currency list.
* Cache category menu.
* Tối ưu ảnh sản phẩm.
* Lazy load ảnh sản phẩm.
* Sử dụng queue cho email.
* Sử dụng queue cho xử lý nặng.
* Sử dụng Redis nếu cần cache/session.

---

## 18. SEO Design

Hệ thống cần hỗ trợ SEO cơ bản:

* Product có slug riêng theo từng ngôn ngữ.
* Category có slug riêng theo từng ngôn ngữ.
* Product có meta title.
* Product có meta description.
* Category có meta title.
* Category có meta description.
* URL thân thiện.
* Có canonical URL.
* Có sitemap.
* Có robots.txt.
* Có Open Graph tags.
* Ảnh sản phẩm có alt text nếu cần.

Ví dụ URL:

```txt
/vi/products/ao-thun-nam
/en/products/men-t-shirt
```

---

## 19. Email Notification Design

Task 30 triển khai transactional email cho:

* Email xác nhận đơn hàng.
* Email báo đơn hàng mới cho admin.
* Email cập nhật trạng thái đơn hàng.
* Email thông báo thanh toán thành công.
* Email thông báo thanh toán thất bại/cancelled nếu setting được bật.
* Email thông báo hủy đơn.
* Email test cấu hình từ admin.

Email dùng Laravel Blade và dữ liệu snapshot của order, gồm customer email,
order items và currency formatting tại thời điểm đặt hàng. Locale được snapshot
vào `orders.language_code` và fallback về default/fallback language.

Mỗi lần gửi được reserve trong `email_logs` bằng `idempotency_key` unique trước
khi dispatch queue. SMTP/queue failure chỉ cập nhật log, không rollback order
hoặc payment. Production worker nghe queue `emails`.

---

## 20. Logging Design

Hệ thống nên ghi log cho các hành động quan trọng:

* Admin login.
* Admin tạo/sửa/xóa sản phẩm.
* Admin cập nhật đơn hàng.
* Admin thay đổi setting.
* Admin thay đổi currency.
* Admin thay đổi tax.
* Inventory change.
* Payment callback.
* Order created.
* Order cancelled.

---

## 21. Error Handling Design

Các lỗi thường gặp cần xử lý:

* Sản phẩm không tồn tại.
* Sản phẩm hết hàng.
* Số lượng đặt vượt tồn kho.
* Coupon không hợp lệ.
* Coupon hết hạn.
* Currency không hợp lệ.
* Language không hợp lệ.
* Payment failed.
* Payment callback invalid.
* Order không tồn tại.
* User không có quyền truy cập.

Nguyên tắc xử lý:

* Hiển thị message rõ ràng cho customer.
* Ghi log lỗi kỹ thuật.
* Không hiển thị stack trace ở production.
* Redirect về màn hình phù hợp khi lỗi.

---

## 22. Development Roadmap

Thứ tự phát triển đề xuất:

```txt
Task 01: Setup Laravel 12 Project
Task 02: Database Design
Task 03: Authentication
Task 04: Admin Layout
Task 05: System Settings
Task 06: Language Management
Task 07: Currency Management
Task 08: Tax Management
Task 09: Category Management with Translation
Task 10: Product Management with Translation
Task 11: Product Image Upload
Task 12: Inventory Management
Task 13: Public Product Catalog with Language / Currency
Task 14: Product Detail Page with Language / Currency
Task 15: Cart
Task 16: Coupon
Task 17: Checkout with Tax / Currency Snapshot
Task 18: Payment COD
Task 19: Order Creation
Task 20: Admin Order Management
Task 21: Admin Dashboard
Task 22: Banner Management with Translation
Task 23: Report
Task 24: Online Payment
Task 25: Review Product
Task 26: End-to-End Testing and Bug Fix
Task 27: Security Hardening
Task 28: Production Deployment
Task 29: Backup, Logs and Monitoring
Task 30: Email Notification
Task 31: Customer Account and Order History
```

---

## 23. MVP Scope

Giai đoạn MVP nên làm trước:

```txt
Task 01: Setup Laravel 12 Project
Task 02: Database Design
Task 03: Authentication
Task 04: Admin Layout
Task 05: System Settings
Task 06: Language Management
Task 07: Currency Management
Task 08: Tax Management
Task 09: Category Management with Translation
Task 10: Product Management with Translation
Task 11: Product Image Upload
Task 12: Inventory Management
Task 13: Public Product Catalog with Language / Currency
Task 14: Product Detail Page with Language / Currency
Task 15: Cart
Task 17: Checkout with Tax / Currency Snapshot
Task 18: Payment COD
Task 19: Order Creation
Task 20: Admin Order Management
```

Các tính năng làm sau MVP:

```txt
Coupon
Admin Dashboard nâng cao
Banner Management
Report
Online Payment
Review Product
```

---

## 24. Future Enhancement

Các chức năng có thể phát triển trong tương lai:

* Multi-vendor marketplace
* Loyalty point
* Affiliate program
* Flash sale
* Wishlist
* Product comparison
* Recently viewed products
* Advanced search
* Elasticsearch / Meilisearch
* Shipping provider integration
* Invoice export PDF
* Customer group pricing
* Product bundle
* Gift card
* Abandoned cart email
* Mobile app API
* Admin activity log nâng cao
* Multi-warehouse
* Multi-branch
* API for frontend app

---

## 25. Conclusion

Hệ thống e-commerce này được thiết kế theo hướng dễ mở rộng, phù hợp để phát triển bằng Laravel.

Các điểm quan trọng cần làm ngay từ đầu:

* Tách dữ liệu translation cho product, category, banner.
* Lưu giá sản phẩm theo base currency.
* Convert currency khi hiển thị.
* Lưu snapshot currency và exchange rate khi tạo order.
* Lưu snapshot tax khi tạo order item.
* Thiết kế system settings linh hoạt.
* Thiết kế checkout flow rõ ràng.
* Thiết kế inventory log để kiểm soát tồn kho.

Thiết kế này phù hợp để phát triển từng task riêng biệt và có thể mở rộng lên hệ thống e-commerce lớn hơn trong tương lai.
