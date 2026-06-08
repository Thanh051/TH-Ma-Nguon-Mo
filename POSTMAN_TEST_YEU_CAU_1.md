# Hướng dẫn test nhanh Yêu cầu 1 bằng Postman

Base URL khi chạy trên Laragon:

```text
http://localhost/WebStore/api
```

Nếu máy chưa bật rewrite `.htaccess`, dùng dạng dự phòng:

```text
http://localhost/WebStore/index.php?url=api/product
```

## 1. API trả JSON

GET:

```text
/api/product
/api/category
/api/cart
/api/order
/api/account/me
```

## 2. Dùng đúng HTTP Method

### Sản phẩm

GET danh sách:

```text
GET /api/product
```

GET chi tiết:

```text
GET /api/product/detail/1
```

POST thêm:

```text
POST /api/product/create
Content-Type: application/json

{
  "name": "Sản phẩm test API",
  "price": 150000,
  "stock": 10,
  "category_id": 1,
  "image": "test.jpg",
  "description": "Thêm bằng Postman"
}
```

PUT sửa:

```text
PUT /api/product/update/1
Content-Type: application/json

{
  "name": "iPhone 15 Pro Max Updated",
  "price": 33000000,
  "stock": 5,
  "category_id": 1,
  "image": "1779696697_iphone17prm.jpg",
  "description": "Cập nhật bằng API PUT"
}
```

DELETE xóa:

```text
DELETE /api/product/delete/7
```

### Danh mục

```text
GET    /api/category
POST   /api/category/create
PUT    /api/category/update/1
DELETE /api/category/delete/4
```

### Giỏ hàng

```text
GET    /api/cart
POST   /api/cart/add
PUT    /api/cart/update/1
DELETE /api/cart/delete/1
DELETE /api/cart/clear
GET    /api/cart/total
```

Body thêm giỏ hàng:

```json
{
  "product_id": 1,
  "quantity": 2
}
```

Body cập nhật số lượng:

```json
{
  "quantity": 3
}
```

### Đặt hàng

Trước khi tạo đơn hàng cần thêm sản phẩm vào giỏ hàng.

```text
POST /api/order/create
```

```json
{
  "customer_name": "Nguyễn Văn A",
  "customer_phone": "0901234567",
  "customer_address": "TP.HCM"
}
```

```text
GET    /api/order
GET    /api/order/detail/1
DELETE /api/order/cancel/1
```

### Tài khoản

```text
POST /api/account/register
POST /api/account/login
GET  /api/account/me
PUT  /api/account/profile
POST /api/account/logout
```

Tài khoản test:

```text
admin / 123456
user / 123456
```

## 3. Kết luận đối chiếu Yêu cầu 1

- API bắt đầu bằng `/api`.
- API trả JSON bằng `json_encode`.
- Frontend `app/views/index.html` gọi API bằng `fetch()`.
- Form HTML không submit trực tiếp về PHP MVC, tất cả đều `event.preventDefault()` rồi gọi API.
- Các thao tác thêm/sửa/xóa dùng HTTP Method chuẩn: `POST`, `PUT`, `DELETE`.
