# FoodHub Rider Portal

This branch contains the rider-only implementation of the FoodHub delivery platform. It is built with procedural PHP, MySQLi, and a lightweight server-rendered UI for rider operations such as accepting deliveries, updating status, and viewing delivery history.

## Overview

FoodHub is a delivery management application for a food ordering platform. In this Rider branch, the focus is on the rider experience:

- rider login and session protection
- available delivery offers and assignment tracking
- pickup and delivery status transitions
- rider earnings and delivery history
- simple server-side validation and JSON-style AJAX updates

## Tech Stack

- PHP 7+ / 8+ (procedural style)
- MySQL / MariaDB
- Apache via XAMPP
- HTML, CSS, JavaScript
- mysqli procedural database access
- PHP native sessions

## Project Structure

```text
FoodHub/
├── index.php
├── login.php
├── logout.php
├── README.md
├── schema.sql
├── style.css
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── rider.js
├── config/
│   └── db.php
├── controllers/
│   ├── auth/
│   │   ├── auth_check.php
│   │   ├── login_controller.php
│   │   ├── logout_controller.php
│   │   └── rider_check.php
│   └── rider/
│       └── dashboard_controller.php
├── models/
│   ├── rider_model.php
│   └── user_model.php
├── rider/
│   └── dashboard.php
├── views/
│   ├── auth/
│   │   └── login.php
│   ├── partials/
│   │   ├── footer.php
│   │   └── header.php
│   └── rider/
│       ├── dashboard.php
│       ├── delivery_card.php
│       └── delivery_row.php
└── .git/
```

## Rider Features

### Authentication

- rider login page at `login.php`
- rider-only session guard in `controllers/auth/rider_check.php`
- redirect to rider dashboard after valid login
- logout support via `logout.php`

### Dashboard

The rider dashboard includes:

- active deliveries and available offers
- accepted assignments
- pickup confirmation
- delivery completion
- cancellation support
- note updates
- rider earnings summary
- delivery history

### Delivery lifecycle

Delivery statuses used by the app:

- Pending Assignment
- Assigned
- Picked Up
- Delivered
- Cancelled

All rider state changes are validated server-side in `models/rider_model.php` before updating the database.

## Database Setup

Import `schema.sql` into MySQL using phpMyAdmin or the MySQL CLI. The script creates `foodhub_db` and seeds demo data including rider accounts and sample deliveries.

## Local Run

1. Place the project in XAMPP web root:
   `C:\xampp\htdocs\FoodHub`
2. Start Apache and MySQL in XAMPP.
3. Import `schema.sql` into `foodhub_db`.
4. Open:
   `http://localhost/FoodHub/`
5. Sign in as the demo rider:
   - Username: `rider1`
   - Password: `rider123`

## Notes

- This Rider branch intentionally focuses only on the rider workflow.
- The main branch is cleared and kept empty as a baseline.
- SQL schema and CSS were left untouched as requested.
- This project is procedural PHP and is designed for local demo and learning workflows rather than production deployment.

---

## Database Design

The schema is initialized via `schema.sql` and creates the core relational tables for the app.

### Main tables

- `users`
- `restaurants`
- `food_items`
- `orders`
- `order_items`
- `deliveries`
- `delivery_status_history`

### Delivery-related status values

The delivery lifecycle uses the following statuses:

- `Pending Assignment`
- `Assigned`
- `Picked Up`
- `Delivered`
- `Cancelled`

The database also includes seed data for:

- admin account
- rider accounts
- sample restaurants
- order records
- delivery examples for demo and testing

---

## Local Setup

### 1. Prerequisites

Install and run:

- XAMPP
- Apache
- MySQL
- PHP

### 2. Project location

Place the project in the XAMPP web root:

```text
C:\xampp\htdocs\FoodHub
```

### 3. Import database

Open phpMyAdmin and import:

```text
schema.sql
```

This creates the `foodhub_db` database and inserts sample data.

### 4. Start app

Open in browser:

```text
http://localhost/FoodHub/
```

The root entry page redirects to login.

---

## Default Credentials

The included seed data contains demo users.

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `admin123` |
| Rider | `rider1` | `rider123` |
| Customer | `customer1` | `customer123` |
| Customer | `customer2` | `customer123` |
| Restaurant Manager | `manager1` | `manager123` |
| Restaurant Manager | `manager2` | `manager123` |

For the Rider portal, the relevant login is:

- username: `rider1`
- password: `rider123`

---

## Security and Operational Notes

The project follows core web-application security practices for a small procedural PHP project:

- PHP session-based authorization
- redirect to login for non-authenticated access
- role-based validation for rider routes
- use of `mysqli_real_escape_string` for user input sanitization
- `htmlspecialchars` for output escaping
- transaction usage for critical delivery updates

Important operational note:

- the project currently expects a shared DB connection pattern and may require a local `config/db.php` setup if the environment is changed beyond the current branch structure
- all critical rider updates are handled server-side rather than being trusted from client-side JavaScript

---

## DevOps/Deployment Notes

For a small team or local development workflow, the following practices are recommended:

1. Keep `Rider` as the feature branch for rider-specific work.
2. Use a clean, repeatable database import via `schema.sql` for local setup.
3. Validate the app after schema changes before merging into a wider branch.
4. Test rider flow end-to-end:
   - login
   - accept assignment
   - pickup
   - complete delivery
   - view history
5. Keep feature work isolated and documented per module.

A good team workflow is:

```text
Rider branch -> local QA -> merge to main only after validation
```

---

## Known Implementation Notes

This project is intentionally lightweight and procedural. It is suitable for educational use, local delivery demo work, and internal prototype validation. It is not yet a production-hardened system with full CI/CD automation, deployment pipelines, or container orchestration.

For production deployment, the project would typically need:

- environment-based configuration
- secure secret management
- database migration scripts
- production-grade logging
- automated tests
- CI/CD pipeline and deployment checks

---

## Summary

The Rider branch delivers a working browser-based rider portal built in pure procedural PHP. It is built around the FoodHub delivery lifecycle and is intended to support rider operations in a simple, maintainable, and testable way.

The repository structure and seed data are designed to make local onboarding easy with XAMPP and MySQL.
=======
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
>>>>>>> cc910dc (Clean Rider branch and remove admin leftovers)
