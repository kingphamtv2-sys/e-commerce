# Task 23: Report

## 1. Overview

Task này dùng để xây dựng hệ thống báo cáo trong Admin cho e-commerce system.

Report giúp admin xem dữ liệu tổng hợp và phân tích chi tiết hơn Dashboard.

Dashboard ở Task 21 chỉ hiển thị tổng quan nhanh. Task 23 sẽ tập trung vào các báo cáo chi tiết như:

* Sales report.
* Order report.
* Product sales report.
* Inventory report.
* Customer report.
* Coupon report.
* Tax report.
* Payment report.
* Revenue report theo thời gian.

Frontend admin sử dụng:

* Laravel Blade.
* Tailwind CSS.
* Alpine.js nếu cần.
* Fetch API nếu cần.
* Không dùng Vue.js.

---

## 2. Objectives

Sau khi hoàn thành Task 23, hệ thống cần có:

* Admin có thể xem trang Reports.
* Admin có thể xem Sales Report.
* Admin có thể xem Order Report.
* Admin có thể xem Product Sales Report.
* Admin có thể xem Inventory Report.
* Admin có thể xem Coupon Report.
* Admin có thể xem Tax Report.
* Admin có thể xem Payment Report.
* Admin có thể lọc report theo date range.
* Admin có thể lọc report theo order status.
* Admin có thể lọc report theo payment status.
* Admin có thể lọc report theo payment method.
* Admin có thể lọc report theo product/category nếu cần.
* Report hiển thị số liệu rõ ràng.
* Report có table chi tiết.
* Report có summary cards.
* Report có export CSV hoặc Excel nếu phù hợp.
* Report dùng snapshot data từ orders, order_items, order_payments.
* Report không tính lại giá từ product hiện tại.
* Report không làm thay đổi dữ liệu order.
* Report UI chuyên nghiệp, dễ đọc, responsive.
* Không dùng Vue.js.

---

## 3. Scope

### 3.1. In Scope

Các phần sẽ làm trong Task 23:

* Admin reports index page.
* Sales report.
* Order report.
* Product sales report.
* Inventory report.
* Coupon report.
* Tax report.
* Payment report.
* Report filters.
* Report summary cards.
* Report detail tables.
* Basic chart hoặc trend table nếu cần.
* Export CSV.
* Export Excel nếu package đã có hoặc dễ tích hợp.
* Permission admin/staff.
* Empty state.
* Responsive UI.

### 3.2. Out of Scope

Không làm trong Task 23:

* Không làm BI dashboard nâng cao.
* Không làm realtime analytics.
* Không làm customer behavior tracking.
* Không làm conversion tracking.
* Không làm marketing attribution.
* Không làm A/B testing.
* Không làm report scheduling qua email.
* Không làm data warehouse.
* Không làm advanced chart library phức tạp.
* Không implement Online Payment.
* Không implement Order Creation.
* Không implement Inventory transaction mới ngoài report.
* Không dùng Vue.js.

---

## 4. Dependencies

Task này phụ thuộc các task trước:

| Task    | Dependency             |
| ------- | ---------------------- |
| Task 04 | Admin Layout           |
| Task 07 | Currency Management    |
| Task 08 | Tax Management         |
| Task 12 | Inventory Management   |
| Task 16 | Coupon                 |
| Task 19 | Order Creation         |
| Task 20 | Admin Order Management |
| Task 21 | Admin Dashboard        |

Task này không tạo order mới và không thay đổi inventory.

---

## 5. User Roles

| Role     | Permission                      |
| -------- | ------------------------------- |
| Admin    | Xem toàn bộ reports             |
| Staff    | Xem reports nếu được phân quyền |
| Customer | Không truy cập admin reports    |
| Guest    | Không truy cập admin reports    |

Business rules:

* Tất cả report routes phải có admin/auth middleware.
* Nếu chưa có permission chi tiết, chỉ admin/staff được xem.
* Customer/guest bị chặn.

---

## 6. Report List Page

URL đề xuất:

`/admin/reports`

Trang này hiển thị danh sách các báo cáo.

Report cards đề xuất:

| Report               | Description                                 |
| -------------------- | ------------------------------------------- |
| Sales Report         | Doanh thu theo thời gian                    |
| Order Report         | Thống kê đơn hàng                           |
| Product Sales Report | Sản phẩm bán chạy                           |
| Inventory Report     | Tồn kho, low stock, out of stock            |
| Coupon Report        | Hiệu quả coupon                             |
| Tax Report           | Thuế theo tax class                         |
| Payment Report       | Thanh toán theo method/status               |
| Customer Report      | Customer order summary nếu có customer data |

Mỗi report card nên có:

* Icon.
* Title.
* Description ngắn.
* Link view report.
* Badge nếu report quan trọng.

---

## 7. Common Report Filters

Các report nên dùng bộ filter chung nếu phù hợp.

### 7.1. Date Range Filter

Date range options:

| Filter       | Description      |
| ------------ | ---------------- |
| Today        | Hôm nay          |
| Yesterday    | Hôm qua          |
| Last 7 Days  | 7 ngày gần nhất  |
| Last 30 Days | 30 ngày gần nhất |
| This Month   | Tháng hiện tại   |
| Last Month   | Tháng trước      |
| Custom Range | Từ ngày đến ngày |

Business rules:

* Mặc định report hiển thị Last 30 Days.
* Date range filter dùng theo `ordered_at`.
* Nếu report inventory, có thể không cần date range hoặc dùng updated_at/log date.

### 7.2. Order Status Filter

Values:

* pending.
* confirmed.
* processing.
* completed.
* cancelled.

### 7.3. Payment Status Filter

Values:

* unpaid.
* pending.
* paid.
* failed.
* refunded.
* cancelled.

### 7.4. Payment Method Filter

Values:

* cod.
* online nếu có sau này.

### 7.5. Product / Category Filter

Dùng cho Product Sales Report.

Filters:

* Product.
* Category.
* SKU.
* Variant.

### 7.6. Export Filter Consistency

Nếu admin export report:

* Export phải dùng đúng filter đang chọn.
* Không export dữ liệu ngoài filter.
* Filename nên có report name và date range.

---

## 8. Sales Report

URL đề xuất:

`/admin/reports/sales`

Sales Report tập trung vào doanh thu.

### 8.1. Summary Metrics

Sales Report cần hiển thị:

| Metric              | Description                               |
| ------------------- | ----------------------------------------- |
| Gross Revenue       | Tổng doanh thu từ order không cancelled   |
| Paid Revenue        | Tổng doanh thu đã paid                    |
| Unpaid Revenue      | Tổng doanh thu chưa paid                  |
| Discount Amount     | Tổng discount                             |
| Tax Amount          | Tổng tax                                  |
| Shipping Amount     | Tổng shipping                             |
| Net Revenue         | Gross revenue - discount nếu business cần |
| Total Orders        | Tổng số đơn                               |
| Average Order Value | Giá trị trung bình đơn                    |

### 8.2. Revenue Rules

Business rules:

* Không tính cancelled orders vào gross revenue.
* Refund nếu có sau này sẽ xử lý ở report nâng cao.
* Revenue lấy từ `orders.grand_total_amount`.
* Discount lấy từ `orders.discount_amount`.
* Tax lấy từ `orders.tax_amount`.
* Shipping lấy từ `orders.shipping_amount`.
* Không tính lại từ product hiện tại.
* Dữ liệu phải dựa trên order snapshot.

### 8.3. Sales Trend

Hiển thị doanh thu theo ngày.

Columns:

| Column        | Description             |
| ------------- | ----------------------- |
| Date          | Ngày                    |
| Orders        | Số đơn                  |
| Gross Revenue | Tổng doanh thu          |
| Paid Revenue  | Doanh thu đã thanh toán |
| Discount      | Tổng giảm giá           |
| Tax           | Tổng thuế               |
| Grand Total   | Tổng cuối               |

MVP có thể hiển thị dạng table. Chart là optional.

---

## 9. Order Report

URL đề xuất:

`/admin/reports/orders`

Order Report tập trung vào số lượng và trạng thái đơn.

### 9.1. Summary Metrics

| Metric            | Description      |
| ----------------- | ---------------- |
| Total Orders      | Tổng đơn         |
| Pending Orders    | Đơn mới          |
| Confirmed Orders  | Đơn đã xác nhận  |
| Processing Orders | Đơn đang xử lý   |
| Completed Orders  | Đơn hoàn tất     |
| Cancelled Orders  | Đơn hủy          |
| Cancellation Rate | Tỷ lệ hủy nếu có |

### 9.2. Order Status Breakdown

Hiển thị breakdown theo order status:

| Status     | Count  | Amount    |
| ---------- | ------ | --------- |
| pending    | Số đơn | Tổng tiền |
| confirmed  | Số đơn | Tổng tiền |
| processing | Số đơn | Tổng tiền |
| completed  | Số đơn | Tổng tiền |
| cancelled  | Số đơn | Tổng tiền |

### 9.3. Order Detail Table

Table hiển thị:

| Column         | Description    |
| -------------- | -------------- |
| Order Number   | Mã đơn         |
| Customer       | Tên khách      |
| Email          | Email          |
| Phone          | Phone          |
| Order Status   | Status         |
| Payment Status | Payment status |
| Payment Method | COD/online     |
| Grand Total    | Tổng tiền      |
| Ordered At     | Ngày đặt       |
| Action         | View order     |

---

## 10. Product Sales Report

URL đề xuất:

`/admin/reports/product-sales`

Product Sales Report tập trung vào sản phẩm bán chạy.

### 10.1. Summary Metrics

| Metric               | Description             |
| -------------------- | ----------------------- |
| Products Sold        | Tổng số item bán ra     |
| Unique Products Sold | Số sản phẩm unique      |
| Total Quantity Sold  | Tổng quantity           |
| Product Revenue      | Tổng doanh thu từ items |
| Best Seller          | Sản phẩm bán nhiều nhất |

### 10.2. Product Sales Table

Columns:

| Column        | Description             |
| ------------- | ----------------------- |
| Product Name  | Product snapshot        |
| Variant       | Variant snapshot nếu có |
| SKU           | SKU snapshot            |
| Quantity Sold | Tổng số lượng bán       |
| Orders Count  | Số đơn có sản phẩm      |
| Subtotal      | Tổng item subtotal      |
| Discount      | Tổng item discount      |
| Tax           | Tổng item tax           |
| Total Revenue | Tổng item total         |

Business rules:

* Dữ liệu lấy từ `order_items`.
* Không tính orders cancelled nếu rule yêu cầu.
* Product name lấy từ snapshot.
* Nếu product đã bị xóa, report vẫn hiển thị snapshot cũ.
* Có thể group theo product_id + product_variant_id.
* Nếu product_id null, group theo product_name + sku snapshot.

---

## 11. Inventory Report

URL đề xuất:

`/admin/reports/inventory`

Inventory Report tập trung vào tồn kho hiện tại.

### 11.1. Summary Metrics

| Metric                   | Description                   |
| ------------------------ | ----------------------------- |
| Total Stock Items        | Tổng inventory records        |
| Low Stock Items          | Sắp hết hàng                  |
| Out Of Stock Items       | Hết hàng                      |
| Total Available Quantity | Tổng available quantity       |
| Total Reserved Quantity  | Tổng reserved quantity nếu có |

### 11.2. Inventory Table

Columns:

| Column              | Description                       |
| ------------------- | --------------------------------- |
| Product             | Product name                      |
| Variant             | Variant nếu có                    |
| SKU                 | SKU                               |
| Quantity            | Tồn kho                           |
| Reserved            | Reserved quantity                 |
| Available           | Available quantity                |
| Low Stock Threshold | Ngưỡng thấp                       |
| Stock Status        | in_stock, low_stock, out_of_stock |
| Updated At          | Last update                       |
| Action              | View/Edit inventory               |

Business rules:

* Inventory report dùng dữ liệu inventory hiện tại.
* Không thay đổi stock trong report.
* Low stock dựa trên threshold từ Task 12.
* Product có variant thì report theo variant.
* Product không variant thì report theo product.

---

## 12. Coupon Report

URL đề xuất:

`/admin/reports/coupons`

Coupon Report tập trung vào hiệu quả mã giảm giá.

### 12.1. Summary Metrics

| Metric             | Description            |
| ------------------ | ---------------------- |
| Total Coupons Used | Tổng lượt dùng coupon  |
| Total Discount     | Tổng tiền giảm         |
| Orders With Coupon | Số đơn có coupon       |
| Average Discount   | Giảm giá trung bình    |
| Top Coupon         | Coupon dùng nhiều nhất |

### 12.2. Coupon Table

Columns:

| Column         | Description                  |
| -------------- | ---------------------------- |
| Coupon Code    | Mã coupon                    |
| Coupon Name    | Tên coupon                   |
| Usage Count    | Số lần dùng                  |
| Orders Count   | Số đơn                       |
| Total Discount | Tổng giảm giá                |
| Total Revenue  | Doanh thu từ đơn dùng coupon |
| Last Used      | Lần dùng gần nhất            |

Business rules:

* Dữ liệu lấy từ `coupon_usages` nếu có.
* Có thể join với orders.
* Chỉ tính coupon usage đã tạo khi order thành công.
* Không tính coupon chỉ apply trong cart.
* Nếu coupon bị xóa/disable, report vẫn dùng snapshot coupon_code.

---

## 13. Tax Report

URL đề xuất:

`/admin/reports/taxes`

Tax Report tập trung vào số tiền thuế theo tax class.

### 13.1. Summary Metrics

| Metric            | Description            |
| ----------------- | ---------------------- |
| Total Tax         | Tổng thuế              |
| Taxable Amount    | Tổng amount chịu thuế  |
| Tax Classes Count | Số tax class phát sinh |
| Orders With Tax   | Số đơn có tax          |

### 13.2. Tax Table

Columns:

| Column         | Description         |
| -------------- | ------------------- |
| Tax Name       | Tax name snapshot   |
| Tax Rate       | Tax rate snapshot   |
| Taxable Amount | Tổng taxable amount |
| Tax Amount     | Tổng tax amount     |
| Orders Count   | Số đơn              |
| Items Count    | Số items            |

Business rules:

* Dữ liệu ưu tiên lấy từ `order_tax_lines`.
* Nếu không có `order_tax_lines`, có thể aggregate từ `order_items`.
* Tax name/rate dùng snapshot.
* Không dùng tax settings hiện tại để tính lại order cũ.

---

## 14. Payment Report

URL đề xuất:

`/admin/reports/payments`

Payment Report tập trung vào payment method và payment status.

### 14.1. Summary Metrics

| Metric            | Description           |
| ----------------- | --------------------- |
| Total Payments    | Tổng payment records  |
| Paid Amount       | Tổng đã thanh toán    |
| Unpaid Amount     | Tổng chưa thanh toán  |
| COD Orders        | Số đơn COD            |
| COD Unpaid Amount | Số tiền COD chưa thu  |
| Failed Payments   | Payment failed nếu có |

### 14.2. Payment Method Breakdown

Columns:

| Column         | Description         |
| -------------- | ------------------- |
| Payment Method | COD/online          |
| Payment Status | paid/unpaid/pending |
| Orders Count   | Số đơn              |
| Amount         | Tổng tiền           |
| Last Payment   | Lần mới nhất        |

Business rules:

* Dữ liệu lấy từ `order_payments` hoặc orders payment fields.
* COD unpaid cần nổi bật vì admin cần xử lý.
* Online payment sẽ mở rộng ở Task 24.

---

## 15. Customer Report

URL đề xuất:

`/admin/reports/customers`

Customer Report là optional nhưng nên có mức cơ bản.

### 15.1. Summary Metrics

| Metric                      | Description                   |
| --------------------------- | ----------------------------- |
| Total Customers With Orders | Số customer có đơn            |
| Guest Orders                | Số đơn guest                  |
| Registered Customer Orders  | Số đơn customer               |
| Repeat Customers            | Customer mua nhiều hơn 1 lần  |
| Top Customer                | Customer có tổng mua cao nhất |

### 15.2. Customer Table

Columns:

| Column        | Description  |
| ------------- | ------------ |
| Customer Name | Tên khách    |
| Email         | Email        |
| Phone         | Phone        |
| Orders Count  | Số đơn       |
| Total Spent   | Tổng chi     |
| Last Order    | Đơn gần nhất |

Business rules:

* Guest orders group theo email nếu không có user_id.
* Customer orders group theo user_id.
* Không cần customer segmentation nâng cao trong MVP.

---

## 16. Export Requirements

Report nên hỗ trợ export dữ liệu.

### 16.1. Export Formats

MVP hỗ trợ:

| Format | Required                |
| ------ | ----------------------- |
| CSV    | Yes                     |
| Excel  | Optional                |
| PDF    | Optional / Out of scope |

### 16.2. Export Rules

Business rules:

* Export phải dùng đúng filter hiện tại.
* Export không bị ảnh hưởng bởi pagination.
* Export nên giới hạn dữ liệu nếu quá lớn.
* File name gồm report name và date range.
* Export chỉ admin/staff có quyền mới dùng được.
* Không export dữ liệu nhạy cảm không cần thiết.

### 16.3. Export Button UX

Mỗi report page nên có:

* Export CSV button.
* Export Excel button nếu implement.
* Loading state nếu export mất thời gian.
* Error message nếu export thất bại.

---

## 17. Report UI Requirements

### 17.1. Layout

Mỗi report page nên có:

1. Page header.
2. Filter panel.
3. Summary cards.
4. Chart hoặc trend table.
5. Detail table.
6. Export actions.
7. Pagination nếu table lớn.

### 17.2. Filter UX

Filter panel cần:

* Gọn.
* Dễ dùng.
* Có Apply button.
* Có Reset button.
* Giữ filter trong query string.
* Không làm layout quá dài.

### 17.3. Table UX

Report table cần:

* Header rõ.
* Number align right.
* Currency format đúng.
* Status badge rõ.
* Empty state đẹp.
* Pagination nếu cần.
* Responsive trên mobile.

### 17.4. Mobile UX

Mobile layout cần:

* Summary cards xếp dọc.
* Filter có thể collapsible.
* Table có horizontal scroll hoặc card layout.
* Không vỡ layout.

---

## 18. Currency Rules

Report cần xử lý currency cẩn thận.

Business rules:

* Report admin nên ưu tiên base/default currency.
* Nếu order có currency snapshot khác nhau, cần dùng base amount nếu có.
* Nếu không có base amount, hiển thị theo order currency hoặc group theo currency.
* Không quy đổi bằng tỷ giá hiện tại nếu order đã có currency snapshot.
* Không làm thay đổi order snapshot.

MVP recommendation:

* Nếu hệ thống chủ yếu dùng một currency, hiển thị theo default currency.
* Nếu có multi-currency, group report theo `currency_code` hoặc dùng base amount nếu đã snapshot.

---

## 19. Date and Time Rules

Business rules:

* Date range dùng app timezone.
* Report theo order nên dùng `orders.ordered_at`.
* Payment report có thể dùng `order_payments.created_at` hoặc `paid_at`.
* Coupon report dùng `coupon_usages.used_at`.
* Inventory report dùng trạng thái hiện tại hoặc inventory log date nếu có.
* Display date format theo admin locale nếu có.

---

## 20. Status Rules

Report không nên tính sai các status.

### 20.1. Cancelled Orders

Business rules:

* Cancelled orders không tính vào revenue chính.
* Có thể hiển thị riêng trong order report.
* Product sales không nên tính cancelled orders nếu stock đã restock/cancel.

### 20.2. Paid vs Unpaid

Business rules:

* Paid revenue chỉ tính payment_status = paid.
* COD unpaid cần tách riêng.
* Gross revenue có thể tính order chưa cancelled.

### 20.3. Refund

Refund chưa làm trong MVP.

* Nếu sau này có refund, Task Report sẽ mở rộng.
* Trong Task 23 chưa cần refund logic nâng cao.

---

## 21. Database Design

Task 23 chủ yếu dùng dữ liệu đã có.

Không bắt buộc tạo bảng mới.

Có thể dùng các bảng:

| Table            | Usage                         |
| ---------------- | ----------------------------- |
| orders           | Sales, order, customer report |
| order_items      | Product sales, tax report     |
| order_payments   | Payment report                |
| order_tax_lines  | Tax report                    |
| coupon_usages    | Coupon report                 |
| products         | Inventory/product links       |
| product_variants | Inventory/product links       |
| inventories      | Inventory report              |
| inventory_logs   | Inventory trend optional      |

### 21.1. Optional report_exports Table

Không bắt buộc trong MVP.

Nếu muốn tracking export:

| Field       | Description      |
| ----------- | ---------------- |
| user_id     | Admin export     |
| report_type | Report name      |
| filters     | JSON filters     |
| file_path   | Export file      |
| status      | completed/failed |
| created_at  | Time             |

Task 23 MVP không cần tạo bảng này.

---

## 22. Service Design

Nên gom logic report vào services.

Services đề xuất:

| Service                   | Responsibility         |
| ------------------------- | ---------------------- |
| ReportFilterService       | Parse/validate filters |
| SalesReportService        | Sales report data      |
| OrderReportService        | Order report data      |
| ProductSalesReportService | Product sales data     |
| InventoryReportService    | Inventory report data  |
| CouponReportService       | Coupon report data     |
| TaxReportService          | Tax report data        |
| PaymentReportService      | Payment report data    |
| ReportExportService       | Export CSV/Excel       |

Business rules:

* Controller không nên chứa quá nhiều query phức tạp.
* Blade không nên query database.
* Report service trả data đã format hoặc raw + formatted tùy project convention.

---

## 23. Routes

Admin report routes đề xuất:

| Method | URL                          | Name                        | Description              |
| ------ | ---------------------------- | --------------------------- | ------------------------ |
| GET    | /admin/reports               | admin.reports.index         | Report list              |
| GET    | /admin/reports/sales         | admin.reports.sales         | Sales report             |
| GET    | /admin/reports/orders        | admin.reports.orders        | Order report             |
| GET    | /admin/reports/product-sales | admin.reports.product-sales | Product sales report     |
| GET    | /admin/reports/inventory     | admin.reports.inventory     | Inventory report         |
| GET    | /admin/reports/coupons       | admin.reports.coupons       | Coupon report            |
| GET    | /admin/reports/taxes         | admin.reports.taxes         | Tax report               |
| GET    | /admin/reports/payments      | admin.reports.payments      | Payment report           |
| GET    | /admin/reports/customers     | admin.reports.customers     | Customer report optional |

Export routes:

| Method | URL | Name | Description |
|---|---|---|
| GET | /admin/reports/sales/export | admin.reports.sales.export | Export sales |
| GET | /admin/reports/orders/export | admin.reports.orders.export | Export orders |
| GET | /admin/reports/product-sales/export | admin.reports.product-sales.export | Export product sales |
| GET | /admin/reports/inventory/export | admin.reports.inventory.export | Export inventory |
| GET | /admin/reports/coupons/export | admin.reports.coupons.export | Export coupons |
| GET | /admin/reports/taxes/export | admin.reports.taxes.export | Export tax |
| GET | /admin/reports/payments/export | admin.reports.payments.export | Export payments |

---

## 24. Validation Rules

### 24.1. Common Filter Validation

| Field          | Rule                                    |
| -------------- | --------------------------------------- |
| date_from      | Nullable date                           |
| date_to        | Nullable date, after_or_equal date_from |
| order_status   | Nullable valid order status             |
| payment_status | Nullable valid payment status           |
| payment_method | Nullable valid payment method           |
| currency_code  | Nullable valid currency                 |
| product_id     | Nullable exists products                |
| category_id    | Nullable exists categories              |
| export_format  | csv/excel nếu dùng                      |

### 24.2. Invalid Filter Behavior

Nếu filter không hợp lệ:

* Hiển thị validation error.
* Không crash page.
* Fallback về default filter nếu phù hợp.
* Giữ UI dễ hiểu.

---

## 25. Security Requirements

Yêu cầu bảo mật:

* Admin/auth middleware cho toàn bộ report routes.
* CSRF nếu có POST action.
* Export chỉ admin/staff có quyền.
* Không cho customer/guest truy cập report.
* Validate filters ở backend.
* Không expose SQL errors.
* Không export dữ liệu nhạy cảm không cần thiết.
* Không tin query string hoàn toàn.
* Giới hạn export size nếu cần.

---

## 26. Performance Requirements

Report có thể query nhiều dữ liệu, cần tối ưu.

Yêu cầu:

* Dùng aggregation query.
* Không load toàn bộ orders nếu chỉ cần sum/count.
* Detail table có pagination.
* Export có thể stream hoặc chunk nếu dữ liệu lớn.
* Không query trong Blade loop.
* Index các cột quan trọng nếu cần: ordered_at, order_status, payment_status, product_id, coupon_id.
* Không làm report quá nặng trong MVP.
* Advanced analytics để sau.

---

## 27. Files Expected

Codex có thể tạo hoặc cập nhật các nhóm file sau:

| Type               | Description                                                                                                                                            |
| ------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Controller         | AdminReportController                                                                                                                                  |
| Controllers        | Specific report controllers nếu tách                                                                                                                   |
| Services           | SalesReportService, OrderReportService, ProductSalesReportService, InventoryReportService, CouponReportService, TaxReportService, PaymentReportService |
| Export Service     | ReportExportService                                                                                                                                    |
| Request Validation | ReportFilterRequest nếu cần                                                                                                                            |
| Routes             | Admin report routes                                                                                                                                    |
| Blade Views        | Report index and report pages                                                                                                                          |
| Blade Partials     | Filter form, summary cards, report table, empty state                                                                                                  |
| Tests              | Report feature tests                                                                                                                                   |
| Optional Migration | report_exports nếu thật sự cần                                                                                                                         |

---

## 28. UI Components

Reusable components nên có:

| Component           | Usage                 |
| ------------------- | --------------------- |
| Report filter panel | Dùng chung các report |
| Summary card        | KPI cards             |
| Report table        | Table layout          |
| Empty state         | Khi không có data     |
| Export buttons      | CSV/Excel             |
| Status badge        | Order/payment status  |
| Date range selector | Common date filter    |

---

## 29. Error Handling

| Scenario               | Expected Result                    |
| ---------------------- | ---------------------------------- |
| No data                | Show empty state                   |
| Invalid date range     | Show validation error              |
| Invalid status filter  | Show validation error              |
| Export too large       | Show message or limit              |
| Export failed          | Show error                         |
| Unauthorized access    | Forbidden                          |
| Query error            | Log error and show generic message |
| Missing optional table | Graceful fallback if possible      |

---

## 30. Test Cases

| Test Case ID | Scenario                           | Expected Result            |
| ------------ | ---------------------------------- | -------------------------- |
| TC-001       | Admin mở report index              | Hiển thị danh sách reports |
| TC-002       | Guest truy cập reports             | Bị chặn                    |
| TC-003       | Customer truy cập reports          | Bị chặn                    |
| TC-004       | Admin mở sales report              | Hiển thị sales metrics     |
| TC-005       | Sales report date filter           | Dữ liệu lọc đúng           |
| TC-006       | Sales report không tính cancelled  | Revenue đúng               |
| TC-007       | Order report status breakdown      | Count đúng                 |
| TC-008       | Order report payment filter        | Dữ liệu đúng               |
| TC-009       | Product sales report               | Group product đúng         |
| TC-010       | Product sales không tính cancelled | Quantity đúng              |
| TC-011       | Inventory report low stock         | Hiển thị đúng low stock    |
| TC-012       | Inventory report out of stock      | Hiển thị đúng out of stock |
| TC-013       | Coupon report                      | Coupon usage đúng          |
| TC-014       | Coupon chỉ apply cart chưa order   | Không tính usage           |
| TC-015       | Tax report                         | Tax amount đúng snapshot   |
| TC-016       | Payment report COD unpaid          | Hiển thị đúng              |
| TC-017       | Customer report guest order        | Group guest đúng           |
| TC-018       | Invalid date range                 | Hiển thị lỗi               |
| TC-019       | Export sales CSV                   | File export đúng filter    |
| TC-020       | Export product sales CSV           | File export đúng           |
| TC-021       | Report empty data                  | Empty state đẹp            |
| TC-022       | Mobile report page                 | Layout không vỡ            |
| TC-023       | Report table pagination            | Hoạt động đúng             |
| TC-024       | Report currency display            | Format đúng                |
| TC-025       | Staff không quyền                  | Bị chặn nếu có permission  |

---

## 31. Acceptance Criteria

Task này hoàn thành khi:

* [ ] Có admin report index page.
* [ ] Report routes được bảo vệ bởi admin/auth middleware.
* [ ] Customer/guest không truy cập được reports.
* [ ] Có Sales Report.
* [ ] Có Order Report.
* [ ] Có Product Sales Report.
* [ ] Có Inventory Report.
* [ ] Có Coupon Report.
* [ ] Có Tax Report.
* [ ] Có Payment Report.
* [ ] Có Customer Report nếu implement optional.
* [ ] Report có date range filter.
* [ ] Report có filter theo status/payment/product/category nếu phù hợp.
* [ ] Report summary cards hiển thị đúng.
* [ ] Report detail table hiển thị đúng.
* [ ] Report xử lý empty state.
* [ ] Sales Report không tính cancelled orders vào revenue chính.
* [ ] Product Sales Report dùng order item snapshot.
* [ ] Tax Report dùng tax snapshot.
* [ ] Coupon Report chỉ tính coupon usage khi order thành công.
* [ ] Payment Report hiển thị COD unpaid rõ ràng.
* [ ] Inventory Report hiển thị low stock/out of stock.
* [ ] Report không tính lại giá từ product hiện tại.
* [ ] Report không thay đổi order/inventory/coupon data.
* [ ] Export CSV hoạt động cho các report chính.
* [ ] Export dùng đúng filter hiện tại.
* [ ] Report UI responsive.
* [ ] Không implement BI nâng cao.
* [ ] Không implement realtime analytics.
* [ ] Không dùng Vue.js.

---

## 32. Commands

Sau khi implement, chạy các lệnh:

| Command                | Purpose               |
| ---------------------- | --------------------- |
| php artisan route:list | Kiểm tra route        |
| php artisan test       | Chạy test nếu có      |
| npm run build          | Build frontend assets |
| php artisan serve      | Chạy local server     |

URL test:

`http://127.0.0.1:8000/admin/reports`

`http://127.0.0.1:8000/admin/reports/sales`

`http://127.0.0.1:8000/admin/reports/orders`

`http://127.0.0.1:8000/admin/reports/product-sales`

`http://127.0.0.1:8000/admin/reports/inventory`

`http://127.0.0.1:8000/admin/reports/coupons`

`http://127.0.0.1:8000/admin/reports/taxes`

`http://127.0.0.1:8000/admin/reports/payments`

---

## 33. Codex Instruction

Yêu cầu Codex thực hiện task này bằng prompt sau:

Bạn hãy đọc kỹ các file sau:

* docs/basic-design.md
* docs/database-design.md
* docs/tasks/task-04-admin-layout.md
* docs/tasks/task-07-currency-management.md
* docs/tasks/task-08-tax-management.md
* docs/tasks/task-12-inventory-management.md
* docs/tasks/task-16-coupon.md
* docs/tasks/task-19-order-creation.md
* docs/tasks/task-20-admin-order-management.md
* docs/tasks/task-21-admin-dashboard.md
* docs/tasks/task-23-report.md

Sau đó implement Task 23: Report theo đúng tài liệu.

Yêu cầu:

* Chỉ làm đúng phạm vi Task 23.
* Tạo admin report index page.
* Tạo Sales Report.
* Tạo Order Report.
* Tạo Product Sales Report.
* Tạo Inventory Report.
* Tạo Coupon Report.
* Tạo Tax Report.
* Tạo Payment Report.
* Customer Report là optional nếu dữ liệu đã đủ.
* Reports phải có filter date range.
* Reports phải có filter status/payment/product/category nếu phù hợp.
* Report summary cards phải hiển thị rõ ràng.
* Report detail table phải có pagination nếu dữ liệu nhiều.
* Sales report không tính cancelled orders vào revenue chính.
* Product sales report dùng order_items snapshot.
* Tax report dùng tax snapshot từ order_tax_lines hoặc order_items.
* Coupon report chỉ tính coupon_usages đã tạo sau order thành công.
* Payment report hiển thị COD unpaid rõ ràng.
* Inventory report hiển thị low stock và out of stock.
* Export CSV cho các report chính.
* Export phải dùng đúng filter hiện tại.
* Không tính lại giá từ product hiện tại.
* Không thay đổi dữ liệu order/inventory/coupon.
* Report routes phải có admin/auth middleware.
* Không implement BI nâng cao.
* Không implement realtime analytics.
* Không implement Online Payment.
* Không dùng Vue.js.
* Dùng Blade + Tailwind CSS + Alpine.js hoặc Fetch API nếu cần.
* Sau khi làm xong, báo cáo file đã tạo/sửa và lệnh cần chạy để test.
