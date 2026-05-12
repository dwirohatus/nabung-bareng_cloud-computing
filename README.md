# Nabung Bareng App

Aplikasi “Nabung Bareng” adalah aplikasi tabungan bersama berbasis PHP yang mendukung fitur goals bersama, progress visual, reminder pintar, serta integrasi cloud menggunakan Docker dan LocalStack.

---

# Fitur Utama

- Goals bersama
- Progress visual tabungan
- Reminder otomatis
- Setoran fleksibel
- Sosial & transparan
- Upload bukti transfer
- Keamanan transaksi
- Free tier & biaya transaksi

---

# Teknologi

## Frontend

- HTML
- CSS
- Bootstrap
- JavaScript

## Backend

- PHP Native
- REST API

## Database

- MySQL

## DevOps & Cloud

- Docker
- Docker Compose
- LocalStack

---

# Arsitektur Sistem

Frontend (Nginx)
↓
Backend API (PHP Apache)
↓
MySQL + LocalStack

LocalStack menggunakan:

- S3 → penyimpanan file
- SNS → notifikasi reminder

---

# Struktur Server

## 1. Frontend Server

Menggunakan:

- Nginx

Fungsi:

- Menampilkan UI aplikasi
- Dashboard pengguna
- Form transaksi

---

## 2. Backend Server

Menggunakan:

- PHP Apache

Fungsi:

- REST API
- Login & autentikasi
- CRUD goals
- Manajemen transaksi

---

# Cara Menjalankan Project

## 1. Clone Project

```bash
git clone https://github.com/username/nabung-bareng.git
cd nabung-bareng
```
