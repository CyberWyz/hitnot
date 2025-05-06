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
    <style>
        :root {
            --primary: #4e73df;
            --secondary: #858796;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --light: #f8f9fc;
            --dark: #5a5c69;
        }
        
        body {
            margin: 0;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fc;
            display: flex;
        }
        
        .sidebar {
            width: 250px;
            background-color: #4e73df;
            background-image: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            color: white;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
        }
        
        .sidebar-brand {
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 800;
            padding: 1.5rem 1rem;
            text-transform: uppercase;
            letter-spacing: 0.05rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu {
            padding: 0;
            list-style: none;
        }
        
        .sidebar-menu li {
            margin: 0;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 1rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu a.active {
            color: white;
            font-weight: 700;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .content-wrapper {
            flex: 1;
            margin-left: 250px;
            padding: 1.5rem;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .header h1 {
            margin: 0;
            font-size: 1.75rem;
            color: var(--dark);
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info span {
            margin-right: 1rem;
            color: var(--dark);
        }
        
        .btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
        }
        
        .btn:hover {
            background-color: #2e59d9;
        }
        
        .card {
            position: relative;
            display: flex;
            flex-direction: column;
            min-width: 0;
            word-wrap: break-word;
            background-color: #fff;
            background-clip: border-box;
            border: 1px solid #e3e6f0;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            padding: 0.75rem 1.25rem;
            margin-bottom: 0;
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .card-body {
            padding: 1.25rem;
        }
        
        .dashboard-options {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
        }
        
        .dashboard-option {
            background-color: white;
            border-radius: 0.35rem;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        
        .dashboard-option:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.2);
        }
        
        .dashboard-option h3 {
            margin-top: 0;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">
            <span>HitNot System</span>
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