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
    <link rel="stylesheet" href="style/home-modern.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Home</title>
   
</head>
<body>
    <!-- Particle Background -->
    <div class="particles-container"></div>

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
    <script src="js/particles.js"></script>
    <script src="js/home.js"></script>
</body>
</html>
