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
        mysqli_query($con, "INSERT INTO scpersonnel (officer_id, name, lastname, email, password) VALUES ('$officer_id', '$name', '$lastname', '$email', '$password')") or die("Error Occurred");

        echo "<div class='message success'><p>Registration successful!</p></div>";
        echo "<a href='index.php'><button class='btn'>Login Now</button></a>";
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