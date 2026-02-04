<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize input data
    $id_number = htmlspecialchars(trim($_POST['id_number'] ?? ''), ENT_QUOTES, 'UTF-8');
    $username = htmlspecialchars(trim($_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Server-side validation 
    $errors = [];

    // Validate ID Number
    if (!preg_match('/^[A-Z0-9]+$/', $id_number)) {
        $errors[] = 'invalid_id';
    }

    // Validate Email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'invalid_email';
    }

    // Validate Password
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
        $errors[] = 'invalid_password';
    }

    // If there are validation errors, redirect back with error message
    if (!empty($errors)) {
        header("Location: register.php?error=" . $errors[0]);
        exit();
    }

    try {
        // Check if ID number already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id_number = ?");
        $stmt->execute([$id_number]);
        
        if ($stmt->rowCount() > 0) {
            header("Location: register.php?error=id_exists");
            exit();
        }

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            header("Location: register.php?error=email_exists");
            exit();
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (id_number, username, email, password, role, has_voted) VALUES (?, ?, ?, ?, 'voter', 0)");
        $stmt->execute([$id_number, $username, $email, $hashed_password]);

        // Registration successful
        header("Location: register.php?success=1");
        exit();

    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        header("Location: register.php?error=registration_failed");
        exit();
    }
} else {
    // If not POST request, redirect to registration page
    header("Location: register.php");
    exit();
}
?>