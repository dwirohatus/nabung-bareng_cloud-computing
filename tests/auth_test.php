<?php

echo "=== AUTH TEST ===\n";

$data = [
    'email' => 'dwi@gmail.com',
    'password' => '123456'
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded",
        'method'  => 'POST',
        'content' => http_build_query($data)
    ]
];

$context  = stream_context_create($options);

$result = file_get_contents(
    'http://localhost:8000/api/auth/login.php',
    false,
    $context
);

echo "Response Login:\n";

echo $result;
?>