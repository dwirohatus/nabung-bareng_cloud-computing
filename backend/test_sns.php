<?php

include 'config/aws.php';

try {

    $result = $snsClient->publish([

        'TopicArn' =>
        'arn:aws:sns:us-east-1:000000000000:nabung-bareng-topic',

        'Message' => 'SNS berhasil dari LocalStack',

        'Subject' => 'Nabung Bareng'

    ]);

    echo "
    <h1>SNS Berhasil</h1>

    <p>Message berhasil dikirim.</p>

    <b>Message ID:</b><br>
    ".$result['MessageId'];

} catch (Exception $e) {

    echo $e->getMessage();

}
?>