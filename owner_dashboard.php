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
    <title>Owner Dashboard - Guspart Autoshop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <header class="site-header">
        <div class="header-inner">
            <a href="owner_dashboard.php" class="brand-logo">
                <img src="gambar1.png" alt="Guspart Autoshop Logo">
                <span class="brand-name">Guspart Autoshop</span>
                <span class="brand-badge">Owner</span>
            </a>
            <nav class="site-nav" aria-label="Owner navigation">
                <a href="owner_dashboard.php" class="active">Dashboard</a>
                <a href="management-user.php">User Management</a>
                <a href="report.php">Sales Report</a>
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
                        <span class="hero-eyebrow">Owner Executive Portal</span>
                        <h1 class="hero-title">Welcome back, <?= $usernameDisplay ?>.</h1>
                        <p class="hero-subtitle">Oversee staff approvals, review active customers and couriers, and monitor completed sales revenue across Guspart Autoshop.</p>
                        <div class="hero-actions">
                            <a href="report.php" class="btn btn-primary btn-lg">View Sales Report &rarr;</a>
                            <a href="management-user.php" class="btn btn-outline btn-lg">Manage Users</a>
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
                        <h2>Owner Controls</h2>
                        <p>Access user approvals and business performance analytics.</p>
                    </div>
                </div>
                <div class="portal-grid-2">
                    <a href="management-user.php" class="action-tile">
                        <div class="action-tile-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <h3>User Management</h3>
                        <p>Approve or reject pending admin and courier registrations, and review all active customer, admin, and courier accounts.</p>
                        <span style="color: var(--accent); font-weight: 600; font-size: 0.92rem;">Open User Management &rarr;</span>
                    </a>

                    <a href="report.php" class="action-tile">
                        <div class="action-tile-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <line x1="18" y1="20" x2="18" y2="10"></line>
                                <line x1="12" y1="20" x2="12" y2="4"></line>
                                <line x1="6" y1="20" x2="6" y2="14"></line>
                            </svg>
                        </div>
                        <h3>Sales Report</h3>
                        <p>Track total completed orders, cumulative revenue, monthly sales trends, and inspect detailed transaction receipts.</p>
                        <span style="color: var(--accent); font-weight: 600; font-size: 0.92rem;">Open Sales Report &rarr;</span>
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
                    <h3>Owner Links</h3>
                    <a href="owner_dashboard.php">Dashboard</a>
                    <a href="management-user.php">User Management</a>
                    <a href="report.php">Sales Report</a>
                    <a href="logout.php">Logout</a>
                </div>
                <div>
                    <h3>Business Overview</h3>
                    <p>Centralized management for Guspart Autoshop operations and performance.</p>
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
