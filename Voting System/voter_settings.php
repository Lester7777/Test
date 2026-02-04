<?php
session_start();
require_once 'config.php';

// Check if voter is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'voter') {
    header("Location: index.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        // Handle profile update
        $user_id = $_SESSION['user_id'];  // Changed from id to user_id
        $new_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $new_password = $_POST['password'];
        $new_username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
        $new_student_id = filter_var($_POST['student_id'], FILTER_SANITIZE_STRING);
        
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET email = ?, password = ?, username = ?, id_number = ? WHERE id = ?");
            $stmt->execute([$new_email, $hashed_password, $new_username, $new_student_id, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET email = ?, username = ?, id_number = ? WHERE id = ?");
            $stmt->execute([$new_email, $new_username, $new_student_id, $user_id]);
        }
        $_SESSION['message'] = "Profile updated successfully!";
        header("Location: voter_settings.php");
        exit();
    }
}

// Get current voter information
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]); // Changed to use user_id
    $voter_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$voter_info) {
        $_SESSION['error'] = "Could not find voter information.";
        header("Location: index.php");
        exit();
    }
} catch(PDOException $e) {
    $_SESSION['error'] = "Error retrieving voter information.";
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Settings - E-Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --background-color: #f5f6fa;
            --card-background: #ffffff;
            --text-color: #2c3e50;
            --border-color: #e1e8ed;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            min-height: 100vh;
        }

        .main-content {
            padding: 2rem;
            margin-left: 0;
            background-color: var(--background-color);
            min-height: 100vh;
            position: relative;
        }

        .header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .header h1 {
            color: var(--text-color);
            font-size: 2rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .settings-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .settings-card {
            background-color: var(--card-background);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 2rem;
        }

        .settings-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .settings-card h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            font-size: 1.5rem;
            gap: 0.75rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            background-color: #fff;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .submit-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
            font-weight: 500;
            width: 100%;
        }

        .submit-btn:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .return-btn {
            background-color: #95a5a6;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 2rem;
        }

        .return-btn:hover {
            background-color: #7f8c8d;
            transform: translateY(-2px);
        }

        .return-btn i {
            margin-right: 0.5rem;
        }

        .message {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            background-color: #2ecc71;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }

            .settings-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <a href="user_dashboard.php" class="return-btn">
            <i class="fas fa-arrow-left"></i> Return to Dashboard
        </a>
        <div class="header">
            <h1><i class="fas fa-user-cog"></i> Voter Profile Settings</h1>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="message">
                <i class="fas fa-check-circle"></i>
                <?php 
                    echo $_SESSION['message'];
                    unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <div class="settings-container">
            <div class="settings-card">
                <h2><i class="fas fa-user"></i> Profile Information</h2>
                <form method="POST" action="voter_settings.php">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="form-group">
                        <label>Student ID</label>
                        <input type="text" name="student_id" value="<?php echo isset($voter_info['student_id']) ? htmlspecialchars($voter_info['student_id']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?php echo isset($voter_info['username']) ? htmlspecialchars($voter_info['username']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" value="<?php echo isset($voter_info['email']) ? htmlspecialchars($voter_info['email']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>New Password (leave blank to keep current)</label>
                        <input type="password" name="password">
                    </div>
                    <button type="submit" class="submit-btn">Update Profile</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>