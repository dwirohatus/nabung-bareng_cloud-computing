<?php
session_start();

$success = false;

if(isset($_POST['save'])){
    $success = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Keamanan Akun</title>

<link rel="preconnect" href="https://fonts.googleapis.com">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Poppins',sans-serif;
    background:#f4f8f5;
}

.container{
    max-width:650px;
    margin:40px auto;
    padding:20px;
}

.card{
    background:white;
    border-radius:28px;
    padding:34px;
    border:1px solid #dceee1;
    box-shadow:0 10px 30px rgba(0,0,0,0.04);
}

.back{
    display:inline-block;
    margin-bottom:20px;
    text-decoration:none;
    color:#0b6b36;
    font-weight:600;
}

.title{
    font-size:30px;
    font-weight:800;
    margin-bottom:8px;
}

.subtitle{
    color:#73907d;
    margin-bottom:30px;
}

.form-group{
    margin-bottom:22px;
}

label{
    display:block;
    margin-bottom:8px;
    font-weight:600;
}

input{
    width:100%;
    padding:16px;
    border-radius:16px;
    border:1px solid #d8e7dd;
    font-size:15px;
    font-family:'Poppins',sans-serif;
}

input:focus{
    outline:none;
    border-color:#22c76f;
}

.btn{
    width:100%;
    padding:16px;
    border:none;
    border-radius:18px;
    background:#22c76f;
    color:white;
    font-size:16px;
    font-weight:700;
    cursor:pointer;
}

.btn:hover{
    background:#159954;
}

.alert{
    background:#e8fff1;
    color:#0b6b36;
    padding:14px;
    border-radius:14px;
    margin-bottom:20px;
    font-weight:600;
}

</style>
</head>
<body>

<div class="container">

    <a href="profile.php" class="back">
        ← Kembali
    </a>

    <div class="card">

        <div class="title">
            Keamanan Akun
        </div>

        <div class="subtitle">
            Ganti password akun kamu
        </div>

        <?php if($success): ?>

            <div class="alert">
                Password berhasil diperbarui
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="form-group">

                <label>Password Lama</label>

                <input
                    type="password"
                    required
                >

            </div>

            <div class="form-group">

                <label>Password Baru</label>

                <input
                    type="password"
                    required
                >

            </div>

            <div class="form-group">

                <label>Konfirmasi Password Baru</label>

                <input
                    type="password"
                    required
                >

            </div>

            <button type="submit" name="save" class="btn">
                Simpan Password
            </button>

        </form>

    </div>

</div>

</body>
</html>