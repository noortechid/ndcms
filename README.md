# ☕ ND Coffee Management System (NDCMS)

<p align="center">
  <strong>Simplify Operations. Manage Smarter. Serve Better.</strong>
</p>

<p align="center">
  A web-based Coffee Shop Management System designed to streamline daily operations, inventory management, purchasing, and sales activities at ND Coffee.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-Native-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Native">
  <img src="https://img.shields.io/badge/MariaDB-Database-003545?style=for-the-badge&logo=mariadb&logoColor=white" alt="MariaDB">
  <img src="https://img.shields.io/badge/Tailwind_CSS-Styling-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/JavaScript-Vanilla-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript">
</p>

---

## 📌 Overview

**ND Coffee Management System (NDCMS)** is a web-based information system developed to support and improve the daily operational activities of ND Coffee.

The system centralizes essential coffee shop operations into one integrated platform, helping staff manage products, ingredients, inventory, purchasing, sales transactions, cashier shifts, and business reports.

NDCMS is designed with a clean, modern, and user-friendly interface, combining operational efficiency with a warm and elegant visual identity.

## 🖼️ Application Preview

<p align="center">
  <img src="screenshots/dashboard.png" width="100%" alt="NDCMS Dashboard">
  <br>
  <strong>Dashboard</strong>
  <br>
  <sub>Business performance overview and operational insights.</sub>
</p>

<p align="center">
  <img src="screenshots/pos.png" width="100%" alt="NDCMS Point of Sale">
  <br>
  <strong>Point of Sale</strong>
  <br>
  <sub>An intuitive interface for managing coffee shop transactions.</sub>
</p>

<p align="center">
  <img src="screenshots/products.png" width="100%" alt="NDCMS Product Management">
  <br>
  <strong>Product Management</strong>
  <br>
  <sub>Centralized product and category management.</sub>
</p>

<p align="center">
  <img src="screenshots/inventory.png" width="100%" alt="NDCMS Inventory Management">
  <br>
  <strong>Inventory Management</strong>
  <br>
  <sub>Ingredient stock monitoring and stock movement tracking.</sub>
</p>

<p align="center">
  <img src="screenshots/purchasing.png" width="100%" alt="NDCMS Purchasing">
  <br>
  <strong>Purchasing Management</strong>
  <br>
  <sub>Supplier management and purchase order processing.</sub>
</p>

<p align="center">
  <img src="screenshots/reports.png" width="100%" alt="NDCMS Reports">
  <br>
  <strong>Reports & Analytics</strong>
  <br>
  <sub>Sales summaries and business performance reporting.</sub>
</p>

<p align="center">
  <img src="screenshots/employees.png" width="100%" alt="NDCMS Employee Management">
  <br>
  <strong>Employee Management</strong>
  <br>
  <sub>Employee records and system user administration.</sub>
</p>

<p align="center">
  <img src="screenshots/login.png" width="100%" alt="NDCMS Login">
  <br>
  <strong>Login Page</strong>
  <br>
  <sub>Secure authentication with a clean and elegant interface.</sub>
</p>

---

## 🤝 Client Collaboration

NDCMS was developed through a collaborative process involving project planning, requirement discussions, and coordination with the client.

<p align="center">
  <img src="screenshots/client-meeting.jpg" width="100%" alt="NDCMS Client Meeting Documentation">
  <br>
  <strong>Client Meeting & Project Discussion</strong>
  <br>
  <sub>Documentation of project coordination, requirement discussions, and development progress with ND Coffee.</sub>
</p>

This collaboration helped align the system's development with the client's operational needs and business requirements.

---

## ✨ Key Features

- **Centralized Management** — Manage essential coffee shop operations through one integrated system.
- **Role-Based Access Control** — Control system access based on employee responsibilities.
- **Sales Management** — Process transactions through an integrated Point of Sale interface.
- **Inventory Monitoring** — Track ingredient quantities and stock movements.
- **Recipe Management** — Define ingredient requirements for products.
- **Purchasing Management** — Manage suppliers, purchase orders, and receiving activities.
- **Cashier Shift Management** — Monitor cashier sessions and cash reconciliation.
- **Business Reporting** — Review sales performance and operational information.
- **Responsive Interface** — A clean and user-friendly interface for daily operations.

## 🧩 System Modules

| Module | Description |
|---|---|
| Dashboard | Operational overview and business summaries |
| Point of Sale | Product selection and transaction processing |
| Transactions | Transaction history and detail records |
| Products | Product information and availability |
| Categories | Product category management |
| Ingredients | Ingredient master data |
| Recipes | Product ingredient composition |
| Inventory | Stock monitoring and movement management |
| Purchasing | Supplier and purchase order management |
| Receiving | Partial and full purchase receiving |
| Shifts | Cashier opening, closing, and reconciliation |
| Employees | Employee information management |
| User Management | System accounts and access management |
| Reports | Sales and operational reporting |

## 👥 User Roles & Permissions

NDCMS implements role-based access control (RBAC) to separate system responsibilities.

| Role | Responsibilities |
|---|---|
| Owner | Full system access and management |
| Admin | Operational and administrative management |
| Cashier | POS transactions and cashier shifts |
| Inventory Staff | Inventory, ingredients, recipes, and purchasing |

Each role receives access according to its assigned permissions.

## 🛠️ Technology Stack

| Technology | Purpose |
|---|---|
| PHP Native | Backend application development |
| MariaDB | Relational database management |
| PDO | Database connectivity |
| HTML5 | Application structure |
| Tailwind CSS | Interface styling |
| Vanilla JavaScript | Client-side interactions |
| Apache / PHP Development Server | Application hosting environment |

NDCMS uses a native PHP implementation without a full-stack framework.

## 🏗️ System Architecture

The application follows a custom MVC architecture with a service layer to separate responsibilities and improve maintainability.

```text
                 CLIENT / BROWSER
                        |
                        v
                 PUBLIC ENTRY POINT
                   public/index.php
                        |
                        v
                     ROUTER
                        |
                        v
                    MIDDLEWARE
                 Authentication
                 Authorization
                        |
                        v
                   CONTROLLER
                        |
                        v
                   SERVICE LAYER
              Business Logic Processing
                        |
                        v
                  MODEL / DATA ACCESS
                        |
                        v
                    MARIADB
```

### Architecture Components

- **Core:** Routing, database connection, controllers, and middleware infrastructure.
- **Controllers:** Handle incoming requests and coordinate application operations.
- **Models:** Manage database queries and data access.
- **Services:** Handle business logic and operational workflows.
- **Views:** Render the user interface.
- **Middleware:** Handle authentication and authorization.

## 📂 Project Structure

```text
NDCMS/
│
├── app/
│   ├── Controllers/
│   ├── Core/
│   │   └── Middleware/
│   ├── Models/
│   └── Services/
│
├── config/
│
├── public/
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   └── index.php
│
├── resources/
│   ├── css/
│   └── views/
│       ├── layouts/
│       ├── auth/
│       ├── products/
│       ├── inventory/
│       ├── purchases/
│       ├── transactions/
│       └── ...
│
├── routes/
│   └── web.php
│
├── scripts/
│   └── create_owner.php
│
├── screenshots/
│
├── bootstrap.php
├── ndcms_schema.sql
├── tailwindcss
└── README.md
```

*The structure above highlights the main application directories.*

## 🗄️ Database

NDCMS uses MariaDB as its relational database management system.

The database is designed to support interconnected operational modules, including:

- Employee and user management
- Product and category management
- Ingredient and recipe management
- Inventory and stock movements
- Suppliers and purchasing
- Purchase receiving
- Cashier shifts
- Sales transactions and transaction details

Database schema:

```text
ndcms_schema.sql
```

## ⚙️ Installation & Setup

### 1. Clone the Repository

```bash
git clone <YOUR_REPOSITORY_URL>
cd NDCMS
```

### 2. Prepare the Database

Create a MariaDB database:

```sql
CREATE DATABASE ndcms;
```

Import the database schema:

```bash
mariadb -u root -p ndcms < ndcms_schema.sql
```

### 3. Configure the Application

Update the database connection settings in the application's configuration files.

Make sure the database host, database name, username, and password match your local environment.

### 4. Create the Initial Owner Account

Run the owner account setup script:

```bash
php scripts/create_owner.php
```

Follow the script's instructions to create the initial owner account.

### 5. Compile Tailwind CSS

NDCMS uses the standalone Tailwind CSS CLI.

Run:

```bash
./tailwindcss -i ./resources/css/app.css -o ./public/assets/css/app.css --watch
```

For a production build, compile without watch mode:

```bash
./tailwindcss -i ./resources/css/app.css -o ./public/assets/css/app.css --minify
```

### 6. Run the Application

Start the PHP development server:

```bash
php -S 127.0.0.1:8080 -t public
```

Open the application in your browser:

```text
http://127.0.0.1:8080
```

## 🔄 Business Process Workflow

The system supports the following operational workflow:

```text
MASTER DATA
    |
    v
PRODUCT & RECIPE MANAGEMENT
    |
    v
PURCHASING
    |
    v
GOODS RECEIVING
    |
    v
INVENTORY UPDATE
    |
    v
CASHIER OPENS SHIFT
    |
    v
SALES TRANSACTION
    |
    v
RECIPE-BASED INGREDIENT CONSUMPTION
    |
    v
TRANSACTION & STOCK MOVEMENT RECORDS
    |
    v
SHIFT RECONCILIATION
    |
    v
BUSINESS REPORTING
```

## 🔐 Security & Data Integrity

NDCMS incorporates several mechanisms to support secure and consistent application operations:

- Authentication and session-based access.
- Role-based authorization.
- CSRF protection for applicable forms.
- PDO prepared statements for database operations.
- Server-side input validation.
- Transactional database operations for critical workflows.
- Stock validation before transaction completion.
- Access restrictions based on user permissions.

## 🎨 UI/UX Design

The NDCMS interface follows a **Soft Feminine** visual direction with an elegant and professional appearance.

Design principles include:

- Clean and minimal layouts.
- Warm coffee-inspired color palette.
- Consistent spacing and typography.
- Clear navigation hierarchy.
- User-friendly operational interfaces.
- Visual consistency across application modules.

## 📌 Project Status

NDCMS has progressed through the implementation of its core application modules, including authentication, dashboard, master data, inventory, purchasing, shifts, POS, transactions, and reports.

Final deployment, documentation, client acceptance testing, and any remaining refinements should be tracked separately from the implemented application scope.

## 🚀 Future Improvements

Potential future enhancements may include:

- More advanced business analytics.
- Automated database backup workflows.
- Additional operational monitoring.
- Enhanced application deployment and maintenance procedures.

## 📄 License

This project was developed for ND Coffee.

Usage, distribution, modification, and ownership are subject to the agreement between the developer and the client.

---

<p align="center">
  <strong>ND Coffee Management System</strong>
  <br>
  Simplify Operations. Manage Smarter. Serve Better.
  <br><br>
  <sub>Developed with care for better coffee shop operations.</sub>
</p>
