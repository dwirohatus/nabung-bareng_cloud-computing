<?php

include 'backend/config/database.php';

// Tandai satu notifikasi sebagai sudah dibaca
if(isset($_POST['mark_read'])){
    $notif_id = (int)$_POST['notif_id'];
    mysqli_query($conn,
        "UPDATE notifications SET is_read=1 WHERE id='$notif_id'"
    );
    header("Location: notifikasi.php");
    exit;
}

// Tandai semua sebagai sudah dibaca
if(isset($_POST['mark_all_read'])){
    mysqli_query($conn,
        "UPDATE notifications SET is_read=1 WHERE is_read=0"
    );
    header("Location: notifikasi.php");
    exit;
}

// Ambil semua notifikasi
$query = mysqli_query($conn,
    "SELECT * FROM notifications ORDER BY created_at DESC"
);

// Hitung yang belum dibaca
$unread_query  = mysqli_query($conn,
    "SELECT COUNT(*) as total FROM notifications WHERE is_read=0"
);
$unread_row    = mysqli_fetch_assoc($unread_query);
$unread_count  = (int)$unread_row['total'];
$total_notif   = mysqli_num_rows($query);

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notifikasi - Nabung Bareng</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*{ margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }

body{ background:#f4f7fb; }

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
    margin-bottom:8px;
}

.header h1{ font-size:28px; color:#111827; font-weight:700; }

.header-right{ display:flex; align-items:center; gap:12px; }

.badge{
    background:#ef4444;
    color:white;
    padding:4px 12px;
    border-radius:50px;
    font-size:13px;
    font-weight:600;
    min-width:32px;
    text-align:center;
}

.badge.zero{ background:#d1d5db; color:#6b7280; }

.btn-mark-all{
    background:#f0fdf4;
    color:#16a34a;
    border:1.5px solid #bbf7d0;
    padding:7px 16px;
    border-radius:10px;
    font-size:13px;
    font-weight:600;
    cursor:pointer;
    font-family:'Poppins',sans-serif;
    transition:background .2s;
}
.btn-mark-all:hover{ background:#dcfce7; }

.subtitle{
    color:#9ca3af;
    font-size:13px;
    margin-bottom:24px;
}

/* Notifikasi item */
.notif{
    padding:18px 20px;
    border-radius:16px;
    margin-bottom:12px;
    border-left:5px solid #3b82f6;
    transition:.25s;
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:12px;
}

.notif.unread{
    background:#eff6ff;
    border-left-color:#3b82f6;
}

.notif.read{
    background:#f9fafb;
    border-left-color:#d1d5db;
}

.notif:hover{
    transform:translateY(-2px);
    box-shadow:0 5px 15px rgba(0,0,0,0.07);
}

.notif-body{ flex:1; }

.notif-title{
    font-size:16px;
    font-weight:600;
    color:#111827;
    margin-bottom:5px;
    display:flex;
    align-items:center;
    gap:8px;
}

.dot-unread{
    width:8px; height:8px;
    border-radius:50%;
    background:#3b82f6;
    flex-shrink:0;
}

.notif-msg{ color:#6b7280; font-size:14px; margin-bottom:8px; line-height:1.5; }

.notif-time{ color:#9ca3af; font-size:12px; }

.btn-read{
    background:white;
    color:#3b82f6;
    border:1.5px solid #bfdbfe;
    padding:6px 14px;
    border-radius:9px;
    font-size:12px;
    font-weight:600;
    cursor:pointer;
    font-family:'Poppins',sans-serif;
    white-space:nowrap;
    transition:background .2s;
    flex-shrink:0;
}
.btn-read:hover{ background:#eff6ff; }

/* Empty state */
.empty-state{
    text-align:center;
    padding:60px 20px;
    color:#9ca3af;
}
.empty-state .icon{ font-size:56px; margin-bottom:16px; }
.empty-state p{ font-size:15px; }

/* Back link */
.back-link{
    display:inline-flex;
    align-items:center;
    gap:6px;
    color:#6b7280;
    text-decoration:none;
    font-size:14px;
    margin-bottom:20px;
    transition:color .2s;
}
.back-link:hover{ color:#111827; }

@media(max-width:740px){
    .container{ width:100%; margin:0; border-radius:0; padding:20px; }
}
</style>
</head>
<body>

<div class="container">

    <a href="profile.php" class="back-link">← Kembali ke Profile</a>

    <div class="header">
        <h1>🔔 Notifikasi</h1>
        <div class="header-right">
            <span class="badge <?= $unread_count === 0 ? 'zero' : '' ?>">
                <?= $unread_count ?>
            </span>
            <?php if($unread_count > 0): ?>
            <form method="POST" style="margin:0">
                <button type="submit" name="mark_all_read" class="btn-mark-all">
                    ✓ Tandai semua dibaca
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="subtitle">
        <?= $total_notif ?> notifikasi
        <?= $unread_count > 0 ? "· <strong style='color:#3b82f6'>$unread_count belum dibaca</strong>" : '' ?>
    </div>

    <?php if($total_notif === 0): ?>

        <div class="empty-state">
            <div class="icon">🔕</div>
            <p>Belum ada notifikasi</p>
        </div>

    <?php else: ?>

        <?php while($notif = mysqli_fetch_assoc($query)): ?>

        <?php $is_read = (int)$notif['is_read']; ?>

        <div class="notif <?= $is_read ? 'read' : 'unread' ?>">

            <div class="notif-body">
                <div class="notif-title">
                    <?php if(!$is_read): ?>
                    <span class="dot-unread"></span>
                    <?php endif; ?>
                    <?= htmlspecialchars($notif['title']) ?>
                </div>
                <div class="notif-msg"><?= htmlspecialchars($notif['message']) ?></div>
                <div class="notif-time">
                    🕐 <?= date('d M Y, H:i', strtotime($notif['created_at'])) ?> WIB
                </div>
            </div>

            <?php if(!$is_read): ?>
            <form method="POST" style="margin:0">
                <input type="hidden" name="notif_id" value="<?= $notif['id'] ?>">
                <button type="submit" name="mark_read" class="btn-read">
                    Tandai dibaca
                </button>
            </form>
            <?php endif; ?>

        </div>

        <?php endwhile; ?>

    <?php endif; ?>

</div>

</body>
</html>