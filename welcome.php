<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/welcome.css">
    <link rel="stylesheet" href="responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Welcome to Asset Card System</title>
</head>
<body>
    <div class="admin-container">
        <div class="content-wrapper">
            <div id="tsparticles"></div>
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
                <div class="about-section">
                    <h2>About the Asset Card System</h2>
                    <p>
                        The Smart Tag Asset Management System is a comprehensive platform designed to manage and track assets for students, staff, and security personnel.
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
    <script src="https://cdn.jsdelivr.net/npm/tsparticles@2.0.6/tsparticles.min.js"></script>
    <script src="js/welcome.js"></script>
</body>
</html>