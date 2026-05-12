-- BACKUP DATABASE NABUNG BARENG
-- TANGGAL: 08 MEI 2026

CREATE TABLE backup_users AS
SELECT * FROM users;

CREATE TABLE backup_goals AS
SELECT * FROM goals;

CREATE TABLE backup_transactions AS
SELECT * FROM transactions;