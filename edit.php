<?php
session_start();
include("php/config.php");

if (!isset($_SESSION['valid'])) {
    header("Location: index.php");
    exit;
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

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $school = $_POST['school'];

    mysqli_query($con, "UPDATE users SET Username='$username', Lastname='$lastname', Email='$email', Phone='$phone', School='$school' WHERE Id=$id") or die("Error Occurred");

    header("Location: edit.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Edit Profile</title>
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
            max-width: 1000px;
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
        }

        .main-box h2 {
            color: var(--text-dark);
            margin-bottom: 2rem;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .main-box h2 i {
            color: var(--accent-teal);
        }

        /* Photo Container */
        .photo-container {
            text-align: center;
            margin: 2rem 0;
            padding: 2rem;
            background: linear-gradient(-45deg, rgba(75, 100, 141, 0.3), rgba(65, 115, 124, 0.3), rgba(75, 100, 141, 0.3), rgba(65, 115, 124, 0.3));
            background-size: 400% 400%;
            animation: gradientBG 12s ease infinite;
            border-radius: var(--border-radius);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
        }

        .photo-container img {
            width: 100%;
            max-width: 500px;
            height: 400px;
            border-radius: 15px;
            object-fit: cover;
            border: 5px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.25);
            transition: var(--transition);
            position: relative;
            z-index: 2;
        }

        .photo-container img:hover {
            transform: scale(1.02);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
        }

        .photo-container i {
            font-size: 150px;
            color: var(--accent-teal);
            opacity: 0.7;
            position: relative;
            z-index: 2;
        }

        /* Form Styles */
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .field {
            display: flex;
            flex-direction: column;
        }

        .field label {
            color: var(--text-dark);
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .field input {
            padding: 0.75rem 1rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: var(--border-radius);
            font-size: 0.95rem;
            background: rgba(255, 255, 255, 0.9);
            transition: var(--transition);
        }

        .field input:focus {
            outline: none;
            border-color: var(--accent-teal);
            box-shadow: 0 0 0 3px rgba(65, 115, 124, 0.1);
        }

        .field input[readonly] {
            background: rgba(200, 200, 200, 0.3);
            cursor: not-allowed;
        }

        .field input[type="submit"] {
            background: linear-gradient(135deg, var(--accent-teal), var(--primary-dark));
            color: white;
            border: none;
            padding: 1rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
        }

        .field input[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(65, 115, 124, 0.3);
        }

        .btn {
            background: linear-gradient(135deg, var(--accent-teal), var(--primary-dark));
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(65, 115, 124, 0.3);
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
        body.dark-mode .field label {
            color: var(--text-light);
        }

        body.dark-mode .field input {
            background: rgba(45, 45, 45, 0.7);
            color: var(--text-light);
            border-color: rgba(255, 255, 255, 0.1);
        }

        body.dark-mode .photo-container {
            background: linear-gradient(135deg, rgba(30, 30, 30, 0.6), rgba(45, 45, 45, 0.6));
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        body.dark-mode .photo-container img {
            border: 5px solid rgba(255, 255, 255, 0.2);
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

            .form-row {
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
                    <a href="home.php">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="edit.php" class="active">
                        <i class="fas fa-user-edit"></i> Edit Profile
                    </a>
                </li>
                <li>
                    <a href="home.php#help">
                        <i class="fas fa-question-circle"></i> Help
                    </a>
                </li>
                <li>
                    <a href="index.php">
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
                    <h1>Edit Profile</h1>
                </div>
                <div class="user-info">
                    <!-- Theme toggle as a proper button in the nav bar -->
                    <button id="theme-toggle" class="nav-btn">
                        <i class="fas fa-moon"></i>
                        <span>Dark Mode</span>
                    </button>
                    <span><?php echo $res_Uname . ' ' . $res_Lastname; ?></span>
                    
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="main-content">
                <div class="main-box">
                    <h2><i class="fas fa-user-edit"></i> Update Your Profile Information</h2>
                    
                    <div class="photo-container">
                        <?php if (!empty($res_Photo)): ?>
                            <img src="<?php echo $res_Photo; ?>" alt="Profile Photo">
                        <?php else: ?>
                            <i class="fas fa-user-circle" style="font-size: 100px; color: #4e73df;"></i>
                        <?php endif; ?>
                    </div>
                    
                    <form action="" method="post">
                        <div class="form-row">
                            <div class="form-col">
                                <div class="field">
                                    <label for="username">Username</label>
                                    <input type="text" name="username" id="username" value="<?php echo $res_Uname; ?>" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="field">
                                    <label for="lastname">Last Name</label>
                                    <input type="text" name="lastname" id="lastname" value="<?php echo $res_Lastname; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="field">
                                    <label for="email">Email</label>
                                    <input type="email" name="email" id="email" value="<?php echo $res_Email; ?>" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="field">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" name="phone" id="phone" value="<?php echo $res_Phone; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="field">
                                    <label for="reg_number">Registration Number</label>
                                    <input type="text" name="reg_number" id="reg_number" value="<?php echo $res_Reg_Number; ?>" readonly>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="field">
                                    <label for="school">School</label>
                                    <input type="text" name="school" id="school" value="<?php echo $res_School; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="field">
                            <input type="submit" name="submit" value="Update Profile" class="btn">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
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
        
        if (themeToggle) {
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
        }
    </script>
</body>
</html>