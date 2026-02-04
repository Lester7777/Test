<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Fetch all candidates
try {
    $stmt = $pdo->query("SELECT * FROM candidates ORDER BY 
        CASE position 
            WHEN 'president' THEN 1
            WHEN 'vice president' THEN 2
            WHEN 'secretary' THEN 3
            WHEN 'treasurer' THEN 4
            ELSE 5
        END, 
        full_name");
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching candidates: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidates - E-Voting System</title>
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

        .visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
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
            background-position: 0 0, 0 0, 40px 70px;
            min-height: 100vh;
        }

        .main-content {
            margin-left: 0;
            padding: 20px;
            width: 100%;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            -webkit-backdrop-filter: blur(10px);
            backdrop-filter: blur(10px);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .header h1 {
            color: var(--primary-color);
            font-size: 2em;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 1.1em;
        }

        .position-filter {
            background: rgba(255, 255, 255, 0.95);
            -webkit-backdrop-filter: blur(10px);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }

        .position-filter select {
            width: 200px;
            padding: 10px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1em;
            color: var(--text-color);
            background-color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .position-filter select:hover {
            border-color: var(--secondary-color);
        }

        .search-bar {
            background: rgba(255, 255, 255, 0.95);
            -webkit-backdrop-filter: blur(10px);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
        }

        .search-bar input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1em;
            color: var(--text-color);
            transition: all 0.3s ease;
        }

        .search-bar input:focus {
            border-color: var(--secondary-color);
            outline: none;
        }

        .search-bar button {
            padding: 12px 25px;
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .search-bar button:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .candidates-grid {
            display: block;
            padding: 10px;
        }

        .position-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .position-candidates {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .candidate-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            padding: 20px;
            transition: all 0.3s ease;
        }

        .candidate-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .candidate-name {
            font-size: 1.4em;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .candidate-position {
            color: var(--secondary-color);
            font-weight: 500;
            font-size: 1.1em;
            margin-bottom: 15px;
        }

        .candidate-details {
            padding: 15px 0;
            border-top: 2px solid #f5f6fa;
        }

        .candidate-details p {
            margin-bottom: 12px;
            color: #555;
            line-height: 1.5;
        }

        .candidate-details strong {
            color: var(--primary-color);
            font-weight: 600;
        }

        .view-profile-btn {
            width: 100%;
            padding: 12px;
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            text-align: center;
            margin-top: 15px;
        }

        .view-profile-btn:hover {
            background-color: #2980b9;
            transform: scale(1.02);
        }

        /* Media Queries */
        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }

            .search-bar {
                flex-direction: column;
            }

            .search-bar button {
                width: 100%;
            }

            .position-candidates {
                grid-template-columns: 1fr;
            }
            
            .position-section {
                padding: 15px;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 10px;
            }

            .candidate-card {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="header">
            <?php
            // Add back button with role-based redirection
            $backUrl = isset($_SESSION['role']) && $_SESSION['role'] === 'admin' ? 'dashboard.php' : 'user_dashboard.php';
            ?>
            <a href="<?php echo $backUrl; ?>" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h1><i class="fas fa-user-tie"></i> Candidates</h1>
            <p>View and learn more about the candidates running for different positions.</p>
        </div>

        <div class="position-filter">
            <label for="positionFilter" class="visually-hidden">Filter candidates by position</label>
            <select id="positionFilter" onchange="filterCandidates()" aria-label="Filter candidates by position">
                <option value="">All Positions</option>
                <option value="president">President</option>
                <option value="vice president">Vice President</option>
                <option value="secretary">Secretary</option>
                <option value="treasurer">Treasurer</option>
            </select>
        </div>

        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Search candidates..." oninput="searchCandidates()">
            <button onclick="searchCandidates()">
                <i class="fas fa-search"></i> Search
            </button>
        </div>

        <div class="candidates-grid">
            <?php if(isset($candidates) && !empty($candidates)): ?>
                <?php 
                // Group candidates by position
                $candidatesByPosition = [];
                foreach($candidates as $candidate) {
                    $position = $candidate['position'];
                    if(!isset($candidatesByPosition[$position])) {
                        $candidatesByPosition[$position] = [];
                    }
                    $candidatesByPosition[$position][] = $candidate;
                }
                
                // Display candidates grouped by position
                foreach($candidatesByPosition as $position => $positionCandidates): 
                ?>
                    <div class="position-section">
                        <h2 class="position-title"><?php echo htmlspecialchars($position); ?></h2>
                        <div class="position-candidates">
                            <?php foreach($positionCandidates as $candidate): ?>
                                <div class="candidate-card" data-position="<?php echo htmlspecialchars(strtolower($candidate['position'])); ?>">
                                    <div class="candidate-info">
                                        <div class="candidate-name">
                                            <h3><?php echo htmlspecialchars(ucwords(strtolower($candidate['full_name']))); ?></h3>
                                        </div>
                                        <div class="candidate-position">
                                            <?php echo htmlspecialchars(ucwords($candidate['position'])); ?>
                                        </div>
                                        <div class="candidate-details">
                                            <p><strong>Platform:</strong> <?php echo htmlspecialchars($candidate['platform']); ?></p>
                                        </div>
                                        <div class="candidate-actions">
                                            <button class="view-profile-btn" onclick="viewProfile(<?php echo $candidate['id']; ?>)">
                                                View Full Profile
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No candidates found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function filterCandidates() {
            const position = document.getElementById('positionFilter').value.toLowerCase();
            const cards = document.querySelectorAll('.candidate-card');
            
            cards.forEach(card => {
                if (position === '' || card.dataset.position.toLowerCase() === position) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function searchCandidates() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.candidate-card');
            
            cards.forEach(card => {
                const candidateName = card.querySelector('.candidate-name').textContent.toLowerCase();
                const candidatePosition = card.querySelector('.candidate-position').textContent.toLowerCase();
                const candidateDetails = card.querySelector('.candidate-details').textContent.toLowerCase();
                
                if (searchTerm === '' || 
                    candidateName.includes(searchTerm) || 
                    candidatePosition.includes(searchTerm) || 
                    candidateDetails.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function viewProfile(candidateId) {
            window.location.href = `candidate-profile.php?id=${candidateId}`;
        }
    </script>
</body>
</html>
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

    .visually-hidden {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
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
        background-position: 0 0, 0 0, 40px 70px;
        min-height: 100vh;
    }

    .header {
        background: rgba(255, 255, 255, 0.95);
        -webkit-backdrop-filter: blur(10px);
        backdrop-filter: blur(10px);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .header h1 {
        color: var(--primary-color);
        font-size: 2em;
        margin-bottom: 10px;
    }

    .header p {
        color: #666;
        font-size: 1.1em;
    }

    .position-filter {
        background: rgba(255, 255, 255, 0.95);
        -webkit-backdrop-filter: blur(10px);
        backdrop-filter: blur(10px);
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin-bottom: 25px;
    }

    .position-filter select {
        width: 200px;
        padding: 10px 15px;
        border: 2px solid #e1e8ed;
        border-radius: 8px;
        font-size: 1em;
        color: var(--text-color);
        background-color: white;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .position-filter select:hover {
        border-color: var(--secondary-color);
    }

    .search-bar {
        background: rgba(255, 255, 255, 0.95);
        -webkit-backdrop-filter: blur(10px);
        backdrop-filter: blur(10px);
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin-bottom: 25px;
        display: flex;
        gap: 15px;
    }

    .search-bar input {
        flex: 1;
        padding: 12px 15px;
        border: 2px solid #e1e8ed;
        border-radius: 8px;
        font-size: 1em;
        color: var(--text-color);
        transition: all 0.3s ease;
    }

    @media (max-width: 768px) {
        .main-content {
            margin-left: 70px;
            padding: 15px;
            width: calc(100% - 70px);
        }
    }

    @media (max-width: 480px) {
        .main-content {
            padding: 10px;
        }
    }
    .search-bar input:focus {
        border-color: var(--secondary-color);
        outline: none;
    }

    .search-bar button {
        padding: 12px 25px;
        background-color: var(--secondary-color);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .search-bar button:hover {
        background-color: #2980b9;
        transform: translateY(-2px);
    }

    .candidates-grid {
        display: grid;
        gap: 25px;
    }

    .position-section {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .position-candidates {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
            padding: 15px;
            width: 100%;
        }

        .search-bar {
            flex-direction: column;
        }

        .search-bar button {
            width: 100%;
        }

        .position-candidates {
            grid-template-columns: 1fr;
        }
        
        .position-section {
            padding: 15px;
        }
    }

    @media (max-width: 480px) {
        .main-content {
            padding: 10px;
        }
    }
    .search-bar input:focus {
        border-color: var(--secondary-color);
        outline: none;
    }

    .search-bar button {
        padding: 12px 25px;
        background-color: var(--secondary-color);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .search-bar button:hover {
        background-color: #2980b9;
        transform: translateY(-2px);
    }

    .candidates-grid {
        display: block;
        padding: 10px;
    }

    .candidate-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    @media (max-width: 768px) {
        .position-candidates {
            grid-template-columns: 1fr;
        }
        
        .position-section {
            padding: 15px;
        }
    }

    .candidate-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }

    .candidate-name {
        font-size: 1.4em;
        font-weight: 600;
        color: var(--primary-color);
        margin-bottom: 8px;
    }

    .candidate-position {
        color: var(--secondary-color);
        font-weight: 500;
        font-size: 1.1em;
        margin-bottom: 15px;
    }

    .candidate-details {
        padding: 15px 0;
        border-top: 2px solid #f5f6fa;
    }

    .candidate-details p {
        margin-bottom: 12px;
        color: #555;
        line-height: 1.5;
    }

    .candidate-details strong {
        color: var(--primary-color);
        font-weight: 600;
    }

    .view-profile-btn {
        width: 100%;
        padding: 12px;
        background-color: var(--secondary-color);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
        text-align: center;
        margin-top: 15px;
    }

    .view-profile-btn:hover {
        background-color: #2980b9;
        transform: scale(1.02);
    }

    @media (max-width: 768px) {
        .candidates-grid {
            grid-template-columns: 1fr;
            padding: 10px;
        }

        .search-bar {
            flex-direction: column;
        }

        .search-bar button {
            width: 100%;
        }
    }

    /* Add styles for the back button */
    .back-button {
        display: inline-flex;
        align-items: center;
        padding: 8px 15px;
        background-color: var(--primary-color);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .back-button:hover {
        background-color: #1a252f;
        transform: translateX(-5px);
    }

    .back-button i {
        margin-right: 8px;
    }
</style>
