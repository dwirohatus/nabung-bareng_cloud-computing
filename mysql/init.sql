CREATE DATABASE IF NOT EXISTS nabung_bareng;

USE nabung_bareng;

-- =========================
-- TABLE USERS
-- =========================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    balance BIGINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- TABLE GOALS
-- =========================
CREATE TABLE goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100),
    description TEXT,
    target_amount BIGINT,
    current_amount BIGINT DEFAULT 0,
    deadline DATE,
    status ENUM('active','completed') DEFAULT 'active',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- TABLE TRANSACTIONS
-- =========================
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    goal_id INT,
    user_id INT,
    amount BIGINT,
    fee BIGINT DEFAULT 2500,
    payment_method VARCHAR(50),
    status ENUM('pending','success','failed') DEFAULT 'success',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- DUMMY DATA USERS
-- =========================
INSERT INTO users(
    name,
    email,
    password,
    balance
)
VALUES
(
    'Dwi',
    'dwi@gmail.com',
    '123456',
    500000
),
(
    'Budi',
    'budi@gmail.com',
    '123456',
    300000
);

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
    'Tabungan bersama untuk liburan',
    10000000,
    3500000,
    '2026-12-31',
    1
);