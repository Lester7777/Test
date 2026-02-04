<?php
session_start();
require_once 'config.php';

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_user':
                $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $id_number = filter_var($_POST['id_number'], FILTER_SANITIZE_STRING);
                
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, id_number) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $email, $password, $id_number]);
                $_SESSION['message'] = "User added successfully!";
                break;

            case 'delete_user':
                $user_id = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $_SESSION['message'] = "User deleted successfully!";
                break;

            case 'make_admin':
                $user_id = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);
                $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
                $stmt->execute([$user_id]);
                $_SESSION['message'] = "User has been made an administrator!";
                break;

            case 'remove_admin':
                $user_id = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);
                $stmt = $pdo->prepare("UPDATE users SET role = 'voter' WHERE id = ?");
                        $stmt->execute([$user_id]);
                $_SESSION['message'] = "Administrator has been changed to voter!";
                break;
        }
        header("Location: user_management.php");
        exit();
    }
}

// Modify the users query to include is_admin field
$stmt = $pdo->query("SELECT * FROM users ORDER BY username");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - E-Voting System</title>
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
            display: flex;
        }

        @media (max-width: 768px) {

            .main-content {
                margin-left: 70px;
                width: calc(100% - 70px);
            }
        }

    .main-content {
        flex: 1;
        margin-left: 12px;
        padding: 20px;
        min-height: 100vh;
        width: calc(100% - 250px);
        position: relative;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
    }

    .header {
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .add-user-form {
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 15px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        color: var(--text-color);
        font-weight: 500;
    }

    .form-group input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .form-group input:focus {
        border-color: var(--secondary-color);
        box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        outline: none;
    }

    .submit-btn {
        background-color: var(--success-color);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
    }

    .submit-btn:hover {
        background-color: #27ae60;
        transform: translateY(-1px);
    }

    .users-table {
        width: 100%;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .users-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .users-table th, .users-table td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .users-table th {
        background-color: var(--primary-color);
        color: white;
        font-weight: 600;
        white-space: nowrap;
    }

    .users-table tr:hover {
        background-color: #f8f9fa;
    }

    .users-table td {
        vertical-align: middle;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .action-btn {
        padding: 8px 16px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .delete-btn {
        background-color: var(--accent-color);
        color: white;
    }

    .delete-btn:hover {
        background-color: #c0392b;
    }

    .admin-btn {
        background-color: var(--secondary-color);
        color: white;
    }

    .admin-btn:hover {
        background-color: #2980b9;
    }

    .voter-btn {
        background-color: var(--warning-color);
        color: var(--text-color);
    }

    .voter-btn:hover {
        background-color: #f39c12;
    }

    .message {
        padding: 12px 20px;
        margin-bottom: 20px;
        border-radius: 6px;
        background-color: var(--success-color);
        color: white;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            transform: translateY(-10px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @media (max-width: 768px) {
        body {
            margin: 0;
            padding: 0;
        }

        .main-content {
            margin-left: 1px;
            width: calc(100% - 1px);
            padding: 15px;
        }

        .form-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .users-table {
            overflow-x: auto;
            margin: 0;
            padding: 10px;
            width: 100%;
            border-radius: 8px;
        }

        .users-table table {
            min-width: 800px;
        }

        .header,
        .search-bar,
        .add-user-form {
            margin: 0 0 15px 0;
            width: 100%;
            border-radius: 8px;
            padding: 15px;
        }

        .action-buttons {
            flex-direction: row;
            gap: 5px;
        }

        .action-btn {
            width: auto;
            padding: 6px 12px;
            font-size: 13px;
        }

        .container {
            padding: 0;
            margin: 0;
            width: 100%;
        }

        .message {
            margin: 0 0 15px 0;
            padding: 10px 15px;
        }
    }
    </style>

    <div class="main-content">
        <div class="container">
            <div class="header">
                <h1><i class="fas fa-user-shield"></i> User Management</h1>
                <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
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

            <div class="add-user-form">
                <h2><i class="fas fa-user-plus"></i> Add New User</h2>
                <form method="POST" action="user_management.php" autocomplete="off">
                    <input type="hidden" name="action" value="add_user">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" required autocomplete="username">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required autocomplete="email">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required autocomplete="new-password">
                        </div>
                        <div class="form-group">
                            <label for="id_number">ID Number</label>
                            <input type="text" id="id_number" name="id_number" required autocomplete="off">
                        </div>
                    </div>
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-plus"></i> Add User
                    </button>
                </form>
            </div>

            <div class="users-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>ID Number</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['id_number']); ?></td>
                            <td>
                                <?php if (isset($user['role']) && $user['role'] == 'admin'): ?>
                                    <span class="admin-badge">Admin</span>
                                <?php else: ?>
                                    User
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($user['role']) && $user['role'] == 'admin'): ?>
                                    <form method="POST" action="user_management.php" style="display: inline;">
                                        <input type="hidden" name="action" value="remove_admin">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="voter-btn" onclick="return confirm('Are you sure you want to remove administrator privileges from this user?')">
                                            <i class="fas fa-user"></i> Make User
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="user_management.php" style="display: inline;">
                                        <input type="hidden" name="action" value="make_admin">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="admin-btn" onclick="return confirm('Are you sure you want to make this user an administrator?')">
                                            <i class="fas fa-user-shield"></i> Make Admin
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" action="user_management.php" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this user?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

<style>
    .admin-btn {
        background-color: var(--secondary-color);
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-right: 5px;
    }

    .admin-btn:hover {
        background-color: #2980b9;
    }

    .admin-badge {
        background-color: var(--success-color);
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 12px;
    }

    .user-btn {
        background-color: #f39c12;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-right: 5px;
    }

    .user-btn:hover {
        background-color: #d68910;
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background-color: var(--primary-color);
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.3s ease;
        margin-top: 10px;
    }

    .back-btn:hover {
        background-color: #34495e;
        transform: translateX(-5px);
    }

    .back-btn i {
        font-size: 0.9em;
    }
</style>