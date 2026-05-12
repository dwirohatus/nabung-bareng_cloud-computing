<?php

function cleanInput($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

function hashPassword($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}
?>