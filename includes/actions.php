<?php

include 'config.php';
include 'auth.php';

// Register Customer
if (isset($_POST['form_type']) && $_POST['form_type'] === 'register') {

    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['regEmail'] ?? '');
    $password = $_POST['regPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $agreeTerms = isset($_POST['agreeTerms']) ? 'Yes' : 'No';

    try {

        $query = "INSERT INTO users (first_name, last_name, email, password, role, created_at) VALUES (:first_name, :last_name, :email, :password, :role, NOW())";
        $statement = $conn->prepare($query);
        $data = [
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':email' => $email,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':role' => 'customer',
        ];

        $result = $statement->execute($data);

        if ($result) {
            echo "Registration successful!";
            header("Location: ../login.php");
        } else {
            echo "Registration failed. Please try again.";
            header("Location: ../register.php");
        }

    } catch (\PDOException $e) {
        $e->getMessage();
    }
}


// Login Customer
if (isset($_POST['form_type']) && $_POST['form_type'] === 'login') {

    $email = trim($_POST['loginEmail'] ?? '');
    $password = $_POST['loginPassword'] ?? '';

    try {

        $query = "SELECT * FROM users WHERE email = :email";
        $statement = $conn->prepare($query);
        $data = [':email' => $email];

        $statement->execute($data);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result && password_verify($password, $result['password'])) {
            
            $_SESSION['user_data'] = [
                'id' => $result['id'],
                'name' => $result['first_name'] . ' '  . $result['last_name'],
                'email' => $result['email'],
                'phone' => $result['phone'],
                'role' => $result['role'],
            ];

            if ($_SESSION['user_data']['role'] == 'admin') {
                header("Location: ../gb/dashboard.php");
            } else {
                header("Location: ../gb/my_orders.php");
            }

        } else {
            echo "Login failed. Please try again.";
            header("Location: ../login.php");
        }

    } catch (\PDOException $e) {
        $e->getMessage();
    }
}

// Add Category

if (isset($_POST['form_type']) && $_POST['form_type'] === 'add_category') {

    try {
        //code...
    } catch (\PDOException $e) {
        $e->getMessage();
    }
}
?>