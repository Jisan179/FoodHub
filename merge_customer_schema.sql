-- FoodHub Customer Module Schema Migration
-- Compatible with MySQL 5.7+ / MariaDB 10.4+
-- Integrates with existing foodhub_db (users, restaurants, food_items, orders, order_items, deliveries)

USE foodhub_db;

-- 1. Favorites Table (Customer favorite restaurants)
CREATE TABLE IF NOT EXISTS favorites (
    favorite_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    restaurant_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(restaurant_id) ON DELETE CASCADE,
    UNIQUE KEY unique_customer_restaurant_fav (customer_id, restaurant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Cart Table (Customer persistent cart items linked to food_items)
CREATE TABLE IF NOT EXISTS cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES food_items(item_id) ON DELETE CASCADE,
    UNIQUE KEY unique_customer_cart_item (customer_id, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Reviews Table (Customer reviews on food items from delivered orders)
CREATE TABLE IF NOT EXISTS reviews (
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
-- CUSTOMER SEED DATA
-- ==========================================================

-- Seed Favorites (customer1 -> Spice Grill House)
INSERT IGNORE INTO favorites (favorite_id, customer_id, restaurant_id) VALUES
(1, 2, 1);

-- Seed Cart Item (customer1 -> Double Cheeseburger from The Burger Spot)
INSERT IGNORE INTO cart (cart_id, customer_id, item_id, quantity) VALUES
(1, 2, 4, 2);

-- Seed Review for Delivered Order #3 (customer1 reviewed Double Cheeseburger)
INSERT IGNORE INTO reviews (review_id, customer_id, order_id, item_id, rating, comment) VALUES
(1, 2, 3, 4, 5, 'The double cheeseburger was juicy and packed with flavor! Loved the special sauce.');
