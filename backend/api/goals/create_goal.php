<?php
header('Content-Type: application/json');

// Include database connection
include '../../config/database.php';

// Cek koneksi database
if (!$conn) {
    echo json_encode([
        "status" => false,
        "message" => "Koneksi database gagal"
    ]);
    exit;
}

// Ambil data dari POST
$user_id       = intval($_POST['user_id'] ?? 0);
$title         = trim($_POST['title'] ?? '');
$description   = trim($_POST['description'] ?? '');
$target_amount = floatval($_POST['target_amount'] ?? 0);
$deadline      = trim($_POST['deadline'] ?? '');

// Validasi input
if ($user_id === 0 || $title === '' || $deadline === '' || $target_amount <= 0) {
    echo json_encode([
        "status"  => false,
        "message" => "Data tidak lengkap. User ID, Title, Target Amount, dan Deadline wajib diisi."
    ]);
    exit;
}

try {
    $sql = "INSERT INTO goals 
            (user_id, title, description, target_amount, current_amount, deadline, created_at) 
            VALUES (?, ?, ?, ?, 0, ?, NOW())";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Prepare gagal: " . $conn->error);
    }

    $stmt->bind_param(
        "issds",
        $user_id,
        $title,
        $description,
        $target_amount,
        $deadline
    );

    if ($stmt->execute()) {
        echo json_encode([
            "status"  => true,
            "message" => "Goals berhasil dibuat",
            "goal_id" => $conn->insert_id
        ]);
    } else {
        echo json_encode([
            "status"  => false,
            "message" => "Gagal menyimpan data: " . $stmt->error
        ]);
    }

    $stmt->close();

} catch (Exception $e) {
    echo json_encode([
        "status"  => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}

$conn->close();
?>