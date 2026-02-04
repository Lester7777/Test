<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Check if user has already voted
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM votes v INNER JOIN users u ON v.user_id = u.id WHERE u.id = ?");
    $stmt->execute([$user_id]);
    $has_voted = $stmt->fetchColumn() > 0;
    
    if ($has_voted) {
        $_SESSION['error'] = "You have already cast your vote.";
        header("Location: voting.php");
        exit();
    }
} catch(PDOException $e) {
    $_SESSION['error'] = "Error checking vote status: " . $e->getMessage();
    header("Location: voting.php");
    exit();
}

// Process the votes
try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Get all positions
    $stmt = $pdo->query("SELECT DISTINCT position FROM candidates c");
    $positions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Insert votes for each position
    foreach ($positions as $position) {
        if (isset($_POST['vote'][$position])) {
            $candidate_id = $_POST['vote'][$position];
            
            $stmt = $pdo->prepare("INSERT INTO votes (user_id, candidate_id, position, timestamp) 
                                 VALUES (?, ?, ?, NOW())");
            $stmt->execute([$user_id, $candidate_id, $position]);
        }
    }
    
    // Update user's has_voted status
    $stmt = $pdo->prepare("UPDATE users SET has_voted = 1 WHERE id = ?");
    $stmt->execute([$user_id]);
    
    // Commit transaction
    $pdo->commit();
    
    $_SESSION['success'] = "Your vote has been successfully recorded!";
    
    // Redirect based on user role
    if ($_SESSION['role'] === 'admin') {
        header("Location: dashboard.php");
    } else {
        header("Location: user_dashboard.php");
    }
    exit();
    
} catch(PDOException $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    $_SESSION['error'] = "Error processing vote: " . $e->getMessage();
    header("Location: voting.php");
    exit();
}
?>