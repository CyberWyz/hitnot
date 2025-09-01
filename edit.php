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
    <link rel="stylesheet" href="style/home-modern.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Edit Profile</title>
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

    <script src="js/particles.js"></script>
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