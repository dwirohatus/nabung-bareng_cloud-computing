<?php

echo "=== API TEST ===\n";

$apiUrl = "http://localhost:8000/api/goals/get_goals.php";

$response = file_get_contents($apiUrl);

if ($response) {

    echo "API berhasil diakses\n";

    echo "Response:\n";

    echo $response;

} else {

    echo "API gagal diakses\n";
}
?>