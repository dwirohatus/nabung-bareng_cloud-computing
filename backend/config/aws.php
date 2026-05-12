<?php

require __DIR__ . '/../vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Sns\SnsClient;

$config = [

    'version' => 'latest',

    'region'  => 'us-east-1',

    'endpoint' => 'http://localstack:4566',

    'credentials' => [
        'key'    => 'test',
        'secret' => 'test'
    ]

];

$s3Client = new S3Client(array_merge($config, [

    'use_path_style_endpoint' => true

]));

$snsClient = new SnsClient($config);

?>