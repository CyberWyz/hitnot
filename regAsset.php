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

while ($result = mysqli_fetch_assoc($query)) {
    $res_name = $result['name'];
    $officer_id = $result['officer_id'];
    $officer_lastname = $result['lastname'];
}

$message = "";

if (isset($_POST['submit'])) {
    $item_description = mysqli_real_escape_string($con, $_POST['item_description']);
    $item_model = mysqli_real_escape_string($con, $_POST['item_model']);
    $reg_number = mysqli_real_escape_string($con, $_POST['reg_number']);
    $serial_number = mysqli_real_escape_string($con, $_POST['serial_number']);
    $date_registered = mysqli_real_escape_string($con, $_POST['date_registered']);
    $picture = $_FILES['picture']['name'];
    $picture_tmp = $_FILES['picture']['tmp_name'];

    $picture_path = "uploads/" . basename($picture);
    $picture_path = mysqli_real_escape_string($con, $picture_path);
    move_uploaded_file($picture_tmp, $picture_path);

    // Generate QR code data
    $qr_data_student = "Reg Number: $reg_number, Date Registered: $date_registered, Officer: $officer_lastname, Officer ID: $officer_id";
    $qr_data_asset = "Serial: $serial_number, Model: $item_model, Reg: $reg_number";

    // Use the RFID UID from the form (which was populated by the RFID reader)
    $rfid_uid = mysqli_real_escape_string($con, $_POST['rfid_uid']);

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
    <link rel="stylesheet" href="style/home-modern.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Register Asset - Security Dashboard</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        /* Exact copy of home.php CSS variables and measurements */

        :root {
            --primary-dark: #4b648d;
            --primary-light: #e7fbf9;
            --accent-teal: #41737c;
            --text-dark: #2c3e50;
            --text-light: #ffffff;
            --shadow: rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        body {
            background: linear-gradient(-45deg, #4b648d, #41737c, #4b648d, #41737c);
            background-size: 400% 400%;
            animation: gradientBG 12s ease infinite;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* Particle Background - Exact copy */
        .particles-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0.5));
            border-radius: 50%;
            box-shadow: 0 0 25px rgba(255, 255, 255, 0.5);
            animation: float 25s infinite linear;
            backdrop-filter: blur(2px);
        }

        .particle:nth-child(odd) {
            animation-duration: 30s;
            animation-delay: -5s;
        }

        .particle:nth-child(even) {
            animation-duration: 35s;
            animation-delay: -10s;
        }

        .particle:nth-child(3n) {
            background: linear-gradient(45deg, rgba(231, 251, 249, 0.9), rgba(65, 115, 124, 0.6));
            box-shadow: 0 0 30px rgba(65, 115, 124, 0.7);
        }

        .particle:nth-child(4n) {
            background: linear-gradient(45deg, rgba(75, 100, 141, 0.8), rgba(255, 255, 255, 0.4));
            box-shadow: 0 0 35px rgba(75, 100, 141, 0.5);
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg) scale(0.3);
                opacity: 0;
            }
            10% {
                opacity: 0.9;
                transform: translateY(90vh) rotate(36deg) scale(1.2);
            }
            50% {
                opacity: 1;
                transform: translateY(50vh) rotate(180deg) scale(1.5);
            }
            90% {
                opacity: 0.9;
                transform: translateY(10vh) rotate(324deg) scale(1.2);
            }
            100% {
                transform: translateY(-10vh) rotate(360deg) scale(0.3);
                opacity: 0;
            }
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Main Container - Exact measurements */
        .admin-container {
            display: flex;
            min-height: 100vh;
            position: relative;
            z-index: 2;
        }

        /* Sidebar - Exact copy with measurements */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, var(--primary-dark), var(--accent-teal));
            color: var(--text-light);
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            z-index: 100;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 15px var(--shadow);
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

        .sidebar-brand img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid var(--text-light);
            object-fit: cover;
            margin-bottom: 10px;
        }

        .sidebar-brand .reg-number {
            font-size: 0.9rem;
            opacity: 0.9;
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
            transition: var(--transition);
            border-left: 4px solid transparent;
        }

        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            color: var(--text-light);
            background: rgba(255, 255, 255, 0.1);
            border-left-color: var(--primary-light);
            transform: translateX(5px);
        }

        .sidebar-menu i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }

        /* Content Wrapper - Exact measurements */
        .content-wrapper {
            margin-left: 280px;
            width: calc(100% - 280px);
            min-height: 100vh;
        }

        /* Topbar - Exact copy with measurements */
        .topbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px var(--shadow);
            height: 70px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .topbar h1 {
            color: var(--text-dark);
            font-size: 1.8rem;
            font-weight: 600;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-info span {
            color: var(--text-dark);
            font-weight: 500;
        }

        .user-info img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: 2px solid var(--accent-teal);
            object-fit: cover;
        }

        /* Main Content - Exact copy from home.php */
        .main-content {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .main-box {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: 0 8px 32px var(--shadow);
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .main-box h2 {
            color: var(--text-dark);
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .main-box h2 i {
            color: var(--accent-teal);
        }

        /* Modern Form Styling */
        .registration-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .form-section {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 20px var(--shadow);
        }

        .form-section h3 {
            color: var(--text-dark);
            margin-bottom: 1rem;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-section h3 i {
            color: var(--accent-teal);
        }

        /* RFID Reader Section */
        .rfid-reader {
            background: linear-gradient(135deg, rgba(75, 100, 141, 0.1), rgba(65, 115, 124, 0.1));
            border: 2px solid var(--primary-dark);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .rfid-reader h3 {
            color: var(--primary-dark);
            margin-bottom: 0.5rem;
        }

        .rfid-reader p {
            color: var(--text-dark);
            margin-bottom: 1rem;
            opacity: 0.8;
        }

        #rfid-status {
            font-weight: 600;
            padding: 0.75rem;
            border-radius: 8px;
            margin: 1rem 0;
            font-family: 'Courier New', monospace;
        }

        #rfid-status.status-waiting {
            background: rgba(255, 193, 62, 0.2);
            color: #856404;
            border: 1px solid rgba(255, 193, 62, 0.3);
        }

        #rfid-status.status-success {
            background: rgba(40, 167, 69, 0.2);
            color: #155724;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        #rfid-status.status-error {
            background: rgba(220, 53, 69, 0.2);
            color: #721c24;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        /* Form Fields */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            font-size: 0.9rem;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-teal);
            box-shadow: 0 0 0 3px rgba(65, 115, 124, 0.1);
        }

        .form-control[type="file"] {
            padding: 0.5rem;
            border-style: dashed;
        }

        /* QR Code Section */
        .qr-section {
            background: linear-gradient(135deg, rgba(65, 115, 124, 0.1), rgba(75, 100, 141, 0.1));
            border: 2px solid var(--accent-teal);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            text-align: center;
        }

        .qr-section h3 {
            color: var(--accent-teal);
            margin-bottom: 1rem;
        }

        #qrcode-student {
            display: inline-block;
            background: white;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            box-shadow: 0 4px 15px var(--shadow);
        }

        /* Buttons - Exact copy from home.php */
        .btn,
        .nav-btn {
            background: linear-gradient(135deg, var(--accent-teal), var(--primary-dark));
            color: var(--text-light);
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: var(--border-radius);
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn:hover,
        .nav-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(65, 115, 124, 0.3);
        }

        .btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #20c997, #17a2b8);
        }

        /* Mobile Sidebar Toggle */
        .toggle-sidebar {
            background: none;
            border: none;
            color: var(--text-dark);
            font-size: 1.5rem;
            cursor: pointer;
            display: none;
            margin-right: 1rem;
        }

        /* Responsive Design - Exact copy from home.php */
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                overflow: hidden;
            }
            .content-wrapper {
                margin-left: 0;
                width: 100%;
            }
            .sidebar.active {
                width: 280px;
            }
            .toggle-sidebar {
                display: block !important;
            }
            .main-content {
                padding: 1rem;
            }
            .topbar {
                padding: 0 1rem;
            }
            .topbar h1 {
                font-size: 1.4rem;
            }
            .registration-form {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }

        /* Animations - Exact copy from home.php */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Apply animations to dashboard elements */
        .sidebar {
            animation: slideInLeft 0.8s ease-out;
        }

        .topbar {
            animation: fadeIn 0.6s ease-out 0.2s both;
        }

        .main-box {
            animation: fadeIn 0.8s ease-out 0.4s both;
        }

        .form-section {
            animation: scaleIn 0.7s ease-out 0.6s both;
        }

        .btn,
        .nav-btn {
            animation: scaleIn 0.5s ease-out 0.8s both;
        }

        /* Messages */
        .message {
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            text-align: center;
            backdrop-filter: blur(10px);
        }

        .message.success {
            background: rgba(212, 237, 218, 0.9);
            color: #155724;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .message.error {
            background: rgba(248, 215, 218, 0.9);
            color: #721c24;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        /* RFID Details */
        .rfid-details {
            background: rgba(75, 100, 141, 0.1);
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            border-left: 4px solid var(--primary-dark);
        }

        .rfid-details h4 {
            color: var(--primary-dark);
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .rfid-details p {
            margin: 0.25rem 0;
            font-size: 0.9rem;
        }

        .copyable {
            background: rgba(255, 255, 255, 0.8);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            cursor: pointer;
            transition: var(--transition);
            font-family: 'Courier New', monospace;
        }

        .copyable:hover {
            background: rgba(255, 255, 255, 1);
            transform: scale(1.05);
        }

        /* QR Details */
        .qr-details {
            background: rgba(65, 115, 124, 0.1);
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            border-left: 4px solid var(--accent-teal);
        }

        .qr-details h4 {
            color: var(--accent-teal);
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .qr-details p {
            margin: 0.25rem 0;
            font-size: 0.9rem;
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
                    <i class="fas fa-user-circle" style="font-size: 50px; color: white;"></i>
                    <div class="reg-number"><?php echo htmlspecialchars($res_name); ?></div>
                </div>
            </div>
            <ul class="sidebar-menu">
                <li>
                    <a href="schome.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="missingassets.php">
                        <i class="fas fa-search"></i> Missing Assets
                    </a>
                </li>
                <li>
                    <a href="regAsset.php" class="active">
                        <i class="fas fa-plus-circle"></i> Register Asset
                    </a>
                </li>
                <li>
                    <a href="blacklistedassets.php">
                        <i class="fas fa-ban"></i> Blacklisted Assets
                    </a>
                </li>
                <li>
                    <a href="verifyassets.php">
                        <i class="fas fa-check-circle"></i> Verify Asset
                    </a>
                </li>
                <li>
                    <a href="php/logout.php">
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
                    <h1>Register New Asset</h1>
                </div>
                <div class="user-info">
                    <span>Welcome, <b><?php echo htmlspecialchars($res_name); ?></b></span>
                    <i class="fas fa-user-circle" style="font-size: 24px;"></i>
                </div>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <?php if(isset($message)) echo $message; ?>

                <div class="main-box">
                    <h2><i class="fas fa-plus-circle"></i> Asset Registration System</h2>

                    <!-- RFID Reader Section -->
                    <div class="rfid-reader">
                        <h3><i class="fas fa-wifi"></i> RFID Tag Reader</h3>
                        <p>Place an RFID tag near the reader to capture its UID automatically.</p>
                        <div id="rfid-status" class="status-waiting">Waiting for RFID tag...</div>
                        <button id="refresh-rfid" class="btn">Refresh RFID</button>
                    </div>

                    <!-- Asset Registration Form -->
                    <form action="" method="post" enctype="multipart/form-data">
                        <!-- Hidden RFID UID field that will be populated by the reader -->
                        <input type="hidden" name="rfid_uid" id="rfid-uid-input">

                        <div class="registration-form">
                            <!-- Asset Information Section -->
                            <div class="form-section">
                                <h3><i class="fas fa-laptop"></i> Asset Information</h3>

                                <div class="form-group">
                                    <label for="item_description">Item Description</label>
                                    <input type="text" name="item_description" id="item_description" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="item_model">Item Model</label>
                                    <input type="text" name="item_model" id="item_model" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="serial_number">Serial Number</label>
                                    <input type="text" name="serial_number" id="serial_number" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="picture">Asset Picture</label>
                                    <input type="file" name="picture" id="picture" class="form-control" accept="image/*">
                                </div>
                            </div>

                            <!-- Registration Information Section -->
                            <div class="form-section">
                                <h3><i class="fas fa-user-graduate"></i> Registration Information</h3>

                                <div class="form-group">
                                    <label for="reg_number">Registration Number</label>
                                    <input type="text" name="reg_number" id="reg_number" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="date_registered">Date Registered</label>
                                    <input type="date" name="date_registered" id="date_registered" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <!-- QR Code Section -->
                        <div class="qr-section">
                            <h3><i class="fas fa-qrcode"></i> Student/Staff QR Code</h3>
                            <div id="qrcode-student"></div>
                            <button type="button" id="download-student-qr" class="btn btn-success" style="display:none;">
                                <i class="fas fa-download"></i> Download QR Code
                            </button>
                        </div>

                        <!-- Submit Button -->
                        <div style="text-align: center; margin-top: 2rem;">
                            <input type="submit" name="submit" class="btn" value="Register Asset" id="submit-btn" disabled>
                        </div>
                    </form>
                </div>
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
    <script src="js/particles.js"></script>
    <script src="js/home.js"></script>
    <script>
        // Initialize particles
        document.addEventListener('DOMContentLoaded', function() {
            // Create particles container
            const particlesContainer = document.querySelector('.particles-container');

            // Create particles
            for (let i = 0; i < 50; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 25 + 's';
                particlesContainer.appendChild(particle);
            }
        });

        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');

            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });
            }
        });
    </script>
</body>
</html>