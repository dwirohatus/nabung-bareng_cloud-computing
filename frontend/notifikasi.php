<?php

include 'backend/config/database.php';

$query = mysqli_query($conn,
"SELECT * FROM notifications
ORDER BY created_at DESC");

?>

<!DOCTYPE html>
<html>

<head>

    <title>Notifikasi</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Poppins',sans-serif;
        }

        body{
            background:#f4f7fb;
        }

        .container{
            width:700px;
            margin:40px auto;
            background:white;
            padding:30px;
            border-radius:20px;
            box-shadow:0 5px 20px rgba(0,0,0,0.08);
        }

        .header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:25px;
        }

        .header h1{
            font-size:32px;
            color:#111827;
        }

        .badge{
            background:red;
            color:white;
            padding:5px 12px;
            border-radius:50px;
            font-size:14px;
        }

        .notif{
            background:#f9fafb;
            padding:18px;
            border-radius:16px;
            margin-bottom:15px;
            border-left:5px solid #3b82f6;
            transition:0.3s;
        }

        .notif:hover{
            transform:translateY(-2px);
            box-shadow:0 5px 15px rgba(0,0,0,0.08);
        }

        .notif h3{
            font-size:18px;
            color:#111827;
            margin-bottom:5px;
        }

        .notif p{
            color:#6b7280;
            margin-bottom:10px;
        }

        .notif small{
            color:#9ca3af;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="header">

        <h1>🔔 Notifikasi</h1>

        <?php

        $unread = mysqli_query($conn,
        "SELECT COUNT(*) as total
        FROM notifications
        WHERE is_read=0");

        $badge = mysqli_fetch_assoc($unread);

        ?>

        <div class="badge">

            <?= $badge['total']; ?>

        </div>

    </div>

    <?php while($notif = mysqli_fetch_assoc($query)) : ?>

        <div class="notif">

            <h3><?= $notif['title']; ?></h3>

            <p><?= $notif['message']; ?></p>

            <small><?= $notif['created_at']; ?></small>

        </div>

    <?php endwhile; ?>

</div>

</body>

</html>