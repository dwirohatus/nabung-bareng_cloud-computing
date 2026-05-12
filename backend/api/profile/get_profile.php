<?php

include '../../config/database.php';

header('Content-Type: application/json');

$userId = (int) ($_GET['user_id'] ?? 0);

if ($userId === 0) {
    echo json_encode([
        "status"  => false,
        "message" => "user_id wajib diisi"
    ]);
    exit;
}

$res  = mysqli_query($conn, "SELECT id, name, email, avatar, balance FROM users WHERE id = $userId");
$user = mysqli_fetch_assoc($res);

if (!$user) {
    echo json_encode([
        "status"  => false,
        "message" => "User tidak ditemukan"
    ]);
    exit;
}

// Konversi URL internal ke external agar bisa ditampilkan di browser
if (!empty($user['avatar'])) {
    $user['avatar'] = str_replace(
        'http://localstack:4566',
        'http://localhost:4566',
        $user['avatar']
    );
}

echo json_encode([
    "status" => true,
    "data"   => $user
]);
?>