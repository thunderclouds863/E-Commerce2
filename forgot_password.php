<?php

// Database connection
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

  // Check if email exists
  $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $reset_code = rand(100000, 999999); // Generate 6-digit unique code

    // Update the database with the reset code
    $stmt = $conn->prepare("UPDATE users SET reset_code = ? WHERE id = ?");
    $stmt->bind_param('si', $reset_code, $user['id']);
    $stmt->execute();

    // Send the reset code via email
    $subject = 'Password Reset Code';
    $message = "Your password reset code is: $reset_code";
    $headers = 'From: no-reply@guspart-autoshop.com';

    if (mail($email, $subject, $message, $headers)) {
      $message = 'Reset code sent! Check your email.';
      header("location: reset_password.php");
    } else {
      $message = 'Failed to send reset code.';
    }
  } else {
    $message = 'Email not found!';
  }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password - Guspart Autoshop</title>
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
          <span class="hero-eyebrow">Account Recovery</span>
          <h1>Reset Your<br>Password</h1>
          <p>Enter the email address associated with your Guspart Autoshop account and we will send a 6-digit verification code.</p>
          <div class="auth-switch">
            <span>Remember your password?</span>
            <a href="login.php" class="btn btn-outline btn-sm">Back to Login</a>
          </div>
        </div>

        <div class="auth-card">
          <h2>Forgot Password</h2>
          <p class="auth-card-subtitle">We'll email you a reset code to verify your identity.</p>

          <?php if (!empty($message)): ?>
            <div class="alert alert-error">
              <?php echo htmlspecialchars($message); ?>
            </div>
          <?php endif; ?>

          <form method="POST">
            <div class="form-group">
              <label for="email">Registered Email</label>
              <input id="email" type="email" name="email" placeholder="Masukkan Email Terdaftar" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Send Reset Code</button>
            <div class="auth-footer-link">
              <a href="reset_password.php">Already have a code? Reset password</a>
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
