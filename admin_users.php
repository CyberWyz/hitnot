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
    <link rel="stylesheet" href="style/home-modern.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Admin - User Management</title>
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
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            color: var(--text-dark);
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
        }

        .btn-submit, .btn-delete, .btn-cancel {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            margin-right: 10px;
        }

        .btn-submit {
            background-color: var(--primary);
            color: white;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
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

        .action-btn.view {
            background-color: #17a2b8;
            color: white;
        }

        .action-btn.edit {
            background-color: #ffc107;
            color: #212529;
        }

        .action-btn.delete {
            background-color: #dc3545;
            color: white;
        }

        .action-btn.assets {
            background-color: #28a745;
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

        /* User Card Styles */
        .asset-info {
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

        .asset-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, var(--accent-teal), var(--primary-dark));
        }

        .asset-info h3 {
            color: var(--text-dark);
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .asset-info p {
            margin-bottom: 0.8rem;
            color: var(--text-dark);
        }

        .asset-info b {
            color: var(--accent-teal);
        }

        .asset-content {
            position: relative;
            z-index: 0;
        }

        /* Add User Button Styles */
        .add-user-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .add-user-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .add-user-btn:hover::before {
            left: 100%;
        }

        .add-user-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
            background: linear-gradient(135deg, #0056b3, #004085);
        }

        .add-user-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(0, 123, 255, 0.3);
        }

        .add-user-btn i {
            font-size: 18px;
        }

        .user-details {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .user-photo-section {
            text-align: center;
            margin-bottom: 20px;
        }

        .user-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-dark);
        }

        .user-stats {
            background: rgba(75, 100, 141, 0.1);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .user-stats h4 {
            color: var(--primary-dark);
            margin-bottom: 10px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .stat-value {
            font-weight: bold;
            color: var(--accent-teal);
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
                    <a href="admin_users.php" class="active">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
                <li>
                    <a href="admin_security_approvals.php">
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
                    <h1>User Management</h1>
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
                    // Clear the message after displaying
                    unset($_SESSION['admin_message']);
                    unset($_SESSION['admin_message_type']);
                    ?>
                <?php endif; ?>
                
                <!-- Add User Button -->
                <div class="filters-section" style="margin-bottom: 20px;">
                    <a href="admin_add_user.php" class="add-user-btn">
                        <i class="fas fa-user-plus"></i> Add New User
                    </a>
                </div>
                
                <!-- Users Display -->
                <div class="main-box">
                    <h2><i class="fas fa-users"></i> All Users</h2>
                    
                    <?php if (isset($query_error)): ?>
                        <div class="message error">
                            <?php echo $query_error; ?>
                        </div>
                    <?php elseif (mysqli_num_rows($users_query) == 0): ?>
                        <p style="text-align: center; padding: 20px;">No users found in the database.</p>
                    <?php else: ?>
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
                            <div class="asset-info">
                                <div class="asset-content">
                                    <h3><?php echo htmlspecialchars($user['Username'] . ' ' . $user['Lastname']); ?></h3>
                                    <div style="display: flex; flex-wrap: wrap; gap: 20px;">
                                        <div style="flex: 2; min-width: 300px;">
                                            <p><b>User ID:</b> <?php echo htmlspecialchars($user['Id']); ?></p>
                                            <p><b>Registration Number:</b> <?php echo isset($user['Reg_Number']) ? htmlspecialchars($user['Reg_Number']) : 'N/A'; ?></p>
                                            <p><b>Email:</b> <?php echo isset($user['Email']) ? htmlspecialchars($user['Email']) : 'N/A'; ?></p>
                                            <p><b>Phone:</b> <?php echo isset($user['Phone']) ? htmlspecialchars($user['Phone']) : 'N/A'; ?></p>
                                            <p><b>School:</b> <?php echo isset($user['School']) ? htmlspecialchars($user['School']) : 'N/A'; ?></p>
                                            
                                            <div class="user-stats">
                                                <h4><i class="fas fa-chart-bar"></i> User Statistics</h4>
                                                <div class="stat-item">
                                                    <span>Registered Assets:</span>
                                                    <span class="stat-value"><?php echo $asset_count; ?></span>
                                                </div>
                                            </div>
                                            
                                            <!-- Admin Actions -->
                                            <div class="admin-actions" style="margin-top: 15px;">
                                                <a href="admin_view_user.php?id=<?php echo $user['Id']; ?>" class="action-btn view">
                                                    <i class="fas fa-eye"></i> View Details
                                                </a>
                                                <a href="admin_edit_user.php?id=<?php echo $user['Id']; ?>" class="action-btn edit">
                                                    <i class="fas fa-edit"></i> Edit User
                                                </a>
                                                <?php if ($asset_count > 0): ?>
                                                    <a href="admin_user_assets.php?reg_number=<?php echo urlencode($user['Reg_Number']); ?>" class="action-btn assets">
                                                        <i class="fas fa-laptop"></i> View Assets (<?php echo $asset_count; ?>)
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($asset_count == 0): ?>
                                                    <button type="button" class="action-btn delete" onclick="deleteUser(<?php echo $user['Id']; ?>, '<?php echo htmlspecialchars($user['Username'] . ' ' . $user['Lastname']); ?>', '<?php echo htmlspecialchars($user['Reg_Number']); ?>')">
                                                        <i class="fas fa-trash"></i> Delete User
                                                    </button>
                                                <?php else: ?>
                                                    <button class="action-btn delete" disabled title="Cannot delete user with assets" style="opacity: 0.5; cursor: not-allowed;">
                                                        <i class="fas fa-trash"></i> Delete User
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div style="flex: 1; min-width: 220px;">
                                            <div class="asset-details-container" style="display: flex; flex-direction: column; gap: 20px;">
                                                <?php if (!empty($user['myphoto'])): ?>
                                                    <div class="asset-image">
                                                        <h4><i class="fas fa-camera"></i> User Photo</h4>
                                                        <img src="<?php echo htmlspecialchars($user['myphoto']); ?>" alt="User Photo">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-trash"></i> Delete User</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this user? This action cannot be undone.</p>
                <p><strong>User:</strong> <span id="deleteUserName"></span></p>
                <p><strong>Registration Number:</strong> <span id="deleteUserReg"></span></p>
                <form id="deleteForm" action="" method="post">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <input type="hidden" name="user_name" id="deleteUserNameInput">
                    <input type="hidden" name="user_reg" id="deleteUserRegInput">
                    <button type="submit" name="delete_user" class="btn-delete">Yes, Delete User</button>
                    <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                </form>
            </div>
        </div>
    </div>

    <script src="js/particles.js"></script>
    <script src="js/home.js"></script>
    <script>
        // Modal functionality
        var deleteModal = document.getElementById('deleteModal');
        var closeBtns = document.getElementsByClassName('close');

        function deleteUser(userId, userName, userReg) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('deleteUserNameInput').value = userName;
            document.getElementById('deleteUserRegInput').value = userReg;
            document.getElementById('deleteUserName').textContent = userName;
            document.getElementById('deleteUserReg').textContent = userReg;
            deleteModal.style.display = 'block';
        }

        function closeDeleteModal() {
            deleteModal.style.display = 'none';
        }

        for (var i = 0; i < closeBtns.length; i++) {
            closeBtns[i].onclick = function() {
                deleteModal.style.display = 'none';
            }
        }

        window.onclick = function(event) {
            if (event.target == deleteModal) {
                deleteModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>