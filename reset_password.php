<?php
require_once __DIR__ . '/includes/bootstrap.php';

$prefillEmail = trim($_GET['email'] ?? ($_POST['email'] ?? ''));
$prefillToken = trim($_GET['token'] ?? ($_POST['reset_code'] ?? ''));

$statusType = '';
$statusMessage = '';
$tokenState = 'ready';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $prefillEmail !== '' && $prefillToken !== '') {
    $conn = db();
    $stmt = $conn->prepare('SELECT reset_code FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $prefillEmail);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stored = $row['reset_code'] ?? '';
    if (!$row || strpos($stored, '|') === false) {
        $tokenState = 'invalid';
        $statusType = 'error';
        $statusMessage = 'This password reset link is invalid or has already been used.';
    } else {
        [$storedHash, $expiresAt] = explode('|', $stored, 2);
        if (!hash_equals($storedHash, hash('sha256', $prefillToken))) {
            $tokenState = 'invalid';
            $statusType = 'error';
            $statusMessage = 'This password reset link is invalid.';
        } elseif (time() > (int)$expiresAt) {
            $tokenState = 'expired';
            $statusType = 'error';
            $statusMessage = 'This password reset link has expired. Please request a new one.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $statusType = 'error';
        $statusMessage = 'Invalid security token. Please refresh and try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $token = trim($_POST['reset_code'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $token === '') {
            $statusType = 'error';
            $statusMessage = 'Please provide a valid email address and recovery token.';
        } elseif (strlen($newPassword) < 6) {
            $statusType = 'error';
            $statusMessage = 'New password must be at least 6 characters long.';
        } elseif ($newPassword !== $confirmPassword) {
            $statusType = 'error';
            $statusMessage = 'Password confirmation does not match.';
        } else {
            $conn = db();
            $stmt = $conn->prepare('SELECT id, reset_code FROM users WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $stored = $user['reset_code'] ?? '';
            $valid = false;
            $expired = false;

            if ($user && $stored !== '' && strpos($stored, '|') !== false) {
                [$storedHash, $expiresAt] = explode('|', $stored, 2);
                if (hash_equals($storedHash, hash('sha256', $token))) {
                    if (time() <= (int)$expiresAt) {
                        $valid = true;
                    } else {
                        $expired = true;
                    }
                }
            }

            if ($valid) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $upd = $conn->prepare('UPDATE users SET password = ?, reset_code = NULL WHERE id = ?');
                $upd->bind_param('si', $hashedPassword, $user['id']);
                $upd->execute();
                $upd->close();

                header('Location: login.php?success=' . urlencode('Your password has been updated. Please sign in.'));
                exit;
            } elseif ($expired) {
                $tokenState = 'expired';
                $statusType = 'error';
                $statusMessage = 'This recovery token has expired. Please request a new recovery link.';
            } else {
                $statusType = 'error';
                $statusMessage = 'Invalid or already used recovery token.';
            }
        }
    }
}
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
                    <span class="hero-eyebrow">Account Security</span>
                    <h1>Set a New<br>Password</h1>
                    <p>Enter your registered email, your recovery token, and choose a strong new password to regain access.</p>
                    <ul class="auth-highlights">
                        <li>
                            <span class="auth-check" aria-hidden="true">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </span>
                            Minimum 6 characters with secure password hashing
                        </li>
                        <li>
                            <span class="auth-check" aria-hidden="true">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </span>
                            Recovery token is invalidated immediately after reset
                        </li>
                    </ul>
                    <div class="auth-switch">
                        <span>Need a new link?</span>
                        <a href="forgot_password.php" class="btn btn-outline btn-sm">Request Link</a>
                    </div>
                </div>

                <div class="auth-card">
                    <div class="auth-card-header">
                        <h2>Reset Password</h2>
                        <p class="auth-card-subtitle">Complete the form below to update your password.</p>
                    </div>

                    <?php if ($statusMessage !== ''): ?>
                        <div class="alert alert-<?= e($statusType) ?>" role="alert" aria-live="polite">
                            <?= e($statusMessage) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($tokenState === 'expired' || $tokenState === 'invalid'): ?>
                        <a href="forgot_password.php" class="btn btn-primary btn-block">Request New Recovery Link</a>
                        <div class="auth-footer-link">
                            <a href="login.php">&larr; Back to Login</a>
                        </div>
                    <?php else: ?>
                        <form method="POST" data-loading-text="Updating password...">
                            <?= csrf_field() ?>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input id="email" type="email" name="email" value="<?= e($prefillEmail) ?>" placeholder="you@example.com" autocomplete="email" required>
                            </div>

                            <div class="form-group">
                                <label for="reset_code">Recovery Token</label>
                                <input id="reset_code" type="text" name="reset_code" value="<?= e($prefillToken) ?>" placeholder="Paste recovery token" required>
                            </div>

                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <div class="password-wrap">
                                    <input id="new_password" type="password" name="new_password" minlength="6" placeholder="Minimum 6 characters" autocomplete="new-password" required>
                                    <button type="button" class="password-toggle" data-target="new_password" aria-label="Show password" aria-pressed="false">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <div class="password-wrap">
                                    <input id="confirm_password" type="password" name="confirm_password" minlength="6" placeholder="Re-enter new password" autocomplete="new-password" required>
                                    <button type="button" class="password-toggle" data-target="confirm_password" aria-label="Show password" aria-pressed="false">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block btn-lg">Update Password</button>
                            <div class="auth-footer-link">
                                <a href="login.php">Back to Login</a>
                            </div>
                        </form>
                    <?php endif; ?>
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
