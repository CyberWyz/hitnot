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
    <link rel="stylesheet" href="style/style.css">
    <title>Admin Login</title>
    <style>
        .admin-login-container {
            max-width: 400px;
            margin: 50px auto;
        }
        .admin-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .admin-header h1 {
            color: #333;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .admin-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.8em;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container admin-login-container">
        <div class="admin-header">
            <h1>Admin Portal</h1>
            <p>Secure access to system administration</p>
        </div>
        
        <div class="box form-box">
            <header>Administrator Login</header>
            
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form action="" method="post">
                <div class="field input">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" autocomplete="off" required>
                </div>

                <div class="field">
                    <input type="submit" name="submit" value="Login" class="btn">
                </div>
            </form>
        </div>
        
        <div class="admin-footer">
            <p>Asset Management System - Admin Portal</p>
        </div>
    </div>
</body>
</html>