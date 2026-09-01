# 🍔 FoodHub — Food Ordering & Delivery Platform

## Authentication, Role-Based Access Control (RBAC) & User Management System

FoodHub is a **server-side web application** built for a complete food delivery platform experience. It is built using **pure procedural PHP and procedural MySQLi** (zero classes, zero OOP, zero PDO, zero AJAX) with a clean **MVC-style separation** of database operations (**models**), presentation templates (**views**), and request handlers (**controllers**) across 4 distinct user roles.

---

## 📋 Table of Contents

1. [Tech Stack](#tech-stack)
2. [User Roles](#user-roles)
3. [Procedural Architecture](#procedural-architecture)
4. [Project Directory Structure](#project-directory-structure)
5. [Features](#features)
6. [Database Setup & Migrations](#database-setup--migrations)
7. [How to Run Locally](#how-to-run-locally)
8. [Default Credentials](#default-credentials)
9. [Security Implementation](#security-implementation)

---

## 🛠 Tech Stack

| Layer        | Technology                                                    |
|:-------------|:--------------------------------------------------------------|
| **Backend**  | PHP (Pure Procedural, `mysqli_*` procedural functions)        |
| **Database** | MySQL / MariaDB (via XAMPP)                                   |
| **Frontend** | HTML5, Bootstrap 5, Vanilla CSS (`style.css`)                 |
| **Fonts**    | Plus Jakarta Sans (Google Fonts)                              |
| **Session**  | PHP Native Sessions (`session_start()`)                       |
| **Server**   | Apache (XAMPP local development stack)                        |

> **100% Procedural PHP**: Zero classes, zero OOP objects (`->` / `new`), zero namespaces, zero PDO, and zero AJAX. All queries use procedural `mysqli_*` functions with **prepared statements** (`mysqli_prepare`, `mysqli_bind_param`, `mysqli_execute`, `mysqli_get_result`). All form submissions use standard HTML `POST`/`GET` requests with header redirects.

---

## 👥 User Roles

The system supports **4 distinct user roles** with separate dashboards, access guards, and capabilities:

| Role | Description |
|:---|:---|
| **Administrator** | Full platform control: user management, restaurant approvals, order oversight |
| **Customer** | Browse restaurants, place orders, track delivery status |
| **Restaurant Manager** | Register & manage restaurants, menu items, order fulfillment queue, and restaurant deletion |
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
- **Models Layer (`models/` & `manager/models/`)**: 100% of SQL queries encapsulated in procedural functions (prepared statements throughout):
  - `user_model.php`: Authentication, CRUD, search, pagination, role filtering, stats, self-deletion.
  - `restaurant_model.php`: Listings, pending approvals, status updates.
  - `RestaurantModel.php`: Manager ownership lookup, insert restaurant, update profile, toggle availability, deletion.
  - `FoodModel.php`: Menu item insertion, update, soft-deletion (`is_deleted = 1`).
  - `OrderModel.php`: Manager order retrieval, forward-only status transition enforcement, status change logging.
  - `delivery_model.php`: Delivery tracking, claim delivery, status updates, rider earnings.
- **Controllers Layer (`controllers/` & `manager/controllers/`)**: Procedural request handlers / business logic:
  - `auth/login_controller.php`: Role-aware credential check (username or email), session init, remember-me cookie.
  - `auth/logout_controller.php`: Session destroy, cookie clear, redirect.
  - `auth/register_controller.php`: Registration validation, duplicate check, `password_hash()`.
  - `auth/profile_controller.php`: Profile update, account deactivation / deletion.
  - `restaurant_controller.php`: Manager restaurant onboarding, profile edit, availability toggle, restaurant deletion.
  - `menu_controller.php`: Add, edit, and soft-delete menu items.
  - `order_controller.php`: Manager order acceptance, status update, order status logging.
- **Views Layer (`views/` & `manager/views/`)**: Clean HTML templates decoupled from SQL logic using shared header, role-aware navbar, and footer partials.

---

## 📁 Project Directory Structure

```
FoodHub/
│
├── config/
│   └── db.php                           # Procedural mysqli_connect() database connection
│
├── includes/
│   ├── auth_check.php                   # RBAC middleware: check_auth(), is_logged_in(), get_logged_user()
│   └── auth.php                         # Manager-level session authentication guard
│
├── models/                              # Core procedural SQL query functions
│   ├── user_model.php                   # User auth, CRUD, search, pagination, role stats, self-delete
│   ├── restaurant_model.php             # Admin restaurant listings & approvals
│   ├── order_model.php                  # System orders & revenue calculations
│   └── delivery_model.php               # Rider delivery tracking & earnings
│
├── controllers/                         # Procedural business logic handlers
│   ├── auth/
│   │   ├── login_controller.php         # Role-aware login & session initialization
│   │   ├── logout_controller.php        # Session destroy & redirect
│   │   ├── register_controller.php      # Public registration handler
│   │   └── profile_controller.php       # Profile update & account self-deletion handler
│   └── admin/
│       ├── dashboard_controller.php     # Admin platform KPIs & pending reviews
│       ├── user_controller.php          # User CRUD, live search, pagination
│       ├── restaurant_controller.php    # Restaurant status approval handler
│       └── order_controller.php        # Order fulfillment & rider assignment
│
├── manager/                             # Restaurant Manager Module
│   ├── models/
│   │   ├── RestaurantModel.php          # Manager restaurant ownership, update, availability toggle, deletion
│   │   ├── FoodModel.php                # Menu item insert, update, soft-delete (is_deleted = 1)
│   │   └── OrderModel.php               # Manager orders query, state transition, status logging
│   ├── controllers/
│   │   ├── restaurant_controller.php    # Restaurant registration, update, toggle availability, delete
│   │   ├── menu_controller.php          # Add, edit, soft-delete menu items
│   │   └── order_controller.php         # Accept/reject orders, forward-only status updates
│   └── views/
│       ├── dashboard.php                # Manager overview: KPI cards, restaurant list & actions
│       ├── menu.php                     # Menu item listing, add & edit modals
│       ├── orders.php                   # Live kitchen order queue & status updates
│       ├── order_detail.php             # Detailed order breakdown & customer info
│       ├── register_restaurant.php      # New restaurant registration form
│       └── restaurant_profile.php       # Restaurant profile edit & delete form
│
├── views/                               # Shared HTML presentation templates & partials
│   ├── auth/
│   │   ├── login.php                    # Login form interface
│   │   ├── register.php                 # Registration form interface
│   │   └── profile.php                  # Profile edit & account self-deletion interface
│   ├── admin/                           # Admin portal view templates
│   ├── customer/
│   │   └── dashboard.php                # Customer catalog & order tracking interface
│   └── partials/
│       ├── header.php                   # Global HTML head & stylesheet links
│       ├── navbar.php                   # Role-aware navigation bar component
│       └── footer.php                   # Global footer & scripts
│
├── assets/
│   └── js/
│       └── manager.js                   # Modal helpers (no AJAX)
│
├── style.css                            # Global FoodHub stylesheet (Bootstrap 5 + custom design system)
├── index.php                            # Root entrypoint router
├── login.php                            # Public login entrypoint
├── logout.php                           # Logout entrypoint
├── profile.php                          # User profile management entrypoint
├── manager_schema_migration.sql         # Migration SQL for Restaurant Manager module
├── database.sql                         # Full database schema & seed data
├── schema.sql                           # Synchronized schema file
└── README.md                            # Project documentation
```

---

## ✨ Features

### 🔐 Authentication & Profile Management
- **Role-Aware Login (`login.php`)**: Authenticates usernames or email addresses and routes users directly to their role-specific dashboard (`Admin`, `Restaurant Manager`, `Customer`, `Rider`).
- **Public Registration (`register.php`)**: Registration for `Customer`, `Restaurant Manager`, and `Rider` roles with secure `password_hash()` encryption.
- **Profile Management (`profile.php`)**: Update name, email, phone, and operational address.
- **Account Self-Deletion**: Danger Zone panel on profile page allows users to close/delete their own user account by entering `DELETE`.

### 🏪 Restaurant Manager Module (`/manager/`)
- **Restaurant Onboarding**: Register new restaurants (starts with `Pending` status until Admin approval).
- **Restaurant Management**: Update address, phone, cuisine, and description; toggle availability (`Open`/`Closed`) independently of admin status; delete restaurants with confirmation safeguards.
- **Menu Management**: Add, view, edit, and soft-delete menu items (`is_deleted = 1` flag ensures order history integrity).
- **Order Fulfillment Queue**:
  - View incoming customer orders in BDT Taka (`৳`).
  - Accept or reject pending orders (`Pending` $\rightarrow$ `Preparing` / `Cancelled`).
  - Update preparation status (`Preparing` $\rightarrow$ `Ready for Delivery`).
  - Order status change logging in `order_status_log`.

### 📊 Administrator Dashboard & Control Panel
- Platform KPI metrics (Users, Pending Approvals, Orders, Revenue).
- User Management: Live multi-column search, role filtering, pagination, user provisioning, edit, and deletion safeguards.
- Restaurant Approvals: Whitelisted status updates (`Pending`, `Approved`, `Rejected`, `Suspended`).

### 🛒 Customer Dashboard
- Restaurant catalog browsing and live order tracking in BDT Taka (`৳`).

---

## 🗄 Database Setup & Migrations

The database consists of **7 core relational tables**:

| Table | Description |
|:---|:---|
| `users` | User accounts (`Administrator`, `Customer`, `Restaurant Manager`, `Rider`) |
| `restaurants` | Partner restaurants linked to a manager |
| `restaurant_managers` | Manager-to-restaurant relationship tracking table |
| `food_items` | Menu items linked to restaurants (`is_deleted` soft-delete flag) |
| `orders` | Customer orders referencing restaurant and customer |
| `order_items` | Individual line items per order |
| `order_status_log` | Audit log for order status transitions |

### Setup Steps (XAMPP / phpMyAdmin)

1. Start **Apache** and **MySQL** in XAMPP.
2. Open **`http://localhost/phpmyadmin`**.
3. Import `database.sql` (or `schema.sql`) to set up base tables and seed users.
4. Import `manager_schema_migration.sql` to execute schema updates (`restaurant_managers`, `order_status_log`, and soft-delete flags).

---

## ▶ How to Run Locally

1. Place the repository in the XAMPP web root:
   ```
   C:\xampp\htdocs\FoodHub\
   ```
2. Verify database connection in `config/db.php`:
   ```php
   $conn = mysqli_connect("localhost", "root", "", "foodhub_db");
   ```
3. Open your browser and navigate to:
   ```
   http://localhost/FoodHub/
   ```

---

## 🔑 Default Credentials

| Role | Username | Password |
|:---|:---|:---|
| **Administrator** | `admin` | `admin123` |
| **Restaurant Manager** | `manager1` | `manager123` |
| **Restaurant Manager** | `manager2` | `manager123` |
| **Customer** | `customer1` | `customer123` |
| **Customer** | `customer2` | `customer123` |
| **Rider** | `rider1` | `rider123` |

---

## 🔒 Security Implementation

- **SQL Injection Protection**: Prepared statements (`mysqli_prepare` + `mysqli_bind_param`) across all database queries.
- **XSS Prevention**: Output escaping using `htmlspecialchars()` on all rendered variables.
- **Password Hashing**: `password_hash($password, PASSWORD_DEFAULT)` for secure credential storage.
- **RBAC Security Middleware**: `check_auth($allowed_roles)` included on all protected pages.
- **Data Integrity**: Soft deletion for food items (`is_deleted = 1`) preserves order history analytics.
