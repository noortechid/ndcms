# ☕ ND Coffee Management System (NDCMS)

### A Modern Coffee Shop Management Solution

**Simplify Operations. Manage Smarter. Serve Better.**

NDCMS is a web-based Coffee Shop Management System designed to streamline daily business operations at ND Coffee. It integrates sales transactions, inventory control, recipe management, purchasing, employee management, and business reporting into one centralized platform.

---

<p align="center">
  <img src="https://img.shields.io/badge/PHP-Native-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MariaDB-Database-003545?style=for-the-badge&logo=mariadb&logoColor=white" alt="MariaDB">
  <img src="https://img.shields.io/badge/Tailwind_CSS-Styling-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/JavaScript-Vanilla-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript">
</p>

<p align="center">
  <strong>Custom MVC Architecture · Service Layer · Role-Based Access Control</strong>
</p>

---

## 📖 Table of Contents

- [Overview](#-overview)
- [Key Features](#-key-features)
- [System Modules](#-system-modules)
- [User Roles](#-user-roles)
- [Technology Stack](#-technology-stack)
- [System Architecture](#-system-architecture)
- [Database Structure](#-database-structure)
- [Project Structure](#-project-structure)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Running the Application](#-running-the-application)
- [Business Workflow](#-business-workflow)
- [Security](#-security)
- [Development Status](#-development-status)
- [Future Improvements](#-future-improvements)
- [License](#-license)

---

## 🌟 Overview

Managing a coffee shop involves more than processing customer orders. It requires accurate inventory tracking, consistent product recipes, purchasing coordination, employee management, and reliable financial records.

NDCMS addresses these operational needs through an integrated management platform.

The system is designed around a single-outlet coffee shop business model, with a focus on operational efficiency, data consistency, and controlled access.

### Project Objectives

- Centralize coffee shop operational management.
- Simplify point-of-sale transactions.
- Maintain accurate ingredient stock records.
- Standardize product recipes and ingredient consumption.
- Manage supplier relationships and purchasing activities.
- Monitor cashier shifts and cash reconciliation.
- Provide meaningful sales and operational reports.

---

## ✨ Key Features

### 🖥️ Dashboard
- Daily revenue summary.
- Daily transaction statistics.
- Active product overview.
- Low-stock ingredient alerts.
- Weekly sales visualization.
- Recent transaction overview.

### 🛒 Point of Sale
- Product catalog and search.
- Shopping cart management.
- Quantity and discount calculation.
- Multiple payment methods.
- Automatic change calculation.
- Transaction validation.
- Integration with active cashier shifts.

### 📦 Product Management
- Product CRUD operations.
- Category management.
- Product availability control.
- Active/inactive product status.
- Recipe-based and non-recipe product configuration.

### 🧪 Recipe & HPP Management
- Product recipe management.
- Ingredient composition.
- Ingredient quantity configuration.
- Recipe and ingredient validation.

### 🧾 Inventory Management
- Stock In.
- Stock Out.
- Stock Adjustment.
- Stock movement history.
- Minimum stock monitoring.
- Automatic ingredient deduction through sales.

### 🛍️ Purchasing Management
- Supplier management.
- Purchase order creation.
- Purchase order status management.
- Partial receiving.
- Full receiving.
- Purchase receipt history.
- Automatic inventory updates upon receiving.

### 👥 Employee & User Management
- Employee records.
- User account management.
- Role assignment.
- Account status management.
- Employee and user relationship.

### ⏱️ Shift Management
- Open Shift.
- Opening cash recording.
- Cash and non-cash sales tracking.
- Cash In and Cash Out.
- Expected cash calculation.
- Close Shift.
- Cash difference reconciliation.
- Shift history.

### 📊 Reports
- Sales reporting.
- Date range filtering.
- Transaction summaries.
- PDF export.
- Print functionality.

### 🔐 Authentication & Authorization
- Secure login and logout.
- Session management.
- Role-Based Access Control.
- Authentication middleware.
- Permission middleware.
- CSRF protection.

---

## 🧩 System Modules

| Module | Description |
|---|---|
| Dashboard | Business performance overview |
| Authentication | Login, logout, and session handling |
| User Management | User accounts and role assignments |
| Employee Management | Employee records |
| Category Management | Product category organization |
| Product Management | Product catalog management |
| Ingredient Management | Raw material management |
| Recipe Management | Product ingredient composition |
| Inventory Management | Ingredient stock control |
| Supplier Management | Supplier records |
| Purchasing | Purchase orders and receiving |
| Shift Management | Cashier shift and cash control |
| Point of Sale | Sales transaction processing |
| Transactions | Transaction history and details |
| Reports | Sales reports and PDF export |

---

## 👤 User Roles

NDCMS implements a static Role-Based Access Control (RBAC) mechanism.

| Role | Responsibilities |
|---|---|
| **Owner** | Full system access and business oversight |
| **Admin** | Operational and administrative management |
| **Cashier** | POS transactions and cashier shift operations |
| **Inventory Staff** | Ingredient, inventory, recipe viewing, and purchasing operations |

Each role receives access only to the modules and actions permitted by its authorization rules.

---

## 🛠️ Technology Stack

### Backend
- **PHP Native** — Server-side application development.
- **MariaDB** — Relational database management.
- **PDO** — Database connectivity and prepared statements.
- **Object-Oriented Programming** — Application development paradigm.

### Frontend
- **HTML5** — Application structure.
- **Tailwind CSS** — Utility-first styling.
- **Vanilla JavaScript** — Client-side interactions.

### Architecture & Design
- Custom MVC Architecture.
- Service Layer Architecture.
- Front Controller Pattern.
- Middleware.
- Data Access Layer.
- Separation of Concerns.

### Development Environment
- PHP Development Server.
- Tailwind CSS Standalone CLI.
- MariaDB Client.

---

## 🏗️ System Architecture

NDCMS uses a custom MVC architecture enhanced with a Service Layer to separate application responsibilities.

```text
                 ┌──────────────────┐
                 │      Browser     │
                 └────────┬─────────┘
                          │
                 ┌────────▼─────────┐
                 │  Front Controller│
                 │ public/index.php │
                 └────────┬─────────┘
                          │
                 ┌────────▼─────────┐
                 │      Router      │
                 └────────┬─────────┘
                          │
                 ┌────────▼─────────┐
                 │    Middleware    │
                 │ Auth & Permission│
                 └────────┬─────────┘
                          │
                 ┌────────▼─────────┐
                 │    Controller    │
                 └────────┬─────────┘
                          │
                 ┌────────▼─────────┐
                 │   Service Layer  │
                 └────────┬─────────┘
                          │
                 ┌────────▼─────────┐
                 │      Model       │
                 └────────┬─────────┘
                          │
                 ┌────────▼─────────┐
                 │     MariaDB      │
                 └──────────────────┘
```

### Architecture Responsibilities

| Layer | Responsibility |
|---|---|
| Front Controller | Entry point for incoming requests |
| Router | Maps URLs to application controllers |
| Middleware | Handles authentication and authorization |
| Controller | Handles HTTP requests and responses |
| Service | Implements business logic and workflows |
| Model | Handles database operations |
| View | Renders application interfaces |

---

## 🗄️ Database Structure

The database consists of 16 primary tables organized around the application's operational requirements.

| No. | Table | Purpose |
|---:|---|---|
| 1 | `users` | User accounts and access roles |
| 2 | `employees` | Employee information |
| 3 | `cashier_shifts` | Cashier shift records |
| 4 | `categories` | Product categories |
| 5 | `products` | Product information |
| 6 | `ingredients` | Raw material information |
| 7 | `recipes` | Product recipes |
| 8 | `recipe_items` | Recipe ingredient details |
| 9 | `stock_movements` | Inventory movement history |
| 10 | `suppliers` | Supplier information |
| 11 | `purchases` | Purchase orders |
| 12 | `purchase_items` | Purchase order details |
| 13 | `purchase_receipts` | Goods receiving records |
| 14 | `purchase_receipt_items` | Received item details |
| 15 | `transactions` | Sales transaction records |
| 16 | `transaction_items` | Transaction product details |

---

## 📁 Project Structure

The application follows a modular directory structure.

```text
ndcms/
│
├── app/
│   ├── Controllers/
│   ├── Core/
│   ├── Middleware/
│   ├── Models/
│   └── Services/
│
├── config/
│
├── database/
│   ├── migrations/
│   └── seeds/
│
├── public/
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   │
│   └── index.php
│
├── resources/
│   ├── css/
│   └── views/
│       ├── layouts/
│       ├── dashboard/
│       ├── products/
│       ├── inventory/
│       ├── purchases/
│       ├── transactions/
│       └── reports/
│
├── routes/
│
├── tailwindcss
└── README.md
```

*Note: This is a conceptual overview. The actual repository structure may contain additional files and directories.*

---

## 🚀 Installation

Follow these steps to set up NDCMS locally.

### 1. Clone the Repository

```bash
git clone <repository-url>
cd ndcms
```

### 2. Create the Database

Open MariaDB:

```bash
mariadb -u root -p
```

Create the database:

```sql
CREATE DATABASE ndcms
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

### 3. Import Database Structure

Import the database schema using the SQL files provided in the repository.

```bash
mariadb -u root -p ndcms < database/schema.sql
```

*Adjust the SQL filename according to the actual repository.*

### 4. Configure Database Connection

Update the database connection configuration with your local credentials.

Example:

```php
return [
    'host' => '127.0.0.1',
    'database' => 'ndcms',
    'username' => 'root',
    'password' => '',
];
```

*Use the actual configuration format implemented in the project.*

### 5. Build Tailwind CSS

NDCMS uses the Tailwind CSS Standalone CLI.

Example:

```bash
./tailwindcss \
  -i resources/css/app.css \
  -o public/assets/css/app.css \
  --minify
```

Adjust the input and output paths if your repository uses a different configuration.

### 6. Run the Application

```bash
php -S 127.0.0.1:8080 -t public
```

Open your browser:

```text
http://127.0.0.1:8080
```

---

## ⚙️ Configuration

Before running the application, ensure that the following requirements are available:

- PHP 8.x or a version compatible with the application code.
- MariaDB.
- PDO MySQL extension.
- Fileinfo extension for image upload functionality.
- Writable storage directories where required.
- Tailwind CSS Standalone CLI.

Database credentials should be configured according to the application's existing database configuration.

---

## 🔄 Business Workflow

The primary operational workflow of NDCMS is organized as follows:

```text
       MASTER DATA SETUP
               │
               ▼
      SUPPLIER & PURCHASING
               │
               ▼
        GOODS RECEIVING
               │
               ▼
       INVENTORY UPDATED
               │
               ▼
        CASHIER OPENS SHIFT
               │
               ▼
        POS TRANSACTIONS
               │
               ▼
       RECIPE CONSUMPTION
               │
               ▼
       INVENTORY DEDUCTION
               │
               ▼
      TRANSACTION RECORDS
               │
               ▼
        REPORT GENERATION
```

### Important Business Rules

- Purchase orders do not directly increase inventory.
- Inventory increases when purchased goods are received.
- Products configured as recipe-based require valid recipes.
- Ingredient consumption is recorded during successful sales.
- Sales transactions are associated with an active cashier shift.
- Shift closing reconciles expected cash with actual cash.

---

## 🔐 Security

Security is an important part of the NDCMS implementation.

The system includes:

- Password-based authentication.
- Session-based user management.
- CSRF token validation.
- Role-Based Access Control.
- Authentication and permission middleware.
- PDO prepared statements for database operations.
- Server-side validation of application input.

---

## 📌 Development Status

NDCMS has implemented its core operational modules.

| Component | Status |
|---|---|
| System Architecture | Completed |
| Database Design | Completed |
| Authentication & Authorization | Implemented |
| Dashboard | Implemented |
| Employee Management | Implemented |
| User Management | Implemented |
| Product Management | Implemented |
| Ingredient Management | Implemented |
| Recipe Management | Implemented |
| Inventory Management | Implemented |
| Supplier Management | Implemented |
| Purchasing Management | Implemented |
| Shift Management | Implemented |
| Point of Sale | Implemented |
| Transaction Management | Implemented |
| Reports | Completed |
| Product Image Upload | Under verification |
| Production Deployment | Pending final preparation |
| User Documentation | Pending final preparation |

---

## 🧭 Future Improvements

Potential enhancements for future development include:

- Product image upload and catalog enhancement.
- Improved reporting and business analytics.
- Automated database backup.
- Production deployment and monitoring.
- Additional operational insights.
- Extended documentation and user onboarding.

---

## 📄 License

This project is developed for **ND Coffee**.

Copyright © 2026 ND Coffee Management System.

Usage, distribution, and modification rights are subject to the agreement between the developer and the project owner.

---

<p align="center">
  <strong>☕ ND Coffee Management System</strong>
  <br>
  Simplify Operations. Manage Smarter. Serve Better.
  <br><br>
  <sub>Built with dedication for better coffee shop management.</sub>
</p>
