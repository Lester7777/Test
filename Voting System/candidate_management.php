<?php
session_start();
require_once 'config.php';

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_candidate':
                $full_name = filter_var($_POST['full_name'], FILTER_SANITIZE_STRING);
                $position = filter_var($_POST['position'], FILTER_SANITIZE_STRING);
                $platform = filter_var($_POST['platform'], FILTER_SANITIZE_STRING);
                
                // Handle image upload
                $image_path = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $target_dir = "uploads/candidates/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $image_path = $target_dir . time() . '_' . basename($_FILES['image']['name']);
                    move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
                }
                
                $stmt = $pdo->prepare("INSERT INTO candidates (full_name, position, platform, image_path) VALUES (?, ?, ?, ?)");
                $stmt->execute([$full_name, $position, $platform, $image_path]);
                $_SESSION['message'] = "Candidate added successfully!";
                break;

            case 'delete_candidate':
                $candidate_id = filter_var($_POST['candidate_id'], FILTER_SANITIZE_NUMBER_INT);
                $stmt = $pdo->prepare("DELETE FROM candidates WHERE id = ?");
                $stmt->execute([$candidate_id]);
                $_SESSION['message'] = "Candidate deleted successfully!";
                break;
        }
        header("Location: candidate_management.php");
        exit();
    }
}

// Get all candidates
$stmt = $pdo->query("SELECT * FROM candidates ORDER BY position, full_name");
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Management - E-Voting System</title>
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
            }
        }

        .container {
            flex: 1;
            margin-left: 12px;
            padding: 20px;
            background-color: var(--background-color);
        }

        .header {
            margin-bottom: 20px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .header h1 {
            color: var(--text-color);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .add-candidate-form {
            background-color: white;
            padding: 30px;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            max-width: 800px;
            margin-left: 20px;
            margin-right: auto;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 500;
            font-size: 1rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
            font-size: 1rem;
        }

        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
            min-height: 120px;
            resize: vertical;
            font-size: 1rem;
        }

        .preview-image {
            max-width: 200px;
            max-height: 200px;
            display: none;
            margin-top: 10px;
            border-radius: 6px;
            object-fit: cover;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .add-candidate-form {
                padding: 20px;
                margin: 10px;
                margin-bottom: 15px;
            }
        }
       

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--text-color);
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--secondary-color);
            outline: none;
        }

        .submit-btn,
        .delete-btn,
        .return-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .submit-btn {
            background-color: var(--success-color);
            color: white;
        }

        .delete-btn {
            background-color: var(--accent-color);
            color: white;
        }

        .return-btn {
            background-color: var(--text-color);
            color: white;
            text-decoration: none;
        }

        .submit-btn:hover,
        .delete-btn:hover,
        .return-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            background-color: var(--success-color);
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .candidates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .candidate-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .candidate-card:hover {
            transform: translateY(-5px);
        }

        .candidate-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .candidate-info {
            padding: 20px;
        }

        .candidate-info h3 {
            margin: 0 0 10px 0;
            color: var(--text-color);
        }

        .candidate-position {
            color: var(--secondary-color);
            margin-bottom: 10px;
            font-weight: 500;
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
        <a href="dashboard.php" class="return-btn">
            <i class="fas fa-arrow-left"></i> Return to Dashboard
        </a>

        <div class="header">
            <h1><i class="fas fa-user-tie"></i> Candidate Management</h1>
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

        <div class="add-candidate-form">
            <h2><i class="fas fa-plus-circle"></i> Add New Candidate</h2>
            <form method="POST" action="candidate_management.php" enctype="multipart/form-data" id="candidateForm">
                <input type="hidden" name="action" value="add_candidate">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" name="full_name" id="full_name" required minlength="2" title="Enter candidate's full name">
                        <div class="error-message">Please enter a valid name (minimum 2 characters)</div>
                    </div>
                    <div class="form-group">
                        <label for="position">Position</label>
                        <select name="position" id="position" required title="Select candidate's position">
                            <option value="">Select Position</option>
                            <option value="President">President</option>
                            <option value="Vice President">Vice President</option>
                            <option value="Secretary">Secretary</option>
                            <option value="Treasurer">Treasurer</option>
                        </select>
                        <div class="error-message">Please select a position</div>
                    </div>
                    <div class="form-group">
                        <label for="image">Profile Image</label>
                        <input type="file" name="image" id="image" accept="image/*" required title="Upload candidate's profile image">
                        <img id="imagePreview" class="preview-image" alt="Image preview" title="Preview of uploaded image">
                        <div class="error-message">Please select an image file</div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="platform">Platform/Description</label>
                    <textarea name="platform" id="platform" required minlength="10" title="Enter candidate's platform or description"></textarea>
                    <div class="error-message">Please enter a platform (minimum 10 characters)</div>
                </div>
                <button type="submit" class="submit-btn" id="submitBtn" title="Add new candidate">
                    <i class="fas fa-plus"></i> Add Candidate
                </button>
            </form>
        </div>

        <div class="candidates-grid">
            <?php
            // Group candidates by position
            $positions = ['President', 'Vice President', 'Secretary', 'Treasurer'];
            foreach ($positions as $currentPosition):
                $positionCandidates = array_filter($candidates, function($candidate) use ($currentPosition) {
                    return $candidate['position'] === $currentPosition;
                });
                
                if (!empty($positionCandidates)):
            ?>
                <div class="position-section">
                    <h2 class="position-title"><?php echo htmlspecialchars($currentPosition); ?></h2>
                    <div class="position-candidates">
                        <?php foreach ($positionCandidates as $candidate): ?>
                        <div class="candidate-card">
                            <img src="<?php echo htmlspecialchars($candidate['image_path']); ?>" 
                                 alt="Profile photo of <?php echo htmlspecialchars($candidate['full_name']); ?>" 
                                 title="<?php echo htmlspecialchars($candidate['full_name']); ?>'s profile photo"
                                 class="candidate-image">
                            <div class="candidate-info">
                                <h3><?php echo htmlspecialchars($candidate['full_name']); ?></h3>
                                <div class="candidate-position"><?php echo htmlspecialchars($candidate['position']); ?></div>
                                <p><?php echo htmlspecialchars($candidate['platform']); ?></p>
                                <form method="POST" action="candidate_management.php" style="margin-top: 10px;">
                                    <input type="hidden" name="action" value="delete_candidate">
                                    <input type="hidden" name="candidate_id" value="<?php echo $candidate['id']; ?>">
                                    <button type="submit" class="delete-btn" 
                                            onclick="return confirm('Are you sure you want to delete this candidate?')"
                                            title="Delete <?php echo htmlspecialchars($candidate['full_name']); ?>">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php 
                endif;
            endforeach; 
            ?>
        </div>

        <style>
            .position-section {
                margin-bottom: 40px;
                width: 100%;
            }

            .position-title {
                color: var(--primary-color);
                font-size: 24px;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 2px solid var(--secondary-color);
            }

            .position-candidates {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 20px;
            }

            .candidates-grid {
                display: flex;
                flex-direction: column;
                gap: 40px;
            }
        </style>
    </div>
</body>
</html>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('candidateForm');
            const imageInput = document.getElementById('imageInput');
            const imagePreview = document.getElementById('imagePreview');
            const submitBtn = document.getElementById('submitBtn');

            // Image preview
            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.style.display = 'block';
                        imagePreview.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Form validation
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                let isValid = true;
                
                // Reset previous errors
                form.querySelectorAll('.form-group').forEach(group => {
                    group.classList.remove('error');
                    group.querySelector('.error-message').style.display = 'none';
                });

                // Validate each field
                const name = form.querySelector('[name="full_name"]');
                const position = form.querySelector('[name="position"]');
                const platform = form.querySelector('[name="platform"]');
                const image = form.querySelector('[name="image"]');

                if (name.value.length < 2) {
                    showError(name, 'Please enter a valid name');
                    isValid = false;
                }

                if (!position.value) {
                    showError(position, 'Please select a position');
                    isValid = false;
                }

                if (platform.value.length < 10) {
                    showError(platform, 'Platform must be at least 10 characters');
                    isValid = false;
                }

                if (!image.files[0] && !image.hasAttribute('data-has-file')) {
                    showError(image, 'Please select an image');
                    isValid = false;
                }

                if (isValid) {
                    submitBtn.classList.add('loading');
                    form.submit();
                }
            });

            function showError(element, message) {
                const group = element.closest('.form-group');
                group.classList.add('error');
                const errorMessage = group.querySelector('.error-message');
                errorMessage.textContent = message;
                errorMessage.style.display = 'block';
            }

            // Show success animation if there's a success message
            const message = document.querySelector('.message');
            if (message) {
                message.classList.add('success-animation');
                setTimeout(() => {
                    message.classList.remove('success-animation');
                }, 500);
            }
        });
    </script>