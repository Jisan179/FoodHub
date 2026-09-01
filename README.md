# 🍔 FoodHub - Online Food Ordering & Delivery Platform
## Module: Customer Module & Unified Multi-Role Architecture (Pure Procedural PHP & MySQLi)

**FoodHub** is a server-side food ordering and delivery web application built with **pure procedural PHP and procedural MySQLi** (strictly zero classes, zero OOP objects, and zero PDO). It follows a clean procedural MVC architecture separating database queries (**models**), business logic & request routing (**controllers**), action handlers (**customer/actions**), and presentation templates (**views**).

---

## 📋 Table of Contents

1. [Tech Stack](#-tech-stack)
2. [Procedural Architecture](#-procedural-architecture)
3. [Project Directory Structure](#-project-directory-structure)
4. [Customer Module Features](#-customer-module-features)
5. [Multi-Role Platform Capabilities](#-multi-role-platform-capabilities)
6. [Database Schema (9 Relational Tables)](#-database-schema-9-relational-tables)
7. [How to Run Locally](#-how-to-run-locally)
8. [Default Seed Credentials](#-default-seed-credentials)
9. [Security & Transaction Integrity](#-security--transaction-integrity)

---

## 🛠 Tech Stack

| Layer | Technology |
|:---|:---|
| **Backend** | PHP (Pure Procedural PHP 8+, `mysqli_*` procedural functions) |
| **Database** | MySQL / MariaDB (via XAMPP) |
| **Frontend Styling** | Vanilla CSS (`assets/css/style.css` & `assets/css/customer.css`) |
| **Authentication** | Native PHP Sessions (`session_start()`) & RBAC Middleware |
| **Server** | Apache (XAMPP local development environment) |

> **Strict 100% Procedural PHP Standard**: Zero OOP objects (`->` / `new`), zero classes, zero namespaces, and zero PDO. All database operations strictly utilize procedural `mysqli_*` functions (`mysqli_connect`, `mysqli_query`, `mysqli_prepare`, `mysqli_stmt_bind_param`, `mysqli_stmt_execute`, `mysqli_fetch_assoc`, `mysqli_real_escape_string`, `mysqli_begin_transaction`, `mysqli_commit`, `mysqli_rollback`).

---

## 🏛 Procedural Architecture

```
                                  ┌────────────────────────┐
                                  │      Browser Client    │
                                  └───────────┬────────────┘
                                              │ HTTP Requests
                                              ▼
                        ┌───────────────────────────────────────────┐
                        │      Entry Points & Route Handlers        │
                        │  (dashboard.php, customer/*.php, actions) │
                        └─────────────────────┬─────────────────────┘
                                              │
                       ┌──────────────────────┴──────────────────────┐
                       ▼                                             ▼
        ┌─────────────────────────────┐               ┌─────────────────────────────┐
        │     Controllers Layer       │               │      Middleware Guards      │
        │ (controllers/customer/*.php)│               │   (includes/auth_check.php) │
        └──────────────┬──────────────┘               └─────────────────────────────┘
                       │
         ┌─────────────┴─────────────┐
         ▼                           ▼
┌──────────────────┐       ┌──────────────────┐
│   Models Layer   │       │   Views Layer    │
│(customer_model)  │       │(views/customer)  │
└────────┬─────────┘       └──────────────────┘
         │ Procedural MySQLi
         ▼
┌──────────────────┐
│ MySQL (foodhub)  │
└──────────────────┘
```

The application is modularized into clearly decoupled procedural components:

- **Database Connection (`config/db.php`)**: Connects procedurally via `mysqli_connect()` with `utf8mb4` encoding.
- **Models Layer (`models/`)**: Contains all SQL queries encapsulated inside reusable procedural functions:
  - `customer_model.php`: Restaurant browsing, food catalog, favorites, persistent cart, transactional order placement, order tracking, and review operations.
  - `user_model.php`: User lookups, registration, profile updates, and authentication helpers.
  - `restaurant_model.php`: Restaurant listings, status management, and menu queries.
  - `order_model.php`: Orders retrieval, revenue calculations, and customer order statistics.
  - `delivery_model.php`: Delivery tracking, rider assignment, and status updates.
- **Controllers Layer (`controllers/`)**: Handles request parameters, session state, and queries:
  - `dashboard_controller.php`: Role-based router gathering stats for Customer, Manager, Rider, or Admin.
  - `customer/browse_controller.php`: Restaurant search, catalog queries, and food filtering.
  - `customer/menu_controller.php`: Restaurant menu item retrieval and category grouping.
  - `customer/cart_controller.php`: Cart item summation, quantity calculations, and delivery fee computation.
  - `customer/checkout_controller.php`: Pre-checkout cart verification and user address population.
  - `customer/order_history_controller.php`: Past and active order history records.
  - `customer/order_track_controller.php`: Individual order tracking status and delivery rider assignment.
  - `customer/favorites_controller.php`: Saved favorite restaurant spots.
  - `customer/reviews_controller.php`: Feedback manager and unreviewed delivered item reminders.
- **Action Handlers (`customer/actions/`)**: Clean POST endpoints with redirect resolution (`add_to_cart.php`, `update_cart_item.php`, `remove_from_cart.php`, `place_order.php`, `cancel_order.php`, `add_favorite.php`, `remove_favorite.php`, `submit_review.php`, `edit_review.php`, `delete_review.php`).
- **Views Layer (`views/`)**: HTML view templates styled with dedicated Customer styles (`customer.css`) and global components (`navbar.php`, `header.php`, `footer.php`).

---

## 📁 Project Directory Structure

```
FoodHub/
│
├── config/
│   └── db.php                            # Procedural mysqli_connect() database connection
│
├── models/                               # Procedural SQL query functions
│   ├── customer_model.php                # Complete Customer CRUD, cart, orders, reviews, favorites
│   ├── delivery_model.php                # Delivery lookups and rider management
│   ├── order_model.php                   # Order listings, revenue stats, and status updates
│   ├── restaurant_model.php              # Restaurant listings and menu items
│   └── user_model.php                    # User authentication, profiles, and account CRUD
│
├── controllers/                          # Procedural request & business logic
│   ├── dashboard_controller.php          # Unified multi-role dashboard router
│   ├── auth/
│   │   ├── auth_check.php                # Admin auth guard
│   │   ├── customer_auth_check.php       # Customer RBAC guard
│   │   ├── login_controller.php          # Credential verification & session initialization
│   │   ├── logout_controller.php         # Session termination & safe sign-out
│   │   ├── register_controller.php       # Public user registration handler
│   │   ├── profile_controller.php        # Profile update & self-deactivation handler
│   │   ├── change_password_controller.php# Password update controller
│   │   └── forgot_password_controller.php# Password reset controller
│   ├── customer/
│   │   ├── browse_controller.php         # Restaurant catalog & search
│   │   ├── cart_controller.php           # Customer shopping cart logic
│   │   ├── checkout_controller.php       # Checkout validation
│   │   ├── dashboard_controller.php      # Customer KPI metrics & quick views
│   │   ├── favorites_controller.php      # Favorite spots list
│   │   ├── menu_controller.php           # Restaurant menu & category filters
│   │   ├── order_history_controller.php  # Order history listing
│   │   ├── order_track_controller.php    # Live order tracking stepper
│   │   └── reviews_controller.php        # Feedback & rating center
│   └── admin/
│       ├── dashboard_controller.php      # Admin analytics
│       ├── order_controller.php          # Order fulfillment & rider dispatch
│       ├── restaurant_controller.php     # Restaurant approvals
│       └── user_controller.php           # User management
│
├── customer/                             # Customer Module Entrypoints
│   ├── browse_restaurants.php            # Browse & search food & restaurants
│   ├── cart.php                          # View & edit cart
│   ├── checkout.php                      # Place order form
│   ├── dashboard.php                     # Customer dashboard
│   ├── favorites.php                     # My favorite restaurants
│   ├── order_history.php                 # Orders history list
│   ├── order_track.php                   # Live order progress tracking
│   ├── reviews.php                       # Reviews & ratings center
│   ├── view_menu.php                     # Restaurant food items menu
│   └── actions/                          # Dedicated POST Action Handlers
│       ├── add_favorite.php              # Add to favorites
│       ├── add_to_cart.php               # Add item to cart (single-restaurant enforced)
│       ├── cancel_order.php              # Cancel eligible order
│       ├── delete_review.php             # Delete review
│       ├── edit_review.php               # Edit review
│       ├── place_order.php               # Transactional checkout & order submission
│       ├── remove_favorite.php           # Remove from favorites
│       ├── remove_from_cart.php          # Remove cart line item
│       ├── submit_review.php             # Submit food item rating
│       └── update_cart_item.php          # Update item quantity
│
├── views/                                # HTML presentation templates
│   ├── auth/                             # Login, register, profile, password views
│   │   ├── change-password.php
│   │   ├── forgot-password.php
│   │   ├── login.php
│   │   ├── profile.php
│   │   └── register.php
│   ├── customer/                         # Customer UI views
│   │   ├── browse_restaurants.php
│   │   ├── cart.php
│   │   ├── checkout.php
│   │   ├── dashboard.php
│   │   ├── favorites.php
│   │   ├── order_history.php
│   │   ├── order_track.php
│   │   ├── reviews.php
│   │   └── view_menu.php
│   ├── admin/                            # Admin views (dashboard, users, orders, restaurants)
│   ├── manager/                          # Restaurant Manager dashboard view
│   ├── rider/                            # Rider dashboard view
│   └── partials/                         # Shared partials
│       ├── footer.php                    # Global footer markup
│       ├── header.php                    # Head metadata & stylesheet link resolver
│       └── navbar.php                    # Multi-role navigation bar with dynamic cart badge
│
├── assets/
│   └── css/
│       ├── customer.css                  # Customer module dedicated styling & micro-animations
│       └── style.css                     # Global design system & theme variables
│
├── admin/                                # Admin entrypoints
├── dashboard.php                         # Unified root dashboard router
├── index.php                             # Root entrypoint router
├── login.php                             # Root sign-in page
├── logout.php                            # Root logout endpoint
├── register.php                          # Root public registration page
├── profile.php                           # Root profile management page
├── change-password.php                   # Root change password page
├── forgot-password.php                   # Root password recovery page
├── database.sql                          # Full database schema & seed data
├── schema.sql                            # Production database schema
└── README.md                             # Project documentation
```

---

## ✨ Customer Module Features

### 1. 🏠 Customer Dashboard
- **Welcome Hero Banner**: Personalized customer greeting with instant food search input and profile quick-link.
- **5 KPI Metric Cards**:
  - `Total Orders`: Lifetime count of orders placed.
  - `Active Orders`: Currently in preparation or out for delivery.
  - `Favorite Spots`: Total bookmarked restaurants.
  - `Reviews Shared`: Verified reviews submitted.
  - `Total Spent`: Lifetime monetary expenditure across delivered orders.
- **Live Active Orders Tracker Panel**: Displays active orders with real-time status badges, assigned delivery rider details, and direct **"Track Live 📍"** buttons.
- **Meal Review Reminders**: Banner reminding customers to rate dishes from recent delivered orders.
- **Top Restaurants Near You**: Grid displaying ratings, item counts, favorite toggles, and direct menu ordering links.
- **Recent Order History Table**: Quick overview of recent orders with status badges and details.

### 2. 🍔 Restaurant Browsing & Menu Catalog
- **Multi-Field Search**: Real-time searching across restaurant names, cuisine descriptions, addresses, and individual food item names.
- **Restaurant Details & Badges**: Average star ratings, review counts, active menu counts, and contact information.
- **Category Filtering**: Filter menu items by category (`Main Course`, `Burgers`, `Appetizer`, `Sides`, etc.).
- **Live Favorite Toggle**: Heart icon button (❤️/🤍) allowing 1-click favorite additions and removals directly from browse cards or menu headers.

### 3. 🛒 Persistent Cart Management
- **Database Persistence**: Cart items are stored in the database (`cart` table) linked to the customer account.
- **Single-Restaurant Constraint**: Enforces ordering from one restaurant at a time.
- **Cart Conflict Resolution**: If an item is added from a different restaurant, a conflict banner prompts the user to either replace the existing cart or keep current items.
- **Quantity Controls**: Inline `+` and `−` quantity adjustments and line-item removal.
- **Price Calculation**: Dynamic subtotal and standard delivery fee calculation.
- **Live Cart Badge**: Navbar badge automatically reflects total item count in real-time.

### 4. 💳 Transactional Server-Secured Checkout
- **Anti-Price Spoofing**: Prices are locked and recalculated server-side within the database transaction (`mysqli_begin_transaction`) using active `food_items` records.
- **Address & Payment Selection**: Pre-populates customer default address with option to update delivery destination and payment method (`Cash on Delivery`).
- **Atomic Order Creation**: Inserts order header (`orders`), creates individual line items (`order_items`), initializes delivery record (`deliveries`), and clears cart (`cart`) in an atomic transaction with automatic rollback on error.

### 5. 📍 Live Order Tracking & History
- **5-Stage Visual Stepper**:
  1. `Order Placed` (Pending)
  2. `Kitchen Preparing` (Preparing)
  3. `Ready for Delivery` (Ready)
  4. `Out for Delivery` (Rider assigned & en route)
  5. `Delivered` (Completed)
- **Rider Information**: Displays assigned rider name and contact phone number.
- **Order Cancellation**: Customers can cancel orders that are still in `Pending` or `Preparing` status; completed/out-for-delivery orders are safeguarded against cancellation.
- **Complete Order History**: Chronological table of all past orders with status badges, item counts, and totals.

### 6. ⭐ Verified Food Ratings & Reviews
- **Delivered Orders Only**: Reviews can only be submitted for dishes from verified `Delivered` orders.
- **1-5 Star Ratings & Comments**: Rating selector and feedback textarea.
- **Review Management**: Full capability to update existing reviews or delete past reviews.
- **Dynamic Aggregate Ratings**: Item and restaurant star ratings are computed live across all customer reviews.

---

## 👥 Multi-Role Platform Capabilities

| Role | Access URL | Capabilities |
|:---|:---|:---|
| **Customer** | `dashboard.php` / `customer/` | Browse restaurants, manage cart, place orders, live track delivery, write reviews, manage favorites. |
| **Administrator** | `admin/dashboard.php` | System KPIs, user management (CRUD), restaurant approvals, order fulfillment, rider assignment. |
| **Restaurant Manager** | `dashboard.php` | Manage restaurant profile, menu items, incoming orders, and kitchen preparation statuses. |
| **Rider** | `dashboard.php` | View available delivery orders, accept/claim deliveries, and update delivery status. |

---

## 🗄 Database Schema (9 Relational Tables)

The database schema is defined in `database.sql` and `schema.sql`:

```
┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│    users     │1       *│ restaurants  │1       *│  food_items  │
│──────────────│─────────│──────────────│─────────│──────────────│
│ user_id (PK) │         │ rest_id (PK) │         │ item_id (PK) │
│ role, status │         │ user_id (FK) │         │ rest_id (FK) │
└──────┬───────┘         └──────┬───────┘         └──────┬───────┘
       │1                       │1                       │1
       │                        │                        │
       │*                       │*                       │*
┌──────┴───────┐         ┌──────┴───────┐         ┌──────┴───────┐
│  favorites   │         │    orders    │1       *│  order_items │
│──────────────│         │──────────────│─────────│──────────────│
│ fav_id (PK)  │         │ order_id(PK) │         │ ord_item(PK) │
│ cust_id (FK) │         │ cust_id (FK) │         │ order_id(FK) │
│ rest_id (FK) │         │ rest_id (FK) │         │ item_id (FK) │
└──────────────┘         └──────┬───────┘         └──────────────┘
                                │1
                                │
                                │1
                         ┌──────┴───────┐         ┌──────────────┐
                         │  deliveries  │         │     cart     │
                         │──────────────│         │──────────────│
                         │ deliv_id(PK) │         │ cart_id (PK) │
                         │ order_id(FK) │         │ cust_id (FK) │
                         │ rider_id(FK) │         │ item_id (FK) │
                         └──────────────┘         └──────────────┘
                                                         │
                         ┌──────────────┐                │
                         │   reviews    │                │
                         │──────────────│                │
                         │ rev_id (PK)  │                │
                         │ cust_id (FK) │                │
                         │ order_id(FK) │                │
                         │ item_id (FK) ◄────────────────┘
                         └──────────────┘
```

| Table | Primary Key | Foreign Keys | Purpose |
|:---|:---|:---|:---|
| `users` | `user_id` | - | Authentication and profiles for Admin, Customer, Manager, Rider |
| `restaurants` | `restaurant_id` | `user_id` -> `users` | Approved and pending dining partner entities |
| `food_items` | `item_id` | `restaurant_id` -> `restaurants` | Menu catalog items with pricing and category |
| `orders` | `order_id` | `customer_id`, `restaurant_id` -> `users`, `restaurants` | Order headers with status and delivery address |
| `order_items` | `order_item_id` | `order_id`, `item_id` -> `orders`, `food_items` | Individual line items and locked purchase prices |
| `deliveries` | `delivery_id` | `order_id`, `rider_id` -> `orders`, `users` | Dispatch status and assigned delivery riders |
| `favorites` | `favorite_id` | `customer_id`, `restaurant_id` -> `users`, `restaurants` | Saved favorite restaurants per customer |
| `cart` | `cart_id` | `customer_id`, `item_id` -> `users`, `food_items` | Persistent customer cart entries |
| `reviews` | `review_id` | `customer_id`, `order_id`, `item_id` -> `users`, `orders`, `food_items` | Verified ratings & text comments |

---

## ▶ How to Run Locally

### 1. Prerequisites
- **XAMPP** (PHP 8.0+ and MySQL / MariaDB).

### 2. Setup Directory
Place the repository in your XAMPP `htdocs` directory:
```
C:\xampp\htdocs\WebTech_Summer25-26\FoodHub\
```

### 3. Database Configuration & Import
1. Start **Apache** and **MySQL** from the XAMPP Control Panel.
2. Open **phpMyAdmin** at `http://localhost/phpmyadmin`.
3. Create database `foodhub_db` (or import `database.sql` directly).
4. Select the **Import** tab, choose `database.sql` (or `schema.sql`), and click **Import / Go**.
5. Verify database credentials in `config/db.php`:
   ```php
   $host = "localhost";
   $user = "root";
   $pass = "";
   $db   = "foodhub_db";
   ```

### 4. Access the Application
Open your browser and navigate to:
```
http://localhost/WebTech_Summer25-26/FoodHub/
```
You will be routed to the unified login page.

---

## 🔑 Default Seed Credentials

All accounts are pre-seeded and verified with standard bcrypt hashes and plain-text fallback:

| Role | Username | Password | Access Area |
|:---|:---|:---|:---|
| **Customer** | `customer1` | `customer123` | Customer Dashboard & Food Catalog |
| **Customer** | `customer2` | `customer123` | Customer Dashboard & Food Catalog |
| **Administrator** | `admin` | `admin123` | Full Admin Management Portal |
| **Restaurant Manager** | `manager1` | `manager123` | Spice Grill House Manager Portal |
| **Restaurant Manager** | `manager2` | `manager123` | The Burger Spot Manager Portal |
| **Rider** | `rider1` | `rider123` | Rider Delivery Portal |
| **Rider** | `rider2` | `rider123` | Rider Delivery Portal |

---

## 🔒 Security & Transaction Integrity

1. **SQL Injection Protection**:
   - All dynamic SQL parameters are sanitized using `mysqli_real_escape_string($conn, ...)` and strict type casting (`intval()`, `floatval()`).
   - Prepared statements (`mysqli_prepare`, `mysqli_stmt_bind_param`) are utilized on parameter-heavy models.
2. **Cross-Site Scripting (XSS) Sanitization**:
   - All user-supplied output in views is escaped through `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
3. **Role-Based Access Control (RBAC)**:
   - Protected routes execute `check_auth(['Customer'])` / `check_auth(['Administrator'])`.
   - Unauthorized attempts are trapped and redirected to the user's appropriate portal with flash alerts.
4. **Database Transaction Atomicity**:
   - Order placement executes inside atomic transactions with `mysqli_begin_transaction($conn)`, verifying line item prices against active catalog values, committing only when all line items and delivery records are created, and rolling back (`mysqli_rollback($conn)`) on failure.
5. **Session Security**:
   - Secure session handling with flash message management and proper destruction on sign-out.
