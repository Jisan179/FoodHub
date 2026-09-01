-- ==========================================================
-- FoodHub Unified Database Schema & Initial Seed Data
-- ==========================================================

CREATE DATABASE IF NOT EXISTS foodhub_db;
USE foodhub_db;

-- 1. Drop existing tables if they exist in dependency order
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS delivery_status_history;
DROP TABLE IF EXISTS deliveries;
DROP TABLE IF EXISTS order_status_log;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS food_items;
DROP TABLE IF EXISTS restaurant_managers;
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
    cuisine_type VARCHAR(100) DEFAULT 'Traditional',
    status ENUM('Pending', 'Approved', 'Rejected', 'Suspended') DEFAULT 'Pending',
    rejection_reason VARCHAR(255) NULL,
    is_open TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Restaurant Managers Table (Manager Authorization & Mapping)
CREATE TABLE restaurant_managers (
    manager_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    restaurant_id INT NOT NULL,
    role_title VARCHAR(50) DEFAULT 'owner',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(restaurant_id) ON DELETE CASCADE,
    UNIQUE KEY unique_manager_restaurant (user_id, restaurant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Food Items Table
CREATE TABLE food_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(50) DEFAULT 'Main Course',
    status ENUM('Available', 'Unavailable') DEFAULT 'Available',
    is_deleted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(restaurant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Orders Table
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

-- 7. Order Items Table
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

-- 8. Order Status Log Table
CREATE TABLE order_status_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    old_status VARCHAR(30) NULL,
    new_status VARCHAR(30) NOT NULL,
    changed_by INT NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Deliveries Table
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (rider_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Delivery Status History Table
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

-- 11. Favorites Table
CREATE TABLE favorites (
    favorite_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    restaurant_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(restaurant_id) ON DELETE CASCADE,
    UNIQUE KEY unique_customer_restaurant_fav (customer_id, restaurant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. Cart Table
CREATE TABLE cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES food_items(item_id) ON DELETE CASCADE,
    UNIQUE KEY unique_customer_cart_item (customer_id, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. Reviews Table
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_id INT NOT NULL,
    item_id INT NOT NULL,
    rating TINYINT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES food_items(item_id) ON DELETE CASCADE,
    UNIQUE KEY unique_customer_order_item_review (customer_id, order_id, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================================
-- SEED DATA (Default passwords: admin123, customer123, manager123, rider123)
-- ==========================================================

INSERT INTO users (user_id, name, username, email, phone, role, password, address, status) VALUES
(1, 'System Administrator', 'admin', 'admin@foodhub.com', '+8801700000000', 'Administrator', '$2y$10$MH0BCzGFT.TGMwIzsbVk5OFUtGMjPkSYVNP.03jtEpMeTrYzGFcKC', 'FoodHub HQ, Level 8, Gulshan 2, Dhaka', 'Active'),
(2, 'Ibrar Amin', 'customer1', 'ibrar@example.com', '+8801700000001', 'Customer', '$2y$10$3caFJW8Zsr0NKGD4e8cKHuCRwJ/1wErea3UGWpNvike8j4buY23Cm', 'House 12, Road 5, Dhanmondi, Dhaka', 'Active'),
(3, 'Rahim Ahmed', 'manager1', 'rahim@spicegrill.com', '+8801700000002', 'Restaurant Manager', '$2y$10$MDvRh8U08Uy0uj7c4hCGRuOwyY8KW8O5hQlUr4kFfp6vrkUL7c7hi', 'Plot 45, Road 11, Gulshan 1, Dhaka', 'Active'),
(4, 'Karim Khan', 'rider1', 'karim@riderhub.com', '+8801700000003', 'Rider', '$2y$10$dUYITObmJpjcBrxIhTqp8O4Qm851oHfaKT7YNYBl/xRQ/0/HfkREu', 'House 56, Sector 4, Uttara, Dhaka', 'Active'),
(5, 'Sarah Jenkins', 'customer2', 'sarah@example.com', '+8801700000004', 'Customer', '$2y$10$3caFJW8Zsr0NKGD4e8cKHuCRwJ/1wErea3UGWpNvike8j4buY23Cm', 'Block C, House 14, Bashundhara R/A, Dhaka', 'Active'),
(6, 'Tariqul Islam', 'manager2', 'tariq@burgerspot.com', '+8801700000005', 'Restaurant Manager', '$2y$10$MDvRh8U08Uy0uj7c4hCGRuOwyY8KW8O5hQlUr4kFfp6vrkUL7c7hi', 'House 22, Sector 3, Uttara, Dhaka', 'Active'),
(7, 'Fahim Hasan', 'rider2', 'fahim@riderhub.com', '+8801700000006', 'Rider', '$2y$10$dUYITObmJpjcBrxIhTqp8O4Qm851oHfaKT7YNYBl/xRQ/0/HfkREu', 'Road 10, Banani Model Town, Dhaka', 'Active');

-- Insert Restaurants
INSERT INTO restaurants (restaurant_id, user_id, name, description, address, phone, cuisine_type, status, is_open) VALUES
(1, 3, 'Spice Grill House', 'Authentic traditional South Asian grilled specialties, kebabs and rich dum biryanis.', 'Plot 45, Road 11, Gulshan 1, Dhaka', '+8801811111111', 'Traditional Grill & Biryani', 'Approved', 1),
(2, 6, 'The Burger Spot', 'Gourmet artisan smash burgers, crispy seasoned fries and thick craft shakes.', 'House 22, Sector 3, Uttara, Dhaka', '+8801822222222', 'Burgers & Fast Food', 'Approved', 1);

-- Link Managers to Restaurants
INSERT INTO restaurant_managers (user_id, restaurant_id, role_title) VALUES
(3, 1, 'owner'),
(6, 2, 'owner');

-- Insert Food Items
INSERT INTO food_items (item_id, restaurant_id, name, description, price, category, status, is_deleted) VALUES
(1, 1, 'Chicken Dum Biryani', 'Aromatic basmati rice cooked with tender marinated chicken and secret spices.', 8.50, 'Main Course', 'Available', 0),
(2, 1, 'Beef Seekh Kebab Wrap', 'Charcoal grilled spiced beef seekh rolled in fresh butter naan with mint chutney.', 6.00, 'Appetizer', 'Available', 0),
(3, 1, 'Mutton Kacchi Biryani', 'Traditional wedding style kacchi biryani with succulent mutton pieces and spiced potatoes.', 12.00, 'Main Course', 'Available', 0),
(4, 2, 'Double Cheeseburger', 'Two 100% prime beef smash patties with melted cheddar, pickles, and signature burger sauce.', 7.50, 'Burgers', 'Available', 0),
(5, 2, 'Loaded Truffle Fries', 'Crispy skin-on golden fries topped with parmesan shavings and black truffle aioli.', 4.50, 'Sides', 'Available', 0);

-- Insert Orders
INSERT INTO orders (order_id, customer_id, restaurant_id, total_amount, order_status, delivery_address, payment_method, payment_status, created_at) VALUES
(1, 2, 1, 14.50, 'Pending', 'House 12, Road 5, Dhanmondi, Dhaka', 'Cash on Delivery', 'Unpaid', NOW() - INTERVAL 45 MINUTE),
(2, 5, 1, 20.50, 'Preparing', 'Block C, House 14, Bashundhara R/A, Dhaka', 'Cash on Delivery', 'Unpaid', NOW() - INTERVAL 30 MINUTE),
(3, 2, 2, 12.00, 'Delivered', 'House 12, Road 5, Dhanmondi, Dhaka', 'Cash on Delivery', 'Paid', NOW() - INTERVAL 180 MINUTE),
(4, 5, 1, 18.50, 'Ready for Delivery', 'Block C, House 14, Bashundhara R/A, Dhaka', 'Cash on Delivery', 'Unpaid', NOW() - INTERVAL 20 MINUTE),
(5, 2, 1, 22.00, 'Ready for Delivery', 'House 12, Road 5, Dhanmondi, Dhaka', 'Cash on Delivery', 'Unpaid', NOW() - INTERVAL 15 MINUTE),
(6, 5, 2, 16.00, 'Out for Delivery', 'Road 7, Banani, Dhaka', 'Cash on Delivery', 'Unpaid', NOW() - INTERVAL 10 MINUTE);

-- Insert Order Items
INSERT INTO order_items (order_item_id, order_id, item_id, quantity, price, subtotal) VALUES
(1, 1, 1, 1, 8.50, 8.50),
(2, 1, 2, 1, 6.00, 6.00),
(3, 2, 1, 1, 8.50, 8.50),
(4, 2, 3, 1, 12.00, 12.00),
(5, 3, 4, 1, 7.50, 7.50),
(6, 3, 5, 1, 4.50, 4.50),
(7, 4, 1, 1, 8.50, 8.50),
(8, 4, 2, 1, 6.00, 6.00),
(9, 5, 3, 1, 12.00, 12.00),
(10, 5, 2, 1, 6.00, 6.00),
(11, 6, 4, 1, 7.50, 7.50),
(12, 6, 5, 1, 4.50, 4.50);

-- Insert Order Status Log
INSERT INTO order_status_log (order_id, old_status, new_status, changed_by, changed_at) VALUES
(2, 'Pending', 'Preparing', 3, NOW() - INTERVAL 25 MINUTE),
(3, 'Pending', 'Preparing', 6, NOW() - INTERVAL 170 MINUTE),
(3, 'Preparing', 'Ready for Delivery', 6, NOW() - INTERVAL 150 MINUTE),
(4, 'Pending', 'Preparing', 3, NOW() - INTERVAL 18 MINUTE),
(4, 'Preparing', 'Ready for Delivery', 3, NOW() - INTERVAL 10 MINUTE),
(5, 'Pending', 'Preparing', 3, NOW() - INTERVAL 12 MINUTE),
(5, 'Preparing', 'Ready for Delivery', 3, NOW() - INTERVAL 5 MINUTE),
(6, 'Pending', 'Preparing', 6, NOW() - INTERVAL 8 MINUTE),
(6, 'Preparing', 'Ready for Delivery', 6, NOW() - INTERVAL 4 MINUTE);

-- Insert Deliveries
INSERT INTO deliveries (delivery_id, order_id, rider_id, delivery_status, assigned_at, pickup_time, delivered_at, rider_earning, rider_note) VALUES
(1, 1, NULL, 'Pending Assignment', NULL, NULL, NULL, 0.00, NULL),
(2, 2, 4, 'Assigned', NOW() - INTERVAL 20 MINUTE, NULL, NULL, 0.00, 'On route to restaurant'),
(3, 3, 4, 'Delivered', NOW() - INTERVAL 120 MINUTE, NOW() - INTERVAL 105 MINUTE, NOW() - INTERVAL 60 MINUTE, 1.20, 'Delivered to door'),
(4, 4, NULL, 'Pending Assignment', NULL, NULL, NULL, 0.00, 'Fresh order ready for pickup'),
(5, 5, 4, 'Assigned', NOW() - INTERVAL 5 MINUTE, NULL, NULL, 0.00, 'Accepting trip'),
(6, 6, 4, 'Picked Up', NOW() - INTERVAL 8 MINUTE, NOW() - INTERVAL 2 MINUTE, NULL, 0.00, 'Food collected, on way to customer');

-- Insert Delivery Status History
INSERT INTO delivery_status_history (history_id, delivery_id, rider_id, status, note, created_at) VALUES
(1, 1, NULL, 'Pending Assignment', 'Order placed, awaiting rider acceptance', NOW() - INTERVAL 45 MINUTE),
(2, 2, 4, 'Assigned', 'Delivery assigned to rider Karim Khan', NOW() - INTERVAL 20 MINUTE),
(3, 3, 4, 'Assigned', 'Delivery assigned to rider Karim Khan', NOW() - INTERVAL 120 MINUTE),
(4, 3, 4, 'Picked Up', 'Order picked up from Spice Grill House', NOW() - INTERVAL 105 MINUTE),
(5, 3, 4, 'Delivered', 'Delivered safely to customer', NOW() - INTERVAL 60 MINUTE),
(6, 4, NULL, 'Pending Assignment', 'Order ready for rider pickup', NOW() - INTERVAL 10 MINUTE),
(7, 5, 4, 'Assigned', 'Delivery assigned to rider', NOW() - INTERVAL 5 MINUTE),
(8, 6, 4, 'Assigned', 'Delivery assigned to rider', NOW() - INTERVAL 8 MINUTE),
(9, 6, 4, 'Picked Up', 'Order collected from restaurant', NOW() - INTERVAL 2 MINUTE);

-- Insert Favorites
INSERT INTO favorites (favorite_id, customer_id, restaurant_id) VALUES
(1, 2, 1);

-- Insert Cart
INSERT INTO cart (cart_id, customer_id, item_id, quantity) VALUES
(1, 2, 4, 2);

-- Insert Reviews
INSERT INTO reviews (review_id, customer_id, order_id, item_id, rating, comment) VALUES
(1, 2, 3, 4, 5, 'The double cheeseburger was juicy and packed with flavor! Loved the special sauce.');
