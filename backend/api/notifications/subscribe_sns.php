<?php

require __DIR__ . '/../../vendor/autoload.php';
include __DIR__ . '/../../helpers/sns_helper.php';

use Aws\Exception\AwsException;

header('Content-Type: application/json');

if (!isset($_POST['email'])) {
    echo json_encode([
        "status"  => false,
        "message" => "Email wajib diisi"
    ]);
    exit;
}

$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
if (!$email) {
    echo json_encode([
        "status"  => false,
        "message" => "Format email tidak valid"
    ]);
    exit;
}

try {
    $sns           = getSnsClient();
    $topicSetor    = getOrCreateTopic($sns, 'nabung-bareng-setor');
    $topicGoal     = getOrCreateTopic($sns, 'nabung-bareng-goal-tercapai');

    $sns->subscribe([
        'TopicArn' => $topicSetor,
        'Protocol' => 'email',
        'Endpoint' => $email
    ]);

    $sns->subscribe([
        'TopicArn' => $topicGoal,
        'Protocol' => 'email',
        'Endpoint' => $email
    ]);

    echo json_encode([
        "status"  => true,
        "message" => "Email $email berhasil didaftarkan untuk notifikasi"
    ]);

} catch (AwsException $e) {
    echo json_encode([
        "status"  => false,
        "message" => "Gagal subscribe: " . $e->getMessage()
    ]);
}
?>