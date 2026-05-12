<?php

header('Content-Type: application/json');

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        "status"  => false,
        "message" => "File tidak ditemukan"
    ]);
    exit;
}

$userId  = (int) ($_POST['user_id'] ?? 0);
$tmpPath = $_FILES['avatar']['tmp_name'];
$name    = $_FILES['avatar']['name'];

// 'backend' = nama service di docker-compose.yml = hostname di jaringan nabung_network
$ch = curl_init('http://backend/api/profile/upload_avatar.php');

$postData = [
    'avatar'  => new CURLFile($tmpPath, mime_content_type($tmpPath), $name),
    'user_id' => $userId
];

curl_setopt($ch, CURLOPT_POST,           true);
curl_setopt($ch, CURLOPT_POSTFIELDS,     $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT,        30);

$result   = curl_exec($ch);
$error    = curl_error($ch);
curl_close($ch);

if ($result === false) {
    echo json_encode([
        "status"  => false,
        "message" => "Gagal konek ke backend: " . $error
    ]);
    exit;
}

$response = json_decode($result, true);

if (isset($response['status']) && $response['status'] == true) {
    header("Location: profile.php");
    exit;
}

echo $result;
?>