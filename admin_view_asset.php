<?php
session_start();
include("php/config.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_valid']) || $_SESSION['admin_valid'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];
$message = "";

// Check if asset ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin_assets.php");
    exit;
}

$asset_id = mysqli_real_escape_string($con, $_GET['id']);

// Get asset details
$asset_query = mysqli_query($con, "SELECT a.*, u.Username, u.Lastname, u.Email 
                                 FROM assets a 
                                 LEFT JOIN users u ON a.reg_number = u.Reg_Number 
                                 WHERE a.id = $asset_id");

if (mysqli_num_rows($asset_query) == 0) {
    header("Location: admin_assets.php");
    exit;
}

$asset = mysqli_fetch_assoc($asset_query);

// Get asset history
$history_query = mysqli_query($con, "SELECT * FROM asset_history 
                                   WHERE asset_id = $asset_id 
                                   ORDER BY timestamp DESC 
                                   LIMIT 10");

// Get verification history
$verification_query = mysqli_query($con, "SELECT v.*, s.name as officer_name 
                                        FROM verifications v 
                                        LEFT JOIN scpersonnel s ON v.officer_id = s.id 
                                        WHERE v.asset_id = $asset_id 
                                        ORDER BY v.verification_date DESC 
                                        LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <link rel="styleshee <link rel="stylesheet" href="responsive.css">t" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   
    <title>Asset Details - <?php echo htmlspecialchars($asset['AssetName']); ?></title>
    <style>
        :root {
            --primary: #4e73df;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --secondary: #858796;
            --light: #f8f9fc;
            --dark: #5a5c69;
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, var(--primary) 10%, #224abe 100%);
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
            padding: 1rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }
        
        .content-wrapper {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        
        .topbar {
            height: 70px;
            background-color: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .topbar h1 {
            font-size: 1.5rem;
            margin: 0;
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
        
        .logout-btn {
            background-color: var(--danger);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .logout-btn:hover {
            background-color: #c82333;
        }
        
        .card {
            background-color: white;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            padding: 1.25rem;
            border-bottom: 1px solid #e3e6f0;
            background-color: #f8f9fc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h2 {
            margin: 0;
            font-size: 1.25rem;
            color: var(--dark);
        }
        
        .card-body {
            padding: 1.25rem;
        }
        
        .asset-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .detail-group {
            margin-bottom: 1rem;
        }
        
        .detail-label {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .detail-value {
            color: #5a5c69;
        }
        
        .badge {
            display: inline-block;
            padding: 0.25em 0.4em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.35rem;
        }
        
        .badge-success {
            color: #fff;
            background-color: var(--success);
        }
        
        .badge-warning {
            color: #212529;
            background-color: var(--warning);
        }
        
        .badge-danger {
            color: #fff;
            background-color: var(--danger);
        }
        
        .badge-info {
            color: #fff;
            background-color: var(--info);
        }
        
        .badge-secondary {
            color: #fff;
            background-color: var(--secondary);
        }
        
        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.35rem;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            cursor: pointer;
        }
        
        .btn-primary {
            color: #fff;
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            color: #fff;
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        
        .btn-warning {
            color: #212529;
            background-color: var(--warning);
            border-color: var(--warning);
        }
        
        .btn-warning:hover {
            color: #212529;
            background-color: #e0a800;
            border-color: #d39e00;
        }
        
        .btn-danger {
            color: #fff;
            background-color: var(--danger);
            border-color: var(--danger);
        }
        
        .btn-danger:hover {
            color: #fff;
            background-color: #c82333;
            border-color: #bd2130;
        }
        
        .btn-secondary {
            color: #fff;
            background-color: var(--secondary);
            border-color: var(--secondary);
        }
        
        .btn-secondary:hover {
            color: #fff;
            background-color: #717384;
            border-color: #6b6d7d;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .qr-code {
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .qr-code img {
            max-width: 200px;
            height: auto;
        }
        
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid #e3e6f0;
        }
        
        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #e3e6f0;
            background-color: #f8f9fc;
            color: var(--primary);
            font-weight: 700;
        }
        
        .table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.03);
        }
        
        .asset-image {
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .asset-image img {
            max-width: 100%;
            height: auto;
            max-height: 300px;
            border-radius: 0.35rem;
            border: 1px solid #e3e6f0;
        }
        
        .tabs {
            display: flex;
            border-bottom: 1px solid #e3e6f0;
            margin-bottom: 1rem;
        }
        
        .tab {
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
        }
        
        .tab.active {
            border-bottom-color: var(--primary);
            color: var(--primary);
            font-weight: 700;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-brand">
                <i class="fas fa-shield-alt"></i> Admin Portal
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="admin_assets.php" class="active"><i class="fas fa-laptop"></i> Assets</a></li>
                <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="admin_security.php"><i class="fas fa-user-shield"></i> Security Personnel</a></li>
                <li><a href="admin_logs.php"><i class="fas fa-history"></i> System Logs</a></li>
                <li><a href="admin_settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="content-wrapper">
            <!-- Top Bar -->
            <div class="topbar">
                <h1>Asset Details</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['admin_username']; ?></span>
                    <a href="admin_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Message Display -->
            <?php echo $message; ?>
            
            <!-- Asset Details Card -->
            <div class="card">
                <div class="card-header">
                    <h2>
                        <i class="fas fa-<?php echo $asset['AssetType'] == 'Laptop' ? 'laptop' : ($asset['AssetType'] == 'Phone' ? 'mobile-alt' : 'tablet-alt'); ?>"></i>
                        <?php echo htmlspecialchars($asset['AssetName']); ?>
                    </h2>
                    <div>
                        <a href="admin_assets.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Assets
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="tabs">
                        <div class="tab active" data-tab="details">Details</div>