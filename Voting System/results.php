<?php
session_start();
try {
    require_once 'config.php';
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Fetch voting results grouped by position
try {
    $results = [];
    
    // Get all positions
    $stmt = $pdo->query("SELECT DISTINCT position FROM candidates 
        ORDER BY 
            CASE position
                WHEN 'President' THEN 1
                WHEN 'Vice President' THEN 2
                WHEN 'Secretary' THEN 3
                WHEN 'Treasurer' THEN 4
                ELSE 5
            END,
            position");
    $positions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach($positions as $position) {
        // Get candidates and their vote counts for each position
        $stmt = $pdo->prepare("
            SELECT DISTINCT
                c.id,
                c.full_name,
                COUNT(DISTINCT v.id) as vote_count
            FROM candidates c
            LEFT JOIN votes v ON c.id = v.candidate_id
            WHERE TRIM(LOWER(c.position)) = TRIM(LOWER(?))
            GROUP BY c.id
            HAVING c.id IS NOT NULL
            ORDER BY vote_count DESC, c.full_name ASC
        ");
        $stmt->execute([$position]);
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate total votes for this position
        $total_votes = array_sum(array_column($candidates, 'vote_count'));
        
        // Add percentage for each candidate
        foreach($candidates as &$candidate) {
            $candidate['percentage'] = $total_votes > 0 
                ? round(($candidate['vote_count'] / $total_votes) * 100, 1)
                : 0;
        }
        
        $results[$position] = [
            'candidates' => $candidates,
            'total_votes' => $total_votes
        ];
    }
} catch(PDOException $e) {
    $error = "Error fetching results: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results - E-Voting System</title>
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
            background-image: 
                linear-gradient(30deg, #f6f8fc 12%, transparent 12.5%, transparent 87%, #f6f8fc 87.5%, #f6f8fc),
                linear-gradient(150deg, #f6f8fc 12%, transparent 12.5%, transparent 87%, #f6f8fc 87.5%, #f6f8fc),
                linear-gradient(30deg, #f6f8fc 12%, transparent 12.5%, transparent 87%, #f6f8fc 87.5%, #f6f8fc),
                linear-gradient(150deg, #f6f8fc 12%, transparent 12.5%, transparent 87%, #f6f8fc 87.5%, #f6f8fc),
                linear-gradient(60deg, #e3e7ed 25%, transparent 25.5%, transparent 75%, #e3e7ed 75%, #e3e7ed),
                linear-gradient(60deg, #e3e7ed 25%, transparent 25.5%, transparent 75%, #e3e7ed 75%, #e3e7ed);
            background-size: 80px 140px;
            background-position: 0 0, 0 0, 40px 70px, 40px 70px, 0 0, 40px 70px;
            min-height: 100vh;
        }

        .main-content {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .results-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .position-title {
            color: var(--primary-color);
            font-size: 1.5em;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--secondary-color);
        }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .candidate-result {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        .candidate-info {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .candidate-name {
            font-size: 1.2em;
            font-weight: 600;
            color: var(--primary-color);
        }

        .candidate-party {
            color: var(--secondary-color);
            font-size: 0.9em;
            margin-top: 5px;
        }

        .vote-bar {
            height: 25px;
            background: #e9ecef;
            border-radius: 12.5px;
            overflow: hidden;
            margin: 10px 0;
        }

        .vote-progress {
            height: 100%;
            background: linear-gradient(45deg, var(--secondary-color),hsl(204, 63.70%, 44.30%));
            border-radius: 12.5px;
            transition: width 1s ease-in-out;
        }

        .vote-stats {
            display: flex;
            justify-content: space-between;
            color: var(--text-color);
            font-size: 0.9em;
     
        }
        .back-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 25px;
    background-color: var(--primary-color);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    margin-bottom: 20px;
    transition: all 0.3s ease;
}

.back-button:hover {
    background-color: var(--secondary-color);
    transform: translateY(-2px);
}
        @media (max-width: 768px) {
            .results-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <?php
        // Add back button with role-based redirection
        $back_url = ($_SESSION['role'] === 'admin') ? 'dashboard.php' : 'user_dashboard.php';
        ?>
        <a href="<?php echo $back_url; ?>" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="header">
            <h1><i class="fas fa-chart-bar"></i> Election Results</h1>
            <p>Live voting results and statistics</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php else: ?>
            <?php foreach($results as $position => $data): ?>
                <div class="results-section">
                    <h2 class="position-title"><?php echo htmlspecialchars($position); ?></h2>
                    
                    <div class="results-grid">
                        <?php foreach($data['candidates'] as $candidate): ?>
                            <div class="candidate-result">
                                <div class="candidate-info">
                                    <div>
                                        <div class="candidate-name">
                                            <?php echo htmlspecialchars($candidate['full_name']); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="vote-bar">
                                    <div class="vote-progress" style="width: <?php echo $candidate['percentage']; ?>%"></div>
                                </div>
                                <div class="vote-stats">
                                    <span class="vote-count"><?php echo number_format($candidate['vote_count']); ?> votes</span>
                                    <span class="vote-percentage"><?php echo number_format($candidate['percentage'], 1); ?>%</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>

