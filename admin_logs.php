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
    <link rel="stylesheet" href="style/home-modern.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Admin - <?php echo $page_title; ?></title>
    <style>
        /* Color Palette from schome.php */
        :root {
            --primary-dark: #4b648d;
            --primary-light: #e7fbf9;
            --accent-teal: #41737c;
            --text-dark: #2c3e50;
            --text-light: #ffffff;
            --shadow: rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        body {
            background: linear-gradient(-45deg, #4b648d, #41737c, #4b648d, #41737c);
            background-size: 400% 400%;
            animation: gradientBG 12s ease infinite;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* Particle Background */
        .particles-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0.5));
            border-radius: 50%;
            box-shadow: 0 0 25px rgba(255, 255, 255, 0.5);
            animation: float 25s infinite linear;
            backdrop-filter: blur(2px);
        }

        .particle:nth-child(odd) {
            animation-duration: 30s;
            animation-delay: -5s;
        }

        .particle:nth-child(even) {
            animation-duration: 35s;
            animation-delay: -10s;
        }

        .particle:nth-child(3n) {
            background: linear-gradient(45deg, rgba(231, 251, 249, 0.9), rgba(65, 115, 124, 0.6));
            box-shadow: 0 0 30px rgba(65, 115, 124, 0.7);
        }

        .particle:nth-child(4n) {
            background: linear-gradient(45deg, rgba(75, 100, 141, 0.8), rgba(255, 255, 255, 0.4));
            box-shadow: 0 0 35px rgba(75, 100, 141, 0.5);
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg) scale(0.3);
                opacity: 0;
            }
            10% {
                opacity: 0.9;
                transform: translateY(90vh) rotate(36deg) scale(1.2);
            }
            50% {
                opacity: 1;
                transform: translateY(50vh) rotate(180deg) scale(1.5);
            }
            90% {
                opacity: 0.9;
                transform: translateY(10vh) rotate(324deg) scale(1.2);
            }
            100% {
                transform: translateY(-10vh) rotate(360deg) scale(0.3);
                opacity: 0;
            }
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Main Container */
        .admin-container {
            display: flex;
            min-height: 100vh;
            position: relative;
            z-index: 2;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, var(--primary-dark), var(--accent-teal));
            color: var(--text-light);
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            z-index: 100;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 15px var(--shadow);
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
            transition: var(--transition);
            border-left: 4px solid transparent;
        }

        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            color: var(--text-light);
            background: rgba(255, 255, 255, 0.1);
            border-left-color: var(--primary-light);
            transform: translateX(5px);
        }

        .sidebar-menu i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }

        /* Content Wrapper */
        .content-wrapper {
            margin-left: 280px;
            width: calc(100% - 280px);
            min-height: 100vh;
        }

        /* Topbar */
        .topbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px var(--shadow);
            height: 70px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .topbar h1 {
            color: var(--text-dark);
            font-size: 1.8rem;
            font-weight: 600;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-info span {
            color: var(--text-dark);
            font-weight: 500;
        }

        .logout-btn {
            background: linear-gradient(135deg, var(--accent-teal), var(--primary-dark));
            color: var(--text-light);
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: var(--border-radius);
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(65, 115, 124, 0.3);
        }

        /* Main Content */
        .main-content {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Tab Navigation */
        .tab-navigation {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .tab-navigation a {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            margin-right: 0.5rem;
            background: rgba(255, 255, 255, 0.8);
            color: var(--text-dark);
            text-decoration: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .tab-navigation a:hover,
        .tab-navigation a.active {
            background: var(--accent-teal);
            color: var(--text-light);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(65, 115, 124, 0.3);
        }

        .tab-navigation a i {
            margin-right: 0.5rem;
        }

        /* Filters */
        .filters {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .filter-group {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
        }

        .filter-group label {
            font-weight: 600;
            color: var(--text-dark);
            margin-right: 0.5rem;
        }

        .filter-group .form-control {
            padding: 0.8rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: var(--border-radius);
            background: rgba(255, 255, 255, 0.9);
            color: var(--text-dark);
            font-size: 0.9rem;
            min-width: 150px;
        }

        .filter-group .form-control:focus {
            outline: none;
            border-color: var(--accent-teal);
            box-shadow: 0 0 0 3px rgba(65, 115, 124, 0.1);
        }

        .filter-group button {
            background: linear-gradient(135deg, var(--accent-teal), var(--primary-dark));
            color: var(--text-light);
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .filter-group button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(65, 115, 124, 0.3);
        }

        /* Export Button Styles (matching asset button style) */
        .export-btn-inline {
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

        .export-btn-inline::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .export-btn-inline:hover::before {
            left: 100%;
        }

        .export-btn-inline:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
            background: linear-gradient(135deg, #0056b3, #004085);
        }

        .export-btn-inline:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(0, 123, 255, 0.3);
        }

        .export-btn-inline.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .export-btn-inline.loading::after {
            content: '';
            width: 16px;
            height: 16px;
            margin-left: 8px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Table Styles */
        .table-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: 0 4px 20px var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background: linear-gradient(135deg, var(--primary-dark), var(--accent-teal));
            color: var(--text-light);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            color: var(--text-dark);
        }

        .table tbody tr:hover {
            background: rgba(65, 115, 124, 0.05);
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Status badges */
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-warning {
            background: #fff3e0;
            color: #e65100;
        }

        .status-danger {
            background: #ffebee;
            color: #c62828;
        }

        .status-info {
            background: #e3f2fd;
            color: #1565c0;
        }

        /* Alert Styles */
        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            border: 1px solid;
        }

        .alert-danger {
            background: rgba(244, 67, 54, 0.1);
            border-color: #f44336;
            color: #c62828;
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            border-color: #4caf50;
            color: #2e7d32;
        }

        .alert-warning {
            background: rgba(255, 152, 0, 0.1);
            border-color: #ff9800;
            color: #e65100;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination a,
        .pagination span {
            padding: 0.8rem 1rem;
            background: rgba(255, 255, 255, 0.9);
            color: var(--text-dark);
            text-decoration: none;
            border-radius: var(--border-radius);
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .pagination a:hover,
        .pagination .current {
            background: var(--accent-teal);
            color: var(--text-light);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .content-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                overflow: hidden;
            }
            
            .content-wrapper {
                margin-left: 0;
                width: 100%;
            }
            
            .sidebar.active {
                width: 280px;
            }

            .topbar h1 {
                font-size: 1.4rem;
            }

            .main-content {
                padding: 1rem;
            }

            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group .form-control {
                min-width: auto;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .sidebar {
            animation: slideInLeft 0.8s ease-out;
        }

        .topbar {
            animation: fadeIn 0.6s ease-out 0.2s both;
        }

        .main-content {
            animation: fadeIn 0.8s ease-out 0.4s both;
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
                    <i class="fas fa-shield-alt"></i>
                    <div class="title">Admin Portal</div>
                </div>
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="admin_assets.php"><i class="fas fa-laptop"></i> Assets</a></li>
                <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="admin_security_approvals.php"><i class="fas fa-user-shield"></i> Security Personnel</a></li>
                <li><a href="admin_logs.php" class="active"><i class="fas fa-history"></i> System Logs</a></li>
                <li><a href="admin_settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="welcome.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
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
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <i class="fas fa-user-shield" style="font-size: 24px;"></i>
                </div>
            </div>

            <!-- Main Content -->
            <div class="main-content">
            
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
                
                <!-- Export Button -->
                <div style="margin-top: 15px;">
                    <a href="export_logs.php?type=admin" class="export-btn-inline">
                        <i class="fas fa-file-export"></i> Export CSV
                    </a>
                </div>
                
                <!-- Admin Logs Table -->
                <div class="card">
                    <div class="card-header">
                        <h3>Admin Activity Logs</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($logs_query && mysqli_num_rows($logs_query) > 0): ?>
                            <div class="table-container">
                                <table class="table">
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
                                                <span class="status-badge 
                                                    <?php 
                                                        if (strpos($log['action'], 'asset_') === 0) echo 'status-info';
                                                        elseif (strpos($log['action'], 'user_') === 0) echo 'status-success';
                                                        elseif (strpos($log['action'], 'security_') === 0) echo 'status-warning';
                                                        elseif ($log['action'] == 'login') echo 'status-success';
                                                        elseif ($log['action'] == 'logout') echo 'status-info';
                                                        elseif ($log['action'] == 'create') echo 'status-success';
                                                        elseif ($log['action'] == 'update') echo 'status-warning';
                                                        elseif ($log['action'] == 'delete') echo 'status-danger';
                                                        else echo 'status-info';
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
                            </div>
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
                
                <!-- Export Button -->
                <div style="margin-top: 15px;">
                    <a href="export_logs.php?type=scan" class="export-btn-inline">
                        <i class="fas fa-file-export"></i> Export CSV
                    </a>
                </div>
                
                <!-- Scan Logs Table -->
                <div class="card">
                    <div class="card-header">
                        <h3>RFID Scan Logs</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($logs_query && mysqli_num_rows($logs_query) > 0): ?>
                            <div class="table-container">
                                <table class="table">
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
                                                        <small>Status: <span class="status-badge status-<?php echo strtolower(htmlspecialchars($log['rfid_status'])); ?>">
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
                                                <span class="status-badge status-<?php echo htmlspecialchars($log['status']); ?>">
                                                    <?php echo htmlspecialchars($log['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('Y-m-d H:i:s', strtotime($log['scan_time'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            </div>
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

    <script src="js/particles.js"></script>
    <script src="js/home.js"></script>
    <script>
        // Initialize particles
        document.addEventListener('DOMContentLoaded', function() {
            const particlesContainer = document.querySelector('.particles-container');
            for (let i = 0; i < 50; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 25 + 's';
                particlesContainer.appendChild(particle);
            }
        });

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

        // Sidebar toggle for mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });
            }
        });

        // Export button loading state
        document.addEventListener('DOMContentLoaded', function() {
            const exportButtons = document.querySelectorAll('.export-btn-inline');
            exportButtons.forEach(button => {
                button.addEventListener('click', function() {
                    this.classList.add('loading');
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exporting...';

                    // Remove loading state after 2 seconds (in case download doesn't start immediately)
                    setTimeout(() => {
                        this.classList.remove('loading');
                        this.innerHTML = '<i class="fas fa-file-export"></i> Export CSV';
                    }, 2000);
                });
            });
        });
    </script>
    <button id="scrollToTop" class="scroll-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>
</body>
</html>

