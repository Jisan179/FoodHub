-- FoodHub Restaurant Manager Module Schema Migration

-- Extend restaurants table
ALTER TABLE restaurants
  ADD COLUMN cuisine_type VARCHAR(100),
  ADD COLUMN rejection_reason VARCHAR(255) NULL,
  ADD COLUMN is_open TINYINT(1) DEFAULT 1;
-- Note: status enum is existing ('Pending', 'Approved', 'Rejected', 'Suspended')

-- Create restaurant_managers table to handle ownership/authorization
CREATE TABLE restaurant_managers (
  manager_id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  restaurant_id INT NOT NULL,
  role_title VARCHAR(50) DEFAULT 'owner',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (restaurant_id) REFERENCES restaurants(restaurant_id) ON DELETE CASCADE,
  UNIQUE KEY unique_manager_restaurant (user_id, restaurant_id)
);

-- Extend food_items for soft deletes
ALTER TABLE food_items
  ADD COLUMN is_deleted TINYINT(1) DEFAULT 0;

-- Create order_status_log
CREATE TABLE order_status_log (
  log_id INT PRIMARY KEY AUTO_INCREMENT,
  order_id INT NOT NULL,
  old_status VARCHAR(30),
  new_status VARCHAR(30) NOT NULL,
  changed_by INT NOT NULL,
  changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
  FOREIGN KEY (changed_by) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Seed data: Link existing managers to existing restaurants
-- user 3 (manager1) -> restaurant 1
-- user 6 (manager2) -> restaurant 2
INSERT IGNORE INTO restaurant_managers (user_id, restaurant_id, role_title) VALUES
(3, 1, 'owner'),
(6, 2, 'owner');
