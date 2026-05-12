<?php

include 'backend/config/database.php';

$id = $_GET['id'] ?? 0;

if(isset($_POST['add_member'])){

    $member_name = mysqli_real_escape_string(
        $conn,
        $_POST['member_name']
    );

    mysqli_query(
        $conn,
        "INSERT INTO goal_members(goal_id, member_name)
         VALUES('$id','$member_name')"
    );

    header("Location: detail_goal.php?id=$id");
    exit;
}

if(isset($_POST['update_member'])){

    $member_id   = $_POST['member_id'];

    $member_name = mysqli_real_escape_string(
        $conn,
        $_POST['member_name']
    );

    mysqli_query(
        $conn,
        "UPDATE goal_members
         SET member_name='$member_name'
         WHERE id='$member_id'"
    );

    header("Location: detail_goal.php?id=$id");
    exit;
}

if(isset($_POST['delete_member'])){

    $member_id = $_POST['member_id'];

    mysqli_query(
        $conn,
        "DELETE FROM goal_members
         WHERE id='$member_id'"
    );

    header("Location: detail_goal.php?id=$id");
    exit;
}

$query = mysqli_query(
    $conn,
    "SELECT * FROM goals
     WHERE id='$id'
     LIMIT 1"
);

$goal = mysqli_fetch_assoc($query);

if(!$goal){
    die("Goals tidak ditemukan");
}

$target  = $goal['target_amount'];
$current = $goal['current_amount'];

$progress = 0;

if($target > 0){
    $progress = ($current / $target) * 100;
}

$progress = min(100, round($progress));

$member_query = mysqli_query(
    $conn,
    "SELECT * FROM goal_members
     WHERE goal_id='$id'
     ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Detail Goals</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>

body{
    background:#f4f8f5;
    font-family:'Poppins',sans-serif;
}

.detail-card{
    border:none;
    border-radius:28px;
    overflow:hidden;
    box-shadow:0 15px 40px rgba(0,0,0,0.06);
}

.top-banner{
    background:linear-gradient(135deg,#0b6b36,#22c76f);
    padding:35px;
    color:white;
    position:relative;
}

.top-banner::after{
    content:'';
    position:absolute;
    width:180px;
    height:180px;
    background:rgba(255,255,255,0.08);
    border-radius:50%;
    right:-50px;
    bottom:-70px;
}

.goal-title{
    font-size:34px;
    font-weight:800;
    margin-bottom:12px;
}

.goal-info{
    display:flex;
    gap:30px;
    flex-wrap:wrap;
    margin-top:20px;
}

.info-box{
    background:rgba(255,255,255,0.12);
    padding:16px 20px;
    border-radius:18px;
    backdrop-filter:blur(8px);
}

.info-label{
    font-size:13px;
    opacity:0.9;
}

.info-value{
    font-size:20px;
    font-weight:700;
    margin-top:5px;
}

.content{
    padding:35px;
}

.progress{
    height:20px;
    border-radius:20px;
    background:#edf3ef;
}

.progress-bar{
    border-radius:20px;
    font-weight:600;
}

.section-title{
    font-size:22px;
    font-weight:700;
    margin-bottom:20px;
}

.member-list{
    list-style:none;
    padding:0;
}

.member-item{
    background:#f7fbf8;
    padding:14px 18px;
    border-radius:16px;
    margin-bottom:12px;
    border:1px solid #e1eee5;
}

.member-left{
    display:flex;
    align-items:center;
    gap:12px;
}

.avatar{
    width:42px;
    height:42px;
    border-radius:50%;
    background:#22c76f;
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:700;
    flex-shrink:0;
}

.form-control{
    height:52px;
    border-radius:16px;
    border:1px solid #dce9df;
}

.form-control:focus{
    box-shadow:none;
    border-color:#22c76f;
}

.btn-success{
    background:#16a34a;
    border:none;
    border-radius:16px;
    height:52px;
    font-weight:600;
}

.btn-success:hover{
    background:#15803d;
}

.btn-main{
    width:100%;
    margin-top:30px;
    height:58px;
    font-size:17px;
    border-radius:18px;
}

.member-input{
    height:42px !important;
    min-width:220px;
    border-radius:12px !important;
}

.action-btn{
    height:42px !important;
    border-radius:12px !important;
    padding:0 18px !important;
}

@media(max-width:768px){

    .member-form{
        flex-direction:column;
        align-items:stretch !important;
    }

    .member-left{
        width:100%;
    }

    .member-input{
        width:100%;
        min-width:unset;
    }

    .action-group{
        width:100%;
    }

    .action-group button{
        width:100%;
    }

}

</style>
</head>

<body>

<div class="container py-5">

    <div class="card detail-card">

        <div class="top-banner">

            <div class="goal-title">
                <?= htmlspecialchars($goal['title']) ?>
            </div>

            <div>
                <?= htmlspecialchars($goal['description']) ?>
            </div>

            <div class="goal-info">

                <div class="info-box">

                    <div class="info-label">
                        Target Tabungan
                    </div>

                    <div class="info-value">
                        Rp<?= number_format($target,0,',','.') ?>
                    </div>

                </div>

                <div class="info-box">

                    <div class="info-label">
                        Dana Terkumpul
                    </div>

                    <div class="info-value">
                        Rp<?= number_format($current,0,',','.') ?>
                    </div>

                </div>

                <div class="info-box">

                    <div class="info-label">
                        Progress
                    </div>

                    <div class="info-value">
                        <?= $progress ?>%
                    </div>

                </div>

            </div>

        </div>

        <div class="content">

            <div class="mb-4">

                <div class="d-flex justify-content-between mb-2">

                    <span class="fw-semibold">
                        Progress Goals
                    </span>

                    <span class="fw-bold text-success">
                        <?= $progress ?>%
                    </span>

                </div>

                <div class="progress">

                    <div
                        class="progress-bar bg-success"
                        style="width:<?= $progress ?>%"
                    >
                    </div>

                </div>

            </div>

            <div class="section-title">
                👥 Anggota Goals
            </div>

            <ul class="member-list">

                <?php while($member = mysqli_fetch_assoc($member_query)): ?>

                    <li class="member-item">

                        <form
                            method="POST"
                            class="member-form d-flex align-items-center justify-content-between gap-3"
                        >

                            <div class="member-left">

                                <div class="avatar">
                                    <?= strtoupper(substr($member['member_name'],0,1)) ?>
                                </div>

                                <input
                                    type="text"
                                    name="member_name"
                                    value="<?= htmlspecialchars($member['member_name']) ?>"
                                    class="form-control member-input"
                                    required
                                >

                            </div>

                            <div class="action-group d-flex gap-2">

                                <input
                                    type="hidden"
                                    name="member_id"
                                    value="<?= $member['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    name="update_member"
                                    class="btn btn-success action-btn"
                                >
                                    Simpan
                                </button>

                                <button
                                    type="submit"
                                    name="delete_member"
                                    class="btn btn-danger action-btn"
                                >
                                    Hapus
                                </button>

                            </div>

                        </form>

                    </li>

                <?php endwhile; ?>

            </ul>

            <form method="POST" class="mt-4">

                <div class="row">

                    <div class="col-md-9 mb-3">

                        <input
                            type="text"
                            name="member_name"
                            class="form-control"
                            placeholder="Masukkan nama anggota baru"
                            required
                        >

                    </div>

                    <div class="col-md-3 mb-3">

                        <button
                            type="submit"
                            name="add_member"
                            class="btn btn-success w-100"
                        >
                            Tambah
                        </button>

                    </div>

                </div>

            </form>

            <a
                href="transaksi.php"
                class="btn btn-success btn-main"
            >
                💸 Setor Sekarang
            </a>

        </div>

    </div>

</div>

</body>
</html>