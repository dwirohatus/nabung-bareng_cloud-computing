<?php
session_start();

$username =
    $_SESSION['name']
    ?? $_SESSION['user']['name']
    ?? 'Pengguna';

$email =
    $_SESSION['email']
    ?? $_SESSION['user']['email']
    ?? 'user@gmail.com';

$success = false;

if(isset($_POST['save'])){

    $username = $_POST['name'];
    $email    = $_POST['email'];

    $_SESSION['name'] = $username;
    $_SESSION['email'] = $email;

    if(isset($_SESSION['user'])){
        $_SESSION['user']['name']  = $username;
        $_SESSION['user']['email'] = $email;
    }

    $success = true;
}

$foto = "https://ui-avatars.com/api/?name=" .
        urlencode($username) .
        "&background=22c76f&color=fff&size=256";
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Profile</title>

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
    color:#111;
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

.avatar{
    width:110px;
    height:110px;
    border-radius:50%;
    overflow:hidden;
    margin:auto;
    margin-bottom:24px;
    border:5px solid #fff;
    box-shadow:0 10px 25px rgba(0,0,0,0.12);
}

.avatar img{
    width:100%;
    height:100%;
    object-fit:cover;
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
    transition:0.2s;
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

@media(max-width:768px){

    .container{
        padding:14px;
        margin:20px auto;
    }

    .card{
        padding:24px;
    }

    .title{
        font-size:24px;
    }

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
            Edit Profile
        </div>

        <div class="subtitle">
            Kelola informasi akun kamu
        </div>

        <?php if($success): ?>

            <div class="alert">
                Profile berhasil diperbarui
            </div>

        <?php endif; ?>

        <div class="avatar">
            <img src="<?= $foto ?>">
        </div>

        <form method="POST">

            <div class="form-group">

                <label>Nama Lengkap</label>

                <input
                    type="text"
                    name="name"
                    value="<?= htmlspecialchars($username) ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>Email</label>

                <input
                    type="email"
                    name="email"
                    value="<?= htmlspecialchars($email) ?>"
                    required
                >

            </div>

            <button type="submit" name="save" class="btn">
                Simpan Perubahan
            </button>

        </form>

    </div>

</div>

</body>
</html>