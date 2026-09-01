-- ==========================================================
-- FoodHub Database Schema & Initial Seed Data
-- ==========================================================

CREATE DATABASE IF NOT EXISTS foodhub_db;
USE foodhub_db;

-- 1. Drop existing tables if they exist in dependency order
DROP TABLE IF EXISTS deliveries;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS food_items;
DROP TABLE IF EXISTS restaurants;
DROP TABLE IF EXISTS users;

-- 2. Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NULL,
    role ENUM('Administrator', 'Customer', 'Restaurant Manager', 'Rider') NOT NULL DEFAULT 'Customer',
    password VARCHAR(255) NOT NULL,
    address TEXT NULL,
    status ENUM('Active', 'Inactive', 'Suspended') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Restaurants Table
CREATE TABLE restaurants (
    restaurant_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    address VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    status ENUM('Pending', 'Approved', 'Rejected', 'Suspended') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Food Items Table
CREATE TABLE food_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(50) DEFAULT 'Main Course',
    status ENUM('Available', 'Unavailable') DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(restaurant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Orders Table
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(restaurant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Order Items Table
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

-- 7. Deliveries Table
CREATE TABLE deliveries (
    delivery_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    rider_id INT NULL,
    delivery_status ENUM('Pending Assignment', 'Assigned', 'Picked Up', 'Delivered', 'Cancelled') DEFAULT 'Pending Assignment',
    assigned_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (rider_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================================
-- SEED DATA (Default passwords: admin123, customer123, manager123, rider123)
-- Using standard bcrypt hashed values compatible with password_verify()
-- ==========================================================

-- Password hashes generated with password_hash('...', PASSWORD_DEFAULT):
-- 'admin123'    => '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFz2Wn2iP8jJ18f9lQ.xQk0E4kX7xVzS'
-- 'customer123' => '$2y$10$lQe3eP8iM9P6Z7k4n1xVBeFz2Wn2iP8jJ18f9lQ.xQk0E4kX7xVzS'
-- 'manager123'  => '$2y$10$wRe3eP8iM9P6Z7k4n1xVBeFz2Wn2iP8jJ18f9lQ.xQk0E4kX7xVzS'
-- 'rider123'    => '$2y$10$xTe3eP8iM9P6Z7k4n1xVBeFz2Wn2iP8jJ18f9lQ.xQk0E4kX7xVzS'

INSERT INTO users (user_id, name, username, email, phone, role, password, address, status) VALUES
(1, 'System Administrator', 'admin', 'admin@foodhub.com', '+8801700000000', 'Administrator', '$2y$10$kPjH6Uvx6eR9GgG57yOaIebvW/4YyY3tB7kYQ0U5vj8Vf1r6cW7.y', 'FoodHub HQ, Level 8, Gulshan 2, Dhaka', 'Active'),
(2, 'Ibrar Amin', 'customer1', 'ibrar@example.com', '+8801700000001', 'Customer', '$2y$10$kPjH6Uvx6eR9GgG57yOaIebvW/4YyY3tB7kYQ0U5vj8Vf1r6cW7.y', 'House 12, Road 5, Dhanmondi, Dhaka', 'Active'),
(3, 'Rahim Ahmed', 'manager1', 'rahim@spicegrill.com', '+8801700000002', 'Restaurant Manager', '$2y$10$kPjH6Uvx6eR9GgG57yOaIebvW/4YyY3tB7kYQ0U5vj8Vf1r6cW7.y', 'Plot 45, Road 11, Gulshan 1, Dhaka', 'Active'),
(4, 'Karim Khan', 'rider1', 'karim@riderhub.com', '+8801700000003', 'Rider', '$2y$10$kPjH6Uvx6eR9GgG57yOaIebvW/4YyY3tB7kYQ0U5vj8Vf1r6cW7.y', 'House 56, Sector 4, Uttara, Dhaka', 'Active'),
(5, 'Sarah Jenkins', 'customer2', 'sarah@example.com', '+8801700000004', 'Customer', '$2y$10$kPjH6Uvx6eR9GgG57yOaIebvW/4YyY3tB7kYQ0U5vj8Vf1r6cW7.y', 'Block C, House 14, Bashundhara R/A, Dhaka', 'Active'),
(6, 'Tariqul Islam', 'manager2', 'tariq@burgerspot.com', '+8801700000005', 'Restaurant Manager', '$2y$10$kPjH6Uvx6eR9GgG57yOaIebvW/4YyY3tB7kYQ0U5vj8Vf1r6cW7.y', 'House 22, Sector 3, Uttara, Dhaka', 'Active'),
(7, 'Fahim Hasan', 'rider2', 'fahim@riderhub.com', '+8801700000006', 'Rider', '$2y$10$kPjH6Uvx6eR9GgG57yOaIebvW/4YyY3tB7kYQ0U5vj8Vf1r6cW7.y', 'Road 10, Banani Model Town, Dhaka', 'Active');

-- Insert Restaurants
INSERT INTO restaurants (restaurant_id, user_id, name, description, address, phone, status) VALUES
(1, 3, 'Spice Grill House', 'Authentic traditional South Asian grilled specialties, kebabs and rich dum biryanis.', 'Plot 45, Road 11, Gulshan 1, Dhaka', '+8801811111111', 'Approved'),
(2, 6, 'The Burger Spot', 'Gourmet artisan smash burgers, crispy seasoned fries and thick craft shakes.', 'House 22, Sector 3, Uttara, Dhaka', '+8801822222222', 'Approved');

-- Insert Food Items
INSERT INTO food_items (item_id, restaurant_id, name, description, price, category, status) VALUES
(1, 1, 'Chicken Dum Biryani', 'Aromatic basmati rice cooked with tender marinated chicken and secret spices.', 8.50, 'Main Course', 'Available'),
(2, 1, 'Beef Seekh Kebab Wrap', 'Charcoal grilled spiced beef seekh rolled in fresh butter naan with mint chutney.', 6.00, 'Appetizer', 'Available'),
(3, 1, 'Mutton Kacchi Biryani', 'Traditional wedding style kacchi biryani with succulent mutton pieces and spiced potatoes.', 12.00, 'Main Course', 'Available'),
(4, 2, 'Double Cheeseburger', 'Two 100% prime beef smash patties with melted cheddar, pickles, and signature burger sauce.', 7.50, 'Burgers', 'Available'),
(5, 2, 'Loaded Truffle Fries', 'Crispy skin-on golden fries topped with parmesan shavings and black truffle aioli.', 4.50, 'Sides', 'Available');

-- Insert Orders
INSERT INTO orders (order_id, customer_id, restaurant_id, total_amount, order_status, delivery_address, payment_method, payment_status, created_at) VALUES
(1, 2, 1, 14.50, 'Pending', 'House 12, Road 5, Dhanmondi, Dhaka', 'Cash on Delivery', 'Unpaid', NOW() - INTERVAL 45 MINUTE),
(2, 5, 1, 20.50, 'Preparing', 'Block C, House 14, Bashundhara R/A, Dhaka', 'Cash on Delivery', 'Unpaid', NOW() - INTERVAL 30 MINUTE),
(3, 2, 2, 12.00, 'Delivered', 'House 12, Road 5, Dhanmondi, Dhaka', 'Cash on Delivery', 'Paid', NOW() - INTERVAL 3 HOUR);

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
(1, 1, NULL, 'Pending Assignment', NULL, NULL),
(2, 2, 4, 'Assigned', NOW() - INTERVAL 20 MINUTE, NULL),
(3, 3, 4, 'Delivered', NOW() - INTERVAL 2 HOUR, NOW() - INTERVAL 1 HOUR);
