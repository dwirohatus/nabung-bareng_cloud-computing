<?php

include '../../config/database.php';

header('Content-Type: application/json');

if (
    !isset($_POST['name']) ||
    !isset($_POST['email']) ||
    !isset($_POST['password'])
) {

    echo json_encode([
        "status" => false,
        "message" => "Semua field wajib diisi"
    ]);

    exit;
}

$name     = mysqli_real_escape_string($conn, $_POST['name']);
$email    = mysqli_real_escape_string($conn, $_POST['email']);
$password = password_hash($_POST['password'], PASSWORD_BCRYPT);

/* =========================
   CHECK EMAIL
========================= */

$cek = mysqli_query(
    $conn,
    "SELECT id FROM users WHERE email='$email'"
);

if (mysqli_num_rows($cek) > 0) {

    echo json_encode([
        "status" => false,
        "message" => "Email sudah terdaftar"
    ]);

    exit;
}

/* =========================
   INSERT USER
========================= */

$query = mysqli_query(
    $conn,
    "INSERT INTO users (name, email, password)
     VALUES ('$name','$email','$password')"
);

if ($query) {

    echo json_encode([
        "status" => true,
        "message" => "Registrasi berhasil"
    ]);

} else {

    echo json_encode([
        "status" => false,
        "message" => mysqli_error($conn)
    ]);
}