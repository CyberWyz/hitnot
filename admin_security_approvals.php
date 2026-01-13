<?php
session_start();
include("php/config.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_valid']) || $_SESSION['admin_valid'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$message = "";

// Handle approval/rejection actions
if (isset($_POST['approve'])) {
    $id = mysqli_real_escape_string($con, $_POST['id']);
    mysqli_query($con, "UPDATE scpersonnel SET status='approved' WHERE id=$id");
    $message = "<div class='alert alert-success'>Security personnel account has been approved successfully!</div>";
} elseif (isset($_POST['reject'])) {
    $id = mysqli_real_escape_string($con, $_POST['id']);
    mysqli_query($con, "UPDATE scpersonnel SET status='rejected' WHERE id=$id");
    $message = "<div class='alert alert-warning'>Security personnel account has been rejected!</div>";
}

// Get pending security personnel
$pending_query = mysqli_query($con, "SELECT * FROM scpersonnel WHERE status='pending' ORDER BY id DESC");

// Get all security personnel for the full list
$all_query = mysqli_query($con, "SELECT * FROM scpersonnel ORDER BY status, id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/home-modern.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Security Personnel Approvals</title>
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
            background: linear-gradient(135deg, var(--primary-dark), var(--accent-teal));
            color: white;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.2rem;
        }

        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            opacity: 0.7;
        }

        .modal-body {
            padding: 20px;
        }

        .btn-delete, .btn-cancel {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            margin: 5px;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }

        /* Security Personnel Card Styles */
        .personnel-info {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .personnel-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, var(--accent-teal), var(--primary-dark));
        }

        .personnel-info h3 {
            color: var(--text-dark);
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .personnel-info p {
            margin-bottom: 0.8rem;
            color: var(--text-dark);
        }

        .personnel-info b {
            color: var(--accent-teal);
        }

        .personnel-details {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .personnel-details-left, .personnel-details-right {
            flex: 1;
            min-width: 250px;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            margin-bottom: 1rem;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
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

        .action-btn.approve {
            background-color: #28a745;
            color: white;
        }

        .action-btn.reject {
            background-color: #ffc107;
            color: #212529;
        }

        .action-btn.delete {
            background-color: #dc3545;
            color: white;
        }

        .action-btn:hover {
            opacity: 0.8;
            transform: translateY(-1px);
        }

        /* Admin Actions Container */
        .admin-actions {
            margin-top: 15px;
        }

        /* Tab Styles */
        .tabs-section {
            margin-bottom: 20px;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .tab-btn {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            color: var(--text-dark);
            transition: all 0.3s;
            position: relative;
        }

        .tab-btn.active {
            background: linear-gradient(135deg, var(--primary-dark), var(--accent-teal));
            color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .tab-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .notification-badge {
            background-color: var(--danger);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }
        /* Add your existing admin styles here */
        
        .badge {
            display: inline-block;
            padding: 0.25em 0.4em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.35rem;
        }
        
        .badge-success {
            color: #fff;
            background-color: #1cc88a;
        }
        
        .badge-warning {
            color: #212529;
            background-color: #f6c23e;
        }
        
        .badge-danger {
            color: #fff;
            background-color: #e74a3b;
        }
        
        .badge-info {
            color: #fff;
            background-color: #36b9cc;
        }
        
        .badge-secondary {
            color: #fff;
            background-color: #858796;
        }
        
        .alert {
            position: relative;
            padding: 0.75rem 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.35rem;
        }
        
        .alert-success {
            color: #0f6848;
            background-color: #d1f0e8;
            border-color: #bfecda;
        }
        
        .alert-warning {
            color: #806520;
            background-color: #fdf1d8;
            border-color: #fceec9;
        }
        
        .nav-tabs {
            display: flex;
            border-bottom: 1px solid #e3e6f0;
            margin-bottom: 1rem;
        }
        
        .nav-tabs .nav-item {
            margin-bottom: -1px;
        }
        
        .nav-tabs .nav-link {
            border: 1px solid transparent;
            border-top-left-radius: 0.35rem;
            border-top-right-radius: 0.35rem;
            padding: 0.5rem 1rem;
            text-decoration: none;
            color: #4e73df;
        }
        
        .nav-tabs .nav-link.active {
            color: #495057;
            background-color: #fff;
            border-color: #e3e6f0 #e3e6f0 #fff;
        }
        
        .tab-content > .tab-pane {
            display: none;
        }
        
        .tab-content > .active {
            display: block;
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
                    <a href="admin_assets.php">
                        <i class="fas fa-laptop"></i> Assets
                    </a>
                </li>
                <li>
                    <a href="admin_users.php">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
                <li>
                    <a href="admin_security_approvals.php" class="active">
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
                    <h1>Security Personnel Approvals</h1>
                </div>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <i class="fas fa-user-shield" style="font-size: 24px;"></i>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="main-content">
                <!-- Display Messages -->
                <?php if (!empty($message)): ?>
                    <div class="message <?php echo strpos($message, 'success') !== false ? 'success' : (strpos($message, 'error') !== false ? 'error' : 'warning'); ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- Tabs Section -->
                <div class="tabs-section">
                    <div class="tabs">
                        <button class="tab-btn active" data-tab="pending">
                            Pending Approvals
                            <?php if (mysqli_num_rows($pending_query) > 0): ?>
                                <span class="notification-badge"><?php echo mysqli_num_rows($pending_query); ?></span>
                            <?php endif; ?>
                        </button>
                        <button class="tab-btn" data-tab="all">All Security Personnel</button>
                    </div>
                </div>

                <!-- Pending Approvals Tab -->
                <div class="tab-content active" id="pending-tab">
                    <?php if (mysqli_num_rows($pending_query) > 0): ?>
                        <?php while ($personnel = mysqli_fetch_assoc($pending_query)): ?>
                            <div class="personnel-info">
                                <div class="personnel-details">
                                    <div style="flex: 2; min-width: 300px;">
                                        <h3><?php echo htmlspecialchars($personnel['name'] . ' ' . $personnel['lastname']); ?></h3>
                                        <p><b>Officer ID:</b> <?php echo htmlspecialchars($personnel['officer_id']); ?></p>
                                        <p><b>Email:</b> <?php echo htmlspecialchars($personnel['email']); ?></p>
                                        <p><b>Registration Date:</b> <?php echo htmlspecialchars($personnel['created_at'] ?? 'N/A'); ?></p>
                                        <p><b>Status:</b> <span class="status-badge status-pending">Pending</span></p>

                                        <!-- Admin Actions -->
                                        <div class="admin-actions">
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($personnel['id']); ?>">
                                                <button type="submit" name="approve" class="action-btn approve">Approve</button>
                                                <button type="submit" name="reject" class="action-btn reject">Reject</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="text-align: center; padding: 20px;">No pending approval requests at this time.</p>
                    <?php endif; ?>
                </div>

                <!-- All Security Personnel Tab -->
                <div class="tab-content" id="all-tab">
                    <?php if (mysqli_num_rows($all_query) > 0): ?>
                        <?php while ($personnel = mysqli_fetch_assoc($all_query)): ?>
                            <div class="personnel-info">
                                <div class="personnel-details">
                                    <div style="flex: 2; min-width: 300px;">
                                        <h3><?php echo htmlspecialchars($personnel['name'] . ' ' . $personnel['lastname']); ?></h3>
                                        <p><b>Officer ID:</b> <?php echo htmlspecialchars($personnel['officer_id']); ?></p>
                                        <p><b>Email:</b> <?php echo htmlspecialchars($personnel['email']); ?></p>
                                        <p><b>Registration Date:</b> <?php echo htmlspecialchars($personnel['created_at'] ?? 'N/A'); ?></p>
                                        <p><b>Status:</b>
                                            <?php
                                            $status_class = '';
                                            switch ($personnel['status']) {
                                                case 'approved':
                                                    $status_class = 'status-approved';
                                                    break;
                                                case 'pending':
                                                    $status_class = 'status-pending';
                                                    break;
                                                case 'rejected':
                                                    $status_class = 'status-rejected';
                                                    break;
                                            }
                                            ?>
                                            <span class="status-badge <?php echo $status_class; ?>">
                                                <?php echo ucfirst(htmlspecialchars($personnel['status'])); ?>
                                            </span>
                                        </p>

                                        <!-- Admin Actions -->
                                        <div class="admin-actions">
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($personnel['id']); ?>">
                                                <?php if ($personnel['status'] != 'approved'): ?>
                                                    <button type="submit" name="approve" class="action-btn approve">Approve</button>
                                                <?php endif; ?>

                                                <?php if ($personnel['status'] != 'rejected'): ?>
                                                    <button type="submit" name="reject" class="action-btn reject">Reject</button>
                                                <?php endif; ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="text-align: center; padding: 20px;">No security personnel found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="js/particles.js"></script>
    <script src="js/home.js"></script>
    <script>
        // Tab switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab-btn');

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    document.querySelectorAll('.tab-btn').forEach(t => {
                        t.classList.remove('active');
                    });

                    // Add active class to clicked tab
                    this.classList.add('active');

                    // Hide all tab content
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.remove('active');
                    });

                    // Show the corresponding tab content
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId + '-tab').classList.add('active');
                });
            });
        });
    </script>
</body>
</html>