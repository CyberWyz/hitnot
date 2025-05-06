// Process form submission
if (isset($_POST['submit'])) {
    // Validate and sanitize input
    $item_description = mysqli_real_escape_string($con, $_POST['item_description']);
    $item_model = mysqli_real_escape_string($con, $_POST['item_model']);
    $serial_number = mysqli_real_escape_string($con, $_POST['serial_number']);
    $rfid_uid = mysqli_real_escape_string($con, $_POST['rfid_uid']);
    $reg_number = mysqli_real_escape_string($con, $_POST['reg_number']);
    $asset_status = mysqli_real_escape_string($con, $_POST['asset_status']);
    
    // Check if RFID already exists (excluding current asset)
    $check_rfid = mysqli_query($con, "SELECT * FROM assets WHERE rfid_uid = '$rfid_uid' AND id != $asset_id");
    if (mysqli_num_rows($check_rfid) > 0) {
        $error = "RFID tag already exists in the system.";
    } 
    // Check if serial number already exists (excluding current asset)
    else if (!empty($serial_number)) {
        $check_serial = mysqli_query($con, "SELECT * FROM assets WHERE serial_number = '$serial_number' AND id != $asset_id");
        if (mysqli_num_rows($check_serial) > 0) {
            $error = "Serial number already exists in the system.";
        }
    }
    
    // If no errors, update the asset
    if (empty($error)) {
        $update_query = mysqli_query($con, "UPDATE assets SET 
                                          item_description = '$item_description', 
                                          item_model = '$item_model', 
                                          serial_number = '$serial_number', 
                                          rfid_uid = '$rfid_uid', 
                                          reg_number = '$reg_number', 
                                          AssetStatus = '$asset_status' 
                                          WHERE id = $asset_id");
        
        if ($update_query) {
            // Log the action
            mysqli_query($con, "INSERT INTO admin_logs (admin_id, action, details, ip_address) 
                              VALUES ($admin_id, 'edit_asset', 'Updated asset: $item_description (ID: $asset_id)', '{$_SERVER['REMOTE_ADDR']}')");
            
            $success = "Asset updated successfully!";
            
            // Refresh asset data
            $asset_query = mysqli_query($con, "SELECT * FROM assets WHERE id = $asset_id");
            $asset = mysqli_fetch_assoc($asset_query);
        } else {
            $error = "Failed to update asset: " . mysqli_error($con);
        }
    }
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
    <title>Admin - Edit Asset</title>
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
        
        .card {
            background-color: white;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            padding: 1.25rem;
            border-bottom: 1px solid #e3e6f0;
            background-color: #f8f9fc;
        }
        
        .card-header h2 {
            margin: 0;
            font-size: 1.25rem;
            color: var(--dark);
        }
        
        .card-body {
            padding: 1.25rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .form-control {
            display: block;
            width: 100%;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #d1d3e2;
            border-radius: 0.35rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            color: #495057;
            background-color: #fff;
            border-color: #bac8f3;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.35rem;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            cursor: pointer;
        }
        
        .btn-primary {
            color: #fff;
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            color: #fff;
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        
        .btn-secondary {
            color: #fff;
            background-color: var(--secondary);
            border-color: var(--secondary);
        }
        
        .btn-secondary:hover {
            color: #fff;
            background-color: #717384;
            border-color: #6b6d7d;
        }
        
        .alert {
            position: relative;
            padding: 0.75rem 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.35rem;
        }
        
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        
        .rfid-section {
            margin-top: 1rem;
            padding: 1rem;
            background-color: #f8f9fc;
            border-radius: 0.35rem;
            border: 1px solid #e3e6f0;
        }
        
        .rfid-status {
            margin-top: 0.5rem;
            padding: 0.5rem;
            border-radius: 0.25rem;
            font-weight: 600;
        }
        
        .status-waiting {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-error {
            background-color: #f8d7da;
            color: #721c24;
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
                <li><a href="admin_assets.php" class="active"><i class="fas fa-laptop"></i> Assets</a></li>
                <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="admin_security.php"><i class="fas fa-user-shield"></i> Security Personnel</a></li>
                <li><a href="admin_logs.php"><i class="fas fa-history"></i> System Logs</a></li>
                <li><a href="admin_settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="content-wrapper">
            <!-- Top Bar -->
            <div class="topbar">
                <h1>Edit Asset</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo $_SESSION['admin_username']; ?></span>
                    <a href="admin_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Edit Asset Form -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-edit"></i> Asset Information</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success">
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="" method="post">
                        <div class="form-group">
                            <label for="item_description">Asset Description *</label>
                            <input type="text" class="form-control" id="item_description" name="item_description" value="<?php echo htmlspecialchars($asset['item_description']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="item_model">Model/Make</label>
                            <input type="text" class="form-control" id="item_model" name="item_model" value="<?php echo htmlspecialchars($asset['item_model'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="serial_number">Serial Number</label>
                            <input type="text" class="form-control" id="serial_number" name="serial_number" value="<?php echo htmlspecialchars($asset['serial_number'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="rfid_uid">RFID Tag UID *</label>
                            <input type="text" class="form-control" id="rfid_uid" name="rfid_uid" value="<?php echo htmlspecialchars($asset['rfid_uid']); ?>" required>
                            
                            <div class="rfid-section">
                                <button type="button" id="scan-rfid" class="btn btn-secondary">
                                    <i class="fas fa-wifi"></i> Scan RFID Tag
                                </button>
                                <div id="rfid-status" class="rfid-status status-waiting">
                                    Current RFID: <?php echo htmlspecialchars($asset['rfid_uid']); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="reg_number">Asset Owner</label>
                            <select class="form-control" id="reg_number" name="reg_number">
                                <option value="">-- Select Owner (Optional) --</option>
                                <?php 
                                // Reset the pointer to the beginning of the result set
                                mysqli_data_seek($students_query, 0);
                                while ($student = mysqli_fetch_assoc($students_query)): 
                                ?>
                                    <option value="<?php echo $student['Reg_Number']; ?>" <?php echo ($asset['reg_number'] == $student['Reg_Number']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($student['Username'] . ' ' . $student['Lastname'] . ' (' . $student['Reg_Number'] . ')'); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="asset_status">Asset Status *</label>
                            <select class="form-control" id="asset_status" name="asset_status" required>
                                <option value="Active" <?php echo ($asset['AssetStatus'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                <option value="Missing" <?php echo ($asset['AssetStatus'] == 'Missing') ? 'selected' : ''; ?>>Missing</option>
                                <option value="Blacklisted" <?php echo ($asset['AssetStatus'] == 'Blacklisted') ? 'selected' : ''; ?>>Blacklisted</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Asset
                            </button>
                            <a href="admin_assets.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // RFID scanning functionality
        document.getElementById('scan-rfid').addEventListener('click', function() {
            const rfidStatus = document.getElementById('rfid-status');
            const rfidInput = document.getElementById('rfid_uid');
            
            rfidStatus.textContent = "Scanning for RFID tag...";
            rfidStatus.className = "rfid-status status-waiting";
            
            // Simulate RFID scanning (in a real implementation, this would connect to your RFID reader)
            fetch('read_rfid.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        rfidStatus.textContent = "RFID tag detected: " + data.rfid;
                        rfidStatus.className = "rfid-status status-success";
                        rfidInput.value = data.rfid;
                    } else {
                        rfidStatus.textContent = "Error: " + data.message;
                        rfidStatus.className = "rfid-status status-error";
                    }
                })
                .catch(error => {
                    rfidStatus.textContent = "Error connecting to RFID reader. Please try again.";
                    rfidStatus.className = "rfid-status status-error";
                    console.error('Error:', error);
                });
        });
    </script>
</body>
</html>