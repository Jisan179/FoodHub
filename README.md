# 🍔 FoodHub - Customer Module

**FoodHub** is an online food ordering and delivery web application. This repository contains the **Customer Module**, developed using **pure procedural PHP and procedural MySQLi** (strictly zero OOP classes/objects, zero PDO) following a clean procedural MVC structure.

---

## 🛠 Tech Stack

| Component | Technology |
|:---|:---|
| **Backend** | Pure Procedural PHP 8+ (`mysqli_*` functions) |
| **Database** | MySQL / MariaDB (via XAMPP) |
| **Frontend** | HTML5, Vanilla CSS (`customer.css` & `style.css`) |
| **Authentication** | Native PHP Sessions & Procedural Role Guards |
| **Local Server** | Apache / XAMPP |

---

## ✨ Key Features

- 🏠 **Customer Dashboard**: Overview KPI cards (total orders, active orders, favorite spots, reviews, total spent), live active orders banner, review reminders, and top restaurant recommendations.
- 🍔 **Restaurant Browsing & Menu Catalog**: Global search across restaurants and food items, category filtering, star ratings, and menu pricing.
- ❤️ **Favorites System**: 1-click favorite bookmarking (❤️/🤍) directly from restaurant cards and menu pages.
- 🛒 **Persistent Shopping Cart**: Database-backed cart with single-restaurant enforcement, conflict resolution prompts, and live navbar item counter badge.
- 💳 **Secure Checkout**: Server-side price recalculation within database transactions (`mysqli_begin_transaction`) to prevent client price spoofing.
- 📍 **Real-Time Live Order Tracking**: Visual 5-stage progress timeline (`Order Placed` ➔ `Kitchen Preparing` ➔ `Ready for Delivery` ➔ `Out for Delivery` ➔ `Delivered`) with assigned rider details and cancellation options for pending orders.
- ⭐ **Ratings & Food Reviews**: Verified review submission for delivered items (1–5 stars with comments), along with edit and delete capabilities.
- 👤 **Profile & Security**: Update profile details, change passwords with current-password verification, and account deactivation.

---

## 📁 Directory Structure

```
FoodHub/
├── config/
│   └── db.php                     # Procedural mysqli connection
├── models/
│   └── customer_model.php         # Customer SQL operations (browsing, cart, orders, reviews)
├── controllers/
│   ├── dashboard_controller.php   # Main dashboard router
│   ├── auth/                      # Login, register, profile, and auth guards
│   └── customer/                  # Browse, cart, checkout, tracking, and review controllers
├── customer/
│   ├── browse_restaurants.php     # Browse restaurants entrypoint
│   ├── cart.php                   # Shopping cart entrypoint
│   ├── checkout.php               # Checkout entrypoint
│   ├── dashboard.php              # Customer dashboard entrypoint
│   ├── favorites.php              # Favorites entrypoint
│   ├── order_history.php          # Order history entrypoint
│   ├── order_track.php            # Live order tracker entrypoint
│   ├── reviews.php                # Reviews entrypoint
│   ├── view_menu.php              # Restaurant menu entrypoint
│   └── actions/                   # POST action handlers (add_to_cart, place_order, etc.)
├── views/
│   ├── customer/                  # Customer HTML templates
│   ├── auth/                      # Authentication views (login, register, profile)
│   └── partials/                  # Shared header, navbar, and footer
├── assets/
│   └── css/                       # customer.css and style.css
├── database.sql                   # Database schema with 9 tables and seed data
├── index.php                      # Root entrypoint
├── login.php                      # Sign-in page
└── README.md                      # Project documentation
```

---

## 🗄 Database Schema

The database `foodhub_db` comprises **9 relational tables**:

1. `users` – Customer, Admin, Manager, and Rider accounts
2. `restaurants` – Dining partners and manager associations
3. `food_items` – Menu catalog items and pricing
4. `orders` – Order headers, totals, and statuses
5. `order_items` – Line items with locked purchase prices
6. `deliveries` – Order delivery dispatch and assigned riders
7. `favorites` – Customer saved restaurants
8. `cart` – Persistent cart items per customer
9. `reviews` – Verified customer ratings and feedback

---

## ▶ How to Run Locally

### 1. Prerequisites
- Install and start **XAMPP** (Apache + MySQL).

### 2. Place Project in `htdocs`
Clone or copy the project into your XAMPP directory:
```
C:\xampp\htdocs\WebTech_Summer25-26\FoodHub\
```

### 3. Import Database
1. Open **phpMyAdmin** at `http://localhost/phpmyadmin`.
2. Click **Import**, select `database.sql` (or `schema.sql`), and click **Go**.
3. Confirm database credentials in `config/db.php`:
   ```php
   $conn = mysqli_connect("localhost", "root", "", "foodhub_db");
   ```

### 4. Access in Browser
Navigate to:
```
http://localhost/WebTech_Summer25-26/FoodHub/
```
You will be redirected to the login page.

---

## 🔑 Default Credentials

| Role | Username | Password | Access / Portal |
|:---|:---|:---|:---|
| **Customer** | `customer1` | `customer123` | Customer Dashboard, Menu, Cart, Orders |
| **Customer** | `customer2` | `customer123` | Customer Dashboard, Menu, Cart, Orders |
| **Administrator** | `admin` | `admin123` | Admin Portal |
| **Restaurant Manager** | `manager1` | `manager123` | Restaurant Management Portal |
| **Rider** | `rider1` | `rider123` | Rider Delivery Portal |

