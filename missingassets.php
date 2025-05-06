<?php
session_start();
include("php/config.php");

if (!isset($_SESSION['valid'])) {
    header("Location: login_scpersonnel.php");
    exit;
}

$id = $_SESSION['id'];
$query = mysqli_query($con, "SELECT * FROM scpersonnel WHERE id=$id");

while ($result = mysqli_fetch_assoc($query)) {
    $res_name = $result['name'];
}

$message = "";

// Handle recovering an asset
if (isset($_POST['recover_asset'])) {
    $asset_id = mysqli_real_escape_string($con, $_POST['asset_id']);
    $recovery_notes = mysqli_real_escape_string($con, $_POST['recovery_notes']);
    
    // Update the asset status
    $update_query = mysqli_query($con, "UPDATE assets SET 
                                      AssetStatus = 'Recovered',
                                      date_recovered = NOW(),
                                      recovery_notes = '$recovery_notes'
                                      WHERE serial_number = '$asset_id'");
    
    if ($update_query) {
        $message = "<div class='message success'><p>Asset has been successfully marked as recovered.</p></div>";
    } else {
        $message = "<div class='message error'><p>Failed to update asset status: " . mysqli_error($con) . "</p></div>";
    }
}

// Get all missing assets with student information
$query = mysqli_query($con, "SELECT a.*, u.Username, u.Lastname, u.Email, u.Phone, u.School, u.myphoto 
                            FROM assets a 
                            LEFT JOIN users u ON a.reg_number = u.Reg_Number 
                            WHERE a.AssetStatus = 'Missing' 
                            ORDER BY a.date_reported_missing DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Missing Assets</title>
    <style>
        /* Sidebar styles */
        :root {
            --primary: #4e73df;
            --secondary: #858796;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --light: #f8f9fc;
            --dark: #5a5c69;
        }
        
        body {
            margin: 0;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fc;
            display: flex;
        }
        
        .sidebar {
            width: 250px;
            background-color: #4e73df;
            background-image: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            color: white;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
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
            margin: 0;
        }
        
        .sidebar-menu li {
            margin: 0;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 1rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu a.active {
            color: white;
            font-weight: 700;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        /* Content wrapper */
        .content-wrapper {
            flex: 1;
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 1.5rem;
        }
        
        /* Table styles */
        .missing-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .missing-table th, .missing-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .missing-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .missing-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .missing-table tr:hover {
            background-color: #f1f1f1;
        }
        .status-missing {
            background-color: #dc3545;
            color: white;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 0.8em;
        }
        .btn-recover {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }
        .btn-recover:hover {
            background-color: #218838;
        }
        .no-assets {
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }
        .back-link {
            margin-bottom: 20px;
            display: inline-block;
        }
        .asset-image {
            max-width: 100px;
            max-height: 100px;
            border-radius: 4px;
        }
        .student-photo {
            max-width: 80px;
            max-height: 80px;
            border-radius: 50%;
        }
        .recovery-notes {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 5px;
            width: 50%;
            max-width: 500px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
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
    </style>
</head>
<body>
    <!-- Sidebar navigation -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <span>HitNot System</span>
        </div>
        <ul class="sidebar-menu">
            <li><a href="schome.php">Dashboard</a></li>
            <li><a href="missingassets.php" class="active">Missing Assets</a></li>
            <li><a href="regAsset.php">Register New Asset</a></li>
            <li><a href="blacklistedassets.php">Blacklisted Assets</a></li>
            <li><a href="verifyassets.php">Verify Asset</a></li>
            <li><a href="php/logout.php">Logout</a></li>
        </ul>
    </div>
    
    <!-- Main content -->
    <div class="content-wrapper">
        <div class="box">
            <h2>Missing Assets</h2>
            <a href="schome.php" class="back-link">← Back to Dashboard</a>
            
            <?php echo $message; ?>
            
            <?php if (mysqli_num_rows($query) > 0): ?>
                <table class="missing-table">
                    <thead>
                        <tr>
                            <th>Asset Details</th>
                            <th>Student Information</th>
                            <th>Missing Report</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($asset = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($asset['picture']) && file_exists($asset['picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($asset['picture']); ?>" alt="Asset Image" class="asset-image"><br>
                                    <?php endif; ?>
                                    <strong>Description:</strong> <?php echo htmlspecialchars($asset['item_description']); ?><br>
                                    <strong>Serial:</strong> <?php echo htmlspecialchars($asset['serial_number']); ?><br>
                                    <strong>Model:</strong> <?php echo htmlspecialchars($asset['item_model']); ?><br>
                                    <strong>RFID:</strong> <code><?php echo htmlspecialchars($asset['rfid_uid']); ?></code><br>
                                    <span class="status-missing">MISSING</span>
                                </td>
                                <td>
                                    <?php if (!empty($asset['Username']) && !empty($asset['Lastname'])): ?>
                                        <?php if (!empty($asset['myphoto']) && file_exists($asset['myphoto'])): ?>
                                            <img src="<?php echo htmlspecialchars($asset['myphoto']); ?>" alt="Student Photo" class="student-photo"><br>
                                        <?php endif; ?>
                                        <strong>Name:</strong> <?php echo htmlspecialchars($asset['Username'] . ' ' . $asset['Lastname']); ?><br>
                                        <strong>Reg Number:</strong> <?php echo htmlspecialchars($asset['reg_number']); ?><br>
                                        <strong>Email:</strong> <?php echo htmlspecialchars($asset['Email']); ?><br>
                                        <strong>Phone:</strong> <?php echo htmlspecialchars($asset['Phone']); ?><br>
                                        <strong>School:</strong> <?php echo htmlspecialchars($asset['School']); ?>
                                    <?php else: ?>
                                        <em>No student information available</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong>Date Reported:</strong><br>
                                    <?php echo !empty($asset['date_reported_missing']) ? 
                                        date('Y-m-d H:i', strtotime($asset['date_reported_missing'])) : 'Unknown'; ?><br>
                                    <strong>Days Missing:</strong><br>
                                    <?php 
                                        if (!empty($asset['date_reported_missing'])) {
                                            $reported_date = new DateTime($asset['date_reported_missing']);
                                            $current_date = new DateTime();
                                            $interval = $reported_date->diff($current_date);
                                            echo $interval->days . ' days';
                                        } else {
                                            echo 'Unknown';
                                        }
                                    ?>
                                </td>
                                <td>
                                    <button class="btn-recover" onclick="openRecoveryModal('<?php echo $asset['serial_number']; ?>')">Mark as Recovered</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-assets">
                    <p>No missing assets found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recovery Modal -->
    <div id="recoveryModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeRecoveryModal()">×</span>
            <h3>Mark Asset as Recovered</h3>
            <form action="" method="post">
                <input type="hidden" id="asset_id" name="asset_id" value="">
                <div>
                    <label for="recovery_notes">Recovery Notes:</label>
                    <textarea name="recovery_notes" id="recovery_notes" class="recovery-notes" rows="4" placeholder="Enter details about how the asset was recovered..."></textarea>
                </div>
                <button type="submit" name="recover_asset" class="btn-recover">Confirm Recovery</button>
            </form>
        </div>
    </div>
    
    <script>
        // Modal functions
        function openRecoveryModal(assetId) {
            document.getElementById('asset_id').value = assetId;
            document.getElementById('recoveryModal').style.display = 'block';
        }
        
        function closeRecoveryModal() {
            document.getElementById('recoveryModal').style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('recoveryModal');
            if (event.target == modal) {
                closeRecoveryModal();
            }
        }
    </script>
</body>
</html>