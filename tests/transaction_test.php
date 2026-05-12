<?php

echo "=== TRANSACTION TEST ===\n";

$data = [
    'goal_id' => 1,
    'amount' => 200000
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded",
        'method'  => 'POST',
        'content' => http_build_query($data)
    ]
];

$context = stream_context_create($options);

$result = file_get_contents(
    'http://localhost:8000/api/transactions/deposit.php',
    false,
    $context
);

echo "Response Deposit:\n";

echo $result;
?>