<?php
session_start();

// If user is already logged in, redirect to dashboard
if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>E-Voting System Login</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            background: #ffffff;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .login-container {
            position: relative;
            z-index: 2;
            background: #ffffff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            margin: auto;
        }

        .logo {
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
            object-fit: contain;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .login-container h2 {
            color: #242b42;
            margin-bottom: 30px;
            font-size: 2.2em;
        }

        .form-group {
            position: relative;
            margin-bottom: 20px;
        }

        .form-group input {
            width: 100%;
            padding: 15px 25px;
            border: 1px solid #242b42;
            background: #ffffff;
            border-radius: 30px;
            color: #242b42;
            font-size: 16px;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        /* Add new styles for password toggle */
        .password-toggle {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #242b42;
        }

        .password-toggle:hover {
            color: #4a69dd;
        }

        .form-group input:focus {
            border-color: #4a69dd;
            outline: none;
            box-shadow: 0 0 0 2px rgba(74, 105, 221, 0.2);
        }

        .form-group input::placeholder {
            color: #8e9ab4;
        }

        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #4a69dd 0%, #242b42 100%);
            border: none;
            border-radius: 30px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 105, 221, 0.4);
            background: linear-gradient(135deg, #5a79ed 0%, #343d5c 100%);
        }

        .register-link {
            margin-top: 20px;
            text-align: center;
        }

        .register-link p {
            color: #242b42;
        }

        .register-link a {
            color: #4a69dd;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-block;
            padding: 10px 20px;
            border: 2px solid #4a69dd;
            border-radius: 30px;
            margin-top: 15px;
        }

        .register-link a:hover {
            background: #4a69dd;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 105, 221, 0.2);
        }

        .error-message {
            color: #ff6b6b;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
            background-color: rgba(255, 107, 107, 0.1);
            display: none;
        }

        .error-message.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="images/logo.png" alt="E-Voting Logo" class="logo">
        <h2>JRMSU E-Voting System</h2>
        <?php if(isset($_GET['error']) && $_GET['error'] == 'invalid_credentials'): ?>
        <div class="error-message show">
            Invalid ID number or password. Please try again.
        </div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="form-group">
                <input type="text" name="id_number" placeholder="ID Number" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <i class="password-toggle fas fa-eye" id="togglePassword"></i>
            </div>
            <button type="submit">Login</button>
        </form>
        <div class="register-link">
            <p>Don't have an account?</p>
            <a href="register.php">Register Now</a>
        </div>
    </div>

    <script>
        // Clock update
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            document.getElementById('clock').textContent = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Number animation
        function animateNumbers() {
            const numbers = document.querySelectorAll('.number');
            numbers.forEach(number => {
                const targetValue = parseInt(number.getAttribute('data-value'));
                let currentValue = 0;
                const duration = 2000; // 2 seconds
                const steps = 60;
                const increment = targetValue / steps;
                const interval = duration / steps;

                const counter = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= targetValue) {
                        currentValue = targetValue;
                        clearInterval(counter);
                    }
                    number.textContent = Math.round(currentValue).toLocaleString();
                }, interval);
            });
        }

        // Trigger animation when page loads
        document.addEventListener('DOMContentLoaded', animateNumbers);
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>E-Voting System Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    

    <script>
        // Clock update
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            document.getElementById('clock').textContent = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Number animation
        function animateNumbers() {
            const numbers = document.querySelectorAll('.number');
            numbers.forEach(number => {
                const targetValue = parseInt(number.getAttribute('data-value'));
                let currentValue = 0;
                const duration = 2000; // 2 seconds
                const steps = 60;
                const increment = targetValue / steps;
                const interval = duration / steps;

                const counter = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= targetValue) {
                        currentValue = targetValue;
                        clearInterval(counter);
                    }
                    number.textContent = Math.round(currentValue).toLocaleString();
                }, interval);
            });
        }

        // Trigger animation when page loads
        document.addEventListener('DOMContentLoaded', animateNumbers);
    </script>
</body>
</html>
    </div>
</body>
</html>

    <script>
        // Add password toggle functionality
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            // Toggle the password visibility
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Toggle the eye icon
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Clock update
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            document.getElementById('clock').textContent = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Number animation
        function animateNumbers() {
            const numbers = document.querySelectorAll('.number');
            numbers.forEach(number => {
                const targetValue = parseInt(number.getAttribute('data-value'));
                let currentValue = 0;
                const duration = 2000; // 2 seconds
                const steps = 60;
                const increment = targetValue / steps;
                const interval = duration / steps;

                const counter = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= targetValue) {
                        currentValue = targetValue;
                        clearInterval(counter);
                    }
                    number.textContent = Math.round(currentValue).toLocaleString();
                }, interval);
            });
        }

        // Trigger animation when page loads
        document.addEventListener('DOMContentLoaded', animateNumbers);
    </script>
</body>
</html>
