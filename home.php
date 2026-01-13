<?php
session_start();
include("php/config.php");

if (!isset($_SESSION['valid'])) {
    header("Location: index.php");
    exit;
}

// Handle reporting missing asset
$message = "";
if (isset($_POST['report_missing']) && isset($_POST['asset_id'])) {
    $asset_id = mysqli_real_escape_string($con, $_POST['asset_id']);
    
    // Update the asset status to missing
    $update_query = mysqli_query($con, "UPDATE assets SET 
                                        AssetStatus = 'Missing',
                                        date_reported_missing = NOW()
                                        WHERE serial_number = '$asset_id'");
    
    if ($update_query) {
        $message = "<div class='message success'><p>Asset has been reported as missing.</p></div>";
    } else {
        $message = "<div class='message error'><p>Failed to report asset as missing: " . mysqli_error($con) . "</p></div>";
    }
}

$id = $_SESSION['id'];
$query = mysqli_query($con, "SELECT * FROM users WHERE Id=$id");

while ($result = mysqli_fetch_assoc($query)) {
    $res_Uname = $result['Username'];
    $res_Lastname = $result['Lastname'];
    $res_Email = $result['Email'];
    $res_Reg_Number = $result['Reg_Number'];
    $res_Phone = $result['Phone'];
    $res_School = $result['School'];
    $res_Photo = $result['myphoto'];
}

$asset_query = mysqli_query($con, "SELECT * FROM assets WHERE reg_number='$res_Reg_Number'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Home</title>
    <style>
        /* Color Palette */
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

        /* Removed particle background for better performance */

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Main Container */
        .admin-container {
            display: flex;
            min-height: 100vh;
            position: relative;
            z-index: 2;
        }

        /* Sidebar */
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

        .sidebar-brand-content img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .reg-number {
            font-size: 0.9rem;
            font-weight: 600;
            margin-top: 5px;
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

        /* Content Wrapper */
        .content-wrapper {
            margin-left: 280px;
            width: calc(100% - 280px);
            min-height: 100vh;
        }

        /* Topbar */
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
        }

        .topbar h1 {
            color: var(--text-dark);
            font-size: 1.8rem;
            font-weight: 600;
        }

        .toggle-sidebar {
            background: transparent;
            border: none;
            color: var(--text-dark);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            margin-right: 1rem;
            display: none;
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
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }

        .nav-btn {
            background: linear-gradient(135deg, var(--accent-teal), var(--primary-dark));
            color: var(--text-light);
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: var(--border-radius);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(65, 115, 124, 0.3);
        }

        /* Main Content */
        .main-content {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Main Box */
        .main-box {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: 0 8px 32px var(--shadow);
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 2rem;
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

        /* Profile Boxes */
        .top, .bottom {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .box {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 20px var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition);
            cursor: pointer;
        }

        .box p {
            color: var(--text-dark);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .box b {
            color: var(--primary-dark);
        }

        /* Info Bubble Tooltip */
        .info-bubble {
            position: fixed;
            background: linear-gradient(135deg, var(--accent-teal), var(--primary-dark));
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
            font-size: 0.95rem;
            font-weight: 500;
            pointer-events: none;
            opacity: 0;
            transform: scale(0.8) translateY(10px);
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            z-index: 10000;
            max-width: 300px;
            word-wrap: break-word;
        }

        .info-bubble.show {
            opacity: 1;
            transform: scale(1) translateY(0);
        }

        .info-bubble::before {
            content: '';
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 10px solid transparent;
            border-right: 10px solid transparent;
            border-bottom: 10px solid var(--accent-teal);
        }

        /* Asset Info */
        .asset-info {
            position: relative;
            margin-bottom: 20px;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: 0 4px 20px var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .status-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--border-radius);
            z-index: 10;
        }

        .missing-overlay {
            background: rgba(255, 152, 0, 0.85);
        }

        .blacklisted-overlay {
            background: rgba(244, 67, 54, 0.85);
        }

        .status-text {
            color: white;
            font-size: 3rem;
            font-weight: 800;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5);
            transform: rotate(-15deg);
        }

        .asset-content {
            position: relative;
            z-index: 5;
        }

        .asset-content h3 {
            color: var(--text-dark);
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .asset-content p {
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .asset-image, .qr-code {
            background: rgba(255, 255, 255, 0.9);
            padding: 1rem;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 10px var(--shadow);
            text-align: center;
        }

        .asset-image h4, .qr-code h4 {
            color: var(--text-dark);
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .asset-image img, .qr-code img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .status {
            display: inline-block;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .status-Active {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-Missing {
            background: #fff3e0;
            color: #e65100;
        }

        .status-Blacklisted {
            background: #ffebee;
            color: #c62828;
        }

        .status-info {
            background: rgba(255, 255, 255, 0.5);
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            border-left: 4px solid;
        }

        .status-info.missing {
            border-left-color: #ff9800;
        }

        .status-info.blacklisted {
            border-left-color: #f44336;
        }

        .btn-report-missing {
            background: linear-gradient(135deg, #ff9800, #f57c00);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-report-missing:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 152, 0, 0.4);
        }

        /* Messages */
        .message {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .message.success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #4caf50;
        }

        .message.error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #f44336;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.98);
            margin: 2% auto;
            padding: 0;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 1000px;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.3);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-dark), var(--accent-teal));
            color: white;
            padding: 1.5rem 2rem;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .close {
            color: white;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            transition: var(--transition);
        }

        .close:hover {
            transform: scale(1.2);
        }

        .modal-body {
            padding: 2rem;
            color: var(--text-dark);
        }

        .toc {
            background: rgba(231, 251, 249, 0.5);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
        }

        .toc h3 {
            color: var(--primary-dark);
            margin-bottom: 1rem;
        }

        .toc ul {
            list-style: none;
            padding: 0;
        }

        .toc li {
            margin-bottom: 0.5rem;
        }

        .toc a {
            color: var(--accent-teal);
            text-decoration: none;
            transition: var(--transition);
        }

        .toc a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .help-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .help-section h3 {
            color: var(--primary-dark);
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .help-section h4 {
            color: var(--accent-teal);
            margin: 1rem 0 0.5rem;
            font-size: 1.2rem;
        }

        .help-section p, .help-section li {
            line-height: 1.8;
            margin-bottom: 0.5rem;
        }

        .help-footer {
            background: rgba(231, 251, 249, 0.5);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-top: 2rem;
        }

        /* Dark Mode */
        body.dark-mode {
            background: linear-gradient(-45deg, #1a1a2e, #16213e, #1a1a2e, #16213e);
        }

        body.dark-mode .topbar {
            background: rgba(30, 30, 30, 0.95);
        }

        body.dark-mode .topbar h1,
        body.dark-mode .user-info span {
            color: var(--text-light);
        }

        body.dark-mode .toggle-sidebar {
            color: var(--text-light);
        }

        body.dark-mode .main-box {
            background: rgba(30, 30, 30, 0.7);
        }

        body.dark-mode .main-box h2,
        body.dark-mode .box p,
        body.dark-mode .asset-content h3,
        body.dark-mode .asset-content p {
            color: var(--text-light);
        }

        body.dark-mode .box {
            background: rgba(45, 45, 45, 0.7);
        }

        body.dark-mode .asset-info {
            background: rgba(30, 30, 30, 0.8);
        }

        /* Responsive Design */
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
                display: block;
            }

            .topbar h1 {
                font-size: 1.4rem;
            }

            .main-content {
                padding: 1rem;
            }

            .top, .bottom {
                grid-template-columns: 1fr;
            }
        }

        /* Animations */
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

        .sidebar {
            animation: slideInLeft 0.8s ease-out;
        }

        .topbar {
            animation: fadeIn 0.6s ease-out 0.2s both;
        }

        .main-box {
            animation: fadeIn 0.8s ease-out 0.4s both;
        }
    </style>
</head>
<body>
    <!-- Info Bubble Tooltip -->
    <div id="infoBubble" class="info-bubble"></div>

    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-brand-content">
                    <?php if (!empty($res_Photo)): ?>
                        <img src="<?php echo $res_Photo; ?>" alt="User Photo">
                    <?php else: ?>
                        <i class="fas fa-user-circle" style="font-size: 50px; color: white;"></i>
                    <?php endif; ?>
                    <div class="reg-number"><?php echo $res_Reg_Number; ?></div>
                </div>
            </div>
            <ul class="sidebar-menu">
                <li>
                    <a href="home.php" class="active">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="edit.php">
                        <i class="fas fa-user-edit"></i> Edit Profile
                    </a>
                </li>
                
                
                <li>
                    <a href="#" id="helpButton">
                        <i class="fas fa-question-circle"></i> Help
                    </a>
                </li>
                <li>
                    <a href="welcome.php">
                        <i class="fas fa-sign-out-alt"></i> Log Out
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
                    <h1>User Dashboard</h1>
                </div>
                <div class="user-info">
                    <!-- Theme toggle as a proper button in the nav bar -->
                    <button id="theme-toggle" class="nav-btn">
                        <i class="fas fa-moon"></i>
                        <span>Dark Mode</span>
                    </button>
                    <span><?php echo $res_Uname . ' ' . $res_Lastname; ?></span>
                    <?php if (!empty($res_Photo)): ?>
                        <img src="<?php echo $res_Photo; ?>" alt="User">
                    <?php else: ?>
                        <i class="fas fa-user-circle" style="font-size: 24px;"></i>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="main-content">
                <?php echo $message; ?>
                
                <div class="main-box">
                    <h2><i class="fas fa-user-circle"></i> User Profile</h2>
                    <div class="top">
                        <div class="box">
                            <p>Hello <b><?php echo $res_Uname; ?></b>, Welcome</p>
                        </div>
                        <div class="box">
                            <p>Email : <b><?php echo $res_Email; ?></b>.</p>
                        </div>
                    </div>
                    <div class="bottom">
                        <div class="box">
                            <p>Last Name : <b><?php echo $res_Lastname; ?></b>.</p>
                        </div>
                        <div class="box">
                            <p>Registration Number : <b><?php echo $res_Reg_Number; ?></b>.</p>
                        </div>
                        <div class="box">
                            <p>Phone Number : <b><?php echo $res_Phone; ?></b>.</p>
                        </div>
                        <div class="box">
                            <p>In the School of : <b><?php echo $res_School; ?></b>.</p>
                        </div>
                    </div>
                </div>

                

                <div class="main-box">
                    <h2><i class="fas fa-laptop"></i> Your Registered Assets</h2>
                    
                    <?php if (mysqli_num_rows($asset_query) > 0): ?>
                        <?php while ($asset = mysqli_fetch_assoc($asset_query)): ?>
                            <div class="asset-info">
                                <?php if (isset($asset['AssetStatus']) && $asset['AssetStatus'] == 'Missing'): ?>
                                    <div class="status-overlay missing-overlay">
                                        <div class="status-text missing-text">MISSING</div>
                                    </div>
                                <?php elseif (isset($asset['AssetStatus']) && $asset['AssetStatus'] == 'Blacklisted'): ?>
                                    <div class="status-overlay blacklisted-overlay">
                                        <div class="status-text blacklisted-text">CONFISCATED</div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="asset-content">
                                    <h3><?php echo htmlspecialchars($asset['item_description']); ?></h3>
                                    <div style="display: flex; flex-wrap: wrap; gap: 20px;">
                                        <div style="flex: 2; min-width: 300px;">
                                            <p><b>Serial Number:</b> <?php echo htmlspecialchars($asset['serial_number']); ?></p>
                                            <p><b>Date Registered:</b> <?php echo htmlspecialchars($asset['date_registered']); ?></p>
                                            <p><b>Item Model:</b> <?php echo htmlspecialchars($asset['item_model']); ?></p>
                                            
                                            <?php if (isset($asset['AssetStatus']) && $asset['AssetStatus'] == 'Missing'): ?>
                                                <div class="status-info missing">
                                                    <p><b>Status:</b> <span class="status status-Missing">MISSING</span></p>
                                                    <p><b>Reported Missing On:</b> <?php echo date('Y-m-d H:i', strtotime($asset['date_reported_missing'])); ?></p>
                                                </div>
                                            <?php elseif (isset($asset['AssetStatus']) && $asset['AssetStatus'] == 'Blacklisted'): ?>
                                                <div class="status-info blacklisted">
                                                    <p><b>Status:</b> <span class="status status-Blacklisted">CONFISCATED</span></p>
                                                    <p><b>Date Confiscated:</b> <?php echo date('Y-m-d H:i', strtotime($asset['date_blacklisted'])); ?></p>
                                                    <p><b>Message:</b> This asset has been confiscated at a security checkpoint. Please visit the security office to confirm ownership.</p>
                                                    <?php if (!empty($asset['blacklist_reason'])): ?>
                                                        <p><b>Reason:</b> <?php echo htmlspecialchars($asset['blacklist_reason']); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php elseif (!isset($asset['AssetStatus']) || $asset['AssetStatus'] == NULL): ?>
                                                <p><b>Status:</b> <span class="status status-Active">ACTIVE</span></p>
                                                <form action="" method="post" onsubmit="return confirm('Are you sure you want to report this asset as missing?');">
                                                    <input type="hidden" name="asset_id" value="<?php echo $asset['serial_number']; ?>">
                                                    <button type="submit" name="report_missing" class="btn-report-missing"><i class="fas fa-exclamation-triangle"></i> Report as Missing</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div style="flex: 1; min-width: 220px;">
                                            <div class="asset-details-container" style="display: flex; flex-direction: column; gap: 20px;">
                                                <?php if (!empty($asset['picture'])): ?>
                                                    <div class="asset-image">
                                                        <h4><i class="fas fa-camera"></i> Asset Picture</h4>
                                                        <img src="<?php echo htmlspecialchars($asset['picture']); ?>" alt="Asset Image">
                                                    </div>
                                                <?php endif; ?>

                                                <?php if (!empty($asset['qr_code'])): ?>
                                                    <div class="qr-code">
                                                        <h4><i class="fas fa-qrcode"></i> QR Code</h4>
                                                        <img src="<?php echo htmlspecialchars($asset['qr_code']); ?>" alt="QR Code">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="text-align: center; padding: 20px;">No asset information found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Help Modal -->
    <div id="helpModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-question-circle"></i> Smart Tag Asset Management System User Guide</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="toc">
                    <h3>Table of Contents</h3>
                    <ul>
                        <li><a href="#getting-started">Getting Started</a></li>
                        <li><a href="#dashboard-overview">Dashboard Overview</a></li>
                        <li><a href="#managing-profile">Managing Your Profile</a></li>
                        <li><a href="#viewing-assets">Viewing Your Assets</a></li>
                        <li><a href="#reporting-missing">Reporting Missing Assets</a></li>
                        <li><a href="#understanding-status">Understanding Asset Status</a></li>
                        <li><a href="#qr-codes">QR Codes and Security</a></li>
                        <li><a href="#faq">Frequently Asked Questions</a></li>
                    </ul>
                </div>
                
                <div id="getting-started" class="help-section">
                    <h3>Getting Started</h3>
                    
                    <h4>Logging In</h4>
                    <ol>
                        <li>Navigate to the login page</li>
                        <li>Enter your username and password</li>
                        <li>Click "Login" to access your dashboard</li>
                    </ol>
                    
                    <h4>First-time Users</h4>
                    <p>If this is your first time using the system, you may need to:</p>
                    <ul>
                        <li>Update your profile information</li>
                        <li>Change your default password</li>
                        <li>Verify your contact details</li>
                    </ul>
                </div>
                
                <div id="dashboard-overview" class="help-section">
                    <h3>Dashboard Overview</h3>
                    <p>The dashboard provides a quick overview of:</p>
                    <ul>
                        <li>Your personal information</li>
                        <li>Your registered assets</li>
                        <li>Any assets with special status (missing or confiscated)</li>
                    </ul>
                    
                    <h4>Navigation</h4>
                    <ul>
                        <li><strong>Dashboard</strong>: View your main information and assets</li>
                        <li><strong>Edit Profile</strong>: Update your personal information</li>
                        <li><strong>My Assets</strong>: View detailed information about your registered assets</li>
                        <li><strong>Notifications</strong>: Check system notifications about your assets</li>
                        <li><strong>Help</strong>: Access this user guide</li>
                        <li><strong>Log Out</strong>: Securely exit the system</li>
                    </ul>
                </div>
                
                <div id="managing-profile" class="help-section">
                    <h3>Managing Your Profile</h3>
                    
                    <h4>Viewing Profile Information</h4>
                    <p>Your profile displays:</p>
                    <ul>
                        <li>Username</li>
                        <li>Last name</li>
                        <li>Email address</li>
                        <li>Registration number</li>
                        <li>Phone number</li>
                        <li>School/Department</li>
                    </ul>
                    
                    <h4>Updating Profile Information</h4>
                    <ol>
                        <li>Click "Edit Profile" in the sidebar</li>
                        <li>Update the necessary fields</li>
                        <li>Click "Save Changes" to update your information</li>
                    </ol>
                    
                    <h4>Profile Photo</h4>
                    <p>Your profile photo is used for identification purposes and is displayed on your ID card in the system.</p>
                </div>
                
                <div id="viewing-assets" class="help-section">
                    <h3>Viewing Your Assets</h3>
                    
                    <h4>Asset Information</h4>
                    <p>For each registered asset, you can view:</p>
                    <ul>
                        <li>Item description</li>
                        <li>Serial number</li>
                        <li>Date registered</li>
                        <li>Item model</li>
                        <li>Asset status</li>
                        <li>Asset picture (if available)</li>
                        <li>QR code (for security checkpoints)</li>
                    </ul>
                    
                    <h4>Asset Details</h4>
                    <p>Click on an asset to view more detailed information, including:</p>
                    <ul>
                        <li>Registration history</li>
                        <li>Technical specifications</li>
                        <li>Security information</li>
                    </ul>
                </div>
                
                <div id="reporting-missing" class="help-section">
                    <h3>Reporting Missing Assets</h3>
                    <p>If one of your assets is missing:</p>
                    <ol>
                        <li>Locate the asset in your dashboard</li>
                        <li>Click the "Report as Missing" button</li>
                        <li>Confirm your action when prompted</li>
                        <li>The system will update the asset status to "Missing"</li>
                        <li>Campus security will be notified automatically</li>
                    </ol>
                    
                    <h4>Important Notes About Missing Assets</h4>
                    <ul>
                        <li>Once reported missing, the asset's QR code will be flagged in the security system</li>
                        <li>If the asset is found at a security checkpoint, it will be confiscated for verification</li>
                        <li>You will need to visit the security office to reclaim a found asset</li>
                    </ul>
                </div>
                
                <div id="understanding-status" class="help-section">
                    <h3>Understanding Asset Status</h3>
                    <p>Your assets can have different statuses:</p>
                    
                    <h4>Active</h4>
                    <ul>
                        <li>Normal status for registered assets</li>
                        <li>Asset is in your possession</li>
                        <li>No restrictions on campus movement</li>
                    </ul>
                    
                    <h4>Missing</h4>
                    <ul>
                        <li>You have reported this asset as missing</li>
                        <li>Security personnel are alerted to look for this asset</li>
                        <li>The asset's QR code is flagged in the system</li>
                    </ul>
                    
                    <h4>Confiscated</h4>
                    <ul>
                        <li>The asset was stopped at a security checkpoint</li>
                        <li>Possible reasons include:
                            <ul>
                                <li>Previously reported as missing</li>
                                <li>Suspicious activity</li>
                                <li>Verification needed</li>
                            </ul>
                        </li>
                        <li>You must visit the security office to reclaim the asset</li>
                    </ul>
                    
                    <h4>Recovered</h4>
                    <ul>
                        <li>A previously missing asset that has been found</li>
                        <li>Verification of ownership has been completed</li>
                        <li>Asset has been returned to you</li>
                    </ul>
                </div>
                
                <div id="qr-codes" class="help-section">
                    <h3>QR Codes and Security</h3>
                    
                    <h4>Purpose of QR Codes</h4>
                    <p>Each asset has a unique QR code that:</p>
                    <ul>
                        <li>Identifies the asset in the system</li>
                        <li>Links the asset to your registration number</li>
                        <li>Allows for quick verification at security checkpoints</li>
                    </ul>
                    
                    <h4>Using QR Codes</h4>
                    <ul>
                        <li>Keep the QR code visible on your asset</li>
                        <li>Present the QR code when requested at security checkpoints</li>
                        <li>Do not tamper with or attempt to modify the QR code</li>
                    </ul>
                    
                    <h4>Security Checkpoints</h4>
                    <p>When passing through a security checkpoint:</p>
                    <ol>
                        <li>Security personnel may scan your asset's QR code</li>
                        <li>The system verifies the asset's status and ownership</li>
                        <li>If everything is in order, you may proceed</li>
                        <li>If there are issues, the asset may be temporarily confiscated</li>
                    </ol>
                </div>
                
                <div id="faq" class="help-section">
                    <h3>Frequently Asked Questions</h3>
                    
                    <h4>How do I register a new asset?</h4>
                    <p>New assets must be registered through the campus security office. Bring your asset and student ID to complete the registration process.</p>
                    
                    <h4>What should I do if my asset is confiscated?</h4>
                    <p>Visit the security office with your student ID to verify ownership and reclaim your asset.</p>
                    
                    <h4>Can I update the information about my asset?</h4>
                    <p>Asset information can only be updated by authorized security personnel. Contact the security office if you need to update asset details.</p>
                    
                    <h4>What happens if someone else tries to take my asset off campus?</h4>
                    <p>The QR code links the asset to your registration number. Security checkpoints will identify if the person carrying the asset is not the registered owner.</p>
                    
                    <h4>How long does it take to process a missing asset report?</h4>
                    <p>Missing asset reports are processed immediately in the system. Security personnel are notified in real-time.</p>
                    
                    <h4>Can I cancel a missing asset report?</h4>
                    <p>If you find your asset after reporting it missing, you must visit the security office to update the status.</p>
                </div>
                
                <div class="help-footer">
                    <p>For additional assistance, please contact the campus security office at:</p>
                    <ul>
                        <li><strong>Email</strong>: security@campus.edu</li>
                        <li><strong>Phone</strong>: (123) 456-7890</li>
                        <li><strong>Location</strong>: Main Campus, Security Building, Room 101</li>
                    </ul>
                    <p><em>This user guide is provided by the Campus Security Department</em></p>
                </div>
            </div>
        </div>
    </div>
    </div> <!-- End of your main container -->
    
    <script>
        // Info Bubble Tooltip Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const infoBubble = document.getElementById('infoBubble');
            const boxes = document.querySelectorAll('.box');
            const userInfoArea = document.querySelector('.user-info');
            
            // Get user info position for bubble placement
            function getTargetPosition() {
                if (userInfoArea) {
                    const rect = userInfoArea.getBoundingClientRect();
                    return {
                        x: rect.left + (rect.width / 2),
                        y: rect.bottom + 15
                    };
                }
                return { x: window.innerWidth / 2, y: 100 };
            }
            
            boxes.forEach(box => {
                box.addEventListener('mouseenter', function() {
                    const text = this.textContent.trim();
                    infoBubble.textContent = text;
                    
                    const pos = getTargetPosition();
                    infoBubble.style.left = pos.x + 'px';
                    infoBubble.style.top = pos.y + 'px';
                    infoBubble.style.transform = 'translateX(-50%) scale(0.8)';
                    
                    // Show bubble with animation
                    setTimeout(() => {
                        infoBubble.classList.add('show');
                        infoBubble.style.transform = 'translateX(-50%) scale(1)';
                    }, 10);
                });
                
                box.addEventListener('mouseleave', function() {
                    infoBubble.classList.remove('show');
                    infoBubble.style.transform = 'translateX(-50%) scale(0.8)';
                });
            });
        });

        // Sidebar Toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        const contentWrapper = document.querySelector('.content-wrapper');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                
                if (sidebar.classList.contains('active')) {
                    contentWrapper.style.marginLeft = '280px';
                    contentWrapper.style.width = 'calc(100% - 280px)';
                } else {
                    contentWrapper.style.marginLeft = '0';
                    contentWrapper.style.width = '100%';
                }
            });
        }
        
        // Check if on mobile and adjust sidebar
        function checkWidth() {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('active');
                contentWrapper.style.marginLeft = '0';
                contentWrapper.style.width = '100%';
            } else {
                sidebar.classList.add('active');
                contentWrapper.style.marginLeft = '280px';
                contentWrapper.style.width = 'calc(100% - 280px)';
            }
        }
        
        // Run on page load
        checkWidth();
        
        // Run on window resize
        window.addEventListener('resize', checkWidth);
        
        // Dark Mode Toggle
        const themeToggle = document.getElementById('theme-toggle');
        const body = document.body;
        const themeIcon = themeToggle.querySelector('i');

        // Check for saved theme in localStorage
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            body.classList.add(savedTheme);
            if (savedTheme === 'dark-mode') {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
                themeToggle.querySelector('span').textContent = 'Light Mode';
            }
        }

        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            const isDarkMode = body.classList.contains('dark-mode');
            
            // Toggle icon and text
            if (isDarkMode) {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
                themeToggle.querySelector('span').textContent = 'Light Mode';
            } else {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
                themeToggle.querySelector('span').textContent = 'Dark Mode';
            }
            
            localStorage.setItem('theme', isDarkMode ? 'dark-mode' : '');
        });

        // Help Modal Functionality
        const helpButton = document.getElementById('helpButton');
        const helpModal = document.getElementById('helpModal');
        const closeButton = document.querySelector('.close');

        // Open modal when help button is clicked
        helpButton.addEventListener('click', function(e) {
            e.preventDefault();
            helpModal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent scrolling behind modal
        });

        // Close modal when X is clicked
        closeButton.addEventListener('click', function() {
            helpModal.style.display = 'none';
            document.body.style.overflow = ''; // Restore scrolling
        });

        // Close modal when clicking outside of it
        window.addEventListener('click', function(event) {
            if (event.target === helpModal) {
                helpModal.style.display = 'none';
                document.body.style.overflow = ''; // Restore scrolling
            }
        });

        // Handle anchor links within the help modal
        document.querySelectorAll('.toc a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    // Scroll to the section
                    targetElement.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Check if page loaded with #help hash and open modal
        if (window.location.hash === '#help') {
            helpModal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    </script>
</body>
</html>
