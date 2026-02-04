<?php
session_start();
// Add these lines after session_start()
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$user_role = $_SESSION['role']; // Make sure this is set during login

// Check if user has already voted
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $has_voted = $stmt->fetchColumn() > 0;
} catch(PDOException $e) {
    die("Error checking vote status: " . $e->getMessage());
}

// Get candidates
try {
    $stmt = $pdo->query("SELECT * FROM candidates");  // Remove the WHERE status = 'active' condition
    $candidates = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error fetching candidates: " . $e->getMessage());
}


// Get candidates grouped by position
try {
    $stmt = $pdo->query("SELECT DISTINCT position FROM candidates 
        ORDER BY CASE position 
            WHEN 'President' THEN 1 
            WHEN 'Vice President' THEN 2 
            WHEN 'Secretary' THEN 3 
            WHEN 'Treasurer' THEN 4 
            ELSE 5 
        END");
    $positions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $candidates_by_position = [];
    foreach ($positions as $position) {
        $stmt = $pdo->prepare("SELECT * FROM candidates WHERE position = ?");
        $stmt->execute([$position]);
        $candidates_by_position[$position] = $stmt->fetchAll();
    }
} catch(PDOException $e) {
    die("Error fetching candidates: " . $e->getMessage());
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
        /* Copy the same CSS styles from dashboard.php for consistency */
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
            padding: 20px;
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            color: var(--primary-color);
            margin-bottom: 30px;
            font-size: 2.5em;
            text-align: center;
            border-bottom: 3px solid var(--secondary-color);
            padding-bottom: 15px;
        }

        .voting-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-top: 30px;
        }

        .voting-section h2 {
            color: var(--primary-color);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--secondary-color);
            font-size: 1.8em;
            text-align: center;
        }

        .candidate-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
            margin-top: 25px;
        }

        .candidate-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #eee;
        }

        .candidate-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .candidate-card h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 1.4em;
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 10px;
        }

        .candidate-card p {
            margin: 12px 0;
            color: #555;
        }

        .candidate-card strong {
            color: var(--primary-color);
        }

        .vote-option {
            margin-top: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .vote-option input[type="radio"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .vote-option label {
            font-weight: 500;
            color: var(--primary-color);
            cursor: pointer;
        }

        .vote-button {
            display: block;
            width: 200px;
            margin: 30px auto;
            background: var(--success-color);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.1em;
            font-weight: bold;
            text-align: center;
        }

        .vote-button:hover {
            background: #27ae60;
            transform: translateY(-2px);
        }

        .vote-button:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            font-weight: 500;
            text-align: center;
        }

        .alert i {
            margin-right: 10px;
        }

        .alert-warning {
            background: var(--warning-color);
            color: #fff;
        }

        .alert-success {
            background: var(--success-color);
            color: #fff;
        }

        .back-button {
            display: inline-block;
            padding: 12px 25px;
            background-color: var(--primary-color);
            color: white !important;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            margin-top: 30px;
        }

        .back-button:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .back-button i {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-vote-yea"></i> Cast Your Vote</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($_SESSION['error']); ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if ($has_voted): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> You have already cast your vote.
            </div>
        <?php else: ?>
            <form action="process_vote.php" method="POST" onsubmit="return validateVoteForm()">
                <?php foreach ($positions as $position): ?>
                    <div class="voting-section">
                        <h2><?php echo htmlspecialchars($position); ?></h2>
                        <div class="candidate-grid">
                            <?php foreach ($candidates_by_position[$position] as $candidate): ?>
                                <div class="candidate-card">
                                    <h3><?php echo htmlspecialchars($candidate['full_name']); ?></h3>
                                    <p><strong>Position:</strong> <?php echo htmlspecialchars($candidate['position']); ?></p>
                                    <p><strong>Platform:</strong> <?php echo htmlspecialchars($candidate['platform']); ?></p>
                                    <div class="vote-option">
                                        <input type="radio" 
                                               id="candidate_<?php echo $candidate['id']; ?>" 
                                               name="vote[<?php echo htmlspecialchars($position); ?>]" 
                                               value="<?php echo $candidate['id']; ?>" 
                                               data-position="<?php echo htmlspecialchars($position); ?>"
                                               required>
                                        <label for="candidate_<?php echo $candidate['id']; ?>">Select this candidate</label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="vote-button" <?php echo $has_voted ? 'disabled' : ''; ?>>
                    <i class="fas fa-check-circle"></i> Submit Vote
                </button>
            </form>
        <?php endif; ?>
        
        <div style="margin-top: 20px;">
            <?php if ($user_role === 'admin'): ?>
                <a href="dashboard.php?nocache=<?php echo time(); ?>" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            <?php else: ?>
                <a href="user_dashboard.php?nocache=<?php echo time(); ?>" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<script>
function validateVoteForm() {
    const positions = <?php echo json_encode($positions); ?>;
    for (const position of positions) {
        const radios = document.getElementsByName(`vote[${position}]`);
        let checked = false;
        for (const radio of radios) {
            if (radio.checked) {
                checked = true;
                break;
            }
        }
        if (!checked) {
            alert(`Please select a candidate for ${position}`);
            return false;
        }
    }
    return true;
}
</script>