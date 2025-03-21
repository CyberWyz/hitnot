<?php
session_start();
include("php/config.php");

if (!isset($_SESSION['valid'])) {
    header("Location: login_scpersonnel.php");
    exit;
}

$id = $_SESSION['id'];
$query = mysqli_query($con, "SELECT * FROM scpersonnel WHERE id=$id");

while ($result = mysqli_fetch_assoc($query)) {
    $res_name = $result['name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Security Personnel Dashboard</title>
</head>
<body>
    <div class="nav">
        <div class="logo">
            <p>Logo</p>
        </div>
        <div class="right-links">
            <a href=".php">Edit Profile</a>
            <a href="welcome.php"><button class="btn">Log Out</button></a>
        </div>
    </div>
    <main>
        <div class="main-box top">
            <div class="top">
                <div class="box">
                    <p>Hello <b><?php echo $res_name; ?></b>, Welcome</p>
                </div>
            </div>
            <div class="options">
                <div onclick="window.location.href='missing_assets.php'">Missing Assets</div>
                <div onclick="window.location.href='regAsset.php'">Register New Asset</div>
                <div onclick="window.location.href='blacklist_asset.php'">Blacklist an Asset</div>
                <div onclick="window.location.href='verifyassets.php'">Verify Asset</div>
                
            </div>
        </div>
    </main>
</body>
</html>