# Chạy dự án qua ngrok HTTPS

Tài liệu này hướng dẫn public ứng dụng Laravel qua ngrok và xử lý lỗi giao diện mất CSS/JavaScript do mixed content.

## 1. Chạy Laravel

Ví dụ chạy ứng dụng ở cổng `8001`:

```bash
php artisan serve --port=8001
```

## 2. Chạy ngrok

Mở tunnel tới đúng cổng Laravel:

```bash
ngrok http 8001
```

Sau đó cập nhật URL HTTPS do ngrok cung cấp trong `.env`:

```env
APP_URL=https://your-domain.ngrok-free.dev
```

Không thêm dấu `/` ở cuối URL.

## 3. Cấu hình trusted proxy

Ngrok nhận request HTTPS từ trình duyệt nhưng chuyển tiếp request tới Laravel bằng HTTP nội bộ. Laravel cần tin các forwarded headers để nhận biết scheme công khai vẫn là HTTPS.

Trong `bootstrap/app.php`, cấu hình:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->trustProxies(at: '*');

    // Các middleware khác...
})
```

Nếu thiếu cấu hình này, HTML có thể sinh asset URL dạng:

```text
http://your-domain.ngrok-free.dev/build/assets/app.css
```

Trình duyệt đang mở trang HTTPS sẽ chặn các asset HTTP này vì mixed content, khiến giao diện mất CSS hoặc JavaScript.

Sau khi cấu hình đúng, asset URL phải là:

```text
https://your-domain.ngrok-free.dev/build/assets/app.css
```

> Lưu ý: `trustProxies(at: '*')` phù hợp khi phát triển local với ngrok. Khi deploy production, nên thay `*` bằng IP hoặc dải IP của reverse proxy/load balancer tin cậy.

## 4. Build asset và xóa cache

Khi dùng asset đã build:

```bash
npm run build
php artisan optimize:clear
```

Nếu đang chạy Vite development server, HTTPS tunnel cần thêm cấu hình HMR phù hợp. Với ngrok, cách ổn định và đơn giản nhất là dùng `npm run build` và bảo đảm file `public/hot` không còn tồn tại.

Kiểm tra:

```bash
test ! -f public/hot && echo "Using production assets"
```

## 5. Kiểm tra kết quả

Kiểm tra HTML không còn URL HTTP:

```bash
curl -ks https://your-domain.ngrok-free.dev/products \
  | grep 'http://your-domain.ngrok-free.dev'
```

Lệnh không nên trả về kết quả.

Kiểm tra asset:

```bash
curl -I https://your-domain.ngrok-free.dev/build/assets/app.css
```

CSS cần trả về HTTP `200` và content type `text/css`.

Cuối cùng, hard refresh trình duyệt:

```text
Ctrl + Shift + R
```

## 6. Checklist xử lý lỗi layout

- `APP_URL` dùng `https://`.
- Ngrok trỏ đúng cổng Laravel.
- Laravel đã cấu hình `trustProxies`.
- Đã chạy `npm run build`.
- Không tồn tại `public/hot` nếu không dùng Vite dev server.
- Đã chạy `php artisan optimize:clear`.
- CSS và JavaScript trả về HTTP `200`.
- HTML không chứa asset URL `http://` trên trang HTTPS.
