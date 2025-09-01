<?php
include("php/config.php");

// Handle form submission
if (isset($_POST['submit'])) {
    $identifier = $_POST['identifier'];
    $name = $_POST['name'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Check if identifier starts with H2 (student) or 0F (security)
    if (substr($identifier, 0, 2) === 'H2') {
        // Student registration
        $reg_number = $identifier;
        $phone = $_POST['phone'];
        $school = $_POST['school'];
        
        // Handle file upload
        $myphoto = $_FILES['myphoto']['name'];
        $myphoto_tmp = $_FILES['myphoto']['tmp_name'];
        $upload_dir = "uploads/";
        $myphoto_path = $upload_dir . basename($myphoto);
        
        if (move_uploaded_file($myphoto_tmp, $myphoto_path)) {
            $verify_query = mysqli_query($con, "SELECT Email FROM users WHERE Email='$email'");
            
            if (mysqli_num_rows($verify_query) != 0) {
                $error_message = "This email is already used. Try another one.";
            } else {
                $query = "INSERT INTO users (Username, Lastname, Email, Reg_Number, Phone, School, Password, myphoto) 
                          VALUES ('$name', '$lastname', '$email', '$reg_number', '$phone', '$school', '$password', '$myphoto_path')";
                
                if (mysqli_query($con, $query)) {
                    $success_message = "Student registration successful!";
                    $show_login_button = true;
                    $login_page = "index.php";
                } else {
                    $error_message = "Error occurred: " . mysqli_error($con);
                }
            }
        } else {
            $error_message = "Error uploading photo. Please try again.";
        }
    } 
    elseif (substr($identifier, 0, 2) === '0F') {
        // Security personnel registration
        $officer_id = $identifier;
        
        $verify_query = mysqli_query($con, "SELECT email FROM scpersonnel WHERE email='$email'");
        
        if (mysqli_num_rows($verify_query) != 0) {
            $error_message = "This email is already used. Try another one.";
        } else {
            // Check if status column exists
            $check_column = mysqli_query($con, "SHOW COLUMNS FROM scpersonnel LIKE 'status'");
            if (mysqli_num_rows($check_column) == 0) {
                mysqli_query($con, "ALTER TABLE scpersonnel ADD COLUMN status VARCHAR(20) DEFAULT NULL");
            }
            
            $sql = "INSERT INTO scpersonnel(name, lastname, officer_id, email, password, status) 
                    VALUES('$name', '$lastname', '$officer_id', '$email', '$password', 'pending')";
            
            if (mysqli_query($con, $sql)) {
                $success_message = "Security personnel registration successful! Your account is pending approval by an administrator. You will be able to log in once your account is approved.";
                $show_login_button = true;
                $login_page = "sclogin.php";
            } else {
                $error_message = "Error occurred: " . mysqli_error($con);
            }
        }
    } 
    else {
        $error_message = "Invalid identifier format. Registration number must start with H2 (students) or 0F (security personnel).";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
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
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
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
            max-width: 500px;
            padding: 30px;
            transition: all 0.3s ease;
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
        .field input, .field select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border 0.3s ease;
        }
        .field input:focus, .field select:focus {
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
            padding: 15px;
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
            line-height: 1.6;
        }
        .dynamic-fields {
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            transition: all 0.5s ease;
        }
        .dynamic-fields.visible {
            max-height: 1000px;
            opacity: 1;
        }
        .identifier-hint {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .action-buttons .btn {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <header>Create Account</header>
            
            <?php if (isset($error_message)): ?>
                <div class="message error">
                    <p><?php echo $error_message; ?></p>
                </div>
            <?php endif; ?>
            
           <?php if (isset($success_message)): ?>
    <div class="message success">
        <p><?php echo $success_message; ?></p>
        <div class="action-buttons">
            <a href="idenify.php"><button class="btn">OK</button></a>
        </div>
    </div>
<?php endif; ?>
            
            <?php if (!isset($success_message)): ?>
            <form action="" method="post" enctype="multipart/form-data" id="registrationForm">
                <div class="field input">
                    <label for="identifier">Registration/Officer Number</label>
                    <input type="text" name="identifier" id="identifier" required 
                           oninput="checkIdentifierType()"
                        
                           value="<?php echo isset($_POST['identifier']) ? htmlspecialchars($_POST['identifier']) : ''; ?>">
                    <div class="identifier-hint">
                    
                    </div>
                </div>

                <div class="field input">
                    <label for="name" id="nameLabel">Name/Username</label>
                    <input type="text" name="name" id="name" required
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>

                <div class="field input">
                    <label for="lastname">Last Name</label>
                    <input type="text" name="lastname" id="lastname" required
                           value="<?php echo isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : ''; ?>">
                </div>

                <div class="field input">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="field input">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                </div>

                <!-- Dynamic fields that will be shown based on identifier type -->
                <div class="dynamic-fields" id="studentFields">
                    <div class="field input">
                        <label for="phone">Phone Number</label>
                        <input type="tel" name="phone" id="phone"
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>

                    <div class="field input">
                        <label for="school">School</label>
                        <input type="text" name="school" id="school"
                               value="<?php echo isset($_POST['school']) ? htmlspecialchars($_POST['school']) : ''; ?>">
                    </div>

                    <div class="field input">
                        <label for="myphoto">Upload Photo</label>
                        <input type="file" name="myphoto" id="myphoto" accept="image/*">
                    </div>
                </div>

                <div class="field">
                    <input type="submit" name="submit" value="Register" class="btn">
                </div>

                <div class="links">
                    Already have an account? <a href="idenify.php">Sign In Now</a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function checkIdentifierType() {
            const identifier = document.getElementById('identifier').value;
            const studentFields = document.getElementById('studentFields');
            const nameLabel = document.getElementById('nameLabel');
            
            if (identifier.startsWith('H2')) {
                // Student registration
                studentFields.classList.add('visible');
                nameLabel.textContent = 'Username';
            } 
            else if (identifier.startsWith('0F')) {
                // Security personnel registration
                studentFields.classList.remove('visible');
                nameLabel.textContent = 'Name';
            } 
            else {
                // Unknown identifier type
                studentFields.classList.remove('visible');
                nameLabel.textContent = 'Name/Username';
            }
        }
        
        // Check identifier on page load in case of form submission errors
        window.onload = function() {
            const identifier = document.getElementById('identifier');
            if (identifier.value) {
                checkIdentifierType();
            }
        };
    </script>
</body>
</html>