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
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Edit Profile</title>
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
        
        .field {
            margin-bottom: 1rem;
        }
        
        .field label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .field input[type="text"],
        .field input[type="email"],
        .field input[type="password"],
        .field input[type="tel"],
        .field select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d3e2;
            border-radius: 0.35rem;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .field input[type="text"]:focus,
        .field input[type="email"]:focus,
        .field input[type="password"]:focus,
        .field input[type="tel"]:focus,
        .field select:focus {
            border-color: var(--primary);
            outline: none;
        }
        
        .field input[readonly] {
            background-color: #f8f9fc;
            cursor: not-allowed;
        }
        
        .btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.35rem;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #2e59d9;
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
        
        /* Form layout */
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .form-col {
            flex: 1;
            min-width: 250px;
        }
        
        /* Dark mode support */
        body.dark-mode {
            background-color: #121212;
            color: #e0e0e0;
        }
        
        body.dark-mode .main-box {
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
        
        body.dark-mode .field label {
            color: #e0e0e0;
        }
        
        body.dark-mode .field input[type="text"],
        body.dark-mode .field input[type="email"],
        body.dark-mode .field input[type="password"],
        body.dark-mode .field input[type="tel"],
        body.dark-mode .field select {
            background-color: #2d2d2d;
            border-color: #444;
            color: #e0e0e0;
        }
        
        body.dark-mode .field input[readonly] {
            background-color: #333;
        }
        
        body.dark-mode .main-box h2 {
            color: #e0e0e0;
            border-bottom-color: #444;
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
                    <a href="#" id="helpButton">
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