<?php

$server = "localhost";
$username = "root";
$password = "";
$database = "gobinalot";

try {
    $conn = new PDO("mysql:host=$server;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Database Connection Failed!" . $e->getMessage();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}