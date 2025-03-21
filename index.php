<?php
session_start();
include("php/config.php");

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = mysqli_query($con, "SELECT * FROM users WHERE Username='$username'");

    if (mysqli_num_rows($query) > 0) {
        $result = mysqli_fetch_assoc($query);

        if (password_verify($password, $result['Password'])) {
            $_SESSION['valid'] = true;
            $_SESSION['id'] = $result['Id'];
            $_SESSION['username'] = $result['Username'];

            header("Location: home.php");
            exit;
        } else {
            echo "<div class='message error'><p>Incorrect password!</p></div>";
        }
    } else {
        echo "<div class='message error'><p>User not found!</p></div>";
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
    <title>Login</title>
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <header>Login</header>
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

                <div class="links">
                    Don't have an account? <a href="register.php">Sign Up Now</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>