<?php
session_start();
include("php/config.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_valid']) || $_SESSION['admin_valid'] !== true) {
    header("Location: adminlogin.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

// 1. Total assets
$assets_query = mysqli_query($con, "SELECT COUNT(*) as total FROM assets");
$total_assets = mysqli_num_rows($assets_query) > 0 ? mysqli_fetch_assoc($assets_query)['total'] : 0;

// 2. Missing assets
$missing_query = mysqli_query($con, "SELECT COUNT(*) as total FROM assets WHERE AssetStatus = 'missing'");
$missing_assets = mysqli_num_rows($missing_query) > 0 ? mysqli_fetch_assoc($missing_query)['total'] : 0;

// 3. Blacklisted assets
$blacklisted_query = mysqli_query($con, "SELECT COUNT(*) as total FROM assets WHERE AssetStatus = 'blacklisted'");
$blacklisted_assets = mysqli_num_rows($blacklisted_query) > 0 ? mysqli_fetch_assoc($blacklisted_query)['total'] : 0;

// 4. Total users
$users_query = mysqli_query($con, "SELECT COUNT(*) as total FROM users");
$total_users = mysqli_num_rows($users_query) > 0 ? mysqli_fetch_assoc($users_query)['total'] : 0;

// 5. Total security personnel
$security_query = mysqli_query($con, "SELECT COUNT(*) as total FROM scpersonnel");
$total_security = mysqli_num_rows($security_query) > 0 ? mysqli_fetch_assoc($security_query)['total'] : 0;

// 6. Pending security personnel approvals
$pending_query = mysqli_query($con, "SELECT COUNT(*) as count FROM scpersonnel WHERE status='pending' OR status IS NULL");
$pending_count = mysqli_num_rows($pending_query) > 0 ? mysqli_fetch_assoc($pending_query)['count'] : 0;

// 7. Recent activities
$logs_query = mysqli_query($con, "SELECT * FROM admin_logs ORDER BY created_at DESC LIMIT 10");
if (!$logs_query) {
    $logs_query = mysqli_query($con, "SELECT 1 as id LIMIT 0");
}

// 8. Recent assets
$recent_assets_query = mysqli_query($con, "SELECT a.item_id, a.item_model as AssetName, a.AssetStatus, u.Username, u.Lastname 
                                         FROM assets a
                                         LEFT JOIN users u ON a.reg_number = u.Reg_Number
                                         ORDER BY a.item_id DESC LIMIT 5");
if (!$recent_assets_query) {
    $recent_assets_query = mysqli_query($con, "SELECT 1 as item_id LIMIT 0");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Admin Dashboard</title>
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
            position: relative;
            z-index: 10;
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

        /* Dashboard Options Grid */
        .dashboard-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .dashboard-option {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: 0 4px 20px var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .dashboard-option::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, var(--accent-teal), var(--primary-dark));
        }

        .dashboard-option:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px var(--shadow);
        }

        .dashboard-option h3 {
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .dashboard-option p {
            color: var(--text-dark);
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .dashboard-option .stat-value {
            color: var(--primary-dark);
            font-size: 2.5rem;
            font-weight: 700;
            margin: 1rem 0;
        }

        .dashboard-option i {
            font-size: 3rem;
            color: var(--accent-teal);
            margin-bottom: 1rem;
            display: block;
            opacity: 0.8;
        }

        /* Content Row */
        .content-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        /* Cards */
        .main-box {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: 0 8px 32px var(--shadow);
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .main-box h2 {
            color: var(--text-dark);
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .main-box h2 i {
            color: var(--accent-teal);
        }

        /* Activity List */
        .activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .activity-item {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: start;
            gap: 1rem;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            flex-shrink: 0;
        }

        .activity-icon.add {
            background: #4caf50;
        }

        .activity-icon.edit {
            background: #2196f3;
        }

        .activity-icon.delete {
            background: #f44336;
        }

        .activity-icon.login {
            background: var(--accent-teal);
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .activity-time {
            font-size: 0.8rem;
            color: #999;
        }

        /* Asset List */
        .asset-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .asset-item {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .asset-item:last-child {
            border-bottom: none;
        }

        .asset-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            background: var(--primary-dark);
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .asset-details {
            flex: 1;
        }

        .asset-name {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .asset-owner {
            font-size: 0.8rem;
            color: #999;
            margin-bottom: 0.5rem;
        }

        .asset-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .asset-status.active {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .asset-status.missing {
            background: #fff3e0;
            color: #e65100;
        }

        .asset-status.blacklisted {
            background: #ffebee;
            color: #c62828;
        }

        /* Notification Alert */
        .notification-alert {
            background: rgba(255, 152, 0, 0.1);
            border-left: 4px solid #ff9800;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            color: var(--text-dark);
        }

        .notification-alert strong {
            color: var(--accent-teal);
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

            .dashboard-options {
                grid-template-columns: 1fr;
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

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .sidebar {
            animation: slideInLeft 0.8s ease-out;
        }

        .topbar {
            animation: fadeIn 0.6s ease-out 0.2s both;
        }

        .main-box {
            animation: fadeIn 0.8s ease-out 0.4s both;
        }

        .dashboard-option {
            animation: scaleIn 0.7s ease-out 0.6s both;
        }
    </style>
</head>
<body>
    <!-- Particle Background -->
    <div class="particles-container"></div>    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-brand-content">
                    <i class="fas fa-shield-alt"></i>
                    <div class="title">Admin Portal</div>
                </div>
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="admin_assets.php"><i class="fas fa-laptop"></i> Assets</a></li>
                <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="admin_security_approvals.php"><i class="fas fa-user-shield"></i> Security Personnel</a></li>
                <li><a href="admin_logs.php"><i class="fas fa-history"></i> System Logs</a></li>
                <li><a href="admin_settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="welcome.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Topbar -->
            <div class="topbar">
                <h1>Admin Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <b><?php echo htmlspecialchars($_SESSION['admin_username']); ?></b></span>
                    <a href="welcome.php" class="logout-btn">Logout</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <!-- Statistics Cards -->
                <div class="dashboard-options">
                    <!-- Total Assets -->
                    <div class="dashboard-option" onclick="window.location.href='admin_assets.php'">
                        <i class="fas fa-laptop"></i>
                        <h3>Total Assets</h3>
                        <div class="stat-value"><?php echo $total_assets; ?></div>
                        <p>All registered assets</p>
                    </div>

                    <!-- Missing Assets -->
                    <div class="dashboard-option" onclick="window.location.href='admin_assets.php'">
                        <i class="fas fa-search"></i>
                        <h3>Missing Assets</h3>
                        <div class="stat-value"><?php echo $missing_assets; ?></div>
                        <p>Assets reported missing</p>
                    </div>

                    <!-- Blacklisted Assets -->
                    <div class="dashboard-option" onclick="window.location.href='admin_assets.php'">
                        <i class="fas fa-ban"></i>
                        <h3>Blacklisted Assets</h3>
                        <div class="stat-value"><?php echo $blacklisted_assets; ?></div>
                        <p>Flagged assets</p>
                    </div>

                    <!-- Total Users -->
                    <div class="dashboard-option" onclick="window.location.href='admin_users.php'">
                        <i class="fas fa-users"></i>
                        <h3>Total Users</h3>
                        <div class="stat-value"><?php echo $total_users; ?></div>
                        <p>Registered users</p>
                    </div>

                    <!-- Total Security Personnel -->
                    <div class="dashboard-option" onclick="window.location.href='admin_security_approvals.php'">
                        <i class="fas fa-user-shield"></i>
                        <h3>Security Personnel</h3>
                        <div class="stat-value"><?php echo $total_security; ?></div>
                        <p>Active officers</p>
                    </div>

                    <!-- Pending Approvals -->
                    <div class="dashboard-option" onclick="window.location.href='admin_security_approvals.php'">
                        <i class="fas fa-clock"></i>
                        <h3>Pending Approvals</h3>
                        <div class="stat-value"><?php echo $pending_count; ?></div>
                        <p>Awaiting review</p>
                    </div>
                </div>

                <!-- Content Row -->
                <div class="content-row">
                    <!-- Recent Activities -->
                    <div class="main-box">
                        <h2><i class="fas fa-history"></i> Recent Activities</h2>
                        
                        <?php if ($pending_count > 0): ?>
                        <div class="notification-alert">
                            <strong>⚠️ Attention:</strong> You have <?php echo $pending_count; ?> security personnel account<?php echo $pending_count > 1 ? 's' : ''; ?> waiting for approval.
                        </div>
                        <?php endif; ?>

                        <ul class="activity-list">
                            <?php while ($log = mysqli_fetch_assoc($logs_query)): ?>
                            <li class="activity-item">
                                <div class="activity-icon <?php echo strtolower($log['action'] ?? 'login'); ?>">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        <?php echo htmlspecialchars($log['action'] ?? 'System Action'); ?>
                                    </div>
                                    <div class="activity-time">
                                        <?php echo date('M j, Y g:i a', strtotime($log['created_at'])); ?>
                                    </div>
                                </div>
                            </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>

                    <!-- Recent Assets -->
                    <div class="main-box">
                        <h2><i class="fas fa-laptop"></i> Recent Assets</h2>
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
                                    <div class="asset-status <?php echo strtolower($asset['AssetStatus'] ?? 'active'); ?>">
                                        <?php echo htmlspecialchars($asset['AssetStatus'] ?? 'Active'); ?>
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
    </script>
</body>
</html>