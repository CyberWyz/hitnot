<?php
session_start();
include("php/config.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_valid']) || $_SESSION['admin_valid'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];
$page_title = "System Logs";

// Determine which log type to display
$log_type = isset($_GET['log_type']) ? $_GET['log_type'] : 'admin';

// Pagination setup
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// ADMIN LOGS SECTION
if ($log_type == 'admin') {
    // Filter by action if provided
    $action_filter = "";
    if (isset($_GET['action']) && !empty($_GET['action'])) {
        $action = mysqli_real_escape_string($con, $_GET['action']);
        $action_filter = "WHERE action = '$action'";
    }

    // Filter by admin if provided
    $admin_filter = "";
    if (isset($_GET['admin_id']) && !empty($_GET['admin_id'])) {
        $filter_admin_id = mysqli_real_escape_string($con, $_GET['admin_id']);
        if (empty($action_filter)) {
            $admin_filter = "WHERE admin_id = '$filter_admin_id'";
        } else {
            $admin_filter = "AND admin_id = '$filter_admin_id'";
        }
    }

    // Date range filter
    $date_filter = "";
    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $start_date = mysqli_real_escape_string($con, $_GET['start_date']);
        if (empty($action_filter) && empty($admin_filter)) {
            $date_filter = "WHERE created_at >= '$start_date 00:00:00'";
        } else {
            $date_filter = "AND created_at >= '$start_date 00:00:00'";
        }
    }

    if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
        $end_date = mysqli_real_escape_string($con, $_GET['end_date']);
        if (empty($action_filter) && empty($admin_filter) && empty($date_filter)) {
            $date_filter = "WHERE created_at <= '$end_date 23:59:59'";
        } else {
            $date_filter = "AND created_at <= '$end_date 23:59:59'";
        }
    }

    // Get total logs count for pagination with proper error handling
    $count_query = mysqli_query($con, "SELECT COUNT(*) as total FROM admin_logs $action_filter $admin_filter $date_filter");
    
    if (!$count_query) {
        $admin_logs_error = "Error counting admin logs: " . mysqli_error($con);
        $total_logs = 0;
        $total_pages = 1;
    } else {
        $total_logs = mysqli_fetch_assoc($count_query)['total'];
        $total_pages = ceil($total_logs / $limit);
    }

    // Get logs with admin information with proper error handling
   // Get logs without joining with admins table
$logs_query = mysqli_query($con, "SELECT l.* 
                                FROM admin_logs l
                                $action_filter $admin_filter $date_filter
                                ORDER BY l.created_at DESC
                                LIMIT $offset, $limit");

if (!$logs_query) {
$admin_logs_error = "Error fetching admin logs: " . mysqli_error($con);
}

    
    if (!$logs_query) {
        $admin_logs_error = "Error fetching admin logs: " . mysqli_error($con);
    }

    // Get all admins for filter dropdown with proper error handling
    // Get distinct admin IDs for filter dropdown with proper error handling
    $admins_query = mysqli_query($con, "SELECT DISTINCT admin_id FROM admin_logs ORDER BY admin_id");

    if (!$admins_query) {
        $admins_error = "Error fetching admin IDs: " . mysqli_error($con);
    }

        
        
        if (!$admins_query) {
            $admins_error = "Error fetching admins: " . mysqli_error($con);
        }

        // Get distinct actions for filter dropdown with proper error handling
        $actions_query = mysqli_query($con, "SELECT DISTINCT action FROM admin_logs ORDER BY action");
        
        if (!$actions_query) {
            $actions_error = "Error fetching actions: " . mysqli_error($con);
        }
    }
// SCAN LOGS SECTION
else if ($log_type == 'scan') {
    // Check if scan_logs table exists
    $table_check = mysqli_query($con, "SHOW TABLES LIKE 'scan_logs'");
    
    if (!$table_check) {
        $scan_logs_error = "Error checking for scan_logs table: " . mysqli_error($con);
    } else if (mysqli_num_rows($table_check) == 0) {
        $scan_logs_error = "The scan_logs table does not exist in the database.";
    } else {
        // Filter by RFID UID if provided
        $rfid_filter = "";
        if (isset($_GET['rfid_uid']) && !empty($_GET['rfid_uid'])) {
            $rfid_uid = mysqli_real_escape_string($con, $_GET['rfid_uid']);
            $rfid_filter = "WHERE rfid_uid = '$rfid_uid'";
        }

        // Filter by scanner ID if provided
        $scanner_filter = "";
        if (isset($_GET['scanner_id']) && !empty($_GET['scanner_id'])) {
            $scanner_id = mysqli_real_escape_string($con, $_GET['scanner_id']);
            if (empty($rfid_filter)) {
                $scanner_filter = "WHERE scanner_id = '$scanner_id'";
            } else {
                $scanner_filter = "AND scanner_id = '$scanner_id'";
            }
        }

        // Filter by status if provided
        $status_filter = "";
        if (isset($_GET['status']) && !empty($_GET['status'])) {
            $status = mysqli_real_escape_string($con, $_GET['status']);
            if (empty($rfid_filter) && empty($scanner_filter)) {
                $status_filter = "WHERE status = '$status'";
            } else {
                $status_filter = "AND status = '$status'";
            }
        }

        // Date range filter
        $date_filter = "";
        if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
            $start_date = mysqli_real_escape_string($con, $_GET['start_date']);
            if (empty($rfid_filter) && empty($scanner_filter) && empty($status_filter)) {
                $date_filter = "WHERE scan_time >= '$start_date 00:00:00'";
            } else {
                $date_filter = "AND scan_time >= '$start_date 00:00:00'";
            }
        }

        if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
            $end_date = mysqli_real_escape_string($con, $_GET['end_date']);
            if (empty($rfid_filter) && empty($scanner_filter) && empty($status_filter) && empty($date_filter)) {
                $date_filter = "WHERE scan_time <= '$end_date 23:59:59'";
            } else {
                $date_filter = "AND scan_time <= '$end_date 23:59:59'";
            }
        }

        // Get total scan logs count for pagination with proper error handling
        $count_query = mysqli_query($con, "SELECT COUNT(*) as total FROM scan_logs $rfid_filter $scanner_filter $status_filter $date_filter");
        
        if (!$count_query) {
            $scan_logs_error = "Error counting scan logs: " . mysqli_error($con);
            $total_logs = 0;
            $total_pages = 1;
        } else {
            $total_logs = mysqli_fetch_assoc($count_query)['total'];
            $total_pages = ceil($total_logs / $limit);
        }

        // Get scan logs with asset information with proper error handling
        // Join scan_logs with assets table
$logs_query = mysqli_query($con, "SELECT s.*, a.item_model, a.item_description, a.serial_number, a.reg_number, a.rfid_status
                                FROM scan_logs s
                                LEFT JOIN assets a ON s.rfid_uid = a.rfid_uid
                                $date_filter
                                ORDER BY s.scan_time DESC
                                LIMIT $offset, $limit");

if (!$logs_query) {
    $scan_logs_error = "Error fetching scan logs: " . mysqli_error($con);
}

        // Get distinct scanner IDs for filter dropdown with proper error handling
        $scanners_query = mysqli_query($con, "SELECT DISTINCT scanner_id FROM scan_logs ORDER BY scanner_id");
        
        if (!$scanners_query) {
            $scanners_error = "Error fetching scanners: " . mysqli_error($con);
        }

        // Get distinct statuses for filter dropdown with proper error handling
        $statuses_query = mysqli_query($con, "SELECT DISTINCT status FROM scan_logs ORDER BY status");
        
        if (!$statuses_query) {
            $statuses_error = "Error fetching statuses: " . mysqli_error($con);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
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
        
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, var(--primary) 10%, #224abe 100%);
            color: white;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
            z-index: 1000;
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
            min-height: 100vh;
            overflow-y: auto;
            position: relative;
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
            align-items: flex-start;
            margin-bottom: 1.5rem;
            background-color: white;
            padding: 1rem;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            overflow-x: auto;
        }
        
        .filter-group {
            display: flex;
            align-items: flex-start;
            flex-wrap: wrap;
            width: 100%;
        }
        
        .filter-group form {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
        }
        
        .filter-group label {
            margin-right: 0.5rem;
            font-weight: 600;
            margin-top: 0.5rem;
            min-width: 60px;
        }
        
        .filter-group select, .filter-group input {
            padding: 0.5rem;
            border: 1px solid #d1d3e2;
            border-radius: 0.35rem;
            margin-right: 1rem;
            margin-bottom: 0.5rem;
            max-width: 200px;
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
        
        .reset-btn {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 0.5rem 1rem
            border-radius: 0.25rem;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-left: 0.5rem;
            text-decoration: none;
        }
        
        .reset-btn:hover {
            background-color: #6e707e;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            background-color: white;
            border-radius: 0.35rem;
            overflow: hidden;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        table th, table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e3e6f0;
            word-break: break-word;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        table th {
            background-color: #f8f9fc;
            color: var(--dark);
            font-weight: 700;
        }
        
        table tr:last-child td {
            border-bottom: none;
        }
        
        .action-tag {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .action-tag.login {
            background-color: #e3fcef;
            color: #1cc88a;
        }
        
        .action-tag.logout {
            background-color: #eaecf4;
            color: #858796;
        }
        
        .action-tag.create {
            background-color: #d1ecf1;
            color: #36b9cc;
        }
        
        .action-tag.update {
            background-color: #fff3cd;
            color: #f6c23e;
        }
        
        .action-tag.delete {
            background-color: #f8d7da;
            color: #e74a3b;
        }
        
        .action-tag.asset_status_change {
            background-color: #d1ecf1;
            color: #36b9cc;
        }
        
        .action-tag.asset_deleted {
            background-color: #f8d7da;
            color: #e74a3b;
        }
        
        /* Status tags for scan logs */
        .status-tag {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .status-tag.success {
            background-color: #e3fcef;
            color: #1cc88a;
        }
        
        .status-tag.warning {
            background-color: #fff3cd;
            color: #f6c23e;
        }
        
        .status-tag.alert {
            background-color: #f8d7da;
            color: #e74a3b;
        }
        
        .status-tag.info {
            background-color: #d1ecf1;
            color: #36b9cc;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h2, .card-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .card-body {
            flex: 1 1 auto;
            overflow-x: auto;
            padding: 1.25rem;
        }
        
        .export-btn {
            background-color: var(--success);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: background-color 0.3s;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }
        
        .export-btn i {
            margin-right: 0.5rem;
        }
        
        .export-btn:hover {
            background-color: #17a673;
            color: white;
        }
        
        .tab-navigation {
            display: flex;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .tab-navigation a {
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            color: var(--secondary);
            font-weight: 600;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
        }
        
        .tab-navigation a:hover {
            color: var(--primary);
        }
        
        .tab-navigation a.active {
            color: var(--primary);
            border-bottom: 3px solid var(--primary);
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.35rem;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
                <li><a href="admin_dashboard.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php') ? 'class="active"' : ''; ?>><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="admin_assets.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'admin_assets.php') ? 'class="active"' : ''; ?>><i class="fas fa-laptop"></i> Assets</a></li>
                <li><a href="admin_users.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'admin_users.php') ? 'class="active"' : ''; ?>><i class="fas fa-users"></i> Users</a></li>
                <li><a href="scpersonnel.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'scpersonnel.php') ? 'class="active"' : ''; ?>><i class="fas fa-user-shield"></i> Security Personnel</a></li>
                <li><a href="admin_logs.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'admin_logs.php') ? 'class="active"' : ''; ?>><i class="fas fa-history"></i> System Logs</a></li>
                <li><a href="admin_settings.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'admin_settings.php') ? 'class="active"' : ''; ?>><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="content-wrapper">
            <!-- Top Bar -->
            <div class="topbar">
                <h1><?php echo $page_title; ?></h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="admin_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Tab Navigation -->
            <div class="tab-navigation">
                <a href="?log_type=admin" class="<?php echo ($log_type == 'admin') ? 'active' : ''; ?>">
                    <i class="fas fa-user-shield"></i> Admin Logs
                </a>
                <a href="?log_type=scan" class="<?php echo ($log_type == 'scan') ? 'active' : ''; ?>">
                    <i class="fas fa-wifi"></i> RFID Scan Logs
                </a>
            </div>
            
            <?php if ($log_type == 'admin'): ?>
            <!-- Admin Logs Section -->
            <?php if (isset($admin_logs_error)): ?>
                <div class="alert alert-danger">
                    <?php echo $admin_logs_error; ?>
                </div>
            <?php else: ?>
                <!-- Filters -->
                <div class="filters">
                    <div class="filter-group">
                        <form action="" method="get">
                            <input type="hidden" name="log_type" value="admin">
                            <label for="action">Action:</label>
                            <select name="action" id="action" class="form-control">
                                <option value="">All</option>
                                <?php if ($actions_query && mysqli_num_rows($actions_query) > 0): ?>
                                    <?php while ($action = mysqli_fetch_assoc($actions_query)): ?>
                                        <option value="<?php echo htmlspecialchars($action['action']); ?>" <?php echo (isset($_GET['action']) && $_GET['action'] == htmlspecialchars($action['action']) ? 'selected' : ''); ?>><?php echo htmlspecialchars($action['action']); ?></option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                            
                            <label for="admin_id">Admin ID:</label>
                            <select name="admin_id" id="admin_id" class="form-control">
                                <option value="">All</option>
                                <?php if ($admins_query && mysqli_num_rows($admins_query) > 0): ?>
                                    <?php while ($admin = mysqli_fetch_assoc($admins_query)): ?>
                                        <option value="<?php echo htmlspecialchars($admin['admin_id']); ?>" <?php echo (isset($_GET['admin_id']) && $_GET['admin_id'] == htmlspecialchars($admin['admin_id']) ? 'selected' : ''); ?>><?php echo htmlspecialchars($admin['admin_id']); ?></option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                            
                            <label for="start_date">From:</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>">
                            
                            <label for="end_date">To:</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>">
                            
                            <button type="submit" class="filter-btn">Filter</button>
                            <a href="?log_type=admin" class="reset-btn">Reset</a>
                        </form>
                    </div>
                </div>
                
                <!-- Admin Logs Table -->
                <div class="card">
                    <div class="card-header">
                        <h3>Admin Activity Logs</h3>
                        <a href="export_logs.php?type=admin" class="export-btn"><i class="fas fa-file-export"></i> Export CSV</a>
                    </div>
                    <div class="card-body">
                        <?php if ($logs_query && mysqli_num_rows($logs_query) > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Admin</th>
                                        <th>Action</th>
                                        <th>Description</th>
                                        <th>IP Address</th>
                                        <th>Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($log = mysqli_fetch_assoc($logs_query)): ?>
                                        <tr>
                                            <td><?php echo $log['admin_id'] ? htmlspecialchars($log['admin_id']) : 'System'; ?></td>
                                            <td><?php echo htmlspecialchars($log['admin_username'] ?? 'Unknown'); ?></td>
                                            <td>
                                                <span class="action-tag 
                                                    <?php 
                                                        if (strpos($log['action'], 'asset_') === 0) echo 'asset_status_change';
                                                        elseif (strpos($log['action'], 'user_') === 0) echo 'create';
                                                        elseif (strpos($log['action'], 'security_') === 0) echo 'update';
                                                        elseif ($log['action'] == 'login') echo 'login';
                                                        elseif ($log['action'] == 'logout') echo 'logout';
                                                        elseif ($log['action'] == 'create') echo 'create';
                                                        elseif ($log['action'] == 'update') echo 'update';
                                                        elseif ($log['action'] == 'delete') echo 'delete';
                                                        else echo 'info';
                                                    ?>">
                                                    <?php echo htmlspecialchars($log['action']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($log['details'] ?? 'No details available'); ?></td>
                                            <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                            <td><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No admin logs found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php else: ?>
            <!-- RFID Scan Logs Section -->
            <?php if (isset($scan_logs_error)): ?>
                <div class="alert alert-danger">
                    <?php echo $scan_logs_error; ?>
                    <?php if (strpos($scan_logs_error, "table does not exist") !== false): ?>
                        <p>You may need to create the scan_logs table with the following structure:</p>
                        <pre>

                        </pre>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Filters -->
                <div class="filters">
                    <div class="filter-group">
                        <form action="" method="get">
                            <input type="hidden" name="log_type" value="scan">
                            <label for="rfid_uid">RFID UID:</label>
                            <input type="text" name="rfid_uid" id="rfid_uid" class="form-control" value="<?php echo isset($_GET['rfid_uid']) ? htmlspecialchars($_GET['rfid_uid']) : ''; ?>">
                            
                            <label for="scanner_id">Scanner:</label>
                            <select name="scanner_id" id="scanner_id" class="form-control">
                                <option value="">All</option>
                                <?php if ($scanners_query && mysqli_num_rows($scanners_query) > 0): ?>
                                    <?php while ($scanner = mysqli_fetch_assoc($scanners_query)): ?>
                                        <option value="<?php echo htmlspecialchars($scanner['scanner_id']); ?>" <?php echo (isset($_GET['scanner_id']) && $_GET['scanner_id'] == htmlspecialchars($scanner['scanner_id']) ? 'selected' : ''); ?>><?php echo htmlspecialchars($scanner['scanner_id']); ?></option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                            
                            <label for="status">Status:</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All</option>
                                <?php if ($statuses_query && mysqli_num_rows($statuses_query) > 0): ?>
                                    <?php while ($status = mysqli_fetch_assoc($statuses_query)): ?>
                                        <option value="<?php echo htmlspecialchars($status['status']); ?>" <?php echo (isset($_GET['status']) && $_GET['status'] == htmlspecialchars($status['status']) ? 'selected' : ''); ?>><?php echo htmlspecialchars($status['status']); ?></option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                            
                            <label for="start_date">From:</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>">
                            
                            <label for="end_date">To:</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>">
                            
                            <button type="submit" class="filter-btn">Filter</button>
                            <a href="?log_type=scan" class="reset-btn">Reset</a>
                        </form>
                    </div>
                </div>
                
                <!-- Scan Logs Table -->
                <div class="card">
                    <div class="card-header">
                        <h3>RFID Scan Logs</h3>
                        <a href="export_logs.php?type=scan" class="export-btn"><i class="fas fa-file-export"></i> Export CSV</a>
                    </div>
                    <div class="card-body">
                        <?php if ($logs_query && mysqli_num_rows($logs_query) > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>RFID UID</th>
                                        <th>Asset</th>
                                        <th>Scanner ID</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                        <th>Scan Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($log = mysqli_fetch_assoc($logs_query)): ?>
                                        <tr>
                                            <td><?php echo $log['id']; ?></td>
                                            <td><?php echo htmlspecialchars($log['rfid_uid']); ?></td>
                                            <td>
                                                <?php if (isset($log['item_model']) || isset($log['serial_number'])): ?>
                                                    <?php if (isset($log['item_model'])): ?>
                                                        <?php echo htmlspecialchars($log['item_model']); ?>
                                                    <?php endif; ?>
                                                    <?php if (isset($log['serial_number'])): ?>
                                                        (S/N: <?php echo htmlspecialchars($log['serial_number']); ?>)
                                                    <?php endif; ?>
                                                    <?php if (isset($log['reg_number'])): ?>
                                                        <br>
                                                        <small>Reg: <?php echo htmlspecialchars($log['reg_number']); ?></small>
                                                    <?php endif; ?>
                                                    <?php if (isset($log['rfid_status'])): ?>
                                                        <br>
                                                        <small>Status: <span class="status-tag <?php echo strtolower(htmlspecialchars($log['rfid_status'])); ?>">
                                                            <?php echo htmlspecialchars($log['rfid_status']); ?>
                                                        </span></small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Unknown Asset</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($log['scanner_id']); ?></td>
                                            <td><?php echo htmlspecialchars($log['location'] ?? 'Unknown'); ?></td>
                                            <td>
                                                <span class="status-tag <?php echo htmlspecialchars($log['status']); ?>">
                                                    <?php echo htmlspecialchars($log['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('Y-m-d H:i:s', strtotime($log['scan_time'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No scan logs found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php endif; ?>
            
            <!-- Pagination -->
            <?php if (!isset($admin_logs_error) && !isset($scan_logs_error)): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?log_type=<?php echo $log_type; ?>&page=<?php echo $page - 1; ?><?php echo isset($_GET['action']) ? '&action=' . htmlspecialchars($_GET['action']) : ''; ?><?php echo isset($_GET['admin_id']) ? '&admin_id=' . htmlspecialchars($_GET['admin_id']) : ''; ?><?php echo isset($_GET['rfid_uid']) ? '&rfid_uid=' . htmlspecialchars($_GET['rfid_uid']) : ''; ?><?php echo isset($_GET['scanner_id']) ? '&scanner_id=' . htmlspecialchars($_GET['scanner_id']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . htmlspecialchars($_GET['status']) : ''; ?><?php echo isset($_GET['start_date']) ? '&start_date=' . htmlspecialchars($_GET['start_date']) : ''; ?><?php echo isset($_GET['end_date']) ? '&end_date=' . htmlspecialchars($_GET['end_date']) : ''; ?>">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="?log_type=<?php echo $log_type; ?>&page=<?php echo $i; ?><?php echo isset($_GET['action']) ? '&action=' . htmlspecialchars($_GET['action']) : ''; ?><?php echo isset($_GET['admin_id']) ? '&admin_id=' . htmlspecialchars($_GET['admin_id']) : ''; ?><?php echo isset($_GET['rfid_uid']) ? '&rfid_uid=' . htmlspecialchars($_GET['rfid_uid']) : ''; ?><?php echo isset($_GET['scanner_id']) ? '&scanner_id=' . htmlspecialchars($_GET['scanner_id']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . htmlspecialchars($_GET['status']) : ''; ?><?php echo isset($_GET['start_date']) ? '&start_date=' . htmlspecialchars($_GET['start_date']) : ''; ?><?php echo isset($_GET['end_date']) ? '&end_date=' . htmlspecialchars($_GET['end_date']) : ''; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?log_type=<?php echo $log_type; ?>&page=<?php echo $page + 1; ?><?php echo isset($_GET['action']) ? '&action=' . htmlspecialchars($_GET['action']) : ''; ?><?php echo isset($_GET['admin_id']) ? '&admin_id=' . htmlspecialchars($_GET['admin_id']) : ''; ?><?php echo isset($_GET['rfid_uid']) ? '&rfid_uid=' . htmlspecialchars($_GET['rfid_uid']) : ''; ?><?php echo isset($_GET['scanner_id']) ? '&scanner_id=' . htmlspecialchars($_GET['scanner_id']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . htmlspecialchars($_GET['status']) : ''; ?><?php echo isset($_GET['start_date']) ? '&start_date=' . htmlspecialchars($_GET['start_date']) : ''; ?><?php echo isset($_GET['end_date']) ? '&end_date=' . htmlspecialchars($_GET['end_date']) : ''; ?>">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Add event listener to reset button
        document.addEventListener('DOMContentLoaded', function() {
            const resetButtons = document.querySelectorAll('.reset-btn');
            resetButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const logType = new URLSearchParams(window.location.search).get('log_type') || 'admin';
                    window.location.href = `?log_type=${logType}`;
                });
            });
        });
    </script>
    <button id="scrollToTop" class="scroll-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>
</body>
</html>

