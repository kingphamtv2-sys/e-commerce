Bạn tạo file:

```txt
docs/tasks/task-06-language-management.md
```

và copy nội dung dưới đây vào file.

# Task 06: Language Management

## 1. Overview

Task này dùng để xây dựng chức năng quản lý ngôn ngữ cho hệ thống e-commerce Laravel 12.

Language Management cho phép admin quản lý danh sách ngôn ngữ được hỗ trợ trên website, ví dụ:

* Vietnamese: `vi`
* English: `en`
* Japanese: `ja`

Các module sau sẽ sử dụng dữ liệu ngôn ngữ này:

* Category Translation
* Product Translation
* Banner Translation
* Public Product Catalog
* Product Detail Page
* SEO URL theo ngôn ngữ

---

## 2. Objectives

Sau khi hoàn thành task này, hệ thống cần đạt được:

* Có màn hình danh sách ngôn ngữ trong admin.
* Có thể thêm ngôn ngữ mới.
* Có thể sửa ngôn ngữ.
* Có thể bật/tắt ngôn ngữ.
* Có thể thiết lập ngôn ngữ mặc định.
* Chỉ có một ngôn ngữ mặc định trong hệ thống.
* Không được xóa hoặc disable ngôn ngữ mặc định.
* Có seeder tạo sẵn `vi`, `en`, `ja`.
* Có service/helper lấy danh sách ngôn ngữ active.
* Sidebar admin có link tới Language Management.
* Label trong admin layout tự động dùng ngôn ngữ đang được chọn làm mặc định.
* Hỗ trợ bộ dịch admin tiếng Việt và tiếng Anh; locale chưa có bộ dịch sẽ fallback về tiếng Anh.

---

## 3. Scope

## 3.1. In Scope

Các phần sẽ làm trong task này:

* Tạo hoặc kiểm tra bảng `languages`.
* Tạo model `Language`.
* Tạo `LanguageService`.
* Tạo seeder `LanguageSeeder`.
* Tạo admin CRUD cơ bản cho languages.
* Tạo màn hình list.
* Tạo màn hình create.
* Tạo màn hình edit.
* Tạo chức năng delete nếu không phải default language.
* Tạo chức năng set default language.
* Validate input.
* Cache danh sách languages nếu cần.
* Cập nhật sidebar admin.
* Tạo middleware thiết lập locale cho khu vực admin từ default language.
* Bổ sung translation resource cho label admin tiếng Việt và tiếng Anh.

---

## 3.2. Out of Scope

Các phần không làm trong task này:

* Dịch Category.
* Dịch Product.
* Dịch Banner.
* Public language switcher.
* Middleware detect language từ URL.
* Auto translate.
* SEO URL đa ngôn ngữ.
* Import/export translation.
* Dịch toàn bộ public website.
* Bộ dịch admin tiếng Nhật đầy đủ.

---

## 4. User Roles

| Role        | Permission                                 |
| ----------- | ------------------------------------------ |
| super_admin | Quản lý toàn bộ language                   |
| admin       | Quản lý language                           |
| staff       | Có thể xem, quyền chỉnh sửa sẽ mở rộng sau |
| customer    | Không được truy cập                        |

Route admin cần bảo vệ bằng:

```txt
auth
admin
```

---

## 5. Functional Requirements

## FR-01: Language List

Admin có thể xem danh sách ngôn ngữ tại:

```txt
/admin/languages
```

Danh sách hiển thị:

| Field       | Description                         |
| ----------- | ----------------------------------- |
| Code        | Mã ngôn ngữ, ví dụ `vi`, `en`, `ja` |
| Name        | Tên tiếng Anh, ví dụ `Vietnamese`   |
| Native Name | Tên bản địa, ví dụ `Tiếng Việt`     |
| Default     | Có phải ngôn ngữ mặc định không     |
| Status      | Active / Inactive                   |
| Sort Order  | Thứ tự hiển thị                     |
| Actions     | Edit / Delete / Set Default         |

---

## FR-02: Create Language

Admin có thể thêm ngôn ngữ mới.

URL:

```txt
/admin/languages/create
```

Input:

| Field       | Required | Description            |
| ----------- | -------- | ---------------------- |
| code        | Yes      | Mã ngôn ngữ            |
| name        | Yes      | Tên ngôn ngữ           |
| native_name | No       | Tên bản địa            |
| sort_order  | No       | Thứ tự hiển thị        |
| status      | Yes      | Active / Inactive      |
| is_default  | No       | Có phải mặc định không |

Expected behavior:

* `code` phải unique.
* `code` nên viết thường.
* Nếu set language mới là default, các language khác phải bỏ default.
* Nếu chưa có language default nào, language đầu tiên có thể được set default.

---

## FR-03: Edit Language

Admin có thể sửa thông tin ngôn ngữ.

URL:

```txt
/admin/languages/{id}/edit
```

Expected behavior:

* Có thể sửa `name`, `native_name`, `sort_order`, `status`.
* Cẩn thận khi sửa `code` vì các bảng translation sẽ dùng `language_code`.
* Nếu language đã được sử dụng trong translation table, không nên đổi code tùy tiện.

Giai đoạn này có thể cho sửa code nếu chưa phát sinh translation.

---

## FR-04: Delete Language

Admin có thể xóa ngôn ngữ nếu thỏa điều kiện.

Business rules:

```txt
Không được xóa default language.
Không được xóa language đã có dữ liệu translation nếu sau này đã phát sinh.
```

Trong task này, nếu chưa có translation data, chỉ cần check:

```txt
is_default = false
```

---

## FR-05: Set Default Language

Admin có thể chọn một language làm mặc định.

URL đề xuất:

```txt
/admin/languages/{id}/set-default
```

Expected behavior:

* Language được chọn sẽ có `is_default = true`.
* Tất cả language khác có `is_default = false`.
* Language được set default phải có `status = active`.
* Clear cache language sau khi update.
* Request admin tiếp theo phải sử dụng code của default language làm locale.
* Khi chuyển default sang `vi`, các label đã khai báo trong admin layout, dashboard, System Settings và Language Management hiển thị tiếng Việt.
* Khi chuyển default sang `en`, các label trên hiển thị tiếng Anh.
* Nếu locale chưa có bộ dịch riêng, hệ thống fallback về tiếng Anh.

---

## FR-05.1: Admin Locale

Khu vực `/admin/*` sử dụng middleware locale riêng:

```txt
SetAdminLocale
```

Flow:

```txt
1. Admin truy cập route /admin/*.
2. Middleware lấy default language qua LanguageService.
3. Gọi app()->setLocale(default_language.code).
4. Blade render label từ translation resource.
5. Nếu chưa có default language, dùng locale mặc định trong config/app.php.
```

Translation resource hiện có:

```txt
lang/vi/admin.php
lang/en/admin.php
```

Phạm vi label đã dịch:

```txt
Admin sidebar
Admin header và breadcrumb
Admin dashboard
System Settings
Language Management
Flash message liên quan settings/languages
```

Locale `ja` hiện fallback về tiếng Anh cho đến khi có `lang/ja/admin.php`.

---

## FR-06: Disable Language

Admin có thể tắt language không sử dụng.

Business rules:

```txt
Không được disable default language.
Nếu language inactive, không hiển thị ở public site.
```

---

## FR-07: Language Service

Tạo service để dùng lại trong các module sau.

File đề xuất:

```txt
app/Services/LanguageService.php
```

Methods đề xuất:

```php
public function all(): array;

public function active(): array;

public function getDefault(): ?Language;

public function findByCode(string $code): ?Language;

public function setDefault(Language $language): void;

public function clearCache(): void;
```

---

## FR-08: Cache Languages

Danh sách language nên được cache.

Cache keys đề xuất:

```txt
languages_all
languages_active
language_default
```

Khi create/update/delete/set default thì clear cache.

---

## 6. UI / Screen Design

## 6.1. Screen List

| Screen          | URL                          | Description        |
| --------------- | ---------------------------- | ------------------ |
| Language List   | `/admin/languages`           | Danh sách ngôn ngữ |
| Create Language | `/admin/languages/create`    | Thêm ngôn ngữ      |
| Edit Language   | `/admin/languages/{id}/edit` | Sửa ngôn ngữ       |

---

## 6.2. List Screen

Các thành phần chính:

* Page title: `Languages`
* Button: `Add Language`
* Table danh sách language
* Action buttons: `Edit`, `Delete`, `Set Default`

Table columns:

| Column      | Description       |
| ----------- | ----------------- |
| Code        | Mã ngôn ngữ       |
| Name        | Tên ngôn ngữ      |
| Native Name | Tên bản địa       |
| Default     | Badge default     |
| Status      | Active / Inactive |
| Sort Order  | Thứ tự            |
| Actions     | Các nút thao tác  |

---

## 6.3. Create/Edit Form

Fields:

| Label            | Input Type      | Field       |
| ---------------- | --------------- | ----------- |
| Code             | text            | code        |
| Name             | text            | name        |
| Native Name      | text            | native_name |
| Sort Order       | number          | sort_order  |
| Status           | select/checkbox | status      |
| Default Language | checkbox        | is_default  |

Submit button:

```txt
Save
```

Cancel button:

```txt
Back
```

---

## 7. Database Design

## 7.1. Table: languages

Nếu bảng chưa có, tạo migration theo thiết kế sau.

| Column      | Type            | Nullable | Default        | Description                   |
| ----------- | --------------- | -------- | -------------- | ----------------------------- |
| id          | bigint unsigned | No       | auto increment | Primary key                   |
| code        | varchar(10)     | No       | null           | `vi`, `en`, `ja`              |
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
index: sort_order
```

---

## 7.2. Default Data

Seeder cần tạo:

| Code | Name       | Native Name | Default | Status | Sort Order |
| ---- | ---------- | ----------- | ------- | ------ | ---------- |
| vi   | Vietnamese | Tiếng Việt  | Yes     | Active | 1          |
| en   | English    | English     | No      | Active | 2          |
| ja   | Japanese   | 日本語         | No      | Active | 3          |

---

## 8. Route Design

Route đề xuất:

```php
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::resource('languages', LanguageController::class);
        Route::put('languages/{language}/set-default', [LanguageController::class, 'setDefault'])
            ->name('languages.set-default');
    });
```

Routes chính:

| Method | URL                                 | Name                        | Description |
| ------ | ----------------------------------- | --------------------------- | ----------- |
| GET    | `/admin/languages`                  | admin.languages.index       | List        |
| GET    | `/admin/languages/create`           | admin.languages.create      | Create form |
| POST   | `/admin/languages`                  | admin.languages.store       | Store       |
| GET    | `/admin/languages/{id}/edit`        | admin.languages.edit        | Edit form   |
| PUT    | `/admin/languages/{id}`             | admin.languages.update      | Update      |
| DELETE | `/admin/languages/{id}`             | admin.languages.destroy     | Delete      |
| PUT    | `/admin/languages/{id}/set-default` | admin.languages.set-default | Set default |

---

## 9. Controller Design

## 9.1. LanguageController

File đề xuất:

```txt
app/Http/Controllers/Admin/LanguageController.php
```

Methods:

```php
public function index();

public function create();

public function store(StoreLanguageRequest $request);

public function edit(Language $language);

public function update(UpdateLanguageRequest $request, Language $language);

public function destroy(Language $language);

public function setDefault(Language $language);
```

---

## 10. Request Validation

## 10.1. StoreLanguageRequest

File:

```txt
app/Http/Requests/Admin/StoreLanguageRequest.php
```

Rules:

```php
return [
    'code' => ['required', 'string', 'max:10', 'alpha_dash', 'unique:languages,code'],
    'name' => ['required', 'string', 'max:100'],
    'native_name' => ['nullable', 'string', 'max:100'],
    'is_default' => ['nullable', 'boolean'],
    'status' => ['required', 'boolean'],
    'sort_order' => ['nullable', 'integer', 'min:0'],
];
```

---

## 10.2. UpdateLanguageRequest

File:

```txt
app/Http/Requests/Admin/UpdateLanguageRequest.php
app/Http/Middleware/SetAdminLocale.php
```

Rules:

```php
return [
    'code' => ['required', 'string', 'max:10', 'alpha_dash', 'unique:languages,code,' . $this->route('language')->id],
    'name' => ['required', 'string', 'max:100'],
    'native_name' => ['nullable', 'string', 'max:100'],
    'is_default' => ['nullable', 'boolean'],
    'status' => ['required', 'boolean'],
    'sort_order' => ['nullable', 'integer', 'min:0'],
];
```

---

## 11. Model Design

## 11.1. Language Model

File:

```txt
app/Models/Language.php
```

Fillable:

```php
protected $fillable = [
    'code',
    'name',
    'native_name',
    'is_default',
    'status',
    'sort_order',
];
```

Casts:

```php
protected $casts = [
    'is_default' => 'boolean',
    'status' => 'boolean',
    'sort_order' => 'integer',
];
```

Scopes đề xuất:

```php
public function scopeActive($query)
{
    return $query->where('status', true);
}

public function scopeDefault($query)
{
    return $query->where('is_default', true);
}
```

---

## 12. Service Design

## 12.1. LanguageService

File:

```txt
app/Services/LanguageService.php
```

Responsibilities:

* Lấy tất cả languages.
* Lấy active languages.
* Lấy default language.
* Tìm language theo code.
* Set default language.
* Clear cache.

Methods:

```php
public function all();

public function active();

public function getDefault();

public function findByCode(string $code);

public function setDefault(Language $language): void;

public function clearCache(): void;
```

---

## 13. Seeder Design

## 13.1. LanguageSeeder

File:

```txt
database/seeders/LanguageSeeder.php
```

Seeder dùng `updateOrCreate()` để chạy nhiều lần không bị duplicate.

Default data:

```php
[
    [
        'code' => 'vi',
        'name' => 'Vietnamese',
        'native_name' => 'Tiếng Việt',
        'is_default' => true,
        'status' => true,
        'sort_order' => 1,
    ],
    [
        'code' => 'en',
        'name' => 'English',
        'native_name' => 'English',
        'is_default' => false,
        'status' => true,
        'sort_order' => 2,
    ],
    [
        'code' => 'ja',
        'name' => 'Japanese',
        'native_name' => '日本語',
        'is_default' => false,
        'status' => true,
        'sort_order' => 3,
    ],
]
```

---

## 14. View Design

## 14.1. Expected View Files

```txt
resources/views/admin/languages/index.blade.php
resources/views/admin/languages/create.blade.php
resources/views/admin/languages/edit.blade.php
resources/views/admin/languages/_form.blade.php
```

Tất cả view dùng admin layout:

```blade
@extends('layouts.admin')

@section('title', 'Languages')
```

---

## 14.2. Success Messages

Messages đề xuất:

```txt
Language created successfully.
Language updated successfully.
Language deleted successfully.
Default language updated successfully.
```

---

## 14.3. Error Messages

Messages đề xuất:

```txt
Cannot delete default language.
Cannot disable default language.
Default language must be active.
Language not found.
```

---

## 15. Business Logic

## 15.1. Create Language Flow

```txt
1. Admin mở create form.
2. Nhập thông tin language.
3. Submit.
4. Validate input.
5. Nếu is_default = true:
   - Set tất cả language khác is_default = false.
6. Tạo language mới.
7. Clear cache.
8. Redirect về list với success message.
```

---

## 15.2. Update Language Flow

```txt
1. Admin mở edit form.
2. Submit update.
3. Validate input.
4. Nếu language hiện tại là default:
   - Không cho status = inactive.
5. Nếu is_default = true:
   - Set tất cả language khác is_default = false.
   - Đảm bảo language này active.
6. Update language.
7. Clear cache.
8. Redirect về list với success message.
```

---

## 15.3. Delete Language Flow

```txt
1. Admin click delete.
2. Nếu language là default:
   - Không cho xóa.
3. Nếu không phải default:
   - Xóa language.
4. Clear cache.
5. Redirect về list.
```

---

## 15.4. Set Default Flow

```txt
1. Admin click Set Default.
2. Kiểm tra language status = active.
3. Set tất cả language khác is_default = false.
4. Set language hiện tại is_default = true.
5. Clear cache.
6. Redirect back với success message.
```

---

## 16. Error Handling

| Case                             | Action                |
| -------------------------------- | --------------------- |
| Duplicate code                   | Show validation error |
| Delete default language          | Show error message    |
| Disable default language         | Show error message    |
| Set inactive language as default | Show error message    |
| Customer access admin language   | 403 hoặc redirect     |
| Guest access admin language      | Redirect login        |
| Seeder run multiple times        | Không duplicate data  |

---

## 17. Security

Yêu cầu bảo mật:

* Route phải dùng middleware `auth` và `admin`.
* Validate toàn bộ input.
* Không cho customer truy cập.
* Không cho xóa default language.
* Không cho disable default language.
* Dùng CSRF protection cho form.
* Không lưu arbitrary request keys.
* Không dùng dữ liệu input trực tiếp để render HTML không escape.

---

## 18. Files Expected

Codex có thể tạo hoặc sửa:

```txt
routes/web.php

app/Models/Language.php
app/Services/LanguageService.php
app/Http/Controllers/Admin/LanguageController.php
app/Http/Requests/Admin/StoreLanguageRequest.php
app/Http/Requests/Admin/UpdateLanguageRequest.php

database/migrations/*_create_languages_table.php
database/seeders/LanguageSeeder.php
database/seeders/DatabaseSeeder.php

resources/views/admin/languages/index.blade.php
resources/views/admin/languages/create.blade.php
resources/views/admin/languages/edit.blade.php
resources/views/admin/languages/_form.blade.php

resources/views/admin/partials/sidebar.blade.php

lang/vi/admin.php
lang/en/admin.php

bootstrap/app.php
```

Nếu bảng `languages` đã tồn tại từ Task 02 Database Design, không tạo migration trùng.

---

## 19. Commands

Sau khi implement, chạy:

```bash
php artisan migrate
php artisan db:seed --class=LanguageSeeder
```

Hoặc reset local:

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
http://127.0.0.1:8000/admin/languages
```

---

## 20. Test Cases

| Test Case ID | Scenario                      | Steps                             | Expected Result                         |
| ------------ | ----------------------------- | --------------------------------- | --------------------------------------- |
| TC-001       | Guest access language list    | Chưa login vào `/admin/languages` | Redirect login                          |
| TC-002       | Customer access language list | Customer vào `/admin/languages`   | Bị chặn                                 |
| TC-003       | Admin access language list    | Admin vào `/admin/languages`      | Hiển thị danh sách                      |
| TC-004       | Create language success       | Nhập dữ liệu hợp lệ               | Tạo thành công                          |
| TC-005       | Create duplicate code         | Nhập code đã tồn tại              | Hiển thị lỗi                            |
| TC-006       | Update language success       | Sửa name/native_name              | Update thành công                       |
| TC-007       | Delete non-default language   | Xóa language không default        | Xóa thành công                          |
| TC-008       | Delete default language       | Xóa `vi` default                  | Không cho xóa                           |
| TC-009       | Disable default language      | Set default language inactive     | Không cho disable                       |
| TC-010       | Set default language          | Set `en` default                  | `en` là default, `vi` không còn default |
| TC-011       | Set inactive language default | Inactive language rồi set default | Không cho set                           |
| TC-012       | Seeder repeat                 | Chạy seeder nhiều lần             | Không duplicate                         |

---

## 21. Acceptance Criteria

Task này hoàn thành khi:

* [ ] Có bảng `languages` hoặc xác nhận đã tồn tại.
* [ ] Có model `Language`.
* [ ] Có `LanguageService`.
* [ ] Có `LanguageSeeder`.
* [ ] Có route `/admin/languages`.
* [ ] Có màn hình list languages.
* [ ] Có màn hình create language.
* [ ] Có màn hình edit language.
* [ ] Có chức năng delete language.
* [ ] Có chức năng set default language.
* [ ] Chỉ có một default language.
* [ ] Không xóa được default language.
* [ ] Không disable được default language.
* [ ] Không set inactive language làm default.
* [ ] Sidebar admin link `Languages` trỏ đúng `/admin/languages`.
* [ ] Có validate input.
* [ ] Có clear cache sau create/update/delete/set default.
* [ ] Admin route sử dụng middleware `admin.locale`.
* [ ] Default language `vi` làm các label admin đã hỗ trợ hiển thị tiếng Việt.
* [ ] Default language `en` làm các label admin đã hỗ trợ hiển thị tiếng Anh.
* [ ] Đổi default language có hiệu lực ở request tiếp theo mà không cần đăng xuất.
* [ ] Locale chưa có resource riêng fallback về tiếng Anh.
* [ ] Chạy được `php artisan migrate:fresh --seed`.
* [ ] Không implement Category/Product Translation trong task này.

---

## 22. Prompt for Codex

Sử dụng prompt sau để giao task này cho Codex:

```txt
Bạn hãy đọc các file sau trong project Laravel 12:

- docs/basic-design.md
- docs/database-design.md
- docs/tasks/task-04-admin-layout.md
- docs/tasks/task-05-system-settings.md
- docs/tasks/task-06-language-management.md

Sau đó implement Task 06: Language Management.

Yêu cầu:

1. Kiểm tra bảng languages đã tồn tại chưa.
   - Nếu chưa có, tạo migration theo docs/database-design.md.
   - Nếu đã có, không tạo migration trùng.

2. Tạo hoặc cập nhật:
   - app/Models/Language.php
   - app/Services/LanguageService.php
   - app/Http/Controllers/Admin/LanguageController.php
   - app/Http/Requests/Admin/StoreLanguageRequest.php
   - app/Http/Requests/Admin/UpdateLanguageRequest.php
   - database/seeders/LanguageSeeder.php
   - database/seeders/DatabaseSeeder.php
   - resources/views/admin/languages/index.blade.php
   - resources/views/admin/languages/create.blade.php
   - resources/views/admin/languages/edit.blade.php
   - resources/views/admin/languages/_form.blade.php
   - routes/web.php
   - resources/views/admin/partials/sidebar.blade.php nếu cần cập nhật link menu

3. Tạo admin CRUD:
   - GET /admin/languages
   - GET /admin/languages/create
   - POST /admin/languages
   - GET /admin/languages/{language}/edit
   - PUT /admin/languages/{language}
   - DELETE /admin/languages/{language}
   - PUT /admin/languages/{language}/set-default

4. Business rules:
   - code phải unique.
   - Chỉ có một default language.
   - Không được xóa default language.
   - Không được disable default language.
   - Không được set inactive language làm default.
   - Khi set default language mới, các language khác phải is_default = false.
   - Sau create/update/delete/set default phải clear cache.

5. Seed mặc định:
   - vi / Vietnamese / Tiếng Việt / default / active
   - en / English / English / active
   - ja / Japanese / 日本語 / active

6. Tạo LanguageService có:
   - all()
   - active()
   - getDefault()
   - findByCode()
   - setDefault()
   - clearCache()

7. Không implement:
   - Category Translation
   - Product Translation
   - Banner Translation
   - Public language switcher
   - Language middleware
   - SEO URL đa ngôn ngữ

8. Sau khi code xong, hãy báo cáo:
   - File đã tạo
   - File đã sửa
   - Lệnh cần chạy
   - Cách test /admin/languages
   - Có điểm nào khác với task document không
```

---

## 23. Notes

* Task này phụ thuộc Task 04 Admin Layout.
* Task này có thể làm sau Task 05 System Settings.
* Nếu `languages` đã được seed ở Task 02 hoặc Task 05, cần tránh duplicate seeder.
* Sau task này mới làm `Task 07: Currency Management`.
* Category/Product Translation sẽ dùng bảng `languages` ở các task sau.
* Locale của admin lấy trực tiếp từ `languages.is_default`, không lấy từ session hoặc URL.
* `LanguageService::setDefault()` clear cache; middleware đọc lại default ở request kế tiếp.
* Bộ dịch admin hiện hỗ trợ `vi` và `en`. `ja` fallback về `en` cho đến khi bổ sung `lang/ja/admin.php`.

Sau khi lưu file này, bạn đưa Codex prompt trong mục **22. Prompt for Codex** để nó thực thi Task 06.
