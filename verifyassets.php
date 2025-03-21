<?php
// Start the session
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("php/config.php");

$message = ""; // Variable to store success/error messages

// Initialize session variables if they don't exist
if (!isset($_SESSION['asset_details'])) {
    $_SESSION['asset_details'] = [];
}
if (!isset($_SESSION['student_details'])) {
    $_SESSION['student_details'] = [];
}
if (!isset($_SESSION['officer_details'])) {
    $_SESSION['officer_details'] = [];
}
if (!isset($_SESSION['assets_count'])) {
    $_SESSION['assets_count'] = 0;
}

// Fetch asset details based on the serial number
if (isset($_POST['serial_number'])) {
    $serial_number = $_POST['serial_number'];
    $asset_query = "SELECT assets.*, users.Username, users.Lastname, users.School 
                    FROM assets 
                    JOIN users ON assets.reg_number = users.Reg_Number 
                    WHERE assets.serial_number = '$serial_number' LIMIT 1";
    
    $asset_result = mysqli_query($con, $asset_query);

    if ($asset_result === false) {
        die("Asset Query Failed: " . mysqli_error($con));
    }

    if (mysqli_num_rows($asset_result) > 0) {
        $_SESSION['asset_details'] = mysqli_fetch_assoc($asset_result);
    } else {
        $message = "<div class='message error'><p>Asset not found.</p></div>";
    }
}

// Fetch student details based on the registration number
if (isset($_POST['reg_number'])) {
    $reg_number = $_POST['reg_number'];
    $student_query = "SELECT * FROM users WHERE Reg_Number = '$reg_number' LIMIT 1";
    
    $student_result = mysqli_query($con, $student_query);

    if ($student_result === false) {
        die("Student Query Failed: " . mysqli_error($con));
    }

    if (mysqli_num_rows($student_result) > 0) {
        $_SESSION['student_details'] = mysqli_fetch_assoc($student_result);

        // Fetch all assets owned by the student
        $assets_query = "SELECT * FROM assets WHERE reg_number = '$reg_number'";
        
        $assets_result = mysqli_query($con, $assets_query);

        if ($assets_result === false) {
            die("Assets Query Failed: " . mysqli_error($con));
        }

        $_SESSION['assets_count'] = mysqli_num_rows($assets_result);

        // Fetch officer details using the officer_id from the student QR code
        if (isset($_POST['officer_id'])) {
            $officer_id = $_POST['officer_id'];
            $officer_query = "SELECT * FROM scpersonnel WHERE officer_id = '$officer_id' LIMIT 1";
            
            $officer_result = mysqli_query($con, $officer_query);

            if ($officer_result === false) {
                die("Officer Query Failed: " . mysqli_error($con));
            }

            if (mysqli_num_rows($officer_result) > 0) {
                $_SESSION['officer_details'] = mysqli_fetch_assoc($officer_result);
            } else {
                $message = "<div class='message error'><p>Officer not found.</p></div>";
            }
        }
    } else {
        $message = "<div class='message error'><p>Student not found.</p></div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="">
    <title>Verify Assets</title>
    <!-- Include the html5-qrcode library -->
    <script src="https://unpkg.com/html5-qrcode@2.0.9/dist/html5-qrcode.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <header>Verify Assets</header>
            <?php echo $message; // Display success/error message here ?>

            <!-- Main Content -->
            <div class="main-content">
                <!-- Asset Details Section -->
                <div class="left-section">
                    <h3>Asset Details</h3>
                    <?php if (!empty($_SESSION['asset_details'])): ?>
                        <div class="asset-section">
                            <div class="photo-container">
                                <img src="<?php echo $_SESSION['asset_details']['picture']; ?>" alt="Asset Picture">
                            </div>
                            <p><strong>Serial Number:</strong> <?php echo $_SESSION['asset_details']['serial_number']; ?></p>
                            <p><strong>Model:</strong> <?php echo $_SESSION['asset_details']['item_model']; ?></p>
                            <p><strong>Date Registered:</strong> <?php echo $_SESSION['asset_details']['date_registered']; ?></p>
                            <p><strong>Owner:</strong> <?php echo $_SESSION['asset_details']['Username'] . ' ' . $_SESSION['asset_details']['Lastname']; ?></p>
                        </div>
                    <?php else: ?>
                        <p>No asset data available. Scan an asset QR code to populate this section.</p>
                    <?php endif; ?>
                </div>

                <!-- Student and Officer Details Section -->
                <div class="right-section">
                    <h3>Student Details</h3>
                    <?php if (!empty($_SESSION['student_details'])): ?>
                        <div class="student-section">
                            <div class="photo-container">
                                <?php if (!empty($_SESSION['student_details']['myphoto'])): ?>
                                    <img src="<?php echo $_SESSION['student_details']['myphoto']; ?>" alt="Student Picture">
                                <?php else: ?>
                                    <p>No picture available.</p>
                                <?php endif; ?>
                            </div>
                            <p><strong>Name:</strong> <?php echo $_SESSION['student_details']['Username'] . ' ' . $_SESSION['student_details']['Lastname']; ?></p>
                            <p><strong>School:</strong> <?php echo $_SESSION['student_details']['School']; ?></p>
                            <p><strong>Registration Number:</strong> <?php echo $_SESSION['student_details']['Reg_Number']; ?></p>
                            <p><strong>Number of Assets Owned:</strong> <?php echo $_SESSION['assets_count']; ?></p>
                        </div>

                        <?php if (!empty($_SESSION['officer_details'])): ?>
                            <div class="officer-section">
                                <h3>Officer Details</h3>
                                <p><strong>Name:</strong> <?php echo $_SESSION['officer_details']['name'] . ' ' . $_SESSION['officer_details']['lastname']; ?></p>
                                <p><strong>Officer ID:</strong> <?php echo $_SESSION['officer_details']['officer_id']; ?></p>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>No student data available. Scan a student QR code to populate this section.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- QR Code Scanners -->
            <div class="scanner-section">
                <h3>Scan Asset QR Code</h3>
                <div id="qr-reader-asset" style="width: 100%;"></div>
                <div id="qr-reader-results-asset"></div>
            </div>

            <div class="scanner-section">
                <h3>Scan Student QR Code</h3>
                <div id="qr-reader-student" style="width: 100%;"></div>
                <div id="qr-reader-results-student"></div>
            </div>

            <!-- Hidden forms to submit scanned data -->
            <form id="scan-form-asset" action="" method="post" style="display: none;">
                <input type="hidden" name="serial_number" id="serial_number">
            </form>

            <form id="scan-form-student" action="" method="post" style="display: none;">
                <input type="hidden" name="reg_number" id="reg_number">
                <input type="hidden" name="officer_id" id="officer_id">
            </form>
        </div>
    </div>

    <script>
        // Initialize the Asset QR code scanner
        function onScanSuccessAsset(decodedText, decodedResult) {
            console.log(`Asset QR Code scanned = ${decodedText}`, decodedResult);

            try {
                // Extract serial number from the decoded text
                const serialNumberMatch = decodedText.match(/Serial: ([^,]+)/);
                
                if (serialNumberMatch && serialNumberMatch[1]) {
                    const serialNumber = serialNumberMatch[1].trim();
                    console.log("Extracted Serial Number:", serialNumber);

                    // Set the serial number in the hidden form field
                    document.getElementById('serial_number').value = serialNumber;

                    // Submit the form to fetch asset details
                    document.getElementById('scan-form-asset').submit();
                } else {
                    alert("Invalid Asset QR code format. Please scan a valid asset QR code.");
                    console.error("Invalid Asset QR code format. Decoded text:", decodedText);
                }
            } catch (error) {
                console.error("Error parsing QR code:", error);
                alert("Error scanning QR code. Please try again.");
            }
        }

        const html5QrcodeScannerAsset = new Html5QrcodeScanner(
            "qr-reader-asset", { fps: 10, qrbox: 250 });
        html5QrcodeScannerAsset.render(onScanSuccessAsset);

        // Initialize the Student QR code scanner
        function onScanSuccessStudent(decodedText, decodedResult) {
            console.log(`Student QR Code scanned = ${decodedText}`, decodedResult);

            try {
                // Extract registration number and officer ID from the decoded text
                const regNumberMatch = decodedText.match(/Reg Number: ([^,]+)/);
                const officerIdMatch = decodedText.match(/Officer ID: ([^,]+)/);
                
                if (regNumberMatch && regNumberMatch[1] && officerIdMatch && officerIdMatch[1]) {
                    const regNumber = regNumberMatch[1].trim();
                    const officerId = officerIdMatch[1].trim();
                    console.log("Extracted Registration Number:", regNumber);
                    console.log("Extracted Officer ID:", officerId);

                    // Set the registration number and officer ID in the hidden form fields
                    document.getElementById('reg_number').value = regNumber;
                    document.getElementById('officer_id').value = officerId;

                    // Submit the form to fetch student and officer details
                    document.getElementById('scan-form-student').submit();
                } else {
                    alert("Invalid Student QR code format. Please scan a valid student QR code.");
                    console.error("Invalid Student QR code format. Decoded text:", decodedText);
                }
            } catch (error) {
                console.error("Error parsing QR code:", error);
                alert("Error scanning QR code. Please try again.");
            }
        }

        const html5QrcodeScannerStudent = new Html5QrcodeScanner(
            "qr-reader-student", { fps: 10, qrbox: 250 });
        html5QrcodeScannerStudent.render(onScanSuccessStudent);
    </script>
</body>
</html>