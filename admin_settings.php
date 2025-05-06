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
        
        .settings-card {
            background-color: white;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 1.5rem;
        }
        
        .settings-card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e3e6f0;
            background-color: #f8f9fc;
            color: var(--dark);
            font-weight: 700;
        }
        
        .settings-card-body {
            padding: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="number"],
        .form-group select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d3e2;
            border-radius: 0.35rem;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
        }
        
        .checkbox-group input[type="checkbox"] {
            margin-right: 0.5rem;
        }
        
        .btn-save {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-save:hover {
            background-color: #2e59d9;
        }
        
        .btn-reset {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-left: 0.5rem;
        }
        
        .btn-reset:hover {
            background-color: #717384;
        }
        
        .form-actions {
            margin-top: 1.5rem;
            text-align: right;
        }
        
        .settings-tabs {
            display: flex;
            border-bottom: 1px solid #e3e6f0;
            margin-bottom: 1.5rem;
        }
        
        .settings-tab {
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
        }
        
        .settings-tab.active {
            border-bottom: 2px solid var(--primary);
            color: var(--primary);
            font-weight: 600;
        }
        
        .settings-tab:hover {
            background-color: #f8f9fc;
        }
        
        .settings-section {
            display: none;
        }
        
        .settings-section.active {
            display: block;
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
                <li><a href="welcome.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
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
                    unset($_SESSION['admin_message']);
                    unset($_SESSION['admin_message_type']);
                ?>
            <?php endif; ?>
            
            <!-- Settings Tabs -->
            <div class="settings-tabs">
                <div class="settings-tab active" data-tab="general">General Settings</div>
                <div class="settings-tab" data-tab="rfid">RFID Configuration</div>
                <div class="settings-tab" data-tab="asset">Asset Management</div>
                <div class="settings-tab" data-tab="security">Security Settings</div>
                <div class="settings-tab" data-tab="notifications">Notifications</div>
            </div>
            
            <!-- Settings Form -->
            <form class="settings-form" method="post" action="">
                <!-- General Settings Section -->
                <div class="settings-section active" id="general-settings">
                    <div class="settings-card">
                        <div class="settings-card-header">
                            General Settings
                        </div>
                        <div class="settings-card-body">
                            <div class="form-group">
                                <label for="system_name">System Name</label>
                                <input type="text" id="system_name" name="system_name" value="<?php echo htmlspecialchars($settings['system_name'] ?? 'Asset Management System'); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="institution_name">Institution Name</label>
                                <input type="text" id="institution_name" name="institution_name" value="<?php echo htmlspecialchars($settings['institution_name'] ?? 'University'); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="dashboard_refresh_interval">Dashboard Refresh Interval (seconds)</label>
                                <input type="number" id="dashboard_refresh_interval" name="dashboard_refresh_interval" min="10" max="300" value="<?php echo intval($settings['dashboard_refresh_interval'] ?? 60); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="items_per_page">Items Per Page</label>
                                <input type="number" id="items_per_page" name="items_per_page" min="5" max="100" value="<?php echo intval($settings['items_per_page'] ?? 10); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- RFID Configuration Section -->
                <div class="settings-section" id="rfid-settings">
                    <div class="settings-card">
                        <div class="settings-card-header">
                            RFID Configuration
                        </div>
                        <div class="settings-card-body">
                            <div class="form-group">
                                <label for="rfid_scan_interval">RFID Scan Interval (milliseconds)</label>
                                <input type="number" id="rfid_scan_interval" name="rfid_scan_interval" min="500" max="10000" value="<?php echo intval($settings['rfid_scan_interval'] ?? 2000); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="rfid_data_file">RFID Data File Path</label>
                                <input type="text" id="rfid_data_file" name="rfid_data_file" value="<?php echo htmlspecialchars($settings['rfid_data_file'] ?? 'rfid_data.txt'); ?>">
                            </div>
                            
                            
                            
                            <div class="form-group checkbox-group">
                                <input type="checkbox" id="require_rfid_verification" name="require_rfid_verification" <?php echo (isset($settings['require_rfid_verification']) && $settings['require_rfid_verification'] == '1') ? 'checked' : ''; ?>>
                                <label for="require_rfid_verification">Require RFID Verification for Asset Registration</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Asset Management Section -->
                <div class="settings-section" id="asset-settings">
                    <div class="settings-card">
                        <div class="settings-card-header">
                            Asset Management
                        </div>
                        <div class="settings-card-body">
                            <div class="form-group">
                                <label for="default_asset_status">Default Asset Status</label>
                                <select id="default_asset_status" name="default_asset_status">
                                    <option value="Active" <?php echo (isset($settings['default_asset_status']) && $settings['default_asset_status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="Inactive" <?php echo (isset($settings['default_asset_status']) && $settings['default_asset_status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            
                            <div class="form-group checkbox-group">
                                <input type="checkbox" id="enable_auto_blacklist" name="enable_auto_blacklist" <?php echo (isset($settings['enable_auto_blacklist']) && $settings['enable_auto_blacklist'] == '1') ? 'checked' : ''; ?>>
                                <label for="enable_auto_blacklist">Enable Auto-Blacklisting for Multiple Missing Reports</label>
                            </div>
                            
                            <div class="form-group checkbox-group">
                                <input type="checkbox" id="officer_approval_required" name="officer_approval_required" <?php echo (isset($settings['officer_approval_required']) && $settings['officer_approval_required'] == '1') ? 'checked' : ''; ?>>
                                <label for="officer_approval_required">Require Officer Approval for Asset Status Changes</label>
                            </div>
                            
                            <div class="form-group">
                                <label for="asset_id_prefix">Asset ID Prefix</label>
                                <input type="text" id="asset_id_prefix" name="asset_id_prefix" value="<?php echo htmlspecialchars($settings['asset_id_prefix'] ?? 'ASSET-'); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Security Settings Section -->
                <div class="settings-section" id="security-settings">
                    <div class="settings-card">
                        <div class="settings-card-header">
                            Security Settings
                        </div>
                        <div class="settings-card-body">
                            <div class="form-group">
                                <label for="max_login_attempts">Maximum Login Attempts</label>
                                <input type="number" id="max_login_attempts" name="max_login_attempts" min="1" max="10" value="<?php echo intval($settings['max_login_attempts'] ?? 5); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="session_timeout">Session Timeout (minutes)</label>
                                <input type="number" id="session_timeout" name="session_timeout" min="5" max="120" value="<?php echo intval($settings['session_timeout'] ?? 30); ?>">
                            </div>
                            
                            <div class="form-group checkbox-group">
                                <input type="checkbox" id="enable_2fa" name="enable_2fa" <?php echo (isset($settings['enable_2fa']) && $settings['enable_2fa'] == '1') ? 'checked' : ''; ?>>
                                <label for="enable_2fa">Enable Two-Factor Authentication for Admins</label>
                            </div>
                            
                            <div class="form-group checkbox-group">
                                <input type="checkbox" id="log_all_actions" name="log_all_actions" <?php echo (isset($settings['log_all_actions']) && $settings['log_all_actions'] == '1') ? 'checked' : ''; ?>>
                                <label for="log_all_actions">Log All User Actions</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Notifications Section -->
                <div class="settings-section" id="notifications-settings">
                    <div class="settings-card">
                        <div class="settings-card-header">
                            Notification Settings
                        </div>
                        <div class="settings-card-body">
                            <div class="form-group checkbox-group">
                                <input type="checkbox" id="enable_missing_asset_alerts" name="enable_missing_asset_alerts" <?php echo (isset($settings['enable_missing_asset_alerts']) && $settings['enable_missing_asset_alerts'] == '1') ? 'checked' : ''; ?>>
                                <label for="enable_missing_asset_alerts">Enable Missing Asset Alerts</label>
                            </div>
                            
                            <div class="form-group checkbox-group">
                                <input type="checkbox" id="enable_email_notifications" name="enable_email_notifications" <?php echo (isset($settings['enable_email_notifications']) && $settings['enable_email_notifications'] == '1') ? 'checked' : ''; ?>>
                                <label for="enable_email_notifications">Enable Email Notifications</label>
                            </div>
                            
                            <div class="form-group">
                                <label for="admin_email">Admin Email Address</label>
                                <input type="email" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($settings['admin_email'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group checkbox-group">
                                <input type="checkbox" id="enable_browser_notifications" name="enable_browser_notifications" <?php echo (isset($settings['enable_browser_notifications']) && $settings['enable_browser_notifications'] == '1') ? 'checked' : ''; ?>>
                                <label for="enable_browser_notifications">Enable Browser Notifications</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="reset" class="btn-reset">Reset</button>
                    <button type="submit" name="save_settings" class="btn-save">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Tab switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.settings-tab');
            const sections = document.querySelectorAll('.settings-section');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs and sections
                    tabs.forEach(t => t.classList.remove('active'));
                    sections.forEach(s => s.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Show corresponding section
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId + '-settings').classList.add('active');
                });
            });
        });
    </script>
</body>
</html>