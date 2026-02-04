<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Initialize variables
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;
$error = '';

// Fetch total number of voters and voters based on search
try {
    if (!empty($search)) {
        $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE 
            id_number LIKE :search1 OR 
            username LIKE :search2 OR 
            email LIKE :search3");
        $search_param = "%$search%";
        $count_stmt->bindValue(':search1', $search_param);
        $count_stmt->bindValue(':search2', $search_param);
        $count_stmt->bindValue(':search3', $search_param);
        $count_stmt->execute();
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE 
            id_number LIKE :search1 OR 
            username LIKE :search2 OR 
            email LIKE :search3 
            ORDER BY username 
            LIMIT :limit OFFSET :offset");
        $search_param = "%$search%";
        $stmt->bindValue(':search1', $search_param);
        $stmt->bindValue(':search2', $search_param);
        $stmt->bindValue(':search3', $search_param);
        $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        $count_stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $stmt = $pdo->prepare("SELECT * FROM users ORDER BY username LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    $total_voters = $count_stmt->fetchColumn();
    $total_pages = ceil($total_voters / $per_page);
    $voters = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching voters: " . $e->getMessage();
    $total_pages = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voters - E-Voting System</title>
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
            padding: 20px;
        }

        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px;
            min-height: 100vh;
            width: 100%;
            position: relative;
        }

        .header {
            padding: 30px; 
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 28px; 
        }

        .search-bar {
            padding: 25px; 
            margin-bottom: 30px;
        }

        .users-table {
            padding: 30px;
        }

        .users-table th,
        .users-table td {
            padding: 15px;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            transition: background 0.3s;
        }

        .back-button:hover {
            background: var(--secondary-color);
        }

        .main-content {
            flex: 1;
            margin-left: 0;
            padding: 20px;
            min-height: 100vh;
            width: 100%;
            position: relative;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .header h1 {
            color: var(--primary-color);
            font-size: 24px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header p {
            color: #666;
            font-size: 14px;
        }

        .search-bar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .search-bar form {
            display: flex;
            gap: 10px;
        }

        .search-bar input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .search-bar button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background 0.3s;
        }

        .search-bar button:hover {
            background: var(--secondary-color);
        }

        .users-table {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .users-table table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .users-table th {
            background: var(--primary-color);
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 500;
        }

        .users-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .users-table tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-voted {
            background: var(--success-color);
            color: white;
        }

        .status-not-voted {
            background: var(--warning-color);
            color: white;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }

        .pagination a {
            padding: 8px 12px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.3s;
        }

        .pagination a:hover,
        .pagination a.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        @media (max-width: 768px) {
            .main-content {
                margin: 0;
                width: 100%;
                padding: 15px;
            }

            .header,
            .search-bar,
            .users-table {
                margin: 0 0 15px 0;
                width: 100%;
                border-radius: 10px;
            }

            .users-table {
                overflow-x: auto;
                padding: 15px;
            }

            .users-table table {
                min-width: 800px;
            }

            .header,
            .search-bar {
                margin: 0 -10px 15px -10px;
                width: calc(100% + 20px);
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <a href="dashboard.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>

        <div class="header">
            <h1><i class="fas fa-users"></i> Voters List</h1>
            <p>View and manage registered voters</p>
            <?php if($error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="search-bar">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Search by ID Number, Username, or Email..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
        </div>

        <div class="users-table">
            <table>
                <thead>
                    <tr>
                        <th>ID Number</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Registration Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(isset($voters) && !empty($voters)): ?>
                        <?php foreach($voters as $voter): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($voter['id_number']); ?></td>
                                <td><?php echo htmlspecialchars($voter['username']); ?></td>
                                <td><?php echo htmlspecialchars($voter['email']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $voter['has_voted'] ? 'status-voted' : 'status-not-voted'; ?>">
                                        <?php echo $voter['has_voted'] ? 'Voted' : 'Not Voted'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($voter['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">No voters found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if($total_pages > 1): ?>
            <div class="pagination">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" 
                       class="<?php echo $page === $i ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .header h1 {
            color: var(--primary-color);
            font-size: 24px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header p {
            color: #666;
            font-size: 14px;
        }

        .search-bar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .search-bar form {
            display: flex;
            gap: 10px;
        }

        .search-bar input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .search-bar button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background 0.3s;
        }

        .search-bar button:hover {
            background: var(--secondary-color);
        }

        .users-table {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .users-table table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .users-table th {
            background: var(--primary-color);
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 500;
        }

        .users-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .users-table tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-voted {
            background: var(--success-color);
            color: white;
        }

        .status-not-voted {
            background: var(--warning-color);
            color: white;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }

        .pagination a {
            padding: 8px 12px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.3s;
        }

        .pagination a:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination a.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
    </style>
</body>
</html>
