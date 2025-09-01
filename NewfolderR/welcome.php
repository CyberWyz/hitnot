<!-- filepath: c:\xampp\htdocs\prot2\welcome.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Smart Tag Asset Management</title>
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/welcome.css">
    <link rel="stylesheet" href="responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="NewfolderR/css/aos.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(-45deg, #4fc3f7, #23272e, #4fc3f7, #23272e);
            background-size: 400% 400%;
            animation: gradientBG 12s ease infinite;
        }
        @keyframes gradientBG {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }
        .landing-hero {
            min-height: 60vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-align: center;
            position: relative;
            overflow: hidden;
            margin-bottom: 0;
        }
        .landing-hero img {
            width: 90px;
            margin-bottom: 18px;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.15));
        }
        .landing-hero h1 {
            font-size: 2.5em;
            margin-bottom: 12px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .landing-hero p {
            font-size: 1.2em;
            margin-bottom: 32px;
            color: #e0f7fa;
        }
        .short-about {
            background: #fff;
            margin: -30px auto 0 auto;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.07);
            max-width: 600px;
            padding: 24px 18px 18px 18px;
            position: relative;
            z-index: 2;
        }
        .short-about h2 {
            margin-top: 0;
            font-size: 1.5em;
            color: #23272e;
        }
        .short-about p {
            color: #444;
            font-size: 1.05em;
        }
        .footer-sections {
            background: #23272e;
            padding: 0;
            margin-top: 32px;
            border-top: 2px solid #23272e;
        }
        .footer-sections .footer-cards {
            display: flex;
            flex-wrap: nowrap;
            justify-content: space-between;
            align-items: stretch;
            gap: 0;
            max-width: 100vw;
            margin: 0;
            padding: 0;
        }
        .footer-card {
            background: transparent;
            color: #fff;
            border-radius: 0;
            box-shadow: none;
            flex: 1 1 0;
            min-width: 0;
            max-width: none;
            padding: 24px 18px;
            margin-bottom: 0;
            border-right: 1px solid #333;
            min-height: 220px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .footer-card:last-child {
            border-right: none;
        }
        .footer-card h2, .footer-card h4 {
            color: #fff;
            margin-top: 0;
        }
        .footer-card h2 {
            font-size: 1.15em;
            margin-bottom: 12px;
        }
        .footer-card h4 {
            color: #4fc3f7;
            margin-bottom: 6px;
            font-size: 1em;
        }
        .footer-card p, .footer-card li {
            color: #e0f7fa;
            font-size: 0.98em;
            margin-bottom: 8px;
        }
        .footer-card form input,
        .footer-card form textarea {
            width: 100%;
            margin-bottom: 8px;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #444;
            font-size: 0.98em;
            background: #23272e;
            color: #fff;
        }
        .footer-card form input::placeholder,
        .footer-card form textarea::placeholder {
            color: #b0bec5;
        }
        .footer-card form button {
            background: #4fc3f7;
            color: #23272e;
            border: none;
            padding: 10px 0;
            border-radius: 6px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }
        .footer-contact-info {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .footer-contact-info h4 {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        @media (max-width: 1000px) {
            .footer-sections .footer-cards {
                flex-direction: column;
                align-items: stretch;
            }
            .footer-card {
                border-right: none;
                border-bottom: 1px solid #333;
            }
            .footer-card:last-child {
                border-bottom: none;
            }
        }
        footer {
            width: 100%;
            background: #23272e;
            color: #fff;
            text-align: center;
            padding: 18px 0 12px 0;
            font-size: 1em;
            letter-spacing: 0.5px;
            position: relative;
            bottom: 0;
            margin-top: 0;
        }
        @media (max-width: 600px) {
            .landing-hero h1 { font-size: 1.5em; }
            .short-about { padding: 12px 4px; }
            .footer-card { padding: 12px 4px; }
            footer { font-size: 0.95em; }
        }
    </style>
</head>
<body>
    <div class="landing-hero" data-aos="fade-up">
        <img src="uploads/icons8-laptop-80.png" alt="System Logo" data-aos="zoom-in" data-aos-delay="200">
        <h1 data-aos="fade-down" data-aos-delay="400">Smart Tag Asset Management System</h1>
        <p data-aos="fade-up" data-aos-delay="600">
            Securely manage, track, and verify assets for students, staff, and security personnel.
        </p>
        <div style="position:absolute;bottom:24px;left:50%;transform:translateX(-50%);" data-aos="fade-up" data-aos-delay="1000">
            <i class="fas fa-angle-down" style="font-size:2em;color:#fff;opacity:0.7;"></i>
        </div>
    </div>

    <div class="short-about" data-aos="fade-up" data-aos-delay="1200">
        <h2>About</h2>
        <p>
            The Smart Tag Asset Management System is a modern platform for registering, tracking, and verifying assets. 
            Enjoy seamless QR code generation, real-time verification, and robust security for your belongings.
        </p>
    </div>

    <!-- Footer Sections (FAQ, Help & Feedback, Contact) -->
    <div class="footer-sections">
        <div class="footer-cards">
            <!-- FAQ Card -->
            <div class="footer-card" data-aos="fade-up">
                <h2>Frequently Asked Questions</h2>
                <div>
                    <h4>How do I register an asset?</h4>
                    <p>Log in to your portal and use the "Register New Asset" feature. Fill in the required details and follow the prompts to complete registration.</p>
                </div>
                <div>
                    <h4>What if my asset is lost or stolen?</h4>
                    <p>Report the asset as missing in your portal. The system will flag it and notify security personnel for further action.</p>
                </div>
                <div>
                    <h4>How do I reset my password?</h4>
                    <p>Use the "Forgot Password" link on the login page or contact your administrator for assistance.</p>
                </div>
            </div>
            <!-- Help & Feedback Card -->
            <div class="footer-card" data-aos="fade-up" data-aos-delay="100">
                <h2>Help & Feedback</h2>
                <p>
                    Need assistance or want to share your experience? Fill out the form below and our support team will get back to you!
                </p>
                <form>
                    <input type="text" placeholder="Your Name" required>
                    <input type="email" placeholder="Your Email" required>
                    <textarea placeholder="How can we help you?" required style="min-height:50px;"></textarea>
                    <button type="submit">Send Feedback</button>
                </form>
            </div>
            <!-- Contact Card -->
            <div class="footer-card" data-aos="fade-up" data-aos-delay="200">
                <h2>Contact Us</h2>
                <div class="footer-contact-info">
                    <div>
                        <h4><i class="fas fa-envelope"></i> Email</h4>
                        <p>support@smarttagassetsystem.com</p>
                    </div>
                    <div>
                        <h4><i class="fas fa-phone"></i> Phone</h4>
                        <p>+1 234 567 8900</p>
                    </div>
                    <div>
                        <h4><i class="fas fa-map-marker-alt"></i> Address</h4>
                        <p>123 Main Street, City, Country</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        &copy; <?php echo date('Y'); ?> Smart Tag Asset Management System. All rights reserved.
    </footer>
    <script src="NewfolderR/js/aos.js"></script>
    <script>
        AOS.init({
            duration: 900,
            once: true
        });
    </script>
</body>
</html>