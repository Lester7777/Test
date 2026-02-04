<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session with secure settings
ini_set('session.cookie_httponly', 1);
// ini_set('session.cookie_secure', 1);  // Already commented out for local development
ini_set('session.use_only_cookies', 1);
session_start();
require_once 'config.php';

// Add debug output
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_number = $_POST['id_number'];
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id_number = ?");
        $stmt->execute([$id_number]);
        $user = $stmt->fetch();
        
        // Debug output
        if (!$user) {
            die("Debug: User not found with ID number: " . htmlspecialchars($id_number));
        }
        
        if (!password_verify($password, $user['password'])) {
            die("Debug: Password verification failed");
        }
        
        if ($user && password_verify($password, $user['password'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['id_number'] = $user['id_number'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_login'] = date('Y-m-d H:i:s');
            $_SESSION['logged_in'] = true;
            
            // Add IP address and user agent for session binding
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            
            // Redirect based on user role
            if ($user['role'] === 'admin') {
                header("Location: dashboard.php");
            } else if ($user['role'] === 'voter') {
                header("Location: user_dashboard.php");
            }
            exit();
        } else {
            header("Location: index.php?error=invalid_credentials");
            exit();
        }
    } catch(PDOException $e) {
        die("Debug: Database error: " . $e->getMessage());
    }
}
?>