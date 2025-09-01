<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Smart Tag Asset Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(-45deg, #4fc3f7, #23272e, #4fc3f7, #23272e);
            background-size: 400% 400%;
            animation: gradientBG 12s ease infinite;
            scroll-behavior: smooth;
        }
        @keyframes gradientBG {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }
        .get-started-btn {
    position: fixed;
    top: 150px;
    right: 20px;
    padding: 24px 44px;
    background: #00aeffff;
    color: #23272e;
    text-decoration: none;
    border-radius: 30px;
    font-weight: 700;
    box-shadow: 0 4px 15px rgba(79, 195, 247, 0.4);
    z-index: 1000;
    animation: float 3s ease-in-out infinite;
    transition: all 0.3s ease;
}

.get-started-btn:hover {
    background: #29b6f6;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(41, 182, 246, 0.5);
}

@keyframes float {
    0% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-8px);
    }
    100% {
        transform: translateY(0px);
    }
}
        .landing-hero {
            min-height: 100vh;
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
            margin: 40px auto;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.07);
            max-width: 800px;
            padding: 40px;
            position: relative;
            z-index: 2;
        }
        .short-about h2 {
            margin-top: 0;
            font-size: 1.8em;
            color: #23272e;
            text-align: center;
            margin-bottom: 25px;
        }
        .short-about p {
            color: #444;
            font-size: 1.1em;
            line-height: 1.6;
        }
        
        /* New Features Section */
        .features-section {
            padding: 60px 20px;
            background: rgba(255, 255, 255, 0.9);
            margin: 40px 0;
        }
        .section-title {
            text-align: center;
            font-size: 2.2em;
            margin-bottom: 40px;
            color: #23272e;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .feature-card {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .feature-icon {
            font-size: 2.5em;
            color: #4fc3f7;
            margin-bottom: 20px;
            text-align: center;
        }
        .feature-card h3 {
            color: #23272e;
            font-size: 1.4em;
            margin-bottom: 15px;
        }
        .feature-card p {
            color: #555;
            line-height: 1.6;
        }
        
        /* Testimonials Section */
        .testimonials {
            padding: 60px 20px;
            background: #23272e;
            color: #fff;
        }
        .testimonials .section-title {
            color: #fff;
        }
        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .testimonial-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 30px;
            border-left: 4px solid #4fc3f7;
        }
        .testimonial-text {
            font-style: italic;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .testimonial-author {
            display: flex;
            align-items: center;
        }
        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #4fc3f7;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
        }
        .author-info h4 {
            margin: 0;
            color: #4fc3f7;
        }
        .author-info p {
            margin: 0;
            color: #b0bec5;
            font-size: 0.9em;
        }
        
        /* Stats Section */
        .stats-section {
            padding: 60px 20px;
            background: linear-gradient(45deg, #4fc3f7, #29b6f6);
            color: #fff;
            text-align: center;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            max-width: 1000px;
            margin: 0 auto;
        }
        .stat-item h3 {
            font-size: 2.5em;
            margin: 0 0 10px 0;
        }
        .stat-item p {
            margin: 0;
            font-size: 1.1em;
        }
        
        /* CTA Section */
        .cta-section {
            padding: 80px 20px;
            text-align: center;
            background: #fff;
        }
        .cta-content {
            max-width: 800px;
            margin: 0 auto;
        }
        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .cta-button {
            padding: 15px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .cta-primary {
            background: #4fc3f7;
            color: #23272e;
        }
        .cta-primary:hover {
            background: #29b6f6;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(41, 182, 246, 0.4);
        }
        .cta-secondary {
            background: transparent;
            color: #23272e;
            border: 2px solid #4fc3f7;
        }
        .cta-secondary:hover {
            background: #4fc3f7;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(41, 182, 246, 0.4);
        }

        /* Scroll to top button */
        .scroll-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #4fc3f7;
            color: #23272e;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
        }
        
        .scroll-to-top.visible {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .scroll-to-top:hover {
            background: #29b6f6;
            transform: translateY(-5px);
        }

        .footer-sections {
            background: #23272e;
            padding: 0;
            margin-top: 60px;
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
            .short-about { padding: 20px; }
            .footer-card { padding: 20px; }
            footer { font-size: 0.95em; }
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            .scroll-to-top {
                bottom: 20px;
                right: 20px;
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <a href="idenify.php" class="get-started-btn">Get Started</a>
    <!-- Scroll to top button -->
    <div class="scroll-to-top" id="scrollToTop">
        <i class="fas fa-arrow-up"></i>
    </div>

    <div class="landing-hero" id="top">
        <img src="https://cdn-icons-png.flaticon.com/512/7016/7016572.png" alt="System Logo">
        <h1>Smart Tag Asset Management System</h1>
        <p>
            Securely manage, track, and verify assets for students, staff, and security personnel.
        </p>
        <div style="position:absolute;bottom:24px;left:50%;transform:translateX(-50%);">
            <a href="#features" style="color: inherit;">
                <i class="fas fa-angle-down" style="font-size:2em;color:#fff;opacity:0.7;"></i>
            </a>
        </div>
    </div>

    <div class="short-about">
        <h2>About Our System</h2>
        <p>
            The Smart Tag Asset Management System is a modern platform for registering, tracking, and verifying assets. 
            Enjoy seamless QR code generation, real-time verification, and robust security for your belongings. Our system
            is designed to provide institutions with a comprehensive solution to manage valuable assets efficiently and prevent loss.
        </p>
        <p>
            With an intuitive user interface and powerful backend, our system simplifies asset management while providing
            detailed reporting and analytics to help organizations make informed decisions about their resources.
        </p>
    </div>

    <!-- Features Section -->
    <section id="features" class="features-section">
        <h2 class="section-title">Key Features</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-qrcode"></i>
                </div>
                <h3>QR Code Tracking</h3>
                <p>Generate unique QR codes for each asset, enabling quick scanning and verification by authorized personnel.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-database"></i>
                </div>
                <h3>Centralized Database</h3>
                <p>Store all asset information in a secure, cloud-based database accessible from anywhere with proper authentication.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <h3>Real-time Alerts</h3>
                <p>Receive instant notifications for unauthorized asset movements, maintenance schedules, and security breaches.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Advanced Reporting</h3>
                <p>Generate detailed reports on asset status, usage patterns, and audit trails for better decision making.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Mobile Responsive</h3>
                <p>Access the system from any device - smartphones, tablets, or desktops with a consistent user experience.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Enhanced Security</h3>
                <p>Multi-level authentication and encryption protocols ensure your asset data remains secure and protected.</p>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <h2 class="section-title">What Our Users Say</h2>
        <div class="testimonial-grid">
            <div class="testimonial-card">
                <p class="testimonial-text">"This system has revolutionized how we manage our laboratory equipment. The QR code scanning makes inventory checks incredibly efficient."</p>
                <div class="testimonial-author">
                    <div class="author-avatar">JD</div>
                    <div class="author-info">
                        <h4>Dr. James Davidson</h4>
                        <p>Science Department Head</p>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <p class="testimonial-text">"As a security officer, the real-time alerts have been invaluable. We can now respond immediately to unauthorized asset movements."</p>
                <div class="testimonial-author">
                    <div class="author-avatar">MR</div>
                    <div class="author-info">
                        <h4>Michael Rodriguez</h4>
                        <p>Campus Security Director</p>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <p class="testimonial-text">"The reporting features have saved me countless hours. I can now generate asset reports with a few clicks instead of manual spreadsheet work."</p>
                <div class="testimonial-author">
                    <div class="author-avatar">SL</div>
                    <div class="author-info">
                        <h4>Sarah Johnson</h4>
                        <p>IT Administrator</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <h2 class="section-title">Our Impact</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <h3>5,000+</h3>
                <p>Assets Managed</p>
            </div>
            <div class="stat-item">
                <h3>98%</h3>
                <p>Reduction in Losses</p>
            </div>
            <div class="stat-item">
                <h3>250+</h3>
                <p>Organizations Served</p>
            </div>
            <div class="stat-item">
                <h3>24/7</h3>
                <p>Support Available</p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-content">
            <h2 class="section-title">Ready to Transform Your Asset Management?</h2>
            <p>Join thousands of organizations that have improved their asset tracking and security with our system.</p>
            <div class="cta-buttons">
                <a href="#" class="cta-button cta-primary">Request a Demo</a>
                <a href="#" class="cta-button cta-secondary">Contact Sales</a>
            </div>
        </div>
    </section>

    <!-- Footer Sections (FAQ, Help & Feedback, Contact) -->
    <div class="footer-sections">
        <div class="footer-cards">
            <!-- FAQ Card -->
            <div class="footer-card">
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
            <div class="footer-card">
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
            <div class="footer-card">
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
        &copy; 2023 Smart Tag Asset Management System. All rights reserved.
    </footer>

    <script>
        // Scroll to top functionality
        const scrollToTopBtn = document.getElementById('scrollToTop');
        
        // Show button when user scrolls down
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.add('visible');
            } else {
                scrollToTopBtn.classList.remove('visible');
            }
        });
        
        // Scroll to top when button is clicked
        scrollToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>