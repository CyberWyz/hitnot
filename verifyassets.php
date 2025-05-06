<?php
session_start();
// Force session to not be saved in browser cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include("php/config.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = "";
$owner_verified = false;
$data_file = "rfid_data.txt"; // File where Arduino/Python writes the RFID data

// Initialize session variables
if (!isset($_SESSION['asset_details'])) $_SESSION['asset_details'] = [];
if (!isset($_SESSION['student_details'])) $_SESSION['student_details'] = [];
if (!isset($_SESSION['officer_details'])) $_SESSION['officer_details'] = [];
if (!isset($_SESSION['assets_count'])) $_SESSION['assets_count'] = 0;

// Function to log scan to database
function logScan($rfid_uid, $status) {
    global $con;
    
    // Get officer ID from session
    $scanner_id = isset($_SESSION['officer_id']) ? $_SESSION['officer_id'] : 
                 (isset($_SESSION['officer_details']['officer_id']) ? $_SESSION['officer_details']['officer_id'] : 'unknown');
    
    // Get location (can be set in configuration or passed as parameter)
    $location = isset($_SESSION['scan_location']) ? $_SESSION['scan_location'] : 'Main Gate';
    
    // Prepare and execute query
    $query = "INSERT INTO scan_logs (rfid_uid, scanner_id, location, status) 
              VALUES (?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($con, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $rfid_uid, $scanner_id, $location, $status);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if (!$result) {
            error_log("Failed to log scan: " . mysqli_error($con));
        }
    } else {
        error_log("Failed to prepare scan log statement: " . mysqli_error($con));
    }
}

// Function to verify asset owner
function verifyOwner() {
    global $message, $owner_verified, $con;
    
    if (!empty($_SESSION['asset_details']) && !empty($_SESSION['student_details'])) {
        if ($_SESSION['asset_details']['reg_number'] === $_SESSION['student_details']['Reg_Number']) {
            $message .= "<div class='message success'><p>OWNER VERIFIED: This asset belongs to the scanned student.</p></div>";
            $owner_verified = true;
        } else {
            // Owner mismatch - blacklist the asset
            $asset_serial = mysqli_real_escape_string($con, $_SESSION['asset_details']['serial_number']);
            $scanned_student = mysqli_real_escape_string($con, $_SESSION['student_details']['Username'] . ' ' . $_SESSION['student_details']['Lastname']);
            $registered_owner = mysqli_real_escape_string($con, $_SESSION['asset_details']['Username'] . ' ' . $_SESSION['asset_details']['Lastname']);
            
            // Create a detailed blacklist reason that includes both the registered owner and the scanned student
            $blacklist_reason = "Ownership mismatch. Asset registered to: $registered_owner but found with: $scanned_student";
            
            // Update only the AssetStatus and related fields
            $blacklist_query = "UPDATE assets SET 
                                AssetStatus = 'Blacklisted',
                                date_blacklisted = NOW(),
                                blacklist_reason = '$blacklist_reason'
                                WHERE serial_number = '$asset_serial'";
            
            if (mysqli_query($con, $blacklist_query)) {
                $message .= "<div class='message error'><p>OWNER MISMATCH: This asset does NOT belong to the scanned student.</p>";
                $message .= "<p>The asset has been automatically BLACKLISTED.</p>";
                $message .= "<p>Registered owner: <strong>" . htmlspecialchars($registered_owner) . "</strong></p>";
                $message .= "<p>Found with: <strong>" . htmlspecialchars($scanned_student) . "</strong></p></div>";
                
                // Log the blacklisting action
                logScan($_SESSION['asset_details']['rfid_uid'], 'blacklisted');
            } else {
                $message .= "<div class='message error'><p>OWNER MISMATCH: This asset does NOT belong to the scanned student.</p>";
                $message .= "<p>Failed to blacklist asset: " . mysqli_error($con) . "</p></div>";
            }
            
            $owner_verified = false;
        }
    }
}

// AJAX endpoint to get the RFID data from the text file
if (isset($_GET['read_rfid'])) {
    header('Content-Type: application/json');
    
    if (file_exists($data_file) && is_readable($data_file)) {
        $rfid_data = file_get_contents($data_file);
        $uid = trim($rfid_data);
        
        // Remove any trailing commas
        $uid = rtrim($uid, ',');
        
        // Format the RFID data if it's in the format XX,XX,XX,XX
        if (preg_match('/^([0-9A-F]{2},)*[0-9A-F]{2}$/i', $uid)) {
            // Remove commas to get a clean hex string
            $formatted_uid = 'VIRT_' . str_replace(',', '', $uid);
            echo json_encode(['uid' => $formatted_uid, 'status' => 'success']);
        } else {
            // If it's already formatted or in another format, just pass it through
            echo json_encode(['uid' => $uid, 'status' => 'success']);
        }
    } else {
        echo json_encode(['uid' => 'Error: Cannot read RFID data file', 'status' => 'error']);
    }
    exit;
}

// Handle RFID Verification (both UID and Secret)
if (isset($_POST['rfid_data'])) {
    $input = trim($_POST['rfid_data']);
    
    // Check if this is a different RFID than the last one scanned
    $is_new_rfid = true;
    if (!empty($_SESSION['asset_details']) && 
        (($_SESSION['asset_details']['rfid_uid'] == $input) || 
         ($_SESSION['asset_details']['rfid_secret'] == $input))) {
        $is_new_rfid = false;
    }
    
    // Only clear student details if this is a different RFID
    if ($is_new_rfid) {
        $_SESSION['asset_details'] = [];
        $_SESSION['student_details'] = [];
        $_SESSION['assets_count'] = 0;
    } else {
        // Just clear asset details to refresh them
        $_SESSION['asset_details'] = [];
    }
    
    // Determine if input is UID (VIRT_) or Secret (64 hex chars)
    if (preg_match('/^VIRT_[a-f0-9]+$/i', $input)) {
        // Handle as UID
        $query = "SELECT assets.*, users.Username, users.Lastname, users.School 
                 FROM assets 
                 JOIN users ON assets.reg_number = users.Reg_Number
                 WHERE assets.rfid_uid = ?";
        $type = "UID";
    } elseif (preg_match('/^[a-f0-9]{64}$/i', $input)) {
        // Handle as Secret
        $query = "SELECT assets.*, users.Username, users.Lastname, users.School 
                 FROM assets 
                 JOIN users ON assets.reg_number = users.Reg_Number
                 WHERE assets.rfid_secret = ?";
        $type = "Secret";
    } else {
        $message = "<div class='message error'><p>Invalid RFID format: " . htmlspecialchars($input) . "</p></div>";
        error_log("Invalid RFID format: " . $input);
    }

    if (empty($message)) {
        $stmt = mysqli_prepare($con, $query);
        if (!$stmt) {
            $message = "<div class='message error'><p>Database error: " . htmlspecialchars(mysqli_error($con)) . "</p></div>";
            error_log("Prepare failed: " . mysqli_error($con));
        } else {
            mysqli_stmt_bind_param($stmt, "s", $input);
            if (!mysqli_stmt_execute($stmt)) {
                $message = "<div class='message error'><p>Query failed: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p></div>";
                error_log("Execute failed: " . mysqli_stmt_error($stmt));
            } else {
                $result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($result) > 0) {
                    $_SESSION['asset_details'] = mysqli_fetch_assoc($result);
                    error_log("Found asset: " . print_r($_SESSION['asset_details'], true));
                    
                    // Update last scanned - use serial_number instead of id
                    $update = mysqli_query($con, "UPDATE assets SET last_scanned = NOW() WHERE serial_number = '" . 
                        mysqli_real_escape_string($con, $_SESSION['asset_details']['serial_number']) . "'");
                    if (!$update) {
                        error_log("Update failed: " . mysqli_error($con));
                    }
                    
                    // When a tampered tag is detected
                    if ($_SESSION['asset_details']['rfid_status'] == 'tampered') {
                        $message = "<div class='message error'><p>TAMPERED TAG DETECTED!</p></div>";
                        // Log with the actual asset status plus tampered flag
                        logScan($input, $_SESSION['asset_details']['AssetStatus'] . '_tampered');
                    } else {
                        $message = "<div class='message success'><p>RFID verification successful! (Using $type)</p></div>";
                        
                        // Log the scan with the actual asset status from the database
                        logScan($input, $_SESSION['asset_details']['AssetStatus']);
                    }
                    
                    // Log secret usage if applicable
                    if ($type == "Secret") {
                        $officer_id = isset($_SESSION['officer_id']) ? $_SESSION['officer_id'] : 
                                     (isset($_SESSION['officer_details']['officer_id']) ? $_SESSION['officer_details']['officer_id'] : 'unknown');
                        $log = mysqli_query($con, "INSERT INTO secret_logs (officer_id, asset_id, used_at) 
                                               VALUES ('" . mysqli_real_escape_string($con, $officer_id) . "', '" . 
                                               mysqli_real_escape_string($con, $_SESSION['asset_details']['serial_number']) . "', NOW())");
                        if (!$log) {
                            error_log("Secret log failed: " . mysqli_error($con));
                        }
                    }
                    
                    // Check if student details are already available for owner verification
                    if (!empty($_SESSION['student_details'])) {
                        verifyOwner();
                    }
                } else {
                    // When an RFID is not found in the system
                    if ($type == "Secret") {
                        $message = "<div class='message error'><p>Invalid RFID secret - not found in system</p></div>";
                    } else {
                        $message = "<div class='message error'><p>Invalid RFID UID - not found in system</p></div>";
                    }
                    error_log("No asset found for $type: " . $input);
                    
                    // Log as not found
                    logScan($input, 'not_found');
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Fetch student details based on the registration number
if (isset($_POST['reg_number'])) {
    $reg_number = mysqli_real_escape_string($con, $_POST['reg_number']);
    $student_query = "SELECT * FROM users WHERE Reg_Number = '$reg_number' LIMIT 1";
    
    $student_result = mysqli_query($con, $student_query);

    if ($student_result === false) {
        $message = "<div class='message error'><p>Student Query Failed: " . htmlspecialchars(mysqli_error($con)) . "</p></div>";
        error_log("Student Query Failed: " . mysqli_error($con));
    } else {
        if (mysqli_num_rows($student_result) > 0) {
            $_SESSION['student_details'] = mysqli_fetch_assoc($student_result);

            // Fetch all assets owned by the student
            $assets_query = "SELECT * FROM assets WHERE reg_number = '$reg_number'";
            
            $assets_result = mysqli_query($con, $assets_query);

            if ($assets_result === false) {
                $message = "<div class='message error'><p>Assets Query Failed: " . htmlspecialchars(mysqli_error($con)) . "</p></div>";
                error_log("Assets Query Failed: " . mysqli_error($con));
            } else {
                $_SESSION['assets_count'] = mysqli_num_rows($assets_result);

                // Fetch officer details using the officer_id from the student QR code
                if (isset($_POST['officer_id'])) {
                    $officer_id = mysqli_real_escape_string($con, $_POST['officer_id']);
                    $officer_query = "SELECT * FROM scpersonnel WHERE officer_id = '$officer_id' LIMIT 1";
                    
                    $officer_result = mysqli_query($con, $officer_query);

                    if ($officer_result === false) {
                        $message = "<div class='message error'><p>Officer Query Failed: " . htmlspecialchars(mysqli_error($con)) . "</p></div>";
                        error_log("Officer Query Failed: " . mysqli_error($con));
                    } else {
                        if (mysqli_num_rows($officer_result) > 0) {
                            $_SESSION['officer_details'] = mysqli_fetch_assoc($officer_result);
                            // Store officer_id in session for scan logging
                            $_SESSION['officer_id'] = $_SESSION['officer_details']['officer_id'];
                        } else {
                            $message = "<div class='message error'><p>Officer not found with ID: $officer_id</p></div>";
                        }
                    }
                }
                
                // Check if asset details are already available for owner verification
                if (!empty($_SESSION['asset_details'])) {
                    verifyOwner();
                }
            }
        } else {
            $message = "<div class='message error'><p>Student not found with registration number: $reg_number</p></div>";
        }
    }
}

// Check if we need to show the missing asset modal
$show_missing_modal = false;
if (!empty($_SESSION['asset_details']) && isset($_SESSION['asset_details']['AssetStatus']) && $_SESSION['asset_details']['AssetStatus'] == 'missing') {
    $show_missing_modal = true;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Assets</title>
    <script src="https://unpkg.com/html5-qrcode@2.0.9/dist/html5-qrcode.min.js"></script>
    <style>
        /* RFID Section Styles */
        
        .rfid-verification {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .rfid-verification h3 {
            margin-top: 0;
            color: #495057;
        }
        
        #rfid-form {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        
        #rfid-form .field {
            flex: 1;
        }
        
        #manual-rfid-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-family: monospace;
        }
        
        .rfid-details {
            margin-top: 15px;
            padding: 15px;
            background: white;
            border: 1px solid #eee;
            border-radius: 4px;
        }
        
        .rfid-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-tampered {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-lost {
            background: #fff3cd;
            color: #856404;
        }
        /* Owner Verification Section */
        
        .owner-verification {
            margin: 20px 0;
            padding: 15px;
            border-radius: 5px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        
        .owner-verification h3 {
            margin-top: 0;
            color: #495057;
        }
        /* Container and Layout */
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .box.form-box {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        .main-content {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        
        .left-section,
        .right-section {
            flex: 1;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 5px;
        }
        /* Photo and Details */
        
        .photo-container img {
            max-width: 200px;
            max-height: 200px;
            border: 1px solid #ddd;
            margin: 10px 0;
        }
        /* Messages */
        
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
        }
        /* Scanner Sections */
        
        .scanner-section {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 5px;
        }
        /* Buttons */
        
        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn:hover {
            background: #0069d9;
        }
        /* Debug Info */
        
        .debug-info {
            background: #f0f0f0;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            font-family: monospace;
        }
        
        /* Officer Info Bar */
        .officer-info-bar {
            background: #343a40;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .officer-info-bar .officer-details {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .officer-info-bar .officer-name {
            font-weight: bold;
            font-size: 1.1em;
        }
        
        .officer-info-bar .officer-id {
            background: rgba(255, 255, 255, 0.2);
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.9em;
        }
        
        .officer-info-bar .scan-count {
            background: #007bff;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9em;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.7);
        }
        
        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 10% auto;
            padding: 0;
            width: 70%;
            max-width: 700px;
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
            animation: modalopen 0.5s;
        }
        
        @keyframes modalopen {
            from {opacity: 0; transform: translateY(-60px);}
            to {opacity: 1; transform: translateY(0);}
        }
        
        .emergency-modal .modal-content {
            background-color: #fff;
            border: 3px solid #dc3545;
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }
        
        .emergency-modal .modal-header {
            background-color: #dc3545;
            color: white;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .emergency-modal h2 {
            margin: 0;
            font-size: 1.5rem;
        }
        
        .emergency-modal .modal-body {
            padding: 20px;
        }
        
        .emergency-modal .modal-footer {
            padding: 15px;
            border-top: 1px solid #dee2e6;
            text-align: right;
        }
        
        .emergency-message {
            font-size: 1.1rem;
        }
        
        .emergency-message p {
            margin-bottom: 15px;
        }
        
        .emergency-message strong {
            color: #dc3545;
            font-size: 1.2em;
        }
        
        .action-instructions {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .action-instructions h3 {
            margin-top: 0;
            color: #dc3545;
        }
        
        .action-instructions ol {
            padding-left: 20px;
        }
        
        .action-instructions li {
            margin-bottom: 8px;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .missing-detail {
            margin-bottom: 10px;
            padding: 8px;
            background-color: #f8d7da;
            border-radius: 4px;
        }
        
        .missing-detail strong {
            color: #721c24;
        }
        
        .close-modal {
            color: white;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close-modal:hover {
            color: #f8f9fa;
        }
        
        .status-missing {
            background: #dc3545;
            color: white;
        }
        
        .status-blacklisted {
            background: #343a40;
            color: white;
        }
        
        .status-recovered {
            background: #28a745;
            color: white;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Officer Info Bar -->
        <?php if (isset($_SESSION['officer_details']) && !empty($_SESSION['officer_details'])): ?>
        <div class="officer-info-bar">
            <div class="officer-details">
                <div class="officer-name"><?php echo htmlspecialchars($_SESSION['officer_details']['name'] . ' ' . $_SESSION['officer_details']['lastname']); ?></div>
                <div class="officer-id">ID: <?php echo htmlspecialchars($_SESSION['officer_details']['officer_id']); ?></div>
            </div>
            <div class="scan-count">
                <?php 
                    // Get scan count for today
                    $officer_id = mysqli_real_escape_string($con, $_SESSION['officer_details']['officer_id']);
                    $scan_count_query = "SELECT COUNT(*) as count FROM scan_logs WHERE scanner_id = '$officer_id' AND DATE(scan_time) = CURDATE()";
                    $scan_count_result = mysqli_query($con, $scan_count_query);
                    $scan_count = 0;
                    if ($scan_count_result && $row = mysqli_fetch_assoc($scan_count_result)) {
                        $scan_count = $row['count'];
                    }
                    echo "Today's Scans: " . $scan_count;
                ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="box form-box">
            <header>Verify Assets</header>

            <!-- Debug output -->
            <?php if (isset($_POST['rfid_data'])): ?>
            <div class="debug-info">
                <h4>Debug Information:</h4>
                <p>Input:
                    <?php echo htmlspecialchars($_POST['rfid_data']); ?>
                </p>
                <p>Input Length:
                    <?php echo strlen($_POST['rfid_data']); ?>
                </p>
                <p>Is UID:
                    <?php echo preg_match('/^VIRT_[a-f0-9]+$/i', $_POST['rfid_data']) ? 'Yes' : 'No'; ?>
                </p>
                <p>Is Secret:
                    <?php echo preg_match('/^[a-f0-9]{64}$/i', $_POST['rfid_data']) ? 'Yes' : 'No'; ?>
                </p>
            </div>
            <?php endif; ?>

            <?php echo $message; ?>

            <!-- RFID Verification Section -->
            <div class="rfid-verification">
                <h3>RFID Verification</h3>
                <form id="rfid-form" method="post">
                    <div class="field input">
                        <label for="rfid_data">Enter RFID UID or Secret:</label>
                        <input type="text" name="rfid_data" id="manual-rfid-input" placeholder="VIRT_... or 64-character secret" required>
                    </div>
                    <div class="field">
                        <input type="submit" value="Verify" class="btn">
                    </div>
                </form>

                <?php if (!empty($_SESSION['asset_details'])): ?>
                <div class="rfid-details">
                    <h4>RFID Tag Details</h4>
                    <p><strong>UID:</strong> <code><?php echo htmlspecialchars($_SESSION['asset_details']['rfid_uid']); ?></code></p>
                    <?php if (!empty($_SESSION['asset_details']['rfid_secret'])): ?>
                    <p><strong>Secret:</strong>
                        <code style="word-break: break-all;">
                                <?php echo substr($_SESSION['asset_details']['rfid_secret'], 0, 10) . '...' . substr($_SESSION['asset_details']['rfid_secret'], -10); ?>
                            </code>
                    </p>
                    <?php endif; ?>
                    <p><strong>Status:</strong>
                        <span class="rfid-status status-<?php echo htmlspecialchars($_SESSION['asset_details']['rfid_status']); ?>">
                            <?php echo strtoupper(htmlspecialchars($_SESSION['asset_details']['rfid_status'])); ?>
                        </span>
                    </p>
                    <p><strong>Asset Status:</strong>
                        <span class="rfid-status status-<?php echo strtolower(htmlspecialchars($_SESSION['asset_details']['AssetStatus'] ?? 'unknown')); ?>">
                            <?php echo strtoupper(htmlspecialchars($_SESSION['asset_details']['AssetStatus'] ?? 'UNKNOWN')); ?>
                        </span>
                    </p>
                    <p><strong>Last Scanned:</strong>
                        <?php echo !empty($_SESSION['asset_details']['last_scanned']) ? 
                            date('Y-m-d H:i', strtotime($_SESSION['asset_details']['last_scanned'])) : 
                            'Never'; ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Owner Verification Section -->
            <?php if (!empty($_SESSION['asset_details']) && !empty($_SESSION['student_details'])): ?>
            <div class="owner-verification">
                <h3>Ownership Verification</h3>
                <?php if ($owner_verified): ?>
                <div class="message success">
                    <p>✅ Verified Owner: This asset is registered to the scanned student.</p>
                </div>
                <?php else: ?>
                <div class="message error">
                    <p>❌ Ownership Mismatch: This asset is NOT registered to the scanned student!</p>
                    <p>Asset Owner:
                        <?php echo htmlspecialchars($_SESSION['asset_details']['Username'] . ' ' . $_SESSION['asset_details']['Lastname']); ?>
                    </p>
                    <p>Scanned Student:
                        <?php echo htmlspecialchars($_SESSION['student_details']['Username'] . ' ' . $_SESSION['student_details']['Lastname']); ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Main Content -->
            <div class="main-content">
                <!-- Asset Details Section -->
                <div class="left-section">
                    <h3>Asset Details</h3>
                    <?php if (!empty($_SESSION['asset_details'])): ?>
                    <div class="asset-section">
                        <?php if (!empty($_SESSION['asset_details']['picture'])): ?>
                        <div class="photo-container">
                            <img src="<?php echo htmlspecialchars($_SESSION['asset_details']['picture']); ?>" alt="Asset Picture">
                        </div>
                        <?php endif; ?>
                        <p><strong>Serial Number:</strong>
                            <?php echo htmlspecialchars($_SESSION['asset_details']['serial_number']); ?>
                        </p>
                        <p><strong>Model:</strong>
                            <?php echo htmlspecialchars($_SESSION['asset_details']['item_model']); ?>
                        </p>
                        <p><strong>Date Registered:</strong>
                            <?php echo htmlspecialchars($_SESSION['asset_details']['date_registered']); ?>
                        </p>
                        <p><strong>Owner:</strong>
                            <?php echo htmlspecialchars($_SESSION['asset_details']['Username'] . ' ' . $_SESSION['asset_details']['Lastname']); ?>
                        </p>
                    </div>
                    <?php else: ?>
                    <p>No asset data available. Enter RFID UID/Secret to populate this section.</p>
                    <?php endif; ?>
                </div>

                <!-- Student and Officer Details Section -->
                <div class="right-section">
                    <h3>Student Details</h3>
                    <?php if (!empty($_SESSION['student_details'])): ?>
                    <div class="student-section">
                        <?php if (!empty($_SESSION['student_details']['myphoto'])): ?>
                        <div class="photo-container">
                            <img src="<?php echo htmlspecialchars($_SESSION['student_details']['myphoto']); ?>" alt="Student Picture">
                        </div>
                        <?php endif; ?>
                        <p><strong>Name:</strong>
                            <?php echo htmlspecialchars($_SESSION['student_details']['Username'] . ' ' . $_SESSION['student_details']['Lastname']); ?>
                        </p>
                        <p><strong>School:</strong>
                            <?php echo htmlspecialchars($_SESSION['student_details']['School']); ?>
                        </p>
                        <p><strong>Registration Number:</strong>
                            <?php echo htmlspecialchars($_SESSION['student_details']['Reg_Number']); ?>
                        </p>
                        <p><strong>Number of Assets Owned:</strong>
                            <?php echo htmlspecialchars($_SESSION['assets_count']); ?>
                        </p>
                    </div>
                    <?php if (!empty($_SESSION['officer_details'])): ?>
                    <div class="officer-section">
                        <h3>Officer Details</h3>
                        <p><strong>Name:</strong>
                            <?php echo htmlspecialchars($_SESSION['officer_details']['name'] . ' ' . $_SESSION['officer_details']['lastname']); ?>
                        </p>
                        <p><strong>Officer ID:</strong>
                            <?php echo htmlspecialchars($_SESSION['officer_details']['officer_id']); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    <?php else: ?>
                    <p>No student data available. Scan a student QR code to populate this section.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- QR Code Scanner for Student -->
            <div class="scanner-section">
                <h3>Scan Student QR Code</h3>
                <div id="qr-reader-student" style="width: 100%;"></div>
                <div id="qr-reader-results-student"></div>
            </div>

            <!-- Hidden form to submit scanned student data -->
            <form id="scan-form-student" action="" method="post" style="display: none;">
                <input type="hidden" name="reg_number" id="reg_number">
                <input type="hidden" name="officer_id" id="officer_id">
            </form>
        </div>
    </div>

    <!-- Emergency Modal for Missing Assets -->
    <div id="missingAssetModal" class="modal emergency-modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close-modal">×</span>
                <h2>⚠️ ALERT: MISSING ASSET DETECTED ⚠️</h2>
            </div>
            <div class="modal-body">
                <div class="emergency-message">
                    <p>This asset has been reported as <strong>MISSING</strong>!</p>
                    <div id="missingAssetDetails">
                        <!-- Details will be filled by JavaScript -->
                    </div>
                    <div class="action-instructions">
                        <h3>Required Actions:</h3>
                        <ol>
                            <li>Detain the individual in possession of this asset</li>
                            <li>Contact security supervisor immediately</li>
                            <li>Document the incident</li>
                            <li>Do not release the asset without proper authorization</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="acknowledgeBtn" class="btn btn-danger">Acknowledge Alert</button>
            </div>
        </div>
    </div>

    <script>
        // Function to periodically check for RFID data from text file
        function startRFIDListener() {
            console.log("Starting RFID listener");
            
            // Keep track of the last processed UID to avoid duplicates
            let lastProcessedUID = '';
            let processingSubmit = false;
            
            async function pollRFIDData() {
                // Don't poll if we're currently processing a submission
                if (processingSubmit) {
                    return;
                }
                
                try {
                    const response = await fetch('?read_rfid=1&t=' + new Date().getTime(), {
                        cache: 'no-store' // Prevent caching
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    
                    if (data.status === 'success' && data.uid) {
                        const uid = data.uid.trim();
                        const rfidInput = document.getElementById('manual-rfid-input');
                        
                        // Only process if it's a new UID and different from what's already in the input field
                        if (uid && uid !== lastProcessedUID && uid !== rfidInput.value) {
                            console.log("New RFID data received:", uid);
                            
                            // Update the last processed UID
                            lastProcessedUID = uid;
                            
                            // Don't clear student information here - let the server handle it
                            // based on whether this is a different RFID from the last one
                            
                            // Populate the form field with the RFID data
                            if (rfidInput) {
                                rfidInput.value = uid;
                                
                                // Auto-submit the form for new scans
                                processingSubmit = true;
                                document.getElementById('rfid-form').dispatchEvent(new Event('submit'));
                            }
                        }
                    }
                } catch (e) {
                    console.error("RFID Error:", e);
                }
            }
            
            // Function to clear student information
            function clearStudentInformation() {
                // Clear student section
                const studentSection = document.querySelector('.right-section');
                if (studentSection) {
                    studentSection.innerHTML = '<h3>Student Details</h3><p>No student data available. Scan a student QR code to populate this section.</p>';
                }
                
                // Clear owner verification section if it exists
                const ownerVerification = document.querySelector('.owner-verification');
                if (ownerVerification) {
                    ownerVerification.remove();
                }
            }
            
            // Initial call
            pollRFIDData();
            
            // Poll every 2 seconds
            setInterval(pollRFIDData, 2000);
            
            // Enhanced RFID form handling
            document.getElementById('rfid-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                // Show loading indicator
                const submitBtn = this.querySelector('input[type="submit"]');
                const originalBtnText = submitBtn.value;
                submitBtn.value = "Processing...";
                submitBtn.disabled = true;

                fetch('', {
                        method: 'POST',
                        body: formData,
                        cache: 'no-store' // Prevent caching
                    })
                    .then(response => {
                        if (response.ok) {
                            // Instead of reloading, fetch the response and update the page content
                            return response.text();
                        } else {
                            throw new Error('Network response was not ok');
                        }
                    })
                    .then(html => {
                        // Parse the HTML response
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        
                        // Update only the necessary parts of the page
                        const newMessage = doc.querySelector('.message');
                        if (newMessage) {
                            const currentMessage = document.querySelector('.message');
                            if (currentMessage) {
                                currentMessage.replaceWith(newMessage);
                            } else {
                                document.querySelector('.rfid-verification').insertAdjacentElement('beforebegin', newMessage);
                            }
                        }
                        
                        // Update RFID details
                        const newRfidDetails = doc.querySelector('.rfid-details');
                        if (newRfidDetails) {
                            const currentRfidDetails = document.querySelector('.rfid-details');
                            if (currentRfidDetails) {
                                currentRfidDetails.replaceWith(newRfidDetails);
                            } else {
                                document.querySelector('.rfid-verification').appendChild(newRfidDetails);
                            }
                        }
                        
                        // Update asset details
                        const newAssetSection = doc.querySelector('.asset-section');
                        if (newAssetSection) {
                            const currentAssetSection = document.querySelector('.asset-section');
                            if (currentAssetSection) {
                                currentAssetSection.replaceWith(newAssetSection);
                            } else {
                                const leftSection = document.querySelector('.left-section');
                                if (leftSection) {
                                    leftSection.innerHTML = '<h3>Asset Details</h3>';
                                    leftSection.appendChild(newAssetSection);
                                }
                            }
                        }
                        
                        // Check if we need to show the missing asset modal
                        // Look for a status indicator with "MISSING" text
                        const assetStatusElements = document.querySelectorAll('.rfid-status');
                        let isMissing = false;
                        
                        assetStatusElements.forEach(element => {
                            if (element.textContent.trim() === 'MISSING') {
                                isMissing = true;
                                
                                // Populate missing asset details
                                const missingAssetDetails = document.getElementById('missingAssetDetails');
                                if (missingAssetDetails) {
                                    // Get asset details from the page
                                    const serialNumber = document.querySelector('.asset-section p:nth-child(2)').textContent;
                                    const model = document.querySelector('.asset-section p:nth-child(3)').textContent;
                                    const owner = document.querySelector('.asset-section p:nth-child(5)').textContent;
                                    
                                    // Create HTML for missing asset details
                                    missingAssetDetails.innerHTML = `
                                        <div class="missing-detail">${serialNumber}</div>
                                        <div class="missing-detail">${model}</div>
                                        <div class="missing-detail">${owner}</div>
                                        <div class="missing-detail"><strong>Date Reported Missing:</strong> 
                                            ${new Date().toLocaleDateString()}</div>
                                    `;
                                }
                                
                                // Show the modal
                                document.getElementById('missingAssetModal').style.display = 'block';
                            }
                        });
                        
                        console.log("Page content updated without reload");
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error processing RFID: ' + error.message);
                    })
                    .finally(() => {
                        // Reset button state
                        submitBtn.value = originalBtnText;
                        submitBtn.disabled = false;
                        processingSubmit = false;
                    });
            });
        }

        // Initialize the Student QR code scanner
        function onScanSuccessStudent(decodedText, decodedResult) {
            console.log(`Student QR Code scanned = ${decodedText}`, decodedResult);

            try {
                const regNumberMatch = decodedText.match(/Reg Number: ([^,]+)/);
                const officerIdMatch = decodedText.match(/Officer ID: ([^,]+)/);

                if (regNumberMatch && regNumberMatch[1]) {
                    document.getElementById('reg_number').value = regNumberMatch[1].trim();
                    
                    // If officer ID is in the QR code, use it
                    if (officerIdMatch && officerIdMatch[1]) {
                        document.getElementById('officer_id').value = officerIdMatch[1].trim();
                    } else {
                        // Otherwise use the current logged in officer's ID
                        const officerId = "<?php echo isset($_SESSION['officer_id']) ? $_SESSION['officer_id'] : ''; ?>";
                        if (officerId) {
                            document.getElementById('officer_id').value = officerId;
                        }
                    }
                    
                    document.getElementById('scan-form-student').submit();
                } else {
                    alert("Invalid Student QR code format. Please scan a valid student QR code.");
                }
            } catch (error) {
                console.error("Error parsing QR code:", error);
                alert("Error scanning QR code. Please try again.");
            }
        }

        const html5QrcodeScannerStudent = new Html5QrcodeScanner(
            "qr-reader-student", {
                fps: 10,
                qrbox: 250
            });
        html5QrcodeScannerStudent.render(onScanSuccessStudent);
        
        // Modal handling
        document.addEventListener('DOMContentLoaded', function() {
            // Close modal when clicking the X
            const closeButtons = document.querySelectorAll('.close-modal');
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('missingAssetModal').style.display = 'none';
                });
            });
            
            // Close modal when clicking the acknowledge button
            const acknowledgeBtn = document.getElementById('acknowledgeBtn');
            if (acknowledgeBtn) {
                acknowledgeBtn.addEventListener('click', function() {
                    document.getElementById('missingAssetModal').style.display = 'none';
                });
            }
            
            // Close modal when clicking outside of it
            window.addEventListener('click', function(event) {
                const modal = document.getElementById('missingAssetModal');
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
            
            // Check if we need to show the missing asset modal on page load
            <?php if ($show_missing_modal): ?>
            // Populate missing asset details
            const missingAssetDetails = document.getElementById('missingAssetDetails');
            if (missingAssetDetails) {
                missingAssetDetails.innerHTML = `
                    <div class="missing-detail"><strong>Serial Number:</strong> 
                        <?php echo htmlspecialchars($_SESSION['asset_details']['serial_number']); ?></div>
                    <div class="missing-detail"><strong>Model:</strong> 
                        <?php echo htmlspecialchars($_SESSION['asset_details']['item_model']); ?></div>
                    <div class="missing-detail"><strong>Owner:</strong> 
                        <?php echo htmlspecialchars($_SESSION['asset_details']['Username'] . ' ' . $_SESSION['asset_details']['Lastname']); ?></div>
                    <div class="missing-detail"><strong>Date Reported Missing:</strong> 
                        <?php echo !empty($_SESSION['asset_details']['date_reported_missing']) ? 
                            date('Y-m-d', strtotime($_SESSION['asset_details']['date_reported_missing'])) : 
                            'Unknown'; ?></div>
                `;
            }
            
            // Show the modal
            document.getElementById('missingAssetModal').style.display = 'block';
            <?php endif; ?>
        });
        
        // Start RFID listener
        document.addEventListener('DOMContentLoaded', startRFIDListener);
    </script>
</body>

</html>
