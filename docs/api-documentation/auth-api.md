# Authentication API

## Login

Endpoint:
POST /api/auth/login.php

Request:

{
"email": "dwi@gmail.com",
"password": "123456"
}

Response:

{
"status": true,
"message": "Login berhasil"
}

---

## Register

Endpoint:
POST /api/auth/register.php

Request:

{
"name": "Dwi",
"email": "dwi@gmail.com",
"password": "123456"
}

Response:

{
"status": true,
"message": "Register berhasil"
}
