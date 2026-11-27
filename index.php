<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$usernameDisplay = e($_SESSION['username']);
$avatarInitial = strtoupper(substr($_SESSION['username'], 0, 1));
$cartCount = cart_item_count();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guspart Autoshop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <header class="site-header">
        <div class="header-inner">
            <a href="index.php" class="brand-logo">
                <img src="gambar1.png" alt="Guspart Autoshop Logo">
                <span class="brand-name">Guspart Autoshop</span>
            </a>
            <nav class="site-nav" aria-label="Primary navigation">
                <a href="index.php" class="active">Home</a>
                <a href="product.php">Products</a>
                <a href="order.php">Order<?= $cartCount > 0 ? " ({$cartCount})" : '' ?></a>
                <a href="order-history.php">Order History</a>
                <a href="#footer">Contact Us</a>
                <div class="user-menu">
                    <button type="button" class="user-trigger" aria-haspopup="true" aria-expanded="false">
                        <span class="user-avatar"><?= $avatarInitial ?></span>
                        <span><?= $usernameDisplay ?></span>
                    </button>
                    <div class="dropdown-menu">
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            </nav>
            <button type="button" class="hamburger" onclick="toggleMenu()" aria-label="Toggle navigation" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <main id="main-content">
        <section class="hero-section" id="hero">
            <div class="container">
                <div class="hero-grid">
                    <div class="hero-text">
                        <span class="hero-eyebrow">Genuine Spare Parts &amp; Workshop Essentials</span>
                        <h1 class="hero-title">Complete Your Ride, Perfect Your Drive.</h1>
                        <p class="hero-subtitle">Reliable brake pads, engine oils, spark plugs, batteries, and essential spare parts sourced from trusted suppliers and delivered straight to your garage.</p>
                        <div class="hero-actions">
                            <a href="product.php" class="btn btn-primary btn-lg">Shop Now &rarr;</a>
                            <a href="order-history.php" class="btn btn-outline btn-lg">Track Orders</a>
                        </div>
                    </div>
                    <div class="hero-media">
                        <div class="hero-media-frame">
                            <img src="gambar2.png" alt="Guspart Autoshop spare parts collection">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="features-band">
            <div class="container">
                <div class="features-grid">
                    <div class="feature-item">
                        <div class="feature-icon"><img src="gambar3.png" alt="Free Shipping"></div>
                        <div>
                            <h3>Free Shipping</h3>
                            <p>Fast, dependable delivery directly to your doorstep.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><img src="gambar4.png" alt="24/7 Support"></div>
                        <div>
                            <h3>24/7 Support</h3>
                            <p>Dedicated assistance for part fitment and order help.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><img src="gambar5.png" alt="Free Return"></div>
                        <div>
                            <h3>Free Return</h3>
                            <p>Easy return guarantee if a spare part doesn't match.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="promo-section">
            <div class="container">
                <div class="promo-card">
                    <h2>Upgrade Your Ride Today! Special Discounts End on Sunday!</h2>
                    <p>Explore our curated catalog of high-durability motorcycle and automotive components at competitive prices.</p>
                    <a href="product.php" class="btn btn-primary btn-lg">Browse Catalog &rarr;</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer" id="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <h3>Guspart Autoshop</h3>
                    <p>We've worked tirelessly to find the best suppliers to bring high-quality and reliable spare parts to your garage.</p>
                </div>
                <div>
                    <h3>Get in Touch</h3>
                    <p>123-456-7890</p>
                    <p>+123-456-7890</p>
                    <p>guspart@autoshop.com</p>
                </div>
                <div>
                    <h3>Quick Links</h3>
                    <a href="index.php">Home</a>
                    <a href="product.php">Products</a>
                    <a href="order.php">Shopping Cart</a>
                    <a href="order-history.php">Order History</a>
                </div>
                <div>
                    <h3>Join Our Newsletter</h3>
                    <p>Get notified about new spare part arrivals and weekend promos.</p>
                    <form class="newsletter-form" onsubmit="event.preventDefault();">
                        <input type="email" placeholder="Enter your email" aria-label="Email address">
                        <button type="submit" class="btn btn-primary btn-sm">Subscribe</button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; 2024 Guspart Autoshop. All Rights Reserved.
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>

</html>
