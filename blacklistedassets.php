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
    $officer_id = $result['officer_id'];
    $officer_lastname = $result['lastname'];
}

$message = "";

// Handle unblacklisting an asset
if (isset($_POST['unblacklist']) && isset($_POST['asset_id'])) {
    $asset_id = mysqli_real_escape_string($con, $_POST['asset_id']);

    // Update the asset status
    $update_query = mysqli_query($con, "UPDATE assets SET
                                        AssetStatus = NULL,
                                        blacklist_reason = NULL,
                                        date_blacklisted = NULL,
                                        rfid_status = 'active'
                                        WHERE serial_number = '$asset_id'");

    if ($update_query) {
        $message = "<div class='message success'><p>Asset has been successfully removed from blacklist.</p></div>";
    } else {
        $message = "<div class='message error'><p>Failed to remove asset from blacklist: " . mysqli_error($con) . "</p></div>";
    }
}

// Get all blacklisted assets with error handling
$assets_query = mysqli_query($con, "SELECT a.*, u.Username, u.Lastname
                                   FROM assets a
                                   LEFT JOIN users u ON a.reg_number = u.Reg_Number
                                   WHERE a.AssetStatus = 'Blacklisted'
                                   ORDER BY a.date_blacklisted DESC");

if (!$assets_query) {
    $message = "<div class='message error'><p>Database error: " . mysqli_error($con) . "</p></div>";
    $assets_query = mysqli_query($con, "SELECT 1 as dummy LIMIT 0"); // Empty result set
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
    <title>Blacklisted Assets - Security Dashboard</title>
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

        /* Modern Table Styling */
        .assets-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.9);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 4px 20px var(--shadow);
            margin-top: 1.5rem;
        }

        .assets-table thead {
            background: linear-gradient(135deg, var(--primary-dark), var(--accent-teal));
            color: var(--text-light);
        }

        .assets-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .assets-table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            vertical-align: top;
        }

        .assets-table tbody tr {
            transition: var(--transition);
        }

        .assets-table tbody tr:hover {
            background: rgba(75, 100, 141, 0.05);
            transform: scale(1.01);
        }

        .assets-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Asset Image */
        .asset-image {
            max-width: 80px;
            max-height: 80px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .asset-image:hover {
            transform: scale(1.1);
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-blacklisted {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }

        .status-active {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        /* RFID Code Styling */
        .rfid-code {
            background: rgba(0, 0, 0, 0.05);
            padding: 0.5rem;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            color: var(--text-dark);
            border: 1px solid rgba(0, 0, 0, 0.1);
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

        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #c82333, #bd2130);
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
            .assets-table {
                font-size: 0.8rem;
            }
            .assets-table th,
            .assets-table td {
                padding: 0.5rem;
            }
            .asset-image {
                max-width: 60px;
                max-height: 60px;
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

        .assets-table {
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

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-dark);
            opacity: 0.7;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--accent-teal);
            margin-bottom: 1rem;
            display: block;
        }

        .empty-state h3 {
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }

        .empty-state p {
            font-size: 0.9rem;
        }

        /* Asset Details */
        .asset-details {
            margin-bottom: 0.5rem;
        }

        .asset-details strong {
            color: var(--text-dark);
            font-weight: 600;
        }

        .asset-details br {
            margin-bottom: 0.25rem;
        }

        /* Owner Info */
        .owner-info {
            background: rgba(75, 100, 141, 0.05);
            padding: 0.75rem;
            border-radius: 8px;
            border-left: 3px solid var(--primary-dark);
        }

        .owner-info strong {
            color: var(--primary-dark);
        }

        /* Blacklist Info */
        .blacklist-info {
            background: rgba(220, 53, 69, 0.05);
            padding: 0.75rem;
            border-radius: 8px;
            border-left: 3px solid #dc3545;
        }

        .blacklist-info strong {
            color: #dc3545;
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
                    <a href="regAsset.php">
                        <i class="fas fa-plus-circle"></i> Register Asset
                    </a>
                </li>
                <li>
                    <a href="blacklistedassets.php" class="active">
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
                    <h1>Blacklisted Assets</h1>
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
                    <h2><i class="fas fa-ban"></i> Blacklisted Assets Management</h2>
                    <p style="color: var(--text-dark); opacity: 0.8; margin-bottom: 1.5rem;">
                        View and manage assets that have been blacklisted from the system.
                    </p>

                    <?php if ($assets_query && mysqli_num_rows($assets_query) > 0): ?>
                        <div style="overflow-x: auto;">
                            <table class="assets-table">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-image"></i> Asset</th>
                                        <th><i class="fas fa-wifi"></i> RFID Info</th>
                                        <th><i class="fas fa-user"></i> Owner</th>
                                        <th><i class="fas fa-exclamation-triangle"></i> Blacklist Details</th>
                                        <th><i class="fas fa-cogs"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($asset = mysqli_fetch_assoc($assets_query)): ?>
                                        <tr>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 1rem;">
                                                    <?php if (!empty($asset['picture']) && file_exists($asset['picture'])): ?>
                                                        <img src="<?php echo htmlspecialchars($asset['picture']); ?>" alt="Asset Image" class="asset-image">
                                                    <?php else: ?>
                                                        <div style="width: 80px; height: 80px; background: rgba(0,0,0,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-image" style="font-size: 2rem; color: rgba(0,0,0,0.3);"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="asset-details">
                                                        <div><strong>Serial:</strong> <?php echo htmlspecialchars($asset['serial_number']); ?></div>
                                                        <div><strong>Model:</strong> <?php echo htmlspecialchars($asset['item_model']); ?></div>
                                                        <div><strong>Description:</strong> <?php echo htmlspecialchars($asset['item_description']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="rfid-code"><?php echo htmlspecialchars($asset['rfid_uid']); ?></div>
                                                <div style="margin-top: 0.5rem;">
                                                    <span class="status-badge status-blacklisted">BLACKLISTED</span>
                                                </div>
                                                <div style="margin-top: 0.5rem; font-size: 0.8rem; color: var(--text-dark); opacity: 0.7;">
                                                    <i class="fas fa-clock"></i>
                                                    Last Scanned: <?php echo !empty($asset['last_scanned']) ?
                                                        date('M d, Y H:i', strtotime($asset['last_scanned'])) : 'Never'; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($asset['Username']) && !empty($asset['Lastname'])): ?>
                                                    <div class="owner-info">
                                                        <div><strong>Name:</strong> <?php echo htmlspecialchars($asset['Username'] . ' ' . $asset['Lastname']); ?></div>
                                                        <div><strong>Reg Number:</strong> <?php echo htmlspecialchars($asset['reg_number']); ?></div>
                                                    </div>
                                                <?php else: ?>
                                                    <div style="color: var(--text-dark); opacity: 0.6; font-style: italic;">
                                                        <i class="fas fa-user-slash"></i> No owner information
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="blacklist-info">
                                                    <div><strong>Date:</strong> <?php echo !empty($asset['date_blacklisted']) ?
                                                        date('M d, Y H:i', strtotime($asset['date_blacklisted'])) : 'Unknown'; ?></div>
                                                    <div style="margin-top: 0.5rem;">
                                                        <strong>Reason:</strong><br>
                                                        <?php echo !empty($asset['blacklist_reason']) ?
                                                            htmlspecialchars($asset['blacklist_reason']) : 'No reason provided'; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <form action="" method="post" onsubmit="return confirm('Are you sure you want to remove this asset from the blacklist? This action cannot be undone.');" style="display: inline;">
                                                    <input type="hidden" name="asset_id" value="<?php echo htmlspecialchars($asset['serial_number']); ?>">
                                                    <button type="submit" name="unblacklist" class="btn btn-success" style="width: 100%; margin-bottom: 0.5rem;">
                                                        <i class="fas fa-undo"></i> Remove from Blacklist
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-shield-alt"></i>
                            <h3>No Blacklisted Assets</h3>
                            <p>All assets are currently active and not blacklisted.</p>
                        </div>
                    <?php endif; ?>
                </div>
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
    </script>
</body>
</html>