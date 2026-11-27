<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    header('Location: ' . role_home($_SESSION['role']));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Guspart Autoshop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <header class="site-header">
        <div class="header-inner">
            <a href="login.php" class="brand-logo">
                <img src="gambar1.png" alt="Guspart Autoshop Logo">
                <span class="brand-name">Guspart Autoshop</span>
            </a>
            <nav class="site-nav" aria-label="Primary navigation">
                <a href="index.php">Home</a>
                <a href="product.php">Products</a>
                <a href="order.php">Order</a>
                <a href="#footer">Contact Us</a>
                <a href="login.php" class="nav-cta">Login</a>
            </nav>
            <button type="button" class="hamburger" onclick="toggleMenu()" aria-label="Toggle navigation" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <main id="main-content" class="auth-main">
        <div class="container">
            <div class="auth-grid">
                <div class="auth-intro">
                    <span class="hero-eyebrow">Join Guspart Autoshop</span>
                    <h1>Create Your<br>Account Today</h1>
                    <p>Register as a customer to shop authentic spare parts, or apply as an admin or courier to join our operations team.</p>
                    <ul class="auth-highlights">
                        <li>
                            <span class="auth-check" aria-hidden="true">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </span>
                            Instant activation for customer shopping accounts
                        </li>
                        <li>
                            <span class="auth-check" aria-hidden="true">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </span>
                            Owner-verified onboarding for Admin &amp; Courier roles
                        </li>
                        <li>
                            <span class="auth-check" aria-hidden="true">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </span>
                            Encrypted credentials and protected order history
                        </li>
                    </ul>
                    <div class="auth-switch">
                        <span>Already registered?</span>
                        <a href="login.php" class="btn btn-outline btn-sm">Login here</a>
                    </div>
                </div>

                <div class="auth-card">
                    <div class="auth-card-header">
                        <h2>Create an Account</h2>
                        <p class="auth-card-subtitle">Fill in your details below to get started.</p>
                    </div>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-error" role="alert" aria-live="polite">
                            <?= e($_GET['error']) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success" role="status" aria-live="polite">
                            <?= e($_GET['success']) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="process_register.php" data-loading-text="Creating account...">
                        <?= csrf_field() ?>

                        <div class="form-group">
                            <label for="role">Account Role</label>
                            <select id="role" name="role" required>
                                <option value="">-- Select Role --</option>
                                <option value="customer">Customer</option>
                                <option value="admin">Admin (requires owner approval)</option>
                                <option value="courier">Kurir (requires owner approval)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="username">Username</label>
                            <input id="username" type="text" name="username" minlength="3" maxlength="50" placeholder="Choose a username" autocomplete="username" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input id="email" type="email" name="email" placeholder="you@example.com" autocomplete="email" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="password-wrap">
                                <input id="password" type="password" name="password" minlength="6" placeholder="Minimum 6 characters" autocomplete="new-password" required>
                                <button type="button" class="password-toggle" data-target="password" aria-label="Show password" aria-pressed="false">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <div class="password-wrap">
                                <input id="confirm_password" type="password" name="confirm_password" minlength="6" placeholder="Re-enter your password" autocomplete="new-password" required>
                                <button type="button" class="password-toggle" data-target="confirm_password" aria-label="Show password" aria-pressed="false">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg">Sign Up</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <section class="features-band" id="footer">
        <div class="container">
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon"><img src="gambar3.png" alt="Free Shipping"></div>
                    <div>
                        <h3>Free Shipping</h3>
                        <p>Fast, reliable delivery straight to your garage.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><img src="gambar4.png" alt="24/7 Support"></div>
                    <div>
                        <h3>24/7 Support</h3>
                        <p>Ready to help you find the exact compatible part.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><img src="gambar5.png" alt="Free Return"></div>
                    <div>
                        <h3>Free Return</h3>
                        <p>Hassle-free returns if a component doesn't fit.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="assets/js/main.js"></script>
</body>

</html>
