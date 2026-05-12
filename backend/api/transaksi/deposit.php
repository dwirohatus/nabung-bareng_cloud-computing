<?php

header('Content-Type: application/json');

include '../../config/database.php';

/* =========================
   VALIDASI
========================= */

if (
    !isset($_POST['goal_id']) ||
    !isset($_POST['amount'])  ||
    !isset($_POST['user_id'])
) {
    echo json_encode([
        "status"  => false,
        "message" => "Data tidak lengkap"
    ]);
    exit;
}

/* =========================
   AMBIL DATA
========================= */

$goal_id        = (int) $_POST['goal_id'];
$user_id        = (int) $_POST['user_id'];
$amount         = (int) $_POST['amount'];
$payment_method = 'manual';
$status         = 'success';
$fee            = 2500;

/* =========================
   CEK GOAL
========================= */

$goalQuery = mysqli_query($conn,
    "SELECT * FROM goals WHERE id='$goal_id' LIMIT 1"
);

if (mysqli_num_rows($goalQuery) == 0) {
    echo json_encode([
        "status"  => false,
        "message" => "Goals tidak ditemukan"
    ]);
    exit;
}

$goal = mysqli_fetch_assoc($goalQuery);

/* =========================
   INSERT TRANSACTION
========================= */

$insert = mysqli_query($conn,
    "INSERT INTO transactions
        (goal_id, user_id, amount, fee, payment_method, status)
     VALUES
        ('$goal_id', '$user_id', '$amount', '$fee', '$payment_method', '$status')"
);

if (!$insert) {
    echo json_encode([
        "status"  => false,
        "message" => mysqli_error($conn)
    ]);
    exit;
}

/* =========================
   UPDATE GOALS
========================= */

$oldAmount = (int) $goal['current_amount'];
$newAmount = $oldAmount + $amount;
$target    = (int) $goal['target_amount'];
$goalTitle = $goal['title'];

$update = mysqli_query($conn,
    "UPDATE goals SET current_amount='$newAmount' WHERE id='$goal_id'"
);

if (!$update) {
    echo json_encode([
        "status"  => false,
        "message" => mysqli_error($conn)
    ]);
    exit;
}

/* =========================
   HELPER: INSERT NOTIFIKASI
========================= */

function insertNotif($conn, $user_id, $title, $message) {
    $title   = mysqli_real_escape_string($conn, $title);
    $message = mysqli_real_escape_string($conn, $message);
    mysqli_query($conn,
        "INSERT INTO notifications (user_id, title, message, is_read, created_at)
         VALUES ('$user_id', '$title', '$message', 0, NOW())"
    );
}

/* =========================
   NOTIFIKASI OTOMATIS
========================= */

$amountFmt = 'Rp' . number_format($amount, 0, ',', '.');
$totalFmt  = 'Rp' . number_format($newAmount, 0, ',', '.');

// 1. Notifikasi setor berhasil — selalu muncul
insertNotif(
    $conn,
    $user_id,
    '💰 Setoran Berhasil',
    "Kamu baru saja menyetor $amountFmt ke goals \"$goalTitle\". Total terkumpul: $totalFmt."
);

// 2. Notifikasi milestone progress (hanya sekali per milestone)
if ($target > 0) {
    $oldPct = (int) floor(($oldAmount / $target) * 100);
    $newPct = (int) floor(($newAmount / $target) * 100);

    // Milestone 25%
    if ($oldPct < 25 && $newPct >= 25 && $newPct < 100) {
        insertNotif(
            $conn,
            $user_id,
            '🎯 Sudah 25%!',
            "Goals \"$goalTitle\" sudah mencapai 25%. Semangat terus menabung!"
        );
    }

    // Milestone 50%
    if ($oldPct < 50 && $newPct >= 50 && $newPct < 100) {
        insertNotif(
            $conn,
            $user_id,
            '🚀 Setengah Jalan!',
            "Goals \"$goalTitle\" sudah 50% tercapai. Kamu hebat, tinggal selangkah lagi!"
        );
    }

    // Milestone 75%
    if ($oldPct < 75 && $newPct >= 75 && $newPct < 100) {
        insertNotif(
            $conn,
            $user_id,
            '⚡ Hampir Sampai!',
            "Goals \"$goalTitle\" sudah 75%! Sedikit lagi menuju target."
        );
    }

    // 3. Notifikasi goals tercapai 100%
    if ($oldAmount < $target && $newAmount >= $target) {
        insertNotif(
            $conn,
            $user_id,
            '🎉 Goals Tercapai!',
            "Selamat! Goals \"$goalTitle\" sudah 100% tercapai. Target $totalFmt berhasil terpenuhi!"
        );

        // Update status goals jadi completed
        mysqli_query($conn,
            "UPDATE goals SET status='completed' WHERE id='$goal_id'"
        );
    }
}

/* =========================
   SUCCESS
========================= */

echo json_encode([
    "status"  => true,
    "message" => "Setoran berhasil"
]);