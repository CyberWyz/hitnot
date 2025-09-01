<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Welcome to Asset Card System</title>
    <script src="https://cdn.jsdelivr.net/npm/tsparticles@2.0.6/tsparticles.min.js"></script>
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
        
        
        
        /* Content Wrapper */
        .content-wrapper {
            margin-left: 100px;
            marginright: 100px;
            width: calc(100% - 250px);
            padding: 1.5rem;
        }
        
        /* Welcome Page Specific Styles */
        .welcome-container {
            background-color: white;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 2rem;
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .welcome-container header {
            font-size: 2rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 1rem;
        }
        
        .user-type {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
            margin: 2rem 0;
        }
        
        .user-type .btn {
            flex: 1;
            min-width: 200px;
            padding: 1rem;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        
        .user-type .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .about-section {
            text-align: left;
            margin-top: 2rem;
            padding: 1.5rem;
            background-color: #f8f9fc;
            border-radius: 0.35rem;
            border-left: 4px solid var(--info);
        }
        
        .about-section h2 {
            color: var(--dark);
            margin-top: 0;
        }
        
        .about-section p {
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        
        /* Button Styles */
        .btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.35rem;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            background-color: #2e59d9;
        }
        
        /* Dark mode support */
        body.dark-mode {
            background-color: #121212;
            color: #e0e0e0;
        }
        
        body.dark-mode .welcome-container {
            background-color: #1e1e1e;
        }
        
        body.dark-mode .about-section {
            background-color: #2d2d2d;
        }
        
        body.dark-mode .about-section h2 {
            color: #e0e0e0;
        }
        
        body.dark-mode .welcome-container header {
            color: var(--info);
        }
        
        /* TSParticles Background */
        #tsparticles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
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
            
            .user-type .btn {
                min-width: 100%;
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
    </style>
</head>
<body>
    <div class="admin-container">
        
        
        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- TSParticles Background -->
            <div id="tsparticles"></div>
            
            <!-- Main Content -->
            <div class="welcome-container">
                <header>Welcome to the Smart Tag Asset Management System</header>
                <div class="user-type">
                    <button class="btn" onclick="window.location.href='index.php'">
                        <i class="fas fa-user-graduate"></i> Student/Staff Member
                    </button>
                    <button class="btn" onclick="window.location.href='sclogin.php'">
                        <i class="fas fa-shield-alt"></i> Security Officer
                    </button>
                    <a href="adminlogin.php?fresh=1" class="btn">
                        <i class="fas fa-user-cog"></i> Admin Portal
                    </a>
                </div>

                <!-- About Section -->
                <div class="about-section">
                    <h2>About the Asset Card System</h2>
                    <p>
                        The Smart Tag Asset Management System System is a comprehensive platform designed to manage and track assets for students, staff, and security personnel. 
                        It provides a seamless experience for registering assets, generating QR codes, and ensuring the security of your belongings.
                    </p>
                    <p>
                        Whether you're a student, staff member, or security officer, this system is tailored to meet your needs. 
                        Log in to your respective portal to get started!
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // TSParticles Initialization
        tsParticles.load("tsparticles", {
            fpsLimit: 60,
            interactivity: {
                events: {
                    onClick: { enable: true, mode: "push" },
                    onHover: { enable: true, mode: "repulse" },
                },
                modes: {
                    push: { quantity: 4 },
                    repulse: { distance: 200, duration: 0.4 },
                },
            },
            particles: {
                color: { value: "#4e73df" },
                links: {
                    color: "#4e73df",
                    distance: 150,
                    enable: true,
                    opacity: 0.5,
                    width: 1,
                },
                move: {
                    direction: "none",
                    enable: true,
                    outModes: "bounce",
                    random: false,
                    speed: 2,
                    straight: false,
                },
                number: { density: { enable: true, area: 800 }, value: 80 },
                opacity: { value: 0.5 },
                shape: { type: "circle" },
                size: { value: { min: 1, max: 5 } },
            },
            detectRetina: true,
        });

        // Sidebar Toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        const contentWrapper = document.querySelector('.content-wrapper');
        
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
        const themeToggle = document.createElement('button');
        themeToggle.id = 'theme-toggle';
        themeToggle.className = 'btn nav-btn';
        themeToggle.innerHTML = '<i class="fas fa-moon"></i> <span>Dark Mode</span>';
        document.querySelector('.content-wrapper').prepend(themeToggle);

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
    </script>
</body>
</html>