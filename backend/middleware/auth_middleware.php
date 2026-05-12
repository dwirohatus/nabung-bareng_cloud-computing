<?php

session_start();

if (!isset($_SESSION['user'])) {

    http_response_code(401);

    echo json_encode([
        "status" => false,
        "message" => "Unauthorized"
    ]);

    exit;
}
?>