<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Welcome to Asset Card System</title>
    <script src="https://cdn.jsdelivr.net/npm/tsparticles@2.0.6/tsparticles.min.js"></script>
</head>
<body>
    <!-- TSParticles Background -->
    <div id="tsparticles" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></div>

    <!-- Dark Mode Toggle Button -->
    <button id="theme-toggle" class="btn theme-toggle-btn">Toggle Dark Mode</button>

    <!-- Main Container -->
    <div class="container">
        <div class="box form-box welcome-container">
            <header>Welcome to the Asset Card System</header>
            <div class="user-type">
                <div class="btn" onclick="window.location.href='index.php'">Student/Staff Member</div>
                <div class="btn" onclick="window.location.href='sclogin.php'">Security Officer</div>
                <div class="btn" onclick="window.location.href='admin.php'">Admin</div>
            </div>

            <!-- About Section -->
            <div class="about-section">
                <h2>About the Asset Card System</h2>
                <p>
                    The Asset Card System is a comprehensive platform designed to manage and track assets for students, staff, and security personnel. 
                    It provides a seamless experience for registering assets, generating QR codes, and ensuring the security of your belongings.
                </p>
                <p>
                    Whether you're a student, staff member, or security officer, this system is tailored to meet your needs. 
                    Log in to your respective portal to get started!
                </p>
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
                color: { value: "#ffffff" },
                links: {
                    color: "#ffffff",
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

        // Dark Mode Toggle
        const themeToggle = document.getElementById('theme-toggle');
        const body = document.body;

        // Check for saved theme in localStorage
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            body.classList.add(savedTheme);
        }

        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            const isDarkMode = body.classList.contains('dark-mode');
            localStorage.setItem('theme', isDarkMode ? 'dark-mode' : '');
        });

        // Button Animations
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(button => {
            button.classList.add('btn-animate');
        });
    </script>
</body>
</html>
