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
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="responsive.css">
    <title>Admin - <?php echo $page_title; ?></title>
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
        
        .filters {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            background-color: white;
            padding: 1rem;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .filter-group {
            display: flex;
            align-items: center;
        }
        
        .filter-group label {
            margin-right: 0.5rem;
            font-weight: 600;
        }
        
        .filter-group select, .filter-group input {
            padding: 0.5rem;
            border: 1px solid #d1d3e2;
            border-radius: 0.35rem;
            margin-right: 1rem;
        }
        
        .filter-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .filter-btn:hover {
            background-color: #2e59d9;
        }
        
        .add-btn {
            background-color: var(--success);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: background-color 0.3s;
        }
        
        .add-btn i {
            margin-right: 0.5rem;
        }
        
        .add-btn:hover {
            background-color: #17a673;
            color: white;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 0.35rem;
            overflow: hidden;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        table th, table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e3e6f0;
        }
        
        table th {
            background-color: #f8f9fc;
            color: var(--dark);
            font-weight: 700;
        }
        
        table tr:last-child td {
            border-bottom: none;
        }
        
        .status {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .status-Active {
            background-color: #e3fcef;
            color: #1cc88a;
        }
        
        .status-Inactive {
            background-color: #f8d7da;
            color: #e74a3b;
        }
        
        .status-Missing {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-Blacklisted {
            background-color: #343a40;
            color: white;
        }
        
        .status-Recovered {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .action-btn {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            margin-right: 0.5rem;
            text-decoration: none;
            font-size: 0.875rem;
        }
        
        .action-btn.edit {
            background-color: var(--info);
            color: white;
        }
        
        .action-btn.edit:hover {
            background-color: #2a96a5;
            color: white;
        }
        
        .action-btn.delete {
            background-color: var(--danger);
            color: white;
        }
        
        .action-btn.delete:hover {
            background-color: #c82333;
            color: white;
        }
        
        .action-btn.update {
            background-color: var(--primary);
            color: white;
        }
        
        .action-btn.update:hover {
            background-color: #2e59d9;
            color: white;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
        }
        
        .pagination a, .pagination span {
            display: inline-block;
            padding: 0.5rem 0.75rem;
            margin: 0 0.25rem;
            border-radius: 0.25rem;
            text-decoration: none;
            color: var(--primary);
            background-color: white;
            border: 1px solid #dddfeb;
            transition: all 0.3s;
        }
        
        .pagination a:hover {
            background-color: #eaecf4;
            border-color: #dddfeb;
        }
        
        .pagination a.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .pagination .disabled {
            color: #b7b9cc;
            pointer-events: none;
            cursor: default;
        }
        
        .message {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0.35rem;
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
        
        .asset-photo {
            width: 50px;
            height: 50px;
            border-radius: 4px;
            object-fit: cover;
        }
        
        .form-control {
            padding: 0.5rem;
            border: 1px solid #d1d3e2;
            border-radius: 0.35rem;
            margin-right: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
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
                <h1><?php echo $page_title; ?></h1>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['admin_username']; ?></span>
                    <a href="admin_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
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
                
                <a href="admin_add_asset.php" class="add-btn"><i class="fas fa-plus"></i> Add New Asset</a>
            </div>
            
            <!-- Assets Table -->
            <table>
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Serial</th>
                        <th>Model</th>
                        <th>Description</th>
                        <th>Owner</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($assets_query) > 0): ?>
                        <?php while ($asset = mysqli_fetch_assoc($assets_query)): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($asset['picture']) && file_exists($asset['picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($asset['picture']); ?>" alt="Asset Photo" class="asset-photo">
                                    <?php elseif (!empty($asset['picture']) && file_exists("uploads/" . $asset['picture'])): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($asset['picture']); ?>" alt="Asset Photo" class="asset-photo">
                                    <?php else: ?>
                                        <img src="uploads/default-asset.png" alt="Default Photo" class="asset-photo">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($asset['serial_number']); ?></td>
                                <td><?php echo htmlspecialchars($asset['item_model']); ?></td>
                                <td><?php echo htmlspecialchars($asset['item_description']); ?></td>
                                <td>
                                    <?php if (!empty($asset['Username'])): ?>
                                        <?php echo htmlspecialchars($asset['Username'] . ' ' . $asset['Lastname']); ?>
                                        <br><small><?php echo htmlspecialchars($asset['Reg_Number']); ?></small>
                                    <?php else: ?>
                                        <em>Unassigned</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status status-<?php echo $asset['AssetStatus'] ?? 'Active'; ?>">
                                        <?php echo $asset['AssetStatus'] ?? 'Active'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y-m-d', strtotime($asset['date_registered'])); ?></td>
                                <td>
                                    <!-- Status Change Form -->
                                    <form method="post" action="" class="d-inline">
                                        <input type="hidden" name="asset_id" value="<?php echo $asset['item_id']; ?>">
                                        <select name="new_status" class="form-control" style="width: auto; display: inline-block;">
                                            <option value="">Change Status</option>
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                            <option value="Missing">Missing</option>
                                            <option value="Blacklisted">Blacklisted</option>
                                            <option value="Recovered">Recovered</option>
                                        </select>
                                        <input type="text" name="reason" placeholder="Reason" class="form-control" style="width: 100px; display: inline-block;">
                                        <button type="submit" name="change_status" class="action-btn update"><i class="fas fa-sync-alt"></i> Update</button>
                                    </form>
                                    
                                    <a href="admin_edit_asset.php?id=<?php echo $asset['item_id']; ?>" class="action-btn edit"><i class="fas fa-edit"></i> Edit</a>
                                    
                                    <form method="post" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this asset? This action cannot be undone.');">
                                        <input type="hidden" name="asset_id" value="<?php echo $asset['item_id']; ?>">
                                        <button type="submit" name="delete_asset" class="action-btn delete"><i class="fas fa-trash"></i> Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">No assets found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=1<?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>"><i class="fas fa-angle-double-left"></i></a>
                        <a href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>"><i class="fas fa-angle-left"></i></a>
                    <?php else: ?>
                        <span class="disabled"><i class="fas fa-angle-double-left"></i></span>
                        <span class="disabled"><i class="fas fa-angle-left"></i></span>
                    <?php endif; ?>
                    
                    <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <a href="?page=<?php echo $i; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>"><i class="fas fa-angle-right"></i></a>
                        <a href="?page=<?php echo $total_pages; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>"><i class="fas fa-angle-double-right"></i></a>
                    <?php else: ?>
                        <span class="disabled"><i class="fas fa-angle-right"></i></span>
                        <span class="disabled"><i class="fas fa-angle-double-right"></i></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>