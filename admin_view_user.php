<?php
session_start();
include("php/config.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_valid']) || $_SESSION['admin_valid'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['admin_message'] = "User ID is required.";
    $_SESSION['admin_message_type'] = "error";
    header("Location: admin_users.php");
    exit;
}

$user_id = mysqli_real_escape_string($con, $_GET['id']);

// Get user details
$user_query = mysqli_query($con, "SELECT * FROM users WHERE Id = '$user_id'");

if (!$user_query || mysqli_num_rows($user_query) == 0) {
    $_SESSION['admin_message'] = "User not found.";
    $_SESSION['admin_message_type'] = "error";
    header("Location: admin_users.php");
    exit;
}

$user = mysqli_fetch_assoc($user_query);

// Get user's assets
$reg_number = $user['Reg_Number'];
$assets_query = mysqli_query($con, "SELECT * FROM assets WHERE reg_number = '$reg_number'");

// Message variable
$message = "";
if (isset($_SESSION['admin_message'])) {
    $message_type = $_SESSION['admin_message_type'] ?? 'info';
    $message = "<div class='message {$message_type}'>{$_SESSION['admin_message']}</div>";
    unset($_SESSION['admin_message']);
    unset($_SESSION['admin_message_type']);
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
    <title>View User - Admin Portal</title>
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
                <h1>View User</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['admin_username']; ?></span>
                    <a href="admin_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Message Display -->
            <?php echo $message; ?>
            
            <!-- User Details Card -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-user"></i> User Details</h2>
                    <div>
                        <a href="admin_users.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Users
                        </a>
                        <a href="admin_edit_user.php?id=<?php echo $user['Id']; ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit User
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="user-profile">
                        <div class="user-avatar">
                            <?php if (!empty($user['myphoto'])): ?>
                                <img src="<?php echo htmlspecialchars($user['myphoto']); ?>" alt="User Photo">
                            <?php else: ?>
                                <div class="default-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="user-details">
                            <h3><?php echo htmlspecialchars($user['Username'] . ' ' . $user['Lastname']); ?></h3>
                            <p><strong>Registration Number:</strong> <?php echo htmlspecialchars($user['Reg_Number']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['Phone']); ?></p>
                            <p><strong>School:</strong> <?php echo htmlspecialchars($user['School']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- User's Assets -->
            <div class="card mt-4">
                <div class="card-header">
                    <h2><i class="fas fa-laptop"></i> User's Assets</h2>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($assets_query) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Serial Number</th>
                                        <th>Item Description</th>
                                        <th>Model</th>
                                        <th>Date Registered</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($asset = mysqli_fetch_assoc($assets_query)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($asset['serial_number']); ?></td>
                                            <td><?php echo htmlspecialchars($asset['item_description']); ?></td>
                                            <td><?php echo htmlspecialchars($asset['item_model']); ?></td>
                                            <td><?php echo htmlspecialchars($asset['date_registered']); ?></td>
                                            <td>
                                                <?php 
                                                $status = isset($asset['AssetStatus']) ? $asset['AssetStatus'] : 'Active';
                                                $status_class = '';
                                                
                                                switch ($status) {
                                                    case 'Missing':
                                                        $status_class = 'status-missing';
                                                        break;
                                                    case 'Blacklisted':
                                                        $status_class = 'status-blacklisted';
                                                        break;
                                                    default:
                                                        $status_class = 'status-active';
                                                }
                                                ?>
                                                <span class="status <?php echo $status_class; ?>">
                                                    <?php echo htmlspecialchars($status); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="admin_view_asset.php?id=<?php echo $asset['serial_number']; ?>" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No assets found for this user.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>