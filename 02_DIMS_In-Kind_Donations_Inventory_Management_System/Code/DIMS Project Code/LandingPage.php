<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIMS | Donation Inventory Management System</title>
    <link rel="stylesheet" href="LandingPage.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
       <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="LandingPage.html" class="nav-logo">
                <i class="fas fa-warehouse nav-logo-icon"></i>
                <span>DIMS</span>
            </a>
            
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
            
            <ul class="nav-links" id="navLinks">
                <li><a href="#" class="nav-link active">Home</a></li>
                <li><a href="LoginPage.php" class="nav-link">Login</a></li>
                <li><a href="DonReg.php" class="nav-link">Register as Donor</a></li>
                <li><a href="ASReg.php" class="nav-link">Register as Staff/Volunteer</a></li>
            </ul>
        </div>
    </nav>

     <!-- Main Content -->
    <div class="landing-container">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
            <i class="fas fa-warehouse hero-logo"></i>
            <h1>Donation Inventory Management System</h1>
            <p>An innovative web-based solution designed to streamline in-kind donation management for local community centres. Our system helps optimize resource distribution, enhance transparency, and maximize aid impact.</p>
            <a href="LoginPage.php" class="cta-button">Access DIMS</a>
            </div>
        </section>

       <!-- Features Section -->
        <section class="features-section">
            <h2 class="section-title">Key Features</h2>
            <div class="feature-flex">
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-qrcode feature-icon"></i>
                    <h3>QR Code Tracking</h3>
                    <p>Generate and scan QR codes for each inventory item, enabling real-time tracking from donation intake to distribution.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-chart-line feature-icon"></i>
                    <h3>Real-Time Reporting</h3>
                    <p>Generate instant reports on inventory levels, donation history, and distribution data for informed decision-making.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-user-shield feature-icon"></i>
                    <h3>Role-Based Access</h3>
                    <p>Secure, role-specific interfaces for Donors, Staff, and Admin with appropriate permissions.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-mobile-alt feature-icon"></i>
                    <h3>Responsive Design</h3>
                    <p>Accessible on any device - desktop, tablet, or mobile - eliminating the need for specialized hardware.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-history feature-icon"></i>
                    <h3>Donation Lifecycle Tracking</h3>
                    <p>Monitor each donation through its complete lifecycle: 'To be received', 'Processing', 'Rejected', 'In Inventory', or 'Distributed'.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-comments feature-icon"></i>
                    <h3>Donor Engagement</h3>
                    <p>Foster transparency and trust by allowing donors to track their contributions and provide feedback directly.</p>
                </div>
            </div>
            </div>
        </section>

      <section class="why-section">
    <div class="why-container">
        <div class="why-content">
            <h2 class="section-title-why">Why Choose DIMS?</h2>
            <ul class="why-list">
                <li><i class="fas fa-check whylogo"></i> Simple & Intuitive Interface</li>
                <li><i class="fas fa-graduation-cap whylogo"></i> Minimal Training Required</li>
                <li><i class="fas fa-eye whylogo"></i> Increased Transparency</li>
                <li><i class="fas fa-handshake whylogo"></i> Enhanced Donor Engagement</li>
                <li><i class="fas fa-sync-alt whylogo"></i> Real-Time Updates</li>
                <li><i class="fas fa-tablet whylogo"></i> Responsive Design</li>
            </ul>
        </div>
    </div>
    <div class="why-image">
        <img src="LoginImage.jpg" alt="DIMS Dashboard Preview">
    </div>
</section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section footer-about">
                <div class="footer-logo">
                    <i class="fas fa-warehouse"></i>
                    <span>DIMS</span>
                </div>
                <p class="footer-description">
                    The Donation Inventory Management System streamlines in-kind donation processes for local community donation centres, enhancing transparency and maximizing aid impact.
                </p>
                <div class="footer-social">
                    <a href="#" class="social-icon" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-icon" aria-label="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-icon" aria-label="LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="#" class="social-icon" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>

            <div class="footer-section footer-links">
                <h3 class="footer-title">Quick Links</h3>
                <ul>
                    <li><a href="LandingPage.html">Home</a></li>
                    <li><a href="LoginPage.php">Login</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </div>

            <div class="footer-section footer-contact">
                <h3 class="footer-title">Contact Info</h3>
                <ul>
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Mbagathi Way Logistics Centre, Nairobi</span>
                    </li>
                    <li>
                        <i class="fas fa-phone"></i>
                        <span>+254706555891</span>
                    </li>
                    <li>
                        <i class="fas fa-envelope"></i>
                        <span>donationinventory.ms@gmail.com</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-bottom-container">
                <p>&copy; 2025 Donation Inventory Management System (DIMS). All rights reserved.</p>
                <div class="footer-legal">
                    <a href="#">Privacy Policy</a>
                    <span>|</span>
                    <a href="#">Terms of Service</a>
                    <span>|</span>
                    <a href="#">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>