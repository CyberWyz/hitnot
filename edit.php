<?php
session_start();
include("php/config.php");

if (!isset($_SESSION['valid'])) {
    header("Location: index.php");
    exit;
}

$id = $_SESSION['id'];
$query = mysqli_query($con, "SELECT * FROM users WHERE Id=$id");

while ($result = mysqli_fetch_assoc($query)) {
    $res_Uname = $result['Username'];
    $res_Lastname = $result['Lastname'];
    $res_Email = $result['Email'];
    $res_Reg_Number = $result['Reg_Number'];
    $res_Phone = $result['Phone'];
    $res_School = $result['School'];
}

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $school = $_POST['school'];

    mysqli_query($con, "UPDATE users SET Username='$username', Lastname='$lastname', Email='$email', Phone='$phone', School='$school' WHERE Id=$id") or die("Error Occurred");

    header("Location: edit.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Edit Profile</title>
</head>
<body>
    <div class="nav">
        <div class="logo">
            <p>Logo</p>
        </div>
        <div class="right-links">
            <a href="home.php">Home</a>
            <a href="index.php"><button class="btn">Log Out</button></a>
        </div>
    </div>
    <main>
        <div class="main-box top">
            <div class="top">
                <div class="box">
                    <p>Edit Profile</p>
                </div>
            </div>
            <div class="bottom">
                <form action="" method="post">
                    <div class="field input">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" value="<?php echo $res_Uname; ?>" required>
                    </div>

                    <div class="field input">
                        <label for="lastname">Last Name</label>
                        <input type="text" name="lastname" id="lastname" value="<?php echo $res_Lastname; ?>" required>
                    </div>

                    <div class="field input">
                        <label for="email">Email</label>
                        <input type="text" name="email" id="email" value="<?php echo $res_Email; ?>" required>
                    </div>

                    <div class="field input">
                        <label for="reg_number">Registration Number</label>
                        <input type="text" name="reg_number" id="reg_number" value="<?php echo $res_Reg_Number; ?>" readonly>
                    </div>

                    <div class="field input">
                        <label for="phone">Phone</label>
                        <input type="text" name="phone" id="phone" value="<?php echo $res_Phone; ?>" required>
                    </div>

                    <div class="field input">
                        <label for="school">School</label>
                        <input type="text" name="school" id="school" value="<?php echo $res_School; ?>" required>
                    </div>

                    <div class="field">
                        <input type="submit" name="submit" value="Update" class="btn">
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>