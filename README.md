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


- **Role Verification**: Non-admin users attempting to log in receive an access denial, and unauthenticated requests are redirected.
- **Whitelist Validation**: Status update endpoints validate values against defined status arrays.
>>>>>>> cc910dc (Clean Rider branch and remove admin leftovers)
