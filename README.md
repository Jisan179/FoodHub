# 🍔 FoodHub — Food Ordering & Delivery Platform

## Authentication, Role-Based Access Control (RBAC) & User Management System

FoodHub is a **server-side web application** built for a complete food delivery platform experience. It is built using **pure procedural PHP and procedural MySQLi** (zero classes, zero OOP, zero PDO) with a clean **MVC-style separation** of database operations (**models**), presentation templates (**views**), and request handlers (**controllers**) across 4 distinct user roles.

---

## 📋 Table of Contents

1. [Tech Stack](#tech-stack)
2. [User Roles](#user-roles)
3. [Procedural Architecture](#procedural-architecture)
4. [Project Directory Structure](#project-directory-structure)
5. [Features](#features)
6. [Database Setup](#database-setup)
7. [How to Run Locally](#how-to-run-locally)
8. [Default Credentials](#default-credentials)
9. [Security Implementation](#security-implementation)

---

## 🛠 Tech Stack

| Layer        | Technology                                                    |
|:-------------|:--------------------------------------------------------------|
| **Backend**  | PHP  (Pure Procedural, `mysqli_*` procedural functions)       |
| **Database** | MySQL / MariaDB (via XAMPP)                                   |
| **Frontend** | HTML5, Bootstrap 5, Vanilla CSS (`assets/css/style.css`)      |
| **Fonts**    | Plus Jakarta Sans (Google Fonts)                              |
| **Session**  | PHP Native Sessions (`session_start()`)                       |
| **Server**   | Apache (XAMPP local development stack)                        |

> **100% Procedural PHP**: Zero classes, zero OOP objects (`->` / `new`), zero namespaces, and zero PDO. All queries use procedural `mysqli_*` functions with **prepared statements** (`mysqli_prepare`, `mysqli_bind_param`, `mysqli_execute`, `mysqli_get_result`).

---

## 👥 User Roles

The system supports **4 distinct user roles** with separate dashboards, access guards, and capabilities:

| Role | Description |
|:---|:---|
| **Administrator** | Full platform control: user management, restaurant approvals, order oversight |
| **Customer** | Browse restaurants, place orders, track delivery status |
| **Restaurant Manager** | Manage own restaurant's menu items and incoming orders |
| **Rider** | Claim available deliveries, update delivery status, track earnings |

> Administrator accounts **cannot be created via public registration**. They must be provisioned by an existing Admin through the Admin User Management panel.

---

## 🏛 Procedural Architecture

The application is structured into clear procedural layers connected by `require_once` / `include_once` file includes:

- **Database Connection (`config/db.php`)**: Establishes a single procedural `$conn = mysqli_connect(...)` with `utf8mb4` charset.
- **RBAC Middleware (`includes/auth_check.php`)**: Universal role guard functions:
  - `check_auth($allowed_roles)`: Restricts page access to specified roles.
  - `is_logged_in()`: Validates active session state.
  - `get_logged_user()`: Retrieves full profile of the current user.
  - `get_user_dashboard_url($role)`: Maps a role to its destination dashboard route.
- **Models Layer (`models/`)**: 100% of SQL queries encapsulated in procedural functions (prepared statements throughout):
  - `user_model.php`: Authentication, CRUD, search, pagination, role filtering, stats.
  - `restaurant_model.php`: Listings, pending approvals, status updates.
  - `order_model.php`: Order queries, status management, revenue calculations.
  - `delivery_model.php`: Delivery tracking, claim delivery, status updates, rider earnings.
- **Controllers Layer (`controllers/`)**: Procedural request handlers / business logic:
  - `auth/login_controller.php`: Credential check (username or email), session init, remember-me cookie.
  - `auth/logout_controller.php`: Session destroy, cookie clear, redirect.
  - `auth/register_controller.php`: Registration validation, duplicate check, `password_hash()`.
  - `auth/profile_controller.php`: Profile update, account deactivation / deletion.
  - `auth/change_password_controller.php`: Current password verification + new hash update.
  - `auth/forgot_password_controller.php`: Two-step account recovery.
  - `dashboard_controller.php`: Role-based dashboard dispatcher.
  - `admin/dashboard_controller.php`: Platform KPI aggregation.
  - `admin/user_controller.php`: User CRUD, live search, pagination, duplicate protection.
  - `admin/restaurant_controller.php`: Approval and status update handler.
  - `admin/order_controller.php`: Order fulfillment and rider assignment.
- **Views Layer (`views/`)**: Clean HTML templates decoupled from SQL logic.
- **Role Entrypoints (`admin/`, root `*.php`)**: URL entry files that include the corresponding controller + view.

---

## 📁 Project Directory Structure

```
FoodHub/
│
├── config/
│   └── db.php                           # Procedural mysqli_connect() database connection
│
├── includes/
│   └── auth_check.php                   # RBAC middleware: check_auth(), is_logged_in(), get_logged_user()
│
├── models/                              # Pure procedural SQL query functions (prepared statements)
│   ├── user_model.php                   # User auth, CRUD, search, pagination, role stats
│   ├── restaurant_model.php             # Restaurant listings, pending approvals, status updates
│   ├── order_model.php                  # Orders, revenue, status management
│   └── delivery_model.php               # Delivery tracking, claim, rider stats
│
├── controllers/                         # Procedural business logic handlers
│   ├── auth/
│   │   ├── auth_check.php               # Admin-level session guard
│   │   ├── login_controller.php         # Login: credential check, session init, remember-me
│   │   ├── logout_controller.php        # Session destroy & cookie clear
│   │   ├── register_controller.php      # Registration validation & password hashing
│   │   ├── profile_controller.php       # Profile update & account deletion
│   │   ├── change_password_controller.php # Current password check + hash update
│   │   └── forgot_password_controller.php # Two-step account recovery
│   ├── dashboard_controller.php         # Role-based dashboard dispatcher
│   └── admin/
│       ├── dashboard_controller.php     # Platform KPIs, pending approvals, recent orders
│       ├── user_controller.php          # User creation, deletion, live search, pagination
│       ├── restaurant_controller.php    # Restaurant status update handler
│       └── order_controller.php        # Order fulfillment & rider assignment handler
│
├── views/                               # HTML presentation templates
│   ├── auth/
│   │   ├── login.php                    # Login form: username/email, password toggle, remember-me
│   │   ├── register.php                 # Registration form: name, email, phone, role selector
│   │   ├── profile.php                  # View/edit personal details, deactivate account
│   │   ├── change-password.php          # Change password form
│   │   └── forgot-password.php          # Password recovery form
│   ├── admin/
│   │   ├── dashboard.php                # Platform metrics, user stats, pending restaurants
│   │   ├── users.php                    # User table: search, role/status filter, pagination
│   │   ├── user-create.php              # Admin user provisioning form (all roles)
│   │   ├── user-edit.php                # Admin user edit: details, role, status, password
│   │   ├── orders.php                   # Order tracking & inline status update table
│   │   └── restaurants.php              # Restaurant approval & status management table
│   ├── customer/
│   │   └── dashboard.php                # Stats, live order tracking, restaurant catalog
│   ├── manager/
│   │   └── dashboard.php                # Kitchen orders queue, menu items, revenue stats
│   ├── rider/
│   │   └── dashboard.php                # Available deliveries, active trips, earnings summary
│   └── partials/
│       ├── header.php                   # Global HTML head, stylesheet & font links
│       ├── navbar.php                   # Role-aware navbar: role badge, nav links
│       └── footer.php                   # Global footer & Bootstrap JS bundle
│
├── assets/
│   └── css/
│       └── style.css                    # Global FoodHub stylesheet (Bootstrap 5 + custom)
│
├── admin/                               # Admin role entrypoints
│   ├── dashboard.php
│   ├── users.php
│   ├── user-create.php
│   ├── user-edit.php
│   ├── user-delete.php
│   ├── orders.php
│   └── restaurants.php
│
├── index.php                            # Root entrypoint redirect
├── login.php                            # Login entrypoint
├── logout.php                           # Logout entrypoint
├── register.php                         # Public registration entrypoint
├── dashboard.php                        # Dashboard router entrypoint
├── profile.php                          # Profile management entrypoint
├── change-password.php                  # Change password entrypoint
├── forgot-password.php                  # Password recovery entrypoint
├── database.sql                         # Full schema (6 tables) + seed data for all 4 roles
├── schema.sql                           # Synchronized schema copy
└── README.md                            # This documentation file
```

---

## ✨ Features

### 🔐 Authentication
- **Login (`login.php`)**: Supports login via username **or** email address.
  - Password visibility toggle button.
  - "Remember Me" checkbox with 30-day cookie persistence.
  - Automatic role-based redirection to each user's personalized dashboard.
  - Link to registration and password recovery.
- **Registration (`register.php`)**: Public registration for `Customer`, `Restaurant Manager`, and `Rider` roles.
  - Full Name, Username, Email, Phone, Password, Confirm Password, and Role fields.
  - Administrator accounts are blocked from public self-registration.
  - `password_hash($password, PASSWORD_DEFAULT)` for secure credential storage.
- **Logout (`logout.php`)**: Destroys session, clears remember-me cookie, and redirects.
- **Profile Management (`profile.php`)**: Edit personal details and optionally deactivate or delete your own account.
- **Change Password (`change-password.php`)**: Enforces verification of the current password before accepting a new one.
- **Forgot Password (`forgot-password.php`)**: Two-step recovery — find account by username or email, then set a new password.

### 📊 Administrator Dashboard
- Platform-wide KPI cards: total users, pending restaurant approvals, total orders, total revenue.
- User distribution breakdown by role.
- Pending restaurant approvals quick-action panel.
- Recent orders table with colour-coded status badges.

### 👥 Admin User Management (`admin/users.php`)
- **Live Search**: Real-time multi-column search across name, username, email, phone, and address.
- **Filter by Role**: `All`, `Administrator`, `Customer`, `Restaurant Manager`, `Rider`.
- **Filter by Status**: `All`, `Active`, `Inactive`, `Suspended`.
- **Sorting & Pagination**: Sortable columns with configurable results per page.
- **Create User** (`admin/user-create.php`): Provision any role including Administrator.
- **Edit User** (`admin/user-edit.php`): Update profile, reassign role, toggle status, optional password reset.
- **Delete User** (`admin/user-delete.php`): Protected handler — prevents self-deletion of an active Administrator.

### 🛒 Customer Dashboard
- Summary stats: total orders placed, active orders, completed orders, total amount spent.
- Live order tracking with status badges (`Pending`, `Preparing`, `Out for Delivery`, `Delivered`, `Cancelled`).
- Partner restaurants catalog.
- Full order history table.

### 🏪 Restaurant Manager Dashboard
- KPI cards: incoming orders, total orders, revenue earned, total menu items.
- Live kitchen orders queue with one-click status transitions.
- Full menu items listing for the managed restaurant.

### 🛵 Rider Dashboard
- Available deliveries queue with **"Accept Delivery"** claim button.
- Active deliveries management with **"Mark Picked Up"** and **"Mark Delivered"** transitions.
- Delivery history and commission earnings summary.

### 🏪 Restaurant Approvals & Management (Admin)
- Lists all partner restaurants with owner details, contact info, and computed menu item counts.
- Inline status updates validated against a server-side whitelist (`Pending`, `Approved`, `Rejected`, `Suspended`).

### 📦 Orders & Delivery Tracking (Admin)
- Comprehensive order table joining orders, customers, restaurants, deliveries, and riders.
- Inline order status and delivery status updates with automatic delivery record upsertion.

---

## 🗄 Database Setup

The database contains **6 relational tables**:

| Table         | Description                                                     |
|:--------------|:----------------------------------------------------------------|
| `users`       | Accounts: Administrator, Customer, Restaurant Manager, Rider    |
| `restaurants` | Partner restaurants linked to a `Restaurant Manager` user       |
| `food_items`  | Menu items belonging to a restaurant                            |
| `orders`      | Customer orders referencing a customer and a restaurant         |
| `order_items` | Individual line items within each order                         |
| `deliveries`  | Delivery tracking records linking orders to Riders              |

### Import Steps (XAMPP / phpMyAdmin)

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Open **`http://localhost/phpmyadmin`** in your browser.
3. Create a new database named **`foodhub_db`** (or let the SQL file create it).
4. Select the **"Import"** tab.
5. Choose `database.sql` from the FoodHub workspace.
6. Click **"Go"** to create all tables and seed test data.

---

## ▶ How to Run Locally

1. Place the project in the XAMPP web root:
   ```
   C:\xampp\htdocs\FoodHub\
   ```
2. Ensure database configuration in `config/db.php` matches your local MySQL:
   ```php
   $conn = mysqli_connect("localhost", "root", "", "foodhub_db");
   ```
3. Import `database.sql` via phpMyAdmin (see above).
4. Open your browser and navigate to:
   ```
   http://localhost/FoodHub/
   ```
   You will automatically be redirected to the login page.

---

## 🔑 Default Credentials

| Role                   | Username    | Password      |
|:-----------------------|:------------|:--------------|
| **Administrator**      | `admin`     | `admin123`    |
| **Customer**           | `customer1` | `customer123` |
| **Customer**           | `customer2` | `customer123` |
| **Restaurant Manager** | `manager1`  | `manager123`  |
| **Restaurant Manager** | `manager2`  | `manager123`  |
| **Rider**              | `rider1`    | `rider123`    |

---

## 🔒 Security Implementation

- **SQL Injection Prevention**: All user-facing queries use **prepared statements** (`mysqli_prepare` + `mysqli_bind_param`).
- **XSS Protection**: All view output uses `htmlspecialchars()` before HTML rendering.
- **Password Hashing**: All passwords stored with `password_hash($password, PASSWORD_DEFAULT)` (bcrypt).
- **RBAC Guards**: `check_auth($allowed_roles)` is included at the top of every protected page, redirecting unauthorized access.
- **Self-Deletion Protection**: Administrators cannot delete their own account from the User Management panel.
- **Role Registration Lock**: Public registration form does not expose the `Administrator` role option.
- **Whitelist Validation**: All status-update endpoints validate values against defined status arrays before writing to the database.
- **Session Security**: Sessions are destroyed completely on logout (including cookie and session data), with redirect to prevent back-button session reuse.
