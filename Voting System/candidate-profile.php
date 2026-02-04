<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Check if candidate ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: candidates.php");
    exit();
}

$candidate_id = $_GET['id'];

// Fetch candidate details
try {
    $stmt = $pdo->prepare("SELECT * FROM candidates WHERE id = ?");
    $stmt->execute([$candidate_id]);
    $candidate = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$candidate) {
        header("Location: candidates.php");
        exit();
    }
} catch(PDOException $e) {
    $error = "Error fetching candidate details: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Profile - E-Voting System</title>
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
            min-height: 100vh;
            display: flex;
        }

        .main-content {
            flex: 1;
            padding: 20px;
        }

        .profile-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--background-color);
        }

        .profile-header h1 {
            color: var(--primary-color);
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .candidate-image {
            width: 200px;
            height: 200px;
            margin: 0 auto 20px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid var(--secondary-color);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .candidate-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .position-badge {
            display: inline-block;
            background: var(--secondary-color);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 1.1em;
            margin-top: 10px;
        }

        .profile-section {
            margin-bottom: 25px;
        }

        .profile-section h2 {
            color: var(--primary-color);
            font-size: 1.5em;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--background-color);
        }

        .profile-section p {
            color: var(--text-color);
            line-height: 1.6;
            font-size: 1.1em;
            margin-bottom: 15px;
        }

        .back-button {
            display: inline-block;
            padding: 12px 25px;
            background-color: var(--secondary-color);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
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
        }

        .sidebar-header h2 {
            font-size: 1.4em;
            font-weight: 600;
            margin: 10px 0;
            color: white;
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
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-item.active {
            background: rgba(52, 152, 219, 0.2);
            color: white;
        }

        .nav-item i {
            margin-right: 12px;
            width: 20px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 1px;
                padding: 15px 10px;
            }

            .sidebar-header h2,
            .nav-item span {
                display: none;
            }

            .main-content {
                margin-left: 1px;
            }

            .profile-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="profile-container">
            <div class="profile-header">
                <div class="candidate-image">
                    <?php if(!empty($candidate['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($candidate['image_path']); ?>" alt="<?php echo htmlspecialchars($candidate['full_name']); ?>">
                    <?php else: ?>
                        <img src="images/default-candidate.png" alt="Default Profile">
                    <?php endif; ?>
                </div>
                <h1><?php echo htmlspecialchars($candidate['full_name']); ?></h1>
                <div class="position-badge">
                    <?php echo htmlspecialchars($candidate['position']); ?>
                </div>
            </div>

            <div class="profile-section">
                <h2>Platform</h2>
                <p><?php echo nl2br(htmlspecialchars($candidate['platform'])); ?></p>
            </div>

            <a href="candidates.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Candidates
            </a>
        </div>
    </div>
</body>
</html>
</style>
