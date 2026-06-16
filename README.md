<div align="center">

<img src="assets/banner.png" alt="NexaBank Banner" width="100%" style="border-radius: 12px; border: 1px solid rgba(212,170,82,0.25); box-shadow: 0 8px 32px rgba(0,0,0,0.5);" />

# 🏦 NexaBank

### **Next-Generation Dark Luxury Banking Management System**

*A secure, premium, and sophisticated online banking experience styled with an editorial financial design system, powered by an optimized PHP/MySQL relational database backend.*

---

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.0-777bb4?style=for-the-badge&logo=php&logoColor=white)](#)
[![MySQL Database](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](#)
[![CSS3 Theme](https://img.shields.io/badge/CSS3-Dark%20Luxury%20Theme-1572B6?style=for-the-badge&logo=css3&logoColor=white)](#)
[![Security](https://img.shields.io/badge/Security-Prepared%20Queries-26c97e?style=for-the-badge&logo=snyk&logoColor=white)](#)
[![License](https://img.shields.io/badge/License-MIT-d4aa52?style=for-the-badge)](#)

</div>

---

## 🎨 Visual Identity & Design System

NexaBank breaks away from typical bright corporate banking interfaces, introducing a **Dark Luxury Financial** theme designed to feel premium, exclusive, and editorial.

### 🌗 Theme Color Palette
*   🔵 **Deep Navy Canvas (`#060c1a`):** Gradients and shadows that provide a high-contrast, distraction-free environment.
*   🟡 **Gold Accents (`#d4aa52`):** Elegant tone representing luxury, wealth, and sophisticated action elements.
*   ⚪ **Slate Typography (`#e8edf8`):** Restful body copy and `#8892aa` secondary titles maintaining optimal readability.
*   🟢 **Success Green (`#26c97e`):** Highlights incoming funds and positive actions.
*   🔴 **Error Red (`#f05a6e`):** Highlights outgoing transactions and system alerts.

### ✍️ Editorial Typography
*   **Headers:** Font Family *Playfair Display*, Georgia, serif — for editorial finance authority.
*   **Body Copy:** Font Family *DM Sans*, system-ui, sans-serif — for fluid, modern text rendering.
*   **Ledgers & Identifiers:** Font Family *JetBrains Mono*, monospace — for numerical accuracy and security identifiers.

---

## ⚡ System Capabilities & Features

### 👤 Customer Experience
*   **Dynamic Dashboard:** Instant visual summary of account balance, real-time monthly stats (incoming/outgoing logs), and detailed account info.
*   **Secure Fund Transfers:** Real-time money transfers to other accounts validated by account number and branch IFSC code.
*   **Relative Ledger Timestamp:** Transactions show relative timestamps (e.g. *"5m ago"*, *"Just now"*) for dynamic audit logging.
*   **Autonomous Registration:** Create accounts, set initial deposits, and automatically generate a unique `14-digit` account number.

### 👑 System Control Panel (Admin)
*   **Surveillance Dashboard:** Overview of total users, overall bank volume, daily logs, and average transfer amounts.
*   **User Ledger Admin:** Full access to all accounts with instant activate/deactivate toggles to freeze suspicious accounts.
*   **Central Transaction Ledger:** Audit-grade tracker of all transactions occurring within the system, searchable by username, email, or transfer remarks.

---

## 🏗️ System Architecture

The following diagram illustrates how requests flow through the NexaBank secure system:

```mermaid
flowchart TD
    %% Base Styling
    classDef client fill:#0b1428,stroke:#d4aa52,stroke-width:2px,color:#fff;
    classDef process fill:#172444,stroke:#8892aa,stroke-dasharray: 5 5,color:#fff;
    classDef database fill:#060c1a,stroke:#26c97e,stroke-width:2px,color:#fff;
    
    subgraph Client View
        A[index.php Landing Page]:::client
        B[createuser.php Registration]:::client
        C[login.php User Login]:::client
        D[admin_login.php Admin Login]:::client
    end

    subgraph Secure Session Control
        E{Session Verified?}:::process
        F[dashboard.php User Dashboard]:::client
        G[admin_dashboard.php Admin Panel]:::client
    end

    subgraph Operations & Database
        H[(user_accounts Table)]:::database
        I[(transactions Table)]:::database
        J[transfer.php Fund Transfer Process]:::process
        K[view_users.php Admin User List]:::process
    end

    A --> B
    A --> C
    A --> D
    
    C -->|Authenticate User| E
    D -->|Authenticate Admin| E
    
    E -->|Yes - User| F
    E -->|Yes - Admin| G
    E -->|No| A
    
    F -->|Initiate| J
    J -->|SQL Transaction Begin| H
    J -->|Debit Sender / Credit Receiver| H
    J -->|Commit Ledger Log| I
    
    G -->|Monitor| K
    K -->|Activate/Deactivate| H
    G -->|Audit Logs| I
```

---

## 🔒 Security Infrastructure

NexaBank is built with robust protocols to safeguard customer information and financial assets:

1.  **Prepared Statements (SQLi Prevention):** 100% of database actions use PHP Object-Oriented `mysqli::prepare()` statements and parameter binding (`bind_param`). No raw input concatenation.
2.  **BCrypt Password Hashing:** Secure registration and login verify passwords using state-of-the-art **BCrypt hashing** (`password_hash` with `PASSWORD_DEFAULT`).
3.  **XSS Protection:** Output sanitization helper function `e()` escapes user-provided values globally using `htmlspecialchars` and UTF-8 encoding.
4.  **Database Transactions (ACID Compliance):** Fund transfers utilize SQL transactions (`mysqli_begin_transaction`, `mysqli_commit`, `mysqli_rollback`). If either the debit or credit step fails, the entire transaction rolls back to prevent money leakage.

---

## 📊 Relational Database Schema

The database consists of three relational tables optimized with appropriate primary/foreign keys and delete cascades.

<details>
<summary><b>📋 Click to Expand Database Schema Details</b></summary>

### 1. `admin_accounts`
Stores administrative staff profiles authorized to run the backend dashboard.
| Column | Data Type | Key Type | Purpose |
| :--- | :--- | :--- | :--- |
| `id` | INT | PRIMARY KEY | Auto-incrementing identifier |
| `username` | VARCHAR(100) | - | Administrator name |
| `email` | VARCHAR(150) | UNIQUE | Authentication email |
| `password` | VARCHAR(255) | - | BCrypt hashed password |
| `created_at` | TIMESTAMP | - | Date/time of record creation |

### 2. `user_accounts`
Maintains user records, financial balances, account details, and status flags.
| Column | Data Type | Key Type | Purpose |
| :--- | :--- | :--- | :--- |
| `id` | INT | PRIMARY KEY | Auto-incrementing identifier |
| `name` | VARCHAR(150) | - | Account owner full name |
| `email` | VARCHAR(150) | UNIQUE | Authentication email address |
| `phone` | VARCHAR(20) | - | Contact phone number |
| `address` | TEXT | - | Physical address |
| `gender` | ENUM | - | Male, Female, or Other |
| `dob` | DATE | - | Owner date of birth |
| `password` | VARCHAR(255) | - | BCrypt hashed password |
| `account_number` | VARCHAR(20) | UNIQUE | Generated 14-digit number |
| `ifsc` | VARCHAR(20) | - | Branch routing identifier (Default: `NEXA0001`) |
| `balance` | DECIMAL(15,2) | - | Active ledger balance (Default: `0.00`) |
| `is_active` | TINYINT(1) | - | Status flag (`1` = Active, `0` = Frozen) |
| `created_at` | TIMESTAMP | - | Registration date |

### 3. `transactions`
Double-entry ledger documenting transfers between user accounts.
| Column | Data Type | Key Type | Purpose |
| :--- | :--- | :--- | :--- |
| `id` | INT | PRIMARY KEY | Auto-incrementing identifier |
| `sender_id` | INT | FOREIGN KEY | References `user_accounts(id)` on delete cascade |
| `receiver_id` | INT | FOREIGN KEY | References `user_accounts(id)` on delete cascade |
| `amount` | DECIMAL(15,2) | - | Money transfer volume |
| `note` | VARCHAR(255) | - | Transaction remarks |
| `status` | ENUM | - | Success, Failed, or Pending |
| `date_time` | TIMESTAMP | - | Transfer timestamp |

</details>

---

## 🛠️ Installation & Setup

Follow these simple steps to host NexaBank on your local development environment:

### Prerequisites
*   **PHP** version 8.0 or higher.
*   **MySQL** Database / MariaDB.
*   **Apache Server** (e.g. XAMPP, WAMP, or Laragon).

### Setup Steps
1.  **Clone / Copy Project Files:**
    Place the project folder (`nexabank`) inside your local server directory:
    *   **XAMPP:** `C:\xampp\htdocs\nexabank`
    *   **WAMP:** `C:\wamp64\www\nexabank`
    
2.  **Initialize Database:**
    *   Open your web browser and navigate to `http://localhost/phpmyadmin/`.
    *   Create a new database named `nexabank`.
    *   Click on **Import** in the database tab, select the `nexabank.sql` file from the project root, and click **Go**.
    
3.  **Configure Connection Settings:**
    Open `config.php` and verify the database connection details:
    ```php
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');      // Your MySQL username
    define('DB_PASS', '');          // Your MySQL password
    define('DB_NAME', 'nexabank');  // Your database name
    ```
    
4.  **Run Application:**
    Navigate to `http://localhost/nexabank` in your browser.

> [!IMPORTANT]
> Make sure to import `nexabank.sql` before running the project to initialize the schema and populate the default seed data.

> [!TIP]
> **To display the header banner image:**
> Once you have configured the project, run the setup script by opening `http://localhost/nexabank/copy_asset.php` in your browser. This will automatically copy the generated banner graphic to your assets folder!

---

## 🔑 Test Credentials

Use the following credentials to explore user and admin dashboards immediately:

### 👤 User Accounts
All users share the default password: `password123`
| Name | Email | Account Number | Initial Balance |
| :--- | :--- | :--- | :--- |
| **Arjun Sharma** | `arjun@example.com` | `NEXA0000000001` | ₹125,000.00 |
| **Priya Patel** | `priya@example.com` | `NEXA0000000002` | ₹87,500.00 |
| **Rahul Verma** | `rahul@example.com` | `NEXA0000000003` | ₹210,000.00 |
| **Sneha Reddy** | `sneha@example.com` | `NEXA0000000004` | ₹54,000.00 |

### 👑 Administrator Account
*   **Super Admin:** `admin@nexabank.com`
*   **Password:** `admin123`

---

## 📂 Directory Layout

<details>
<summary><b>📂 Click to Expand Directory structure</b></summary>

```bash
nexabank/
├── assets/                  # Project assets (branding & graphics)
│   └── banner.png           # Header visual graphic
├── css/                     # Styling styles sheets
│   └── style.css            # Dark luxury visual system
├── config.php               # Database configuration and global helpers
├── index.php                # Public landing page
├── login.php                # User portal login
├── logout.php               # User session destroy
├── createuser.php           # Shared registration (User signup / Admin direct create)
├── dashboard.php            # Active user interface
├── transfer.php             # Secure transaction terminal
├── transaction_history.php  # Personal account ledger
├── profile.php              # Personal settings editor
├── services.php             # Informational overview
├── admin_login.php          # Admin portal verification
├── admin_dashboard.php      # Main administration hub
├── admin_logout.php         # Admin session destroy
├── view_users.php           # User records management
├── view_transactions.php    # Complete bank transaction ledger
├── navbar.php               # Dynamic header layout component
├── footer.php               # Core brand footer component
└── nexabank.sql             # Relational database setup logic
```

</details>

---

<div align="center">
  <p>Designed with ❤️ for premium banking experience.</p>
</div>
