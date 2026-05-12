<?php

header('Content-Type: application/json');

include '../../config/database.php';

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($user_id <= 0) {
    echo json_encode([
        "status"  => false,
        "message" => "user_id tidak valid",
        "data"    => []
    ]);
    exit;
}

$query = mysqli_query(
    $conn,
    "SELECT * FROM goals
     WHERE created_by = '$user_id'
     ORDER BY id DESC"
);

if (!$query) {
    echo json_encode([
        "status"  => false,
        "message" => mysqli_error($conn),
        "data"    => []
    ]);
    exit;
}

$data = [];

while ($row = mysqli_fetch_assoc($query)) {
    $data[] = $row;
}

echo json_encode([
    "status" => true,
    "data"   => $data
]);