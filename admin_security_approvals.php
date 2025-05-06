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
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="responsive.css">
    <title>Security Personnel Approvals</title>
    <style>
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
                <li><a href="admin_security_approvals.php" class="active"><i class="fas fa-user-shield"></i> Security Personnel</a></li>
                <li><a href="admin_assets.php"><i class="fas fa-laptop"></i> Assets</a></li>
                <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="admin_logs.php"><i class="fas fa-history"></i> System Logs</a></li>
                <li><a href="admin_settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="welcome.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="content-wrapper">
            <!-- Top Bar -->
            <div class="topbar">
                <h1>Security Personnel Management</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['admin_username']; ?></span>
                    <a href="admin_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Message Display -->
            <?php echo $message; ?>
            
            <!-- Tabs Navigation -->
            <ul class="nav-tabs">
                <li class="nav-item">
                    <a class="nav-link active" id="pending-tab" data-toggle="tab" href="#pending">
                        Pending Approvals 
                        <?php if (mysqli_num_rows($pending_query) > 0): ?>
                            <span class="notification-badge"><?php echo mysqli_num_rows($pending_query); ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="all-tab" data-toggle="tab" href="#all">All Security Personnel</a>
                </li>
            </ul>
            
            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Pending Approvals Tab -->
                <div class="tab-pane active" id="pending">
                    <div class="card">
                        <div class="card-header">
                            <h2>Pending Security Personnel Approvals</h2>
                        </div>
                        <div class="card-body">
                            <?php if (mysqli_num_rows($pending_query) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Officer ID</th>
                                                <th>Name</th>
                                                <th>Last Name</th>
                                                <th>Email</th>
                                                <th>Registration Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($personnel = mysqli_fetch_assoc($pending_query)): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($personnel['officer_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($personnel['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($personnel['lastname']); ?></td>
                                                    <td><?php echo htmlspecialchars($personnel['email']); ?></td>
                                                    <td>
                                                        <?php 
                                                        if (isset($personnel['created_at'])) {
                                                            echo date('M d, Y', strtotime($personnel['created_at']));
                                                        } else {
                                                            echo "N/A";
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <form method="post" style="display:inline;">
                                                            <input type="hidden" name="id" value="<?php echo $personnel['id']; ?>">
                                                            <button type="submit" name="approve" class="btn btn-success btn-sm">
                                                                <i class="fas fa-check"></i> Approve
                                                            </button>
                                                            <button type="submit" name="reject" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-times"></i> Reject
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p>No pending approval requests at this time.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- All Security Personnel Tab -->
                <div class="tab-pane" id="all">
                    <div class="card">
                        <div class="card-header">
                            <h2>All Security Personnel</h2>
                        </div>
                        <div class="card-body">
                            <?php if (mysqli_num_rows($all_query) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Officer ID</th>
                                                <th>Name</th>
                                                <th>Last Name</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($personnel = mysqli_fetch_assoc($all_query)): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($personnel['officer_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($personnel['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($personnel['lastname']); ?></td>
                                                    <td><?php echo htmlspecialchars($personnel['email']); ?></td>
                                                    <td>
                                                        <?php 
                                                        $status_class = '';
                                                        switch ($personnel['status']) {
                                                            case 'approved':
                                                                $status_class = 'badge-success';
                                                                break;
                                                            case 'pending':
                                                                $status_class = 'badge-warning';
                                                                break;
                                                            case 'rejected':
                                                                $status_class = 'badge-danger';
                                                                break;
                                                            default:
                                                                $status_class = 'badge-secondary';
                                                        }
                                                        ?>
                                                        <span class="badge <?php echo $status_class; ?>">
                                                            <?php echo ucfirst(htmlspecialchars($personnel['status'])); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($personnel['status'] != 'approved'): ?>
                                                            <form method="post" style="display:inline;">
                                                                <input type="hidden" name="id" value="<?php echo $personnel['id']; ?>">
                                                                <button type="submit" name="approve" class="btn btn-success btn-sm">
                                                                    <i class="fas fa-check"></i> Approve
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($personnel['status'] != 'rejected'): ?>
                                                            <form method="post" style="display:inline;">
                                                                <input type="hidden" name="id" value="<?php echo $personnel['id']; ?>">
                                                                <button type="submit" name="reject" class="btn btn-danger btn-sm">
                                                                    <i class="fas fa-times"></i> Reject
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <a href="admin_edit_security.php?id=<?php echo $personnel['id']; ?>" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p>No security personnel found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Tab switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.nav-link');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all tabs and panes
                    document.querySelectorAll('.nav-link').forEach(t => t.classList.remove('active'));
                    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Show corresponding tab content
                    const target = this.getAttribute('href').substring(1);
                    document.getElementById(target).classList.add('active');
                });
            });
        });
    </script>
</body>
</html>