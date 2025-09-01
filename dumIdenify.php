<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Identification - Smart Tag Asset Management</title>
    <style>
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
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        @keyframes gradientBG {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }
        .container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 450px;
            padding: 30px;
        }
        .box form-box {
            width: 100%;
        }
        header {
            text-align: center;
            font-size: 28px;
            font-weight: 600;
            color: #23272e;
            margin-bottom: 25px;
        }
        .field {
            margin-bottom: 20px;
        }
        .field label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #23272e;
        }
        .field input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border 0.3s ease;
        }
        .field input:focus {
            border-color: #4fc3f7;
            outline: none;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #4fc3f7;
            border: none;
            border-radius: 6px;
            color: #23272e;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background: #29b6f6;
        }
        .links {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        .links a {
            color: #4fc3f7;
            text-decoration: none;
        }
        .links a:hover {
            text-decoration: underline;
        }
        .message {
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
        .error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }
        .success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <header>User Identification</header>
            
            <?php
            // Enable error reporting for debugging
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            
            echo "<!-- Debug: PHP is working -->";
            
            // Check if config file exists
            $config_file = "php/config.php";
            if (!file_exists($config_file)) {
                echo "<div class='message error'><p>Error: Config file not found at $config_file</p></div>";
            } else {
                // Try to include the config file
                try {
                    include($config_file);
                    echo "<!-- Debug: Config file included successfully -->";
                    
                    // Simple test query to check database connection
                    if ($con) {
                        echo "<!-- Debug: Database connection established -->";
                    } else {
                        echo "<div class='message error'><p>Error: Database connection failed</p></div>";
                    }
                } catch (Exception $e) {
                    echo "<div class='message error'><p>Error including config file: " . $e->getMessage() . "</p></div>";
                }
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
                echo "<!-- Debug: Form submitted -->";
                
                $identifier = mysqli_real_escape_string($con, $_POST['identifier']);
                $email = mysqli_real_escape_string($con, $_POST['email']);
                
                echo "<!-- Debug: Identifier: $identifier, Email: $email -->";
                
                // Check if user exists in students table
                $student_query = mysqli_query($con, "SELECT * FROM users WHERE Reg_Number='$identifier' AND Email='$email'");
                
                if ($student_query && mysqli_num_rows($student_query) > 0) {
                    echo "<!-- Debug: Student found -->";
                    session_start();
                    $_SESSION['identifier'] = $identifier;
                    header("Location: index.php");
                    exit;
                }
                
                // Check if user exists in security personnel table
                $security_query = mysqli_query($con, "SELECT * FROM scpersonnel WHERE officer_id='$identifier' AND email='$email' AND status='approved'");
                
                if ($security_query && mysqli_num_rows($security_query) > 0) {
                    echo "<!-- Debug: Security personnel found -->";
                    session_start();
                    $_SESSION['identifier'] = $identifier;
                    header("Location: sclogin.php");
                    exit;
                }
                
                // If no user found in either table
                echo "<div class='message error'><p>No account found with these credentials!</p></div>";
                echo "<div class='message error'><p>Please check your registration/work number and email, or register if you don't have an account.</p></div>";
            }
            ?>
            
            <form action="" method="post">
                <div class="field input">
                    <label for="identifier">Registration/Work Number</label>
                    <input type="text" name="identifier" id="identifier" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" autocomplete="off" required>
                </div>

                <div class="field">
                    <input type="submit" name="submit" value="Continue" class="btn">
                </div>

                <div class="links">
                    Don't have an account? <a href="reg1.php">Sign Up here !</a><br>
                    
                </div>
            </form>
        </div>
    </div>
</body>
</html>