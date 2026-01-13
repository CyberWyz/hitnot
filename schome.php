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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/home-modern.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Security Personnel Dashboard</title>
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

        /* Dashboard Options - Exact copy from home.php */
        .dashboard-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }

        .dashboard-option {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: 0 4px 20px var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .dashboard-option::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, var(--accent-teal), var(--primary-dark));
        }

        .dashboard-option:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px var(--shadow);
        }

        .dashboard-option h3 {
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .dashboard-option p {
            color: var(--text-dark);
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .dashboard-option i {
            font-size: 3rem;
            color: var(--accent-teal);
            margin-bottom: 1rem;
            display: block;
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
            .dashboard-options {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .dashboard-option {
                padding: 1.5rem;
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

        .dashboard-option {
            animation: scaleIn 0.7s ease-out 0.6s both;
        }

        .btn,
        .nav-btn {
            animation: scaleIn 0.5s ease-out 0.8s both;
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
                    <a href="schome.php" class="active">
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
                    <h1>Security Personnel Dashboard</h1>
                </div>
                <div class="user-info">
                    <span>Welcome, <b><?php echo htmlspecialchars($res_name); ?></b></span>
                    <i class="fas fa-user-circle" style="font-size: 24px;"></i>
                </div>
            </div>
        
            <!-- Main Content -->
            <div class="main-content">
                <!-- Quick Actions -->
                <div class="main-box">
                    <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                    <div class="dashboard-options">
                        <div class="dashboard-option" onclick="window.location.href='missingassets.php'">
                            <i class="fas fa-search"></i>
                            <h3>Missing Assets</h3>
                            <p>View and manage missing assets</p>
                        </div>
                        <div class="dashboard-option" onclick="window.location.href='regAsset.php'">
                            <i class="fas fa-plus-circle"></i>
                            <h3>Register New Asset</h3>
                            <p>Add a new asset to the system</p>
                        </div>
                        <div class="dashboard-option" onclick="window.location.href='blacklistedassets.php'">
                            <i class="fas fa-ban"></i>
                            <h3>Blacklisted Assets</h3>
                            <p>View and manage blacklisted assets</p>
                        </div>
                        <div class="dashboard-option" onclick="window.location.href='verifyassets.php'">
                            <i class="fas fa-check-circle"></i>
                            <h3>Verify Asset</h3>
                            <p>Verify and check asset status</p>
                        </div>
                    </div>
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