<?php
/**
 * Get the count of pending security personnel approvals
 * 
 * @param mysqli $con Database connection
 * @return int Number of pending approvals
 */
function getPendingApprovalsCount($con) {
    $query = mysqli_query($con, "SELECT COUNT(*) as count FROM scpersonnel WHERE status='pending'");
    $result = mysqli_fetch_assoc($query);
    return $result['count'];
}

/**
 * Generate the admin sidebar with notification badges
 * 
 * @param mysqli $con Database connection
 * @param string $active_page Current active page
 * @return string HTML for the sidebar
 */
function getAdminSidebar($con, $active_page = '') {
    $pending_count = getPendingApprovalsCount($con);
    
    $html = '
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-shield-alt"></i> Admin Portal
        </div>
        <ul class="sidebar-menu">
            <li><a href="admin_dashboard.php" ' . ($active_page == 'dashboard' ? 'class="active"' : '') . '><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li>
                <a href="admin_security_approvals.php" ' . ($active_page == 'security' ? 'class="active"' : '') . '>
                    <i class="fas fa-user-shield"></i> Security Personnel
                    ' . ($pending_count > 0 ? '<span class="notification-badge">' . $pending_count . '</span>' : '') . '
                </a>
            </li>
            <li><a href="admin_assets.php" ' . ($active_page == 'assets' ? 'class="active"' : '') . '><i class="fas fa-laptop"></i> Assets</a></li>
            <li><a href="admin_users.php" ' . ($active_page == 'users' ? 'class="active"' : '') . '><i class="fas fa-users"></i> Users</a></li>
            <li><a href="admin_logs.php" ' . ($active_page == 'logs' ? 'class="active"' : '') . '><i class="fas fa-history"></i> System Logs</a></li>
            <li><a href="admin_settings.php" ' . ($active_page == 'settings' ? 'class="active"' : '') . '><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>';
    
    return $html;
}