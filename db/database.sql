-- ============================================================
-- ONLINE COMPUTER STORE - COMPLETE DATABASE SETUP
-- ============================================================
-- This file creates all tables and populates them with sample data
-- Database: computer_store
-- Created: December 2025
-- ============================================================

-- Create database (if not exists)
CREATE DATABASE IF NOT EXISTS computer_store;
USE computer_store;

-- ============================================================
-- TABLE STRUCTURES
-- ============================================================

-- Table: users
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: products
DROP TABLE IF EXISTS products;
CREATE TABLE products (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255),
    category VARCHAR(100),
    stock INT(11) NOT NULL DEFAULT 0,
    specifications TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: cart
DROP TABLE IF EXISTS cart;
CREATE TABLE cart (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cart_item (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: wishlist
DROP TABLE IF EXISTS wishlist;
CREATE TABLE wishlist (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wishlist_item (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: orders
DROP TABLE IF EXISTS orders;
CREATE TABLE orders (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    shipping_name VARCHAR(100) NOT NULL,
    shipping_email VARCHAR(100) NOT NULL,
    shipping_address VARCHAR(255) NOT NULL,
    shipping_city VARCHAR(100) NOT NULL,
    shipping_state VARCHAR(100) NOT NULL,
    shipping_zip VARCHAR(20) NOT NULL,
    shipping_phone VARCHAR(20),
    billing_name VARCHAR(100),
    billing_address VARCHAR(255),
    billing_city VARCHAR(100),
    billing_state VARCHAR(100),
    billing_zip VARCHAR(20),
    payment_method VARCHAR(50),
    status VARCHAR(50) DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: order_items
DROP TABLE IF EXISTS order_items;
CREATE TABLE order_items (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: reviews
DROP TABLE IF EXISTS reviews;
CREATE TABLE reviews (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    product_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    rating INT(11) NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SAMPLE DATA - USERS
-- ============================================================

-- Admin user (Password: admin123)
INSERT INTO users (name, email, password, is_admin) VALUES 
('Admin User', 'admin@computerstore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Regular test user (Password: user123)
INSERT INTO users (name, email, password, is_admin) VALUES 
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0);

-- Sample customer users for reviews (Password: password123)
INSERT INTO users (name, email, password, is_admin) VALUES 
('Sarah Johnson', 'sarah.j@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0),
('Michael Chen', 'michael.c@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0),
('Emily Davis', 'emily.d@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0),
('James Wilson', 'james.w@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0),
('Jessica Martinez', 'jessica.m@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0),
('David Brown', 'david.b@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0),
('Amanda Garcia', 'amanda.g@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0),
('Robert Taylor', 'robert.t@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0);

-- ============================================================
-- SAMPLE DATA - PRODUCTS
-- ============================================================

INSERT INTO products (name, description, price, image_url, category, stock, specifications) VALUES 
('Dell XPS 13 Laptop', 'Ultra-portable 13-inch laptop with InfinityEdge display, Intel Core i7 processor, 16GB RAM, and 512GB SSD. Perfect for professionals on the go.', 1299.99, 'assets/images/laptop-1.jpg', 'laptop', 15, '{"processor": "Intel Core i7-1165G7", "ram": "16GB LPDDR4", "storage": "512GB SSD", "display": "13.4-inch FHD+", "graphics": "Intel Iris Xe", "battery": "Up to 12 hours"}'),

('Gaming Desktop RTX 4070', 'High-performance gaming desktop featuring NVIDIA RTX 4070, AMD Ryzen 7 processor, 32GB RAM, and RGB lighting. Ready for 4K gaming.', 1899.99, 'assets/images/desktop-1.jpg', 'desktop', 8, '{"processor": "AMD Ryzen 7 5800X", "ram": "32GB DDR4", "storage": "1TB NVMe SSD + 2TB HDD", "graphics": "NVIDIA RTX 4070 12GB", "cooling": "Liquid cooling", "case": "RGB Tempered Glass"}'),

('Samsung 27-inch 4K Monitor', 'Stunning 4K UHD display with HDR10 support, 99% sRGB color accuracy, and ergonomic stand. Ideal for content creators and professionals.', 449.99, 'assets/images/monitor-1.jpg', 'monitor', 25, '{"resolution": "3840x2160 (4K)", "size": "27 inches", "panel": "IPS", "refresh_rate": "60Hz", "response_time": "5ms", "hdr": "HDR10", "ports": "HDMI 2.0, DisplayPort 1.4, USB-C"}'),

('Mechanical Gaming Keyboard RGB', 'Premium mechanical keyboard with Cherry MX Red switches, per-key RGB lighting, aluminum frame, and programmable macro keys.', 129.99, 'assets/images/keyboard-1.jpg', 'keyboard', 40, '{"switch_type": "Cherry MX Red", "backlighting": "Per-key RGB", "connectivity": "USB-C Wired", "layout": "Full-size (104 keys)", "features": "Programmable macros, Media controls", "build": "Aluminum top plate"}'),

('Logitech MX Master 3S Mouse', 'Advanced wireless mouse with MagSpeed scroll wheel, precision tracking on any surface, and up to 70 days battery life. Ergonomic design for all-day comfort.', 99.99, 'assets/images/mouse-1.jpg', 'mouse', 50, '{"sensor": "8000 DPI", "connectivity": "Bluetooth, USB-C", "battery": "Up to 70 days", "buttons": "7 programmable buttons", "scroll": "MagSpeed electromagnetic", "compatibility": "Windows, Mac, Linux"}'),

('Sony WH-1000XM5 Headphones', 'Industry-leading noise cancellation with premium sound quality. 30-hour battery life, multipoint connection, and adaptive sound control.', 399.99, 'assets/images/headphones-1.jpg', 'headphones', 30, '{"type": "Over-ear wireless", "noise_cancellation": "Industry-leading ANC", "battery": "30 hours", "connectivity": "Bluetooth 5.2, NFC", "drivers": "30mm", "features": "Speak-to-chat, Quick Attention"}'),

('Samsung 2TB Portable SSD', 'Ultra-fast portable SSD with read speeds up to 1050MB/s. Compact, durable design with password protection and AES 256-bit encryption.', 199.99, 'assets/images/storage-1.jpg', 'storage', 60, '{"capacity": "2TB", "interface": "USB 3.2 Gen 2", "read_speed": "1050 MB/s", "write_speed": "1000 MB/s", "encryption": "AES 256-bit hardware", "compatibility": "Windows, Mac, Android"}'),

('NVIDIA GeForce RTX 4090', 'Ultimate graphics card for gaming and content creation. 24GB GDDR6X memory, ray tracing, DLSS 3, and 4K 144Hz gaming capability.', 1599.99, 'assets/images/gpu-1.jpg', 'graphics-card', 12, '{"memory": "24GB GDDR6X", "cuda_cores": "16384", "boost_clock": "2.52 GHz", "tdp": "450W", "outputs": "3x DisplayPort 1.4a, 1x HDMI 2.1", "cooling": "Triple-fan design"}');

-- ============================================================
-- SAMPLE DATA - REVIEWS
-- ============================================================

-- Reviews for Dell XPS 13 Laptop
INSERT INTO reviews (product_id, user_id, rating, comment, created_at) VALUES 
(1, 3, 5, 'Absolutely love this laptop! Exceeded all my expectations. The display is stunning and battery life is incredible.', DATE_SUB(NOW(), INTERVAL 15 DAY)),
(1, 4, 5, 'Best laptop I have ever owned. Build quality is exceptional and performance is outstanding.', DATE_SUB(NOW(), INTERVAL 30 DAY)),
(1, 5, 4, 'Great laptop overall. Only complaint is the limited ports, but the performance makes up for it.', DATE_SUB(NOW(), INTERVAL 45 DAY));

-- Reviews for Gaming Desktop RTX 4070
INSERT INTO reviews (product_id, user_id, rating, comment, created_at) VALUES 
(2, 6, 5, 'This gaming PC is a beast! Runs all my games at max settings with no issues.', DATE_SUB(NOW(), INTERVAL 20 DAY)),
(2, 7, 5, 'Outstanding performance and the RGB lighting looks amazing. Worth every penny.', DATE_SUB(NOW(), INTERVAL 35 DAY)),
(2, 8, 4, 'Very powerful gaming desktop. Setup was easy and it has been running flawlessly.', DATE_SUB(NOW(), INTERVAL 50 DAY));

-- Reviews for Samsung 27-inch 4K Monitor
INSERT INTO reviews (product_id, user_id, rating, comment, created_at) VALUES 
(3, 9, 5, 'Perfect monitor for photo editing. Colors are accurate and the 4K resolution is crisp.', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(3, 10, 4, 'Really good monitor for the price. HDR looks great and build quality is solid.', DATE_SUB(NOW(), INTERVAL 25 DAY)),
(3, 3, 5, 'Excellent display! The stand is very adjustable and the picture quality is stunning.', DATE_SUB(NOW(), INTERVAL 40 DAY)),
(3, 4, 4, 'Great value for a 4K monitor. Only wish the refresh rate was higher for gaming.', DATE_SUB(NOW(), INTERVAL 60 DAY));

-- Reviews for Mechanical Gaming Keyboard
INSERT INTO reviews (product_id, user_id, rating, comment, created_at) VALUES 
(4, 5, 5, 'Best keyboard I have used. Cherry MX Reds feel amazing and the RGB is customizable.', DATE_SUB(NOW(), INTERVAL 18 DAY)),
(4, 6, 4, 'Solid mechanical keyboard. Build quality is premium and typing experience is great.', DATE_SUB(NOW(), INTERVAL 33 DAY));

-- Reviews for Logitech MX Master 3S Mouse
INSERT INTO reviews (product_id, user_id, rating, comment, created_at) VALUES 
(5, 7, 5, 'Most comfortable mouse I have ever used. The ergonomics are perfect for long work sessions.', DATE_SUB(NOW(), INTERVAL 12 DAY)),
(5, 8, 5, 'Absolutely fantastic mouse. Battery life is incredible and the scroll wheel is amazing.', DATE_SUB(NOW(), INTERVAL 28 DAY)),
(5, 9, 4, 'Very good mouse with great features. Would recommend for productivity work.', DATE_SUB(NOW(), INTERVAL 55 DAY));

-- Reviews for Sony WH-1000XM5 Headphones
INSERT INTO reviews (product_id, user_id, rating, comment, created_at) VALUES 
(6, 10, 5, 'The noise cancellation is phenomenal. Sound quality is excellent and very comfortable.', DATE_SUB(NOW(), INTERVAL 22 DAY)),
(6, 3, 5, 'Best headphones for travel. The ANC blocks everything and battery lasts forever.', DATE_SUB(NOW(), INTERVAL 38 DAY)),
(6, 4, 4, 'Great headphones overall. Sound is good and comfort is excellent for long listening.', DATE_SUB(NOW(), INTERVAL 65 DAY));

-- Reviews for Samsung 2TB Portable SSD
INSERT INTO reviews (product_id, user_id, rating, comment, created_at) VALUES 
(7, 5, 5, 'Super fast and very compact. Perfect for backing up my files and transferring large projects.', DATE_SUB(NOW(), INTERVAL 14 DAY)),
(7, 6, 4, 'Excellent portable SSD. Transfer speeds are impressive and it is very durable.', DATE_SUB(NOW(), INTERVAL 42 DAY));

-- Reviews for NVIDIA GeForce RTX 4090
INSERT INTO reviews (product_id, user_id, rating, comment, created_at) VALUES 
(8, 7, 5, 'Ultimate graphics card! Handles 4K gaming effortlessly and ray tracing looks incredible.', DATE_SUB(NOW(), INTERVAL 8 DAY)),
(8, 8, 5, 'Worth the investment. This GPU is future-proof and performance is unmatched.', DATE_SUB(NOW(), INTERVAL 24 DAY)),
(8, 9, 4, 'Incredible performance but runs a bit hot. Make sure you have good cooling.', DATE_SUB(NOW(), INTERVAL 48 DAY));

-- ============================================================
-- DATABASE SETUP COMPLETE
-- ============================================================
-- You can now access the application at: http://localhost/Project/
-- Admin Login: admin@computerstore.com / admin123
-- User Login: john@example.com / user123
-- ============================================================
