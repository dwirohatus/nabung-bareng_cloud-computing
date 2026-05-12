<?php
session_start();

include 'backend/config/database.php';

$query = mysqli_query($conn, "
SELECT 
    g.id,
    g.title,
    COUNT(gm.id) as total_member
FROM goals g
INNER JOIN goal_members gm 
ON g.id = gm.goal_id
GROUP BY g.id
ORDER BY g.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>

<title>Grup Nabung</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Poppins',sans-serif;
    background:#f4f8f5;
    padding:40px;
}

.card{
    background:white;
    max-width:700px;
    margin:auto;
    border-radius:28px;
    padding:32px;
    border:1px solid #dceee1;
    box-shadow:0 10px 30px rgba(0,0,0,0.04);
}

.heading{
    font-size:36px;
    font-weight:800;
    margin-bottom:8px;
}

.sub{
    color:#73907d;
    margin-bottom:28px;
}

.group{
    padding:22px;
    border-radius:22px;
    background:#f7fcf8;
    margin-top:18px;
    border:1px solid #e9f2ec;
    transition:0.2s;
}

.group:hover{
    transform:translateY(-2px);
    box-shadow:0 8px 20px rgba(0,0,0,0.05);
}

.title{
    font-size:22px;
    font-weight:700;
    color:#111;
}

.member{
    color:#73907d;
    margin-top:8px;
    font-size:15px;
}

.empty{
    text-align:center;
    padding:40px 20px;
    color:#7f9789;
    font-size:16px;
}

@media(max-width:768px){

    body{
        padding:20px;
    }

    .card{
        padding:24px;
    }

    .heading{
        font-size:28px;
    }

    .title{
        font-size:18px;
    }

}

</style>
</head>
<body>

<div class="card">

    <div class="heading">
        👥 Grup Nabung
    </div>

    <div class="sub">
        Kelola tabungan bersama teman atau keluarga
    </div>

    <?php if(mysqli_num_rows($query) > 0): ?>

        <?php while($row = mysqli_fetch_assoc($query)): ?>

            <div class="group">

                <div class="title">
                    <?= htmlspecialchars($row['title']) ?>
                </div>

                <div class="member">
                    <?= $row['total_member'] ?> anggota aktif
                </div>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="empty">
            Belum ada grup nabung 😢
        </div>

    <?php endif; ?>

</div>

</body>
</html>