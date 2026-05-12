<?php

$host     = "mysql";
$user     = "root";
$password = "root";
$database = "nabung_bareng";

mysqli_report(MYSQLI_REPORT_OFF); // matikan exception, tangani manual

$conn = @mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    header('Content-Type: application/json');
    http_response_code(503);
    echo json_encode([
        "status"  => false,
        "message" => "Database tidak dapat dihubungi. Pastikan MySQL sudah berjalan."
    ]);
    exit;
}
?>