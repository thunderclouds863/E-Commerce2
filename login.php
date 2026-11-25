<?php
// Database connection settings
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'part';

// Create a connection
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password']; // Plain text password

    // Modified query to check username and password directly
    $stmt = $conn->prepare("SELECT id, username, role, status FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param('ss', $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // User found and password matches
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $username; // Store username in session

        // Check user role and approval status
        if ($user['role'] == 'customer') {
            // Redirect to the customer dashboard (index.php)
            header("Location: index.php");
            exit;
        } elseif (($user['role'] == 'owner' || $user['role'] == 'admin' || $user['role'] == 'courier') && $user['status'] == 'approved') {
            // Redirect to the respective dashboard if approved
            $role_dashboard = $user['role'] . '_dashboard.php';
            header("Location: $role_dashboard");
            exit;
        } elseif (($user['role'] == 'admin' || $user['role'] == 'courier') && $user['status'] != 'approved') {
            // User is not approved yet
            $error_message = "Your account is pending approval by the owner.";
            header("Location: login.php?error=" . urlencode($error_message));
            exit;
        }
    } else {
        // Invalid username or password
        $error_message = "Invalid username or password!";
        header("Location: login.php?error=" . urlencode($error_message));
        exit;
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Guspart Autoshop</title>
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
                <a href="login.php" class="nav-cta active">Login</a>
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
                    <span class="hero-eyebrow">Trusted Motorcycle &amp; Auto Spare Parts</span>
                    <h1>Welcome to<br>Guspart Autoshop</h1>
                    <p>Sign in to browse genuine spare parts, track your orders, or manage store inventory and deliveries from one unified platform.</p>
                    <div class="auth-switch">
                        <span>Don't have an account?</span>
                        <a href="register.php" class="btn btn-outline btn-sm">Register here</a>
                    </div>
                </div>

                <div class="auth-card">
                    <h2>Sign In</h2>
                    <p class="auth-card-subtitle">Enter your role and credentials to continue.</p>

                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-error">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-error">
                            <?php echo htmlspecialchars($_GET['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select id="role" name="role" required>
                                <option value="" disabled selected>-- Select Role --</option>
                                <option value="admin">Admin</option>
                                <option value="customer">Customer</option>
                                <option value="courier">Kurir</option>
                                <option value="owner">Owner</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input id="username" type="text" name="username" placeholder="Enter your username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input id="password" type="password" name="password" placeholder="Enter your password" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block btn-lg" name="submit">Login</button>
                        <div class="auth-footer-link">
                            <a href="forgot_password.php">Forgot Password?</a>
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
