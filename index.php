<?php
session_start();
include("php/config.php");

$error_message = "";
$flash_button = false;

// Initialize failed attempts counter
if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['failed_attempts'] = 0;
}
if (!isset($_SESSION['last_attempt_username'])) {
    $_SESSION['last_attempt_username'] = '';
}

// Function to log suspicious login attempts
function logSuspiciousAttempt($con, $identifier, $attempted_username, $ip_address, $reason) {
    $identifier = mysqli_real_escape_string($con, $identifier);
    $attempted_username = mysqli_real_escape_string($con, $attempted_username);
    $ip_address = mysqli_real_escape_string($con, $ip_address);
    $reason = mysqli_real_escape_string($con, $reason);
    
    // Insert into admin_logs table for security monitoring
    $query = "INSERT INTO admin_logs (admin_id, action, details, ip_address) 
              VALUES ('$identifier', 'suspicious_login_attempt', '$reason - Attempted username: $attempted_username', '$ip_address')";
    mysqli_query($con, $query);
}

if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Reset counter if username changed
    if ($_SESSION['last_attempt_username'] !== $username) {
        $_SESSION['failed_attempts'] = 0;
        $_SESSION['last_attempt_username'] = $username;
    }

    // Check if user came from idenify.php with a verified identifier
    if (isset($_SESSION['identifier'])) {
        $identifier = $_SESSION['identifier'];
        
        // Verify that the username matches the identifier
        $verify_query = mysqli_query($con, "SELECT * FROM users WHERE Username='$username' AND Reg_Number='$identifier'");
        
        if (mysqli_num_rows($verify_query) == 0) {
            // Username doesn't match the verified identifier - LOG IMMEDIATELY
            logSuspiciousAttempt($con, $identifier, $username, $ip_address, "Username mismatch: Attempted to login with wrong username for verified account '$identifier'");
            $error_message = "User not found";
            $flash_button = true;
            $_SESSION['failed_attempts']++;
        } else {
            // Username matches, proceed with password verification
            $result = mysqli_query($con, "SELECT * FROM users WHERE Username='$username'") or die("Select Error");
            
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                
                if (password_verify($password, $row['Password']) || $password === $row['Password']) {
                    $_SESSION['valid'] = true;
                    $_SESSION['username'] = $row['Username'];
                    $_SESSION['id'] = $row['Id'];
                    $_SESSION['failed_attempts'] = 0; // Reset on success
                    $_SESSION['last_attempt_username'] = '';
                    unset($_SESSION['identifier']); // Clear the identifier session
                    header("Location: home.php");
                    exit;
                } else {
                    // Wrong password - increment counter and log after 3 attempts
                    $_SESSION['failed_attempts']++;
                    
                    if ($_SESSION['failed_attempts'] >= 3) {
                        logSuspiciousAttempt($con, $identifier, $username, $ip_address, "Multiple failed password attempts (3+) for verified account");
                    }
                    
                    $error_message = "User not found";
                    $flash_button = true;
                }
            }
        }
    } else {
        // Normal login without identifier verification
        $result = mysqli_query($con, "SELECT * FROM users WHERE Username='$username'") or die("Select Error");
        
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            
            if (password_verify($password, $row['Password']) || $password === $row['Password']) {
                $_SESSION['valid'] = true;
                $_SESSION['username'] = $row['Username'];
                $_SESSION['id'] = $row['Id'];
                $_SESSION['failed_attempts'] = 0; // Reset on success
                $_SESSION['last_attempt_username'] = '';
                header("Location: home.php");
                exit;
            } else {
                // Wrong password - increment counter and log after 3 attempts
                $_SESSION['failed_attempts']++;
                
                if ($_SESSION['failed_attempts'] >= 3) {
                    logSuspiciousAttempt($con, $row['Reg_Number'], $username, $ip_address, "Multiple failed password attempts (3+) without prior verification");
                }
                
                $error_message = "User not found";
                $flash_button = true;
            }
        } else {
            $_SESSION['failed_attempts']++;
            
            // Log after 3 failed attempts for non-existent users
            if ($_SESSION['failed_attempts'] >= 3) {
                logSuspiciousAttempt($con, 'UNKNOWN', $username, $ip_address, "Multiple failed login attempts (3+) for non-existent user");
            }
            
            $error_message = "User not found";
            $flash_button = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Login - Smart Tag Asset Management</title>
    <style>
        /* Color Palette */
        :root {
            --primary-dark: #4b648d;
            --primary-light: #e7fbf9;
            --accent-teal: #41737c;
            --text-dark: #2c3e50;
            --text-light: #ffffff;
            --shadow: rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        body {
            background: linear-gradient(-45deg, #4fc3f7, #23272e, #4fc3f7, #23272e);
            background-size: 400% 400%;
            animation: gradientBG 12s ease infinite;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 450px;
            padding: 2.5rem;
            position: relative;
            animation: fadeIn 0.8s ease-out;
        }

        .box.form-box {
            width: 100%;
        }

        header {
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        header .title {
            font-size: 2.5rem;
            color: var(--primary-dark);
        }

        header .subtitle {
            font-size: 1.5rem;
            color: var(--accent-teal);
            font-weight: 600;
        }

        header i {
            color: var(--accent-teal);
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }

        .field {
            margin-bottom: 1.5rem;
        }

        .field label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.95rem;
        }

        .field input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.9);
        }

        .field input:focus {
            border-color: var(--accent-teal);
            outline: none;
            box-shadow: 0 0 0 3px rgba(65, 115, 124, 0.1);
            background: white;
        }

        .btn {
            width: 100%;
            padding: 1rem;
            background: #4fc3f7 !important;
            border: none;
            border-radius: var(--border-radius);
            color: #23272e !important;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn:hover {
            background: #29b6f6 !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 195, 247, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn.flash-red {
            animation: flashRed 0.6s ease-in-out;
        }

        @keyframes flashRed {
            0%, 100% {
                background: #4fc3f7;
            }
            25%, 75% {
                background: #f44336;
                box-shadow: 0 0 20px rgba(244, 67, 54, 0.6);
            }
            50% {
                background: #d32f2f;
                box-shadow: 0 0 30px rgba(211, 47, 47, 0.8);
            }
        }

        .links {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.95rem;
            color: var(--text-dark);
        }

        .links a {
            color: var(--accent-teal);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .links a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .message {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 500;
            animation: slideDown 0.5s ease-out;
        }

        .message.error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #f44336;
        }

        .message.success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #4caf50;
        }

        /* Dark Mode */
        body.dark-mode {
            background: linear-gradient(-45deg, #23272e, #1a1a1a, #23272e, #1a1a1a);
        }

        body.dark-mode .container {
            background: rgba(30, 30, 30, 0.95);
        }

        body.dark-mode header,
        body.dark-mode .field label,
        body.dark-mode .links {
            color: var(--text-light);
        }

        body.dark-mode header .title,
        body.dark-mode header .subtitle {
            color: var(--text-light);
        }

        body.dark-mode header .subtitle {
            color: var(--primary-light);
        }

        body.dark-mode .field input {
            background: rgba(45, 45, 45, 0.9);
            border-color: rgba(255, 255, 255, 0.2);
            color: var(--text-light);
        }

        body.dark-mode .field input:focus {
            background: rgba(45, 45, 45, 1);
            border-color: var(--accent-teal);
        }

        body.dark-mode .message.error {
            background: rgba(198, 40, 40, 0.2);
            color: #ff8a80;
            border-left-color: #ff8a80;
        }

        body.dark-mode .message.success {
            background: rgba(46, 125, 50, 0.2);
            color: #69f0ae;
            border-left-color: #69f0ae;
        }

        body.dark-mode .btn {
            background: #4fc3f7 !important;
            color: #23272e !important;
        }

        body.dark-mode .btn:hover {
            background: #29b6f6 !important;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 2rem 1.5rem;
            }

            header {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <header>
                <i class="fas fa-user-check"></i>
                <div class="title">STAMS</div>
                <div class="subtitle">Login</div>
            </header>
            
            <?php if (!empty($error_message)): ?>
                <div class="message error">
                    <p><?php echo $error_message; ?></p>
                </div>
            <?php endif; ?>
            
            <form action="" method="post">
                <div class="field input">
                    <label for="username"><i class="fas fa-user"></i> Username</label>
                    <input type="text" name="username" id="username" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" name="password" id="password" autocomplete="off" required>
                </div>

                <div class="field">
                    <input type="submit" name="submit" value="Login" class="btn <?php echo $flash_button ? 'flash-red' : ''; ?>">
                </div>
            </form>
        </div>
    </div>

    <?php if ($flash_button): ?>
    <script>
        // Remove the flash-red class after animation completes
        setTimeout(function() {
            const btn = document.querySelector('.btn');
            if (btn) {
                btn.classList.remove('flash-red');
            }
        }, 600);
    </script>
    <?php endif; ?>

</body>
</html>