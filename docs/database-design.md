# Database Design - E-commerce System

## 1. Overview

Tài liệu này mô tả thiết kế database cho hệ thống e-commerce sử dụng Laravel 12.

Hệ thống có hỗ trợ:

* Đa ngôn ngữ
* Đa tiền tệ
* Cấu hình thuế
* Quản lý sản phẩm
* Quản lý danh mục
* Quản lý tồn kho
* Giỏ hàng
* Checkout
* Đơn hàng
* Thanh toán COD và online payment
* Coupon
* Banner
* Review sản phẩm
* Admin dashboard và report

Database được thiết kế theo hướng dễ mở rộng, phù hợp cho giai đoạn MVP và có thể nâng cấp về sau.

---

## 2. Database Engine

Đề xuất sử dụng:

```txt
Database: MySQL 8
Charset: utf8mb4
Collation: utf8mb4_unicode_ci
Framework: Laravel 12
```

---

## 3. Design Principles

Các nguyên tắc thiết kế chính:

1. Sử dụng `id` dạng `bigint unsigned auto increment` làm primary key.
2. Tất cả bảng nghiệp vụ nên có `created_at` và `updated_at`.
3. Các bảng cần soft delete có thể thêm `deleted_at`.
4. Dữ liệu đa ngôn ngữ được tách ra bảng translation riêng.
5. Giá sản phẩm lưu theo base currency.
6. Order phải lưu snapshot currency, exchange rate, tax, product name và product price tại thời điểm mua.
7. Không phụ thuộc hoàn toàn vào dữ liệu product hiện tại khi xem lại order cũ.
8. Inventory phải có log để truy vết thay đổi tồn kho.
9. Các field thường dùng để search/filter nên có index.
10. Các trạng thái nên dùng enum hoặc string/tinyint rõ ràng.

---

## 4. Main Table List

Danh sách bảng chính:

```txt
users
roles
permissions
role_user
permission_role

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
coupon_usages

orders
order_items
payments
shipping_addresses

banners
banner_translations

reviews
email_logs
customer_addresses
```

---

## 5. Table Groups

## 5.1. User and Permission Tables

```txt
users
roles
permissions
role_user
permission_role
```

Dùng cho authentication và phân quyền.

---

## 5.2. System Configuration Tables

```txt
system_settings
languages
currencies
tax_classes
tax_rates
```

Dùng cho setting hệ thống, đa ngôn ngữ, tiền tệ và thuế.

---

## 5.3. Product Catalog Tables

```txt
categories
category_translations
products
product_translations
product_images
product_variants
```

Dùng cho danh mục, sản phẩm, hình ảnh, biến thể và dữ liệu dịch.

---

## 5.4. Inventory Tables

```txt
inventory_stocks
inventory_logs
```

Dùng cho tồn kho và lịch sử thay đổi tồn kho.

---

## 5.5. Cart and Checkout Tables

```txt
carts
cart_items
coupons
coupon_usages
```

Dùng cho giỏ hàng và coupon.

---

## 5.6. Order and Payment Tables

```txt
orders
order_items
payments
shipping_addresses
email_logs
```

Dùng cho đơn hàng, sản phẩm trong đơn hàng, thanh toán và địa chỉ giao hàng.

---

## 5.7. Content Tables

```txt
banners
banner_translations
reviews
```

Dùng cho banner và đánh giá sản phẩm.

---

# 6. Detailed Table Design

# 6.1. users

Lưu thông tin người dùng.

Laravel mặc định đã có bảng `users`, có thể mở rộng thêm role và status.

| Column            | Type            | Nullable | Default        | Description                         |
| ----------------- | --------------- | -------- | -------------- | ----------------------------------- |
| id                | bigint unsigned | No       | auto increment | Primary key                         |
| name              | varchar(255)    | No       | null           | User name                           |
| email             | varchar(255)    | No       | null           | Email login                         |
| email_verified_at | timestamp       | Yes      | null           | Email verified time                 |
| password          | varchar(255)    | No       | null           | Hashed password                     |
| phone             | varchar(30)     | Yes      | null           | Phone number                        |
| role              | varchar(50)     | No       | customer       | super_admin, admin, staff, customer |
| status            | tinyint         | No       | 1              | 1: active, 0: inactive              |
| remember_token    | varchar(100)    | Yes      | null           | Laravel remember token              |
| created_at        | timestamp       | Yes      | null           | Created time                        |
| updated_at        | timestamp       | Yes      | null           | Updated time                        |
| deleted_at        | timestamp       | Yes      | null           | Soft delete                         |

Indexes:

```txt
unique: email
index: role
index: status
```

Role values:

```txt
super_admin
admin
staff
customer
```

---

# 6.2. roles

Lưu danh sách role nếu sau này muốn phân quyền nâng cao.

| Column      | Type            | Nullable | Default        | Description            |
| ----------- | --------------- | -------- | -------------- | ---------------------- |
| id          | bigint unsigned | No       | auto increment | Primary key            |
| name        | varchar(100)    | No       | null           | Role name              |
| code        | varchar(100)    | No       | null           | Role code              |
| description | text            | Yes      | null           | Description            |
| status      | tinyint         | No       | 1              | 1: active, 0: inactive |
| created_at  | timestamp       | Yes      | null           | Created time           |
| updated_at  | timestamp       | Yes      | null           | Updated time           |

Indexes:

```txt
unique: code
```

---

# 6.3. permissions

Lưu danh sách permission.

| Column      | Type            | Nullable | Default        | Description      |
| ----------- | --------------- | -------- | -------------- | ---------------- |
| id          | bigint unsigned | No       | auto increment | Primary key      |
| name        | varchar(100)    | No       | null           | Permission name  |
| code        | varchar(100)    | No       | null           | Permission code  |
| group       | varchar(100)    | Yes      | null           | Permission group |
| description | text            | Yes      | null           | Description      |
| created_at  | timestamp       | Yes      | null           | Created time     |
| updated_at  | timestamp       | Yes      | null           | Updated time     |

Indexes:

```txt
unique: code
index: group
```

---

# 6.4. role_user

Pivot table giữa users và roles.

| Column     | Type            | Nullable | Default        | Description    |
| ---------- | --------------- | -------- | -------------- | -------------- |
| id         | bigint unsigned | No       | auto increment | Primary key    |
| user_id    | bigint unsigned | No       | null           | FK to users.id |
| role_id    | bigint unsigned | No       | null           | FK to roles.id |
| created_at | timestamp       | Yes      | null           | Created time   |
| updated_at | timestamp       | Yes      | null           | Updated time   |

Indexes:

```txt
index: user_id
index: role_id
unique: user_id, role_id
```

---

# 6.5. permission_role

Pivot table giữa roles và permissions.

| Column        | Type            | Nullable | Default        | Description          |
| ------------- | --------------- | -------- | -------------- | -------------------- |
| id            | bigint unsigned | No       | auto increment | Primary key          |
| role_id       | bigint unsigned | No       | null           | FK to roles.id       |
| permission_id | bigint unsigned | No       | null           | FK to permissions.id |
| created_at    | timestamp       | Yes      | null           | Created time         |
| updated_at    | timestamp       | Yes      | null           | Updated time         |

Indexes:

```txt
index: role_id
index: permission_id
unique: role_id, permission_id
```

---

# 6.6. system_settings

Lưu cấu hình hệ thống dạng key-value.

| Column     | Type            | Nullable | Default        | Description                   |
| ---------- | --------------- | -------- | -------------- | ----------------------------- |
| id         | bigint unsigned | No       | auto increment | Primary key                   |
| key        | varchar(255)    | No       | null           | Setting key                   |
| value      | text            | Yes      | null           | Setting value                 |
| type       | varchar(50)     | No       | string         | string, number, boolean, json |
| group      | varchar(100)    | Yes      | null           | Setting group                 |
| is_public  | tinyint         | No       | 0              | 1: public, 0: private         |
| created_at | timestamp       | Yes      | null           | Created time                  |
| updated_at | timestamp       | Yes      | null           | Updated time                  |

Indexes:

```txt
unique: key
index: group
index: is_public
```

Example data:

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

# 6.7. languages

Lưu danh sách ngôn ngữ.

| Column      | Type            | Nullable | Default        | Description                   |
| ----------- | --------------- | -------- | -------------- | ----------------------------- |
| id          | bigint unsigned | No       | auto increment | Primary key                   |
| code        | varchar(10)     | No       | null           | vi, en, ja                    |
| name        | varchar(100)    | No       | null           | Vietnamese, English, Japanese |
| native_name | varchar(100)    | Yes      | null           | Tiếng Việt, English, 日本語      |
| is_default  | tinyint         | No       | 0              | 1: default language           |
| status      | tinyint         | No       | 1              | 1: active, 0: inactive        |
| sort_order  | int             | No       | 0              | Display order                 |
| created_at  | timestamp       | Yes      | null           | Created time                  |
| updated_at  | timestamp       | Yes      | null           | Updated time                  |

Indexes:

```txt
unique: code
index: status
index: is_default
```

Default data:

```txt
vi - Vietnamese - Tiếng Việt - default
en - English - English
ja - Japanese - 日本語
```

Business rules:

```txt
Chỉ có một language được set default.
Không được xóa default language.
Không được disable default language.
```

---

# 6.8. currencies

Lưu danh sách tiền tệ.

| Column             | Type            | Nullable | Default        | Description                      |
| ------------------ | --------------- | -------- | -------------- | -------------------------------- |
| id                 | bigint unsigned | No       | auto increment | Primary key                      |
| code               | varchar(10)     | No       | null           | VND, USD, JPY                    |
| name               | varchar(100)    | No       | null           | Currency name                    |
| symbol             | varchar(10)     | No       | null           | ₫, $, ¥                          |
| exchange_rate      | decimal(15,6)   | No       | 1.000000       | Rate compared with base currency |
| decimal_places     | tinyint         | No       | 0              | Number of decimal places         |
| symbol_position    | varchar(20)     | No       | after          | before, after                    |
| thousand_separator | varchar(5)      | Yes      | ,              | Thousand separator               |
| decimal_separator  | varchar(5)      | Yes      | .              | Decimal separator                |
| is_default         | tinyint         | No       | 0              | 1: default currency              |
| status             | tinyint         | No       | 1              | 1: active, 0: inactive           |
| created_at         | timestamp       | Yes      | null           | Created time                     |
| updated_at         | timestamp       | Yes      | null           | Updated time                     |

Indexes:

```txt
unique: code
index: status
index: is_default
```

Default data:

```txt
VND - Vietnamese Dong - ₫ - exchange_rate: 1
USD - US Dollar - $ - exchange_rate: 25000
JPY - Japanese Yen - ¥ - exchange_rate: 170
```

Business rules:

```txt
Base currency mặc định là VND.
Giá sản phẩm lưu theo base currency.
Khi hiển thị sẽ convert sang currency customer chọn.
Khi tạo order phải lưu snapshot currency_code và exchange_rate.
```

---

# 6.9. tax_classes

Lưu nhóm thuế.

| Column      | Type            | Nullable | Default        | Description            |
| ----------- | --------------- | -------- | -------------- | ---------------------- |
| id          | bigint unsigned | No       | auto increment | Primary key            |
| name        | varchar(255)    | No       | null           | Tax class name         |
| code        | varchar(100)    | No       | null           | Tax class code         |
| description | text            | Yes      | null           | Description            |
| status      | tinyint         | No       | 1              | 1: active, 0: inactive |
| created_at  | timestamp       | Yes      | null           | Created time           |
| updated_at  | timestamp       | Yes      | null           | Updated time           |

Indexes:

```txt
unique: code
index: status
```

Example data:

```txt
standard_tax
reduced_tax
tax_free
```

---

# 6.10. tax_rates

Lưu mức thuế theo tax class, quốc gia hoặc khu vực.

| Column       | Type            | Nullable | Default        | Description                        |
| ------------ | --------------- | -------- | -------------- | ---------------------------------- |
| id           | bigint unsigned | No       | auto increment | Primary key                        |
| tax_class_id | bigint unsigned | No       | null           | FK to tax_classes.id               |
| country_code | varchar(10)     | Yes      | null           | VN, US, JP                         |
| region       | varchar(100)    | Yes      | null           | Region/State/Province              |
| rate         | decimal(8,4)    | No       | 0.0000         | Tax rate percent                   |
| priority     | int             | No       | 0              | Priority when multiple rates exist |
| status       | tinyint         | No       | 1              | 1: active, 0: inactive             |
| created_at   | timestamp       | Yes      | null           | Created time                       |
| updated_at   | timestamp       | Yes      | null           | Updated time                       |

Indexes:

```txt
index: tax_class_id
index: country_code
index: status
```

Example:

```txt
tax_class_id: standard_tax
country_code: VN
rate: 10.0000
```

---

# 6.11. categories

Lưu thông tin kỹ thuật của danh mục.

Tên, slug, mô tả sẽ lưu ở `category_translations`.

| Column     | Type            | Nullable | Default        | Description            |
| ---------- | --------------- | -------- | -------------- | ---------------------- |
| id         | bigint unsigned | No       | auto increment | Primary key            |
| parent_id  | bigint unsigned | Yes      | null           | Parent category id     |
| image      | varchar(500)    | Yes      | null           | Category image         |
| sort_order | int             | No       | 0              | Display order          |
| status     | tinyint         | No       | 1              | 1: active, 0: inactive |
| created_at | timestamp       | Yes      | null           | Created time           |
| updated_at | timestamp       | Yes      | null           | Updated time           |
| deleted_at | timestamp       | Yes      | null           | Soft delete            |

Indexes:

```txt
index: parent_id
index: status
index: sort_order
```

Relationships:

```txt
categories.id 1-n categories.parent_id
categories.id 1-n category_translations.category_id
categories.id 1-n products.category_id
```

---

# 6.12. category_translations

Lưu nội dung dịch của danh mục.

| Column           | Type            | Nullable | Default        | Description         |
| ---------------- | --------------- | -------- | -------------- | ------------------- |
| id               | bigint unsigned | No       | auto increment | Primary key         |
| category_id      | bigint unsigned | No       | null           | FK to categories.id |
| language_code    | varchar(10)     | No       | null           | vi, en, ja          |
| name             | varchar(255)    | No       | null           | Category name       |
| slug             | varchar(255)    | No       | null           | Category slug       |
| description      | text            | Yes      | null           | Description         |
| meta_title       | varchar(255)    | Yes      | null           | SEO title           |
| meta_description | text            | Yes      | null           | SEO description     |
| created_at       | timestamp       | Yes      | null           | Created time        |
| updated_at       | timestamp       | Yes      | null           | Updated time        |

Indexes:

```txt
index: category_id
index: language_code
unique: language_code, slug
unique: category_id, language_code
```

Example:

```txt
category_id: 1
vi: Áo nam
en: Men's Shirts
ja: メンズシャツ
```

---

# 6.13. products

Lưu thông tin kỹ thuật của sản phẩm.

Tên, slug, mô tả sẽ lưu ở `product_translations`.

| Column       | Type            | Nullable | Default        | Description            |
| ------------ | --------------- | -------- | -------------- | ---------------------- |
| id           | bigint unsigned | No       | auto increment | Primary key            |
| category_id  | bigint unsigned | No       | null           | FK to categories.id    |
| tax_class_id | bigint unsigned | Yes      | null           | FK to tax_classes.id   |
| sku          | varchar(100)    | No       | null           | Product SKU            |
| price        | decimal(15,2)   | No       | 0.00           | Base price             |
| sale_price   | decimal(15,2)   | Yes      | null           | Sale price             |
| cost_price   | decimal(15,2)   | Yes      | null           | Cost price             |
| status       | tinyint         | No       | 1              | 1: active, 0: inactive |
| is_featured  | tinyint         | No       | 0              | 1: featured            |
| created_at   | timestamp       | Yes      | null           | Created time           |
| updated_at   | timestamp       | Yes      | null           | Updated time           |
| deleted_at   | timestamp       | Yes      | null           | Soft delete            |

Indexes:

```txt
unique: sku
index: category_id
index: tax_class_id
index: status
index: is_featured
index: price
```

Business rules:

```txt
price lưu theo base currency.
sale_price nếu có thì dùng làm giá bán hiện tại.
cost_price chỉ dùng cho admin/report, không hiển thị public.
Không xóa cứng product nếu đã phát sinh order.
```

---

# 6.14. product_translations

Lưu nội dung dịch của sản phẩm.

| Column            | Type            | Nullable | Default        | Description       |
| ----------------- | --------------- | -------- | -------------- | ----------------- |
| id                | bigint unsigned | No       | auto increment | Primary key       |
| product_id        | bigint unsigned | No       | null           | FK to products.id |
| language_code     | varchar(10)     | No       | null           | vi, en, ja        |
| name              | varchar(255)    | No       | null           | Product name      |
| slug              | varchar(255)    | No       | null           | Product slug      |
| short_description | text            | Yes      | null           | Short description |
| description       | longtext        | Yes      | null           | Full description  |
| meta_title        | varchar(255)    | Yes      | null           | SEO title         |
| meta_description  | text            | Yes      | null           | SEO description   |
| created_at        | timestamp       | Yes      | null           | Created time      |
| updated_at        | timestamp       | Yes      | null           | Updated time      |

Indexes:

```txt
index: product_id
index: language_code
unique: language_code, slug
unique: product_id, language_code
```

Example:

```txt
product_id: 10

vi:
name = Áo thun nam màu đen

en:
name = Black Men T-Shirt

ja:
name = 黒いメンズTシャツ
```

---

# 6.15. product_images

Lưu hình ảnh sản phẩm.

| Column     | Type            | Nullable | Default        | Description       |
| ---------- | --------------- | -------- | -------------- | ----------------- |
| id         | bigint unsigned | No       | auto increment | Primary key       |
| product_id | bigint unsigned | No       | null           | FK to products.id |
| image_path | varchar(500)    | No       | null           | Image path        |
| alt_text   | varchar(255)    | Yes      | null           | Image alt text    |
| sort_order | int             | No       | 0              | Display order     |
| is_main    | tinyint         | No       | 0              | 1: main image     |
| created_at | timestamp       | Yes      | null           | Created time      |
| updated_at | timestamp       | Yes      | null           | Updated time      |

Indexes:

```txt
index: product_id
index: is_main
index: sort_order
```

Business rules:

```txt
Mỗi product nên có tối đa một ảnh chính.
Ảnh chính dùng ở listing page.
```

---

# 6.16. product_variants

Lưu biến thể sản phẩm.

Ví dụ:

```txt
Áo thun đen - Size M
Áo thun đen - Size L
iPhone 15 - 128GB - Black
```

| Column     | Type            | Nullable | Default        | Description            |
| ---------- | --------------- | -------- | -------------- | ---------------------- |
| id         | bigint unsigned | No       | auto increment | Primary key            |
| product_id | bigint unsigned | No       | null           | FK to products.id      |
| sku        | varchar(100)    | No       | null           | Variant SKU            |
| name       | varchar(255)    | No       | null           | Variant name           |
| price      | decimal(15,2)   | Yes      | null           | Override price         |
| sale_price | decimal(15,2)   | Yes      | null           | Override sale price    |
| status     | tinyint         | No       | 1              | 1: active, 0: inactive |
| created_at | timestamp       | Yes      | null           | Created time           |
| updated_at | timestamp       | Yes      | null           | Updated time           |
| deleted_at | timestamp       | Yes      | null           | Soft delete            |

Indexes:

```txt
unique: sku
index: product_id
index: status
```

Business rules:

```txt
Nếu variant.price có giá trị thì dùng giá variant.
Nếu variant.price null thì dùng product.price.
Inventory nên quản lý theo variant nếu sản phẩm có variant.
```

---

# 6.17. inventory_stocks

Lưu tồn kho hiện tại.

| Column              | Type            | Nullable | Default        | Description               |
| ------------------- | --------------- | -------- | -------------- | ------------------------- |
| id                  | bigint unsigned | No       | auto increment | Primary key               |
| product_id          | bigint unsigned | No       | null           | FK to products.id         |
| product_variant_id  | bigint unsigned | Yes      | null           | FK to product_variants.id |
| quantity            | int             | No       | 0              | Current stock quantity    |
| low_stock_threshold | int             | No       | 5              | Low stock alert threshold |
| created_at          | timestamp       | Yes      | null           | Created time              |
| updated_at          | timestamp       | Yes      | null           | Updated time              |

Indexes:

```txt
index: product_id
index: product_variant_id
unique: product_id, product_variant_id
```

Business rules:

```txt
quantity không được nhỏ hơn 0.
Khi tạo order thành công thì trừ kho.
Khi hủy order thì hoàn kho.
```

---

# 6.18. inventory_logs

Lưu lịch sử thay đổi tồn kho.

| Column             | Type            | Nullable | Default        | Description               |
| ------------------ | --------------- | -------- | -------------- | ------------------------- |
| id                 | bigint unsigned | No       | auto increment | Primary key               |
| product_id         | bigint unsigned | No       | null           | FK to products.id         |
| product_variant_id | bigint unsigned | Yes      | null           | FK to product_variants.id |
| type               | varchar(50)     | No       | null           | Log type                  |
| quantity           | int             | No       | 0              | Changed quantity          |
| before_quantity    | int             | No       | 0              | Before quantity           |
| after_quantity     | int             | No       | 0              | After quantity            |
| note               | text            | Yes      | null           | Note                      |
| created_by         | bigint unsigned | Yes      | null           | FK to users.id            |
| created_at         | timestamp       | Yes      | null           | Created time              |

Indexes:

```txt
index: product_id
index: product_variant_id
index: type
index: created_by
index: created_at
```

Type values:

```txt
import
export
order
cancel_order
manual_adjust
```

---

# 6.19. carts

Lưu giỏ hàng.

| Column        | Type            | Nullable | Default        | Description          |
| ------------- | --------------- | -------- | -------------- | -------------------- |
| id            | bigint unsigned | No       | auto increment | Primary key          |
| user_id       | bigint unsigned | Yes      | null           | FK to users.id       |
| session_id    | varchar(255)    | Yes      | null           | Session id for guest |
| currency_code | varchar(10)     | Yes      | null           | Selected currency    |
| created_at    | timestamp       | Yes      | null           | Created time         |
| updated_at    | timestamp       | Yes      | null           | Updated time         |

Indexes:

```txt
index: user_id
index: session_id
index: currency_code
```

Business rules:

```txt
User login: dùng user_id.
Guest user: dùng session_id.
Khi guest login, có thể merge cart vào user cart.
```

---

# 6.20. cart_items

Lưu sản phẩm trong giỏ hàng.

| Column             | Type            | Nullable | Default        | Description               |
| ------------------ | --------------- | -------- | -------------- | ------------------------- |
| id                 | bigint unsigned | No       | auto increment | Primary key               |
| cart_id            | bigint unsigned | No       | null           | FK to carts.id            |
| product_id         | bigint unsigned | No       | null           | FK to products.id         |
| product_variant_id | bigint unsigned | Yes      | null           | FK to product_variants.id |
| quantity           | int             | No       | 1              | Quantity                  |
| price              | decimal(15,2)   | No       | 0.00           | Price snapshot when added |
| created_at         | timestamp       | Yes      | null           | Created time              |
| updated_at         | timestamp       | Yes      | null           | Updated time              |

Indexes:

```txt
index: cart_id
index: product_id
index: product_variant_id
unique: cart_id, product_id, product_variant_id
```

Business rules:

```txt
quantity phải lớn hơn 0.
Nếu add cùng product/variant vào cart thì tăng quantity.
Trước checkout phải validate lại giá và tồn kho.
```

---

# 6.21. coupons

Lưu mã giảm giá.

| Column              | Type            | Nullable | Default        | Description                   |
| ------------------- | --------------- | -------- | -------------- | ----------------------------- |
| id                  | bigint unsigned | No       | auto increment | Primary key                   |
| code                | varchar(100)    | No       | null           | Coupon code                   |
| type                | varchar(50)     | No       | null           | fixed, percent                |
| value               | decimal(15,2)   | No       | 0.00           | Discount value                |
| min_order_amount    | decimal(15,2)   | Yes      | null           | Minimum order amount          |
| max_discount_amount | decimal(15,2)   | Yes      | null           | Max discount for percent type |
| usage_limit         | int             | Yes      | null           | Total usage limit             |
| used_count          | int             | No       | 0              | Used count                    |
| start_date          | datetime        | Yes      | null           | Start date                    |
| end_date            | datetime        | Yes      | null           | End date                      |
| status              | tinyint         | No       | 1              | 1: active, 0: inactive        |
| created_at          | timestamp       | Yes      | null           | Created time                  |
| updated_at          | timestamp       | Yes      | null           | Updated time                  |
| deleted_at          | timestamp       | Yes      | null           | Soft delete                   |

Indexes:

```txt
unique: code
index: type
index: status
index: start_date
index: end_date
```

Type values:

```txt
fixed
percent
```

Business rules:

```txt
fixed: giảm số tiền cố định.
percent: giảm theo phần trăm.
Nếu percent có max_discount_amount thì không được giảm vượt quá max.
Coupon hết hạn thì không được áp dụng.
```

---

# 6.22. coupon_usages

Lưu lịch sử sử dụng coupon.

| Column          | Type            | Nullable | Default        | Description          |
| --------------- | --------------- | -------- | -------------- | -------------------- |
| id              | bigint unsigned | No       | auto increment | Primary key          |
| coupon_id       | bigint unsigned | No       | null           | FK to coupons.id     |
| user_id         | bigint unsigned | Yes      | null           | FK to users.id       |
| order_id        | bigint unsigned | Yes      | null           | FK to orders.id      |
| coupon_code     | varchar(100)    | No       | null           | Coupon code snapshot |
| discount_amount | decimal(15,2)   | No       | 0.00           | Discount amount      |
| created_at      | timestamp       | Yes      | null           | Created time         |

Indexes:

```txt
index: coupon_id
index: user_id
index: order_id
index: coupon_code
```

---

# 6.23. orders

Lưu đơn hàng.

Order cần lưu snapshot currency, tax, customer information và total amount tại thời điểm đặt hàng.

| Column           | Type            | Nullable | Default        | Description                                                              |
| ---------------- | --------------- | -------- | -------------- | ------------------------------------------------------------------------ |
| id               | bigint unsigned | No       | auto increment | Primary key                                                              |
| user_id          | bigint unsigned | Yes      | null           | FK to users.id                                                           |
| order_code       | varchar(100)    | No       | null           | Unique order code                                                        |
| customer_name    | varchar(255)    | No       | null           | Customer name snapshot                                                   |
| customer_phone   | varchar(30)     | No       | null           | Customer phone snapshot                                                  |
| customer_email   | varchar(255)    | Yes      | null           | Customer email snapshot                                                  |
| language_code    | varchar(10)     | Yes      | null           | Customer language snapshot used for transactional email                  |
| shipping_address | text            | No       | null           | Full shipping address snapshot                                           |
| currency_code    | varchar(10)     | No       | null           | Currency snapshot                                                        |
| exchange_rate    | decimal(15,6)   | No       | 1.000000       | Exchange rate snapshot                                                   |
| subtotal         | decimal(15,2)   | No       | 0.00           | Order subtotal                                                           |
| discount_amount  | decimal(15,2)   | No       | 0.00           | Discount amount                                                          |
| tax_amount       | decimal(15,2)   | No       | 0.00           | Tax amount                                                               |
| shipping_fee     | decimal(15,2)   | No       | 0.00           | Shipping fee                                                             |
| total_amount     | decimal(15,2)   | No       | 0.00           | Final total                                                              |
| payment_method   | varchar(50)     | No       | cod            | cod, vnpay, momo, zalopay                                                |
| payment_status   | varchar(50)     | No       | pending        | pending, paid, failed, refunded                                          |
| order_status     | varchar(50)     | No       | pending        | pending, confirmed, processing, shipping, completed, cancelled, refunded |
| note             | text            | Yes      | null           | Customer note                                                            |
| admin_note       | text            | Yes      | null           | Admin note                                                               |
| confirmed_at     | datetime        | Yes      | null           | Confirmed time                                                           |
| completed_at     | datetime        | Yes      | null           | Completed time                                                           |
| cancelled_at     | datetime        | Yes      | null           | Cancelled time                                                           |
| created_at       | timestamp       | Yes      | null           | Created time                                                             |
| updated_at       | timestamp       | Yes      | null           | Updated time                                                             |

Indexes:

```txt
unique: order_code
index: user_id
index: currency_code
index: payment_method
index: payment_status
index: order_status
index: language_code
index: created_at
```

Order status values:

```txt
pending
confirmed
processing
shipping
completed
cancelled
refunded
```

Payment status values:

```txt
pending
paid
failed
refunded
```

Payment method values:

```txt
cod
bank_transfer
vnpay
momo
zalopay
```

Business rules:

```txt
Order code phải unique.
Order đã completed không được xóa.
Order cancelled cần hoàn kho nếu đã trừ kho.
Order phải lưu snapshot currency và exchange_rate.
Transactional email phải dùng customer, item, currency và language snapshot của order.
```

---

# 6.23.1. email_logs

Theo dõi transactional email và chống gửi trùng.

| Column                 | Type              | Nullable | Default        | Description                                      |
| ---------------------- | ----------------- | -------- | -------------- | ------------------------------------------------ |
| id                     | bigint unsigned   | No       | auto increment | Primary key                                      |
| event                  | varchar(60)       | No       | null           | Email event                                      |
| idempotency_key        | varchar(64)       | No       | null           | Unique event/recipient/context hash              |
| order_id               | bigint unsigned   | Yes      | null           | FK to orders.id                                  |
| payment_transaction_id | bigint unsigned   | Yes      | null           | FK to payment_transactions.id                    |
| recipient_email        | varchar(320)      | No       | null           | Snapshot recipient                               |
| subject                | varchar(255)      | No       | null           | Translated subject snapshot                      |
| locale                 | varchar(10)       | No       | en             | Language used to render                          |
| status                 | varchar(20)       | No       | pending        | pending, sent, failed, skipped                   |
| attempts               | smallint unsigned | No       | 0              | Delivery attempts                                |
| payload                | json              | Yes      | null           | Non-secret event context                         |
| error_message          | varchar(255)      | Yes      | null           | Safe exception category only                     |
| sent_at                | timestamp         | Yes      | null           | Delivery time                                    |
| failed_at              | timestamp         | Yes      | null           | Last failure time                                |
| created_at             | timestamp         | Yes      | null           | Created time                                     |
| updated_at             | timestamp         | Yes      | null           | Updated time                                     |

Indexes:

```txt
unique: idempotency_key
index: event
index: recipient_email
index: status
index: order_id, event
index: status, created_at
```

`payload` và `error_message` không được chứa SMTP password, API key hoặc payment secret.

---

# 6.24. order_items

Lưu sản phẩm trong đơn hàng.

Bảng này phải lưu snapshot product name, sku, price, tax tại thời điểm mua.

| Column             | Type            | Nullable | Default        | Description               |
| ------------------ | --------------- | -------- | -------------- | ------------------------- |
| id                 | bigint unsigned | No       | auto increment | Primary key               |
| order_id           | bigint unsigned | No       | null           | FK to orders.id           |
| product_id         | bigint unsigned | Yes      | null           | FK to products.id         |
| product_variant_id | bigint unsigned | Yes      | null           | FK to product_variants.id |
| product_name       | varchar(255)    | No       | null           | Product name snapshot     |
| product_sku        | varchar(100)    | Yes      | null           | Product SKU snapshot      |
| variant_name       | varchar(255)    | Yes      | null           | Variant name snapshot     |
| price              | decimal(15,2)   | No       | 0.00           | Unit price snapshot       |
| quantity           | int             | No       | 1              | Quantity                  |
| subtotal           | decimal(15,2)   | No       | 0.00           | price * quantity          |
| tax_rate           | decimal(8,4)    | No       | 0.0000         | Tax rate snapshot         |
| tax_amount         | decimal(15,2)   | No       | 0.00           | Tax amount                |
| total              | decimal(15,2)   | No       | 0.00           | subtotal + tax amount     |
| created_at         | timestamp       | Yes      | null           | Created time              |
| updated_at         | timestamp       | Yes      | null           | Updated time              |

Indexes:

```txt
index: order_id
index: product_id
index: product_variant_id
index: product_sku
```

Business rules:

```txt
Không phụ thuộc product hiện tại khi xem order cũ.
Nếu product bị xóa thì order item vẫn hiển thị được product_name.
```

---

# 6.25. payments

Lưu thông tin thanh toán.

| Column         | Type            | Nullable | Default        | Description                     |
| -------------- | --------------- | -------- | -------------- | ------------------------------- |
| id             | bigint unsigned | No       | auto increment | Primary key                     |
| order_id       | bigint unsigned | No       | null           | FK to orders.id                 |
| payment_method | varchar(50)     | No       | null           | cod, vnpay, momo, zalopay       |
| transaction_id | varchar(255)    | Yes      | null           | Gateway transaction id          |
| amount         | decimal(15,2)   | No       | 0.00           | Payment amount                  |
| currency_code  | varchar(10)     | No       | null           | Payment currency                |
| status         | varchar(50)     | No       | pending        | pending, paid, failed, refunded |
| paid_at        | datetime        | Yes      | null           | Paid time                       |
| raw_response   | json            | Yes      | null           | Gateway response                |
| created_at     | timestamp       | Yes      | null           | Created time                    |
| updated_at     | timestamp       | Yes      | null           | Updated time                    |

Indexes:

```txt
index: order_id
index: payment_method
index: transaction_id
index: status
```

Payment status values:

```txt
pending
paid
failed
refunded
```

Business rules:

```txt
COD tạo payment status pending.
Online payment phải verify callback signature trước khi update paid.
Không lưu thông tin thẻ thanh toán trong database.
```

---

# 6.26. shipping_addresses

Lưu địa chỉ giao hàng của user.

Order vẫn phải lưu snapshot `shipping_address`, không phụ thuộc hoàn toàn vào bảng này.

| Column       | Type            | Nullable | Default        | Description     |
| ------------ | --------------- | -------- | -------------- | --------------- |
| id           | bigint unsigned | No       | auto increment | Primary key     |
| user_id      | bigint unsigned | No       | null           | FK to users.id  |
| full_name    | varchar(255)    | No       | null           | Receiver name   |
| phone        | varchar(30)     | No       | null           | Receiver phone  |
| country_code | varchar(10)     | Yes      | VN             | Country code    |
| province     | varchar(255)    | Yes      | null           | Province/City   |
| district     | varchar(255)    | Yes      | null           | District        |
| ward         | varchar(255)    | Yes      | null           | Ward            |
| address_line | varchar(500)    | No       | null           | Address line    |
| is_default   | tinyint         | No       | 0              | Default address |
| created_at   | timestamp       | Yes      | null           | Created time    |
| updated_at   | timestamp       | Yes      | null           | Updated time    |

Indexes:

```txt
index: user_id
index: country_code
index: is_default
```

---

# 6.26.1. customer_addresses

Lưu sổ địa chỉ của customer cho Task 31. Bảng này độc lập với
`order_addresses`; đơn hàng luôn tiếp tục dùng snapshot tại thời điểm đặt hàng.

| Column               | Type            | Nullable | Default        | Description                 |
| -------------------- | --------------- | -------- | -------------- | --------------------------- |
| id                   | bigint unsigned | No       | auto increment | Primary key                 |
| user_id              | bigint unsigned | No       | null           | FK to users.id              |
| label                | varchar(255)    | Yes      | null           | Home, Office                |
| recipient_name       | varchar(255)    | No       | null           | Receiver snapshot source    |
| phone                | varchar(30)     | No       | null           | Receiver phone              |
| address_line_1       | varchar(500)    | No       | null           | Main address                |
| address_line_2       | varchar(500)    | Yes      | null           | Additional address          |
| city                 | varchar(255)    | No       | null           | Province/city               |
| district             | varchar(255)    | Yes      | null           | District                    |
| ward                 | varchar(255)    | Yes      | null           | Ward                        |
| postal_code          | varchar(30)     | Yes      | null           | Postal code                 |
| country              | varchar(10)     | No       | VN             | Country code                |
| is_default_shipping  | tinyint         | No       | 0              | Default shipping address    |
| is_default_billing   | tinyint         | No       | 0              | Default billing address     |
| created_at           | timestamp       | Yes      | null           | Created time                |
| updated_at           | timestamp       | Yes      | null           | Updated time                |

Application transaction bảo đảm mỗi user chỉ có một default shipping và một
default billing address. `user_id` luôn lấy từ authenticated user, không nhận từ form.

---

# 6.27. banners

Lưu thông tin kỹ thuật của banner.

Text dịch sẽ lưu ở `banner_translations`.

| Column     | Type            | Nullable | Default        | Description            |
| ---------- | --------------- | -------- | -------------- | ---------------------- |
| id         | bigint unsigned | No       | auto increment | Primary key            |
| image      | varchar(500)    | No       | null           | Banner image           |
| link_url   | varchar(500)    | Yes      | null           | Link URL               |
| position   | varchar(100)    | No       | null           | Banner position        |
| sort_order | int             | No       | 0              | Display order          |
| start_at   | datetime        | Yes      | null           | Start time             |
| end_at     | datetime        | Yes      | null           | End time               |
| status     | tinyint         | No       | 1              | 1: active, 0: inactive |
| created_at | timestamp       | Yes      | null           | Created time           |
| updated_at | timestamp       | Yes      | null           | Updated time           |
| deleted_at | timestamp       | Yes      | null           | Soft delete            |

Indexes:

```txt
index: position
index: status
index: sort_order
index: start_at
index: end_at
```

Position values:

```txt
home_slider
home_middle
category_top
product_detail
```

---

# 6.28. banner_translations

Lưu nội dung dịch của banner.

| Column        | Type            | Nullable | Default        | Description      |
| ------------- | --------------- | -------- | -------------- | ---------------- |
| id            | bigint unsigned | No       | auto increment | Primary key      |
| banner_id     | bigint unsigned | No       | null           | FK to banners.id |
| language_code | varchar(10)     | No       | null           | vi, en, ja       |
| title         | varchar(255)    | Yes      | null           | Banner title     |
| subtitle      | varchar(255)    | Yes      | null           | Banner subtitle  |
| button_text   | varchar(100)    | Yes      | null           | Button text      |
| created_at    | timestamp       | Yes      | null           | Created time     |
| updated_at    | timestamp       | Yes      | null           | Updated time     |

Indexes:

```txt
index: banner_id
index: language_code
unique: banner_id, language_code
```

---

# 6.29. reviews

Lưu đánh giá sản phẩm.

| Column     | Type            | Nullable | Default        | Description                 |
| ---------- | --------------- | -------- | -------------- | --------------------------- |
| id         | bigint unsigned | No       | auto increment | Primary key                 |
| user_id    | bigint unsigned | No       | null           | FK to users.id              |
| product_id | bigint unsigned | No       | null           | FK to products.id           |
| order_id   | bigint unsigned | Yes      | null           | FK to orders.id             |
| rating     | tinyint         | No       | 5              | Rating from 1 to 5          |
| comment    | text            | Yes      | null           | Review comment              |
| status     | varchar(50)     | No       | pending        | pending, approved, rejected |
| created_at | timestamp       | Yes      | null           | Created time                |
| updated_at | timestamp       | Yes      | null           | Updated time                |
| deleted_at | timestamp       | Yes      | null           | Soft delete                 |

Indexes:

```txt
index: user_id
index: product_id
index: order_id
index: rating
index: status
```

Business rules:

```txt
Chỉ customer đã mua sản phẩm mới được review.
Mỗi user chỉ nên review một lần cho một product trong một order.
Review cần được admin duyệt trước khi hiển thị.
```

---

# 7. Main Relationships

Quan hệ chính:

```txt
users 1-n orders
users 1-n reviews
users 1-n shipping_addresses
users 1-n customer_addresses
users 1-n inventory_logs

roles n-n users
roles n-n permissions

categories 1-n categories
categories 1-n category_translations
categories 1-n products

languages 1-n category_translations
languages 1-n product_translations
languages 1-n banner_translations

currencies 1-n orders

tax_classes 1-n tax_rates
tax_classes 1-n products

products 1-n product_translations
products 1-n product_images
products 1-n product_variants
products 1-n inventory_stocks
products 1-n inventory_logs
products 1-n reviews
products 1-n order_items
products 1-n cart_items

product_variants 1-n inventory_stocks
product_variants 1-n inventory_logs
product_variants 1-n cart_items
product_variants 1-n order_items

carts 1-n cart_items
users 1-n carts

coupons 1-n coupon_usages

orders 1-n order_items
orders 1-1 payments
orders 1-n coupon_usages

banners 1-n banner_translations
```

---

# 8. Currency Design

## 8.1. Base Currency

Base currency mặc định:

```txt
VND
```

Tất cả giá trong bảng `products`, `product_variants`, `cart_items` nên lưu theo base currency.

---

## 8.2. Exchange Rate

Trong bảng `currencies`:

```txt
VND exchange_rate = 1
USD exchange_rate = 25000
JPY exchange_rate = 170
```

Công thức hiển thị:

```txt
display_price = base_price / exchange_rate
```

Ví dụ:

```txt
Product price = 500000 VND
USD exchange_rate = 25000
Display price = 20 USD
```

---

## 8.3. Order Snapshot

Khi tạo order, phải lưu:

```txt
orders.currency_code
orders.exchange_rate
orders.subtotal
orders.discount_amount
orders.tax_amount
orders.shipping_fee
orders.total_amount
```

Mục tiêu:

```txt
Đơn hàng cũ không bị sai khi admin thay đổi tỷ giá mới.
```

---

# 9. Tax Design

## 9.1. Tax Class

Mỗi product có thể gán một `tax_class_id`.

Ví dụ:

```txt
standard_tax
reduced_tax
tax_free
```

---

## 9.2. Tax Rate

Mỗi tax class có thể có nhiều tax rate theo country/region.

Ví dụ:

```txt
standard_tax - VN - 10%
tax_free - VN - 0%
```

---

## 9.3. Tax Calculation

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

## 9.4. Order Item Tax Snapshot

Khi tạo order item, phải lưu:

```txt
order_items.tax_rate
order_items.tax_amount
order_items.total
```

Mục tiêu:

```txt
Đơn hàng cũ vẫn đúng khi admin thay đổi tax rate.
```

---

# 10. Translation Design

## 10.1. Translation Tables

Các bảng translation:

```txt
category_translations
product_translations
banner_translations
```

---

## 10.2. Language Code

Các translation table dùng `language_code`.

Ví dụ:

```txt
vi
en
ja
```

---

## 10.3. Slug Design

Slug nên unique theo từng language.

Ví dụ:

```txt
unique: language_code, slug
```

Ví dụ product:

```txt
/vi/products/ao-thun-nam
/en/products/men-t-shirt
/ja/products/mens-t-shirt
```

---

## 10.4. Fallback Language

Nếu không có translation cho language hiện tại, fallback về default language.

Ví dụ:

```txt
Current language: en
Default language: vi
Product chưa có bản dịch en
Hệ thống hiển thị bản dịch vi
```

---

# 11. Order Calculation Design

## 11.1. Checkout Formula

Công thức checkout:

```txt
subtotal = sum(item_price * quantity)
discount_amount = coupon discount
taxable_amount = subtotal - discount_amount
tax_amount = taxable_amount * tax_rate
shipping_fee = configured shipping fee
total_amount = taxable_amount + tax_amount + shipping_fee
```

---

## 11.2. Order Code

Format order code đề xuất:

```txt
ORD + YYYYMMDD + running number
```

Ví dụ:

```txt
ORD202606180001
```

---

## 11.3. Inventory Handling

Khi tạo order thành công:

```txt
1. Validate stock
2. Create order
3. Create order items
4. Create payment
5. Reduce inventory
6. Create inventory logs
7. Clear cart
```

Khi hủy order:

```txt
1. Update order_status = cancelled
2. Return stock
3. Create inventory logs
```

---

# 12. Important Indexes

Các index quan trọng nên có:

```txt
users.email
users.role
users.status

languages.code
languages.status

currencies.code
currencies.status

categories.parent_id
categories.status

category_translations.language_code
category_translations.slug

products.sku
products.category_id
products.status
products.price
products.is_featured

product_translations.language_code
product_translations.slug

product_images.product_id
product_images.is_main

product_variants.product_id
product_variants.sku

inventory_stocks.product_id
inventory_stocks.product_variant_id

inventory_logs.product_id
inventory_logs.type
inventory_logs.created_at

carts.user_id
carts.session_id

cart_items.cart_id
cart_items.product_id

coupons.code
coupons.status
coupons.start_date
coupons.end_date

orders.user_id
orders.order_code
orders.order_status
orders.payment_status
orders.created_at

order_items.order_id
order_items.product_id

payments.order_id
payments.transaction_id
payments.status

reviews.product_id
reviews.user_id
reviews.status
```

---

# 13. Migration Order

Thứ tự tạo migration đề xuất:

```txt
01_create_users_table
02_create_roles_table
03_create_permissions_table
04_create_role_user_table
05_create_permission_role_table

06_create_system_settings_table
07_create_languages_table
08_create_currencies_table

09_create_tax_classes_table
10_create_tax_rates_table

11_create_categories_table
12_create_category_translations_table

13_create_products_table
14_create_product_translations_table
15_create_product_images_table
16_create_product_variants_table

17_create_inventory_stocks_table
18_create_inventory_logs_table

19_create_carts_table
20_create_cart_items_table

21_create_coupons_table
22_create_coupon_usages_table

23_create_orders_table
24_create_order_items_table
25_create_payments_table
26_create_shipping_addresses_table

27_create_banners_table
28_create_banner_translations_table

29_create_reviews_table
```

Lưu ý:

```txt
Bảng cha phải tạo trước bảng con.
Bảng có foreign key phải tạo sau bảng được tham chiếu.
```

---

# 14. Seeder Data

Dữ liệu seed ban đầu nên có:

## 14.1. Roles

```txt
super_admin
admin
staff
customer
```

---

## 14.2. Languages

```txt
vi - Vietnamese - Tiếng Việt - default
en - English - English
ja - Japanese - 日本語
```

---

## 14.3. Currencies

```txt
VND - Vietnamese Dong - ₫ - default - exchange_rate: 1
USD - US Dollar - $ - exchange_rate: 25000
JPY - Japanese Yen - ¥ - exchange_rate: 170
```

---

## 14.4. Tax Classes

```txt
standard_tax
reduced_tax
tax_free
```

---

## 14.5. Tax Rates

```txt
standard_tax - VN - 10%
reduced_tax - VN - 5%
tax_free - VN - 0%
```

---

## 14.6. System Settings

```txt
site_name = E-commerce System
default_language = vi
default_currency = VND
tax_enabled = true
price_include_tax = false
multi_language_enabled = true
multi_currency_enabled = true
```

---

# 15. Data Snapshot Rules

Các bảng cần lưu snapshot:

## 15.1. orders

```txt
customer_name
customer_phone
customer_email
shipping_address
currency_code
exchange_rate
subtotal
discount_amount
tax_amount
shipping_fee
total_amount
payment_method
payment_status
order_status
```

---

## 15.2. order_items

```txt
product_name
product_sku
variant_name
price
quantity
subtotal
tax_rate
tax_amount
total
```

---

## 15.3. payments

```txt
payment_method
transaction_id
amount
currency_code
status
raw_response
```

---

# 16. Soft Delete Rules

Nên dùng soft delete cho:

```txt
users
categories
products
product_variants
coupons
banners
reviews
```

Không nên hard delete dữ liệu đã phát sinh order.

Lý do:

```txt
Đảm bảo đơn hàng cũ vẫn truy xuất được dữ liệu liên quan.
Tránh mất dữ liệu lịch sử.
```

---

# 17. Security Notes

Các điểm bảo mật liên quan database:

```txt
Không lưu plain password.
Không lưu thông tin thẻ thanh toán.
Không lưu secret key payment gateway trong bảng public.
Sensitive setting phải để is_public = 0.
Upload image chỉ lưu path, không lưu file binary trong database.
Payment raw_response cần kiểm soát dữ liệu nhạy cảm trước khi lưu.
```

---

# 18. Future Enhancement Tables

Các bảng có thể thêm trong tương lai:

```txt
wishlists
wishlist_items

product_compare_items

flash_sales
flash_sale_items

customer_groups
customer_group_prices

shipping_methods
shipping_zones
shipping_rates

warehouses
warehouse_stocks

invoices
invoice_items

activity_logs

api_tokens

failed_jobs
jobs
```

---

# 19. MVP Database Scope

Giai đoạn MVP nên làm các bảng sau trước:

```txt
users
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
orders
order_items
payments
shipping_addresses
```

Các bảng có thể làm sau MVP:

```txt
roles
permissions
role_user
permission_role
coupons
coupon_usages
banners
banner_translations
reviews
```

---

# 20. Conclusion

Database này được thiết kế để hỗ trợ hệ thống e-commerce có đa ngôn ngữ, đa tiền tệ và cấu hình thuế ngay từ đầu.

Các điểm quan trọng nhất:

```txt
Product và Category phải tách translation table.
Product price lưu theo base currency.
Order phải lưu snapshot currency và exchange rate.
Order item phải lưu snapshot product name, price và tax.
Inventory phải có stock table và log table.
Checkout phải validate stock trước khi tạo order.
Không hard delete dữ liệu quan trọng đã phát sinh giao dịch.
```

Tài liệu này sẽ được dùng làm cơ sở để tạo migration, model, relationship và các task phát triển tiếp theo.
