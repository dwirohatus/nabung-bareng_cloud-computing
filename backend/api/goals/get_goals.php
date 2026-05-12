<?php

header('Content-Type: application/json');

include '../../config/database.php';

$user_id = intval($_GET['user_id'] ?? 0);

if ($user_id <= 0) {

    echo json_encode([
        "status" => false,
        "message" => "User tidak valid"
    ]);

    exit;
}

$query = mysqli_query(

    $conn,

    "SELECT
        id,
        title,
        description,
        target_amount,
        current_amount,
        deadline,
        status,
        created_at

     FROM goals

     WHERE created_by = '$user_id'

     ORDER BY created_at DESC"
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

?>