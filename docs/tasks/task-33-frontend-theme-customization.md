# Task 33: Frontend Theme Customization

## 1. Mục tiêu

Xây dựng chức năng cho phép admin tùy chỉnh giao diện frontend của website e-commerce thông qua admin panel mà không cần chỉnh sửa trực tiếp source code.

Chức năng này giúp hệ thống hoạt động giống một e-commerce CMS cơ bản, cho phép quản trị viên thay đổi logo, màu sắc, banner, nội dung trang chủ, footer và một số section hiển thị trên frontend.

## 2. Bối cảnh dự án

Dự án hiện tại là Laravel 12 e-commerce system.

Hệ thống đã có các module chính như:

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
* Cart and checkout
* Order management
* Email notification
* Customer account and order history
* Shipping management

Task này sẽ bổ sung khả năng tùy chỉnh giao diện frontend từ admin panel.

## 3. Phạm vi chức năng

Admin cần có màn hình quản lý theme settings trong admin panel.

Admin có thể chỉnh sửa các nhóm thông tin sau:

### 3.1. Brand Settings

Admin có thể cấu hình:

* Website logo
* Website favicon
* Tên thương hiệu hiển thị trên frontend nếu chưa có logo
* Ảnh preview hiện tại của logo và favicon

Logo và favicon sau khi upload phải được sử dụng tự động ở frontend.

### 3.2. Color Settings

Admin có thể cấu hình các màu chính của giao diện:

* Primary color
* Secondary color
* Text color
* Button color
* Link color nếu cần
* Background color nếu phù hợp với layout hiện tại

Các màu này phải được áp dụng vào frontend layout, đặc biệt là:

* Button
* Link
* Header
* Hero section
* Các thành phần dùng màu thương hiệu
* Các class theme chung của frontend

### 3.3. Homepage Hero Section

Admin có thể cấu hình hero section trên trang chủ:

* Hero title
* Hero subtitle
* Hero image hoặc banner image
* Button text
* Button URL
* Bật hoặc tắt hero section

Frontend home page phải đọc dữ liệu này và hiển thị tương ứng.

### 3.4. Homepage Section Visibility

Admin có thể bật hoặc tắt một số section trên trang chủ.

Các section nên hỗ trợ:

* Featured categories
* Featured products
* New arrivals
* Best sellers
* Promotion banner nếu hệ thống đã có
* Newsletter section nếu hệ thống đã có

Nếu một section bị tắt, frontend không hiển thị section đó.

### 3.5. Footer Settings

Admin có thể cấu hình footer:

* Footer text
* Copyright text
* Short store description
* Contact email
* Contact phone
* Address
* Facebook URL
* Instagram URL
* YouTube URL
* TikTok URL

Frontend footer phải tự động cập nhật theo dữ liệu admin cấu hình.

### 3.6. Custom CSS Cơ Bản

Admin có thể nhập custom CSS cơ bản để tinh chỉnh giao diện.

Yêu cầu bảo mật:

* Chỉ admin có quyền cao mới được chỉnh custom CSS.
* Không cho nhập JavaScript.
* Không cho nhập script tag.
* Không cho nhập HTML nguy hiểm.
* Cần validate hoặc sanitize dữ liệu trước khi lưu.
* Custom CSS phải được áp dụng trên frontend sau khi lưu.

## 4. Không nằm trong phạm vi task này

Task này không yêu cầu làm các chức năng nâng cao sau:

* Drag and drop page builder
* Live preview nâng cao
* Visual editor kiểu WordPress
* Quản lý nhiều theme template phức tạp
* Cho admin chỉnh sửa Blade template trực tiếp
* Cho admin nhập JavaScript tùy chỉnh
* Theme marketplace
* Versioning nhiều bản theme
* A/B testing giao diện

Các chức năng trên có thể làm ở task sau nếu cần.

## 5. Database Requirements

Cần có nơi lưu theme settings trong database.

Có thể chọn một trong hai hướng:

### Option 1: Dùng lại bảng settings hiện có

Nếu hệ thống đã có bảng settings từ Task 05, có thể mở rộng bảng này để lưu theme settings.

Ưu điểm:

* Không cần tạo bảng mới
* Dễ tái sử dụng helper settings hiện có
* Đơn giản cho hệ thống nhỏ

Nhược điểm:

* Dữ liệu system settings và theme settings có thể bị lẫn nhau
* Khó quản lý khi theme settings phát triển lớn hơn

### Option 2: Tạo bảng theme_settings riêng

Nên dùng hướng này nếu muốn module rõ ràng và dễ bảo trì.

Bảng theme settings nên lưu được:

* Key của setting
* Value của setting
* Type của setting
* Group của setting
* Thời gian tạo
* Thời gian cập nhật

Các type cần hỗ trợ:

* Text
* Image
* Color
* Boolean
* CSS
* URL

Các group nên có:

* Brand
* Colors
* Homepage
* Homepage sections
* Footer
* Social links
* Advanced

## 6. Default Theme Settings

Khi cài đặt module, hệ thống cần có dữ liệu mặc định để frontend không bị lỗi nếu admin chưa cấu hình.

Các giá trị mặc định nên bao gồm:

* Primary color mặc định
* Secondary color mặc định
* Text color mặc định
* Button color mặc định
* Hero title mặc định
* Hero subtitle mặc định
* Footer text mặc định
* Các homepage section mặc định đang bật
* Custom CSS mặc định rỗng

Nếu chưa có logo, frontend nên hiển thị tên website hoặc app name.

Nếu chưa có hero image, frontend vẫn phải hiển thị layout ổn định.

## 7. Admin UI Requirements

Cần tạo màn hình trong admin panel để quản lý Frontend Theme Customization.

Màn hình nên được chia thành các card hoặc tab rõ ràng:

1. Brand Settings
2. Color Settings
3. Homepage Hero Section
4. Homepage Sections
5. Footer Settings
6. Social Links
7. Custom CSS
8. Reset Theme Settings

### 7.1. Brand Settings UI

Admin có thể:

* Upload logo mới
* Xem logo hiện tại
* Upload favicon mới
* Xem favicon hiện tại
* Xóa hoặc thay thế logo nếu cần

### 7.2. Color Settings UI

Admin có thể chọn màu bằng color picker.

Các trường màu cần validate đúng định dạng màu hợp lệ.

Nếu admin nhập màu không hợp lệ, hệ thống phải hiển thị lỗi rõ ràng.

### 7.3. Homepage Hero UI

Admin có thể chỉnh:

* Hero title
* Hero subtitle
* Hero image
* Button text
* Button URL
* Trạng thái bật/tắt hero section

Cần hiển thị ảnh hero hiện tại nếu đã upload.

### 7.4. Homepage Sections UI

Admin có thể bật hoặc tắt từng section bằng checkbox hoặc switch.

Các section nên có label rõ ràng.

Ví dụ:

* Show featured categories
* Show featured products
* Show new arrivals
* Show best sellers
* Show newsletter section

### 7.5. Footer Settings UI

Admin có thể chỉnh:

* Footer text
* Copyright text
* Store description
* Contact email
* Contact phone
* Address

### 7.6. Social Links UI

Admin có thể nhập URL cho:

* Facebook
* Instagram
* YouTube
* TikTok

Các URL phải được validate đúng định dạng.

Nếu một social link không có dữ liệu, frontend không hiển thị link đó.

### 7.7. Custom CSS UI

Admin có thể nhập custom CSS trong textarea.

Textarea nên sử dụng font monospace để dễ đọc.

Cần có mô tả cảnh báo rằng chỉ CSS được cho phép, không nhập JavaScript.

### 7.8. Reset Theme UI

Admin có thể reset theme settings về mặc định.

Trước khi reset cần có confirm dialog.

Sau khi reset, frontend phải quay về giao diện mặc định an toàn.

## 8. Frontend Requirements

Frontend phải tự động đọc theme settings và áp dụng vào layout.

### 8.1. Header

Header frontend cần dùng logo từ theme settings.

Nếu chưa có logo, hiển thị tên website.

Favicon trong trình duyệt cũng phải lấy từ theme settings nếu đã cấu hình.

### 8.2. Global Theme Colors

Frontend cần áp dụng các màu theme vào giao diện.

Các thành phần nên sử dụng theme colors:

* Button chính
* Link chính
* Header highlight
* Badge nếu phù hợp
* Hero background
* Các section title
* Các thành phần nhận diện thương hiệu

Cách triển khai nên đảm bảo dễ bảo trì và không phải sửa từng view quá nhiều.

### 8.3. Homepage

Homepage cần đọc các setting sau:

* Hero title
* Hero subtitle
* Hero image
* Hero button text
* Hero button URL
* Trạng thái hiển thị hero section
* Trạng thái hiển thị từng homepage section

Nếu section bị tắt trong admin, frontend không render section đó.

### 8.4. Footer

Footer frontend cần đọc dữ liệu từ theme settings.

Các social links chỉ hiển thị khi có URL hợp lệ.

Footer text và thông tin liên hệ phải cập nhật theo cấu hình admin.

### 8.5. Custom CSS

Custom CSS do admin nhập phải được áp dụng vào frontend.

Custom CSS không được phá vỡ admin panel.

Custom CSS chỉ áp dụng cho frontend public layout.

## 9. Cache Requirements

Theme settings nên được cache để tránh query database lặp lại nhiều lần trên mỗi request frontend.

Khi admin cập nhật theme settings:

* Cache theme settings phải được clear
* Frontend phải nhận dữ liệu mới sau khi refresh
* Không cần chạy thủ công cache clear sau mỗi lần update

Nếu hệ thống đã có cache service cho settings, hãy tái sử dụng.

Nếu chưa có, cần tạo cơ chế cache đơn giản, dễ bảo trì.

## 10. File Upload Requirements

Các file upload cần lưu vào public storage.

Cần hỗ trợ:

* Logo image
* Favicon
* Hero image

Yêu cầu validation:

* Logo chỉ cho phép image hợp lệ
* Favicon chỉ cho phép định dạng phù hợp
* Hero image chỉ cho phép image hợp lệ
* Giới hạn dung lượng file upload
* Không cho upload file nguy hiểm
* File cũ có thể được giữ lại hoặc xóa tùy cách triển khai, nhưng không được làm lỗi frontend

Nếu storage public link chưa tồn tại, cần đảm bảo hệ thống có hướng dẫn hoặc xử lý phù hợp.

## 11. Validation Requirements

Cần validate toàn bộ input từ admin.

Các rule cần có:

* Color phải đúng định dạng màu hợp lệ
* URL phải đúng định dạng URL
* Text không vượt quá độ dài cho phép
* File upload phải đúng mime type
* File upload không vượt quá dung lượng cho phép
* Boolean field phải xử lý đúng khi checkbox không được gửi lên request
* Custom CSS không được chứa script hoặc nội dung nguy hiểm

Khi validation lỗi, admin phải thấy thông báo rõ ràng và dữ liệu nhập không bị mất.

## 12. Permission Requirements

Chỉ admin mới được truy cập chức năng Theme Customization.

Nếu hệ thống đang có role hoặc permission, cần thêm permission phù hợp.

Permission gợi ý:

* View theme settings
* Update theme settings
* Reset theme settings
* Update custom CSS

Nếu hệ thống chỉ có role admin đơn giản, route cần được bảo vệ bằng middleware admin hiện có.

## 13. Security Requirements

Cần đảm bảo các yêu cầu bảo mật sau:

* Guest không được truy cập trang theme settings.
* Customer không được truy cập trang theme settings.
* Chỉ admin được chỉnh theme.
* Custom CSS không được chứa JavaScript.
* Không cho admin nhập script tag.
* Không render HTML nguy hiểm từ theme settings.
* Social URLs cần dùng thuộc tính bảo mật khi mở tab mới.
* File upload phải validate kỹ.
* Không cho upload file executable.
* Không làm lộ đường dẫn server thật.
* Không để custom CSS ảnh hưởng admin panel.

## 14. Error Handling Requirements

Hệ thống cần xử lý các trường hợp lỗi:

* Upload file không hợp lệ
* Upload file vượt quá dung lượng
* URL không hợp lệ
* Color không hợp lệ
* Không ghi được file vào storage
* Cache không clear được
* Setting key bị thiếu
* Frontend gọi setting chưa tồn tại

Trong mọi trường hợp, frontend không được bị crash.

Nếu setting thiếu, frontend phải dùng giá trị mặc định.

## 15. UX Requirements

Admin UI cần dễ dùng, rõ ràng và nhất quán với admin layout hiện tại.

Yêu cầu UX:

* Các nhóm setting được chia rõ ràng
* Có preview ảnh hiện tại
* Có thông báo thành công sau khi lưu
* Có thông báo lỗi nếu validation fail
* Có confirm trước khi reset
* Form không quá dài hoặc khó dùng
* Các field có label rõ ràng
* Các field quan trọng có mô tả ngắn nếu cần

## 16. Testing Requirements

Cần viết test cho các trường hợp chính.

### 16.1. Admin Access Test

Kiểm tra admin có thể truy cập trang theme customization.

### 16.2. Guest Access Test

Kiểm tra guest không thể truy cập trang theme customization.

### 16.3. Customer Access Test

Nếu hệ thống có role customer, kiểm tra customer không thể truy cập trang này.

### 16.4. Update Theme Settings Test

Kiểm tra admin có thể cập nhật:

* Màu sắc
* Hero content
* Footer text
* Social links
* Homepage section visibility

### 16.5. Validation Test

Kiểm tra hệ thống từ chối:

* Color không hợp lệ
* URL không hợp lệ
* File không hợp lệ
* File vượt quá dung lượng
* Custom CSS chứa nội dung không hợp lệ

### 16.6. Frontend Apply Theme Test

Kiểm tra frontend hiển thị đúng:

* Logo
* Hero title
* Hero subtitle
* Theme color
* Footer text
* Social links
* Section visibility

### 16.7. Cache Test

Kiểm tra sau khi admin cập nhật setting, cache được clear và frontend hiển thị dữ liệu mới.

### 16.8. Reset Theme Test

Kiểm tra admin có thể reset theme settings về mặc định.

## 17. Acceptance Criteria

Task này được xem là hoàn thành khi:

* Admin có màn hình Frontend Theme Customization.
* Admin có thể chỉnh logo và favicon.
* Admin có thể chỉnh màu giao diện frontend.
* Admin có thể chỉnh nội dung hero section.
* Admin có thể upload hero image.
* Admin có thể bật hoặc tắt các homepage section.
* Admin có thể chỉnh footer text và social links.
* Admin có thể nhập custom CSS cơ bản một cách an toàn.
* Frontend tự động áp dụng theme settings.
* Theme settings có giá trị mặc định an toàn.
* Theme settings được cache.
* Cache được clear sau khi cập nhật.
* Validation hoạt động đúng.
* Permission hoạt động đúng.
* Guest và customer không thể truy cập chức năng admin này.
* Các test chính pass.
* Không làm ảnh hưởng các chức năng e-commerce hiện có.

## 18. Suggested Implementation Order for Codex

Codex nên triển khai theo thứ tự sau:

1. Kiểm tra cấu trúc dự án hiện tại, đặc biệt là admin layout, frontend layout, system settings và permission.
2. Quyết định dùng bảng settings hiện có hay tạo bảng theme settings riêng.
3. Tạo database structure cần thiết.
4. Tạo model hoặc service để đọc và ghi theme settings.
5. Tạo dữ liệu theme mặc định.
6. Tạo helper hoặc service để frontend lấy theme settings.
7. Tạo admin route, controller, request validation và view.
8. Tích hợp menu Theme Customization vào admin sidebar.
9. Tích hợp logo và favicon vào frontend layout.
10. Tích hợp theme colors vào frontend layout.
11. Tích hợp hero section vào homepage.
12. Tích hợp bật/tắt homepage sections.
13. Tích hợp footer và social links.
14. Tích hợp custom CSS cho frontend.
15. Thêm cache handling.
16. Thêm permission hoặc middleware bảo vệ route.
17. Viết test cho các case chính.
18. Chạy test và sửa lỗi phát sinh.
19. Kiểm tra lại UI frontend và admin sau khi hoàn thành.

## 19. Notes for Codex

Khi triển khai task này, cần ưu tiên sự ổn định và đơn giản.

Không tạo page builder phức tạp.

Không cho admin chỉnh sửa template Blade trực tiếp.

Không thêm JavaScript tùy chỉnh từ admin.

Không phá vỡ layout frontend hiện tại.

Nếu dự án đã có system settings service, hãy tái sử dụng khi hợp lý.

Nếu chưa có, tạo theme settings service riêng để code dễ bảo trì.

Frontend phải luôn có fallback value để không bị lỗi khi thiếu setting.

Custom CSS chỉ nên áp dụng ở frontend public layout, không áp dụng vào admin panel.

## 20. Expected Result

Sau khi hoàn thành task này, admin có thể vào admin panel và tùy chỉnh giao diện frontend cơ bản của website.

Frontend sẽ tự động cập nhật theo cấu hình mới mà không cần developer sửa code thủ công.

Chức năng này giúp hệ thống e-commerce có khả năng tùy biến giao diện tốt hơn và phù hợp hơn cho môi trường production.
