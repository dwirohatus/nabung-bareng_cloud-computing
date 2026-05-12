<?php

header('Content-Type: application/json');

include '../../config/database.php';

/* =========================
   USER ID
========================= */

$user_id = $_GET['user_id'] ?? 1;

/* =========================
   GET GOALS
========================= */

$query = mysqli_query(
    $conn,
    "SELECT * FROM goals
     WHERE user_id = '$user_id'
     ORDER BY id DESC"
);

if (!$query) {

    echo json_encode([
        "status" => false,
        "message" => mysqli_error($conn)
    ]);

    exit;
}

$data = [];

while ($row = mysqli_fetch_assoc($query)) {
    $data[] = $row;
}

echo json_encode([
    "status" => true,
    "data" => $data
]);