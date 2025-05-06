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
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Home</title>
    <style>
        :root {
            --primary: #4e73df;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --light: #f8f9fc;
            --dark: #5a5c69;
        }
        
        body {
            overflow-x: hidden;
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        
        /* Sidebar Styles */
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background-color: #4e73df;
            background-image: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            background-size: cover;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            z-index: 10;
            transition: all 0.3s;
        }
        
        .sidebar-brand {
            height: 4.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-brand-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }
        
        .sidebar-brand img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
        }
        
        .sidebar-brand .reg-number {
            color: white;
            font-size: 0.8rem;
            margin-top: 5px;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }
        
        .sidebar-menu {
            padding: 1.5rem 0;
            margin: 0;
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
            width: 1.5rem;
            text-align: center;
        }
        
        /* Content Wrapper */
        .content-wrapper {
            margin-left: 250px;
            width: calc(100% - 250px);
        }
        
        /* Topbar */
        .topbar {
            background-color: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            height: 4.375rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        /* Theme toggle button in topbar */
        .theme-toggle-btn {
            background-color: transparent;
            color: var(--dark);
            border: none;
            padding: 0.5rem;
            margin-right: 1rem;
            cursor: pointer;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.3s;
        }
        
        .theme-toggle-btn:hover {
            color: var(--primary);
        }
        
        body.dark-mode .theme-toggle-btn {
            color: #e0e0e0;
        }
        
        /* Main Content */
        .main-content {
            padding: 1.5rem;
        }
        
        .main-box {
            background-color: white;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .main-box h2 {
            color: var(--dark);
            margin-top: 0;
            border-bottom: 1px solid #e3e6f0;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .top {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .box {
            flex: 1;
            min-width: 250px;
            padding: 1rem;
            background-color: #f8f9fc;
            border-radius: 0.35rem;
            border-left: 4px solid var(--primary);
        }
        
        .bottom {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .photo-container {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .photo-container img {
            max-width: 200px;
            border-radius: 50%;
            border: 4px solid var(--primary);
        }
        
        .asset-info {
            position: relative;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .status-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1;
            border-radius: 0.35rem;
        }
        
        .missing-overlay {
            background-color: rgba(255, 0, 0, 0.1);
        }
        
        .blacklisted-overlay {
            background-color: rgba(255, 165, 0, 0.1);
        }
        
        .status-text {
            font-size: 48px;
            font-weight: bold;
            transform: rotate(-15deg);
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            letter-spacing: 5px;
        }
        
        .missing-text {
            color: var(--danger);
        }
        
        .blacklisted-text {
            color: #ff8c00;
        }
        
        .asset-content {
            position: relative;
            z-index: 0;
        }
        
        .btn-report-missing {
            background-color: var(--danger);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.35rem;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s;
        }
        
        .btn-report-missing:hover {
            background-color: #c82333;
        }
        
        .message {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0.35rem;
        }
        
        .message.success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .message.error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .asset-image img, .qr-code img {
            max-width: 200px;
            height: auto;
            margin-top: 10px;
            border-radius: 0.35rem;
            border: 1px solid #e3e6f0;
            padding: 5px;
        }
        
        .status-info {
            margin-top: 10px;
            padding: 10px;
            border-radius: 0.35rem;
        }
        
        .status-info.missing {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .status-info.blacklisted {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
        }
        
        .status {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .status-Active {
            background-color: #e3fcef;
            color: #1cc88a;
        }
        
        .status-Missing {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-Blacklisted {
            background-color: #343a40;
            color: white;
        }
        
        .status-Recovered {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        /* Dark mode support */
        body.dark-mode {
            background-color: #121212;
            color: #e0e0e0;
        }
        
        body.dark-mode .main-box,
        body.dark-mode .box {
            background-color: #1e1e1e;
            color: #e0e0e0;
        }
        
        body.dark-mode .topbar {
            background-color: #1e1e1e;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(0, 0, 0, 0.3);
        }
        
        body.dark-mode .topbar h1,
        body.dark-mode .user-info span {
            color: #e0e0e0;
        }
        
        /* Mobile Responsiveness */
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
                width: 250px;
            }
            
            .toggle-sidebar {
                display: block;
            }
        }
        
        .toggle-sidebar {
            background: none;
            border: none;
            color: var(--dark);
            font-size: 1.5rem;
            cursor: pointer;
            display: none;
        }
        
        @media (max-width: 768px) {
            .toggle-sidebar {
                display: block;
            }
        }
        
        /* Nav button style */
        .nav-btn {
            display: flex;
            align-items: center;
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.35rem;
            cursor: pointer;
            margin-right: 1rem;
            transition: background-color 0.3s;
        }

        .nav-btn:hover {
            background-color: #2e59d9;
        }

        .nav-btn i {
            margin-right: 0.5rem;
        }

        body.dark-mode .nav-btn {
            background-color: #343a40;
        }

        body.dark-mode .nav-btn:hover {
            background-color: #23272b;
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
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 2% auto;
            padding: 0;
            border: 1px solid #888;
            width: 80%;
            max-width: 900px;
            max-height: 90vh;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            padding: 1rem 1.5rem;
            background-color: var(--primary);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #dee2e6;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            max-height: calc(90vh - 60px);
        }

        .close {
            color: white;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: #f8f9fc;
            text-decoration: none;
        }

        /* Help Content Styles */
        .toc {
            background-color: #f8f9fc;
            padding: 1rem;
            border-radius: 0.35rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary);
        }

        .toc ul {
            margin-bottom: 0;
        }

        .toc a {
            color: var(--primary);
            text-decoration: none;
        }

        .toc a:hover {
            text-decoration: underline;
        }

        .help-section {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e3e6f0;
        }

        .help-section:last-child {
            border-bottom: none;
        }

        .help-section h3 {
            color: var(--primary);
            margin-top: 0;
            padding-top: 1rem;
        }

        .help-section h4 {
            color: var(--dark);
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .help-section ul, 
        .help-section ol {
            padding-left: 1.5rem;
        }

        .help-section li {
            margin-bottom: 0.5rem;
        }

        .help-footer {
            background-color: #f8f9fc;
            padding: 1rem;
            border-radius: 0.35rem;
            margin-top: 1.5rem;
            border-left: 4px solid var(--info);
        }

        /* Dark mode support for modal */
        body.dark-mode .modal-content {
            background-color: #1e1e1e;
            color: #e0e0e0;
        }

        body.dark-mode .modal-header {
            background-color: #2c3e50;
        }

        body.dark-mode .toc,
        body.dark-mode .help-footer {
            background-color: #2c3e50;
        }

        body.dark-mode .help-section h3 {
            color: #4e73df;
        }

        body.dark-mode .help-section h4 {
            color: #e0e0e0;
        }
    </style>
</head>
<body>
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
    
    <script>
        // Sidebar Toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        const contentWrapper = document.querySelector('.content-wrapper');
        
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            
            if (sidebar.classList.contains('active')) {
                contentWrapper.style.marginLeft = '250px';
                contentWrapper.style.width = 'calc(100% - 250px)';
            } else {
                contentWrapper.style.marginLeft = '0';
                contentWrapper.style.width = '100%';
            }
        });
        
        // Check if on mobile and adjust sidebar
        function checkWidth() {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('active');
                contentWrapper.style.marginLeft = '0';
                contentWrapper.style.width = '100%';
            } else {
                sidebar.classList.add('active');
                contentWrapper.style.marginLeft = '250px';
                contentWrapper.style.width = 'calc(100% - 250px)';
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
    </script>
</body>
</html>
    <!-- Help Modal -->
    <div id="helpModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-question-circle"></i> HITNOT System User Guide</h2>
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
