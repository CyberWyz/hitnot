<?php
session_start();
include("php/config.php");

// RFID reading functionality
$data_file = "rfid_data.txt"; // File where Python/Arduino writes the RFID data
$rfid_uid = "";
$rfid_read_success = false;
$rfid_message = "";

// Function to read RFID data from the text file
function readRFIDFromFile() {
    global $data_file, $rfid_uid, $rfid_read_success, $rfid_message;
    
    if (file_exists($data_file) && is_readable($data_file)) {
        $rfid_data = file_get_contents($data_file);
        $uid = trim($rfid_data);
        
        // Remove any trailing commas
        $uid = rtrim($uid, ',');
        
        // Check if it's in the format XX,XX,XX,XX
        if (preg_match('/^([0-9A-F]{2},)*[0-9A-F]{2}$/i', $uid)) {
            // Remove commas to get a clean hex string
            $formatted_uid = 'VIRT_' . str_replace(',', '', $uid);
            $rfid_uid = $formatted_uid;
            $rfid_read_success = true;
            $rfid_message = "RFID tag read successfully: " . $rfid_uid;
            
            // Log the successful read
            error_log("RFID Read Success: Original=" . $uid . ", Formatted=" . $rfid_uid);
        } else {
            $rfid_message = "Invalid RFID format: " . $uid;
            error_log("Invalid RFID format: " . $uid);
        }
    } else {
        $rfid_message = "Error: Cannot read RFID data file";
        error_log("Cannot read RFID data file: " . $data_file);
    }
}

// AJAX endpoint to get the RFID data from the text file
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['read_rfid'])) {
    header('Content-Type: application/json');
    
    readRFIDFromFile();
    
    echo json_encode([
        'uid' => $rfid_uid,
        'status' => $rfid_read_success ? 'success' : 'error',
        'message' => $rfid_message
    ]);
    exit;
}

if (!isset($_SESSION['valid'])) {
    header("Location: login_scpersonnel.php");
    exit;
}

$id = $_SESSION['id'];
$query = mysqli_query($con, "SELECT * FROM scpersonnel WHERE id=$id");

if ($result = mysqli_fetch_assoc($query)) {
    $officer_id = $result['officer_id'];
    $officer_lastname = $result['lastname'];
} else {
    die("Officer details not found.");
}

$message = "";

if (isset($_POST['submit'])) {
    $item_description = $_POST['item_description'];
    $item_model = $_POST['item_model'];
    $reg_number = $_POST['reg_number'];
    $serial_number = $_POST['serial_number'];
    $date_registered = $_POST['date_registered'];
    $picture = $_FILES['picture']['name'];
    $picture_tmp = $_FILES['picture']['tmp_name'];

    $picture_path = "uploads/" . basename($picture);
    move_uploaded_file($picture_tmp, $picture_path);

    // Generate QR code data
    $qr_data_student = "Reg Number: $reg_number, Date Registered: $date_registered, Officer: $officer_lastname, Officer ID: $officer_id";
    $qr_data_asset = "Serial: $serial_number, Model: $item_model, Reg: $reg_number";

    // Use the RFID UID from the form (which was populated by the RFID reader)
    $rfid_uid = $_POST['rfid_uid'];
    
    // If no RFID UID was provided, generate a virtual one
    if (empty($rfid_uid)) {
        $rfid_uid = 'VIRT_' . bin2hex(random_bytes(6)); // 12-character UID
    }
    
    // Generate RFID secret
    $rfid_secret = hash('sha256', $serial_number . microtime() . $officer_id);
    $rfid_status = 'active';

    // Check for existing registration
    $check_query = "SELECT * FROM assets WHERE reg_number = '$reg_number'";
    $check_result = mysqli_query($con, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $qr_data_student = "";
    }

    $query = "INSERT INTO assets 
              (item_description, item_model, reg_number, serial_number, 
               date_registered, picture, qr_code, asset_qr_code,
               rfid_uid, rfid_secret, rfid_status) 
              VALUES 
              ('$item_description', '$item_model', '$reg_number', '$serial_number', 
               '$date_registered', '$picture_path', '$qr_data_student', '$qr_data_asset',
               '$rfid_uid', '$rfid_secret', '$rfid_status')";

    if (mysqli_query($con, $query)) {
        $asset_id = mysqli_insert_id($con);
        
        $message = <<<HTML
        <div class='message success'>
            <p>Asset registered successfully!</p>
            <div class='rfid-details'>
                <h4>RFID Security Data</h4>
                <p><strong>UID:</strong> <span class='copyable' onclick='copyToClipboard(this)'>$rfid_uid</span></p>
                <p><strong>Status:</strong> $rfid_status</p>
                <small>Click on UID to copy</small>
            </div>
            <div class='qr-details'>
                <h4>QR Code Generated</h4>
                <p>A QR code has been generated for the student/staff member.</p>
                <p>You can view and download this QR code below the form.</p>
            </div>
        </div>
HTML;
    } else {
        $message = "<div class='message error'><p>Error occurred: " . mysqli_error($con) . "</p></div>";
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

<link rel="stylesheet" href="style/missingassets.css">
    <link rel="stylesheet" href="responsive.css">
    <link rel="stylesheet" href="style/regAsset.css">
    <title>Register Asset</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

</head>
<body>
    <!-- Sidebar navigation -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <span>Smart Tag Asset Management System</span>
        </div>
        <ul class="sidebar-menu">
            <li><a href="schome.php">Dashboard</a></li>
            <li><a href="missingassets.php">Missing Assets</a></li>
            <li><a href="regAsset.php" class="active">Register New Asset</a></li>
            <li><a href="blacklistedassets.php">Blacklisted Assets</a></li>
            <li><a href="verifyassets.php">Verify Asset</a></li>
            <li><a href="php/logout.php">Logout</a></li>
        </ul>
    </div>
    
    <!-- Main content -->
    <div class="content-wrapper">
        <div class="container">
            <div class="box form-box">
                <header>Register New Asset</header>
                
                <?php if(isset($message)) echo $message; ?>
                
                <!-- RFID Reader Section -->
                <div class="rfid-reader">
                    <h3>RFID Tag Reader</h3>
                    <p>Place an RFID tag near the reader to capture its UID.</p>
                    <div id="rfid-status" class="status-waiting">Waiting for RFID tag...</div>
                    <button id="refresh-rfid" class="btn">Refresh RFID</button>
                </div>
                
                <!-- Asset Registration Form -->
                <form action="" method="post" enctype="multipart/form-data">
                    <!-- Hidden RFID UID field that will be populated by the reader -->
                    <input type="hidden" name="rfid_uid" id="rfid-uid-input">
                    
                    <div class="field input">
                        <label for="item_description">Item Description</label>
                        <input type="text" name="item_description" id="item_description" required>
                    </div>

                    <div class="field input">
                        <label for="item_model">Item Model</label>
                        <input type="text" name="item_model" id="item_model" required>
                    </div>

                    <div class="field input">
                        <label for="reg_number">Registration Number</label>
                        <input type="text" name="reg_number" id="reg_number" required>
                    </div>

                    <div class="field input">
                        <label for="serial_number">Serial Number</label>
                        <input type="text" name="serial_number" id="serial_number" required>
                    </div>

                    <div class="field input">
                        <label for="date_registered">Date Registered</label>
                        <input type="date" name="date_registered" id="date_registered" required>
                    </div>

                    <div class="field input">
                        <label for="picture">Asset Picture</label>
                        <input type="file" name="picture" id="picture" accept="image/*">
                    </div>

                    <div class="field">
                        <div class="qr-code-container">
                            <h3>Student/Staff QR Code</h3>
                            <div id="qrcode-student"></div>
                            <button type="button" id="download-student-qr" class="btn" style="display:none;">Download QR Code</button>
                        </div>
                    </div>

                    <div class="field">
                        <input type="submit" name="submit" class="btn" value="Register Asset" id="submit-btn" disabled>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Function to read RFID data from the server
        async function readRFID() {
            try {
                const response = await fetch("?read_rfid=1");
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log("RFID response:", data);
                
                const statusElement = document.getElementById("rfid-status");
                const rfidInput = document.getElementById("rfid-uid-input");
                const submitBtn = document.getElementById("submit-btn");
                
                if (data.status === 'success') {
                    statusElement.textContent = "RFID Tag Detected: " + data.uid;
                    statusElement.className = "status-success";
                    rfidInput.value = data.uid;
                    submitBtn.disabled = false;
                } else {
                    statusElement.textContent = data.message || "Error reading RFID tag";
                    statusElement.className = "status-error";
                    rfidInput.value = "";
                    submitBtn.disabled = true;
                }
            } catch (e) {
                console.error("RFID Error:", e);
                document.getElementById("rfid-status").textContent = "Connection error: " + e.message;
                document.getElementById("rfid-status").className = "status-error";
                document.getElementById("rfid-uid-input").value = "";
                document.getElementById("submit-btn").disabled = true;
            }
        }
        
        // Initial call
        readRFID();
        
        // Poll every second
        const rfidInterval = setInterval(readRFID, 1000);
        
        // Refresh button handler
        document.getElementById("refresh-rfid").addEventListener("click", function() {
            document.getElementById("rfid-status").textContent = "Refreshing...";
            document.getElementById("rfid-status").className = "status-waiting";
            readRFID();
        });
        
        // Stop polling when the page is not visible
        document.addEventListener("visibilitychange", function() {
            if (document.hidden) {
                clearInterval(rfidInterval);
            } else {
                readRFID();
                setInterval(readRFID, 1000);
            }
        });
        
        // Function to generate and display student QR code
        function generateStudentQRCode(regNumber, dateRegistered, officerLastname, officerId) {
            // Check if student already exists
            fetch(`check_student.php?reg_number=${encodeURIComponent(regNumber)}`)
                .then(response => response.json())
                .then(data => {
                    const qrCodeStudentElement = document.getElementById('qrcode-student');
                    qrCodeStudentElement.innerHTML = '';
            
                    if (data.exists) {
                        qrCodeStudentElement.innerHTML = "<p>Student QR code not generated (registration number already exists).</p>";
                        document.getElementById('download-student-qr').style.display = 'none';
                    } else {
                        const qrDataStudent = `Reg Number: ${regNumber}, Date Registered: ${dateRegistered}, Officer: ${officerLastname}, Officer ID: ${officerId}`;
                        new QRCode(qrCodeStudentElement, {
                            text: qrDataStudent,
                            width: 128,
                            height: 128
                        });
                        document.getElementById('download-student-qr').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error("Error checking registration number:", error);
                    alert("Error checking registration number. Please try again.");
                });
        }

        // Function to download the generated QR code
        function downloadStudentQRCode() {
            const qrElement = document.getElementById('qrcode-student');
            html2canvas(qrElement).then(canvas => {
                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/png');
                link.download = 'student_qr.png';
                link.click();
            });
        }

        // Add event listeners to form fields to update QR code
        document.getElementById('reg_number').addEventListener('input', function() {
            const regNumber = this.value;
            const dateRegistered = document.getElementById('date_registered').value;
            
            if (regNumber && dateRegistered) {
                generateStudentQRCode(regNumber, dateRegistered, "<?php echo $officer_lastname; ?>", "<?php echo $officer_id; ?>");
            }
        });

        document.getElementById('date_registered').addEventListener('change', function() {
            const dateRegistered = this.value;
            const regNumber = document.getElementById('reg_number').value;
            
            if (regNumber && dateRegistered) {
                generateStudentQRCode(regNumber, dateRegistered, "<?php echo $officer_lastname; ?>", "<?php echo $officer_id; ?>");
            }
        });

        // Add event listener to download button
        document.getElementById('download-student-qr').addEventListener('click', downloadStudentQRCode);

        // Function to copy text to clipboard
        function copyToClipboard(element) {
            const text = element.textContent;
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            
            // Show a temporary tooltip or notification
            const originalText = element.innerHTML;
            element.innerHTML = "Copied!";
            setTimeout(() => {
                element.innerHTML = originalText;
            }, 1000);
        }
    </script>
    <script src="js/missingassets.js"></script>
</body>
</html>