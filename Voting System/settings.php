<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                // Handle profile update
                $new_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                $new_password = $_POST['password'];
                
                if (!empty($new_password)) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET email = ?, password = ? WHERE id = ?");
                    $stmt->execute([$new_email, $hashed_password, $_SESSION['user_id']]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                    $stmt->execute([$new_email, $_SESSION['user_id']]);
                }
                $_SESSION['message'] = "Profile updated successfully!";
                break;

            case 'update_election':
                // Handle election settings update
                $election_title = filter_var($_POST['election_title'], FILTER_SANITIZE_STRING);
                $start_date = $_POST['start_date'];
                $end_date = $_POST['end_date'];
                
                // Validate dates
                $start_timestamp = strtotime($start_date);
                $end_timestamp = strtotime($end_date);
                
                if ($end_timestamp <= $start_timestamp) {
                    $_SESSION['error'] = "End date must be after the start date!";
                } else {
                    $stmt = $pdo->prepare("UPDATE election_settings SET title = ?, start_date = ?, end_date = ? WHERE id = 1");
                    $stmt->execute([$election_title, $start_date, $end_date]);
                    $_SESSION['message'] = "Election settings updated successfully!";
                }
                break;
        }
        header("Location: settings.php");
        exit();
    }
}

// Get current settings
try {
    $stmt = $pdo->prepare("SELECT * FROM election_settings WHERE id = 1");
    $stmt->execute();
    $election_settings = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $election_settings = [
        'title' => 'Student Council Election',
        'start_date' => date('Y-m-d'),
        'end_date' => date('Y-m-d', strtotime('+1 week'))
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - E-Voting System</title>
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
            display: flex;
            justify-content: center;  /* Added */
        }

        .main-content {
            padding: 2rem;
            background-color: var(--background-color);
            min-height: 100vh;
            position: relative;
            max-width: 1200px;  /* Added */
            width: 100%;  /* Added */
            margin: 0 auto;  /* Updated */
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
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin: 2rem 0;  /* Updated margin */
        }

        .settings-card {
            background-color: var(--card-background);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 1rem;  /* Added margin-bottom */
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
            background-color:rgb(34, 75, 102);
            transform: translateY(-2px);
        }

        .return-btn {
            background-color:rgb(26, 59, 79);
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
            margin: 0 0 2rem 0;  /* Updated margin */
        }

        .return-btn:hover {
            background-color:#2980b9;
            transform: translateY(-2px);
        }

        .return-btn i {
            margin-right: 0.5rem;
        }

        .message {
            padding: 1rem;
            margin: 1rem 0 2rem 0;  /* Updated margin */
            border-radius: 8px;
            background-color: #2ecc71;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: slideIn 0.3s ease;
        }

        .error-message {
            padding: 1rem;
            margin: 1rem 0 2rem 0;  /* Updated margin */
            border-radius: 8px;
            background-color: #e74c3c;
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

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
                max-width: 100%;
            }

            .settings-container {
                grid-template-columns: 1fr;
            }

            .settings-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <a href="dashboard.php" class="return-btn">
            <i class="fas fa-arrow-left"></i> Return to Dashboard
        </a>
        <div class="header">
            <h1><i class="fas fa-cog"></i> System Settings</h1>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="message">
                <?php 
                    echo $_SESSION['message'];
                    unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <div class="settings-container">
            <div class="settings-card">
                <h2><i class="fas fa-user-cog"></i> Profile Settings</h2>
                <form method="POST" action="settings.php">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" required autocomplete="email">
                    </div>
                    <div class="form-group">
                        <label for="password">New Password (leave blank to keep current)</label>
                        <input type="password" id="password" name="password" autocomplete="new-password">
                    </div>
                    <button type="submit" class="submit-btn">Update Profile</button>
                </form>
            </div>

            <div class="settings-card">
                <h2><i class="fas fa-vote-yea"></i> Election Settings</h2>
                <form method="POST" action="settings.php">
                    <input type="hidden" name="action" value="update_election">
                    <div class="form-group">
                        <label for="election_title">Election Title</label>
                        <input type="text" id="election_title" name="election_title" value="<?php echo htmlspecialchars($election_settings['title']); ?>" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo $election_settings['start_date']; ?>" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo $election_settings['end_date']; ?>" required autocomplete="off">
                    </div>
                    <button type="submit" class="submit-btn">Update Election Settings</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');
        
        // Add client-side validation
        document.querySelector('form[action="settings.php"]').addEventListener('submit', function(e) {
            if (new Date(endDate.value) <= new Date(startDate.value)) {
                e.preventDefault();
                alert('End date must be after the start date!');
            }
        });
    });
    </script>
</body>
</html>