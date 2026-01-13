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
    <link rel="stylesheet" href="style/home-modern.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Missing Assets - Security Dashboard</title>
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

        /* Modern Asset Cards */
        .assets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .asset-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: 0 4px 20px var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .asset-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, #e74a3b, #c82333);
        }

        .asset-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px var(--shadow);
        }

        .asset-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            gap: 1rem;
        }

        .asset-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #e74a3b, #c82333);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .asset-title {
            flex: 1;
        }

        .asset-title h3 {
            color: var(--text-dark);
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .asset-status {
            background: linear-gradient(135deg, #e74a3b, #c82333);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .asset-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .detail-item {
            background: rgba(231, 251, 249, 0.5);
            padding: 0.75rem;
            border-radius: 8px;
            border-left: 3px solid var(--accent-teal);
        }

        .detail-item strong {
            color: var(--accent-teal);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-item p {
            color: var(--text-dark);
            margin: 0.25rem 0 0 0;
            font-size: 0.9rem;
        }

        .student-info {
            background: rgba(75, 100, 141, 0.1);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary-dark);
        }

        .student-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .student-photo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--primary-dark);
            object-fit: cover;
        }

        .student-name {
            font-weight: 600;
            color: var(--text-dark);
        }

        .missing-info {
            background: rgba(231, 76, 60, 0.1);
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #e74a3b;
        }

        .missing-info strong {
            color: #e74a3b;
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

        .btn-recover {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .btn-recover:hover {
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
            .assets-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .asset-details {
                grid-template-columns: 1fr;
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

        .asset-card {
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

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            margin: 2% auto;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            box-shadow: 0 20px 40px var(--shadow);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-dark), var(--accent-teal));
            color: var(--text-light);
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.3rem;
        }

        .close {
            font-size: 28px;
            cursor: pointer;
            transition: var(--transition);
        }

        .close:hover {
            color: var(--primary-light);
        }

        .modal-body {
            padding: 1.5rem;
            flex: 1;
            overflow-y: auto;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-teal);
            box-shadow: 0 0 0 3px rgba(65, 115, 124, 0.1);
        }

        .recovery-notes {
            resize: vertical;
            min-height: 100px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-dark);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--accent-teal);
            margin-bottom: 1rem;
            display: block;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            opacity: 0.7;
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
                    <a href="missingassets.php" class="active">
                        <i class="fas fa-search"></i> Missing Assets
                    </a>
                </li>
                <li>
                    <a href="regAsset.php">
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
                    <h1>Missing Assets</h1>
                </div>
                <div class="user-info">
                    <span>Welcome, <b><?php echo htmlspecialchars($res_name); ?></b></span>
                    <i class="fas fa-user-circle" style="font-size: 24px;"></i>
                </div>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <?php echo $message; ?>

                <div class="main-box">
                    <h2><i class="fas fa-search"></i> Missing Assets Management</h2>

                    <?php if (mysqli_num_rows($query) > 0): ?>
                        <div class="assets-grid">
                            <?php while ($asset = mysqli_fetch_assoc($query)): ?>
                                <div class="asset-card">
                                    <div class="asset-header">
                                        <div class="asset-icon">
                                            <i class="fas fa-laptop"></i>
                                        </div>
                                        <div class="asset-title">
                                            <h3><?php echo htmlspecialchars($asset['item_description']); ?></h3>
                                            <div class="asset-status">Missing</div>
                                        </div>
                                    </div>

                                    <div class="asset-details">
                                        <div class="detail-item">
                                            <strong>Serial Number</strong>
                                            <p><?php echo htmlspecialchars($asset['serial_number']); ?></p>
                                        </div>
                                        <div class="detail-item">
                                            <strong>Model</strong>
                                            <p><?php echo htmlspecialchars($asset['item_model']); ?></p>
                                        </div>
                                        <div class="detail-item">
                                            <strong>RFID Tag</strong>
                                            <p><code><?php echo htmlspecialchars($asset['rfid_uid']); ?></code></p>
                                        </div>
                                        <div class="detail-item">
                                            <strong>Registration</strong>
                                            <p><?php echo htmlspecialchars($asset['reg_number']); ?></p>
                                        </div>
                                    </div>

                                    <?php if (!empty($asset['Username']) && !empty($asset['Lastname'])): ?>
                                        <div class="student-info">
                                            <div class="student-header">
                                                <?php if (!empty($asset['myphoto']) && file_exists($asset['myphoto'])): ?>
                                                    <img src="<?php echo htmlspecialchars($asset['myphoto']); ?>" alt="Student Photo" class="student-photo">
                                                <?php endif; ?>
                                                <div class="student-name">
                                                    <?php echo htmlspecialchars($asset['Username'] . ' ' . $asset['Lastname']); ?>
                                                </div>
                                            </div>
                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.85rem;">
                                                <div><strong>Email:</strong> <?php echo htmlspecialchars($asset['Email']); ?></div>
                                                <div><strong>Phone:</strong> <?php echo htmlspecialchars($asset['Phone']); ?></div>
                                                <div style="grid-column: span 2;"><strong>School:</strong> <?php echo htmlspecialchars($asset['School']); ?></div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="missing-info">
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 1rem;">
                                            <div>
                                                <strong>Date Reported:</strong><br>
                                                <?php echo !empty($asset['date_reported_missing']) ?
                                                    date('M d, Y H:i', strtotime($asset['date_reported_missing'])) : 'Unknown'; ?>
                                            </div>
                                            <div>
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
                                            </div>
                                        </div>
                                        <button class="btn btn-recover" onclick="openRecoveryModal('<?php echo $asset['serial_number']; ?>')">
                                            <i class="fas fa-check-circle"></i> Mark as Recovered
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-search"></i>
                            <h3>No Missing Assets</h3>
                            <p>All assets are currently accounted for. Great job maintaining security!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recovery Modal -->
    <div id="recoveryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-check-circle"></i> Mark Asset as Recovered</h3>
                <span class="close" onclick="closeRecoveryModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form action="" method="post">
                    <input type="hidden" id="asset_id" name="asset_id" value="">
                    <div class="form-group">
                        <label for="recovery_notes">Recovery Details:</label>
                        <textarea name="recovery_notes" id="recovery_notes" class="form-control recovery-notes" rows="4" placeholder="Enter details about how the asset was recovered..." required></textarea>
                    </div>
                    <button type="submit" name="recover_asset" class="btn btn-recover">
                        <i class="fas fa-check-circle"></i> Confirm Recovery
                    </button>
                </form>
            </div>
        </div>
    </div>

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

        // Modal functionality
        function openRecoveryModal(assetId) {
            document.getElementById('asset_id').value = assetId;
            document.getElementById('recoveryModal').style.display = 'block';
        }

        function closeRecoveryModal() {
            document.getElementById('recoveryModal').style.display = 'none';
            document.getElementById('recovery_notes').value = '';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('recoveryModal');
            if (event.target == modal) {
                closeRecoveryModal();
            }
        }
    </script>
</body>
</html>