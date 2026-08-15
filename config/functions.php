<?php

function clean($data)
{
    return htmlspecialchars(trim($data));
}

function redirect($page)
{
    header("Location: ".$page);
    exit();
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

?>