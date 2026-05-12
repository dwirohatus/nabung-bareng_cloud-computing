<?php

header('Content-Type: application/json');

include '../../config/database.php';

/* =========================
   VALIDASI
========================= */

if (
    !isset($_POST['goal_id']) ||
    !isset($_POST['amount']) ||
    !isset($_POST['user_id'])
) {

    echo json_encode([
        "status" => false,
        "message" => "Data tidak lengkap"
    ]);

    exit;
}

/* =========================
   AMBIL DATA
========================= */

$goal_id = (int) $_POST['goal_id'];
$user_id = (int) $_POST['user_id'];
$amount  = (int) $_POST['amount'];

$payment_method = 'manual';
$status = 'success';
$fee = 2500;

/* =========================
   CEK GOAL
========================= */

$goalQuery = mysqli_query(
    $conn,
    "SELECT * FROM goals
     WHERE id='$goal_id'
     LIMIT 1"
);

if (mysqli_num_rows($goalQuery) == 0) {

    echo json_encode([
        "status" => false,
        "message" => "Goals tidak ditemukan"
    ]);

    exit;
}

$goal = mysqli_fetch_assoc($goalQuery);

/* =========================
   INSERT TRANSACTION
========================= */

$insert = mysqli_query(
    $conn,
    "INSERT INTO transactions
    (
        goal_id,
        user_id,
        amount,
        fee,
        payment_method,
        status
    )
    VALUES
    (
        '$goal_id',
        '$user_id',
        '$amount',
        '$fee',
        '$payment_method',
        '$status'
    )"
);

if (!$insert) {

    echo json_encode([
        "status" => false,
        "message" => mysqli_error($conn)
    ]);

    exit;
}

/* =========================
   UPDATE GOALS
========================= */

$newAmount = $goal['current_amount'] + $amount;

$update = mysqli_query(
    $conn,
    "UPDATE goals
     SET current_amount='$newAmount'
     WHERE id='$goal_id'"
);

if (!$update) {

    echo json_encode([
        "status" => false,
        "message" => mysqli_error($conn)
    ]);

    exit;
}

/* =========================
   SUCCESS
========================= */

echo json_encode([
    "status" => true,
    "message" => "Setoran berhasil"
]);