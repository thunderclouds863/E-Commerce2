<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    header('Location: ' . role_home($_SESSION['role']));
    exit;
}

$error_message = '';
$success_message = '';

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = ['count' => 0, 'locked_until' => 0];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $now = time();
    if ($_SESSION['login_attempts']['locked_until'] > $now) {
        $waitSec = $_SESSION['login_attempts']['locked_until'] - $now;
        $error_message = "Too many failed attempts. Please wait {$waitSec} seconds before trying again.";
    } elseif (!verify_csrf()) {
        $error_message = 'Invalid security token. Please refresh and try again.';
    } else {
        $selectedRole = trim($_POST['role'] ?? '');
        $usernameInput = trim($_POST['username'] ?? '');
        $passwordInput = $_POST['password'] ?? '';

        if ($selectedRole === '' || $usernameInput === '' || $passwordInput === '') {
            $error_message = 'Please select your role and enter both username and password.';
        } else {
            $conn = db();
            $stmt = $conn->prepare('SELECT id, username, password, role, status FROM users WHERE username = ? LIMIT 1');
            $stmt->bind_param('s', $usernameInput);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            $passwordValid = false;
            $needsRehash = false;
            if ($user) {
                $stored = (string)$user['password'];
                if (password_verify($passwordInput, $stored)) {
                    $passwordValid = true;
                    $needsRehash = password_needs_rehash($stored, PASSWORD_DEFAULT);
                } elseif (hash_equals($stored, $passwordInput)) {
                    $passwordValid = true;
                    $needsRehash = true;
                }
            }

            if ($user && $passwordValid && $user['role'] === $selectedRole) {
                if ($user['role'] !== 'customer' && $user['status'] !== 'approved') {
                    $error_message = ($user['status'] === 'rejected')
                        ? 'Your account registration was rejected by the owner.'
                        : 'Your account is pending approval by the owner.';
                } else {
                    if ($needsRehash) {
                        $newHash = password_hash($passwordInput, PASSWORD_DEFAULT);
                        $up = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
                        $up->bind_param('si', $newHash, $user['id']);
                        $up->execute();
                        $up->close();
                    }

                    $_SESSION['login_attempts'] = ['count' => 0, 'locked_until' => 0];
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = (int)$user['id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['username'] = $user['username'];

                    header('Location: ' . role_home($user['role']));
                    exit;
                }
            } else {
                $_SESSION['login_attempts']['count']++;
                if ($_SESSION['login_attempts']['count'] >= 5) {
                    $_SESSION['login_attempts']['locked_until'] = $now + 120;
                    $_SESSION['login_attempts']['count'] = 0;
                    $error_message = 'Too many failed attempts. Please try again in 2 minutes.';
                } else {
                    $error_message = 'Invalid role, username, or password.';
                }
            }
        }
    }
}

if (isset($_GET['error']) && $error_message === '') {
    $error_message = $_GET['error'];
}
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}
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
                <a href="login.php" class="nav-cta active">Login</a>
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

                    <?php if ($error_message !== ''): ?>
                        <div class="alert alert-error" role="alert" aria-live="polite">
                            <?= e($error_message) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success_message !== ''): ?>
                        <div class="alert alert-success" role="status" aria-live="polite">
                            <?= e($success_message) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" data-loading-text="Signing in...">
                        <?= csrf_field() ?>

                        <div class="form-group">
                            <label for="role">Role</label>
                            <select id="role" name="role" required>
                                <option value="" disabled <?= empty($_POST['role']) ? 'selected' : '' ?>>-- Select Role --</option>
                                <option value="customer" <?= (($_POST['role'] ?? '') === 'customer') ? 'selected' : '' ?>>Customer</option>
                                <option value="admin" <?= (($_POST['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option>
                                <option value="courier" <?= (($_POST['role'] ?? '') === 'courier') ? 'selected' : '' ?>>Kurir</option>
                                <option value="owner" <?= (($_POST['role'] ?? '') === 'owner') ? 'selected' : '' ?>>Owner</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="username">Username</label>
                            <input id="username" type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>" placeholder="Enter your username" autocomplete="username" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="password-wrap">
                                <input id="password" type="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                                <button type="button" class="password-toggle" data-target="password" aria-label="Show password" aria-pressed="false">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
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
