<?php
$host = "localhost";  // Changed back to localhost
$dbname = "user_db";
$username = "root";
$password = "";

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    // More detailed error handling
    $error_message = $e->getMessage();
    if (strpos($error_message, "No connection could be made") !== false) {
        die("Database connection failed: Please make sure MySQL is running in XAMPP Control Panel");
    } else {
        die("Connection failed: " . $error_message);
    }
}
?>