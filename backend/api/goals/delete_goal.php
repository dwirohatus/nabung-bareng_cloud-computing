<?php

include '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_POST['id'])) {
    echo json_encode([
        "status"  => false,
        "message" => "ID goals tidak ditemukan"
    ]);
    exit;
}

$id = (int) $_POST['id']; // cast ke integer untuk keamanan

$query = mysqli_query(
    $conn,
    "DELETE FROM goals WHERE id = $id"
);

if ($query) {
    echo json_encode([
        "status"  => true,
        "message" => "Goals berhasil dihapus"
    ]);
} else {
    echo json_encode([
        "status"  => false,
        "message" => "Gagal menghapus goals: " . mysqli_error($conn)
    ]);
}
?>