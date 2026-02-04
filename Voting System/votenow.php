<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once 'config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log errors to a file
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Check if user has already voted
try {
    $stmt = $pdo->prepare("SELECT has_voted FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user['has_voted']) {
        $_SESSION['error'] = "You have already voted.";
        header("Location: user_dashboard.php");
        exit();
    }
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred. Please try again later.";
    header("Location: user_dashboard.php");
    exit();
}

// Fetch all candidates grouped by position
try {
    $stmt = $pdo->query("SELECT * FROM candidates ORDER BY position, last_name");
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group candidates by position
    $positions = [];
    foreach ($candidates as $candidate) {
        $positions[$candidate['position']][] = $candidate;
    }
} catch(PDOException $e) {
    header("Location: dashboard.php?error=fetch_error");
    exit();
}

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate that all positions have been voted for
    if (!isset($_POST['votes']) || empty($_POST['votes'])) {
        header("Location: votenow.php?error=invalid_selection");
        exit();
    }

    try {
        $pdo->beginTransaction();
        
        // Verify user hasn't voted yet (double-check)
        $stmt = $pdo->prepare("SELECT has_voted FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user['has_voted']) {
            throw new Exception('User has already voted');
        }
        
        // Record each vote
        foreach ($_POST['votes'] as $position => $candidate_id) {
            // Validate candidate exists and matches position
            $stmt = $pdo->prepare("SELECT id FROM candidates WHERE id = ? AND position = ?");
            $stmt->execute([$candidate_id, $position]);
            if (!$stmt->fetch()) {
                throw new Exception('Invalid candidate selection');
            }
            
            $stmt = $pdo->prepare("INSERT INTO votes (user_id, candidate_id, position) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $candidate_id, $position]);
        }
        
        // Update user's voting status
        $stmt = $pdo->prepare("UPDATE users SET has_voted = 1 WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        $pdo->commit();
        
        // Clear session data related to voting
        unset($_SESSION['votes']);
        
        header("Location: user_dashboard.php?success=vote_recorded");
        exit();
    } catch(Exception $e) {
        $pdo->rollBack();
        error_log("Voting error: " . $e->getMessage());
        header("Location: votenow.php?error=vote_failed&message=" . urlencode($e->getMessage()));
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cast Your Vote - E-Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f1c40f;
            --background-color: #f5f6fa;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--background-color) 0%, #e8f0fe 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .voting-header {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            text-align: center;
            border-bottom: 4px solid var(--secondary-color);
        }

        .voting-header h1 {
            color: var(--primary-color);
            margin: 0;
            font-size: 2em;
        }

        .voting-instructions {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .voting-instructions ul {
            list-style-type: none;
            padding: 0;
        }

        .voting-instructions li {
            margin: 10px 0;
            padding-left: 25px;
            position: relative;
        }

        .voting-instructions li:before {
            content: '✓';
            color: var(--success-color);
            position: absolute;
            left: 0;
        }

        .position-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            transition: transform 0.3s ease;
        }

        .position-section:hover {
            transform: translateY(-5px);
        }

        .position-title {
            color: var(--primary-color);
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .candidates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .candidate-card {
            background: #ffffff;
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .candidate-card:hover {
            border-color: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .candidate-card.selected {
            border-color: var(--success-color);
            background: linear-gradient(45deg, #f8fff8 0%, #ffffff 100%);
        }

        .candidate-card.selected:before {
            content: '✓';
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--success-color);
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .candidate-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .candidate-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e1e8ed;
        }

        .candidate-details h3 {
            margin: 0 0 5px 0;
            color: var(--primary-color);
        }

        .candidate-details p {
            margin: 0;
            color: #666;
            font-size: 0.9em;
        }

        .submit-vote {
            background: linear-gradient(45deg, var(--success-color) 0%, #27ae60 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 1.1em;
            cursor: pointer;
            width: 100%;
            margin-top: 30px;
            transition: all 0.3s ease;
        }

        .submit-vote:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
        }

        .error-message {
            background: #fee;
            color: var(--accent-color);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid var(--accent-color);
        }

        @media (max-width: 768px) {
            .candidates-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="voting-header">
            <h1><i class="fas fa-vote-yea"></i> Cast Your Vote</h1>
            <p>Make your voice heard in this election</p>
        </div>

        <div class="voting-instructions">
            <h2><i class="fas fa-info-circle"></i> Voting Instructions</h2>
            <ul>
                <li>Select one candidate for each position</li>
                <li>Your selections will be highlighted in green</li>
                <li>Review your choices before submitting</li>
                <li>You can only vote once</li>
            </ul>
        </div>

        <?php if(isset($_GET['error'])): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php
                switch($_GET['error']) {
                    case 'vote_failed':
                        echo 'Failed to record your vote. Please try again.';
                        break;
                    case 'invalid_selection':
                        echo 'Please select one candidate for each position.';
                        break;
                    default:
                        echo 'An error occurred. Please try again.';
                }
                ?>
            </div>
        <?php endif; ?>

        <form action="votenow.php" method="POST" id="votingForm">
            <?php foreach ($positions as $position => $position_candidates): ?>
            <div class="position-section">
                <h2 class="position-title"><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($position); ?></h2>
                <div class="candidates-grid">
                    <?php foreach ($position_candidates as $candidate): ?>
                    <label class="candidate-card">
                        <input type="radio" name="votes[<?php echo htmlspecialchars($position); ?>]" 
                               value="<?php echo $candidate['id']; ?>" 
                               style="display: none;"
                               required>
                        <div class="candidate-info">
                            <img src="<?php echo !empty($candidate['photo']) ? htmlspecialchars($candidate['photo']) : 'assets/default-avatar.png'; ?>" 
                                 alt="<?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']); ?>" 
                                 class="candidate-photo">
                            <div class="candidate-details">
                                <h3><?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']); ?></h3>
                                <p><?php echo htmlspecialchars($candidate['platform']); ?></p>
                            </div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <button type="submit" class="submit-vote">
                <i class="fas fa-check-circle"></i> Submit Your Vote
            </button>
        </form>
    </div>

    <script>
        document.querySelectorAll('.candidate-card input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // Remove selected class from all cards in the same position
                const positionSection = this.closest('.position-section');
                positionSection.querySelectorAll('.candidate-card').forEach(card => {
                    card.classList.remove('selected');
                });
                
                // Add selected class to the chosen card
                if (this.checked) {
                    this.closest('.candidate-card').classList.add('selected');
                }
            });
        });

        // Prevent double form submission
        document.getElementById('votingForm').addEventListener('submit', function(e) {
            if (this.submitted) {
                e.preventDefault();
                return;
            }
            
            const positions = <?php echo json_encode(array_keys($positions)); ?>;
            let allSelected = true;
            
            positions.forEach(position => {
                const selected = document.querySelector(`input[name="votes[${position}]"]:checked`);
                if (!selected) {
                    allSelected = false;
                }
            });
            
            if (!allSelected) {
                e.preventDefault();
                alert('Please select one candidate for each position before submitting your vote.');
                return;
            }
            
            this.submitted = true;
            this.querySelector('.submit-vote').disabled = true;
            this.querySelector('.submit-vote').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting Vote...';
        });
    </script>
</body>
</html>


