# WebStore - Bản sửa theo Yêu cầu 1 LAB 5&6

## Nội dung đã sửa

1. `index.php` được chỉnh thành router API:
   - Endpoint bắt đầu bằng `/api`.
   - API luôn trả JSON.
   - Hỗ trợ `GET`, `POST`, `PUT`, `DELETE`, `OPTIONS`.

2. Giao diện chính đổi sang `app/views/index.html`:
   - Gọi API bằng JavaScript `fetch()`.
   - Không xử lý thêm/sửa/xóa bằng form PHP truyền thống.
   - Các form đều dùng `event.preventDefault()`.

3. Bổ sung API Controller:
   - `ProductApiController.php`
   - `CategoryApiController.php`
   - `CartApiController.php`
   - `OrderApiController.php`
   - `PaymentApiController.php`
   - `AccountApiController.php`
   - `BaseApiController.php`

4. Sửa Model:
   - `ProductModel.php`
   - `CategoryModel.php`

5. Sửa `database.sql` đồng bộ với `app/config/database.php`:
   - Database: `My_Store`
   - Có bảng `categories`, `products`, `users`, `orders`, `order_details`, `payments`.

## Cách chạy

1. Copy thư mục `WebStore` vào:

```text
C:/laragon/www/WebStore
```

2. Import file:

```text
database.sql
```

3. Mở trình duyệt:

```text
http://localhost/WebStore/
```

4. Test API bằng Postman theo file:

```text
POSTMAN_TEST_YEU_CAU_1.md
```

## Tài khoản test

```text
admin / 123456
user / 123456
```
