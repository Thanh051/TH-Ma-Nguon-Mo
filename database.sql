-- ============================================================
-- ShopAdmin — Database Setup
-- Chạy file này trong phpMyAdmin hoặc MySQL CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS shop_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE shop_db;

-- Danh mục
CREATE TABLE IF NOT EXISTS categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150)    NOT NULL,
    description TEXT,
    created_at  DATETIME        DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sản phẩm
CREATE TABLE IF NOT EXISTS products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(250)    NOT NULL,
    description TEXT,
    price       DECIMAL(15,2)   NOT NULL DEFAULT 0,
    stock       INT             NOT NULL DEFAULT 0,
    category_id INT,
    image       VARCHAR(300),
    created_at  DATETIME        DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dữ liệu mẫu
INSERT INTO categories (name, description) VALUES
    ('Điện thoại',    'Smartphone, điện thoại thông minh'),
    ('Máy tính bảng', 'iPad, tablet các loại'),
    ('Laptop',        'Máy tính xách tay'),
    ('Phụ kiện',      'Tai nghe, sạc, ốp lưng…');

INSERT INTO products (name, description, price, stock, category_id) VALUES
    ('iPhone 15 Pro Max', 'Chip A17 Pro, camera 48MP, titanium', 34990000, 25, 1),
    ('Samsung Galaxy S24 Ultra', 'S Pen, AI, camera 200MP',       28990000, 18, 1),
    ('iPad Pro M4 11"',  'Chip M4, màn OLED, Wi-Fi 6E',           25990000, 12, 2),
    ('MacBook Air M3',   'Chip M3, 8GB RAM, 256GB SSD',            32990000, 8,  3),
    ('AirPods Pro 2',    'Chống ồn chủ động, USB-C',               6490000,  40, 4),
    ('Samsung Galaxy Tab S9', 'AMOLED 120Hz, IP68',                18990000, 10, 2);

-- Bảng lưu thông tin đơn hàng và khách hàng
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_address TEXT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng lưu chi tiết các sản phẩm trong đơn hàng đó
CREATE TABLE order_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);