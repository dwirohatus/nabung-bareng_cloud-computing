<?php

require '../../vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

header('Content-Type: application/json');

include '../../config/database.php';

if (!$conn) {
    echo json_encode(["status" => false, "message" => "Koneksi database gagal"]);
    exit;
}

if (!isset($_FILES['avatar'])) {
    echo json_encode(["status" => false, "message" => "File avatar tidak ditemukan"]);
    exit;
}

if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["status" => false, "message" => "Upload file gagal (kode: " . $_FILES['avatar']['error'] . ")"]);
    exit;
}

$file   = $_FILES['avatar'];
$userId = (int)($_POST['user_id'] ?? 0);

if ($userId <= 0) {
    echo json_encode(["status" => false, "message" => "User tidak valid"]);
    exit;
}

$tmpPath = $file['tmp_name'];

if (!file_exists($tmpPath)) {
    echo json_encode(["status" => false, "message" => "Temporary file tidak ditemukan"]);
    exit;
}

$fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'webp'];

if (!in_array($fileExt, $allowed)) {
    echo json_encode(["status" => false, "message" => "Format file harus JPG, PNG, atau WEBP"]);
    exit;
}

if ($file['size'] > 2 * 1024 * 1024) {
    echo json_encode(["status" => false, "message" => "Ukuran file maksimal 2MB"]);
    exit;
}

$fileName = 'avatars/user_' . $userId . '_' . time() . '.' . $fileExt;
$bucket   = 'nabung-bareng';

// LocalStack pakai nama service Docker
$localstackInternal = 'http://localstack:4566';

try {
    $s3 = new S3Client([
        'version'                 => 'latest',
        'region'                  => 'ap-southeast-1',
        'endpoint'                => $localstackInternal,
        'use_path_style_endpoint' => true,
        'credentials'             => [
            'key'    => 'test',
            'secret' => 'test'
        ]
    ]);

    // Buat bucket kalau belum ada
    try {
        $s3->headBucket(['Bucket' => $bucket]);
    } catch (AwsException $e) {
        $s3->createBucket(['Bucket' => $bucket]);
    }

    // Set bucket policy supaya public — browser bisa akses langsung
    $policy = json_encode([
        "Version" => "2012-10-17",
        "Statement" => [[
            "Sid"       => "PublicReadGetObject",
            "Effect"    => "Allow",
            "Principal" => "*",
            "Action"    => "s3:GetObject",
            "Resource"  => "arn:aws:s3:::$bucket/*"
        ]]
    ]);

    $s3->putBucketPolicy([
        'Bucket' => $bucket,
        'Policy' => $policy
    ]);

    // Upload file
    $s3->putObject([
        'Bucket'      => $bucket,
        'Key'         => $fileName,
        'SourceFile'  => $tmpPath,
        'ContentType' => mime_content_type($tmpPath),
    ]);

    // Simpan URL internal ke DB (pakai nama service Docker)
    $avatarUrlInternal = "http://localstack:4566/$bucket/$fileName";

    // URL untuk browser (localhost)
    $avatarUrlExternal = "http://localhost:4566/$bucket/$fileName";

    $safeUrl = mysqli_real_escape_string($conn, $avatarUrlInternal);

    $update = mysqli_query($conn,
        "UPDATE users SET avatar='$safeUrl' WHERE id='$userId'"
    );

    if (!$update) {
        echo json_encode(["status" => false, "message" => "Gagal update database: " . mysqli_error($conn)]);
        exit;
    }

    echo json_encode([
        "status"     => true,
        "message"    => "Avatar berhasil diupload",
        "avatar_url" => $avatarUrlExternal
    ]);

} catch (AwsException $e) {
    echo json_encode(["status" => false, "message" => "Gagal upload ke S3: " . $e->getMessage()]);
}
?>