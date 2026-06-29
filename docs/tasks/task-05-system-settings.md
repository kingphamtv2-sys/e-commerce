# Task 05: System Settings

## 1. Overview

Task này dùng để xây dựng chức năng quản lý thiết lập hệ thống cho e-commerce Laravel 12.

System Settings là nơi admin cấu hình các thông tin chung của website như:

* Tên website
* Logo
* Email hệ thống
* Số điện thoại
* Địa chỉ công ty
* Ngôn ngữ mặc định
* Tiền tệ mặc định
* Bật/tắt đa ngôn ngữ
* Bật/tắt đa tiền tệ
* Bật/tắt thuế
* Cấu hình giá đã bao gồm thuế hay chưa
* Phí vận chuyển mặc định

Các module sau như Language, Currency, Tax, Checkout, Order sẽ sử dụng dữ liệu từ System Settings.

---

## 2. Objectives

Sau khi hoàn thành task này, hệ thống cần đạt được:

* Có bảng `system_settings`.
* Có model `SystemSetting`.
* Có seeder tạo dữ liệu setting mặc định.
* Có admin screen để xem và cập nhật settings.
* Có service/helper để đọc setting dễ dàng.
* Có thể cache settings để tối ưu hiệu năng.
* Chỉ admin/super_admin được chỉnh sửa system settings.
* Staff có thể bị giới hạn quyền chỉnh sửa sau này.
* Settings được validate trước khi lưu.
* Sensitive settings không được public.

---

## 3. Scope

## 3.1. In Scope

Các phần sẽ làm trong task này:

* Tạo hoặc kiểm tra migration bảng `system_settings`.
* Tạo model `SystemSetting`.
* Tạo seeder dữ liệu mặc định.
* Tạo `SystemSettingService`.
* Tạo admin screen `/admin/settings`.
* Hiển thị form chỉnh sửa setting.
* Lưu setting dạng key-value.
* Validate input.
* Cache settings.
* Clear cache sau khi update.
* Tạo helper lấy setting nếu cần.
* Tích hợp menu System Settings trong Admin Layout.

---

## 3.2. Out of Scope

Các phần không làm trong task này:

* CRUD Language.
* CRUD Currency.
* CRUD Tax.
* Upload logo nâng cao.
* Quản lý payment gateway settings.
* SMTP settings nâng cao.
* Shipping method nâng cao.
* Permission nâng cao theo bảng `roles`, `permissions`.
* Audit log nâng cao.
* Multi-store settings.
* API settings.

---

## 4. User Roles

| Role        | Permission                                   |
| ----------- | -------------------------------------------- |
| super_admin | Có quyền xem và cập nhật tất cả settings     |
| admin       | Có quyền xem và cập nhật settings cơ bản     |
| staff       | Có thể xem hoặc bị chặn tùy cấu hình sau này |
| customer    | Không được truy cập                          |

Trong task này, route `/admin/settings` chỉ cần bảo vệ bởi middleware:

```txt
auth
admin
```

---

## 5. Functional Requirements

## FR-01: View System Settings

Admin có thể truy cập:

```txt
/admin/settings
```

Expected behavior:

* Nếu chưa login, redirect về `/login`.
* Nếu là customer, không cho truy cập.
* Nếu là admin/super_admin/staff, hiển thị màn hình settings.
* Form hiển thị dữ liệu setting hiện tại.
* Nếu setting chưa tồn tại, hiển thị giá trị mặc định.

---

## FR-02: Update General Settings

Admin có thể cập nhật thông tin chung của website.

Fields:

| Field        | Key          | Type   | Required | Description       |
| ------------ | ------------ | ------ | -------- | ----------------- |
| Site Name    | site_name    | string | Yes      | Tên website       |
| Site Email   | site_email   | string | No       | Email liên hệ     |
| Site Phone   | site_phone   | string | No       | Số điện thoại     |
| Site Address | site_address | text   | No       | Địa chỉ công ty   |
| Logo         | site_logo    | string | No       | Đường dẫn logo    |
| Favicon      | site_favicon | string | No       | Đường dẫn favicon |

Expected behavior:

* `site_name` bắt buộc.
* Email nếu nhập phải đúng format.
* Dữ liệu được lưu vào bảng `system_settings`.

---

## FR-03: Update Localization Settings

Admin có thể cập nhật thiết lập liên quan ngôn ngữ và tiền tệ.

Fields:

| Field                  | Key                    | Type    | Required | Description         |
| ---------------------- | ---------------------- | ------- | -------- | ------------------- |
| Default Language       | default_language       | string  | Yes      | Ngôn ngữ mặc định   |
| Default Currency       | default_currency       | string  | Yes      | Tiền tệ mặc định    |
| Multi Language Enabled | multi_language_enabled | boolean | Yes      | Bật/tắt đa ngôn ngữ |
| Multi Currency Enabled | multi_currency_enabled | boolean | Yes      | Bật/tắt đa tiền tệ  |

Default values:

```txt
default_language = vi
default_currency = VND
multi_language_enabled = true
multi_currency_enabled = true
```

Lưu ý:

* Trong task này chưa cần CRUD language/currency.
* Có thể dùng select hard-code tạm thời: `vi`, `en`, `ja` và `VND`, `USD`, `JPY`.
* Khi Task 06/07 hoàn thành, select có thể lấy dữ liệu từ bảng `languages`, `currencies`.

---

## FR-04: Update Tax Settings

Admin có thể cập nhật thiết lập thuế cơ bản.

Fields:

| Field             | Key               | Type    | Required | Description                  |
| ----------------- | ----------------- | ------- | -------- | ---------------------------- |
| Tax Enabled       | tax_enabled       | boolean | Yes      | Bật/tắt thuế                 |
| Price Include Tax | price_include_tax | boolean | Yes      | Giá đã bao gồm thuế hay chưa |

Default values:

```txt
tax_enabled = true
price_include_tax = false
```

Ý nghĩa:

```txt
tax_enabled = true
```

Hệ thống có tính thuế ở checkout.

```txt
price_include_tax = false
```

Giá sản phẩm chưa bao gồm thuế.

---

## FR-05: Update Order Settings

Admin có thể cập nhật một số setting cơ bản cho order/checkout.

Fields:

| Field                        | Key                      | Type   | Required | Description                       |
| ---------------------------- | ------------------------ | ------ | -------- | --------------------------------- |
| Default Shipping Fee         | default_shipping_fee     | number | Yes      | Phí vận chuyển mặc định           |
| Free Shipping Minimum Amount | free_shipping_min_amount | number | No       | Miễn phí ship nếu đơn đạt mức này |
| Order Code Prefix            | order_code_prefix        | string | Yes      | Tiền tố mã đơn hàng               |

Default values:

```txt
default_shipping_fee = 30000
free_shipping_min_amount = 500000
order_code_prefix = ORD
```

---

## FR-06: Setting Service

Hệ thống cần có service để đọc setting.

File đề xuất:

```txt
app/Services/SystemSettingService.php
```

Methods đề xuất:

```php
get(string $key, mixed $default = null): mixed
set(string $key, mixed $value, string $type = 'string', ?string $group = null): void
all(): array
getGroup(string $group): array
clearCache(): void
```

Ví dụ sử dụng:

```php
app(SystemSettingService::class)->get('site_name', 'E-commerce System');
```

Hoặc helper nếu có:

```php
setting('site_name', 'E-commerce System');
```

---

## FR-07: Cache Settings

Settings nên được cache để tránh query database nhiều lần.

Expected behavior:

* Khi gọi `all()` lần đầu, lấy từ database và cache lại.
* Khi update setting, clear cache.
* Cache key đề xuất:

```txt
system_settings
```

---

## FR-08: Public Settings

Một số settings có thể public, một số không.

Ví dụ public:

```txt
site_name
site_logo
site_favicon
default_language
default_currency
multi_language_enabled
multi_currency_enabled
tax_enabled
price_include_tax
```

Ví dụ private:

```txt
payment_secret_key
smtp_password
api_secret
```

Trong task này chưa cần payment secret, nhưng bảng đã có field `is_public` để hỗ trợ về sau.

---

## 6. UI / Screen Design

## 6.1. Screen List

| Screen          | URL               | Description            |
| --------------- | ----------------- | ---------------------- |
| System Settings | `/admin/settings` | Form cấu hình hệ thống |

---

## 6.2. Layout

Screen này dùng admin layout đã làm ở Task 04.

```blade
@extends('layouts.admin')

@section('title', 'System Settings')

@section('content')
    ...
@endsection
```

---

## 6.3. Form Sections

Form nên chia thành các section:

```txt
General Settings
Localization Settings
Tax Settings
Order Settings
```

---

## 6.4. General Settings UI

Fields:

| Label        | Input Type | Key          |
| ------------ | ---------- | ------------ |
| Site Name    | text       | site_name    |
| Site Email   | email      | site_email   |
| Site Phone   | text       | site_phone   |
| Site Address | textarea   | site_address |
| Site Logo    | text       | site_logo    |
| Site Favicon | text       | site_favicon |

Trong task này, logo/favicon có thể nhập path dạng text.

Upload file sẽ làm sau nếu cần.

---

## 6.5. Localization Settings UI

Fields:

| Label                 | Input Type | Key                    |
| --------------------- | ---------- | ---------------------- |
| Default Language      | select     | default_language       |
| Default Currency      | select     | default_currency       |
| Enable Multi Language | checkbox   | multi_language_enabled |
| Enable Multi Currency | checkbox   | multi_currency_enabled |

Select options tạm thời:

```txt
Languages: vi, en, ja
Currencies: VND, USD, JPY
```

---

## 6.6. Tax Settings UI

Fields:

| Label             | Input Type | Key               |
| ----------------- | ---------- | ----------------- |
| Enable Tax        | checkbox   | tax_enabled       |
| Price Include Tax | checkbox   | price_include_tax |

---

## 6.7. Order Settings UI

Fields:

| Label                        | Input Type | Key                      |
| ---------------------------- | ---------- | ------------------------ |
| Default Shipping Fee         | number     | default_shipping_fee     |
| Free Shipping Minimum Amount | number     | free_shipping_min_amount |
| Order Code Prefix            | text       | order_code_prefix        |

---

## 6.8. Submit Button

Button:

```txt
Save Settings
```

Expected behavior:

* Submit form bằng `PUT` hoặc `POST`.
* Validate dữ liệu.
* Save settings.
* Clear cache.
* Redirect back với success message.

---

## 7. Database Design

## 7.1. Table: system_settings

Nếu bảng chưa có, tạo migration.

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

---

## 7.2. Setting Groups

Group đề xuất:

```txt
general
localization
tax
order
payment
mail
```

Trong task này dùng:

```txt
general
localization
tax
order
```

---

## 7.3. Default Setting Data

Seeder cần có dữ liệu mặc định:

| Key                      | Value             | Type    | Group        | Public |
| ------------------------ | ----------------- | ------- | ------------ | ------ |
| site_name                | E-commerce System | string  | general      | Yes    |
| site_email               | null              | string  | general      | Yes    |
| site_phone               | null              | string  | general      | Yes    |
| site_address             | null              | string  | general      | Yes    |
| site_logo                | null              | string  | general      | Yes    |
| site_favicon             | null              | string  | general      | Yes    |
| default_language         | vi                | string  | localization | Yes    |
| default_currency         | VND               | string  | localization | Yes    |
| multi_language_enabled   | true              | boolean | localization | Yes    |
| multi_currency_enabled   | true              | boolean | localization | Yes    |
| tax_enabled              | true              | boolean | tax          | Yes    |
| price_include_tax        | false             | boolean | tax          | Yes    |
| default_shipping_fee     | 30000             | number  | order        | Yes    |
| free_shipping_min_amount | 500000            | number  | order        | Yes    |
| order_code_prefix        | ORD               | string  | order        | No     |

---

## 8. Route Design

## 8.1. Admin Settings Routes

Routes đề xuất:

```php
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::get('/settings', [SystemSettingController::class, 'edit'])
            ->name('settings.edit');

        Route::put('/settings', [SystemSettingController::class, 'update'])
            ->name('settings.update');
    });
```

---

## 9. Controller Design

## 9.1. SystemSettingController

File đề xuất:

```txt
app/Http/Controllers/Admin/SystemSettingController.php
```

Methods:

```php
public function edit()
{
    // Load settings
    // Return view admin.settings.edit
}

public function update(UpdateSystemSettingRequest $request)
{
    // Validate
    // Save settings
    // Clear cache
    // Redirect back with success message
}
```

---

## 10. Request Validation

## 10.1. UpdateSystemSettingRequest

File đề xuất:

```txt
app/Http/Requests/Admin/UpdateSystemSettingRequest.php
```

Validation rules:

```php
return [
    'site_name' => ['required', 'string', 'max:255'],
    'site_email' => ['nullable', 'email', 'max:255'],
    'site_phone' => ['nullable', 'string', 'max:30'],
    'site_address' => ['nullable', 'string', 'max:1000'],
    'site_logo' => ['nullable', 'string', 'max:500'],
    'site_favicon' => ['nullable', 'string', 'max:500'],

    'default_language' => ['required', 'string', 'max:10'],
    'default_currency' => ['required', 'string', 'max:10'],
    'multi_language_enabled' => ['nullable', 'boolean'],
    'multi_currency_enabled' => ['nullable', 'boolean'],

    'tax_enabled' => ['nullable', 'boolean'],
    'price_include_tax' => ['nullable', 'boolean'],

    'default_shipping_fee' => ['required', 'numeric', 'min:0'],
    'free_shipping_min_amount' => ['nullable', 'numeric', 'min:0'],
    'order_code_prefix' => ['required', 'string', 'max:20'],
];
```

Checkbox fields cần xử lý nếu không submit value.

Các key dạng boolean:

```txt
multi_language_enabled
multi_currency_enabled
tax_enabled
price_include_tax
```

Nếu checkbox không được tick, lưu là `false`.

---

## 11. Model Design

## 11.1. SystemSetting Model

File:

```txt
app/Models/SystemSetting.php
```

Fillable:

```php
protected $fillable = [
    'key',
    'value',
    'type',
    'group',
    'is_public',
];
```

Casts:

```php
protected $casts = [
    'is_public' => 'boolean',
];
```

---

## 12. Service Design

## 12.1. SystemSettingService

File:

```txt
app/Services/SystemSettingService.php
```

Responsibilities:

* Load settings.
* Get setting by key.
* Get settings by group.
* Save setting.
* Cast setting value by type.
* Clear settings cache.

Methods:

```php
public function all(): array;

public function get(string $key, mixed $default = null): mixed;

public function getGroup(string $group): array;

public function set(
    string $key,
    mixed $value,
    string $type = 'string',
    ?string $group = null,
    bool $isPublic = false
): void;

public function clearCache(): void;
```

---

## 12.2. Value Casting

Setting value cần được convert theo `type`.

Supported types:

```txt
string
number
boolean
json
```

Casting rules:

```txt
string  -> return string
number  -> return numeric value
boolean -> return true/false
json    -> decode json to array
```

---

## 12.3. Cache Logic

Pseudo logic:

```txt
all():
    return Cache::rememberForever('system_settings', function () {
        return SystemSetting::query()->get()->mapWithKeys(...)
    })

set():
    updateOrCreate setting
    clearCache()
```

---

## 13. Helper Design

Có thể tạo helper function nếu project đang có cấu trúc helper.

Helper đề xuất:

```php
function setting(string $key, mixed $default = null): mixed
{
    return app(\App\Services\SystemSettingService::class)->get($key, $default);
}
```

Nếu không muốn tạo helper ở task này thì có thể dùng service trực tiếp.

---

## 14. Seeder Design

## 14.1. SystemSettingSeeder

File đề xuất:

```txt
database/seeders/SystemSettingSeeder.php
```

Seeder nên dùng `updateOrCreate()` để chạy nhiều lần không bị duplicate.

Default settings:

```php
[
    ['key' => 'site_name', 'value' => 'E-commerce System', 'type' => 'string', 'group' => 'general', 'is_public' => true],
    ['key' => 'site_email', 'value' => null, 'type' => 'string', 'group' => 'general', 'is_public' => true],
    ['key' => 'site_phone', 'value' => null, 'type' => 'string', 'group' => 'general', 'is_public' => true],
    ['key' => 'site_address', 'value' => null, 'type' => 'string', 'group' => 'general', 'is_public' => true],
    ['key' => 'site_logo', 'value' => null, 'type' => 'string', 'group' => 'general', 'is_public' => true],
    ['key' => 'site_favicon', 'value' => null, 'type' => 'string', 'group' => 'general', 'is_public' => true],

    ['key' => 'default_language', 'value' => 'vi', 'type' => 'string', 'group' => 'localization', 'is_public' => true],
    ['key' => 'default_currency', 'value' => 'VND', 'type' => 'string', 'group' => 'localization', 'is_public' => true],
    ['key' => 'multi_language_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'localization', 'is_public' => true],
    ['key' => 'multi_currency_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'localization', 'is_public' => true],

    ['key' => 'tax_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'tax', 'is_public' => true],
    ['key' => 'price_include_tax', 'value' => '0', 'type' => 'boolean', 'group' => 'tax', 'is_public' => true],

    ['key' => 'default_shipping_fee', 'value' => '30000', 'type' => 'number', 'group' => 'order', 'is_public' => true],
    ['key' => 'free_shipping_min_amount', 'value' => '500000', 'type' => 'number', 'group' => 'order', 'is_public' => true],
    ['key' => 'order_code_prefix', 'value' => 'ORD', 'type' => 'string', 'group' => 'order', 'is_public' => false],
]
```

---

## 15. View Design

## 15.1. Expected View File

```txt
resources/views/admin/settings/edit.blade.php
```

View dùng:

```blade
@extends('layouts.admin')

@section('title', 'System Settings')

@section('content')
    ...
@endsection
```

---

## 15.2. Success Message

Sau khi update thành công, hiển thị message:

```txt
Settings updated successfully.
```

---

## 15.3. Error Message

Nếu validation failed, hiển thị lỗi bên dưới từng field hoặc trên đầu form.

---

## 16. Business Logic

## 16.1. Update Settings Flow

Flow:

```txt
1. Admin truy cập /admin/settings.
2. Hệ thống load settings từ service.
3. Hiển thị form.
4. Admin cập nhật dữ liệu.
5. Submit form.
6. Validate request.
7. Convert checkbox boolean.
8. Save từng setting bằng service.
9. Clear cache.
10. Redirect back với success message.
```

---

## 16.2. Boolean Handling

HTML checkbox không gửi value nếu không được tick.

Vì vậy controller cần xử lý:

```txt
Nếu checkbox không có trong request:
    value = false

Nếu checkbox có trong request:
    value = true
```

Boolean keys:

```txt
multi_language_enabled
multi_currency_enabled
tax_enabled
price_include_tax
```

---

## 16.3. Setting Update Rule

Khi update setting, dùng:

```txt
updateOrCreate by key
```

Không tạo duplicate key.

---

## 17. Error Handling

| Case                              | Message / Action                           |
| --------------------------------- | ------------------------------------------ |
| Guest access `/admin/settings`    | Redirect login                             |
| Customer access `/admin/settings` | 403 hoặc redirect                          |
| Missing site_name                 | Show validation error                      |
| Invalid email                     | Show validation error                      |
| Invalid shipping fee              | Show validation error                      |
| Cache clear failed                | Log error, vẫn báo update nếu data save OK |
| Setting key duplicate             | Không xảy ra nếu dùng updateOrCreate       |

---

## 18. Security

Các yêu cầu bảo mật:

* Route phải dùng middleware `auth` và `admin`.
* Chỉ admin area mới được update settings.
* Validate toàn bộ input.
* Không cho update arbitrary key từ request nếu chưa whitelist.
* Không lưu secret ở setting public.
* CSRF protection phải bật.
* Không hiển thị sensitive private setting ra public.
* Không cho customer truy cập settings.

Quan trọng:

```txt
Không lặp qua toàn bộ request rồi lưu hết vào database.
Chỉ lưu các key được định nghĩa trong whitelist.
```

---

## 19. Files Expected

Codex có thể tạo hoặc sửa các file sau:

```txt
routes/web.php

app/Models/SystemSetting.php
app/Services/SystemSettingService.php
app/Http/Controllers/Admin/SystemSettingController.php
app/Http/Requests/Admin/UpdateSystemSettingRequest.php

database/migrations/*_create_system_settings_table.php
database/seeders/SystemSettingSeeder.php
database/seeders/DatabaseSeeder.php

resources/views/admin/settings/edit.blade.php
resources/views/admin/partials/sidebar.blade.php
```

Có thể sửa thêm nếu có helper:

```txt
app/helpers.php
composer.json
```

Nếu bảng `system_settings` đã có từ Task 02 database, không tạo migration trùng.

---

## 20. Commands

Sau khi implement, chạy:

```bash
php artisan migrate
php artisan db:seed --class=SystemSettingSeeder
```

Hoặc nếu local có thể reset database:

```bash
php artisan migrate:fresh --seed
```

Kiểm tra route:

```bash
php artisan route:list
```

Chạy server:

```bash
php artisan serve
```

Mở:

```txt
http://127.0.0.1:8000/admin/settings
```

Nếu có thay đổi frontend asset:

```bash
npm run build
```

---

## 21. Test Cases

| Test Case ID | Scenario                 | Steps                                | Expected Result             |
| ------------ | ------------------------ | ------------------------------------ | --------------------------- |
| TC-001       | Guest access settings    | Chưa login vào `/admin/settings`     | Redirect tới `/login`       |
| TC-002       | Customer access settings | Login customer vào `/admin/settings` | Bị chặn 403 hoặc redirect   |
| TC-003       | Admin access settings    | Login admin vào `/admin/settings`    | Hiển thị form settings      |
| TC-004       | Update general settings  | Sửa site name và submit              | Lưu thành công              |
| TC-005       | Missing site name        | Xóa site name và submit              | Hiển thị lỗi validation     |
| TC-006       | Invalid email            | Nhập email sai format                | Hiển thị lỗi validation     |
| TC-007       | Toggle multi language    | Tắt/bật checkbox                     | Giá trị lưu đúng true/false |
| TC-008       | Toggle tax enabled       | Tắt/bật checkbox                     | Giá trị lưu đúng true/false |
| TC-009       | Update shipping fee      | Nhập phí ship hợp lệ                 | Lưu thành công              |
| TC-010       | Invalid shipping fee     | Nhập số âm                           | Hiển thị lỗi validation     |
| TC-011       | Seeder repeat            | Chạy seeder nhiều lần                | Không duplicate settings    |
| TC-012       | Cache clear              | Update setting rồi reload            | Hiển thị giá trị mới        |

---

## 22. Acceptance Criteria

Task này được xem là hoàn thành khi:

* [ ] Có bảng `system_settings` hoặc xác nhận bảng đã tồn tại.
* [ ] Có model `SystemSetting`.
* [ ] Có `SystemSettingService`.
* [ ] Có seeder dữ liệu mặc định.
* [ ] Có route `/admin/settings`.
* [ ] Route được bảo vệ bởi `auth` và `admin`.
* [ ] Có màn hình edit settings.
* [ ] Admin xem được form settings.
* [ ] Admin update được general settings.
* [ ] Admin update được localization settings.
* [ ] Admin update được tax settings.
* [ ] Admin update được order settings.
* [ ] Checkbox boolean lưu đúng true/false.
* [ ] Settings được validate.
* [ ] Settings được cache.
* [ ] Cache được clear sau khi update.
* [ ] Menu System Settings trong sidebar link đúng tới `/admin/settings`.
* [ ] Không implement Language/Currency/Tax CRUD trong task này.
* [ ] Không lưu arbitrary request keys vào database.
* [ ] Chạy được `php artisan migrate:fresh --seed`.

---

## 23. Prompt for Codex

Sử dụng prompt sau để giao task này cho Codex:

```txt
Bạn hãy đọc các file sau trong project Laravel 12:

- docs/basic-design.md
- docs/database-design.md
- docs/tasks/task-04-admin-layout.md
- docs/tasks/task-05-system-settings.md

Sau đó implement Task 05: System Settings.

Yêu cầu:

1. Kiểm tra bảng system_settings đã tồn tại chưa.
   - Nếu chưa có, tạo migration theo docs/database-design.md.
   - Nếu đã có, không tạo migration trùng.

2. Tạo hoặc cập nhật:
   - app/Models/SystemSetting.php
   - app/Services/SystemSettingService.php
   - app/Http/Controllers/Admin/SystemSettingController.php
   - app/Http/Requests/Admin/UpdateSystemSettingRequest.php
   - database/seeders/SystemSettingSeeder.php
   - resources/views/admin/settings/edit.blade.php
   - routes/web.php
   - resources/views/admin/partials/sidebar.blade.php nếu cần cập nhật link menu

3. Tạo màn hình admin:
   - GET /admin/settings
   - PUT /admin/settings

4. Form settings gồm các nhóm:
   - General Settings
   - Localization Settings
   - Tax Settings
   - Order Settings

5. Các setting cần hỗ trợ:
   - site_name
   - site_email
   - site_phone
   - site_address
   - site_logo
   - site_favicon
   - default_language
   - default_currency
   - multi_language_enabled
   - multi_currency_enabled
   - tax_enabled
   - price_include_tax
   - default_shipping_fee
   - free_shipping_min_amount
   - order_code_prefix

6. Dùng whitelist setting keys.
   - Không lưu toàn bộ request tự do vào database.

7. Tạo SystemSettingService có:
   - all()
   - get()
   - getGroup()
   - set()
   - clearCache()

8. Cache settings bằng key:
   - system_settings

9. Sau khi update settings:
   - Save data
   - Clear cache
   - Redirect back với success message

10. Boolean checkbox phải lưu đúng true/false:
   - multi_language_enabled
   - multi_currency_enabled
   - tax_enabled
   - price_include_tax

11. Không implement:
   - Language CRUD
   - Currency CRUD
   - Tax CRUD
   - Product
   - Category
   - Cart
   - Checkout
   - Order

12. Sau khi code xong, hãy báo cáo:
   - File đã tạo
   - File đã sửa
   - Lệnh cần chạy
   - Cách test /admin/settings
   - Có điểm nào khác với task document không
```

---

## 24. Notes

* Task này phụ thuộc Task 03 Authentication và Task 04 Admin Layout.
* Nếu `system_settings` đã tạo ở Task 02 Database Design thì chỉ cần dùng lại.
* Nếu chưa có Language/Currency CRUD, select có thể hard-code tạm `vi/en/ja` và `VND/USD/JPY`.
* Sau này Task 06 Language Management và Task 07 Currency Management có thể cập nhật select lấy từ database.
* Không nên làm upload logo trong task này để tránh phức tạp.
* Logo/Favicon tạm thời lưu path dạng text.
