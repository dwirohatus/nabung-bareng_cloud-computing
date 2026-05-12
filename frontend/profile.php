<?php
session_start();

include 'backend/config/database.php';

$userId = $_SESSION['user_id'] ?? 1;

$username =
    $_SESSION['name']
    ?? $_SESSION['user']['name']
    ?? $_SESSION['user']['email']
    ?? 'Pengguna';

$resGoals = @file_get_contents(
    'http://backend_server/api/goals/get_goals.php?user_id=' . $userId
);

$goals = [];

if ($resGoals !== false) {

    $decoded = json_decode($resGoals, true);

    if ($decoded && isset($decoded['data'])) {
        $goals = $decoded['data'];
    }
}

$totalGoals = count($goals);

$resTx = @file_get_contents(
    'http://backend_server/api/transaksi/get_transaksi.php?user_id=' . $userId
);

$transaksi = [];

if ($resTx !== false) {

    $decodedTx = json_decode($resTx, true);

    if ($decodedTx && isset($decodedTx['data'])) {
        $transaksi = $decodedTx['data'];
    }
}

$totalTabungan = array_sum(array_column($transaksi, 'amount'));

$saldo = $totalTabungan;

$qUser = mysqli_query($conn, "
SELECT * FROM users
WHERE id='$userId'
");

$user = mysqli_fetch_assoc($qUser);

if(!empty($user['avatar'])){

    $foto = str_replace(
        'http://host.docker.internal:4566',
        'http://localhost:4566',
        $user['avatar']
    );

}else{

    $foto = "https://ui-avatars.com/api/?name=" .
            urlencode($username) .
            "&background=22c76f&color=fff&size=256";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Profile - Nabung Bareng</title>

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

.navbar{
    width:100%;
    height:78px;
    background:#0b6b36;
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:0 28px;
    position:sticky;
    top:0;
    z-index:100;
}

.logo{
    display:flex;
    align-items:center;
    gap:12px;
    color:white;
    font-size:22px;
    font-weight:800;
}

.logo-icon{
    width:34px;
    height:34px;
    border-radius:50%;
    background:#ffcc4d;
    display:flex;
    align-items:center;
    justify-content:center;
}

.nav-menu{
    display:flex;
    align-items:center;
    gap:18px;
}

.nav-menu a{
    color:#d8f3df;
    text-decoration:none;
    font-size:14px;
    font-weight:600;
    transition:0.2s;
}

.nav-menu a:hover{
    color:white;
}

.nav-active{
    background:#22c76f;
    color:white !important;
    padding:10px 18px;
    border-radius:14px;
}

.page{
    max-width:1000px;
    margin:24px auto;
    padding:0 20px 50px;
}

.profile-card{
    background:white;
    border-radius:28px;
    overflow:hidden;
    border:1px solid #dceee1;
    box-shadow:0 10px 30px rgba(0,0,0,0.04);
    margin-bottom:24px;
}

.cover{
    height:150px;
    background:
    linear-gradient(135deg,#0b6b36,#27d977);
    position:relative;
}

.cover::after{
    content:'';
    position:absolute;
    inset:0;
    background-image:
    radial-gradient(rgba(255,255,255,0.08) 2px, transparent 2px);
    background-size:24px 24px;
}

.profile-content{
    padding:0 30px 30px;
    position:relative;
}

.avatar{
    width:95px;
    height:95px;
    border-radius:50%;
    border:5px solid white;
    overflow:hidden;
    margin-top:-45px;
    box-shadow:0 10px 25px rgba(0,0,0,0.12);
    cursor:pointer;
    display:block;
}

.avatar img{
    width:100%;
    height:100%;
    object-fit:cover;
}

.profile-name{
    font-size:30px;
    font-weight:800;
    margin-top:14px;
}

.profile-sub{
    color:#6f8b79;
    margin-top:4px;
}

.badge{
    display:inline-flex;
    align-items:center;
    gap:8px;
    margin-top:16px;
    padding:8px 18px;
    background:linear-gradient(135deg,#ffd86b,#ff9d76);
    border-radius:999px;
    color:#7a3d00;
    font-size:13px;
    font-weight:700;
}

.stats-row{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:18px;
    margin-bottom:24px;
}

.stat-box{
    background:white;
    border-radius:24px;
    padding:22px;
    border:1px solid #dceee1;
    box-shadow:0 8px 24px rgba(0,0,0,0.03);
}

.stat-icon{
    width:52px;
    height:52px;
    border-radius:18px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
    margin-bottom:16px;
}

.icon-goals{
    background:#e8fff0;
}

.icon-money{
    background:#fff5df;
}

.icon-wallet{
    background:#eef7ff;
}

.stat-val{
    font-size:28px;
    font-weight:800;
    margin-bottom:4px;
}

.stat-label{
    color:#7c9586;
    font-size:14px;
    font-weight:500;
}

.menu-card{
    background:white;
    border-radius:28px;
    overflow:hidden;
    border:1px solid #dceee1;
    box-shadow:0 8px 24px rgba(0,0,0,0.03);
}

.menu-item{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:18px 22px;
    border-bottom:1px solid #eef4ef;
    transition:0.2s;
    text-decoration:none;
    color:inherit;
}

.menu-item:last-child{
    border-bottom:none;
}

.menu-item:hover{
    background:#f7fcf8;
}

.menu-left{
    display:flex;
    align-items:center;
    gap:16px;
}

.menu-icon{
    width:46px;
    height:46px;
    border-radius:16px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
}

.menu-title{
    font-size:18px;
    font-weight:700;
}

.menu-desc{
    font-size:14px;
    color:#7f9789;
    margin-top:2px;
}

.arrow{
    color:#8aa395;
    font-size:22px;
}

.btn-logout{
    width:100%;
    margin-top:24px;
    padding:14px;
    border:none;
    border-radius:18px;
    background:white;
    border:2px solid #ffb9b9;
    color:#e24d4d;
    font-size:16px;
    font-weight:700;
    cursor:pointer;
}

.btn-logout:hover{
    background:#fff3f3;
}

</style>

</head>

<body>

<div class="navbar">

    <div class="logo">

        <div class="logo-icon">
            🪙
        </div>

        NabungBareng

    </div>

    <div class="nav-menu">

        <a href="dashboard.php">Dashboard</a>

        <a href="goals.php">Goals</a>

        <a href="transaksi.php">Transaksi</a>

        <a href="profile.php" class="nav-active">
            Profile
        </a>

    </div>

</div>

<div class="page">

    <div class="profile-card">

        <div class="cover"></div>

        <div class="profile-content">

            <form 
                action="upload_avatar_proxy.php" 
                method="POST" 
                enctype="multipart/form-data"
            >

                <input 
                    type="hidden" 
                    name="user_id" 
                    value="<?= $userId ?>"
                >

                <label for="avatarInput" class="avatar">

                    <img src="<?= $foto ?>" alt="Profile">

                </label>

                <input
                    type="file"
                    id="avatarInput"
                    name="avatar"
                    accept="image/*"
                    onchange="this.form.submit()"
                    hidden
                >

            </form>

            <div class="profile-name">
                <?= htmlspecialchars($username) ?>
            </div>

            <div class="profile-sub">
                Kelola tabungan dan goals finansialmu 🚀
            </div>

            <div class="badge">
                ⭐ PREMIUM MEMBER
            </div>

        </div>

    </div>

    <div class="stats-row">

        <div class="stat-box">

            <div class="stat-icon icon-goals">
                🎯
            </div>

            <div class="stat-val">
                <?= $totalGoals ?>
            </div>

            <div class="stat-label">
                Goals Aktif
            </div>

        </div>

        <div class="stat-box">

            <div class="stat-icon icon-money">
                💰
            </div>

            <div class="stat-val">
                Rp<?= number_format($totalTabungan,0,',','.') ?>
            </div>

            <div class="stat-label">
                Total Tabungan
            </div>

        </div>

        <div class="stat-box">

            <div class="stat-icon icon-wallet">
                💳
            </div>

            <div class="stat-val">
                Rp<?= number_format($saldo,0,',','.') ?>
            </div>

            <div class="stat-label">
                Saldo Akun
            </div>

        </div>

    </div>

    <div class="menu-card">

        <a href="edit_profile.php" class="menu-item">

            <div class="menu-left">

                <div class="menu-icon" style="background:#e9fff1;">
                    ✏️
                </div>

                <div>

                    <div class="menu-title">
                        Edit Profile
                    </div>

                    <div class="menu-desc">
                        Ubah nama dan informasi akun
                    </div>

                </div>

            </div>

            <div class="arrow">›</div>

        </a>

        <a href="notifikasi.php" class="menu-item">

            <div class="menu-left">

                <div class="menu-icon" style="background:#fff5e7;">
                    🔔
                </div>

                <div>

                    <div class="menu-title">
                        Notifikasi
                    </div>

                    <div class="menu-desc">
                        Atur reminder tabungan
                    </div>

                </div>

            </div>

            <div class="arrow">›</div>

        </a>

        <a href="keamanan.php" class="menu-item">

            <div class="menu-left">

                <div class="menu-icon" style="background:#eef5ff;">
                    🔒
                </div>

                <div>

                    <div class="menu-title">
                        Keamanan
                    </div>

                    <div class="menu-desc">
                        Ubah password akun
                    </div>

                </div>

            </div>

            <div class="arrow">›</div>

        </a>

        <a href="grup.php" class="menu-item">

            <div class="menu-left">

                <div class="menu-icon" style="background:#f5ecff;">
                    👥
                </div>

                <div>

                    <div class="menu-title">
                        Grup Nabung
                    </div>

                    <div class="menu-desc">
                        Kelola anggota dan tabungan bersama
                    </div>

                </div>

            </div>

            <div class="arrow">›</div>

        </a>

    </div>

    <a href="logout.php">

        <button class="btn-logout">
            🚪 Keluar dari Akun
        </button>

    </a>

</div>

</body>
</html>