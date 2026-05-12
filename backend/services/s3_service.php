<?php

include '../config/aws.php';

function uploadToS3($filePath, $fileName)
{
    global $s3Client;

    $result = $s3Client->putObject([
        'Bucket' => 'bukti-transfer',
        'Key'    => $fileName,
        'SourceFile' => $filePath
    ]);

    return $result['ObjectURL'];
}
?>