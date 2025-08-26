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
    <link rel="stylesheet" href="style/schome.css">
    <title>Security Personnel Dashboard</title>
    
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">
            <span>Smart Tag Asset Management System</span>
        </div>
        <ul class="sidebar-menu">
            <li><a href="schome.php" class="active">Dashboard</a></li>
            <li><a href="missingassets.php">Missing Assets</a></li>
            <li><a href="regAsset.php">Register New Asset</a></li>
            <li><a href="blacklistedassets.php">Blacklisted Assets</a></li>
            <li><a href="verifyassets.php">Verify Asset</a></li>
            <li><a href="welcome.php">Logout</a></li>
        </ul>
    </div>
    
    <div class="content-wrapper">
        <div class="header">
            <h1>Security Personnel Dashboard</h1>
            <div class="user-info">
                <span>Welcome, <b><?php echo $res_name; ?></b></span>
                <a href="welcome.php" class="btn">Log Out</a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2>Quick Actions</h2>
            </div>
            <div class="card-body">
                <div class="dashboard-options">
                    <div class="dashboard-option" onclick="window.location.href='missingassets.php'">
                        <h3>Missing Assets</h3>
                        <p>View and manage missing assets</p>
                    </div>
                    <div class="dashboard-option" onclick="window.location.href='regAsset.php'">
                        <h3>Register New Asset</h3>
                        <p>Add a new asset to the system</p>
                    </div>
                    <div class="dashboard-option" onclick="window.location.href='blacklistedassets.php'">
                        <h3>Blacklisted Assets</h3>
                        <p>View and manage blacklisted assets</p>
                    </div>
                    <div class="dashboard-option" onclick="window.location.href='verifyassets.php'">
                        <h3>Verify Asset</h3>
                        <p>Verify and check asset status</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>