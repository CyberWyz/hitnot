<?php
session_start();
include("php/config.php");

$error = "";

// Clear admin session if coming from welcome page
if (isset($_GET['fresh']) && $_GET['fresh'] == 1) {
    unset($_SESSION['admin_valid']);
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
}

// Process login form
if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    
    // Check if admin table exists, if not create it with default admin account
    $check_table = mysqli_query($con, "SHOW TABLES LIKE 'admin_users'");
    
    if (mysqli_num_rows($check_table) == 0) {
        // Create admin table
        $create_table = mysqli_query($con, "CREATE TABLE admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            last_login DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Create default admin account (username: admin, password: admin)
        // In production, use a strong password and password_hash()
        $default_password = 'admin';
        $insert_admin = mysqli_query($con, "INSERT INTO admin_users (username, password, email) 
                                          VALUES ('admin', '$default_password', 'admin@example.com')");
    }
    
    // Verify admin credentials
    $query = mysqli_query($con, "SELECT * FROM admin_users WHERE username='$username'");
    
    if (mysqli_num_rows($query) > 0) {
        $admin = mysqli_fetch_assoc($query);
        
        // In production, use password_verify() for hashed passwords
        if ($password === $admin['password']) {
            // Update last login time
            mysqli_query($con, "UPDATE admin_users SET last_login = NOW() WHERE id = {$admin['id']}");
            
            // Set session variables
            $_SESSION['admin_valid'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            
            // Log the successful login
            $admin_id = $admin['id'];
            mysqli_query($con, "INSERT INTO admin_logs (admin_id, action, details) 
                              VALUES ($admin_id, 'login', 'Admin logged in successfully')");
            
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Admin user not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Admin Login - STAMS</title>
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

        .header-section {
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

        .header-section .title {
            font-size: 2.5rem;
            color: var(--primary-dark);
        }

        .header-section .subtitle {
            font-size: 1.5rem;
            color: var(--accent-teal);
            font-weight: 600;
        }

        .header-section i {
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

        body.dark-mode .header-section,
        body.dark-mode .field label {
            color: var(--text-light);
        }

        body.dark-mode .header-section .title,
        body.dark-mode .header-section .subtitle {
            color: var(--text-light);
        }

        body.dark-mode .header-section .subtitle {
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

            .header-section {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-section">
            <i class="fas fa-user-shield"></i>
            <div class="title">STAMS</div>
            <div class="subtitle">Administrator Portal</div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="message error">
                <p><?php echo $error; ?></p>
            </div>
        <?php endif; ?>

        <form action="" method="post">
            <div class="field">
                <label for="username"><i class="fas fa-user"></i> Username</label>
                <input type="text" name="username" id="username" autocomplete="off" required>
            </div>

            <div class="field">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="password" id="password" autocomplete="off" required>
            </div>

            <div class="field">
                <input type="submit" name="submit" value="Login" class="btn">
            </div>
        </form>
    </div>
</body>
</html>