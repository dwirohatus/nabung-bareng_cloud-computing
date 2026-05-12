<?php

header('Content-Type: application/json');

include '../../config/database.php';

if (!$conn) {

    echo json_encode([
        "status" => false,
        "message" => "Koneksi database gagal"
    ]);

    exit;
}

$user_id       = intval($_POST['user_id'] ?? 0);

$title         = trim($_POST['title'] ?? '');

$description   = trim($_POST['description'] ?? '');

$target_amount = floatval($_POST['target_amount'] ?? 0);

$deadline      = trim($_POST['deadline'] ?? '');

if (
    $user_id <= 0 ||
    empty($title) ||
    empty($deadline) ||
    $target_amount <= 0
) {

    echo json_encode([
        "status"  => false,
        "message" => "Data goals tidak lengkap"
    ]);

    exit;
}

$sql = "INSERT INTO goals
(
    title,
    description,
    target_amount,
    current_amount,
    deadline,
    created_by,
    status,
    created_at
)

VALUES
(
    ?, ?, ?, 0, ?, ?, 'active', NOW()
)";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    echo json_encode([
        "status"  => false,
        "message" => mysqli_error($conn)
    ]);

    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "ssisi",
    $title,
    $description,
    $target_amount,
    $deadline,
    $user_id
);

$execute = mysqli_stmt_execute($stmt);

if ($execute) {

    echo json_encode([

        "status"  => true,

        "message" => "Goals berhasil dibuat",

        "goal_id" => mysqli_insert_id($conn)

    ]);

} else {

    echo json_encode([

        "status"  => false,

        "message" => mysqli_stmt_error($stmt)

    ]);
}

mysqli_stmt_close($stmt);

mysqli_close($conn);

?>