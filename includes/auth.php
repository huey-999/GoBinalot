<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function redirectIfAuthenticated(string $to = 'gb/dashboard.php'): void
{
    if (isset($_SESSION['user_data']['id'])) {
        header('Location: ' . $to);
        exit;
    }
}

function requireLogin(string $to = 'login.php'): void
{
    if (!isset($_SESSION['user_data']['id'])) {
        header('Location: ' . $to);
        exit;
    }
}