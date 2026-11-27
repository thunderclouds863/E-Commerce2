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
                    // Seamless upgrade path for legacy seeded plaintext demo accounts
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
    <title>Sign In - Guspart Autoshop</title>
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
                <h1>Sign In</h1>
                <p class="auth-card-subtitle">Access your customer store or staff portal.</p>

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
                            <option value="" disabled <?= empty($_POST['role']) ? 'selected' : '' ?>>Select your role</option>
                            <option value="customer" <?= (($_POST['role'] ?? '') === 'customer') ? 'selected' : '' ?>>Customer</option>
                            <option value="admin" <?= (($_POST['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option>
                            <option value="courier" <?= (($_POST['role'] ?? '') === 'courier') ? 'selected' : '' ?>>Kurir (Courier)</option>
                            <option value="owner" <?= (($_POST['role'] ?? '') === 'owner') ? 'selected' : '' ?>>Owner</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input id="username" type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>" placeholder="Enter your username" autocomplete="username" required>
                    </div>

                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
                            <label for="password" style="margin-bottom: 0;">Password</label>
                            <a href="forgot_password.php" style="font-size: 0.82rem; font-weight: 600; color: var(--primary);">Forgot password?</a>
                        </div>
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

                    <button type="submit" class="btn btn-primary btn-block btn-lg" name="submit">Sign In</button>

                    <div class="auth-footer-link">
                        Don't have an account? <a href="register.php">Create one</a>
                    </div>
                </form>
            </div>

            <p class="auth-meta-footer">&copy; 2024 Guspart Autoshop</p>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
</body>

</html>
