Dưới đây là nội dung cho file:

```txt
docs/tasks/task-03-authentication.md
```

Bạn copy nguyên phần này vào file.

# Task 03: Authentication

## 1. Overview

Task này dùng để xây dựng chức năng xác thực người dùng cho hệ thống e-commerce Laravel 12.

Hệ thống cần hỗ trợ đăng ký, đăng nhập, đăng xuất, quên mật khẩu, reset mật khẩu và phân quyền cơ bản theo role.

Authentication sẽ được dùng cho cả:

* Customer
* Staff
* Admin
* Super Admin

Sau khi hoàn thành task này, hệ thống phải có thể phân biệt người dùng thường và người quản trị để điều hướng vào đúng khu vực.

---

## 2. Objectives

Sau khi hoàn thành task này, hệ thống cần đạt được:

* Customer có thể đăng ký tài khoản.
* User có thể đăng nhập.
* User có thể đăng xuất.
* User có thể dùng chức năng quên mật khẩu nếu email được cấu hình.
* User có thể cập nhật thông tin cá nhân cơ bản nếu starter kit hỗ trợ.
* User mới đăng ký mặc định có role là `customer`.
* Admin có thể đăng nhập bằng tài khoản admin seed sẵn.
* Admin, staff, super admin có thể truy cập `/admin/dashboard`.
* Customer có thể truy cập `/account` hoặc `/dashboard`.
* User không có quyền không được truy cập admin area.
* Có middleware kiểm tra quyền admin.
* Có seeder tạo tài khoản super admin mặc định.

---

## 3. Scope

## 3.1. In Scope

Các phần sẽ làm trong task này:

* Cài đặt Laravel Breeze hoặc authentication starter phù hợp.
* Login.
* Register.
* Logout.
* Forgot password.
* Reset password.
* Profile update cơ bản nếu starter kit có sẵn.
* Update bảng `users` để hỗ trợ role, phone, status, soft delete.
* Tạo middleware kiểm tra admin.
* Tạo route admin dashboard đơn giản.
* Tạo route customer account/dashboard đơn giản.
* Tạo seeder super admin mặc định.
* Redirect user theo role sau khi login.
* Bảo vệ admin route bằng middleware.

---

## 3.2. Out of Scope

Các phần không làm trong task này:

* Admin layout hoàn chỉnh.
* Product management.
* Category management.
* Cart.
* Checkout.
* Order.
* Payment.
* Customer profile nâng cao.
* Role permission nâng cao theo bảng `roles`, `permissions`.
* Social login.
* Two-factor authentication.
* Email template chuyên nghiệp.
* UI e-commerce hoàn chỉnh.

---

## 4. User Roles

Hệ thống sử dụng các role cơ bản sau:

| Role        | Description   | Permission                                     |
| ----------- | ------------- | ---------------------------------------------- |
| super_admin | Chủ hệ thống  | Toàn quyền                                     |
| admin       | Quản trị viên | Truy cập admin area                            |
| staff       | Nhân viên     | Truy cập admin area với quyền giới hạn sau này |
| customer    | Khách hàng    | Mua hàng, quản lý tài khoản cá nhân            |

Trong task này, chỉ cần kiểm tra role cơ bản:

```txt
super_admin, admin, staff -> được vào admin area
customer -> không được vào admin area
```

---

## 5. Functional Requirements

## FR-01: Register

Customer có thể đăng ký tài khoản mới từ public site.

Input:

| Field                 | Required | Description       |
| --------------------- | -------- | ----------------- |
| name                  | Yes      | Tên người dùng    |
| email                 | Yes      | Email đăng nhập   |
| password              | Yes      | Mật khẩu          |
| password_confirmation | Yes      | Xác nhận mật khẩu |

Expected behavior:

* Email phải unique.
* Password phải được hash.
* User mới có role mặc định là `customer`.
* User mới có status mặc định là `1`.
* Sau khi đăng ký thành công, user có thể đăng nhập hoặc được auto login tùy cấu trúc starter kit.

---

## FR-02: Login

User có thể đăng nhập bằng email và password.

Expected behavior:

* Nếu thông tin hợp lệ, user đăng nhập thành công.
* Nếu thông tin sai, hiển thị lỗi.
* Nếu user bị inactive, không cho đăng nhập.
* Sau khi login thành công:

  * `super_admin`, `admin`, `staff` redirect tới `/admin/dashboard`.
  * `customer` redirect tới `/account` hoặc `/dashboard`.

---

## FR-03: Logout

User có thể đăng xuất.

Expected behavior:

* Session được clear.
* User được redirect về trang login hoặc homepage.

---

## FR-04: Forgot Password

User có thể yêu cầu reset password qua email.

Expected behavior:

* User nhập email.
* Nếu email tồn tại, hệ thống gửi link reset password.
* Nếu mail chưa cấu hình ở local, không cần xử lý nâng cao trong task này.

---

## FR-05: Reset Password

User có thể đặt lại mật khẩu mới bằng reset token.

Expected behavior:

* Token hợp lệ thì cho đổi mật khẩu.
* Password mới phải được hash.
* Token không hợp lệ hoặc hết hạn thì hiển thị lỗi.

---

## FR-06: Admin Middleware

Hệ thống cần middleware bảo vệ admin area.

Middleware name đề xuất:

```txt
admin
```

Điều kiện được phép truy cập:

```txt
role in: super_admin, admin, staff
status = 1
```

Nếu không hợp lệ:

* Nếu chưa login: redirect `/login`
* Nếu đã login nhưng không có quyền: trả về 403 hoặc redirect về `/dashboard`

---

## FR-07: Customer Area

Customer sau khi đăng nhập có thể vào trang tài khoản.

URL đề xuất:

```txt
/account
```

hoặc:

```txt
/dashboard
```

Trang này chỉ cần đơn giản trong task này.

Hiển thị:

* Tên user
* Email
* Role
* Link logout

---

## FR-08: Admin Dashboard Placeholder

Admin sau khi đăng nhập có thể vào:

```txt
/admin/dashboard
```

Trang này chỉ là placeholder ở task này.

Hiển thị:

```txt
Admin Dashboard
Welcome, {user_name}
```

Admin layout chi tiết sẽ làm ở task sau.

---

## 6. UI / Screen Design

## 6.1. Screen List

| Screen           | URL                          | Description                    |
| ---------------- | ---------------------------- | ------------------------------ |
| Register         | `/register`                  | Đăng ký customer               |
| Login            | `/login`                     | Đăng nhập                      |
| Forgot Password  | `/forgot-password`           | Quên mật khẩu                  |
| Reset Password   | `/reset-password/{token}`    | Reset mật khẩu                 |
| Customer Account | `/account` hoặc `/dashboard` | Trang tài khoản customer       |
| Admin Dashboard  | `/admin/dashboard`           | Trang dashboard admin đơn giản |

---

## 6.2. Login Screen

Fields:

| Field    | Type     | Required |
| -------- | -------- | -------- |
| email    | email    | Yes      |
| password | password | Yes      |
| remember | checkbox | No       |

Actions:

* Login
* Forgot password
* Register

---

## 6.3. Register Screen

Fields:

| Field                 | Type     | Required |
| --------------------- | -------- | -------- |
| name                  | text     | Yes      |
| email                 | email    | Yes      |
| password              | password | Yes      |
| password_confirmation | password | Yes      |

Actions:

* Register
* Login

---

## 7. Database Design

## 7.1. Table: users

Laravel mặc định đã có bảng `users`. Task này cần đảm bảo bảng `users` có thêm các field sau:

| Column     | Type        | Nullable | Default  | Description            |
| ---------- | ----------- | -------- | -------- | ---------------------- |
| phone      | varchar(30) | Yes      | null     | Số điện thoại          |
| role       | varchar(50) | No       | customer | Role của user          |
| status     | tinyint     | No       | 1        | 1: active, 0: inactive |
| deleted_at | timestamp   | Yes      | null     | Soft delete            |

Bảng `users` sau khi hoàn chỉnh nên có:

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
| remember_token    | varchar(100)    | Yes      | null           | Remember token                      |
| created_at        | timestamp       | Yes      | null           | Created time                        |
| updated_at        | timestamp       | Yes      | null           | Updated time                        |
| deleted_at        | timestamp       | Yes      | null           | Soft delete                         |

Indexes:

```txt
unique: email
index: role
index: status
```

---

## 8. Route Design

## 8.1. Auth Routes

Nếu dùng Laravel Breeze, các route auth sẽ được starter kit tạo sẵn.

Các route chính:

| Method | URL                       | Description           |
| ------ | ------------------------- | --------------------- |
| GET    | `/register`               | Register form         |
| POST   | `/register`               | Register submit       |
| GET    | `/login`                  | Login form            |
| POST   | `/login`                  | Login submit          |
| POST   | `/logout`                 | Logout                |
| GET    | `/forgot-password`        | Forgot password form  |
| POST   | `/forgot-password`        | Send reset link       |
| GET    | `/reset-password/{token}` | Reset password form   |
| POST   | `/reset-password`         | Reset password submit |

---

## 8.2. Customer Routes

```php
Route::middleware(['auth'])->group(function () {
    Route::get('/account', [AccountController::class, 'index'])->name('account.index');
});
```

Nếu dùng `/dashboard` mặc định của Breeze thì có thể giữ lại:

```php
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
```

---

## 8.3. Admin Routes

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

## 9. Middleware Design

## 9.1. AdminMiddleware

File đề xuất:

```txt
app/Http/Middleware/AdminMiddleware.php
```

Logic:

```txt
Nếu user chưa login:
    redirect login

Nếu user.status != 1:
    logout hoặc abort 403

Nếu user.role không nằm trong super_admin, admin, staff:
    abort 403

Ngược lại:
    cho phép truy cập
```

Role được phép:

```php
['super_admin', 'admin', 'staff']
```

---

## 9.2. Middleware Alias

Đăng ký alias:

```txt
admin
```

Tùy cấu trúc Laravel 12 hiện tại, đăng ký middleware theo chuẩn project đang dùng.

---

## 10. Login Redirect Logic

Sau khi login thành công, hệ thống cần redirect theo role.

Logic:

```txt
Nếu role là super_admin, admin, staff:
    redirect /admin/dashboard

Nếu role là customer:
    redirect /account hoặc /dashboard
```

Có thể xử lý trong:

```txt
AuthenticatedSessionController
```

hoặc cấu trúc tương đương của starter kit.

---

## 11. Validation Rules

## 11.1. Register Validation

| Field    | Rule                                                 |
| -------- | ---------------------------------------------------- |
| name     | required, string, max:255                            |
| email    | required, string, email, max:255, unique:users,email |
| password | required, confirmed, min:8                           |

---

## 11.2. Login Validation

| Field    | Rule            |
| -------- | --------------- |
| email    | required, email |
| password | required        |

---

## 11.3. Profile Update Validation

| Field | Rule                      |
| ----- | ------------------------- |
| name  | required, string, max:255 |
| email | required, email, max:255  |
| phone | nullable, string, max:30  |

---

## 12. Seeder Design

## 12.1. Super Admin Seeder

Tạo tài khoản admin mặc định:

```txt
name: Super Admin
email: admin@example.com
password: password
role: super_admin
status: 1
```

Lưu ý:

* Password phải được hash bằng `Hash::make()`.
* Seeder nên dùng `updateOrCreate()` để chạy nhiều lần không bị duplicate.

---

## 12.2. Customer Test Seeder Optional

Có thể tạo thêm customer test:

```txt
name: Test Customer
email: customer@example.com
password: password
role: customer
status: 1
```

Không bắt buộc, nhưng nên có để test redirect customer.

---

## 13. Business Logic

## 13.1. Register Logic

Khi customer đăng ký:

```txt
1. Validate input.
2. Tạo user mới.
3. Set role = customer.
4. Set status = 1.
5. Hash password.
6. Login user hoặc redirect login.
```

---

## 13.2. Login Logic

Khi user login:

```txt
1. Validate email/password.
2. Kiểm tra user tồn tại.
3. Kiểm tra password.
4. Kiểm tra status.
5. Nếu status inactive, không cho login.
6. Login thành công.
7. Redirect theo role.
```

---

## 13.3. Admin Access Logic

Khi truy cập `/admin/*`:

```txt
1. Kiểm tra user đã login.
2. Kiểm tra status active.
3. Kiểm tra role thuộc super_admin, admin, staff.
4. Nếu hợp lệ thì cho truy cập.
5. Nếu không hợp lệ thì chặn.
```

---

## 14. Error Handling

| Case                         | Message / Action          |
| ---------------------------- | ------------------------- |
| Email đã tồn tại             | Hiển thị lỗi validation   |
| Sai email/password           | Hiển thị lỗi login        |
| User inactive                | Không cho login           |
| Customer vào admin           | Trả về 403                |
| Chưa login vào admin         | Redirect login            |
| Reset password token invalid | Hiển thị lỗi token        |
| Seeder chạy lại              | Không tạo duplicate admin |

---

## 15. Security

Các yêu cầu bảo mật:

* Password phải được hash.
* Không log password.
* Không hiển thị lỗi hệ thống ra màn hình production.
* Auth route phải có CSRF protection.
* Admin route phải có middleware.
* Không cho user tự set role khi register.
* Role customer phải được set ở backend.
* Không cho inactive user đăng nhập.
* Dùng validation cho toàn bộ input.
* Không mass assignment các field nguy hiểm nếu chưa khai báo rõ `$fillable`.

---

## 16. Files Expected

Codex có thể tạo hoặc sửa các file sau tùy cấu trúc project:

```txt
composer.json
package.json

routes/web.php
routes/auth.php

app/Models/User.php

app/Http/Controllers/Auth/*
app/Http/Controllers/Admin/AdminDashboardController.php
app/Http/Controllers/AccountController.php

app/Http/Middleware/AdminMiddleware.php

database/migrations/*_add_auth_fields_to_users_table.php
database/seeders/SuperAdminSeeder.php
database/seeders/DatabaseSeeder.php

resources/views/auth/*
resources/views/dashboard.blade.php
resources/views/account/index.blade.php
resources/views/admin/dashboard.blade.php
```

Nếu dùng Laravel Breeze, một số file auth sẽ được tạo tự động.

---

## 17. Commands

## 17.1. Install Breeze

Nếu project chưa có authentication starter kit, có thể dùng:

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
npm run build
php artisan migrate
```

Nếu Codex thấy project đã có starter kit khác, không cài chồng.

---

## 17.2. Run Migration and Seeder

```bash
php artisan migrate
php artisan db:seed
```

Hoặc reset database local:

```bash
php artisan migrate:fresh --seed
```

---

## 17.3. Run Server

```bash
php artisan serve
```

Mở:

```txt
http://127.0.0.1:8000/login
```

---

## 18. Test Cases

| Test Case ID | Scenario                  | Steps                                  | Expected Result                           |
| ------------ | ------------------------- | -------------------------------------- | ----------------------------------------- |
| TC-001       | Register customer success | Vào `/register`, nhập thông tin hợp lệ | User được tạo với role `customer`         |
| TC-002       | Register duplicate email  | Đăng ký email đã tồn tại               | Hiển thị lỗi validation                   |
| TC-003       | Login customer success    | Login bằng customer account            | Redirect tới `/account` hoặc `/dashboard` |
| TC-004       | Login admin success       | Login bằng admin account               | Redirect tới `/admin/dashboard`           |
| TC-005       | Customer access admin     | Customer truy cập `/admin/dashboard`   | Bị chặn 403 hoặc redirect                 |
| TC-006       | Guest access admin        | Chưa login truy cập `/admin/dashboard` | Redirect tới `/login`                     |
| TC-007       | Logout success            | Login rồi logout                       | Session bị clear                          |
| TC-008       | Inactive user login       | User status = 0 login                  | Không cho login                           |
| TC-009       | Forgot password screen    | Vào `/forgot-password`                 | Hiển thị form quên mật khẩu               |
| TC-010       | Seeder admin              | Chạy seeder                            | Có user `admin@example.com`               |

---

## 19. Acceptance Criteria

Task này được xem là hoàn thành khi:

* [ ] Có authentication starter hoạt động.
* [ ] Có register page.
* [ ] Có login page.
* [ ] Có logout.
* [ ] Có forgot password/reset password nếu starter kit hỗ trợ.
* [ ] Bảng `users` có field `phone`, `role`, `status`, `deleted_at`.
* [ ] User đăng ký mới có role mặc định là `customer`.
* [ ] Có middleware admin.
* [ ] `/admin/dashboard` được bảo vệ bởi middleware admin.
* [ ] Admin login redirect tới `/admin/dashboard`.
* [ ] Customer login redirect tới `/account` hoặc `/dashboard`.
* [ ] Customer không vào được `/admin/dashboard`.
* [ ] Có seeder tạo super admin.
* [ ] Chạy được `php artisan migrate:fresh --seed`.
* [ ] Login được bằng `admin@example.com / password`.
* [ ] Không làm lẫn sang product/category/cart/order.

---

## 20. Prompt for Codex

Sử dụng prompt sau để giao task này cho Codex:

```txt
Bạn hãy đọc các file sau trong project Laravel 12:

- docs/basic-design.md
- docs/database-design.md
- docs/tasks/task-03-authentication.md

Sau đó implement Task 03: Authentication.

Yêu cầu:

1. Cài đặt authentication scaffolding phù hợp cho Laravel 12.
   - Ưu tiên Laravel Breeze với Blade + Tailwind.
   - Nếu project đã có starter auth khác, không cài chồng gây xung đột.

2. Authentication cần hỗ trợ:
   - Register
   - Login
   - Logout
   - Forgot password
   - Reset password
   - Profile update cơ bản nếu starter kit có sẵn

3. Cập nhật bảng users nếu chưa có:
   - phone
   - role
   - status
   - deleted_at

4. Khi user đăng ký từ public site:
   - role mặc định là customer
   - status mặc định là active

5. Tạo middleware AdminMiddleware:
   - Chỉ cho phép super_admin, admin, staff truy cập admin area.
   - Customer không được truy cập admin area.
   - User inactive không được truy cập.

6. Tạo route:
   - /admin/dashboard cho admin area
   - /account hoặc /dashboard cho customer area

7. Sau khi login:
   - super_admin, admin, staff redirect tới /admin/dashboard
   - customer redirect tới /account hoặc /dashboard

8. Tạo seeder admin mặc định:
   - name: Super Admin
   - email: admin@example.com
   - password: password
   - role: super_admin
   - status: active

9. Không implement:
   - Product
   - Category
   - Cart
   - Checkout
   - Order
   - Payment
   - Admin layout nâng cao

10. Sau khi code xong, hãy báo cáo:
   - File đã tạo
   - File đã sửa
   - Lệnh cần chạy
   - Cách test login admin
   - Cách test login customer
   - Có điểm nào khác với task document không
```

---

## 21. Notes

* Task này nên hoàn thành trước Admin Layout.
* Sau task này mới làm tiếp `Task 04: Admin Layout`.
* Không nên làm role/permission nâng cao ở task này để tránh phức tạp.
* Có thể dùng field `role` trong bảng `users` trước, sau này nếu cần phân quyền nâng cao thì chuyển sang bảng `roles`, `permissions`.
