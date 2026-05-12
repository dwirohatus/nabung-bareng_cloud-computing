<?php

session_start();

include '../../config/database.php';

header('Content-Type: application/json');

if (
    !isset($_POST['email']) ||
    !isset($_POST['password'])
) {

    echo json_encode([
        "status"  => false,
        "message" => "Email dan password wajib diisi"
    ]);

    exit;
}

$email    = mysqli_real_escape_string($conn, $_POST['email']);
$password = $_POST['password'];

$query = mysqli_query(
    $conn,
    "SELECT * FROM users WHERE email='$email' LIMIT 1"
);

if (mysqli_num_rows($query) == 0) {

    echo json_encode([
        "status"  => false,
        "message" => "Email tidak ditemukan"
    ]);

    exit;
}

$user = mysqli_fetch_assoc($query);

if (!password_verify($password, $user['password'])) {

    echo json_encode([
        "status"  => false,
        "message" => "Password salah"
    ]);

    exit;
}

$_SESSION['user'] = [
    "id"    => $user['id'],
    "name"  => $user['name'],
    "email" => $user['email']
];

$_SESSION['user_id'] = $user['id'];
$_SESSION['name']    = $user['name'];
$_SESSION['email']   = $user['email'];

echo json_encode([
    "status" => true,
    "message" => "Login berhasil",
    "data" => [
        "id"    => $user['id'],
        "name"  => $user['name'],
        "email" => $user['email']
    ]
]);
?>