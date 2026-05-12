CREATE DATABASE IF NOT EXISTS nabung_bareng;

USE nabung_bareng;

-- =========================
-- TABLE USERS
-- =========================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    balance BIGINT DEFAULT 0,
    role ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- TABLE GOALS
-- =========================
CREATE TABLE goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    target_amount BIGINT NOT NULL,
    current_amount BIGINT DEFAULT 0,
    deadline DATE,
    created_by INT,
    status ENUM('active','completed','cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- =========================
-- TABLE GOAL MEMBERS
-- =========================
CREATE TABLE goal_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    goal_id INT,
    user_id INT,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (goal_id) REFERENCES goals(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =========================
-- TABLE TRANSACTIONS
-- =========================
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    goal_id INT,
    user_id INT,
    type ENUM('deposit','withdraw') NOT NULL,
    amount BIGINT NOT NULL,
    fee BIGINT DEFAULT 2500,
    payment_method VARCHAR(50),
    status ENUM('pending','success','failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (goal_id) REFERENCES goals(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =========================
-- TABLE NOTIFICATIONS
-- =========================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255),
    message TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =========================
-- TABLE REMINDERS
-- =========================
CREATE TABLE reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    goal_id INT,
    reminder_type ENUM('daily','weekly','monthly'),
    next_schedule DATETIME,

    FOREIGN KEY (goal_id) REFERENCES goals(id)
);

-- =========================
-- DUMMY DATA USERS
-- =========================
INSERT INTO users(name, email, password, balance)
VALUES
('Dwi', 'dwi@gmail.com', '123456', 500000),
('Budi', 'budi@gmail.com', '123456', 300000);

-- =========================
-- DUMMY DATA GOALS
-- =========================
INSERT INTO goals(
    title,
    description,
    target_amount,
    current_amount,
    deadline,
    created_by
)
VALUES
(
    'Liburan Bali',
    'Tabungan untuk liburan bersama',
    10000000,
    3500000,
    '2026-12-31',
    1
);

-- =========================
-- DUMMY MEMBER
-- =========================
INSERT INTO goal_members(goal_id, user_id)
VALUES
(1,1),
(1,2);

-- =========================
-- DUMMY TRANSAKSI
-- =========================
INSERT INTO transactions(
    goal_id,
    user_id,
    type,
    amount,
    payment_method,
    status
)
VALUES
(
    1,
    1,
    'deposit',
    200000,
    'QRIS',
    'success'
);