<?php
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

if (!verify_csrf()) {
    header('Location: register.php?error=' . urlencode('Invalid security token. Please try again.'));
    exit;
}

$role = trim($_POST['role'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if ($username === '' || strlen($username) < 3 || strlen($username) > 50) {
    header('Location: register.php?error=' . urlencode('Username must be between 3 and 50 characters.'));
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: register.php?error=' . urlencode('Please provide a valid email address.'));
    exit;
}

if (strlen($password) < 6) {
    header('Location: register.php?error=' . urlencode('Password must be at least 6 characters long.'));
    exit;
}

if ($password !== $confirm_password) {
    header('Location: register.php?error=' . urlencode('Passwords do not match!'));
    exit;
}

$allowed_roles = ['customer', 'admin', 'courier'];
if (!in_array($role, $allowed_roles, true)) {
    header('Location: register.php?error=' . urlencode('Invalid role selected!'));
    exit;
}

$status = ($role === 'customer') ? 'approved' : 'pending';
$conn = db();

$stmt_check = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
$stmt_check->bind_param('ss', $username, $email);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows > 0) {
    $stmt_check->close();
    header('Location: register.php?error=' . urlencode('Username or email already exists!'));
    exit;
}
$stmt_check->close();

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, ?, ?)');
$stmt->bind_param('sssss', $username, $email, $hashedPassword, $role, $status);

if ($stmt->execute()) {
    $stmt->close();
    $msg = ($status === 'pending')
        ? 'Account created! Your registration is pending owner approval.'
        : 'Account created successfully! You can now sign in.';
    header('Location: login.php?success=' . urlencode($msg));
    exit;
}

$stmt->close();
header('Location: register.php?error=' . urlencode('Registration failed. Please try again.'));
exit;
