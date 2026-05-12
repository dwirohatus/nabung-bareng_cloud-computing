<?php

$host = "mysql";
$user = "root";
$password = "root";
$database = "nabung_bareng";

$conn = mysqli_connect(
    $host,
    $user,
    $password,
    $database
);

if (!$conn) {
    die("Koneksi database gagal");
}
?>