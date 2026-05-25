# ShopAdmin — PHP MVC Website

## Cấu trúc thư mục

```
website/
├── app/
│   ├── config/
│   │   └── database.php          # Kết nối PDO (Singleton)
│   ├── controllers/
│   │   ├── DefaultController.php  # Trang tổng quan
│   │   ├── CategoryController.php # CRUD danh mục
│   │   └── ProductController.php  # CRUD sản phẩm + upload ảnh
│   ├── models/
│   │   ├── CategoryModel.php
│   │   └── ProductModel.php       # Phân trang, tìm kiếm, lọc
│   └── views/
│       ├── home.php               # Dashboard
│       ├── category/
│       │   ├── list.php / add.php / edit.php
│       └── product/
│           ├── list.php / add.php / edit.php / show.php
│       └── shares/
│           ├── header.php
│           └── footer.php
├── public/
│   ├── css/style.css
│   ├── js/app.js
│   └── uploads/                   # Ảnh sản phẩm (auto-created)
├── .htaccess
├── index.php                      # Front controller
└── database.sql                   # SQL khởi tạo DB + dữ liệu mẫu
```

## Cài đặt

### 1. Yêu cầu
- PHP >= 8.1
- MySQL / MariaDB
- Apache với mod_rewrite (XAMPP / Laragon / WAMP)

### 2. Tạo database
```bash
mysql -u root -p < database.sql
```
Hoặc mở `database.sql` trong phpMyAdmin và chạy.

### 3. Cấu hình kết nối
Mở `app/config/database.php` và sửa:
```php
private $host     = 'localhost';
private $dbname   = 'shop_db';
private $username = 'root';
private $password = '';   // mật khẩu MySQL của bạn
```

### 4. Đặt thư mục vào web root
- XAMPP: `C:/xampp/htdocs/website/`
- Laragon: `C:/laragon/www/website/`

Truy cập: `http://localhost/website/`

## Tính năng

| Chức năng             | Mô tả                                            |
|-----------------------|--------------------------------------------------|
| Dashboard             | Thống kê tổng quan, sản phẩm mới nhất           |
| Quản lý sản phẩm      | Thêm / Sửa / Xóa / Xem chi tiết + upload ảnh   |
| Quản lý danh mục      | Thêm / Sửa / Xóa                                |
| Tìm kiếm & Lọc        | Tìm theo tên, lọc theo danh mục                 |
| Phân trang            | 9 sản phẩm / trang                              |
| Upload ảnh            | Kéo thả hoặc chọn file, preview trực tiếp       |
| Responsive            | Hỗ trợ mobile với sidebar toggle                |

## Bảo mật
- Tất cả output đều được `htmlspecialchars()`
- Query dùng Prepared Statements (PDO)
- Chỉ cho phép controller trong whitelist
