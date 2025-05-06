<?php
session_start();
include("php/config.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_valid']) || $_SESSION['admin_valid'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Get admin ID for logging
$admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 0;

// Process delete user request
if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
    $user_id = mysqli_real_escape_string($con, $_POST['user_id']);
    $user_name = mysqli_real_escape_string($con, $_POST['user_name']);
    $user_reg = mysqli_real_escape_string($con, $_POST['user_reg']);
    
    // Check if user has assets
    $check_assets = mysqli_query($con, "SELECT COUNT(*) as count FROM assets WHERE reg_number = '$user_reg'");
    
    if (!$check_assets) {
        $_SESSION['admin_message'] = "Error checking user assets: " . mysqli_error($con);
        $_SESSION['admin_message_type'] = "error";
    } else {
        $assets_count = mysqli_fetch_assoc($check_assets)['count'];
        
        if ($assets_count > 0) {
            $_SESSION['admin_message'] = "Cannot delete user with registered assets. Please transfer or delete the assets first.";
            $_SESSION['admin_message_type'] = "error";
        } else {
            // Delete a user
            $delete = mysqli_query($con, "DELETE FROM users WHERE Id = $user_id");
            
            if ($delete) {
                // Log the action
                mysqli_query($con, "INSERT INTO admin_logs (admin_id, action, description, ip_address) 
                              VALUES ($admin_id, 'delete', 'Deleted user: $user_name ($user_reg)', '{$_SERVER['REMOTE_ADDR']}')");
                
                $_SESSION['admin_message'] = "User deleted successfully.";
                $_SESSION['admin_message_type'] = "success";
            } else {
                $_SESSION['admin_message'] = "Failed to delete user: " . mysqli_error($con);
                $_SESSION['admin_message_type'] = "error";
            }
        }
    }
    
    // Redirect to refresh the page
    header("Location: admin_users.php");
    exit;
}

// Fetch all users with proper error handling
$users_query = mysqli_query($con, "SELECT * FROM users ORDER BY Id DESC");

// Check if query failed
if (!$users_query) {
    $query_error = "Error fetching users: " . mysqli_error($con);
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
    <title>Admin - User Management</title>
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
            overflow-x: hidden;
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .content-wrapper {
            margin-left: 250px;
            width: calc(100% - 250px);
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
        
        .filters {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            background-color: white;
            padding: 1rem;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .filter-group {
            display: flex;
            align-items: center;
        }
        
        .filter-group label {
            margin-right: 0.5rem;
            font-weight: 600;
        }
        
        .filter-group select, .filter-group input {
            padding: 0.5rem;
            border: 1px solid #d1d3e2;
            border-radius: 0.35rem;
            margin-right: 1rem;
        }
        
        .filter-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .filter-btn:hover {
            background-color: #2e59d9;
        }
        
        .add-btn {
            background-color: var(--success);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: background-color 0.3s;
        }
        
        .add-btn i {
            margin-right: 0.5rem;
        }
        
        .add-btn:hover {
            background-color: #17a673;
            color: white;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 0.35rem;
            overflow: hidden;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
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
        
        table tr:last-child td {
            border-bottom: none;
        }
        
        .status {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .status.active {
            background-color: #e3fcef;
            color: #1cc88a;
        }
        
        .status.inactive {
            background-color: #f8d7da;
            color: #e74a3b;
        }
        
        .action-btn {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            margin-right: 0.5rem;
            text-decoration: none;
            font-size: 0.875rem;
        }
        
        .action-btn.edit {
            background-color: var(--info);
            color: white;
        }
        
        .action-btn.edit:hover {
            background-color: #2a96a5;
            color: white;
        }
        
        .action-btn.activate {
            background-color: var(--success);
            color: white;
        }
        
        .action-btn.activate:hover {
            background-color: #17a673;
            color: white;
        }
        
        .action-btn.deactivate {
            background-color: var(--warning);
            color: #5a5c69;
        }
        
        .action-btn.deactivate:hover {
            background-color: #dda20a;
            color: #5a5c69;
        }
        
        .action-btn.delete {
            background-color: var(--danger);
            color: white;
        }
        
        .action-btn.delete:hover {
            background-color: #c82333;
            color: white;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
        }
        
        .pagination a, .pagination span {
            display: inline-block;
            padding: 0.5rem 0.75rem;
            margin: 0 0.25rem;
            border-radius: 0.25rem;
            text-decoration: none;
            color: var(--primary);
            background-color: white;
            border: 1px solid #dddfeb;
            transition: all 0.3s;
        }
        
        .pagination a:hover {
            background-color: #eaecf4;
            border-color: #dddfeb;
        }
        
        .pagination a.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .pagination .disabled {
            color: #b7b9cc;
            pointer-events: none;
            cursor: default;
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
        
        .user-photo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
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
                <li><a href="admin_assets.php"><i class="fas fa-laptop"></i> Assets</a></li>
                <li><a href="admin_users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="scpersonnel.php"><i class="fas fa-user-shield"></i> Security Personnel</a></li>
                <li><a href="admin_logs.php"><i class="fas fa-history"></i> System Logs</a></li>
                <li><a href="admin_settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="content-wrapper">
            <!-- Top Bar -->
            <div class="topbar">
                <h1>User Management</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="admin_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Display Messages -->
            <?php if (isset($_SESSION['admin_message'])): ?>
                <div class="message <?php echo $_SESSION['admin_message_type']; ?>">
                    <?php echo $_SESSION['admin_message']; ?>
                </div>
                <?php 
                // Clear the message after displaying
                unset($_SESSION['admin_message']);
                unset($_SESSION['admin_message_type']);
                ?>
            <?php endif; ?>
            
            <!-- Users Table -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-users"></i> All Users</h2>
                    <div>
                        <a href="admin_add_user.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New User
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (isset($query_error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $query_error; ?>
                        </div>
                    <?php elseif (mysqli_num_rows($users_query) == 0): ?>
                        <p>No users found in the database.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Reg Number</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>School</th>
                                        <th>Assets</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = mysqli_fetch_assoc($users_query)): ?>
                                        <?php
                                        // Count user's assets
                                        $reg_number = isset($user['Reg_Number']) ? $user['Reg_Number'] : '';
                                        $asset_count_query = mysqli_query($con, "SELECT COUNT(*) as count FROM assets WHERE reg_number = '$reg_number'");
                                        $asset_count = 0;
                                        
                                        if ($asset_count_query) {
                                            $asset_count = mysqli_fetch_assoc($asset_count_query)['count'];
                                        }
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['Id']); ?></td>
                                            <td><?php echo isset($user['Reg_Number']) ? htmlspecialchars($user['Reg_Number']) : 'N/A'; ?></td>
                                            <td>
                                                <?php 
                                                $name = '';
                                                if (isset($user['Username'])) {
                                                    $name .= $user['Username'];
                                                }
                                                if (isset($user['Lastname'])) {
                                                    $name .= ' ' . $user['Lastname'];
                                                }
                                                echo !empty($name) ? $name : 'N/A';
                                                ?>
                                            </td>
                                            <td><?php echo isset($user['Email']) ? htmlspecialchars($user['Email']) : 'N/A'; ?></td>
                                            <td><?php echo isset($user['Phone']) ? htmlspecialchars($user['Phone']) : 'N/A'; ?></td>
                                            <td><?php echo isset($user['School']) ? htmlspecialchars($user['School']) : 'N/A'; ?></td>
                                            <td><?php echo $asset_count; ?></td>
                                            <td>
                                                <a href="admin_view_user.php?id=<?php echo $user['Id']; ?>" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <a href="admin_edit_user.php?id=<?php echo $user['Id']; ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <?php if ($asset_count == 0): ?>
                                                    <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['Id']; ?>">
                                                        <input type="hidden" name="user_name" value="<?php echo isset($user['Username']) ? $user['Username'] . ' ' . $user['Lastname'] : 'Unknown'; ?>">
                                                        <input type="hidden" name="user_reg" value="<?php echo isset($user['Reg_Number']) ? $user['Reg_Number'] : ''; ?>">
                                                        <button type="submit" name="delete_user" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button class="btn btn-secondary btn-sm" disabled title="Cannot delete user with assets">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                    
                                                    <!-- Add View Assets button -->
                                                    <a href="admin_user_assets.php?reg_number=<?php echo urlencode($user['Reg_Number']); ?>" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-laptop"></i> View Assets
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>