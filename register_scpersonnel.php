<?php
include("php/config.php");

if (isset($_POST['submit'])) {
    $officer_id = $_POST['officer_id'];
    $name = $_POST['name'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $verify_query = mysqli_query($con, "SELECT email FROM scpersonnel WHERE email='$email'");

    if (mysqli_num_rows($verify_query) != 0) {
        echo "<div class='message error'><p>This email is already used. Try another one.</p></div>";
        echo "<a href='javascript:self.history.back()'><button class='btn'>Go Back</button></a>";
    } else {
        // Check if status column exists
        $check_column = mysqli_query($con, "SHOW COLUMNS FROM scpersonnel LIKE 'status'");
        if (mysqli_num_rows($check_column) == 0) {
            mysqli_query($con, "ALTER TABLE scpersonnel ADD COLUMN status VARCHAR(20) DEFAULT NULL");
        }

        // Modify your INSERT query to include the status
        $sql = "INSERT INTO scpersonnel(name, lastname, officer_id, email, password, status) 
                VALUES('$name', '$lastname', '$officer_id', '$email', '$password', 'pending')";

        // After successful registration, show a message about pending approval
        if (mysqli_query($con, $sql)) {
            echo "<div class='message'>
                    <p>Registration successful! Your account is pending approval by an administrator.</p>
                    <p>You will be able to log in once your account is approved.</p>
                    <a href='sclogin.php'><button class='btn'>Login Now</button></a>
                  </div>";
        }
    }
} else {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Register Security Personnel</title>
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <header>Register Security Personnel</header>
            <form action="" method="post">
                <div class="field input">
                    <label for="officer_id">Officer ID</label>
                    <input type="text" name="officer_id" id="officer_id" required>
                </div>

                <div class="field input">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" required>
                </div>

                <div class="field input">
                    <label for="lastname">Last Name</label>
                    <input type="text" name="lastname" id="lastname" required>
                </div>

                <div class="field input">
                    <label for="email">Email</label>
                    <input type="text" name="email" id="email" required>
                </div>

                <div class="field input">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                </div>

                <div class="field">
                    <input type="submit" name="submit" value="Register" class="btn">
                </div>

                <div class="links">
                    Already have an account? <a href="sclogin.php">Sign In Now</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
<?php } ?>