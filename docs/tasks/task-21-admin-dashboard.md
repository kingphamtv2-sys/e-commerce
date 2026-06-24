# Task 21: Admin Dashboard

## 1. Overview

Task này dùng để xây dựng màn hình Dashboard trong Admin.

Admin Dashboard là màn hình tổng quan giúp admin nhanh chóng nắm được tình trạng hệ thống e-commerce.

Dashboard cần hiển thị các thông tin chính:

* Tổng quan đơn hàng.
* Doanh thu cơ bản.
* Số lượng sản phẩm.
* Số lượng khách hàng.
* Đơn hàng mới gần đây.
* Trạng thái thanh toán.
* Tồn kho thấp.
* Sản phẩm bán chạy.
* Sản phẩm mới.
* Cảnh báo cần xử lý.

Task này không thay thế Report. Các báo cáo chi tiết sẽ xử lý ở Task 23.

Frontend admin sử dụng:

* Laravel Blade
* Tailwind CSS
* Alpine.js nếu cần
* Fetch API nếu cần
* Không dùng Vue.js.

---

## 2. Objectives

Sau khi hoàn thành Task 21, hệ thống cần có:

* Admin dashboard page.
* Dashboard hiển thị KPI cards.
* Dashboard hiển thị tổng số orders.
* Dashboard hiển thị doanh thu cơ bản.
* Dashboard hiển thị số pending orders.
* Dashboard hiển thị số unpaid COD orders.
* Dashboard hiển thị số products.
* Dashboard hiển thị số low stock products/variants.
* Dashboard hiển thị recent orders.
* Dashboard hiển thị top selling products cơ bản.
* Dashboard hiển thị order status summary.
* Dashboard hiển thị payment status summary.
* Dashboard hiển thị alerts cần xử lý.
* Dashboard có filter thời gian cơ bản.
* Dashboard UI hiện đại, rõ ràng, responsive.
* Admin có thể click từ dashboard sang trang quản lý tương ứng.
* Không implement report nâng cao trong Task 21.
* Không dùng Vue.js.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong Task 21:

* Admin dashboard route.
* Dashboard controller.
* Dashboard service nếu cần.
* Dashboard Blade view.
* KPI cards.
* Recent orders widget.
* Low stock widget.
* Order status summary.
* Payment status summary.
* Basic sales chart nếu phù hợp.
* Time range filter cơ bản.
* Quick action links.
* Empty state khi chưa có dữ liệu.
* Responsive dashboard layout.

### 3.2. Out of Scope

Không làm trong Task 21:

* Không implement report chi tiết.
* Không export Excel/PDF.
* Không advanced analytics.
* Không cohort/customer analysis.
* Không profit report nâng cao.
* Không inventory report chi tiết.
* Không coupon report chi tiết.
* Không real-time websocket dashboard.
* Không notification center nâng cao.
* Không dùng Vue.js.

---

## 4. Dependencies

Task này phụ thuộc các task trước:

| Task    | Dependency             |
| ------- | ---------------------- |
| Task 04 | Admin Layout           |
| Task 10 | Product Management     |
| Task 12 | Inventory Management   |
| Task 19 | Order Creation         |
| Task 20 | Admin Order Management |

Task sau sẽ mở rộng dashboard/report:

| Task    | Purpose        |
| ------- | -------------- |
| Task 23 | Report         |
| Task 24 | Online Payment |

---

## 5. User Roles

| Role     | Permission                        |
| -------- | --------------------------------- |
| Admin    | Xem dashboard đầy đủ              |
| Staff    | Xem dashboard nếu được phân quyền |
| Customer | Không truy cập admin dashboard    |
| Guest    | Không truy cập admin dashboard    |

Business rules:

* Dashboard routes phải bảo vệ bằng admin/auth middleware.
* Nếu chưa có permission chi tiết, chỉ admin/staff được vào.
* Customer/guest bị chặn.

---

## 6. Dashboard Route

Route đề xuất:

| Method | URL              | Name            | Description                   |
| ------ | ---------------- | --------------- | ----------------------------- |
| GET    | /admin           | admin.dashboard | Admin dashboard               |
| GET    | /admin/dashboard | admin.dashboard | Admin dashboard alias nếu cần |

Business rules:

* `/admin` có thể redirect hoặc render dashboard.
* Dashboard phải dùng Admin Layout từ Task 04.
* Route phải nằm trong admin middleware group.

---

## 7. Dashboard Time Range Filter

Dashboard nên có filter thời gian cơ bản.

Filter đề xuất:

| Filter       | Description      |
| ------------ | ---------------- |
| Today        | Dữ liệu hôm nay  |
| Last 7 Days  | 7 ngày gần nhất  |
| Last 30 Days | 30 ngày gần nhất |
| This Month   | Tháng hiện tại   |
| Custom Range | Optional         |

MVP nên hỗ trợ:

* Today.
* Last 7 Days.
* Last 30 Days.
* This Month.

Business rules:

* Mặc định hiển thị `Last 7 Days` hoặc `Today`.
* KPI revenue/order nên tính theo filter.
* Một số KPI tổng hệ thống như total products có thể không phụ thuộc date range.
* Filter giữ query string khi reload page.

---

## 8. KPI Cards

Dashboard cần có các KPI cards rõ ràng.

### 8.1. Required KPI Cards

| KPI               | Description                   |
| ----------------- | ----------------------------- |
| Total Revenue     | Doanh thu theo date range     |
| Total Orders      | Tổng số đơn theo date range   |
| Pending Orders    | Đơn đang chờ xử lý            |
| Unpaid COD Orders | Đơn COD chưa thanh toán       |
| Products          | Tổng số sản phẩm active       |
| Low Stock Items   | Sản phẩm/variant sắp hết hàng |

### 8.2. Optional KPI Cards

| KPI                    | Description                          |
| ---------------------- | ------------------------------------ |
| Completed Orders       | Đơn hoàn tất                         |
| Cancelled Orders       | Đơn bị hủy                           |
| New Customers          | Customer mới                         |
| Average Order Value    | Giá trị đơn trung bình               |
| Conversion Placeholder | Không implement nếu chưa có tracking |

### 8.3. KPI Card UX

Mỗi KPI card cần có:

* Label rõ ràng.
* Number nổi bật.
* Icon phù hợp nếu có.
* Short description.
* Link tới trang quản lý liên quan nếu có.
* Empty/zero state đẹp.

Ví dụ:

| KPI               | Link                                                   |
| ----------------- | ------------------------------------------------------ |
| Pending Orders    | /admin/orders?order_status=pending                     |
| Unpaid COD Orders | /admin/orders?payment_status=unpaid&payment_method=cod |
| Low Stock Items   | /admin/inventory?stock=low                             |
| Products          | /admin/products                                        |

---

## 9. Revenue Calculation

Dashboard hiển thị doanh thu cơ bản.

### 9.1. Revenue Source

Revenue lấy từ `orders`.

Business rules:

* Chỉ tính order chưa cancelled.
* Có thể tính theo `grand_total_amount`.
* Nếu có nhiều currency, hiển thị theo currency của hệ thống hoặc base currency.
* Nếu order có currency snapshot khác nhau, cần quy đổi về base currency nếu có base amount.
* Nếu chưa có base amount rõ ràng, MVP có thể hiển thị theo default currency và note rõ.

### 9.2. Revenue Status Rules

MVP recommendation:

| Case                       | Revenue Count                                  |
| -------------------------- | ---------------------------------------------- |
| completed order            | Count                                          |
| confirmed/processing order | Optional count                                 |
| pending COD unpaid         | Có thể count trong gross sales nhưng chưa paid |
| cancelled order            | Không count                                    |

Để đơn giản trong MVP:

* Dashboard hiển thị `Gross Revenue` từ orders không cancelled.
* Có thêm `Paid Revenue` nếu payment_status = paid.

### 9.3. Revenue Display

Dashboard nên hiển thị:

| Metric            | Description                     |
| ----------------- | ------------------------------- |
| Gross Revenue     | Tổng đơn không cancelled        |
| Paid Revenue      | Tổng đơn đã paid nếu có         |
| COD Unpaid Amount | Tổng COD chưa thanh toán nếu có |

---

## 10. Orders Summary

Dashboard cần hiển thị tình trạng đơn hàng.

### 10.1. Order Status Summary

Hiển thị số đơn theo status:

| Status     | Description |
| ---------- | ----------- |
| pending    | Đơn mới     |
| confirmed  | Đã xác nhận |
| processing | Đang xử lý  |
| completed  | Hoàn tất    |
| cancelled  | Đã hủy      |

UI có thể là:

* Small cards.
* Horizontal list.
* Simple chart.
* Badge list.

### 10.2. Payment Status Summary

Hiển thị số đơn theo payment status:

| Status   | Description      |
| -------- | ---------------- |
| unpaid   | Chưa thanh toán  |
| pending  | Chờ thanh toán   |
| paid     | Đã thanh toán    |
| failed   | Thất bại         |
| refunded | Hoàn tiền nếu có |

Đặc biệt cần nổi bật:

* COD unpaid orders.
* COD paid orders.

---

## 11. Recent Orders Widget

Dashboard cần hiển thị đơn hàng mới gần đây.

### 11.1. Columns

Recent orders nên hiển thị:

| Column         | Description   |
| -------------- | ------------- |
| Order Number   | Mã đơn        |
| Customer       | Tên khách     |
| Grand Total    | Tổng tiền     |
| Payment Status | Badge         |
| Order Status   | Badge         |
| Ordered At     | Thời gian đặt |
| Action         | View          |

### 11.2. Behavior

* Hiển thị 5 đến 10 đơn gần nhất.
* Click order number hoặc View để vào order detail.
* Nếu chưa có order, hiển thị empty state.
* Không load quá nhiều order trên dashboard.

---

## 12. Low Stock Widget

Dashboard cần hiển thị sản phẩm hoặc variant sắp hết hàng.

### 12.1. Data Source

Dữ liệu lấy từ Inventory Management Task 12.

Low stock có thể dựa trên:

* `low_stock_threshold`.
* `available_quantity`.
* `stock_status`.
* Product inventory hoặc variant inventory.

### 12.2. Display

Hiển thị:

| Field              | Description         |
| ------------------ | ------------------- |
| Product Name       | Tên sản phẩm        |
| Variant            | Variant nếu có      |
| SKU                | SKU                 |
| Available Quantity | Số lượng còn        |
| Threshold          | Ngưỡng cảnh báo     |
| Action             | View/Edit inventory |

### 12.3. Behavior

* Hiển thị tối đa 5 đến 10 item.
* Item có available quantity thấp nhất hiển thị trước.
* Nếu không có low stock, hiển thị state tốt.

Message:

`No low stock items.`

---

## 13. Top Selling Products Widget

Dashboard có thể hiển thị sản phẩm bán chạy cơ bản.

### 13.1. Data Source

Dữ liệu lấy từ `order_items`.

Business rules:

* Chỉ tính order không cancelled.
* Group theo product_id hoặc product_name snapshot.
* Tổng quantity bán ra trong date range.
* Nếu product bị xóa, vẫn hiển thị theo snapshot.

### 13.2. Display

| Field         | Description               |
| ------------- | ------------------------- |
| Product Name  | Product snapshot          |
| SKU           | SKU snapshot              |
| Quantity Sold | Tổng quantity             |
| Revenue       | Tổng amount               |
| Action        | Link product/order nếu có |

MVP có thể hiển thị top 5.

---

## 14. New Products Widget

Optional widget.

Hiển thị sản phẩm mới tạo gần đây:

| Field        | Description     |
| ------------ | --------------- |
| Product Name | Tên sản phẩm    |
| SKU          | SKU             |
| Status       | Active/inactive |
| Created At   | Ngày tạo        |
| Action       | Edit            |

Mục tiêu:

* Admin nhanh chóng truy cập sản phẩm mới.
* Không thay thế Product List.

---

## 15. Alerts / Action Required Widget

Dashboard nên có khu vực cảnh báo admin cần xử lý.

Alerts đề xuất:

| Alert              | Condition                  |
| ------------------ | -------------------------- |
| Pending Orders     | Có đơn pending             |
| Unpaid COD Orders  | Có COD chưa thanh toán     |
| Low Stock Items    | Có item thấp hơn threshold |
| Out Of Stock Items | Có item hết hàng           |
| Inactive Products  | Optional                   |
| Expired Coupons    | Optional                   |

Mỗi alert nên có:

* Short message.
* Count.
* Link xử lý.

Ví dụ:

`8 orders are waiting for confirmation.`

---

## 16. Basic Chart

Dashboard có thể có biểu đồ cơ bản.

### 16.1. Sales Chart

Biểu đồ doanh thu theo ngày trong date range.

Fields:

| Data    | Description |
| ------- | ----------- |
| Date    | Ngày        |
| Revenue | Doanh thu   |
| Orders  | Số đơn      |

### 16.2. Chart Rules

* MVP có thể dùng simple HTML/CSS chart hoặc lightweight JavaScript nếu đã có.
* Không cần chart library phức tạp.
* Nếu chưa muốn dùng chart, có thể hiển thị table/list.
* Không dùng Vue.js.
* Không cần realtime chart.

---

## 17. Dashboard Layout

### 17.1. Desktop Layout

Layout đề xuất:

1. Header: title + date range filter.
2. KPI cards grid.
3. Sales chart.
4. Recent orders + low stock side by side.
5. Top selling products.
6. Alerts/action required.

### 17.2. Mobile Layout

Mobile cần:

* Cards xếp dọc.
* Tables có thể scroll ngang hoặc chuyển thành card.
* Date filter dễ dùng.
* Không vỡ layout.
* Không làm admin phải zoom.

---

## 18. Empty State

Dashboard phải xử lý khi chưa có dữ liệu.

Các trường hợp:

| Case            | Display                  |
| --------------- | ------------------------ |
| No orders       | Show empty recent orders |
| No revenue      | Show 0 amount            |
| No low stock    | Show positive message    |
| No top products | Show empty top products  |
| No products     | Show link create product |

Không để dashboard lỗi hoặc trắng.

---

## 19. Currency Display

Dashboard phải format tiền đúng.

Business rules:

* Sử dụng default/base currency cho dashboard admin.
* Nếu order có nhiều currency, cần quy đổi nếu có dữ liệu base amount.
* Nếu chưa hỗ trợ multi-currency report, hiển thị theo order currency hoặc default currency rõ ràng.
* Format số tiền bằng helper/service chung nếu đã có.
* Không lấy currency hiện tại của customer để format admin dashboard.

---

## 20. Permissions and Security

Yêu cầu bảo mật:

* Dashboard route bảo vệ bằng admin middleware.
* Customer/guest không truy cập được.
* Không expose dữ liệu nhạy cảm không cần thiết.
* Không hiển thị lỗi kỹ thuật.
* Staff chỉ xem dữ liệu nếu có quyền.
* Nếu có role permission sau này, dashboard widgets có thể ẩn theo quyền.

---

## 21. Performance Requirements

Dashboard không được query quá nặng.

Yêu cầu:

* Query KPI tối ưu.
* Recent orders giới hạn số lượng.
* Low stock giới hạn số lượng.
* Top selling giới hạn số lượng.
* Không load toàn bộ order/items.
* Dùng aggregation query hợp lý.
* Không query lặp trong Blade.
* Có thể dùng DashboardService để gom logic.
* Report nâng cao để Task 23, không làm quá phức tạp ở Task 21.

---

## 22. Admin UI / UX Requirements

Dashboard UI cần:

* Gọn gàng.
* Hiện đại.
* Dễ scan.
* Status badge rõ ràng.
* KPI cards đẹp.
* Spacing nhất quán.
* Link hành động rõ.
* Không quá nhiều thông tin trên một màn hình.
* Mobile responsive.
* Đồng bộ với Admin Layout Task 04.

---

## 23. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type           | Description                                       |
| -------------- | ------------------------------------------------- |
| Controller     | AdminDashboardController                          |
| Service        | DashboardService                                  |
| Routes         | Admin dashboard routes                            |
| Blade View     | Admin dashboard page                              |
| Blade Partials | KPI card, recent orders, low stock, chart, alerts |
| View Models    | Optional dashboard data object                    |
| Tests          | Admin dashboard feature tests                     |

Không cần migration nếu các bảng order/product/inventory đã có.

---

## 24. Route Design

Routes đề xuất:

| Method | URL              | Name            | Purpose         |
| ------ | ---------------- | --------------- | --------------- |
| GET    | /admin           | admin.dashboard | Dashboard       |
| GET    | /admin/dashboard | admin.dashboard | Dashboard alias |

Nếu cần AJAX refresh widget:

| Method | URL                      | Name                    | Purpose                         |
| ------ | ------------------------ | ----------------------- | ------------------------------- |
| GET    | /admin/dashboard/summary | admin.dashboard.summary | Dashboard summary JSON optional |

MVP có thể render dashboard bằng Blade thông thường, không cần AJAX.

---

## 25. Data Requirements

Dashboard cần lấy dữ liệu:

| Data           | Source                                 |
| -------------- | -------------------------------------- |
| Total orders   | orders                                 |
| Revenue        | orders                                 |
| Pending orders | orders                                 |
| Payment status | orders/order_payments                  |
| Products count | products                               |
| Low stock      | inventories hoặc product/variant stock |
| Recent orders  | orders                                 |
| Top products   | order_items                            |
| Alerts         | orders/inventory/products              |

Business rules:

* Dữ liệu phải dựa trên database hiện tại.
* Không hard-code số liệu.
* Nếu bảng chưa có dữ liệu, hiển thị 0/empty state.

---

## 26. Error Handling

| Scenario             | Expected Result                 |
| -------------------- | ------------------------------- |
| No orders            | Dashboard vẫn load              |
| No products          | Dashboard vẫn load              |
| No inventory data    | Low stock widget hiển thị empty |
| Invalid date range   | Fallback default range          |
| Database query error | Hiển thị lỗi chung hoặc log     |
| Unauthorized user    | Forbidden/redirect login        |
| Staff không có quyền | Forbidden                       |

---

## 27. Test Cases

| Test Case ID | Scenario                       | Expected Result                                   |
| ------------ | ------------------------------ | ------------------------------------------------- |
| TC-001       | Admin mở /admin                | Dashboard hiển thị                                |
| TC-002       | Admin mở /admin/dashboard      | Dashboard hiển thị hoặc redirect đúng             |
| TC-003       | Guest truy cập dashboard       | Bị chặn                                           |
| TC-004       | Customer truy cập dashboard    | Bị chặn                                           |
| TC-005       | Dashboard không có order       | Hiển thị empty/zero state                         |
| TC-006       | Dashboard có orders            | KPI orders hiển thị đúng                          |
| TC-007       | Dashboard có revenue           | Revenue hiển thị đúng                             |
| TC-008       | Dashboard có cancelled orders  | Cancelled không tính vào revenue nếu rule yêu cầu |
| TC-009       | Dashboard có pending orders    | Pending count đúng                                |
| TC-010       | Dashboard có unpaid COD orders | Unpaid COD count đúng                             |
| TC-011       | Dashboard có low stock         | Low stock widget hiển thị                         |
| TC-012       | Dashboard không có low stock   | Hiển thị no low stock state                       |
| TC-013       | Recent orders                  | Hiển thị đơn mới nhất                             |
| TC-014       | Top selling products           | Hiển thị product bán chạy                         |
| TC-015       | Date range today               | Dữ liệu lọc đúng                                  |
| TC-016       | Date range last 7 days         | Dữ liệu lọc đúng                                  |
| TC-017       | Invalid date range             | Fallback default                                  |
| TC-018       | Click pending orders KPI       | Chuyển tới admin orders filter                    |
| TC-019       | Click low stock item           | Chuyển tới inventory/product liên quan            |
| TC-020       | Mobile dashboard               | Layout không vỡ                                   |

---

## 28. Acceptance Criteria

Task này hoàn thành khi:

* [ ] Có admin dashboard page.
* [ ] `/admin` hoặc `/admin/dashboard` hiển thị dashboard.
* [ ] Dashboard dùng admin layout.
* [ ] Dashboard được bảo vệ bởi admin/auth middleware.
* [ ] Customer/guest không truy cập được.
* [ ] Dashboard có date range filter cơ bản.
* [ ] Dashboard hiển thị total orders.
* [ ] Dashboard hiển thị revenue cơ bản.
* [ ] Dashboard hiển thị pending orders.
* [ ] Dashboard hiển thị unpaid COD orders.
* [ ] Dashboard hiển thị products count.
* [ ] Dashboard hiển thị low stock count.
* [ ] Dashboard hiển thị recent orders.
* [ ] Dashboard hiển thị order status summary.
* [ ] Dashboard hiển thị payment status summary.
* [ ] Dashboard hiển thị low stock widget.
* [ ] Dashboard hiển thị top selling products cơ bản nếu có order items.
* [ ] Dashboard có alerts/action required.
* [ ] KPI cards có link sang trang quản lý liên quan.
* [ ] Dashboard xử lý empty state tốt.
* [ ] Dashboard không query quá nặng.
* [ ] Dashboard mobile responsive.
* [ ] Không implement report nâng cao.
* [ ] Không implement export.
* [ ] Không implement online payment.
* [ ] Không dùng Vue.js.

---

## 29. Commands

Sau khi implement, chạy các lệnh:

| Command                | Purpose               |
| ---------------------- | --------------------- |
| php artisan route:list | Kiểm tra route        |
| php artisan test       | Chạy test nếu có      |
| npm run build          | Build frontend assets |
| php artisan serve      | Chạy local server     |

URL test:

`http://127.0.0.1:8000/admin`

`http://127.0.0.1:8000/admin/dashboard`

---

## 30. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-04-admin-layout.md
* docs/tasks/task-10-product-management-with-translation.md
* docs/tasks/task-12-inventory-management.md
* docs/tasks/task-19-order-creation.md
* docs/tasks/task-20-admin-order-management.md
* docs/tasks/task-21-admin-dashboard.md

Sau đó implement Task 21: Admin Dashboard theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 21.
* Tạo admin dashboard page.
* Dashboard dùng admin layout hiện tại.
* Route `/admin` hoặc `/admin/dashboard` hiển thị dashboard.
* Route phải được bảo vệ bởi admin/auth middleware.
* Dashboard có date range filter cơ bản.
* Dashboard hiển thị KPI cards: revenue, total orders, pending orders, unpaid COD orders, products count, low stock count.
* Dashboard hiển thị recent orders.
* Dashboard hiển thị order status summary.
* Dashboard hiển thị payment status summary.
* Dashboard hiển thị low stock items.
* Dashboard hiển thị top selling products cơ bản.
* Dashboard có alerts/action required.
* KPI cards có link sang trang quản lý liên quan.
* Dashboard xử lý tốt khi chưa có dữ liệu.
* Dashboard mobile responsive.
* Không query quá nặng, nên gom logic vào service nếu phù hợp.
* Không implement Report nâng cao.
* Không implement Export.
* Không implement Online Payment.
* Không dùng Vue.js.
* Dùng Blade + Tailwind CSS + Alpine.js hoặc Fetch API nếu cần.
* Sau khi làm xong, báo cáo file đã tạo/sửa và lệnh cần chạy để test.
