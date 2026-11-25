<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'part';
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $code = $_POST['reset_code'];
    $new_password = $_POST['new_password'];

    // Verify reset code
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND reset_code = ?");
    $stmt->bind_param('ss', $email, $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update password
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_code = NULL WHERE email = ?");
        $stmt->bind_param('ss', $new_password, $email);
        $stmt->execute();

        header("location:login.php");
    } else {
        $message = 'Invalid reset code!';
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Guspart Autoshop</title>
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
                    <span class="hero-eyebrow">Account Security</span>
                    <h1>Set a New<br>Password</h1>
                    <p>Enter your registered email, the 6-digit OTP reset code we sent you, and choose a new password.</p>
                    <div class="auth-switch">
                        <span>Need a new code?</span>
                        <a href="forgot_password.php" class="btn btn-outline btn-sm">Request Code</a>
                    </div>
                </div>

                <div class="auth-card">
                    <h2>Reset Password</h2>
                    <p class="auth-card-subtitle">Complete the form below to update your password.</p>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-error">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input id="email" type="email" name="email" placeholder="you@example.com" required>
                        </div>
                        <div class="form-group">
                            <label for="reset_code">Reset Code</label>
                            <input id="reset_code" type="text" name="reset_code" placeholder="Kode OTP" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input id="new_password" type="password" name="new_password" placeholder="Masukkan Password Baru" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block btn-lg">Reset Password</button>
                        <div class="auth-footer-link">
                            <a href="login.php">Back to Login</a>
                        </div>
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
