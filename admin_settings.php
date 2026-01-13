<?php
session_start();
include("php/config.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_valid']) || $_SESSION['admin_valid'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];
$page_title = "System Settings";

// Get current settings
$settings_query = mysqli_query($con, "SELECT * FROM system_settings");

// Initialize settings array
$settings = [];

// Check if query was successful
if ($settings_query) {
    while ($row = mysqli_fetch_assoc($settings_query)) {
        $settings[$row['setting_name']] = $row['setting_value'];
    }
} else {
    // Handle error - table might not exist
    $_SESSION['admin_message'] = "Settings table not found: " . mysqli_error($con);
    $_SESSION['admin_message_type'] = "error";
    
    // Create the table if it doesn't exist
    $create_table_query = "CREATE TABLE IF NOT EXISTS `system_settings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `setting_name` varchar(100) NOT NULL,
        `setting_value` text NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `setting_name` (`setting_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if (mysqli_query($con, $create_table_query)) {
        // Insert default settings
        $default_settings = [
            ['system_name', 'Asset Management System'],
            ['institution_name', 'University'],
            ['dashboard_refresh_interval', '60'],
            ['items_per_page', '10'],
            ['rfid_scan_interval', '2000'],
            ['rfid_data_file', 'rfid_data.txt'],
            ['enable_virtual_rfid', '1'],
            ['require_rfid_verification', '0'],
            ['default_asset_status', 'Active'],
            ['enable_auto_blacklist', '0'],
            ['officer_approval_required', '0'],
            ['asset_id_prefix', 'ASSET-'],
            ['max_login_attempts', '5'],
            ['session_timeout', '30'],
            ['enable_2fa', '0'],
            ['log_all_actions', '1'],
            ['enable_missing_asset_alerts', '1'],
            ['enable_email_notifications', '0'],
            ['admin_email', ''],
            ['enable_browser_notifications', '0']
        ];
        
        foreach ($default_settings as $setting) {
            mysqli_query($con, "INSERT INTO system_settings (setting_name, setting_value) VALUES ('$setting[0]', '$setting[1]')");
        }
        
        $_SESSION['admin_message'] = "Settings table created with default values.";
        $_SESSION['admin_message_type'] = "success";
    } else {
        $_SESSION['admin_message'] = "Failed to create settings table: " . mysqli_error($con);
        $_SESSION['admin_message_type'] = "error";
    }
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    // Process each setting
    $updated_settings = [
        'system_name' => $_POST['system_name'],
        'institution_name' => $_POST['institution_name'],
        'dashboard_refresh_interval' => intval($_POST['dashboard_refresh_interval']),
        'items_per_page' => intval($_POST['items_per_page']),
        'rfid_scan_interval' => intval($_POST['rfid_scan_interval']),
        'rfid_data_file' => $_POST['rfid_data_file'],
        'enable_virtual_rfid' => isset($_POST['enable_virtual_rfid']) ? '1' : '0',
        'require_rfid_verification' => isset($_POST['require_rfid_verification']) ? '1' : '0',
        'default_asset_status' => $_POST['default_asset_status'],
        'enable_auto_blacklist' => isset($_POST['enable_auto_blacklist']) ? '1' : '0',
        'officer_approval_required' => isset($_POST['officer_approval_required']) ? '1' : '0',
        'asset_id_prefix' => $_POST['asset_id_prefix'],
        'max_login_attempts' => intval($_POST['max_login_attempts']),
        'session_timeout' => intval($_POST['session_timeout']),
        'enable_2fa' => isset($_POST['enable_2fa']) ? '1' : '0',
        'log_all_actions' => isset($_POST['log_all_actions']) ? '1' : '0',
        'enable_missing_asset_alerts' => isset($_POST['enable_missing_asset_alerts']) ? '1' : '0',
        'enable_email_notifications' => isset($_POST['enable_email_notifications']) ? '1' : '0',
        'admin_email' => $_POST['admin_email'],
        'enable_browser_notifications' => isset($_POST['enable_browser_notifications']) ? '1' : '0'
    ];
    
    // Update settings in database
    foreach ($updated_settings as $name => $value) {
        mysqli_query($con, "INSERT INTO system_settings (setting_name, setting_value) 
                           VALUES ('$name', '$value')
                           ON DUPLICATE KEY UPDATE setting_value = '$value'");
    }
    
    $_SESSION['admin_message'] = "Settings updated successfully.";
    $_SESSION['admin_message_type'] = "success";
    
    // Refresh settings
    $settings_query = mysqli_query($con, "SELECT * FROM system_settings");
    $settings = [];
    while ($row = mysqli_fetch_assoc($settings_query)) {
        $settings[$row['setting_name']] = $row['setting_value'];
    }
}
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

        /* Settings Card */
        .settings-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: 0 4px 20px var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .settings-card-header {
            background: linear-gradient(135deg, var(--primary-dark), var(--accent-teal));
            color: var(--text-light);
            padding: 1.5rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .settings-card-header h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .settings-card-header h2 i {
            font-size: 1.2rem;
        }

        .settings-card-body {
            padding: 2rem;
        }

        /* Settings Content */
        .settings-content {
            display: none;
        }

        .settings-content.active {
            display: block;
        }

        /* Settings Tabs */
        .settings-tabs {
            display: flex;
            border-bottom: 1px solid #e3e6f0;
            margin-bottom: 1.5rem;
        }

        .settings-tab {
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            border: none;
            background: none;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
            font-size: inherit;
            font-family: inherit;
            color: inherit;
            text-align: left;
        }

        .settings-tab:focus {
            outline: none;
        }

        .settings-tab.active {
            border-bottom: 2px solid var(--primary);
            color: var(--primary);
            font-weight: 600;
        }

        .settings-tab:hover {
            background-color: #f8f9fc;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .setting-item {
            background: rgba(255, 255, 255, 0.9);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }

        .setting-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px var(--shadow);
        }

        .setting-item label {
            display: block;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .setting-item input,
        .setting-item select,
        .setting-item textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: var(--border-radius);
            background: rgba(255, 255, 255, 0.9);
            color: var(--text-dark);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .setting-item input:focus,
        .setting-item select:focus,
        .setting-item textarea:focus {
            outline: none;
            border-color: var(--accent-teal);
            box-shadow: 0 0 0 3px rgba(65, 115, 124, 0.1);
        }

        .setting-item textarea {
            resize: vertical;
            min-height: 80px;
        }

        .setting-item small {
            display: block;
            color: #666;
            margin-top: 0.25rem;
            font-size: 0.8rem;
        }

        /* Settings Table Styles */
        .settings-table {
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 4px 20px var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .settings-table thead {
            background: linear-gradient(135deg, var(--primary-dark), var(--accent-teal));
            color: var(--text-light);
        }

        .settings-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .settings-table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            vertical-align: top;
        }

        .settings-table tbody tr:hover {
            background: rgba(0, 0, 0, 0.02);
        }

        .settings-table .setting-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            display: block;
        }

        .settings-table .setting-description {
            color: #666;
            font-size: 0.8rem;
            display: block;
            margin-top: 0.25rem;
        }

        .settings-table input,
        .settings-table select,
        .settings-table textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: var(--border-radius);
            background: rgba(255, 255, 255, 0.9);
            color: var(--text-dark);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .settings-table input:focus,
        .settings-table select:focus,
        .settings-table textarea:focus {
            outline: none;
            border-color: var(--accent-teal);
            box-shadow: 0 0 0 3px rgba(65, 115, 124, 0.1);
        }

        .settings-table textarea {
            resize: vertical;
            min-height: 80px;
        }

        /* Toggle Switch Styles */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 24px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        input:checked + .toggle-slider {
            background-color: var(--accent-teal);
        }

        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }

        /* Form Actions */
        .form-actions {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 4px 20px var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }

        .form-actions button {
            background: linear-gradient(135deg, var(--accent-teal), var(--primary-dark));
            color: var(--text-light);
            border: none;
            padding: 1rem 2rem;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin: 0 0.5rem;
        }

        .form-actions button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(65, 115, 124, 0.3);
        }

        .form-actions .btn-secondary {
            background: rgba(255, 255, 255, 0.9);
            color: var(--text-dark);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .form-actions .btn-secondary:hover {
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        /* Alert Styles */
        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            border: 1px solid;
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            border-color: #4caf50;
            color: #2e7d32;
        }

        .alert-error {
            background: rgba(244, 67, 54, 0.1);
            border-color: #f44336;
            color: #c62828;
        }

        .alert-warning {
            background: rgba(255, 152, 0, 0.1);
            border-color: #ff9800;
            color: #e65100;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .settings-table th,
            .settings-table td {
                padding: 0.8rem 0.5rem;
            }

            .settings-card-body {
                padding: 1.5rem;
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

            .settings-card-body {
                padding: 1rem;
            }

            .settings-table {
                font-size: 0.9rem;
            }

            .settings-table th,
            .settings-table td {
                padding: 0.6rem 0.4rem;
            }

            .settings-card-header {
                padding: 1rem 1.5rem;
            }

            .settings-card-header h2 {
                font-size: 1.3rem;
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
                <li><a href="admin_logs.php"><i class="fas fa-history"></i> System Logs</a></li>
                <li><a href="admin_settings.php" class="active" id="settings-menu-link"><i class="fas fa-cog"></i> Settings - General</a></li>
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
            
            <!-- Display Messages -->
            <?php if (isset($_SESSION['admin_message'])): ?>
                <div class="message <?php echo $_SESSION['admin_message_type']; ?>">
                    <?php echo $_SESSION['admin_message']; ?>
                </div>
                <?php 
                    unset($_SESSION['admin_message']);
                    unset($_SESSION['admin_message_type']);
                ?>
            <?php endif; ?>
            
            <!-- Settings Tabs -->
            <div class="settings-tabs">
                <button type="button" class="settings-tab active" data-tab="general">General Settings</button>
                <button type="button" class="settings-tab" data-tab="rfid">RFID Configuration</button>
                <button type="button" class="settings-tab" data-tab="asset">Asset Management</button>
                <button type="button" class="settings-tab" data-tab="security">Security Settings</button>
                <button type="button" class="settings-tab" data-tab="notifications">Notifications</button>
            </div>

            <!-- Single Settings Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <h2 id="card-title"><i class="fas fa-cogs"></i> General Settings</h2>
                </div>
                <div class="settings-card-body">
                    <!-- Settings Form -->
                    <form class="settings-form" method="post" action="">
                        <!-- General Settings Content -->
                        <div class="settings-content active" id="general-content">
                            <table class="settings-table">
                                <thead>
                                    <tr>
                                        <th>Setting</th>
                                        <th>Value</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><label for="system_name">System Name</label></td>
                                        <td><input type="text" id="system_name" name="system_name" value="<?php echo htmlspecialchars($settings['system_name'] ?? 'Asset Management System'); ?>"></td>
                                        <td><small>The name displayed throughout the system</small></td>
                                    </tr>
                                    <tr>
                                        <td><label for="institution_name">Institution Name</label></td>
                                        <td><input type="text" id="institution_name" name="institution_name" value="<?php echo htmlspecialchars($settings['institution_name'] ?? 'University'); ?>"></td>
                                        <td><small>The name of your institution or organization</small></td>
                                    </tr>
                                    <tr>
                                        <td><label for="dashboard_refresh_interval">Dashboard Refresh Interval (seconds)</label></td>
                                        <td><input type="number" id="dashboard_refresh_interval" name="dashboard_refresh_interval" min="10" max="300" value="<?php echo intval($settings['dashboard_refresh_interval'] ?? 60); ?>"></td>
                                        <td><small>How often the dashboard data refreshes (10-300 seconds)</small></td>
                                    </tr>
                                    <tr>
                                        <td><label for="items_per_page">Items Per Page</label></td>
                                        <td><input type="number" id="items_per_page" name="items_per_page" min="5" max="100" value="<?php echo intval($settings['items_per_page'] ?? 10); ?>"></td>
                                        <td><small>Number of items to display per page in lists (5-100)</small></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- RFID Configuration Content -->
                        <div class="settings-content" id="rfid-content">
                            <table class="settings-table">
                                <thead>
                                    <tr>
                                        <th>Setting</th>
                                        <th>Value</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><label for="rfid_scan_interval">RFID Scan Interval (milliseconds)</label></td>
                                        <td><input type="number" id="rfid_scan_interval" name="rfid_scan_interval" min="500" max="10000" value="<?php echo intval($settings['rfid_scan_interval'] ?? 2000); ?>"></td>
                                        <td><small>Time between RFID scans in milliseconds (500-10000)</small></td>
                                    </tr>
                                    <tr>
                                        <td><label for="rfid_data_file">RFID Data File Path</label></td>
                                        <td><input type="text" id="rfid_data_file" name="rfid_data_file" value="<?php echo htmlspecialchars($settings['rfid_data_file'] ?? 'rfid_data.txt'); ?>"></td>
                                        <td><small>Path to the RFID data file</small></td>
                                    </tr>
                                    <tr>
                                        <td><label for="enable_virtual_rfid">Enable Virtual RFID</label></td>
                                        <td>
                                            <select id="enable_virtual_rfid" name="enable_virtual_rfid">
                                                <option value="1" <?php echo (isset($settings['enable_virtual_rfid']) && $settings['enable_virtual_rfid'] == '1') ? 'selected' : ''; ?>>Enabled</option>
                                                <option value="0" <?php echo (!isset($settings['enable_virtual_rfid']) || $settings['enable_virtual_rfid'] == '0') ? 'selected' : ''; ?>>Disabled</option>
                                            </select>
                                        </td>
                                        <td><small>Enable virtual RFID simulation for testing</small></td>
                                    </tr>
                                    <tr>
                                        <td><label for="require_rfid_verification">Require RFID Verification</label></td>
                                        <td>
                                            <select id="require_rfid_verification" name="require_rfid_verification">
                                                <option value="1" <?php echo (isset($settings['require_rfid_verification']) && $settings['require_rfid_verification'] == '1') ? 'selected' : ''; ?>>Required</option>
                                                <option value="0" <?php echo (!isset($settings['require_rfid_verification']) || $settings['require_rfid_verification'] == '0') ? 'selected' : ''; ?>>Optional</option>
                                            </select>
                                        </td>
                                        <td><small>Require RFID verification for asset registration</small></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Asset Management Content -->
                        <div class="settings-content" id="asset-content">
                            <table class="settings-table">
                                <thead>
                                    <tr>
                                        <th>Setting</th>
                                        <th>Value</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><label for="default_asset_status">Default Asset Status</label></td>
                                        <td>
                                            <select id="default_asset_status" name="default_asset_status">
                                                <option value="Active" <?php echo (isset($settings['default_asset_status']) && $settings['default_asset_status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                                <option value="Inactive" <?php echo (isset($settings['default_asset_status']) && $settings['default_asset_status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </td>
                                        <td><small>Default status for newly registered assets</small></td>
                                    </tr>
                                    <tr>
                                        <td><label for="enable_auto_blacklist">Auto-Blacklist Missing Assets</label></td>
                                        <td>
                                            <select id="enable_auto_blacklist" name="enable_auto_blacklist">
                                                <option value="1" <?php echo (isset($settings['enable_auto_blacklist']) && $settings['enable_auto_blacklist'] == '1') ? 'selected' : ''; ?>>Enabled</option>
                                                <option value="0" <?php echo (!isset($settings['enable_auto_blacklist']) || $settings['enable_auto_blacklist'] == '0') ? 'selected' : ''; ?>>Disabled</option>
                                            </select>
                                        </td>
                                        <td><small>Automatically blacklist assets reported missing multiple times</small></td>
                                    </tr>
                                    <tr>
                                        <td><label for="officer_approval_required">Require Officer Approval</label></td>
                                        <td>
                                            <select id="officer_approval_required" name="officer_approval_required">
                                                <option value="1" <?php echo (isset($settings['officer_approval_required']) && $settings['officer_approval_required'] == '1') ? 'selected' : ''; ?>>Required</option>
                                                <option value="0" <?php echo (!isset($settings['officer_approval_required']) || $settings['officer_approval_required'] == '0') ? 'selected' : ''; ?>>Not Required</option>
                                            </select>
                                        </td>
                                        <td><small>Require security officer approval for asset status changes</small></td>
                                    </tr>
                                    <tr>
                                        <td><label for="asset_id_prefix">Asset ID Prefix</label></td>
                                        <td><input type="text" id="asset_id_prefix" name="asset_id_prefix" value="<?php echo htmlspecialchars($settings['asset_id_prefix'] ?? 'ASSET-'); ?>"></td>
                                        <td><small>Prefix used for generating asset IDs</small></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Security Settings Content -->
                        <div class="settings-content" id="security-content">
                            <table class="settings-table">
                                <thead>
                                    <tr>
                                        <th>Setting</th>
                                        <th>Value</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><label for="max_login_attempts">Maximum Login Attempts</label></td>
                                        <td><input type="number" id="max_login_attempts" name="max_login_attempts" min="1" max="10" value="<?php echo intval($settings['max_login_attempts'] ?? 5); ?>"></td>
                                        <td><small>Number of failed login attempts before account lockout (1-10)</small></td>
                                    </tr>
                                    <tr>
                                        <td><label for="session_timeout">Session Timeout (minutes)</label></td>
                                        <td><input type="number" id="session_timeout" name="session_timeout" min="5" max="120" value="<?php echo intval($settings['session_timeout'] ?? 30); ?>"></td>
                                        <td><small>How long before inactive sessions expire (5-120 minutes)</small></td>
                                    </tr>
                                    <tr>
                                        <td><label for="enable_2fa">Two-Factor Authentication</label></td>
                                        <td>
                                            <select id="enable_2fa" name="enable_2fa">
                                                <option value="1" <?php echo (isset($settings['enable_2fa']) && $settings['enable_2fa'] == '1') ? 'selected' : ''; ?>>Enabled</option>
                                                <option value="0" <?php echo (!isset($settings['enable_2fa']) || $settings['enable_2fa'] == '0') ? 'selected' : ''; ?>>Disabled</option>
                                            </select>
                                        </td>
                                        <td><small>Require two-factor authentication for admin accounts</small></td>
                                    </tr>
                                    <tr>
                                        <td><label for="log_all_actions">Log All Actions</label></td>
                                        <td>
                                            <select id="log_all_actions" name="log_all_actions">
                                                <option value="1" <?php echo (isset($settings['log_all_actions']) && $settings['log_all_actions'] == '1') ? 'selected' : ''; ?>>Enabled</option>
                                                <option value="0" <?php echo (!isset($settings['log_all_actions']) || $settings['log_all_actions'] == '0') ? 'selected' : ''; ?>>Disabled</option>
                                            </select>
                                        </td>
                                        <td><small>Log all user actions for audit purposes</small></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Notifications Content -->
                        <div class="settings-content" id="notifications-content">
                            <table class="settings-table">
                                <thead>
                                    <tr>
                                        <th>Setting</th>
                                        <th>Value</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><label for="enable_missing_asset_alerts">Missing Asset Alerts</label></td>
                                        <td>
                                            <label class="toggle-switch">
                                                <input type="checkbox" id="enable_missing_asset_alerts" name="enable_missing_asset_alerts" <?php echo (isset($settings['enable_missing_asset_alerts']) && $settings['enable_missing_asset_alerts'] == '1') ? 'checked' : ''; ?>>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </td>
                                        <td><small>Receive notifications when assets are reported missing</small></td>
                                    </tr>
                                    <tr>
                                        <td><label for="enable_email_notifications">Email Notifications</label></td>
                                        <td>
                                            <label class="toggle-switch">
                                                <input type="checkbox" id="enable_email_notifications" name="enable_email_notifications" <?php echo (isset($settings['enable_email_notifications']) && $settings['enable_email_notifications'] == '1') ? 'checked' : ''; ?>>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </td>
                                        <td><small>Send notifications via email to administrators</small></td>
                                    </tr>
                                    <tr>
                                        <td><label for="admin_email">Admin Email Address</label></td>
                                        <td><input type="email" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($settings['admin_email'] ?? ''); ?>" placeholder="admin@example.com"></td>
                                        <td><small>Primary email address for system notifications</small></td>
                                    </tr>
                                    <tr>
                                        <td><label for="enable_browser_notifications">Browser Notifications</label></td>
                                        <td>
                                            <label class="toggle-switch">
                                                <input type="checkbox" id="enable_browser_notifications" name="enable_browser_notifications" <?php echo (isset($settings['enable_browser_notifications']) && $settings['enable_browser_notifications'] == '1') ? 'checked' : ''; ?>>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </td>
                                        <td><small>Show notifications in the browser when available</small></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-undo"></i> Reset Changes
                            </button>
                            <button type="submit" name="save_settings" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Tab switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.settings-tab');
            const contents = document.querySelectorAll('.settings-content');
            const cardTitle = document.getElementById('card-title');
            const settingsMenuLink = document.getElementById('settings-menu-link');

            // Tab configuration
            const tabConfig = {
                'general': {
                    title: '<i class="fas fa-cogs"></i> General Settings',
                    menuText: 'Settings - General',
                    contentId: 'general-content'
                },
                'rfid': {
                    title: '<i class="fas fa-wifi"></i> RFID Configuration',
                    menuText: 'Settings - RFID',
                    contentId: 'rfid-content'
                },
                'asset': {
                    title: '<i class="fas fa-boxes"></i> Asset Management',
                    menuText: 'Settings - Assets',
                    contentId: 'asset-content'
                },
                'security': {
                    title: '<i class="fas fa-shield-alt"></i> Security Settings',
                    menuText: 'Settings - Security',
                    contentId: 'security-content'
                },
                'notifications': {
                    title: '<i class="fas fa-bell"></i> Notification Settings',
                    menuText: 'Settings - Notifications',
                    contentId: 'notifications-content'
                }
            };

            tabs.forEach(tab => {
                tab.addEventListener('click', function(event) {
                    event.preventDefault();
                    
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));

                    // Add active class to clicked tab
                    this.classList.add('active');

                    // Get tab type
                    const tabType = this.getAttribute('data-tab');
                    const config = tabConfig[tabType];

                    // Update card title
                    cardTitle.innerHTML = config.title;

                    // Update sidebar menu text
                    if (settingsMenuLink) {
                        settingsMenuLink.innerHTML = '<i class="fas fa-cog"></i> ' + config.menuText;
                    }

                    // Hide all content
                    contents.forEach(c => c.classList.remove('active'));

                    // Show selected content
                    document.getElementById(config.contentId).classList.add('active');
                });
            });
        });
    </script>
</body>
</html>