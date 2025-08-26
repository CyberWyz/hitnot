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
    <link rel="stylesheet" href="style/missingassets.css">
    <title>Missing Assets</title>
   
</head>
<body>
    <!-- Sidebar navigation -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <span>Missing Assets</span>
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
    
   
</body>
</html>