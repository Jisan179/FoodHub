# 🍔 FoodHub - Online Food Ordering & Delivery System

**FoodHub** is a full-featured multi-role online food ordering and delivery web application built for the **Web Technologies** course.

The application strictly adheres to the following architectural rules:
- **4 Dedicated User Folders**: `admin/`, `customer/`, `manager/`, `rider/`.
- **MVC Architecture per User Folder**: Each user folder contains its own `models/`, `views/`, and `controllers/` subdirectories.
- **Pure Procedural PHP & MySQLi**: Zero OOP classes, **Zero PDO**, and **Zero `try...catch` exceptions** — all database transactions and errors are handled using pure procedural `if/else` checks with `mysqli_rollback()`.
- **Manual Database & Table Queries**: Complete schema setup with raw MySQL queries in `database.sql` and `schema.sql`.

---

## 🛠 Tech Stack

| Component | Technology |
|:---|:---|
| **Backend** | Pure Procedural PHP 8+ (`mysqli_*` procedural functions, prepared statements) |
| **Architecture** | 4-User Procedural MVC (`models/`, `views/`, `controllers/` per user role) |
| **Database** | MySQL / MariaDB (via XAMPP) |
| **Frontend** | HTML5, Vanilla CSS (`style.css`, `customer.css`, `manager.css`) |
| **Authentication** | Native PHP Sessions & Dynamic Depth-Aware Role Guards |
| **Local Server** | Apache / XAMPP |

---

## 👥 Role Modules & Key Features

### 1. 🛡️ Administrator Module (`admin/`)
- **Dashboard Analytics**: Real-time KPI metrics for total users, approved restaurants, active orders, system revenue, and pending approvals.
- **User Management**: Full CRUD operations for users (Customer, Restaurant Manager, Rider, Admin), password reset, and status toggles (`Active`, `Inactive`, `Suspended`).
- **Restaurant Approvals**: Review pending restaurant applications, inspect details, approve or reject with custom reasons.
- **Orders & Delivery Oversight**: Live order monitoring, manual rider assignments, and delivery status audits.

### 2. 🛍️ Customer Module (`customer/`)
- **Customer Dashboard**: Overview cards (total orders, active orders, favorites, reviews), active orders banner, and top recommended spots.
- **Restaurant & Menu Browsing**: Search across restaurants and food items, category filtering, ratings, and pricing.
- **Favorites System**: 1-click bookmarking (❤️/🤍) directly from restaurant cards and menu pages.
- **Persistent Shopping Cart**: Database-backed cart with single-restaurant conflict protection and live navbar item counter badge.
- **Secure Atomic Checkout**: Server-side price recalculation inside database transactions (`mysqli_begin_transaction`) to prevent client-side price spoofing.
- **Live Order Tracking**: Visual 5-stage progress tracker (`Order Placed` ➔ `Preparing` ➔ `Ready for Delivery` ➔ `Out for Delivery` ➔ `Delivered`) with rider contact details and order cancellation options.
- **Food Ratings & Reviews**: Verified review submission for delivered dishes (1–5 stars with comments), along with edit and delete capabilities.

### 3. 👨‍🍳 Restaurant Manager Module (`manager/`)
- **Restaurant Registration & Profile**: Register new dining spots for admin review and manage restaurant profile information, address, cuisine types, and opening status.
- **Menu Management**: Add, update, and soft-delete food items, set category tags, and toggle item availability.
- **Live Kitchen Order Workflow**: Accept incoming orders, transition statuses (`Pending` ➔ `Preparing` ➔ `Ready for Delivery`), and track automated timestamped logs in `order_status_log`.
- **Order Details**: Comprehensive receipt breakdown with customer details, delivery address, and line items.

### 4. 🛵 Rider Delivery Module (`rider/`)
- **Rider Portal Dashboard**: Live summary cards (available requests, active trips, completed deliveries, earnings).
- **Available Delivery Task Claiming**: Claim pending delivery tasks with 1 click.
- **Delivery Lifecycle Management**: Update order transitions (`Assigned` ➔ `Picked Up` ➔ `Delivered`) with custom delivery notes.
- **Audit Logging & History**: Historical trip ledger recorded in `delivery_status_history`.
- **Commission Earnings**: Automatic commission calculations per completed delivery.

---

## 📁 4-User MVC Directory Structure

```
FoodHub/
│
├── config/
│   └── db.php                     # Procedural MySQLi connection (Zero PDO)
│
├── includes/
│   └── auth_check.php             # Dynamic path resolver & role-based middleware
│
├── views/partials/                # Shared layout templates
│   ├── header.php                 # Dynamic HTML <head> & stylesheet loader
│   ├── navbar.php                 # Role-tailored navigation bar
│   └── footer.php                 # Shared footer
│
├── assets/
│   ├── css/                       # style.css, customer.css, manager.css
│   └── js/                        # rider.js
│
├── admin/                         # 🛡️ USER 1: Administrator Module
│   ├── models/                    # UserModel.php, RestaurantModel.php, OrderModel.php
│   ├── views/                     # dashboard.php, users.php, user-create.php, user-edit.php, restaurants.php, orders.php
│   ├── controllers/               # DashboardController.php, UserController.php, RestaurantController.php, OrderController.php
│   ├── dashboard.php              # Entrypoint router
│   ├── users.php
│   ├── restaurants.php
│   └── orders.php
│
├── customer/                      # 🛍️ USER 2: Customer Module
│   ├── models/                    # CustomerModel.php
│   ├── views/                     # dashboard.php, browse_restaurants.php, view_menu.php, cart.php, checkout.php, favorites.php, order_history.php, order_track.php, reviews.php
│   ├── controllers/               # DashboardController.php, BrowseController.php, CartController.php, CheckoutController.php, FavoritesController.php, MenuController.php, etc.
│   ├── actions/                   # add_to_cart.php, place_order.php, submit_review.php, cancel_order.php, etc.
│   ├── dashboard.php              # Entrypoint router
│   ├── browse_restaurants.php
│   ├── cart.php
│   ├── checkout.php
│   └── ...
│
├── manager/                       # 👨‍🍳 USER 3: Restaurant Manager Module
│   ├── models/                    # RestaurantModel.php, FoodModel.php, OrderModel.php
│   ├── views/                     # dashboard.php, menu.php, orders.php, order_detail.php, register_restaurant.php, restaurant_profile.php
│   ├── controllers/               # restaurant_controller.php, menu_controller.php, order_controller.php
│   ├── dashboard.php              # Entrypoint router
│   ├── menu.php
│   └── orders.php
│
├── rider/                         # 🛵 USER 4: Rider Module
│   ├── models/                    # RiderModel.php, DeliveryModel.php
│   ├── views/                     # dashboard.php, delivery_card.php, delivery_row.php
│   ├── controllers/               # DashboardController.php, DeliveryAction.php
│   └── dashboard.php              # Entrypoint router
│
├── database.sql                   # Complete manual database, table, and seed SQL queries
├── schema.sql                     # Unified SQL schema & seed script
└── index.php                      # Root entrypoint router
```

---

## 🗄 Database Schema

The database `foodhub_db` comprises **12 relational tables**:

1. **`users`** – System user accounts with role assignments (`Administrator`, `Customer`, `Restaurant Manager`, `Rider`).
2. **`restaurants`** – Dining partners, addresses, cuisine types, approval statuses, and opening flags.
3. **`restaurant_managers`** – Authorization mapping linking managers to one or more restaurants.
4. **`food_items`** – Menu items, pricing, categories, and availability flags (with soft-delete support).
5. **`orders`** – Order headers, grand totals, delivery addresses, payment methods, and statuses.
6. **`order_items`** – Line items with locked purchase prices.
7. **`order_status_log`** – Audit history of kitchen and order status transitions.
8. **`deliveries`** – Delivery tracking, assigned riders, pickup timestamps, and earnings.
9. **`delivery_status_history`** – Timestamped audit ledger of all rider delivery updates and notes.
10. **`favorites`** – Customer bookmarked restaurants.
11. **`cart`** – Persistent shopping cart items per customer.
12. **`reviews`** – Verified customer star ratings (1–5) and review comments on ordered dishes.

---

## ▶ How to Run Locally

### 1. Prerequisites
- Install **XAMPP** (Apache + MySQL/MariaDB) with PHP 8.0+.

### 2. Place Project in `htdocs`
Clone or copy the project into your XAMPP `htdocs` folder:
```
C:\xampp\htdocs\FoodHub\
```

### 3. Import Database
1. Open **phpMyAdmin** at `http://localhost/phpmyadmin/`.
2. Click **Import**, select [database.sql](file:///c:/xampp/htdocs/FoodHub/database.sql) (or [schema.sql](file:///c:/xampp/htdocs/FoodHub/schema.sql)), and click **Import / Go**.
3. Verify connection settings in `config/db.php`:
   ```php
   $conn = mysqli_connect("localhost", "root", "", "foodhub_db");
   ```

### 4. Open in Browser
Navigate to:
```
http://localhost/FoodHub/
```
You will be automatically redirected to `login.php`.

---

## 🔑 Default Credentials

| Role | Username | Password | Dedicated Portal URL |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `admin123` | `http://localhost/FoodHub/admin/dashboard.php` |
| **Customer** | `customer1` | `customer123` | `http://localhost/FoodHub/customer/dashboard.php` |
| **Customer** | `customer2` | `customer123` | `http://localhost/FoodHub/customer/dashboard.php` |
| **Restaurant Manager** | `manager1` | `manager123` | `http://localhost/FoodHub/manager/views/dashboard.php` |
| **Restaurant Manager** | `manager2` | `manager123` | `http://localhost/FoodHub/manager/views/dashboard.php` |
| **Rider** | `rider1` | `rider123` | `http://localhost/FoodHub/rider/dashboard.php` |
| **Rider** | `rider2` | `rider123` | `http://localhost/FoodHub/rider/dashboard.php` |
