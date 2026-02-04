<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>E-Voting System Registration</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 2vh 0;
            min-height: 100vh;
            background: #ffffff;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            position: relative;
            overflow-y: auto;
        }

        .register-container {
            position: relative;
            z-index: 2;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(74, 105, 221, 0.2);
            width: 100%;
            max-width: 450px;
            text-align: center;
            margin: auto;
            border: 2px solid #4a69dd;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
            object-fit: contain;
            display: block;
        }

        .register-container h2 {
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
            padding: 15px 45px;
            border: 1px solid #242b42;
            background: white;
            border-radius: 30px;
            color: #242b42;
            font-size: 16px;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4a69dd;
            box-shadow: 0 0 5px rgba(74, 105, 221, 0.3);
        }

        .form-group input[type="password"] {
            padding-right: 50px;
        }

        .form-group i.icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #4a69dd;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #4a69dd;
            cursor: pointer;
            padding: 5px;
            z-index: 2;
            background: none;
            border: none;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .toggle-password:hover {
            color: #242b42;
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

        .login-link {
            margin-top: 20px;
            text-align: center;
        }

        .login-link a {
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

        .login-link a:hover {
            background: #4a69dd;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 105, 221, 0.2);
        }

        .message {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
            display: none;
        }

        .error-message {
            color: #ff6b6b;
            background-color: rgba(255, 107, 107, 0.1);
        }

        .success-message {
            color: #2ecc71;
            background-color: rgba(46, 204, 113, 0.1);
        }
    </style>
</head>
<body>
    <div class="register-container">
        <img src="images/logo.png" alt="E-Voting Logo" class="logo">
        <h2>JRMSU E-Voting Registration</h2>
        <div class="message error-message" id="errorMessage"></div>
        <div class="message success-message" id="successMessage"></div>
        <form action="process_register.php" method="POST" id="registrationForm">
            <div class="form-group">
                <i class="fas fa-id-card icon"></i>
                <input type="text" name="id_number" id="id_number" placeholder="ID Number" 
                    pattern="^[A-Z0-9]+$" 
                    title="ID number must contain only letters (A-Z) and numbers"
                    required>
            </div>
            <div class="form-group">
                <i class="fas fa-user icon"></i>
                <input type="text" name="username" id="username" placeholder="Full Name" required>
            </div>
            <div class="form-group">
                <i class="fas fa-envelope icon"></i>
                <input type="email" name="email" id="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <i class="fas fa-lock icon"></i>
                <input type="password" name="password" id="password" 
                    placeholder="Password" 
                    pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$"
                    title="Password must be at least 8 characters long and include both letters and numbers"
                    required>
                <i class="fas fa-eye toggle-password" id="togglePassword"></i>
            </div>
            <button type="submit">Register</button>
        </form>
        <div class="login-link">
            <p>Already have an account?</p>
            <a href="index.php">Login Here</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#password');
            const form = document.getElementById('registrationForm');
            const errorMessage = document.getElementById('errorMessage');
            const successMessage = document.getElementById('successMessage');

            // Password visibility toggle
            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });

            // Form validation
            form.addEventListener('submit', function(e) {
                const password = document.getElementById('password');
                const email = document.getElementById('email');
                const idNumber = document.getElementById('id_number');

                let isValid = true;
                errorMessage.style.display = 'none';
                errorMessage.textContent = '';

                // Validate ID Number
                if (!idNumber.value.match(/^[A-Z0-9]+$/)) {
                    errorMessage.textContent = 'ID number must contain only letters (A-Z) and numbers.';
                    isValid = false;
                }

                // Validate Email
                if (!email.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                    errorMessage.textContent = 'Please enter a valid email address.';
                    isValid = false;
                }

                // Validate Password
                if (!password.value.match(/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/)) {
                    errorMessage.textContent = 'Password must be at least 8 characters long and include both letters and numbers.';
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    errorMessage.style.display = 'block';
                }
            });

            // Handle URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            
            // Handle error messages
            const error = urlParams.get('error');
            if (error) {
                errorMessage.style.display = 'block';
                switch(error) {
                    case 'invalid_username':
                        errorMessage.textContent = 'Full Name must be 3-30 characters long and can only contain letters, numbers, and underscores.';
                        break;
                    case 'invalid_email':
                        errorMessage.textContent = 'Please enter a valid email address.';
                        break;
                    case 'id_exists':
                        errorMessage.textContent = 'This ID number is already registered.';
                        break;
                    case 'email_exists':
                        errorMessage.textContent = 'This email is already registered.';
                        break;
                    case 'registration_failed':
                        errorMessage.textContent = 'Registration failed. Please try again.';
                        break;
                    default:
                        errorMessage.textContent = 'An error occurred. Please try again.';
                }
            }

            // Handle success message
            const success = urlParams.get('success');
            if (success) {
                successMessage.style.display = 'block';
                successMessage.textContent = 'Registration successful! Redirecting to login page...';
                
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 2000);
            }
        });
    </script>
</body>
</html>


