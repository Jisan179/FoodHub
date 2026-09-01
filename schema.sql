-- FoodHub Database Schema & Initial Seed Data
-- Compatible with MySQL 5.7+ / MariaDB 10.4+

CREATE DATABASE IF NOT EXISTS foodhub_db;
USE foodhub_db;

-- 1. Users Table
DROP TABLE IF EXISTS delivery_status_history;
DROP TABLE IF EXISTS deliveries;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS food_items;
DROP TABLE IF EXISTS restaurants;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Customer', 'Restaurant Manager', 'Rider') NOT NULL DEFAULT 'Customer',
    address TEXT,
    phone VARCHAR(20),
    status ENUM('Active', 'Inactive', 'Banned') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Restaurants Table
CREATE TABLE restaurants (
    restaurant_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    address VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    status ENUM('Pending', 'Approved', 'Rejected', 'Suspended') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Food Items Table
CREATE TABLE food_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(50) DEFAULT 'Main Course',
    status ENUM('Available', 'Unavailable') DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(restaurant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Orders Table
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    restaurant_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    order_status ENUM('Pending', 'Preparing', 'Ready for Delivery', 'Out for Delivery', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    delivery_address TEXT NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'Cash on Delivery',
    payment_status ENUM('Unpaid', 'Paid') DEFAULT 'Unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(restaurant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Order Items Table
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES food_items(item_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Deliveries Table
CREATE TABLE deliveries (
    delivery_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    rider_id INT NULL,
    delivery_status ENUM('Pending Assignment', 'Assigned', 'Picked Up', 'Delivered', 'Cancelled') DEFAULT 'Pending Assignment',
    assigned_at TIMESTAMP NULL,
    pickup_time TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    rider_earning DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    rider_note VARCHAR(500) NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (rider_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Delivery Status History Table
CREATE TABLE delivery_status_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    delivery_id INT NOT NULL,
    rider_id INT NULL,
    status ENUM('Pending Assignment', 'Assigned', 'Picked Up', 'Delivered', 'Cancelled') NOT NULL,
    note VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (delivery_id) REFERENCES deliveries(delivery_id) ON DELETE CASCADE,
    FOREIGN KEY (rider_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================================
-- SEED DATA
-- ==========================================================

-- Insert Users
INSERT INTO users (user_id, name, username, email, password, role, address, phone, status) VALUES
(1, 'System Administrator', 'admin', 'admin@foodhub.com', 'admin123', 'Admin', 'FoodHub HQ, Dhaka', '+8801700000000', 'Active'),
(2, 'John Doe', 'customer1', 'john@example.com', 'customer123', 'Customer', 'House 12, Road 5, Dhanmondi, Dhaka', '+8801700000001', 'Active'),
(3, 'Rahim Ahmed', 'manager1', 'rahim@spicegrill.com', 'manager123', 'Restaurant Manager', 'Gulshan Avenue, Dhaka', '+8801700000002', 'Active'),
(4, 'Karim Khan', 'rider1', 'karim@riderhub.com', 'rider123', 'Rider', 'Banani Model Town, Dhaka', '+8801700000003', 'Active'),
(5, 'Sarah Jenkins', 'customer2', 'sarah@example.com', 'customer123', 'Customer', 'Block C, Bashundhara R/A, Dhaka', '+8801700000004', 'Active'),
(6, 'Tariqul Islam', 'manager2', 'tariq@burgerspot.com', 'manager123', 'Restaurant Manager', 'Uttara Sector 3, Dhaka', '+8801700000005', 'Active');

-- Insert Restaurants
INSERT INTO restaurants (restaurant_id, user_id, name, description, address, phone, status) VALUES
(1, 3, 'Spice Grill House', 'Authentic traditional South Asian grilled specialties and rich biryanis.', 'Plot 45, Road 11, Gulshan 1, Dhaka', '+8801811111111', 'Approved'),
(2, 6, 'The Burger Spot', 'Gourmet artisan smash burgers, crispy fries and thick milkshakes.', 'House 22, Sector 3, Uttara, Dhaka', '+8801822222222', 'Pending');

-- Insert Food Items
INSERT INTO food_items (item_id, restaurant_id, name, description, price, category, status) VALUES
(1, 1, 'Chicken Dum Biryani', 'Aromatic basmati rice cooked with tender marinated chicken and special spices.', 8.50, 'Main Course', 'Available'),
(2, 1, 'Beef Seekh Kebab Wrap', 'Charcoal grilled spiced beef seekh rolled in fresh butter naan with mint chutney.', 6.00, 'Appetizer', 'Available'),
(3, 1, 'Mutton Kacchi Biryani', 'Traditional wedding style kacchi biryani with succulent mutton pieces and potatoes.', 12.00, 'Main Course', 'Available'),
(4, 2, 'Double Cheeseburger', 'Two 100% prime beef patties with melted cheddar, pickles, and signature sauce.', 7.50, 'Burgers', 'Available'),
(5, 2, 'Loaded Truffle Fries', 'Crispy skin-on golden fries topped with parmesan cheese and truffle mayo.', 4.50, 'Sides', 'Available');

-- Insert Orders (Pending Order #1)
INSERT INTO orders (order_id, customer_id, restaurant_id, total_amount, order_status, delivery_address, payment_method, payment_status) VALUES
(1, 2, 1, 14.50, 'Pending', 'House 12, Road 5, Dhanmondi, Dhaka', 'Cash on Delivery', 'Unpaid'),
(2, 5, 1, 20.50, 'Preparing', 'Block C, Bashundhara R/A, Dhaka', 'Cash on Delivery', 'Unpaid'),
(3, 2, 2, 12.00, 'Delivered', 'House 12, Road 5, Dhanmondi, Dhaka', 'Cash on Delivery', 'Paid');

-- Insert Order Items
INSERT INTO order_items (order_item_id, order_id, item_id, quantity, price, subtotal) VALUES
(1, 1, 1, 1, 8.50, 8.50),
(2, 1, 2, 1, 6.00, 6.00),
(3, 2, 1, 1, 8.50, 8.50),
(4, 2, 3, 1, 12.00, 12.00),
(5, 3, 4, 1, 7.50, 7.50),
(6, 3, 5, 1, 4.50, 4.50);

-- Insert Deliveries
INSERT INTO deliveries (delivery_id, order_id, rider_id, delivery_status, assigned_at, delivered_at) VALUES
(1, 1, 4, 'Assigned', NOW(), NULL),
(2, 2, 4, 'Assigned', NOW(), NULL),
(3, 3, 4, 'Delivered', NOW() - INTERVAL 2 HOUR, NOW() - INTERVAL 1 HOUR);

INSERT INTO delivery_status_history (delivery_id, rider_id, status, note)
SELECT delivery_id, rider_id, delivery_status, 'Initial delivery status'
FROM deliveries;

UPDATE deliveries d
JOIN orders o ON o.order_id = d.order_id
SET d.rider_earning = ROUND(o.total_amount * 0.10, 2)
WHERE d.delivery_status = 'Delivered';

-- Rider Demo Deliveries for CRUD Testing
INSERT INTO orders (order_id, customer_id, restaurant_id, total_amount, order_status, delivery_address, payment_method, payment_status) VALUES
(4, 5, 1, 18.50, 'Ready for Delivery', 'Block C, Bashundhara R/A, Dhaka', 'Cash on Delivery', 'Unpaid'),
(5, 2, 1, 22.00, 'Ready for Delivery', 'House 12, Road 5, Dhanmondi, Dhaka', 'Cash on Delivery', 'Unpaid'),
(6, 5, 1, 16.00, 'Out for Delivery', 'Road 7, Banani, Dhaka', 'Cash on Delivery', 'Unpaid');

INSERT INTO deliveries (delivery_id, order_id, rider_id, delivery_status, assigned_at, pickup_time) VALUES
(4, 4, NULL, 'Pending Assignment', NULL, NULL),
(5, 5, 4, 'Assigned', NOW(), NULL),
(6, 6, 4, 'Picked Up', NOW() - INTERVAL 30 MINUTE, NOW() - INTERVAL 10 MINUTE);

INSERT INTO delivery_status_history (delivery_id, rider_id, status, note) VALUES
(4, NULL, 'Pending Assignment', 'Demo offer available for acceptance'),
(5, 4, 'Assigned', 'Demo delivery assigned to rider'),
(6, 4, 'Assigned', 'Demo delivery assigned to rider'),
(6, 4, 'Picked Up', 'Demo food pickup confirmed');

-- Rider Verification Batch 2
INSERT INTO orders (order_id, customer_id, restaurant_id, total_amount, order_status, delivery_address, payment_method, payment_status) VALUES
(7, 2, 1, 19.50, 'Ready for Delivery', 'House 8, Road 12, Dhanmondi, Dhaka', 'Cash on Delivery', 'Unpaid'),
(8, 5, 1, 24.00, 'Ready for Delivery', 'Apartment 4B, Gulshan 2, Dhaka', 'Cash on Delivery', 'Unpaid'),
(9, 2, 2, 15.50, 'Out for Delivery', 'Sector 7, Uttara, Dhaka', 'Cash on Delivery', 'Unpaid');

INSERT INTO deliveries (delivery_id, order_id, rider_id, delivery_status, assigned_at, pickup_time) VALUES
(7, 7, NULL, 'Pending Assignment', NULL, NULL),
(8, 8, 4, 'Assigned', NOW(), NULL),
(9, 9, 4, 'Picked Up', NOW() - INTERVAL 25 MINUTE, NOW() - INTERVAL 8 MINUTE);

INSERT INTO delivery_status_history (delivery_id, rider_id, status, note) VALUES
(7, NULL, 'Pending Assignment', 'Verification offer available for acceptance'),
(8, 4, 'Assigned', 'Verification delivery assigned to rider'),
(9, 4, 'Assigned', 'Verification delivery assigned to rider'),
(9, 4, 'Picked Up', 'Verification food pickup confirmed');
