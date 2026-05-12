<?php

include '../config/aws.php';

function sendNotification($message)
{
    global $snsClient;

    $snsClient->publish([
        'TopicArn' => 'arn:aws:sns:us-east-1:000000000000:reminder-topic',
        'Message'  => $message
    ]);
}
?>