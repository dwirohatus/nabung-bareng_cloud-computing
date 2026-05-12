<?php

function validateRequired($fields)
{
    foreach ($fields as $field) {

        if (empty($_POST[$field])) {

            echo json_encode([
                "status" => false,
                "message" => "$field wajib diisi"
            ]);

            exit;
        }
    }
}
?>