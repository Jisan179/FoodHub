# FoodHub Rider Portal

This branch contains the Rider-focused implementation of the FoodHub delivery platform. It is built with procedural PHP, MySQLi, and a lightweight server-rendered UI for rider operations such as accepting deliveries, updating status, and viewing delivery history.

---

## Overview

FoodHub is a delivery management application for a food ordering platform. In this Rider branch, the focus is on the rider experience:

- rider login and session protection
- available delivery offers and active assignment tracking
- pickup and delivery status transitions
- delivery history and rider earning summary
- JSON-based AJAX interaction for delivery actions

The implementation keeps the project simple and maintainable by separating concerns into:

- controllers for business logic
- models for database access
- views for HTML presentation
- static assets for CSS/JS

---

## Tech Stack

| Layer | Stack |
|---|---|
| Backend | PHP 7+ / 8+ (procedural style) |
| Database | MySQL / MariaDB |
| Web Server | Apache via XAMPP |
| Frontend | HTML, CSS, JavaScript |
| Data Access | mysqli procedural functions |
| Session Handling | PHP native sessions |

This project intentionally avoids OOP, PDO, and frameworks. It uses plain procedural PHP for clarity and compatibility with the course/project structure.

---

## Branch Scope

This repository has been split into separate branch workstreams. The Rider branch is the working branch for operational rider functionality.

Current branch in use:

- `Rider` — rider portal implementation

The main branch has been cleared and kept empty as a clean baseline.

---

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
├── controllers/
│   ├── auth/
│   │   ├── auth_check.php
│   │   ├── login_controller.php
│   │   ├── logout_controller.php
│   │   └── rider_check.php
│   └── rider/
│       └── dashboard_controller.php
├── models/
│   └── rider_model.php
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

---

## Functional Scope

### Authentication

The application uses a shared login flow via:

- `login.php`
- `controllers/auth/login_controller.php`
- `controllers/auth/rider_check.php`

Behavior:

- users submit username/password
- the system checks the user record in the database
- valid Rider users are redirected to the rider dashboard
- unauthenticated or wrong-role users are redirected back to login

### Rider Dashboard

The rider dashboard is the main UI and is served from:

- `rider/dashboard.php`
- `views/rider/dashboard.php`

The dashboard includes:

- active delivery list
- available assignment offers
- assignment acceptance
- pickup confirmation
- delivery completion
- cancellation support
- rider earning summary
- delivery history viewer

### Delivery Actions

Riders can perform these transitions through the dashboard and controller logic:

- Accept assignment
- Mark as picked up
- Mark as delivered
- Cancel delivery
- Update rider note

The status transitions are validated in `models/rider_model.php` and recorded in `delivery_status_history`.

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
