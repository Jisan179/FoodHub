# 🍔 FoodHub - Food Ordering and Delivery Platform
## Module: Customer Module (Pure Procedural PHP & MySQLi)

**FoodHub** is a server-side web application built for an online food ordering and delivery ecosystem. This repository contains the complete **Customer Module** implemented with **pure procedural PHP and procedural MySQLi** (with strictly zero classes, zero OOP objects, and zero PDO). The application enforces clean procedural separation between database operations (**models**), presentation templates (**views**), request orchestrators (**controllers**), and dedicated POST action handlers (**customer/actions**) using standard PHP file includes (`require_once` / `include_once`).

---

## 📋 Table of Contents

1. [Tech Stack](#-tech-stack)
2. [Procedural Architecture](#-procedural-architecture)
3. [Project Directory Structure](#-project-directory-structure)
4. [Customer Module Features](#-customer-module-features)
5. [Database Setup & Schema](#-database-setup--schema)
6. [How to Run Locally](#-how-to-run-locally)
7. [Default Seed Credentials](#-default-seed-credentials)
8. [Security & Transaction Integrity](#-security--transaction-integrity)

---

## 🛠 Tech Stack

| Layer | Technology |
|:---|:---|
| **Backend** | PHP (Pure Procedural PHP 8+, `mysqli_*` procedural functions) |
| **Database** | MySQL / MariaDB (via XAMPP) |
| **Frontend Styling** | Vanilla CSS (`assets/css/style.css` & `assets/css/customer.css`) |
| **Authentication** | PHP Native Sessions (`session_start()`) & Procedural RBAC Guard |
| **Server** | Apache (XAMPP local development stack) |

> **100% Procedural PHP Standard**: Zero classes, zero OOP objects (`->` / `new`), zero namespaces, and zero PDO. All database operations strictly utilize procedural `mysqli_*` functions (`mysqli_connect`, `mysqli_query`, `mysqli_prepare`, `mysqli_stmt_bind_param`, `mysqli_stmt_execute`, `mysqli_fetch_assoc`, `mysqli_real_escape_string`, `mysqli_begin_transaction`, `mysqli_commit`, `mysqli_rollback`).

---

## 🏛 Procedural Architecture

The customer application is organized into distinct procedural layers connected by standard file includes:

```
                            ┌──────────────────────────────────┐
                            │          Browser Client          │
                            └────────────────┬─────────────────┘
                                             │ HTTP GET / POST
                                             ▼
                     ┌─────────────────────────────────────────────────┐
                     │          Customer Module Entrypoints            │
                     │ (dashboard.php, customer/*.php, actions/*.php)  │
                     └───────────────────────┬─────────────────────────┘
                                             │
                      ┌──────────────────────┴──────────────────────┐
                      ▼                                             ▼
       ┌─────────────────────────────┐               ┌─────────────────────────────┐
       │     Controllers Layer       │               │      Auth Guard & RBAC      │
       │ (controllers/customer/*.php)│               │(auth/customer_auth_check.php│
       └──────────────┬──────────────┘               └─────────────────────────────┘
                      │
        ┌─────────────┴─────────────┐
        ▼                           ▼
┌──────────────────┐       ┌──────────────────┐
│   Models Layer   │       │   Views Layer    │
│(customer_model)  │       │ (views/customer) │
└────────┬─────────┘       └──────────────────┘
         │ Procedural MySQLi
         ▼
┌──────────────────┐
│ MySQL Database   │
└──────────────────┘
```

- **Database Connection (`config/db.php`)**: Establishes a procedural connection `$conn = mysqli_connect(...)` with UTF-8 (`utf8mb4`) charset.
- **Models Layer (`models/`)**: Encapsulates 100% of database queries inside pure procedural functions:
  - `customer_model.php`: 
    - *Catalog & Search*: `get_customer_approved_restaurants()`, `get_customer_restaurant_by_id()`, `get_customer_menu_items()`, `get_restaurant_categories()`, `search_food_and_restaurants()`, `get_food_item_by_id()`.
    - *Favorites*: `get_customer_favorites()`, `is_restaurant_favorited()`, `add_customer_favorite()`, `remove_customer_favorite()`.
    - *Shopping Cart*: `get_customer_cart()`, `get_cart_summary()`, `count_cart_items()`, `add_to_cart()` (single-restaurant enforced), `update_cart_quantity()`, `remove_from_cart()`, `clear_customer_cart()`.
    - *Orders & Transactions*: `place_customer_order()` (atomic DB transaction with anti-spoofing price verification), `get_customer_orders()`, `get_customer_order_details()`, `cancel_customer_order()`.
    - *Reviews & Ratings*: `can_review_food_item()`, `submit_food_review()`, `get_customer_reviews()`, `get_review_by_id()`, `update_food_review()`, `delete_food_review()`, `get_unreviewed_delivered_items()`.
    - *Utilities*: `resolve_customer_redirect()`.
  - `user_model.php`: `find_user_by_username_or_email()`, `find_user_by_id()`, `create_user()`, `update_user_profile()`, `update_user_password()`.
  - `order_model.php`: `get_customer_order_stats()`, `get_orders_by_customer_id()`.
  - `restaurant_model.php`: `get_approved_restaurants()`.
- **Controllers Layer (`controllers/customer/`)**: Request and business logic handlers:
  - `auth/customer_auth_check.php`: Procedural route guard checking `$_SESSION['role'] === 'Customer'`.
  - `dashboard_controller.php`: Gathers lifetime metrics, active order tracker, favorites count, reviews, unreviewed items, and recommended restaurants.
  - `browse_controller.php`: Handles multi-field restaurant search and dish filtering.
  - `menu_controller.php`: Fetches restaurant menu items grouped by category with live ratings.
  - `cart_controller.php`: Computes cart line totals, quantities, and delivery fee calculation.
  - `checkout_controller.php`: Validates cart availability, user profile address, and payment options.
  - `order_history_controller.php`: Gathers customer order history and live delivery statuses.
  - `order_track_controller.php`: Loads single order details, assigned rider information, and 5-stage progress timeline.
  - `favorites_controller.php`: Gathers saved favorite restaurants.
  - `reviews_controller.php`: Fetches customer submitted reviews and unreviewed delivered items.
- **Action Handlers (`customer/actions/`)**: Dedicated POST endpoints that execute business logic, manage session flash messages, and safely resolve redirection targets (`add_to_cart.php`, `update_cart_item.php`, `remove_from_cart.php`, `place_order.php`, `cancel_order.php`, `add_favorite.php`, `remove_favorite.php`, `submit_review.php`, `edit_review.php`, `delete_review.php`).
- **Views Layer (`views/customer/`)**: Clean HTML presentation templates decoupled from raw SQL queries:
  - `dashboard.php`: Welcome hero search, 5 KPI cards, live order tracking panel, review reminder cards, top restaurants, and order history overview.
  - `browse_restaurants.php`: Restaurant grid with search bar, ratings, item counts, and favorite toggles.
  - `view_menu.php`: Category filter tabs, dish list with prices, and "Add to Cart" triggers.
  - `cart.php`: Persistent cart table, quantity buttons (`+`/`−`), subtotal summary, and checkout button.
  - `checkout.php`: Delivery address confirmation, order review, payment selection, and submit order.
  - `order_track.php`: 5-step progress stepper, order breakdown, assigned rider info, and cancellation button.
  - `order_history.php`: Comprehensive order history table with status badges and detail buttons.
  - `favorites.php`: Saved favorite restaurant spots with quick menu ordering links.
  - `reviews.php`: Delivered meal review submission forms and submitted review manager (edit/delete).
- **Partials (`views/partials/`)**:
  - `header.php`: Global HTML `<head>`, responsive viewports, and dynamic CSS path resolver.
  - `navbar.php`: Multi-role navigation bar with dynamic active states and live cart counter badge.
  - `footer.php`: Global footer component.

---

## 📁 Project Directory Structure

```
FoodHub/
│
├── config/
│   └── db.php                            # Procedural mysqli_connect() database connection
│
├── models/                               # Pure procedural SQL query functions
│   ├── customer_model.php                # Complete Customer CRUD, cart, orders, reviews, favorites
│   ├── delivery_model.php                # Delivery lookups and rider management
│   ├── order_model.php                   # Order listings, revenue stats, & customer stats
│   ├── restaurant_model.php              # Restaurant listings & menu queries
│   └── user_model.php                    # User authentication, profiles, & account CRUD
│
├── controllers/                          # Procedural business logic handlers
│   ├── dashboard_controller.php          # Unified multi-role dashboard router
│   ├── auth/
│   │   ├── auth_check.php                # Session guard redirecting unauthorized users
│   │   ├── customer_auth_check.php       # Customer role authorization guard
│   │   ├── login_controller.php          # Credential verification & session initialization
│   │   ├── logout_controller.php         # Session termination & safe sign-out
│   │   ├── register_controller.php       # Public user registration handler
│   │   ├── profile_controller.php        # Profile update & self-deactivation handler
│   │   ├── change_password_controller.php# Password update controller
│   │   └── forgot_password_controller.php# Password reset controller
│   └── customer/
│       ├── browse_controller.php         # Restaurant catalog & search handler
│       ├── cart_controller.php           # Customer shopping cart logic
│       ├── checkout_controller.php       # Checkout validation handler
│       ├── dashboard_controller.php      # Customer KPI metrics & quick views
│       ├── favorites_controller.php      # Favorite spots list handler
│       ├── menu_controller.php           # Restaurant menu & category filters
│       ├── order_history_controller.php  # Order history listing handler
│       ├── order_track_controller.php    # Live order tracking stepper handler
│       └── reviews_controller.php        # Feedback & rating center handler
│
├── customer/                             # Customer Module URL Entrypoints
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
│       ├── place_order.php               # Transactional checkout & order placement
│       ├── remove_favorite.php           # Remove from favorites
│       ├── remove_from_cart.php          # Remove cart line item
│       ├── submit_review.php             # Submit food item rating
│       └── update_cart_item.php          # Update item quantity
│
├── views/                                # Procedural HTML view templates
│   ├── customer/                         # Customer UI views
│   │   ├── browse_restaurants.php        # Restaurant catalog & search view
│   │   ├── cart.php                      # Shopping cart view
│   │   ├── checkout.php                  # Checkout view
│   │   ├── dashboard.php                 # Customer dashboard view
│   │   ├── favorites.php                 # Favorites view
│   │   ├── order_history.php             # Order history view
│   │   ├── order_track.php               # Order tracking timeline view
│   │   ├── reviews.php                   # Reviews & ratings view
│   │   └── view_menu.php                 # Restaurant menu & category view
│   ├── auth/                             # Login, register, profile, password views
│   │   ├── change-password.php
│   │   ├── forgot-password.php
│   │   ├── login.php
│   │   ├── profile.php
│   │   └── register.php
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
- **Personalized Greeting**: Displays customer name with instant search bar for dishes and restaurants.
- **5 KPI Metric Cards**:
  - `Total Orders`: Lifetime orders placed by customer.
  - `Active Orders`: Orders currently being prepared or delivered.
  - `Favorite Spots`: Count of bookmarked restaurants.
  - `Reviews Shared`: Verified reviews submitted.
  - `Total Spent`: Lifetime monetary amount spent across delivered orders.
- **Live Active Orders Tracker Panel**: Highlights in-progress orders with real-time status badges, assigned rider contact information, and direct **"Track Live 📍"** links.
- **Review Reminder Banner**: Prompts customer to rate dishes from recent completed deliveries.
- **Top Restaurants Grid**: Displays top-rated partner restaurants with favorite toggles and direct menu links.
- **Recent Orders Table**: Quick overview of recent orders with status badges and detail links.

### 2. 🍔 Restaurant Browsing & Menu Catalog
- **Global Search**: Multi-field search across restaurant names, descriptions, addresses, and individual food items.
- **Restaurant Details & Ratings**: Average star rating (1–5 stars), total review count, available menu count, and contact information.
- **Category Filter Tabs**: Group dishes by category (`Main Course`, `Burgers`, `Appetizer`, `Sides`, etc.).
- **Live Favorite Toggle**: Heart icon button (❤️/🤍) allowing 1-click favorite additions and removals directly from browse cards or menu headers.

### 3. 🛒 Persistent Shopping Cart
- **Database Persistence**: Cart items are stored in the database (`cart` table) linked to the authenticated customer ID.
- **Single-Restaurant Constraint**: Enforces ordering from one restaurant at a time.
- **Smart Conflict Resolution Modal**: When adding an item from a different restaurant, a conflict modal prompts the customer to either clear existing items or keep the current cart.
- **Inline Quantity Controls**: Fast `+` / `−` quantity adjustments and line-item deletion.
- **Dynamic Cost Breakdown**: Real-time subtotal and flat delivery fee (`৳50.00`) calculation.
- **Live Cart Badge**: Navbar badge dynamically displays current total item count.

### 4. 💳 Transactional Server-Secured Checkout
- **Anti-Price Spoofing**: Prices are locked and recalculated server-side within the database transaction (`mysqli_begin_transaction`) using active `food_items` records.
- **Pre-Populated Address & Phone**: Automatically fills saved customer address and phone number with editable destination fields.
- **Payment Method Selection**: Supports `Cash on Delivery`.
- **Atomic Order Placement**: Inserts order header (`orders`), creates line items (`order_items`), initializes delivery record (`deliveries`), and clears cart (`cart`) in an atomic transaction with automatic rollback on error.

### 5. 📍 Live Order Tracking & History
- **5-Stage Visual Stepper**:
  1. `Order Placed` (Pending)
  2. `Kitchen Preparing` (Preparing)
  3. `Ready for Delivery` (Ready)
  4. `Out for Delivery` (Rider assigned & en route)
  5. `Delivered` (Completed)
- **Assigned Rider Information**: Displays rider name and contact phone number once assigned.
- **Cancellation Safeguard**: Customers can cancel orders only if status is `Pending` or `Preparing`; delivered or out-for-delivery orders are protected.
- **Chronological History**: Full order history table with status badges, item counts, dates, and track actions.

### 6. ⭐ Verified Food Ratings & Reviews
- **Delivered Orders Only**: Reviews can only be submitted for dishes from verified `Delivered` orders.
- **1-5 Star Ratings & Comments**: Star rating selection and feedback comment textarea.
- **Review Management**: Full capability to edit existing reviews or delete past reviews.
- **Aggregate Rating Integration**: Individual dish ratings dynamically calculate restaurant overall average ratings.

### 7. 👤 Profile & Security Management
- **Profile Details**: View and update full name, email, phone number, and delivery address.
- **Change Password**: Update account password with old password verification.
- **Account Closure**: Option to deactivate or delete account with confirmation safeguards.

---

## 🗄 Database Setup & Schema

The database contains **9 relational tables**:

| Table | Primary Key | Foreign Keys | Description |
|:---|:---|:---|:---|
| `users` | `user_id` | - | Customer, Administrator, Manager, and Rider accounts |
| `restaurants` | `restaurant_id` | `user_id` -> `users` | Partner restaurants linked to Restaurant Managers |
| `food_items` | `item_id` | `restaurant_id` -> `restaurants` | Menu items with price, category, and availability status |
| `orders` | `order_id` | `customer_id`, `restaurant_id` -> `users`, `restaurants` | Order records with total amount, status, and delivery address |
| `order_items` | `order_item_id` | `order_id`, `item_id` -> `orders`, `food_items` | Line items with quantity and unit price |
| `deliveries` | `delivery_id` | `order_id`, `rider_id` -> `orders`, `users` | Delivery tracking records linking orders to Riders |
| `favorites` | `favorite_id` | `customer_id`, `restaurant_id` -> `users`, `restaurants` | Saved favorite restaurants per customer |
| `cart` | `cart_id` | `customer_id`, `item_id` -> `users`, `food_items` | Persistent customer cart entries |
| `reviews` | `review_id` | `customer_id`, `order_id`, `item_id` -> `users`, `orders`, `food_items` | Verified ratings (1–5) and comments for ordered dishes |

### Import Steps (XAMPP / phpMyAdmin)

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Open **`http://localhost/phpmyadmin`** in your browser.
3. Select the **"Import"** tab.
4. Choose `database.sql` (or `schema.sql`) from the FoodHub directory.
5. Click **"Go"** / **"Import"** to create the `foodhub_db` database and populate initial seed data.

---

## ▶ How to Run Locally

1. Place the project in your XAMPP web root directory:
   ```
   C:\xampp\htdocs\WebTech_Summer25-26\FoodHub\
   ```
2. Verify database connection settings in `config/db.php`:
   ```php
   $host = "localhost";
   $user = "root";
   $pass = "";
   $db   = "foodhub_db";
   ```
3. Open your browser and navigate to:
   ```
   http://localhost/WebTech_Summer25-26/FoodHub/
   ```
   You will automatically be routed to the login page.

---

## 🔑 Default Seed Credentials

All accounts are pre-configured with bcrypt hashes and plain-text fallbacks:

| Role | Username | Password | Access Area |
|:---|:---|:---|:---|
| **Customer** | `customer1` | `customer123` | Customer Dashboard, Food Browsing, Cart, Orders, Reviews |
| **Customer** | `customer2` | `customer123` | Customer Dashboard, Food Browsing, Cart, Orders, Reviews |
| **Administrator** | `admin` | `admin123` | Admin Management Portal |
| **Restaurant Manager** | `manager1` | `manager123` | Spice Grill House Manager Portal |
| **Restaurant Manager** | `manager2` | `manager123` | The Burger Spot Manager Portal |
| **Rider** | `rider1` | `rider123` | Rider Delivery Portal |
| **Rider** | `rider2` | `rider123` | Rider Delivery Portal |

---

## 🔒 Security & Transaction Integrity

1. **SQL Injection Prevention**:
   - All input parameters are sanitized with `mysqli_real_escape_string($conn, ...)` and strict type casting (`intval()`, `floatval()`).
   - Parameter-heavy procedures utilize MySQLi prepared statements (`mysqli_prepare`, `mysqli_stmt_bind_param`, `mysqli_stmt_execute`).
2. **Cross-Site Scripting (XSS) Protection**:
   - All view outputs are escaped using `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
3. **Role-Based Access Control (RBAC)**:
   - Protected customer routes include `controllers/auth/customer_auth_check.php` enforcing `check_auth(['Customer'])`.
   - Unauthorized access attempts are redirected to the appropriate portal with flash error notifications.
4. **Database Transaction Atomicity**:
   - Order placement executes inside atomic transactions with `mysqli_begin_transaction($conn)`, verifying line item prices against active catalog values, committing (`mysqli_commit`) only when all records are created, and rolling back (`mysqli_rollback`) on failure.
5. **Anti-Price Spoofing**:
   - Prices submitted from client forms are ignored during checkout; totals are recalculated directly from locked database records.
