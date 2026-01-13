<?php
session_start();
include("php/config.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_valid']) || $_SESSION['admin_valid'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];
$page_title = "Asset Management";

// Handle asset status updates
if (isset($_POST['change_status'])) {
    $asset_id = mysqli_real_escape_string($con, $_POST['asset_id']);
    $new_status = mysqli_real_escape_string($con, $_POST['new_status']);
    $reason = mysqli_real_escape_string($con, $_POST['reason']);
    
    // Update asset status
    $update = mysqli_query($con, "UPDATE assets SET AssetStatus = '$new_status' WHERE item_id = $asset_id");
    
    if ($update) {
        // Log the action
        $details = "Changed asset ID $asset_id status to $new_status. Reason: $reason";
        mysqli_query($con, "INSERT INTO admin_logs (admin_id, action, details, ip_address) 
                          VALUES ($admin_id, 'asset_status_change', '$details', '{$_SERVER['REMOTE_ADDR']}')");
        
        $_SESSION['admin_message'] = "Asset status updated successfully.";
        $_SESSION['admin_message_type'] = "success";
    } else {
        $_SESSION['admin_message'] = "Failed to update asset status: " . mysqli_error($con);
        $_SESSION['admin_message_type'] = "error";
    }
    
    header("Location: admin_assets.php");
    exit;
}

// Handle asset deletion
if (isset($_POST['delete_asset'])) {
    $asset_id = mysqli_real_escape_string($con, $_POST['asset_id']);
    
    // Get asset details for logging
    $asset_query = mysqli_query($con, "SELECT * FROM assets WHERE item_id = $asset_id");
    $asset = mysqli_fetch_assoc($asset_query);
    $asset_details = "Asset ID: {$asset['item_id']}, Serial: {$asset['serial_number']}, Model: {$asset['item_model']}";
    
    // Delete the asset
    $delete = mysqli_query($con, "DELETE FROM assets WHERE item_id = $asset_id");
    
    if ($delete) {
        // Log the action
        mysqli_query($con, "INSERT INTO admin_logs (admin_id, action, details, ip_address) 
                          VALUES ($admin_id, 'asset_deleted', 'Deleted asset: $asset_details', '{$_SERVER['REMOTE_ADDR']}')");
        
        $_SESSION['admin_message'] = "Asset deleted successfully.";
        $_SESSION['admin_message_type'] = "success";
    } else {
        $_SESSION['admin_message'] = "Failed to delete asset: " . mysqli_error($con);
        $_SESSION['admin_message_type'] = "error";
    }
    
    header("Location: admin_assets.php");
    exit;
}

// Pagination setup
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter by status if provided
$status_filter = "";
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status = mysqli_real_escape_string($con, $_GET['status']);
    $status_filter = "WHERE AssetStatus = '$status'";
}

// Search functionality
$search_filter = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($con, $_GET['search']);
    
    if (empty($status_filter)) {
        $search_filter = "WHERE serial_number LIKE '%$search%' OR item_model LIKE '%$search%' OR item_description LIKE '%$search%'";
    } else {
        $search_filter = "AND (serial_number LIKE '%$search%' OR item_model LIKE '%$search%' OR item_description LIKE '%$search%')";
    }
}

// Get total assets count for pagination
$count_query = mysqli_query($con, "SELECT COUNT(*) as total FROM assets $status_filter $search_filter");
$total_assets = mysqli_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_assets / $limit);

// Get assets with user information
$assets_query = mysqli_query($con, "SELECT a.*, u.Username, u.Lastname, u.Reg_Number 
                                  FROM assets a
                                  LEFT JOIN users u ON a.reg_number = u.Reg_Number
                                  $status_filter $search_filter
                                  ORDER BY a.date_registered DESC
                                  LIMIT $offset, $limit");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/home-modern.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Admin - <?php echo $page_title; ?></title>
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 0;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            animation: modalopen 0.3s;
        }

        @keyframes modalopen {
            from {opacity: 0; transform: translateY(-50px);}
            to {opacity: 1; transform: translateY(0);}
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            color: var(--text-dark);
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
        }

        .btn-submit, .btn-delete, .btn-cancel {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            margin-right: 10px;
            transition: all 0.3s ease;
        }

        .btn-submit {
            background-color: #28a745;
            color: white;
        }

        .btn-submit:hover {
            background-color: #218838;
            transform: translateY(-1px);
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background-color: #c82333;
            transform: translateY(-1px);
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }

        .btn-cancel:hover {
            background-color: #5a6268;
            transform: translateY(-1px);
        }

        /* Action buttons */
        .action-btn {
            display: inline-block;
            padding: 6px 12px;
            margin: 2px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .action-btn.view {
            background-color: #17a2b8;
            color: white;
        }

        .action-btn.edit {
            background-color: #ffc107;
            color: #212529;
        }

        .action-btn.update {
            background-color: #007bff;
            color: white;
        }

        .action-btn.delete {
            background-color: #dc3545;
            color: white;
        }

        .action-btn:hover {
            opacity: 0.8;
            transform: translateY(-1px);
        }

        /* Status Overlay Styles */
        .status-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1;
            border-radius: var(--border-radius);
        }

        .missing-overlay {
            background-color: rgba(255, 0, 0, 0.1);
        }

        .blacklisted-overlay {
            background-color: rgba(255, 165, 0, 0.1);
        }

        .status-text {
            font-size: 48px;
            font-weight: bold;
            transform: rotate(-15deg);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            letter-spacing: 5px;
        }

        .missing-text {
            color: #dc3545;
        }

        .blacklisted-text {
            color: #ff8c00;
        }

        /* Add Asset Button Styles */
        .add-asset-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .add-asset-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .add-asset-btn:hover::before {
            left: 100%;
        }

        .add-asset-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
            background: linear-gradient(135deg, #218838, #1aa085);
        }

        .add-asset-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(40, 167, 69, 0.3);
        }

        /* Add Asset Button Styles (matching user button style) */
        .add-asset-btn-inline {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .add-asset-btn-inline::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .add-asset-btn-inline:hover::before {
            left: 100%;
        }

        .add-asset-btn-inline:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
            background: linear-gradient(135deg, #0056b3, #004085);
        }

        .add-asset-btn-inline:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(0, 123, 255, 0.3);
        }

        /* Dashboard Sidebar Styles */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #4b648d, #41737c);
            color: #ffffff;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            z-index: 100;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .sidebar-brand {
            padding: 2rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-brand-content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .sidebar-brand i {
            font-size: 50px;
            margin-bottom: 10px;
        }

        .sidebar-brand .title {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .sidebar-menu {
            padding: 2rem 0;
            list-style: none;
            flex: 1;
        }

        .sidebar-menu li a {
            display: block;
            padding: 1rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
            border-left-color: #e7fbf9;
            transform: translateX(5px);
        }

        .sidebar-menu i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Particle Background -->
    <div class="particles-container"></div>

    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-brand-content">
                    <i class="fas fa-shield-alt" style="font-size: 50px; color: white;"></i>
                    <div class="title">Admin Portal</div>
                </div>
            </div>
            <ul class="sidebar-menu">
                <li>
                    <a href="admin_dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="admin_assets.php" class="active">
                        <i class="fas fa-laptop"></i> Assets
                    </a>
                </li>
                <li>
                    <a href="admin_users.php">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
                <li>
                    <a href="admin_security_approvals.php">
                        <i class="fas fa-user-shield"></i> Security Personnel
                    </a>
                </li>
                <li>
                    <a href="admin_logs.php">
                        <i class="fas fa-history"></i> System Logs
                    </a>
                </li>
                <li>
                    <a href="admin_settings.php">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
                <li>
                    <a href="welcome.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Topbar -->
            <div class="topbar">
                <div style="display: flex; align-items: center;">
                    <button class="toggle-sidebar" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1><?php echo $page_title; ?></h1>
                </div>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['admin_username']; ?></span>
                    <i class="fas fa-user-shield" style="font-size: 24px;"></i>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="main-content">
            
            <!-- Display Messages -->
            <?php if (isset($_SESSION['admin_message'])): ?>
                <div class="message <?php echo $_SESSION['admin_message_type']; ?>">
                    <?php echo $_SESSION['admin_message']; ?>
                </div>
                <?php 
                    // Clear the message after displaying
                    unset($_SESSION['admin_message']);
                    unset($_SESSION['admin_message_type']);
                ?>
            <?php endif; ?>
            
                <!-- Filters and Add Asset Button -->
                <div class="filters-section" style="margin-bottom: 20px;">
                    <div class="filters">
                        <div class="filter-group">
                            <form action="" method="get">
                                <label for="status">Status:</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">All</option>
                                    <option value="Active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Active' ? 'selected' : ''); ?>>Active</option>
                                    <option value="Inactive" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Inactive' ? 'selected' : ''); ?>>Inactive</option>
                                    <option value="Missing" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Missing' ? 'selected' : ''); ?>>Missing</option>
                                    <option value="Blacklisted" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Blacklisted' ? 'selected' : ''); ?>>Blacklisted</option>
                                    <option value="Recovered" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Recovered' ? 'selected' : ''); ?>>Recovered</option>
                                </select>
                                
                                <label for="search">Search:</label>
                                <input type="text" name="search" id="search" class="form-control" placeholder="Serial, Model, Description" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                
                                <button type="submit" class="filter-btn">Filter</button>
                            </form>
                        </div>
                        
                        <div style="margin-top: 15px;">
                            <a href="admin_add_asset.php" class="add-asset-btn-inline">
                                <i class="fas fa-plus-circle"></i> Add New Asset
                            </a>
                        </div>
                    </div>
                </div>
            
                <!-- Assets Display -->
                <div class="main-box">
                    <h2><i class="fas fa-laptop"></i> Asset Management</h2>
                    
                    <?php if (mysqli_num_rows($assets_query) > 0): ?>
                        <?php while ($asset = mysqli_fetch_assoc($assets_query)): ?>
                            <div class="asset-info">
                                <?php if (isset($asset['AssetStatus']) && $asset['AssetStatus'] == 'Missing'): ?>
                                    <div class="status-overlay missing-overlay">
                                        <div class="status-text missing-text">MISSING</div>
                                    </div>
                                <?php elseif (isset($asset['AssetStatus']) && $asset['AssetStatus'] == 'Blacklisted'): ?>
                                    <div class="status-overlay blacklisted-overlay">
                                        <div class="status-text blacklisted-text">CONFISCATED</div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="asset-content">
                                    <h3><?php echo htmlspecialchars($asset['item_description']); ?></h3>
                                    <div style="display: flex; flex-wrap: wrap; gap: 20px;">
                                        <div style="flex: 2; min-width: 300px;">
                                            <p><b>Serial Number:</b> <?php echo htmlspecialchars($asset['serial_number']); ?></p>
                                            <p><b>Date Registered:</b> <?php echo htmlspecialchars($asset['date_registered']); ?></p>
                                            <p><b>Item Model:</b> <?php echo htmlspecialchars($asset['item_model']); ?></p>
                                            <p><b>Owner:</b> <?php echo htmlspecialchars($asset['Username'] . ' ' . $asset['Lastname'] . ' (' . $asset['Reg_Number'] . ')'); ?></p>
                                            
                                            <?php if (isset($asset['AssetStatus']) && $asset['AssetStatus'] == 'Missing'): ?>
                                                <div class="status-info missing">
                                                    <p><b>Status:</b> <span class="status status-Missing">MISSING</span></p>
                                                    <p><b>Reported Missing On:</b> <?php echo date('Y-m-d H:i', strtotime($asset['date_reported_missing'])); ?></p>
                                                </div>
                                            <?php elseif (isset($asset['AssetStatus']) && $asset['AssetStatus'] == 'Blacklisted'): ?>
                                                <div class="status-info blacklisted">
                                                    <p><b>Status:</b> <span class="status status-Blacklisted">CONFISCATED</span></p>
                                                    <p><b>Date Confiscated:</b> <?php echo date('Y-m-d H:i', strtotime($asset['date_blacklisted'])); ?></p>
                                                    <p><b>Message:</b> This asset has been confiscated at a security checkpoint. Please visit the security office to confirm ownership.</p>
                                                    <?php if (!empty($asset['blacklist_reason'])): ?>
                                                        <p><b>Reason:</b> <?php echo htmlspecialchars($asset['blacklist_reason']); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php elseif (!isset($asset['AssetStatus']) || $asset['AssetStatus'] == NULL): ?>
                                                <p><b>Status:</b> <span class="status status-Active">ACTIVE</span></p>
                                            <?php else: ?>
                                                <p><b>Status:</b> <span class="status status-<?php echo $asset['AssetStatus']; ?>"><?php echo strtoupper($asset['AssetStatus']); ?></span></p>
                                            <?php endif; ?>
                                            
                                            <!-- Admin Actions -->
                                            <div class="admin-actions" style="margin-top: 15px;">
                                                <a href="admin_view_asset.php?id=<?php echo $asset['item_id']; ?>" class="action-btn view">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <a href="admin_edit_asset.php?id=<?php echo $asset['item_id']; ?>" class="action-btn edit">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <button type="button" class="action-btn update" onclick="changeStatus(<?php echo $asset['item_id']; ?>, '<?php echo $asset['AssetStatus'] ?? 'Active'; ?>')">
                                                    <i class="fas fa-exchange-alt"></i> Change Status
                                                </button>
                                                <button type="button" class="action-btn delete" onclick="deleteAsset(<?php echo $asset['item_id']; ?>)">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div style="flex: 1; min-width: 220px;">
                                            <div class="asset-details-container" style="display: flex; flex-direction: column; gap: 20px;">
                                                <?php if (!empty($asset['picture'])): ?>
                                                    <div class="asset-image">
                                                        <h4><i class="fas fa-camera"></i> Asset Picture</h4>
                                                        <img src="<?php echo htmlspecialchars($asset['picture']); ?>" alt="Asset Image">
                                                    </div>
                                                <?php endif; ?>

                                                <?php if (!empty($asset['qr_code'])): ?>
                                                    <div class="qr-code">
                                                        <h4><i class="fas fa-qrcode"></i> QR Code</h4>
                                                        <img src="<?php echo htmlspecialchars($asset['qr_code']); ?>" alt="QR Code">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="text-align: center; padding: 20px;">No assets found matching the criteria.</p>
                    <?php endif; ?>
                </div>
            
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>">&laquo; Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
    <!-- Status Change Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-exchange-alt"></i> Change Asset Status</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="statusForm" action="" method="post">
                    <input type="hidden" name="asset_id" id="modalAssetId">
                    <div class="form-group">
                        <label for="new_status">New Status:</label>
                        <select name="new_status" id="new_status" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Missing">Missing</option>
                            <option value="Blacklisted">Blacklisted</option>
                            <option value="Recovered">Recovered</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="reason">Reason:</label>
                        <textarea name="reason" id="reason" rows="3" placeholder="Enter reason for status change..." required></textarea>
                    </div>
                    <button type="submit" name="change_status" class="btn-submit">Update Status</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-trash"></i> Delete Asset</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this asset? This action cannot be undone.</p>
                <form id="deleteForm" action="" method="post">
                    <input type="hidden" name="asset_id" id="deleteAssetId">
                    <button type="submit" name="delete_asset" class="btn-delete">Yes, Delete Asset</button>
                    <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                </form>
            </div>
        </div>
    </div>

    <script src="js/particles.js"></script>
    <script src="js/home.js"></script>
    <script>
        // Modal functionality
        var statusModal = document.getElementById('statusModal');
        var deleteModal = document.getElementById('deleteModal');
        var closeBtns = document.getElementsByClassName('close');

        function changeStatus(assetId, currentStatus) {
            document.getElementById('modalAssetId').value = assetId;
            document.getElementById('new_status').value = currentStatus;
            statusModal.style.display = 'block';
        }

        function deleteAsset(assetId) {
            document.getElementById('deleteAssetId').value = assetId;
            deleteModal.style.display = 'block';
        }

        function closeDeleteModal() {
            deleteModal.style.display = 'none';
        }

        for (var i = 0; i < closeBtns.length; i++) {
            closeBtns[i].onclick = function() {
                statusModal.style.display = 'none';
                deleteModal.style.display = 'none';
            }
        }

        window.onclick = function(event) {
            if (event.target == statusModal) {
                statusModal.style.display = 'none';
            }
            if (event.target == deleteModal) {
                deleteModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
