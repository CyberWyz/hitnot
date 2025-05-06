<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-shield-alt"></i> Admin Portal
    </div>
    <ul class="sidebar-menu">
        <li><da href="admin_dashboard.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php') ? 'class="active"' : ''; ?>><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="admin_assets.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'admin_assets.php') ? 'class="active"' : ''; ?>><i class="fas fa-laptop"></i> Assets</a></li>
        <li><a href="amin_users.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'admin_users.php') ? 'class="active"' : ''; ?>><i class="fas fa-users"></i> Users</a></li>
        <li><a href="scpersonnel.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'scpersonnel.php') ? 'class="active"' : ''; ?>><i class="fas fa-user-shield"></i> Security Personnel</a></li>
        <li><a href="admin_logs.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'admin_logs.php') ? 'class="active"' : ''; ?>><i class="fas fa-history"></i> System Logs</a></li>
        <li><a href="admin_settings.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'admin_settings.php') ? 'class="active"' : ''; ?>><i class="fas fa-cog"></i> Settings</a></li>
        <li><a href="welcome.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</div>

<!-- Top Bar -->
<div class="topbar">
    <h1><?php echo isset($page_title) ? $page_title : 'Admin Panel'; ?></h1>
    <div class="user-info">
        <span>Welcome, <?php echo isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin'; ?></span>
        <a href="admin_logout.php" class="logout-btn">Logout</a>
    </div>
</div>