-- ============================================================
--  NexaBank - Banking Management System
--  Database Schema & Seed Data
-- ============================================================

CREATE DATABASE IF NOT EXISTS nexabank CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nexabank;

-- ============================================================
--  TABLE: admin_accounts
-- ============================================================
CREATE TABLE IF NOT EXISTS admin_accounts (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
--  TABLE: user_accounts
-- ============================================================
CREATE TABLE IF NOT EXISTS user_accounts (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(150) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    phone           VARCHAR(20),
    address         TEXT,
    gender          ENUM('Male','Female','Other') DEFAULT 'Other',
    dob             DATE,
    password        VARCHAR(255) NOT NULL,
    account_number  VARCHAR(20) NOT NULL UNIQUE,
    ifsc            VARCHAR(20) NOT NULL DEFAULT 'NEXA0001',
    balance         DECIMAL(15,2) DEFAULT 0.00,
    is_active       TINYINT(1) DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
--  TABLE: transactions
-- ============================================================
CREATE TABLE IF NOT EXISTS transactions (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    sender_id    INT NOT NULL,
    receiver_id  INT NOT NULL,
    amount       DECIMAL(15,2) NOT NULL,
    note         VARCHAR(255) DEFAULT NULL,
    status       ENUM('success','failed','pending') DEFAULT 'success',
    date_time    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id)   REFERENCES user_accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES user_accounts(id) ON DELETE CASCADE
);

-- ============================================================
--  SEED: Default Admin
--  Password: admin123 (bcrypt)
-- ============================================================
INSERT INTO admin_accounts (username, email, password) VALUES
('Super Admin', 'admin@nexabank.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ============================================================
--  SEED: Sample Users
--  All passwords: password123
-- ============================================================
INSERT INTO user_accounts (name, email, phone, address, gender, dob, password, account_number, ifsc, balance) VALUES
('Arjun Sharma',   'arjun@example.com',   '9876543210', '12 MG Road, Mumbai, Maharashtra',    'Male',   '1990-05-15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'NEXA0000000001', 'NEXA0001', 125000.00),
('Priya Patel',    'priya@example.com',   '9876543211', '45 Ring Road, Ahmedabad, Gujarat',    'Female', '1995-08-22', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'NEXA0000000002', 'NEXA0001', 87500.00),
('Rahul Verma',    'rahul@example.com',   '9876543212', '78 Park Street, Kolkata, West Bengal','Male',   '1988-11-30', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'NEXA0000000003', 'NEXA0001', 210000.00),
('Sneha Reddy',    'sneha@example.com',   '9876543213', '23 Jubilee Hills, Hyderabad, Telangana','Female','1993-03-07','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'NEXA0000000004', 'NEXA0001', 54000.00);

-- ============================================================
--  SEED: Sample Transactions
-- ============================================================
INSERT INTO transactions (sender_id, receiver_id, amount, note, status) VALUES
(1, 2, 5000.00,  'Rent payment',       'success'),
(2, 3, 12000.00, 'Project invoice',    'success'),
(3, 1, 3500.00,  'Dinner split',       'success'),
(1, 4, 8000.00,  'Freelance work',     'success'),
(4, 2, 2000.00,  'Gift',               'success'),
(3, 4, 15000.00, 'Business payment',   'success');
