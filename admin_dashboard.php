<?php
session_start();
include("php/config.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_valid']) || $_SESSION['admin_valid'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Get dashboard statistics with proper error handling

// 1. Total assets
$assets_query = mysqli_query($con, "SELECT COUNT(*) as total FROM assets");
if (!$assets_query) {
    error_log("Assets query failed: " . mysqli_error($con));
    $total_assets = 0;
} else {
    $total_assets = mysqli_fetch_assoc($assets_query)['total'];
}

// 2. Missing assets (using rfid_status field)
$missing_query = mysqli_query($con, "SELECT COUNT(*) as total FROM assets WHERE AssetStatus = 'missing'");
if (!$missing_query) {
    error_log("Missing assets query failed: " . mysqli_error($con));
    $missing_assets = 0;
} else {
    $missing_assets = mysqli_fetch_assoc($missing_query)['total'];
}

// 3. Blacklisted assets (using rfid_status field)
$blacklisted_query = mysqli_query($con, "SELECT COUNT(*) as total FROM assets WHERE AssetStatus = 'blacklisted'");
if (!$blacklisted_query) {
    error_log("Blacklisted assets query failed: " . mysqli_error($con));
    $blacklisted_assets = 0;
} else {
    $blacklisted_assets = mysqli_fetch_assoc($blacklisted_query)['total'];
}

// 4. Total users
$users_query = mysqli_query($con, "SELECT COUNT(*) as total FROM users");
if (!$users_query) {
    error_log("Users query failed: " . mysqli_error($con));
    $total_users = 0;
} else {
    $total_users = mysqli_fetch_assoc($users_query)['total'];
}

// 5. Total security personnel
$security_query = mysqli_query($con, "SELECT COUNT(*) as total FROM scpersonnel");
if (!$security_query) {
    error_log("Security personnel query failed: " . mysqli_error($con));
    $total_security = 0;
} else {
    $total_security = mysqli_fetch_assoc($security_query)['total'];
}

// 6. Pending security personnel approvals
$pending_query = mysqli_query($con, "SELECT COUNT(*) as count FROM scpersonnel WHERE status='pending' OR status IS NULL");
if (!$pending_query) {
    error_log("Pending approvals query failed: " . mysqli_error($con));
    $pending_count = 0;
} else {
    $pending_result = mysqli_fetch_assoc($pending_query);
    $pending_count = $pending_result['count'];
}

// 7. Recent activities (from admin_logs)
$logs_query = mysqli_query($con, "SELECT l.*, a.username as admin_name 
                                FROM admin_logs l 
                                JOIN admins a ON l.admin_id = a.id 
                                ORDER BY l.created_at DESC 
                                LIMIT 10");
if (!$logs_query) {
    error_log("Logs query failed: " . mysqli_error($con));
    $logs_query = mysqli_query($con, "SELECT 1 as id LIMIT 0"); // Empty result set
}

// 8. Recent assets
$recent_assets_query = mysqli_query($con, "SELECT 
    a.item_id, 
    a.item_model as AssetName, 
    a.rfid_status as AssetStatus,
    u.Username,
    u.Lastname
    FROM assets a
    LEFT JOIN users u ON a.reg_number = u.Reg_Number
    ORDER BY a.item_id DESC 
    LIMIT 5");

if (!$recent_assets_query) {
    error_log("Recent assets query failed: " . mysqli_error($con));
    $recent_assets_query = mysqli_query($con, "SELECT 1 as item_id LIMIT 0"); // Empty result set
}

// Get weekly scanning stats (example - adjust based on your scan_logs table)
$weekly_scanning_stats = [];
$scan_stats_query = mysqli_query($con, "SELECT 
    DAYNAME(scan_time) as day, 
    COUNT(*) as count 
    FROM scan_logs 
    WHERE scan_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DAYNAME(scan_time)");

if ($scan_stats_query) {
    while ($row = mysqli_fetch_assoc($scan_stats_query)) {
        $weekly_scanning_stats[$row['day']] = $row['count'];
    }
}

// Convert data to JSON for JavaScript
$weekly_scanning_json = json_encode($weekly_scanning_stats);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" href="style/style.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
      <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
      <link rel="stylesheet" href="responsive.css">
      <title>Admin Dashboard</title>
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
        
          .stats-row {
              display: flex;
              flex-wrap: wrap;
              margin-right: -0.75rem;
              margin-left: -0.75rem;
              margin-bottom: 1.5rem;
          }
        
          .stat-card {
              flex: 0 0 25%;
              max-width: 25%;
              padding-right: 0.75rem;
              padding-left: 0.75rem;
              margin-bottom: 1.5rem;
          }
        
          @media (max-width: 1200px) {
              .stat-card {
                  flex: 0 0 50%;
                  max-width: 50%;
              }
          }
        
          @media (max-width: 768px) {
              .stat-card {
                  flex: 0 0 100%;
                  max-width: 100%;
              }
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
          }
        
          .card-body {
              flex: 1 1 auto;
              padding: 1.25rem;
          }
        
          .card-header {
              padding: 0.75rem 1.25rem;
              margin-bottom: 0;
              background-color: #f8f9fc;
              border-bottom: 1px solid #e3e6f0;
          }
        
          .card-header h2 {
              margin: 0;
              font-size: 1rem;
              font-weight: 700;
              color: var(--primary);
          }
        
          .stat-card .card {
              border-left: 0.25rem solid;
              border-radius: 0.35rem;
          }
        
          .stat-card.primary .card {
              border-left-color: var(--primary);
          }
        
          .stat-card.success .card {
              border-left-color: var(--success);
          }
        
          .stat-card.info .card {
              border-left-color: var(--info);
          }
        
          .stat-card.warning .card {
              border-left-color: var(--warning);
          }
        
          .stat-card.danger .card {
              border-left-color: var(--danger);
          }
        
          .stat-card .card-body {
              display: flex;
              justify-content: space-between;
              align-items: center;
          }
        
          .stat-card .stat-title {
              text-transform: uppercase;
              font-size: 0.7rem;
              font-weight: 700;
              color: var(--secondary);
              margin-bottom: 0.25rem;
          }
        
          .stat-card .stat-value {
              color: var(--dark);
              font-size: 1.5rem;
              font-weight: 700;
              margin-bottom: 0;
          }
        
          .stat-card .stat-icon {
              font-size: 2rem;
              opacity: 0.3;
          }
        
          .stat-card.primary .stat-icon {
              color: var(--primary);
          }
        
          .stat-card.success .stat-icon {
              color: var(--success);
          }
        
          .stat-card.info .stat-icon {
              color: var(--info);
          }
        
          .stat-card.warning .stat-icon {
              color: var(--warning);
          }
        
          .stat-card.danger .stat-icon {
              color: var(--danger);
          }
        
          .content-row {
              display: flex;
              flex-wrap: wrap;
              margin-right: -0.75rem;
              margin-left: -0.75rem;
          }
        
          .content-column {
              flex: 0 0 50%;
              max-width: 50%;
              padding-right: 0.75rem;
              padding-left: 0.75rem;
              margin-bottom: 1.5rem;
          }
        
          @media (max-width: 992px) {
              .content-column {
                  flex: 0 0 100%;
                  max-width: 100%;
              }
          }
        
          .activity-list {
              list-style: none;
              padding: 0;
              margin: 0;
          }
        
          .activity-item {
              padding: 0.75rem 1.25rem;
              border-bottom: 1px solid #e3e6f0;
          }
        
          .activity-item:last-child {
              border-bottom: none;
          }
        
          .activity-item .activity-icon {
              display: inline-block;
              width: 30px;
              height: 30px;
              line-height: 30px;
              text-align: center;
              border-radius: 50%;
              margin-right: 0.5rem;
              color: white;
          }
        
          .activity-item .activity-icon.add {
              background-color: var(--success);
          }
        
          .activity-item .activity-icon.edit {
              background-color: var(--primary);
          }
        
          .activity-item .activity-icon.delete {
              background-color: var(--danger);
          }
        
          .activity-item .activity-icon.login {
              background-color: var(--info);
          }
        
          .activity-item .activity-content {
              display: inline-block;
              vertical-align: middle;
          }
        
          .activity-item .activity-title {
              font-weight: 600;
              margin-bottom: 0.25rem;
          }
        
          .activity-item .activity-time {
              font-size: 0.8rem;
              color: var(--secondary);
          }
        
          .asset-list {
              list-style: none;
              padding: 0;
              margin: 0;
          }
        
          .asset-item {
              padding: 0.75rem 1.25rem;
              border-bottom: 1px solid #e3e6f0;
              display: flex;
              align-items: center;
          }
        
          .asset-item:last-child {
              border-bottom: none;
          }
        
          .asset-item .asset-icon {
              width: 40px;
              height: 40px;
              line-height: 40px;
              text-align: center;
              border-radius: 50%;
              margin-right: 1rem;
              color: white;
              background-color: var(--primary);
              font-size: 1.2rem;
          }
        
          .asset-item .asset-details {
              flex: 1;
          }
        
          .asset-item .asset-name {
              font-weight: 600;
              margin-bottom: 0.25rem;
          }
        
          .asset-item .asset-owner {
              font-size: 0.8rem;
              color: var(--secondary);
          }
        
          .asset-item .asset-status {
              padding: 0.25rem 0.5rem;
              border-radius: 0.25rem;
              font-size: 0.75rem;
              font-weight: 600;
          }
        
          .asset-item .asset-status.active {
              background-color: #e3fcef;
              color: var(--success);
          }
        
          .asset-item .asset-status.missing {
              background-color: #fff3cd;
              color: var(--warning);
          }
        
          .asset-item .asset-status.blacklisted {
              background-color: #f8d7da;
              color: var(--danger);
          }
        
          .notification-alert {
              background-color: #f8d7da;
              color: #721c24;
              border: 1px solid #f5c6cb;
              border-radius: 5px;
              padding: 15px;
              margin-bottom: 20px;
          }
        
          .notification-alert a {
              color: #721c24;
              font-weight: bold;
              text-decoration: underline;
          }
        
          .chart-container {
              position: relative;
              height: 300px;
              margin-bottom: 20px;
          }
        
          .chart-card {
              background-color: white;
              border-radius: 0.35rem;
              box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
              margin-bottom: 20px;
              padding: 15px;
          }
        
          .chart-card h3 {
              color: var(--primary);
              font-size: 1.1rem;
              margin-top: 0;
              margin-bottom: 15px;
              border-bottom: 1px solid #e3e6f0;
              padding-bottom: 10px;
          }
        
          .chart-row {
              display: flex;
              flex-wrap: wrap;
              margin-right: -0.75rem;
              margin-left: -0.75rem;
          }
        
          .chart-column {
              flex: 0 0 50%;
              max-width: 50%;
              padding-right: 0.75rem;
              padding-left: 0.75rem;
              margin-bottom: 1.5rem;
          }
        
          @media (max-width: 992px) {
              .chart-column {
                  flex: 0 0 100%;
                  max-width: 100%;
              }
          }
        
          .user-creation-table {
              width: 100%;
              border-collapse: collapse;
          }
        
          .user-creation-table th, .user-creation-table td {
              padding: 8px;
              text-align: left;
              border-bottom: 1px solid #e3e6f0;
          }
        
          .user-creation-table th {
              background-color: #f8f9fc;
              color: var(--primary);
          }
      </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar (keep your existing sidebar) -->
        <div class="sidebar">
              <div class="sidebar-brand">
                  <i class="fas fa-shield-alt"></i> Admin Portal
              </div>
              <ul class="sidebar-menu">
                  <li><a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                  <li><a href="admin_assets.php"><i class="fas fa-laptop"></i> Assets</a></li>
                  <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
                  <li><a href="scpersonnel.php"><i class="fas fa-user-shield"></i> Security Personnel</a></li>
                  <li><a href="admin_logs.php"><i class="fas fa-history"></i> System Logs</a></li>
                  <li><a href="admin_settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                  <li><a href="welcome.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
              </ul>
          </div>
        
        <!-- Main Content -->
        <div class="content-wrapper">
            <!-- Top Bar (keep your existing topbar) -->
            <div class="topbar">
                  <h1>Dashboard</h1>
                  <div class="user-info">
                      <span>Welcome, <?php echo $_SESSION['admin_username']; ?></span>
                      <a href="admin_logout.php" class="logout-btn">Logout</a>
                  </div>
              </div>
            
            <!-- Statistics Cards -->
            <div class="stats-row">
                <!-- Total Assets Card -->
                <div class="stat-card primary">
                    <div class="card">
                        <div class="card-body">
                            <div>
                                <div class="stat-title">Total Assets</div>
                                <div class="stat-value"><?php echo $total_assets; ?></div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-laptop"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Missing Assets Card -->
                <div class="stat-card warning">
                    <div class="card">
                        <div class="card-body">
                            <div>
                                <div class="stat-title">Missing Assets</div>
                                <div class="stat-value"><?php echo $missing_assets; ?></div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Blacklisted Assets Card -->
                <div class="stat-card danger">
                    <div class="card">
                        <div class="card-body">
                            <div>
                                <div class="stat-title">Blacklisted Assets</div>
                                <div class="stat-value"><?php echo $blacklisted_assets; ?></div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-ban"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Total Users Card -->
                <div class="stat-card success">
                    <div class="card">
                        <div class="card-body">
                            <div>
                                <div class="stat-title">Total Users</div>
                                <div class="stat-value"><?php echo $total_users; ?></div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Content Row -->
            <div class="content-row">
                <!-- Recent Activities -->
                <div class="content-column">
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-history"></i> Recent Activities</h2>
                        </div>
                        <div class="card-body">
                            <?php if ($pending_count > 0): ?>
                            <div class="notification-alert">
                                <p><strong>Notification:</strong> You have <?php echo $pending_count; ?> security personnel account<?php echo $pending_count > 1 ? 's' : ''; ?> waiting for approval.</p>
                            </div>
                            <?php endif; ?>
                            
                            <ul class="activity-list">
                                <?php while ($log = mysqli_fetch_assoc($logs_query)): ?>
                                <li class="activity-item">
                                    <div class="activity-icon <?php echo $log['action']; ?>">
                                        <i class="fas fa-<?php echo $log['action']; ?>"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">
                                            <?php echo htmlspecialchars($log['admin_name']) . ': ' . htmlspecialchars($log['details'] ?? $log['action']); ?>
                                        </div>
                                        <div class="activity-time">
                                            <?php echo date('M j, Y g:i a', strtotime($log['created_at'])); ?>
                                        </div>
                                    </div>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Assets -->
                <div class="content-column">
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-laptop"></i> Recent Assets</h2>
                        </div>
                        <div class="card-body">
                            <ul class="asset-list">
                                <?php while ($asset = mysqli_fetch_assoc($recent_assets_query)): ?>
                                <li class="asset-item">
                                    <div class="asset-icon">
                                        <i class="fas fa-laptop"></i>
                                    </div>
                                    <div class="asset-details">
                                        <div class="asset-name"><?php echo htmlspecialchars($asset['AssetName']); ?></div>
                                        <div class="asset-owner">
                                            <?php 
                                            if (!empty($asset['Username'])) {
                                                echo htmlspecialchars($asset['Username']);
                                                if (!empty($asset['Lastname'])) {
                                                    echo ' ' . htmlspecialchars($asset['Lastname']);
                                                }
                                            } else {
                                                echo "Asset ID: " . htmlspecialchars($asset['item_id']);
                                            }
                                            ?>
                                        </div>
                                        <div class="asset-status <?php echo strtolower($asset['AssetStatus']); ?>">
                                            <?php echo htmlspecialchars($asset['AssetStatus']); ?>
                                        </div>
                                    </div>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize your charts here
        function initCharts() {
            // 1. Weekly Scanning Chart
            if (document.getElementById('weeklyScanningChart')) {
                const ctx = document.getElementById('weeklyScanningChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                        datasets: [{
                            label: 'Scans',
                            data: [12, 19, 3, 5, 2, 3, 10],
                            backgroundColor: 'rgba(78, 115, 223, 0.05)',
                            borderColor: 'rgba(78, 115, 223, 1)',
                            borderWidth: 2,
                            tension: 0.1,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top'
                            },
                            tooltip: {
                                backgroundColor: '#fff',
                                titleColor: chartColors.dark,
                                bodyColor: chartColors.dark,
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                displayColors: false,
                                caretPadding: 10,
                                callbacks: {
                                    label: function(context) {
                                        return `Registered: ${context.parsed.y}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
            
            // 2. Asset Status Distribution Chart
            if (document.getElementById('assetStatusChart')) {
                const ctx = document.getElementById('assetStatusChart').getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Active', 'Missing', 'Blacklisted'],
                        datasets: [{
                            label: 'Assets',
                            data: [12, 19, 3],
                            backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b'],
                            hoverBackgroundColor: ['#17a673', '#84cc16', '#cc2529'],
                            hoverBorderColor: "rgba(234, 236, 244, 1)",
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top'
                            },
                            tooltip: {
                                backgroundColor: '#fff',
                                titleColor: chartColors.dark,
                                bodyColor: chartColors.dark,
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                displayColors: false,
                                caretPadding: 10,
                                callbacks: {
                                    label: function(context) {
                                        return `Registered: ${context.parsed.y}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
            
            // 3. Confiscation & Recovery Rate Chart
            if (document.getElementById('confiscationRecoveryChart')) {
                const ctx = document.getElementById('confiscationRecoveryChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                        datasets: [{
                            label: 'Confiscations',
                            data: [12, 19, 3, 5, 2, 3, 10],
                            backgroundColor: 'rgba(78, 115, 223, 0.05)',
                            borderColor: 'rgba(78, 115, 223, 1)',
                            borderWidth: 2,
                            tension: 0.1,
                            fill: false
                        }, {
                            label: 'Recoveries',
                            data: [12, 19, 3, 5, 2, 3, 10],
                            backgroundColor: 'rgba(78, 115, 223, 0.05)',
                            borderColor: 'rgba(78, 115, 223, 1)',
                            borderWidth: 2,
                            tension: 0.1,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top'
                            },
                            tooltip: {
                                backgroundColor: '#fff',
                                titleColor: chartColors.dark,
                                bodyColor: chartColors.dark,
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                displayColors: false,
                                caretPadding: 10,
                                callbacks: {
                                    label: function(context) {
                                        return `Registered: ${context.parsed.y}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
            
            // 4. Asset Registration Trend Chart
            if (document.getElementById('assetRegistrationChart')) {
                const ctx = document.getElementById('assetRegistrationChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                        datasets: [{
                            label: 'Registrations',
                            data: [12, 19, 3, 5, 2, 3, 10],
                            backgroundColor: 'rgba(78, 115, 223, 0.05)',
                            borderColor: 'rgba(78, 115, 223, 1)',
                            borderWidth: 2,
                            tension: 0.1,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top'
                            },
                            tooltip: {
                                backgroundColor: '#fff',
                                titleColor: chartColors.dark,
                                bodyColor: chartColors.dark,
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                displayColors: false,
                                caretPadding: 10,
                                callbacks: {
                                    label: function(context) {
                                        return `Registered: ${context.parsed.y}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
            
            // 5. User Creation Chart
            if (document.getElementById('userCreationChart')) {
                const ctx = document.getElementById('userCreationChart').getContext('2d');
                
                // Process data to group by date
                const dateGroups = {};
                userCreationData.forEach(item => {
                    if (!dateGroups[item.creation_date]) {
                        dateGroups[item.creation_date] = {
                            date: item.creation_date,
                            hourCounts: Array(24).fill(0)
                        };
                    }
                    dateGroups[item.creation_date].hourCounts[item.creation_hour] = item.user_count;
                });
                
                // Convert to array and sort by date
                const processedData = Object.values(dateGroups).sort((a, b) => 
                    new Date(a.date) - new Date(b.date)
                );
                
                // Prepare datasets - one for each date
                const datasets = processedData.map((dayData, index) => {
                    // Generate a color based on index
                    const hue = (index * 30) % 360;
                    const color = `hsl(${hue}, 70%, 60%)`;
                    
                    return {
                        label: new Date(dayData.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }),
                        data: dayData.hourCounts,
                        backgroundColor: color,
                        borderColor: color,
                        borderWidth: 2,
                        tension: 0.1,
                        fill: false
                    };
                });
                
                // Hours labels
                const hourLabels = Array.from({ length: 24 }, (_, i) => 
                    i.toString().padStart(2, '0') + ':00'
                );
                
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: hourLabels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top'
                            },
                            tooltip: {
                                backgroundColor: '#fff',
                                titleColor: chartColors.dark,
                                bodyColor: chartColors.dark,
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                displayColors: true,
                                caretPadding: 10,
                                callbacks: {
                                    title: function(context) {
                                        return `${context[0].dataset.label} at ${context[0].label}`;
                                    },
                                    label: function(context) {
                                        return `Users created: ${context.parsed.y}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Hour of Day'
                                },
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                },
                                title: {
                                    display: true,
                                    text: 'Number of Users Created'
                                }
                            }
                        }
                    }
                });
            }
        }
        
        // Initialize all charts when the page loads
        document.addEventListener('DOMContentLoaded', initCharts);
    </script>
</body>
</html>