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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $lastname = mysqli_real_escape_string($con, $_POST['lastname']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $phone = mysqli_real_escape_string($con, $_POST['phone']);
    $school = mysqli_real_escape_string($con, $_POST['school']);
    
    // Update user
    $update_query = mysqli_query($con, "UPDATE users SET 
                                    Username = '$username',
                                    Lastname = '$lastname',
                                    Email = '$email',
                                    Phone = '$phone',
                                    School = '$school'
                                    WHERE Id = '$user_id'");
    
    if ($update_query) {
        // Log the action
        mysqli_query($con, "INSERT INTO admin_logs (admin_id, action, description, ip_address) 
                      VALUES ($admin_id, 'edit', 'Updated user: $username $lastname', '{$_SERVER['REMOTE_ADDR']}')");
        
        $_SESSION['admin_message'] = "User updated successfully.";
        $_SESSION['admin_message_type'] = "success";
        header("Location: admin_view_user.php?id=$user_id");
        exit;
    } else {
        $message = "<div class='message error'><p>Failed to update user: " . mysqli_error($con) . "</p></div>";
    }
}

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
    <title>Edit User - Admin Portal</title>
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
                <h1>Edit User</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['admin_username']; ?></span>
                    <a href="admin_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Message Display -->
            <?php echo $message; ?>
            
            <!-- Edit User Form -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-user-edit"></i> Edit User Information</h2>
                    <div>
                        <a href="admin_view_user.php?id=<?php echo $user['Id']; ?>" class="btn btn-secondary">
                            <i class="fas fa-eye"></i> View User
                        </a>
                        <a href="admin_users.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Users
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <div class="form-group">
                            <label for="username">First Name</label>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['Username']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="lastname">Last Name</label>
                            <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['Lastname']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['Phone']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="school">School</label>
                            <input type="text" id="school" name="school" value="<?php echo htmlspecialchars($user['School']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="reg_number">Registration Number (Read Only)</label>
                            <input type="text" id="reg_number" value="<?php echo htmlspecialchars($user['Reg_Number']); ?>" readonly>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="update_user" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>