<?php
include("php/config.php");

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $reg_number = $_POST['reg_number'];
    $phone = $_POST['phone'];
    $school = $_POST['school'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $myphoto = $_FILES['myphoto']['name'];
    $myphoto_tmp = $_FILES['myphoto']['tmp_name'];

    $upload_dir = "uploads/";
    $myphoto_path = $upload_dir . basename($myphoto);
    move_uploaded_file($myphoto_tmp, $myphoto_path);

    $verify_query = mysqli_query($con, "SELECT Email FROM users WHERE Email='$email'");

    if (mysqli_num_rows($verify_query) != 0) {
        echo "<div class='message error'><p>This email is already used. Try another one.</p></div>";
        echo "<a href='javascript:self.history.back()'><button class='btn'>Go Back</button></a>";
    } else {
        $query = "INSERT INTO users (Username, Lastname, Email, Reg_Number, Phone, School, Password, myphoto) 
                  VALUES ('$username', '$lastname', '$email', '$reg_number', '$phone', '$school', '$password', '$myphoto_path')";

        if (mysqli_query($con, $query)) {
            echo "<div class='message success'><p>Registration successful!</p></div>";
            echo "<a href='index.php'><button class='btn'>Login Now</button></a>";
        } else {
            echo "<div class='message error'><p>Error occurred: " . mysqli_error($con) . "</p></div>";
            echo "<a href='javascript:self.history.back()'><button class='btn'>Go Back</button></a>";
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
    <title>Sign Up</title>
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <header>Sign Up</header>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="field input">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required>
                </div>

                <div class="field input">
                    <label for="lastname">Last Name</label>
                    <input type="text" name="lastname" id="lastname" required>
                </div>

                <div class="field input">
                    <label for="email">Email</label>
                    <input type="text" name="email" id="email" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label for="reg_number">Registration Number</label>
                    <input type="text" name="reg_number" id="reg_number" required>
                </div>

                <div class="field input">
                    <label for="phone">Phone</label>
                    <input type="text" name="phone" id="phone" required>
                </div>

                <div class="field input">
                    <label for="school">School</label>
                    <input type="text" name="school" id="school" required>
                </div>

                <div class="field input">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label for="myphoto">Upload Photo</label>
                    <input type="file" name="myphoto" id="myphoto" accept="image/*" required>
                </div>

                <div class="field">
                    <input type="submit" name="submit" value="Register" class="btn">
                </div>

                <div class="links">
                    Already a member? <a href="index.php">Sign In</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
<?php } ?>