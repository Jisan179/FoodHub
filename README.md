# 🍔 FoodHub - Food Ordering and Delivery Platform
## Modules: Admin Management Portal and Rider Portal (Pure Procedural PHP & MySQLi)

FoodHub is a **server-side web application** built for managing a food delivery platform. It features a complete **Admin Portal** built using **pure procedural PHP and procedural MySQLi** (with strictly zero classes, zero OOP objects, and zero PDO). The codebase separates database operations (**models**), presentation templates (**views**), and request handlers (**controllers**) into clean, user-specific role directories using standard PHP file includes (`include`/`require`).

---

## 📋 Table of Contents

1. [Tech Stack](#tech-stack)
2. [Procedural Architecture](#procedural-architecture)
3. [Project Directory Structure](#project-directory-structure)
4. [Features](#features)
5. [Database Setup](#database-setup)
6. [How to Run Locally](#how-to-run-locally)
7. [Default Credentials](#default-credentials)
8. [Security Implementation](#security-implementation)

---

## 🛠 Tech Stack

| Layer        | Technology                                                  |
|:-------------|:------------------------------------------------------------|
| **Backend**  | PHP (Pure Procedural PHP, `mysqli_*` procedural functions)  |
| **Database** | MySQL / MariaDB  (via XAMPP)                                |
| **Frontend** | HTML5, Vanilla CSS (`assets/css/style.css`)                 |
| **Session**  | PHP Native Sessions (`session_start()`)                     |
| **Server**   | Apache (XAMPP local development stack)                      |

> **100% Procedural PHP**: Zero classes, zero OOP objects (`->` / `new`), zero namespaces, and zero PDO. All queries use procedural `mysqli_*` functions (`mysqli_connect`, `mysqli_query`, `mysqli_fetch_assoc`, `mysqli_real_escape_string`, `mysqli_num_rows`, `mysqli_error`).

---

## 🏛 Procedural Architecture

The application is structured into clear procedural layers connected by standard file includes (`require_once` / `include_once`):

- **Database Connection (`config/db.php`)**: Establishes a procedural connection `$conn = mysqli_connect(...)` with UTF-8 (`utf8mb4`) charset.
- **Models Layer (`models/`)**: Encapsulates 100% of database queries inside procedural functions:
  - `user_model.php`: `find_user_by_username()`, `find_user_by_id()`, `check_user_exists()`, `create_user()`, `get_all_users()`, `delete_user()`, `count_total_users()`, `get_active_riders()`.
  - `restaurant_model.php`: `get_all_restaurants()`, `get_pending_restaurants()`, `count_pending_restaurants()`, `update_restaurant_status()`.
  - `order_model.php`: `get_all_orders()`, `get_recent_orders()`, `count_total_orders()`, `get_total_revenue()`, `update_order_status()`.
  - `delivery_model.php`: `get_delivery_by_order_id()`, `upsert_delivery()`.
- **Controllers Layer (`controllers/`)**: Procedural request and business logic handlers:
  - `auth/auth_check.php`: Procedural route guard checking `$_SESSION['role'] === 'Admin'`.
  - `auth/login_controller.php`: Authenticates credentials (plain-text and `password_verify` support) and manages sessions.
  - `auth/logout_controller.php`: Destroys active sessions and redirects to login.
  - `admin/dashboard_controller.php`: Gathers metrics, pending approvals, and recent orders.
  - `admin/user_controller.php`: Handles user creation (with duplicate validation), deletion (with self-delete protection), and search queries.
  - `admin/restaurant_controller.php`: Handles restaurant approvals and whitelist-validated status updates.
  - `admin/order_controller.php`: Handles order fulfillment status and rider delivery assignments.
- **Views Layer (`views/`)**: Clean HTML presentation templates decoupled from raw SQL queries:
  - `partials/`: `header.php`, `navbar.php` (with dynamic active link highlighting), `footer.php`.
  - `auth/login.php`: Admin login interface.
  - `admin/dashboard.php`: Dashboard analytics cards, pending reviews, and recent orders.
  - `admin/users.php`: User creation form, search filter, and user records table.
  - `admin/restaurants.php`: Restaurant approvals table with inline status update controls.
  - `admin/orders.php`: Order fulfillment table with inline status & rider assignments.
- **Role Entrypoints (`admin/`)**: User-specific URL endpoints (`admin/dashboard.php`, `admin/users.php`, `admin/restaurants.php`, `admin/orders.php`) that include the respective views.

---

## 📁 Project Directory Structure

```
FoodHub/
│
├── config/
│   └── db.php                       # Procedural mysqli_connect() database connection
│
├── models/                          # Pure procedural SQL query functions
│   ├── delivery_model.php           # Delivery lookups and upsert queries
│   ├── order_model.php              # Orders listing, revenue calculations, & status updates
│   ├── restaurant_model.php         # Restaurant listings, pending counts, & status updates
│   └── user_model.php               # User authentication, CRUD, search, & riders
│
├── controllers/                     # Procedural business logic handlers
│   ├── auth/
│   │   ├── auth_check.php           # Session guard redirecting unauthorized users
│   │   ├── login_controller.php     # Login validation & session initialization
│   │   └── logout_controller.php    # Session destroy & redirect
│   └── admin/
│       ├── dashboard_controller.php # Gathers dashboard KPIs & recent activity
│       ├── order_controller.php     # Order status & rider delivery assignment handler
│       ├── restaurant_controller.php# Restaurant status update handler
│       └── user_controller.php      # User creation, deletion, & search handler
│
├── views/                           # Procedural HTML view templates
│   ├── auth/
│   │   └── login.php                # Admin login form template
│   ├── admin/
│   │   ├── dashboard.php            # Admin dashboard metrics & tables template
│   │   ├── orders.php               # Order tracking & inline update template
│   │   ├── restaurants.php          # Restaurant approvals template
│   │   └── users.php                # User creation & management template
│   └── partials/
│       ├── footer.php               # Global footer markup
│       ├── header.php               # Global HTML head & stylesheet links
│       └── navbar.php               # Navigation bar with dynamic active states
│
├── assets/
│   └── css/
│       └── style.css                # Global stylesheet
│
├── admin/                           # Role-specific entrypoints
│   ├── dashboard.php                # Access admin dashboard
│   ├── orders.php                   # Access order & delivery tracking
│   ├── restaurants.php              # Access restaurant management
│   └── users.php                    # Access user management
│
├── index.php                        # Root entrypoint redirect
├── login.php                        # Root login entrypoint
├── logout.php                       # Root logout entrypoint
├── schema.sql                       # Complete MySQL schema (7 tables) & seed/demo data
└── README.md                        # Documentation & setup guide
```

---

## ✨ Features

### 🔐 Authentication
- Admin login portal with username + password validation.
- Supports both plain-text seed passwords and `password_hash()` / `password_verify()`.
- Procedural auth guard (`controllers/auth/auth_check.php`) included on all admin controllers.
- Safe logout terminating session state.

### 📊 Admin Dashboard
- Computed metrics using SQL `COUNT()` and `SUM()`:
  - Total registered users across all roles.
  - Pending restaurant approval count.
  - Total all-time orders.
  - Total revenue (excluding cancelled orders).
- Quick-action panel for restaurants awaiting approval.
- Recent orders table with colour-coded status badges.

### 👥 User Management
- **Create User Account**: Field validation for Name, Username, Email, Password, and Role (`Customer`, `Restaurant Manager`, `Rider`, `Admin`).
- **Duplicate Prevention**: Checks for existing usernames and email addresses before insertion.
- **Search**: Multi-column search across name, username, email, role, and phone.
- **Delete Safeguard**: Admins are prevented from deleting their own active account.

### 🏪 Restaurant Approvals & Management
- Lists all partner restaurants with owner details, contact info, and computed menu item counts.
- **Inline Status Updates**: Dropdown updates validated against a server-side whitelist (`Pending`, `Approved`, `Rejected`, `Suspended`).

### 📦 Orders & Delivery Tracking
- Comprehensive order table joining orders, customers, restaurants, deliveries, and assigned riders.
- **Inline Order & Delivery Update**: Allows simultaneous updating of order status, delivery status, and rider assignment with automatic delivery record upsertion.

---

## 🗄 Database Setup

The database contains **6 relational tables**:

| Table           | Description                                                       |
|:----------------|:------------------------------------------------------------------|
| `users`         | Accounts: Admin, Customer, Restaurant Manager, Rider              |
| `restaurants`   | Partner restaurants linked to a `Restaurant Manager` user         |
| `food_items`    | Menu items belonging to a restaurant                              |
| `orders`        | Customer orders referencing a customer and a restaurant           |
| `order_items`   | Individual line items within each order                           |
| `deliveries`    | Delivery tracking records linking orders to Riders                |

### Import Steps (XAMPP / phpMyAdmin)

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Open **`http://localhost/phpmyadmin`** in your browser.
3. Select the **"Import"** tab.
4. Choose `schema.sql` from the FoodHub workspace.
5. Click **"Go"** to create the `foodhub_db` database and seed data.

---

## ▶ How to Run Locally

1. Place the project in XAMPP web root:
   ```
   C:\xampp\htdocs\FoodHub\
   ```
2. Ensure database configuration in `config/db.php` matches your local MySQL server:
   ```php
   $conn = mysqli_connect("localhost", "root", "", "foodhub_db");
   ```
3. Open your browser and navigate to:
   ```
   http://localhost/FoodHub/
   ```
   You will automatically be redirected to the login page.

---

## 🔑 Default Credentials

| Role                | Username    | Password      |
|:--------------------|:------------|:--------------|
| **Admin**           | `admin`     | `admin123`    |
| Customer            | `customer1` | `customer123` |
| Customer            | `customer2` | `customer123` |
| Restaurant Manager  | `manager1`  | `manager123`  |
| Restaurant Manager  | `manager2`  | `manager123`  |
| Rider               | `rider1`    | `rider123`    |

> The `Admin` and `Rider` roles can log in. Customers and Restaurant Managers are not enabled in this module.

## Rider Portal

The Rider Portal is available at `rider/dashboard.php` after signing in as a Rider. Riders can claim unassigned deliveries, view assigned deliveries, confirm pickup, mark deliveries complete, cancel an assignment, add a delivery note, and inspect completed delivery history. Every action is validated in PHP, limited to the logged-in rider, and returned as JSON for the AJAX client.

The single `schema.sql` file includes the complete seven-table schema, rider delivery metadata, status history, and rider CRUD demo records. Import it into a fresh `foodhub_db` database through phpMyAdmin. It resets and recreates the database tables, so export any data you need before importing.

---

## 🔒 Security Implementation

- **SQL Injection Prevention**: All inputs are sanitized using `mysqli_real_escape_string($conn, ...)` and integer casting `intval(...)`.
- **XSS Protection**: All view outputs use `htmlspecialchars()` before HTML rendering.
- **Role Verification**: Non-admin users attempting to log in receive an access denial, and unauthenticated requests are redirected.
- **Whitelist Validation**: Status update endpoints validate values against defined status arrays.
