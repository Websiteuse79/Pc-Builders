<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pc Modification - Custom PC Solutions</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <?php
    session_start();
    // Prevent order placement if not logged in
    if (
        ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['place_order']) || isset($_POST['checkout']) || isset($_POST['add_to_cart']))) &&
        !isset($_SESSION['user'])
    ) {
        header('Location: Login/login.php');
        exit;
    }
    ?>
    <?php include 'navbar.php'; ?>


    
    <!-- Hero Section -->
    <section id="home" class="hero" style=" overflow: hidden; min-height: 500px; display: flex; align-items: center;">
        <video autoplay muted loop playsinline
            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1;">
            <source src="assets/Firefly _Cinematic shot, 4K, hyper-detailed- A mysterious, matte black cube sits centered on a minim.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="container" style="position: relative; z-index: 2;">
            <div class="hero-content" style="text-align: center; color: #fff; text-shadow: 0 2px 16px rgba(0,0,0,0.7);">
                <h1 class="animate-on-scroll">Build Your Dream PC</h1>
                <p class="animate-on-scroll">Customize every component or choose from our pre-built masterpieces</p>
                <div class="hero-buttons animate-on-scroll" style="margin-top: 32px;">
                    <a href="#custom" class="btn">Custom Build</a>
                    <a href="#prebuild" class="btn btn-outline">Pre-Builds</a>
                </div>
            </div>
        </div>
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.45); z-index: 1;"></div>
    </section>

    <!-- Pre-Build PCs Section -->
    <section id="prebuild" class="section prebuild-section">
        <div id="prebuilds-container">
        
            <h2 class="section-title animate-on-scroll">Pre-Build PCs</h2>
            <p class="section-subtitle animate-on-scroll">Choose from our expertly crafted configurations</p>

            <div class="prebuild-slider animate-on-scroll">
                <div class="slider-container">
                    <div class="slider">
                        <!-- Pre-build PC items will be added here by JavaScript -->
                    </div>
                    <button class="slider-prev"><i class="fas fa-chevron-left"></i></button>
                    <button class="slider-next"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </div>
    </section>

    <!-- Custom Build Section -->
    <section id="custom" class="section custom-build-section">
        <div class="container">
            <h2 class="section-title animate-on-scroll">Custom PC Builder</h2>
            <p class="section-subtitle animate-on-scroll">Select each component to build your perfect system</p>

            <div class="build-steps animate-on-scroll">
              
            </div>

            <div class="sort-filter" style="margin-bottom: 15px;">
                <label for="sortPrice">Sort by Price:</label>
                <select id="sortPrice">
                    <option value="low-high">Low to High</option>
                    <option value="high-low">High to Low</option>
                </select>
            </div>
            <div class="component-selector">
                <!-- Components will be loaded here by Database in like tables-->
            </div>

            <div class="build-summary animate-on-scroll">
                <h3>Your Build Summary</h3>
                <div class="summary-items">
                    <!-- Selected components will be shown here -->
                </div>
                <div class="summary-total">
                    <p>Estimated Total: ₹<span class="build-total">0</span></p>
                    <button class="btn add-to-cart-build">Add to Cart</button>
                </div>
            </div>
        </div>
    </section>




    <!-- Services Section -->
    <section id="services" class="section services-section">
        <div class="container">
            <h2 class="section-title animate-on-scroll">Our Services</h2>
            <p class="section-subtitle animate-on-scroll">Beyond just building PCs</p>

            <div class="services-grid">
                <div class="service-card animate-on-scroll">
                    <i class="fas fa-tools"></i>
                    <h3>PC Maintenance</h3>
                    <p>Keep your system running smoothly with our maintenance services.</p>
                </div>
                <div class="service-card animate-on-scroll">
                    <i class="fas fa-broom"></i>
                    <h3>Cleaning</h3>
                    <p>Professional cleaning to remove dust and improve cooling.</p>
                </div>
                <div class="service-card animate-on-scroll">
                    <i class="fas fa-magic"></i>
                    <h3>Upgrades</h3>
                    <p>Boost your system's performance with component upgrades.</p>
                </div>
                <div class="service-card animate-on-scroll">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Virus Removal</h3>
                    <p>Complete virus scanning and removal with data protection.</p>
                </div>
            </div>
        </div>
    </section>








    <!-- About Section -->
    <section id="about" class="section about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-text animate-on-scroll">
                    <h2 class="section-title">About Pc Modification</h2>
                    <p>Founded in 2025, Pc Modification is dedicated to providing high-quality custom PC solutions for gamers,
                        creators, and professionals.</p>
                    <p>Our team of experts carefully selects each component to ensure optimal performance and
                        reliability for every build.</p>
                    <p>Whether you need a powerful workstation, a gaming rig, or a compact home theater PC, we've got
                        you covered.</p>
                </div>
                <div class="about-image animate-on-scroll">
                    <img src="assets/images/about-pc.png" alt="About Pc Modification">
                </div>
            </div>
        </div>
    </section>







    <!-- Footer -->

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Pc Modification</h3>
                    <p>Building dreams, Enjoy a Dream</p>
                    <p>Play Games , Creating Ideas , Designing  </p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                <li><a href="index.php#home">Home</a></li>
                <li><a href="index.php#prebuild">Pre-Build PC</a></li>
                <li><a href="index.php#custom">Custom Pc Build</a></li>
                <li><a href="index.php#services">Services</a></li>
                <li><a href="index.php#about">About</a></li>
                <li><a href="Maintenance.php">Maintenance</a></li>

                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact</h3>
                    <p>info@Pc-Modification.com</p>
                    <p>+91 9978716188</p>
                    <p>+91 7600088311</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Pc Modification. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script src="script.js"></script>
   
</body>

</html>