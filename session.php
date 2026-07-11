<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function isAdmin() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

function isClient() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'client');
}

function redirectIfNotLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}
?>
