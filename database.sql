CREATE DATABASE IF NOT EXISTS My_Store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE My_Store;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_details;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS cart;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(250) NOT NULL,
    description TEXT,
    price DECIMAL(15,2) NOT NULL DEFAULT 0,
    stock INT NOT NULL DEFAULT 0,
    category_id INT NULL,
    image VARCHAR(300) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_categories FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    status TINYINT(1) NOT NULL DEFAULT 1,
    avatar VARCHAR(255) NULL,
    reset_token VARCHAR(255) NULL,
    token_expiry DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE cart (
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    qty INT NOT NULL DEFAULT 1,
    PRIMARY KEY (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_address TEXT NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    order_status ENUM('pending','confirmed','shipping','completed','cancelled') NOT NULL DEFAULT 'pending',
    payment_status ENUM('unpaid','cod_pending','paid') NOT NULL DEFAULT 'unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    method ENUM('cod','bank_transfer','wallet') NOT NULL DEFAULT 'cod',
    amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    status ENUM('cod_pending','paid') NOT NULL DEFAULT 'cod_pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO categories (name, description) VALUES
('Điện thoại', 'Smartphone, điện thoại thông minh'),
('Máy tính bảng', 'iPad, tablet các loại'),
('Laptop', 'Máy tính xách tay'),
('Phụ kiện', 'Tai nghe, sạc, ốp lưng');

INSERT INTO products (name, description, price, stock, category_id, image) VALUES
('iPhone 15 Pro Max', 'Chip A17 Pro, camera 48MP, titanium', 34990000, 25, 1, '1779696697_iphone17prm.jpg'),
('Samsung Galaxy S24 Ultra', 'S Pen, AI, camera 200MP', 28990000, 18, 1, '1779696618_samsungs25u.jpg'),
('iPad Pro M4 11 inch', 'Chip M4, màn OLED, Wi-Fi 6E', 25990000, 12, 2, NULL),
('MacBook Air M3', 'Chip M3, 8GB RAM, 256GB SSD', 32990000, 8, 3, '1779696769_macbook.jpg'),
('Dell Inspiron 15', 'Laptop văn phòng, màn 15.6 inch', 15990000, 20, 3, '1779696849_dell15.jpg'),
('Bàn phím cơ', 'Bàn phím cơ LED RGB', 1290000, 40, 4, '1779696896_banphim.jpg'),
('Chuột gaming', 'Chuột gaming DPI cao', 690000, 50, 4, '1779696938_chuot.jpg');

-- Password mặc định của 2 tài khoản bên dưới: 123456
INSERT INTO users (username, email, password, role, status) VALUES
('admin', 'admin@webstore.local', '$2y$12$qbQHA/6DXpqM1pNkc7IBbuA/QNyykW/xt.9g/3VMLnW54tuBg2b7K', 'admin', 1),
('user', 'user@webstore.local', '$2y$12$qbQHA/6DXpqM1pNkc7IBbuA/QNyykW/xt.9g/3VMLnW54tuBg2b7K', 'user', 1);
