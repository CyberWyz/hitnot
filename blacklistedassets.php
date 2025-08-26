<?php
session_start();
include("php/config.php");

if (!isset($_SESSION['valid'])) {
    header("Location: sclogin.php");
    exit;
}

$id = $_SESSION['id'];
$query = mysqli_query($con, "SELECT * FROM scpersonnel WHERE id=$id");

while ($result = mysqli_fetch_assoc($query)) {
    $res_name = $result['name'];
}

$message = "";

// Handle unblacklisting an asset
if (isset($_POST['unblacklist']) && isset($_POST['asset_id'])) {
    $asset_id = mysqli_real_escape_string($con, $_POST['asset_id']);
    
    // Update the asset status
    $update_query = mysqli_query($con, "UPDATE assets SET 
                                        AssetStatus = NULL,
                                        blacklist_reason = NULL,
                                        date_blacklisted = NULL,
                                        rfid_status = 'active'
                                        WHERE serial_number = '$asset_id'");
    
    if ($update_query) {
        $message = "<div class='message success'><p>Asset has been successfully removed from blacklist.</p></div>";
    } else {
        $message = "<div class='message error'><p>Failed to remove asset from blacklist: " . mysqli_error($con) . "</p></div>";
    }
}

// Get all blacklisted assets
$query = mysqli_query($con, "SELECT a.*, u.Username, u.Lastname 
                            FROM assets a 
                            LEFT JOIN users u ON a.reg_number = u.Reg_Number 
                            WHERE a.AssetStatus = 'Blacklisted' 
                            ORDER BY a.date_blacklisted DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="responsive.css">
    <title>Blacklisted Assets</title>
    <style>
        /* Sidebar styles */
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
            z-index: 100;
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
            margin: 0;
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
        
        /* Content wrapper */
        .content-wrapper {
            flex: 1;
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 1.5rem;
        }
        
        /* Table styles */
        .blacklist-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .blacklist-table th, .blacklist-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .blacklist-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .blacklist-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .blacklist-table tr:hover {
            background-color: #f1f1f1;
        }
        .status-blacklisted {
            background-color: #dc3545;
            color: white;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 0.8em;
        }
        .btn-unblacklist {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }
        .btn-unblacklist:hover {
            background-color: #218838;
        }
        .no-assets {
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }
        .back-link {
            margin-bottom: 20px;
            display: inline-block;
        }
        .asset-image {
            max-width: 100px;
            max-height: 100px;
            border-radius: 4px;
        }
        
        /* Message styles */
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Container adjustments */
        .container {
            width: 100%;
            max-width: none;
            padding: 0;
        }
        
        .box {
            width: 100%;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Sidebar navigation -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <span>Smart Tag Asset Management System</span>
        </div>
        <ul class="sidebar-menu">
            <li><a href="schome.php">Dashboard</a></li>
            <li><a href="missingassets.php">Missing Assets</a></li>
            <li><a href="regAsset.php">Register New Asset</a></li>
            <li><a href="blacklistedassets.php" class="active">Blacklisted Assets</a></li>
            <li><a href="verifyassets.php">Verify Asset</a></li>
            <li><a href="php/logout.php">Logout</a></li>
        </ul>
    </div>
    
    <!-- Main content -->
    <div class="content-wrapper">
        <div class="container">
            <div class="box">
                <h2>Blacklisted Assets</h2>
                <a href="schome.php" class="back-link">← Back to Dashboard</a>
                
                <?php echo $message; ?>
                
                <?php if (mysqli_num_rows($query) > 0): ?>
                    <table class="blacklist-table">
                        <thead>
                            <tr>
                                <th>Asset Details</th>
                                <th>RFID Information</th>
                                <th>Owner</th>
                                <th>Blacklist Details</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($asset = mysqli_fetch_assoc($query)): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($asset['picture']) && file_exists($asset['picture'])): ?>
                                            <img src="<?php echo htmlspecialchars($asset['picture']); ?>" alt="Asset Image" class="asset-image"><br>
                                        <?php endif; ?>
                                        <strong>Serial:</strong> <?php echo htmlspecialchars($asset['serial_number']); ?><br>
                                        <strong>Model:</strong> <?php echo htmlspecialchars($asset['item_model']); ?><br>
                                        <strong>Description:</strong> <?php echo htmlspecialchars($asset['item_description']); ?>
                                    </td>
                                    <td>
                                        <strong>RFID UID:</strong> <code><?php echo htmlspecialchars($asset['rfid_uid']); ?></code><br>
                                        <strong>Status:</strong> <span class="status-blacklisted">BLACKLISTED</span><br>
                                        <strong>Last Scanned:</strong> <?php echo !empty($asset['last_scanned']) ? 
                                            date('Y-m-d H:i', strtotime($asset['last_scanned'])) : 'Never'; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($asset['Username']) && !empty($asset['Lastname'])): ?>
                                            <strong>Name:</strong> <?php echo htmlspecialchars($asset['Username'] . ' ' . $asset['Lastname']); ?><br>
                                            <strong>Reg Number:</strong> <?php echo htmlspecialchars($asset['reg_number']); ?>
                                        <?php else: ?>
                                            <em>No owner information</em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong>Date Blacklisted:</strong><br>
                                        <?php echo !empty($asset['date_blacklisted']) ? 
                                            date('Y-m-d H:i', strtotime($asset['date_blacklisted'])) : 'Unknown'; ?><br>
                                        <strong>Reason:</strong><br>
                                        <?php echo !empty($asset['blacklist_reason']) ? 
                                            htmlspecialchars($asset['blacklist_reason']) : 'No reason provided'; ?>
                                    </td>
                                    <td>
                                        <form action="" method="post" onsubmit="return confirm('Are you sure you want to remove this asset from the blacklist?');">
                                            <input type="hidden" name="asset_id" value="<?php echo $asset['serial_number']; ?>">
                                            <button type="submit" name="unblacklist" class="btn-unblacklist">Remove from Blacklist</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-assets">
                        <p>No blacklisted assets found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>