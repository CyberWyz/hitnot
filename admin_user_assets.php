<?php
session_start();
include("php/config.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_valid']) || $_SESSION['admin_valid'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Check if registration number is provided
if (!isset($_GET['reg_number']) || empty($_GET['reg_number'])) {
    $_SESSION['admin_message'] = "Registration number is required.";
    $_SESSION['admin_message_type'] = "error";
    header("Location: admin_users.php");
    exit;
}

$reg_number = mysqli_real_escape_string($con, $_GET['reg_number']);

// Get user details
$user_query = mysqli_query($con, "SELECT * FROM users WHERE Reg_Number = '$reg_number'");

if (!$user_query || mysqli_num_rows($user_query) == 0) {
    $_SESSION['admin_message'] = "User not found.";
    $_SESSION['admin_message_type'] = "error";
    header("Location: admin_users.php");
    exit;
}

$user = mysqli_fetch_assoc($user_query);

// Get user's assets
$assets_query = mysqli_query($con, "SELECT * FROM assets WHERE reg_number = '$reg_number'");

// Message variable
$message = "";
if (isset($_SESSION['admin_message'])) {
    $message_type = $_SESSION['admin_message_type'] ?? 'info';
    $message = "<div class='message {$message_type}'>{$_SESSION['admin_message']}</div>";
    unset($_SESSION['admin_message']);
    unset($_SESSION['admin_message_type']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="responsive.css">
    <title>User Assets - Admin Portal</title>
    <style>
        .asset-card {
            margin-bottom: 20px;
            border: 1px solid #e3e6f0;
            border-radius: 0.35rem;
            overflow: hidden;
        }
        
        .asset-header {
            background-color: #f8f9fc;
            padding: 15px;
            border-bottom: 1px solid #e3e6f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .asset-header h3 {
            margin: 0;
            font-size: 1.1rem;
        }
        
        .asset-body {
            padding: 15px;
            display: flex;
        }
        
        .asset-image {
            flex: 0 0 200px;
            margin-right: 20px;
        }
        
        .asset-image img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }
        
        .asset-details {
            flex: 1;
        }
        
        .asset-details p {
            margin: 5px 0;
        }
        
        .asset-details .label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        
        .asset-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-active {
            background-color: #e3fcef;
            color: #1cc88a;
        }
        
        .status-missing {
            background-color: #fff3cd;
            color: #f6c23e;
        }
        
        .status-blacklisted {
            background-color: #f8d7da;
            color: #e74a3b;
        }
        
        .asset-actions {
            margin-top: 15px;
        }
        
        .qr-code {
            margin-top: 15px;
        }
        
        .qr-code img {
            max-width: 150px;
            height: auto;
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
                <li><a href="admin_assets.php"><i class="fas fa-laptop"></i> Assets</a></li>
                <li><a href="admin_users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="scpersonnel.php"><i class="fas fa-user-shield"></i> Security Personnel</a></li>
                <li><a href="admin_logs.php"><i class="fas fa-history"></i> System Logs</a></li>
                <li><a href="admin_settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="welcome.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="content-wrapper">
            <!-- Top Bar -->
            <div class="topbar">
                <h1>User Assets</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['admin_username']; ?></span>
                    <a href="admin_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Message Display -->
            <?php echo $message; ?>
            
            <!-- User Info Card -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-user"></i> User Information</h2>
                    <div>
                        <a href="admin_users.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Users
                        </a>
                        <a href="admin_view_user.php?id=<?php echo $user['Id']; ?>" class="btn btn-info">
                            <i class="fas fa-eye"></i> View User Details
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="user-profile">
                        <div class="user-avatar">
                            <?php if (!empty($user['myphoto'])): ?>
                                <img src="<?php echo htmlspecialchars($user['myphoto']); ?>" alt="User Photo">
                            <?php else: ?>
                                <div class="default-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="user-details">
                            <h3><?php echo htmlspecialchars($user['Username'] . ' ' . $user['Lastname']); ?></h3>
                            <p><strong>Registration Number:</strong> <?php echo htmlspecialchars($user['Reg_Number']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
                            <p><strong>School:</strong> <?php echo htmlspecialchars($user['School']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Assets Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h2><i class="fas fa-laptop"></i> User's Assets</h2>
                    <div>
                        <a href="admin_add_asset.php?reg_number=<?php echo urlencode($user['Reg_Number']); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Asset
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($assets_query) > 0): ?>
                        <?php while ($asset = mysqli_fetch_assoc($assets_query)): ?>
                            <div class="asset-card">
                                <div class="asset-header">
                                    <h3><?php echo htmlspecialchars($asset['item_description']); ?></h3>
                                    <?php 
                                    $status = isset($asset['AssetStatus']) ? $asset['AssetStatus'] : 'Active';
                                    $status_class = '';
                                    
                                    switch ($status) {
                                        case 'Missing':
                                            $status_class = 'status-missing';
                                            break;
                                        case 'Blacklisted':
                                            $status_class = 'status-blacklisted';
                                            break;
                                        default:
                                            $status_class = 'status-active';
                                    }
                                    ?>
                                    <span class="asset-status <?php echo $status_class; ?>">
                                        <?php echo htmlspecialchars($status); ?>
                                    </span>
                                </div>
                                <div class="asset-body">
                                    <div class="asset-image">
                                        <?php if (!empty($asset['picture'])): ?>
                                            <img src="<?php echo htmlspecialchars($asset['picture']); ?>" alt="Asset Image">
                                        <?php else: ?>
                                            <div class="default-asset-image">
                                                <i class="fas fa-laptop"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($asset['qr_code'])): ?>
                                            <div class="qr-code">
                                                <img src="<?php echo htmlspecialchars($asset['qr_code']); ?>" alt="QR Code">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="asset-details">
                                        <p><span class="label">Serial Number:</span> <?php echo htmlspecialchars($asset['serial_number']); ?></p>
                                        <p><span class="label">Model:</span> <?php echo htmlspecialchars($asset['item_model']); ?></p>
                                        <p><span class="label">Date Registered:</span> <?php echo htmlspecialchars($asset['date_registered']); ?></p>
                                        
                                        <?php if ($status == 'Missing'): ?>
                                            <p><span class="label">Reported Missing:</span> <?php echo htmlspecialchars($asset['date_reported_missing']); ?></p>
                                        <?php elseif ($status == 'Blacklisted'): ?>
                                            <p><span class="label">Date Blacklisted:</span> <?php echo htmlspecialchars($asset['date_blacklisted']); ?></p>
                                            <?php if (!empty($asset['blacklist_reason'])): ?>
                                                <p><span class="label">Reason:</span> <?php echo htmlspecialchars($asset['blacklist_reason']); ?></p>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <div class="asset-actions">
                                            <a href="admin_view_asset.php?id=<?php echo $asset['serial_number']; ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> View Details
                                            </a>
                                            <a href="admin_edit_asset.php?id=<?php echo $asset['serial_number']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            
                                            <?php if ($status == 'Missing'): ?>
                                                <a href="admin_recover_asset.php?id=<?php echo $asset['serial_number']; ?>" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check"></i> Mark as Found
                                                </a>
                                            <?php elseif ($status == 'Blacklisted'): ?>
                                                <a href="admin_clear_blacklist.php?id=<?php echo $asset['serial_number']; ?>" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-unlock"></i> Clear Blacklist
                                                </a>
                                            <?php else: ?>
                                                <a href="admin_report_missing.php?id=<?php echo $asset['serial_number']; ?>" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-search"></i> Report Missing
                                                </a>
                                                <a href="admin_blacklist_asset.php?id=<?php echo $asset['serial_number']; ?>" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-ban"></i> Blacklist
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-assets">
                            <p>No assets found for this user.</p>
                            <a href="admin_add_asset.php?reg_number=<?php echo urlencode($user['Reg_Number']); ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Register New Asset
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>