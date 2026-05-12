<?php
session_start();

/* =========================
   REDIRECT JIKA SUDAH LOGIN
========================= */

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error   = '';
$success = '';

/* =========================
   HANDLE REGISTER
========================= */

if (isset($_POST['register'])) {

    $nama      = trim($_POST['nama'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    /* VALIDASI */

    if (
        empty($nama) ||
        empty($email) ||
        empty($password)
    ) {

        $error = '⚠️ Semua field wajib diisi.';

    } elseif (strlen($password) < 6) {

        $error = '⚠️ Password minimal 6 karakter.';

    } elseif ($password !== $konfirmasi) {

        $error = '⚠️ Konfirmasi password tidak cocok.';

    } else {

        /* DATA KE BACKEND */

        $data = [
            'name'     => $nama,
            'email'    => $email,
            'password' => $password
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded",
                'method'  => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        $context = stream_context_create($options);

        $result = @file_get_contents(
            'http://backend/api/auth/register.php',
            false,
            $context
        );

        /* ERROR KONEK */

        if ($result === false) {

            $error = '❌ Gagal konek ke backend.';

        } else {

            /* DECODE JSON */

            $response = json_decode($result, true);

            if (
                $response &&
                isset($response['status']) &&
                $response['status'] == true
            ) {

                $success = '✅ Registrasi berhasil! Silakan login.';

            } else {

                $error = '❌ ' . htmlspecialchars(
                    $response['message'] ?? 'Gagal registrasi'
                );
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Register – Nabung Bareng</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap"
        rel="stylesheet"
    >

    <style>

        :root {
            --green-d: #0a5c36;
            --green-m: #1a8c56;
            --green-l: #22c573;
            --ink: #0d1a13;
            --muted: #6b8f7a;
            --border: #d8ede3;
            --cream: #f7faf8;
            --danger: #e53e3e;
        }

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:'DM Sans',sans-serif;
            min-height:100vh;
            display:flex;
            background:var(--cream);
        }

        .left-panel{
            width:420px;
            background:linear-gradient(
                160deg,
                var(--green-d) 0%,
                var(--green-m) 60%,
                var(--green-l) 100%
            );

            display:flex;
            flex-direction:column;
            justify-content:center;
            padding:60px 48px;
            position:relative;
            overflow:hidden;
            flex-shrink:0;
        }

        .panel-logo{
            font-family:'Syne',sans-serif;
            font-size:28px;
            font-weight:800;
            color:#fff;
            margin-bottom:48px;
        }

        .panel-title{
            font-family:'Syne',sans-serif;
            font-size:36px;
            font-weight:800;
            color:#fff;
            line-height:1.2;
            margin-bottom:16px;
        }

        .panel-desc{
            font-size:15px;
            color:rgba(255,255,255,0.75);
            line-height:1.6;
            margin-bottom:32px;
        }

        .feature-item{
            display:flex;
            align-items:center;
            gap:12px;
            margin-bottom:14px;
        }

        .feature-dot{
            width:32px;
            height:32px;
            background:rgba(255,255,255,0.15);
            border-radius:8px;
            display:flex;
            align-items:center;
            justify-content:center;
        }

        .feature-text{
            font-size:14px;
            color:rgba(255,255,255,0.85);
            font-weight:500;
        }

        .right-panel{
            flex:1;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:40px 20px;
        }

        .form-box{
            width:100%;
            max-width:420px;
        }

        .form-title{
            font-family:'Syne',sans-serif;
            font-size:28px;
            font-weight:800;
            color:var(--ink);
            margin-bottom:6px;
        }

        .form-subtitle{
            font-size:14px;
            color:var(--muted);
            margin-bottom:32px;
        }

        .form-subtitle a{
            color:var(--green-m);
            font-weight:600;
            text-decoration:none;
        }

        .alert{
            padding:12px 16px;
            border-radius:10px;
            font-size:13px;
            font-weight:500;
            margin-bottom:20px;
        }

        .alert-success{
            background:#e6faf2;
            color:var(--green-d);
            border:1px solid #b7f0d4;
        }

        .alert-danger{
            background:#fef2f2;
            color:var(--danger);
            border:1px solid #fecaca;
        }

        .field{
            margin-bottom:18px;
        }

        .field label{
            display:block;
            font-size:13px;
            font-weight:600;
            color:var(--ink);
            margin-bottom:7px;
        }

        .field input{
            width:100%;
            padding:12px 16px;
            border:1.5px solid var(--border);
            border-radius:10px;
            font-size:14px;
        }

        .btn-submit{
            width:100%;
            padding:14px;
            background:linear-gradient(
                90deg,
                var(--green-d),
                var(--green-m)
            );

            color:#fff;
            border:none;
            border-radius:10px;
            font-size:15px;
            font-weight:700;
            cursor:pointer;
        }

        @media (max-width:768px){
            .left-panel{
                display:none;
            }
        }

    </style>

</head>

<body>

<div class="left-panel">

    <div class="panel-logo">
        🪙 Nabung Bareng
    </div>

    <div class="panel-title">
        Mulai menabung bersama hari ini
    </div>

    <div class="panel-desc">
        Wujudkan impianmu bersama teman dan keluarga.
    </div>

    <div class="feature-item">
        <div class="feature-dot">🎯</div>
        <span class="feature-text">
            Buat goals tabungan bersama
        </span>
    </div>

    <div class="feature-item">
        <div class="feature-dot">📊</div>
        <span class="feature-text">
            Pantau progres secara real-time
        </span>
    </div>

    <div class="feature-item">
        <div class="feature-dot">🔔</div>
        <span class="feature-text">
            Reminder otomatis setiap bulan
        </span>
    </div>

</div>

<div class="right-panel">

    <div class="form-box">

        <div class="form-title">
            Buat Akun Baru
        </div>

        <div class="form-subtitle">
            Sudah punya akun?
            <a href="login.php">
                Masuk di sini
            </a>
        </div>

        <?php if ($error): ?>

            <div class="alert alert-danger">
                <?= $error ?>
            </div>

        <?php endif; ?>

        <?php if ($success): ?>

            <div class="alert alert-success">
                <?= $success ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="field">

                <label>Nama Lengkap</label>

                <input
                    type="text"
                    name="nama"
                    placeholder="Contoh: Syahrur Baihaqi"
                    value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                    required
                >

            </div>

            <div class="field">

                <label>Email</label>

                <input
                    type="email"
                    name="email"
                    placeholder="contoh@email.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    required
                >

            </div>

            <div class="field">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Minimal 6 karakter"
                    required
                >

            </div>

            <div class="field">

                <label>Konfirmasi Password</label>

                <input
                    type="password"
                    name="konfirmasi"
                    placeholder="Ulangi password"
                    required
                >

            </div>

            <button
                type="submit"
                name="register"
                class="btn-submit"
            >

                Buat Akun →

            </button>

        </form>

    </div>

</div>

</body>
</html>