<?php

include '../../config/database.php';

header('Content-Type: application/json');

$user_id = isset($_GET['user_id'])
    ? (int) $_GET['user_id']
    : 0;

if ($user_id <= 0) {

    echo json_encode([

        "status" => false,

        "message" => "User ID tidak valid",

        "data" => []

    ]);

    exit;
}

$query = mysqli_query(

    $conn,

    "SELECT
        t.id,
        t.goal_id,
        t.user_id,
        t.amount,
        t.fee,
        t.payment_method,
        t.status,
        t.created_at,

        g.title AS goal_title

     FROM transactions t

     LEFT JOIN goals g
     ON t.goal_id = g.id

     WHERE t.user_id = $user_id

     ORDER BY t.created_at DESC"

);

if (!$query) {

    echo json_encode([

        "status"  => false,

        "message" => mysqli_error($conn),

        "data" => []

    ]);

    exit;
}

$data = [];

while ($row = mysqli_fetch_assoc($query)) {

    $data[] = $row;
}

echo json_encode([

    "status" => true,

    "total"  => count($data),

    "data"   => $data

]);
?>