# Task 04: Admin Layout

## 1. Overview

Task này dùng để xây dựng giao diện nền tảng cho khu vực quản trị của hệ thống e-commerce Laravel 12.

Admin Layout là phần khung giao diện dùng chung cho toàn bộ màn hình admin sau này, bao gồm:

* Admin sidebar
* Admin header
* Admin dashboard placeholder
* Breadcrumb
* User dropdown
* Logout button
* Responsive layout cơ bản
* Menu điều hướng tới các module chính

Task này chỉ xây dựng layout và cấu trúc giao diện admin, chưa implement CRUD cho Product, Category, Order hoặc các module nghiệp vụ khác.

---

## 2. Objectives

Sau khi hoàn thành task này, hệ thống cần đạt được:

* Có layout admin dùng chung.
* Có sidebar admin.
* Có header admin.
* Có menu điều hướng các module chính.
* Có trang `/admin/dashboard`.
* Có giao diện dashboard đơn giản.
* Có thể tái sử dụng layout cho các màn hình admin sau này.
* Admin route được bảo vệ bằng middleware `auth` và `admin`.
* Customer không truy cập được admin area.
* Admin có thể logout từ giao diện admin.

---

## 3. Scope

## 3.1. In Scope

Các phần sẽ làm trong task này:

* Tạo admin layout Blade.
* Tạo sidebar menu.
* Tạo header.
* Tạo breadcrumb đơn giản.
* Tạo dashboard placeholder.
* Tạo menu admin chính.
* Tạo active menu state theo route hiện tại.
* Tạo responsive layout cơ bản.
* Tạo admin CSS/Blade structure phù hợp với Tailwind.
* Tạo route `/admin/dashboard`.
* Tạo `AdminDashboardController` nếu chưa có.
* Đảm bảo admin layout dùng được với Laravel Breeze nếu project đang dùng Breeze.

---

## 3.2. Out of Scope

Các phần không làm trong task này:

* Category CRUD.
* Product CRUD.
* Order management.
* Customer management.
* Coupon management.
* Banner management.
* Report logic.
* Chart doanh thu thật.
* Permission nâng cao.
* Notification realtime.
* Admin theme phức tạp.
* Dark mode.
* Multi-language UI cho admin.
* API admin.

---

## 4. User Roles

Admin Layout chỉ dành cho các role:

| Role        | Permission                           |
| ----------- | ------------------------------------ |
| super_admin | Truy cập toàn bộ admin area          |
| admin       | Truy cập admin area                  |
| staff       | Truy cập admin area giới hạn sau này |
| customer    | Không được truy cập admin area       |

Điều kiện truy cập admin:

```txt
User phải login
User status = active
User role nằm trong: super_admin, admin, staff
```

---

## 5. Functional Requirements

## FR-01: Admin Dashboard Page

Admin có thể truy cập dashboard tại:

```txt
/admin/dashboard
```

Expected behavior:

* Nếu chưa login, redirect về `/login`.
* Nếu login bằng customer, không cho truy cập.
* Nếu login bằng admin/staff/super_admin, hiển thị admin dashboard.
* Dashboard sử dụng admin layout chung.

---

## FR-02: Admin Sidebar

Sidebar cần hiển thị menu chính của hệ thống.

Menu đề xuất:

```txt
Dashboard
System Settings
Languages
Currencies
Tax Classes
Tax Rates
Categories
Products
Inventory
Orders
Customers
Coupons
Banners
Reports
```

Mỗi menu cần có:

* Label
* URL
* Active state
* Icon riêng phù hợp với chức năng

Trong task này, các URL chưa có chức năng vẫn có thể để dạng placeholder hoặc `#`.

### Sidebar Icon Requirements

Mỗi menu trong admin sidebar phải có icon với các yêu cầu sau:

* Icon hiển thị bên trái label.
* Icon có kích thước đồng nhất `20x20`.
* Icon đổi màu theo trạng thái active và hover bằng cách kế thừa màu chữ hiện tại.
* Không sử dụng icon dạng ảnh PNG/JPG.
* Ưu tiên inline SVG thông qua Blade component hoặc partial dùng chung.
* Không cài package icon hoặc admin template nặng nếu không cần thiết.

Menu và icon đề xuất:

| Menu            | Icon                  |
| --------------- | --------------------- |
| Dashboard       | Home / chart          |
| System Settings | Cog                    |
| Languages       | Globe                  |
| Currencies      | Banknote / currency    |
| Tax Classes     | Receipt                |
| Tax Rates       | Percent                |
| Categories      | Squares / folder       |
| Products        | Cube / box             |
| Inventory       | Archive / warehouse    |
| Orders          | Shopping cart / bag    |
| Customers       | Users                  |
| Coupons         | Ticket                 |
| Banners         | Image                  |
| Reports         | Chart bar             |

---

## FR-03: Active Menu

Menu hiện tại cần được highlight.

Ví dụ:

Nếu đang ở:

```txt
/admin/dashboard
```

thì menu `Dashboard` được active.

Nếu đang ở:

```txt
/admin/products
```

thì menu `Products` được active.

---

## FR-04: Admin Header

Header cần có:

* Tên hệ thống
* Nút mở/đóng sidebar trên mobile nếu có
* Tên user đang login
* Role user
* Dropdown hoặc button logout

Thông tin hiển thị:

```txt
Super Admin
admin@example.com
```

---

## FR-05: Logout

Admin có thể logout từ admin header.

Expected behavior:

* Submit POST `/logout`
* Session bị clear
* Redirect về login hoặc homepage

---

## FR-06: Breadcrumb

Admin layout cần có breadcrumb đơn giản.

Ví dụ dashboard:

```txt
Home / Dashboard
```

Ví dụ sau này product:

```txt
Home / Products
```

Trong task này có thể implement breadcrumb đơn giản bằng section/yield.

---

## FR-07: Page Title

Mỗi admin page có thể truyền title vào layout.

Ví dụ:

```blade
@section('title', 'Dashboard')
```

Admin layout hiển thị title này ở:

* HTML title
* Page header

---

## FR-08: Responsive Layout

Admin layout cần hiển thị ổn ở desktop.

Mobile chỉ cần cơ bản:

* Sidebar có thể ẩn/hiện hoặc nằm trên cùng.
* Không yêu cầu animation phức tạp.

---

## 6. UI / Screen Design

## 6.1. Screen List

| Screen          | URL                | Description                       |
| --------------- | ------------------ | --------------------------------- |
| Admin Dashboard | `/admin/dashboard` | Trang dashboard admin             |
| Admin Layout    | N/A                | Layout dùng chung cho admin pages |

---

## 6.2. Admin Layout Structure

Cấu trúc giao diện:

```txt
+--------------------------------------------------+
| Header                                           |
+----------------------+---------------------------+
| Sidebar              | Page Content              |
|                      |                           |
| - Dashboard          | Page Title                |
| - Settings           | Breadcrumb                |
| - Languages          |                           |
| - Currencies         | Main content              |
| - Tax                |                           |
| - Categories         |                           |
| - Products           |                           |
| - Orders             |                           |
+----------------------+---------------------------+
```

---

## 6.3. Sidebar Menu

Menu chính:

| Menu            | URL                  | Description         |
| --------------- | -------------------- | ------------------- |
| Dashboard       | `/admin/dashboard`   | Tổng quan           |
| System Settings | `/admin/settings`    | Thiết lập hệ thống  |
| Languages       | `/admin/languages`   | Quản lý ngôn ngữ    |
| Currencies      | `/admin/currencies`  | Quản lý tiền tệ     |
| Tax Classes     | `/admin/tax-classes` | Quản lý nhóm thuế   |
| Tax Rates       | `/admin/tax-rates`   | Quản lý mức thuế    |
| Categories      | `/admin/categories`  | Quản lý danh mục    |
| Products        | `/admin/products`    | Quản lý sản phẩm    |
| Inventory       | `/admin/inventory`   | Quản lý tồn kho     |
| Orders          | `/admin/orders`      | Quản lý đơn hàng    |
| Customers       | `/admin/customers`   | Quản lý khách hàng  |
| Coupons         | `/admin/coupons`     | Quản lý mã giảm giá |
| Banners         | `/admin/banners`     | Quản lý banner      |
| Reports         | `/admin/reports`     | Báo cáo             |

Lưu ý:

* Các route chưa implement có thể để `#` hoặc tạo route placeholder.
* Không implement CRUD trong task này.

---

## 6.4. Dashboard Placeholder

Dashboard trong task này chỉ cần hiển thị dữ liệu giả hoặc card rỗng.

Các card đề xuất:

```txt
Total Orders
Total Revenue
Total Products
Low Stock Products
```

Có thể hiển thị giá trị tạm:

```txt
0
```

Không cần query dữ liệu thật trong task này.

---

## 7. Route Design

## 7.1. Admin Route Group

Route đề xuất trong `routes/web.php`:

```php
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');
    });
```

---

## 7.2. Future Admin Routes

Các route sau này sẽ được thêm ở các task khác:

```php
Route::resource('categories', AdminCategoryController::class);
Route::resource('products', AdminProductController::class);
Route::resource('orders', AdminOrderController::class);
```

Không cần implement trong task này.

---

## 8. Controller Design

## 8.1. AdminDashboardController

File đề xuất:

```txt
app/Http/Controllers/Admin/AdminDashboardController.php
```

Method:

```php
public function index()
{
    return view('admin.dashboard');
}
```

Trong task này chưa cần lấy dữ liệu thật từ database.

---

## 9. Blade File Design

## 9.1. Expected Blade Files

Các file có thể tạo:

```txt
resources/views/layouts/admin.blade.php
resources/views/admin/dashboard.blade.php
resources/views/admin/partials/sidebar.blade.php
resources/views/admin/partials/header.blade.php
resources/views/admin/partials/breadcrumb.blade.php
```

Có thể gom partial vào layout nếu muốn đơn giản, nhưng nên tách partial để dễ bảo trì.

---

## 9.2. Admin Layout

File:

```txt
resources/views/layouts/admin.blade.php
```

Layout cần có:

```blade
<title>@yield('title', 'Admin') - {{ config('app.name') }}</title>

@include('admin.partials.sidebar')
@include('admin.partials.header')

<main>
    @yield('content')
</main>
```

---

## 9.3. Dashboard View

File:

```txt
resources/views/admin/dashboard.blade.php
```

Nội dung cần dùng layout:

```blade
@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1>Dashboard</h1>
@endsection
```

---

## 10. Styling

Nếu project đang dùng Laravel Breeze Blade + Tailwind, admin layout nên dùng Tailwind CSS.

Yêu cầu styling:

* Giao diện sạch, chuyên nghiệp.
* Sidebar nền tối hoặc sáng đều được.
* Header rõ ràng.
* Content có padding.
* Card dashboard bo góc.
* Button logout rõ ràng.
* Active menu dễ nhìn.
* Sidebar icon có kích thước và stroke đồng nhất.
* Sidebar icon thay đổi màu cùng menu khi active hoặc hover.

Không cần cài admin template bên ngoài trong task này.

---

## 11. Business Logic

## 11.1. Access Admin Dashboard

Flow:

```txt
1. User truy cập /admin/dashboard
2. Middleware auth kiểm tra login
3. Middleware admin kiểm tra role và status
4. Nếu hợp lệ, hiển thị admin dashboard
5. Nếu không hợp lệ, redirect hoặc abort 403
```

---

## 11.2. Logout Flow

Flow:

```txt
1. Admin click logout
2. Submit POST /logout
3. Laravel xử lý logout
4. Redirect ra login hoặc homepage
```

---

## 12. Validation Rules

Task này không có form nhập dữ liệu nghiệp vụ nên không cần validation riêng.

Chỉ cần đảm bảo:

* Admin route có middleware.
* Logout dùng POST request.
* CSRF token có trong form logout.

---

## 13. Security

Các yêu cầu bảo mật:

* Admin route phải dùng middleware `auth`.
* Admin route phải dùng middleware `admin`.
* Không cho customer truy cập `/admin/*`.
* Logout phải dùng POST, không dùng GET.
* Không hiển thị thông tin nhạy cảm trong admin header.
* Không hard-code password hoặc secret key trong view.
* Không implement route admin không bảo vệ middleware.

---

## 14. Files Expected

Codex có thể tạo hoặc sửa các file sau:

```txt
routes/web.php

app/Http/Controllers/Admin/AdminDashboardController.php

resources/views/layouts/admin.blade.php
resources/views/admin/dashboard.blade.php
resources/views/admin/partials/sidebar.blade.php
resources/views/admin/partials/header.blade.php
resources/views/admin/partials/breadcrumb.blade.php
```

Có thể sửa thêm:

```txt
resources/css/app.css
resources/js/app.js
```

nếu cần cho layout hoặc sidebar mobile.

---

## 15. Commands

Sau khi implement, chạy:

```bash
php artisan route:list
```

Kiểm tra có route:

```txt
admin.dashboard
GET /admin/dashboard
```

Nếu có thay đổi frontend asset:

```bash
npm install
npm run build
```

Chạy server:

```bash
php artisan serve
```

Mở:

```txt
http://127.0.0.1:8000/admin/dashboard
```

---

## 16. Test Cases

| Test Case ID | Scenario                     | Steps                                  | Expected Result           |
| ------------ | ---------------------------- | -------------------------------------- | ------------------------- |
| TC-001       | Guest access admin dashboard | Chưa login, vào `/admin/dashboard`     | Redirect tới `/login`     |
| TC-002       | Admin access dashboard       | Login admin, vào `/admin/dashboard`    | Hiển thị admin dashboard  |
| TC-003       | Customer access dashboard    | Login customer, vào `/admin/dashboard` | Bị chặn 403 hoặc redirect |
| TC-004       | Sidebar displayed            | Login admin, vào dashboard             | Sidebar hiển thị menu     |
| TC-005       | Header displayed             | Login admin, vào dashboard             | Header hiển thị user info |
| TC-006       | Active menu                  | Vào `/admin/dashboard`                 | Menu Dashboard active     |
| TC-007       | Logout                       | Click logout                           | User logout thành công    |
| TC-008       | Page title                   | Vào dashboard                          | Title hiển thị Dashboard  |

---

## 17. Acceptance Criteria

Task này được xem là hoàn thành khi:

* [ ] Có admin layout dùng chung.
* [ ] Có sidebar admin.
* [ ] Có header admin.
* [ ] Có breadcrumb hoặc page header cơ bản.
* [ ] Có dashboard page tại `/admin/dashboard`.
* [ ] Dashboard dùng layout admin.
* [ ] Admin route được bảo vệ bởi `auth` và `admin`.
* [ ] Customer không vào được admin dashboard.
* [ ] Admin có thể logout từ header.
* [ ] Sidebar có menu các module chính.
* [ ] Mỗi sidebar menu có inline SVG icon phù hợp ở bên trái label.
* [ ] Icon sidebar đồng nhất kích thước `20x20` và hỗ trợ màu active/hover.
* [ ] Active menu hoạt động cơ bản.
* [ ] Giao diện desktop nhìn gọn gàng.
* [ ] Không implement CRUD product/category/order trong task này.
* [ ] Chạy được `php artisan route:list`.
* [ ] Không có lỗi Blade hoặc route.

---

## 18. Prompt for Codex

Sử dụng prompt sau để giao task này cho Codex:

```txt
Bạn hãy đọc các file sau trong project Laravel 12:

- docs/basic-design.md
- docs/database-design.md
- docs/tasks/task-03-authentication.md
- docs/tasks/task-04-admin-layout.md

Sau đó implement Task 04: Admin Layout.

Yêu cầu:

1. Tạo admin layout dùng chung bằng Blade + Tailwind.
2. Tạo sidebar admin với các menu chính:
   - Dashboard
   - System Settings
   - Languages
   - Currencies
   - Tax Classes
   - Tax Rates
   - Categories
   - Products
   - Inventory
   - Orders
   - Customers
   - Coupons
   - Banners
   - Reports

3. Tạo header admin hiển thị:
   - Tên hệ thống
   - Tên user đang login
   - Email hoặc role user
   - Logout button

4. Tạo dashboard placeholder tại:
   - /admin/dashboard

5. Dashboard chỉ cần hiển thị giao diện tổng quan đơn giản, chưa cần query dữ liệu thật.

6. Admin route phải được bảo vệ bởi middleware:
   - auth
   - admin

7. Tạo hoặc cập nhật:
   - routes/web.php
   - app/Http/Controllers/Admin/AdminDashboardController.php
   - resources/views/layouts/admin.blade.php
   - resources/views/admin/dashboard.blade.php
   - resources/views/admin/partials/sidebar.blade.php
   - resources/views/admin/partials/header.blade.php
   - resources/views/admin/partials/breadcrumb.blade.php

8. Không implement:
   - Product CRUD
   - Category CRUD
   - Order CRUD
   - System Settings CRUD
   - Language CRUD
   - Currency CRUD
   - Tax CRUD
   - Report logic

9. Sau khi code xong, hãy báo cáo:
   - File đã tạo
   - File đã sửa
   - Lệnh cần chạy
   - Cách test admin dashboard
   - Có điểm nào khác với task document không
```

---

## 19. Notes

* Task này phụ thuộc vào Task 03 Authentication.
* Nếu chưa có middleware `admin`, phải hoàn thành Task 03 trước.
* Không nên cài admin template nặng ở task này.
* Nên giữ layout đơn giản nhưng chuyên nghiệp.
* Các route menu chưa có chức năng có thể để `#` để tránh lỗi route.
* Sau task này mới làm `Task 05: System Settings`.
