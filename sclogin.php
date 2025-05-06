<?php
session_start();
include("php/config.php");

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $password = $_POST['password'];

    $query = mysqli_query($con, "SELECT * FROM scpersonnel WHERE name='$name'");

    if (mysqli_num_rows($query) > 0) {
        $result = mysqli_fetch_assoc($query);

        if (password_verify($password, $result['password'])) {
            // Check if the account is approved
            if (isset($result['status']) && $result['status'] == 'approved') {
                $_SESSION['valid'] = true;
                $_SESSION['id'] = $result['id'];
                $_SESSION['name'] = $result['name'];
                header("Location: schome.php");
                exit;
            } elseif (isset($result['status']) && $result['status'] == 'rejected') {
                echo "<div class='message error'><p>Your account has been rejected. Please contact the administrator.</p></div>";
            } else {
                echo "<div class='message error'><p>Your account is pending approval. Please wait for administrator approval.</p></div>";
            }
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
    <title>Security Personnel Login</title>
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <header>Security Personnel Login</header>
            <form action="" method="post">
                <div class="field input">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" autocomplete="off" required>
                </div>

                <div class="field">
                    <input type="submit" name="submit" value="Login" class="btn">
                </div>

                <div class="links">
                    Don't have an account? <a href="register_scpersonnel.php">Sign Up Now</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>