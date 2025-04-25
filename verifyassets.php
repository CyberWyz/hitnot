<?php
session_start();
include("php/config.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = "";
$owner_verified = false;

// Initialize session variables
if (!isset($_SESSION['asset_details'])) $_SESSION['asset_details'] = [];
if (!isset($_SESSION['student_details'])) $_SESSION['student_details'] = [];
if (!isset($_SESSION['officer_details'])) $_SESSION['officer_details'] = [];
if (!isset($_SESSION['assets_count'])) $_SESSION['assets_count'] = 0;

// Function to verify asset owner
function verifyOwner() {
    global $message, $owner_verified;
    
    if (!empty($_SESSION['asset_details']) && !empty($_SESSION['student_details'])) {
        if ($_SESSION['asset_details']['reg_number'] === $_SESSION['student_details']['Reg_Number']) {
            $message .= "<div class='message success'><p>OWNER VERIFIED: This asset belongs to the scanned student.</p></div>";
            $owner_verified = true;
        } else {
            $message .= "<div class='message error'><p>OWNER MISMATCH: This asset does NOT belong to the scanned student. Please investigate!</p></div>";
            $owner_verified = false;
        }
    }
}

// Add this near the beginning of your file, after session_start() and include statements
$data_file = "rfid_data.txt"; // File where Arduino/Python writes the RFID data

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
    
    // Debug: Log the input
    error_log("RFID Input Received: " . $input);
    
    // Format the RFID data if needed
    if (preg_match('/^([0-9A-F]{2},)*[0-9A-F]{2}$/i', $input)) {
        $input = 'VIRT_' . str_replace(',', '', strtoupper($input));
        error_log("Formatted RFID: " . $input);
    }
    
    // Determine if input is UID (VIRT_) or Secret (64 hex chars)
    if (preg_match('/^VIRT_[a-f0-9]+$/i', $input)) {
        // Handle as UID
        $query = "SELECT assets.id, assets.*, users.Username, users.Lastname, users.School 
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

    // Add this right before the if (empty($message)) check
    error_log("Checking database for RFID UID: " . $input);
    $check_query = "SELECT COUNT(*) as count FROM assets WHERE rfid_uid = '" . mysqli_real_escape_string($con, $input) . "'";
    $check_result = mysqli_query($con, $check_query);
    if ($check_result) {
        $row = mysqli_fetch_assoc($check_result);
        error_log("Database check: Found " . $row['count'] . " matching records for " . $input);
    } else {
        error_log("Database check query failed: " . mysqli_error($con));
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
                    
                    // Update last scanned
                    if (!empty($_SESSION['asset_details']) && isset($_SESSION['asset_details']['id'])) {
                        $update = mysqli_query($con, "UPDATE assets SET last_scanned = NOW() WHERE id = " . $_SESSION['asset_details']['id']);
                        if (!$update) {
                            error_log("Update failed: " . mysqli_error($con));
                        }
                    } else {
                        // If id doesn't exist, try to update using rfid_uid instead
                        if (!empty($_SESSION['asset_details']) && isset($_SESSION['asset_details']['rfid_uid'])) {
                            $rfid_uid = mysqli_real_escape_string($con, $_SESSION['asset_details']['rfid_uid']);
                            $update = mysqli_query($con, "UPDATE assets SET last_scanned = NOW() WHERE rfid_uid = '$rfid_uid'");
                            if (!$update) {
                                error_log("Update failed: " . mysqli_error($con));
                            }
                        } else {
                            error_log("Cannot update last_scanned: No id or rfid_uid available");
                        }
                    }
                    
                    // Check tamper status
                    if ($_SESSION['asset_details']['rfid_status'] == 'tampered') {
                        $message = "<div class='message error'><p>TAMPERED TAG DETECTED!</p></div>";
                    } else {
                        $message = "<div class='message success'><p>RFID verification successful! (Using $type)</p></div>";
                    }
                    
                    // Log secret usage if applicable
                    if ($type == "Secret") {
                        $officer_id = $_SESSION['officer_details']['officer_id'] ?? 'unknown';
                        $log = mysqli_query($con, "INSERT INTO secret_logs (officer_id, asset_id, used_at) 
                                               VALUES ('$officer_id', " . $_SESSION['asset_details']['id'] . ", NOW())");
                        if (!$log) {
                            error_log("Secret log failed: " . mysqli_error($con));
                        }
                    }
                    
                    // Check if student details are already available for owner verification
                    if (!empty($_SESSION['student_details'])) {
                        verifyOwner();
                    }
                } else {
                    if ($type == "Secret") {
                        $message = "<div class='message error'><p>Invalid RFID secret - not found in system</p></div>";
                    } else {
                        $message = "<div class='message error'><p>Invalid RFID UID - not found in system</p></div>";
                    }
                    error_log("No asset found for $type: " . $input);
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

// Add this at the beginning of your file, after session_start()
$data_file = "rfid_data.txt"; // File where Python/Arduino writes the RFID data
error_log("RFID data file path: " . realpath($data_file)); // Log the absolute path

// AJAX endpoint to get the RFID data from the text file
if (isset($_GET['read_rfid'])) {
    header('Content-Type: application/json');
    
    try {
        error_log("RFID read request received");
        
        if (file_exists($data_file)) {
            error_log("RFID data file exists");
            
            if (is_readable($data_file)) {
                error_log("RFID data file is readable");
                
                $rfid_data = file_get_contents($data_file);
                error_log("Raw RFID data: " . $rfid_data);
                
                $uid = trim($rfid_data);
                
                // Remove any trailing commas
                $uid = rtrim($uid, ',');
                error_log("RFID data after trim: " . $uid);
                
                // Check if it's in the format XX,XX,XX,XX
                if (preg_match('/^([0-9A-F]{2},)*[0-9A-F]{2}$/i', $uid)) {
                    error_log("RFID data matches expected format");
                    
                    // Remove commas to get a clean hex string
                    $formatted_uid = 'VIRT_' . str_replace(',', '', $uid);
                    error_log("Formatted RFID: " . $formatted_uid);
                    
                    echo json_encode(['uid' => $formatted_uid, 'status' => 'success']);
                } else {
                    error_log("RFID data does not match expected format");
                    
                    // If it's already formatted or in another format, just pass it through
                    echo json_encode(['uid' => $uid, 'status' => 'success']);
                }
            } else {
                error_log("RFID data file is not readable");
                echo json_encode(['uid' => 'Error: Cannot read RFID data file', 'status' => 'error']);
            }
        } else {
            error_log("RFID data file does not exist at: " . realpath(dirname(__FILE__)) . "/" . $data_file);
            echo json_encode(['uid' => 'Error: RFID data file not found', 'status' => 'error']);
        }
    } catch (Exception $e) {
        error_log("Exception in RFID read: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
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
    </style>
</head>

<body>
    <div class="container">
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
                        <input type="text" name="rfid_data" id="manual-rfid-input" placeholder="Enter RFID UID" required>
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

    <script>
        // Function to periodically check for RFID data from text file
        function startRFIDListener() {
            console.log("Starting RFID listener");
            
            async function pollRFIDData() {
                try {
                    const response = await fetch('?read_rfid=1');
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    console.log("RFID response:", data);
                    
                    if (data.status === 'success' && data.uid) {
                        const uid = data.uid.trim();
                        console.log("Received RFID UID:", uid);
                        
                        // Populate the form field with the RFID data
                        const rfidInput = document.getElementById('manual-rfid-input');
                        if (rfidInput) {
                            rfidInput.value = uid;
                            console.log("RFID input field populated with:", uid);
                        }
                    }
                } catch (e) {
                    console.error("RFID Error:", e);
                }
            }
            
            // Initial call
            pollRFIDData();
            
            // Poll every 2 seconds
            setInterval(pollRFIDData, 2000);
        }

        // Start RFID listener when the document is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log("Document ready, starting RFID listener");
            startRFIDListener();
        });

        // Function to submit the RFID form via AJAX (for manual submissions)
        async function submitRFIDFormViaAjax(uid) {
            try {
                // Create form data
                const formData = new FormData();
                formData.append('rfid_data', uid);
                
                // Show loading indicator
                const messageContainer = document.createElement('div');
                messageContainer.className = 'message';
                messageContainer.innerHTML = '<p>Processing RFID data...</p>';
                document.querySelector('.rfid-verification').appendChild(messageContainer);
                
                // Send AJAX request
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Reload the page to show the results
                window.location.reload();
                
            } catch (error) {
                console.error("Error submitting RFID data:", error);
                
                // Show error message
                const errorMsg = document.createElement('div');
                errorMsg.className = 'message error';
                errorMsg.innerHTML = `<p>Error processing RFID: ${error.message}</p>`;
                document.querySelector('.rfid-verification').appendChild(errorMsg);
            }
        }

        // Initialize the Student QR code scanner
        function onScanSuccessStudent(decodedText, decodedResult) {
            console.log(`Student QR Code scanned = ${decodedText}`, decodedResult);

            try {
                const regNumberMatch = decodedText.match(/Reg Number: ([^,]+)/);
                const officerIdMatch = decodedText.match(/Officer ID: ([^,]+)/);

                if (regNumberMatch && regNumberMatch[1] && officerIdMatch && officerIdMatch[1]) {
                    document.getElementById('reg_number').value = regNumberMatch[1].trim();
                    document.getElementById('officer_id').value = officerIdMatch[1].trim();
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
        
        // Start RFID listener
        document.addEventListener('DOMContentLoaded', startRFIDListener);
    </script>
</body>

</html>