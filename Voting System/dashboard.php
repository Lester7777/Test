<?php
session_start();

// Security headers
header("X-Content-Type-Options: nosniff");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

// Remove deprecated headers
// header("Pragma: no-cache");  // Removed
// header("Expires: 0");        // Removed

// First check if user is logged in at all
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Then check if user has admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
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

// Get voting statistics
$stmt = $pdo->query("SELECT 
    (SELECT COUNT(*) FROM users WHERE role = 'voter') as total_voters,
    (SELECT COUNT(*) FROM candidates) as total_candidates,
    (SELECT COUNT(*) FROM votes) as total_votes,
    (SELECT COUNT(*) FROM users WHERE role = 'voter' AND has_voted = 1) as votes_cast
");
$stats = $stmt->fetch();

// Get recent activity
$stmt = $pdo->query("SELECT v.timestamp, u.username, c.full_name, c.position 
    FROM votes v 
    JOIN users u ON v.user_id = u.id 
    JOIN candidates c ON v.candidate_id = c.id 
    ORDER BY v.timestamp DESC LIMIT 5");
$recent_activity = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Voting System Dashboard</title>
    <link rel="stylesheet" href="dashboard.css?v=<?php echo filemtime('dashboard.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <img src="images/logo.png?v=<?php echo filemtime('images/logo.png'); ?>" alt="Logo">
                <h2>E-Voting System</h2>
            </div>
        </div>
        <nav class="nav-menu">
            <div class="menu-section">
                <div class="menu-header">Main Menu</div>
                <a href="dashboard.php" class="nav-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="candidates.php" class="nav-item">
                    <i class="fas fa-user-tie"></i>
                    <span>Candidates</span>
                </a>
                <a href="voters.php" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Voters</span>
                </a>
                <a href="voting.php" class="nav-item">
                    <i class="fas fa-vote-yea"></i>
                    <span>Vote Now</span>
                </a>
                <a href="results.php" class="nav-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Results</span>
                </a>
            </div>
            
            <div class="menu-section">
                <div class="menu-header">Administration</div>
                <a href="user_management.php" class="nav-item">
                    <i class="fas fa-user-shield"></i>
                    <span>User Management</span>
                </a>
                <a href="candidate_management.php" class="nav-item">
                    <i class="fas fa-user-plus"></i>
                    <span>Manage Candidates</span>
                </a>
                <a href="settings.php" class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </div>
            
            <div class="menu-section mt-auto">
                <a href="logout.php" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </nav>
    </div>

    <div class="main-content">
        <div class="header">
            <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($username); ?>!</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                    <h3>Total Voters</h3>
                    <p><?php echo $stats['total_voters']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-details">
                    <h3>Total Candidates</h3>
                    <p><?php echo $stats['total_candidates']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-vote-yea"></i>
                </div>
                <div class="stat-details">
                    <h3>Votes Cast</h3>
                    <p><?php echo $stats['votes_cast']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div class="stat-details">
                    <h3>Total Votes</h3>
                    <p><?php echo $stats['total_votes']; ?></p>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h2><i class="fas fa-history"></i> Recent Activity</h2>
                <div class="activity-list">
                    <?php if(empty($recent_activity)): ?>
                        <div class="no-activity">
                            <i class="fas fa-info-circle"></i>
                            <p>No recent voting activity to display.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($recent_activity as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-vote-yea"></i>
                            </div>
                            <div class="activity-details">
                                <div class="activity-header">
                                    <span class="voter-name"><?php echo htmlspecialchars($activity['username']); ?></span>
                                    <span class="timestamp">
                                        <?php 
                                            $timestamp = strtotime($activity['timestamp']);
                                            $now = time();
                                            $diff = $now - $timestamp;
                                            
                                            if($diff < 60) {
                                                echo '<i class="far fa-clock"></i> Just now';
                                            } elseif($diff < 3600) {
                                                $mins = floor($diff / 60);
                                                echo '<i class="far fa-clock"></i> ' . $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
                                            } elseif($diff < 86400) {
                                                $hours = floor($diff / 3600);
                                                echo '<i class="far fa-clock"></i> ' . $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
                                            } else {
                                                echo '<i class="far fa-clock"></i> ' . date('M j, Y g:i A', $timestamp);
                                            }
                                        ?>
                                    </span>
                                </div>
                                <p class="vote-info">
                                    Voted for <strong><?php echo htmlspecialchars($activity['full_name']); ?></strong>
                                    <span class="position-tag"><?php echo htmlspecialchars($activity['position']); ?></span>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="dashboard-card">
                <h2><i class="fas fa-chart-bar"></i> Voting Progress</h2>
                <div class="chart-container" style="position: relative; height: 300px; width: 100%;">
                    <canvas id="votingProgress"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize voting progress chart
        const ctx = document.getElementById('votingProgress').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Voted', 'Not Voted'],
                datasets: [{
                    data: [
                        <?php echo $stats['votes_cast']; ?>,
                        <?php echo $stats['total_voters'] - $stats['votes_cast']; ?>
                    ],
                    backgroundColor: [
                        'rgba(46, 204, 113, 0.8)',  // Green for voted
                        'rgba(231, 76, 60, 0.8)'    // Red for not voted
                    ],
                    borderColor: [
                        'rgba(46, 204, 113, 1)',
                        'rgba(231, 76, 60, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: {
                                size: 14
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = <?php echo $stats['total_voters']; ?>;
                                const value = context.raw;
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${context.label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '70%',
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });
    </script>
</body>
</html>
