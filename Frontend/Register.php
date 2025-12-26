<?php
session_start();

// If already logged in, redirect to index
if(isset($_SESSION['user'])){
    header('location:loanpayback.php');
    exit();
}

include('connection.php');

$error_message = '';
$success_message = '';

if(isset($_POST['register'])){
    $fullname = mysqli_real_escape_string($con, trim($_POST['fullname']));
    $username = mysqli_real_escape_string($con, trim($_POST['username']));
    $email = mysqli_real_escape_string($con, trim($_POST['email']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    if(!empty($fullname) && !empty($username) && !empty($email) && !empty($password)){
        
        // Validate email
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $error_message = 'Invalid email format';
        }
        // Check password match
        elseif($password !== $confirm_password){
            $error_message = 'Passwords do not match';
        }
        // Check password length
        elseif(strlen($password) < 6){
            $error_message = 'Password must be at least 6 characters';
        }
        else {
            // Check if username already exists
            $check_user = "SELECT * FROM users WHERE username = '$username'";
            $check_query = mysqli_query($con, $check_user);
            
            if(mysqli_num_rows($check_query) > 0){
                $error_message = 'Username already exists';
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $sql = "INSERT INTO users (fullname, username, email, password) VALUES ('$fullname', '$username', '$email', '$hashed_password')";
                $query = mysqli_query($con, $sql);
                
                if($query){
                    $success_message = 'Registration successful! You can now login.';
                    // Optionally auto-login the user
                    // $_SESSION['user'] = $username;
                    // $_SESSION['fullname'] = $fullname;
                    // header('location:index.php');
                } else {
                    $error_message = 'Registration failed. Please try again.';
                }
            }
        }
    } else {
        $error_message = 'Please fill in all fields';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Loan Payback Prediction System</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #FF4655;
            --secondary: #2D3561;
            --accent: #00D9FF;
            --dark: #1a1a2e;
            --light: #F8FAFC;
            --success: #00E5A0;
            --shadow: rgba(255, 70, 85, 0.25);
        }

        body {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            position: relative;
            overflow-x: hidden;
            padding: 40px 20px;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255, 70, 85, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(0, 217, 255, 0.06) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(45, 53, 97, 0.1) 0%, transparent 60%);
            animation: gradientShift 15s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.05); }
            66% { transform: translate(-30px, 30px) scale(0.98); }
        }

        body::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                linear-gradient(rgba(255, 70, 85, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 217, 255, 0.02) 1px, transparent 1px);
            background-size: 50px 50px;
            pointer-events: none;
        }

        .floating-element {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            animation: float 20s ease-in-out infinite;
        }

        .element-1 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, var(--primary), transparent);
            top: -100px;
            right: -100px;
            animation-delay: 0s;
        }

        .element-2 {
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, var(--accent), transparent);
            bottom: -100px;
            left: -100px;
            animation-delay: 7s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            33% { transform: translate(40px, -60px); }
            66% { transform: translate(-50px, 40px); }
        }

        .register-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 380px;
        }

        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(30px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            padding: 35px 30px;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 1);
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 28px;
        }

        .logo-icon {
            width: 65px;
            height: 65px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            box-shadow: 0 10px 30px var(--shadow);
        }

        .logo-icon i {
            font-size: 30px;
            color: white;
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 4px;
            letter-spacing: -0.02em;
        }

        .subtitle {
            font-size: 0.88rem;
            color: rgba(26, 26, 46, 0.6);
            font-weight: 400;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 11px;
            margin-bottom: 20px;
            font-size: 0.88rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.5s ease;
        }

        .alert-error {
            background: rgba(255, 70, 85, 0.1);
            border: 1px solid rgba(255, 70, 85, 0.3);
            color: var(--primary);
        }

        .alert-success {
            background: rgba(0, 229, 160, 0.1);
            border: 1px solid rgba(0, 229, 160, 0.3);
            color: var(--success);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert i {
            font-size: 1.1rem;
        }

        .form-group {
            margin-bottom: 18px;
            position: relative;
        }

        label {
            display: block;
            color: rgba(26, 26, 46, 0.85);
            font-size: 0.88rem;
            font-weight: 600;
            margin-bottom: 8px;
            letter-spacing: 0.01em;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(26, 26, 46, 0.4);
            font-size: 0.95rem;
            transition: color 0.3s;
        }

        input {
            width: 100%;
            padding: 13px 14px 13px 44px;
            background: rgba(255, 255, 255, 0.8);
            border: 1.5px solid rgba(0, 0, 0, 0.1);
            border-radius: 11px;
            color: var(--dark);
            font-size: 0.92rem;
            font-family: 'Outfit', sans-serif;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        input::placeholder {
            color: rgba(26, 26, 46, 0.4);
        }

        input:focus {
            outline: none;
            background: rgba(255, 255, 255, 1);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(255, 70, 85, 0.1);
        }

        input:focus + .input-icon {
            color: var(--primary);
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(26, 26, 46, 0.5);
            cursor: pointer;
            font-size: 0.95rem;
            padding: 6px;
            transition: color 0.3s;
        }

        .password-toggle:hover {
            color: var(--dark);
        }

        .btn-register {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border: none;
            border-radius: 11px;
            color: white;
            font-size: 0.92rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            box-shadow: 0 10px 30px var(--shadow);
            position: relative;
            overflow: hidden;
            margin-top: 8px;
        }

        .btn-register::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--accent), var(--primary));
            opacity: 0;
            transition: opacity 0.3s;
        }

        .btn-register:hover::before {
            opacity: 1;
        }

        .btn-register span {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(255, 70, 85, 0.4);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: rgba(26, 26, 46, 0.6);
            font-size: 0.88rem;
        }

        .login-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .login-link a:hover {
            color: var(--primary);
        }

        .password-strength {
            margin-top: 6px;
            font-size: 0.82rem;
            display: none;
        }

        .strength-bar {
            height: 3px;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            width: 0;
            transition: all 0.3s;
            border-radius: 2px;
        }

        @media (max-width: 768px) {
            body {
                padding: 20px 10px;
            }

            .register-container {
                max-width: 100%;
            }

            .register-card {
                padding: 32px 26px;
                border-radius: 18px;
            }

            .logo-icon {
                width: 60px;
                height: 60px;
                margin-bottom: 12px;
            }

            .logo-icon i {
                font-size: 28px;
            }

            h1 {
                font-size: 1.6rem;
            }

            .subtitle {
                font-size: 0.85rem;
            }

            .form-group {
                margin-bottom: 16px;
            }

            input {
                padding: 12px 14px 12px 42px;
                font-size: 0.9rem;
            }

            .input-icon {
                left: 13px;
                font-size: 0.9rem;
            }

            .password-toggle {
                right: 13px;
            }

            .btn-register {
                padding: 13px;
                font-size: 0.9rem;
            }
        }

        /* Mobile phones */
        @media (max-width: 480px) {
            body {
                padding: 15px 10px;
            }

            .register-card {
                padding: 28px 22px;
                border-radius: 16px;
            }

            .logo-section {
                margin-bottom: 24px;
            }

            .logo-icon {
                width: 55px;
                height: 55px;
                border-radius: 14px;
            }

            .logo-icon i {
                font-size: 26px;
            }

            h1 {
                font-size: 1.45rem;
            }

            .subtitle {
                font-size: 0.82rem;
            }

            .form-group {
                margin-bottom: 14px;
            }

            label {
                font-size: 0.85rem;
                margin-bottom: 7px;
            }

            input {
                padding: 11px 12px 11px 40px;
                font-size: 0.88rem;
                border-radius: 10px;
            }

            .input-icon {
                left: 12px;
                font-size: 0.88rem;
            }

            .password-toggle {
                right: 12px;
                font-size: 0.9rem;
            }

            .btn-register {
                padding: 12px;
                font-size: 0.88rem;
                border-radius: 10px;
                margin-top: 6px;
            }

            .login-link {
                margin-top: 18px;
                font-size: 0.85rem;
            }

            .alert {
                padding: 10px 14px;
                font-size: 0.85rem;
            }

            /* Reduce floating element sizes on mobile */
            .element-1 {
                width: 250px;
                height: 250px;
            }

            .element-2 {
                width: 220px;
                height: 220px;
            }
        }

        /* Extra small devices */
        @media (max-width: 360px) {
            .register-card {
                padding: 25px 18px;
            }

            h1 {
                font-size: 1.35rem;
            }

            .logo-icon {
                width: 50px;
                height: 50px;
            }

            .logo-icon i {
                font-size: 24px;
            }

            input {
                padding: 10px 10px 10px 38px;
                font-size: 0.85rem;
            }

            .input-icon {
                left: 11px;
            }

            .password-toggle {
                right: 11px;
            }

            .btn-register {
                padding: 11px;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div class="floating-element element-1"></div>
    <div class="floating-element element-2"></div>

    <div class="register-container">
        <div class="register-card">
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="fa fa-money"></i>
                </div>
                <h1>Create Account</h1>
                <p class="subtitle">Join the Loan Payback Prediction System</p>
            </div>

            <?php if(!empty($error_message)): ?>
            <div class="alert alert-error">
                <i class="fa fa-exclamation-triangle"></i>
                <span><?php echo htmlspecialchars($error_message); ?></span>
            </div>
            <?php endif; ?>

            <?php if(!empty($success_message)): ?>
            <div class="alert alert-success">
                <i class="fa fa-check-circle"></i>
                <span><?php echo htmlspecialchars($success_message); ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <div class="input-wrapper">
                        <input 
                            type="text" 
                            id="fullname" 
                            name="fullname" 
                            placeholder="Enter your full name" 
                            required
                            value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>"
                        >
                        <i class="fa fa-user input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            placeholder="Choose a username" 
                            required
                            value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                        >
                        <i class="fa fa-at input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="Enter your email" 
                            required
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        >
                        <i class="fa fa-envelope input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Create a password" 
                            required
                        >
                        <i class="fa fa-lock input-icon"></i>
                        <button type="button" class="password-toggle" onclick="togglePassword('password', 'toggleIcon1')">
                            <i class="fa fa-eye" id="toggleIcon1"></i>
                        </button>
                    </div>
                    <div class="password-strength" id="strengthIndicator">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <span id="strengthText"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Confirm your password" 
                            required
                        >
                        <i class="fa fa-lock input-icon"></i>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                            <i class="fa fa-eye" id="toggleIcon2"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" name="register" class="btn-register">
                    <span>
                        Create Account
                        <i class="fa fa-arrow-right"></i>
                    </span>
                </button>
            </form>

            <div class="login-link">
                Already have an account? <a href="i
                ndex.php">Sign in here</a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const strengthIndicator = document.getElementById('strengthIndicator');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            strengthIndicator.style.display = 'block';
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            const colors = ['#FF4655', '#FF9500', '#FFD700', '#00D9FF', '#00E5A0'];
            const texts = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
            const widths = ['20%', '40%', '60%', '80%', '100%'];
            
            strengthFill.style.width = widths[strength - 1] || '0';
            strengthFill.style.background = colors[strength - 1] || '#FF4655';
            strengthText.textContent = texts[strength - 1] || '';
            strengthText.style.color = colors[strength - 1] || '#FF4655';
        });
    </script>
</body>
</html>