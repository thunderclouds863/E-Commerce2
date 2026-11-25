<?php
session_start();
$isLoggedIn = isset($_SESSION['username']);
if (!$isLoggedIn) {
    header("Location: login.php");
    exit();
}
$usernameDisplay = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
$avatarInitial = strtoupper(substr($_SESSION['username'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Guspart Autoshop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <header class="site-header">
        <div class="header-inner">
            <a href="admin_dashboard.php" class="brand-logo">
                <img src="gambar1.png" alt="Guspart Autoshop Logo">
                <span class="brand-name">Guspart Autoshop</span>
                <span class="brand-badge">Admin</span>
            </a>
            <nav class="site-nav" aria-label="Admin navigation">
                <a href="admin_dashboard.php" class="active">Dashboard</a>
                <a href="order-management.php">Order Management</a>
                <a href="inventory_management.php">Inventory Management</a>
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

    <main>
        <section class="hero-section" id="hero">
            <div class="container">
                <div class="hero-grid">
                    <div class="hero-text">
                        <span class="hero-eyebrow">Admin Operations Portal</span>
                        <h1 class="hero-title">Welcome back, <?= $usernameDisplay ?>.</h1>
                        <p class="hero-subtitle">Manage incoming customer orders, confirm shipments for couriers, and keep spare parts inventory accurate and up to date.</p>
                        <div class="hero-actions">
                            <a href="order-management.php" class="btn btn-primary btn-lg">Manage Orders &rarr;</a>
                            <a href="inventory_management.php" class="btn btn-outline btn-lg">Manage Inventory</a>
                        </div>
                    </div>
                    <div class="hero-media">
                        <div class="hero-media-frame">
                            <img src="gambar2.png" alt="Guspart Autoshop spare parts">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="page-section">
            <div class="container">
                <div class="section-header">
                    <div>
                        <h2>Quick Access</h2>
                        <p>Jump directly into daily store administration workflows.</p>
                    </div>
                </div>
                <div class="portal-grid-2">
                    <a href="order-management.php" class="action-tile">
                        <div class="action-tile-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"></path>
                                <rect x="9" y="3" width="6" height="4" rx="1"></rect>
                                <path d="M9 12h6M9 16h6"></path>
                            </svg>
                        </div>
                        <h3>Order Management</h3>
                        <p>Inspect incoming orders, verify order items, confirm pending transactions, and prepare packages for courier dispatch.</p>
                        <span style="color: var(--accent); font-weight: 600; font-size: 0.92rem;">Open Order Management &rarr;</span>
                    </a>

                    <a href="inventory_management.php" class="action-tile">
                        <div class="action-tile-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                <line x1="12" y1="22.08" x2="12" y2="12"></line>
                            </svg>
                        </div>
                        <h3>Inventory Management</h3>
                        <p>Add new spare parts, update pricing and stock levels, upload product imagery, and remove discontinued items.</p>
                        <span style="color: var(--accent); font-weight: 600; font-size: 0.92rem;">Open Inventory Management &rarr;</span>
                    </a>
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
                    <h3>Admin Links</h3>
                    <a href="admin_dashboard.php">Dashboard</a>
                    <a href="order-management.php">Order Management</a>
                    <a href="inventory_management.php">Inventory Management</a>
                    <a href="logout.php">Logout</a>
                </div>
                <div>
                    <h3>Store Support</h3>
                    <p>Need assistance with store operations? Contact the system owner or operations lead.</p>
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
