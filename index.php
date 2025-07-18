<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedCare - Patient Feedback & Reminder System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        section {
            padding: 4rem 0;
        }

        h2 {
            font-size: 2.2rem;
            color: #2a7fba;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        /* Navigation Bar */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 5%;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            display: flex;
            align-items: center;
            font-size: 1.5rem;
            font-weight: 600;
            color: #2a7fba;
        }

        .logo i {
            margin-right: 10px;
            font-size: 1.8rem;
        }

        .nav-links {
            display: flex;
            list-style: none;
        }

        .nav-links li {
            margin: 0 15px;
        }

        .nav-links a {
            text-decoration: none;
            color: #555;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover, .nav-links a.active {
            color: #2a7fba;
        }

        .auth-buttons a {
            padding: 0.5rem 1.2rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            margin-left: 10px;
            transition: all 0.3s;
        }

        .btn-login {
            color: #2a7fba;
            border: 1px solid #2a7fba;
        }

        .btn-login:hover {
            background: #2a7fba;
            color: white;
        }

        .btn-register {
            background: #2a7fba;
            color: white;
        }

        .btn-register:hover {
            background: #1e6a9b;
        }

        /* Hero Section */
       /* Hero Section */
.hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5rem 5%;
    background: linear-gradient(rgba(93, 178, 231, 0.9), rgba(221, 230, 240, 0.9)), 
                url('assets/images/yh2.jpg') center/cover no-repeat;
    position: relative;
    /* z-index: -1; */
    color: #333; /* Ensures text remains readable */
}

/* Keep all other hero styles the same */
.hero-content {
    max-width: 50%;
    position: relative;
    z-index: 2;
}

.hero-image {
    position: relative;
    z-index: 2;
}

        .hero h1 {
            font-size: 2.8rem;
            margin-bottom: 1.5rem;
            color: #2a7fba;
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2.5rem;
            color: #555;
        }

        .cta-buttons {
            display: flex;
            gap: 15px;
        }

        .cta-buttons a {
            padding: 0.9rem 1.8rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #2a7fba;
            color: white;
        }

        .btn-primary:hover {
            background: #1e6a9b;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: white;
            color: #2a7fba;
            border: 1px solid #2a7fba;
        }

        .btn-secondary:hover {
            background: #f0f8ff;
            transform: translateY(-2px);
        }

        .hero-image img {
           
            width: 100%;
            
            max-width: 500px;
            margin-top: 100px;
            margin: -100px 650px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }

        /* Features Section */
        .features {
            background: white;
        }

        .features-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 2rem;
        }

        .feature-card {
            text-align: center;
            padding: 2.5rem 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            max-width: 300px;
            background: white;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .feature-card i {
            font-size: 2.8rem;
            color: #2a7fba;
            margin-bottom: 1.5rem;
        }

        .feature-card h3 {
            margin-bottom: 1rem;
            color: #333;
            font-size: 1.4rem;
        }

        .feature-card p {
            color: #777;
            font-size: 1rem;
        }

        /* How It Works Section */
        .how-it-works {
            background: #f0f8ff;
        }

        .steps-container {
            display: flex;
            justify-content: space-between;
            margin-top: 3rem;
            flex-wrap: wrap;
        }

        .step {
            flex: 1;
            min-width: 250px;
            margin: 0 15px 30px;
            text-align: center;
            position: relative;
        }

        .step-number {
            width: 50px;
            height: 50px;
            background: #2a7fba;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0 auto 1.5rem;
        }

        .step h3 {
            margin-bottom: 1rem;
            color: #2a7fba;
        }

        .step p {
            color: #555;
        }

        /* Testimonials */
        .testimonials {
            background: white;
        }

        .testimonial-container {
            display: flex;
            overflow-x: auto;
            gap: 2rem;
            padding: 2rem 0;
            scroll-snap-type: x mandatory;
        }

        .testimonial {
            min-width: 300px;
            background: #f9f9f9;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            scroll-snap-align: start;
        }

        .testimonial-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .testimonial-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1rem;
        }

        .testimonial-info h4 {
            color: #2a7fba;
            margin-bottom: 0.3rem;
        }

        .testimonial-info p {
            color: #777;
            font-size: 0.9rem;
        }

        .rating {
            color: #ffc107;
            margin-bottom: 1rem;
        }

        /* Stats Section */
        .stats {
            background: #2a7fba;
            color: white;
            text-align: center;
        }

        .stats-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }

        .stat-item {
            padding: 2rem;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.2rem;
        }

        /* FAQ Section */
        .faq {
            background: #f9f9f9;
        }

        .faq-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .faq-item {
            margin-bottom: 1rem;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .faq-question {
            background: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            font-weight: 500;
        }

        .faq-question:hover {
            background: #f0f8ff;
        }

        .faq-answer {
            background: white;
            padding: 0 1.5rem;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .faq-answer.active {
            padding: 1.5rem;
            max-height: 300px;
        }

        /* Newsletter */
        .newsletter {
            background: linear-gradient(135deg, #2a7fba, #1e6a9b);
            color: white;
            text-align: center;
        }

        .newsletter h2 {
            color: white;
        }

        .newsletter p {
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        .newsletter-form {
            display: flex;
            max-width: 500px;
            margin: 0 auto;
        }

        .newsletter-form input {
            flex: 1;
            padding: 1rem;
            border: none;
            border-radius: 5px 0 0 5px;
            font-size: 1rem;
        }

        .newsletter-form button {
            padding: 0 1.5rem;
            background: #ff6b6b;
            color: white;
            border: none;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
            transition: background 0.3s;
        }

        .newsletter-form button:hover {
            background: #ff5252;
        }

        /* Footer */
        footer {
            background: #1a1a1a;
            color: white;
            padding: 4rem 0 2rem;
        }

        .footer-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .footer-column {
            flex: 1;
            min-width: 200px;
        }

        .footer-column h3 {
            color: #2a7fba;
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
        }

        .footer-column ul {
            list-style: none;
        }

        .footer-column li {
            margin-bottom: 0.8rem;
        }

        .footer-column a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-column a:hover {
            color: white;
        }

        .contact-info {
            display: flex;
            align-items: center;
            margin-bottom: 0.8rem;
        }

        .contact-info i {
            margin-right: 10px;
            color: #2a7fba;
        }

        .social-links {
            display: flex;
            gap: 15px;
        }

        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: #333;
            border-radius: 50%;
            color: white;
            transition: all 0.3s;
        }

        .social-links a:hover {
            background: #2a7fba;
            transform: translateY(-3px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid #333;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .hero {
                flex-direction: column;
                text-align: center;
                padding: 4rem 5%;
            }
            
            .hero-content {
                max-width: 100%;
                margin-bottom: 3rem;
            }
            
            .cta-buttons {
                justify-content: center;
            }
            
            .steps-container {
                flex-direction: column;
                align-items: center;
            }
            
            .step {
                margin-bottom: 2rem;
            }
        }

        @media (max-width: 768px) {
            nav {
                flex-direction: column;
                padding: 1rem;
            }
            
            .nav-links {
                margin: 1rem 0;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .auth-buttons {
                margin-top: 1rem;
            }
            
            .hero h1 {
                font-size: 2.2rem;
            }
            
            .feature-card {
                min-width: 100%;
            }
            
            .footer-column {
                min-width: 100%;
                margin-bottom: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav>
        <div class="logo">
            <a href="staff/login.php"><i class="fas fa-heartbeat"></i></a>
            <span>MedCare</span>
        </div>
        <ul class="nav-links">    
            <li><a href="index.php" class="active">Home</a></li>
            <li><a href="contact.html">Contact</a></li>
        </ul>
        <div class="auth-buttons">
            <a href="patient-portal/login.php" class="btn-login">Login</a>
            <a href="includes/register.php" class="btn-register">Register</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Your Health Journey Starts Here</h1>
                <p>Experience seamless healthcare management with our patient feedback system and smart appointment reminders. Join thousands of satisfied patients who take control of their healthcare experience.</p>
                <div class="cta-buttons">
                    <a href="feedback.html" class="btn-primary">Submit Feedback</a>
                    <a href="reminders.html" class="btn-secondary">Set Reminder</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="assets/images/yh.jpg" alt="Doctor and patient">
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2>Why Choose MedCare?</h2>
            <p style="text-align: center; max-width: 800px; margin: 0 auto 3rem;">We're revolutionizing patient care with technology that puts you first</p>
            
            <div class="features-container">
                <div class="feature-card">
                    <i class="fas fa-comment-medical"></i>
                    <h3>Real-time Feedback</h3>
                    <p>Share your experience instantly with our easy-to-use interface. Your feedback helps us improve care quality.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-bell"></i>
                    <h3>Smart Reminders</h3>
                    <p>Customizable alerts via email, SMS, or app notifications so you never miss important health appointments.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-shield-alt"></i>
                    <h3>HIPAA Compliant</h3>
                    <p>Your health information is protected with enterprise-grade security and strict privacy controls.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-chart-line"></i>
                    <h3>Progress Tracking</h3>
                    <p>Monitor your health journey with personalized dashboards and historical data visualization.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works">
        <div class="container">
            <h2>How Our System Works</h2>
            <p style="text-align: center; max-width: 800px; margin: 0 auto 3rem;">Simple steps to better healthcare communication</p>
            
            <div class="steps-container">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Create Your Account</h3>
                    <p>Register in minutes with basic information to get started with your personalized healthcare profile.</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Connect With Providers</h3>
                    <p>Link your account with healthcare providers to enable seamless communication and reminders.</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Share Your Experience</h3>
                    <p>After each visit, provide feedback to help improve services and track your health journey.</p>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <h3>Receive Smart Reminders</h3>
                    <p>Get timely notifications for upcoming appointments, medication schedules, and health check-ups.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <h2>What Our Patients Say</h2>
            <p style="text-align: center; max-width: 800px; margin: 0 auto 3rem;">Hear from people who transformed their healthcare experience</p>
            
            <div class="testimonial-container">
                <div class="testimonial">
                    <div class="testimonial-header">
                        <img src="https://randomuser.me/api/portraits/women/45.jpg" alt="Sarah J." class="testimonial-img">
                        <div class="testimonial-info">
                            <h4>Sarah Johnson</h4>
                            <p>Cardiology Patient</p>
                        </div>
                    </div>
                    <div class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p>"The reminder system saved me from missing two critical follow-up appointments. The feedback process is so simple and I love that my doctors actually respond to my concerns."</p>
                </div>
                
                <div class="testimonial">
                    <div class="testimonial-header">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Robert T." class="testimonial-img">
                        <div class="testimonial-info">
                            <h4>Robert Thompson</h4>
                            <p>Diabetes Management</p>
                        </div>
                    </div>
                    <div class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <p>"As someone managing a chronic condition, the medication reminders have been life-changing. The feedback system helped my care team adjust my treatment plan based on my symptoms."</p>
                </div>
                
                <div class="testimonial">
                    <div class="testimonial-header">
                        <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Maria G." class="testimonial-img">
                        <div class="testimonial-info">
                            <h4>Maria Gonzalez</h4>
                            <p>Pediatric Care</p>
                        </div>
                    </div>
                    <div class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p>"Managing my children's health appointments used to be stressful. Now I get reminders for all their vaccinations and check-ups. The pediatrician actually made changes based on my feedback!"</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-container">
                <div class="stat-item">
                    <div class="stat-number">10,000+</div>
                    <div class="stat-label">Satisfied Patients</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">95%</div>
                    <div class="stat-label">Appointment Adherence</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">4.8/5</div>
                    <div class="stat-label">Average Rating</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Support Available</div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq">
        <div class="container">
            <h2>Frequently Asked Questions</h2>
            <p style="text-align: center; max-width: 800px; margin: 0 auto 3rem;">Find answers to common questions about our system</p>
            
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question">
                        <span>How do I connect my healthcare providers to the system?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>After registering, you can search for your healthcare providers in our verified network and send connection requests. Most major hospitals and clinics are already integrated with our system. For providers not yet in our network, you can invite them to join.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>What types of reminders can I receive?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Our system supports appointment reminders, medication alerts, prescription renewal notifications, vaccination schedules, and preventive screening reminders. You can customize which reminders you receive and how you receive them (email, SMS, or app notifications).</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Is my health information secure?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Absolutely. We use bank-level encryption and comply with all HIPAA regulations. Your data is never shared without your explicit consent, and we employ rigorous security measures including two-factor authentication and regular security audits.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Can family members access my account?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>You can designate family members or caregivers as authorized users with customizable access levels. This is particularly useful for elderly patients or those managing chronic conditions who may need support from loved ones.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter">
        <div class="container">
            <h2>Stay Informed About Healthcare Innovations</h2>
            <p>Subscribe to our newsletter for health tips, system updates, and ways to get the most from your healthcare experience.</p>
            
            <form class="newsletter-form">
                <input type="email" placeholder="Enter your email address" required>
                <button type="submit">Subscribe</button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-container">
                <div class="footer-column">
                    <h3>MedCare</h3>
                    <p>Transforming patient care through innovative feedback systems and smart healthcare management tools.</p>
                    <div class="social-links">
                        <a href="https://www.facebook.com/bobo.godswill.73"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.linkedin.com/in/anyanwu-godwill-b84577308?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app"><i class="fab fa-linkedin-in"></i></a>
                        <a href="https://www.instagram.com/bobo.godswill.73?igsh=YzljYTk1ODg3Zg=="><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="#">Home</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Services</a></li>
                        <li><a href="#">Feedback System</a></li>
                        <li><a href="#">Reminder Features</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Services</h3>
                    <ul>
                        <li><a href="#">Patient Feedback</a></li>
                        <li><a href="#">Appointment Reminders</a></li>
                        <li><a href="#">Medication Alerts</a></li>
                        <li><a href="#">Health Tracking</a></li>
                        <li><a href="#">Provider Network</a></li>
                        <li><a href="#">Telehealth Integration</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Contact Us</h3>
                    <div class="contact-info">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>ISTAMA INUBIL</span>
                    </div>
                    <div class="contact-info">
                        <i class="fas fa-phone-alt"></i>
                        <span>(+237) 683 221 265</span>
                    </div>
                    <div class="contact-info">
                        <i class="fas fa-envelope"></i>
                        <span>anyanwugodwill7@gmail.com</span>
                    </div>
                    <div class="contact-info">
                        <i class="fas fa-clock"></i>
                        <span>24/7</span>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 MedCare Patient Feedback System. All rights reserved. | <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Animation for feature cards on scroll
        const featureCards = document.querySelectorAll('.feature-card');
        const steps = document.querySelectorAll('.step');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });

        featureCards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.5s ease';
            observer.observe(card);
        });

        steps.forEach(step => {
            step.style.opacity = '0';
            step.style.transform = 'translateY(20px)';
            step.style.transition = 'all 0.5s ease';
            observer.observe(step);
        });

        // FAQ Accordion
        const faqQuestions = document.querySelectorAll('.faq-question');
        
        faqQuestions.forEach(question => {
            question.addEventListener('click', () => {
                const answer = question.nextElementSibling;
                const icon = question.querySelector('i');
                
                // Close all other answers first
                document.querySelectorAll('.faq-answer').forEach(item => {
                    if (item !== answer && item.classList.contains('active')) {
                        item.classList.remove('active');
                        item.previousElementSibling.querySelector('i').classList.remove('fa-chevron-up');
                        item.previousElementSibling.querySelector('i').classList.add('fa-chevron-down');
                    }
                });
                
                // Toggle current answer
                answer.classList.toggle('active');
                
                // Toggle icon
                if (answer.classList.contains('active')) {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                } else {
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            });
        });

        // Testimonial auto-scroll
        const testimonialContainer = document.querySelector('.testimonial-container');
        let scrollAmount = 0;
        const scrollWidth = testimonialContainer.scrollWidth - testimonialContainer.clientWidth;

        function autoScrollTestimonials() {
            if (scrollAmount < scrollWidth) {
                scrollAmount += 1;
                testimonialContainer.scrollLeft = scrollAmount;
            } else {
                scrollAmount = 0;
                testimonialContainer.scrollLeft = 0;
            }
        }

        let scrollInterval = setInterval(autoScrollTestimonials, 20);

        // Pause on hover
        testimonialContainer.addEventListener('mouseenter', () => {
            clearInterval(scrollInterval);
        });

        testimonialContainer.addEventListener('mouseleave', () => {
            scrollInterval = setInterval(autoScrollTestimonials, 20);
        });
    </script>
</body>
</html>