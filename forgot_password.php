<?php
require_once __DIR__ . '/includes/bootstrap.php';

$statusType = '';
$statusMessage = '';

if (!isset($_SESSION['recovery_requests'])) {
    $_SESSION['recovery_requests'] = ['count' => 0, 'window_start' => time()];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $now = time();
    if ($now - $_SESSION['recovery_requests']['window_start'] > 300) {
        $_SESSION['recovery_requests'] = ['count' => 0, 'window_start' => $now];
    }

    if ($_SESSION['recovery_requests']['count'] >= 5) {
        $statusType = 'error';
        $statusMessage = 'Too many recovery requests. Please wait a few minutes before trying again.';
    } elseif (!verify_csrf()) {
        $statusType = 'error';
        $statusMessage = 'Invalid security token. Please refresh and try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $statusType = 'error';
            $statusMessage = 'Please enter a valid email address.';
        } else {
            $_SESSION['recovery_requests']['count']++;
            $conn = db();

            $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            $mailDispatched = true;

            if ($user) {
                // Cryptographically secure 32-byte token, stored as SHA-256 hash + 15-minute expiry
                $rawToken = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $rawToken);
                $expiresAt = time() + 900; // 15 minutes
                $storedPayload = $tokenHash . '|' . $expiresAt;

                $upd = $conn->prepare('UPDATE users SET reset_code = ? WHERE id = ?');
                $upd->bind_param('si', $storedPayload, $user['id']);
                $upd->execute();
                $upd->close();

                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $dir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
                $resetUrl = $scheme . '://' . $host . $dir . '/reset_password.php?email=' . urlencode($email) . '&token=' . urlencode($rawToken);

                $subject = 'Guspart Autoshop — Password Recovery';
                $body = "You requested a password reset for your Guspart Autoshop account.\n\n"
                      . "Open the link below within 15 minutes to set a new password:\n{$resetUrl}\n\n"
                      . "If you did not request this, you can safely ignore this email.";
                $from = getenv('MAIL_FROM') ?: 'no-reply@guspart-autoshop.com';
                $headers = "From: {$from}\r\nContent-Type: text/plain; charset=UTF-8";

                $mailDispatched = @mail($email, $subject, $body, $headers);
            }

            if ($mailDispatched) {
                $statusType = 'success';
                $statusMessage = 'If an account matches that email, a time-limited password recovery link (valid for 15 minutes) has been sent.';
            } else {
                $statusType = 'warning';
                $statusMessage = 'A secure recovery token was generated, but outbound email delivery (SMTP/sendmail) is not configured on this server. Contact the administrator or configure SMTP to deliver reset emails.';
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
    <title>Forgot Password - Guspart Autoshop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="auth-page">
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <main id="main-content" class="auth-shell">
        <div class="auth-box">
            <a href="login.php" class="auth-brand-top">
                <img src="gambar1.png" alt="Guspart Autoshop Logo">
                <span>Guspart Autoshop</span>
            </a>

            <div class="auth-card">
                <h1>Forgot Password</h1>
                <p class="auth-card-subtitle">Enter your registered email and we'll generate a time-limited recovery link.</p>

                <?php if ($statusMessage !== ''): ?>
                    <div class="alert alert-<?= e($statusType) ?>" role="alert" aria-live="polite">
                        <?= e($statusMessage) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" data-loading-text="Sending recovery link...">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label for="email">Registered Email</label>
                        <input id="email" type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="you@example.com" autocomplete="email" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg">Send Recovery Link</button>

                    <div class="auth-footer-link">
                        Already have a reset token? <a href="reset_password.php">Reset password</a>
                    </div>
                    <div class="auth-footer-link" style="margin-top: 8px;">
                        <a href="login.php">&larr; Back to Sign In</a>
                    </div>
                </form>
            </div>

            <p class="auth-meta-footer">&copy; 2024 Guspart Autoshop</p>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
</body>

</html>
