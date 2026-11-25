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

    <main class="auth-main">
        <div class="container">
            <div class="auth-grid">
                <div class="auth-intro">
                    <span class="hero-eyebrow">Join Guspart Autoshop</span>
                    <h1>Create Your<br>Account Today</h1>
                    <p>Register as a customer to shop authentic spare parts, or apply as an admin or courier to join our operations team.</p>
                    <div class="auth-switch">
                        <span>Already registered?</span>
                        <a href="login.php" class="btn btn-outline btn-sm">Login here</a>
                    </div>
                </div>

                <div class="auth-card">
                    <h2>Create an Account</h2>
                    <p class="auth-card-subtitle">Fill in your details below to get started.</p>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-error">
                            <?= htmlspecialchars($_GET['error']) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($_GET['success']) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="process_register.php">
                        <div class="form-group">
                            <label for="role">Account Role</label>
                            <select id="role" name="role" required>
                                <option value="">-- Select Role --</option>
                                <option value="customer">Customer</option>
                                <option value="admin">Admin</option>
                                <option value="courier">Kurir</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input id="username" type="text" name="username" placeholder="Choose a username" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input id="email" type="email" name="email" placeholder="you@example.com" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input id="password" type="password" name="password" placeholder="Create a password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input id="confirm_password" type="password" name="confirm_password" placeholder="Re-enter your password" required>
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
