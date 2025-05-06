<?php
session_start();

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

include("php/config.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_valid']) || $_SESSION['admin_valid'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = "";

// Handle approval/rejection actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = "<div class='message error'><p>Invalid CSRF token!</p></div>";
    } else {
        if (isset($_POST['approve'])) {
            $id = intval($_POST['id']);
            $stmt = $con->prepare("UPDATE scpersonnel SET status='approved' WHERE id=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "<div class='message success'><p>Security personnel account has been approved successfully!</p></div>";
            } else {
                $message = "<div class='message error'><p>Error approving account: " . htmlspecialchars($stmt->error) . "</p></div>";
            }
            $stmt->close();
        } elseif (isset($_POST['reject'])) {
            $id = intval($_POST['id']);
            $stmt = $con->prepare("UPDATE scpersonnel SET status='rejected' WHERE id=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "<div class='message warning'><p>Security personnel account has been rejected!</p></div>";
            } else {
                $message = "<div class='message error'><p>Error rejecting account: " . htmlspecialchars($stmt->error) . "</p></div>";
            }
            $stmt->close();
        } elseif (isset($_POST['delete'])) {
            $id = intval($_POST['id']);
            $stmt = $con->prepare("DELETE FROM scpersonnel WHERE id=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "<div class='message error'><p>Security personnel account has been deleted!</p></div>";
            } else {
                $message = "<div class='message error'><p>Error deleting account: " . htmlspecialchars($stmt->error) . "</p></div>";
            }
            $stmt->close();
        } elseif (isset($_POST['add_security'])) {
            // Validate and sanitize inputs
            $officer_id = trim($_POST['officer_id']);
            $name = trim($_POST['name']);
            $lastname = trim($_POST['lastname']);
            $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];
            
            // Validate inputs
            if (empty($officer_id) || empty($name) || empty($lastname) || empty($email) || empty($password)) {
                $message = "<div class='message error'><p>All fields are required!</p></div>";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "<div class='message error'><p>Invalid email format!</p></div>";
            } elseif (strlen($password) < 8) {
                $message = "<div class='message error'><p>Password must be at least 8 characters long!</p></div>";
            } else {
                // Check if email already exists
                $stmt = $con->prepare("SELECT id FROM scpersonnel WHERE email=?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();
                
                if ($stmt->num_rows > 0) {
                    $message = "<div class='message error'><p>This email is already used. Try another one.</p></div>";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    $insert_stmt = $con->prepare("INSERT INTO scpersonnel(name, lastname, officer_id, email, password, status) VALUES(?, ?, ?, ?, ?, 'approved')");
                    $insert_stmt->bind_param("sssss", $name, $lastname, $officer_id, $email, $hashed_password);
                    
                    if ($insert_stmt->execute()) {
                        $message = "<div class='message success'><p>Security personnel added successfully!</p></div>";
                    } else {
                        $message = "<div class='message error'><p>Error adding security personnel: " . htmlspecialchars($insert_stmt->error) . "</p></div>";
                    }
                    $insert_stmt->close();
                }
                $stmt->close();
            }
        }
    }
}

// Get pending security personnel
$pending_query = $con->prepare("SELECT * FROM scpersonnel WHERE status='pending' ORDER BY id DESC");
$pending_query->execute();
$pending_result = $pending_query->get_result();
$pending_count = $pending_result->num_rows;

// Get all security personnel
$all_query = $con->prepare("SELECT * FROM scpersonnel ORDER BY status, id DESC");
$all_query->execute();
$all_result = $all_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Security Personnel Management</title>
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
        
        .container {
            background-color: white;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 2rem;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .tabs {
            display: flex;
            border-bottom: 1px solid #e3e6f0;
            margin-bottom: 2rem;
        }
        
        .tab {
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            position: relative;
            color: var(--secondary);
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .tab.active {
            color: var(--primary);
            border-bottom: 2px solid var(--primary);
        }
        
        .tab:hover:not(.active) {
            color: var(--dark);
            background-color: #f8f9fc;
        }
        
        .notification-badge {
            background-color: var(--danger);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
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
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }
        
        table th, table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e3e6f0;
        }
        
        table th {
            background-color: #f8f9fc;
            color: var(--dark);
            font-weight: 700;
        }
        
        .status {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-weight: 600;
            text-transform: capitalize;
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
        
        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
        }
        
        .btn-success {
            background-color: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #17a673;
        }
        
        .btn-warning {
            background-color: var(--warning);
            color: #5a5c69;
        }
        
        .btn-warning:hover {
            background-color: #dda20a;
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .form-box {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .field {
            margin-bottom: 1.5rem;
        }
        
        .field label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .field input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d3e2;
            border-radius: 0.35rem;
            transition: border-color 0.3s;
        }
        
        .field input:focus {
            border-color: var(--primary);
            outline: none;
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
        
        .message.warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <div class="sidebar-brand">
                <i class="fas fa-shield-alt"></i> Admin Portal
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="admin_assets.php"><i class="fas fa-laptop"></i> Assets</a></li>
                <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="scpersonnel.php" class="active"><i class="fas fa-user-shield"></i> Security Personnel</a></li>
                <li><a href="admin_logs.php"><i class="fas fa-history"></i> System Logs</a></li>
                <li><a href="admin_settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content Area -->
        <div class="content-wrapper">
            <!-- Top Bar -->
            <div class="topbar">
                <h1>Security Personnel Management</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
                    <a href="admin_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Page Content -->
            <div class="container">
                <?php echo $message; ?>
                
                <div class="tabs">
                    <div class="tab active" data-tab="pending">
                        Pending Approvals
                        <?php if ($pending_count > 0): ?>
                            <span class="notification-badge"><?php echo htmlspecialchars($pending_count); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="tab" data-tab="all">All Security Personnel</div>
                    <div class="tab" data-tab="add">Add New Security Personnel</div>
                </div>
                
                <!-- Pending Approvals Tab -->
                <div class="tab-content active" id="pending-tab">
                    <?php if ($pending_count > 0): ?>
                        <table>
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
                                <?php while ($personnel = $pending_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($personnel['officer_id']); ?></td>
                                        <td><?php echo htmlspecialchars($personnel['name']); ?></td>
                                        <td><?php echo htmlspecialchars($personnel['lastname']); ?></td>
                                        <td><?php echo htmlspecialchars($personnel['email']); ?></td>
                                        <td>
                                            <span class="status status-pending">Pending</span>
                                        </td>
                                        <td>
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($personnel['id']); ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                <button type="submit" name="approve" class="btn btn-success">Approve</button>
                                                <button type="submit" name="reject" class="btn btn-danger">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No pending approval requests at this time.</p>
                    <?php endif; ?>
                </div>
                
                <!-- All Security Personnel Tab -->
                <div class="tab-content" id="all-tab">
                    <table>
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
                            <?php if ($all_result->num_rows > 0): ?>
                                <?php while ($personnel = $all_result->fetch_assoc()): ?>
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
                                            <span class="status <?php echo $status_class; ?>">
                                                <?php echo ucfirst(htmlspecialchars($personnel['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($personnel['id']); ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                
                                                <?php if ($personnel['status'] != 'approved'): ?>
                                                    <button type="submit" name="approve" class="btn btn-success">Approve</button>
                                                <?php endif; ?>
                                                
                                                <?php if ($personnel['status'] != 'rejected'): ?>
                                                    <button type="submit" name="reject" class="btn btn-warning">Reject</button>
                                                <?php endif; ?>
                                                
                                                <button type="submit" name="delete" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this security personnel?');">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">No security personnel found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Add New Security Personnel Tab -->
                <div class="tab-content" id="add-tab">
                    <div class="form-box">
                        <header>Add New Security Personnel</header>
                        <form action="" method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <div class="field input">
                                <label for="officer_id">Officer ID</label>
                                <input type="text" name="officer_id" id="officer_id" required maxlength="50">
                            </div>
                            <div class="field input">
                                <label for="name">Name</label>
                                <input type="text" name="name" id="name" required maxlength="50">
                            </div>
                            <div class="field input">
                                <label for="lastname">Last Name</label>
                                <input type="text" name="lastname" id="lastname" required maxlength="50">
                            </div>
                            <div class="field input">
                                <label for="email">Email</label>
                                <input type="email" name="email" id="email" required maxlength="100">
                            </div>
                            <div class="field input">
                                <label for="password">Password</label>
                                <input type="password" name="password" id="password" required minlength="8">
                            </div>
                            <div class="field">
                                <input type="submit" name="add_security" value="Add Security Personnel" class="btn btn-primary">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Tab switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    document.querySelectorAll('.tab').forEach(t => {
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