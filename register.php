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
    <title>Create Account - Guspart Autoshop</title>
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
                <h1>Create an Account</h1>
                <p class="auth-card-subtitle">Register as a customer or apply for an admin/courier role.</p>

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
                            <option value="">Select role</option>
                            <option value="customer">Customer</option>
                            <option value="admin">Admin (requires owner approval)</option>
                            <option value="courier">Kurir / Courier (requires owner approval)</option>
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

                    <button type="submit" class="btn btn-primary btn-block btn-lg">Create Account</button>

                    <div class="auth-footer-link">
                        Already have an account? <a href="login.php">Sign in</a>
                    </div>
                </form>
            </div>

            <p class="auth-meta-footer">&copy; 2024 Guspart Autoshop</p>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
</body>

</html>
