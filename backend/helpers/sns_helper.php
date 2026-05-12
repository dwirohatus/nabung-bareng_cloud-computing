<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;

function getSnsClient() {
    return new SnsClient([
        'version'                 => 'latest',
        'region'                  => 'us-east-1',
        'endpoint'                => 'http://localstack:4566',
        'use_path_style_endpoint' => true,
        'credentials'             => [
            'key'    => 'test',
            'secret' => 'test'
        ]
    ]);
}

function getOrCreateTopic($sns, $topicName) {
    $topics = $sns->listTopics();
    foreach ($topics['Topics'] as $topic) {
        if (strpos($topic['TopicArn'], $topicName) !== false) {
            return $topic['TopicArn'];
        }
    }
    $result = $sns->createTopic(['Name' => $topicName]);
    return $result['TopicArn'];
}

function kirimNotifikasiSetor($goalTitle, $amount, $currentAmount, $targetAmount) {
    try {
        $sns      = getSnsClient();
        $topicArn = getOrCreateTopic($sns, 'nabung-bareng-setor');
        $persen   = round(($currentAmount / $targetAmount) * 100);
        $message  = "Setoran Berhasil!\n\nGoal: $goalTitle\nJumlah Setor: Rp" . number_format($amount, 0, ',', '.') . "\nTotal Terkumpul: Rp" . number_format($currentAmount, 0, ',', '.') . "\nTarget: Rp" . number_format($targetAmount, 0, ',', '.') . "\nProgress: $persen%\n\nTerus semangat menabung!";
        $sns->publish([
            'TopicArn' => $topicArn,
            'Message'  => $message,
            'Subject'  => "Setoran Nabung Bareng - $goalTitle"
        ]);
        return true;
    } catch (AwsException $e) {
        error_log("SNS Error: " . $e->getMessage());
        return false;
    }
}

function kirimNotifikasiGoalTercapai($goalTitle, $targetAmount) {
    try {
        $sns      = getSnsClient();
        $topicArn = getOrCreateTopic($sns, 'nabung-bareng-goal-tercapai');
        $message  = "Selamat! Goal Tercapai!\n\nGoal \"$goalTitle\" telah berhasil dicapai!\nTotal: Rp" . number_format($targetAmount, 0, ',', '.') . "\n\nKerja keras kalian membuahkan hasil!\nYuk buat goal baru di Nabung Bareng!";
        $sns->publish([
            'TopicArn' => $topicArn,
            'Message'  => $message,
            'Subject'  => "Goal Tercapai! - $goalTitle"
        ]);
        return true;
    } catch (AwsException $e) {
        error_log("SNS Error: " . $e->getMessage());
        return false;
    }
}
?>
