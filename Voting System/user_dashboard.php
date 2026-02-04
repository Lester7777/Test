<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'voter') {
    die("Access denied.");
}
// Admin-only content here

require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$id_number = $_SESSION['id_number'];
$email = $_SESSION['email'];
$last_login = $_SESSION['last_login'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - E-Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f1c40f;
            --text-color: #2c3e50;
            --background-color: #f5f6fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--background-color);
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, var(--primary-color) 0%, #1a252f 100%);
            color: white;
            padding: 20px;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            transition: all 0.3s ease;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .sidebar-header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-header img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 15px;
            padding: 5px;
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-header h2 {
            font-size: 1.4em;
            font-weight: 600;
            margin: 10px 0;
            color: white;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }

        .nav-menu {
            margin-top: 20px;
        }

        .nav-item {
            padding: 12px 15px;
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-item:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: var(--secondary-color);
            transform: scaleY(0);
            transition: transform 0.2s;
        }

        .nav-item:hover:before,
        .nav-item.active:before {
            transform: scaleY(1);
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .nav-item.active {
            background: rgba(52, 152, 219, 0.2);
            color: white;
            font-weight: 600;
        }

        .nav-item i {
            margin-right: 12px;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1em;
            transition: all 0.3s ease;
        }

        .nav-item:hover i {
            transform: scale(1.1);
            color: var(--secondary-color);
        }

        .nav-item span {
            font-size: 0.95em;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }

        .header {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .welcome-section {
            text-align: center;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .quick-actions {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .action-button {
            padding: 15px;
            border: none;
            border-radius: 8px;
            background-color: var(--secondary-color);
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .profile-section {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .profile-info {
            margin-top: 15px;
        }

        .profile-info p {
            margin: 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-menu {
            margin-top: 30px;
        }

        .nav-item {
            padding: 15px;
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }

        .nav-item:hover {
            background-color: var(--secondary-color);
            transform: translateX(5px);
        }

        .nav-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                padding: 15px 10px;
            }

            .sidebar-header {
                padding: 10px 0;
            }

            .sidebar-header img {
                width: 40px;
                height: 40px;
                margin-bottom: 10px;
            }

            .sidebar-header h2,
            .nav-item span {
                display: none;
            }

            .nav-item {
                padding: 12px;
                justify-content: center;
            }

            .nav-item i {
                margin-right: 0;
                font-size: 1.2em;
            }

            .main-content {
                margin-left: 70px;
                width: calc(100% - 70px);
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <img src="images/logo.png?v=<?php echo hash('sha256', filemtime('images/logo.png')); ?>" alt="Logo">
                <h2>E-Voting System</h2>
            </div>
        </div>
        <div class="nav-menu">
            <a href="user_dashboard.php" class="nav-item active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="candidates.php" class="nav-item">
                <i class="fas fa-user-tie"></i>
                <span>Candidates</span>
            </a>
            <a href="results.php" class="nav-item">
                <i class="fas fa-chart-bar"></i>
                <span>Results</span>
            </a>
            <a href="voter_settings.php" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
            <a href="logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <div class="user-info">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($username); ?>" alt="Profile">
                <div>
                    <h3><?php echo htmlspecialchars($username); ?></h3>
                    <small>ID: <?php echo htmlspecialchars($id_number); ?></small>
                </div>
            </div>
            <div id="clock"></div>
        </div>

        <div class="welcome-section">
            <h2>Welcome back, <?php echo htmlspecialchars($username); ?>!</h2>
            <p>Last login: <?php echo $last_login; ?></p>
        </div>

        <div class="quick-actions">
            <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
            <div class="action-buttons">
                <a href="voting.php" class="action-button">
                    <i class="fas fa-vote-yea"></i> Vote Now
                </a>
                <a href="voter_settings.php" class="action-button">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </div>
        </div>

        <div class="profile-section">
            <h2><i class="fas fa-user"></i> Your Profile</h2>
            <div class="profile-info">
                <p><i class="fas fa-id-card"></i> ID Number: <?php echo htmlspecialchars($id_number); ?></p>
                <p><i class="fas fa-envelope"></i> Email: <?php echo htmlspecialchars($email); ?></p>
                <p><i class="fas fa-clock"></i> Member Since: <?php echo date('F d, Y', strtotime($last_login)); ?></p>
            </div>
        </div>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            document.getElementById('clock').textContent = timeString;
        }

        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>