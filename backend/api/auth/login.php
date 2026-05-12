<?php

session_start();

header('Content-Type: application/json');

include '../../config/database.php';

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

$email    = trim($_POST['email']);
$password = $_POST['password'];

$email = mysqli_real_escape_string(
    $conn,
    $email
);

$query = mysqli_query(
    $conn,
    "SELECT * FROM users
     WHERE email='$email'
     LIMIT 1"
);

if (!$query) {

    echo json_encode([
        "status"  => false,
        "message" => mysqli_error($conn)
    ]);

    exit;
}

if (mysqli_num_rows($query) == 0) {

    echo json_encode([
        "status"  => false,
        "message" => "Email tidak ditemukan"
    ]);

    exit;
}

$user = mysqli_fetch_assoc($query);

if (
    !isset($user['password']) ||
    empty($user['password'])
) {

    echo json_encode([
        "status"  => false,
        "message" => "Password user tidak valid"
    ]);

    exit;
}

if (
    !password_verify(
        $password,
        $user['password']
    )
) {

    echo json_encode([
        "status"  => false,
        "message" => "Password salah"
    ]);

    exit;
}

$_SESSION['user'] = [

    "id"     => $user['id'],

    "name"   => $user['name'],

    "email"  => $user['email'],

    "avatar" => $user['avatar'] ?? ''

];

$_SESSION['user_id'] = $user['id'];

$_SESSION['nama'] = $user['name'];

$_SESSION['email'] = $user['email'];

$_SESSION['avatar'] = $user['avatar'] ?? '';

unset($user['password']);

echo json_encode([

    "status"  => true,

    "message" => "Login berhasil",

    "data" => [

        "id"     => $user['id'],

        "name"   => $user['name'],

        "email"  => $user['email'],

        "avatar" => $user['avatar'] ?? ''

    ]
]);
?>